<?php

echo "<h1>disks</h1>";

$filters = handle_filters( array( 'hosts_id', 'type_disk' ) );

handle_action( array( 'update', 'delete' ) );
switch( $action ) {
  case 'delete':
    need( $message > 0 );
    sql_delete_disk( $message );
    break;
}

open_table('menu');
  open_tr();
    open_th('', "colspan='2'", 'filters' );
  open_tr();
    open_td('', '', 'host:' );
    open_td();
    open_select( 'hosts_id', '', html_options_hosts( $hosts_id, false, " (all) " ), 'reload' );
  open_tr();
    open_td('', '', 'type:' );
    open_td();
    open_select( 'hosts_id', '', html_options_type_disk( $type_disk, false, " (all) " ), 'reload' );
  open_tr();
    open_th('', "colspan='2'", 'actions' );
  open_tr();
    open_td( '', "colspan='2'", inlink( 'disk', 'class=bigbutton,text=new disk,disks_id=0' ) );
close_table();

bigskip();

diskslist_view( $filters, '' );

?>
