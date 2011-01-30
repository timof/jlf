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
  print_on_exit( "<!-- sql_do: $sql -->" );
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
//    if array: return this as default row
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
  $n = 1;
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
  $cols = $tables[$tablename]['cols'];
  foreach( $cols as $fieldname => $c ) {
    $row[$fieldname] = $c['default'];
  }
  return $row;
}

function row2global( $tablename, $row = false, $prefix = '' ) {
  global $tables;
  if( $prefix && is_string( $prefix ) ) {
    $prefix = $prefix . '_';
  }
  $cols = $tables[$tablename]['cols'];
  foreach( $cols as $fieldname => $c ) {
    if( is_string( $prefix ) ) {
      $GLOBALS[ $prefix.$fieldname ] = adefault( $row, $fieldname, $c['default'] );
    } else if( isset( $prefix[$fieldname] ) ) {
      $GLOBALS[ $prefix[$fieldname] ] = adefault( $row, $fieldname, $c['default'] );
    } else {
      $GLOBALS[ $fieldname ] = adefault( $row, $fieldname, $c['default'] );
    }
  }
}

///////////////////////////////////////////
// functions to compile query strings:
//

// turn $key and $cond into a boolean sql expression, using some heuristics.
//
function sql_cond2expression( $key, $cond ) {
  if( $cond === NULL )
    return 'true';
  if( is_numeric( $key ) ) {   // assume $cond is a complete boolean expression
    return $cond;
  } else {
    if( is_array( $cond ) ) {
      $f = "$key IN ";
      $komma = '(';
      foreach( $cond as $c ) {
        $f .= "$komma '".mysql_real_escape_string($c)."'";
        $komma = ',';
      }
      return $f . ') ';
    } else {         // assume we need a '=':
      return "$key = '".mysql_real_escape_string($cond)."'";
    }
  }
}

function sql_filters2expression( $filters ) {
  // print_on_exit( "<!-- sql_filters2expression: " .var_export( $filters, true ). " -->" );
  // echo "sql_filters2expression: ";
  // prettydump( $filters );
  if( is_string( $filters ) ) {
    // print_on_exit( "<!-- sql_filters2expression: string: $filters -->" );
    return " ( $filters ) ";
  } else if( $filters ) {
    // print_on_exit( "<!-- sql_filters2expression: array -->" );
    $and = '';
    $query = '';
    foreach( $filters as $key => $cond ) {
      $query .= " $and ( ". sql_cond2expression( $key, $cond ) ." ) ";
      $and = 'AND';
    }
    // print_on_exit( "<!-- sql_filters2expression: query: $query -->" );
    return $query;
  } else {
    return " true ";
  }
}

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

// sql_canonicalize_filters:
//   - turn $filters into an array
//   - prefix fieldnames with table name, where possible
//   - allow shortcut: integer means primary key
// (this is to handle simple default cases in table-specific functions)
//
function sql_canonicalize_filters( $table, $filters ) {
  global $tables;
  $cols = $tables[$table]['cols'];

  if( ! $filters )
    return array();
  if( is_numeric( $filters ) ) {  // guess: is primary key
    if( isset( $cols[$table.'_id'] ) ) {
      return array( "{$table}.{$table}_id" => $filters );
    }
  }
  if( is_string( $filters ) ) {
    $pairs = explode( ',', $filters );
    $filters = array();
    foreach( $pairs as $pair ) {
      $v = explode( '=', $pair );
      if( $v[1] == '' )
        $filters[] = $v[0];
      else
        $filters[$v[0]] = $v[1];
    }
  }
  if( is_array( $filters ) ) {
    // print_on_exit( "<!-- sql_canonicalize_filters: in: " .var_export( $filters, true ). " -->" );
    $fnew = array();
    foreach( $filters as $key => $cond ) {
      // print_on_exit( "<!-- sql_canonicalize_filters: [$fieldname, $cond] -->" );
      if( "$key" == 'id' )
        $key = $table.'_id';
      if( isset( $cols[$key] ) )
        $key = "$table.$key";
      $fnew[$key] = $cond;
    }
    // print_on_exit( "<!-- sql_canonicalize_filters: out: " .var_export( $fnew, true ). " -->" );
    return $fnew;
  }
  prettydump( $filters );
  error( 'hitting end of canonicalize_filters()' );
}


/*
 * use_filters: to be used in scalar subqueries as in "SELECT x , ( SELECT ... ) as y, z":
 *  generate optional filters refering to tables already available from outer context
 */
function use_filters_array( $using, $rules ) {
  $filters = array();
  is_array( $using ) or $using = array( $using );
  foreach( $rules as $table => $f ) {
    if( in_array( $table, $using ) ) {
      $filters = array_merge( $filters, $f );
    }
  }
  return $filters;
}
function use_filters( $using, $rules ) {
  $filters_array = use_filters_array( $using, $rules );
  return sql_filters2expression( $filters_array );
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
      $joins .= " JOIN $rule ";
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


function orderby_string2sql( $defaults, $string ) {
  if( is_string( $defaults ) )
    $defaults = parameters_explode( $defaults );
  $sql = '';
  $comma = '';
  if( $string )
    $a = explode( ',', $string );
  else
    $a = array();
  foreach( $defaults as $key => $value ) {
    if( is_numeric( $key ) ) {
      $defaults[$value] = $value;
      unset( $defaults[$key] );
    }
  }
  foreach( $a as $i => $key ) {
    $reverse = preg_match( '/-R$/', $key );
    if( $reverse )
      $key = preg_replace( '/-R$/', '', $key );
    need( isset( $defaults[$key] ), "undefined orderby keyword: $key" );
    $expression = $defaults[$key];
    if( $reverse ) {
      if( preg_match( '/ DESC$/', $expression ) )
        $expression = preg_replace( '/ DESC$/', '', $expression );
      else
        $expression = "$expression DESC";
    }
    $sql .= "$comma $expression";
    $comma = ',';
    unset( $defaults[$key] );
  }
  foreach( $defaults as $key => $expression ) {
    $sql .= "$comma $expression";
    $comma = ',';
  }
  return $sql;
}

function orderby_join( $string, $new ) {
  if( ! $string )
    return $new;
  $a = explode( ',', $string );
  if( $a[0] == $new ) {
    $a[0] = "$new-R";
    $a_new = $a;
  } else if( $a[0] == "$new-R" ) {
    $a[0] = $new;
    $a_new = $a;
  } else {
    $a_new[] = $new;
    foreach( $a as $i => $key ) {
      if( $key == $new || $key == "$new-R" )
        continue;
      $a_new[] = $key;
    }
  }
  return implode( ',', $a_new );
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
  foreach( $rows as $r ) {   // fake unique ids
    $a[ md5( $r[$column] ) ] = $r[$column];
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
  need( isset( $rows[$id] ), "$table.$column: no value matching id $id" );
  return $rows[$id];
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
  $joins = array( $table_1 => $table_1.'_id', $table_1 => $table_2.'_id' );
  $selects = array( $table_relation.'.'.$table_1.'_id', $table_relation.'.'.$table_2.'_id' );
  $orderby = $table_relation.'.'.$table_1.'_id, '.$table_relation.'.'.$table_2.'_id';
  $sql = sql_query( 'SELECT', $table_relation, $filters_1 + $filters_2, $selects, $joins );
  $relation = mysql2array( sql_do( $sql ) );
  return $relation;
}

function sql_relation_on( $table_1, $table_2, $table_relation, $id_1, $id_2 ) {
  $values = array( $table_1.'_id' => $id_1 , $table_2.'_id' => $id_2 );
  return sql_insert( $table_relation, $values );
}

function sql_relation_off( $table_1, $table_2, $table_relation, $id_1, $id_2 ) {
  $values = array( $table_1.'_id' => $id_1 , $table_2.'_id' => $id_2 );
  return sql_insert( $table_relation, $values );
}


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
    // print_on_exit( "<!-- auth_check_password: 1: $people_id -->" );
    $allowed = explode( ',', $allowed_authentication_methods );
    if( ! in_array( 'simple', $allowed ) )
      return false;
    if( ! $people_id )
      return false;
    if( ! $password )
      return false;
    $person = sql_person( $people_id );
    $auth_methods = explode( ',', $person['authentication_methods'] );
    if( ! in_array( 'simple', $auth_methods ) )
      return false;
    switch( $person['password_hashfunction'] ) {
      case 'crypt':
        $c = crypt( $password, $person['password_salt'] );
        // print_on_exit( "<!-- auth_check_password: 3: $c -->" );
        return ( $person['password_hashvalue'] == crypt( $password, $person['password_salt'] ) );
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
  $filters = array(
    'sessions_id' => $sessions_id
  , 'thread' => $thread
  , 'script' => $script
  , 'window' => $window
  , 'self' => $self
  );
  if( $window || $self ) {
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
  return mysql2array( sql_do( $sql ), 'name', 'value' );
}

?>
