<?php

// define( 'LDAP_URI', 'ldaps://ldap-master.qipc.org' );
// define( 'LDAP_BINDDN', 'cn=admin quantenoptik,ou=people,ou=physik,o=uni-potsdam,c=de' );
// define( 'LDAP_PWFILE', '/keys/admin.quantenoptik' );
// define( 'LDAP_BASEDN', 'ou=physik,o=uni-potsdam,c=de' );

// set these environment variables to use ssl client auth:
//
// define( 'LDAPTLS_CERT', '/keys/cert.subversion.qipc.org.pem' );
// define( 'LDAPTLS_KEY', '/keys/rsa.subversion.qipc.org.pem' );
// define( 'LDAPSASL_MECH', 'external' );
// define( 'LDAPSASL_SECPROPS', 'minssf=128' );


global $ldap_handle;
$ldap_handle = false;

function init_ldap_handle() {
  // the following should be from leitvariable.php or from table `leitvariable':
  //
  global $ldap_handle, $ldap_url, $ldap_binddn, $ldap_password, $ldap_basedn;

  // echo "init_ldap_handle: 1<br>";

  if( $ldap_handle )
    return $ldap_handle;

  if( ! $ldap_url )
    return false;

  $h = ldap_connect( LDAP_URI );
  if( ! ldap_set_option( $h, LDAP_OPT_PROTOCOL_VERSION, 3 ) ) {
    $err = $ldap_errno;
    ldap_unbind( $h );
    error( "ldap_set_option failed: ". ldap_err2str($err) );
    return false;
  }

  if( $ldap_binddn ) {
    $ok = ldap_bind( $h, $ldap_bind_dn, $ldap_password );
  } else {
    $ok = ldap_bind( $h );
  }
  if( ! $ok ) {
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


?>
