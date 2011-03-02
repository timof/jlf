<?php

function adefault( $array, $index, $default = 0 ) {
  if( ( ! $index ) && ( $index !== 0 ) ) // numeric 0 is the only legal non-true index
    return $default;
  if( is_array( $array ) && isset( $array[$index] ) )
    return $array[$index];
  else
    return $default;
}

function gdefault( $names, $default = 0 ) {
  if( ! is_array( $names ) )
    $names = array( $names );
  foreach( $names as $name ) {
    if( isset( $GLOBALS[$name] ) )
      return $GLOBALS[$name];
  }
  return $default;
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

function merge_array_tree( $a = array(), $b = array() ) {
  foreach( $a as $key => $val ) {
    if( isset( $b[ $key ] ) ) {
      if( is_array( $val ) && is_array( $b[ $key ] ) )
        $a[ $key ] = merge_array_tree(  $a[ $key ], $b[ $key ] );
      else
        $a[ $key ] = $b[ $key ];
    }
  }
  return $a;
}

function date_canonical2weird( $date_can ) {
  return substr( $date_can, 0, 4 ) .'-'. substr( $date_can, 4, 2 ) .'-'. substr( $date_can, 6, 2 );
}
function date_weird2canonical( $date_weird ) {
  $d = substr( $date_weird, 0, 4 ) . substr( $date_weird, 5, 2 ) . substr( $date_weird, 8, 2 );
  return $d;
}

?>
