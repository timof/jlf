<?

$mainmenu = array();

$mainmenu[] = array( "window" => "hostlist",
     "title" => "hosts",
     "text" => "hosts" );

$mainmenu[] = array( "window" => "disklist",
     "title" => "disks",
     "text" => "disks" );

$mainmenu[] = array( "window" => "tapelist",
     "title" => "tapes",
     "text" => "tapes" );

$mainmenu[] = array( "window" => "accountlist",
     "title" => "accounts",
     "text" => "accounts" );

$mainmenu[] = array( "window" => "accountdomainlist",
     "title" => "accountdomains",
     "text" => "accountdomains" );

$mainmenu[] = array( "window" => "servicelist",
     "title" => "services",
     "text" => "services" );

$mainmenu[] = array( "window" => "sync",
     "title" => "sync",
     "text" => "sync" );


function mainmenu_vollbild() {
  global $mainmenu;
  foreach( $mainmenu as $h ) { 
    open_tr();
      open_td( '', '', inlink( $h['window'], array(
        'text' => $h['text'], 'title' => $h['title'] , 'class' => 'bigbutton'
      ) ) );
  }
}

function mainmenu_kopfleiste() {
  global $mainmenu;
  foreach( $mainmenu as $h ) { 
    open_li( '', '', inlink( $h['window'], array(
      'text' => $h['text'], 'title' => $h['title'] , 'class' => 'href'
    ) ) );
  }
}


function hosts_view( $filters = array(), $orderby = 'fqhostname' ) {
  global $window;
  open_table('list');
    open_th( '','', $orderby ? inlink( '', 'class=href,text=fqhostname,orderby=fqhostname' ) : 'fqhostname' );
    open_th( '','', $orderby ? inlink( '', 'class=href,text=ip4,orderby=ip4' ) : 'ip4' );
    open_th( '','', $orderby ? inlink( '', 'class=href,text=oid,orderby=oid' ) : 'oid' );
    open_th( '','', $orderby ? inlink( '', 'class=href,text=location,orderby=location' ) : 'location' );
    open_th( '','', $orderby ? inlink( '', 'class=href,text=invlabel,orderby=invlabel' ) : 'invlabel' );
    open_th( '','', 'accountdomains' );
    open_th( '','', 'accounts' );
    open_th( '','', 'disks' );
    open_th( '','', 'services' );
    open_th( '','', 'actions' );

    $hosts = sql_hosts( $filters, $orderby );
    foreach( $hosts as $host ) {
      $hosts_id = $host['hosts_id'];
      // $accountdomains = ldap_accountdomains_host( "cn={$host['fqhostname']},ou=hosts," . LDAP_BASEDN );
      open_tr();
        open_td( 'left', '', inlink( 'host', array( 'class' => 'href', 'text' => "{$host['fqhostname']} / {$host['sequential_number']}" ) ) );
        open_td( 'left', '', $host['ip4'] );
        open_td( 'left', '', $host['oid'] );
        open_td( 'left', '', $host['location'] );
        open_td( 'left', '', $host['invlabel'] );
        open_td( 'left', '', inlink( 'accountdomainlist', "text= {$host['accountdomains']},class=href,hosts_id=$hosts_id" ) );
        open_td( 'number', '', inlink( 'accountlist', "text= {$host['accounts_count']},class=href,hosts_id=$hosts_id" ) );
        open_td( 'number', '', inlink( 'disklist', "text= {$host['disks_count']},class=href,hosts_id=$hosts_id" ) );
        open_td( 'number', '', inlink( 'servicelist', "text= {$host['services_count']},class=href,hosts_id=$hosts_id" ) );
        open_td();
          if( $window == 'hostlist' ) {
            echo postaction( 'update,class=drop,confirm=delete host?', "action=delete,message=$hosts_id" );
          }
    }
  close_table();
}

function disks_view( $filters = array(), $orderby = 'cn' ) {
  global $window;
  open_table('list');
    open_th( '','', $orderby ? inlink( '', 'class=href,text=cn,orderby=cn' ) : 'cn' );
    if( $window != 'host' )
      open_th( '','', $orderby ? inlink( '', 'class=href,text=host,orderby=fqhostname' ) : 'host' );
    open_th( '','', $orderby ? inlink( '', 'class=href,text=location,orderby=location' ) : 'location' );
    open_th( '','', 'type' );
    open_th( '','', 'size / GB' );
    open_th( '','', 'oid' );
    open_th( '','', 'system' );
    open_th( '','', 'actions' );

    $disks = sql_disks( $filters, $orderby );
    foreach( $disks as $disk ) {
      $disks_id = $disk['disks_id'];
      $hosts_id = $disk['hosts_id'];
      open_tr();
        open_td( 'left', '', inlink( 'disk', "text={$disk['cn']},disks_id=$disks_id" ) );
        if( $window != 'host' ) {
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
          if( $window == 'disklist' ) {
            echo postaction( 'update,class=drop,confirm=delete disk?', "action=delete,message=$disks_id" );
          }
    }
  close_table();
}

function tapes_view( $filters = array(), $orderby = 'cn' ) {
  global $window;
  open_table('list');
    open_th( '','', $orderby ? inlink( '', 'class=href,text=cn,orderby=cn' ) : 'cn' );
    open_th( '','', $orderby ? inlink( '', 'class=href,text=type,orderby=type_tape' ) : 'type' );
    open_th( '','', $orderby ? inlink( '', 'class=href,text=oid,orderby=oid' ) : 'oid' );
    open_th( '','', $orderby ? inlink( '', 'class=href,text=location,orderby=location' ) : 'location' );
    open_th( '','', 'good' );
    open_th( '','', 'retired' );
    open_th( '','', 'actions' );

    $tapes = sql_tapes( $filters, $orderby );
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
          if( $window == 'tapelist' ) {
            echo postaction( 'update,class=drop,confirm=delete tape?', "action=delete,message=$tapes_id" );
          }
    }
  close_table();
}

function services_view( $filters = array(), $orderby = 'type_service, description' ) {
  global $window;
  open_table('list');
    open_th( '','', 'type_service' );
    open_th( '','', 'description' );
    open_th( '','', 'host' );
    open_th( '','', 'actions' );

    $services = sql_services( $filters, $orderby );
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
          if( $window == 'servicelist' ) {
            echo postaction( 'update,class=drop,confirm=delete service?', "action=delete,message=$services_id" );
          }
    }
  close_table();
}


function accounts_view( $filters = array(), $orderby = 'uid' ) {
  global $window;
  open_table('list');
    open_th( '','', $orderby ? inlink( '', 'orderby=cn,text=cn' ) : 'cn' );
    open_th( '','', $orderby ? inlink( '', 'orderby=uid,text=uid' ) : 'uid' );
    open_th( '','', $orderby ? inlink( '', 'orderby=uidnumber,text=uidnumber' ) : 'uidnumber' );
    if( $window != 'host' )
      open_th( '','', $orderby ? inlink( '', 'orderby=fqhostname,text=fqhostname' ) : 'fqhostname' );
    open_th( '','', 'accountdomains' );

    $accounts = sql_accounts( $filters, $orderby );
    foreach( $accounts as $account ) {
      open_tr();
        open_td( 'left', '', $account['cn'] );
        open_td( 'left', '', $account['uid'] );
        open_td( 'number', '', $account['uidnumber'] );
        if( $window != 'host' )
          open_td( 'left', '', inlink( 'host', "text={$account['fqhostname']},hosts_id={$account['hosts_id']}" ) );
        open_td( 'left', '', $account['accountdomains'] );
    }
  close_table();
}

function accountdomains_view() {
  open_table('list');
    open_th( '', '', 'accountdomain' );
    open_th( '', '', 'hosts' );
    open_th( '', '', 'accounts' );

    // $accountdomains = ldap_accountdomains();
    $accountdomains = sql_accountdomains();
    foreach( $accountdomains as $name => $a ) {
      open_tr();
        open_td( 'left', '', $name );
        open_td( 'number', '', inlink( 'hostlist', array( 'class' => 'href', 'accountdomain' => $name, 'text' => $a['hosts'] ) ) );
        open_td( 'number', '', inlink( 'accountlist', array( 'class' => 'href', 'accountdomain' => $name, 'text' => $a['accounts'] ) ) );
    }
  close_table();
}

?>
