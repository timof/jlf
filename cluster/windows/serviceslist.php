<?php

echo "<h1>services</h1>";

$filters = handle_filters( array( 'hosts_id', 'type_service' ) );

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
    filter_host();
  open_tr();
    open_td( '', 'type:' );
    filter_type_service();
  open_tr();
    open_th( 'colspan=2', 'actions' );
  open_tr();
    open_td();
    echo inlink( 'service', 'class=bigbutton,text=new service,services_id=0' );
close_table();

bigskip();

serviceslist_view( $filters, '' );

?>
