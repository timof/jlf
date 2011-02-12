<?php

echo "<h1>hosts</h1>";

init_global_var( 'options', 'u', 'http,persistent', 0, 'window' );

$filters = handle_filters( array( 'accountdomains_id', 'locations_id' ) );

handle_action( array( 'update', 'deleteHost' ) );
switch( $action ) {
  case 'deleteHost':
    need( $message > 0 );
    sql_delete_hosts( $message );
    break;
}

open_table('menu');
  open_tr();
    open_th('', "colspan='2'", 'filters' );
  open_tr();
    open_td('', '', 'accountdomain:' );
    open_td();
    open_select( 'accountdomains_id', '', html_options_accountdomains( $accountdomains_id, ' (all) ' ), 'reload' );
  open_tr();
    open_td('', '', 'location:' );
    open_td();
    open_select( 'locations_id', '', html_options_locations( $locations_id, ' (all) ' ), 'reload' );
  open_tr();
    open_th('', "colspan='2'", 'actions' );
  open_tr();
    open_td( '', "colspan='2'", inlink( 'host', 'class=bigbutton,text=new host,action=init' ) );
close_table();

bigskip();

hostslist_view( $filters, true, 'hosts_id' );

init_global_var( 'hosts_id', 'u', 'http,persistent', 0, true );
if( $hosts_id ) {
  diskslist_view( "hosts_id=$hosts_id" );
  bigskip();
  serviceslist_view( "hosts_id=$hosts_id" );
}

?>
