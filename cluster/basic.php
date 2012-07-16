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
  return preg_replace( '/\\.0*/', '.', $ip4 );
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

?>
