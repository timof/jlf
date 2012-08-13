<?php

echo html_tag( 'h1', '', 'hosts' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

$fields = init_fields( 'accountdomains_id,location=a64' );
$filters = & $fields['_filters'];

// debug( $fields, 'fields' );

handle_action( array( 'update', 'deleteHost' ) );
switch( $action ) {
  case 'deleteHost':
    need( $message > 0 );
    sql_delete_hosts( $message );
    break;
}

open_table( 'menu' );
  open_tr();
    open_th( 'colspan=2', 'filters' );
  open_tr();
    open_td( '', 'accountdomain:' );
    open_td();
    filter_accountdomain( $fields['accountdomains_id'] );
  open_tr();
    open_td( '', 'location:' );
    open_td();
    filter_location( $fields['location'], 'filters=hosts' );
  open_tr();
    open_th( 'colspan=2', 'actions' );
  open_tr();
    open_td( 'colspan=2', inlink( 'host', 'class=bigbutton,text=new host' ) );
close_table();

bigskip();

init_var( 'hosts_id', 'global,type=u,sources=http persistent,default=0,set_scopes=self' );
hostslist_view( $filters, array( 'select' => 'hosts_id' ) );

if( $hosts_id ) {
  diskslist_view( "hosts_id=$hosts_id" );
  bigskip();
  serviceslist_view( "hosts_id=$hosts_id" );
}

?>
