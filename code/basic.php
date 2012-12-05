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
function isnumber( $bla ) {
  return is_numeric( $bla );
}
function isnull( $bla ) {
  return is_null( $bla );
}
// reverse case: would also be nice but can't work:
// function is_set( $bla ) {
//   return isset( $bla );
//}


// adefault():
// - $indices is an index, or list of indices, to try in turn on $array
// - every index may also be a list of indices to try deep access on multi-level $array
// - if no valid index is found, $default is returned
//
function adefault( $array, $indices, $default = 0 ) {
  if( ! is_array( $array ) )
    return $default;
  if( ! is_array( $indices ) )
    $indices = array( $indices );
  foreach( $indices as $index ) {
    if( ( $index === false ) || ( $index === NULL ) )
      continue;
    if( isarray( $index ) ) {
      $a = $array;
      foreach( $index as $i ) {
        if( ! is_array( $a ) ) {
          continue 2;
        }
        // must use array_key_exists() here: isset() returns FALSE for NULL!
        if( ! array_key_exists( $i, $a ) ) {
          continue 2;
        }
        $a = $a[ $i ];
      }
      return $a;
    }
    if( array_key_exists( $index, $array ) ) {
      return $array[ $index ];
    }
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
    $s .= sprintf( '%02x', ord( $c ) );
    $bytes--;
  }
  return $s;
}

function get_tmp_working_dir( $base = '/tmp' ) {
  for( $retries = 0; $retries < 10; $retries++ ) {
    $fqpath = $base .'/'.$GLOBALS['jlf_application_name'].'-'.$GLOBALS['jlf_application_name'].'-'.random_hex_string( 8 );
    if( mkdir( $fqpath, 0700 ) )
      return $fqpath;
  }
  return false;
}

// tree_merge: recursively merge data structures $a and $b:
// - numeric-indexed elements from $b will be appended to $a
// - string-indexed elements will be merged recursively, if they exist in both arrays
// - any non-null, non-array rhs will replace lhs (at all levels)
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
// - turns n-array into a-array
// - flags with no assignment "f1,f2,..." or n-array elements will map to 1: array( 'f1' => 1, 'f2' => 1, ... )
// options:
// - 'default_value': map flags and n-indexed elements to this value instead of 1
// - 'default_key': use flags with no assignment as value to this key, rather than as a key
// - 'default_null': flag: use NULL as default value 
// - 'keep': array or comma-separated list of parameter names or name=default pairs:
//     * parameters not in this list will be discarded
//     * parameters with default value other than NULL are guaranteed to be set
// - 'separator': separator (default: , (comma))
//
function parameters_explode( $r, $opts = array() ) {
  if( is_string( $opts ) ) {
    $opts = parameters_explode( $opts, array( 'default_key' => 'default_key' ) );
  }
  $default_key = ( isset( $opts['default_key'] ) ? $opts['default_key'] : '' ); // allow to omit name of most common option
  if( isset( $opts['default_null'] ) ) {
    $default_value = NULL;
  } else {
    $default_value = ( array_key_exists( 'default_value', $opts ) ? $opts['default_value'] : 1  ); // default value (often: 1 for boolean options)
  }
  $keep = ( isset( $opts['keep'] ) ? $opts['keep'] : true );
  $separator = adefault( $opts, 'separator', ',' );
  if( $keep !== true ) {
    $keep = parameters_explode( $keep, array( 'default_null' => true ) );
    // debug( $keep, 'keep' );
  }
  if( ! $r ) {
    $r = array();
  } else if( is_string( $r ) || is_numeric( $r ) ) {
    $pairs = explode( $separator, "$r" );
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
        if( $default_key )
          $r[ $default_key ] = $val;
        else
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
    if( array_key_exists( $key, $r ) ) {
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



// date/time functions:
// for the time being, we try to be y2038 safe and restrict valid dates.
// 0 ist used to denote invalid / empty dates, both as unix time stamp and string.

// date/time conversion functions: we support 3 formats:
// - canonical: good for display, sorting and composing from parts
// - unix: the unix time stamp, good for arithmetics and sorting
// - weird: the traditional baroque format. good for nothing but frequently requested by users.
//
function date_canonical2weird( $date_can ) {
  return substr( $date_can, 0, 4 ) .'-'. substr( $date_can, 4, 2 ) .'-'. substr( $date_can, 6, 2 );
}
function time_canonical2weird( $date_can ) {
  return substr( $date_can, 9, 2 ) . ':' . substr( $date_can, 11, 2 ) . ':' . substr( $date_can, 13, 2 );
}
function date_weird2canonical( $date_weird ) {
  $d = substr( $date_weird, 0, 4 ) . substr( $date_weird, 5, 2 ) . substr( $date_weird, 8, 2 );
  return $d;
}
function datetime_canonical2unix( $time_can ) {
  if( $time_can[ 0 ] === '0' ) {
    return 0;
  }
  $l = strlen( $time_can );
  $year  = (int)( substr( $time_can, 0, 4 ) );
  if( ( $year < 1970 ) || ( $year > 2029 ) ) {
    return 0;
  }
  $month = (int)( substr( $time_can, 4, 2 ) );
  $day   = (int)( substr( $time_can, 6, 2 ) );
  if( $l <= 8 ) {
    $hour = $minute = $second = 0;
  } else {
    $time_can .= '000000';
    $hour = (int)( substr( $time_can, 9, 2 ) );
    $minute = (int)( substr( $time_can, 11, 2 ) );
    $second = (int)( substr( $time_can, 13, 2 ) );
  }
  return mktime( $hour, $minute, $second, $month, $day, $year, 0 );
}

function datetime_unix2canonical( $time_unix ) {
  if( (int)$time_unix === 0 ) {
    return '0';
  }
  $time = explode( ',' , gmdate( 'Y,m,d,H,i,s', $time_unix ) );
  return $time[0] . $time[1] . $time[2] . '.' . $time[3] . $time[4] . $time[5];
}

function date_yearweek2unix( $year, $week, $day = 1 ) {
  if( $week == 0 ) {
    $unix = strtotime( sprintf( '%4u-W01-%1u -7 days', $year, $day ) );
  } else {
    $unix = strtotime( sprintf( '%4u-W%2u-%1u', $year, $week, $day ) );
    if( ! $unix )
      $unix = strtotime( sprintf( '%4u-W%2u-%1u +7 days', $year, $week-1, $day ) );
  }
  need( $unix );
  return $unix;
}


// datetime_explode():
// - takes unix timestamp and returns array with fields
//   'utc', 'unix', 'Y', 'M', 'D', 'h', 'm', 's', 'W' (week-of-year number), 'N' (day-of-week-number)
// - out-of-range timestamps will return special value '0'
//
function datetime_explode( $unix ) {
  if( (int)$unix === 0 ) {
    return '0';
  }
  $utc = datetime_unix2canonical( $unix );
  return array(
    'utc' => $utc
  , 'unix' => $unix
  , 'Y' => (int)substr( $utc,  0, 4 )
  , 'M' => (int)substr( $utc,  4, 2 )
  , 'D' => (int)substr( $utc,  6, 2 )
  , 'h' => (int)substr( $utc,  9, 2 )
  , 'm' => (int)substr( $utc, 11, 2 )
  , 's' => (int)substr( $utc, 13, 2 )
  , 'W' => (int)date( 'W', $unix )
  , 'N' => (int)date( 'N', $unix )
  );
}


// datetime_wizard():
// - $in: either an array having element 'utc' or string
// - try to parse $in as utc; if parsing fails, default to $default, or as last fallback, to "now"
// - apply modifications from $mods, which is a list of <key> => <new_value> mappings
// - return array with fields
//   'utc', 'unix', 'Y', 'M', 'D', 'h', 'm', 's', 'W' (week-of-year number), 'N' (day-of-week-number)
//
function datetime_wizard( $in, $default, $mods = array() ) {
  if( isarray( $in ) ) {
    $in = (string)adefault( $in, 'utc', false );
  }
  $type = jlf_complete_type('t');
  if( ( $in = checkvalue( $in, $type ) ) === NULL ) {
    $in = "$default";
  }
  if( ( $in = checkvalue( $in, $type ) ) === NULL ) {
    $in = '0';
  }

  // debug( $in, 'wizard: in' );
  $unix = datetime_canonical2unix( $in );
  // debug( $unix, 'wizard: unix in' );

  foreach( $mods as $key => $val ) {
    if( ! $unix ) {
      $unix = $GLOBALS['now_unix'];
    }
    $unix_new = false;
    $val = (int)$val;
    $a = datetime_explode( $unix );
    $utc = $a['utc'];
    switch( $key ) {
      case 'Y':
        if( $val === 0 ) {
          $unix_new = 0;
        } else if( ( $val >= 1970 ) && ( $val <= 2029 ) ) {
          $unix_new = datetime_canonical2unix( sprintf( '%4u%s', $val, substr( $utc, 4 ) ) );
        }
        break;
      case 'M':
        $unix_new = datetime_canonical2unix( sprintf( '%s%2u%s', substr( $utc, 0, 4 ), $val, substr( $utc, 6 ) ) );
        break;
      case 'D':
        $unix_new = datetime_canonical2unix( sprintf( '%s%2u%s', substr( $utc, 0, 6 ), $val, substr( $utc, 8 ) ) );
        break;
      case 'h':
        $unix_new = datetime_canonical2unix( sprintf( '%s%2u%s', substr( $utc, 0, 9 ), $val, substr( $utc, 11 ) ) );
        break;
      case 'm':
        $unix_new = datetime_canonical2unix( sprintf( '%s%2u%s', substr( $utc, 0, 11 ), $val, substr( $utc, 13 ) ) );
        break;
      case 's':
        $unix_new = datetime_canonical2unix( sprintf( '%s%2u', substr( $utc, 0, 13 ), $val ) );
        break;
      case 'W':
        $unix_new = strtotime( sprintf( '%4u-W%2u-%1u', $a['Y'], $val, $a['N'] ) );
        breal;
      case 'N':
        $unix_new = strtotime( sprintf( '%4u-W%2u-%1u', $a['Y'], $a['W'], $val ) );
        breal;
      default:
        error( 'unsupported modification requested', LOG_LEVEL_CODE, 'datetime' );
    }
    if( $unix_new !== false ) {
      $unix = $unix_new;
    }
  }

  return datetime_explode( $unix );
}


// check_utf8(): 
//  - verify $in is correct utf8 data
//  - additionally, the non-printable ASCII characters (0...31) will be rejected, with 3 exceptions:
//    "\n" === "\0x0a" (linefeed), "\r" === "\0x0d" (carriage return) and "\t" === "\0x09" (tab) are allowed
//  - returns true or false
//
function check_utf8( $in ) {
  $str = "$in"; // grr...
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


// jlf_complete_type(): takes an assoc array and returns it completed by setting the following fields if unset:
// - pattern: either regex pattern to be matched by legal values, or n-array of allowed literal values (enum type)
// - default: default value for type; NULL for no default
// - format: default output format, usually beginning with '%' to indicate a printf-style conversion
// - normalize: n-array (possibly empty) of normalization operations to be applied to web input
// missing fields will be derived from 'type' shorthand, or global last-resort defaults
//
// special cases:
//  R: for file upload
//  t<F>: time stamp: canonical format YYYYMMDD.hhmm
//  <F> is format string containing subset of letters Y M D h m W(week) indicating parts that may be POSTed separately
//
function jlf_complete_type( $t ) {
  global $cgi_vars;
  $t = parameters_explode( $t, 'default_key=type' );
  if( ! isset( $t['type'] ) ) {
    // minimum requirement: pattern must be specified:
    need( isset( $t['pattern'] ) );
    // last-resort defaults for other properties:
    if( ! isset( $t['default'] ) )
      $t['default'] = NULL;
    if( ! isset( $t['format'] ) )
      $t['format'] = '%s';
    if( ! isset( $t['normalize'] ) )
      $t['normalize'] = array();
    return $t;
  }
  $normalize = array();
  $default = '';
  $type = $t['type'];
  $maxlen = substr( $type, 1 );
  switch( $type[ 0 ] ) {
    case 'a':
      $pattern = '/^[[:ascii:]]*$/';
      $default = '';
      $format = '%s';
      if( ! $maxlen )
        $maxlen = 1024;
      $normalize = array( "T$maxlen", 'k[[:ascii:]]*' );
      break;
    case 'A':
      $pattern = '/^[[:ascii:]]+$/';
      $default = '';
      $format = '%s';
      if( ! $maxlen )
        $maxlen = 1024;
      $normalize = array( "T$maxlen", 'k[[:ascii:]]*' );
      break;
    case 'b':
      $pattern = '/^[01]$/';
      $default = '0';
      $format = '%u';
      $maxlen = 1;
      $normalize = array( 'T1', 'k[01]', 'N' );
      break;
    case 'd':
      $pattern = '/^-?\d+$/';
      $default = '0';
      $format = '%d';
      if( ! $maxlen )
        $maxlen = 12;
      $normalize = array( "T$maxlen", 'k-?\d*', 'N' );
      break;
    case 'U':
      $pattern = '/^0*[1-9]\d*$/';
      $default = 0; // not an acceptable value - just to initialize form fields
      $format = '%u';
      if( ! $maxlen )
        $maxlen = 11;
      $normalize = array( "T$maxlen", 'k\d*', 'N' );
      break;
    case 'u':
      $pattern = '/^\d+$/';
      $default = '0';
      $format = '%u';
      if( ! $maxlen )
        $maxlen = 11;
      $normalize = array( "T$maxlen", 'k\d*', 'N' );
      break;
    case 'x':
      $pattern = '/^[a-fA-F0-9]*$/';
      $default = '0';
      $format = '%x';
      if( ! $maxlen )
        $maxlen = 256;
      $normalize = array( "T$maxlen", 'k[\dabcdef]*', 'N' );
      break;
    case 'X':
      $pattern = '/^0*[a-fA-F1-9][a-fA-F0-9]*$/';
      $default = 0;
      $format = '%x';
      if( ! $maxlen )
        $maxlen = 256;
      $normalize = array( "T$maxlen", 'k[\dabcdef]*', 'N' );
      break;
    case 'f':
      $pattern = '/^-?\d+[.]?\d*$/';
      $default = '0.0';
      $format = '%F';
      if( ! $maxlen )
        $maxlen = 32;
      $normalize = array( "T$maxlen", 'k-?\d*[,.]?\d*', 's/,/./', 's/^(-?)[.]/${1}0./', 'N' );
      break;
    case 'F':
      $pattern = '/^\d+[.]?\d*$/';
      $default = '0.0';
      $format = '%F';
      if( ! $maxlen )
        $maxlen = 32;
      $normalize = array( "T$maxlen", 'k\d*[,.]?\d*', 's/,/./', 's/^[.]/0./', 'N' );
      break;
    case 'w':
      $pattern = '/^([a-zA-Z_][a-zA-Z0-9_]*|)$/';
      $default = '';
      $format = '%s';
      if( ! $maxlen )
        $maxlen = 256;
      $normalize[] = "T$maxlen";
      break;
    case 'W':
      $pattern = '/^[a-zA-Z_][a-zA-Z0-9_]*$/';
      $default = '';
      $format = '%s';
      if( ! $maxlen )
        $maxlen = 256;
      $normalize[] = "T$maxlen";
      break;
    case 'l':
      $pattern = '/^[a-zA-Z0-9_,=-]*$/';
      $default = '';
      $format = '%s';
      if( ! $maxlen )
        $maxlen = 4096;
      $normalize[] = "T$maxlen";
      break;
    case 't': //timestamp
      // $pattern = '/^\d{8}([.]\d{1,6})?$/';
      // for the time being: use y2038-safe pattern:
      $pattern = '/(^((19[789])|(20[012]))\d{5}([.]\d{1,6})?$)|(^0$)/';
      $default = '0';
      $format = '%s';
      $normalize = array( 'T15', 'k(0)|(\d{8}([.]\d{1,6})?)', 's/^0.*$/0/' );
      $maxlen = 15;
      break;
    case 'h': // arbitrary string, not trimmed
      $pattern = '/^/';    /* dummy pattern... */
      $default = '';
      if( ! $maxlen )
        $maxlen = 10000;
      $format = '%s';
      $normalize[] = "L$maxlen";
      break;
    case 'H': // non-empty string, trimmed
      $pattern = '/./';
      $default = '';
      if( ! $maxlen )
        $maxlen = 10000;
      $format = '%s';
      $normalize[] = "T$maxlen";
      break;
    case 'E':
      $pattern = explode( $type[ 1 ], substr( $type, 2 ) );
      $format = '%s';
      $maxlen = max( array_map( 'strlen', $pattern ) );
      $normalize[] = "T$maxlen";
      $default = '';
      break;
    case 'T':
      $name = $maxlen;
      need( isset( $cgi_vars[ $name ] ) );
      $r = jlf_complete_type( $cgi_vars[ $name ] );
      $pattern = $r['pattern'];
      $default = $r['default'];
      $format = $r['format'];
      $normalize = $r['normalize'];
      break;
    case 'R': // raw: data from file upload, will be returned uuencoded
      $pattern = '/^/';
      $format = '%x';
      $default = '';
      break;
    default:
      error( "unknown type $type", LOG_FLAG_CODE, 'type' );
  }
  if( ! isset( $t['pattern'] ) ) {
    $t['pattern'] = $pattern;
  }
  if( ! array_key_exists( 'default', $t ) /* NULL is a possible default value */ ) {
    $t['default'] = $default;
  }
  if( ! isset( $t['format'] ) ) {
    $t['format'] = $format;
  }
  if( ! isset( $t['normalize'] ) ) {
    $t['normalize'] = $normalize;
  }
  return $t;
}


// jlf_get_column: identify and return column information from global $tables for $fieldname
// a column <col> of <table> will match if $fieldname == <col> or $fieldname === <table>_<col>
// $opts:
//   'tables': a-array or n-array or space-separated list of tables to consider
//             (special case: $opts === true: consider all $tables)
//   'basename': look for this name rather than $fieldname
//   'rows': a-array of <table> => <row> mappings; consider columns present in one <row>
//
function jlf_get_column( $fieldname, $opts = true ) {
  global $tables;

  if( $opts === true ) {
    $tnames = parameters_explode( array_keys( $tables ) );
    $opts = array();
  } else {
    $opts = parameters_explode( $opts );
    $tnames = adefault( $opts, 'tables', array() );
    if( isstring( $tnames ) )
      $tnames = explode( ' ', $tnames );
  }
  $tnames = parameters_explode( $tnames ); // turn into a-array: <tab1> => 1, <tab2> => 1, ...
  $basename = adefault( $opts, 'basename', $fieldname );
  $rows = adefault( $opts, 'rows', array() );
  // debug( $tnames, 'tnames' );
  // debug( $rows, 'rows' );
  // debug( $basename, 'basename' );
  foreach( array_merge( $rows, $tnames ) as $table => $row ) {
    if( isarray( $row ) && ! isset( $row[ $basename ] ) ) {
      continue;
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
  // debug( $fieldname, 'No column found:' );
  return NULL;
}

// jlf_get_complete_type(): identify and return type information for $fieldname
// the return value will at least contain fields 'format', 'pattern', 'normalize', 'default'
// sources tried in this order are:
//   - entry 'type' in $opts
//   - type information in column for $fieldname identified by jlf_get_column() (see above)
//   - type information for $fieldname in global array $cgi_vars
// fields 'pattern', 'default', 'format', 'normalize' will be completed by jlf_complete_type,
// values already present in $opts take precedence
//
function jlf_get_complete_type( $fieldname, $opts = array() ) {
  global $cgi_vars;

  $opts = parameters_explode( $opts );
  $t = parameters_explode( $opts, array( 'keep' => 'default,pattern,format,type,normalize,maxlen,min,max,allow_null' ) );

  $basename = adefault( $opts, 'basename', $fieldname );
  if( $basename[ 0 ] === 'F' ) {
    $basename = preg_replace( '/^F[^_]*_/', '', $basename );
  }
  if( isset( $t['type'] ) ) {
    // nop
  } else if( ( $col = jlf_get_column( $basename, $opts ) ) ) {
    $t = array_merge( $col, $t );
  } else if( isset( $cgi_vars[ $basename ] ) ) {
    $t = array_merge( $cgi_vars[ $basename ], $t );
  }
  return jlf_complete_type( $t );
}


// normalize(): normalize $in based on $normalize, which is an n-array of normalization instructions:
// first letter of each instruction determines the operation:
//  - 'T': trim value, optionally followed by a truncation length
//  - 'L': followed by truncation length
//  - 'N': map any value which is == false to '0'
//  - '%': feed through sprintf
//  - 'k': anchored preg pattern to keep. no delimiters needed. pattern ist greedy; any unmatched trailing characters will be deleted.
//  - 's': sed-like substitution instruction (delimiters needed)
// before executing normalization instructions, any numbers and booleans will be converted to strings
//
function normalize( $in, $normalize ) {
  if( ! isstring( $in ) ) {
    if( isnumeric( $in ) ) {
      $in = "$in";
    } else if ( ( $in === false ) || ( $in === NULL ) ) {
      $in = '';
    } else if ( $in === true ) {
      $in = '1';
    } else {
      error( 'cannot handle input type', LOG_LEVEL_CODE, 'type' );
    }
  }
  if( ! isarray( $normalize ) )
    $normalize = array( $normalize );
  foreach( $normalize as $op ) {
    switch( $op[ 0 ] ) {
      case 'T':
        $in = trim( $in );
        // fall-through...
      case 'L':
        if( $in )
          if( ( $len = substr( $op, 1 ) ) > 0 )
            $in = substr( $in, 0, $len );
        break;
      case 'N':
        if( ! $in )
          $in = '0';
        break;
      case '%':
        $in = sprintf( $op, $in );
        break;
      case 'k':
        $in = preg_replace( '/^('.substr( $op, 1 ).').*$/' , '$1', $in );
        break;
      case 's':
        $sep = $op[ 1 ];
        $op = explode( $sep, substr( $op, 2 ) );
        $in = preg_replace( $sep.$op[ 0 ].$sep, $op[ 1 ], $in );
        break;
      default:
        error( 'cannot handle normalization instruction', LOG_FLAG_CODE, 'type' );
    }
  }
  need( isstring( $in ) );
  return $in;
}

// checkvalue: type-check and normalize $in:
// - any input will be converted to string and must be valid utf-8
// - input will be normalize()d according to $type['normalize'] (if set)
// - after normalization, input must match $type['pattern']
// - $type['min'] and $type['max'] can be set to impose limits on numerical data
//
// return value: the normalized input or NULL
//
function checkvalue( $in, $type ) {
  $val = (string) $in;
  // check_utf8 is already performed in sanitize_http_input()
  // if( ! check_utf8( $val ) ) {
  //   return NULL;
  // }
  if( $type['normalize'] )
    $val = normalize( $val, $type['normalize'] ); 

  if( $type['type'][ 0 ] == 'R' ) {
    // debug( $type, 'type' );
    // debug( substr( $in, 0, 6 ), 'in' );
  }
  if( isset( $type['allow_null'] ) ) {
    if( $val === $type['allow_null'] )
      return $val;
  }

  if( isarray( ( $pattern = $type['pattern'] ) ) ) {
    while( 1 ) {
      foreach( $pattern as $literal ) {
        if( $val === $literal )
          break 2;
      }
      return NULL;
    }
  } else {
    if( ! preg_match( $pattern, $val ) )
      return NULL;
  }

  if( ( $min = adefault( $type, 'min', false ) ) !== false ) {
    if( (float)$val < (float)$min )
      return NULL;
  }
  if( ( $max = adefault( $type, 'max', false ) ) !== false ) {
    if( (float)$val > (float)$max )
      return NULL;
  }

  return $val;
}




function hex_decode( $r ) {
  need( preg_match( '/^[0-9a-f]*$/', $r ) );
  $l = strlen( $r );
  need( $l % 2 == 0 );
  return pack( 'H*', $r );
}

function we( $se, $sd = '' ) {
  return ( ( $GLOBALS['language'] == 'E' ) ? $se : $sd );
}
function wd( $sd, $se = '' ) {
  return ( ( $GLOBALS['language'] == 'D' ) ? $sd : $se );
}

function ldif_encode( $a ) {
  $r = '';
  foreach( $a as $key => $val ) {
    if( isarray( $val ) ) {
      $r .= ldif_encode( $val );
      $r .= "\n";
    } else {
      $r .= "$key:";
      for( $i = 0; $i < strlen( $val ); $i++ ) {
        $c = ord( $val[ $i ] );
        if( ( $c < 32 ) || ( $c > 126 ) ) {
          $r .= ': ' . base64_encode( $val ) . "\n";
          continue 2;
        }
      }
      $r .= ' ' . $val . "\n";
    }
  }
  return $r;
}


// PDF generation (via pdflatex):
//
//
function tex2pdf( $tex ) {
  $tex = preg_replace( '/@@macros_prettytables@@/', file_get_contents( 'tex/prettytables.tex' ), $tex );
  $cwd = getcwd();
  need( $tmpdir = get_tmp_working_dir() );
  need( chdir( $tmpdir ) );
  file_put_contents( 'tex2pdf.tex', $tex );
  exec( 'pdflatex tex2pdf.tex', /* & */ $output, /* & */ $rv );
  if( ! $rv ) {
    $pdf = file_get_contents( 'tex2pdf.pdf' );
    // open_div( 'ok', '', 'ok: '.  implode( ' ', $output ) );
  } else {
    open_div( 'warn', '', 'error: '. file_get_contents( 'tex2pdf.log' ) );
    logger( 'tex2pdf failed', LOG_LEVEL_ERROR, LOG_FLAG_CODE | LOG_FLAG_USER, 'tex2pdf' ); 
    $pdf = false;
  }
  @ unlink( 'tex2pdf.tex' );
  @ unlink( 'tex2pdf.aux' );
  @ unlink( 'tex2pdf.log' );
  @ unlink( 'tex2pdf.pdf' );
  chdir( $cwd );
  rmdir( $tmpdir );

  return $pdf;
}

function tex_encode( $s ) {
  $maps = array(
    '/\\\\/' => '\\backslash'
  , '/\\&quot;/' => "''"
  , '/\\&#039;/' => "'"
  , '/([$%_#~])/' => '\\\\$1'
  , '/\\&amp;/' => '\\&'
  , '/\\&lt;/' => '$<$'
  , '/\\&gt;/' => '$>$'
  , '/[}]/' => '$\}$'
  , '/[{]/' => '$\{$'
  , '/ä/' => '{\"a}'
  , '/Ä/' => '{\"A}'
  , '/ö/' => '{\"o}'
  , '/Ö/' => '{\"O}'
  , '/ü/' => '{\"u}'
  , '/Ü/' => '{\"U}'
  , '/ß/' => '{\ss}'
  , '/\\\\backslash/' => '\\$\\backslash{}\\$'
  );
  foreach( $maps as $pattern => $to ) {
    $s = preg_replace( $pattern, $to, $s );
  }
  $len = strlen( $s );
  $i = 0;
  $out = '';
  while( $i < $len ) {
    $c = $s[ $i ];
    $n = ord( $c );
    $bytes = 1;
    if( $n < 128 ) {
      // skip most control characters:
      if( $n < 32 ) {
        switch( $n ) {
          case  9: // tab
            $out .= ' ';
            break;
          case 10: // lf
            $out .= '\\newline{}';
            break;
          case 13: // cr
            break;
          default:
            break;
        }
      } else {
        $out .= $c;
      }
    } else {
      // skip remaining utf-8 characters:
      if( $n > 247 ) continue;
      elseif( $n > 239 ) $bytes = 4;
      elseif( $n > 223 ) $bytes = 3;
      elseif( $n > 191 ) $bytes = 2;
      else continue;
    }
    $i += $bytes;
  }
  return $out;
}


function fork_new_thread() {
  global $thread, $now_canonical, $login_people_id, $login_sessions_id, $H_SQ;

  // find new thread id:
  // 
  $tmin = $now_canonical;
  $thread_unused = 0;
  for( $i = 1; $i <= 4; $i++ ) {
    if( $i == $thread )
        continue;
    $v = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id, $i );
    $t = adefault( $v, 'thread_atime', 0 );
    if( $t < $tmin ) {
      $tmin = $t;
      $thread_unused = $i;
    }
  }
  if( ! $thread_unused ) {
    $thread_unused = ( $thread == 4 ? 1 : $thread + 1 );
    logger( "last resort: [$thread_unused] ", LOG_LEVEL_INFO, LOG_FLAG_DEBUG, 'fork' );
  }
  // create fork_form: submission will start new thread; different thread will enforce new window:
  //
  $fork_form_id = open_form( "thread=$thread_unused", '', 'hidden' );
  js_on_exit( " submit_form( {$H_SQ}$fork_form_id{$H_SQ} ); " );
  logger( "forking: $thread -> $thread_unused", LOG_LEVEL_INFO, LOG_FLAG_USER, 'fork' );
}


?>
