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


function hostslist_view( $filters = array(), $opts = true ) {
  global $script;

  $opts = handle_list_options( $opts, array( 'fqhostname', 'ip4', 'oid', 'location', 'invlabel' ) );

  if( ! ( $hosts = sql_hosts( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'no matching hosts' );
    return;
  }
  $count = count( $hosts );
  $limits = handle_list_limits( $opts, $count );

  open_table( 'list oddeven', '', $limits );
    open_tr( 'solidbottom solidtop' );
      open_th( '','', 'fqhostname', 'fqhostname', $opts['sort_prefix'] );
      open_th( '','', 'ip4', 'ip4', $opts['sort_prefix'] );
      open_th( '','', 'oid', 'oid', $opts['sort_prefix'] );
      open_th( '','', 'location', 'location', $opts['sort_prefix'] );
      open_th( '','', 'invlabel', 'invlabel', $opts['sort_prefix'] );
      open_th( '','', 'accountdomains' );
      open_th( '','', 'accounts' );
      open_th( '','', 'disks' );
      open_th( '','', 'services' );
      open_th( '','', 'actions' );
    foreach( $hosts as $host ) {
      if( $host['nr'] <= $limits['limit_from'] )
        continue;
      if( $host['nr'] > $limits['limit_to'] )
        break;
      $hosts_id = $host['hosts_id'];
      // $accountdomains = ldap_accountdomains_host( "cn={$host['fqhostname']},ou=hosts," . LDAP_BASEDN );
      open_tr();
        open_td( 'left', '', inlink( 'host', array( 'class' => 'href', 'text' => "{$host['fqhostname']} / {$host['sequential_number']}" ) ) );
        open_td( 'left', '', $host['ip4'] );
        open_td( 'left', '', $host['oid'] );
        open_td( 'left', '', $host['location'] );
        open_td( 'left', '', $host['invlabel'] );
        open_td( 'left', '', inlink( 'accountdomainslist', "text= {$host['accountdomains']},class=href,hosts_id=$hosts_id" ) );
        open_td( 'number', '', inlink( 'accountslist', "text= {$host['accounts_count']},class=href,hosts_id=$hosts_id" ) );
        open_td( 'number', '', inlink( 'diskslist', "text= {$host['disks_count']},class=href,hosts_id=$hosts_id" ) );
        open_td( 'number', '', inlink( 'serviceslist', "text= {$host['services_count']},class=href,hosts_id=$hosts_id" ) );
        open_td();
          if( $script == 'hostslist' ) {
            echo postaction( 'update,class=drop,confirm=delete host?', "action=deleteHost,message=$hosts_id" );
          }
    }
  close_table();
}

function diskslist_view( $filters = array(), $opts = true ) {
  global $script;

  $opts = handle_list_options( $opts, array( 'cn' => 'cn', 'host' => 'fqhostname', 'location', 'type', 'sizeGB', 'oid' ) );

  if( ! ( $disks = sql_disks( $filters, $orderby_sql ) ) ) {
    open_div( '', '', 'no matching disks' );
    return;
  }
  $count = count( $disks );
  $limits = handle_list_limits( $opts, $count );
  open_table( 'list oddeven', '', $limits );
    open_tr( 'solidbottom solidtop' );
      open_th( '','', 'cn', 'cn', $opts['sort_prefix'] );
      if( $script != 'host' );
        open_th( '','', 'host', 'host', $opts['sort_prefix'] );
      open_th( '','', 'location', 'location', $opts['sort_prefix'] );
      open_th( '','', 'type', 'type', $opts['sort_prefix'] );
      open_th( '','', 'size / GB', 'size', $opts['sort_prefix'] );
      open_th( '','', 'oid', 'oid', $opts['sort_prefix'] );
      open_th( '','', 'system' );
      open_th( '','', 'actions' );

    foreach( $disks as $disk ) {
      if( $disk['nr'] <= $limits['limit_from'] )
        continue;
      if( $disk['nr'] > $limits['limit_to'] )
        break;
      $disks_id = $disk['disks_id'];
      $hosts_id = $disk['hosts_id'];
      open_tr();
        open_td( 'left', '', inlink( 'disk', "text={$disk['cn']},disks_id=$disks_id" ) );
        if( $script != 'host' ) {
          open_td( 'left' );
          if( $hosts_id ) {
            echo inlink( 'host', "hosts_id=$hosts_id,class=href,text=".sql_fqhostname( $hosts_id ) );
          } else {
            echo "(none)";
          }
        }
        open_td( 'left', '', $disk['location'] );
        open_td( 'left', '', $disk['type_disk'] );
        open_td( 'number', '', $disk['sizeGB'] );
        open_td( 'left', '', $disk['oid'] );
        open_td( 'left', '', "{$disk['systems_type']}.{$disk['systems_arch']}.{$disk['systems_date_built']}" );
        open_td();
          if( $script == 'diskslist' ) {
            echo postaction( 'update,class=drop,confirm=delete disk?', "action=deleteDisk,message=$disks_id" );
          }
    }
  close_table();
}

function tapeslist_view( $filters = array(), $opts = true ) {
  global $script;

  $opts = handle_list_options( $opts, array( 'cn', 'type', 'oid', 'location' ) );

  if( ! ( $tapes = sql_tapes( $filters, $orderby_sql ) ) ) {
    open_div( '', '', 'no matching tapes' );
    return;
  }
  $count = count( $tapes );
  $limits = handle_list_limits( $opts, $count );

  open_table( 'list oddeven', '', $limits );
    open_th( '','', 'cn', 'cn', $opts['sort_prefix'] );
    open_th( '','', 'type', 'type', $opts['sort_prefix'] );
    open_th( '','', 'oid', 'oid', $opts['sort_prefix'] );
    open_th( '','', 'location', 'location', $opts['sort_prefix'] );
    open_th( '','', 'good' );
    open_th( '','', 'retired' );
    open_th( '','', 'actions' );

    foreach( $tapes as $tape ) {
      if( $tape['nr'] <= $limits['limit_from'] )
        continue;
      if( $tape['nr'] > $limits['limit_to'] )
        break;
      $tapes_id = $tape['tapes_id'];
      open_tr();
        open_td( 'left', '', $tape['cn'] );
        open_td( 'left', '', $tape['type_tape'] );
        open_td( 'left', '', $tape['oid'] );
        open_td( 'left', '', $disk['location'] );
        open_td( 'left', '', $disk['good'] );
        open_td( 'left', '', $disk['retired'] );
        open_td();
          echo inlink( 'tape', "class=edit,text=,tapes_id=$tapes_id" );
          if( $script == 'tapeslist' ) {
            echo postaction( 'update,class=drop,confirm=delete tape?', "action=deleteTape,message=$tapes_id" );
          }
    }
  close_table();
}

function serviceslist_view( $filters = array(), $opts = true ) {
  global $script;

  $opts = handle_list_options( $opts, array( 'type_service', 'description' ) );

  if( ! ( $services = sql_services( $filters, $orderby_sql ) ) ) {
    open_div( '', '', 'no matching entries' );
    return;
  }
  $count = count( $services );
  $limits = handle_list_limits( $opts, $count );
  open_table( 'list oddeven', '', $limits );
    open_th( '','', 'type_service', 'type_service', $opts['sort_prefix'] );
    open_th( '','', 'description', 'description', $opts['sort_prefix'] );
    open_th( '','', 'host' );
    open_th( '','', 'actions' );

    foreach( $services as $service ) {
      if( $l['nr'] <= $limits['limit_from'] )
        continue;
      if( $l['nr'] > $limits['limit_to'] )
        break;
      $services_id = $service['services_id'];
      $hosts_id = $service['hosts_id'];
      open_tr();
        open_td( 'left', '', $service['type_service'] );
        open_td( 'left', '', $service['description'] );
        open_td( 'left' );
        if( $hosts_id ) {
          echo inlink( 'host', "hosts_id=$hosts_id,class=href,text=".sql_fqhostname( $hosts_id ) );
        } else {
          echo "(none)";
        }
        open_td();
          echo inlink( 'service', "class=edit,text=,services_id=$services_id" );
          if( $script == 'serviceslist' ) {
            echo postaction( 'update,class=drop,confirm=delete service?', "action=deleteServive,message=$services_id" );
          }
    }
  close_table();
}


function accountslist_view( $filters = array(), $orderby_prefix = false ) {
  global $script;

  if( $orderby_prefix === false ) {
    $p_ = false;
    $orderby_sql = 'fqhostname';
  } else {
    $p_ = ( $orderby_prefix ? $orderby_prefix.'_' : '' );
    $orderby_sql = handle_orderby( array( 'cn', 'uid', 'uidnumber', 'fqhostname' ), $orderby_prefix );
  }

  open_table('list');
    open_th( '','', 'cn', 'cn', $p_ );
    open_th( '','', 'uid', 'uid', $p_ );
    open_th( '','', 'uidnumber', 'uidnumber', $p_ );
    if( $script != 'host' )
      open_th( '','', 'fqhostname', 'fqhostname', $p_ );
    open_th( '','', 'accountdomains' );

    $accounts = sql_accounts( $filters, $orderby_sql );
    foreach( $accounts as $account ) {
      open_tr();
        open_td( 'left', '', $account['cn'] );
        open_td( 'left', '', $account['uid'] );
        open_td( 'number', '', $account['uidnumber'] );
        if( $script != 'host' )
          open_td( 'left', '', inlink( 'host', "text={$account['fqhostname']},hosts_id={$account['hosts_id']}" ) );
        open_td( 'left', '', $account['accountdomains'] );
    }
  close_table();
}

function accountdomainslist_view( $filters = array(), $orderby_prefix = false ) {
  if( $orderby_prefix === false ) {
    $p_ = false;
    $orderby_sql = 'accountdomain';
  } else {
    $p_ = ( $orderby_prefix ? $orderby_prefix.'_' : '' );
    $orderby_sql = handle_orderby( array( 'accountdomain' ), $orderby_prefix );
  }

  open_table('list');
    open_th( '', '', 'accountdomain', 'accountdomain', $p_ );
    open_th( '', '', 'hosts' );
    open_th( '', '', 'accounts' );

    // $accountdomains = ldap_accountdomains();
    $accountdomains = sql_accountdomains( $filters, $orderby_sql );
    foreach( $accountdomains as $a ) {
      open_tr();
        open_td( 'left', '', $name );
        open_td( 'number', '', inlink( 'hostslist', array( 'class' => 'href', 'accountdomains_id' => $a['accountdomains_id'], 'text' => $a['hosts_count'] ) ) );
        open_td( 'number', '', inlink( 'accountslist', array( 'class' => 'href', 'accountdomains_id' => $a['accountdomains_id'], 'text' => $a['accounts_count'] ) ) );
    }
  close_table();
}


// logbook:
//
function logbook_view( $filters = array(), $opts = true ) {

  $opts = handle_list_options( $opts, array( 
    'session' => 'sessions_id', 'timestamp' => 'timestamp', 'logbook_id' => 'logbook_id'
  , 'thread' => 'thread', 'window' => 'window' , 'script' => 'script'
  ) );

  if( ! ( $logbook = sql_logbook( $filters, $opts['orderby_sql'] ) ) ) {
    open_div( '', '', 'no matching entries' );
    return;
  }
  $count = count( $logbook );
  $limits = handle_list_limits( $opts, $count );

  open_table( 'list hfill oddeven', '', $limits );
    open_tr();
      open_th( 'center',"rowspan='2'", 'id', 'logbook_id', $opts['sort_prefix'] );
      open_th( 'center',"rowspan='2'", 'session', 'session', $opts['sort_prefix'] );
      open_th( 'center',"rowspan='2'", 'timestamp', 'timestamp', $opts['sort_prefix'] );
      open_th( 'center','', 'thread', 'thread', $opts['sort_prefix'] );
      open_th( 'center','', 'window', 'window', $opts['sort_prefix'] );
      open_th( 'center','', 'script', 'script', $opts['sort_prefix'] );
      open_th( 'left',"rowspan='2'", 'event' );
      open_th( 'left',"rowspan='2'", 'note' );
      // open_th( 'left',"rowspan='2'", 'details' );
      open_th( 'center',"rowspan='2'", 'actions' );
    open_tr();
      open_th( 'small center','', 'parent' );
      open_th( 'small center','', 'parent' );
      open_th( 'small center','', 'parent' );

    foreach( $logbook as $l ) {
      if( $l['nr'] <= $limits['limit_from'] )
        continue;
      if( $l['nr'] > $limits['limit_to'] )
        break;
      open_tr();
        open_td( 'number', '', $l['logbook_id'] );
        open_td( 'number', '', $l['sessions_id'] );
        open_td( 'right', '', $l['timestamp'] );
        open_td( 'center' );
          open_div( 'center', '', $l['thread'] );
          open_div( 'center small', '', $l['parent_thread'] );
        open_td( 'center' );
          open_div( 'center', '', $l['window'] );
          open_div( 'center small', '', $l['parent_window'] );
        open_td( 'center' );
          open_div( 'center', '', $l['script'] );
          open_div( 'center small', '', $l['parent_script'] );
        open_td( 'left', '', $l['event'] );
        open_td( 'left' );
          if( strlen( $l['note'] ) > 100 )
            $s = substr( $l['note'], 0, 100 ).'...';
          else
            $s = $l['note'];
          if( $l['stack'] )
            $s .= ' [stack]';
          echo inlink( 'logentry', array( 'class' => 'card', 'text' => $s, 'logbook_id' => $l['logbook_id'] ) );
        open_td();
          echo postaction( array( 'class' => 'button', 'text' => 'prune', 'update' => 1 )
                         , array( 'action' => 'prune', 'message' => $l['logbook_id'] ) );
    }
  close_table();
}

?>
