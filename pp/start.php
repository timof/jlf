<?php // pp/start.php

require_once('code/environment.php');

// nail it, for the public pages (overrides the setting of the shared leitvariable):
//
$allowed_authentication_methods = 'public';
init_login();
switch( check_cookie_support() ) {
  case 'fail': // should never happen if url cookies are allowed
    html_head_view( 'please activate cookie support in your browser' );
    open_div( 'bigskips warn', 'please activate cookie support in your browser / Bitte cookie-Unterstützung ihres Browsers einschalten!' );
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
  case 'probe':
    // 'probe' would be unexpected here as we can always fallback to url cookies for the public pages!
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
  if( $insert_itan_in_forms ) {
    get_itan(); // pick new itans
  }
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

$script_defaults = script_defaults( $script );
$file = adefault( $script_defaults, 'file' );

// head.php: if global_format is 'html',
// - print <doctype> babble and html <head> section
// - open update form
// - output visible page head
//
include( "$jlf_application_name/head.php" );

if( $file && is_readable( ( $path = "$jlf_application_name/windows/$file" ) ) ) {
  include( $path );
  sql_transaction_boundary(); // in case a script returns early while in transaction
} else {
  logger( "invalid script: $script", LOG_LEVEL_WARNING, LOG_FLAG_INPUT | LOG_FLAG_CODE, 'links' );
  open_div( 'warn bigskips qquads', "invalid script: $script" );
}

include( "$jlf_application_name/foot.php" );

set_persistent_var( 'thread_atime', 'thread', $utc );
sql_transaction_boundary( '', 'persistentvars' );
  store_all_persistent_vars();
sql_transaction_boundary();

$end_unix_microtime = microtime( true );
finish_debugger();

sql_commit_delayed_inserts();

?>
