<?php

echo html_tag( 'h1', '', 'disks' );

$fields = init_fields( 'hosts_id,type_disk,interface_disk,location=a64' );
$filters = & $fields['_filters'];

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
    filter_host( $fields['hosts_id'] );
  open_tr();
    open_td( '', 'type:' );
    open_td();
    filter_type_disk( $fields['type_disk'] );
  open_tr();
    open_td( '', 'interface:' );
    open_td();
    filter_interface_disk( $fields['interface_disk'] );
  open_tr();
    open_td( '', 'location:' );
    open_td();
    filter_location( $fields['location'], 'filters=disks' );
  open_tr();
    open_th( 'colspan=2', 'actions' );
  open_tr();
    open_td( 'colspan=2', inlink( 'disk', 'class=bigbutton,text=new disk' ) );
close_table();

bigskip();

diskslist_view( $filters );

?>
