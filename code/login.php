<?php
//
// login.php
//
// login script:
//  - check, whether already logged in (via cookie)
//  - handle new login data and create session
//  - passing "login=logout" enforces logout (ie, removes cookie)
//
// in case of successful login, the following global variables will be set:
//  - $logged_in === true
//  - $login_people_id
//  - $login_authentication_method
//  - $login_uid
//  - $login_privs (optional; if not present in table 'people' it will be set to 0)
//  - $login_sessions_id
//  - $cookie_sessions_id (not always same as $login_sessions_id: may initially contain unverified input from client!)
//  - $cookie_signature
//  - $cookie
//
// if public access is allowed ("public" is one of $allowed_authentication_methods), then:
//  - $logged_in === false
//  - $login_authentication_method === 'public'
//  - $login_sessions_id, $cookie_session_cookie will be set (a session is created)
//  - $cookie, $cookie_signature are valid
//  - $login_uid === ''
//  - $login_people_id === 0
//  - $login_privs === 0
//
// if no session at all is to be used (mainly: for robots, or strictly public pages): same as above, but:
//  - $cookie = $cookie_type = ''
//
// thus, scripts may
//  - check for $login_sessions_id, if public access is allowed
//  - check for $logged_in and possibly $login_authentication_method if authentication is required
//  - if $logged_in: optionally, check $login_uid, to get more fine-grained access control


// init_login(): initialize everything but cookie data which may contain client input still to be parsed:
//
function init_login() {
  global $logged_in, $login_people_id, $login_authentication_method, $login_uid, $login_privs;
  global $login_sessions_id, $cookie_sessions_id, $cookie_signaturelogin_session_cookie;

  $logged_in = false;
  $login_people_id = 0;
  $login_authentication_method = 'none';
  $login_uid = '';
  $login_sessions_id = 0;
  $login_privs = 0;
  return true;
}

// logout(): reset _all_ login data including the cookie:
//
function logout( $reason = 0 ) {
  global $login_sessions_id, $cookie, $cookie_sessions_id, $cookie_signature;

  if( $login_sessions_id ) {
    logger( "ending session [$login_sessions_id], reason [$reason]", LOG_LEVEL_INFO, LOG_FLAG_AUTH, 'logout' );
    sql_delete( 'persistentvars', array( 'sessions_id' => $login_sessions_id ) );
  }
  init_login();
  $cookie = '0_0';
  $cookie_sessions_id = 0;
  $cookie_signature = '';
  setcookie( COOKIE_NAME, $cookie, 0, '/' );
}

// create_session(): complete a login procedure after authentication,
//
function create_session( $people_id, $authentication_method ) {
  global $utc, $login, $login_privs;
  global $logged_in, $login_people_id, $login_sessions_id;
  global $login_authentication_method, $login_uid;
  global $cookie, $cookie_sessions_id, $cookie_signature;
  global $jlf_application_name, $jlf_application_instance;

  // debug( $people_id, 'create_session for:' );
  init_login();
  $login_people_id = $people_id;
  $login_authentication_method = $authentication_method;
  $cookie_signature = random_hex_string( 6 );
  if( $people_id ) {
    $person = sql_person( $login_people_id );
    $login_uid = $person['uid'];
    $login_privs = adefault( $person, 'privs', 0 );
    $logged_in = true;
  } else {
    need( $authentication_method === 'public' );
  }
  $login_sessions_id = sql_insert( 'sessions', array( 
    'cookie_signature' => $cookie_signature
  , 'login_people_id' => $login_people_id
  , 'login_authentication_method' => $login_authentication_method
  , 'atime' => $utc
  , 'ctime' => $utc
  , 'login_remote_ip' => $_SERVER['REMOTE_ADDR']
  , 'login_remote_port' => $_SERVER['REMOTE_PORT']
  , 'application' => "$jlf_application_name-$jlf_application_instance"
  ) );
  $cookie_sessions_id = $login_sessions_id;
  $cookie = $cookie_sessions_id.'_'.$cookie_signature;
  need( setcookie( COOKIE_NAME, $cookie, 0, '/' ), "setcookie() failed" );
  logger( "session [$login_sessions_id] created for client: {$_SERVER['HTTP_USER_AGENT']}", LOG_LEVEL_INFO, LOG_FLAG_AUTH, 'login' );
  // print_on_exit( "[create_session(): method:$login_authentication_method, login_uid:$login_uid, login_sessions_id:$login_sessions_id]" );
  // discard $_POST (http input will _not_ yet be sanitized at this point, so $_POST is not yet merged into $_GET)
  // - itan will be invalid in new session context
  // - 'login' in particular must be deleted after successful login (so we don't display the form again):
  $_POST = array();
  $login = '';
  // debug( $login_sessions_id, 'new login_sessions_id:' );
  return $login_sessions_id;
}

// create dummy session - create session always recycling same dummy entry in sessions table
// (mostly for robots, who don't support cookies and thus cannot get actual session)
//
function create_dummy_session() {
  global $utc, $login_authentication_method, $login_sessions_id, $login, $cookie_type, $cookie, $cookie_signature;
  global $jlf_application_name, $jlf_application_instance;

  init_login();
  $login_authentication_method = 'public';
  $sessions = sql_sessions( 'cookie_signature=NOCOOKIE', NULL );
  if( $sessions ) {
    $session = $sessions[ 0 ];
    $login_sessions_id = $session['sessions_id'];
  } else {
    $login_sessions_id = sql_insert( 'sessions', array(
      'cookie' => 'NOCOOKIE'
    , 'login_people_id' => 0
    , 'login_authentication_method' => 'public'
    , 'atime' => $utc
    , 'ctime' => '19990101.000000' // fake canary date
    , 'login_remote_ip' => '0.0.0.0'
    , 'login_remote_port' => '0'
    , 'application' => "$jlf_application_name-$jlf_application_instance"
    ) );
    logger( "dummy session inserted: [$login_sessions_id]", LOG_LEVEL_DEBUG, LOG_FLAG_SYSTEM | LOG_FLAG_AUTH, 'login' );
  }
  $cookie_type = $cookie_signature = $cookie = '';
  logger( "using dummy session [$login_sessions_id] for client: {$_SERVER['HTTP_USER_AGENT']}", LOG_LEVEL_INFO, LOG_FLAG_AUTH, 'login' );
  $_POST = array(); // no POST from robots!
  $login = '';
  return $login_sessions_id;
}


function try_public_access() {
  global $allowed_authentication_methods, $cookie_type;

  $allowed = explode( ',', $allowed_authentication_methods );
  if( ! in_array( 'public', $allowed ) )
    return false;

  if( $cookie_type ) {
    return ( create_session( 0, 'public' ) ? true : false );
  } else {
    return ( create_dummy_session() ? true : false );
  }
}


// get_auth_ssl(): check for ssl auth data provided by server; if found, return people_id
//
function get_auth_ssl() {
  global $allowed_authentication_methods;

  $allowed = explode( ',', $allowed_authentication_methods );
  if( ! in_array( 'ssl', $allowed ) ) {
    return 0;
  }
  $id = 0;
  if( ! ( adefault( $_ENV, 'auth', '' ) === 'ssl' ) ) {
    return 0;
  }
  $uid = $_ENV['user'];
  if( ! preg_match( '/^[a-zA-Z0-9]+$/', $uid ) ) {
    return 0;
  }
  $person = sql_person( array( 'uid' => $uid ), NULL );
  if( ! $person ) {
    return 0;
  }
  if( ! $person['authentication_method_ssl'] ) {
    return 0;
  }
  return $person['people_id'];
}

// check_auth_ssl(): check whether this is a valid ssl-authenticated session:
//
function check_auth_ssl() {
  global $logged_in, $login_authentication_method, $login_people_id;
  if( ! $logged_in )
    return false;
  if( $login_authentication_method !== 'ssl' )
    return false;
  need( $login_people_id > 0 );
  return ( get_auth_ssl() === $login_people_id );
}

// login_auth_ssl(): try to login via ssl client authentication:
//
function login_auth_ssl() {
  if( ( $id = get_auth_ssl() ) ) {
    create_session( $id, 'ssl' );
  }
  return $id;
}

// handle_login():
// - check whether we are already logged in (valid session cookie)
// - handle explicit requests (ssl, (simple) login, logout, ...)
// - last resort: try ssl (client cert) and public authentication if available
//
function handle_login() {
  global $logged_in, $login_people_id, $login_privs, $password, $login, $login_sessions_id, $login_authentication_method, $login_uid;
  global $login_session_cookie, $error_messages, $info_messages, $utc;
  global $cookie_type, $cookie_sessions_id, $cookie_signature;
  global $jlf_application_name, $jlf_application_instance;

  init_login();

  need( ! $error_messages, implode( ' , ', $error_messages ) );

  // check for existing session:
  //
  if( $cookie_type && ( $cookie_sessions_id > 0 ) ) {
    // $row = sql_query( 'sessions', "$cookie_sessions_id,single_row=1,default=" );
    $row = sql_one_session( "sessions_id=$cookie_sessions_id,application=$jlf_application_name-$jlf_application_instance", 'single_row=1,default=0' );
    if( ! $row ) {
      $error_messages[] = 'sessions entry not found: not logged in';
    } elseif( $cookie_signature != $row['cookie_signature'] ) {
      $error_messages[] = 'cookie mismatch: not logged in';
    } elseif( $row['expired'] ) {
      $error_messages[] = 'session expired';
    } elseif( $row['login_people_id'] && ! $row['people_people_id'] ) { // not public access, but person deleted?
      $error_messages[] = 'session invalid';
    } else {
      $login_people_id = $row['login_people_id'];
      $login_authentication_method = $row['login_authentication_method'];
    }
    if( ! $error_messages ) {
      // session is still valid:
      $login_sessions_id = $cookie_sessions_id;
      if( $login_people_id ) {
        $person = sql_person( $login_people_id );
        $logged_in = true;
        $login_uid = $person['uid'];
        $login_privs = adefault( $person, 'privs', 0 );
        switch( $login_authentication_method ) {
          case 'ssl':
            // for ssl client auth, session data should match ssl data:
            if( ! check_auth_ssl() ) {
              $error_messages[] = 'cookie / ssl auth mismatch';
            }
        }
      } else {
        need( $login_authentication_method === 'public' );
        $login_uid = false;
        $login_privs = 0;
        $logged_in = false;
      }
      sql_update( 'sessions', $login_sessions_id, "atime=$utc" );
    }
    if( $error_messages ) {
      foreach( $error_messages as $p ) {
        logger( "problem: $p", LOG_LEVEL_ERROR, LOG_FLAG_AUTH, 'login' );
      }
      logout( 1 );
    }
  }

  // check for new login data (mostly to handle simple logins):
  //
  switch( $login ) {
    case 'login': 
      $password = adefault( $_POST, 'password', '' );
      if( ! $password ) {
        // probably we just display the empty form this time, so don't attempt authentication now
        break;
      }

      // debug( $password, 'password' );

      // we have a password, so we attempt simple authentication. default is failure:
      //
      $error_messages[] = 'authentication failed / Anmeldung fehlgeschlagen'; // $language not yet available!

      $people = 0;
      $people_id = adefault( $_POST, 'people_id', 'X' );
      if( preg_match( '/^\d{1,6}$/', $people_id ) ) {
        $people = sql_people( array(
            'people.people_id' => $people_id
          , 'authentication_method_simple' => '1'
        ) );
      } else {
        $uid = adefault( $_POST, 'uid', '' );
        if( preg_match( '/^[a-z0-9]{2,16}$/', $uid ) ) {
          $people = sql_people( array(
            'people.uid' => $uid
          , 'authentication_method_simple' => '1'
          ) );
        }
      }

      if( isarray( $people ) && ( count( $people ) == 1 ) ) {
        $people_id = $people[ 0 ]['people_id'];
      } else {
        break;
      }

      /// $ticket = adefault( $_GET, 'ticket', false );  // special case: allow ticket-based login

      if( ! auth_check_password( $people_id, $password ) ) {
        break;
      }

      $error_messages = array();
      create_session( $people_id, 'simple' );
      break;

    case 'logout':
      // debug( $login, 'login' );
      $info_messages[] = 'logged out!';

    case 'silentlogout':
      logout( 4 );
      break;

    case 'ssl':
      logout( 5 );
      login_auth_ssl();
      break;

    case 'public':
      logout( 5 );
      try_public_access();
      break;

    case 'nop':
    default:
      break;
  }

  if( $login_sessions_id )
    return;

  // not yet logged in - try ssl client certs:
  //
  login_auth_ssl();
  if( $login_sessions_id )
    return;

  // no session yet - see whether we are supposed and allowed to use public access:
  //
  try_public_access();
  if( $login_sessions_id )
    return;

  // still not logged in - reset global login status and discard any cookies:
  //
  logout( 6 );

  return;
}


// check_cookie_support(): attempt to test whether client supports cookies; return value:
// - 'http':   client supports http cookies
// - 'url':    use url cookies
// - 'ignore': ignore cookie support (for robots)
// - 'probe':  cannot decide yet; send cookie probe
// - 'fail':   no cookie support; issue warning
//
function check_cookie_support() {
  global $cookie, $cookie_type, $cookie_sessions_id, $cookie_signature, $allow_url_cookies;
  if( adefault( $_ENV, 'robot', 0 ) ) {
    // no cookies for robots - reset any and ignore:
    $cookie = $cookie_signature = $cookie_type = '';
    $cookie_sessions_id = 0;
    return 'ignore';
  }
  if( $cookie_type === 'http' ) { // real browser cookies available - great
    return 'http';
  }
  if( $allow_url_cookies ) {
    if( $cookie_type !== 'url' ) {
      // url cookies should always be a safe fallback - create dummy cookie and use it:
      $cookie_sessions_id = 0;
      $cookie_signature = '0';
      $cookie = '0_0';
      setcookie( COOKIE_NAME, '0_0', 0, '/' ); // just try it, maybe it will work
    }
    return ( $cookie_type = 'url' );
  }
  $cookie = $cookie_signature = $cookie_type = '';
  $cookie_sessions_id = 0;
  if( $GLOBALS['login'] === 'cookie_probe' ) {
    logger( "cookie probe failed", LOG_LEVEL_WARNING, LOG_FLAG_SYSTEM, 'cookie' );
    return 'fail';
  }
  // try to set dummy cookie and suggest to send out as probe:
  setcookie( COOKIE_NAME, '0_0', 0, '/' );
  return 'probe';
}

// send out cookie probe. has to be done very low-level way, we don't have a full session available.
// this function will try and send a true browser cookie, as well as a 'url-cookie' which should never
// ever fail and serve as a fallback mode
//
function send_cookie_probe() {
  global $H_SQ, $debug;
  $debug = false;

  // include a url cookie probe, too:
  //
  $linkfields = inlink( 'menu', 'context=form,form_id=update_form,c=0_0' );
  echo html_tag( 'form', array(
    'action' => $linkfields['action']
    , 'id' => 'update_form'
    , 'method' => 'post'
  ) );
  echo html_tag( 'input', 'type=hidden,name=l,value=cookie_probe', '' );
  echo html_tag( 'form', false );
  echo html_tag( 'script'
  , 'type=text/javascript'
  , "\n /* alert( {$H_SQ}sending cookie probe{$H_SQ} ); */ document.forms.update_form.submit(); \n" 
  , 'nodebug'
  );
  logger( "sending cookie probe", LOG_LEVEL_INFO, LOG_FLAG_SYSTEM, 'cookie' );
}

?>
