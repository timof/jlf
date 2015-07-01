<?php

echo html_tag( 'h1', '', 'hosts' );

sql_transaction_boundary('*');

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

$fields = init_fields( array(
  'accountdomains_id'
, 'location' => 'a64'
, 'online' => 'B,auto=1,default=2'
, 'host_current' => 'B,auto=1,default=2'
, 'REGEX' => 'a128,size=20,auto=1'
) );
$filters = & $fields['_filters'];

// debug( $fields, 'fields' );

handle_actions( array( 'deleteHost' ) );
if( $action ) switch( $action ) {
  case 'deleteHost':
    need( $message > 0 );
    sql_delete_hosts( $message );
    break;
}

open_div('menubox');
  open_table( 'css filters' );
    open_caption( '', filter_reset_button( $fields, 'floatright' ) . 'Filter' );
    open_tr();
      open_th( '', 'status:' );
      open_td('oneline smallskipb', radiolist_element( $fields['online'], 'choices=:offline:online:both' ) );
    open_tr();
      open_th( '', 'currency:' );
      open_td('oneline smallskipb', radiolist_element( $fields['host_current'], 'choices=:outdated:current:both' ) );
//    open_tr();
//      open_th( '', 'accountdomain:' );
//      open_td( '', filter_accountdomain( $fields['accountdomains_id'] ) );
    open_tr();
      open_th( '', 'location:' );
      open_td( '', filter_location( $fields['location'], 'filters=hosts' ) );
    open_tr();
      open_th( '', 'search:' );
      open_td( '', string_element( $fields['REGEX'] ) );
  close_table();
  open_table('css actions');
    open_caption( '', 'actions' );
    open_tr( '', inlink( 'host', 'class=bigbutton,text=new host' ) );
  close_table();
close_div();

init_var( 'hosts_id', 'global,type=u,sources=http persistent,default=0,set_scopes=self' );
hostslist_view( $filters, array( 'select' => 'hosts_id' ) );

if( $hosts_id ) {
  diskslist_view( "hosts_id=$hosts_id" );
  bigskip();
  serviceslist_view( "hosts_id=$hosts_id" );
}

?>
