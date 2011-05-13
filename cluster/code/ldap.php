<?php

global $ldap_handle;
$ldap_handle = false;

define( 'LDAP_URI', 'ldaps://ldap-master.qipc.org' );
define( 'LDAP_BINDDN', 'cn=admin quantenoptik,ou=people,ou=physik,o=uni-potsdam,c=de' );
define( 'LDAP_PWFILE', '/keys/admin.quantenoptik' );
define( 'LDAP_BASEDN', 'ou=physik,o=uni-potsdam,c=de' );

// define( 'LDAPTLS_CERT', '/keys/cert.subversion.qipc.org.pem' );
// define( 'LDAPTLS_KEY', '/keys/rsa.subversion.qipc.org.pem' );
// define( 'LDAPSASL_MECH', 'external' );
// define( 'LDAPSASL_SECPROPS', 'minssf=128' );


function init_ldap_handle() {
  global $ldap_handle;

  // echo "init_ldap_handle: 1<br>";

  if( $ldap_handle )
    return $ldap_handle;

  /// $handle = fopen( LDAP_PWFILE, 'r' );
  /// fscanf( $handle, '%s', & $ldap_pw );

  // echo "init_ldap_handle: (" . strlen( $ldap_pw ) . ")<br>";

  // putenv( 'LDAPTLS_CERT=' . LDAPTLS_CERT );
  // putenv( 'LDAPTLS_KEY=' . LDAPTLS_KEY );
  // putenv( 'LDAPSASL_MECH=' . LDAPSASL_MECH );
  // putenv( 'LDAPSASL_SECPROPS=' . LDAPSASL_SECPROPS );

  $h = ldap_connect( LDAP_URI );
  if( ! ldap_set_option( $h, LDAP_OPT_PROTOCOL_VERSION, 3 ) ) {
    $err = $ldap_errno;
    ldap_unbind( $h );
    error( "ldap_set_option failed: ". ldap_err2str($err) );
    return false;
  }

  // echo "init_ldap_handle: 2<br>";
  // echo "init_ldap_handle: " . getenv( 'LDAPTLS_CERT' ) . "<br>";
  // echo "init_ldap_handle: " . getenv( 'LDAPTLS_KEY' ) . "<br>";

  /// if( ! ldap_bind( $h, LDAP_BINDDN, $ldap_pw ) ) {
  if( ! ldap_bind( $h ) ) {
    $err = $ldap_errno;
    ldap_unbind( $h );
    error( "ldap_bind failed: " . ldap_err2str($err) );
    return false;
  }
  $ldap_handle = $h;
  register_shutdown_function( 'close_ldap_handle' );
  // echo "init_ldap_handle: 3<br>";
  return $ldap_handle;
}

function close_ldap_handle() {
  global $ldap_handle;
  if( $ldap_handle )
    ldap_unbind( $ldap_handle );
  $ldap_handle = false;
  return true;
}


function ldap_entry( $dn, $allow_null = false ) {
  global $ldap_handle;

  init_ldap_handle();
  $r = ldap_read( $ldap_handle, $dn, '(&)' );
  switch( ldap_count_entries( $ldap_handle, $r ) ) {
    case 0:
      need( $allow_null );
      return NULL;
    case 1:
      $r = ldap_get_entries( $ldap_handle, $r );
      return $r[0];
    default:
      error( 'ldap_read returned more than one entry' );
  }
}

function ldap_entries( $basedn, $keys ) {
  global $ldap_handle;

  if( is_array( $keys ) ) {
    $filter = '';
    foreach( $keys as $attr => $value ) {
      if( $value === '' ) {
        $filter .= "(!($attr=*))";
      } else {
        $filter .= "($attr=$value)";
      }
    }
    $filter = "(&$filter)";
  } else {
    $filter = $keys;
  }
  // echo "filter: [$filter]<br>";
  // echo "basedn: [$basedn]<br>";
  init_ldap_handle();
  $r = ldap_search( $ldap_handle, $basedn, $filter );
  return ldap_get_entries( $ldap_handle, $r );
}

///////////////////////////////
//
// user-functions
//
///////////////////////////////

function ldap_users( $keys = array() ) {
  $keys['objectclass'] = 'posixaccount';
  return ldap_entries( 'ou=people,' . LDAP_BASEDN, $keys );
}

function ldap_accountdomains() {
  static $accountdomains;
  if( ! isset( $accountdomains ) ) {
    $accountdomains = array();
    $keys['accountdomain'] = '*';
    $keys['objectclass'] = 'posixaccount';
    $users = ldap_entries( 'ou=people,' . LDAP_BASEDN, $keys );
    for( $n = 0; $n < $users['count'] ; $n++ ) {
      $user = $users[$n];
      if( isset( $user['accountdomain'] ) ) {
        for( $i = 0; $i < $user['accountdomain']['count'] ; $i++ ) {
          $a = $user['accountdomain'][$i];
          if( isset( $accountdomains[$a] ) ) {
            $accountdomains[$a]['users']++;
          } else {
            $accountdomains[$a] = array();
            $accountdomains[$a]['users'] = 1;
            $accountdomains[$a]['hosts'] = 0;
          }
        }
      }
    }
    $keys['objectclass'] = 'physikhost';
    $hosts = ldap_entries( 'ou=hosts,' . LDAP_BASEDN, $keys );
    for( $n = 0; $n < $hosts['count'] ; $n++ ) {
      $host = $hosts[$n];
      if( isset( $host['accountdomain'] ) ) {
        for( $i = 0; $i < $host['accountdomain']['count'] ; $i++ ) {
          $a = $host['accountdomain'][$i];
          if( isset( $accountdomains[$a] ) ) {
            $accountdomains[$a]['hosts']++;
          } else {
            $accountdomains[$a] = array();
            $accountdomains[$a]['users'] = 0;
            $accountdomains[$a]['hosts'] = 1;
          }
        }
      }
    }
  }
  return $accountdomains;
}

function ldap_accountdomains_host( $hostdn ) {
  $host = ldap_entry( $hostdn );
  $r = array();
  if( isset( $host['accountdomain'] ) ) {
    for( $i = 0; $i < $host['accountdomain']['count'] ; $i++ ) {
      $r[] = $host['accountdomain'][$i];
    }
  }
  return $r;
}

function options_accountdomains(
  $selected = 0
, $option_0 = false
) {
  $output='';
  if( $option_0 ) {
    $output = "<option value='0'";
    if( ! $selected ) {
      $output = $output . " selected";
      $selected = -1;
    }
    $output = $output . ">$option_0</option>";
  }
  foreach( ldap_accountdomains() as $name => $a ) {
    $output = "$output
      <option value='$name'";
    if( $selected == $name ) {
      $output = $output . " selected";
      $selected = -1;
    }
    $output = $output . "> $name </option>";
  }
  if( $selected >=0 ) {
    // $selected stand nicht zur Auswahl; vermeide zufaellige Anzeige:
    $output = "<option value='0' selected>(bitte accountdomain wählen)</option>" . $output;
  }
  return $output;
}


?>
