<?php // code/foot.php


close_div(); // thePayload

end_deliverable( 'htmlPayloadOnly' );

close_div(); // theOutbacks

open_div( 'id=theFooter' );
  open_table( 'css hfill' );
  open_tr();
    open_td( 'left' );
      echo 'server: ' . html_tag( 'span', 'bold', adefault( $_ENV, 'HOSTNAME', '(unknown host)' ) .'/'. adefault( $_SERVER, 'server', '(unknown server)' ) ) . ' | ';
      echo $logged_in ? ( 'user: ' . html_tag( 'span', 'bold', $login_uid ) ) : '(anonymous access)';
      echo ' | auth: ' .html_tag( 'span', 'bold', $login_authentication_method );

    $lines = file( 'version.txt' );
    $version = "jlf version " . adefault( $lines, 1, '(unknown)' );
    if( ( $url = adefault( $lines, 0, '' ) ) ) {
      $version = html_tag( 'a', "href=$url", $version );
    }
    open_td( 'center', $version );
    open_td( 'right', "$now_mysql utc" );
  close_table();
  debug_window_view();
close_div();

// insert an invisible submit button to allow to submit the update_form by pressing ENTER:
open_span( 'nodisplay', html_tag( 'input', 'type=submit', NULL ) );
close_form();

?>
