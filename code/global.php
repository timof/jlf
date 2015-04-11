<?php
//
// global.php: define constants and global variables
//

// set defaults used in case of very early errors:
//
$debug = 0;
$max_debug_chars_display = 1000;
$language = 'E';
$language_suffix = 'en';
$initialization_steps = array();

apache_note( 'php_note_robot', '?' );
apache_note( 'php_note_result', 'E' );
apache_note( 'php_note_debug', '-' );

// define encoding for HTML hot characters:
//
define( 'H_SQ', "\x11" );
define( 'H_DQ', "\x12" );
define( 'H_LT', "\x13" );
define( 'H_GT', "\x14" );
define( 'H_AMP', "\x15" );

define( 'DIVERT_OUTPUT_SEQUENCE',   "\n\x13\x13\x13\n" );
define( 'UNDIVERT_OUTPUT_SEQUENCE', "\n\x14\x14\x14\n" );
// define( 'ESCAPE_OUTPUT_SEQUENCE',   "\x15\x15\x14" );

define( 'AUTH', 'authorized=1' );

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
$NBSP = H_AMP.'nbsp;';
$SHY = H_AMP.'shy;';


// define similar encodings for TeX hot characters:
//
define( 'TEX_BS', "\x11" );
define( 'TEX_LBR', "\x12" );
define( 'TEX_RBR', "\x13" );
$TEX_BS = TEX_BS;
$TEX_LBR = TEX_LBR;
$TEX_RBR = TEX_RBR;

// constants to be used in table logbook:
//
define( 'LOG_LEVEL_DEBUG', 1 );
define( 'LOG_LEVEL_INFO', 2 );
define( 'LOG_LEVEL_NOTICE', 3 );
define( 'LOG_LEVEL_WARNING', 4 );
define( 'LOG_LEVEL_ERROR', 5 );
 
$log_level_text = array( 1 => 'debug', 2 => 'info', 3 => 'notice', 4 => 'warning', 5 => 'error' );
 
//
// flags: can be combined in a bitmask:
//
define( 'LOG_FLAG_AUTH',   0x01 ); // authentication-related event
define( 'LOG_FLAG_ABORT',  0x02 ); // db operation was aborted
define( 'LOG_FLAG_DELETE', 0x04 ); // involved db deletion operation
define( 'LOG_FLAG_UPDATE', 0x08 ); // involved db update operation
define( 'LOG_FLAG_INSERT', 0x10 ); // involved db insert operation
define( 'LOG_FLAG_INPUT',  0x20 ); // issue in user input
define( 'LOG_FLAG_DATA',   0x40 ); // data model violation
define( 'LOG_FLAG_SYSTEM', 0x80 ); // system operation: garbage collection, gb update, ...
define( 'LOG_FLAG_USER',  0x100 ); // special user operation (forking, ...)
define( 'LOG_FLAG_CODE',  0x200 ); // code model violation

$log_flag_text = array(
  0x01 => 'auth'
, 0x02 => 'abort'
, 0x04 => 'delete'
, 0x08 => 'update'
, 0x10 => 'insert'
, 0x20 => 'input'
, 0x40 => 'consistency'
, 0x80 => 'system'
, 0x100 => 'user'
, 0x200 => 'code'
);

define( 'DEBUG_FLAG_INSITU', 0x01 );
define( 'DEBUG_FLAG_LAYOUT', 0x02 );
define( 'DEBUG_FLAG_HTML', 0x04 );
define( 'DEBUG_FLAG_PROFILE', 0x08 );
define( 'DEBUG_FLAG_TRACE', 0x10 );
define( 'DEBUG_FLAG_DEBUGMENU', 0x20 );
define( 'DEBUG_FLAG_JAVASCRIPT', 0x40 );
define( 'DEBUG_FLAG_ROOTMENU', 0x80 );

define( 'WORD_PATTERN', '/^[a-zA-Z_][a-zA-Z0-9_]{0,255}$/' );

// some variables are only in ENV (eg: HOSTNAME), some only in SERVER (eg: auth, user, robot), some in both (eg: jlf_mysql_db_name),
// with no obvious system (???). we just merge them:
//
$_ENV = array_merge( $_ENV, $_SERVER );

$client_is_intranet = adefault( $_ENV, 'client_is_intranet', 0 );

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
if( $cookie_type ) {
  $cookie_sessions_id = $matches[ 1 ];
  $cookie_signature = $matches[ 2 ];
  $cookie = $cookie_sessions_id .'_'. $cookie_signature;
  setcookie( COOKIE_NAME, $cookie, 0, '/' ); // just try it - will seamlessly switch to http cookies if possible
}

// unset( $_GET['c'] );
// unset( $_GET['d'] ); // will be used and later unset in robots.php

unset( $_POST['DEVNULL'] );

// POST parameters q and s: can be used to stuff arbitrary parameters into a single one. q will override s.
//
$s = '';
if( isset( $_POST['q'] ) ) {
  $s = $_POST['q'];
} else if( isset( $_POST['s'] ) ) {
  $s = $_POST['s'];
}
unset( $_POST['q'] );
unset( $_POST['s'] );
if( $s ) {
  need( preg_match( '/^[a-zA-Z0-9_,=]*$/', $s ), "malformed parameter s posted" );
  $s = parameters_explode( $s );
  foreach( $s as $key => $val ) {
    $_POST[ $key ] = hex_decode( $val );
  }
}

// POST parameter l: used to pass small amounts of data to early or low-level code:
//
$login = ( ( isset( $_POST['l'] ) && preg_match( '/^[A-Za-z_]{1,32}$/', $_POST['l'] ) ) ? $_POST['l'] : '' );
unset( $_POST['l'] );
if( $login === 'cookie_probe' ) {
  // cookie probe implies: no session yet and thus no valid itan; avoid invalid form errors:
  $_POST = array();
}

// GET parameter me: determines script, window, thread of me and my parent:
//
$me = ( isset( $_GET['m'] ) ? $_GET['m'] : '' );
unset( $_GET['m'] );
if( ! preg_match( '/^[a-zA-Z0-9_,]{1,256}$/', $me ) ) {
  $me = 'menu,menu,menu';
}

$me = explode( ',', $me );
$script = ( ( isset( $me[ 0 ] ) && $me[ 0 ] ) ? $me[ 0 ] : 'menu' );
$window = ( ( isset( $me[ 2 ] ) && $me[ 2 ] ) ? $me[ 2 ] : 'menu' );
$thread = ( ( isset( $me[ 4 ] ) && preg_match( '/^[1-4]$/', $me[ 4 ] ) ) ? $me[ 4 ] : '1' );

$parent_script = ( ( isset( $me[ 1 ] ) && $me[ 1 ] ) ? $me[ 1 ] : $script );
$parent_window = ( ( isset( $me[ 3 ] ) && $me[ 3 ] ) ? $me[ 3 ] : $window );
$parent_thread = ( ( isset( $me[ 5 ] ) && preg_match( '/^[1-4]$/', $me[ 5 ] ) ) ? $me[ 5 ] : $thread );

// GET parameter i: request 'deliverable' other than the "normal" http output from a script
//
$deliverable = ( ( isset( $_GET['i'] ) && preg_match( WORD_PATTERN, $_GET['i'] ) ) ? $_GET['i'] : '' );
unset( $_GET['i'] );

// parameter f: determines output type (other than http)
// parameter n: file name for be suggested for downloads. this name must be available early, so we need this parameter as a kludge.
//
if( isset( $_POST['f'] ) ) {
  $global_format = $_POST['f'];
  $n = adefault( $_POST, 'n' );
} else if( isset( $_GET['f'] ) ) {
  $global_format = $_GET['f'];
  $n = adefault( $_GET, 'n' );
} else {
  $global_format = 'html';
  $n = false;
}
unset( $_GET['f'] ); unset( $_POST['f'] );
unset( $_GET['n'] ); unset( $_POST['n'] );

if( $deliverable ) {
  if( $n ) {
    $n = hex_decode( $n );
    need( preg_match( '/^[a-zA-Z0-9._-]{1,64}$/', $n ), "malformed parameter n" );
  } else {
    $n = $script;
  }
  $global_filter = 'error';
  $window = 'download';
  switch( $global_format ) {
    case 'csv':
      $global_filter = 'null';
      header( 'Content-Type: text/plain' );
      // header( 'Content-Disposition: attachment; filename="'.rfc2184_encode( $n ).'.csv"' );
      header( 'Content-Disposition: attachment; filename="'.$n.'.csv"' );
      break;
    case 'ldif':
      $global_filter = 'null';
      header( 'Content-Type: text/plain' );
      break;
    case 'pdf':
      $global_filter = 'null';
      // header( 'Content-Type: text/plain' ); // for testing
      header( 'Content-Type: text/pdf' ); // for production
      // header( 'Content-Disposition: attachment; filename="'.rfc2184_encode( $n ).'.pdf"' );
      // header( 'Content-Type: application/pdf' );
      header( 'Content-Disposition: attachment; filename="'.$n.'.pdf"' );
      break;
    case 'jpg':
      $global_filter = 'null';
      // header( 'Content-Type: text/plain' ); // for testing
      header( 'Content-Type: image/jpeg' ); // for production
      // header( 'Content-Disposition: attachment; filename="'.rfc2184_encode( $n ).'.pdf"' );
      // header( 'Content-Type: application/pdf' );
      break;
    case 'download':
      $global_filter = 'xxd';
      // 'Content-Type'-header to be set later!
      break;
    case 'cli':
      // will not pass through filter
      break;
    default:
    case 'html':
      header( 'Content-Type: text/html' );
      $global_format = 'html';
      $global_filter = 'html';
      break;
  }
} else {
  // case 'cli': // doesn't apply
  $global_filter = 'html';
}


date_default_timezone_set('UTC'); // the only sane choice

// we take exactly _one_ wall clock reading per script run and store the result in $now_unix:
// $now_mysql is to be used instead of NOW() (in sql) and repeated calls of date() (in php), because:
//  - can be quoted (in sql)
//  - use same time everywhere during one script run
$start_unix_microtime = microtime( true );
$now_unix = (int) $start_unix_microtime;
$utc = $now_canonical = datetime_unix2canonical( $now_unix );
$current_year = substr( $utc, 0, 4 );
$current_month = substr( $utc, 4, 2 );
$current_day = substr( $utc, 6, 2 );
$today_canonical = substr( $utc, 0, 8 );
$today_mysql = date_canonical2weird( $today_canonical );
$now_mysql = $today_mysql . ' ' . time_canonical2weird( $now_canonical );

$jlf_persistent_vars = array();

$actions_handled = array();

?>
