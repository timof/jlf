<?php

close_div(); // thePayload

end_deliverable( 'htmlPayloadOnly' );

close_div(); // theOutbacks

open_div( 'id=theFooter' );
  if( $debug ) {
    open_div( 'smallskips' );
      echo "[$allowed_authentication_methods,$cookie,$login_sessions_id,l:$login,a:$action,d:$deliverable]";
    close_div();
    open_div( 'medskips,id=jsdebug', '[INIT]' );
  }
  open_table( 'css=1,hfill' );
  open_tr();
    open_td( 'left,style=padding-left:128px;', inlink( 'impressum', 'text=impressum,class=href inlink' ) );
    open_td( 'right small', "page generated: $now_mysql utc" );

  close_table();
close_div();

// insert an invisible submit button to allow to submit the update_form by pressing ENTER:
open_span( 'nodisplay', html_tag( 'input', 'type=submit', NULL ) );

?>
