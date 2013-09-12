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
// 
// two types of delete functions:
// - sql_delete_*(): for ordinary deletes caused by user action - should check privileges
// - sql_prune_*(): deletes by garbage collection (should only be called with admin privileges). Will return number of deleted entries.
// both may delete/reset refering entries as appropriate.

$sql_profile = array();

// sql_do(): master function to execute sql query:
//
function sql_do( $sql, $opts = array() ) {
  global $debug, $sql_profile;

  $opts = parameters_explode( $opts );
  $debug_level = adefault( $opts, 'debug_level', LOG_LEVEL_INFO );
  debug( $sql, 'sql query:', $debug_level );

  $start = microtime( true );
  if( ! ( $result = mysql_query( $sql ) ) ) {
    error( "mysql query failed: \n $sql\n mysql error: " . mysql_error(), LOG_FLAG_CODE | LOG_FLAG_DATA, 'sql' );
  }
  $end = microtime( true );
  if( $debug & DEBUG_FLAG_PROFILE ) {
    $sql_profile[] = array(
      'sql' => $sql
    , 'rows_returned' => ( $result === true ? 0 : mysql_num_rows( $result ) )
    , 'wallclock_seconds' => $end - $start
    , 'stack' => debug_backtrace()
    );
  }
  return $result;
}

/////////////////////////////////////////
//
// 0. transactions and locking
//
// with innodb tables, which support transactions and implicit locking (good things) we are unfortunately running into deadlocks, caused by implicit locking
// thus: experimental locking concept:
// we have two classes of scripts: A-scripts and B-scripts, where A-scripts will hopefully evolve into B-scripts over time:
//   A-scripts: not aware of locking (current situation). a write lock on `uids` will be obtained globally and be held during the execution of such a script.
//   B-scripts: are locking-aware and perform fine-grained table locking. a read-lock on `uids` will automatically be appended to every list of requested locks.
// thus,
//   - B-scripts may run concurrently as long as their fine-grained locks permit (multiple read-locks on `uids` are ok), but...
//   - A-scripts can only run serially, and not in parallel to a B-script; this is suboptimal but solves the deadlock problem until proper locking-awareness is implemented
//


// delayed_inserts: we collect write operations to be performed after COMMIT, to be used for
//  - pure appends which are...
//  - not-so-critical for db consistency (so don't need to be part of transaction), in particular...
//  - into "popular" tables
// currently:
// - in A-scripts, we have exclusive access to the db so no delay is required.
// - in B-scripts, writes to some tables will be delayed, unless the script explicitely specifies locking of these tables
//   thus, we don't have to write-lock logbook et al in every transaction just in case (which would force global serialization of virtually all scripts)
//
$delayed_inserts = array();

// sql_transaction_boundary():
//   COMMIT pending transaction and begin a new one.
//   $read_locks, $write_locks: a-arrays of <alias> => <tablename> mappings to indicate the table locks which are required in the new transaction
//   - if the same alias is present in both arrays, only the write lock (which also allows reading) will be obtained.
//   - a request for a read lock on table `uids` will always be appended; this is to make the following work:
//   - special value $read_locks === false will enforce an _implicit_ (by touching the table) write lock on `uids` but issue no explicit LOCK TABLE.
//     this is used to force serialization of scripts which are not locking-aware.
//   $opts:
//   - 'rollback': do not commit but ROLLBACK the pending transaction
//   - 'release': RELEASE db connection
//   the tables `uids` and `logbook` receive special treatment: if no write lock is requested, global $delayed_inserts will be
//   initialized to hold back inserts to these tables; they will be flushed (with explicit locking) at the next transaction boundary.
// 
function sql_transaction_boundary( $read_locks = array(), $write_locks = array(), $opts = array() ) {
  global $delayed_inserts, $utc;

  $opts = parameters_explode( $opts );
  if( adefault( $opts, 'rollback' ) ) {
    sql_do('ROLLBACK');
    $delayed_inserts = array();
  }
  if( $delayed_inserts ) {
    foreach( $delayed_inserts as $table => $values ) {
      if( $values ) {
        sql_do( "LOCK TABLES $table WRITE" );
        foreach( $values as $v ) {
          sql_insert( $table, $v );
        }
      }
    }
    $delayed_inserts = array();
  }
  if( adefault( $opts, 'release' ) ) {
    sql_do( 'COMMIT RELEASE' );
    return;
  }
  sql_do( 'COMMIT' );

  if( $read_locks === false ) { // temporary kludge: touch common semaphore to serialize everything
    sql_update( 'uids', 1,  array( 'signature' => $utc, 'value' => 'semaphore' ) );
    return;
  }

  if( isstring( $read_locks ) ) {
    $read_locks = explode( ',', $read_locks );
  }
  if( isstring( $write_locks ) ) {
    $write_locks = explode( ',', $write_locks );
  }
  $read_locks['uids'] = 'uids';

  $delayed_inserts = array( 'logbook' => array(), 'uids' => array() );
  $comma = '';
  $s = '';
  foreach( $write_locks as $alias => $table ) {
    if( ! $table ) {
      continue;
    }
    if( isnumber( $alias ) ) {
      $alias = $table;
    }
    unset( $delayed_inserts[ $alias ] );
    unset( $read_locks[ $alias ] );
    $s .= "$comma $table as $alias WRITE";
    $comma = ',';
  }
  foreach( $read_locks as $alias => $table ) {
    if( ! $table ) {
      continue;
    }
    if( isnumber( $alias ) ) {
      $alias = $table;
    }
    $s .= "$comma $table as $alias READ";
    $comma = ',';
  }
  sql_do( "LOCK TABLES $s" );
}


///////////////////////////////////////////
// 1. functions to compile query strings:
//

///////////////////////////////////////////////////////
// 1.1. functions to compile an sql filter expression:
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
// return value: n-array( -1 => 'canonical_filter', 0 => <filter_tree>, 1 => <raw_atoms>, 2 => <cooked_atoms> ), where 
// <filter_tree>: tree-representation of the filter expression suitable as input to function sql_filters2expression()
//                the leaf nodes are "atoms" of the form array( -1 => 'raw_atom'|'cooked_atom', 0 => <operator>, 1 => <lhs>, 2 => <rhs> )
//                where 'cooked_atom' means that the expression can be handled by generic code (in particular: only involves "known" variables)
//                'raw_atom' means that table-specific code will be needed to convert this object to a 'cooked_atom'
// <cooked_atoms>: list of references to all 'cooked_atom' leaf nodes
// <raw_atoms>:    list of references to all 'raw_atom' leaf nodes (table-specific code should handle nodes on this list)
//
function sql_canonicalize_filters( $tlist_in, $filters_in, $joins = array(), $selects = array(), $hints = array() ) {

  // this function is idempotent - calling it again on already canonicalized filters is a nop:
  //
  if( adefault( $filters_in, -1, '' ) === 'canonical_filter' ) {
    return $filters_in; // already canonicalized - return as-is
  }

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
  if( $joins ) {
    if( adefault( $joins, -1 ) !== 'canonical_joins' ) {
      $joins = canonicalize_joins( $joins );
    }
    unset( $joins[ -1 ] );
    foreach( $joins as $talias => $j ) {
      $tlist[ $talias ] = $j['tname'];
    }
  }
  $table = reset( $tlist );

  $root = sql_canonicalize_filters_rec( $filters_in );
  // debug( $root, 'sql_canonicalize_filters: raw root' );

  $rv = array( -1 => 'canonical_filter', 0 => $root, 1 => array(), 2 => array() );
  cook_atoms_rec( /* & */ $rv[ 0 ], /* & */ $rv[ 1 ], /* & */ $rv[ 2 ], $hints, $selects, $tlist );

  // debug( $rv, 'sql_canonicalize_filters: cooked rv' );
  return $rv;
}


// cook_atoms_rec(): try to handle leaf nodes in filter expression generically (may leave 'raw_atoms' to be handled by specialzied code):
// keys will be handled like this:
//   a prefix F*_ will always be discarded (can be used freely for disambiguation of cgi parameter names)
//   if <key> is in $hints, the hint will be used: either a string to replace key, or an array to replace the whole node
//   'id' : will map to primary key of first table in $tlist
//   <talias>.<column>: can be handled if <talias> is a table alias in $tlist and <column> one of its columns
//   <column>: can be handled if <columm> is a column of a table in $tlist, or is in $selects
//   <select>: can be handled in most cases (only simple &&-expressions) by a HAVING clause, if <select> is in $selects
function cook_atoms_rec( & $node, & $raw_atoms, & $cooked_atoms, $hints, $selects, $tlist ) {
  global $tables;
  switch( $node[ -1 ] ) {
    case 'filter_list':
      for( $i = 1; isset( $node[ $i ] ); $i++ ) {
        cook_atoms_rec( /* & */ $node[ $i ], /* & */ $raw_atoms, /* & */ $cooked_atoms, $hints, $selects, $tlist );
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
        $table = reset( $tlist );
        $talias = key( $tlist );
        $key = $talias.'.'.$table.'_id';
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

// split_atom(): parse atomic expression ATOMSTRING:
// ATOMSTRING ::= `SQL | KEY [REL VAL]
// SQL is an SQL expression to be used verbatim
// VAL supports two escaping mechanisms:
//   - if VAL starts with :, the remainder will be base64-decoded;
//   - otherwise, any individual octed can be encoded as =XY where XY is lower-case hexadecimal
// REL must be one of '=', '>=', '<=', '!=', '~=', '%=' (sql: LIKE), '&=' ( check: KEY & VAL == VAL )
// KEY and VAL will be trimmed (before unescaping is done on VAL)
// REL and VAL may be absent; REL will default to $default_rel (check for boolean true of KEY), VAL will default to empty string
//
function split_atom( $a, $default_rel = '!0' ) {
  $a = trim( $a );
  if( $a && ( $a[ 0 ] === '`' ) ) {
    return array( -1 => 'cooked_atom', 0 => '!0', 1 => substr( $a, 1 ), 2 => '' );
  }
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
  } else if( ( $n2 = strpos( $a, '~' ) ) > 0 ) {
    $n1 = $n2;
  } else {
    $n1 = $n2 = 0;
  }
  if( $n2 > 0 ) {
    $rel = substr( $a, $n1, $n2 - $n1 + 1 );
    $key = trim( substr( $a, 0, $n1 ) );
    $val = trim( substr( $a, $n2 + 1 ) );
    if( $rel === '~' ) {
      $rel = '~=';
    }
    if( $val && ( $val[ 0 ] == ':' ) ) {
      $val = base64_decode( substr( $val, 1 ) );
      need( check_utf8( $val ), 'rhs of atom: not valid utf-8' );
    } else {
      $val = atom_rhs_unescape( $val );
    }
    return array( -1 => 'raw_atom', 0 => $rel, 1 => $key, 2 => $val );
  } else {
    return array( -1 => 'raw_atom', 0 => $default_rel, 1 => trim( $a ), 2 => '' );
  }
}

// parse_filter_string(): parse FSTRING where
// FSTRING ::= OLDSTYLE | POLISHSTYLE
// OLDSTYLE ::= ATOMSTRING [ , ... ]     (where the , implies boolean "and")
// ATOMSTRING ::= `SQL | KEY [REL VAL]
//   SQL is an SQL expression to be used verbatim
//   VAL supports two escaping mechanisms:
//     - if VAL starts with :, the remainder will be base64-decoded;
//     - otherwise, any individual octed can be encoded as =XY where XY is lower-case hexadecimal
// POLISHSTYLE ::= ( ATOM ) | ( OP POLISHSTYlE [...] )
// posible OPs are &, | and ! as in ldap
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
    // old style: comma-separated list of atoms:
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
//   - FSTRING ::= (see above in function parse_filter_string())
//   - FARRAY ::= FATOM | FLIST | CANONICAL_FILTER
//   - FATOM ::= array( REL, KEY, RHS )
//   - RHS ::= VAL | array( VAL [ , ... ] )
//   - FLIST ::= array( [ OP ,] [ FILTER [ , ... ] ] , [ KEY [REL] => VAL [ , ... ] ] )
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
          need( count( $f ) === 1, 'NOT requires exactly one operand' );
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
// 1.2. functions to compile selection clauses
//

// sql_default_selects():
// return SELECT clauses for all colums in all given tables:
//  $tnames may be
//    - <table_name> or list of table names
//    - array of <table_alias> => <table_name> | <table_options>
//    - table options may contain
//       'table' => <table_name>
//       'prefix' => <prefix> to prepend to this tables column names (for disambiguation)
//       'aprefix' => <prefix> like prefix, but only prepend to ambiguous columns
//       'aprefix' => '' special case: skip ambigous columns
//       '.<column>' => <identifier> special rule for <column> (prefixes do not apply)
//       '.<column>' => FALSE (or '') to skip this column
//
function sql_default_selects( $tnames ) {
  global $tables;

  $selects = array();
  if( isstring( $tnames ) ) {
    $tnames = parameters_explode( $tnames );
  }
  foreach( $tnames as $talias => $topts ) {
    if( $topts == 1 ) {
      $tname = $talias;
      $topts = array();
    } else {
      $topts = parameters_explode( $topts, 'default_key=table' );
      $tname = adefault( $topts, 'table', $talias );
      if( is_numeric( $talias ) ) {
        $talias = $tname;
      }
    }
    need( adefault( $tables, $tname ), 'no such table' );
    $t = $tables[ $tname ];
    $cols = $t['cols'];
    $prefix = adefault( $topts, 'prefix', '' );
    if( $prefix && isnumber( $prefix ) ) {
      $prefix = $talias.'_';
    }
    $aprefix = adefault( $topts, 'aprefix', false );
    if( ! $prefix ) {
      $cols += adefault( $t, 'more_selects', array() );
    }
    foreach( $cols as $name => $type ) {
      $rule = ( is_array( $type ) ? "$talias.$name" : str_replace( "`%`", "`$talias`", $type ) );
      if( ( $crule = adefault( $topts, ".$name", NULL ) ) !== NULL ) {
        if( ! $crule )
          continue;
        else
          $calias = $crule;
      } else {
        $calias = "$prefix$name";
        if( isset( $selects[ $calias ] ) && ( $aprefix !== false ) ) {
          if( ! $aprefix )
            continue
          $calias = "$aprefix$name";
        }
      }
      need( ! isset( $selects[ $calias ] ), "ambiguous: [$calias]" );
      $selects[ $calias ] = $rule;
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


//////////////////////
// 1.3. functions to compile JOIN clauses
//

function canonicalize_joins( $joins = array(), $using = array() ) {
  global $tables;
  if( adefault( $joins, -1 ) === 'canonical_joins' ) {
    return $joins;
  }
  $using = parameters_explode( $using );
  foreach( $using as $key => $val ) {
    if( is_numeric( $val ) ) {
      $using[ $key ] = $key;
    }
  }
  $joins = parameters_explode( $joins );

  $rv = array( -1 => 'canonical_joins' );
  foreach( $joins as $key => $val ) {
    $j = array();
    if ( is_numeric( $val ) ) {
      $rule = $key;
      $talias = false;
    } else {
      $rule = $val;
      $talias = $key;
    }
    preg_match( '/^(LEFT |OUTER )? *([^ ]+) *([^ ].*)?$/', $rule, /* & */ $matches );
    $tname = $matches[ 2 ];
    if( adefault( $using, $talias ) === $tname ) {
      continue;
    }
    $rv[ $talias ? $talias : $tname ] = array(
      'type' => adefault( $matches, 1 , '' )
    , 'tname' => $tname
    , 'rule' => adefault( $matches, 3, "USING ( {$tname}_id ) " )
    );
  }
  return $rv;
}

function joins2expression( $joins ) {
  need( $joins[-1] === 'canonical_joins' );
  unset( $joins[ -1 ] );
  $sql = '';
  foreach( $joins as $talias => $j ) {
    $tname = $j['tname'];
    $sql .= "{$j['type']} JOIN `$tname` ";
    if( $talias !== $tname ) {
      $sql .= "AS `$talias` ";
    }
    $sql .= "{$j['rule']} ";
  }
  return $sql;
}


//////////////////////
// 1.4. functions to compile and execute a sql SELECT statement
//

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
    $n = 1;
    while( $row = mysql_fetch_array( $result, MYSQL_ASSOC ) ) {
      $row['nr'] = $n++;
      $r[] = $row;
    }
  }
  return $r;
}


// sql_query(): compose sql SELECT query from parts:
//
function sql_query( $table_name, $opts = array() ) {
  global $debug_requests;

  $opts = parameters_explode( $opts, 'filters' );

  $debug = adefault( $debug_requests['cooked'], 'sql_query' );
  if( isarray( $debug ) ) {
    if( ( $op = adefault( $debug, $table_name ) ) !== false ) {
      $debug = 1;
    } else {
      $debug = 0;
    }
  }

  $table_alias = adefault( $opts, 'table_alias', $table_name );
  $filters = adefault( $opts, 'filters', false );
  $selects = adefault( $opts, 'selects', true );
  if( $selects === true ) {
    $selects = sql_default_selects( $table_name );
  };
  $joins = adefault( $opts, 'joins', array() );
  $having = adefault( $opts, 'having', false );
  $orderby = adefault( $opts, 'orderby', false );
  $limit_from = adefault( $opts, 'limit_from', 0 );
  $limit_count = adefault( $opts, 'limit_count', 0 );
  $single_row = ( isset( $opts['single_row'] ) ? $opts['single_row'] : '' );
  $single_field = ( isset( $opts['single_field'] ) ? $opts['single_field'] : '' );

  if( ( $distinct = ( isset( $opts['distinct'] ) ? $opts['distinct'] : '' ) ) ) {
    $selects = "DISTINCT $distinct";
    $key_col = true;
    $val_col = $distinct;
  } else {
    $key_col = adefault( $opts, 'key_col' );
    $val_col = adefault( $opts, 'val_col' );
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
    $comma = ',';
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
  $joins = canonicalize_joins( $joins );
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
      $select_string = "MAX( {$table_alias}.{$table_name}_id ) AS last_id";
      $groupby = false;
      break;
    default:
      $groupby = adefault( $opts, 'groupby', false );
      if( $groupby ) {
        // default_query_options() will group by "{$table_alias}.{$table_name}_id", so by default, you get a free count:
        $select_string .= "$comma COUNT(*) AS count";
      }
      break;
  }
  $query = "SELECT $select_string FROM $table_name AS $table_alias $join_string";

  $having_clause = '';
  if( $filters !== false ) {
    $cf = sql_canonicalize_filters( array( $table_alias => $table_name ), $filters, $joins, is_array( $selects ) ? $selects : array() );
    if( $debug ) {
      debug( $cf, "sql_query() [$table_name]: canonical filters" );
    }
    list( $where_clause, $having_clause ) = sql_filters2expressions( $cf );
    $query .= ( " WHERE " . $where_clause );
  }
  if( $groupby ) {
    if( $groupby === '*' ) {
      $groupby = "'1'";  // mysql seems to interpret constant _strings_ as "group all rows into one"
    }
    $query .= " GROUP BY $groupby ";
  }
  if( $having !== false ) {
    $cf = sql_canonicalize_filters( array( $table_alias => $table_name ), $having );
    if( $debug ) {
      debug( $cf, "sql_query() [$table_name]: canonical HAVING" );
    }
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
    debug( $query, "sql_query() [$table_name]: query" );
  }
  if( adefault( $opts, 'noexec' ) ) {
    return $query;
  }
  $result = sql_do( $query );
  if( $debug ) {
    debug( mysql_num_rows( $result ), "sql_query() [$table_name]: number of rows" );
  }
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
  return mysql2array( $result, $key_col, $val_col );
}


// default_query_options(): mostly to make sure options are set at all to some sensible value
// (so we don't need adefault() or isset() checks for every access)
// logic:
// - most defaults are hardcoded except joins, selects, orderby, filtes where table-specific defaults can be passed
// - $opts is user-defined a-arrya to override the defaults
// - special options 'more_selects' and 'more_joins' will not override but append to defaults
//
function default_query_options( $table, $opts, $defaults = array() ) {
  $opts = parameters_explode( $opts, array( 'default_key' => 'filters', 'keep' => array(
    'filters' => adefault( $defaults, 'filters', true )
  , 'joins' => adefault( $defaults, 'joins', array() )
  , 'groupby' => $table.'.'.$table.'_id'
  , 'selects' => adefault( $defaults, 'selects', true )
  , 'orderby' => adefault( $defaults, 'orderby' )
  , 'default' => false
  , 'single_field' => false
  , 'single_row' => false
  , 'distinct' => false
  , 'more_selects' => false
  , 'more_joins' => false
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
  if( $opts['more_joins'] ) {
    $opts['joins'] = array_merge( $opts['joins'], $opts['more_joins'] );
  }
  unset( $opts['more_joins'] );
  return $opts;
}


/////////////////////////////////////////////////////
// 1.5. functions to compile and execute other (not SELECT) sql statements
//


function sql_delete( $table, $filters, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $joins = adefault( $opts, 'joins', array() );
  $join_string = joins2expression( canonicalize_joins( $joins ) );
  $cf = sql_canonicalize_filters( $table, $filters, $joins );
  list( $where_clause, $having_clause ) = sql_filters2expressions( $cf );
  need( ! $having_clause, 'cannot use HAVING in DELETE statement' );
  
  $query = "DELETE FROM $table ";
  if( $join_string ) {
    $query .= "USING $table $join_string ";
  }
  $query .= "WHERE $where_clause ";
  sql_do( $query, "failed to delete from $table:" );
  return mysql_affected_rows();
}

function init_rv_delete_action( $rv = false ) {
  return $rv ? $rv : array( 'problems' => array() , 'deleted' => 0 , 'deletable' => 0 );
}

// sql_handle_delete_action():
// generic helper for sql_*_delete() to support actions 'dryrun', 'soft', 'hard':
//   'dryrun': dont actually delete anything, just check how much can be deleted;
//   'soft': delete if possible
//   'hard': delete or abort
//   with START TRANSACTION and ROLLBACK, 'hard' can be safer than 'soft' in cases where 'soft' may cause db inconsistencies!
// options:
//   'logical': instead of actual delete, set 'flag_deleted' => 1
//   'quick': skip tests whether entry exists and, with 'logical', whether it is not yet marked as deleted
// 
function sql_handle_delete_action( $table, $id, $action, $problems, $rv = false, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $quick = adefault( $opts, 'quick' );
  $logical = adefault( $opts, 'logical' );
  $log_prefix = "sql_handle_delete_action(): ".( $logical ? 'logical' : 'physical' ) ." delete: $table/$id: ";
  $log = adefault( $opts, 'log' );
  if( ! $rv ) {
    $rv = init_rv_delete_action();
  }
  if( ! $quick ) {
    if( ! ( $row = sql_query( $table, array( 'filters' => "$id", 'single_row' => '1' ) ) ) ) {
      $problems += new_problem( "$log_prefix no such entry" );
    } else if( $logical ) {
      if( adefault( $row, 'flag_deleted' ) ) {
        $problems += new_problem( "$log_prefix already marked as deleted" );
      }
    }
  }
  if( $problems ) {
    $rv['problems'] += $problems;
  } else {
    $rv['deletable'] += 1;
  }
  switch( $action ) {
    case 'dryrun':
      return $rv;
    case 'hard':
      if( $problems ) {
        error( $log_prefix . reset( $problems ), LOG_FLAG_DATA | LOG_FLAG_DELETE | LOG_FLAG_ABORT, $table );
      }
    case 'soft':
      if( ! $problems ) {
        $n = ( $logical ? sql_update( $table, $id, 'flag_deleted=1' ) : sql_delete( $table, $id ) );
        $rv['deleted'] += $n;
        if( $n ) {
          if( $log ) {
            logger( "$log_prefix deleted", LOG_LEVEL_INFO, LOG_FLAG_SYSTEM | LOG_FLAG_DELETE, $table );
          }
          // do not delete the changelog - its there to record history after all!
          // if( ! $logical ) {
          //   sql_delete_changelog( "tname=$table,tkey=$id", 'quick=1,action=soft' );
          // }
        } else {
          if( ! ( $logical && $quick ) ) {
            logger( "$log_prefix 0 rows affected", LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM | LOG_FLAG_DELETE, $table );
          }
        }
      }
      return $rv;
    default:
      error( "$log_prefix unsupported action requested", LOG_FLAG_CODE | LOG_FLAG_DELETE, $table );
  }
}

// sql_delete_generic()
// delete handler for tables which do not require more specialized treatment,
// but where privileges and references should be obeyed when deleting.
//
function sql_delete_generic( $table, $filters, $opts = array() ) {
  $opts = parameters_explode( $opts ) ;
  $action = adefault( $opts, 'action', 'hard' );
  $log = adefault( $opts, 'log' );
  $quick = adefault( $opts, 'quick' );
  $logical = adefault( $opts, 'logical' );
  $handler = "sql_$table";
  if( function_exists( $handler ) ) {
    $rows = $handler( $filters );
  } else {
    $rows = sql_query( $table, array( 'filters' => $filters ) );
  }
  $rv = init_rv_delete_action( adefault( $opts, 'rv' ) );
  foreach( $rows as $r ) {
    $id = $r[ $table.'_id' ];
    $problems = priv_problems( $table, 'delete', $r );
    if( ( ! $problems ) && ( ! $logical ) ) {
      $problems = sql_references( $table, $id, array(
        'return' => 'report'
      , 'delete_action' => $action
      , 'ignore' => adefault( $opts, 'ignore', '' )
      , 'reset' => adefault( $opts, 'reset', '' ) 
      , 'prune' => adefault( $opts, 'prune', '' )
      ) );
    }
    $rv = sql_handle_delete_action( $table, $id, $action, $problems, $rv, array( 'log' => $log, 'logical' => $logical, 'quick' => $quick ) );
  }
  return $rv;
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
    'tname' => $table
  , 'tkey' => $id
  , 'prev_changelog_id' => $current['changelog_id']
  , 'payload' => json_encode( $current )
  ) );
}

// sql_update()
// update all entries in $table matching $filters
// if $filters is a number, it is assumed to be a primary key and must match one entry in $table.
// otherwise, it is not an error if $filters have zero matches.
//
function sql_update( $table, $filters, $values, $opts = array() ) {
  global $tables, $utc, $login_sessions_id, $debug_requests;

  $opts = parameters_explode( $opts );
  if( ( $table !== 'changelog' ) && isset( $tables[ $table ]['cols']['changelog_id'] ) ) {
    $changelog = adefault( $opts, 'changelog', true );
  } else {
    $changelog = false;
  }
  $debug = adefault( 'debug_requests', 'sql_update' );

  if( $debug && isnumber( $filters ) ) {
    need( ( $filters >= 1 ) && sql_query( $table, "$filters,single_field=COUNT" ) , 'sql_update(): no such entry' );
  }

  $values = parameters_explode( $values );
  if( isset( $tables[ $table ]['cols']['mtime'] ) ) {
    $values['mtime'] = $utc;
  }
  if( isset( $tables[ $table ]['cols']['modifier_sessions_id'] ) ) {
    $values['modifier_sessions_id'] = $login_sessions_id;
  }
  unset( $values[ "{$table}_id" ] );
  if( $changelog ) {
    if( is_numeric( $filters ) ) {
      $values['changelog_id'] = copy_to_changelog( $table, $filters );
    } else {
      // serialize it:
      $matches = sql_query( $table, array( 'filters' => $filters, 'selects' => "$table.{$table}_id" ) );
      $rv = true;
      foreach( $matches as $row ) {
        sql_update( $table, $row[ $table.'_id' ], $values, $opts );
      }
      return count( $matches );
    }
  }
  list( $where_clause, $having_clause ) = sql_filters2expressions( sql_canonicalize_filters( $table, $filters ) );
  need( ! $having_clause, 'cannot use HAVING in UPDATE statement' );
  $sql = "UPDATE $table SET";
  $comma='';
  foreach( $values as $key => $val ) {
    $val = "'" . mysql_real_escape_string($val) . "'";
    $sql .= "$comma $key=$val";
    $comma=',';
  }
  $sql .= ( " WHERE " . $where_clause );

  sql_do( $sql, "failed to update table $table: " );
  return mysql_affected_rows();
}

function sql_insert( $table, $values, $opts = array() ) {
  global $tables, $utc, $login_sessions_id, $login_people_id;

  $opts = parameters_explode( $opts );
  $update_cols = adefault( $opts, 'update_cols', false );

  if( ( $table !== 'changelog' ) && isset( $tables[ $table ]['cols']['changelog_id'] ) ) {
    $changelog = adefault( $opts, 'changelog', true );
  } else {
    $changelog = false;
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
  if( strpos( adefault( $tables[ $table ]['cols'][ "{$table}_id" ], 'extra', '' ), 'auto_increment' ) !== false ) {
    unset( $values[ "{$table}_id" ] );
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
    $val = "'" . mysql_real_escape_string($val) . "'";

    $vals .= "$comma $val";
    if( is_array( $update_cols ) ) {
      if( isset( $update_cols[$key] ) ) {
        if( $update_cols[$key] !== true ) {
          $val = $update_cols[$key];
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
    if( isset( $tables[ $table ][ 'cols' ][ $table.'_id' ] ) ) {
      // a strange kludge required to cause mysql_insert_id (see below) to be set in case of update:
      $sql .= "$update_comma {$table}_id = LAST_INSERT_ID( {$table}_id ) ";
    }
  }
  if( sql_do( $sql, "failed to insert into table $table: " ) ) {
    return mysql_insert_id();
  } else {
    return FALSE;
  }
}

// validate_row(): basic check before insert/update: check $values for compliance with column types in $table
// more subtle checks should be done in in sql_*_save()
// options:
// - 'action': 'soft', 'dryrun': just return problems; 'hard': fail hard if problem detected
// - 'update': just check the values passed; default: also validate default values for columns where no value is passed
//             if update is numeric, it should be the primary key to be updated, which will be checked for existence
//
function validate_row( $table, $values, $opts = array() ) {
  $cols = $GLOBALS['tables'][ $table ]['cols'];
  $opts = parameters_explode( $opts );
  $update = adefault( $opts, 'update' );
  $action = adefault( $opts, 'action', 'hard' );
  $check = ( ( $action == 'dryrun' ) || ( $action == 'soft' ) );
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
          $problems[ $name ] = "$name: illegal value specified";
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
            $problems[ $name ] = "$name: no value passed, and default is not a legal value";
          } else {
            error( "validate_row: default not a legal value for: [$name]", LOG_FLAG_CODE | LOG_FLAG_ABORT, 'validate_row' ); 
          }
        }
      }
    }
  }
  if( $update && isnumber( $update ) ) {
    if( ! sql_query( $table, "$update,single_field={$table}_id,default=0" ) ) {
      $problems += new_problem("update $table/$update: no such entry");
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


///////////////////////////////////////////////////////
// 1.6. functions to perform more complex but generic sql operations
//


// sql_references():
// find references pointing to entry $referent_id in table $referent
// $referent_id must be a numeric primary key, or (when requesting 'filters' or 'selects') a column specification <table>.<table>_id
// - references are any fields in any table, whose column name matches {$referent}_id" or *_{$referent}_id and whose value is $referent_id
// - supportet $opts:
//   'ignore': references in these columns are ignored; supported formats:
//     'ignore=<table1>[:col1:col2:...] <table2>...'
//     'ignore' => '<table1>[:col1:col2:...] <table2>...'
//     'ignore' => array( '<table1>', 'table2' => 'col1:col2:...' )
//     'ignore' => array( '<table1>' => array( 'col1', 'col2', ... ) )
//   * if no columns are specified for a table, all columns in that <table> are ignored
//   * the primary key field $referent.{$refefent}_id, ie the self-pointer, will always be ignored.
//   * instead of a column, a numeric primary key may be specified to indicate that references in this record are to be ignored
//     (useful to ignore references to self (other than by primary key) in a record to be deleted)
//   'prune': table entries with references in these colums will be deleted; formats like 'ignore', except primary keys not supported
//   'reset': references in these columns will be reset to 0; formats like 'ignore', except primary keys not supported
//   'force': prune entries even if they are referenced by other entries, possibly creating dandling links
//            (by default, prune refuses to delete entries if that leaves dangling links)
//   'return': what to return if unhandled references are found
//      'references' (default): 
//         returns a 3-level a-array with entries of the form
//         'refering table' => 'refering col' => <id> => <id>
//         only non-zero counts, and only 'refering tables' with at least one 'refering col' will be returned.
//         only references not handled by 'ignore', 'reset' or 'prune' will be counted.
//         if no unhandled references are found, an empty array is returned.
//      'report': return array of human-readable strings; if no unhandled references are found an empty array is returned
//      'filters': return array of type 'canonical_filter' which evaluates to true if unhandled references exist
//      'selects': return array of $selects to be passed to sql_query(); they will SELECT space-separated lists of
//                 primary keys stored in columns named REFERENCES_<table>_<col>
//      'abort': dont return but abort with error if references are found
//      if no unhandled references are found, 'references', 'report' and 'abort' will return an empty array()
//   'delete_action': as in handle_delete_action(), default is 'hard'. with delete_action 'dryrun', 'prune' and 'reset' are treated like 'ignore'
//   'prefix': change default message (see below) used in 'report' and 'abort'
//
function sql_references( $referent, $referent_id, $opts = array() ) {
  $opts = parameters_explode( $opts );

  $ignore = adefault( $opts, 'ignore', array() );
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

  $prune = adefault( $opts, 'prune', array() );
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

  $reset = adefault( $opts, 'reset', array() );
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

  $force = adefault( $opts, 'force' );
  $refname = $referent.'_id';

  $rv = array();
  $return = adefault( $opts, 'return', 'references' );
  $delete_action = adefault( $opts, 'delete_action', 'hard' );
  switch( $delete_action ) {
    case 'dryrun':
      $ignore = parameters_merge( $ignore, $prune );
      $ignore = parameters_merge( $ignore, $reset );
      $prune = array();
      $reset = array();
      break;
    case 'hard':
    case 'soft':
      break;
    default:
      error( 'sql_references(): undefined value for option `delete_action` ', LOG_FLAG_CODE, 'references' );
  }
  switch( $return ) {
    case 'filters':
      $rv = array( -1 => 'filter_list', 0 => '||' );
    case 'selects':
      need( ( ! $prune ) && ( ! $reset ), 'sql_references(): prune and reset not supported when returning filters or selects' );
    case 'abort':
    case 'report':
    case 'references':
      break;
    default:
      error( 'sql_references(): undefined value for option `return` ', LOG_FLAG_CODE, 'references' );
  }
  foreach( $GLOBALS['tables'] as $referer => $t ) {
    $ignore_cols = adefault( $ignore, $referer, array() );
    if( $ignore_cols && ! is_array( $ignore_cols ) ) {
      continue;
    }
    $prune_cols = adefault( $prune, $referer, array() );
    $reset_cols = adefault( $reset, $referer, array() );
    foreach( $GLOBALS['tables'][ $referer ]['cols'] as $col => $props ) {
      $is_candidate = preg_match( '/_'.$refname.'$/', $col );
      if( ! $is_candidate ) {
        if( ( $referer !== $referent ) && ( $col === $refname ) ) {
          $is_candidate = true;
        }
      }
      if( ! $is_candidate ) {
        continue;
      }
      if( adefault( $ignore_cols, $col ) ) {
        continue;
      }
      if( $prune_cols ) {
        if( ( ! isarray( $prune_cols ) ) || adefault( $prune_cols, $col ) ) {
          // debug( "$referer: $col=$referent_id", 'prune' );
          if( $force ) {
            $count = sql_delete( $referer, "$col=$referent_id" );
          } else {
            $refs = sql_query( $referer, array( 'filters' => "$col=$referent_id", 'select' => $referer.'_id' ) );
            $count = 0;
            foreach( $refs as $row ) {
              $id = $row[ $referer.'_id' ];
              need( ! sql_references( $referer, $id ), "sql_references(): cannot prune table $referer/$id: references exist" );
              $count += sql_delete( $referer, $id );
            }
          }
          continue;
        }
      }
      if( $reset_cols ) {
        if( ( ! isarray( $reset_cols ) ) || adefault( $reset_cols, $col ) ) {
          // debug( "$referer: $col=$referent_id", 'reset' );
          $count = sql_update( $referer, "$col=$referent_id", "$col=0", 'changelog=0' );
          if( $count ) {
            logger( "sql_references: reset: [$referer:$col=$referent_id]: $count references reset", LOG_LEVEL_DEBUG, LOG_FLAG_DELETE, 'references' );
          }
          continue;
        }
      }
      $ignorelist = array();
      foreach( $ignore_cols as $c => $dummy ) {
        if( isnumber( $c ) ) {
          $ignorelist[] = $c;
        }
      }
      $referer_alias = "TMP_$referer"; // need disambiguation in sql in case $referer == $referent
      $filters = array( '&&', array( -1 => 'cooked_atom', 0 => '!0', 1 => "{$referer_alias}.{$col} = $referent_id", 2 => '' ) );
      if( $ignorelist ) {
        $filters[] = array( '!', "{$referer_alias}.{$referer}_id" => $ignorelist );
      }
      switch( $return ) {
        case 'filters':
          $sql = sql_query( $referer, array( 'noexec' => 1, 'table_alias' => $referer_alias, 'selects' => 'COUNT(*)', 'filters' => $filters ) );
          $rv[] = array( -1 => 'cooked_atom', 0 => '!0', 1 => $sql, 2 => '' );
          break;
        case 'selects':
          $sql = sql_query( $referer, array( 'noexec' => 1, 'table_alias' => $referer_alias, 'selects' => "GROUP_CONCAT( {$referer_alias}.{$referer}_id SEPARATOR ' ' )", 'filters' => $filters ) );
          $rv[] = "( $sql ) AS REFERENCES_{$referer}_{$col}";
          break;
        case 'abort':
        case 'report':
        case 'references':
          foreach( sql_query( $referer, array( 'table_alias' => $referer_alias, 'selects' => "{$referer_alias}.{$referer}_id AS {$referer}_id", 'filters' => $filters ) ) as $r ) {
            $id = $r[ "{$referer}_id" ];
            $rv[ $referer ][ $col ][ $id ] = $id;
          }
      }
    }
  }
  $prefix = adefault( $opts, 'prefix', we('cannot delete: references exist','Löschen nicht möglich: Verweise vorhanden') );
  switch( $return ) {
    case 'abort':
      if( $rv ) {
        logger( "sql_references(): aborting on existing references to [$referent/$referent_id] : " . implode( ', ', array_keys( $rv ) ), LOG_LEVEL_ERROR, 'references' );
        error( $prefix, LOG_FLAG_DATA, 'references' );
      }
    case 'report':
      if( $rv ) {
        if( have_priv('*','*') ) {
          $prefix .= ( ': '. implode( ', ', array_keys( $rv ) ) );
        }
        return new_problem( $prefix );
      }
    case 'filters':
    case 'selects':
    case 'references':
      return $rv;
  }
}

// sql_dangling_links()
// supported options:
//   'tables': n-array or space-separated list of tables to search; default: all tables
//   'columns': n-array or space-separated list of column names to search; default: all pointer columns
//   'filters': additional filters to narrow search
// returns array( <refering_table> => array( <refering_col> => array( <refering_id> => <referent_id>, ... ), ... ), ... )
//
function sql_dangling_links( $opts = array() ) {
  global $tables;

  $opts = parameters_explode( $opts );
  $tnames = adefault( $opts, 'tables', array_keys( $tables ) );
  $tnames = parameters_explode( $tnames );
  $cnames = adefault( $opts, 'columns' );
  $cnames = parameters_explode( $cnames );
  $more_filters = adefault( $opts, 'filters', true );
  $dangling_links = array();
  foreach( $tnames as $refering_table => $dummy ) {
    $cols = $tables[ $refering_table ]['cols'];
    foreach( $cols as $refering_col => $props ) {
      if( $cnames && ! adefault( $cnames, $refering_col ) ) {
        continue;
      }
      if( preg_match( '/^([a-zA-Z0-9_]*_)?([a-zA-Z0-9]+)_id$/', $refering_col, /* & */ $v ) ) {
        $referent = $v[ 2 ];
        if( ! isset( $tables[ $referent ] ) ) {
          continue;
        }
        $dangling_links[ $refering_table ][ $refering_col ] = sql_query( $refering_table, array(
          'joins' => array( 'referent' => "LEFT $referent ON referent.{$referent}_id = $refering_table.$refering_col" )
        , 'filters' => array( '&&', "`$refering_table.$refering_col", "`ISNULL( referent.{$referent}_id )", $more_filters )
        , 'selects' => array( "$refering_col" => "$refering_table.$refering_col", "{$refering_table}_id" => "$refering_table.{$refering_table}_id" )
        , 'key_col' => "{$refering_table}_id"
        , 'val_col' => "$refering_col"
        ) );
      }
    }
  }
  return $dangling_links;
}

function sql_reset_dangling_links( $refering_table, $refering_col, $refering_id = 0 ) {
  $dangling_links = sql_dangling_links( array(
    'tables' => $refering_table
  , 'columns' => $refering_col
  , 'filters' => ( $refering_id ? $refering_id : true )
  ) );
  $count = 0;
  $dangling_links = $dangling_links[ $refering_table ][ $refering_col ];
  foreach( $dangling_links as $refering_id => $referent_id ) {
    sql_update( $refering_table, $refering_id, "$refering_col=0" );
  }
  $count = count( $dangling_links );
  logger( "reset dangling links: $count dangling links grounded [$refering_table / $refering_col / $refering_id]", LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'maintenance' );
  return $count;
}


///////////////////////////////////////////
// 2. functions operating on particular tables
//

///////////////////////////////////////
//
// 2.1. functions operating on "ANY" table
//      (must be used with care; should be reserved for admin maintenance use)
//

function sql_delete_entry( $table, $id, $opts = array() ) {
  need_priv('*','*');
  need( $table );
  need( $id );
  logger( "manually deleting entry: [$table / $id]", LOG_LEVEL_WARNING | LOG_FLAG_DELETE, 'maintenance' );
  return sql_delete( $table, $id );
}


///////////////////////
// 2.2. functions to access table `logbook'
//

if( ! function_exists( 'sql_logbook' ) ) {
  function sql_logbook( $filters = array(), $opts = array() ) {
    need_priv('*','*');
    $opts = default_query_options( 'logbook', $opts, array(
      'joins' => array( 'LEFT sessions' )
    , 'orderby' => 'logbook.sessions_id,logbook.utc'
    , 'selects' => sql_default_selects( 'logbook,sessions=aprefix=' )
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

function sql_delete_logbook( $filters, $opts = array() ) {
  return sql_delete_generic( 'logbook', $filters, $opts );
//   need_priv( 'logbook', 'delete' );
//   $rows = sql_logbook( $filters );
//   $opts = parameters_explode( $opts );
//   $action = adefault( $opts, 'action', 'hard' );
//   $rv = init_rv_delete_action( adefault( $opts, 'rv' ) );
//   foreach( $rows as $r ) {
//     $id = $r['logbook_id'];
//     $problems = sql_references( 'logbook', $id, 'return=report' );
//     $rv = sql_handle_delete_action( 'logbook', $id, $action, $problems, $rv );
//   }
//   return $rv;
}

function sql_prune_logbook( $opts = array() ) {
  global $now_unix, $info_messages;
  $opts = parameters_explode( $opts );
  $maxage_seconds = adefault( $opts, 'maxage_seconds', 60 * 24 * 3600 );
  $action = adefault( $opts, 'action', 'soft' );

  $rv = sql_delete_logbook( 'utc < '.datetime_unix2canonical( $now_unix - $maxage_seconds ), "action=$action,quick=1" );
  if( ( $count = $rv['deleted'] ) ) {
    $info_messages[] = "sql_prune_logbook(): $count logbook entries deleted";
    logger( "sql_prune_logbook(): $count logbook entries deleted", LOG_LEVEL_INFO, LOG_FLAG_SYSTEM | LOG_FLAG_DELETE, 'maintenance' );
  }
  return $rv;
}

///////////////////////
// 2.3. functions to access table `changelog'
//

function sql_changelog( $filters = array(), $opts = array() ) {
  need_priv('*','*');
  $opts = default_query_options( 'changelog', $opts, array(
    'selects' => sql_default_selects( 'changelog' )
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'changelog', $filters );
  $s = sql_query( 'changelog', $opts );
  return $s;
}

function sql_delete_changelog( $filters, $opts = array() ) {
  need_priv( 'changelog', 'delete' );
  $opts = parameters_explode( $opts );
  $rows = sql_query( 'changelog', array( 'filters' => $filters, 'joins' => adefault( $opts, 'joins' ) ) );
  $action = adefault( $opts, 'action', 'hard' );
  $rv = init_rv_delete_action( adefault( $opts, 'rv' ) );
  foreach( $rows as $r ) {
    $id = $r['changelog_id'];
    $table = $r['tname'];
    $problems = sql_references( 'changelog', $id, "return=report,delete_action=$action,reset=changelog:prev_changelog_id $table:changelog_id" );
    $rv = sql_handle_delete_action( 'changelog', $id, $action, $problems, $rv );
  }
  return $rv;
}

function sql_prune_changelog( $opts = array() ) {
  global $now_unix, $info_messages, $tables;

  $opts = parameters_explode( $opts );
  $maxage_seconds = adefault( $opts, 'maxage_seconds', 60 * 24 * 3600 );
  $action = adefault( $opts, 'action', 'soft' );

  $rv = sql_delete_changelog( 'ctime < '.datetime_unix2canonical( $now_unix - $maxage_seconds ), "action=$action,quick=1" );
  foreach( $tables as $tname => $props ) {
    if( $tname === 'changelog' ) {
      continue;
    }
    if( ! isset( $props['cols']['changelog_id'] ) ) {
      continue;
    }
    $rv = sql_delete_changelog( "`$tname.{$tname}_id IS NULL" , array(
      'joins' => "LEFT $tname USING ( changelog_id )"
    , 'action' => $action
    , 'quick' => 1
    , 'rv' => $rv
    ) );
  }
  if( ( $count = $rv['deleted'] ) ) {
    $info_messages[] = "sql_prune_changelog(): $count changelog entries deleted";
    logger( "sql_prune_changelog(): $count changelog entries deleted", LOG_LEVEL_INFO, LOG_FLAG_SYSTEM | LOG_FLAG_DELETE, 'maintenance' );
  }
  return $count;
}

///////////////////////
// 2.4. functions to access table `people'
// they are always required for authentication, but subprojects may provide their own versions

if( ! function_exists( 'sql_people' ) ) {
  function sql_people( $filters = array(), $opts = array() ) {
    need_priv('people','read');
    $opts = default_query_options( 'people', $opts, array( 'orderby' => 'people.cn', 'filters' => $filters ) );
    return sql_query( 'people', $opts );
  }
}

if( ! function_exists( 'sql_person' ) ) {
  function sql_person( $filters, $default = false ) {
    return sql_people( $filters, array( 'default' => $default, 'single_row' => true ) );
  }
}

// if( ! function_exists( 'sql_delete_people' ) ) {
//   function sql_delete_people( $filters ) {
//     sql_delete( 'people', $filters );
//   }
// }

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



    if( ! $person['authentication_method_simple'] ) {
      logger( 'auth_check_password: simple authentication disallowed for person', LOG_LEVEL_WARNING, LOG_FLAG_AUTH, 'auth' );
      return false;
    }
    switch( $person['password_hashfunction'] ) {
      case '':
        // probably: no password set
        return false;
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
    need_priv( 'person', 'password', $people_id );
    $person = sql_person( $people_id );
    if( $password ) {
      $salt = random_hex_string( 8 );
      $hash = crypt( $password, $salt );
      $hashfunction = 'crypt';
    } else {
      $salt = '';
      $hash = '';
      $hashfunction = '';
    }
    logger( "setting password [$people_id,$hashfunction]", LOG_LEVEL_INFO, LOG_FLAG_AUTH, 'password' );
    return sql_update( 'people', $people_id, array(
      'password_salt' => $salt
    , 'password_hashvalue' => $hash
    , 'password_hashfunction' => $hashfunction
    ) );
  }
}

/////////////////////
//
// 2.5. functions handling sessions:
//

function sql_sessions( $filters = array(), $opts = array() ) {
  global $now_unix, $session_lifetime;
  $joins = array(
    'people' => 'LEFT people on ( people.people_id = sessions.login_people_id )'
  );
  $selects = sql_default_selects( array( 'sessions', 'people' => 'prefix=1,.jpegphoto=' ) );
  $selects['logentries_count'] = " ( SELECT COUNT(*) FROM logbook WHERE logbook.sessions_id = sessions.sessions_id )";
  // 'expired': can't put this in 'more_selects' as it depends on leitvariable $session_lifetime
  $selects['expired'] = "( IF( sessions.atime < '".datetime_unix2canonical( $now_unix - $session_lifetime )."', 1, 0 ) )";
  $opts = default_query_options('sessions', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'sessions_id'
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'sessions', $filters, $opts['joins'], array(), array(
    'REGEX' => array( '~=', " CONCAT( 'sessions.uid', ';', 'people.cn' ) " )
  ) );
  return sql_query( 'sessions', $opts );
}

function sql_one_session( $filters, $default = false ) {
  return sql_sessions( $filters, array( 'default' => $default, 'single_row' => true ) );
}

function sql_delete_sessions( $filters, $opts = array() ) {
  global $login_sessions_id;

  need_priv( 'sessions', 'delete' );
  $rows = sql_sessions( $filters );
  $opts = parameters_explode( $opts, 'action' );
  $action = adefault( $opts, 'action', 'hard' );
  $rv = init_rv_delete_action( adefault( $opts, 'rv' ) );
  foreach( $rows as $r ) {
    $id = $r['sessions_id'];
    if( (int)$id === (int)$login_sessions_id ) {
      $problems = new_problem('cannot delete current login session');
    } else {
      $problems = sql_references( 'sessions', $id, "return=report,delete_action=$action,prune=persistentvars:sessions_id transactions:sessions_id" );
    }
    $rv = sql_handle_delete_action( 'sessions', $id, $action, $problems, $rv );
  }
  if( ( $count = $rv['deleted'] ) ) {
    logger( "sql_delete_sessions(): $count sessions deleted", LOG_LEVEL_INFO, LOG_FLAG_SYSTEM | LOG_FLAG_DELETE, 'sessions' );
  }
  return $rv;
}

// sql_prune_sessions():
// even when action 'check' or 'count' is requested, this function will still have the side effect
// of expiring sessions and deletion of corresonding persistentvars and transactions
//
function sql_prune_sessions( $opts = array() ) {
  global $now_unix, $login_sessions_id, $info_messages, $session_lifetime;

  // expire sessions; delete persistentvars and transactions of invalid sessions:
  //
  $thresh = datetime_unix2canonical( $now_unix - $session_lifetime );
  $count = $count_invalidate_sessions = sql_update( 'sessions', "valid, sessions_id != $login_sessions_id, atime < $thresh", 'valid=0' );
  $count += ( $count_delete_persistentvars = sql_delete( 'persistentvars', 'sessions.valid=0', 'joins=LEFT sessions' ) );
  $count += ( $count_delete_transactions = sql_delete( 'transactions', 'sessions.valid=0', 'joins=LEFT sessions' ) );

  if( $count ) {
    logger(
      "sql_prune_sessions(): $count_invalidate_sessions sessions expired; $count_delete_persistentvars persistent vars and $count_delete_transactions transactions deleted"
    , LOG_LEVEL_INFO, LOG_FLAG_SYSTEM | LOG_FLAG_DELETE, 'maintenance'
    );
  }

  $opts = parameters_explode( $opts );
  $maxage_seconds = adefault( $opts, 'maxage_seconds', $session_lifetime );
  $action = adefault( $opts, 'action', 'soft' );

  $filters = array( '&&'
  , 'valid=0'
  , array( '!', sql_references( 'sessions', 'sessions.sessions_id', 'return=filters,ignore=persistentvars:sessions_id transactions:sessions_id' ) )
  );
  $rv = sql_delete_sessions( $filters, "action=$action" );
  if( ( $count = $rv['deleted'] ) ) {
    logger( "sql_prune_sessions(): $count sessions deleted", LOG_LEVEL_INFO, LOG_FLAG_SYSTEM | LOG_FLAG_DELETE, 'maintenance' );
    $info_messages[] = "sql_prune_sessions(): $count sessions deleted";
  }

  return $rv;
}

/////////////////////
//
// 2.6. functions handling persistent vars:
//

function sql_store_persistent_vars( $vars, $people_id = 0, $sessions_id = 0, $thread = '', $script = '', $window = '', $self = 0 ) {
  global $login_sessions_id, $login_people_id;

  if( ( ! $login_sessions_id ) || ( $login_people_id != $people_id ) ) {
    need_priv('*','*');
  }

  $filters = array(
    'sessions_id' => $sessions_id
  , 'people_id'=> $people_id
  , 'thread' => $thread
  , 'script' => $script
  , 'window' => $window
  , 'self' => $self
  );
  if( $window || $self || $script ) {
    sql_delete( 'persistentvars', $filters );
  }
  foreach( $vars as $name => $value ) {
    if( $value === NULL ) {
      sql_delete( 'persistentvars', $filters + array( 'name' => $name ) );
    } else {
      if( isarray( $value ) ) {
        $value = json_encode( $value );
        $json = 1;
      } else {
        $json = 0;
      }
      sql_insert( 'persistentvars'
      , $filters + array( 'name' => $name , 'value' => $value, 'json' => $json )
      , array( 'update_cols' => array( 'value' => true, 'json' => true ) )
      );
    }
  }
}

function sql_persistent_vars( $filters = array(), $orderby = true ) {
  need_priv('persistent_vars','read');
  if( $orderby === true ) {
    $orderby = 'name,people_id,sessions_id,thread,script,window';
  }

  $filters = sql_canonicalize_filters( 'persistentvars', $filters );
  $selects = sql_default_selects( 'persistentvars' );
  $s = sql_query( 'persistentvars', array( 'filters' => $filters, 'selects' => $selects, 'orderby' => $orderby ) );
  return $s;
}

function sql_retrieve_persistent_vars( $people_id = 0, $sessions_id = 0, $thread = '', $script = '', $window = '', $self = 0 ) {
  global $login_sessions_id, $login_people_id;

  if( ( ! $login_sessions_id ) || ( $login_people_id != $people_id ) ) {
    need_priv('*','*');
  }

  $filters = array();
  if( $people_id !== NULL ) {
    $filters['people_id'] = $people_id;
  }
  if( $sessions_id !== NULL ) {
    $filters['sessions_id'] = $sessions_id;
  }
  if( $thread !== NULL ) {
    $filters['thread'] = $thread;
  }
  if( $script !== NULL ) {
    $filters['script'] = $script;
  }
  if( $window !== NULL ) {
    $filters['window'] = $window;
  }
  if( $self !== NULL ) {
    $filters['self'] = $self;
  }

  // we don't  cal sql_persistent_vars, as that would call need_priv()
  
  $filters = sql_canonicalize_filters( 'persistentvars', $filters );
  $selects = sql_default_selects( 'persistentvars' );
  $rows = sql_query( 'persistentvars', array( 'filters' => $filters, 'selects' => $selects, 'orderby' => 'name' ) );
  $r = array();
  foreach( $rows as $row ) {
    if( $row['json'] ) {
      $r[ $row['name'] ] = json_decode( $row['value'], true );
    } else {
      $r[ $row['name'] ] = $row['value'];
    }
  }
  return $r;
}

function retrieve_all_persistent_vars() {
  global $jlf_persistent_vars, $login_people_id, $login_sessions_id, $global_format, $deliverable;
  global $script, $parent_script, $parent_window, $parent_thread, $script, $window;

  if( ! isset( $jlf_persistent_vars['url'] ) ) {
    $jlf_persistent_vars['url']  = array(); // special case: variables passed around in url
  }
  // $jlf_persistent_vars['global']  = sql_retrieve_persistent_vars();
  $jlf_persistent_vars['user']    = sql_retrieve_persistent_vars( $login_people_id );
  $jlf_persistent_vars['session'] = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id );
  $jlf_persistent_vars['thread']  = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id, $parent_thread );
  $jlf_persistent_vars['script']  = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id, $parent_thread, $script );
  if( $deliverable ) {
    // special case: with deliverable:
    // - $parent_window for `window`
    // - merge `self` into `view`
    $jlf_persistent_vars['window']  = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id, $parent_thread, '',      $parent_window );
    $jlf_persistent_vars['view']    = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id, $parent_thread, $script, $parent_window, NULL );
  } else {
    $jlf_persistent_vars['window']  = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id, $parent_thread, '',      $window        );
    $jlf_persistent_vars['view']    = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id, $parent_thread, $script, $window        );
  }

  if( $parent_script === 'self' ) {
    $jlf_persistent_vars['self'] = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id, $parent_thread, $script, $parent_window, 1 );
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
  // sql_store_persistent_vars( $jlf_persistent_vars['global'] );
}

function sql_delete_persistent_vars( $filters ) {
  $problems = array();
  $vars = sql_persistent_vars( $filters );
  foreach( $vars as $v ) {
    $persistentvars_id = $v['persistentvars_id'];
    if( ! have_priv( 'persistentvars', 'delete', $persistentvars_id ) ) {
      $problems += new_problem( we( 'insufficient privileges to delete','keine Berechtigung zum Löschen' ) );
    }
  }
  if( $check ) {
    return $problems;
  }
  need( ! $problems, $problems );
  foreach( $vars as $v ) {
    sql_delete( 'persistentvars', $v['persistentvars_id'] );
  }
  return count( $vars );
}

////////////////////////////////
//
// 2.7. functions handling uids:
//
// table uids provides a generic mechanism to map arbitrary strings onto unique ids that are
// - relatively short
// - safe to pass in html e.g. as values of selection box items

$v2uid_cache = array();
$uid2v_cache = array();

function value2uid( $value, $tag = '' ) {
  global $v2uid_cache, $uid2v_cache;
  // hard-code two common cases:
  if( "$value" === '' )
    return '0-0';
  if( "$value" === '0' )
    return '0-1';
  $value = bin2hex( "$value" ) . '-' . $tag;
  if( isset( $v2uid_cache[ $value ] ) ) {
    $uid = $v2uid_cache[ $value ];
  } else {
    $result = sql_do( "SELECT CONCAT( uids_id, '-', signature ) as uid FROM uids WHERE value='$value'" ); // $value is hex-encoded!
    if( mysql_num_rows( $result ) > 0 ) {
      $row = mysql_fetch_array( $result, MYSQL_ASSOC );
      $uid = $row['uid'];
    } else {
      $signature = random_hex_string( 8 );
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

  if( "$uid" === '0-0' )
    return '';
  if( "$uid" === '0-1' )
    return '0';
  if( isset( $uid2v_cache[ "$uid" ] ) ) {
    $value = $uid2v_cache[ "$uid" ];
  } else {
    need( preg_match( '/^(\d{1,9})-([a-f0-9]{1,16})$/', $uid, /* & */ $v ), "uid2value(): malformed uid: [$uid]" );
    $result = sql_do( "SELECT value FROM uids WHERE uids_id='{$v[ 1 ]}' AND signature='{$v[ 2 ]}'" );
    if( mysql_num_rows( $result ) > 0 ) {
      $row = mysql_fetch_array( $result, MYSQL_ASSOC );
      $value = $row['value'];
      $v2uid_cache[ "$value" ] = $uid;
      $uid2v_cache[ "$uid" ] = $value;
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
// 2.8. garbage collection
//

function sql_garbage_collection_generic( $opts = array() ) {
  sql_prune_sessions( $opts );
  sql_prune_logbook( $opts );
  sql_prune_changelog( $opts );
}

if( ! function_exists( 'sql_garbage_collection' ) ) {
  function sql_garbage_collection( $opts = array() ) {
    logger( 'start: garbage collection', LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'maintenance' );
    sql_garbage_collection_generic( $opts );
    logger( 'finished: garbage collection', LOG_LEVEL_NOTICE, LOG_FLAG_SYSTEM, 'maintenance' );
  }
}

?>
