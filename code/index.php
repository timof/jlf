<?php

// safe defaults for very early scripts (in particular: login.php):
//
$script = 'menu';
$window = 'menu';
$thread = '1';

require_once('code/common.php');

$problems = do_login();

if( $logged_in ) {

  init_global_var( 'me', '', 'http', '1,menu,menu' );
  $me = explode( ',', $me );
  $thread = adefault( $me, 0, '1' );
  $window = adefault( $me, 1, 'menu' );
  $script = adefault( $me, 2, 'menu' );
  need( preg_match( '/^[1-4]$/', $thread ) );
  $parent_thread = adefault( $me, 3, $thread );
  $parent_thread or $parent_thread = $thread;
  $parent_window = adefault( $me, 4, $window );
  $parent_window or $parent_window = $window;
  $parent_script = adefault( $me, 5, $script );
  $parent_script or $parent_script = $script;
  need( preg_match( '/^[1-4]$/', $parent_thread ) );

  js_on_exit( sprintf( "window.name = '%s';", js_window_name( $window, $thread ) ) );

  $jlf_persistent_vars['session'] = sql_retrieve_persistent_vars( $login_sessions_id );
  $jlf_persistent_vars['thread'] = sql_retrieve_persistent_vars( $login_sessions_id, $parent_thread );
  $jlf_persistent_vars['script'] = sql_retrieve_persistent_vars( $login_sessions_id, $parent_thread, $script );
  $jlf_persistent_vars['window'] = sql_retrieve_persistent_vars( $login_sessions_id, $parent_thread, '', $window );

  if( $parent_script == 'self' ) {
    $jlf_persistent_vars['self'] = sql_retrieve_persistent_vars( $login_sessions_id, $parent_thread, $script, $window, 1 );
  } else {
    $jlf_persistent_vars['self'] = array();
  }
  $jlf_persistent_vars['permanent'] = array(); // currently not used

  if( is_readable( "$jlf_application_name/common.php" ) ) {
    include( "$jlf_application_name/common.php" );
  }

  include('code/head.php');

  // check whether we are requested to fork:
  //
  init_global_var( 'action', 'w', 'http', 'nop' );
  if( $action == 'fork' ) {
    $tmin = $mysqljetzt;
    $thread_unused = 0;
    for( $i = 1; $i <= 4; $i++ ) {
      if( $i == $thread )
        continue;
      $v = sql_retrieve_persistent_vars( $login_sessions_id, $i );
      $t = adefault( $v, 'thread_atime', 0 );
      if( $t < $tmin ) {
        $tmin = $t;
        $thread_unused = $i;
      }
      echo "($i / $t / $tmin / $thread_unused) ";
    }
    if( ! $thread_unused ) {
      $thread_unused = ( $thread == 4 ? 1 : $thread + 1 );
      logger( "last resort: [$thread_unused] ", 'fork' );
    }
    $fork_form_id = open_form( array( 'thread' => $thread_unused ) );
    close_form();
    js_on_exit( "submit_form( 'form_$fork_form_id' );" );
    unset( $_POST['action'] );
    logger( "forking: $thread -> $thread_unused", 'fork' );
  }

  if( is_readable( "$jlf_application_name/windows/$script.php" ) ) {
    include( "$jlf_application_name/windows/$script.php" );
  } else {
    error( "invalid script: $script" );
  }

  if( ! $have_update_form ) {
    open_form( 'name=update_form', 'action=nop,message=0' );
    close_form();
  }
  set_persistent_var( 'thread_atime', 'thread', $mysqljetzt );
  sql_store_persistent_vars( $login_sessions_id, $jlf_persistent_vars['self'], $thread, $script, $window, 1 );
  sql_store_persistent_vars( $login_sessions_id, $jlf_persistent_vars['script'], $thread, $script );
  sql_store_persistent_vars( $login_sessions_id, $jlf_persistent_vars['window'], $thread, '', $window );
  sql_store_persistent_vars( $login_sessions_id, $jlf_persistent_vars['thread'], $thread );
  sql_store_persistent_vars( $login_sessions_id, $jlf_persistent_vars['session'] );

} else {
  include('code/head.php');
  echo $problems;
  form_login();
}

open_table( 'footer', "width='100%'" );
  open_td( 'left', '', "server: <kbd>". getenv('HOSTNAME').'/'.getenv('server') ."</kbd> | user: <b>$login_uid</b> | auth: <b>$login_authentication_method</b>" );
  $version = file_exists( 'version.txt' ) ? file_get_contents( 'version.txt' ) : 'unknown';
  open_td( 'center', '', "version: $version" );
  open_td( 'right', '', "$mysqljetzt utc" );
  echo "<!-- thread/window/script: [$thread/$window/$script] -->";
  echo "<!-- parents: [$parent_thread/$parent_window/$parent_script] -->";
  if(0) {
    open_div();
      prettydump( $jlf_persistent_vars, 'jlf_persistent_vars' );
      prettydump( $filters, 'filters' );
    close_div();
  }
close_table();

?>
