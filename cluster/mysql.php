<?
////////////////////////////////////
//
// host-funktionen:
//
////////////////////////////////////


function sql_query_hosts( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $joins = array();
  $filters = array();
  $groupby = 'hosts.hosts_id';

  $selects = sql_default_selects('hosts');
  $selects[] = "LEFT( hosts.fqhostname, LOCATE( '.',  hosts.fqhostname ) - 1 ) as hostname";
  $selects[] = "SUBSTR( hosts.fqhostname, LOCATE( '.', hosts.fqhostname ) + 1 ) as domain";
  $selects[] = " ( SELECT count(*) FROM disks WHERE disks.hosts_id = hosts.hosts_id ) as disks_count ";
  $selects[] = " ( SELECT count(*) FROM services WHERE services.hosts_id = hosts.hosts_id ) as services_count ";
  $selects[] = " ( SELECT count(*) FROM accounts WHERE accounts.hosts_id = hosts.hosts_id ) as accounts_count ";
  $selects[] = " IFNULL( ( SELECT GROUP_CONCAT( accountdomain SEPARATOR ' ' )
                          FROM accountdomains_hosts_relation JOIN accountdomains USING (accountdomains_id)
                          WHERE accountdomains_hosts_relation.hosts_id = hosts.hosts_id ), ' - ' ) as accountdomains ";

  print_on_exit( "<!-- sql_query_hosts: " .var_export( $filters_in, true ). " -->" );
  foreach( sql_canonicalize_filters( 'hosts', $filters_in ) as $key => $cond ) {
    if( strncmp( $key, 'hosts.', 6 ) == 0 ) {  // match on column in hosts ...
      $filters[$key] = $cond;
      continue; // ... probably ok!
    }
    switch( $key ) {  // otherwise, check for special cases:
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
      case 'accounts_id':
        $joins[] = "accounts USING (hosts_id)";
        $selects[] = "accounts.accounts_id";
        $filters['accounts.accounts_id'] = $cond;
        break;
      case 'accountdomain':
        $joins[] = "accountdomains_hosts_relation USING ( hosts_id )";
        $joins[] = "accountdomains USING ( accountdomains_id )";
        $filters['accountdomains.accountdomain'] = $cond;
        break;
      case 'where': // allow arbitrary condition if indicated by keyword `where'
        $filters[] = $cond;
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
      $selects = 'COUNT(*) as count';
      break;
//     case 'LOCATIONS':
//       $op = 'SELECT';
//       $selects = 'distinct location';
//       $groupby = false;
//       break;
    default:
      error( "undefined op: $op" );
  }
  switch( $orderby ) {
    case 'invlabel':
      $orderby = ' LEFT( invlabel,1) , CONVERT( SUBSTR(invlabel,2) , UNSIGNED) ';
  }
  return sql_query( $op, 'hosts', $filters, $selects, $joins, $orderby, $groupby );
}

function sql_hosts( $filters = array(), $orderby = 'fqhostname' ) {
  $sql = sql_query_hosts( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_host( $filters, $allow_null = false ) {
  $sql = sql_query_hosts( 'SELECT', $filters );
  return sql_do_single_row( $sql, $allow_null );
}

function sql_fqhostname( $filters, $allow_null = false ) {
  $sql = sql_query_hosts( 'SELECT', $filters );
  return sql_do_single_field( $sql, 'fqhostname', $allow_null );
}

function sql_delete_hosts( $filters ) {
  foreach( sql_hosts( $filters ) as $host ) {
    $hosts_id = $host['id'];
    need( sql_count( 'accounts', array( 'hosts_id' => $hosts_id ) ) == 0, "accounts left on host $hosts_id" );
    need( sql_count( 'websites', array( 'hosts_id' => $hosts_id ) ) == 0, "websites left on host $hosts_id" );
    sql_update( 'disks', array( 'hosts_id' => $hosts_id ), array( 'hosts_id' => 0 ) );
    sql_update( 'services', array( 'hosts_id' => $hosts_id ), array( 'hosts_id' => 0 ) );
    sql_delete( 'accountdomains_hosts_relation', array( 'hosts_id' => $hosts_id ) );
    sql_delete( 'hosts', array( 'hosts_id' => $hosts_id ) );
  }
}

// function sql_locations( $filters, $orderby = 'location' ) {
//   $sql = sql_query_hosts( 'LOCATIONS', $filters, array(), $orderby );
//   return mysql2array( sql_do( $sql ) );
// }


////////////////////////////////////
//
// disk-funktionen:
//
////////////////////////////////////

function sql_query_disks( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $filters = array();
  $joins = array();
  $joins['LEFT hosts'] = "disks.hosts_id = hosts.hosts_id";
  $joins['LEFT systems'] = "disks.systems_id = systems.systems_id";

  $selects = sql_default_selects('disks');
  $selects[] = 'ifnull( hosts.location, disks.location ) as location';
  $selects[] = 'hosts.fqhostname as fqhostname';
  $selects[] = 'systems.type as systems_type';
  $selects[] = 'systems.arch as systems_arch';
  $selects[] = 'systems.date_built as systems_date_built';

  print_on_exit( "<!-- sql_query_disks: " .var_export( $filters_in, true ). " -->" );
  foreach( sql_canonicalize_filters( 'disks', $filters_in ) as $key => $cond ) {
    if( strncmp( $key, 'disks.', 6 ) == 0 ) {
      $filters[$key] = $cond;
      continue;
    }
    switch( $key ) {
      case 'where':
        $filters[] = $cond;
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
      $selects = 'COUNT(*) as count';
      break;
    default:
      error( "undefined op: $op" );
  }
  return sql_query( $op, 'disks', $filters, $selects, $joins, $orderby, 'disks.disks_id' );
}

function sql_disks( $filters = array(), $orderby = 'cn' ) {
  $sql = sql_query_disks( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_disk( $filters, $allow_null = false ) {
  $sql = sql_query_disks( 'SELECT', $filters );
  return sql_do_single_row( $sql, $allow_null );
}

function sql_delete_disks( $filters ) {
  foreach( sql_query_disks( $filters ) as $disk ) {
    sql_delete( 'disks', $disk['disks_id'] );
  }
}


////////////////////////////////////
//
// tape-funktionen:
//
////////////////////////////////////

function sql_query_tapes( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $filters = array();
  $joins = array();

  $selects = sql_default_selects('tapes');

  foreach( sql_canonicalize_filters( 'tapes', $filters_in ) as $key => $cond ) {
    if( strncmp( $key, 'tapes.', 6 ) == 0 ) {
      $filters[$key] = $cond;
      continue;
    }
    switch( $key ) {
      case 'where':
        $filters[] = $cond;
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
      $selects = 'COUNT(*) as count';
      break;
    default:
      error( "undefined op: $op" );
  }
  return sql_query( $op, 'tapes', $filters, $selects, $joins, $orderby, 'tapes.tapes_id' );
}

function sql_tapes( $filters = array(), $orderby = 'cn' ) {
  $sql = sql_query_tapes( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_tape( $filters, $allow_null = false ) {
  $sql = sql_query_tapes( 'SELECT', $filters, array(), $orderby );
  return sql_do_single_row( $sql, $allow_null );
}

function sql_delete_tapes( $filters ) {
  foreach( sql_tapes( $filters ) as $tape ) {
    $tapes_id = $tape['tapes_id'];
    sql_delete( 'tapechunks', array( 'tapes_id' => $tapes_id ) );
    sql_delete( 'tapes', array( 'tapes_id' => $tapes_id ) );
  }
}


////////////////////////////////////
//
// service-funktionen:
//
////////////////////////////////////

define( TYPE_SERVICE_HTTP, 10 );
define( TYPE_SERVICE_HTTPS, 11 );
define( TYPE_SERVICE_NTP, 20 );
define( TYPE_SERVICE_DNS, 30 );
define( TYPE_SERVICE_LPR, 40 );

function sql_query_services( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $filters = array();
  $joins = array( 'LEFT hosts' => 'hosts_id' );

  $selects = sql_default_selects( 'services' );

  foreach( sql_canonicalize_filters( 'services', $filters_in ) as $key => $cond ) {
    if( strncmp( $key, 'services.', 9 ) == 0 ) {
      $filters[$key] = $cond;
      continue;
    }
    switch( $key ) {
      case 'where':
        $filters[] = $cond;
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
      $selects = 'COUNT(*) as count';
      break;
    default:
      error( "undefined op: $op" );
  }
  return sql_query( $op, 'services', $filters, $selects, $joins, $orderby, 'services.services_id' );
}

function sql_services( $filters = array(), $orderby = 'type_service, description' ) {
  $sql = sql_query_services( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_service( $filters, $allow_null = false ) {
  $sql = sql_query_services( 'SELECT', $filters, array(), $orderby );
  return sql_do_single_row( $sql, $allow_null );
}

function sql_delete_services( $filters ) {
  foreach( sql_services( $filters ) as $service ) {
    $services_id = $service['services_id'];
    sql_delete( 'services', $services_id );
  }
}


////////////////////////////////////
//
// accounts-funktionen:
//
////////////////////////////////////

function sql_query_accounts( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $filters = array();
  $joins = array( 'LEFT hosts' => 'hosts_id' );
  $joins = array( 'LEFT people' => 'people_id' );

  $selects = sql_default_selects('accounts');
  $selects[] = 'hosts.fqhostname';
  $selects[] = 'people.cn as cn';
  $selects[] = " ( SELECT count(*) FROM accountdomains_accounts_relation
                   WHERE accountdomains_accounts_relation.accounts_id = accounts.accounts_id ) as accountdomains_count ";
  $selects[] = " IFNULL( ( SELECT GROUP_CONCAT( accountdomain SEPARATOR ' ' )
                          FROM accountdomains_accounts_relation JOIN accountdomains USING (accountdomains_id)
                          WHERE accountdomains_accounts_relation.accounts_id = accounts.accounts_id ), ' - ' ) as accountdomains ";

  foreach( sql_canonicalize_filters( 'accounts', $filters_in ) as $key => $cond ) {
    if( strncmp( $key, 'accounts.', 9 ) == 0 ) {
      $filters[$key] = $cond;
      continue;
    }
    switch( $key ) {
      case 'accountdomain':
        $joins['accountdomains_accounts_relation'] = 'accounts_id';
        $joins['accountdomains'] = 'accountdomains_id';
        $filters['accountdomains.accountdomain'] = $cond;
        break;
      case 'accountdomains_id':
        $joins['accountdomains_accounts_relation'] = 'accounts_id';
        $joins['accountdomains'] = 'accountdomains_id';
        $filters['accountdomains.accountdomains_id'] = $cond;
        break;
      case 'people_id':
        $filters['people.id'] = $cond;
        break;
      case 'fqhostname':
        $filters['hosts.fqhostname'] = $cond;
        break;
      case 'where':
        $filters[] = $cond;
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
      $selects = 'COUNT(*) as count';
      break;
    default:
      error( "undefined op: $op" );
  }
  return get_sql_query( $op, 'accounts', $filters, $selects, $joins, $orderby, 'accounts.accounts_id' );
}

function sql_accounts( $filters = array(), $orderby = 'uid' ) {
  $sql = sql_query_accounts( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_account( $filters, $allow_null = false ) {
  $sql = sql_query_accounts( 'SELECT', $filters, array(), $orderby );
  return sql_do_single_row( $sql, $allow_null );
}

function sql_delete_accounts( $filters ) {
  foreach( sql_accounts( $filters ) as $account ) {
    sql_delete( 'accounts', $account['accounts_id'] );
  }
}

////////////////////////////////////
//
// accountdomain-funktionen:
//
////////////////////////////////////

function sql_query_accountdomains( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $filters = array();
  $joins = array();

  $selects = sql_default_selects('accountdomains');
  $selects[] = " ( SELECT count(*) FROM accountdomains_accounts_relation
                   WHERE accountdomains_accounts_relation.accountdomains_id = accountdomains.accountdomains_id )
                   AS accounts_count ";
  $selects[] = " ( SELECT count(*) FROM accountdomains_hosts_relation
                   WHERE accountdomains_hosts_relation.accountdomains_id = accountdomains.accountdomains_id )
                   AS hosts_count ";

  foreach( sql_canonicalize_filters( 'accountdomains', $filters_in ) as $key => $cond ) {
    if( strncmp( $key, 'accountdomains.', 15 ) == 0 ) {
      $filters[$key] = $cond;
      continue;
    }
    switch( $key ) {
      case 'accounts_id':
        $joins['accountdomains_accounts_relation'] = 'accountdomains_id';
        $filters['accountdomains_accounts_relation.accounts_id'] = $cond;
        break;
      case 'hosts_id':
        $joins['accountdomains_hosts_relation'] = 'accountdomains_id';
        $filters['accountdomains_hosts_relation.hosts_id'] = $cond;
        break;
      case 'where':
        $filters[] = $cond;
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
      $selects = 'COUNT(*) as count';
      break;
    default:
      error( "undefined op: $op" );
  }
  return sql_query( $op, 'accountdomains', $filters, $selects, $joins, $orderby, 'accountdomains.accountdomain' );
}

function sql_accountdomains( $filters = array(), $orderby = 'accountdomain' ) {
  $sql = sql_query_accountdomains( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}


////////////////////////////////////
//
// systems-funktionen:
//
////////////////////////////////////

function sql_query_systems( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $filters = array();
  $joins = array();

  $selects = sql_default_selects('systems');
  $selects[] = '( SELECT COUNT(*) FROM disks WHERE disks.systems_id = systems.sysdems_id ) AS disks_count';

  foreach( sql_canonicalize_filters( 'systems', $filters_in ) as $key => $cond ) {
    if( strncmp( $key, 'systems.', 8 ) == 0 ) {
      $filters[$key] = $cond;
      continue;
    }
    switch( $key ) {
      case 'disks_id':
        $joins['disks'] = 'systems_id';
        $filters['disks.disks_id'] = $cond;
        break;
      case 'where':
        $filters[] = $cond;
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
      $selects = 'COUNT(*) as count';
      break;
    default:
      error( "undefined op: $op" );
  }
  return sql_query( $op, 'systems', $filters, $selects, $joins, $orderby, 'systems.systems_id' );
}

function sql_systems( $filters = array(), $orderby = 'systems.type,systems.arch,systems.date_built' ) {
  $sql = sql_query_systems( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_system( $filters, $allow_null = false ) {
  $sql = sql_query_systems( 'SELECT', $filters, array(), $orderby );
  return sql_do_single_row( $sql, $allow_null );
}

function sql_delete_systems( $filters ) {
  foreach( sql_systems( $filters ) ) {
    sql_delete( 'systems', array( 'systems_id' => $systems_id ) );
}




?>
