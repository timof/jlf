<?php
//
// global.php: define constants and global variables
//

// set defaults used in case of very early errors:
//
$debug = 1;
$language = 'E';

$initialization_steps = array();

// define encoding for HTML hot characters:
//
define( 'H_SQ', "\x11" );
define( 'H_DQ', "\x12" );
define( 'H_LT', "\x13" );
define( 'H_GT', "\x14" );
define( 'H_AMP', "\x15" );

define( 'DIVERT_OUTPUT_SEQUENCE',   "\n\x13\x13\x13\n" );
define( 'UNDIVERT_OUTPUT_SEQUENCE', "\n\x14\x14\x14\n" );
define( 'ESCAPE_OUTPUT_SEQUENCE',   "\x15\x15\x14" );

$H_LT = H_LT;
$H_GT = H_GT;
$H_SQ = H_SQ;
$H_DQ = H_DQ;
$AUML = H_AMP.'Auml;';
$aUML = H_AMP.'auml;';
$OUML = H_AMP.'Ouml;';
$oUML = H_AMP.'ouml;';
$UUML = H_AMP.'Uuml;';
$uUML = H_AMP.'uuml;';
$SZLIG = H_AMP.'szlig;';


define( 'WORD_PATTERN', '/^[a-zA-Z_][a-zA-Z0-9_]{0,255}$/' );

//context for script output:
//
define( 'CONTEXT_DOWNLOAD', 12 ); // produce no html at all - download one item
define( 'CONTEXT_DIV', 23 );      // produce html fragment
define( 'CONTEXT_IFRAME', 34 );   // complete html document, but no window
define( 'CONTEXT_WINDOW', 45 );   // complete html document in browser window

// some variables are only in ENV (eg: HOSTNAME), some only in SERVER (eg: auth, user, robot), some in both (eg: jlf_mysql_db_name),
// with no obvious system (???). we just merge them:
//
$_ENV = array_merge( $_ENV, $_SERVER );

// evaluate some cgi parameters early (they are needed before a session is established):
// we can't do proper error handling here yet, so we silently map invalid input to safe defaults:


// handle session cookies: either actual $_COOKIE, or url-cookie passed around in cgi parameter c
// the special value 0_0 matches the pattern and is used for probing but does not refer to a valid session 
//
define( 'COOKIE_PATTERN', '/^(\d{1,9})_([a-f0-9]{1,12})$/' );
define( 'COOKIE_NAME', $jlf_application_name .'_'. $jlf_application_instance . '_keks' );
$cookie_type = $cookie_signature = $cookie = '';
$cookie_sessions_id = 0;
if( isset( $_COOKIE[ COOKIE_NAME ] ) && preg_match( COOKIE_PATTERN, $_COOKIE[ COOKIE_NAME ], /* & */ $matches ) ) {
  $cookie_type = 'http';
} else if( isset( $_GET['c'] ) && preg_match( COOKIE_PATTERN, $_GET['c'], /* & */ $matches ) ) {
  $cookie_type = 'url';
}
unset( $_GET['c'] );
if( $cookie_type ) {
  $cookie_sessions_id = $matches[ 1 ];
  $cookie_signature = $matches[ 2 ];
  $cookie = $cookie_sessions_id .'_'. $cookie_signature;
  setcookie( COOKIE_NAME, $cookie, 0, '/' ); // just try it - will seamlessly switch to http cookies if possible
}

unset( $_GET['d'] );

unset( $_POST['DEVNULL'] );

// POST parameter l: used to pass small amounts of data to early or low-level code:
//
$login = ( ( isset( $_POST['l'] ) && preg_match( '/^[A-Za-z_]{1,32}$/', $_POST['l'] ) ) ? $_POST['l'] : '' );
unset( $_POST['l'] );
if( $login === 'cookie_probe' ) {
  // cookie probe implies: no session yet and thus no valid itan; avoid invalid form errors:
  $_POST = array();
}

$me = ( isset( $_GET['m'] ) ? $_GET['m'] : '' );
unset( $_GET['m'] );
if( ! preg_match( '/^[a-zA-Z0-9_,]{1,256}$/', $me ) ) {
  $me = 'menu,menu,1';
}

$me = explode( ',', $me );
$script = ( ( isset( $me[ 0 ] ) && $me[ 0 ] ) ? $me[ 0 ] : 'menu' );
$window = ( ( isset( $me[ 2 ] ) && $me[ 2 ] ) ? $me[ 2 ] : 'menu' );
$thread = ( ( isset( $me[ 4 ] ) && preg_match( '/^[1-4]$/', $me[ 4 ] ) ) ? $me[ 4 ] : '1' );

$parent_script = ( ( isset( $me[ 1 ] ) && $me[ 1 ] ) ? $me[ 1 ] : $script );
$parent_window = ( ( isset( $me[ 3 ] ) && $me[ 3 ] ) ? $me[ 3 ] : $window );
$parent_thread = ( ( isset( $me[ 5 ] ) && preg_match( '/^[1-4]$/', $me[ 5 ] ) ) ? $me[ 5 ] : $thread );

$action = ( ( isset( $_POST['action'] ) && preg_match( WORD_PATTERN, $_POST['action'] ) ) ? $_POST['action'] : 'nop' );
unset( $_POST['action'] );

$deliverable = ( ( isset( $_GET['i'] ) && preg_match( WORD_PATTERN, $_GET['i'] ) ) ? $_GET['i'] : '' );
unset( $_GET['i'] );

if( isset( $_POST['f'] ) ) {
  $global_format = $_POST['f'];
} else if( isset( $_GET['f'] ) ) {
  $global_format = $_GET['f'];
} else {
  $global_format = 'html';
}
unset( $_GET['f'] ); unset( $_POST['f'] );

if( $deliverable ) {
  $global_filter = 'error';
  $window = 'download';
  $global_context = CONTEXT_DOWNLOAD;
  switch( $global_format ) {
    case 'csv':
      $global_filter = 'null';
      header( 'Content-Type: text/plain' );
      // header( 'Content-Disposition: attachement; filename="'.$script.'.csv"' );
      break;
    case 'ldif':
      $global_filter = 'null';
      header( 'Content-Type: text/plain' );
      break;
    case 'pdf':
      $global_filter = 'null';
      header( 'Content-Type: text/plain' ); // for testing
      // header( 'Content-Type: application/pdf' );
      // header( 'Content-Disposition: attachement; filename="'.$script.'.pdf"' );
      break;
    case 'download':
      $global_filter = 'xxd';
      // 'Content-Type'-header to be set later!
      break;
    case 'cli':
      break;
    default:
    case 'html':
      $global_format = 'html';
      $global_filter = 'html';
      break;
  }
} else {
  // case 'cli': // doesn't apply
  $global_filter = 'html';
  $global_context = CONTEXT_WINDOW;
//   switch( $window ) {
//     case 'DIV':
//       $global_context = CONTEXT_DIV;
//       break;
//     case 'IFRAME':
//       $global_context = CONTEXT_IFRAME;
//       break;
//     default:
//       $global_context = CONTEXT_WINDOW;
//       break;
//   }
}

?>
