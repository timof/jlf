<?php

echo "<h1>tape chunks</h1>";

init_global_var( 'options', 'u', 'http,persistent', 0, 'window' );

$filters = handle_filters( array( 'hosts_id', 'tapes_id' ) );

handle_action( array( 'update', 'deleteTapechunk' ) );
switch( $action ) {
  case 'deleteTapechunk':
    need( $message > 0 );
    sql_delete_tapechunks( $message );
    break;
}

open_table('menu');
  open_tr();
    open_th('', "colspan='2'", 'filters' );
  open_tr();
    open_td('', '', 'hosts:' );
    open_td();
      filter_host();
  open_tr();
    open_td('', '', 'path:' );
    open_td();
      filter_path();
  open_tr();
    open_th('', "colspan='2'", 'actions' );
  open_tr();
    open_td( '', "colspan='2'", inlink( 'tapechunk', 'class=bigbutton,text=new tapechunk,tapechunks_id=0' ) );
close_table();

bigskip();

tapechunkslist_view( $filters );


?>
