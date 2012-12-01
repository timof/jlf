<?php
// mysql.php:
// - generic functions related to sql access
//   conventions on function names:
//     sql_do( $query ),  sql_do_*( $query ): execute given query string
//     sql_query(), sql_query_*(): compile and return query string
//     sql_{select|insert|...}: compile _and_ execute query
//     sql_<table>: shortcut for sql_select_table
//   escaping: mysql_real_escape_string() is used just before '-quotations is applied
// - defaults for some table-specific functions (can be overridden by application-specific ones)



//////////////////////////////////////////////
// functions executing given query string:
//

// sql_do(): master function to execute sql query:
//
function sql_do( $sql, $error_text = "MySQL query failed: ", $debug_level = DEBUG_LEVEL_IMPORTANT ) {
  debug( $sql, 'sql query: '.$debug_level, $debug_level );
  if( ! ( $result = mysql_query( $sql ) ) ) {
    error( $error_text. "\n  query: $sql\n  MySQL error: " . mysql_error(), LOG_FLAG_CODE | LOG_FLAG_DATA, 'sql' );
  }
  return $result;
}


// mysql2array(): return result of SELECT query as an array of rows
// - numerical indices are default; field `nr' will be added to every row (counting from 0)
// - if $key and $val are given: return associative array, mapping every `$key' to `$val'
// - special case: if $key === true, use value2uid() to generate the key for every $val
//
function mysql2array( $result, $key = false, $val = false ) {
  $r = array();
  if( $key === true ) {
    while( $row = mysql_fetch_array( $result, MYSQL_ASSOC ) ) {
      need( isset( $row[ $val ] ) );
      $v = $row[ $val ];
      $r[ value2uid( $v ) ] = $v;
    }
  } else if( $key ) {
    while( $row = mysql_fetch_array( $result, MYSQL_ASSOC ) ) {
      need( isset( $row[ $key ] ) && isset( $row[ $val ] ) );
      $r[ $row[ $key ] ] = $row[ $val ];
    }
  } else {
    $n = 0;
    while( $row = mysql_fetch_array( $result, MYSQL_ASSOC ) ) {
      $row['nr'] = $n++;
      $r[] = $row;
    }
  }
  return $r;
}


///////////////////////////////////////////
// functions to compile query strings:
//

///////////////////////////////////////////////////////
// 1. functions to compile an sql filter expression:
//


// $tlist_in may be
//   - array of <table_alias> => <table_name> mappings,
//   - list of table names
//   - a string "<table>|<alias>=<table> [, ... ]"
// $joins may be
//   - list of JOIN rules,
//   - array of <table_alias> => <join_rule> mappings
// $hints contains hints to cook raw_atoms:
//   - list of <key> => <new_key> mappings
//   - list of <key> => array( <rel>, <key>, <val> ); missing entries in array will be left unchanged
//
function sql_canonicalize_filters( $tlist_in, $filters_in, $joins = array(), $selects = array(), $hints = array() ) {

  // this function is idempotent - calling it again on already canonicalized filters is a nop:
  //
  if( adefault( $filters_in, -1, '' ) === 'canonical_filter' )
    return $filters_in; // already canonicalized - return as-is

  $tlist_in = parameters_explode( $tlist_in, 'default_value=1' );
  $tlist = array();
  foreach( $tlist_in as $key => $val ) {
    if( is_numeric( $key ) ) {
      $tlist[ $val ] = $val;
    } else if( "$val" === "1" ) {
      $tlist[ $key ] = $key;
    } else {
      $tlist[ $key ] = $val;
    }
  }
  need( isarray( $joins ) );
  foreach( $joins as $key => $val ) {
    preg_match( '/^(LEFT )? *([^ ]+)/', $val, /* & */ $matches );
    $tname = $matches[ 2 ];
    $tlist[ is_numeric( $key ) ? $tname : $key ] = $tname;
  }
  $table = reset( $tlist );

  $root = sql_canonicalize_filters_rec( $filters_in );
  // debug( $root, 'sql_canonicalize_filters: raw root' );

  $rv = array( -1 => 'canonical_filter', 0 => $root, 1 => array(), 2 => array() );
  cook_atoms_rec( /* & */ $rv[ 0 ], /* & */ $rv[ 1 ], /* & */ $rv[ 2 ], $hints, $selects, $tlist, $table );

  // debug( $rv, 'sql_canonicalize_filters: cooked rv' );
  return $rv;
}

function cook_atoms_rec( & $node, & $raw_atoms, & $cooked_atoms, $hints, $selects, $tlist, $table ) {
  global $tables;
  switch( $node[ -1 ] ) {
    case 'filter_list':
      for( $i = 1; isset( $node[ $i ] ); $i++ ) {
        cook_atoms_rec( /* & */ $node[ $i ], /* & */ $raw_atoms, /* & */ $cooked_atoms, $hints, $selects, $tlist, $table );
      }
      break;
    case 'cooked_atom':
      $cooked_atoms[] = & $node;
      break;
    case 'raw_atom':
      $key = & $node[ 1 ];
      // discard arbitrary prefix beginning with 'F':
      if( $key[ 0 ] === 'F' ) {
        $key = preg_replace( '/^F[^_]*_/', '', $key );
      }
      if( $h = adefault( $hints, $key ) ) {
        if( isarray( $h ) ) {
          for( $i = 0; $i <= 2; $i++ ) {
            if( isset( $h[ $i ] ) )
              $node[ $i ] = $h[ $i ];
          }
        } else {
          $key = $h;
        }
        $node[ -1 ] = 'cooked_atom';
        $cooked_atoms[] = & $node;
        break;
      } else if( "$key" === 'id' ) {
        // 'id' is short for that table's primary key (every table must have one):
        $key = $table.'.'.$table.'_id';
        $node[ -1 ] = 'cooked_atom';
        break;
      } else {
        $t = explode( '.', $key );
        if( isset( $t[ 1 ] ) ) {
          if( isset( $tlist[ $t[ 0 ] ] ) ) {
            if( isset( $tables[ $tlist[ $t[ 0 ] ] ]['cols'][ $t[ 1 ] ] ) ) {
              // ok: $key is <table_alias>.<column>
              $node[ -1 ] = 'cooked_atom';
              $cooked_atoms[] = & $node;
              break;
            }
          }
        } else {
          foreach( $tlist as $talias => $tname ) {
            if( isset( $tables[ $tname ]['cols'][ $key ] ) ) {
              $key = "$talias.$key";
              $node[ -1 ] = 'cooked_atom';
              $cooked_atoms[] = & $node;
              break 2;
            }
          }
          if( isset( $selects[ $key ] ) ) {
            // $key is not a plain column name, but alias of a selected expression - try to put it in HAVING clause:
            $key = "H:$key";
            $node[ -1 ] = 'cooked_atom';
            $cooked_atoms[] = & $node;
            break 1;
          }
        }
      }
      // could not handle - put on raw list to be cooked later:
      $raw_atoms[] = & $node;
      break;
  }
  // debug( $rv, 'sql_canonicalize_filters: after handling atoms: ' );
}

function atom_rhs_unescape( $in ) {
  $r = '';
  while( ( $n = strpos( $in, '=' ) ) !== false ) {
    if( preg_match( '/^([0-9a-f]{2})$/', substr( $in, $n+1, 2 ), /* & */ $matches ) ) {
      $r .= substr( $in, 0, $n );
      $r .= pack( 'H*', $matches[ 1 ] );
      $in = substr( $in, $n + 3 );
    } else {
      $r .= substr( $in, 0, $n + 1 );
      $in = substr( $in, $n + 1 );
    }
  }
  $r .= $in;
  need( check_utf8( $r ), 'rhs of atom: not valid utf-8' );
  return $r;
}

// split_atom():
//   split "KEY REL VAL" atomic expression string into parts;
//   REL and VAL may be absent to check for boolean true of KEY
//   otherwise, REL must be one of '=', '>=', '<=', '!=', '~=', '%=' (sql: LIKE), '&=' ( check: KEY & VAL == VAL )
//   white space around KEY and VAL will be trimmed; if after trimming VAL starts with ':', the remainder will be base64-decoded
function split_atom( $a, $default_rel = '!0' ) {
  $a = trim( $a );
  if( ( $n2 = strpos( $a, '=' ) ) > 0 ) {
    $n1 = $n2;
    if( strpos( ' &<>!~%', $a[ $n2 - 1 ] ) > 0 ) {
      $n1--;
    } else if( isset( $a[ $n2 + 1 ] ) && ( ! isset( $a[ $n2 + 2 ] ) ) && ( $a[ $n2 + 1 ] == '0' ) ) {
      $n2++;
    }
  } else if( ( $n2 = strpos( $a, '>' ) ) > 0 ) {
    $n1 = $n2;
  } else if( ( $n2 = strpos( $a, '<' ) ) > 0 ) {
    $n1 = $n2;
  } else {
    $n1 = $n2 = 0;
  }
  if( $n2 > 0 ) {
    $rel = substr( $a, $n1, $n2 - $n1 + 1 );
    $key = trim( substr( $a, 0, $n1 ) );
    $val = atom_rhs_unescape( trim( substr( $a, $n2 + 1 ) ) );
    if( strncmp( $val, ':', 1 ) == 0 ) {
      $val = base64_decode( substr( $val, 1 ) );
    }
    return array( -1 => 'raw_atom', 0 => $rel, 1 => $key, 2 => $val );
  } else {
    return array( -1 => 'raw_atom', 0 => $default_rel, 1 => trim( $a ), 2 => '' );
  }
}

// parse filter string: $line can be
// - comma-separated list of atoms (as in split_atom()) to be AND-ed (old-style)
// - LDAP-style polish notation expression
// - to make the expression CLI-safe, ( & ( ! ( | ( a=b ) ) ) )
//
function parse_filter_string( $line ) {
  $r = parse_filter_string_rec( /* & */ $line );
  need( ! $line, 'parse error: trailing characters in filter string' );
  return $r;
}

function parse_filter_string_rec( & $line ) {
  $line = trim( $line );
  $len = strlen( $line );
  if( $len < 1 ) {
    return array( -1 => 'filter_list', 0 => '&&' );
  }
  if( $line[ 0 ] !== '(' ) {
    // old style: comma-separeted list of atoms:
    $atoms = explode( ',', $line );
    $line = ''; // no unparsed chars left
    switch( count( $atoms ) ) {
      case 0:
        return array( -1 => 'filter_list', 0 => '&&' );
      case 1:
        return split_atom( $atoms[ 0 ] );
      default:
        $rv = array( -1 => 'filter_list', 0 => '&&' );
        foreach( $atoms as $a ) {
          $rv[] = split_atom( $a );
        }
        return $rv;
    }
  }
  // need( $len >= 3, 'parse error: incomplete expression' );
  $line = trim( substr( $line, 1 ) ); // strip opening parenthesis
  need( $line, 'parse error: missing operator or atom' );
  switch( $line[ 0 ] ) {
    case '&':
      $op = '&&';
      $sublist = true;
      break;
    case '|':
      $op = '||';
      $sublist = true;
      break;
    case '!':
      $op = '!';
      $sublist = true;
      break;
    default:
      $sublist = false;
      break;
  }
  if( $sublist ) {
    $line = trim( substr( $line, 1 ) ); // strip operator
    $flist = array( -1 => 'filter_list', 0 => $op );
    while( true ) {
      $line = ltrim( $line );
      need( $line, 'parse error: incomplete expression' );
      switch( $line[ 0 ] ) {
        case ')':
          $line = substr( $line, 1 );
          return $flist;
        case '(':
          $flist[] = parse_filter_string_rec( /* & */ $line );
          break;
        default:
          error( 'parse error', LOG_FLAG_CODE, 'sql,filter' );
      }
    }
  } else {
    need( ( $end = strpos( $line, ')' ) ), 'parse error: no closing parenthesis for atom' );
    $a = substr( $line, 0, $end );
    $line = substr( $line, $end + 1 );
    return split_atom( $a );
  }
}

// sql_canonicalize_filters_rec(): worker function to recursively canonicalize $filters_in:
//
// - input $filters_in is a FILTER, where
//   - FILTER ::= FINT | FSTRING | FARRAY
//   - FINT ::= n      (short for primary key: maps to ATOM array( '=', 'id', n ) )
//   - FSTRING ::= "KEY [REL VAL] [ , ... ]"
//   - FARRAY ::= FATOM | FLIST | CANONICAL_FILTER
//   - FATOM ::= array( REL, KEY, RHS )
//   - RHS ::= VAL | array( VAL [ , ... ] )
//   - FLIST ::= array( [ OP ,] [ FILTER [ , ... ] ] [ 'KEY [REL]' => 'VAL' [ , ... ] ] )
//
// - returns CANONICAL_FILTER ::= array( -1 => 'canonical_filter', NODE, ALIST ), where
//   - ALIST ::= array( [ ATOM ] [, ...] )
//   - ATOM ::= array( -1 => 'raw_atom'|'cooked_atom', REL, KEY, RHS ) 
//   - NODE ::= ATOM | FTREE
//   - FTREE ::= array( -1 => 'filter_list', OP, [ NODE ] [, ... ] )
//   - OP ::=  '&&' | '||' | '!'  (boolean operations to compose filters)
//   - REL ::= '=' | '<=' | '>=' | '!=' | '~=' | '%=' | '!0'  | '&=' (boolean relations to be used in atomic expressions)
//
function sql_canonicalize_filters_rec( $filters_in ) {

  if( ( $filters_in === array() ) || ( $filters_in === NULL ) || ( $filters_in === '' ) || ( $filters_in === true ) ) {
    return array( -1 => 'filter_list', '0' => '&&' );
  }

  if( $filters_in === false ) {
    return array( -1 => 'filter_list', '0' => '||' );
  }

  if( is_numeric( $filters_in ) ) {  // guess: is primary key
    return array( -1 => 'raw_atom' , 0 => '=', 1 => 'id', 2 => $filters_in );
  }
  if( is_string( $filters_in ) ) {
    return parse_filter_string( $filters_in );
  }
  if( is_array( $filters_in ) ) {
    switch( adefault( $filters_in, -1, '' ) ) {
      case 'canonical_filter':
        return $filters_in[ 0 ];
      case 'filter_list':
      case 'raw_atom':
      case 'cooked_atom':
        return $filters_in;
    }
    // print_on_exit( "<!-- sql_canonicalize_filters_rec: in: " .var_export( $filters_in, true ). " -->" );
    $op = '&&';
    if( isset( $filters_in[ 0 ] ) && isstring( $filters_in[ 0 ] ) ) {
      switch( $filters_in[ 0 ] ) {
        case '&&':
        case '||':
        case '!':
          // filters_in[ 0 ] is boolean operator - copy and skip it:
          $op = $filters_in[ 0 ];
          unset( $filters_in[ 0 ] );
          break;
        case '>':
        case '>=':
        case '<':
        case '<=':
        case '=':
        case '!=':
        case '~=':
        case '%=':
        case '!0':
        case '=0':
        case '&=':
          // assume: $filters is an atom:
          return array( -1 => 'raw_atom' ) + $filters_in;
      }
    }
    $rv = array( -1 => 'filter_list', 0 => $op );
    foreach( $filters_in as $key => $cond ) {
      if( is_numeric( $key ) ) {
        $rv[] = sql_canonicalize_filters_rec( $cond );
      } else {
        $a = split_atom( $key, '=' );
        $a[ 2 ] = $cond;
        $rv[] = $a;
      }
    }
    return $rv;
  }
  error( 'cannot handle input filters', LOG_FLAG_CODE, 'sql,filter' );
}


// sql_filters2expressions:
//  - turn $filters into an sql where-clause, and - if needed - an additional having_clause
//  - $filters must be canonicalized before calling this function
//
function sql_filters2expressions( $can_filters ) {
  need( $can_filters[ -1 ] === 'canonical_filter' );
  $having_clause = '';
  $where_clause = sql_filters2expressions_rec( $can_filters, /* & */ $having_clause );
  // if( $having_clause ) {
  //   debug( $where_clause, 'where_clause' ) ;
  //   debug( $having_clause, 'having_clause' ) ;
  //   debug( $can_filters, 'can_filters' );
  // }
  return array( $where_clause, $having_clause );
}

function sql_filters2expressions_rec( $f, & $having_clause = false ) {
  switch( $f[ -1 ] ) {
    case 'cooked_atom':
      $op = $f[ 0 ];
      $key = $f[ 1 ];
      $rhs = $f[ 2 ];
      if( ( $is_having = ( substr( $key, 0, 2 ) === 'H:' ) ) ) {
        $key = substr( $key, 2 );
      }
      if( $op === '~=' ) {
        $op = 'RLIKE';
      } else if( $op === '%=' ) {
        $op = 'LIKE';
      } else if( $op === '!0' ) {
        $rhs = $op = '';
      } else if( $op === '=0' ) {
        $rhs = $op = '';
        $key = "NOT ( $key )";
      } else if( $op === '&=' ) {
        $key = "( $key & $rhs )";
        $op = '=';
      }
      if( is_array( $rhs ) ) {
        switch( "$op" ) {
          case '=':
            if( ! $rhs )
              return 'FALSE';
            $op = 'IN';
            break;
          case '!=':
            if( ! $rhs )
              return 'TRUE';
            $op = 'NOT IN';
            break;
          default:
            error( "cannot compare list with operator [$op]", LOG_LEVEL_CODE, 'sql,filter' );
        }
        $s = '(';
        $comma = '';
        foreach( $rhs as $c ) {
          $s .= "$comma '".mysql_real_escape_string( $c )."'";
          $comma = ',';
        }
        $rhs = $s . ')';
      } else if( $op ) {
        $rhs = "'".mysql_real_escape_string( $rhs )."'";
      } else {
        $rhs = '';
      }
      if( $is_having ) {
        // debug( $f, 'having atom' );
        need( $having_clause !== false, 'cannot code complex filter into HAVING clause' );
        if( $having_clause ) {
           $having_clause .= ' AND ';
        }
        $having_clause .= sprintf( "( ( %s ) %s %s )", $key, $op, $rhs );
        return 'TRUE';
      } else {
        return sprintf( "( %s ) %s %s", $key, $op, $rhs );
      }
    case 'filter_list':
      $op = $f[ 0 ];
      unset( $f[ -1 ] );
      unset( $f[ 0 ] );
      $sql = '';
      $having_sql = '';
      switch( $op ) {
        case '&&':
          if( ! $f )
            return 'TRUE';
          $op = 'AND';
          break;
        case '||':
          if( ! $f )
            return 'FALSE';
          $op = 'OR';
          break;
        case '!':
          need( count( $f ) === 1, 'NOT requires one operand' );
          $sql = 'NOT';
          $op = '';
          break;
        default:
          error( "cannot handle operator [$op]", LOG_FLAG_CODE, 'sql,filter' );
      }
      if( $op !== 'AND' ) {
        unset( $having_clause ); // break reference
        $having_clause = false;
      }
      foreach( $f as $subnode ) {
        if( $sql )
          $sql .= $op;
        $sql .= ' ( ' . sql_filters2expressions_rec( $subnode, /* & */ $having_clause ) . ' ) ';
      }
      return $sql;
    case 'canonical_filter':
      need( $f[ 1 ] === array(), 'list of raw atoms not empty' );
      return sql_filters2expressions_rec( $f[ 0 ], /* & */ $having_clause );
    case 'raw_atom':
      error( 'unhandled atom encountered', LOG_FLAG_CODE, 'sql,filter' );
    default:
      error( 'unexpected filter element', LOG_FLAG_CODE, 'sql,filter' );
  }
  return $sql;
}



//////////////////////
// 2. functions to compile SELECT queries
//

// sql_default_selects():
// return SELECT clauses for all colums in all given tables:
//  $tnames may be
//    - <table_name> or list of table names
//    - array of <table_alias> => <table_name> | <table_options>
//    - table options may contain
//       'table' => <table_name>
//       'prefix' => <prefix> to prepend to this tables column names (for disambiguation)
//       '.<column>' => <identifier> special disambiguation rule for <column> (prefix does not apply)
//       '.<column>' => FALSE to skip this column
//
function sql_default_selects( $tnames ) {
  global $tables;

  $selects = array();
  if( isstring( $tnames ) ) {
    $tnames = parameters_explode( $tnames );
  }
  foreach( $tnames as $alias => $topts ) {
    if( $topts == 1 ) {
      $tname = $alias;
      $topts = array();
    } else {
      $topts = parameters_explode( $topts, 'default_key=table' );
      $tname = adefault( $topts, 'table', $alias );
      if( is_numeric( $alias ) ) {
        $alias = $tname;
      }
    }
    $t = $tables[ $tname ];
    $cols = $t['cols'] + adefault( $t, 'more_selects', array() );
    $prefix = adefault( $topts, 'prefix', '' );
    foreach( $cols as $name => $type ) {
      $rule = ( is_array( $type ) ? "$alias.$name" : $type );
      if( ( $crule = adefault( $topts, ".$name", NULL ) ) !== NULL ) {
        if( $crule === FALSE )
          continue;
        else
          $selects[ $crule ] = $rule;
      } else {
        $selects[ "$prefix$name" ] = $rule;
      }
    }
  }
  return $selects;
}


/*
 * use_filters: to be used in scalar subqueries as in "SELECT x , ( SELECT ... ) as y, z":
 *  generate optional filters refering to tables already available from outer context
 */
function use_filters_array( $tlist, $using, $rules ) {
  $filters = array();
  is_array( $using ) or $using = explode( ',', $using );
  foreach( $rules as $table => $f ) {
    if( in_array( $table, $using ) ) {
      $filters[] = $f;
    }
  }
  return $filters;
}
function use_filters( $tlist, $using, $rules ) {
  $can_filters = sql_canonicalize_filters( $tlist, use_filters_array( $using, $rules ) );
  return sql_filters2expressions_rec( $can_filters ); // cannot use HAVING here!
}

function joins2expression( $joins = array(), $using = array() ) {
  $using = parameters_explode( $using );
  // $joins = parameters_explode( $joins );
  need( isarray( $joins ) );
  $sql = '';
  foreach( $joins as $key => $val ) {
    if( is_numeric( $key ) ) {
      $rule = $val;
      $talias = false;
    } else {
      $rule = $val;
      if( isset( $using[ $key ] ) )
        continue;
      $talias = $key;
    }
    // preg_match( '/^(LEFT )? *([^ ]+) *(ON|USING)? *([^ ].*)$/', $rule, & $matches );
    preg_match( '/^(LEFT )?(OUTER )? *([^ ]+) *([^ ].*)?$/', $rule, /* & */ $matches );
    $tname = $matches[ 3 ];
    if( ( ! $talias ) && isset( $using[ $tname ] ) )
      continue;
    $sql .= ( ' ' . $matches[ 1 ] . $matches[ 2 ] . 'JOIN ' . $tname );
    if( $talias ) {
      $sql .= ( ' AS ' . $talias );
    }
    $sql .= ( ' ' . $matches[ 4 ] );
  }
  return $sql;
}


// sql_query(): compose sql SELECT query from parts:
//
function sql_query( $table, $opts = array() ) {
  $opts = parameters_explode( $opts, 'filters' );

  $filters = adefault( $opts, 'filters', false );
  $selects = adefault( $opts, 'selects', true );
  if( $selects === true ) {
    $selects = sql_default_selects( $table );
  };
  $joins = adefault( $opts, 'joins', array() );
  $having = adefault( $opts, 'having', false );
  $orderby = adefault( $opts, 'orderby', false );
  $debug = adefault( $opts, 'debug', false );
  $limit_from = adefault( $opts, 'limit_from', 0 );
  $limit_count = adefault( $opts, 'limit_count', 0 );
  $single_row = ( isset( $opts['single_row'] ) ? $opts['single_row'] : '' );
  $single_field = ( isset( $opts['single_field'] ) ? $opts['single_field'] : '' );

  if( ( $distinct = ( isset( $opts['distinct'] ) ? $opts['distinct'] : '' ) ) ) {
    $selects = "DISTINCT $distinct";
  } else {
    switch( $single_field ) {
      case 'COUNT':
        $single_field = 'count';
        $selects = 'COUNT';
        break;
      case 'LAST_ID':
        $single_field = 'last_id';
        $selects = 'LAST_ID';
        break;
    }
  }

  if( is_string( $selects ) ) {
    $select_string = $selects;
  } else {
    $select_string = '';
    $comma = '';
    foreach( $selects as $key => $val ) {
      if( ! $val ) {
        continue;
      } else if( isnumeric( $key ) ) {
        $select_string .= "$comma $val";
      } else if( isstring( $val ) ) {
        $select_string .= "$comma $val AS `$key`";
      } else {
        // deprecated syntax: allow 'x AS y' => true
        // $select_string .= "$comma $key";
        error( 'deprecated syntax in $selects' );
      }
      $comma = ',';
    }
  }
  $join_string = joins2expression( $joins );

  // some special things to select:
  switch( $select_string ) {
    // with no groupby, the following default grouping rules apply:
    // - aggregate functions COUNT(*), MAX(*), .. will group _all_ rows into a single one which is returned;
    // - non-aggregate selects will cause no grouping at all and return all rows individually
    // thus, these two types of functions cannot be mixed unless groupby is explicitely specified
    case 'COUNT':
      $select_string = "COUNT(*) AS count";
      $groupby = false;
      break;
    case 'LAST_ID':
      $select_string = "MAX( {$table}_id ) AS last_id";
      $groupby = false;
      break;
    default:
      $groupby = adefault( $opts, 'groupby', false );
      if( $groupby ) {
        // default_query_options() will group by "{$table}.{$table}_id", so by default, you get a free count:
        $select_string .= "$comma COUNT(*) AS count";
      }
      break;
  }
  $query = "SELECT $select_string FROM $table $join_string";

  $having_clause = '';
  if( $filters !== false ) {
    // $cf = sql_canonicalize_filters( $table, $filters, $joins );
    // TODO: would be good to allow $joins here, but we cannot handle table aliases in $joins yet, so...
    $cf = sql_canonicalize_filters( $table, $filters );
    list( $where_clause, $having_clause ) = sql_filters2expressions( $cf );
    $query .= ( " WHERE " . $where_clause );
  }
  if( $groupby ) {
    $query .= " GROUP BY $groupby ";
  }
  if( $having !== false ) {
    $cf = sql_canonicalize_filters( $table, $having );
    $more_having = sql_filters2expressions( $cf, 0, /* & */ $having_clause );
    if( $more_having ) {
      $having_clause .= ( $having_clause ? ( ' AND ( ' . $more_having . ' ) ' ) : $more_having );
    }
  }
  if( $having_clause ) {
    $query .= ( " HAVING " . $having_clause );
  }
  if( $orderby ) {
    $query .= " ORDER BY $orderby ";
  }
  if( $limit_count ) {
    if( ! $limit_from )
      $limit_from = 1;
  }
  if( $limit_from ) {
    if( ! $limit_count )
      $limit_count = 99999;
    $query .= sprintf( " LIMIT %u OFFSET %u", $limit_count, $limit_from - 1 );
  }
  if( $debug ) {
    debug( $debug, 'debug' );
    debug( $query, 'query' );
  }
  if( isset( $opts['noexec'] ) ? $opts['noexec'] : false ) {
    return $query;
  }
  $result = sql_do( $query );
  if( $single_row || $single_field ) {
    if( ( $rows = mysql_num_rows( $result ) ) == 0 ) {
      if( ( $default = adefault( $opts, 'default', false ) ) !== false )
        return $default;
    }
    need( $rows > 0, "no match: $query" );
    need( $rows == 1, "result of query $query not unique ($rows rows returned)" );
    $row = mysql_fetch_array( $result, MYSQL_ASSOC );
    if( $single_row ) {
      return $row;
    }
    need( isset( $row[ $single_field ] ), "no such column: $single_field" );
    return $row[ $single_field ];
  }
  if( $distinct ) {
    return mysql2array( $result, true, $distinct );
  } else {
    return mysql2array( $result );
  }
}



/////////////////////////////////////////////////////
// functions to compile and execute query strings:
//



function sql_delete( $table, $filters = false ) {
  $cf = sql_canonicalize_filters( $table, $filters );
  list( $where_clause, $having_clause ) = sql_filters2expressions( $cf );
  need( ! $having_clause, 'cannot use HAVING in DELETE statement' );
  $sql = "DELETE FROM $table WHERE " . $where_clause;
  return sql_do( $sql );
}

function copy_to_changelog( $table, $id ) {
  global $tables;

  $cols = $tables[ $table ]['cols'];
  $maxlen = $tables[ $table ]['cols']['changelog_id']['maxlen'];

  $current = sql_query( $table, "$id,selects=*,single_row=1" );
  foreach( $current as $name => $val ) {
    $len = strlen( $val );
    if( $len > $maxlen ) { // truncate long entries: store only...
      $current[ $name ] = array(
        'length' => $len                // ...original length...
      , 'md5' => md5( $val )         // ...a good hash and...
      , 'head' => substr( $val, 0, 32 ) // ...the first couple of bytes
      );
    }
  }
  return sql_insert( 'changelog', array(
    'table' => $table
  , 'key' => $id
  , 'prev_changelog_id' => $current['changelog_id']
  , 'payload' => json_encode( $current )
  ) );
}

function sql_update( $table, $filters, $values, $opts = array() ) {
  global $tables, $utc, $login_sessions_id;

  $opts = parameters_explode( $opts );
  $escape_and_quote = adefault( $opts, 'escape_and_quote', true );
  if( ( $table !== 'changelog' ) && isset( $tables[ $table ]['cols']['changelog_id'] ) ) {
    $changelog = adefault( $opts, 'changelog', true );
  } else {
    $changelog = false;
  }

  $values = parameters_explode( $values );
  switch( $table ) {
    case 'leitvariable':
    case 'transactions':
    case 'logbook':
    case 'sessions':
    case 'persistent_vars':
      break;
    default:
      fail_if_readonly();
  }
  if( isset( $tables[ $table ]['cols']['mtime'] ) ) {
    $values['mtime'] = $utc;
  }
  if( isset( $tables[ $table ]['cols']['modifier_sessions_id'] ) ) {
    $values['modifier_sessions_id'] = $login_sessions_id;
  }
  if( $changelog ) {
    if( is_numeric( $filters ) ) {
      $values['changelog_id'] = copy_to_changelog( $table, $filters );
    } else {
      // serialize it:
      $matches = sql_query( $table, array( 'filters' => $filters, 'selects' => "$table.{$table}_id" ) );
      $rv = true;
      foreach( $matches as $row ) {
        $rv = ( $rv && sql_update( $table, $row[ $table.'_id' ], $values, $opts ) );
      }
      return $rv;
    }
  }
  list( $where_clause, $having_clause ) = sql_filters2expressions( sql_canonicalize_filters( $table, $filters ) );
  need( ! $having_clause, 'cannot use HAVING in UPDATE statement' );
  $sql = "UPDATE $table SET";
  $comma='';
  foreach( $values as $key => $val ) {
    if( $escape_and_quote )
      $val = "'" . mysql_real_escape_string($val) . "'";
    $sql .= "$comma $key=$val";
    $comma=',';
  }
  $sql .= ( " WHERE " . $where_clause );

  return sql_do( $sql, "failed to update table $table: " );
}

function sql_insert( $table, $values, $opts = array() ) {
  global $tables, $utc, $login_sessions_id, $login_people_id;

  $opts = parameters_explode( $opts );
  $update_cols = adefault( $opts, 'update_cols', false );
  $escape_and_quote = adefault( $opts, 'escape_and_quote', true );
  switch( $table ) {
    case 'leitvariable':
    case 'transactions':
    case 'logbook':
    case 'sessions':
      break;
    default:
      fail_if_readonly();
  }
  if( isset( $tables[ $table ]['cols']['ctime'] ) ) {
    $values['ctime'] = $utc;
  }
  if( isset( $tables[ $table ]['cols']['creator_sessions_id'] ) ) {
    $values['creator_sessions_id'] = $login_sessions_id;
  }
  if( isset( $tables[ $table ]['cols']['creator_people_id'] ) ) {
    $values['creator_people_id'] = $login_people_id;
  }
  $comma='';
  $update_comma='';
  $cols = '';
  $vals = '';
  $update = '';
  foreach( $values as $key => $val ) {
    $cols .= "$comma `$key`";
    if( is_array( $val ) ) {
      error( 'sql_insert: array detected:', LOG_FLAG_CODE | LOG_FLAG_INSERT, 'sql,insert' );
    }
    if( $escape_and_quote )
      $val = "'" . mysql_real_escape_string($val) . "'";

    $vals .= "$comma $val";
    if( is_array( $update_cols ) ) {
      if( isset( $update_cols[$key] ) ) {
        if( $update_cols[$key] !== true ) {
          $val = $update_cols[$key];
          if( $escape_and_quote )
            $val = "'" . mysql_real_escape_string($val) . "'";
        }
        $update .= "$update_comma $key=$val";
        $update_comma=',';
      }
    } elseif( $update_cols ) {
      $update .= "$update_comma $key=$val";
      $update_comma=',';
    }
    $comma=',';
  }
  $sql = "INSERT INTO $table ( $cols ) VALUES ( $vals )";
  if( $update_cols or is_array( $update_cols ) ) {
    $sql .= " ON DUPLICATE KEY UPDATE $update";
    if( isset( $tables[ $table ][ 'cols' ][ $table.'_id' ] ) )
      // a strange kludge required to cause mysql_insert_id (see below) to be set in case of update:
      $sql .= "$update_comma {$table}_id = LAST_INSERT_ID( {$table}_id ) ";
  }
  if( sql_do( $sql, "failed to insert into table $table: " ) )
    return mysql_insert_id();
  else
    return FALSE;
}

// validate_row(): check $values for compliance with column types in $table before insert/update
// - simple check to validate values against their types before insert/update;
// - more subtle checks (other than simple type checks) should be done in in sql_*_save();
//
function validate_row( $table, $values, $opts = array() ) {
  $cols = $GLOBALS['tables'][ $table ]['cols'];
  $opts = parameters_explode( $opts );
  $update = adefault( $opts, 'update' );
  $check = adefault( $opts, 'check' );
  $problems = array();
  foreach( $cols as $name => $col ) {
    if( $name === $table.'_id' ) {
      continue;
    }
    $type = jlf_complete_type( $col );
    if( isset( $values[ $name ] ) ) {
      if( checkvalue( $values[ $name ], $type ) === NULL ) {
        if( $check ) {
          logger( "validate_row: type mismatch for: [$name]", LOG_LEVEL_WARNING, LOG_FLAG_CODE, 'validate_row' ); 
          $problems[ $name ] = 'illegal value specified';
        } else {
          error( "validate_row: type mismatch for: [$name]", LOG_FLAG_CODE | LOG_FLAG_ABORT, 'validate_row' ); 
        }
      }
    } else {
      if( ! $update ) {
        // default may just be the default to init an input form - not necessarily a legal value:
        if( checkvalue( $type['default'], $type ) === NULL ) {
          if( $check ) {
            logger( "validate_row: default not a legal value for: [$name]", LOG_LEVEL_WARNING, LOG_FLAG_CODE, 'validate_row' ); 
            $problems[ $name ] = 'default is not a legal value';
          } else {
            error( "validate_row: default not a legal value for: [$name]", LOG_FLAG_CODE | LOG_FLAG_ABORT, 'validate_row' ); 
          }
        }
      }
    }
  }
  return $problems;
}


///////////////////////
// function to handle relation tables
//
// 
// function sql_get_relation( $table_1, $table_2, $table_relation, $filters_1 = array(), $filters_2 = array() ) {
//   $filters_1 = sql_canonicalize_filters( $table_1, $filters_1 );
//   $filters_2 = sql_canonicalize_filters( $table_2, $filters_2 );
//   $joins = array( $table_1 => $table_1.'_id', $table_2 => $table_2.'_id' );
//   $selects = array( $table_relation.'.'.$table_1.'_id', $table_relation.'.'.$table_2.'_id' );
//   $orderby = $table_relation.'.'.$table_1.'_id, '.$table_relation.'.'.$table_2.'_id';
//   $f = array( '&&', $filters_1['filters'], $filters_2['filters'] );
//   $sql = sql_query( $table_relation, array( 'filters' => $f, 'selects' => $selects, 'joins' => $joins ) );
//   $relation = mysql2array( sql_do( $sql ) );
//   return $relation;
// }
// 
// function sql_relation_on( $table_1, $table_2, $table_relation, $id_1, $id_2 ) {
//   $values = array( $table_1.'_id' => $id_1 , $table_2.'_id' => $id_2 );
//   return sql_insert( $table_relation, $values );
// }
// 
// // function sql_relation_off( $table_1, $table_2, $table_relation, $id_1, $id_2 ) {
// //  $values = array( $table_1.'_id' => $id_1 , $table_2.'_id' => $id_2 );
// //  return sql_insert( $table_relation, $values );
// // }
// 


// sql_references():
// find references to entry $referent_id in table $referent
// - references are any fields in any table, whose column name matches {$referent}_id" or *_{$referent}_id and whose value is $referent_id
// - $rules has 3 significant fields:
//   'ignore': references in these columns are ignored; supported formats:
//     'ignore=<table1>[:col1:col2:...] <table2>...'
//     'ignore' => '<table1>>[:col1:col2:...] <table2>...'
//     'ignore' => array( '<table1>', 'table2' => 'col1:col2:...' )
//     'ignore' => array( '<table1>' => array( 'col1', 'col2', ... )
//   if no columns are specified for a table, all columns in that <table> are ignored
//   the primary key field $referent.{$refefent}_id will always be ignored.
//   'prune': table entries with references in these colums will be deleted; formats like 'ignore'
//   'reset': references in these columns will be reset to 0; formats like 'ignore'
// - the function returns an 2-level a-array with entries of the form
//   'refering table' => 'refering col' => <count>
//   only non-zero counts, and only 'refering tables' with at least one 'refering col' will be returned.
//   only references not handled by $rules will be counted: if no unhandled references are found, an empty array will be returned.
//
function sql_references( $referent, $referent_id, $rules = array() ) {
  $rules = parameters_explode( $rules );

  $ignore = adefault( $rules, 'ignore', array() );
  $ignore = parameters_explode( $ignore, 'separator= ' );
  foreach( $ignore as $key => $val ) {
    if( $val === 1 ) {
      if( ( $n = strpos( $key, ':' ) ) ) {
        $val = parameters_explode( substr( $key, $n + 1 ), 'separator=:' );
        unset( $ignore[ $key ] );
        $ignore[ substr( $key, 0, $n ) ] = $val;
      }
    } else {
      $ignore[ $key ] = parameters_explode( $val, 'separator=:' );
    }
  }

  $prune = adefault( $rules, 'prune', array() );
  $prune = parameters_explode( $prune, 'separator= ' );
  foreach( $prune as $key => $val ) {
    if( $val === 1 ) {
      if( ( $n = strpos( $key, ':' ) ) ) {
        $val = parameters_explode( substr( $key, $n + 1 ), 'separator=:' );
        unset( $prune[ $key ] );
        $prune[ substr( $key, 0, $n ) ] = $val;
      }
    } else {
      $prune[ $key ] = parameters_explode( $val, 'separator=:' );
    }
  }

  $reset = adefault( $rules, 'reset', array() );
  $reset = parameters_explode( $reset, 'separator= ' );
  foreach( $reset as $key => $val ) {
    if( $val === 1 ) {
      if( ( $n = strpos( $key, ':' ) ) ) {
        $val = parameters_explode( substr( $key, $n + 1 ), 'separator=:' );
        unset( $reset[ $key ] );
        $reset[ substr( $key, 0, $n ) ] = $val;
      }
    } else {
      $reset[ $key ] = parameters_explode( $val, 'separator=:' );
    }
  }
  // debug( $ignore, 'ignore' ); 
  // debug( $reset, 'reset' );
  // debug( $prune, 'prune' );

  $refname = $referent.'_id';
  $references = array();
  foreach( $GLOBALS['tables'] as $referer => $t ) {
    $ignore_cols = adefault( $ignore, $referer, array() );
    if( $ignore_cols && ! is_array( $ignore_cols ) ) {
      continue;
    }
    $prune_cols = adefault( $prune, $referer, array() );
    $reset_cols = adefault( $reset, $referer, array() );
    foreach( $GLOBALS['tables'][ $referer ]['cols'] as $col => $props ) {
      if( ( ( $col !== $refname ) || ( $referer === $referent ) ) && ! preg_match( '/_'.$refname.'$/', $col ) ) {
        continue;
      }
      if( adefault( $ignore_cols, $col ) ) {
        continue;
      }
      if( $prune_cols ) {
        if( ( ! isarray( $prune_cols ) ) || adefault( $prune_cols, $col ) ) {
          // debug( "$referer: $col=$referent_id", 'prune' );
          // sql_delete( $referer, "$col=$referent_id" );
          continue;
        }
      }
      if( $reset_cols ) {
        if( ( ! isarray( $reset_cols ) ) || adefault( $reset_cols, $col ) ) {
          // debug( "$referer: $col=$referent_id", 'reset' );
          // sql_update( $referer, "$col=$referent_id", "$col=0" );
          continue;
        }
      }
      $count = sql_query( $referer, array(
        'selects' => 'COUNT'
      , 'filters' => "$col=$referent_id"
      , 'single_field' => 'count'
      ) );
      if( $count > 0 ) {
        $references[ $referer ][ $col ] = $count;
      }
    }
  }
  return $references;
}


function default_query_options( $table, $opts, $defaults = array() ) {
  $default_joins = adefault( $defaults, 'joins', array() );
  return parameters_explode( $opts, array( 'default_key' => 'filters', 'keep' => array(
    'filters' => adefault( $defaults, 'filters', true )
  , 'joins' => $default_joins
  , 'groupby' => $table.'.'.$table.'_id'
  , 'selects' => adefault( $defaults, 'selects', true )
  , 'orderby' => adefault( $defaults, 'orderby' )
  , 'default' => false
  , 'single_field' => false
  , 'single_row' => false
  , 'more_selects' => false
  , 'noexec' => false
  ) ) );
  if( $opts['selects'] === true ) {
    $opts['selects'] = sql_default_selects( $table );
  }
  if( $opts['more_selects'] ) {
    // refuse to merge strings (we _could_ try and handle it but...)
    need( is_array( $opts['selects'] ) && is_array( $opts['more_selects'] ) );
    $opts['selects'] = array_merge( $opts['selects'], $opts['more_selects'] );
  }
  unset( $opts['more_selects'] );
  return $opts;
}


///////////////////////////////////////
//
// functions to access individual tables:
// (many are defaults if no application-specific function provided)
//

///////////////////////
//
// functions to access table `logbook'
//

if( ! function_exists( 'sql_logbook' ) ) {
  function sql_logbook( $filters = array(), $opts = array() ) {
    $opts = default_query_options( 'logbook', $opts, array(
      'joins' => array( 'LEFT sessions USING ( sessions_id )' )
    , 'orderby' => 'logbook.sessions_id,logbook.utc'
    , 'selects' => sql_default_selects( 'logbook,sessions' )
    ) );
    $opts['filters'] = sql_canonicalize_filters(
      'logbook', $filters, $opts['joins'], array()
    , array(
      'flags' => array( '&=', 'logbook.flags' )
    , 'REGEX_tags' => array( '~=', 'logbook.tags' )
    , 'REGEX_note' => array( '~=', 'logbook.note' )
    ) );
    $s = sql_query( 'logbook', $opts );
    return $s;
  }
}

function sql_logentry( $logbook_id, $default = false ) {
  return sql_logbook( $logbook_id, array( 'single_row' => true, 'default' => $default ) );
}

function sql_logbook_max_logbook_id() {
  return sql_logbook( true, 'selects=LAST_ID,single_field=last_id,default=0' );
}

function sql_delete_logbook( $filters ) {
  sql_delete( 'logbook', $filters );
}

function prune_logbook( $maxage = true ) {
  if( $maxage === true )
    $maxage = 60 * 24 * 3600;
  sql_delete_logbook( 'utc < '.datetime_unix2canonical( $GLOBALS['now_unix'] - $maxage ) );
}

///////////////////////
//
// functions to access table `changelog'
//

function sql_delete_changelog( $filters ) {
  $changelog = sql_query( 'changelog', array( 'filters' => $filters ) );
  foreach( $changelog as $c ) {
    $changelog_id = $c['changelog_id'];
    $references = sql_references( 'changelog', $changelog_id, 'reset=changelog:prev_changelog_id' );
    if( $references ) {
      logger(
        'sql_delete_changelog: leaving dangling references: ['.implode( ',', array_keys( $references ) ).']'
      , LOG_LEVEL_WARNING, LOG_FLAG_CODE, 'changelog'
      );
    }
    sql_delete( 'changelog', $changelog_id );
  }
}

function prune_changelog( $maxage = true ) {
  if( $maxage === true )
    $maxage = 60 * 24 * 3600;
  sql_delete_changelog( 'ctime < '.datetime_unix2canonical( $GLOBALS['now_unix'] - $maxage ) );
}

///////////////////////
//
// functions to access table `people' (in particular: for authentication!)
//

if( ! function_exists( 'sql_people' ) ) {
  function sql_people( $filters = array(), $opts = array() ) {
    $opts = default_query_options( 'people', $opts, array( 'orderby' => 'people.cn', 'filters' => $filters ) );
    return sql_query( 'people', $opts );
  }
}

if( ! function_exists( 'sql_person' ) ) {
  function sql_person( $filters, $default = false ) {
    return sql_people( $filters, array( 'default' => $default, 'single_row' => true ) );
  }
}

if( ! function_exists( 'sql_delete_people' ) ) {
  function sql_delete_people( $filters ) {
    sql_delete( 'people', $filters );
  }
}

if( ! function_exists( 'auth_check_password' ) ) {
  function auth_check_password( $people_id, $password ) {
    global $allowed_authentication_methods;
    $allowed = explode( ',', $allowed_authentication_methods );
    // debug( $allowed, 'allowed' );
    if( ! in_array( 'simple', $allowed ) ) {
      // print_on_exit( "<!-- auth_check_password: 2a -->" );
      logger( 'auth_check_password: simple authentication globally disallowed', LOG_LEVEL_WARNING, LOG_FLAG_AUTH, 'auth' );
      return false;
    }
    if( ! $people_id ) {
      logger( 'auth_check_password: no person specified', LOG_LEVEL_WARNING, LOG_FLAG_AUTH | LOG_FLAG_DATA, 'auth' );
      return false;
    }
    if( ! $password ) {
      logger( 'auth_check_password: no password specified', LOG_LEVEL_WARNING, LOG_FLAG_AUTH | LOG_FLAG_DATA, 'auth' );
      return false;
    }
    $person = sql_person( $people_id );
    $auth_methods = explode( ',', $person['authentication_methods'] );
    if( ! in_array( 'simple', $auth_methods ) ) {
      logger( 'auth_check_password: simple authentication disallowed for person', LOG_LEVEL_WARNING, LOG_FLAG_AUTH, 'auth' );
      return false;
    }
    switch( $person['password_hashfunction'] ) {
      case 'crypt':
        $c = crypt( $password, $person['password_salt'] );
        // debug( $c, 'crypt result:' );
        // debug( $person['password_hashvalue'], 'stored hash:' );
        return ( $person['password_hashvalue'] === $c );
      default:
        error( 'unsupported password_hashfunction: ' . $person['password_hashfunction'], LOG_FLAG_CODE | LOG_FLAG_DATA | LOG_FLAG_AUTH, 'auth,password' );
    }
    return false;
  }
}

if( ! function_exists( 'auth_set_password' ) ) {
  function auth_set_password( $people_id, $password ) {
    // debug( $password, 'auth set password:' );
    $person = sql_person( $people_id );
    $auth_methods_string = $person['authentication_methods'];
    $auth_methods = explode( ',', $auth_methods_string );
    if( $password ) {
      $salt = random_hex_string( 8 );
      $hash = crypt( $password, $salt );
      $hashfunction = 'crypt';
      if( ! in_array( 'simple', $auth_methods ) ) {
        $auth_methods[] = 'simple';
      }
    } else {
      $salt = '';
      $hash = '';
      $hashfunction = '';
      foreach( $auth_methods as $key => $val ) {
        if( $val == 'simple' ) {
          unset( $auth_methods[$key] );
        }
      }
    }
    $auth_methods_string = implode( ',', $auth_methods );
    logger( "setting password [$people_id,$hashfunction]", LOG_LEVEL_INFO, LOG_FLAG_AUTH, 'password' );
    return sql_update( 'people', $people_id, array(
      'password_salt' => $salt
    , 'password_hashvalue' => $hash
    , 'password_hashfunction' => $hashfunction
    , 'authentication_methods' => ",$auth_methods_string,"
    ) );
  }
}

/////////////////////
//
// functions handling sessions:
//

function sql_sessions( $filters = array(), $opts = array() ) {
  $joins = array();
  $selects = sql_default_selects('sessions');
  $opts = default_query_options('sessions', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'sessions_id'
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'sessions', $filters, $joins );

  return sql_query( 'sessions', $opts );
}

function sql_one_session( $filters, $default = false ) {
  return sql_sessions( $filters, array( 'default' => $default, 'single_row' => true ) );
}

function sql_delete_sessions( $filters ) {
  global $login_sessions_id;
  $sessions = sql_sessions( $filters );
  foreach( $sessions as $s ) {
    $id = $s['sessions_id'];
    need( (int)$id !== (int)$login_sessions_id );
    sql_delete( 'persistent_vars', "sessions_id=$id" );
    sql_delete( 'transactions', "sessions_id=$id" );
    sql_delete( 'sessions', $id );
  }
}

// prune sessions: will also prune persistent_vars and transactions
//
function prune_sessions( $maxage = true ) {
  global $login_sessions_id;
  if( $maxage === true )
    $maxage = 8 * 24 * 3600;
  sql_delete_sessions( "sessions_id!=$login_sessions_id,atime < ".datetime_unix2canonical( $GLOBALS['now_unix'] - $maxage ) );
}


/////////////////////
//
// functions store and retrieve persistent vars:
//


function sql_store_persistent_vars( $vars, $people_id = 0, $sessions_id = 0, $thread = '', $script = '', $window = '', $self = 0 ) {

  if( $GLOBALS['cookie_support'] !== 'ok' ) // persistent vars will only be useful if cookies are supported
    return;

  $filters = array(
    'sessions_id' => $sessions_id
  , 'people_id'=> $people_id
  , 'thread' => $thread
  , 'script' => $script
  , 'window' => $window
  , 'self' => $self
  );
  if( $window || $self || $script ) {
    sql_delete( 'persistent_vars', $filters );
  }
  foreach( $vars as $name => $value ) {
    if( $value === NULL ) {
      sql_delete( 'persistent_vars', $filters + array( 'name' => $name ) );
    } else {
      if( isarray( $value ) ) {
        $value = json_encode( $value );
        $json = 1;
      } else {
        $json = 0;
      }
      sql_insert( 'persistent_vars'
      , $filters + array( 'name' => $name , 'value' => $value, 'json' => $json )
      , array( 'update_cols' => array( 'value' => true, 'json' => true ) )
      );
    }
  }
}

function sql_persistent_vars( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'name,people_id,sessions_id,thread,script,window';

  $filters = sql_canonicalize_filters( 'persistent_vars', $filters );
    // hints: allow prefix f_ to avoid clash with global variables:
  //  'f_thread' => 'thread', 'f_window' => 'window', 'f_script' => 'script', 'f_sessions_id' => 'sessions_id'
  // ) );
  $selects = sql_default_selects( 'persistent_vars' );
  // $selects[] = '( ISNULL ( SELECT * FROM sessions WHERE sessions.sessions_id = persistent_vars.sessions_id ) ) AS is_dangling ';
  $s = sql_query( 'persistent_vars', array( 'filters' => $filters, 'selects' => $selects, 'orderby' => $orderby ) );
  // debug( $sql, 'sql' );
  return $s;
}

function sql_retrieve_persistent_vars( $people_id = 0, $sessions_id = 0, $thread = '', $script = '', $window = '', $self = 0 ) {
  $filters = array();
  if( $people_id !== NULL )
    $filters['people_id'] = $people_id;
  if( $sessions_id !== NULL )
    $filters['sessions_id'] = $sessions_id;
  if( $thread !== NULL )
    $filters['thread'] = $thread;
  if( $script !== NULL )
    $filters['script'] = $script;
  if( $window !== NULL )
    $filters['window'] = $window;
  if( $self !== NULL )
    $filters['self'] = $self;

  $r = array();
  foreach( sql_persistent_vars( $filters ) as $row ) {
    if( $row['json'] ) {
      $r[ $row['name'] ] = json_decode( $row['value'], true );
    } else {
      $r[ $row['name'] ] = $row['value'];
    }
  }
  // debug( $r, 'persistent vars' );
  return $r;
}

function retrieve_all_persistent_vars() {
  global $jlf_persistent_vars, $login_people_id, $login_sessions_id, $global_format;
  global $script, $parent_script, $parent_window, $parent_thread, $script, $window;

  $jlf_persistent_vars['global']  = sql_retrieve_persistent_vars();
  $jlf_persistent_vars['user']    = sql_retrieve_persistent_vars( $login_people_id );
  $jlf_persistent_vars['session'] = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id );
  $jlf_persistent_vars['thread']  = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id, $parent_thread );
  $jlf_persistent_vars['script']  = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id, $parent_thread, $script );
  $jlf_persistent_vars['window']  = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id, $parent_thread, '',      $window );
  $jlf_persistent_vars['view']    = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id, $parent_thread, $script, $window );

  if( $parent_script === 'self' ) {
    $jlf_persistent_vars['self'] = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id, $parent_thread, $script, $window, 1 );
  } else if( $global_format !== 'html' ) {
    $jlf_persistent_vars['self'] = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id, $parent_thread, $parent_script, $parent_window, 1 );
  } else {
    $jlf_persistent_vars['self'] = array();
  }
  $jlf_persistent_vars['permanent'] = array(); // currently not used
}

function store_all_persistent_vars() {
  global $jlf_persistent_vars, $parent_script, $login_people_id, $login_sessions_id, $thread, $script, $window;

  sql_store_persistent_vars( $jlf_persistent_vars['self'],    $login_people_id, $login_sessions_id, $thread, $script, $window, 1 );
  sql_store_persistent_vars( $jlf_persistent_vars['view'],    $login_people_id, $login_sessions_id, $thread, $script, $window );
  sql_store_persistent_vars( $jlf_persistent_vars['script'],  $login_people_id, $login_sessions_id, $thread, $script );
  sql_store_persistent_vars( $jlf_persistent_vars['window'],  $login_people_id, $login_sessions_id, $thread, '',      $window );
  sql_store_persistent_vars( $jlf_persistent_vars['thread'],  $login_people_id, $login_sessions_id, $thread );
  sql_store_persistent_vars( $jlf_persistent_vars['session'], $login_people_id, $login_sessions_id );
  sql_store_persistent_vars( $jlf_persistent_vars['user'],    $login_people_id );
  sql_store_persistent_vars( $jlf_persistent_vars['global'] );
}

function sql_delete_persistent_vars( $filters ) {
  global $login_people_id;
  $problems = array();
  $vars = sql_persistent_vars( $filters );
  foreach( $vars as $v ) {
    $persistent_vars_id = $v['persistent_vars_id'];
    if( ! have_priv( 'persistent_vars', 'delete', $persistent_vars_id ) ) {
      $problems[] = we( 'insufficient privileges to delete','keine Berechtigung zum Löschen' );
    }
  }
  if( $check ) 
    return $problems;
  need( ! $problems, $problems );
  sql_delete( 'persistent_vars', $filters );
}

////////////////////////////////
//
// handling uids
//

$v2uid_cache = array();
$uid2v_cache = array();

function value2uid( $value, $tag = '' ) {
  global $v2uid_cache, $uid2v_cache;
  if( "$value" === '' ) {
    return 0;
  }
  $value = bin2hex( "$value" ) . '-' . $tag;
  if( isset( $v2uid_cache[ $value ] ) ) {
    $uid = $v2uid_cache[ $value ];
  } else {
    $result = sql_do( "SELECT CONCAT( uids_id, '-', signature ) as uid FROM uids WHERE value='$value'" ); // $value is hex-encoded!
    if( mysql_num_rows( $result ) > 0 ) {
      $row = mysql_fetch_array( $result, MYSQL_ASSOC );
      $uid = $row['uid'];
    } else {
      $signature = random_hex_string( 10 );
      $uids_id = sql_insert( 'uids', array( 'value' => $value, 'signature' => $signature ) );
      $uid = "$uids_id-$signature";
    }
    $v2uid_cache[ $value ] = $uid;
    $uid2v_cache[ $uid ] = $value;
  }
  return $uid;
}

function uid2value( $uid, $tag = '', $default = false ) {
  global $v2uid_cache, $uid2v_cache;

  if( ( $uid === '' ) || ( "$uid" === "0" ) ) {
    return "$uid";
  }
  if( isset( $uid2v_cache[ $uid ] ) ) {
    $value = $uid2v_cache[ $uid ];
  } else {
    need( preg_match( '/^(\d{1,9})-([a-f0-9]{10})$/', $uid, /* & */ $v ), 'malformed uid detected' );
    $result = sql_do( "SELECT value FROM uids WHERE uids_id='{$v[ 1 ]}' AND signature='{$v[ 2 ]}'" );
    if( mysql_num_rows( $result ) > 0 ) {
      $row = mysql_fetch_array( $result, MYSQL_ASSOC );
      $value = $row['value'];
      $v2uid_cache[ $value ] = $uid;
      $uid2v_cache[ $uid ] = $value;
    } else {
      need( $default !== false, 'uid not assigned' );
      return $default;
    }
  }
  $value = explode( '-', $value );
  if( $tag !== false ) {
    need( $value[ 1 ] === $tag, 'invalid uid' );
  }
  return hex_decode( $value[ 0 ] );
}



////////////////////////////////
//
// garbage collection
//

function sql_garbage_collection_generic() {
  prune_sessions();
  prune_logbook();
  prune_changelog();
}

if( ! function_exists( 'sql_garbage_collection' ) ) {
  function sql_garbage_collection() {
    logger( 'start: garbage collection', LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'maintenance' );
    sql_garbage_collection_generic();
    logger( 'finished: garbage collection', LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'maintenance' );
  }
}

?>
