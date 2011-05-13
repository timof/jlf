<?php

function isarray( $bla ) {
  return is_array( $bla );
}
function isstring( $bla ) {
  return is_string( $bla );
}
function isnumeric( $bla ) {
  return is_numeric( $bla );
}
// would be nice but can't work:
// function is_set( $bla ) {
//   return isset( $bla );
//}


function adefault( $array, $indices, $default = 0 ) {
  if( ! is_array( $array ) )
    return $default;
  if( ! is_array( $indices ) )
    $indices = array( $indices );
  foreach( $indices as $index ) {
    // if( ( ! $index ) && ( $index !== 0 ) && ( $index !== '0' ) && ( $index !== '' ) )
    if( $index === false )
      continue;
    if( isset( $array[ $index ] ) )
      return $array[ $index ];
  }
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

function tree_merge( $a = array(), $b = array() ) {
  if( ( ! is_array( $b ) ) && ( $b !== NULL ) ) {
    $a = $b;
  } else {
    foreach( $b as $key => $val ) {
      if( isset( $a[ $key ] ) && is_array( $a[ $key ] ) && is_array( $val ) )
        $a[ $key ] = tree_merge(  $a[ $key ], $val );
      else
        $a[ $key ] = $val;
    }
  }
  return $a;
}

function parameters_merge( /* varargs */ ) {
  $r = array();
  for( $i = 0; $i < func_num_args(); $i++ ) {
    $a = func_get_arg($i);
    if( is_string( $a ) )
      $a = parameters_explode( $a );
    $r = tree_merge( $r, $a );
  }
  return $r;
}

function date_canonical2weird( $date_can ) {
  return substr( $date_can, 0, 4 ) .'-'. substr( $date_can, 4, 2 ) .'-'. substr( $date_can, 6, 2 );
}
function date_weird2canonical( $date_weird ) {
  $d = substr( $date_weird, 0, 4 ) . substr( $date_weird, 5, 2 ) . substr( $date_weird, 8, 2 );
  return $d;
}

?>
