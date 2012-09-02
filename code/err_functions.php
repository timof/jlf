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


function jlf_string_export_cli( $s ) {
  $rv = '"';
  for( $i = 0; $i < strlen( $s ); $i++ ) {
    $c = $s[ $i ];
    if( ( ord( $c ) >= 32 ) && ( ord( $c ) < 127 ) && ( $c !== '\\' ) && ( $c !== '"' ) ) {
      $rv .= $c;
    } else {
      $rv .= sprintf( '\%02x', $ord( $c ) );
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
    if( isarray( $var ) ) {
      $s .= '[EMPTY ARRAY]';
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
          $s .= ( $newline . jlf_string_export_html( substr( $var, 0, 80 ) ) . html_tag( 'span', 'yelloww', '<', 'nodebug' ) );
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
  global $debug_messages, $initialization_steps, $global_format;
  if( $level < $GLOBALS['debug_level'] ) { 
    return;
  }
  switch( $global_format ) {
    case 'html':
      $s = html_tag( 'pre', 'warn black nounderline smallskips solidbottom solidtop' );
      if( $comment ) {
        $s .= ( isstring( $comment ) ? "\n$comment\n" : jlf_var_export_html( $comment, 0 ) );
      }
      $s .= jlf_var_export_html( $var, 1 );
      $s .= html_tag( 'pre', false );
      if( isset( $initialization_steps['header_printed'] ) ) {
        echo $s;
      } else {
        $debug_messages[] = $s;
      }
      break;
    case 'cli':
      if( $comment ) {
        echo ( isstring( $comment ) ? "\n> [$comment]" : jlf_var_export_cli( $comment, 0 ) );
      }
      echo jlf_var_export_cli( $var, 1 );
      break;
    default:
      return;
  }
}


function flush_messages( $messages, $opts = array() ) {
  global $global_format;

  $opts = parameters_explode( $opts );
  if( ! isarray( $messages ) ) {
    $messages = array( $messages );
  }
  switch( $global_format ) {
    case 'html':
      $class = adefault( $opts, 'class', 'warn' );
      $tag = adefault( $opts, 'tag', 'div' );
      header_view( '', 'ERROR: ' ); // is a nop if headers already printed
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

function error( $msg, $flags = 0, $tags = 'error', $links = array() ) {
  global $initialization_steps, $debug;
  static $in_error = false;
  if( ! $in_error ) { // avoid infinite recursion
    $in_error = true;
    if( isset( $initialization_steps['db_ready'] ) ) {
      mysql_query( 'ROLLBACK' );
    }
    $stack = debug_backtrace();
    switch( $GLOBALS['global_format'] ) {
      case 'html':
        if( isset( $initialization_steps['header_printed'] ) ) {
          if( $debug ) {
            open_div( 'warn medskips hfill' );
              open_fieldset( '', 'error' );
                debug( $stack, $msg, DEBUG_LEVEL_KEY );
              close_fieldset();
            close_div();
          } else {
            open_div( 'warn bigskips hfill', we('ERROR: ','FEHLER: ') . $msg );
          }
          close_all_tags();
          break;
        } else {
          flush_debug_messages(); // will also output emergency headers
          if( $debug ) {
            echo html_tag( 'div', 'warn medskips hfill' );
              debug( $stack, $msg, DEBUG_LEVEL_KEY );
            echo html_tag( 'div', false );
          } else {
            echo html_tag( 'div', 'warn bigskips hfill', 'ERROR: ' . $msg );
          }
          echo html_tag( 'body', false );
          echo html_tag( 'html', false );
        }
        break;
      case 'cli':
        if( $debug ) {
          echo "\nERROR:\n-----\n";
            debug( $stack, $msg, DEBUG_LEVEL_KEY );
          echo "\n-----\n";
        } else {
          echo "ERROR: [$msg]";
        }
        break;
      default:
        // can't do much here:
        echo "ERROR: [$msg]\n";
        break;
    }
    logger( $msg, LOG_LEVEL_ERROR, $flags, $tags, $links, $stack );
  }
  die();
}

function need( $exp, $comment = 'problem' ) {
  if( ! $exp ) {
    error( "assertion failed: $comment", LOG_FLAG_CODE | LOG_FLAG_DATA, 'assert' );
  }
  return true;
}

function fail_if_readonly() {
  return need( ! adefault( $GLOBALS, 'readonly', false ), 'database in readonly mode - operation not allowed' );
}

function logger( $note, $level, $flags, $tags = '', $links = array(), $stack = '' ) {
  global $login_sessions_id, $initialization_steps;

  if( ! isset( $initialization_steps['db_ready'] ) ) {
    return false;
  }

  if( $stack === true ) {
    $stack = debug_backtrace();
  }
  if( is_array( $stack ) ) {
    $stack = json_encode( $stack );
  }

  return sql_insert( 'logbook', array(
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
  , 'utc' => $GLOBALS['utc']
  ) );
}

?>
