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


$jlf_url_vars[ 'hostslist_N_ordernew' ] = array( 'type' => 'l', 'default' => '' );
$jlf_url_vars[ 'hostslist_N_limit_from' ] = array( 'type' => 'u', 'default' => 0 );
$jlf_url_vars[ 'hostslist_N_limit_count' ] = array( 'type' => 'u', 'default' => 20 );
function hostslist_view( $filters = array(), $p_ = true ) {
  global $script;
  static $num = 0;

  if( $p_ === true ) {
    $num++;
    $p_ = "hostslist_N{$num}_";
  }
  $orderby_sql = handle_orderby( array( 'fqhostname', 'ip4', 'oid', 'location', 'invlabel' ), $p_ );

  init_global_var( $p_.'limit_from', 'u', 'http,persistent', 0, 'window' );
  init_global_var( $p_.'limit_count', 'u', 'http,persistent', 20, 'window' );
  $limit_from = $GLOBALS[ $p_.'limit_from' ];
  $limit_count = $GLOBALS[ $p_.'limit_count' ];

  $hosts = sql_hosts( $filters, $orderby_sql );
  $count = count( $buchungen );
  if( ! $hosts ) {
    open_div( '', '', '(no hosts found)' );
    return;
  }
  if( $count <= $limit_from )
    $limit_from = $count - 1;

  open_table( 'list oddeven' );
    if( $count > 10 ) {
      open_caption();
        form_limits( $p_, $count, $limit_from, $limit_count );
      close_caption();
    } else {
      $limit_count = 10;
      $limit_from = 0;
    }
    open_tr( 'solidbottom solidtop' );
      open_th( '','', 'fqhostname', 'fqhostname', $p_ );
      open_th( '','', 'ip4', 'ip4', $p_ );
      open_th( '','', 'oid', 'oid', $p_ );
      open_th( '','', 'location', 'location', $p_ );
      open_th( '','', 'invlabel', 'invlabel', $p_ );
      open_th( '','', 'accountdomains' );
      open_th( '','', 'accounts' );
      open_th( '','', 'disks' );
      open_th( '','', 'services' );
      open_th( '','', 'actions' );
    foreach( $hosts as $host ) {
      if( $host['nr'] <= $limit_from )
        continue;
      if( $host['nr'] > $limit_from + $limit_count )
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

$jlf_url_vars[ 'diskslist_N_ordernew' ] = array( 'type' => 'l', 'default' => '' );
$jlf_url_vars[ 'diskslist_N_limit_from' ] = array( 'type' => 'u', 'default' => 0 );
$jlf_url_vars[ 'diskslist_N_limit_count' ] = array( 'type' => 'u', 'default' => 20 );
function diskslist_view( $filters = array(), $p_ = true ) {
  global $script;
  static $num = 0;

  if( $p_ === true ) {
    $num++;
    $p_ = "diskslist_N{$num}_";
  }
  $orderby_sql = handle_orderby( array( 'cn' => 'cn', 'host' => 'fqhostname', 'location', 'type', 'sizeGB', 'oid' ), $p_ );

  init_global_var( $p_.'limit_from', 'u', 'http,persistent', 0, 'window' );
  init_global_var( $p_.'limit_count', 'u', 'http,persistent', 20, 'window' );
  $limit_from = $GLOBALS[ $p_.'limit_from' ];
  $limit_count = $GLOBALS[ $p_.'limit_count' ];

  $disks = sql_disks( $filters, $orderby_sql );
  $count = count( $buchungen );
  if( ! $disks ) {
    open_div( '', '', '(no disks found)' );
    return;
  }
  open_table('list');
    if( $count > 10 ) {
      open_caption();
        form_limits( $p_, $count, $limit_from, $limit_count );
      close_caption();
    } else {
      $limit_count = 10;
      $limit_from = 0;
    }
    open_tr( 'solidbottom solidtop' );
      open_th( '','', 'cn', 'cn', $p_ );
      if( $script != 'host' );
        open_th( '','', 'host', 'host', $p_ );
      open_th( '','', 'location', 'location', $p_ );
      open_th( '','', 'type', 'type', $p_ );
      open_th( '','', 'size / GB', 'size', $p_ );
      open_th( '','', 'oid', 'oid', $p_ );
      open_th( '','', 'system' );
      open_th( '','', 'actions' );

    foreach( $disks as $disk ) {
      if( $disk['nr'] <= $limit_from )
        continue;
      if( $disk['nr'] > $limit_from + $limit_count )
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

function tapeslist_view( $filters = array(), $orderby_prefix = false ) {
  global $script;

  if( $orderby_prefix === false ) {
    $orderby_sql = 'cn';
    $p_ = false;
  } else {
    $orderby_sql = handle_orderby( array( 'cn', 'type', 'oid', 'location' ), $orderby_prefix );
    $p_ = ( $orderby_prefix ? $orderby_prefix.'_' : '' );
  }
  open_table('list');
    open_th( '','', 'cn', 'cn', $p_ );
    open_th( '','', 'type', 'type', $p_ );
    open_th( '','', 'oid', 'oid', $p_ );
    open_th( '','', 'location', 'location', $p_ );
    open_th( '','', 'good' );
    open_th( '','', 'retired' );
    open_th( '','', 'actions' );

    $tapes = sql_tapes( $filters, $orderby_sql );
    foreach( $tapes as $tape ) {
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

function serviceslist_view( $filters = array(), $orderby_prefix = false ) {
  global $script;

  if( $orderby_prefix === false ) {
    $orderby_sql = 'cn';
    $p_ = false;
  } else {
    $orderby_sql = handle_orderby( array( 'type_service', 'description' ), $orderby_prefix );
    $p_ = ( $orderby_prefix ? $orderby_prefix.'_' : '' );
  }

  open_table('list');
    open_th( '','', 'type_service', 'type_service', $p_ );
    open_th( '','', 'description', 'description', $p_ );
    open_th( '','', 'host' );
    open_th( '','', 'actions' );

    $services = sql_services( $filters, $orderby_sql );
    foreach( $services as $service ) {
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

?>
