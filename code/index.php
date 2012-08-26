<?php
//
// activate the following line to see very early errors:
//
// echo "format: html\n";

error_reporting( E_ALL );

require_once('code/common.php');

// cookie check must come early before any output is printed:
//
$cookie_support = check_cookie_support();
handle_login();

if( function_exists( 'init_session' ) ) {
  init_session( $login_sessions_id );
}

// start output now - the htmlDefuse filter will gobble everything up to the "format:"-line:
//
echo "\n\n  ERROR: if you see this line in browser, you need to configure htmlDefuse as ExtFilter for your apache server! \n\n";

if( $login_sessions_id ) {
  get_itan(); // pick new itans
  sanitize_http_input(); // needs login_session_id!
  // irreversibly commit new and invalidate submitted itans (if any) and start main transaction:
  sql_do( 'COMMIT AND CHAIN' );

  retrieve_all_persistent_vars();

  if( $show_debug_button ) {
    init_var( 'debug', 'global,type=u,sources=http window,default=0,set_scopes=window' ); // if set, debug will also be included in every url!
  } else {
    $debug = 0;
  }
  init_var( 'action', 'global,type=w,default=nop,sources=http' );
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
      break;
   case 'ignore':
     // cookie support is ignored for robots - usually they should get a dummy session and not pass here, though
     break;
   case 'ok':
      // everything fine but no session?
      header_view( 'html', 'access denied / kein Zugriff' );
      open_div( 'bigskips warn', 'access denied / kein Zugriff' );
      break;
   default:
      error( 'unexpected value for $cookie_support', LOG_FLAG_CODE, 'sessions,cookie' );
  }
} else {
  // not in html mode - cannot do much here:
  echo "request failed: no session\n";
}

sql_do( 'COMMIT AND NO CHAIN' );

?>
