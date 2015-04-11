<?php
//
// robots.php: detect new robots; decide whether to treat client as robot

$client_ip4 = $_SERVER['REMOTE_ADDR'];
$client_port = $_SERVER['REMOTE_PORT'];
if( ! preg_match( '/^[0-9.]{7,15}$/', $client_ip4 ) ) {
  $client_ip4 = 'INVALID';
}
if( ! preg_match( '/^[0-9]{1,5}$/', $client_port ) ) {
  $client_port = 0;
}
$client_user_agent = preg_replace( '[^a-zA-Z0-9_/() ]', '.', $_SERVER['HTTP_USER_AGENT'] );

sql_transaction_boundary( '', 'robots' );

// check whether apache detected client as robot:
//
$server_detected_robot = adefault( $_SERVER, 'robot', 0 );

// check whether client is known robot in db
//
$client_is_robot = sql_query( 'robots', array( 'filters' => array( 'ip4' => $client_ip4, 'cn' => $client_user_agent ), 'single_field' => 'COUNT', 'authorized' => 1 ) );

if( isset( $_GET['r'] ) ) {
  unset( $_GET['r'] );

  if( ! $client_is_robot ) {
    $client_is_robot = 1;
    logger( "request for robots.txt from new robot: [$client_ip4] [$client_user_agent]", LOG_LEVEL_INFO, LOG_FLAG_SYSTEM, 'robots' );
  }

  sql_insert( 'robots'
  , array(
      'ip4' => $client_ip4
    , 'cn' => $client_user_agent
    , 'atime' => $utc
    , 'freshmeat' => ( $server_detected_robot ? 0 : 1 )
    )
  , array( 'update_cols' => array( 'atime' => true, 'freshmeat' => true ) )
  , 'authorized=1'
  );

  header( 'Content-Type: text/plain' );
  
  echo "\nextfilter: null\n";

  echo UNDIVERT_OUTPUT_SEQUENCE;

  echo "# you have requested the file robots.txt\n";
  echo "# your client [$client_user_agent] from IP $client_ip4 will be treated as a robot for at least 72 hours from now on\n";
  echo "UserAgent: *\n";
  echo "Disallow:\n";

  sql_transaction_boundary();
  sql_commit_delayed_inserts();
  apache_note( 'php_note_robot', '1' );
  apache_note( 'php_note_result', 'R' );
  die(0);
}

sql_transaction_boundary();

if( $server_detected_robot ) {
  $client_is_robot = 1;
}

if( $client_is_robot ) {
  apache_note( 'php_note_robot', '1' );
  $insert_nonce_in_urls = 0;
  if( isset( $_GET['c'] ) || isset( $_GET['d'] ) ) {
    header("HTTP/1.0 410 gone");
    header( 'Content-Type: text/plain' );

    echo "\nextfilter: null\n";
    echo UNDIVERT_OUTPUT_SEQUENCE;
    echo "# not for robots\n";
    die(0);
  }
} else {
  apache_note( 'php_note_robot', '0' );
}

unset( $_GET['c'] );
unset( $_GET['d'] );

?>
