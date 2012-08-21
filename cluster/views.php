<?php


function hostslist_view( $filters = array(), $opts = true ) {
  global $script;

  $opts = handle_list_options( $opts, 'hosts', array(
    'nr' => 't', 'id' => 't=0,s=hosts_id'
  , 'fqhostname' => 's,t'
  , 'ip4' => 's,t', 'oid' => 's,t'
  , 'location' => 's,t', 'invlabel' => 's,t'
  , 'disks' => 's,t', 'accounts' => 's,t', 'accountdomains' => 's,t', 'services' => 's,t'
  , 'year_manufactured' => 't=0,s', 'year_decommissioned' => 't=0,s'
  , 'actions' => 't'
  ) );

  if( ! ( $hosts = sql_hosts( $filters, array( 'orderby' => $opts['orderby_sql'] ) ) ) ) {
    open_div( '', 'no matching hosts' );
    return;
  }
  $count = count( $hosts );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $opts['class'] = 'list oddeven ' . adefault( $opts, 'class', '' );
  open_table( $opts );
    open_tr( 'solidbottom solidtop' );
      open_list_head( 'nr' );
      open_list_head( 'id' );
      open_list_head( 'fqhostname' );
      open_list_head( 'year_manufactured', 'manufactured' );
      open_list_head( 'year_decommissioned', 'decommissioned' );
      open_list_head( 'ip4' );
      open_list_head( 'oid' );
      open_list_head( 'location' );
      open_list_head( 'invlabel' );
      open_list_head( 'accountdomains' );
      open_list_head( 'accounts' );
      open_list_head( 'disks' );
      open_list_head( 'services' );
      open_list_head( 'actions' );
    foreach( $hosts as $host ) {
      if( $host['nr'] < $limits['limit_from'] )
        continue;
      if( $host['nr'] > $limits['limit_to'] )
        break;
      $hosts_id = $host['hosts_id'];
      // $accountdomains = ldap_accountdomains_host( "cn={$host['fqhostname']},ou=hosts," . LDAP_BASEDN );
      open_tr();
        open_list_cell( 'nr', $host['nr'], 'class=number' );
        open_list_cell( 'id', inlink( 'host', array( 'class' => 'href', 'text' => "{$host['hosts_id']}", 'hosts_id' => $hosts_id ) ), 'class=number' );
        open_list_cell( 'fqhostname', inlink( 'host', array( 'class' => 'href', 'text' => "{$host['fqhostname']} / {$host['sequential_number']}", 'hosts_id' => $hosts_id ) ) );
        open_list_cell( 'year_manufactured', $host['year_manufactured'], 'class=number' );
        open_list_cell( 'year_decommissioned', ( $host['year_decommissioned'] ? $host['year_decommissioned'] : '-' ), 'class=number' );
        open_list_cell( 'ip4', $host['ip4'] );
        open_list_cell( 'oid', oid_canonical2traditional( $host['oid'] ) );
        open_list_cell( 'location', $host['location'] );
        open_list_cell( 'invlabel', $host['invlabel'] );
        open_list_cell( 'accountdomains', inlink( 'accountdomainslist', "text= {$host['accountdomains']},class=href,hosts_id=$hosts_id" ) );
        open_list_cell( 'accounts', inlink( 'accountslist', "text= {$host['accounts_count']},class=href,hosts_id=$hosts_id" ), 'class=number' );
        open_list_cell( 'disks', inlink( 'diskslist', "text= {$host['disks_count']},class=href,hosts_id=$hosts_id" ), 'class=number' );
        open_list_cell( 'services', inlink( 'serviceslist', "text= {$host['services_count']},class=href,hosts_id=$hosts_id" ), 'class=number' );
        open_list_cell( 'actions' );
          if( $script == 'hostslist' ) {
            echo inlink( '!submit', 'class=drop,confirm=delete host?,action=deleteHost,message='.$hosts_id );
          }
    }
  close_table();
}

function diskslist_view( $filters = array(), $opts = true ) {
  global $script;

  $opts = handle_list_options( $opts, 'disks', array(
    'nr' => 't', 'id' => 't=0,s=disks_id'
  , 'cn' => 't,s'
  , 'host' => 's=fqhostname,t=' . ( $script == 'host' ? '0' : '1' )
  , 'system' => 't'
  , 'location' => 't,s', 'type' => 't,s=type_disk', 'interface' => 't,s=interface_disk'
  , 'size' => 't,s=sizeGB', 'oid' => 't,s'
  , 'year_manufactured' => 't=0,s', 'year_decommissioned' => 't=0,s'
  , 'actions' => 't'
  ) );

  if( ! ( $disks = sql_disks( $filters, array( 'orderby' => $opts['orderby_sql'] ) ) ) ) {
    open_div( '', 'no matching disks' );
    return;
  }
  $count = count( $disks );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = $limits;

  $opts['class'] = 'list oddeven ' . adefault( $opts, 'class', '' );

  // debug( $opts, 'opts' );
  open_table( $opts );
    open_tr( 'solidbottom solidtop' );
      open_list_head( 'nr' );
      open_list_head( 'id' );
      open_list_head( 'cn' );
      open_list_head( 'year_manufactured', 'manufactured' );
      open_list_head( 'year_decommissioned', 'decommissioned' );
      open_list_head( 'host' );
      open_list_head( 'location' );
      open_list_head( 'type' );
      open_list_head( 'interface' );
      open_list_head( 'size', 'size / GB' );
      open_list_head( 'oid' );
      open_list_head( 'system' );
      open_list_head( 'actions' );

    foreach( $disks as $disk ) {
      if( $disk['nr'] < $limits['limit_from'] )
        continue;
      if( $disk['nr'] > $limits['limit_to'] )
        break;
      $disks_id = $disk['disks_id'];
      $hosts_id = $disk['hosts_id'];
      open_tr();
        open_list_cell( 'nr', $disk['nr'], 'class=number' );
        open_list_cell( 'id', inlink( 'disk', "text={$disk['disks_id']},disks_id=$disks_id" ), 'class=number' );
        open_list_cell( 'cn', inlink( 'disk', "text={$disk['cn']},disks_id=$disks_id" ) );
        open_list_cell( 'year_manufactured', $disk['year_manufactured'], 'class=number' );
        open_list_cell( 'year_decommissioned', ( $disk['year_decommissioned'] ? $disk['year_decommissioned'] : '-' ), 'class=number' );
        open_list_cell( 'host' );
          if( $hosts_id ) {
            echo inlink( 'host', "hosts_id=$hosts_id,class=href,text=".sql_fqhostname( $hosts_id ) );
          } else {
            echo "(none)";
          }
        open_list_cell( 'location', $disk['location'] );
        open_list_cell( 'type', $disk['type_disk'] );
        open_list_cell( 'interface', $disk['interface_disk'] );
        open_list_cell( 'size', $disk['sizeGB'], 'class=number' );
        open_list_cell( 'oid', oid_canonical2traditional( $disk['oid'] ) );
        open_list_cell( 'system', "{$disk['systems_type']}.{$disk['systems_arch']}.{$disk['systems_date_built']}" );
        open_list_cell( 'actions' );
          if( $script == 'diskslist' ) {
            echo inlink( '!submit', 'class=drop,confirm=delete disk?,action=deleteDisk,message='.$disks_id );
          }
    }
  close_table();
}

function tapeslist_view( $filters = array(), $opts = true ) {
  global $script;

  $opts = handle_list_options( $opts, 'tapes', array(
    'nr' => 't', 'id' => 't=0,s=tapes_id', 'actions' => 't'
  , 'cn' => 't,s', 'type' => 't,s=type_tape', 'oid' => 't,s', 'location' => 't,s'
  , 'good' => 't,s', 'retired' => 't,s'
  ) );

  if( ! ( $tapes = sql_tapes( $filters, array( 'orderby' => $opts['orderby_sql'] ) ) ) ) {
    open_div( '', 'no matching tapes' );
    return;
  }
  $count = count( $tapes );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $opts['class'] = 'list oddeven ' . adefault( $opts, 'class', '' );
  open_table( $opts );
    open_list_head( 'nr' );
    open_list_head( 'id' );
    open_list_head( 'cn' );
    open_list_head( 'type' );
    open_list_head( 'oid' );
    open_list_head( 'location' );
    open_list_head( 'good' );
    open_list_head( 'retired' );
    open_list_head( 'actions' );

    foreach( $tapes as $tape ) {
      if( $tape['nr'] < $limits['limit_from'] )
        continue;
      if( $tape['nr'] > $limits['limit_to'] )
        break;
      $tapes_id = $tape['tapes_id'];
      open_tr();
        open_list_cell( 'nr', $tape['nr'], 'class=number' );
        open_list_cell( 'id', $tapes_id, 'class=number' );
        open_list_cell( 'cn', $tape['cn'] );
        open_list_cell( 'type', $tape['type_tape'] );
        open_list_cell( 'oid', oid_canonical2traditional( $tape['oid'] ) );
        open_list_cell( 'location', $disk['location'] );
        open_list_cell( 'good', $disk['good'] );
        open_list_cell( 'retired', $disk['retired'] );
        open_list_cell( 'actions' );
          echo inlink( 'tape', "class=edit,text=,tapes_id=$tapes_id" );
          if( $script == 'tapeslist' ) {
            echo inlink( '!submit', "class=drop,confirm=delete tape?,action=deleteTape,message=$tapes_id" );
          }
    }
  close_table();
}

function serviceslist_view( $filters = array(), $opts = true ) {
  global $script;

  $opts = handle_list_options( $opts, 'services', array(
    'id' => 's=services_id,t=0', 'nr' => 't'
  , 'type_service' => 't,s', 'description' => 't,s'
  , 'host' => 't,s=fq_hostname'
  ) );

  if( ! ( $services = sql_services( $filters, array( 'orderby' => $opts['orderby_sql'] ) ) ) ) {
    open_div( '', 'no matching entries' );
    return;
  }
  $count = count( $services );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $opts['class'] = 'list oddeven ' . adefault( $opts, 'class', '' );
  open_table( $opts );
    open_list_head( 'type_service' );
    open_list_head( 'description' );
    open_list_head( 'host' );
    open_list_head( 'actions' );

    foreach( $services as $service ) {
      if( $l['nr'] < $limits['limit_from'] )
        continue;
      if( $l['nr'] > $limits['limit_to'] )
        break;
      $services_id = $service['services_id'];
      $hosts_id = $service['hosts_id'];
      open_tr();
        open_list_cell( 'type_service', $service['type_service'] );
        open_list_cell( 'description', $service['description'] );
        open_list_cell( 'host' );
        if( $hosts_id ) {
          echo inlink( 'host', array( 'hosts_id' => $hosts_id, 'class' => 'href', 'text' => $service['fq_hostname'] ) );
        } else {
          echo " - ";
        }
        open_list_cell( 'actions' );
          echo inlink( 'service', "class=edit,text=,services_id=$services_id" );
          if( $script == 'serviceslist' ) {
            echo inlink( '!submit', "class=drop,confirm=delete service?,action=deleteService,message=$services_id" );
          }
    }
  close_table();
}


function accountslist_view( $filters = array(), $opts = true ) {
  global $script;

  $opts = handle_list_options(  $opts, 'accounts', array(
    'nr' => 't', 'id' => 't=0,s=accounts_id'
  , 'uid' => 's,t', 'uidnumber' => 's,t'
  , 'fqhostname' => 's,t=' . ( $script === 'host' ? '0' : '1' )
  , 'accountdomains' => 't'
  ) );

  if( ! ( $accounts = sql_accounts( $filters, array( 'orderby' => $opts['orderby_sql'] ) ) ) ) {
    open_div( '', 'no matching accounts' );
    return;
  }
  $count = count( $accounts );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $opts['class'] = 'list oddeven ' . adefault( $opts, 'class', '' );
  open_table( $opts );
    open_tr();
      open_list_head( 'nr' );
      open_list_head( 'id' );
      open_list_head( 'cn' );
      open_list_head( 'uid' );
      open_list_head( 'uidnumber' );
      open_list_head( 'fqhostname' );
      open_list_head( 'accountdomains' );

    foreach( $accounts as $account ) {
      open_tr();
        open_list_cell( 'nr', $account['nr'], 'class=number' );
        open_list_cell( 'id', $account['accounts_id'], 'class=number' );
        open_list_cell( 'cn', $account['cn'] );
        open_list_cell( 'uid', $account['uid'] );
        open_list_cell( 'uidnumber', $account['uidnumber'], 'class=number' );
        open_list_cell( 'fqhostname', inlink( 'host', array( 'text' => $account['fqhostname'], 'hosts_id' => $account['hosts_id'] ) ) );
        open_list_cell( 'accountdomains', $account['accountdomains'] );
    }
  close_table();
}

function accountdomainslist_view( $filters = array(), $opts = true ) {

  $opts = handle_list_options( $opts, 'accountdomains', array(
    'nr' => 't', 'id' => 't=0,s=accountdomains_id'
  , 'accountdomain' => 's,t', 'hosts' => 't,s=hosts_count', 'accounts' => 't,s=accounts_count'
  ) );

  if( ! ( $accountdomains = sql_accountdomains( $filters, array( 'orderby' => $opts['orderby_sql'] ) ) ) ) {
    open_div( '', 'no matching accountdomains' );
    return;
  }
  $count = count( $accountdomains );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $opts['class'] = 'list oddeven ' . adefault( $opts, 'class', '' );
  open_table( $opts );
    open_list_head( 'nr' );
    open_list_head( 'accountdomain' );
    open_list_head( 'hosts' );
    open_list_head( 'accounts' );

    // $accountdomains = ldap_accountdomains();
    foreach( $accountdomains as $a ) {
      open_tr();
        open_list_cell( 'nr', $a['nr'] );
        open_list_cell( 'accountdomain', $a['accountdomain'] );
        open_list_cell( 'hosts', inlink( 'hostslist', array( 'class' => 'href', 'accountdomains_id' => $a['accountdomains_id'], 'text' => $a['hosts_count'] ) ) );
        open_list_cell( 'accounts', inlink( 'accountslist', array( 'class' => 'href', 'accountdomains_id' => $a['accountdomains_id'], 'text' => $a['accounts_count'] ) ) );
    }
  close_table();
}


function tapechunkslist_view( $filters = array(), $opts = true ) {

  $opts = handle_list_options( $opts, 'tchunks', array(
    'nr' => 't', 'id' => 's=tapechunks_id,t=0'
  , 'oid' => 's,t=0'
  , 'tape' => 't,s=tapes_cn'
  , 'sizeGB' => 't,s'
  , 'blocknumber' => 't,s'
  , 'filenumber' => 't,s'
  , 'chunkwrittenutc' => 't,s'
  , 'chunkarchivedutc' => 't,s'
  , 'crypthash' => array( 't', 's' => "CONCAT( '{', crypthashfunction, '}', crypthashvalue )" )
  , 'clearhash' => array( 't', 's' => "CONCAT( '{', clearhashfunction, '}', clearhashvalue )" )
  ) );
  
  if( ! ( $tapechunks = sql_tapechunks( $filters, array( 'orderby' => $opts['orderby_sql'] ) ) ) ) {
    open_div( '', 'no matching tapechunks' );
    return;
  }
  $count = count( $tapechunks );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $opts['class'] = 'list oddeven ' . adefault( $opts, 'class', '' );
  open_table( $opts );
    open_tr();
      open_list_head( 'nr' );
      open_list_head( 'id' );
      open_list_head( 'oid' );
      open_list_head( 'tape' );
      open_list_head( 'blocknumber' );
      open_list_head( 'filenumber' );
      open_list_head( 'sizeGB' );
      open_list_head( 'chunkarchivedutc', 'archived' );
      open_list_head( 'chunkwrittenutc', 'written' );
      open_list_head( 'crypthash' );
      open_list_head( 'clearhash' );
      open_list_head( 'actions' );

    foreach( $tapechunks as $tc ) {
      if( $tc['nr'] < $limits['limit_from'] )
        continue;
      if( $tc['nr'] > $limits['limit_to'] )
        break;
      open_tr();
        open_list_cell( 'nr', $tc['nr'], 'class=number' );
        open_list_cell( 'id', $tc['tapechunks_id'], 'class=number' );
        open_list_cell( 'oid', inlink( 'backupchunk', array( $tc['tapechunks_id'], 'text' => $tc['oid'] ), 'class=number' ) );
        open_list_cell( 'tape', inlink( 'tape', array( 'tapes_id' => $tc['tapes_id'], 'text' => $tc['tapes_cn'] ) ) );
        open_list_cell( 'blocknumber', $tc['blocknumber'], 'class=number' );
        open_list_cell( 'filenumber', $tc['filenumber'], 'class=number' );
        open_list_cell( 'sizeGB', $tc['sizeGB'], 'class=number' );
        open_list_cell( 'chunkarchivedutc', $tc['chunkarchivedutc'] );
        open_list_cell( 'chunkwrittenutc', $tc['chunkwrittenutc'] );
        open_list_cell( 'crypthash', '{'.$tc['crypthashfunktion'].'}'.$tc['crypthashvalue'] );
        open_list_cell( 'clearhash', '{'.$tc['clearhashfunktion'].'}'.$tc['clearhashvalue'] );
        open_list_cell( 'actions' );
    }
  close_table();
}

function backupchunkslist_view( $filters = array(), $opts = true ) {

  $opts = handle_list_options( $opts, 'bchunks', array(
    'nr' => 't', 'id' => 's=backupchunks_id,t=0'
  , 'oid' => 's,t=0'
  , 'sizeGB' => 't,s'
  , 'chunkarchivedutc' => 't,s'
  , 'copies' => 't,s'
  , 'crypthash' => array( 't', 's' => "CONCAT( '{', crypthashfunction, '}', crypthashvalue )" )
  , 'clearhash' => array( 't', 's' => "CONCAT( '{', clearhashfunction, '}', clearhashvalue )" )
  ) );
  
  if( ! ( $backupchunks = sql_backupchunks( $filters, array( 'orderby' => $opts['orderby_sql'] ) ) ) ) {
    open_div( '', 'no matching backupchunks' );
    return;
  }
  $count = count( $backupchunks );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $opts['class'] = 'list oddeven ' . adefault( $opts, 'class', '' );
  open_table( $opts );
    open_tr();
      open_list_head( 'nr' );
      open_list_head( 'id' );
      open_list_head( 'sizeGB' );
      open_list_head( 'chunkarchivedutc', 'archived' );
      open_list_head( 'copies' );
      open_list_head( 'crypthash' );
      open_list_head( 'clearhash' );
      open_list_head( 'actions' );

    foreach( $bakcupchunks as $bc ) {
      if( $bc['nr'] < $limits['limit_from'] )
        continue;
      if( $bc['nr'] > $limits['limit_to'] )
        break;
      $id = $bc['backupchunks_id'];
      open_tr();
        open_list_cell( 'nr', $bc['nr'], 'class=number' );
        open_list_cell( 'id', $id, 'class=number' );
        open_list_cell( 'sizeGB', $bc['sizeGB'], 'class=number' );
        open_list_cell( 'chunkarchivedutc', $bc['chunkarchivedutc'] );
        open_list_cell( 'copies', inlinks( 'tapechunkslist', array( 'backupchunks_id' => $id, 'text' => $tc['copies_count'] ) ), 'class=number' );
        open_list_cell( 'crypthash', '{'.$tc['crypthashfunktion'].'}'.$tc['crypthashvalue'] );
        open_list_cell( 'clearhash', '{'.$tc['clearhashfunktion'].'}'.$tc['clearhashvalue'] );
        open_list_cell( 'actions' );
    }
  close_table();
}

// function backupprofileslist_view( $filters = array(), $opts = true ) {
//   $opts = handle_list_options( $opts, 'backupprofiles', array(
//     'nr' => 't'
//   , 'profile' => 't,s'
//   ) );
//   if( ( $select = adefault( $opts, 'select' ) ) ) {
//     $selected_profile = adefault( $select, 'value', '' );
//     need( $select['cgi_name'] );
//   } else {
//     $selected_profile = false;
//   }
// 
//   if( ! ( $profiles = sql_backupjobs( $filters, array( 'orderby' => $opts['orderby_sql'], 'groupby' => 'profile' ) ) ) ) {
//     open_div( '', 'no matching profiles' );
//     return;
//   }
//   // debug( $profiles, 'p' );
//   $count = count( $profiles );
//   $limits = handle_list_limits( $opts, $count );
//   $opts['limits'] = & $limits;
// 
//   $opts['class'] = 'list oddeven ' . adefault( $opts, 'class', '' );
//   open_table( $opts );
//     open_tr();
//       open_list_head( 'nr' );
//       open_list_head( 'profile' );
// 
//     foreach( $profiles as $p ) {
//       if( $p['nr'] < $limits['limit_from'] )
//         continue;
//       if( $p['nr'] > $limits['limit_to'] )
//         break;
// 
//       if( $selected_profile !== false ) {
//         open_tr( array( 
//           'class' => 'trselectable ' . ( $p['profile'] == $selected_profile ? 'trselected' : 'untrselected' )
//         , 'onclick' => inlink( '!submit', array( 'context' => 'js', $select['cgi_name'] => $p['profile'] ) )
//         ) );
//       } else {
//         open_tr();
//       }
//         open_list_cell( 'nr', $p['nr'], 'class=number' );
//         if( $select ) {
//           open_list_cell( 'profile', inlink( '!submit', array(
//             'text' => $p['profile'], 'class' => 'href', $select['cgi_name'] => $p['profile']
//           ) ) );
//         } else {
//           open_list_cell( 'profile', $p['profile'] );
//         }
//     }
//   close_table();
// }
// 

function backupjobslist_view( $filters = array(), $opts = true ) {
  global $options;

  $opts = handle_list_options( $opts, 'backupjobs', array(
    'nr' => 't'
  , 'id' => 's=backupjobs_id,t=0'
  , 'profile' => 't,s'
  , 'host' => 't,s=fqhostname'
  , 'target' => 't,s'
  , 'cryptcommand' => 't'
  , 'keyname' => 't,s'
  , 'keyhash' => 't=0'
  ) );
  if( ( $select = adefault( $opts, 'select' ) ) ) {
    $selected_id = adefault( $select, 'value', '' );
    need( $select['cgi_name'] );
  } else {
    $selected_id = false;
  }

  if( ! ( $backupjobs = sql_backupjobs( $filters, array( 'orderby' => $opts['orderby_sql'] ) ) ) ) {
    open_div( '', 'no matching backupjobs' );
    return;
  }
  $count = count( $backupjobs );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  $opts['class'] = 'list oddeven ' . adefault( $opts, 'class', '' );
  open_table( $opts );
    open_tr();
      open_list_head( 'nr' );
      open_list_head( 'id' );
      open_list_head( 'profile' );
      open_list_head( 'host' );
      open_list_head( 'target' );
      open_list_head( 'cryptcommand' );
      open_list_head( 'keyname' );
      open_list_head( 'keyhash' );
      open_list_head( 'actions' );

    foreach( $backupjobs as $j ) {
      if( $j['nr'] < $limits['limit_from'] )
        continue;
      if( $j['nr'] > $limits['limit_to'] )
        break;
      $id = $j['backupjobs_id'];

      if( $selected_id !== false ) {
        open_tr( array(
          'class' => 'trselectable ' . ( $id == $selected_id ? 'trselected' : 'trunselected' )
        , 'onclick' => inlink( '!submit', array( 'context' => 'js', $select['cgi_name'] => $id ) )
        ) );
      } else {
        open_tr();
      }
        open_list_cell( 'nr', $j['nr'], 'class=number' );
        open_list_cell( 'id', $id, 'class=number' );
        open_list_cell( 'profile', $j['profile'] );
        open_list_cell( 'host', $j['fqhostname'] );
        open_list_cell( 'target', $j['target'] );
        open_list_cell( 'cryptcommand', $j['cryptcommand'] );
        open_list_cell( 'keyname', $j['keyname'] );
        open_list_cell( 'keyhash', '{'.$j['keyhashfunction'].'}'.$j['keyhashvalue'] );
        open_list_cell( 'actions' );
          if( defined( 'OPTION_DO_EDIT' ) ) {
            echo inlink( '', 'class=drop,text=,action=deleteBackupjob,confirm=really delete?,message='.$id );
            echo inlink( '', 'class=edit,text=,backupjobs_id='.$id.',action=reset,options='.( $options | OPTION_DO_EDIT ) );
          }
    }
  close_table();
}



$mainmenu = array();

$mainmenu[] = array( 'script' => "hostslist",
     "title" => "hosts",
     "text" => "hosts" );

$mainmenu[] = array( 'script' => "diskslist",
     "title" => "disks",
     "text" => "disks" );

$mainmenu[] = array( 'script' => "tapeslist",
     "title" => "tapes",
     "text" => "tapes" );

$mainmenu[] = array( 'script' => "backupprofileslist",
     "title" => "backupprofileslist",
     "text" => "backupprofileslist" );

$mainmenu[] = array( 'script' => "tapechunkslist",
     "title" => "tapechunkslist",
     "text" => "tapechunkslist" );

$mainmenu[] = array( 'script' => "backupchunkslist",
     "title" => "backupchunkslist",
     "text" => "backupchunkslist" );

$mainmenu[] = array( 'script' => "accountslist",
     "title" => "accounts",
     "text" => "accounts" );

$mainmenu[] = array( 'script' => "accountdomainslist",
     "title" => "accountdomains",
     "text" => "accountdomains" );

$mainmenu[] = array( 'script' => "serviceslist",
     "title" => "services",
     "text" => "services" );

$mainmenu[] = array( 'script' => "sync",
     "title" => "sync",
     "text" => "sync" );

$mainmenu[] = array( 'script' => "logbook",
     "title" => "logbook",
     "text" => "logbook" );


function mainmenu_fullscreen() {
  global $mainmenu;
  foreach( $mainmenu as $h ) { 
    open_tr();
      open_td( '', inlink( $h['script'], array(
        'text' => $h['text'], 'title' => $h['title'] , 'class' => 'bigbutton'
      ) ) );
  }
}

function mainmenu_header() {
  global $mainmenu;
  foreach( $mainmenu as $h ) { 
    open_li( '', inlink( $h['script'], array(
      'text' => $h['text'], 'title' => $h['title'] , 'class' => 'href'
    ) ) );
  }
}

function window_title() {
  return $GLOBALS['window'] . '/' . $GLOBALS['thread'];
}

function window_subtitle() {
  return $GLOBALS['script'] . '/' . $GLOBALS['thread'];
}

?>
