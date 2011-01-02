<?php

function adefault( $array, $index, $default = 0 ) {
  if( is_array( $array ) && isset( $array[$index] ) )
    return $array[$index];
  else
    return $default;
}

function gdefault( $name, $default = 0 ) {
  return ( isset( $GLOBALS[$name] ) ? $GLOBALS[$name] : $default );
}

$jlf_defaults = array( 'u' => '0', 'h' => '',  'f' => '0.0', 'w' => '' );


global $jlf_urandom_handle;
$jlf_urandom_handle = false;

function random_hex_string( $bytes ) {
  global $jlf_urandom_handle;
  if( ! $jlf_urandom_handle )
    need( $jlf_urandom_handle = fopen( '/dev/urandom', 'r' ), 'konnte /dev/urandom nicht oeffnen' );
  $s = '';
  while( $bytes > 0 ) {
    $c = fgetc( $jlf_urandom_handle );
    need( $c !== false, 'Lesefehler von /dev/urandom' );
    $s .= sprintf( '%02x', ord($c) );
    $bytes--;
  }
  return $s;
}

function date_canonical2weird( $date_can ) {
  return substr( $date_can, 0, 4 ) .'-'. substr( $date_can, 4, 2 ) .'-'. substr( $date_can, 6, 2 );
}
function date_weird2canonical( $date_weird ) {
  return substr( $date_weird, 0, 4 ) . substr( $date_weird, 5, 2 ) . substr( $date_weird, 8, 2 );
}

?>
