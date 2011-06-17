<?php
//
// login.php
//
// anmeldescript:
//  - prueft, ob benutzer schon angemeldet (per cookie)
//  - verarbeitet neuanmeldungen
//  - per "login=logout" wird ein logout (loeschen des cookie) erzwungen
//  - falls nicht angemeldet: anmeldeformular wird ausgegeben
//
// bei erfolgreicher anmeldung werden global gesetzt:
//  - $logged_in === true
//  - $login_people_id
//  - $login_authentication_method
//  - $login_uid
//  - $login_sessions_id
//  - $login_session_cookie


function init_login() {
  global $logged_in, $login_people_id, $login_sessions_id, $login_authentication_method, $login_uid;
  global $login_session_cookie;

  $logged_in = false;
  $login_people_id = 0;
  $login_authentication_method = 'none';
  $login_uid = 'nobody';
  $login_sessions_id = 0;
  $login_session_cookie = '';
}

function cookie_name() {
  return  "{$GLOBALS['jlf_application_name']}_{$GLOBALS['jlf_application_instance']}_keks";
}

function logout( $reason = 0 ) {
  global $login_sessions_id;

  if( $login_sessions_id ) {
    logger( "ending session [$login_sessions_id], reason [$reason]", 'logout' );
    sql_delete( 'sessionvars', array( 'sessions_id' => $login_sessions_id ) );
  }
  init_login();
  unset( $_COOKIE[ cookie_name() ] );
  setcookie( cookie_name(), '0', 0, '/' );
}

// create_session(): complete a login procedure after authentication,
// which must have set $login_authentication_method and $login_people_id
//
function create_session( $people_id, $authentication_method ) {
  global $logged_in, $login_people_id, $login_sessions_id, $login_session_cookie;
  global $login_authentication_method, $login_uid, $sessionvars;

  init_login();
  $login_people_id = $people_id;
  $login_authentication_method = $authentication_method;
  $login_session_cookie = random_hex_string( 6 );
  // if( $gruppe['admin'] ) {
  //   $admin = true;
  // }
  $login_uid = sql_do_single_field( "SELECT uid FROM people WHERE people_id=$login_people_id", 'uid' );
  $login_sessions_id = sql_insert( 'sessions', array( 
    'cookie' => $login_session_cookie
  , 'login_people_id' => $login_people_id
  , 'login_authentication_method' => $login_authentication_method
  ) );
  $keks = $login_sessions_id.'_'.$login_session_cookie;
  need( setcookie( cookie_name(), $keks, 0, '/' ), "setcookie() failed" );
  $logged_in = true;
  logger( "successful login: client: {$_SERVER['HTTP_USER_AGENT']}, session: [$login_sessions_id]", 'login' );
  print_on_exit( "<!-- create_session(): method:$login_authentication_method, login_uid:$login_uid, login_sessions_id:$login_sessions_id -->" );
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
  global $logged_in, $login_people_id, $password, $login, $login_sessions_id, $login_authentication_method, $login_uid;
  global $login_session_cookie;

  init_login();
  $problems = '';

  // check for existing session:
  //
  // prettydump( $_COOKIE[ cookie_name() ] , 'cookie' );
  if( isset( $_COOKIE[cookie_name()] ) && ( strlen( $_COOKIE[cookie_name()] ) > 1 ) ) {
    sscanf( $_COOKIE[cookie_name()], "%u_%s", &$login_sessions_id, &$login_session_cookie );
    $row = sql_do_single_row( sql_query( 'SELECT', 'sessions', $login_sessions_id ), true );
    if( ! $row ) {
      $problems = "sessions entry not found: not logged in";
    } elseif( $login_session_cookie != $row['cookie'] ) {
      $problems = "cookie mismatch: not logged in";
    } else {
      $login_people_id = $row['login_people_id'];
      $login_authentication_method = $row['login_authentication_method'];
    }
    if( ! $problems ) {
      // session is still valid:
      $logged_in = true;
      $login_uid = sql_do_single_field( "SELECT uid FROM people WHERE people_id=$login_people_id", 'uid' );
      switch( $login_authentication_method ) {
        case 'ssl':
          // for ssl client auth, session data should match ssl data:
          if( ! check_auth_ssl() ) {
            $problems .= "<div class='warn'>cookie / ssl auth mismatch</div>";
          }
      }
    }
    if( $problems ) {
      logger( "problem: $problems", 'login' );
      logout( 1 );
    }
  } else {
    // prettydump( 'no cookie received - make sure to allow cookies for this site' );
  }

  // check for new login data
  // ! we cannot yet use get_http_var() during login procedure (no session yet!) !
  //
  $login = adefault( $_POST, 'login', '' );

  // prettydump( $login, 'login:' );
  switch( $login ) {
    case 'login': 
      logout( 2 );
      $p = adefault( $_GET, 'people_id', '0' );
      $p = adefault( $_POST, 'login_people_id', $p );
      sscanf( $p, '%u', & $people_id );
      ( $people_id > 0 ) or $problems .= "<div class='warn'>ERROR: no user selected</div>";
      $ticket = adefault( $_GET, 'ticket', false );  // special case: allow ticket-based login
      $password = adefault( $_POST, 'password', $ticket );
      if( ! $password )
        $problems .= "<div class='warn'>ERROR: missing password</div>";

      if( ! $problems ) {
        if( ! auth_check_password( $people_id, $password ) ) {
          $problems .= "<div class='warn'>ERROR: wrong password</div>";
        }
      }

      if( $problems ) {
        logout( 3 );
      } else {
        create_session( $people_id, 'simple' );
      }
      break;

    case 'logout':
      $problems .= "<div class='ok'>logged out!</div>";
    case 'silentlogout':
      // ggf. noch  dienstkontrollblatt-Eintrag aktualisieren:
      logout( 4 );
      break;

    case 'ssl':
      logout( 5 );
      login_auth_ssl();
      break;
    default:
      break;
  }

  if( $logged_in )
    return;

  // not yet logged in - try ssl client certs:
  //
  login_auth_ssl();
  if( $logged_in )
    return;

  // still not logged in - reset global login status:
  logout( 6 );

  return $problems;
}


?>
