<?php
//
// low-level error handling and logging
//
// these functions may attempt to log to the database, but must be safe to call if no db is available!
//

$debug_messages = array();
$info_messages = array();
$error_messages = array();

// get_error_id(): return unique error index so we can use += on array of problems:
// it is negative so mixing it with statememtcs like $problems[] = 'new problem'; will also almost work
//
function get_problem_id() {
  static $global_problem_counter = 0;
  return --$global_problem_counter;
}

function new_problem( $p ) {
  return $p ? array( get_problem_id() => $p ) : array();
}


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
        $cut_off = 0;
        if( strlen( $var ) > 200 ) {
          $cut_off = strlen( $var ) - 200;
          $var = substr( $var, 0, 200 );
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

function debug( $var, $comment = '', $level = LOG_LEVEL_NOTICE ) {
  global $debug_messages, $initialization_steps, $global_format, $deliverable;
  if( $level < $GLOBALS['debug_level'] ) { 
    return;
  }
  switch( $global_format ) {
    case 'html':
      if( $deliverable ) {
        echo "\n".UNDIVERT_OUTPUT_SEQUENCE."\n";
      }
      $s = html_tag( 'pre', 'left warn black nounderline smallskips solidbottom solidtop' );
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
    case 'csv':
      if( $deliverable ) {
        echo "\n".UNDIVERT_OUTPUT_SEQUENCE."\n";
      }
      // fallthrough
    case 'cli':
      if( $comment ) {
        echo ( isstring( $comment ) ? "\n> [$comment]" : jlf_var_export_cli( $comment, 0 ) );
      }
      echo jlf_var_export_cli( $var, 1 );
      echo "\n";
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

function flush_debug_messages( $opts = array() ) {
  global $debug_messages;
  $opts = parameters_explode( $opts );
  $opts['class'] = adefault( $opts, 'class', 'warn medskips' );
  flush_messages( $debug_messages, $opts );
  $debug_messages = array();
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
  flush_debug_messages();
  flush_error_messages();
  flush_info_messages();
}

function error( $msg, $flags = 0, $tags = 'error', $links = array() ) {
  global $initialization_steps, $debug, $H_SQ;
  static $in_error = false;
  if( ! $in_error ) { // avoid infinite recursion
    $in_error = true;
    if( isset( $initialization_steps['db_ready'] ) ) {
      mysql_query( 'ROLLBACK RELEASE' );
    }
    $stack = debug_backtrace();
    echo UNDIVERT_OUTPUT_SEQUENCE;
    switch( $GLOBALS['global_format'] ) {
      case 'html':
        if( isset( $initialization_steps['header_printed'] ) ) {
          if( $debug ) {
            open_div( 'warn medskips hfill' );
              open_fieldset( '', 'error' );
                debug( $stack, $msg, LOG_LEVEL_ERROR );
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
              debug( $stack, $msg, LOG_LEVEL_ERROR );
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
            debug( $stack, $msg, LOG_LEVEL_ERROR );
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
  // try to make sure error message is actually visible:
  open_javascript( "window.onresize = true; \$({$H_SQ}theOutbacks{$H_SQ}).style.position = {$H_SQ}static{$H_SQ}; " );
  die();
}

function need( $exp, $comment = 'Houston, we\'ve had a problem' ) {
  while( isarray( $comment ) ) {
    // if there are several fatal problems, just print the first one:
    $comment = reset( $comment );
  }
  if( ! $exp ) {
    error( $comment, LOG_FLAG_CODE | LOG_FLAG_DATA, 'assert' );
  }
  return true;
}

function fail_if_readonly() {
  return need( ! adefault( $GLOBALS, 'readonly', false ), 'database in readonly mode - operation not allowed' );
}

function logger( $note, $level, $flags, $tags = '', $links = array(), $stack = '' ) {
  global $login_sessions_id, $initialization_steps, $jlf_application_name, $jlf_application_instance;

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
  , 'application' => "$jlf_application_name-$jlf_application_instance"
  ) );
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


?>
