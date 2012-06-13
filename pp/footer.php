<?php

  if( $global_context >= CONTEXT_WINDOW ) {
    open_tr( 'id=footer,style=width:100%;' );
      open_td( 'left', inlink( 'impressum', 'text=Impressum,class=href' ) );
      open_td( 'right', "$now_mysql utc" );
    close_table();
  }
  if( $global_context >= CONTEXT_IFRAME ) {
    // insert an invisible submit button to allow to submit the update_form by pressing ENTER:
    open_span( 'nodisplay', html_tag( 'input', 'type=submit', NULL ) );
  }

?>
