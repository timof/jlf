<?php
//
// low-level error handling, logging, debugging and profiling
//
// logging, debugging and profiling will cause delayed writes to the corresponding tables
//

$info_messages = array();
$error_messages = array();

// get_problem_id(): return unique error index so we can use += on array of problems
// it is negative so mixing it with statements like $problems[] = 'new problem'; will also almost work
//
function get_problem_id() {
  static $global_problem_counter = 0;
  return --$global_problem_counter;
}

function new_problem( $p ) {
  return $p ? array( get_problem_id() => $p ) : array();
}

// escaping in php-strings:
// '\\': one literal \
// '\%': two characters: \ and % (\ has no special meaning)
//
function jlf_string_export_cli( $s ) {
  $rv = '"';
  for( $i = 0; $i < strlen( $s ); $i++ ) {
    $c = $s[ $i ];
    if( ( ord( $c ) >= 32 ) && ( ord( $c ) < 127 ) && ( $c !== '\\' ) && ( $c !== '"' ) ) {
      $rv .= $c;
    } else {
      $rv .= sprintf( '\%02x', ord( $c ) );
    }
  }
  return $rv . '"';
}

function jlf_string_export_html( $s ) {
  $rv = '';
  for( $i = 0; $i < strlen( $s ); $i++ ) {
    $c = $s[ $i ];
    if( ( ord( $c ) >= 32 ) && ( ord( $c ) < 127 ) ) {
      $rv .= $c;
    } else {
      $rv .= html_tag( 'span', 'underline bluee quads', sprintf( '%02x', ord( $c ), 'nodebug' ) );
    }
  }
  return html_tag( 'span', 'nounderline bluee', $rv, 'nodebug' );
}

function jlf_var_export_cli( $var, $indent = 0 ) {
  if( isarray( $var ) && ( count( $var ) > 0 ) ) {
    $s = '';
    foreach( $var as $key => $val ) {
      $s .= jlf_var_export_cli( $key, $indent ) . ' => ';
      if( isarray( $val ) ) {
        $s .= jlf_var_export_cli( $val, $indent + 1 );
      } else if( isstring( $val ) && strlen( $val ) > 80 ) {
        $s .= jlf_var_export_cli( $val, $indent + 1 );
      } else {
        $s .= jlf_var_export_cli( $val, -1 );
      }
    }
  } else {
    if( $indent >= 0 ) {
      $s = "\n>" . str_repeat( ' |', $indent );
    } else {
      $s = '';
    }
    if( is_array( $var ) ) {
      $s .= '[EMPTY ARRAY]';
    } else if( is_resource( $var ) ) {
      $s .= '[RESOURCE:'.get_resource_type( $var ).']';
    } else if( $var === NULL ) {
      $s .= '[NULL]';
    } else if( $var === FALSE ) {
      $s .= '[FALSE]';
    } else if( $var === TRUE ) {
      $s .= '[TRUE]';
    } else if( $var === '' ) {
      $s .= '""';
    } else if( isnumeric( $var ) ) {
      $s .= "($var)";
    } else if( isstring( $var ) ) {
      $newline = $s;
      $s = '';
      while( strlen( $var ) > 0 ) {
        $s .= ( $newline . jlf_string_export_cli( substr( $var, 0, 80 ) ) );
        $var = substr( $var, 80 );
      }
    } else {
      $s .= '[?UNKNOWN?]';
    }
  }
  return $s;
}

function jlf_var_export_html( $var, $indent = 0 ) {
  global $max_debug_chars_display;
  if( isarray( $var ) && ( count( $var ) > 0 ) ) {
    $s = '';
    foreach( $var as $key => $val ) {
      $s .= jlf_var_export_html( $key, $indent ) . html_tag( 'span', 'blackk', ' => ', 'nodebug' );
      if( isarray( $val ) ) {
        $s .= jlf_var_export_html( $val, $indent + 1 );
      } else if( isstring( $val ) && strlen( $val ) > 80 ) {
        $s .= jlf_var_export_html( $val, $indent + 1 );
      } else {
        $s .= html_tag( 'span', 'yelloww', '>', 'nodebug' ) . jlf_var_export_html( $val, -1 );
      }
    }
  } else {
    if( $indent > 0 ) {
      $s = "\n" . html_tag( 'span', 'yelloww', str_repeat( ' >', $indent ), 'nodebug' );
    } else if( $indent == 0 ) {
      $s = "\n";
    } else {
      $s = '';
    }
    if( is_array( $var ) ) {
      $s .= html_tag( 'span', 'bold blackk', 'EMPTY ARRAY', 'nodebug' );
    } else if( is_resource( $var ) ) {
      $s .= html_tag( 'span', 'bold blackk', 'RESOURCE: ['.get_resource_type( $var ) .']', 'nodebug' );
    } else if( $var === NULL ) {
      $s .= html_tag( 'span', 'bold blackk', 'NULL', 'nodebug' );
    } else if( $var === FALSE ) {
      $s .= html_tag( 'span', 'bold blackk', 'FALSE', 'nodebug' );
    } else if( $var === TRUE ) {
      $s .= html_tag( 'span', 'bold blackk', 'TRUE', 'nodebug' );
    } else if( isstring( $var ) || isnumeric( $var ) ) {
      $var = (string)( $var );
      if( $var === '' ) {
        $s .= html_tag( 'span', 'bold blackk', 'EMPTY STRING', 'nodebug' );
      } else {
        $newline = $s;
        $s = '';
        $cut_off = 0;
        if( strlen( $var ) > $max_debug_chars_display ) {
          $cut_off = strlen( $var ) - $max_debug_chars_display;
          $var = substr( $var, 0, $max_debug_chars_display );
        }
        while( strlen( $var ) > 0 ) {
          $s .= ( $newline . jlf_string_export_html( substr( $var, 0, 80 ) ) . html_tag( 'span', 'yelloww', '<', 'nodebug' ) );
          $var = substr( $var, 80 );
        }
        if( $cut_off ) {
          $s .= html_tag( 'span', 'bold blackk', " $cut_off MORE CHARACTERS NOT DISPLAYED", 'nodebug' );
        }
      }
    } else {
      $s .= html_tag( 'span', 'bold blackk', '?UNKNOWN?', 'nodebug' );
    }
  }
  return $s;
}

function debug( $value, $comment = '', $facility = '', $object = '', $stack = '' ) {
  global $utc, $script, $sql_delayed_inserts, $initialization_steps, $request_method;
  global $max_debug_messages_display, $max_debug_messages_dump, $debug, $debug_requests;
  global $global_format, $deliverable;
  static $debug_count_dump = 0;
  static $debug_count_display = 1;

  if( $stack === true ) {
    $stack = json_encode_stack( debug_backtrace() );
  } else if( $stack === '' ) {
    if( $debug & DEBUG_FLAG_TRACE ) {
      $stack = json_encode_stack( debug_backtrace() );
    } else {
      $stack = json_encode( false );
    }
  }

  $error = ( $facility === 'error' );
  if( ! $error ) {
    if(    ( ! isset( $initialization_steps['debugger_ready'] ) )
        || ( ! isset( $initialization_steps['payloadbay_open'] ) ) ) {
      // cannot decide (debugger_ready) or output (header_printed) yet - just remember the _raw_ data to process later:
      $sql_delayed_inserts['debug_raw'][] = array(
        'script' => $script
      , 'utc' => $utc
      , 'facility' => $facility
      , 'object' => $object
      , 'stack' => $stack
      , 'show_stack' => '' // obsolete(?)
      , 'comment' => $comment
      , 'value' => $value
      );
      if( $request_method == 'CLI' ) {
        $n = count( $sql_delayed_inserts['debug_raw'] );
        if( ! ( $n % 1000 ) ) {
          echo "debug raw: [$n]\n";
        }
      }
      return;
    }
    if( $facility ) {
      // hard-wired debug calls: only output on request, and enforce upper limit on total number:
      $request = adefault( $debug_requests['cooked'], $facility );
      if( isarray( $request ) ) {
        $request = adefault( $request, $object );
      }
      if( ! $request ) {
        return false;
      }
      if( $object && isstring( $request ) && ! isnumber( $request ) ) {
        if( $request[ 0 ] === '/' ) {
          if( ! preg_match( $request, $object ) ) {
            return false;
          }
        } else {
          if( $request != $object ) {
            return false ;
          }
        }
      }
    }
  }

  ++$debug_count_dump;
  if( $error || ( $debug_count_dump < $max_debug_messages_dump ) ) {
    $sql_delayed_inserts['debug'][] = array(
      'script' => $script
    , 'utc' => $utc
    , 'facility' => $facility
    , 'object' => $object
    , 'stack' => $stack
    , 'comment' => $comment
    , 'value' => json_encode( $value )
    );
  } else if( $debug_count_dump == $max_debug_messages_dump ) {
    $sql_delayed_inserts['debug'][] = array(
      'script' => $script
    , 'utc' => $utc
    , 'facility' => 'debug'
    , 'object' => ''
    , 'stack' => $stack
    , 'comment' => 'maximum number of debug messages reached'
    , 'value' => $debug_count_dump
    );
  }

  if( ! $error ) {
    if( $debug_count_display > $max_debug_messages_display ) {
      return;
    } else if( $debug_count_display == $max_debug_messages_display ) {
      $value = $debug_count_display;
      $comment = 'maximum number of debug messages reached';
      $facility = 'debug';
      $object = '';
    }
  }

  if( $deliverable ) {
    return;
  }
  switch( $global_format ) {
    case 'html':
      if( $error ) {
        html_head_view( 'early error' ); // a nop if header already printed
      } else if( ! isset( $initialization_steps['header_printed'] ) ) {
        return;
      } else if( $facility && ( ! ( $debug & DEBUG_FLAG_INSITU ) ) ) {
        return;
      }
      ++$debug_count_display;
      echo debug_value_view( $value, $comment, $facility, $object, $stack );
      break;
    case 'cli':
      echo "\n> $facility [$object]: $comment";
      echo jlf_var_export_cli( $value, 1 );
      if( $stack ) {
        echo "\n> stack:";
        echo jlf_var_export_cli( $stack, 1 );
      }
      echo "\n";
      ++$debug_count_display;
      break;
    default:
      return;
  }
}


function flush_messages( $messages, $opts = array() ) {
  global $global_format, $open_tags;

  $opts = parameters_explode( $opts );
  if( ! isarray( $messages ) ) {
    $messages = array( $messages );
  }
  switch( $global_format ) {
    case 'html':
      // $n = count( $open_tags );
      // $in_table = ( ( $open_tags['tag'] === 'table' ) || ( $open_tags['role'] === 'table' ) );
      $class = adefault( $opts, 'class', 'warn' );
      $tag = adefault( $opts, 'tag', 'div' );
      html_head_view( 'ERROR: ' ); // is a nop if headers already printed
      foreach( $messages as $s ) {
        echo html_tag( $tag, "class=$class", $s );
      }
      break;
    case 'cli':
      foreach( $messages as $s ) {
        echo "\n$s";
      }
      break;
    default:
      return;
  }
}


function flush_error_messages( $opts = array() ) {
  global $error_messages;
  $opts = parameters_explode( $opts );
  $opts['class'] = adefault( $opts, 'class', 'problem medskips' );
  flush_messages( $error_messages, $opts );
  $error_messages = array();
}

function flush_info_messages( $opts = array() ) {
  global $info_messages;
  $opts = parameters_explode( $opts );
  $opts['class'] = adefault( $opts, 'class', 'ok medskips' );
  flush_messages( $info_messages, $opts );
  $info_messages = array();
}

function flush_all_messages() {
  flush_error_messages();
  flush_info_messages();
}

function error( $msg, $flags = 0, $tags = 'error', $links = array() ) {
  global $initialization_steps, $debug, $H_SQ, $deliverable, $global_format;
  static $in_error = false;
  if( ! $in_error ) { // avoid infinite recursion
    $in_error = true;
    if( isset( $initialization_steps['db_ready'] ) ) {
      sql_do('ROLLBACK', true );
      sql_transaction_boundary(); // required to mark open transaction as closed
    }
    logger( $msg, LOG_LEVEL_ERROR, $flags, $tags, $links, true );
    debug( "$flags", $msg, 'error', $tags, ( $debug & DEBUG_FLAG_TRACE ) ? json_encode_stack( debug_backtrace() ) : false );
    switch( $global_format ) {
      case 'html':
        close_all_tags();
        // try to make sure error message is actually visible:
        open_javascript( "window.onresize = true; \$({$H_SQ}theOutback{$H_SQ}).style.position = {$H_SQ}static{$H_SQ}; " );
        break;
      case 'cli':
        break;
      default:
        // can't do much here:
        break;
    }
    if( isset( $initialization_steps['db_ready'] ) ) {
      sql_commit_delayed_inserts(); // these are for debugging and logging, so they are _not_ rolled back!
      sql_do( 'COMMIT RELEASE', true );
    }
  }
  die();
}

function need( $exp, $comment = 'Houston, we\'ve had a problem' ) {
  if( ! $exp ) {
    while( isarray( $comment ) ) {
      // if there are several fatal problems, just print the first one:
      $comment = reset( $comment );
    }
    error( $comment, LOG_FLAG_CODE | LOG_FLAG_DATA, 'assert' );
  }
  return true;
}

function fail_if_readonly() {
  return need( ! adefault( $GLOBALS, 'readonly', false ), 'database in readonly mode - operation not allowed' );
}

function menatwork() {
  error( 'men at work here - incomplete code ahead', LOG_FLAG_CODE, 'menatwork' );
}

function deprecate() {
  error( 'deprecate code', LOG_FLAG_CODE, 'deprecate' );
}


function logger( $note, $level, $flags, $tags = '', $links = array(), $stack = false ) {
  global $login_sessions_id, $initialization_steps, $jlf_application_name, $sql_delayed_inserts, $log_keep_seconds;
  global $client_ip4, $client_port;

  if( ! isset( $initialization_steps['db_ready'] ) ) {
    return;
  }

  if( $stack === true ) {
    $stack = json_encode_stack( debug_backtrace() );
  } else if( $stack === false ) {
    $stack = json_encode( false );
  }
  if( ( ! $log_keep_seconds ) && ( $level < LOG_LEVEL_ERROR) ) {
    return;
  }

  $sql_delayed_inserts['logbook'][] = array(
    'sessions_id' => adefault( $GLOBALS, 'login_sessions_id', '0' )
  , 'thread' => adefault( $GLOBALS, 'thread', '0' )
  , 'window' => adefault( $GLOBALS, 'window', '0' )
  , 'script' => adefault( $GLOBALS, 'script', '0' )
  , 'parent_thread' => adefault( $GLOBALS, 'parent_thread', '0' )
  , 'parent_window' => adefault( $GLOBALS, 'parent_window', '0' )
  , 'parent_script' => adefault( $GLOBALS, 'parent_script', '0' )
  , 'tags' => $tags
  , 'note' => $note
  , 'flags' => $flags
  , 'level' => $level
  , 'links' => json_encode( $links )
  , 'stack' => $stack
  , 'remote_addr' => "$client_ip4:$client_port"
  , 'utc' => $GLOBALS['utc']
  , 'application' => $jlf_application_name
  );
}


// priv_problems(): a stub to return a "problems" array in case of missing privileges
// function have_priv() must be implemented by every subproject to do the actual checking
//
function priv_problems( $section = '*', $action = '*', $item = 0 ) {
  if( have_priv( $section, $action, $item ) ) {
    return array();
  } else {
    return new_problem( we('insufficient privileges','keine Berechtigung') );
  }
}

$debug_requests = array(
  'raw' => array()
, 'cooked' => array( 'variables' => array() )
);

# init_debugger()
# parses space-separated list debugRequests of debug requests, where
#   REQUEST ::= VARIABLE_REQUEST | FUNCTION_REQUEST
#   FUNCTION_REQUEST ::= FUNCTION [ : ACTION, ... ]
#   ACTION ::= RESOURCE [ . OPERATION ]
#
function init_debugger( $debug_default = 0 ) {
  global $debug_requests, $script, $show_debug_button, $initialization_steps, $sql_delayed_inserts;

  $sources = 'http window'; // ( $show_debug_button ? 'http script window' : 'script' );
  if( $show_debug_button || have_priv( '*','*' ) ) {
    init_var( 'debug', "global,type=u4,sources=$sources,default=$debug_default,set_scopes=window" );
    global $debug; // must come _after_ init_var()!
  } else {
    // use value set in global.php!
    // global $debug;
    // $debug = 0;
  }
  $scopes = ( ( $debug || $show_debug_button ) ? 'window' : '' );
  init_var( 'max_debug_messages_display', "global,type=u,sources=$sources,default=10,set_scopes=$scopes" );
  init_var( 'max_debug_messages_dump', "global,type=u,sources=$sources,default=100,set_scopes=$scopes" );
  init_var( 'max_debug_chars_display', "global,type=u,sources=$sources,default=200,set_scopes=$scopes" );

  $debug_requests['raw'] = init_var( 'debug_requests', "sources=$sources,set_scopes=$scopes,type=a1024" );

  if( $debug_requests['raw']['value'] ) {
    foreach( explode( ' ', $debug_requests['raw']['value'] ) as $r ) {
      if( ! $r ) {
        continue;
      }
      $pair = explode( ':', $r, 2 );
      $name = $pair[ 0 ];
      if( isset( $pair[ 1 ] ) ) {
        if( $pair[ 1 ] ) {
          $lreqs = explode( ',', $pair[ 1 ] );
          foreach( $lreqs as $r ) {
            $action = explode( '.', $r );
            $debug_requests['cooked'][ $name ][ $action[ 0 ] ] = ( isset( $action[ 1 ] ) ? $action[ 1 ] : 1 );
          }
        } else {
          $debug_requests['cooked'][ $name ] = 1;
        }
      } else {
        $debug_requests['cooked']['variables'][ $name ] = 1;
      }
    }

    sql_transaction_boundary( '', 'debug' );
      sql_delete( 'debug', "script=$script", 'authorized=1' );
    sql_transaction_boundary();
  }
  if( $debug & DEBUG_FLAG_PROFILE ) {
    sql_transaction_boundary( '', 'profile' );
      sql_delete( 'profile', "script=$script", 'authorized=1' );
    sql_transaction_boundary();
  } else {
    unset( $sql_delayed_inserts['profile'] );
  }
  $initialization_steps['debugger_ready'] = true;
}

function flush_debug_messages() {
  global $sql_delayed_inserts;
  foreach( $sql_delayed_inserts['debug_raw'] as $r ) {
    debug( $r['value'], $r['comment'], $r['facility'], $r['object'], $r['stack'] );
  }
  $sql_delayed_inserts['debug_raw'] = array();
}

function finish_debugger() {
  global $debug, $debug_requests, $start_unix_microtime, $sql_delayed_inserts, $utc, $script;
  $sql_delayed_inserts['profile'][] = array(
    'script' => $script
  , 'utc' => $utc
  , 'sql' => ''
  , 'rows_returned' => 0
  , 'wallclock_seconds' => microtime( true ) - $start_unix_microtime
  , 'stack' => json_encode( '' )
  );
  foreach( $debug_requests['cooked']['variables'] as $var => $op ) {
    $sql_delayed_inserts['debug'][] = array(
      'script' => $script
    , 'utc' => $utc
    , 'facility' => 'global_vars'
    , 'object' => $var
    , 'comment' => ( isset( $GLOBALS[ $var ] ) ? 'is set' : 'not set'  )
    , 'value' => json_encode( isset( $GLOBALS[ $var ] ) ? $GLOBALS[ $var ] : NULL )
    );
  }
}

?>
