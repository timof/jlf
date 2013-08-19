<?php

echo html_tag( 'h1', '', 'disks' );

$fields = init_fields( array(
  'hosts_id'
, 'type_disk' => 'allow_null=0,default=0'
, 'interface_disk' => 'allow_null=0,default=0'
, 'location' => 'a64'
, 'host_current' => 'B,auto=1,default=2'
, 'REGEX' => 'size=20,auto=1'
) );
$filters = & $fields['_filters'];

handle_action( array( 'update', 'deleteDisk' ) );
switch( $action ) {
  case 'deleteDisk':
    need( $message > 0 );
    sql_delete_disks( $message );
    break;
}

open_div( 'menubox' );
  open_table( 'css filters' );
    open_caption( '', filter_reset_button( $fields, 'floatright' ) . 'Filter' );
    open_tr();
      open_th( '', 'host:' );
      open_td();
        open_div('oneline smallskipb', radiolist_element( $fields['host_current'], 'choices=:outdated:current:both' ) );
        open_div( '', filter_host( $fields['hosts_id'], array( 'filters' => parameters_explode( $filters, 'keep=host_current' ) ) ) );
    open_tr();
      open_th( '', 'type:' );
      open_td( '', filter_type_disk( $fields['type_disk'] ) );
    open_tr();
      open_th( '', 'interface:' );
      open_td( '', filter_interface_disk( $fields['interface_disk'] ) );
    open_tr();
      open_th( '', 'location:' );
      open_td( '', filter_location( $fields['location'], 'filters=disks' ) );
    open_tr();
      open_th( '', 'search:' );
      open_td( '', string_element( $fields['REGEX'] ) );
    open_tr();
      open_th( 'colspan=2', 'actions' );
    open_tr();
      open_th( 'colspan=2', inlink( 'disk', 'class=bigbutton,text=new disk' ) );
  close_table();
close_div();

diskslist_view( $filters );

?>
