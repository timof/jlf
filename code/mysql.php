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

function sql_do( $sql, $debug_level = LEVEL_IMPORTANT, $error_text = "MySQL query failed: " ) {
  if( $debug_level <= $_SESSION['LEVEL_CURRENT'] ) {
    open_div( 'alert', '', htmlspecialchars( $sql ) );
  }
  // print_on_exit( "<!-- sql_do: $sql -->" );
  $result = mysql_query( $sql );
  if( ! $result ) {
    error( $error_text. "\n  query: $sql\n  MySQL error: " . mysql_error() );
  }
  return $result;
}

// sql_do_single_row, sql_do_single_field:
//  expect exactly one row from mysql; return just this row or even just one specific field
//  $allownull:
//    if true : return NULL if no match
//    if array: default row to return if no match
//
function sql_do_single_row( $sql, $allownull = false ) {
  $result = sql_do( $sql );
  $rows = mysql_num_rows($result);
  if( $rows == 0 ) {
    if( is_array( $allownull ) )
      return $allownull;
    if( $allownull )
      return NULL;
  }
  need( $rows > 0, "no match: $sql" );
  need( $rows == 1, "result of query $sql not unique ($rows rows returned)" );
  return mysql_fetch_array( $result, MYSQL_ASSOC );
}

function sql_do_single_field( $sql, $fieldname, $allownull = false ) {
  $row = sql_do_single_row( $sql, $allownull );
  if( $row )
    return $row[$fieldname];
  else
    return NULL;
}

function mysql2array( $result, $key = false, $val = false ) {
  // if( is_array( $result ) )  // temporary kludge: make me idempotent
  //   return $result;
  $r = array();
  $n = 0;
  while( $row = mysql_fetch_array( $result, MYSQL_ASSOC ) ) {
    if( $key ) {
      need( isset( $row[$key] ) );
      need( isset( $row[$val] ) );
      $r[$row[$key]] = $row[$val];
    } else {
      $row['nr'] = $n++;
      $r[] = $row;
    }
  }
  return $r;
}

function row_init( $tablename ) {
  global $tables;
  $cols = $tables[ $tablename ]['cols'];
  foreach( $cols as $fieldname => $c ) {
    $row[ $fieldname ] = $c['default'];
  }
  return $row;
}

function row2global( $tablename, $row = false, $prefix = '' ) {
  global $tables;

  $cols = $tables[$tablename]['cols'];
  foreach( $cols as $fieldname => $c ) {
    if( is_string( $prefix ) ) {
      $GLOBALS[ $prefix.$fieldname ] = adefault( $row, $fieldname, $c['default'] );
    } else if( isset( $prefix[ $fieldname ] ) ) {
      $GLOBALS[ $prefix[ $fieldname ] ] = adefault( $row, $fieldname, $c['default'] );
    } else {
      $GLOBALS[ $fieldname ] = adefault( $row, $fieldname, $c['default'] );
    }
  }
}

///////////////////////////////////////////
// functions to compile query strings:
//


///////////////////////////////////////////////////////
// 1. functions to compile an sql filter expression:
//

// sql_canonicalize_filters:
//   - turn $filters into an array
//   - prefix fieldnames with table name, where possible
//   - allow shortcut: integer means primary key
//   - turn atoms into { relation, key, rhs } arrays
//   - the function is idempotent
//   - atomic subexpressions will be returned as a separate flat list of references
//     which can be post-processed e.g. by $table-specific functions
// (this is to handle simple default cases in table-specific functions)
//
function sql_canonicalize_filters( $tlist, $filters_in, $joins = array(), $hints = array() ) {
  global $tables;

  if( adefault( $filters_in, -1, '' ) === 'canonical_filters' )
    return $filters_in;

  if( isstring( $tlist ) )
    $tlist = array( $tlist );
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

  $rv = sql_canonicalize_filters_rec( $filters_in );

  $rv['unhandled_atoms'] = array();
  // prettydump( $rv, 'sql_canonicalize_filters: got canonical array: ' );
  foreach( $rv['atoms'] as & $atom ) {
    $key = & $atom[ 1 ];
    // prettydump( $key, 'handling key:' );
    if( isset( $hints[ $key ] ) ) {
      // prettydump( $hints[ $key ], 'using hint:' );
      $key = $hints[ $key ];
      continue;
    } else if( "$key" === 'id' ) {
      // prettydump( $key, 'primary key:' );
      need( isset( $tables[ $table ]['cols'][ $table.'_id' ] ) );
      $key = $table.'.'.$table.'_id';
      continue;
    } else {
      $t = explode( '.', $key );
      if( isset( $t[ 1 ] ) ) {
        if( isset( $tlist[ $t[ 0 ] ]['cols'][ $t[ 1 ] ] ) ) {
          // prettydump( $key, 'fq table name:' );
          // ok: it's a fq table name
          continue;
        }
      } else {
        foreach( $tlist as $t ) {
          if( isset( $tables[ $t ]['cols'][ $key ] ) ) {
            // prettydump( $t, 'found in table:' );
            $key = "$t.$key";
            continue 2;
          }
        }
      }
    }
    // prettydump( $atom, 'keeping unhandled atom:' );
    // unhandled atom: keep in list:
    $rv['unhandled_atoms'][] = & $atom;
  }
  $rv[ -1 ] = 'canonical_filters';
  // prettydump( $rv, 'sql_canonicalize_filters: after handling atoms: ' );
  return $rv;
}

function & sql_canonicalize_filters_rec( $filters_in ) {

  $rv = array( 'filters' => array(), 'atoms' => array() );
  $fnew = & $rv['filters'];
  // $atoms will actually take up reference into fnew:
  $atoms = & $rv['atoms'];

  if( ! $filters_in )
    return $rv;

  if( is_numeric( $filters_in ) ) {  // guess: is primary key
    // need( isset( $cols[$table.'_id'] ), "table $table: no primary key" );
    $fnew[ 0 ] = array( '=', 'id', $filters_in );
    $atoms[ 0 ] = & $fnew[ 0 ];
    // prettydump( $rv, 'sql_canonicalize_filters_rec: numeric:' );
    return $rv;
  }
  if( is_string( $filters_in ) ) {
    $n = 0;
    foreach( explode( ',', $filters_in ) as $a ) {
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
        $fnew[ $n ] = array( substr( $a, $n1, $n2 - $n1 + 1 ), trim( substr( $a, 0, $n1 ) ), substr( $a, $n2 + 1 ) );
      } else {
        $fnew[ $n ] = array( '', $a, null );
      }
      $atoms[ $n ] = & $fnew[ $n ];
      $n++;
    }
    // prettydump( $rv, 'sql_canonicalize_filters_rec: string:' );
    return $rv;
  }
  if( is_array( $filters_in ) ) {
    // print_on_exit( "<!-- sql_canonicalize_filters_rec: in: " .var_export( $filters_in, true ). " -->" );
    if( isset( $filters_in[ 0 ] ) ) {
      switch( "{$filters_in[ 0 ]}" ) {
        case '&&':
        case '||':
        case '!':
          // filters_in[ 0 ] is boolean operator - copy and skip it:
          $fnew[ 0 ] = $filters_in[ 0 ];
          unset( $filters_in[ 0 ] );
          break;
        case '>':
        case '>=':
        case '<':
        case '<=':
        case '=':
        case '!=':
        case '~=':
        case '':
          // $filters is an atom - copy and keep a reference:
          $fnew = $filters_in;
          $atoms[ 0 ] = & $fnew;
          return $rv;
      }
    }
    // prettydump( $filters_in, 'sql_canonicalize_filters: array in:' );
    foreach( $filters_in as $key => $cond ) {
      // prettydump( array( $key => $cond), 'sql_canonicalize_filters: handling part:' );
      if( is_numeric( $key ) ) {
        $f = sql_canonicalize_filters_rec( $cond );
        $fnew[] = & $f['filters'];
        for( $n = 0; $n < count( $f['atoms'] ); $n++ ) {
          $atoms[] = & $f['atoms'][ $n ];
        }
        unset( $f );
      } else {
        $lhs = split( ' ', $key );
        $key = $lhs[ 0 ];
        $f = array( adefault( $lhs, 1, '=' ), $key, $cond );
        $fnew[] = & $f;
        $atoms[] = & $f;
        unset( $f );
      }
    }
    // prettydump( $rv, 'sql_canonicalize_filters: array out:' );
    return $rv;
  }
  error( 'cannot handle filters' );
}

// sql_filters2expression:
//  - turn $filters into an sql filter expression
//  - $filters must be canonicalized before calling this function
//
function sql_filters2expression( $can_filters ) {
  need( ! $can_filters['unhandled_atoms'], 'unhandled atoms in expression' );
  return sql_filters2expression_rec( $can_filters['filters'] );
}

function sql_filters2expression_rec( $filters ) {

  $op = 'AND';
  $sql = '';
  if( isset( $filters[ 0 ] ) ) {
    switch( "{$filters[ 0 ]}" ) {
      case '&&':
        $op = 'AND';
        unset( $filters[ 0 ] );
        break;
      case '||':
        $op = 'OR';
        unset( $filters[ 0 ] );
        break;
      case '!':
        $sql = 'NOT';
        $op = '';
        unset( $filters[ 0 ] );
        break;
      case '>':
      case '>=':
      case '<':
      case '<=':
      case '=':
      case '!=':
      case '~=':
      case '':
        // filters is an atom:
        $op = $filters[ 0 ];
        $rhs = $filters[ 2 ];
        if( $op === '~=' )
          $op = 'RLIKE';
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
              error( "cannot handle compare list with operator $op" );
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
        }
        $sql = sprintf( "%s %s %s", $filters[ 1 ], $op, $rhs );
        // prettydump( $sql, 'sql_filters2expression: atom:' );
        return $sql;
    }
  }
  if( ! $filters ) {
    // prettydump( $op, 'sql_filters2expression: empty:' );
    switch( "$op" ) {
      case 'AND';
        return 'TRUE';
      case 'OR':
      default:
        return 'FALSE';
    }
  }
  foreach( $filters as $f ) {
    if( $sql )
      $sql .= $op;
    $sql .= ' ( ' . sql_filters2expression_rec( $f ) . ' ) ';
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



// sql_default_selects():
// return SELECT clauses for all colums in all given tables:
//  $table is either string (table name) or array of table names
//  $disambiguation can be
//  - string: (to be used as a prefix for all columns)
//  - array: 
//    - every key is of the form 'column' or 'table.column'
//    - value is either a unique identifier for this column, or FALSE to skip this column entirely
//
function sql_default_selects( $table, $disambiguation = array() ) {
  global $tables;
  $selects = array();
  if( is_array( $table ) ) {
    foreach( $table as $t ) {
      $selects = array_merge( $selects, sql_default_selects( $t, $disambiguation ) );
    }
    return $selects;
  }
  $cols = $tables[$table]['cols'];
  foreach( $cols as $name => $type ) {
    if( is_string( $disambiguation ) ) {
      $selects[] = "$table.$name as $disambiguation$name";
    } else if( isset( $disambiguation[$name] ) ) {
      if( $disambiguation[$name] ) {
        $selects[] = "$table.$name as ".$disambiguation[$name];
      }
    } else if( isset( $disambiguation["$table.$name"] ) ) {
      if( $disambiguation["$table.$name"] ) {
        $selects[] = "$table.$name as ".$disambiguation["$table.$name"];
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
  is_array( $using ) or $using = array( $using );
  foreach( $rules as $table => $f ) {
    if( in_array( $table, $using ) ) {
      $filters = array_merge( $filters, $f );
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
  is_array( $using ) or $using = array( $using );
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
// function sql_select_single_row( $table, $filters, $selects = '', $joins = '', $orderby = false, $groupby = false ) {
//   return sql_do_single_row( sql_query( 'SELECT', $table, $filters, $selects, $joins, $orderby, $groupby ) );
// }
// function sql_select_single_field( $table, $filters, $fieldname, $joins = '', $orderby = false, $groupby = false ) {
//   return sql_do_single_field( sql_query( 'SELECT', $table, $filters, $selects, $joins, $orderby, $groupby ), $fieldname );
// }


function sql_delete( $table, $filters = false ) {
  $cf = sql_canonicalize_filters( $table, $filters );
  return sql_do( "DELETE FROM $table WHERE " . sql_filters2expression( $cf ) );
}

function sql_update( $table, $filters, $values, $escape_and_quote = true ) {
  switch( $table ) {
    case 'leitvariable':
    case 'transactions':
    case 'logbook':
    case 'sessions':
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
  return sql_do( $sql, LEVEL_IMPORTANT, "failed to update table $table: " );
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
      $sql .= "$update_komma {$table}_id = LAST_INSERT_ID({$table}_id) ";
  }
  if( sql_do( $sql, LEVEL_IMPORTANT, "failed to insert into table $table: "  ) )
    return mysql_insert_id();
  else
    return FALSE;
}



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


// generic versions of functions to access individual tables:
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
  function sql_person( $filters, $allow_null = false ) {
    $sql = sql_query_people( 'SELECT', $filters, array(), 'people.cn' );
    return sql_do_single_row( $sql, $allow_null );
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

function sql_store_persistent_vars( $sessions_id, $vars, $thread = '', $script = '', $window = '', $self = 0 ) {
  // prettydump( array( $sessions_id, $vars, $thread, $script, $window, $self ), 'sql_store_persistent_vars' );
  $filters = array(
    'sessions_id' => $sessions_id
  , 'thread' => $thread
  , 'script' => $script
  , 'window' => $window
  , 'self' => $self
  );
  if( $window || $self || $script ) {
    sql_delete( 'sessionvars', $filters );
  }
  foreach( $vars as $name => $value ) {
    if( $value === NULL ) {
      sql_delete( 'sessionvars', $filters + array( 'name' => $name ) );
    } else {
      sql_insert( 'sessionvars'
      , $filters + array( 'name' => $name , 'value' => $value )
      , array( 'value' => true )
      );
    }
  }
}

function sql_retrieve_persistent_vars( $sessions_id, $thread = '', $script = '', $window = '', $self = 0 ) {
  $sql = sql_query( 'SELECT', 'sessionvars', array(
      'sessions_id' => $sessions_id
    , 'thread' => $thread
    , 'script' => $script
    , 'window' => $window
    , 'self' => $self
  ) );
  // prettydump( array( $sessions_id, $thread, $script, $window, $self ), 'sql_retrieve_persistent_vars' );
  return mysql2array( sql_do( $sql ), 'name', 'value' );
}

?>
