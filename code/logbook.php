<?php

echo html_tag( 'h1', '', 'logbook' );

init_var( 'options', 'global,type=u,sources=http persistent,default=0,set_scopes=window' );

$fields = init_fields( array( 'sessions_id', 'thread', 'window' ), 'tables=logbook,cgi_prefix=F_' );

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
      filter_thread( $fields['thread'] );
close_table();

bigskip();

logbook_view( $fields['_filters'] );

?>
