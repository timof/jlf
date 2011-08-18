<?php
//
// code/basic.php: define general functions not fitting any other category
//

function isarray( $bla ) {
  return is_array( $bla );
}
function isstring( $bla ) {
  return is_string( $bla );
}
function isnumeric( $bla ) {
  return is_numeric( $bla );
}
function isnull( $bla ) {
  return is_null( $bla );
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
    if( ( $index === false ) || isnull( $index ) )
      continue;
    if( isarray( $index ) ) {
      $a = $array;
      foreach( $index as $i ) {
        if( ! isset( $a[ $i ] ) )
          continue 2;
        $a = $a[ $i ];
      }
      return $a;
    }
    if( isset( $array[ $index ] ) )
      return $array[ $index ];
  }
  return $default;
}

function gdefault( $names, $default = 0 ) {
  if( ! is_array( $names ) )
    $names = array( $names );
  foreach( $names as $name ) {
    if( isset( $GLOBALS[ $name ] ) )
      return $GLOBALS[ $name ];
  }
  return $default;
}


global $jlf_urandom_handle;
$jlf_urandom_handle = false;

function random_hex_string( $bytes ) {
  global $jlf_urandom_handle;
  if( ! $jlf_urandom_handle )
    need( $jlf_urandom_handle = fopen( '/dev/urandom', 'r' ), 'failed to open /dev/urandom' );
  $s = '';
  while( $bytes > 0 ) {
    $c = fgetc( $jlf_urandom_handle );
    need( $c !== false, 'read from /dev/urandom failed' );
    $s .= sprintf( '%02x', ord($c) );
    $bytes--;
  }
  return $s;
}

// tree_merge: recursively merge data structures:
// - numeric-indexed elements will be appended
// - string-indexed elements will be merged recursively, if they exist in both arrays
// - a non-null, non-array $b will replace $a
//
function tree_merge( $a = array(), $b = array() ) {
  if( ( ! is_array( $b ) ) && ( $b !== NULL ) ) {
    $a = $b;
  } else {
    foreach( $b as $key => $val ) {
      if( is_numeric( $key ) ) {
        $a[] = $val;
      } else {
        if( isset( $a[ $key ] ) && is_array( $a[ $key ] ) && is_array( $val ) )
          $a[ $key ] = tree_merge(  $a[ $key ], $val );
        else
          $a[ $key ] = $val;
      }
    }
  }
  return $a;
}

// parameters_explode():
// - convert string "k1=v1,k2=k2,..." into assoc array( 'k1' => 'v1', 'k2' => 'v2', ... )
// - flags with no assignment "f1,f2,..." will map to 1: array( 'f1' => 1, 'f2' => 1, ... )
//
function parameters_explode( $r, $default_key = false ) {
  $r_in = $r;
  if( isstring( $r ) ) {
    $pairs = explode( ',', $r );
    $r = array();
    foreach( $pairs as $pair ) {
      $v = explode( '=', $pair );
      if( adefault( $v, 0, '' ) === '' )
        continue;
      if( count( $v ) > 1 ) {
        $r[ $v[ 0 ] ] = $v[ 1 ];
      } else if( $default_key !== false ) {
        $r[ $default_key ] = $v[ 0 ];
        // prettydump( $v, 'default key' );
        // prettydump( $pair, 'pair' );
        // prettydump( $pairs, 'pairs' );
        // prettydump( $r_in, 'r_in' );
        if( trim( $pair ) === 'ubrik' )
          error( 'bla' );
      } else {
        $r[ $v[ 0 ] ] = 1;
      }
    }
  }
  return $r;
}

function parameters_implode( $a ) {
  $s = '';
  $comma = '';
  foreach( $a as $k => $v ) {
    $s .= "$comma$k=$v";
    $comma = ',';
  }
  return $s;
}

// parameters_merge:
// - tree_merge arbitrary number of arguments
// - "k1=v1,k2=v2,..." strings will be parameter_explode'd into assoc array before merge
function parameters_merge( /* varargs */ ) {
  $r = array();
  for( $i = 0; $i < func_num_args(); $i++ ) {
    if( ! ( $a = func_get_arg( $i ) ) )
      continue;
    $r = tree_merge( $r, parameters_explode( $a ) );
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
