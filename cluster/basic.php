<?php

////////////////////////////////////
//
// oid and IP handling:
//
////////////////////////////////////

define( 'OID_MAX_PARTS', 21 );
define( 'OID_ZERO_PADDING', '0000000000' );
define( 'OID_MAX_DIGITS', strlen( OID_ZERO_PADDING ) );

function oid_canonical2traditional( $oid ) {
  return preg_replace( '/\\.0*/', '.', preg_replace( '/^0*/', '', $oid ) );
}

function oid_traditional2canonical( $oid ) {
  $parts = explode( '.', $oid );
  need( count( $parts ) <= OID_MAX_PARTS, 'too many parts' );
  $dot = '';
  $r = '';
  foreach( $parts as $p ) {
    need( strlen( $p ) <= OID_MAX_DIGITS, 'component too large' );
    $r .= $dot . substr( OID_ZERO_PADDING.$p, -OID_MAX_DIGITS );
    $dot = '.';
  }
  return $r;
}

function ip4_canonical2traditional( $ip4 ) {
  return preg_replace( '/\\.0*/', '.', preg_replace( '/^0*/', '', $ip4 ) );
}

function ip4_traditional2canonical( $ip4 ) {
  $parts = explode( '.', $ip4 );
  $dot = '';
  $r = '';
  for( $i = 0; $i < 4; ++$i ) {
    $r .= $dot . substr( '00'.adefault( $parts, $i, '255' ), -3 );
    $dot = '.';
  }
  return $r;
}


function have_minimum_person_priv( $priv, $people_id = 0 ) {
  if( $people_id ) {
    $person = sql_person( $people_id /* , no automatic authorization here! */ );
    $p = $person['privs'];
  } else {
    $p = $GLOBALS['login_privs'];
  }
  return ( $p >= $priv );
}

define( 'PERSON_PRIV_USER', 0x01 );
define( 'PERSON_PRIV_ADMIN', 0x04 );

// for the time being: minimlistic privilige system: admin and nobody
//
function have_priv( $section, $action, $item = 0 ) {
  if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
    return true;
  }
  return false;
}
function need_priv( $section, $action, $item = 0 ) {
  if( ! have_priv( $section, $action, $item ) ) {
    error( we('insufficient privileges','keine Berechtigung'), LOG_FLAG_AUTH | LOG_FLAG_USER, 'privs' );
  }
}
function restrict_view_filters( $filters, $section ) {
  return $filters;
}

?>
