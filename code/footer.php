<?php

if( $global_format !== 'html' ) {
  // no footer (yet) for formats other than html:
  return;
}

if( $global_context >= CONTEXT_IFRAME ) {
  close_div(); // payload container
}

if( $global_context >= CONTEXT_WINDOW ) {
  open_div( 'class=footer,id=theFooter' );
    if( $debug ) {
      open_div( 'bigskips,id=jsdebug', '[INIT]' ); // 
    }
    open_table( 'hfill' );
      open_td( 'left' );
        echo 'server: ' . html_tag( 'span', 'bold', adefault( $_ENV, 'HOSTNAME', '(unknown host)' ) .'/'. adefault( $_SERVER, 'server', '(unknown server)' ) ) . ' | ';
        echo $logged_in ? ( 'user: ' . html_tag( 'span', 'bold', $login_uid ) ) : '(anonymous access)';
        echo ' | auth: ' .html_tag( 'span', 'bold', $login_authentication_method );
      close_td();
  
      $lines = file( 'version.txt' );
      $version = "jlf version " . adefault( $lines, 1, '(unknown)' );
      if( ( $url = adefault( $lines, 0, '' ) ) ) {
        $version = html_tag( 'a', "href=$url", $version );
      }
      open_td( 'center', $version );
      open_td( 'right', "$now_mysql utc" );
  
    close_table();
  close_div();
}
if( $global_context >= CONTEXT_IFRAME ) {
  // insert an invisible submit button to allow to submit the update_form by pressing ENTER:
  open_span( 'nodisplay', html_tag( 'input', 'type=submit', NULL ) );
}

?>
