<?php

assert( $angemeldet ) or exit();

echo "<h1>services</h1>";
$editable = true;

get_http_var( 'options', 'u', 0, true );
get_http_var( 'orderby', 'w', 'type_service', true );
need( in_array( $orderby, array( 'type_service', 'host' ) ) );

$keys = array();
get_http_var( 'hosts_id', 'u', 0, true );
if( $hosts_id )
  $keys['hosts_id'] = $hosts_id;

get_http_var( 'type_service', 'u', 0, true );
if( $type_service )
  $keys['type_service'] = $type_service;

open_table('menu');
    open_th('', '', 'options' );
  open_tr();
    open_td();
    echo fc_link( 'service', 'class=bigbutton,text=new service,services_id=0' );
  open_tr();
    open_td();
    open_select( 'hosts_id', 'autoreload' );
      echo options_hosts( $hosts_id, false, " - all hosts - " );
    close_select();
    open_select( 'type_service', 'autoreload' );
      echo options_services( $services_id, false, " - all types - " );
    close_select();
close_table();

bigskip();


get_http_var('action','w','');
$readonly and $action = '';
switch( $action ) {
  case 'delete':
    need_http_var( 'message','U' );
    sql_delete_service( $message );
    break;
}

medskip();

services_view( $keys, $orderby );

?>
