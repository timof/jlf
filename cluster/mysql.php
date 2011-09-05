<?php

////////////////////////////////////
//
// oid and IP handling:
//
////////////////////////////////////

define( 'OID_MAX_PARTS', 21 );
define( 'OID_ZERO_PADDING', '0000000000' );
define( 'OID_MAX_DIGITS', strlen( OID_ZERO_PADDING ) );

function oid_canonical2traditional( $oid ) {
  return preg_replace( '/\\.0*/', '.', preg_replace( '/^0*/', '', $oid ) );
}

function oid_traditional2canonical( $oid ) {
  $parts = explode( '.', $oid );
  need( count( $parts ) <= OID_MAX_PARTS, 'too many parts' );
  $dot = '';
  $r = '';
  foreach( $parts as $p ) {
    need( strlen( $p ) <= OID_MAX_DIGITS, 'component too large' );
    $r .= $dot . substr( OID_ZERO_PADDING.$p, -OID_MAX_DIGITS );
    $dot = '.';
  }
  return $r;
}

function ip4_canonical2traditional( $ip4 ) {
  return preg_replace( '/\\.0*/', '.', $ip4 );
}

function ip4_traditional2canonical( $ip4 ) {
  $parts = explode( '.', $ip4 );
  $dot = '';
  $r = '';
  for( $i = 0; $i < 4; ++$i ) {
    $r .= $dot . substr( '00'.adefault( $parts, $i, '255' ), -3 );
    $dot = '.';
  }
  return $r;
}


////////////////////////////////////
//
// host-funktionen:
//
////////////////////////////////////


function sql_query_hosts( $op, $filters_in = array(), $using = array(), $orderby = false, $scalars = array() ) {
  $joins = array();
  $groupby = 'hosts.hosts_id';

  $selects = sql_default_selects( 'hosts', false, $scalars );
  $selects[] = "LEFT( hosts.fqhostname, LOCATE( '.',  hosts.fqhostname ) - 1 ) as hostname";
  $selects[] = "SUBSTR( hosts.fqhostname, LOCATE( '.', hosts.fqhostname ) + 1 ) as domain";
  $selects[] = " ( SELECT count(*) FROM disks WHERE disks.hosts_id = hosts.hosts_id ) as disks_count ";
  $selects[] = " ( SELECT count(*) FROM services WHERE services.hosts_id = hosts.hosts_id ) as services_count ";
  $selects[] = " ( SELECT count(*) FROM accounts WHERE accounts.hosts_id = hosts.hosts_id ) as accounts_count ";
  $selects[] = " IFNULL( ( SELECT GROUP_CONCAT( accountdomain SEPARATOR ' ' )
                          FROM accountdomains_hosts_relation JOIN accountdomains USING (accountdomains_id)
                          WHERE accountdomains_hosts_relation.hosts_id = hosts.hosts_id ), ' - ' ) as accountdomains ";

  foreach( $scalars as $tag => $key ) {
    switch( $tag ) {
      case accountdomain:
        $selects[] = " ( SELECT count(*) FROM hosts_accountdomains_relation
                         WHERE ( hosts_accountdomains_relation.hosts_id = hosts.hosts_id ) AND ( accountdomains_id = $key ) )
                         AS accountdomain_relation ";
      default:
        error( "unknown scalar requested: $tag" );
    }
  }

  $filters = sql_canonicalize_filters( 'hosts', $filters_in, $joins + array( 'disks', 'services', 'accounts', 'accountdomains' ) );

  foreach( $filters as & $atom ) {
    $t = adefault( $atom, -1 );
    if( $t === 'cooked_atom' ) {
      $key = & $atom[ 1 ];
      switch( $key ) {
        case 'disks.disks_id':
          $joins['disks'] = "hosts_id";
          $selects[] = "disks.disks_id";
          break;
        case 'services.services_id':
          $joins['services'] = "hosts_id";
          $selects[] = "services.services_id";
          break;
        case 'accounts.accounts_id':
          $joins['accounts'] = "hosts_id";
          $selects[] = "accounts.accounts_id";
          break;
        case 'accountdomains.accountdomain':
          $joins['accountdomains_hosts_relation'] = "hosts_id";
          $joins['accountdomains'] = "accountdomains_id";
          break;
        default:
          // nop: other cooked atoms should work as-is
      }
    }
    if( $t === 'raw_atom' ) {
      switch( $key ) {
        case 'hosts.locations_id':
        case 'locations_id':
          $atom[ 1 ] = 'hosts.location';
          $atom[ 2 ] = sql_unique_value( 'hosts', 'location', $atom[ 2 ] );
          $atom[ -1 ] = 'cooked_atom';
          break;
        default:
          error( "undefined key: $key" );
      }
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

function sql_hosts( $filters = array(), $orderby = 'fqhostname', $scalars = array() ) {
  $sql = sql_query_hosts( 'SELECT', $filters, array(), $orderby, $scalars );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_host( $filters, $default = false ) {
  $sql = sql_query_hosts( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

function sql_fqhostname( $filters, $default = false ) {
  $sql = sql_query_hosts( 'SELECT', $filters );
  return sql_do_single_field( $sql, 'fqhostname', $default );
}

function sql_delete_hosts( $filters ) {
  foreach( sql_hosts( $filters ) as $host ) {
    $hosts_id = $host['hosts_id'];
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

function sql_query_disks( $op, $filters_in = array(), $using = array(), $orderby = false, $scalars = array() ) {
  $joins = array();
  $joins['LEFT hosts'] = "hosts_id";
  $joins['LEFT systems'] = "disks.systems_id = systems.systems_id";

  $selects = sql_default_selects('disks');
  // $selects[] = 'ifnull( hosts.location, disks.location ) as location';
  $selects[] = 'hosts.fqhostname as fqhostname';
  $selects[] = 'systems.type as systems_type';
  $selects[] = 'systems.arch as systems_arch';
  $selects[] = 'systems.date_built as systems_date_built';

  foreach( $scalars as $tag => $key ) {
    switch( $tag ) {
      default:
        error( "unknown scalar requested: $tag" );
    }
  }

  $filters = sql_canonicalize_filters( 'disks', $filters_in, $joins );
  html_comment( 'sql_query_disks: ' .var_export( $filters_in, true ) );
  foreach( $filters as & $atom ) {
    if( adefault( $atom, -1 ) !== 'raw_atom' )
      continue;
    $rel = & $atom[ 0 ];
    $key = & $atom[ 1 ];
    $val = & $atom[ 2 ];
    switch( $key ) {
      case 'hosts.locations_id':
      case 'locations_id':
        $key = 'hosts.location';
        $val = sql_unique_value( 'hosts', 'location', $atom[ 2 ] );
        $atom[ -1 ] = 'cooked_atom';
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

function sql_disks( $filters = array(), $orderby = 'cn', $scalars = array() ) {
  $sql = sql_query_disks( 'SELECT', $filters, array(), $orderby, $scalars );
  // prettydump( $sql, 'sql' );
  $a = mysql2array( sql_do( $sql ) );
  // prettydump( $a, 'a' );
  return $a;
}

function sql_one_disk( $filters, $default = false ) {
  $sql = sql_query_disks( 'SELECT', $filters );
  return sql_do_single_row( $sql, $default );
}

function sql_delete_disks( $filters ) {
  foreach( sql_disks( $filters ) as $disk ) {
    sql_delete( 'disks', $disk['disks_id'] );
  }
}


////////////////////////////////////
//
// tape-funktionen:
//
////////////////////////////////////

function sql_query_tapes( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $joins = array();

  $selects = sql_default_selects('tapes');

  $filters = sql_canonicalize_filters( 'tapes', $filters_in, $joins );
  foreach( $filters as & $atom ) {
    if( adefault( $atom, -1 ) !== 'raw_atom' )
      continue;
    $rel = & $atom[ 0 ];
    $key = & $atom[ 1 ];
    $val = & $atom[ 2 ];
    switch( $key ) {
      case 'hosts.locations_id':
      case 'locations_id':
        $key = 'hosts.location';
        $val = sql_unique_value( 'hosts', 'location', $atom[ 2 ] );
        $atom[ -1 ] = 'cooked_atom';
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

function sql_one_tape( $filters, $default = false ) {
  $sql = sql_query_tapes( 'SELECT', $filters, array(), $orderby );
  return sql_do_single_row( $sql, $default );
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
// tapechunks-funktionen:
//
////////////////////////////////////

function sql_query_tapechunks( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $joins = array( 'tapes' => 'tapes_id', 'backupchunks' => 'backupchunks_id' );
  $selects = sql_default_selects( array( 'tapechunks', 'tapes', 'backupchunks' ) );

  $filters = sql_canonicalize_filters( 'tapechunks', $filters_in, $joins );

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
  return sql_query( $op, 'tapechunks', $filters, $selects, $joins, $orderby, 'tapechunks.tapechunks_id' );
}

function sql_tapechunks( $filters = array(), $orderby = 'cn, chunkwritten, oid' ) {
  $sql = sql_query_tapechunks( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_tapechunk( $filters, $default = false ) {
  $sql = sql_query_tapechunks( 'SELECT', $filters, array(), $orderby );
  return sql_do_single_row( $sql, $default );
}

function sql_delete_tapechunks( $filters ) {
  $chunks = sql_tapechunks( $filters );
  foreach( $chunks as $tc ) {
    $tc_id = $tc['tapechunks_id'];
    sql_delete( 'tapechunks', $tc_id );
  }
  logger( 'sql_delete_tapechunks: '.count( $chunks ).' chunks deleted', 'delete' );
}


////////////////////////////////////
//
// backupchunks-funktionen:
//
////////////////////////////////////

function sql_query_backupchunks( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $joins = array();
  $selects = sql_default_selects( array( 'tapechunks', 'tapes', 'backups' ) );
  $selects[] = " ( SELECT COUNT(*) FROM tapechunks WHERE tapechunks.backupchunks_id = backupchunks.backupchunks_id ) AS copies_count ";

  $filters = sql_canonicalize_filters( 'backupchunks', $filters_in, $joins );

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
  return sql_query( $op, 'backupchunks', $filters, $selects, $joins, $orderby, 'backupchunks.backupchunks_id' );
}

function sql_backupchunks( $filters = array(), $orderby = 'oid' ) {
  $sql = sql_query_backupchunks( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_backupchunk( $filters, $default = false ) {
  $sql = sql_query_backupchunks( 'SELECT', $filters, array(), $orderby );
  return sql_do_single_row( $sql, $default );
}

function sql_delete_backupchunks( $filters ) {
  $chunks = sql_backupchunks( $filters );
  foreach( $chunks as $tc ) {
    $tc_id = $tc['backupchunks_id'];
    sql_delete( 'backupchunks', $tc_id );
  }
  logger( 'sql_delete_backupchunks: '.count( $chunks ).' chunks deleted', 'delete' );
}



////////////////////////////////////
//
// backupjobs-funktionen:
//
////////////////////////////////////

function sql_query_backupjobs( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $joins = array( 'backupchunks' => 'backupjunks_id', 'hosts' => 'hosts_id' );
  $selects = sql_default_selects( array( 'backupjobs', 'backupchunks', 'hosts' ) );

  $filters = sql_canonicalize_filters( 'backupjobs', $filters_in, $joins );

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
  return sql_query( $op, 'backupjobs', $filters, $selects, $joins, $orderby, 'backupjobs.backupjobs_id' );
}

function sql_backupjobs( $filters = array(), $orderby = 'utc, cn, hosts_id' ) {
  $sql = sql_query_backupjobs( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_backupjob( $filters, $default = false ) {
  $sql = sql_query_backupjobs( 'SELECT', $filters, array(), $orderby );
  return sql_do_single_row( $sql, $default );
}

function sql_delete_backupjobs( $filters ) {
  $jobs = sql_backupjobs( $filters );
  foreach( $jobs as $j ) {
    $id = $j['backupjobs_id'];
    sql_delete( 'backupjobs', $id );
  }
  logger( 'sql_delete_backupjobs: '.count( $jobs ).' jobs deleted', 'delete' );
}



////////////////////////////////////
//
// service-funktionen:
//
////////////////////////////////////

define( 'TYPE_SERVICE_HTTP', 10 );
define( 'TYPE_SERVICE_HTTPS', 11 );
define( 'TYPE_SERVICE_NTP', 20 );
define( 'TYPE_SERVICE_DNS', 30 );
define( 'TYPE_SERVICE_LPR', 40 );

function sql_query_services( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $joins = array( 'LEFT hosts' => 'hosts_id' );

  $selects = sql_default_selects( 'services' );

  $filters = sql_canonicalize_filters( 'services', $filters_in, 'hosts' );

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

function sql_one_service( $filters, $default = false ) {
  $sql = sql_query_services( 'SELECT', $filters, array(), $orderby );
  return sql_do_single_row( $sql, $default );
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
  $joins = array();
  $joins['LEFT hosts'] = 'hosts_id';
  $joins['LEFT people'] = 'people_id';
  $joins['LEFT accountdomains_accounts_relation'] = 'accounts_id';
  $joins['LEFT accountdomains'] = 'accountdomains_id';

  $selects = sql_default_selects('accounts');
  $selects[] = 'hosts.fqhostname';
  $selects[] = 'people.cn as cn';
  $selects[] = " ( SELECT count(*) FROM accountdomains_accounts_relation
                   WHERE accountdomains_accounts_relation.accounts_id = accounts.accounts_id ) as accountdomains_count ";
  $selects[] = " IFNULL( ( SELECT GROUP_CONCAT( accountdomain SEPARATOR ' ' )
                          FROM accountdomains_accounts_relation JOIN accountdomains USING (accountdomains_id)
                          WHERE accountdomains_accounts_relation.accounts_id = accounts.accounts_id ), ' - ' ) as accountdomains ";

  $filters = sql_canonicalize_filters( 'accounts', $filters_in, $joins );
//   foreach( $filters as & $atom ) {
//     if( adefault( $atom, -1 ) !== 'raw_atom' )
//       continue;
//     $rel = & $atom[ 0 ];
//     $key = & $atom[ 1 ];
//     $val = & $atom[ 2 ];
//     switch( $key ) {
//       case 'accountdomain':
//         // $joins['accountdomains_accounts_relation'] = 'accounts_id';
//         // $joins['accountdomains'] = 'accountdomains_id';
//         $key = 'accountdomains.accountdomain';
//         break;
//       case 'accountdomains_id':
//         $joins['accountdomains_accounts_relation'] = 'accounts_id';
//         $joins['accountdomains'] = 'accountdomains_id';
//         $filters['accountdomains.accountdomains_id'] = $cond;
//         break;
//     }
//   }
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
  return sql_query( $op, 'accounts', $filters, $selects, $joins, $orderby, 'accounts.accounts_id' );
}

function sql_accounts( $filters = array(), $orderby = 'uid' ) {
  $sql = sql_query_accounts( 'SELECT', $filters, array(), $orderby );
  return mysql2array( sql_do( $sql ) );
}

function sql_one_account( $filters, $default = false ) {
  $sql = sql_query_accounts( 'SELECT', $filters, array(), $orderby );
  return sql_do_single_row( $sql, $default );
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
  $joins = array();
  $joins['LEFT accountdomains_accounts_relation'] = 'accountdomains_id';
  $joins['LEFT accounts'] = 'accounts_id';
  $joins['LEFT accountdomains_hosts_relation'] = 'accountdomains_id';
  $joins['LEFT hosts'] = 'accountdomains_hosts_relation.hosts_id = hosts.hosts_id';

  $selects = sql_default_selects('accountdomains');
  $selects[] = " ( SELECT count(*) FROM accountdomains_accounts_relation
                   WHERE accountdomains_accounts_relation.accountdomains_id = accountdomains.accountdomains_id )
                   AS accounts_count ";
  $selects[] = " ( SELECT count(*) FROM accountdomains_hosts_relation
                   WHERE accountdomains_hosts_relation.accountdomains_id = accountdomains.accountdomains_id )
                   AS hosts_count ";

  $filters = sql_canonicalize_filters( 'accountdomains', $filters_in, $joins );
  foreach( $filters as & $atom ) {
    if( adefault( $atom, -1 ) !== 'raw_atom' )
      continue;
    $rel = & $atom[ 0 ];
    $key = & $atom[ 1 ];
    $val = & $atom[ 2 ];
    switch( $key ) {
      case 'accounts_id':
        $joins['accountdomains_accounts_relation'] = 'accountdomains_id';
        $key = 'accountdomains_accounts_relation.accounts_id';
        break;
      case 'hosts_id':
        $joins['accountdomains_hosts_relation'] = 'accountdomains_id';
        $key = 'accountdomains_hosts_relation.hosts_id';
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
  $joins['LEFT disks'] = 'systems_id';

  $selects = sql_default_selects('systems');
  $selects[] = '( SELECT COUNT(*) FROM disks WHERE disks.systems_id = systems.sysdems_id ) AS disks_count';

  $filters = sql_canonicalize_filters( 'systems', $filters_in, $joins );

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

function sql_one_system( $filters, $default = false ) {
  $sql = sql_query_systems( 'SELECT', $filters, array(), $orderby );
  return sql_do_single_row( $sql, $default );
}

function sql_delete_systems( $filters ) {
  foreach( sql_systems( $filters ) as $s ) {
    sql_delete( 'systems', array( 'systems_id' => $s['systems_id'] ) );
  }
}

?>
