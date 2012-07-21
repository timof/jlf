<?php

// activate this line to see very early errors:
//
// echo "format: html\n";
// echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n\n";

$debug = 1; // good choice in case of very early errors
$language = 'E';

require_once('code/common.php');

// POST parameter l is treated special: we evaluate and sanitize it early and store into $login; this can be
// used to pass small amounts of data e.g. to the code handling login, before a session is established:
//
$login = adefault( $_POST, 'l', '' );
unset( $_POST['l'] );
if( ! preg_match( '/^[A-Za-z_]{1,32}$/', $login ) ) {
  $login = '';
}

$me = adefault( $_GET, 'me', 'menu,menu,1' );
unset( $_GET['me'] );
need( preg_match( '/^[a-zA-Z0-9_,]*$/', $me ) );

$me = explode( ',', $me );
$script = adefault( $me, 0, 'menu' );
$window = adefault( $me, 1, 'menu' );
$thread = adefault( $me, 2, '1' );
need( preg_match( '/^[1-4]$/', $thread ) );
$parent_script = adefault( $me, 3, $script );
$parent_script or $parent_script = $script;
$parent_window = adefault( $me, 4, $window );
$parent_window or $parent_window = $window;
$parent_thread = adefault( $me, 5, $thread );
$parent_thread or $parent_thread = $thread;
need( preg_match( '/^[1-4]$/', $parent_thread ) );
$global_format = adefault( $me, 6, 'html' );

switch( $window ) {
  case 'DIV':
    $global_context = CONTEXT_DIV;
    break;
  case 'IFRAME':
    $global_context = CONTEXT_IFRAME;
    break;
  default:
    $global_context = CONTEXT_WINDOW;
    break;
}
switch( $global_format ) {
  case 'csv':
    // header( 'Content-Type: text/force-download' );
    header( 'Content-Type: text/plain' );
    header( 'Content-Disposition: attachement; filename="'.$script.'.csv"' );
    $global_context = CONTEXT_DOWNLOAD;
    break;
  case 'pdf':
    header( 'Content-Type: application/pdf' );
    header( 'Content-Disposition: attachement; filename="'.$script.'.pdf"' );
    $global_context = CONTEXT_DOWNLOAD;
    break;
  case 'html':
    break;
  default:
    error( 'unsupported global_format', LOG_FLAG_CODE, 'init' );
}

// the following must come early before any output is printed:
//
$cookie_support = check_cookie_support();
handle_login();

// start output now - the htmlDefuse filter will gobble everything up to the "format:"-line:
//
echo "\n\n  ERROR: if you see this line in browser, you need to configure htmlDefuse as ExtFilter for your apache server! \n\n";

if( function_exists( 'init_session' ) ) {
  init_session( $login_sessions_id );
}

if( $login_sessions_id ) {

  // $script_basename = basename( $_SERVER['SCRIPT_URL'] ); // SCRIPT_URL: script relative to document root
  // if( $script_basename === 'index.php' ) { // SCRIPT_URL: script relative to document root
  if( $global_format === 'html' ) {
    if( $global_context >= CONTEXT_WINDOW ) {
      js_on_exit( sprintf( "window.name = {$H_SQ}%s{$H_SQ};", js_window_name( $window, $thread ) ) );
    }
    // all GET requests via load_url() and POST requests via submit_form() will pass current window scroll
    // position in paramater xoffs. restore position for 'self'-requests:
    //
    if( ( $parent_script === 'self' ) && ( $global_context >= CONTEXT_IFRAME ) ) {
      // restore scroll position:
      $offs_field = init_var( 'offs', 'sources=http,default=0x0' );
      if( preg_match( '/^(\d+)x(\d+)$/', $offs_field['value'], & $matches ) ) {
        $xoff = $matches[ 1 ];
        $yoff = $matches[ 2 ];
        js_on_exit( "window.scrollTo( $xoff, $yoff ); " );
      }
    }
  }
  //   } else if( $script_basename == 'get.rphp' ) {
  //     $global_context = CONTEXT_DOWNLOAD;
  //     $script = 'download';
  //   } else if( $script_basename == 'get.php' ) {
  //     $global_context = CONTEXT_DIV;
  // } else {
  //   error( 'invalid script requested: ['. $script_basename . ']', LOG_FLAG_INPUT | LOG_FLAG_CODE, 'links' );
  // }

  retrieve_all_persistent_vars();

  if( $show_debug_button ) {
    init_var( 'debug', 'global,type=u,sources=http window,default=0,set_scopes=window' ); // if set, debug will also be included in every url!
  } else {
    $debug = 0;
  }
  init_var( 'action', 'global,type=w,default=nop,sources=http' );
  init_var( 'language', 'global,sources=http persistent,default=D,type=W1,pattern=/^[DE]$/,set_scopes=session' );

  if( is_readable( "$jlf_application_name/common.php" ) ) {
    include( "$jlf_application_name/common.php" );
  }

  // head.php: if global_format is 'html',
  // - print <doctype> babble and html <head> section
  // - open update form
  // - output visible page head
  //
  include( "$jlf_application_name/head.php" );

  // thread support: check whether we are requested to fork:
  //
  if( ( $global_context >= CONTEXT_WINDOW ) && ( $login === 'fork' ) ) {
    fork_new_thread();
  }

  if( $login === 'login' ) { // request: show paleolithic-style login form:
    form_login();
  } else {
    switch( $login_authentication_method ) {
      case 'public':
        $path = "$jlf_application_name/public/$script.php";
        break;
      default:
        $path = "$jlf_application_name/windows/$script.php";
        break;
    }
    if( is_readable( $path ) ) {
      include( $path );
    } else {
      error( "invalid script: $script", LOG_FLAG_INPUT | LOG_FLAG_CODE, 'links' );
    }
  }

  set_persistent_var( 'thread_atime', 'thread', $utc );
  store_all_persistent_vars();

  include( "$jlf_application_name/footer.php" );

} else if( $global_format === 'html' ) {
  switch( $cookie_support ) {
    case 'fail':
      header_view( 'html', 'please activate cookie support in your browser' );
      open_div( 'bigskips warn', 'please activate cookie support in your browser / Bitte cookie-Unterstützung ihres Browsers einschalten!' );
      break;
    case 'probe':
      header_view( 'html', 'checking cookie support...' );
      js_on_exit( "/* alert( {$H_SQ}sending cookie probe{$H_SQ} ); */ submit_form( {$H_SQ}update_form{$H_SQ}, $H_SQ$H_SQ, {$H_SQ}cookie_probe{$H_SQ} );" );
      exit();
   case 'ignore':
     // todo: how to handle robots - creating a new session on every access is not quite good.
     break;
   case 'ok':
      header_view( 'html', 'access denied / kein Zugriff' );
      open_div( 'bigskips warn', 'access denied: no public access / kein öffentlicher Zugriff' );
      break;
   default:
      error( 'unexpected value for $cookie_support', LOG_FLAG_CODE, 'sessions,cookie' );
  }
} else {
  // not in html mode - cannot do much here:
  echo "request failed: no session\n";
}

if( substr( get_itan(), -2 ) == '00' ) {
  sql_garbage_collection();
}

?>
