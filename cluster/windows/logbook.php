<?php

echo html_tag( 'h1', '', 'logbook' );

init_var( 'options', 'global,pattern=u,sources=http persistent,default=0,set_scopes=window' );

$fields = prepare_filters( array( 'f_sessions_id', 'f_thread', 'f_window' ) );

handle_action( array( 'update', 'prune' ) );
switch( $action ) {
  case 'update':
    // nop
    break;
  case 'prune':
    menatwork();
}

open_table( 'menu' );
  open_tr();
    open_th( 'center,colspan=2', 'Filter' );
  open_tr();
    open_th( 'right', 'session:' );
    open_td();
      // selector_int();
  open_tr();
    open_th( 'right', 'thread:' );
    open_td();
      filter_thread( $fields['f_thread'] );
close_table();

bigskip();

logbook_view( $fields['_filters'] );

?>
