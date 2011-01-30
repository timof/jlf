<?php
//
// low-level error handling and logging
//
// these functions may attempt to log to the database, but must be safe to call even
// if no db is available!
//

define('LEVEL_NEVER', 5);
define('LEVEL_ALL', 4);
define('LEVEL_MOST', 3);
define('LEVEL_IMPORTANT', 2); // all UPDATE and INSERT statements should have level important
define('LEVEL_KEY', 1);
define('LEVEL_NONE', 0);

// LEVEL_CURRENT: alle sql-aufrufe bis zu diesem level werden angezeigt:
$_SESSION['LEVEL_CURRENT'] = LEVEL_NONE;

function prettydump( $var, $comment = '' ) {
  echo "<div class='warn'>$comment<pre><br>[" .htmlspecialchars( var_export( $var, true ) ) . "]<br></pre></div>";
}

function error( $string ) {
  static $in_error = false;
  if( ! $in_error ) { // avoid infinite recursion
    $in_error = true;
    $stack = debug_backtrace();
    open_div( 'warn' );
      smallskip();
      open_fieldset( '', '', "Fehler", 'off' );
        echo "<pre><br>[" .htmlspecialchars($string)."]<br>". htmlspecialchars( var_export( $stack, true ) ) . "</pre>";
      close_fieldset();
      open_span( 'qquad', '', inlink( 'self', 'img=,text=weiter...' ) );
      bigskip();
    close_div();
    logger( "error: $string [$stack]" );
    close_all_tags();
  }
  die();
}

function need( $exp, $comment = "problem" ) {
  static $in_need = false;
  if( ! $exp ) {
    if( $in_need ) // avoid infinite recursion
      die( $comment );
    $in_need = true;
    $stack = debug_backtrace();
    open_div( 'warn' );
      smallskip();
      open_fieldset( '', '', htmlspecialchars( "$comment" ), 'off' );
        echo "<pre>". htmlspecialchars( var_export( $stack, true ) ) . "</pre>";
      close_fieldset();
      open_span( 'qquad', '', inlink( 'self', 'img=,text=weiter...' ) );
      bigskip();
    close_div();
    logger( "assertion failed: $exp [$stack]" );
    die();
  }
  return true;
}

function fail_if_readonly() {
  global $readonly;
  if( isset( $readonly ) and $readonly ) {
    open_div( 'warn', '', 'Datenbank ist schreibgesch&uuml;tzt - Operation nicht m&ouml;glich!' );
    die();
  }
  return true;
}

function logger( $note ) {
  global $login_sessions_id, $jlf_db_handle;
  if( ! $jlf_db_handle )
    return false;
  return sql_insert( 'logbook', array( 'note' => $note, 'sessions_id' => $login_sessions_id ) );
}

?>
