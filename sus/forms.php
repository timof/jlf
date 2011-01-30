<?php

function form_ensure_geschaeftsjahr() {
  global $now;
  get_http_var( 'geschaeftsjahr', 'u', 0, 'thread' );
  if( ! $GLOBALS['geschaeftsjahr'] ) {
    open_form( 'name=update_form' );
      echo "Geschaeftsjahr: ";
      echo int_view( $now[0], 'geschaeftsjahr', 4 );
    close_form();
    exit();
  }
}

?>
