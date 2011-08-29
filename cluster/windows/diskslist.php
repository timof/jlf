<?php

echo html_tag( 'h1', '', 'disks' );

$filters = handle_filters( array( 'hosts_id', 'type_disk' ) );

handle_action( array( 'update', 'deleteDisk' ) );
switch( $action ) {
  case 'deleteDisk':
    need( $message > 0 );
    sql_delete_disks( $message );
    break;
}

open_table( 'menu' );
  open_tr();
    open_th( 'colspan=2', 'filters' );
  open_tr();
    open_td( '', 'host:' );
    open_td();
    filter_host();
  open_tr();
    open_td( '', 'type:' );
    open_td();
    filter_type_disk();
  open_tr();
    open_th( 'colspan=2', 'actions' );
  open_tr();
    open_td( 'colspan=2', inlink( 'disk', 'class=bigbutton,text=new disk,action=init' ) );
close_table();

bigskip();

diskslist_view( $filters );

?>
