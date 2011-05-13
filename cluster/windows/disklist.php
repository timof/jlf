<?php

assert( $angemeldet ) or exit();

echo "<h1>disks</h1>";
$editable = true;

get_http_var( 'options', 'u', 0, true );
get_http_var( 'orderby', 'w', 'cn', true );
need( in_array( $orderby, array( 'cn', 'fqhostname', 'location' ) ) );

$keys = array();
get_http_var( 'hosts_id', 'u', 0, true );
if( $hosts_id )
  $keys['hosts_id'] = $hosts_id;

open_table('menu');
    open_th('', "colspan='2'", 'options' );
  open_tr();
    open_td( '', "colspan='2'", fc_link( 'disk', 'class=bigbutton,text=new disk,disks_id=0' ) );
  open_tr();
    open_td('', '', 'host:' );
    open_td();
    open_select( 'hosts_id', 'autoreload' );
      echo options_hosts( $hosts_id, false, " (all) " );
    close_select();
close_table();

bigskip();


get_http_var('action','w','');
$readonly and $action = '';
switch( $action ) {
  case 'delete':
    need_http_var( 'message','U' );
    sql_delete_host( $message );
    break;
}

medskip();

disks_view( $keys, $orderby );

?>
