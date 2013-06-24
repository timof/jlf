<?php

require_once('code/environment.php');

// nail it, for the public pages (overrides the setting of the shared leitvariable):
//
$allowed_authentication_methods = 'public';
init_login();
switch( check_cookie_support() ) {
  case 'fail': // should never happen if url cookies are allowed
    html_head_view( 'please activate cookie support in your browser' );
    open_div( 'bigskips warn', 'please activate cookie support in your browser / Bitte cookie-Unterstützung ihres Browsers einschalten!' );
    sql_do( 'COMMIT AND NO CHAIN' );
    return;
  case 'ignore': // mostly for robots: ignore missing cookie support and try to create dummy session:
    try_public_access();
    break;
  case 'http':
  case 'url':
    handle_login();
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
// $language = ( ( getenv('english') == 1 ) ? 'E' : 'D' );
init_var( 'language', 'global,sources=http persistent,default=D,type=W1,pattern=/^[DE]$/,set_scopes=session' );
init_var( 'action', 'global,sources=http,default=nop,type=W256' );

$initialization_steps['session_ready'] = true;

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
} else {
  error( "invalid script: $script", LOG_FLAG_INPUT | LOG_FLAG_CODE, 'links' );
}
set_persistent_var( 'thread_atime', 'thread', $utc );
store_all_persistent_vars();

include( "$jlf_application_name/foot.php" );

sql_do( 'COMMIT AND NO CHAIN' );

?>
