<?php

////////////////////////////////////
//
// debugging und fehlerbehandlung:
//
////////////////////////////////////


define('LEVEL_NEVER', 5);
define('LEVEL_ALL', 4);
define('LEVEL_MOST', 3);
define('LEVEL_IMPORTANT', 2); // all UPDATE and INSERT statements should have level important
define('LEVEL_KEY', 1);
define('LEVEL_NONE', 0);

// LEVEL_CURRENT: alle sql-aufrufe bis zu diesem level werden angezeigt:
$_SESSION['LEVEL_CURRENT'] = LEVEL_NONE;

function sql_selects( $table, $prefix = false ) {
  global $tables;
  $cols = $tables[$table]['cols'];
  $selects = array();
  foreach( $cols as $name => $type ) {
    if( $name == 'id' ) {
      if( isstring( $prefix ) )
        $selects[] = "$table.id as {$prefix}id";
      else
        $selects[] = "$table.id as $table_id";
    } else {
      if( isstring( $prefix ) )
        $selects[] = "$table.$name as $prefix$name";
      else if( $prefix )
        $selects[] = "$table.$name as $table_$name";
      else
        $selects[] = "$table.$name as $name";
    }
  }
  return $selects;
}

function doSql( $sql, $debug_level = LEVEL_IMPORTANT, $error_text = "Datenbankfehler: " ) {
  if($debug_level <= $_SESSION['LEVEL_CURRENT']) {
    open_div( 'alert', '', htmlspecialchars( $sql ) );
  }
  $result = mysql_query($sql);
  if( ! $result ) {
    error( $error_text. "\n  query: $sql\n  MySQL-error: " . mysql_error() );
  }
  return $result;
}

// turn $key and $cond into a boolean sql expression, using some heuristics.
//
function cond2filter( $key, $cond ) {
  if( $cond === NULL )
    return ' true ';
  if( is_numeric( $key ) ) {   // assume $cond is a complete boolean expression
    return " $cond ";
  } else {
    if( is_array( $cond ) ) {
      $f = "$key IN ";
      $komma = '(';
      foreach( $cond as $c ) {
        $f .= "$komma '$c'";
        $komma = ',';
      }
      return $f . ') ';
    } else if( strchr( $cond, ' ' ) ) {  // assume $cond contains an operator
      return " $key $cond ";
    } else {                      // assume we need a '=':
      return " $key = '$cond' ";
    }
  }
}

function get_sql_query( $op, $table, $selects = '*', $joins = '', $filters = false, $orderby = false, $groupby = false ) {
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
  $query = "$op $select_string FROM $table $join_string";
  if( $filters ) {
    if( is_string( $filters ) ) {
      $query .= " WHERE ( $filters ) ";
    } else {
      $and = 'WHERE';
      foreach( $filters as $key => $cond ) {
        $query .= " $and (". cond2filter( $key, $cond ) .") ";
        $and = 'AND';
      }
    }
  }
  if( $groupby ) {
    $query .= " GROUP BY $groupby ";
  }
  if( $orderby ) {
    $query .= " ORDER BY $orderby ";
  }
  return $query;
}

function select_query( $table, $selects = '*', $joins = '', $filters = false, $orderby = false ) {
  return get_sql_query( 'SELECT', $table, $selects, $joins, $filters, $orderby );
}

// function delete_query( $table, $what = '*', $joins = '', $filters = false ) {
//   return get_sql_query( 'DELETE', $table, $what, $joins, $filters, $orderby );
// }


function sql_select_single_row( $sql, $allownull = false ) {
  $result = doSql( $sql );
  $rows = mysql_num_rows($result);
  // echo "<br>$sql<br>rows: $rows<br>";
  if( $rows == 0 ) {
    if( is_array( $allownull ) )
      return $allownull;
    if( $allownull )
      return NULL;
  }
  need( $rows > 0, "Kein Treffer bei Datenbanksuche: $sql" );
  need( $rows == 1, "Ergebnis der Datenbanksuche $sql nicht eindeutig ($rows)" );
  return mysql_fetch_array($result);
}

function sql_select_single_field( $sql, $field, $allownull = false ) {
  $row = sql_select_single_row( $sql, $allownull );
  if( ! $row )
    return NULL;
  if( isset( $row[$field] ) )
    return $row[$field];
  need( $allownull );
  return NULL;
}

function sql_count( $table, $where ) {
  return sql_select_single_field(
    "SELECT count(*) as count FROM $table WHERE $where"
  , 'count'
  );
}

function sql_delete( $table, $where = 'true' ) {
  $sql = "DELETE FROM $table WHERE ";
  if( is_array( $where ) ) {
    $and = '';
    foreach( $where as $field => $val ) {
      if( $escape_and_quote )
        $val = "'" . mysql_real_escape_string($val) . "'";
      $sql .= " $and ($field=$val) ";
      $and = 'AND';
    }
  } else if( $where === true ) {
    $sql .= " 1 ";
  } else {
    $sql .= " {$table}_id=$where";
  }
  return doSql( $sql );
}


function sql_update( $table, $where, $values, $escape_and_quote = true ) {
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
  if( is_array( $where ) ) {
    $and = 'WHERE';
    foreach( $where as $field => $val ) {
      if( $escape_and_quote )
        $val = "'" . mysql_real_escape_string($val) . "'";
      $sql .= " $and ($field=$val) ";
      $and = 'AND';
    }
  } else {
    $sql .= " WHERE {$table}_id=$where";
  }
  if( doSql( $sql, LEVEL_IMPORTANT, "Update von Tabelle $table fehlgeschlagen: " ) )
    return $where;
  else
    return FALSE;
}

function sql_insert( $table, $values, $update_cols = false, $escape_and_quote = true ) {
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
        if( $update_cols[$key] ) {
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
    $sql .= " ON DUPLICATE KEY UPDATE $update $update_komma {$table}_id = LAST_INSERT_ID({$table}_id) ";
  }
  if( doSql( $sql, LEVEL_IMPORTANT, "Einfügen in Tabelle $table fehlgeschlagen: "  ))
    return mysql_insert_id();
  else
    return FALSE;
}

function logger( $notiz ) {
  global $session_id;
  return sql_insert( 'logbook', array( 'notiz' => $notiz, 'session_id' => $session_id ) );
}

function adefault( $array, $index, $default ) {
  if( isset( $array[$index] ) )
    return $array[$index];
  else
    return $default;
}

function mysql2array( $result, $key = false, $val = false ) {
  if( is_array( $result ) )  // temporary kludge: make me idempotent
    return $result;
  $r = array();
  $n = 1;
  while( $row = mysql_fetch_array( $result ) ) {
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


/*
 * need_joins: fuer skalare subqueries wie in "SELECT x , ( SELECT ... ) as y, z":
 *  erzeugt aus $rules JOIN-anweisungen fuer benoetigte tabellen; in $using koennen
 *  tabellen uebergeben werden, die bereits verfuegbar sind
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
      $params = explode( ' ', $table );
      foreach( $params as $p ) {
        switch( $p ) {
          case 'LEFT':
            $joins .= " LEFT ";
            break;
          default:
            $joins .= " JOIN $p ON $rule ";
            break;
        }
      }
    }
  }
  return $joins;
}

/*
 * use_filters: fuer skalare subqueries wie in "SELECT x , ( SELECT ... ) as y, z":
 *  erzeugt optionale filterausdruecke, die bereits verfuegbare tabellen benutzen
 */
function use_filters_array( $using, $rules ) {
  $filters = array();
  is_array( $using ) or $using = array( $using );
  foreach( $rules as $table => $f ) {
    if( in_array( $table, $using ) ) {
      $filters[] = $f;
    }
  }
  return $filters;
}
function use_filters( $using, $rules ) {
  $filters = '';
  $filters_array = use_filters_array( $using, $rules );
  foreach( $filters_array as $f ) {
    $filters .= " AND ( $f ) ";
  }
  return $filters;
}


////////////////////////////////////
//
// host-funktionen:
//
////////////////////////////////////


function query_hosts( $op, $keys = array(), $using = array(), $orderby = false ) {
  $selects = array();
  $filters = array();
  $joins = array();

  $selects[] = 'hosts.hosts_id';
  $selects[] = 'hosts.fqhostname';
  $selects[] = 'hosts.sequential_number';
  $selects[] = 'hosts.ip4';
  $selects[] = 'hosts.ip6';
  $selects[] = 'hosts.oid';
  $selects[] = 'hosts.invlabel';
  $selects[] = 'hosts.processor';
  $selects[] = 'hosts.os';
  $selects[] = 'hosts.location';
  $selects[] = "LEFT( hosts.fqhostname, LOCATE( '.',  hosts.fqhostname ) - 1 ) as hostname";
  $selects[] = "SUBSTR( hosts.fqhostname, LOCATE( '.', hosts.fqhostname ) + 1 ) as domain";
  $selects[] = " ( SELECT count(*) FROM disks WHERE disks.hosts_id = hosts.hosts_id ) as disks_cnt ";
  $selects[] = " ( SELECT count(*) FROM services WHERE services.hosts_id = hosts.hosts_id ) as services_cnt ";
  $selects[] = " ( SELECT count(*) FROM users WHERE users.hosts_id = hosts.hosts_id ) as users_cnt ";
  $selects[] = " IFNULL( ( SELECT GROUP_CONCAT( accountdomain SEPARATOR ' ' )
                          FROM accountdomains_hosts_relation JOIN accountdomains USING (accountdomains_id)
                          WHERE accountdomains_hosts_relation.hosts_id = hosts.hosts_id ), ' - ' ) as accountdomains ";

  foreach( $keys as $key => $cond ) {
    switch( $key ) {
      case 'id':
      case 'hosts_id':
        $filters['hosts.hosts_id'] = $cond;
        break;
      case 'disks_id':
        $joins[] = "disks USING (hosts_id)";
        $selects[] = "disks.disks_id";
        $filters['disks.disks_id'] = $cond;
        break;
      case 'services_id':
        $joins[] = "services USING (hosts_id)";
        $selects[] = "services.services_id";
        $filters['services.services_id'] = $cond;
        break;
      case 'location':
        $filters['hosts.location'] = $cond;
        break;
      case 'fqhostname':
        $filters['hosts.fqhostname'] = $cond;
        break;
      case 'accountdomain':
        $joins[] = "accountdomains_hosts_relation USING ( hosts_id )";
        $joins[] = "accountdomains USING ( accountdomains_id )";
        $filters['accountdomains.accountdomain'] = $cond;
        break;
      case 'where':
        $filters = $cond;
        break;
      default:
        error( "undefined key: $key" );
    }
  }
  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as cnt';
      break;
    default:
      error( "undefined op: $op" );
  }
  switch( $orderby ) {
    case 'invlabel':
      $orderby = ' LEFT( invlabel,1) , CONVERT( SUBSTR(invlabel,2) , UNSIGNED) ';
  }
  return get_sql_query( $op, 'hosts', $selects, $joins, $filters, $orderby, 'hosts.hosts_id' );
}
function select_hosts( $keys = array(), $using = array(), $orderby = false ) {
  return query_hosts( 'SELECT', $keys, $using, $orderby );
}

function sql_hosts( $keys = array(), $orderby = 'fqhostname' ) {
  return mysql2array( doSql( select_hosts( $keys, array(), $orderby ) ) );
}

function sql_host( $keys, $allow_null = false ) {
  if( ! is_array( $keys ) ) {
    $keys = array( 'hosts_id' => $keys );
  }
  return sql_select_single_row( select_hosts( $keys ), $allow_null );
}

function sql_fqhostname( $hosts_id ) {
  return sql_select_single_field( select_hosts( array( 'hosts_id' => $hosts_id ) ), 'fqhostname' );
}

function sql_delete_host( $hosts_id ) {
  sql_update( 'disks', array( 'hosts_id' => $hosts_id ), array( 'hosts_id' => 0 ) );
  sql_update( 'services', array( 'hosts_id' => $hosts_id ), array( 'hosts_id' => 0 ) );
  sql_delete( 'hosts', array( 'hosts_id' => $hosts_id ) );
}


function options_hosts(
  $selected = 0
, $keys = array()
, $option_0 = false
) {
  $output='';
  if( $option_0 ) {
    $output = "<option value='0'";
    if( $selected == 0 ) {
      $output = $output . " selected";
      $selected = -1;
    }
    $output = $output . ">$option_0</option>";
  }
  foreach( sql_hosts( $keys ) as $host ) {
    $id = $host['hosts_id'];
    $output = "$output
      <option value='$id'";
    if( $selected == $id ) {
      $output = $output . " selected";
      $selected = -1;
    }
    $output = $output . "> {$host['fqhostname']} </option>";
  }
  if( $selected >=0 ) {
    // $selected stand nicht zur Auswahl; vermeide zufaellige Anzeige:
    $output = "<option value='0' selected>(select host)</option>" . $output;
  }
  return $output;
}


function sql_locations() {
  return mysql2array( doSql( "select distinct location from hosts order by location" ) );
}

function options_locations(
  $selected = 0
, $option_0 = false
) {
  $output='';
  if( $option_0 ) {
    $output = "<option value='0'";
    if( $selected == 0 ) {
      $output = $output . " selected";
      $selected = -1;
    }
    $output = $output . ">$option_0</option>";
  }
  foreach( sql_locations() as $l ) {
    $l = $l['location'];
    $output = "$output
      <option value='$l'";
    if( $selected == $l ) {
      $output = $output . " selected";
      $selected = -1;
    }
    $output = $output . "> $l </option>";
  }
  if( $selected >=0 ) {
    // $selected stand nicht zur Auswahl; vermeide zufaellige Anzeige:
    $output = "<option value='0' selected>(select location)</option>" . $output;
  }
  return $output;
}


////////////////////////////////////
//
// disk-funktionen:
//
////////////////////////////////////

function query_disks( $op, $keys = array(), $using = array(), $orderby = false ) {
  $selects = array();
  $filters = array();
  $joins = array();
  $joins['LEFT hosts'] = "disks.hosts_id = hosts.hosts_id";
  $joins['LEFT systems'] = "disks.systems_id = systems.systems_id";

  $selects[] = 'disks.disks_id';
  $selects[] = 'disks.oid';
  $selects[] = 'disks.sizeGB';
  $selects[] = 'disks.cn';
  $selects[] = 'disks.type_disk';
  $selects[] = 'disks.description';
  $selects[] = 'disks.hosts_id';
  $selects[] = 'disks.systems_id';
  $selects[] = 'ifnull( hosts.location, disks.location ) as location';
  $selects[] = 'hosts.fqhostname';
  $selects[] = 'systems.type as systems_type';
  $selects[] = 'systems.arch as systems_arch';
  $selects[] = 'systems.date_built as systems_date_built';

  foreach( $keys as $key => $cond ) {
    switch( $key ) {
      case 'id':
      case 'disks_id':
        $filters['disks.disks_id'] = $cond;
        break;
      case 'systems_id':
        $filters['disks.systems_id'] = $cond;
        break;
      case 'hosts_id':
        $filters['disks.hosts_id'] = $cond;
        break;
      case 'where':
        $filters = $cond;
        break;
      default:
          error( "undefined key: $key" );
    }
  }
  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as cnt';
      break;
    default:
      error( "undefined op: $op" );
  }
  return get_sql_query( $op, 'disks', $selects, $joins, $filters, $orderby, 'disks.disks_id' );
}
function select_disks( $keys = array(), $using = array(), $orderby = false ) {
  return query_disks( 'SELECT', $keys, $using, $orderby );
}

function sql_disks( $keys = array(), $orderby = 'cn' ) {
  return mysql2array( doSql( select_disks( $keys, array(), $orderby ) ) );
}

function sql_disk( $disks_id, $allow_null = false ) {
  return sql_select_single_row( select_disks( array( 'disks_id' => $disks_id ) ), $allow_null );
}

function sql_delete_disk( $disks_id ) {
  sql_delete( 'disks', array( 'disks_id' => $disks_id ) );
}

function options_type_disk(
  $selected = 0
, $option_0 = false
) {
  $output='';
  if( $option_0 ) {
    $output = "<option value='0'";
    if( $selected == 0 ) {
      $output = $output . " selected";
      $selected = -1;
    }
    $output = $output . ">$option_0</option>";
  }
  foreach( array( 'P-ATA', 'P-SCSI', 'S-ATA', 'SAS' ) as $t ) {
    $output = "$output
      <option value='$t'";
    if( $selected == $t ) {
      $output = $output . " selected";
      $selected = -1;
    }
    $output = $output . "> $t </option>";
  }
  if( $selected >=0 ) {
    $output = "<option value='0' selected>(select type)</option>" . $output;
  }
  return $output;
}

////////////////////////////////////
//
// tape-funktionen:
//
////////////////////////////////////

function query_tapes( $op, $keys = array(), $using = array(), $orderby = false ) {
  $selects = array();
  $filters = array();
  $joins = array();

  $selects[] = 'tapes.*';

  foreach( $keys as $key => $cond ) {
    switch( $key ) {
      case 'id':
      case 'tapes_id':
        $filters['tapes.tapes_id'] = $cond;
        break;
      case 'where':
        $filters = $cond;
        break;
      default:
          error( "undefined key: $key" );
    }
  }
  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as cnt';
      break;
    default:
      error( "undefined op: $op" );
  }
  return get_sql_query( $op, 'tapes', $selects, $joins, $filters, $orderby, 'tapes.tapes_id' );
}
function select_tapes( $keys = array(), $using = array(), $orderby = false ) {
  return query_tapes( 'SELECT', $keys, $using, $orderby );
}

function sql_tapes( $keys = array(), $orderby = 'cn' ) {
  return mysql2array( doSql( select_tapes( $keys, array(), $orderby ) ) );
}

function sql_tape( $tapes_id, $allow_null = false ) {
  return sql_select_single_row( select_tapes( array( 'tapes_id' => $tapes_id ) ), $allow_null );
}

function sql_delete_tape( $tapes_id ) {
  sql_delete( 'tapes', array( 'tapes_id' => $tapes_id ) );
}

function options_type_tape(
  $selected = 0
, $option_0 = false
) {
  $output='';
  if( $option_0 ) {
    $output = "<option value='0'";
    if( $selected == 0 ) {
      $output = $output . " selected";
      $selected = -1;
    }
    $output = $output . ">$option_0</option>";
  }
  foreach( array( 'DDS-2', 'DDS-3', 'DDS-4', 'SDLT-320', 'LTO-3', 'LTO-4' ) as $t ) {
    $output = "$output
      <option value='$t'";
    if( $selected == $t ) {
      $output = $output . " selected";
      $selected = -1;
    }
    $output = $output . "> $t </option>";
  }
  if( $selected >=0 ) {
    $output = "<option value='0' selected>(select type)</option>" . $output;
  }
  return $output;
}


////////////////////////////////////
//
// service-funktionen:
//
////////////////////////////////////

define( TYPE_SERVICE_HTTP, 10 );
define( TYPE_SERVICE_HTTPS, 11 );
define( NTP, 20 );
define( DNS, 30 );
define( LPR, 40 );

function query_services( $op, $keys = array(), $using = array(), $orderby = false ) {
  $selects = array();
  $filters = array();
  $joins = array( 'LEFT hosts' => 'hosts.hosts_id = services.hosts_id' );

  $selects[] = 'services.services_id';
  $selects[] = 'services.type_service';
  $selects[] = 'services.description';
  $selects[] = 'services.url';
  $selects[] = 'services.hosts_id';

  foreach( $keys as $key => $cond ) {
    switch( $key ) {
      case 'id':
      case 'services_id':
        $filters['services.services_id'] = $cond;
        break;
      case 'hosts_id':
        $filters['services.hosts_id'] = $cond;
        break;
      case 'where':
        $filters = $cond;
        break;
      default:
          error( "undefined key: $key" );
    }
  }
  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as cnt';
      break;
    default:
      error( "undefined op: $op" );
  }
  return get_sql_query( $op, 'services', $selects, $joins, $filters, $orderby, 'services.services_id' );
}
function select_services( $keys = array(), $using = array(), $orderby = false ) {
  return query_services( 'SELECT', $keys, $using, $orderby );
}

function sql_services( $keys = array(), $orderby = 'type_service, description' ) {
  return mysql2array( doSql( select_services( $keys, array(), $orderby ) ) );
}

function sql_service( $services_id, $allow_null = false ) {
  return sql_select_single_row( select_services( array( 'services_id' => $services_id ) ), $allow_null );
}

function sql_delete_service( $services_id ) {
  sql_delete( 'services', array( 'services_id' => $services_id ) );
}


////////////////////////////////////
//
// user-funktionen:
//
////////////////////////////////////

function query_users( $op, $keys = array(), $using = array(), $orderby = false ) {
  $selects = array();
  $filters = array();
  $joins = array( 'LEFT hosts' => 'users.hosts_id = hosts.hosts_id' );

  $selects[] = 'users.users_id';
  $selects[] = 'users.uid';
  $selects[] = 'users.cn';
  $selects[] = 'users.uidnumber';
  $selects[] = 'users.hosts_id';
  $selects[] = 'hosts.fqhostname';
  $selects[] = " ( SELECT count(*) FROM accountdomains_users_relation WHERE accountdomains_users_relation.users_id = users.users_id ) as accountdomains_cnt ";
  $selects[] = " IFNULL( ( SELECT GROUP_CONCAT( accountdomain SEPARATOR ' ' )
                          FROM accountdomains_users_relation JOIN accountdomains USING (accountdomains_id)
                          WHERE accountdomains_users_relation.users_id = users.users_id ), ' - ' ) as accountdomains ";

  foreach( $keys as $key => $cond ) {
    switch( $key ) {
      case 'id':
      case 'users_id':
        $filters['users.users_id'] = $cond;
        break;
      case 'uid':
        $filters['users.uid'] = $cond;
        break;
      case 'uidnumner':
        $filters['users.uidnumber'] = $cond;
        break;
      case 'hosts_id':
        $filters['users.hosts_id'] = $cond;
        break;
      case 'accountdomain':
        $joins['accountdomains_users_relation'] = 'accountdomains_users_relation.users_id = users.users_id';
        $joins['accountdomains'] = 'accountdomains.accountdomains_id = accountdomains_users_relation.accountdomains_id';
        $filters['accountdomains.accountdomain'] = $cond;
        break;
      case 'accountdomains_id':
        $joins['accountdomains_users_relation'] = 'accountdomains_users_relation.users_id = users.users_id';
        $joins['accountdomains'] = 'accountdomains.accountdomains_id = accountdomains_users_relation.accountdomains_id';
        $filters['accountdomains.accountdomains_id'] = $cond;
        break;
      case 'where':
        $filters = $cond;
        break;
      default:
        error( "undefined key: $key" );
    }
  }
  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as cnt';
      break;
    default:
      error( "undefined op: $op" );
  }
  return get_sql_query( $op, 'users', $selects, $joins, $filters, $orderby, 'users.users_id' );
}
function select_users( $keys = array(), $using = array(), $orderby = false ) {
  return query_users( 'SELECT', $keys, $using, $orderby );
}

function sql_users( $keys = array(), $orderby = 'uid' ) {
  return mysql2array( doSql( select_users( $keys, array(), $orderby ) ) );
}

function sql_user( $users_id, $allow_null = false ) {
  return sql_select_single_row( select_users( array( 'users_id' => $users_id ) ), $allow_null );
}

function sql_delete_users( $users_id ) {
  sql_delete( 'users', array( 'users_id' => $users_id ) );
}

////////////////////////////////////
//
// accountdomain-funktionen:
//
////////////////////////////////////

function query_accountdomains( $op, $keys = array(), $using = array(), $orderby = false ) {
  $selects = array();
  $filters = array();
  $joins = array();

  $selects[] = 'accountdomains.accountdomains_id';
  $selects[] = 'accountdomains.accountdomain';
  $selects[] = " ( SELECT count(*) FROM accountdomains_users_relation
                   WHERE accountdomains_users_relation.accountdomains_id = accountdomains.accountdomains_id )
                   AS users_cnt ";
  $selects[] = " ( SELECT count(*) FROM accountdomains_hosts_relation
                   WHERE accountdomains_hosts_relation.accountdomains_id = accountdomains.accountdomains_id )
                   AS hosts_cnt ";

  foreach( $keys as $key => $cond ) {
    switch( $key ) {
      case 'id':
      case 'accountdomains_id':
        $filters['accountdomains.accountdomains_id'] = $cond;
        break;
      case 'accountdomain':
        $filters['accountdomains.accountdomain'] = $cond;
        break;
      case 'users_id':
        $joins['accountdomain_users_relation'] = 'accountdomain_users_relation.accountdomains_id = accountdomains.accountdomains_id';
        $filters['accoutndomain_users_relation.users_id'] = $cond;
        break;
      case 'hosts_id':
        $joins['accountdomain_hosts_relation'] = 'accountdomain_hosts_relation.accountdomains_id = accountdomains.accountdomains_id';
        $filters['accoutndomain_hosts_relation.hosts_id'] = $cond;
        break;
      case 'where':
        $filters = $cond;
        break;
      default:
        error( "undefined key: $key" );
    }
  }
  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as cnt';
      break;
    default:
      error( "undefined op: $op" );
  }
  return get_sql_query( $op, 'users', $selects, $joins, $filters, $orderby, 'users.users_id' );
}
function select_accountdomains( $keys = array(), $using = array(), $orderby = false ) {
  return query_accountdomains( 'SELECT', $keys, $using, $orderby );
}

function sql_accountdomains( $keys = array(), $orderby = 'accountdomain' ) {
  return mysql2array( doSql( select_accountdomains( $keys, array(), $orderby ) ) );
}



////////////////////////////////////
//
// systems-funktionen:
//
////////////////////////////////////

function query_systems( $op, $keys = array(), $using = array(), $orderby = false ) {
  $selects = array();
  $filters = array();
  $joins = array();

  $selects[] = 'systems.disks_id';
  $selects[] = 'systems.arch';
  $selects[] = 'systems.type';
  $selects[] = 'systems.date_built';
  $selects[] = 'systems.parent_systems_id';
  $selects[] = 'systems.description';
  $selects[] = '( SELECT COUNT(*) FROM disks WHERE disks.systems_id = systems.sysdems_id ) AS disks_cnt';

  foreach( $keys as $key => $cond ) {
    switch( $key ) {
      case 'id':
      case 'systems_id':
        $filters['systems.systems_id'] = $cond;
        break;
      case 'arch':
        $filters['systems.arch'] = $cond;
        break;
      case 'where':
        $filters = $cond;
        break;
      default:
          error( "undefined key: $key" );
    }
  }
  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $op = 'SELECT';
      $selects = 'COUNT(*) as cnt';
      break;
    default:
      error( "undefined op: $op" );
  }
  return get_sql_query( $op, 'systems', $selects, $joins, $filters, $orderby, 'systems.systems_id' );
}
function select_systems( $keys = array(), $using = array(), $orderby = false ) {
  return query_systems( 'SELECT', $keys, $using, $orderby );
}

function sql_systems( $keys = array(), $orderby = 'systems.type,systems.arch,systems.date_built' ) {
  return mysql2array( doSql( select_systems( $keys, array(), $orderby ) ) );
}

function sql_system( $systems_id, $allow_null = false ) {
  return sql_select_single_row( select_systems( array( 'systems_id' => $systems_id ) ), $allow_null );
}

function sql_delete_system( $disks_id ) {
  sql_delete( 'systems', array( 'systems_id' => $systems_id ) );
}


////////////////////////////////////
//
// HTML-funktionen:
//
////////////////////////////////////

// variablen die in URL (method='GET' oder in href-url) auftreten duerfen,
// mit typen:
//
$foodsoft_get_vars = array(
  'action' => 'w'
, 'accountdomain' => 'w'
, 'hosts_id' => 'u'
, 'disks_id' => 'u'
, 'tapes_id' => 'u'
, 'services_id' => 'u'
, 'confirmed' => 'w'
, 'detail' => 'w'
, 'login' => 'w'
, 'location' => '/[a-zA-Z0-9.]*/'
, 'options' => 'u'
, 'orderby' => 'w'
, 'window' => 'W'
, 'window_id' => 'w'
);

$http_input_sanitized = false;
function sanitize_http_input() {
  global $HTTP_GET_VARS, $HTTP_POST_VARS, $from_dokuwiki
       , $foodsoft_get_vars, $http_input_sanitized, $session_id;

  // if( ! $from_dokuwiki ) {
  foreach( $HTTP_GET_VARS as $key => $val ) {
    need( isset( $foodsoft_get_vars[$key] ), "unerwartete Variable $key in URL uebergeben" );
    need( checkvalue( $val, $foodsoft_get_vars[$key] ) !== false , "unerwarteter Wert fuer Variable $key in URL" );
  }
  if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
    need( isset( $HTTP_POST_VARS['itan'] ), 'foodsoft: fehlerhaftes Formular uebergeben' );
    sscanf( $HTTP_POST_VARS['itan'], "%u_%s", &$t_id, &$itan );
    need( $t_id, 'fehlerhaftes Formular uebergeben' );
    $row = sql_select_single_row( "SELECT * FROM transactions WHERE transactions_id=$t_id", true );
    need( $row, 'fehlerhaftes Formular uebergeben' );
    if( $row['used'] ) {
      // formular wurde mehr als einmal abgeschickt: POST-daten verwerfen:
      $HTTP_POST_VARS = array();
      echo "<div class='warn'>Warnung: mehrfach abgeschicktes Formular detektiert! (wurde nicht ausgewertet)</div>";
    } else {
      need( $row['itan'] == $itan, 'ungueltige iTAN uebergeben' );
      // echo "session_id: $session_id, from db: {$row['session_id']} <br>";
      need( $row['session_id'] == $session_id, 'ungueltige session_id' );
      // id ist noch unverbraucht: jetzt entwerten:
      sql_update( 'transactions', $t_id, array( 'used' => 1 ) );
    }
  } else {
    $HTTP_POST_VARS = array();
  }
  $http_input_sanitized = true;
  // }
}

function checkvalue( $val, $typ){
    $pattern = '';
    $format = '';
    switch( substr( $typ, 0, 1 ) ) {
      case 'H':
        if( get_magic_quotes_gpc() )
          $val = stripslashes( $val );
        $val = htmlspecialchars( $val );
        break;
      case 'R':
        break;
      case 'U':
        $val = trim($val);
        $pattern = '/^\d*[1-9]\d*$/';
        break;
      case 'u':
        //FIXME: zahl sollte als zahl zurückgegeben 
        //werden, zur Zeit String
        $val = trim($val);
        // eventuellen nachkommateil (und sonstigen Muell) abschneiden:
        $val = preg_replace( '/[^\d].*$/', '', $val );
        $pattern = '/^\d+$/';
        break;
      case 'd':
        $val = trim($val);
        // eventuellen nachkommateil abschneiden:
        $val = preg_replace( '/[.].*$/', '', $val );
        $pattern = '/^-{0,1}\d+$/';
        break;
      case 'f':
        $val = str_replace( ',', '.' , trim($val) );
        $format = '%f';
        $pattern = '/^[-\d.]+$/';
        break;
      case 'w':
        $val = trim($val);
        $pattern = '/^[a-zA-Z0-9_]*$/';
        break;
      case 'W':
        $val = trim($val);
        $pattern = '/^[a-zA-Z0-9_]+$/';
        break;
      case '/':
        $val = trim($val);
        $pattern = $typ;
         break;
      default:
        return FALSE;
    }
    if( $pattern ) {
      if( ! preg_match( $pattern, $val ) ) {
        return FALSE;
      }
    }
    if( $format ) {
      sscanf( $val, $format, & $val );
    }
  return $val;
}

// get_http_var:
// - name: wenn name auf [] endet, wird ein array erwartet (aus <input name='bla[]'>)
// - typ: definierte $typ argumente:
//   d : ganze Zahl
//   u : nicht-negative ganze Zahl
//   U : positive ganze Zahl (echt groesser als 0)
//   H : wendet htmlspecialchars an (erlaubt sichere und korrekte ausgabe in HTML)
//   R : raw: keine Einschraenkung, keine Umwandlung
//   f : Festkommazahl
//   w : bezeichner: alphanumerisch und _; leerstring zugelassen
//   W : bezeichner: alphanumerisch und _, mindestens ein zeichen
//   /.../: regex pattern. Wert wird ausserdem ge-trim()-t
// - default:
//   - wenn array erwartet wird, kann der default ein array sein.
//   - wird kein array erwartet, aber default is ein array, so wird $default[$name] versucht
//
// per POST uebergebene variable werden nur beruecksichtigt, wenn zugleich eine
// unverbrauchte transaktionsnummer 'itan' uebergeben wird (als Sicherung
// gegen mehrfache Absendung desselben Formulars per "Reload" Knopfs des Browsers)
//
function get_http_var( $name, $typ, $default = NULL, $is_self_field = false ) {
  global $HTTP_GET_VARS, $HTTP_POST_VARS, $self_fields, $self_post_fields;
  global $http_input_sanitized;

  if( ! $http_input_sanitized )
    sanitize_http_input();

  // echo "get_http_var: $is_self_field";
  if( substr( $name, -2 ) == '[]' ) {
    $want_array = true;
    $name = substr( $name, 0, strlen($name)-2 );
  } else {
    $want_array = false;
  }
  if( isset( $HTTP_GET_VARS[$name] ) ) {
    $arry = $HTTP_GET_VARS[$name];
  } elseif( isset( $HTTP_POST_VARS[$name] ) ) {
    $arry = $HTTP_POST_VARS[$name];
  } else {
    if( isset( $default ) ) {
      if( is_array( $default ) ) {
        if( $want_array ) {
          $GLOBALS[$name] = $default;
          //FIXME self_fields for arrays?
        } else if( isset( $default[$name] ) ) {
          // erlaube initialisierung z.B. aus MySQL-'$row':
          $GLOBALS[$name] = $default[$name];
          if( $is_self_field ) {
            if( $is_self_field === 'POST' )
              $self_post_fields[$name] = $default[$name];
            else
              $self_fields[$name] = $default[$name];
          }
        } else {
          unset( $GLOBALS[$name] );
          return FALSE;
        }
      } else {
        $GLOBALS[$name] = $default;
        if( $is_self_field ) {
          if( $is_self_field === 'POST' )
            $self_post_fields[$name] = $default;
          else
            $self_fields[$name] = $default;
        }
      }
      return TRUE;
    } else {
      unset( $GLOBALS[$name] );
      return FALSE;
    }
  }

  if(is_array($arry)){
    if( ! $want_array ) {
      unset( $GLOBALS[$name] );
      return FALSE;
    }
    foreach($arry as $key => $val){
      $new = checkvalue($val, $typ);
      if($new===FALSE){
        // error( 'unerwarteter Wert fuer Variable $name' );
        unset( $GLOBALS[$name] );
        return FALSE;
      } else {
        $arry[$key]=$new;
      }
    }
    //FIXME self_fields for arrays?
    $GLOBALS[$name] = $arry;
  } else {
      $new = checkvalue($arry, $typ);
      if($new===FALSE){
        // error( 'unerwarteter Wert fuer Variable $name' );
        unset( $GLOBALS[$name] );
        return FALSE;
      } else {
        $GLOBALS[$name] = $new;
        if( $is_self_field ) {
          if( $is_self_field === 'POST' )
            $self_post_fields[$name] = $new;
          else
            $self_fields[$name] = $new;
        }
      }
  }
  return TRUE;
}

function need_http_var( $name, $typ, $is_self_field = false ) {
  need( get_http_var( $name, $typ, NULL, $is_self_field ), "variable $name nicht uebergeben" );
  return TRUE;
}

function self_field( $name, $default = NULL ) {
  global $self_fields;
  if( isset( $self_fields[$name] ) )
    return $self_fields[$name];
  else
    return $default;
}

function update_database( $version ) {
  switch( $version ) {
    case 0:
      logger( 'starting update_database: from version 0' );
       doSql( "ALTER TABLE Dienste ADD `dienstkontrollblatt_id` INT NULL DEFAULT NULL "
       , "update datenbank von version 8 auf 9 fehlgeschlagen"
       );
       sql_update( 'leitvariable', array( 'name' => 'database_version' ), array( 'value' => 1 ) );
      logger( 'update_database: update to version 1 successful' );
  }
}


global $itan, $uandom_handle;
$itan = false;
$urandom_handle = false;

function random_hex_string( $bytes ) {
  global $urandom_handle;
  if( ! $urandom_handle )
    need( $urandom_handle = fopen( '/dev/urandom', 'r' ), 'konnte /dev/urandom nicht oeffnen' );
  $s = '';
  while( $bytes > 0 ) {
    $c = fgetc( $urandom_handle );
    need( $c !== false, 'Lesefehler von /dev/urandom' );
    $s .= sprintf( '%02x', ord($c) );
    $bytes--;
  }
  return $s;
}

function set_itan() {
  global $itan, $sessions_id;
  $tan = random_hex_string(5);
  $id = sql_insert( 'transactions' , array(
    'used' => 0
  , 'sessions_id' => $sessions_id
  , 'itan' => $tan
  ) );
  $itan = $id.'_'.$tan;
}

function get_itan( $force_new = false ) {
  global $itan;
  if( $force_new or ! $itan )
    set_itan();
  return $itan;
}

function optionen( $values, $selected ) {
  $output = '';
  foreach( $values as $v ) {
    if( is_array( $v ) ) {
      $value = $v[0];
      $text = $v[1];
      $title = ( $v[2] ? $v[2] : '' );
    } else {
      $value = $v;
      $text = $v;
      $title = '';
    }
    $output .= "<option value='$value'";
    if( $value == $selected )
      $output .= " selected";
    if( $title )
      $output .= " title='$title'";
    $output .= ">$text</option>";
  }
  return $output;
}

// insert_html:
// erzeugt javascript-code, der $element als Child vom element $id ins HTML einfuegt.
// $element is entweder ein string (erzeugt textelement), oder ein
// array( tag, attrs, childs ):
//   - tag ist der tag-name (z.b. 'table')
//   - attrs ist false, oder Liste von Paaren ( name, wert) gewuenschter Attribute
//   - childs ist entweder false, ein Textstring, oder ein Array von $element-Objekten
function insert_html( $id, $element ) {
  global $autoid;
  if( ! $autoid ) $autoid = 0;

  $output = '
  ';
  if( ! $element )
    return $output;

  if( is_string( $element ) ) {
    $autoid++;
    $output = "$output
      var tnode_$autoid;
      tnode_$autoid = document.createTextNode('$element');
      document.getElementById('$id').appendChild(tnode_$autoid);
    ";
  } else {
    assert( is_array( $element ) );
    $tag = $element[0];
    $attrs = $element[1];
    $childs = $element[2];

    // element mit eindeutiger id erzeugen:
    $autoid++;
    $newid = "autoid_$autoid";
    $output = "$output
      var enode_$newid;
      var attr_$autoid;
      enode_$newid = document.createElement('$tag');
      attr_$autoid = document.createAttribute('id');
      attr_$autoid.nodeValue = '$newid';
      enode_$newid.setAttributeNode( attr_$autoid );
    ";
    // sonstige gewuenschte attribute erzeugen:
    if( $attrs ) {
      foreach( $attrs as $a ) {
        $autoid++;
        $output = "$output
          var attr_$autoid;
          attr_$autoid = document.createAttribute('{$a[0]}');
          attr_$autoid.nodeValue = '{$a[1]}';
          enode_$newid.setAttributeNode( attr_$autoid );
        ";
      }
    }
    // element einhaengen:
    $output = "$output
      document.getElementById( '$id' ).appendChild( enode_$newid );
    ";

    // rekursiv unterelemente erzeugen:
    if( is_array( $childs ) ) {
      foreach( $childs as $c )
        $output = $output . insert_html( $newid, $c );
    } else {
      // abkuerzung fuer reinen textnode:
      $output = $output . insert_html( $newid, $childs );
    }
  }
  return $output;
}

// replace_html: wie insert_html, loescht aber vorher alle Child-Elemente von $id
function replace_html( $id, $element ) {
  global $autoid;
  $autoid++;
  $output = "
    var enode_$autoid;
    var child_$autoid;
    enode_$autoid = document.getElementById('$id');
    while( child_$autoid = enode_$autoid.firstChild )
      enode_$autoid.removeChild(child_$autoid);
  ";
  return $output . insert_html( $id, $element );
}

function move_html( $id, $into_id ) {
  global $autoid;
  $autoid++;
  return "
    var child_$autoid;
    child_$autoid = document.getElementById('$id');
    document.getElementById('$into_id').appendChild(child_$autoid);
  ";
  // appendChild erzeugt _keine_ Kopie!
  // das urspruengliche element verschwindet, also ist das explizite loeschen unnoetig:
  //   document.getElementById('$id').removeChild(child_$autoid);
}

?>
