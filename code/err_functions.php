<?php
//
// low-level error handling and logging
//
// these functions may attempt to log to the database, but must be safe to call even
// if no db is available!
//

define('DEBUG_LEVEL_NEVER', 0);
define('DEBUG_LEVEL_ALL', 1);
define('DEBUG_LEVEL_MOST', 2);
define('DEBUG_LEVEL_IMPORTANT', 3); // all UPDATE and INSERT statements should have level important
define('DEBUG_LEVEL_KEY', 4);
define('DEBUG_LEVEL_NONE', 5);

$debug_level = DEBUG_LEVEL_IMPORTANT; // preliminary value - to be determined from table leitvariable

// we need this here in case we have print error message very early:
//
define( 'DOCTYPE_BLUBBER', "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n\n" );

$debug_messages = array();
$info_messages = array();
$problems = array();

function jlf_string_export( $s ) {
  $rv = '';
  for( $i = 0; $i < strlen( $s ); $i++ ) {
    $c = $s[ $i ];
    if( ord( $c ) >= 32 && ord( $c ) < 127 ) {
      $rv .= $c;
    } else {
      $rv .= html_tag( 'span', 'nounderline bold blue quads', sprintf( '%02x', ord( $c ) ) );
    }
  }
  return html_tag( 'span', 'underline black', $rv );
}

function jlf_var_export( $var, $indent = 0 ) {
  if( isarray( $var ) && ( count( $var ) > 0 ) ) {
    $s = '';
    foreach( $var as $key => $val ) {
      $s .= jlf_var_export( $key, $indent ) . html_tag( 'span', 'bold blue', ' => ' );
      if( isarray( $val ) ) {
        $s .= jlf_var_export( $val, $indent + 2 );
      } else if( isstring( $val ) && strlen( $val ) > 80 ) {
        $s .= jlf_var_export( $val, $indent + 2 );
      } else {
        $s .= jlf_var_export( $val, -1 );
      }
    }
  } else {
    if( $indent >= 0 ) {
      $s = "\n".str_repeat( ' >', $indent );
    } else {
      $s = '';
    }
    if( isarray( $var ) ) {
      $s .= html_tag( 'span', 'bold black', 'EMPTY ARRAY' );
    } else if( $var === NULL ) {
      $s .= html_tag( 'span', 'bold black', 'NULL' );
    } else if( $var === FALSE ) {
      $s .= html_tag( 'span', 'bold black', 'FALSE' );
    } else if( $var === TRUE ) {
      $s .= html_tag( 'span', 'bold black', 'TRUE' );
    } else if( isstring( $var ) || isnumeric( $var ) ) {
      $var = (string)( $var );
      if( $var === '' ) {
        $s .= html_tag( 'span', 'bold black', 'EMPTY STRING' );
      } else {
        $newline = $s;
        $s = '';
        while( strlen( $var ) > 0 ) {
          $s .= $newline . jlf_string_export( substr( $var, 0, 80 ) );
          $var = substr( $var, 80 );
        }
      }
    } else {
      $s .= html_tag( 'span', 'bold black', '?UNKNOWN?' );
    }
  }
  return $s;
}

function debug( $var, $comment = '', $level = DEBUG_LEVEL_KEY ) {
  global $header_printed, $debug_messages;
  if( $level < $GLOBALS['debug_level'] ) { 
    return;
  }
  $s = html_tag( 'pre', 'warn black nounderline smallskips solidbottom solidtop' );
  if( $comment ) {
    $s .= jlf_var_export( $comment, 0 );
  }
  $s .= jlf_var_export( $var, 0 );
  $s .= html_tag( 'pre', false );
  if( $header_printed )
    echo $s;
  else
    $debug_messages[] = $s;
}


function flush_messages( $messages, $class = 'alert' ) {
  foreach( $messages as $s ) {
    open_div( $class, $s );
  }
}

function flush_debug_messages() {
  global $debug_messages, $header_printed;
  if( ! $header_printed )
    echo DOCTYPE_BLUBBER;
  // flush_messages( $debug_messages, 'warn' );
  $debug_messages = array();
}

function flush_problems() {
  global $problems;
  flush_messages( $problems, 'alert' );
  $problems = array();
}

function flush_info_messages() {
  global $info_messages;
  flush_messages( $info_messages, 'ok' );
  $info_messages = array();
}

function error( $msg = 'error', $class = 'error' ) {
  static $in_error = false;
  echo DOCTYPE_BLUBBER;
  if( ! $in_error ) { // avoid infinite recursion
    $in_error = true;
    flush_debug_messages();
    echo 'error';
    $stack = debug_backtrace();
    open_div( 'warn medskips hfill' );
      open_fieldset( '', 'error', 'on' );
        var_export( $stack );
        debug( $stack, $msg );
      close_fieldset();
      // open_span( 'qquad', inlink( 'self', 'img=,text=weiter...' ) );
    close_div();
    logger( $msg, $class, $stack );
    close_all_tags();
  }
  die();
}

function need( $exp, $comment = 'problem' ) {
  if( ! $exp ) {
    error( 'assertion failed: ' . $comment, 'assert' );
  }
  return true;
}

function fail_if_readonly() {
  return need( ! gdefault( 'readonly', false ), 'database in readonly mode - operation not allowed' );
}

function logger( $note, $event = 'notice', $stack = '' ) {
  global $login_sessions_id, $jlf_db_handle;
  if( ! $jlf_db_handle )
    return false;

  if( is_array( $stack ) )
    $stack = jlf_var_export( $stack, 0 );

  return sql_insert( 'logbook', array(
    'sessions_id' => gdefault( 'login_sessions_id ', '0' )
  , 'thread' => gdefault( 'thread', '0' )
  , 'window' => gdefault( 'window', '0' )
  , 'script' => gdefault( 'script', '0' )
  , 'parent_thread' => gdefault( 'parent_thread', '0' )
  , 'parent_window' => gdefault( 'parent_window', '0' )
  , 'parent_script' => gdefault( 'parent_script', '0' )
  , 'event' => $event
  , 'note' => $note
  , 'stack' => $stack
  ) );
}

?>
