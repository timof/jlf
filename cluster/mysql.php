<?php


////////////////////////////////////
//
// host-funktionen:
//
////////////////////////////////////


function sql_hosts( $filters = array(), $opts = array() ) {

  $joins = array();

  $selects = sql_default_selects( 'hosts' );
  $selects['hostname'] = "LEFT( hosts.fqhostname, LOCATE( '.',  hosts.fqhostname ) - 1 )";
  $selects['domain'] = "SUBSTR( hosts.fqhostname, LOCATE( '.', hosts.fqhostname ) + 1 )";
  $selects['disks_count'] = " ( SELECT count(*) FROM disks WHERE disks.hosts_id = hosts.hosts_id )";
  $selects['services_count'] = " ( SELECT count(*) FROM services WHERE services.hosts_id = hosts.hosts_id )";
  $selects['accounts_count'] = " ( SELECT count(*) FROM accounts WHERE accounts.hosts_id = hosts.hosts_id )";
  $selects['accountdomains'] = " IFNULL( ( SELECT GROUP_CONCAT( accountdomain SEPARATOR ' ' )
                         FROM accountdomains_hosts_relation JOIN accountdomains USING (accountdomains_id)
                         WHERE accountdomains_hosts_relation.hosts_id = hosts.hosts_id ), ' - ' )";


  $f = sql_canonicalize_filters( 'hosts', $filters, $joins + array( 'disks', 'services', 'accounts', 'accountdomains' ) );

  foreach( $f as & $atom ) {
    $t = adefault( $atom, -1 );
    if( $t === 'cooked_atom' ) {
      switch( $atom[ 1 ] ) {
        case 'disks.disks_id':
          $joins['disks'] = "disks USING ( hosts_id )";
          $selects['disks_id'] = "disks.disks_id";
          break;
        case 'services.services_id':
          $joins['services'] = "services USING ( hosts_id )";
          $selects['services_id'] = "services.services_id";
          break;
        case 'accounts.accounts_id':
          $joins['accounts'] = "accounts USING ( hosts_id )";
          $selects['accounts_id'] = "accounts.accounts_id";
          break;
        case 'accountdomains.accountdomain':
        case 'accountdomains.accountdomains_id':
          $joins['accountdomains_hosts_relation'] = 'accountdomains_hosts_relation USING ( hosts_id )';
          $joins['accountdomains'] = 'accountdomains USING ( accountdomains_id )';
          break;
        default:
          // nop: other cooked atoms should work as-is
      }
    }
    if( $t === 'raw_atom' ) {
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
          error( "undefined key: [$key]", LOG_FLAGS_CODE, 'hosts,sql' );
      }
    }
  }
  $opts = default_query_options( 'hosts', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'fqhostname'
  , 'filters' => $f
  ) );

  return sql_query( 'hosts', $opts );
}

//   foreach( $scalars as $tag => $key ) {
//     switch( $tag ) {
//       case accountdomain:
//         $selects[] = " ( SELECT count(*) FROM hosts_accountdomains_relation
//                          WHERE ( hosts_accountdomains_relation.hosts_id = hosts.hosts_id ) AND ( accountdomains_id = $key ) )
//                          AS accountdomain_relation ";
//       default:
//         error( "unknown scalar requested: [$tag]", LOG_FLAG_CODE, 'hosts,sql' );
//     }
//   }
//   switch( $orderby ) {
//     case 'invlabel':
//       $orderby = ' LEFT( invlabel,1) , CONVERT( SUBSTR(invlabel,2) , UNSIGNED) ';
//   }


function sql_one_host( $filters, $default = false ) {
  return sql_hosts( $filters, array( 'default' => $default, 'single_row' => true ) );
}

function sql_fqhostname( $filters, $default = false ) {
  return sql_hosts( $filters, array( 'default' => $default, 'single_field' => 'fqhostname' ) );
}

function sql_delete_hosts( $filters, $check = false ) {
  $hosts = sql_hosts( $filters );
  $problems = array();
  if( ! $hosts ) {
    return $problems;
  }
  foreach( $hosts as $h ) {
    $hosts_id = $h['hosts_id'];
    $references = sql_references( 'hosts', $hosts_id );
    if( $references ) {
      $problems[] = 'cannot delete: references exist: '.implode( ', ', array_keys( $references ) );
    }
  }
  if( $check ) {
    return $problems;
  }
  need( ! $problems, $problems );
  foreach( $hosts as $h ) {
    $hosts_id = $h['hosts_id'];
    $references = sql_references( 'hosts', $hosts_id );
//     sql_update( 'disks', array( 'hosts_id' => $hosts_id ), array( 'hosts_id' => 0 ) );
//     sql_update( 'services', array( 'hosts_id' => $hosts_id ), array( 'hosts_id' => 0 ) );
//     sql_delete( 'accountdomains_hosts_relation', array( 'hosts_id' => $hosts_id ) );
    sql_delete( 'hosts', $hosts_id );
    logger( "delete host [$hosts_id]", LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'hosts' );
  }
  return $problems;
}
  
    

////////////////////////////////////
//
// disk-funktionen:
//
////////////////////////////////////

function sql_disks( $filters = array(), $opts = array() ) {

  $joins = array(
    'hosts' => 'LEFT hosts USING ( hosts_id )'
  , 'systems' => 'LEFT systems USING( systems_id = systems.systems_id )'
  );

  $selects = sql_default_selects('disks');
  // $selects[] = 'ifnull( hosts.location, disks.location ) as location';
  $selects['fqhostname'] = 'hosts.fqhostname';
  $selects['systems_type'] = 'systems.type';
  $selects['systems_arch'] = 'systems.arch';
  $selects['systems_date_built'] = 'systems.date_built';

  $opts = default_query_options( 'disks', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'cn'
  ) );

  // foreach( $scalars as $tag => $key ) {
  //   switch( $tag ) {
  //     default:
  //       error( "unknown scalar requested: [$tag]", LOG_FLAGS_CODE, 'disks,sql' );
  //   }
  // }

  $opts['filters'] = sql_canonicalize_filters( 'disks', $filters, $joins );
  // open_html_comment( 'sql_query_disks: ' .var_export( $filters_in, true ) );

  foreach( $opts['filters'] as & $atom ) {
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
        error( "undefined key: [$key]", LOG_FLAGS_CODE, 'disks,sql' );
    }
  }

  return sql_query( 'disks', $opts );
}

function sql_one_disk( $filters, $default = false ) {
  return sql_disks( $filters, array( 'default' => $default, 'single_row' => true ) );
}

function sql_delete_disks( $filters, $check = false ) {
  $disks = sql_disks( $filters );
  $problems = array();
  if( ! $disks ) {
    return $problems;
  }
  foreach( $disks as $disk ) {
    $disks_id = $disk['disks_id'];
    $references = sql_references( 'disks', $disks_id );
    if( $references ) {
      $problems[] = 'cannot delete: references exist: '.implode( ', ', array_keys( $references ) );
    }
  }
  if( $check ) {
    return $problems;
  }
  need( ! $problems );
  foreach( $disks as $disk ) {
    $disks_id = $disk['disks_id'];
    sql_delete( 'disks', $disks_id );
    logger( "delete disk [$disks_id]", LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'disks' );
  }
}


////////////////////////////////////
//
// tape-funktionen:
//
////////////////////////////////////

function sql_tapes( $filters = array(), $opts = array() ) {
  $joins = array();
  $selects = sql_default_selects('tapes');
  $opts = default_query_options( 'tapes', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'oid'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'tapes', $filters, $joins );
  foreach( $opts['filters'] as & $atom ) {
    if( adefault( $atom, -1 ) !== 'raw_atom' )
      continue;
    $rel = & $atom[ 0 ];
    $key = & $atom[ 1 ];
    $val = & $atom[ 2 ];
    switch( $key ) {
      default:
        error( "undefined key: [$key]", LOG_FLAGS_CODE, 'tapes,sql' );
    }
  }

  return sql_query( 'tapes', $opts );
}

function sql_one_tape( $filters, $default = false ) {
  return sql_tapes( $filters, array( 'default' => $default, 'single_row' => true ) );
}

function sql_delete_tapes( $filters, $check = false ) {
  $tapes = sql_tapes( $filters );
  $problems = array();
  foreach( $tapes as $tape ) {
    $tapes_id = $tape['tapes_id'];
    $references = sql_references( 'tapes', $tapes_id, 'ignore=tapechunks' );
    if( $references ) {
      $problems[] = 'cannot delete: references exist: '.implode( ', ', array_keys( $references ) );
    }
  }
  if( $check ) {
    return $problems;
  }
  need( ! $problems );
  foreach( $tapes as $tape ) {
    $tapes_id = $t['tapes_id'];
    sql_delete( 'tapechunks', array( 'tapes_id' => $tapes_id ) );
    sql_delete( 'tapes', $tapes_id );
    logger( "delete tape [$tapes_id]", LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'tapes' );
  }
}


////////////////////////////////////
//
// backupjobs-funktionen:
//
////////////////////////////////////

function sql_backupjobs( $filters = array(), $opts = array() ) {
  $joins = array( 'hosts' => 'hosts USING ( hosts_id )' );
  $selects = sql_default_selects( array( 'backupjobs', 'hosts' ) );

  $opts = default_query_options( 'backupjobs', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'profile, fqhostname, path'
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'backupjobs', $filters, $joins );

  return sql_query( 'backupjobs', $opts );
}

function sql_one_backupjob( $filters, $default = false ) {
  return sql_backupjobs( $filters, array( 'single_row' => true, 'default' => $default ) );
}

function sql_delete_backupjobs( $filters, $check = false ) {
  $jobs = sql_backupjobs( $filters );
  $problems = array();
  foreach( $jobs as $j ) {
    $id = $j['backupjobs_id'];
    $references = sql_references( 'backupjobs', $id );
    if( $references ) {
      $problems[] = 'references exist';
    }
  }
  if( $check ) {
    return $problems;
  }
  need( ! $problems );
  foreach( $jobs as $j ) {
    $id = $j['backupjobs_id'];
    sql_delete( 'backupjobs', $id );
  }
  logger( 'sql_delete_backupjobs: '.count( $jobs ).' jobs deleted', LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'backupjobs' );
}

////////////////////////////////////
//
// backupchunks-funktionen:
//
////////////////////////////////////

function sql_backupchunks( $filters = array(), $opts = array() ) {
  $joins = array(
    'tapechunks' => 'LEFT tapechunks USING ( backupchunks_id )'
  , 'tapes' => 'LEFT tapes USING ( tapes_id )'
  , 'chunklabels' => 'LEFT tapes USING ( backupchunks_id )'
  );
  $selects = sql_default_selects( array( 'backupchunks', 'tapes', 'tapechunks', 'chunklabels' ) );
  $selects[] = " ( SELECT COUNT(*) FROM tapechunks WHERE tapechunks.backupchunks_id = backupchunks.backupchunks_id ) AS copies_count ";

  $opts = default_query_options( 'backupchunks', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'backupchunks.chunkarchivedutc'
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'backupchunks', $filters, $joins );

  return sql_query( 'backupchunks', $opts );
}

function sql_one_backupchunk( $filters, $default = false ) {
  return sql_backupchunks( $filters, array( 'single_row' => true, 'default' => $default ) );
}

function sql_delete_backupchunks( $filters, $check = false ) {
  $chunks = sql_backupchunks( $filters );
  $problems = array();
  foreach( $chunks as $c ) {
    $id = $c['backupchunks_id'];
    $references = sql_references( 'backupchunks', $id, 'ignore=chunklabels' );
    if( $references ) {
      $problems[] = 'references exist: ' . implode( ', ', array_keys( $references ) );
    }
  }
  if( $check ) {
    return $problems;
  }
  need( ! $problems );
  foreach( $chunks as $c ) {
    $id = $c['backupchunks_id'];
    sql_delete( 'chunklabels', "backupchunks_id=$id" );
    sql_delete( 'backupchunks', $id );
  }
  logger( 'sql_delete_backupchunks: '.count( $chunks ).' chunks deleted', LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'backupchunks' );
}


////////////////////////////////////
//
// chunklabels-funktionen:
//
////////////////////////////////////

function sql_chunklabels( $filters = array(), $opts = array() ) {
  $joins = array(
    'backupchunks' => 'LEFT tapechunks USING ( backupchunks_id )'
  , 'tapechunks' => 'LEFT tapechunks USING ( backupchunks_id )'
  , 'tapes' => 'LEFT tapes USING ( tapes_id )'
  );
  $selects = sql_default_selects( array( 'chunklabels', 'backupchunks', 'tapes', 'tapechunks' ) );
  $selects[] = " ( SELECT COUNT(*) FROM tapechunks WHERE tapechunks.backupchunks_id = backupchunks.backupchunks_id ) AS copies_count ";

  $opts = default_query_options( 'chunklabels', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'hosts.fqhostname, backupchunks.chunkarchivedutc'
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'chunklabels', $filters, $joins );

  return sql_query( 'chunklabels', $opts );
}

function sql_one_chunklabel( $filters, $default = false ) {
  return sql_chunklabels( $filters, array( 'single_row' => true, 'default' => $default ) );
}




////////////////////////////////////
//
// tapechunks-funktionen:
//
////////////////////////////////////

function sql_tapechunks( $op, $filters_in = array(), $using = array(), $orderby = false ) {
  $joins = array(
    'tapes' => 'tapes USING ( tapes_id )'
  , 'backupchunks' => 'backupchunks USING ( backupchunks_id )'
  , 'chunklabels' => 'chunklabels USING ( backupchunks_id )'
  );
  $selects = sql_default_selects( array( 'tapechunks', 'tapes', 'backupchunks' ), array( 'tapes.cn' => 'tapes_cn' ) );
  $opts = default_query_options( 'tapechunks', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'tapes.tapes_id, chunkwrittenutc'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'tapechunks', $filters, $joins );

  return sql_query( 'tapechunks', $opts );
}

function sql_one_tapechunk( $filters, $default = false ) {
  return sql_tapechunks( $filters, array( 'single_row' => true, 'default' => $default ) );
}

function sql_delete_tapechunks( $filters, $check = false ) {
  $chunks = sql_tapechunks( $filters );
  $problems = array();
  foreach( $chunks as $c ) {
    $id = $c['tapechunks_id'];
    $references = sql_references( 'tapechunks', $id );
  }
  if( $check ) {
    return $problems;
  }
  foreach( $chunks as $c ) {
    $id = $c['tapechunks_id'];
    sql_delete( 'tapechunks', $id );
  }
  logger( 'sql_delete_tapechunks: '.count( $chunks ).' chunks deleted', LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'tapechunks' );
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

function sql_services( $filters = array(), $opts = array() ) {
  $joins = array( 'hosts' => 'LEFT hosts USING ( hosts_id )' );

  $selects = sql_default_selects( 'services' );

  $opts = default_query_options( 'services', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'type_service, description'
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'services', $filters, 'hosts' );

  return sql_query( 'services', $opts );
}

function sql_one_service( $filters, $default = false ) {
  return sql_services( $filters, array( 'default' => $default, 'single_row' => true ) );
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
  $joins['hosts'] = 'LEFT hosts USING ( hosts_id )';
  $joins['people'] = 'LEFT people USING ( people_id )';
  $joins['accountdomains_accounts_relation'] = 'LEFT accountdomains_accounts_relation USING ( accounts_id )';
  $joins['accountdomains'] = 'LEFT accountdomains USING ( accountdomains_id )';

  $selects = sql_default_selects('accounts');
  $selects['fqhostname'] = 'hosts.fqhostname';
  $selects['cn'] = 'people.cn';
  $selects['accountdomains_count'] = "
    ( SELECT count(*) FROM accountdomains_accounts_relation WHERE accountdomains_accounts_relation.accounts_id = accounts.accounts_id )
  ";
  $selects['accountdomains'] = "
    IFNULL( ( SELECT GROUP_CONCAT( accountdomain SEPARATOR ' ' )
      FROM accountdomains_accounts_relation JOIN accountdomains USING (accountdomains_id)
      WHERE accountdomains_accounts_relation.accounts_id = accounts.accounts_id ), ' - ' )
  ";
  $opts = default_query_options( 'accounts', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'uid'
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'accounts', $filters, $joins );
  return sql_query( 'accounts', $opts );
}

function sql_one_account( $filters, $default = false ) {
  return sql_accounts( $filters, array( 'default' => $default, 'single_row' => true ) );
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
        error( "undefined key: [$key]", LOG_FLAGS_CODE, 'accountdomains,sql' );
    }
  }
  switch( $op ) {
    case 'SELECT':
      break;
    case 'COUNT':
      $selects = 'COUNT(*) as count';
      break;
    default:
      error( "undefined op: [$op]", LOG_FLAGS_CODE, 'accountdomains,sql' );
  }
  return sql_query( 'accountdomains', array( 'filters' => $filters, 'selects' => $selects, 'joins' => $joins, 'orderby' => $orderby ) );
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
      $selects = 'COUNT(*) as count';
      break;
    default:
      error( "undefined op: [$op]", LOG_FLAGS_CODE, 'systems,sql' );
  }
  return sql_query( 'systems', array( 'filters' => $filters, 'selects' => $selects, 'joins' => $joins, 'orderby' => $orderby ) );
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
