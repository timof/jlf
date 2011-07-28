<?php

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
      open_td( '', '', inlink( $h['script'], array(
        'text' => $h['text'], 'title' => $h['title'] , 'class' => 'bigbutton'
      ) ) );
  }
}

function mainmenu_header() {
  global $mainmenu;
  foreach( $mainmenu as $h ) { 
    open_li( '', '', inlink( $h['script'], array(
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

function hostslist_view( $filters = array(), $opts = true ) {
  global $script;

  $opts = handle_list_options( $opts, 'hosts', array(
    'nr' => 't', 'id' => 't=0,s'
  , 'fqhostname' => 's,t'
  , 'ip4' => 's,t', 'oid' => 's,t'
  , 'location' => 's,t', 'invlabel' => 's,t'
  , 'disks' => 's,t', 'accounts' => 's,t', 'accountdomains' => 's,t', 'services' => 's,t'
  , 'actions' => 't'
  ) );

  if( ! ( $hosts = sql_hosts( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'no matching hosts' );
    return;
  }
  $count = count( $hosts );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  open_table( 'list oddeven', '', $opts );
    open_tr( 'solidbottom solidtop' );
      open_list_head( 'nr' );
      open_list_head( 'id' );
      open_list_head( 'fqhostname' );
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
        open_list_cell( 'id', $host['hosts_id'], 'class=right' );
        open_list_cell( 'fqhostname', inlink( 'host', array( 'class' => 'href', 'text' => "{$host['fqhostname']} / {$host['sequential_number']}" ) ) );
        open_list_cell( 'ip4', $host['ip4'] );
        open_list_cell( 'oid', $host['oid'] );
        open_list_cell( 'location', $host['location'] );
        open_list_cell( 'invlabel', $host['invlabel'] );
        open_list_cell( 'accountdomains', inlink( 'accountdomainslist', "text= {$host['accountdomains']},class=href,hosts_id=$hosts_id" ), 'class=number' );
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
    'nr' => 't', 'id' => 't=0,s', 'actions' => 't'
  , 'cn' => 't,s'
  , 'host' => 's=fqhostname,t=' . ( $script == 'host' ? '0' : '1' )
  , 'system' => 't'
  , 'location' => 't,s', 'type' => 't,s=type_disk', 'interface' => 't,s=interface_disk'
  , 'size' => 't,s=sizeGB', 'oid' => 't,s'
  ) );

  if( ! ( $disks = sql_disks( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'no matching disks' );
    return;
  }
  $count = count( $disks );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  open_table( 'list oddeven', '', $opts );
    open_tr( 'solidbottom solidtop' );
      open_list_head( 'nr' );
      open_list_head( 'id' );
      open_list_head( 'cn' );
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
        open_list_cell( 'id', $disks_id, 'class=number' );
        open_list_cell( 'cn', inlink( 'disk', "text={$disk['cn']},disks_id=$disks_id" ) );
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
        open_list_cell( 'oid', $disk['oid'] );
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
    'nr' => 't', 'id' => 't=0,s', 'actions' => 't'
  , 'cn' => 't,s', 'type' => 't,s=type_tape', 'oid' => 't,s', 'location' => 't,s'
  , 'good' => 't,s', 'retired' => 't,s'
  ) );

  if( ! ( $tapes = sql_tapes( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'no matching tapes' );
    return;
  }
  $count = count( $tapes );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  open_table( 'list oddeven', '', $opts );
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
        open_list_cell( 'oid', $tape['oid'] );
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
    'id' => 's,t=0', 'nr' => 't'
  , 'type_service' => 't,s', 'description' => 't,s'
  , 'host' => 't,s=fq_hostname'
  ) );

  if( ! ( $services = sql_services( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'no matching entries' );
    return;
  }
  $count = count( $services );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  open_table( 'list oddeven', '', $opts );
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
    'nr' => 't', 'id' => 't=0,s'
  , 'uid' => 's,t', 'uidnumber' => 's,t'
  , 'fqhostname' => 's,t=' . ( $script === 'host' ? '0' : '1' )
  , 'accountdomains' => 't'
  ) );

  if( ! ( $accounts = sql_accounts( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'no matching accounts' );
    return;
  }
  $count = count( $accounts );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  open_table( 'list', '', $opts );
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
    'nr' => 't', 'id' => 't=0,s'
  , 'accountdomain' => 's,t', 'hosts' => 't,s', 'accounts' => 't,s'
  ) );

  if( ! ( $accountdomains = sql_accountdomains( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'no matching accountdomains' );
    return;
  }
  $count = count( $accountdomains );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  open_table( 'list', '', $opts );
    open_list_head( 'accountdomain' );
    open_th( 'hosts' );
    open_th( 'accounts' );

    // $accountdomains = ldap_accountdomains();
    foreach( $accountdomains as $a ) {
      open_tr();
        open_list_cell( 'accountdomain', $a['accountdomain'] );
        open_td( 'hosts', inlink( 'hostslist', array( 'class' => 'href', 'accountdomains_id' => $a['accountdomains_id'], 'text' => $a['hosts_count'] ) ) );
        open_td( 'accounts', inlink( 'accountslist', array( 'class' => 'href', 'accountdomains_id' => $a['accountdomains_id'], 'text' => $a['accounts_count'] ) ) );
    }
  close_table();
}


function tapechunkslist_view( $filters = array(), $opts = true ) {

  $opts = handle_list_options( $opts, 'tchunks', array(
    'nr' => 't', 'id' => 's,t=0'
  , 'oid' => 's,t=0'
  , 'tape' => 't,s=tapes_cn'
  , 'sizeGB' => 't,s'
  , 'blocknumber' => 't,s'
  , 'filenumber' => 't,s'
  , 'chunkwritten' => 't,s'
  , 'crypthash' => array( 't', 's' => "CONCAT( '{', crypthashfunction, '}', crypthashvalue )" )
  , 'clearhash' => array( 't', 's' => "CONCAT( '{', clearhashfunction, '}', clearhashvalue )" )
  ) );
  
  if( ! ( $tapechunks = sql_tapechunks( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'no matching tapechunks' );
    return;
  }
  $count = count( $tapechunks );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  open_table( 'list oddeven', '', $opts );
    open_tr();
      open_list_head( 'nr' );
      open_list_head( 'id' );
      open_list_head( 'tape' );
      open_list_head( 'blocknumber' );
      open_list_head( 'filenumber' );
      open_list_head( 'sizeGB' );
      open_list_head( 'chunkwritten' );
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
        open_list_cell( 'tape', inlink( 'tape', array( 'tapes_id' => $tc['tapes_id'] ) ) );
        open_list_cell( 'blocknumber', $tc['blocknumber'], 'class=number' );
        open_list_cell( 'filenumber', $tc['filenumber'], 'class=number' );
        open_list_cell( 'sizeGB', $tc['sizeGB'], 'class=number' );
        open_list_cell( 'chunkwritten', $tc['chunkwritten'] );
        open_list_cell( 'crypthash', '{'.$tc['crypthashfunktion'].'}'.$tc['crypthashvalue'] );
        open_list_cell( 'clearhash', '{'.$tc['clearhashfunktion'].'}'.$tc['clearhashvalue'] );
        open_list_cell( 'actions' );
    }
  close_table();
}

function backupchunkslist_view( $filters = array(), $opts = true ) {

  $opts = handle_list_options( $opts, 'bchunks', array(
    'nr' => 't', 'id' => 's,t=0'
  , 'oid' => 's,t=0'
  , 'sizeGB' => 't,s'
  , 'utc' => 't,s'
  , 'copies' => 't,s'
  , 'crypthash' => array( 't', 's' => "CONCAT( '{', crypthashfunction, '}', crypthashvalue )" )
  , 'clearhash' => array( 't', 's' => "CONCAT( '{', clearhashfunction, '}', clearhashvalue )" )
  ) );
  
  if( ! ( $backupchunks = sql_backupchunks( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'no matching backupchunks' );
    return;
  }
  $count = count( $backupchunks );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  open_table( 'list oddeven', '', $opts );
    open_tr();
      open_list_head( 'nr' );
      open_list_head( 'id' );
      open_list_head( 'sizeGB' );
      open_list_head( 'utc' );
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
        open_list_cell( 'utc', $bc['utc'] );
        open_list_cell( 'copies', inlinks( 'tapechunkslist', array( 'backupchunks_id' => $id ) ), 'class=number' );
        open_list_cell( 'crypthash', '{'.$tc['crypthashfunktion'].'}'.$tc['crypthashvalue'] );
        open_list_cell( 'clearhash', '{'.$tc['clearhashfunktion'].'}'.$tc['clearhashvalue'] );
        open_list_cell( 'actions' );
    }
  close_table();
}


// logbook:
//
function logbook_view( $filters = array(), $opts = true ) {

  $opts = handle_list_options( $opts, 'log', array( 
    'nr' => 't', 'id' => 't,s=logbook_id'
  , 'session' => 't,s=sessions_id', 'timestamp' => 't,s'
  , 'thread' => 't,s', 'window' => 't,s', 'script' => 't,s'
  , 'event' => 't,s', 'note' => 't,s', 'actions' => 't'
  ) );

  if( ! ( $logbook = sql_logbook( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'no matching entries' );
    return;
  }
  $count = count( $logbook );
  $limits = handle_list_limits( $opts, $count );
  $opts['limits'] = & $limits;

  open_table( 'list hfill oddeven', '', $opts );
    open_tr();
      open_list_head( 'nr' );
      open_list_head( 'id' );
      open_list_head( 'session' );
      open_list_head( 'timestamp' );
      open_list_head( 'thread', "<div>thread</div><div class='small'>parent</div>" );
      open_list_head( 'window', "<div>window</div><div class='small'>parent</div>" );
      open_list_head( 'script', "<div>script</div><div class='small'>parent</div>" );
      open_list_head( 'event' );
      open_list_head( 'note');
      open_list_head( 'actions' );

    foreach( $logbook as $l ) {
      if( $l['nr'] < $limits['limit_from'] )
        continue;
      if( $l['nr'] > $limits['limit_to'] )
        break;
      open_tr();
        open_list_cell( 'nr', $l['nr'], 'class=number' );
        open_list_cell( 'id', $l['logbook_id'], 'class=number' );
        open_list_cell( 'session', $l['sessions_id'], 'class=number' );
        open_list_cell( 'timestamp', $l['timestamp'], 'class=right' );
        open_list_cell( 'thread', false, 'class=center' );
          open_div( 'center', '', $l['thread'] );
          open_div( 'center small', '', $l['parent_thread'] );
        open_list_cell( 'window', false, 'class=center' );
          open_div( 'center', '', $l['window'] );
          open_div( 'center small', '', $l['parent_window'] );
        open_list_cell( 'script', false, 'class=center' );
          open_div( 'center', '', $l['script'] );
          open_div( 'center small', '', $l['parent_script'] );
        open_list_cell( 'event', $l['event'] );
        open_list_cell( 'note' );
          if( strlen( $l['note'] ) > 100 )
            $s = substr( $l['note'], 0, 100 ).'...';
          else
            $s = $l['note'];
          if( $l['stack'] )
            $s .= ' [stack]';
          echo inlink( 'logentry', array( 'class' => 'card', 'text' => $s, 'logbook_id' => $l['logbook_id'] ) );
        open_list_cell( 'actions' );
          echo inlink( '!submit', 'class=button,text=prune,action=prune,message='.$l['logbook_id'] );
    }
  close_table();
}

?>
