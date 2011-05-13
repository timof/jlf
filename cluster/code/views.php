<?php

function number_selector($name, $min, $max, $selected, $format, $to_stdout = true ){
  global $input_event_handlers;
  $s = "<select name='$name' $input_event_handlers>";
  for( $i = $min; $i <= $max; $i++ ) {
    if( $i == $selected )
    $select_str = ( $i == $selected ? 'selected' : '' );
    $s .= "<option value='$i' $select_str>".sprintf($format,$i)."</option>\n";
  }
  $s .= "</select>";
  if( $to_stdout )
    echo $s;
  return $s;
}

function year_selector( $name, $selected, $to_stdout = true ) {
  return number_selector( $name, 2010, 2040, $selected, '%04u', $to_stdout );
}

function month_selector( $name, $selected, $to_stdout = true ) {
  return number_selector( $name, 1, 12, $selected, '%02u', $to_stdout );
}

/**
 * Stellt eine komplette Editiermöglichkeit für
 * Datum und Uhrzeit zur Verfügung.
 * Muss in ein Formluar eingebaut werden
 * Die Elemente des Datums stehen dann zur Verfügung als
 *   <prefix>_minute
 *   <prefix>_stunde
 *   <prefix>_tag
 *   <prefix>_monat
 *   <prefix>_jahr
 */
function date_time_selector($sql_date, $prefix, $show_time=true, $to_stdout = true ) {
  echo "<!-- sql_date :$sql_date -->";
	$datum = date_parse($sql_date);

  $s = "
    <table class='inner'>
                  <tr>
                     <td><label>Datum:</label></td>
                      <td style='white-space:nowrap;'>
    ". date_selector($prefix."_tag", $datum['day'],$prefix."_monat", $datum['month'], $prefix."_jahr", $datum['year'], false) ."
                   </td>
       </tr>
  ";
  if( $show_time ) {
    $s .= "
         <tr>
                   <td><label>Zeit:</label></td>
                           <td style='white-space:nowrap;'>
      ". time_selector($prefix."_stunde", $datum['hour'],$prefix."_minute", $datum['minute'], false ) ."
                           </td>
                       </tr>
    ";
  }
  $s .= "</table>";
  if( $to_stdout )
    echo $s;
  return $s;
}

function date_selector($tag_feld, $tag, $monat_feld, $monat, $jahr_feld, $jahr, $to_stdout = true ){
  $s = number_selector($tag_feld, 1, 31, $tag,"%02d",false);
  $s .= '.';
  $s .= number_selector($monat_feld,1, 12, $monat,"%02d",false);
  $s .= '.';
  $s .=  number_selector($jahr_feld, 2009, 2015, $jahr,"%04d",false);
  if( $to_stdout )
    echo $s;
  return $s;
}
function time_selector($stunde_feld, $stunde, $minute_feld, $minute, $to_stdout = true ){
  $s =  number_selector($stunde_feld, 0, 23, $stunde,"%02d",false);
  $s .= '.';
  $s .= number_selector($minute_feld,0, 59, $minute,"%02d",false);
  if( $to_stdout )
    echo $s;
  return $s;
}

//////////////////
//
// views for "primitive" types:
// they will return a suitable string, not print to stdout directly!
//

function int_view( $num, $fieldname = false, $size = 6 ) {
  global $input_event_handlers;
  $num = sprintf( "%d", $num );
  if( $fieldname )
    return "<input type='text' class='int number' size='$size' name='$fieldname' value='$num' $input_event_handlers>";
  else
    return "<span class='int number'>$num</span>";
}

function price_view( $price, $fieldname = false ) {
  global $input_event_handlers;
  $price = sprintf( "%.2lf", $price );
  if( $fieldname )
    return "<input type='text' class='price number' size='8' name='$fieldname' value='$price' $input_event_handlers>";
  else
    return "<span class='price number'>$price</span>";
}


function string_view( $text, $length = 20, $fieldname = false, $attr = '' ) {
  global $input_event_handlers;
  if( $fieldname )
    return "<input type='text' class='string' size='$length' name='$fieldname' value='$text' $attr $input_event_handlers>";
  else
    return "<span class='string'>$text</span>";
}

function date_time_view( $datetime, $fieldname = '' ) {
  global $mysqljetzt;
  if( ! $datetime )
    $datetime = $mysqljetzt;
  if( $fieldname ) {
    sscanf( $datetime, '%u-%u-%u %u:%u', &$year, &$month, &$day, &$hour, &$minute );
    return date_selector( $fieldname.'_day', $day, $fieldname.'_month', $month, $fieldname.'_year', $year, false )
           .' '. time_selector( $fieldname.'_hour', $hour, $fieldname.'_minute', $minute, false );
  } else {
    return "<span class='datetime'>$datetime</span>";
  }
}
function date_view( $date, $fieldname = '' ) {
  global $mysqlheute;
  if( ! $date )
    $date = $mysqlheute;
  if( $fieldname ) {
    sscanf( $date, '%u-%u-%u', &$year, &$month, &$day );
    return date_selector( $fieldname.'_day', $day, $fieldname.'_month', $month, $fieldname.'_year', $year, false );
  } else {
    return "<span class='date'>$date</span>";
  }
}




$hauptmenue = array();

$hauptmenue[] = array( "window" => "hostlist",
     "title" => "hosts",
     "text" => "hosts" );

$hauptmenue[] = array( "window" => "disklist",
     "title" => "disks",
     "text" => "disks" );

$hauptmenue[] = array( "window" => "tapelist",
     "title" => "tapes",
     "text" => "tapes" );

$hauptmenue[] = array( "window" => "userlist",
     "title" => "users",
     "text" => "users" );

$hauptmenue[] = array( "window" => "accountdomainlist",
     "title" => "accountdomains",
     "text" => "accountdomains" );

$hauptmenue[] = array( "window" => "servicelist",
     "title" => "services",
     "text" => "services" );

$hauptmenue[] = array( "window" => "sync",
     "title" => "sync",
     "text" => "sync" );


function hauptmenue_vollbild() {
  global $hauptmenue;
  foreach( $hauptmenue as $h ) { 
    open_tr();
      open_td( '', '', fc_link( $h['window'], array(
        'text' => $h['text'], 'title' => $h['title'] , 'class' => 'bigbutton'
      ) ) );
  }
}

function hauptmenue_kopfleiste() {
  global $hauptmenue, $angemeldet;
  foreach( $hauptmenue as $h ) { 
    open_li( '', '', fc_link( $h['window'], array(
      'text' => $h['text'], 'title' => $h['title'] , 'class' => 'href'
    ) ) );
  }
}


function hosts_view( $keys = array(), $orderby = 'fqhostname' ) {
  global $window;
  open_table('list');
    open_th( '','', $orderby ? fc_link( '', 'class=href,text=fqhostname,orderby=fqhostname' ) : 'fqhostname' );
    open_th( '','', $orderby ? fc_link( '', 'class=href,text=ip4,orderby=ip4' ) : 'ip4' );
    open_th( '','', $orderby ? fc_link( '', 'class=href,text=oid,orderby=oid' ) : 'oid' );
    open_th( '','', $orderby ? fc_link( '', 'class=href,text=location,orderby=location' ) : 'location' );
    open_th( '','', $orderby ? fc_link( '', 'class=href,text=invlabel,orderby=invlabel' ) : 'invlabel' );
    open_th( '','', 'accountdomains' );
    open_th( '','', 'users' );
    open_th( '','', 'disks' );
    open_th( '','', 'services' );
    open_th( '','', 'actions' );

    $hosts = sql_hosts( $keys, $orderby );
    foreach( $hosts as $host ) {
      $hosts_id = $host['hosts_id'];
      $accountdomains = ldap_accountdomains_host( "cn={$host['fqhostname']},ou=hosts," . LDAP_BASEDN );
      open_tr();
        open_td( 'left', '', fc_link( 'host', array( 'class' => 'href', 'text' => "{$host['fqhostname']} / {$host['sequential_number']}" ) ) );
        open_td( 'left', '', $host['ip4'] );
        open_td( 'left', '', $host['oid'] );
        open_td( 'left', '', $host['location'] );
        open_td( 'left', '', $host['invlabel'] );
        open_td( 'left', '', fc_link( 'accountdomainlist', "text= {$host['accountdomains']},class=href,hosts_id=$hosts_id" ) );
        open_td( 'number', '', fc_link( 'userlist', "text= {$host['users_cnt']},class=href,hosts_id=$hosts_id" ) );
        open_td( 'number', '', fc_link( 'disklist', "text= {$host['disks_cnt']},class=href,hosts_id=$hosts_id" ) );
        open_td( 'number', '', fc_link( 'servicelist', "text= {$host['services_cnt']},class=href,hosts_id=$hosts_id" ) );
        open_td();
          if( $window == 'hostlist' ) {
            echo fc_action( 'update,class=drop,confirm=delete host?', "action=delete,message=$hosts_id" );
          }
    }
  close_table();
}

function disks_view( $keys = array(), $orderby = 'cn' ) {
  global $window;
  open_table('list');
    open_th( '','', $orderby ? fc_link( '', 'class=href,text=cn,orderby=cn' ) : 'cn' );
    if( $window != 'host' )
      open_th( '','', $orderby ? fc_link( '', 'class=href,text=host,orderby=fqhostname' ) : 'host' );
    open_th( '','', $orderby ? fc_link( '', 'class=href,text=location,orderby=location' ) : 'location' );
    open_th( '','', 'type' );
    open_th( '','', 'size / GB' );
    open_th( '','', 'oid' );
    open_th( '','', 'system' );
    open_th( '','', 'actions' );

    $disks = sql_disks( $keys, $orderby );
    foreach( $disks as $disk ) {
      $disks_id = $disk['disks_id'];
      $hosts_id = $disk['hosts_id'];
      open_tr();
        open_td( 'left', '', fc_link( 'disk', "text={$disk['cn']},disks_id=$disks_id" ) );
        if( $window != 'host' ) {
          open_td( 'left' );
          if( $hosts_id ) {
            echo fc_link( 'host', "hosts_id=$hosts_id,class=href,text=".sql_fqhostname( $hosts_id ) );
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
            echo fc_action( 'update,class=drop,confirm=delete disk?', "action=delete,message=$disks_id" );
          }
    }
  close_table();
}

function tapes_view( $keys = array(), $orderby = 'cn' ) {
  global $window;
  open_table('list');
    open_th( '','', $orderby ? fc_link( '', 'class=href,text=cn,orderby=cn' ) : 'cn' );
    open_th( '','', $orderby ? fc_link( '', 'class=href,text=type,orderby=type_tape' ) : 'type' );
    open_th( '','', $orderby ? fc_link( '', 'class=href,text=oid,orderby=oid' ) : 'oid' );
    open_th( '','', $orderby ? fc_link( '', 'class=href,text=location,orderby=location' ) : 'location' );
    open_th( '','', 'good' );
    open_th( '','', 'retired' );
    open_th( '','', 'actions' );

    $tapes = sql_tapes( $keys, $orderby );
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
          echo fc_link( 'tape', "class=edit,text=,tapes_id=$tapes_id" );
          if( $window == 'tapelist' ) {
            echo fc_action( 'update,class=drop,confirm=delete tape?', "action=delete,message=$tapes_id" );
          }
    }
  close_table();
}

function services_view( $keys = array(), $orderby = 'type_service, description' ) {
  global $window;
  open_table('list');
    open_th( '','', 'type_service' );
    open_th( '','', 'description' );
    open_th( '','', 'host' );
    open_th( '','', 'actions' );

    $services = sql_services( $keys, $orderby );
    foreach( $services as $service ) {
      $services_id = $service['services_id'];
      $hosts_id = $service['hosts_id'];
      open_tr();
        open_td( 'left', '', $service['type_service'] );
        open_td( 'left', '', $service['description'] );
        open_td( 'left' );
        if( $hosts_id ) {
          echo fc_link( 'host', "hosts_id=$hosts_id,class=href,text=".sql_fqhostname( $hosts_id ) );
        } else {
          echo "(none)";
        }
        open_td();
          echo fc_link( 'service', "class=edit,text=,services_id=$services_id" );
          if( $window == 'servicelist' ) {
            echo fc_action( 'update,class=drop,confirm=delete service?', "action=delete,message=$services_id" );
          }
    }
  close_table();
}


function users_view( $keys = array(), $orderby = 'uid' ) {
  global $window;
  open_table('list');
    open_th( '','', $orderby ? fc_link( '', 'orderby=cn,text=cn' ) : 'cn' );
    open_th( '','', $orderby ? fc_link( '', 'orderby=uid,text=uid' ) : 'uid' );
    open_th( '','', $orderby ? fc_link( '', 'orderby=uidnumber,text=uidnumber' ) : 'uidnumber' );
    if( $window != 'host' )
      open_th( '','', $orderby ? fc_link( '', 'orderby=fqhostname,text=fqhostname' ) : 'fqhostname' );
    open_th( '','', 'accountdomains' );

    $users = sql_users( $keys, $orderby );
    foreach( $users as $user ) {
      open_tr();
        open_td( 'left', '', $user['cn'] );
        open_td( 'left', '', $user['uid'] );
        open_td( 'number', '', $user['uidnumber'] );
        if( $window != 'host' )
          open_td( 'left', '', fc_link( 'host', "text={$user['fqhostname']},hosts_id={$user['hosts_id']}" ) );
        open_td( 'left', '', $user['accountdomains'] );
    }
  close_table();
}

function accountdomains_view() {
  open_table('list');
    open_th( '', '', 'accountdomain' );
    open_th( '', '', 'hosts' );
    open_th( '', '', 'users' );

    $accountdomains = ldap_accountdomains();
    foreach( $accountdomains as $name => $a ) {
      open_tr();
        open_td( 'left', '', $name );
        open_td( 'number', '', fc_link( 'hostlist', array( 'class' => 'href', 'accountdomain' => $name, 'text' => $a['hosts'] ) ) );
        open_td( 'number', '', fc_link( 'userlist', array( 'class' => 'href', 'accountdomain' => $name, 'text' => $a['users'] ) ) );
    }
  close_table();
}

?>
