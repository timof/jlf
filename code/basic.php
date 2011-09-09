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

function mdefault( $list, $default = 0 ) {
  foreach( $list as $keys => $a ) {
    if( isstring( $keys ) )
      $keys = explode( ',', $keys );
    foreach( $keys as $i ) {
      if( ! isset( $a[ $i ] ) )
        continue 2;
      $a = $a[ $i ];
    }
    return $a;
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



function random_hex_string( $bytes ) {
  static $urandom_handle;
  if( ! isset( $urandom_handle ) )
    need( $urandom_handle = fopen( '/dev/urandom', 'r' ), 'failed to open /dev/urandom' );
  $s = '';
  while( $bytes > 0 ) {
    $c = fgetc( $urandom_handle );
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
// options:
// - 'default_value': map flags to this value instead of 1
// - 'default_key': use flags with no assignment as value to this key, rather than as a key
// - 'default_null': flag: use NULL as default value 
// - 'keep': comma-separated list of parameter names or name=default pairs:
//     * parameters not in this list will be discarded
//     * parameters with default value are guaranteed to be set
function parameters_explode( $r, $opts = array() ) {
  if( is_string( $opts ) ) {
    $opts = parameters_explode( $opts, array( 'default_key' => 'default_key' ) );
  }
  $default_key = ( isset( $opts['default_key'] ) ? $opts['default_key'] : '' ); // allow to omit name of most common option
  if( isset( $opts['default_null'] ) ) {
    $default_value = NULL;
  } else {
    $default_value = ( isset( $opts['default_value'] ) ? $opts['default_value'] : 1  ); // default value (often: 1 for boolean options)
  }
  $keep = ( isset( $opts['keep'] ) ? $opts['keep'] : true );
  if( $keep !== true ) {
    $keep = parameters_explode( $keep, array( 'default_null' => true ) );
    // debug( $keep, 'keep' );
  }
  if( ! $r ) {
    $r = array();
  } else if( is_string( $r ) || is_numeric( $r ) ) {
    $pairs = explode( ',', "$r" );
    $r = array();
    foreach( $pairs as $pair ) {
      $v = explode( '=', $pair );
      if( ( ! isset( $v[ 0 ] ) ) || ( $v[ 0 ] === '' ) )
        continue;
      if( count( $v ) > 1 ) {
        $r[ $v[ 0 ] ] = $v[ 1 ];
      } else if( $default_key ) {
        $r[ $default_key ] = $v[ 0 ];
      } else {
        $r[ $v[ 0 ] ] = $default_value;
      }
    }
  } else {
    need( is_array( $r ) );
    foreach( $r as $key => $val ) {
      if( isnumeric( $key ) ) {
        $r[ $val ] = $default_value;
        unset( $r[ $key ] );
      }
    }
  }
  if( $keep === true ) {
    return $r;
  }
  $r2 = array();
  foreach( $keep as $key => $val ) {
    if( isset( $r[ $key ] ) ) {
      $r2[ $key ] = $r[ $key ];
    } else if( $val !== NULL ) {
      $r2[ $key ] = $val;
    }
  }
  return $r2;
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

// l2a(): turn list (array with numeric keys) into assoc array; also works for mixed input arrays
//
function l2a( $a, $default = array() ) {
  $r = array();
  foreach( $a as $key => $val ) {
    if( isnumeric( $key ) ) {
      need( isstring( $val ) );
      $r[ $val ] = $default;
    } else {
      $r[ $key ] = $val;
    }
  }
  return $r;
}

function prepare_filter_opts( $opts_in, $opts = array() ) {
  $r = parameters_explode( $opts_in, array( 'keep' => 'filters=,choice_0= (all) ' ) );
  $choice_0 = $r['choice_0'];
  unset( $r['choice_0'] );
  if( $choice_0 ) {
    $r['more_choices'] = array( 0 => $choice_0 );
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


// check_utf8(): verify input is correct utf8 data:
// additionally, the non-printable ASCII characters (0...31) will be rejected except
// for "\n" == "\0x0a" (linefeed), "\r" === "\0x0d" (carriage return) and "\t" === "\0x09:" (tab)
//
function check_utf8( $str ) {
  $len = strlen( $str );
  $i = 0;
  while( $i < $len ) {
    $c = ord( $str[ $i ] );
    if( $c < 128 ) {
      // disallow most control characters:
      if( $c < 32 ) {
        switch( $c ) {
          case  9: // tab
          case 10: // lf 
          case 13: // cr
            break;
          default:
            return false;
        }
      }
    } else {
      if( $c > 247 ) return false;
      elseif( $c > 239 ) $bytes = 4;
      elseif( $c > 223 ) $bytes = 3;
      elseif( $c > 191 ) $bytes = 2;
      else return false;
      if( $i + $bytes > $len ) return false;
      while( $bytes > 1 ) {
        $i++;
        $c = ord( $str[ $i ] );
        if( ( $c < 128 ) || ( $c > 191 ) ) return false;
        $bytes--;
      }
    }
    $i++;
  }
  return true;
}

// define encoding for HTML hot characters:
//
define( 'H_SQ', "\x11" );
define( 'H_DQ', "\x12" );
define( 'H_LT', "\x13" );
define( 'H_GT', "\x14" );
define( 'H_AMP', "\x15" );

$H_SQ = H_SQ;
$H_DQ = H_DQ;
$AUML = H_AMP.'Auml;';
$aUML = H_AMP.'auml;';
$OUML = H_AMP.'Ouml;';
$oUML = H_AMP.'ouml;';
$UUML = H_AMP.'Uuml;';
$uUML = H_AMP.'uuml;';
$SZLIG = H_AMP.'szlig;';


// jlf_get_column: identify and return column information from global $tables for $fieldname
// a column <col> will match if $fieldname == <col> or $fieldname === <table>_<col>
// $opts:
//   'tables': space-separated list of tables to consider (special case: $opts === true: consider all $tables)
//   'basename': look for this name rather than $fieldname
//   'rows': list of <table> => <row> mappings; consider columns present in one <row>
//
function jlf_get_column( $fieldname, $opts = true ) {
  global $tables;

  if( $opts === true ) {
    $tnames = array_keys( $tables );
    $opts = array();
  } else {
    $opts = parameters_explode( $opts );
    $tnames = adefault( $opts, 'tables', array() );
    if( isstring( $tnames ) ) {
      $tnames = explode( ' ', $tnames );
    }
  }
  $basename = adefault( $opts, 'basename', $fieldname );
  $rows = adefault( $opts, 'rows', array() );
  foreach( array_merge( $rows, $tnames ) as $table => $row ) {
    if( isnumeric( $table ) ) {
      // assume: it's really a table name from $tables:
      $table = $row;
    } else {
      // assume: it's a row from a table - only consider if $fieldname is set:
      if( ! isset( $row[ $basename ] ) ) {
        continue;
      }
    }
    if( isset( $tables[ $table ]['cols'][ $basename ] ) )
      return $tables[ $table ]['cols'][ $basename ];
    $n = strlen( $table );
    if( substr( $basename, 0, $n + 1 ) === "{$table}_" ) {
      $f = substr( $basename, $n + 1 );
      if( isset( $tables[ $table ]['cols'][ $f ] ) )
        return $tables[ $table ]['cols'][ $f ];
    }
  }
  return NULL;
}

// jlf_get_pattern(): identify and return pattern for $fieldname based on $opts:
//
function jlf_get_pattern( $fieldname, $opts = array() ) {
  global $cgi_vars;

  $opts = parameters_explode( $opts );
  $basename = adefault( $opts, 'basename', $fieldname );
  if( isset( $opts['pattern'] ) ) {
    $pattern = $opts['pattern'];
  } else if( ( $col = jlf_get_column( $basename, $opts ) ) ) {
    $pattern = $col['pattern'];
  } else if( isset( $cgi_vars[ $basename ]['pattern'] ) ) {
    $pattern = $cgi_vars[ $basename ]['pattern'];
  } else {
    error( "cannot determine pattern for $fieldname" );
  }
  if( adefault( $opts, 'recursive', 1 ) ) {
    if( $pattern[ 0 ] === 'T' ) {
      unset( $opts['basename'] );
      unset( $opts['pattern'] );
      $pattern = jlf_get_pattern( substr( $pattern, 1 ), $opts );
    }
  }
  return $pattern;
}

function jlf_get_default( $fieldname, $opts = array() ) {
  global $cgi_vars;

  $opts = parameters_explode( $opts );
  $basename = adefault( $opts, 'basename', $fieldname );
  if( isset( $opts['default'] ) ) {
    return $opts['default'];
  }
  if( ( $col = jlf_get_column( $basename, $opts ) ) ) {
    if( isset( $col['default'] ) ) {
      return $col['default'];
    }
  }
  if( isset( $cgi_vars[ $basename ]['default'] ) ) {
    return $cgi_vars[ $basename ]['default'];
  }
  $opts['recursive'] = 0;
  $pattern = jlf_get_pattern( $basename, $opts );
  if( $pattern[ 0 ] === 'T' ) {
    unset( $opts['basename'] );
    unset( $opts['pattern'] );
    return jlf_get_default( substr( $pattern, 1 ), $opts );
  } else {
    return jlf_pattern_default( $pattern );
  }
}


function jlf_regex_pattern( $pattern_in ) {
  if( $pattern_in[ 0 ] === '/' )  /* already an regex? */
    return $pattern_in;
  switch( "$pattern_in" ) {
    case 'b': return '/^[01]$/';
    case 'd': return '/^-?\d+$/';
    case 'U': return '/^0*[1-9]\d*$/';
    case 'u': return '/^\d+$/';
    case 'x': return '/^[a-fA-F0-9]*$/';
    case 'X': return '/^0*[a-fA-F1-9][a-f0-9]*$/';
    case 'f': return '/^-?(\d+\.?|.\d)\d*$/';
    case 'w': return '/^([a-zA-Z_][a-zA-Z0-9_]*|)$/';
    case 'W': return '/^[a-zA-Z_][a-zA-Z0-9_]*$/';
    case 'l': return '/^[a-zA-Z0-9_,=-]*$/';
    case 'h': return '/^/';    /* dummy pattern... */
    default: error( "cannot resolve $pattern_in to regular expression" );
  }
}

// default-defaults for common types:
//
function jlf_pattern_default( $pattern_in, $default = NULL ) {
  switch( $pattern_in ) {
    case 'b':
    case 'd':
    case 'u':
    case 'x':
      return '0';
    case 'f':
      return '0.0';
    case 'w':
    case 'l':
    case 'h':
      return '';
    default:
      if( $default !== NULL )
        return $default;
      else
        error( "no default for pattern $pattern_in" );
  }
}

// checkvalue: type-check and optionally filter data passed via http: $type can be
//   b : boolean: 0 or 1
//   d : integer number
//   u : non-negative integer
//   U : integer greater than 0
//   h : text: must be valid utf-8, and must contain no control (<32) chars but \r, \n, \t
//   H : non-empty text (not just white space)
//   f : fixed-point decimal fraction number
//   w : word: alphanumeric and _; empty string allowed
//   W : non-empty word
//   x : non-negative hexadecimal number
//   X : positive hexadecimal number
//   l : list: like w, but may also contain ',', '-' and '='
//   /.../: regex pattern. value will also be trim()-ed
//   Tname: use $cgi_vars['name']['pattern']
//   E<sep><value1>[<sep><value2>: enum: list of literal values, <sep> is arbitrary separator character
//     - with E, a numerical value n will be mapped to n-th element in list, starting from 1
//     - exception: if first element in list is empty string, in will get index 0
//
// return value: the value, possibly in normalized format, or NULL if type check fails.
//
function checkvalue( $val, $pattern_in ) {
  global $cgi_vars;

  if( ! check_utf8( $val ) ) {
    return NULL;
  }
  $pattern = '';
  $format = '';
  if( $pattern_in[ 0 ] === 'T' ) {
    $name = substr( $pattern_in, 1 );
    need( isset( $cgi_vars[ $name ]['pattern'] ), "cannot resolve pattern $pattern_in" );
    $pattern_in = $cgi_vars[ $name ]['pattern'];
  }
  switch( $pattern_in[ 0 ] ) {

    case 'H':
      $pattern = '/\S/';
    case 'h':
      break;

    case 'd':
      $val = trim( $val );
      // discard point or any other trailing garbage:
      $val = preg_replace( '/[^\d].*$/', '', $val );
      $pattern = jlf_regex_pattern( 'd' );
      break;

    case 'f':
      $val = str_replace( ',', '.' , trim($val) );
      $format = '%f';
      $pattern = jlf_regex_pattern( 'f' );
      break;

    case 'b':
    case 'u':
    case 'U':
    case 'l':
    case 'w':
    case 'W':
    case 'x':
    case 'X':
      $val = trim( $val );
      $pattern = jlf_regex_pattern( $pattern_in[ 0 ] );
      break;

    case '/':
      $val = trim( $val );
      $pattern = $pattern_in;
      break;

    case 'E':
      $val = trim( $val );
      $list = explode( $pattern_in[ 1 ], substr( $pattern_in, 2 ) );
      foreach( $list as $literal ) {
        if( $val === $literal )
          return $val;
      }
      if( isnumeric( $val ) ) {
        if( $list[ 0 ] === '' ) {
          // allow option_0 => ''
        } else {
          // no option_0: map 1 to first list entry:
          $val = $val - 1;
        }
        if( isset( $list[ $val ] ) ) {
          return $list[ $val ];
        }
      }
      return NULL;

    default:
      return NULL;
  }
  if( $pattern ) {
    if( ! preg_match( $pattern, $val ) ) {
      return NULL;
    }
  }
  if( $format ) {
    sscanf( $val, $format, & $val );
  }
  return $val;
}

function hex_decode( $r ) {
  need( preg_match( '/^[0-9a-f]*$/', $r ) );
  $l = strlen( $r );
  need( $l % 2 == 0 );
  return pack( 'H*', $r );
}

?>
