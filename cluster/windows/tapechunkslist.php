<?php

sql_transaction_boundary('*');
echo html_tag( 'h1', '', 'tape chunks' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

$fields = init_fields( array(
  'hosts_id'
, 'tapes_id'
, 'type_tape'
, 'targets' => 'type=a1024,relation=~,size=40'
) );

handle_actions( array( 'deleteTapechunk' ) );
if( $action ) switch( $action ) {
  case 'deleteTapechunk':
    need( $message > 0 );
    sql_delete_tapechunks( $message );
    break;
}

open_table('menu');
  open_tr();
    open_th( 'colspan=2', 'filters' );
  open_tr();
    open_td( '', 'hosts:' );
    open_td( '', filter_host( $fields['hosts_id'] ) );
  open_tr();
    open_td( '', 'targets:' );
    open_td( '', '/'.string_element( $fields['targets'] ).'/' );
  open_tr();
    open_th( 'colspan=2', 'actions' );
//  open_tr();
//     open_td( 'colspan=2', inlink( 'tapechunk', 'class=bigbutton,text=new tapechunk,tapechunks_id=0' ) );
close_table();

bigskip();

tapechunkslist_view( $fields['_filters'] );

?>
