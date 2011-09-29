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
    error( $error_text. "\n  query: $sql\n  MySQL error: " . mysql_error() );
  }
  return $result;
}

// sql_do_single_row(), sql_do_single_field():
//  - execute sql query (which should be a SELECT) and expect exactly one row in result
//  - return just this row or even just one specific field
//  - $default === false: no match is an error
//    otherwise: return $default if no match
//
function sql_do_single_row( $sql, $default = false ) {
  $result = sql_do( $sql );
  $rows = mysql_num_rows($result);
  if( $rows == 0 ) {
    if( $default !== false )
      return $default;
  }
  need( $rows > 0, "no match: $sql" );
  need( $rows == 1, "result of query $sql not unique ($rows rows returned)" );
  return mysql_fetch_array( $result, MYSQL_ASSOC );
}

function sql_do_single_field( $sql, $fieldname, $default = false ) {
  $row = sql_do_single_row( $sql, NULL );
  if( isarray( $row ) ) {
    return $row[ $fieldname ];
  }
  if( $default !== false ) {
    return $default;
  }
  error( "no match: $sql" );
}

// mysql2array(): return result of SELECT query as an array of rows
// - numerical indices are default; field `nr' will be added to every row (counting from 0)
// - if $key and $val are given: return associative array, mapping every `$key' to `$val'
//
function mysql2array( $result, $key = false, $val = false ) {
  // if( is_array( $result ) )  // temporary kludge: make me idempotent
  //   return $result;
  $r = array();
  if( $key ) {
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

// row_init(): return array representing row of table $tablename, initialized with table defaults
//
function row_init( $tablename ) {
  global $tables;
  $cols = $tables[ $tablename ]['cols'];
  foreach( $cols as $fieldname => $c ) {
    $row[ $fieldname ] = $c['default'];
  }
  return $row;
}

// row2global(): set global variables to all columns in table $tablename
// - use values from $row or table defaults 
// - prefix can be prefix string, or array mapping columns to variable names
//
// function row2global( $tablename, $row = false, $prefix = '' ) {
//   global $tables;
// 
//   $cols = $tables[$tablename]['cols'];
//   foreach( $cols as $fieldname => $c ) {
//     if( is_string( $prefix ) ) {
//       $GLOBALS[ $prefix.$fieldname ] = adefault( $row, $fieldname, $c['default'] );
//     } else if( isset( $prefix[ $fieldname ] ) ) {
//       $GLOBALS[ $prefix[ $fieldname ] ] = adefault( $row, $fieldname, $c['default'] );
//     } else {
//       $GLOBALS[ $fieldname ] = adefault( $row, $fieldname, $c['default'] );
//     }
//   }
// }
// 


///////////////////////////////////////////
// functions to compile query strings:
//

///////////////////////////////////////////////////////
// 1. functions to compile an sql filter expression:
//


function sql_canonicalize_filters( $tlist, $filters_in, $joins = array(), $hints = array() ) {
  global $tables;

  if( adefault( $filters_in, -1, '' ) === 'canonical_filter' )
    return $filters_in; // already canonicalized - return as-is

  $index = 0;
  $rv = sql_canonicalize_filters_rec( $filters_in, $index );

  // debug( $rv, 'sql_canonicalize_filters: raw canonical filters' );

  if( isstring( $tlist ) )
    $tlist = explode( ',', $tlist );
  if( isstring( $joins ) )
    $joins = explode( ',', $joins );
  foreach( $joins as $key => $t ) {
    if( is_numeric( $key ) ) {
      $tlist[] = $t;
    } else {
      // assume this is from a $join array:
      if( strncmp( $key, 'LEFT ', 5 ) == 0 )
        $tlist[] = substr( $key, 5 );
      else
        $tlist[] = $key;
    }
  }
  $table = reset( $tlist );

  foreach( $rv as & $atom ) {
    if( $atom === 'canonical_filter' )
      continue;
    if( $atom[ -1 ] !== 'raw_atom' )
      continue;
    $key = & $atom[ 1 ];
    // prettydump( $key, 'handling key:' );
    if( isset( $hints[ $key ] ) ) {
      // prettydump( $hints[ $key ], 'using hint:' );
      $key = $hints[ $key ];
      $atom[ -1 ] = 'cooked_atom';
      continue;
    } else if( "$key" === 'id' ) {
      // prettydump( $key, 'primary key:' );
      need( isset( $tables[ $table ]['cols'][ $table.'_id' ] ) );
      $key = $table.'.'.$table.'_id';
      $atom[ -1 ] = 'cooked_atom';
      continue;
    } else {
      $t = explode( '.', $key );
      if( isset( $t[ 1 ] ) ) {
        // prettydump( $t, 'fq: split:' );
        if( in_array( $t[ 0 ], $tlist ) ) {
          if( isset( $tables[ $t[ 0 ] ]['cols'][ $t[ 1 ] ] ) ) {
            // ok: $key is a fq table name!
            $atom[ -1 ] = 'cooked_atom';
            continue;
          }
        }
      } else {
        // prettydump( $t, 'NON-fq: ' );
        foreach( $tlist as $t ) {
          if( isset( $tables[ $t ]['cols'][ $key ] ) ) {
            // prettydump( $t, 'found in table:' );
            $key = "$t.$key";
            $atom[ -1 ] = 'cooked_atom';
            continue 2;
          }
        }
      }
    }
  }
  // debug( $rv, 'sql_canonicalize_filters: after handling atoms: ' );
  return $rv;
}


// split_atom():
//   split "KEY REL VAL" atomic expression string into parts;
//   REL and VAL may be absent to indicate check for boolean true of KEY
//
function split_atom( $a, $default_rel = '!0' ) {
  if( ( $n2 = strpos( $a, '=' ) ) > 0 ) {
    $n1 = $n2;
    if( strpos( ' <>!~', $a[ $n2 - 1 ] ) > 0 ) {
      $n1--;
    }
  } else if( ( $n2 = strpos( $a, '>' ) ) > 0 ) {
    $n1 = $n2;
  } else if( ( $n2 = strpos( $a, '<' ) ) > 0 ) {
    $n1 = $n2;
  } else {
    $n1 = $n2 = 0;
  }
  if( $n2 > 0 ) {
    return array( substr( $a, $n1, $n2 - $n1 + 1 ), trim( substr( $a, 0, $n1 ) ), trim( substr( $a, $n2 + 1 ) ) );
  } else {
    return array( $default_rel, trim( $a ), '' );
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
// - returns CANONICAL_FILTER ::= array( -1 => 'canonical_filter', FTREE | ATOM [ , ... ] ), where
//   - ATOM ::= array( -1 => 'raw_atom', REL, KEY, RHS )
//   - FTREE ::= array( -1 => 'filter_list', OP, [ REF , ... ] )
//   - REF ::= integer (subnode index into CANONICAL_FILTER)
//   - OP ::=  '&&' | '||' | '!'  (boolean operations to compose filters)
//   - REL ::= '=' | '<=' | '>=' | '!=' | '~=' | '!0'  (boolean relations to be used in atomic expressions)
//
function sql_canonicalize_filters_rec( $filters_in, & $index ) {

  $rv = array( -1 => 'canonical_filter' );

  if( ( $filters_in === array() ) || ( $filters_in === NULL ) || ( $filters_in === '' ) ) {
    $rv[ $index++ ] = array( -1 => 'filter_list', '0' => '&&' );
    return $rv;
  }

  if( is_numeric( $filters_in ) ) {  // guess: is primary key
    // need( isset( $cols[$table.'_id'] ), "table $table: no primary key" );
    $rv[ $index++ ] = array( -1 => 'raw_atom' , 0 => '=', 1 => 'id', 2 => $filters_in );
    return $rv;
  }
  if( is_string( $filters_in ) ) {
    $filters_in = explode( ',', $filters_in );
    if( count( $filters_in ) === 1 ) {
      $filters_in = split_atom( $filters_in[ 0 ] );
    }
  }
  if( is_array( $filters_in ) ) {
    if( adefault( $filters_in, -1 ) === 'canonical_filter' ) {
      $delta = $index;
      // prettydump( $delta, 'delta' );
      for( $n = 0; isset( $filters_in[ $n ] ); $n++ ) {
        $rv[ $index ] = $filters_in[ $n ];
        if( $rv[ $index ][ -1 ] === 'filter_list' ) {
          for( $j = 1; isset( $rv[ $index ][ $j ] ); $j++ ) {
            $rv[ $index ][ $j ] += $delta;
          }
        }
        ++$index;
      }
      return $rv;
    }
    // print_on_exit( "<!-- sql_canonicalize_filters_rec: in: " .var_export( $filters_in, true ). " -->" );
    $binop = '&&';
    if( isset( $filters_in[ 0 ] ) ) {
      switch( "{$filters_in[ 0 ]}" ) {
        case '&&':
        case '||':
        case '!':
          // filters_in[ 0 ] is boolean operator - copy and skip it:
          $binop = $filters_in[ 0 ];
          unset( $filters_in[ 0 ] );
          break;
        case '>':
        case '>=':
        case '<':
        case '<=':
        case '=':
        case '!=':
        case '~=':
        case '!0':
          // $filters is an atom:
          $rv[ $index++ ] = array( -1 => 'raw_atom' ) + $filters_in;
          return $rv;
      }
    }
    $flist = & $rv[ $index++ ];
    $flist = array( -1 => 'filter_list', 0 => $binop );
    // prettydump( $filters_in, 'sql_canonicalize_filters: array in:' );
    foreach( $filters_in as $key => $cond ) {
      // prettydump( array( $key => $cond), 'sql_canonicalize_filters: handling part:' );
      if( is_numeric( $key ) ) {
        $i = $index;
        $rv += sql_canonicalize_filters_rec( $cond, $index );
        if( isset( $rv[ $i ] ) )
          $flist[] = $i;
      } else {
        $flist[] = $index;
        $a = split_atom( $key, '=' );
        $a[ 2 ] = $cond;
        $a[ -1 ] = 'raw_atom';
        $rv[ $index++ ] = $a;
      }
    }
    // prettydump( $rv, 'sql_canonicalize_filters: array out:' );
    return $rv;
  }
  error( 'cannot handle input filters' );
}


// sql_filters2expression:
//  - turn $filters into an sql filter expression
//  - $filters must be canonicalized before calling this function
//
function sql_filters2expression( $can_filters ) {
  need( $can_filters[ -1 ] === 'canonical_filter' );
  return sql_filters2expression_rec( $can_filters, 0 );
}

function sql_filters2expression_rec( $filters, $index ) {
  $f = $filters[ $index ];
  switch( $f[ -1 ] ) {
    case 'cooked_atom':
      $op = $f[ 0 ];
      $key = $f[ 1 ];
      $rhs = $f[ 2 ];
      if( $op === '~=' )
        $op = 'RLIKE';
      if( $op === '!0' )
        $rhs = $op = '';
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
            error( "cannot compare list with operator $op" );
        }
        $s = '(';
        $komma = '';
        foreach( $rhs as $c ) {
          $s .= "$komma '".mysql_real_escape_string( $c )."'";
          $komma = ',';
        }
        $rhs = $s . ')';
      } else if( $op ) {
        $rhs = "'".mysql_real_escape_string( $rhs )."'";
      } else {
        $rhs = '';
      }
      return sprintf( "%s %s %s", $key, $op, $rhs );
    case 'filter_list':
      $op = $f[ 0 ];
      unset( $f[ -1 ] );
      unset( $f[ 0 ] );
      $sql = '';
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
          error( "cannot handle operator $op" );
      }
      foreach( $f as $ref ) {
        if( $sql )
          $sql .= $op;
        $sql .= ' ( ' . sql_filters2expression_rec( $filters, $ref ) . ' ) ';
      }
      return $sql;
    case 'raw_atom':
      error( 'unhandled atom encountered' );
    default:
      error( 'unexpected filter element' );
  }
  // prettydump( $sql, 'sql_filters2expression: sql:' );
  return $sql;
}


// function sql_select_scalar( $table, $scalars ) 
//   if( $relation ) {
//     $relation_table = $relation['table'];
//     $relation_field = adefault( $relation, 'match_field', 'hosts_id' );
//     $key_field = $relation['key_field'];
//     $key_value = $relation['key_value'];
//     $count_name = adefault( $relation, 'count_name', 'relation_count' );
//     $selects[] = " ( SELECT count(*) FROM $relation_table
//                      WHERE ( $key_field = $key_value ) AND ( $relation_field = hosts.hosts_id ) AS $count_name ";
//   }

//////////////////////
// 2. functions to compile SELECT queries
//

// sql_default_selects():
// return SELECT clauses for all colums in all given tables:
//  $table :== "table" | array( 'table' [, ... ] )
//  $disambiguation can be
//  - string: (to be used as a prefix for all columns)
//  - array: 
//    - every key is of the form 'column' or 'table.column'
//    - value is either a unique identifier for this column, or FALSE to skip this column entirely
//
function sql_default_selects( $table, $disambiguation = array() ) {
  global $tables;

  $selects = array();
  if( isstring( $table ) ) {
    $table = explode( ',', $table );
    if( count( $table ) == 1 ) {
      $table = $table[ 0 ];
    }
  }
  if( is_array( $table ) ) {
    foreach( $table as $t ) {
      $selects = array_merge( $selects, sql_default_selects( $t, $disambiguation ) );
    }
    return $selects;
  }
  $cols = $tables[ $table ]['cols'];
  foreach( $cols as $name => $type ) {
    if( is_string( $disambiguation ) ) {
      $selects[] = "$table.$name as $disambiguation$name";
    } else if( isset( $disambiguation[ $name ] ) ) {
      if( $disambiguation[ $name ] ) {
        $selects[] = "$table.$name as " . $disambiguation[ $name ];
      }
    } else if( isset( $disambiguation["$table.$name"] ) ) {
      if( $disambiguation["$table.$name"] ) {
        $selects[] = "$table.$name as " . $disambiguation["$table.$name"];
      }
    } else {
      $selects[] = "$table.$name as $name";
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
  return sql_filters2expression( $can_filters );
}

/*
 * need_joins: to be used in scalare subqueries as in "SELECT x , ( SELECT ... ) as y, z":
 *  generate JOIN-statements from rules for required tables, _except_ for those passed in
 *  $using which are assumed to be already available from outer context
 */
function need_joins_array( $using, $rules ) {
  $joins = array();
  is_array( $using ) or $using = explode( ',', $using );
  foreach( $rules as $table => $rule ) {
    if( ! in_array( $table, $using ) ) {
      if( strstr( $rule, ' ON ' ) ) {
        $joins[] = $rule;
      } else {
        $joins[$table] = $rule;
      }
    }
  }
  return $joins;
}
function need_joins( $using, $rules ) {
  $joins = '';
  $joins_array = need_joins_array( $using, $rules );
  foreach( $joins_array as $table => $rule ) {
    if( is_numeric( $table ) ) {
      if( strncmp( $rule, 'LEFT ', 5 ) == 0 ) {
        $rule = substr( $rule, 5 );
        $joins .= " LEFT JOIN $rule ";
      } else {
        $joins .= " JOIN $rule ";
      }
    } else {
      $join = 'JOIN';
      if( strncmp( $table, 'LEFT ', 5 ) == 0 ) {
        $join = 'LEFT JOIN';
        $table = substr( $table, 5 );
      }
      if( strstr( $rule, '=' ) ) {
        $joins .= " $join $table ON $rule ";
      } else {
        $joins .= " $join $table USING ( $rule ) ";
      }
    }
  }
  return $joins;
}


// sql_query(): compose sql SELECT query from parts:
//
function sql_query(
  $op, $table, $filters = false, $selects = '', $joins = '', $orderby = false
, $groupby = false , $limit_from = 0, $limit_count = 0
) {
  // print_on_exit( "<!-- sql_query: early: [$op] [$table] -->" );
  $selects or $selects = sql_default_selects( $table );
  if( is_string( $selects ) ) {
    $select_string = $selects;
  } else {
    $select_string = '';
    $komma = '';
    foreach( $selects as $s ) {
      $select_string .= "$komma $s";
      $komma = ',';
    }
  }
  if( is_string( $joins ) ) {
    $join_string = $joins;
  } else {
    $join_string = need_joins( array(), $joins );
  }
  switch( $op ) {
     case 'COUNT':
       $op = 'SELECT';
       $select_string = "COUNT(*) as count";
       break;
  }
  $query = "$op $select_string FROM $table $join_string";
  // print_on_exit( "<!-- sql_query: mid: [$op] [$table] [$join_string] [$query] -->" );
  if( $filters ) {
    $cf = sql_canonicalize_filters( $table, $filters );
    $query .= ( " WHERE " . sql_filters2expression( $cf ) );
  }
  if( $groupby ) {
    $query .= " GROUP BY $groupby ";
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
  // print_on_exit( "<!-- sql_query: end: [$op] [$table] [$query] -->" );
  return $query;
}



/////////////////////////////////////////////////////
// functions to compile and execute query strings:
//


function sql_count( $table, $filters = false ) {
  $cf = sql_canonicalize_filters( $table, $filters );
  return sql_do_single_field(
    "SELECT count(*) as count FROM $table WHERE " . sql_filters2expression( $cf )
  , 'count'
  );
}

function sql_unique_values( $table, $column, $orderby = '' ) {
  if( ! $orderby )
    $orderby = $column;
  $rows = mysql2array( sql_do( "SELECT DISTINCT $column FROM $table ORDER BY $orderby" ) );
  $a = array();
  if( ( $n = strpos( $column, '.' ) ) > 0 ) {
    // remove table name, if any:
    $column_short = substr( $column, $n + 1 );
  } else {
    $column_short = $column;
  }
  foreach( $rows as $r ) {   // fake unique ids
    $a[ md5( $r[ $column_short ] ) ] = $r[ $column_short ];
  }
  return $a;
}

function sql_unique_id( $table, $column, $value ) {
  if( ! $value )
    return 0;
  if( ! $table )
    return md5( $value );
  if( sql_count( $table, array( $column => $value ) ) ) {
    return md5( $value );
  } else {
    return 0;
  }
}

function sql_unique_value( $table, $column, $id ) {
  if( ! $id )
    return false;
  $rows = sql_unique_values( $table, $column );
  need( isset( $rows[ $id ] ), "$table.$column: no value matching id $id" );
  return $rows[ $id ];
}

function sql_select( $table, $filters = false, $selects = '', $joins = '', $orderby = false, $groupby = false ) {
  $sql = sql_query( 'SELECT', $table, $filters, $selects, $joins, $orderby, $groupby );
  return mysql2array( sql_do( $sql ) );
}


function sql_delete( $table, $filters = false ) {
  $cf = sql_canonicalize_filters( $table, $filters );
  $sql = "DELETE FROM $table WHERE " . sql_filters2expression( $cf );
  return sql_do( $sql );
}

function sql_update( $table, $filters, $values, $escape_and_quote = true ) {
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
  $sql = "UPDATE $table SET";
  $komma='';
  foreach( $values as $key => $val ) {
    if( $escape_and_quote )
      $val = "'" . mysql_real_escape_string($val) . "'";
    $sql .= "$komma $key=$val";
    $komma=',';
  }
  if( $filters ) {
    $cf = sql_canonicalize_filters( $table, $filters );
    $sql .= ( " WHERE " . sql_filters2expression( $cf ) );
  }
  return sql_do( $sql, "failed to update table $table: " );
}

function sql_insert( $table, $values, $update_cols = false, $escape_and_quote = true ) {
  global $tables;
  switch( $table ) {
    case 'leitvariable':
    case 'transactions':
    case 'logbook':
    case 'sessions':
      break;
    default:
      fail_if_readonly();
  }
  $komma='';
  $update_komma='';
  $cols = '';
  $vals = '';
  $update = '';
  foreach( $values as $key => $val ) {
    $cols .= "$komma $key";
    if( is_array( $val ) ) {
      prettydump( $val, 'sql_insert: array detected:' );
    }
    if( $escape_and_quote )
      $val = "'" . mysql_real_escape_string($val) . "'";

    $vals .= "$komma $val";
    if( is_array( $update_cols ) ) {
      if( isset( $update_cols[$key] ) ) {
        if( $update_cols[$key] !== true ) {
          $val = $update_cols[$key];
          if( $escape_and_quote )
            $val = "'" . mysql_real_escape_string($val) . "'";
        }
        $update .= "$update_komma $key=$val";
        $update_komma=',';
      }
    } elseif( $update_cols ) {
      $update .= "$update_komma $key=$val";
      $update_komma=',';
    }
    $komma=',';
  }
  $sql = "INSERT INTO $table ( $cols ) VALUES ( $vals )";
  if( $update_cols or is_array( $update_cols ) ) {
    $sql .= " ON DUPLICATE KEY UPDATE $update";
    if( isset( $tables[ $table ][ 'cols' ][ $table.'_id' ] ) )
      $sql .= "$update_komma {$table}_id = LAST_INSERT_ID( {$table}_id ) ";
  }
  if( sql_do( $sql, "failed to insert into table $table: " ) )
    return mysql_insert_id();
  else
    return FALSE;
}

///////////////////////
// function to handle relation tables
//

function sql_get_relation( $table_1, $table_2, $table_relation, $filters_1 = array(), $filters_2 = array() ) {
  $filters_1 = sql_canonicalize_filters( $table_1, $filters_1 );
  $filters_2 = sql_canonicalize_filters( $table_2, $filters_2 );
  $joins = array( $table_1 => $table_1.'_id', $table_2 => $table_2.'_id' );
  $selects = array( $table_relation.'.'.$table_1.'_id', $table_relation.'.'.$table_2.'_id' );
  $orderby = $table_relation.'.'.$table_1.'_id, '.$table_relation.'.'.$table_2.'_id';
  $f = array( '&&', $filters_1['filters'], $filters_2['filters'] );
  $sql = sql_query( 'SELECT', $table_relation, $f, $selects, $joins );
  $relation = mysql2array( sql_do( $sql ) );
  return $relation;
}

function sql_relation_on( $table_1, $table_2, $table_relation, $id_1, $id_2 ) {
  $values = array( $table_1.'_id' => $id_1 , $table_2.'_id' => $id_2 );
  return sql_insert( $table_relation, $values );
}

// function sql_relation_off( $table_1, $table_2, $table_relation, $id_1, $id_2 ) {
//  $values = array( $table_1.'_id' => $id_1 , $table_2.'_id' => $id_2 );
//  return sql_insert( $table_relation, $values );
// }


///////////////////////////////////////
//
// functions to access individual tables:
// (many are defaults if no application-specific function provided)
//

///////////////////////
//
// functions to access table `logbook'
//

if( ! function_exists( 'sql_query_logbook' ) ) {
  function sql_query_logbook( $op, $filters_in = array(), $using = array(), $orderby = false ) {
    $joins = array();
    $joins['LEFT sessions'] = 'sessions_id';
    $groupby = 'logbook.logbook_id';
    $selects = sql_default_selects( array( 'logbook', 'sessions' ), array( 'sessions.sessions_id' => false ) );
    //   this is totally silly, but MySQL insists on this "disambiguation"     ^ ^ ^

    $filters = sql_canonicalize_filters( 'logbook', $filters_in, $joins, array(
      // hints: allow prefix f_ to avoid clash with global variables:
      'f_thread' => 'thread', 'f_window' => 'window', 'f_script' => 'script', 'f_sessions_id' => 'sessions_id'
    ) );

    switch( $op ) {
      case 'SELECT':
        break;
      case 'COUNT':
        $op = 'SELECT';
        $selects = 'COUNT(*) as count';
        break;
      case 'MAX':
        $op = 'SELECT';
        $selects = 'MAX( logbook_id ) as max_logbook_id';
        break;
      default:
        error( "undefined op: $op" );
    }
    $s = sql_query( $op, 'logbook', $filters, $selects, $joins, $orderby );
    return $s;
  }
}

if( ! function_exists( 'sql_logbook' ) ) {
  function sql_logbook( $filters = array(), $orderby = true ) {
    if( $orderby === true )
      $orderby = 'sessions_id,timestamp';
    $sql = sql_query_logbook( 'SELECT', $filters, array(), $orderby );
    return mysql2array( sql_do( $sql ) );
  }
}

function sql_logentry( $logbook_id, $default = NULL ) {
  $sql = sql_query_logbook( 'SELECT', $logbook_id );
  return sql_do_single_row( $sql, $default );
}

function sql_logbook_max_logbook_id() {
  $sql = sql_query_logbook( 'MAX' );
  return sql_do_single_field( $sql, 'max_logbook_id', 0 );
}

function sql_delete_logbook( $filters ) {
  foreach( sql_logbook( $filters ) as $l ) {
    sql_delete( 'logbook', $l['logbook_id'] );
  }
}


///////////////////////
//
// functions to access table `people' (in particular: for authentication!)
//

if( ! function_exists( 'sql_query_people' ) ) {
  function sql_query_people( $op, $filters = array(), $using = array(), $orderby = false ) {
    $selects = sql_default_selects( 'people' );
    $joins = array();

    switch( $op ) {
      case 'SELECT':
        break;
      case 'COUNT':
        $op = 'SELECT';
        $selects = 'COUNT(*) as count';
        break;
      default:
        error( "undefined op: $op" );
    }
    return sql_query( $op, 'people', $filters, $selects, $joins, $orderby );
  }
}

if( ! function_exists( 'sql_people' ) ) {
  function sql_people( $filters = array(), $orderby = 'people.cn' ) {
    $sql = sql_query_people( 'SELECT', $filters, array(), $orderby );
    return mysql2array( sql_do( $sql ) );
  }
}

if( ! function_exists( 'sql_person' ) ) {
  function sql_person( $filters, $default = false ) {
    $sql = sql_query_people( 'SELECT', $filters, array(), 'people.cn' );
    return sql_do_single_row( $sql, $default );
  }
}

if( ! function_exists( 'sql_delete_people' ) ) {
  function sql_delete_people( $filters ) {
    sql_delete( 'people', $filters );
  }
}

if( ! function_exists( 'sql_insert_person' ) ) {
  function sql_insert_person( $cn, $uid, $password = false ) {
    $id = sql_insert( 'people', array( 'cn' => $ch , 'uid' => $uid ) );
    if( $password )
      auth_set_password( $id, $password );
    return $id;
  }
}

if( ! function_exists( 'auth_check_password' ) ) {
  function auth_check_password( $people_id, $password ) {
    global $allowed_authentication_methods;
    print_on_exit( "<!-- auth_check_password: 1: $people_id, [$password] -->" );
    $allowed = explode( ',', $allowed_authentication_methods );
    if( ! in_array( 'simple', $allowed ) ) {
      // print_on_exit( "<!-- auth_check_password: 2a -->" );
      return false;
    }
    if( ! $people_id ) {
      // print_on_exit( "<!-- auth_check_password: 2b -->" );
      return false;
    }
    if( ! $password ) {
      // print_on_exit( "<!-- auth_check_password: 2c -->" );
      return false;
    }
    $person = sql_person( $people_id );
    $auth_methods = explode( ',', $person['authentication_methods'] );
    if( ! in_array( 'simple', $auth_methods ) ) {
      // print_on_exit( "<!-- auth_check_password: 2d -->" );
      return false;
    }
    switch( $person['password_hashfunction'] ) {
      case 'crypt':
        $c = crypt( $password, $person['password_salt'] );
        print_on_exit( "<!-- auth_check_password: 3: $c -->" );
        return ( $person['password_hashvalue'] === crypt( $password, $person['password_salt'] ) );
      default:
        error( 'unsupported password_hashfunction: ' . $person['password_hashfunction'] );
    }
    return false;
  }
}

if( ! function_exists( 'auth_set_password' ) ) {
  function auth_set_password( $people_id, $password ) {
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
    return sql_update( 'people', $people_id, array(
      'password_salt' => $salt
    , 'password_hashvalue' => crypt( $password, $salt )
    , 'password_hashfunction' => 'crypt'
    , 'authentication_methods' => $auth_methods_string
    ) );
  }
}

/////////////////////
//
// functions handling sessions:
//

function sql_sessions( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'login_people_id,ctime';

  $filters = sql_canonicalize_filters( 'sessions', $filters, array( 'f_sessions_id' => 'sessions_id' ) );
  $sql = sql_query( 'SELECT', 'sessions', $filters, sql_default_selects( 'sessions' ), array(), $orderby );
  // debug( $sql, 'sql' );
  return mysql2array( sql_do( $sql ) );
}


function sql_delete_sessions( $filters ) {
  foreach( sql_sessions( $filters ) as $s ) {
    $id = $s['id'];
    sql_delete( 'persistent_vars', 'sessions_id=$s' );
    sql_delete( 'sessions', $id );
  }
}

/////////////////////
//
// functions store and retrieve persistent vars:
//

function sql_store_persistent_vars( $vars, $people_id = 0, $sessions_id = 0, $thread = '', $script = '', $window = '', $self = 0 ) {
  // prettydump( array( $sessions_id, $vars, $thread, $script, $window, $self ), 'sql_store_persistent_vars' );
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
      , array( 'value' => true, 'json' => true )
      );
    }
  }
}

function sql_persistent_vars( $filters = array(), $orderby = true ) {
  if( $orderby === true )
    $orderby = 'name,people_id,sessions_id,thread,script,window';

  $filters = sql_canonicalize_filters( 'persistent_vars', $filters, array(), array(
    // hints: allow prefix f_ to avoid clash with global variables:
    'f_thread' => 'thread', 'f_window' => 'window', 'f_script' => 'script', 'f_sessions_id' => 'sessions_id'
  ) );
  $sql = sql_query( 'SELECT', 'persistent_vars', $filters, sql_default_selects( 'persistent_vars' ), array(), $orderby );
  // debug( $sql, 'sql' );
  return mysql2array( sql_do( $sql ) );
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

function sql_delete_persistent_vars( $filters ) {
  global $login_people_id;
  sql_delete( 'persistent_vars', array( '&&' , 'people_id' => array( 0, $login_people_id ) , $filters ) );
}

?>
