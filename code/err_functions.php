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

$debug_level = DEBUG_LEVEL_KEY; // preliminary value - to be determined from table leitvariable

$debug_messages = array();
$info_messages = array();
$problems = array();

// error_header(): issue minimal emergency html header, so we can print an error message:
//
function error_header() {
  global $jlf_application_name, $jlf_application_instance;

  if( header_printed() )
    return;

  open_tag( 'html' );
  open_tag( 'head' );
    echo html_tag( 'title', '', "$jlf_application_name $jlf_application_instance --- ERROR" );
  close_tag( 'head' );
  open_tag( 'body' );
}

function jlf_string_export( $s ) {
  $rv = '';
  for( $i = 0; $i < strlen( $s ); $i++ ) {
    $c = $s[ $i ];
    if( ord( $c ) >= 32 && ord( $c ) < 127 ) {
      $rv .= $c;
    } else {
      $rv .= html_tag( 'span', 'underline bluee quads', sprintf( '%02x', ord( $c ), 'nodebug' ) );
    }
  }
  return html_tag( 'span', 'nounderline bluee', $rv, 'nodebug' );
}

function jlf_var_export( $var, $indent = 0 ) {
  if( isarray( $var ) && ( count( $var ) > 0 ) ) {
    $s = '';
    foreach( $var as $key => $val ) {
      $s .= jlf_var_export( $key, $indent ) . html_tag( 'span', 'blackk', ' => ', 'nodebug' );
      if( isarray( $val ) ) {
        $s .= jlf_var_export( $val, $indent + 1 );
      } else if( isstring( $val ) && strlen( $val ) > 80 ) {
        $s .= jlf_var_export( $val, $indent + 1 );
      } else {
        $s .= jlf_var_export( $val, -1 );
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
    if( isarray( $var ) ) {
      $s .= html_tag( 'span', 'bold blackk', 'EMPTY ARRAY', 'nodebug' );
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
        while( strlen( $var ) > 0 ) {
          $s .= ( $newline . jlf_string_export( substr( $var, 0, 80 ) ) . html_tag( 'span', 'yelloww', '<', 'nodebug' ) );
          $var = substr( $var, 80 );
        }
      }
    } else {
      $s .= html_tag( 'span', 'bold blackk', '?UNKNOWN?', 'nodebug' );
    }
  }
  return $s;
}

function debug( $var, $comment = '', $level = DEBUG_LEVEL_KEY ) {
  global $debug_messages;
  if( $level < $GLOBALS['debug_level'] ) { 
    return;
  }
  $s = html_tag( 'pre', 'warn black nounderline smallskips solidbottom solidtop' );
  if( $comment ) {
    if( isstring( $comment ) ) {
      $s .= "\n$comment\n";
    } else {
      $s .= jlf_var_export( $comment, 0 );
    }
  }
  $s .= jlf_var_export( $var, 1 );
  $s .= html_tag( 'pre', false );
  if( header_printed() )
    echo $s;
  else
    $debug_messages[] = $s;
}


function flush_messages( $messages, $opts = array() ) {
  error_header();
  $opts = parameters_explode( $opts );
  if( ! isarray( $messages ) ) {
    $messages = array( $messages );
  }
  $class = adefault( $opts, 'class', 'warn' );
  $t = surrounding_tag();
  $tag = ( ( $t['tag'] == 'ul' ) ? 'li' : 'div' );
  foreach( $messages as $s ) {
    echo html_tag( $tag, "class=$class", $s );
  }
}

function flush_debug_messages( $opts = array() ) {
  global $debug_messages;
  $opts = parameters_explode( $opts );
  $opts['class'] = adefault( $opts, 'class', 'warn' );
  flush_messages( $debug_messages, $opts );
  $debug_messages = array();
}

function flush_problems( $opts = array() ) {
  global $problems;
  $opts = parameters_explode( $opts );
  $opts['class'] = adefault( $opts, 'class', 'problem' );
  flush_messages( $problems, $opts );
  $problems = array();
}

function flush_info_messages( $opts = array() ) {
  global $info_messages;
  $opts = parameters_explode( $opts );
  $opts['class'] = adefault( $opts, 'class', 'ok' );
  flush_messages( $info_messages, $opts );
  $info_messages = array();
}

function error( $msg = 'error', $class = 'error' ) {
  static $in_error = false;
  if( ! $in_error ) { // avoid infinite recursion
    $in_error = true;
    flush_debug_messages();
    $stack = debug_backtrace();
    open_div( 'warn medskips hfill' );
      open_fieldset( '', 'error', 'on' );
        debug( $stack, $msg, DEBUG_LEVEL_KEY );
      close_fieldset();
    close_div();
    logger( $msg, $class, $stack );
    close_all_tags();
  }
  die();
}

function need( $exp, $comment = 'problem' ) {
  if( isstring( $comment ) ) {
    $comment = "[$comment]";
  } else {
    $comment = jlf_var_export( $comment );
  }
  if( ! $exp ) {
    error( "assertion failed: $comment", 'assert' );
  }
  return true;
}

function fail_if_readonly() {
  return need( ! adefault( $GLOBALS, 'readonly', false ), 'database in readonly mode - operation not allowed' );
}

function logger( $note, $event = 'notice', $stack = '' ) {
  global $login_sessions_id, $jlf_db_handle;
  if( ! $jlf_db_handle )
    return false;

  if( is_array( $stack ) )
    $stack = jlf_var_export( $stack, 0 );

  return sql_insert( 'logbook', array(
    'sessions_id' => adefault( $GLOBALS, 'login_sessions_id ', '0' )
  , 'thread' => adefault( $GLOBALS, 'thread', '0' )
  , 'window' => adefault( $GLOBALS, 'window', '0' )
  , 'script' => adefault( $GLOBALS, 'script', '0' )
  , 'parent_thread' => adefault( $GLOBALS, 'parent_thread', '0' )
  , 'parent_window' => adefault( $GLOBALS, 'parent_window', '0' )
  , 'parent_script' => adefault( $GLOBALS, 'parent_script', '0' )
  , 'event' => $event
  , 'note' => $note
  , 'stack' => $stack
  ) );
}

?>
