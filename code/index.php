<?php
//
// activate the following line to see very early errors:
//
// echo "extfilter: html\n";

error_reporting( E_ALL );

require_once('code/common.php');

init_login();
switch( check_cookie_support() ) {
  case 'fail': // should never happen if url cookies are allowed
    header_view( 'html', 'please activate cookie support in your browser' );
    open_div( 'bigskips warn', 'please activate cookie support in your browser / Bitte cookie-Unterstützung ihres Browsers einschalten!' );
    sql_do( 'COMMIT AND NO CHAIN' );
    return;
  case 'probe':
    header_view( 'html', 'checking cookie support...' );
    send_cookie_probe();
    sql_do( 'COMMIT AND NO CHAIN' );
    return;
  case 'ignore': // mostly for robots: ignore missing cookie support and try to create dummy session:
    try_public_access();
    break;
  case 'http':
  case 'url':
    // great, cookies are supported - try hard to get a regular session:
    handle_login();
    break;
  default:
    error( 'unexpected value for $cookie_support', LOG_FLAG_CODE, 'sessions,cookie' );
}

// start output now - the htmlDefuse filter will gobble everything up to the "extfilter:"-line:
//
echo "\n\n  ERROR: if you see this line in browser, you need to configure htmlDefuse as ExtFilter for your apache server! \n\n";

if( ! $login_sessions_id ) {
  if( $global_format === 'html' ) {
    header_view( 'html', 'access denied / kein Zugriff' );
    open_div( 'bigskips warn', 'access denied / kein Zugriff' );
  } else {
    // not in html mode - cannot do much here:
    echo "request failed: no session\n";
  }
  sql_do( 'COMMIT AND NO CHAIN' );
  return;
}

//
// beyond this point, we have a valid $login_sessions_id:
//

if( function_exists( 'init_session' ) ) {
  init_session( $login_sessions_id );
}

get_itan(); // pick new itans
sanitize_http_input();
// irreversibly commit new and invalidate submitted itans (if any) and start main transaction:
sql_do( 'COMMIT AND CHAIN' );

retrieve_all_persistent_vars();

if( $show_debug_button ) {
  init_var( 'debug', 'global,type=u,sources=http window,default=0,set_scopes=window' ); // if set, debug will also be included in every url!
} else {
  $debug = 0;
}
init_var( 'action', 'global,type=w,sources=http,default=nop' );
init_var( 'i', 'global=item,type=w,sources=http,default=' );
// init_var( 'message', 'global,type=u,sources=http,default=0' );
init_var( 'language', 'global,sources=http persistent,default=D,type=W1,pattern=/^[DE]$/,set_scopes=session' );

$initialization_steps['session_ready'] = true;

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
  $path = ( "$jlf_application_name/" .( $logged_in ? 'windows' : 'public' ). "/$script.php" );
  if( is_readable( $path ) ) {
    include( $path );
  } else {
    error( "invalid script: $script", LOG_FLAG_INPUT | LOG_FLAG_CODE, 'links' );
  }
}

set_persistent_var( 'thread_atime', 'thread', $utc );
store_all_persistent_vars();

include( "$jlf_application_name/footer.php" );

sql_do( 'COMMIT AND NO CHAIN' );

?>
