<?php // code/start.php

require_once('code/environment.php');

init_login();
switch( check_cookie_support() ) {
  case 'fail': // should never happen if url cookies are allowed
    html_head_view( 'please activate cookie support in your browser' );
    open_div( 'bigskips warn', 'please activate cookie support in your browser / Bitte cookie-Unterstützung ihres Browsers einschalten!' );
    sql_do( 'COMMIT AND NO CHAIN' );
    return;
  case 'probe':
    html_head_view( 'checking cookie support...' );
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
    html_head_view( 'access denied / kein Zugriff' );
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
  // optional: application-specific code to be executed early:
  init_session( $login_sessions_id );
}

get_itan(); // pick new itans
sanitize_http_input();
// irreversibly commit new and invalidate submitted itans (if any) and start main transaction:
sql_do( 'COMMIT AND CHAIN' );

retrieve_all_persistent_vars();

if( $show_debug_button ) {
  init_var( 'debug', 'global,type=u2,sources=http window,default=0,set_scopes=window' ); // if set, debug will also be included in every url!
} else {
  $debug = 0;
}
init_var( 'language', 'global,sources=http persistent,default=D,type=W1,pattern=/^[DE]$/,set_scopes=session' );
$language_suffix = ( $language === 'D' ? 'de' : 'en' );
init_var( 'action', 'global,sources=http,default=nop,type=W256' );

$initialization_steps['session_ready'] = true;

//
// global session environment is ready, we can now run the application:
//

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
if( ( ! $deliverable ) && ( $login === 'fork' ) ) {
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

include( "$jlf_application_name/foot.php" );


if( $debug & DEBUG_FLAG_PROFILE ) {
  sql_do( 'COMMIT AND CHAIN' );
  $debug = 0; // don't profile the profiler
  $invocation = 0;
  foreach( $sql_profile as $p ) {
    $p['script'] = $script;
    $p['invocation'] = $invocation;
    // $id = sql_insert( 'profile', $p );
    if( ! $invocation ) {
      $invocation = $id;
      // sql_update( 'profile', $id, "invocation=$invocation" );
    }
  }
  debug( count( $sql_profile ), 'entries in sql_profile:' );
  debug( reset( $sql_profile ), 'first profile entry:' );
}

sql_do( 'COMMIT AND NO CHAIN' );

?>
