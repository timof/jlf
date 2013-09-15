<?php

sql_transaction_boundary('*');
echo html_tag( 'h1', '', 'services' );

$fields = init_fields( array( 'hosts_id', 'type_service' ) );
$filters = & $fields['_filters'];

handle_action( array( 'update', 'deleteService' ) );
switch( $action ) {
  case 'deleteService':
    need( $message > 0 );
    sql_delete_services( $message );
    break;
}

open_table('menu');
  open_tr();
    open_th( 'colspan=2', 'filters' );
  open_tr();
    open_td( '', 'host:' );
    open_td( '', filter_host( $fields['hosts_id'] ) );
  open_tr();
    open_td( '', 'type:' );
    open_td( '', filter_type_service( $fields['type_service'] ) );
  open_tr();
    open_th( 'colspan=2', 'actions' );
  open_tr();
    open_td( 'colspan=2', inlink( 'service', 'class=bigbutton,text=new service,services_id=0' ) );
close_table();

bigskip();

serviceslist_view( $filters, '' );

?>
