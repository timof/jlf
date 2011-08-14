<?php
//
// low-level error handling and logging
//
// these functions may attempt to log to the database, but must be safe to call even
// if no db is available!
//

define('DEBUG_LEVEL_NEVER', 5);
define('DEBUG_LEVEL_ALL', 4);
define('DEBUG_LEVEL_MOST', 3);
define('DEBUG_LEVEL_IMPORTANT', 2); // all UPDATE and INSERT statements should have level important
define('DEBUG_LEVEL_KEY', 1);
define('DEBUG_LEVEL_NONE', 0);

$debug_level = DEBUG_LEVEL_IMPORTANT; // preliminary value - to be determined from table leitvariable
$debug_messages = array();

function prettydump( $var, $comment = '' ) {
  global $header_printed, $debug_messages;
  $s = "<div class='warn'>$comment<pre><br>[" .htmlspecialchars( var_export( $var, true ) ) . "]<br></pre></div>";
  if( $header_printed )
    echo $s;
  else
    $debug_messages[] = $s;
}

function debug( $msg, $level = DEBUG_LEVEL_ALL ) {
  if( $level <= $GLOBALS['debug_level'] ) {
    open_div( 'alert', htmlspecialchars( $msg ) );
  }
}

function flush_debug_messages() {
  global $debug_messages;
  foreach( $debug_messages as $s ) {
    echo $s;
  }
  $debug_messages = array();
}

function error( $string ) {
  static $in_error = false;
  flush_debug_messages();
  if( ! $in_error ) { // avoid infinite recursion
    $in_error = true;
    $stack = debug_backtrace();
    open_div( 'warn' );
      smallskip();
      open_fieldset( '', "error", 'off' );
        echo "<pre><br>[" .htmlspecialchars($string)."]<br>". htmlspecialchars( var_export( $stack, true ) ) . "</pre>";
      close_fieldset();
      open_span( 'qquad', inlink( 'self', 'img=,text=weiter...' ) );
      bigskip();
    close_div();
    logger( $string, 'error', $stack );
    close_all_tags();
  }
  die();
}

function need( $exp, $comment = "problem" ) {
  static $in_need = false;
  if( ! $exp ) {
    flush_debug_messages();
    if( $in_need ) // avoid infinite recursion
      die( $comment );
    $in_need = true;
    $stack = debug_backtrace();
    open_div( 'warn' );
      smallskip();
      open_fieldset( '', htmlspecialchars( "$comment" ), 'off' );
        echo "<pre>". htmlspecialchars( var_export( $stack, true ) ) . "</pre>";
      close_fieldset();
      open_span( 'qquad', inlink( 'self', 'img=,text=weiter...' ) );
      bigskip();
    close_div();
    logger( "assertion failed: $exp ($comment)", 'assert', $stack );
    die();
  }
  return true;
}

function fail_if_readonly() {
  global $readonly;
  if( isset( $readonly ) and $readonly ) {
    open_div( 'warn', 'Datenbank ist schreibgesch&uuml;tzt - Operation nicht m&ouml;glich!' );
    die();
  }
  return true;
}

function logger( $note, $event = 'notice', $stack = '' ) {
  global $login_sessions_id, $jlf_db_handle;
  if( ! $jlf_db_handle )
    return false;
  // prettydump( $note, 'logger:' );

  if( is_array( $stack ) )
    $stack = var_export( $stack, true );

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
