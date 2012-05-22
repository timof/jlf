<?php
//
// login.php
//
// login script:
//  - check, whether already logged in (via cookie)
//  -
//  - handle new login data and create session
//  - passing "login=logout" enforces logout (ie, removes cookie)
//
// in case of successful login, the following global variables will be set:
//  - $logged_in === true
//  - $login_people_id
//  - $login_authentication_method
//  - $login_uid
//  - $login_sessions_id
//  - $login_session_cookie
//  - $login_privs (optional; if not present in table 'people' it will be set to 0)
//
// if public access is allowed ("public" is one of $allowed_authentication_methods), then:
//  - $logged_in === false
//  - $login_authentication_method === 'public'
//  - $login_sessions_id, $login_session_cookie will be set (a session is created)
//  - $login_uid === ''
//  - $login_people_id === 0
//  - $login_privs === 0
//
// thus, scripts may
//  - check for $login_sessions_id, if public access is allowed
//  - check for $logged_in and possibly $login_authentication_method if authentication is required
//  - if $logged_in: optionally, check $login_uid, to get more fine-grained access control


// this is independend of actual login status - just used to check whether the client supports cookies at all:
//
$valid_cookie_received = false;

function init_login() {
  global $logged_in, $login_people_id, $login_authentication_method, $login_uid, $login_privs;
  global $login_sessions_id, $login_session_cookie;

  $logged_in = false;
  $login_people_id = 0;
  $login_authentication_method = 'none';
  $login_uid = '';
  $login_sessions_id = 0;
  $login_session_cookie = '';
  $login_privs = 0;
  return true;
}

function cookie_name() {
  return  "{$GLOBALS['jlf_application_name']}_{$GLOBALS['jlf_application_instance']}_keks";
}

function logout( $reason = 0 ) {
  global $login_sessions_id;

  if( $login_sessions_id ) {
    logger( "ending session [$login_sessions_id], reason [$reason]", 'logout' );
    sql_delete( 'persistent_vars', array( 'sessions_id' => $login_sessions_id ) );
  }
  init_login();
  unset( $_COOKIE[ cookie_name() ] );
  setcookie( cookie_name(), '0', 0, '/' );
}

// create_session(): complete a login procedure after authentication,
// which must have set $login_authentication_method and $login_people_id
//
function create_session( $people_id, $authentication_method ) {
  global $utc, $login, $login_privs;
  global $logged_in, $login_people_id, $login_sessions_id, $login_session_cookie;
  global $login_authentication_method, $login_uid;

  // debug( $people_id, 'create_session for:' );
  init_login();
  $login_people_id = $people_id;
  $login_authentication_method = $authentication_method;
  $login_session_cookie = random_hex_string( 6 );
  if( $people_id ) {
    $person = sql_person( $login_people_id );
    $login_uid = $person['uid'];
    $login_privs = adefault( $person, 'privs', 0 );
  } else {
    need( $authentication_method === 'public' );
    $login_uid = '';
  }
  $login_sessions_id = sql_insert( 'sessions', array( 
    'cookie' => $login_session_cookie
  , 'login_people_id' => $login_people_id
  , 'login_authentication_method' => $login_authentication_method
  , 'atime' => $utc
  , 'ctime' => $utc
  , 'login_remote_ip' => $_SERVER['REMOTE_ADDR']
  , 'login_remote_port' => $_SERVER['REMOTE_PORT']
  ) );
  $keks = $login_sessions_id.'_'.$login_session_cookie;
  need( setcookie( cookie_name(), $keks, 0, '/' ), "setcookie() failed" );
  $logged_in = ( $people_id ? true : false );
  logger( "successful login: client: {$_SERVER['HTTP_USER_AGENT']}, session: [$login_sessions_id]", 'login' );
  // print_on_exit( "[create_session(): method:$login_authentication_method, login_uid:$login_uid, login_sessions_id:$login_sessions_id]" );
  // discard $_POST:
  // - itan will be invalid in new session context
  // - 'login' in particular must be deleted after successful login (so we don't display the form again):
  $_POST = array();
  $login = '';
  // debug( $login_sessions_id, 'new login_sessions_id:' );
  return $login_sessions_id;
}

function try_public_access() {
  global $allowed_authentication_methods;

  $allowed = explode( ',', $allowed_authentication_methods );
  if( ! in_array( 'public', $allowed ) )
    return false;

  if( ! getenv('auth') === 'public' )
    return false;

  return ( create_session( 0, 'public' ) ? true : false );
}


// get_auth_ssl(): check for ssl auth data provided by server; if found, return people_id
//
function get_auth_ssl() {
  global $allowed_authentication_methods;

  $allowed = explode( ',', $allowed_authentication_methods );
  if( ! in_array( 'ssl', $allowed ) )
    return 0;
  $id = 0;
  if( ! getenv('auth') === 'ssl' )
    return 0;
  $uid = getenv( 'user' );
  if( ! preg_match( '/^[a-zA-Z0-9]+$/', $uid ) )
    return 0;
  $person = sql_person( array( 'uid' => $uid ), true );
  if( ! $person )
    return 0;
  $auth_methods = explode( ',', $person['authentication_methods'] );
  if( ! in_array( 'ssl', $auth_methods ) )
    return 0;
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
// - check whether we are logged in (valid session cookie)
// - 
function handle_login() {
  global $logged_in, $login_people_id, $login_privs, $password, $login, $login_sessions_id, $login_authentication_method, $login_uid;
  global $login_session_cookie, $problems, $info_messages, $utc;
  global $valid_cookie_received;

  init_login();

  // check for existing session:
  //
  // prettydump( $_COOKIE[ cookie_name() ] , 'cookie' );
  $cookie = '';
  if( isset( $_COOKIE[ cookie_name() ] ) ) {
    $cookie = $_COOKIE[ cookie_name() ];
  }
  if( $cookie === 'probe' ) {
    $valid_cookie_received = true;
  } else if( strlen( $cookie ) > 1 ) {
    sscanf( $cookie, "%u_%s", &$login_sessions_id, &$login_session_cookie );
    $row = sql_do_single_row( sql_query( 'SELECT', 'sessions', $login_sessions_id ), NULL );
    if( ! $row ) {
      $problems[] = 'sessions entry not found: not logged in';
    } elseif( $login_session_cookie != $row['cookie'] ) {
      $problems[] = 'cookie mismatch: not logged in';
    } else {
      $login_people_id = $row['login_people_id'];
      $login_authentication_method = $row['login_authentication_method'];
      $valid_cookie_received = true;
    }
    if( ! $problems ) {
      // session is still valid:
      if( $login_people_id ) {
        $person = sql_person( $login_people_id );
        $logged_in = true;
        $login_uid = $person['uid'];
        $login_privs = $person['privs'];
        switch( $login_authentication_method ) {
          case 'ssl':
            // for ssl client auth, session data should match ssl data:
            if( ! check_auth_ssl() ) {
              $problems[] = 'cookie / ssl auth mismatch';
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
    if( $problems ) {
      foreach( $problems as $p ) {
        logger( "problem: $p", 'login' );
      }
      logout( 1 );
    }
  }

  if( ! $valid_cookie_received ) {
    // no valid session yet and no cookie received - before attempting to create a session,
    // we send a probe to check whether cookie support is on at all:
    //
    setcookie( cookie_name(), 'probe', 0, '/' );
    return;
  }

  // check for new login data (this may replace the existing session, possibly upgrading login_authentication_method
  // from 'public' to 'simple':

  // ! we cannot yet use init_var() during login procedure (no session yet!) !
  //
  // $login = adefault( $_POST, 'login', '' );  // already set in index.php

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
      $problems = array( 'authentication failed / Anmeldung fehlgeschlagen' ); // $language not yet available!

      $people = 0;
      $people_id = adefault( $_POST, 'people_id', 'X' );
      if( preg_match( '/^\d{1,6}$/', $people_id ) ) {
        $people = sql_people( array(
            'people.people_id' => $people_id
          , 'people.authentication_methods ~=' => '[[:<:]]simple[[:>:]]'
        ) );
      } else {
        $uid = adefault( $_POST, 'uid', '' );
        if( preg_match( '/^[a-z0-9]{2,16}$/', $uid ) ) {
          $people = sql_people( array(
            'people.uid' => $uid
          , 'people.authentication_methods ~=' => '[[:<:]]simple[[:>:]]'
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

      $problems = array();
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

  // still not logged in - reset global login status:
  logout( 6 );

  return;
}

// check_cookie_support(): attempt to test whether client supports cookies; return value:
// - 'ignore': ignore cookie support (for robots)
// - 'ok':     client supports cookies
// - 'probe':  cannot decide; send cookie probe
// - 'fail':   no cookie support; issue warning
//
function check_cookie_support() {
  if( getenv( 'robot' ) )
    return 'ignore';
  $cookie = adefault( $_COOKIE, 'cookie_name', '' );
  if( strlen( $cookie ) > 2 ) {
    return 'ok';
  }
  if( $GLOBALS['login'] === 'cookie_probe' ) {
    return 'fail';
  }
  return 'probe';
}

  

?>
