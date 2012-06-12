<?php

// reasonable defaults for very early scripts (in particular: login.php):
//
$script = 'menu';
$window = 'menu';
$thread = '1';
$debug = 1; // good choice in case of very early errors

// activate this line to see very early errors:
//
// echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n\n";

require_once('code/common.php');

// POST parameter l is treated special: we evaluate and sanitize it early and store into $login; this can be
// used to pass small amounts of data e.g. to the code handling login, before a session is established:
//
if( isset( $_POST['l'] ) ) {
  $login = (string)$_POST['l'];
  unset( $_POST['l'] );
  if( ! preg_match( '/^[A-Za-z_]{1,16}$/', $login ) )
    $login = '';
} else {
  $login = '';
}

switch( $login ) {
  case 'Lcsv':
    $global_format = 'csv';
    $login = '';
    break;
  case 'Lpdf':
    $global_format = 'pdf';
    $login = '';
    break;
  default:
    $global_format = 'html';
}

$cookie_support = check_cookie_support();

handle_login();

if( function_exists( 'init_session' ) ) {
  init_session( $login_sessions_id );
}

if( $login_sessions_id ) {

  init_var( 'me', array( 'global' => true, 'sources' => 'http', 'default' => 'menu,menu,1' ) );

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

  $script_basename = basename( $_SERVER['SCRIPT_URL'] ); // SCRIPT_URL: script relative to document root
  if( $script_basename == 'index.php' ) { // SCRIPT_URL: script relative to document root
    switch( $global_format ) {
      case 'html':
        switch( $window ) {
          case 'IFRAME': // complete html, but no browser window
            $global_context = CONTEXT_IFRAME;
            break;
          case 'DIV':  // html fragment only
            $global_context = CONTEXT_DIV;
            break;
          default:  // complete html with :
            $global_context = CONTEXT_WINDOW;
            break;
        }
        break;
      case 'pdf':
        header( 'Content-Type: application/pdf' );
        $global_context = CONTEXT_DIV; // not really, but we don't need the html header and stuff either in the .pdf
        break;
      case 'csv':
        header( 'Content-Type: text/plain' );
        $global_context = CONTEXT_DIV;
        break;
    }
  } else if( $script_basename == 'get.rphp' ) {
    $global_context = CONTEXT_DOWNLOAD;
    $script = 'download';
  } else if( $script_basename == 'get.php' ) {
    $global_context = CONTEXT_DIV;
  } else {
    error( 'invalid script requested: ['. $script_basename . ']' );
  }

  if( $global_context >= CONTEXT_WINDOW )
    js_on_exit( sprintf( "window.name = {$H_SQ}%s{$H_SQ};", js_window_name( $window, $thread ) ) );

  retrieve_all_persistent_vars();

  init_var( 'debug', 'global,type=u,sources=http window,default=0,set_scopes=window' ); // if set, debug will also be included in every url!
  init_var( 'action', 'global,type=w,default=nop,sources=http' );
  init_var( 'language', 'global,sources=http persistent,default=D,type=W1,pattern=/^[DE]$/,set_scopes=session' );

  if( is_readable( "$jlf_application_name/common.php" ) ) {
    include( "$jlf_application_name/common.php" );
  }

  // head.php:
  // - print <doctype> babble and html <head> section
  // - open update form
  // - output visible page head
  //
  include('code/head.php');


  // thread support: check whether we are requested to fork:
  //
  if( ( $global_context >= CONTEXT_WINDOW ) && ( $login === 'fork' ) ) {
    fork_new_thread();
  }


  if( $login == 'login' ) { // request: show paleolithic-style login form:
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
      error( "invalid script: $script" );
    }
  }

  // all GET requests via load_url() and POST requests via submit_form() will pass current window scroll
  // position in paramater xoffs. restore position for 'self'-requests:
  //
  if( $parent_script === 'self' ) {
    // restore scroll position:
    $offs_field = init_var( 'offs', 'sources=http,default=0x0' );
    if( preg_match( '/^(\d+)x(\d+)$/', $offs_field['value'], & $matches ) ) {
      $xoff = $matches[ 1 ];
      $yoff = $matches[ 2 ];
    } else {
      $xoff = $yoff = 0;
    }
    if( $global_context >= CONTEXT_IFRAME ) {
      js_on_exit( "window.scrollTo( $xoff, $yoff ); " );
    }
  }

  set_persistent_var( 'thread_atime', 'thread', $utc );
  store_all_persistent_vars();

  include('code/footer.php');

} else {
  switch( $cookie_support ) {
    case 'fail':
      open_div( 'bigskips warn', we('please activate cookie support in your browser!','Bitte cookie-Unterstützung ihres Browsers einschalten!') );
      break;
    case 'probe':
      setcookie( cookie_name(), 'probe', 0, '/' );
      html_header_view( 'checking cookie support...' );
      js_on_exit( "/* alert( {$H_SQ}sending cookie probe{$H_SQ} ); */ submit_form( {$H_SQ}update_form{$H_SQ}, $H_SQ$H_SQ, {$H_SQ}cookie_probe{$H_SQ} );" );
      exit();
   case 'ignore':
     // todo: how to handle robots - creating a new session on every access is not quite good.
     break;
   case 'ok':
      open_div( 'bigskips warn', we('failed - no public access','Fehler - kein öffentlicher Zugriff') );
      break;
   default:
      error( 'unexpected value for $cookie_support' );
  }
}


if( substr( get_itan(), -2 ) == '00' ) {
  logger( 'starting garbage collection', 'maintenance' );
  sql_garbage_collection();
}

?>
