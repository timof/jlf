<?php

close_div(); // thePayload

end_deliverable( 'htmlPayloadOnly' );

close_div(); // theOutback

open_div( 'id=theFooter' );
  open_table( 'css hfill' );
  open_tr();
    open_td( 'left,style=padding-left:128px;', inlink( 'impressum', 'text=impressum,class=href inlink' ) );
    open_td( 'right small', sprintf( "page %s: $now_mysql utc", ( $client_is_robot ? 'cached' : 'generated' ) ) );
  close_table();
  debug_window_view();
close_div();

// insert an invisible submit button to allow to submit the update_form by pressing ENTER:
open_span( 'nodisplay', html_tag( 'input', 'type=submit', NULL ) );

close_form();

?>
