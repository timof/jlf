<?php // code/foot.php

// insert an invisible submit button to allow to submit the update_form by pressing ENTER:
open_span( 'nodisplay', html_tag( 'input', 'type=submit', NULL ) );
close_form();

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
  if( $debug & DEBUG_FLAG_JAVASCRIPT ) {
    open_div( 'debugbox,id=jsdebug', '[INIT]' );
  }
  if( $debug & DEBUG_FLAG_VARIABLES ) {
    open_div( 'debugbox,id=variablesdebug' );
      echo "[$allowed_authentication_methods,$cookie,$login_sessions_id,l:$login,a:$action,d:$deliverable]";
    close_div();
  }
  if( $debug & DEBUG_FLAG_PROFILE ) {
    open_div( 'debugbox,id=profiledebug' );
      sql_do( 'COMMIT AND CHAIN' );
      $debug = 0; // don't profile the profiler
      $invocation = 0;
      foreach( $sql_profile as $p ) {
        $p['script'] = $script;
        $p['invocation'] = $invocation;
        // $id = sql_insert( 'profile', $p );
        if( ! $invocation ) {
          // $invocation = $id;
          // sql_update( 'profile', $id, "invocation=$invocation" );
        }
      }
      debug( count( $sql_profile ), 'entries in sql_profile:' );
      $total = 0;
      $n = 0;
      foreach( $sql_profile as $p ) {
        $total += ( $t = $p['wallclock_seconds'] );
        if( $n++ < MAX_PROFILE_RECORDS ) {
          debug( $p['sql'], "seconds: $t" );
        }
      }
      debug( $total, 'total seconds:' );
    close_div();
  }
close_div();


?>
