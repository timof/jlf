<?php

echo "<h1>services</h1>";

$filters = handle_filters( array( 'hosts_id', 'type_service' ) );

handle_actions( array( 'update', 'delete' ) );
switch( $action ) {
  case 'delete':
    need( $message > 0 );
    sql_delete_service( $message );
    break;
}

open_table('menu');
  open_tr();
    open_th( '', "colspan='2'", 'filters' );
  open_tr();
    open_td( '', '', 'host:' );
    open_select( 'hosts_id', '', html_options_hosts( $hosts_id, false, " - all hosts - " ), 'reload' );
  open_tr();
    open_td( '', '', 'type:' );
    open_select( 'type_service', html_options_services( $services_id, false, " - all types - " ), 'reload' );
  open_tr();
    open_th( '', "colspan='2'", 'actions' );
  open_tr();
    open_td();
    echo inlink( 'service', 'class=bigbutton,text=new service,services_id=0' );
close_table();

bigskip();

serviceslist_view( $filters, '' );

?>
