<?php // code/start.php

require_once('code/environment.php');

init_login();

switch( check_cookie_support() ) {
  case 'fail': // should never happen if url cookies are allowed
    html_head_view( 'please activate cookie support in your browser' );
    open_div( 'bigskips warn', 'please activate cookie support in your browser / Bitte cookie-Unterstützung ihres Browsers einschalten!' );
    return;
  case 'probe':
    html_head_view( 'checking cookie support...' );
    send_cookie_probe();
    return;
  case 'ignore': // mostly for robots: ignore missing cookie support and try to create dummy session:
    sql_transaction_boundary('*');
      try_public_access();
    sql_transaction_boundary();
    break;
  case 'http':
  case 'url':
    // great, cookies are supported - try hard to get a regular session:
    sql_transaction_boundary('*');
      handle_login();
    sql_transaction_boundary();
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
  return;
}

//
// beyond this point, we have a valid $login_sessions_id:
//

if( function_exists( 'init_session' ) ) {
  // optional: application-specific code to be executed early:
  init_session( $login_sessions_id );
}

sql_transaction_boundary( '', 'transactions' );
  get_itan(); // pick new itans
  sanitize_http_input();
sql_transaction_boundary(); // will irreversibly commit new and invalidate submitted itans (if any)

sql_transaction_boundary( 'persistentvars' );
  retrieve_all_persistent_vars();
sql_transaction_boundary();

init_debugger();

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
    // sql_transaction_boundary('*'); // global lock - temporary kludge until scripts are locking-aware
    include( $path );
  } else {
    error( "invalid script: $script", LOG_FLAG_INPUT | LOG_FLAG_CODE, 'links' );
  }
}
sql_transaction_boundary(); // in case a script returns early while in transaction


include( "$jlf_application_name/foot.php" );

set_persistent_var( 'thread_atime', 'thread', $utc );
sql_transaction_boundary( '', 'persistentvars' );
  store_all_persistent_vars();
sql_transaction_boundary();

finish_debugger();

sql_commit_delayed_inserts();

?>
