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
//  - $logged_in == TRUE
//  - $login_people_id
//  - $login_authentication_method
//  - $login_uid
//  - $login_sessions_id


function init_login() {
  global $logged_in, $login_people_id, $login_sessions_id, $login_authentication_method, $login_uid;

  $logged_in = false;
  $login_people_id = 0;
  $login_authentication_method = 'none';
  $login_uid = 'nobody';
  $login_sessions_id = 0;
}

function cookie_name() {
  return  "{$GLOBALS['jlf_application_name']}_{$GLOBALS['jlf_application_instance']}_keks";
}

function logout() {
  init_login();
  unset( $_COOKIE[ cookie_name() ] );
  setcookie( cookie_name(), '0', 0, '/' );
}

// create_session(): complete a login procedure after authentication,
// which must have set $login_authentication_method and $login_people_id
//
function create_session() {
  global $logged_in, $login_people_id, $login_sessions_id, $login_authentication_method, $login_uid, $sessionvars;

  $cookie = random_hex_string( 5 );
  // if( $gruppe['admin'] ) {
  //   $admin = true;
  // }
  $login_uid = sql_do_single_field( "SELECT uid FROM people WHERE people_id=$login_people_id", 'uid' );
  $login_sessions_id = sql_insert( 'sessions', array( 
    'cookie' => $cookie
  , 'login_people_id' => $login_people_id
  , 'login_authentication_method' => $login_authentication_method
  ) );
  $keks = $login_sessions_id.'_'.$cookie;
  need( setcookie( cookie_name(), $keks, 0, '/' ), "setcookie() failed" );
  $logged_in = true;
  logger( "successful login: client: {$_SERVER['HTTP_USER_AGENT']}" );
  print_on_exit( "<!-- create_session(): method:$login_authentication_method, uid:$login_uid, id:$login_sessions_id -->" );
}

// get_auth_ssl(): try to find ssl auth data and set global variables
//
function get_auth_ssl() {
  global $allowed_authentication_methods;

  $allowed = explode( ',', $allowed_authentication_methods );
  if( ! in_array( 'ssl', $allowed ) )
    return 0;
  $id = 0;
  if( ! getenv('auth') == 'ssl' )
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

function check_auth_ssl() {
  global $logged_in, $login_authentication_method, $login_people_id;
  if( ! $logged_in )
    return false;
  if( $login_authentication_method != 'ssl' )
    return false;
  need( $login_people_id > 0 );
  return ( get_auth_ssl() == $login_people_id );
}

function login_auth_ssl() {
  global $login_authentication_method, $login_people_id;
  $id = get_auth_ssl();
  if( $id ) {
    $login_authentication_method = 'ssl';
    $login_people_id = $id;
    create_session();
  }
  return $id;
}

function do_login() {
  global $logged_in, $login_people_id, $password, $login, $login_sessions_id, $login_authentication_method, $login_uid;

  init_login();
  $problems = '';

  // check for existing session:
  //
  if( isset( $_COOKIE[cookie_name()] ) && ( strlen( $_COOKIE[cookie_name()] ) > 1 ) ) {
    sscanf( $_COOKIE[cookie_name()], "%u_%s", &$login_sessions_id, &$cookie );
    $row = sql_do_single_row( sql_query( 'SELECT', 'sessions', $login_sessions_id ), true );
    if( ! $row ) {
      $problems .= "<div class='warn'>not logged in</div>";
    } elseif( $cookie != $row['cookie'] ) {
      $problems .= "<div class='warn'>cookie mismatch: not logged in</div>";
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
    if( !problems )
      logout();
  }

  // check for new login data
  // ! we cannot yet use get_http_var() during login procedure (no session yet!) !
  //
  $login = adefault( $_POST, 'login', '' );

  switch( $login ) {
    case 'login': 
      logout();
      $p = adefault( $_GET, 'people_id', '0' );
      $p = adefault( $_POST, 'login_people_id', $p );
      sscanf( $p, '%u', & $login_people_id );
      ( $login_people_id > 0 ) or $problems .= "<div class='warn'>ERROR: no user selected</div>";
      $ticket = adefault( $_GET, 'ticket', false );  // special case: allow ticket-based login
      $password = adefault( $_POST, 'password', $ticket );
      if( ! $password )
        $problems .= "<div class='warn'>ERROR: missing password</div>";

      if( ! $problems ) {
        if( auth_check_password( $login_people_id, $password ) ) {
          $login_authentication_method = 'simple';
        } else {
          $problems .= "<div class='warn'>ERROR: wrong password</div>";
        }
      }

      if( $problems ) {
        logout();
      } else {
        create_session();
      }
      break;
    case 'logout':
      $problems .= "<div class='ok'>logged out!</div>";
    case 'silentlogout':
      // ggf. noch  dienstkontrollblatt-Eintrag aktualisieren:
      logout();
      break;
    case 'ssl':
      logout();
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
  logout();  // not correctly loggged in ... reset login status

  return $problems;
}


?>
