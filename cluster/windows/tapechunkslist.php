<?php

echo html_tag( 'h1', '', 'tape chunks' );

init_var( 'options', 'global,pattern=u,sources=http persistent,default=0,set_scopes=window' );

$filters = prepare_filters( 'hosts_id,tapes_id' );

handle_action( array( 'update', 'deleteTapechunk' ) );
switch( $action ) {
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
    open_td();
      filter_host( $fields['hosts_id'] );
  open_tr();
    open_td( '', 'path:' );
    open_td();
      filter_path( $fields['path'] );
  open_tr();
    open_th( 'colspan=2', 'actions' );
  open_tr();
    open_td( 'colspan=2', inlink( 'tapechunk', 'class=bigbutton,text=new tapechunk,tapechunks_id=0' ) );
close_table();

bigskip();

tapechunkslist_view( $filters );


?>
