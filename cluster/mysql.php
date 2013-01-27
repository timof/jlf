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

  $f = sql_canonicalize_filters( 'hosts', $filters
  , $joins + array( 'disks', 'services', 'accounts', 'accountdomains' )
  , $selects
  , array( 'REGEX' => array( '~=', "CONCAT( ';', hosts.fqhostname, ';', hosts.invlabel, ';', hosts.oid, ';', hosts.location, ';', hosts.processor, ';', hosts.os, ';', hosts.ip4, ';' )" ) )
  );

  foreach( $f[ 2 ] as & $atom ) {
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
  }
  $opts = default_query_options( 'hosts', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'fqhostname, sequential_number'
  , 'filters' => $f
  ) );

  return sql_query( 'hosts', $opts );
}


function sql_one_host( $filters, $default = false ) {
  return sql_hosts( $filters, array( 'default' => $default, 'single_row' => true ) );
}

function sql_fqhostname( $filters, $default = false ) {
  return sql_hosts( $filters, array( 'default' => $default, 'single_field' => 'fqhostname' ) );
}

function sql_save_host( $hosts_id, $values, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $opts['update'] = $hosts_id;
  $check = adefault( $opts, 'check' );

  if( isset( $values['ip4_t'] ) ) {
    if( ! isset( $values['ip4'] ) ) {
      $values['ip4'] = ip4_traditional2canonical( $values['ip4_t'] );
    }
    unset( $values['ip4_t'] );
  }
  if( isset( $values['oid_t'] ) ) {
    if( ! isset( $values['oid'] ) ) {
      $values['oid'] = oid_traditional2canonical( $values['oid_t'] );
    }
    unset( $values['oid_t'] );
  }
  if( isset( $values['hostname'] ) || isset( $values['domain'] ) ) {
    if( ! isset( $values['fqhostname'] ) ) {
      $values['fqhostname'] = adefault( $values, 'hostname', '' ) .'.'. adefault( $values, 'domain', '' );
    }
    unset( $values['hostname'] );
    unset( $values['domain'] );
  }
  $host = ( $hosts_id ? sql_one_host( $hosts_id, array() ) : array() );

  $opts['check'] = 1;
  if( ! ( $problems = validate_row( 'hosts', $values, $opts ) ) ) {
    $newvalues = $values + $host;
    if( adefault( $newvalues, 'online' ) ) {
      if( adefault( $values, 'year_decommissioned' ) ) {
        $problems[] = 'conflict: decommissioned host cannot be online';
      }
      if( sql_hosts( array(
        'fqhostname' => $newvalues['fqhostname']
      , 'online'
      , 'hosts_id !=' => $hosts_id
      ) ) ) {
        $problems[] = 'conflict: different host of same name already online';
      }
    }
    if( sql_hosts( array(
      'fqhostname' => $newvalues['fqhostname']
    , 'sequential_number' => $newvalues['sequential_number']
    , 'hosts_id !=' => $hosts_id
    ) ) ) {
      $problems[] = 'conflict: (fqhostname, sequential_number) already exists';
    }
    // more checks?
  }
  // debug( $values, 'sql_save_host' );
  // debug( $problems, 'problems' );
  if( $check ) {
    return $problems;
  }
  need( ! $problems, $problems );
  if( $hosts_id ) {
    sql_update( 'hosts', $hosts_id, $values );
    logger( "updated host [$hosts_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'host', array( 'host' => "hosts_id=$hosts_id" ) );
  } else {
    $hosts_id = sql_insert( 'hosts', $values );
    logger( "new host [$hosts_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'host', array( 'host' => "hosts_id=$hosts_id" ) );
  }
  return $hosts_id;
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
  , 'systems' => 'LEFT systems USING( systems_id )'
  );

  $selects = sql_default_selects( array( 'disks', 'hosts' => 'prefix=host_', 'systems' => 'prefix=system_' ) );
  $selects['location'] = ' IFNULL( hosts.location, disks.location ) ';
  // $selects['fqhostname'] = 'hosts.fqhostname';
  // $selects['systems_type'] = 'systems.type';
  // $selects['systems_arch'] = 'systems.arch';
  // $selects['systems_date_built'] = 'systems.date_built';
  $selects['host_current'] = ' IF( hosts.sequential_number = ( SELECT MAX( sequential_number ) FROM hosts AS subhosts WHERE subhosts.fqhostname = hosts.fqhostname ), 1, 0 ) ';

  $opts = default_query_options( 'disks', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'cn'
  ) );

  $opts['filters'] = sql_canonicalize_filters( 'disks', $filters, $joins, $selects
  , array( 'REGEX' => array( '~=', "CONCAT( ';', disks.cn, ';', disks.oid, ';', disks.sizeGB, ';' )" ) )
  );

  foreach( $opts['filters'][ 1 ] as & $atom ) {
    if( adefault( $atom, -1 ) !== 'raw_atom' )
      continue;
    $rel = & $atom[ 0 ];
    $key = & $atom[ 1 ];
    $val = & $atom[ 2 ];
    switch( $key ) {
      default:
        error( "undefined key: [$key]", LOG_FLAG_CODE, 'disks,sql' );
    }
  }

  return sql_query( 'disks', $opts );
}

function sql_one_disk( $filters, $default = false ) {
  return sql_disks( $filters, array( 'default' => $default, 'single_row' => true ) );
}

function sql_save_disk( $disks_id, $values, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $opts['update'] = $disks_id;
  $check = adefault( $opts, 'check' );

  if( isset( $values['oid_t'] ) ) {
    if( ! isset( $values['oid'] ) ) {
      $values['oid'] = oid_traditional2canonical( $values['oid_t'] );
    }
    unset( $values['oid_t'] );
  }

  $opts['check'] = 1;
  if( ! ( $problems = validate_row( 'disks', $values, $opts ) ) ) {
    // more checks?
  }
  if( $check ) {
    return $problems;
  }
  need( ! $problems, $problems );
  if( $disks_id ) {
    sql_update( 'disks', $disks_id, $values );
    logger( "updated disk [$disks_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'disk', array( 'disk' => "disks_id=$disks_id" ) );
  } else {
    $disks_id = sql_insert( 'disks', $values );
    logger( "new disk [$disks_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'disk', array( 'disk' => "disks_id=$disks_id" ) );
  }
  return $disks_id;
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

  $opts['filters'] = sql_canonicalize_filters( 'tapes', $filters, $joins, $selects
   , array( 'REGEX' => array( '~=', "CONCAT( ';', cn, ';', oid, ';', location, ';', type_tape, ';' )" ) )
  );

  return sql_query( 'tapes', $opts );
}

function sql_one_tape( $filters, $default = false ) {
  return sql_tapes( $filters, array( 'default' => $default, 'single_row' => true ) );
}

function sql_save_tape( $tapes_id, $values, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $opts['update'] = $tapes_id;
  $check = adefault( $opts, 'check' );

  if( isset( $values['oid_t'] ) && ! isset( $values['oid'] ) ) {
    $values['oid'] = $values['oid_t'];
  }
  unset( $values['oid_t'] );
  if( isset( $values['oid'] ) ) {
    $values['oid'] = oid_traditional2canonical( $values['oid'] );
  }
  if( isset( $values['type_tape'] ) ) {
    $values['type_tape'] = strtolower( $values['type_tape'] );
  }
  // debug( $values, 'values' );

  $opts['check'] = 1;
  if( ! ( $problems = validate_row( 'tapes', $values, $opts ) ) ) {
    if( isset( $values['oid'] ) ) {
      if( $tapes_id ) {
        $values['tapes_id'] = $tapes_id;
      }
      if( ( $problems = sql_check_oid( 'tapes', $values, $values['oid'] ) ) ) {
        if( ! $check ) {
          error( "invalid tape OID: [$tapes_id] " . reset( $problems ), LOG_FLAG_INPUT, 'tapes,oid' );
        }
      }
      unset( $values['tapes_id'] );
    }
  }
  if( $check ) {
    return $problems;
  }
  need( ! $problems, $problems );
  if( $tapes_id ) {
    sql_update( 'tapes', $tapes_id, $values );
    logger( "updated tape [$tapes_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'tape', array( 'tape' => "tapes_id=$tapes_id" ) );
  } else {
    $tapes_id = sql_insert( 'tapes', $values );
    logger( "new tape [$tapes_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'tape', array( 'tape' => "tapes_id=$tapes_id" ) );
  }
  return $tapes_id;
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
    $tapes_id = $tape['tapes_id'];
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
  $joins = array( 'hosts' => 'LEFT hosts USING ( hosts_id )' );
  $selects = sql_default_selects( array( 'backupjobs', 'hosts' ) );
  $selects['hostname'] = "LEFT( hosts.fqhostname, LOCATE( '.', hosts.fqhostname ) - 1 )";
  $selects['domain'] = "SUBSTR( hosts.fqhostname, LOCATE( '.', hosts.fqhostname ) + 1 )";

  $opts = default_query_options( 'backupjobs', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'profile, fqhostname, targets'
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'backupjobs', $filters, $joins, $selects );

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

function sql_save_backupjob( $backupjobs_id, $values, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $opts['update'] = $backupjobs_id;
  $check = adefault( $opts, 'check' );

  if( isset( $values['keyhash'] ) ) {
    if( ( ! isset( $values['keyhashfunction'] ) ) && ( ! isset( $values['keyhashvalue'] ) ) ) {
      preg_match( '/^{([a-zA-Z0-9_]+)}([a-fA-F0-9]+)$/', $values['keyhash'], /* & */ $matches );
      if( count( $matches ) == 3 ) {
        $values['keyhashfunction'] = $matches[ 1 ];
        $values['keyhashvalue'] = $matches[ 2 ];
      }
    }
    unset( $values['clearhash'] );
  }

  // debug( $values, 'v' );
  $opts['check'] = 1;
  if( ! ( $problems = validate_row( 'backupjobs', $values, $opts ) ) ) {
    if( ( $hosts_id = adefault( $values, 'hosts_id' ) ) ) { 
      if( ! sql_one_host( $hosts_id, null ) ) {
        $problems[] = "host does not exist: [$hosts_id]";
      }
    }
  }
  if( $check ) {
    return $problems;
  }
  need( ! $problems, $problems );
  if( $backupjobs_id ) {
    sql_update( 'backupjobs', $backupjobs_id, $values );
    logger( "updated backupjob [$backupjobs_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'backupjob', array( 'backupprofileslist' => "backupjobs_id=$backupjobs_id" ) );
  } else {
    $backupjobs_id = sql_insert( 'backupjobs', $values );
    logger( "new backupjob [$backupjobs_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'backupjob', array( 'backupprofileslist' => "backupjobs_id=$backupjobs_id" ) );
  }
  return $backupjobs_id;
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
  , 'hosts' => 'LEFT hosts USING ( hosts_id )'
  , 'chunklabels' => 'LEFT chunklabels USING ( backupchunks_id )'
  );
  $selects = sql_default_selects( array( 'backupchunks', 'tapes', 'hosts' ) );
  $selects['copies_count'] = " ( SELECT COUNT(*) FROM tapechunks WHERE tapechunks.backupchunks_id = backupchunks.backupchunks_id )";

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

function sql_save_backupchunk( $backupchunks_id, $values, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $opts['update'] = $hosts_id;
  $check = adefault( $opts, 'check' );

  if( isset( $values['oid_t'] ) ) {
    if( ! isset( $values['oid'] ) ) {
      $values['oid'] = oid_traditional2canonical( $values['oid_t'] );
    }
    unset( $values['oid_t'] );
  }
  if( isset( $values['clearhash'] ) ) {
    if( ( ! isset( $values['clearhashfunction'] ) ) && ( ! isset( $values['clearhashvalue'] ) ) ) {
      preg_match( '/^{([a-zA-Z0-9_]+)}([a-fA-F0-9]+)$/', $values['clearhash'], /* & */ $matches );
      if( count( $matches ) == 3 ) {
        $values['clearhashfunction'] = $matches[ 1 ];
        $values['clearhashvalue'] = $matches[ 2 ];
      }
    }
    unset( $values['clearhash'] );
  }
  if( isset( $values['crypthash'] ) ) {
    if( ( ! isset( $values['crypthashfunction'] ) ) && ( ! isset( $values['crypthashvalue'] ) ) ) {
      preg_match( '/^{([a-zA-Z0-9_]+)}([a-fA-F0-9]+)$/', $values['crypthash'], /* & */ $matches );
      if( count( $matches ) == 3 ) {
        $values['crypthashfunction'] = $matches[ 1 ];
        $values['crypthashvalue'] = $matches[ 2 ];
      }
    }
    unset( $values['crypthash'] );
  }
  $opts['check'] = 1;
  if( ! ( $problems = validate_row( 'backupchunks', $values, $opts ) ) ) {
    //
  }
  if( $check ) {
    return $problems;
  }
  need( ! $problems, $problems );
  if( $backupchunks_id ) {
    sql_update( 'backupchunks', $backupchunks_id, $values );
    logger( "updated backupchunk [$backupchunks_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'backupchunk', array( 'backupchunks' => "backupchunks_id=$backupchunks_id" ) );
  } else {
    $backupchunks_id = sql_insert( 'backupchunks', $values );
    logger( "new host [$backupchunks_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'backupchunk', array( 'backupchunks' => "backupchunks_id=$backupchunks_id" ) );
  }
  return $backupchunks_id;
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
    sql_delete( 'backupchunks', $id );
  }
  logger( 'sql_delete_backupchunks: '.count( $chunks ).' chunks deleted', LOG_LEVEL_INFO, LOG_FLAG_DELETE, 'backupchunks' );
}


// ////////////////////////////////////
// //
// // chunklabels-funktionen:
// //
// ////////////////////////////////////
// 
// function sql_chunklabels( $filters = array(), $opts = array() ) {
//   $joins = array(
//     'backupchunks' => 'LEFT tapechunks USING ( backupchunks_id )'
//   , 'tapechunks' => 'LEFT tapechunks USING ( backupchunks_id )'
//   , 'tapes' => 'LEFT tapes USING ( tapes_id )'
//   );
//   $selects = sql_default_selects( array( 'chunklabels', 'backupchunks', 'tapes', 'tapechunks' ) );
//   $selects[] = " ( SELECT COUNT(*) FROM tapechunks WHERE tapechunks.backupchunks_id = backupchunks.backupchunks_id ) AS copies_count ";
// 
//   $opts = default_query_options( 'chunklabels', $opts, array(
//     'selects' => $selects
//   , 'joins' => $joins
//   , 'orderby' => 'hosts.fqhostname, backupchunks.chunkarchivedutc'
//   ) );
//   $opts['filters'] = sql_canonicalize_filters( 'chunklabels', $filters, $joins );
// 
//   return sql_query( 'chunklabels', $opts );
// }
// 
// function sql_save_chunklabel( $chunklabels_id, $values, $opts = array() ) {
//   $opts = parameters_explode( $opts );
//   $opts['update'] = $hosts_id;
//   $check = adefault( $opts, 'check' );
// 
//   $opts['check'] = 1;
//   if( ! ( $problems = validate_row( 'chunklabels', $values, $opts ) ) ) {
//     if( isset( $values['hosts_id'] ) ) {
//       if( ! sql_one_host( $values['hosts_id'], NULL ) ) {
//         $problems[] = 'host not found';
//       }
//     }
//     if( isset( $values['backupchunks_id'] ) ) {
//       if( ! sql_one_backupchunk( $values['backupchunks_id'], NULL ) ) {
//         $problems[] = 'backupchunk not found';
//       }
//     }
//   }
//   if( $check ) {
//     return $problems;
//   }
//   need( ! $problems, $problems );
//   if( $chunklabels_id ) {
//     sql_update( 'chunklabels', $chunklabels_id, $values );
//     logger( "updated chunklabel [$chunklabels_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'chunklabel', array( 'backupchunk' => "chunklabels_id=$chunklabels_id" ) );
//   } else {
//     $chunklabels_id = sql_insert( 'chunklabels', $values );
//     logger( "new host [$chunklabels_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'chunklabel', array( 'backupchunk' => "chunklabels_id=$chunklabels_id" ) );
//   }
//   return $chunklabels_id;
// }
// 
// function sql_one_chunklabel( $filters, $default = false ) {
//   return sql_chunklabels( $filters, array( 'single_row' => true, 'default' => $default ) );
// }
// 



////////////////////////////////////
//
// tapechunks-funktionen:
//
////////////////////////////////////

function sql_tapechunks( $filters = array(), $opts = array() ) {
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

function sql_save_tapechunk( $tapechunks_id, $values, $opts = array() ) {
  $opts = parameters_explode( $opts );
  $opts['update'] = $hosts_id;
  $check = adefault( $opts, 'check' );

  $opts['check'] = 1;
  if( ! ( $problems = validate_row( 'tapechunks', $values, $opts ) ) ) {
    if( isset( $values['tapes_id'] ) ) {
      if( ! sql_one_tape( $values['tapes_id'], NULL ) ) {
        $problems[] = 'tape not found';
      }
    }
    if( isset( $values['backupchunks_id'] ) ) {
      if( ! sql_one_backupchunk( $values['backupchunks_id'], NULL ) ) {
        $problems[] = 'backupchunk not found';
      }
    }
  }
  if( $check ) {
    return $problems;
  }
  need( ! $problems, $problems );
  if( $tapechunks_id ) {
    sql_update( 'tapechunks', $tapechunks_id, $values );
    logger( "updated tapechunk [$tapechunks_id]", LOG_LEVEL_INFO, LOG_FLAG_UPDATE, 'tapechunk', array( 'tape' => "tapechunks_id=$tapechunks_id" ) );
  } else {
    $tapechunks_id = sql_insert( 'tapechunks', $values );
    logger( "new host [$tapechunks_id]", LOG_LEVEL_INFO, LOG_FLAG_INSERT, 'tapechunk', array( 'tape' => "tapechunks_id=$tapechunks_id" ) );
  }
  return $tapechunks_id;
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
  $opts['filters'] = sql_canonicalize_filters( 'services', $filters, $joins );

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

function sql_accounts( $filters = array(), $opts = array() ) {
  $joins = array(
    'hosts' => 'LEFT hosts USING ( hosts_id )'
  , 'people' => 'LEFT people USING ( people_id )'
  , 'accountdomains_accounts_relation' => 'LEFT accountdomains_accounts_relation USING ( accounts_id )'
  , 'accountdomains' => 'LEFT accountdomains USING ( accountdomains_id )'
  );

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

function sql_accountdomains( $filters = array(), $opts = array() ) {
  $joins = array(
    'accountdomains_accounts_relation' =>  'LEFT accountdomains_accounts_relation USING ( accountdomains_id )'
  , 'accounts' => 'LEFT accounts USING ( accounts_id )'
  , 'accountdomains_hosts_relation' => 'LEFT accountdomains_hosts_relation USING ( accountdomains_id )'
  , 'hosts' => 'LEFT hosts ON ( hosts.hosts_id = accountdomains_hosts_relation.hosts_id )'
  );

  $selects = sql_default_selects('accountdomains');
  $selects['accounts_count'] = " ( SELECT count(*) FROM accountdomains_accounts_relation
                   WHERE accountdomains_accounts_relation.accountdomains_id = accountdomains.accountdomains_id )";
  $selects['hosts_count'] = " ( SELECT count(*) FROM accountdomains_hosts_relation
                   WHERE accountdomains_hosts_relation.accountdomains_id = accountdomains.accountdomains_id )";

  $opts = default_query_options( 'accountdomains', $opts, array(
    'selects' => $selects
  , 'joins' => $joins
  , 'orderby' => 'accountdomains.accountdomain'
  ) );
  $opts['filters'] = sql_canonicalize_filters( 'accountdomains', $filters, $joins );
  foreach( $opts['filters'][ 1 ] as & $atom ) {
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
        error( "undefined key: [$key]", LOG_FLAG_CODE, 'accountdomains,sql' );
    }
  }
  return sql_query( 'accountdomains', $opts );
}



////////////////////////////////////
//
// systems-funktionen:
//
////////////////////////////////////

function sql_systems( $filters = array(), $opts = array() ) {
  $joins = array( 'disks' => 'LEFT disks USING ( systems_id )' );

  $selects = sql_default_selects('systems');
  $selects['disks_count'] = '( SELECT COUNT(*) FROM disks WHERE disks.systems_id = systems.sysdems_id )';

  $opts['filters'] = sql_canonicalize_filters( 'systems', $filters, $joins );

  return sql_query( 'systems', $opts );
}

function sql_one_system( $filters, $default = false ) {
  return sql_systems( $filters, array( 'default' => $default, 'single_row' => true ) );
}

function sql_delete_systems( $filters ) {
  foreach( sql_systems( $filters ) as $s ) {
    sql_delete( 'systems', array( 'systems_id' => $s['systems_id'] ) );
  }
}



////////////////////////////////////
//
// general functions:
//
////////////////////////////////////



// sql_save(): subproject-specific multiplexer, used from cli-interface:
//
function sql_save( $table, $id, $values, $opts = array() ) {
  $v = array();
  foreach( $values as $key => $val ) {
    $v[ strtolower( $key ) ] = $val;
  }
  $values = $v;
  switch( $table ) {
    case 'host':
    case 'hosts':
      return sql_save_host( $id, $values, $opts );
    case 'backupjob':
    case 'backupjobs':
      return sql_save_backupjob( $id, $values, $opts );
    case 'disk':
    case 'disks':
      return sql_save_disk( $id, $values, $opts );
    case 'tape':
    case 'tapes':
      return sql_save_tape( $id, $values, $opts );
    case 'tapechunk':
    case 'tapechunks':
      return sql_save_tapechunk( $id, $values, $opts );
    case 'backupchunk':
    case 'backupchunks':
      return sql_save_backupchunk( $id, $values, $opts );
    case 'chunklabel':
    case 'chunklabels':
      return sql_save_chunklabel( $id, $values, $opts );
    default:
      error( "unsupported table: [$table]", LOG_FLAG_USER, 'cli' );
  }
}

function get_oid_prefix( $table, $type = false ) {
  global $oid_prefixes;
  need( ( $p = $oid_prefixes[ $table ] ) );
  if( isstring( $p ) ) {
    return $p;
  }
  if( isnumber( $type ) ) {
    $type = sql_query( $table, array(
      'filters' => "{$table}_id=$type"
    , 'single_row' => '1'
    , 'default' => NULL
    ) );
  }
  switch( $table ) {
    case 'tapes':
      if( ( $t = adefault( $type, 'type_tape' ) ) ) {
        $type = $t;
      }
      if( ( $t = adefault( $type, 'value' ) ) ) {
        $type = $t;
      }
      need( isstring( $type ) );
      need( ( $prefix = adefault( $p, $type ) ) );
      return $prefix;
      break;
    default:
      error( "unsupported table: [$table]", LOG_FLAG_CODE, 'oid' );
  }
}

function sql_get_unused_oid( $table, $values = NULL ) {
  $prefix_t = get_oid_prefix( $table, $values );
  $prefix = oid_traditional2canonical( $prefix_t );

  $max = sql_query( $table, array(
    'selects' => 'IFNULL( MAX( oid ), 0 ) as maxoid'
  , 'single_field' => 'maxoid'
  , 'filters' => "oid %= $prefix%"
  , 'default' => "$prefix.0"
  , 'groupby' => false
  ) );
  $nmax = substr( $max, strlen( $prefix ) + 1 );
  if( $nmax ) {
    return $prefix_t . '.' . (string)( 1 + (int)"$nmax" );
  } else {
    return $prefix_t . '.1';
  }
}

function sql_check_oid( $table, $values, $oid ) {
  global $oid_prefixes;

  if( $values && isnumber( $values ) ) {
    $values = sql_query( $table, array(
      'filters' => $values
    , 'single_row' => 1
    , 'default' => null
    ) );
  }
  $id = adefault( $values, $table.'_id', 0 );
  $prefix = oid_traditional2canonical( get_oid_prefix( $table, $values ) ) . '.';
  $oid = oid_traditional2canonical( $oid );
  if( strncmp( $prefix, $oid, strlen( $prefix ) ) ) {
    return array( 'wrong OID prefix' );
  }
  if( sql_query( $table, array( 'filters' => "oid=$oid,{$table}_id!=$id" ) ) ) {
    return array( 'OID already in use' );
  }
  return array();
}

?>
