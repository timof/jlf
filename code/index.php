<?php

// reasonable defaults for very early scripts (in particular: login.php):
//
$script = 'menu';
$window = 'menu';
$thread = '1';

require_once('code/common.php');

$problems = handle_login();

if( $login_sessions_id ) {

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

  $jlf_persistent_vars['global']  = sql_retrieve_persistent_vars();
  $jlf_persistent_vars['user']    = sql_retrieve_persistent_vars( $login_uid );
  $jlf_persistent_vars['session'] = sql_retrieve_persistent_vars( $login_uid, $login_sessions_id );
  $jlf_persistent_vars['thread']  = sql_retrieve_persistent_vars( $login_uid, $login_sessions_id, $parent_thread );
  $jlf_persistent_vars['script']  = sql_retrieve_persistent_vars( $login_uid, $login_sessions_id, $parent_thread, $script );
  $jlf_persistent_vars['window']  = sql_retrieve_persistent_vars( $login_uid, $login_sessions_id, $parent_thread, '',      $window );
  $jlf_persistent_vars['view']    = sql_retrieve_persistent_vars( $login_uid, $login_sessions_id, $parent_thread, $script, $window );

  if( $parent_script === 'self' ) {
    $jlf_persistent_vars['self'] = sql_retrieve_persistent_vars( $login_uid, $login_sessions_id, $parent_thread, $script, $window, 1 );
  } else {
    $jlf_persistent_vars['self'] = array();
  }
  $jlf_persistent_vars['permanent'] = array(); // currently not used

  if( is_readable( "$jlf_application_name/common.php" ) ) {
    include( "$jlf_application_name/common.php" );
  }

  include('code/head.php');

  /////////////////////
  // thread support: check whether we are requested to fork:
  //
  init_global_var( 'action', 'w', 'http', 'nop' );
  if( $action === 'fork' ) {
    // find new thread id:
    // 
    $tmin = $mysql_now;
    $thread_unused = 0;
    for( $i = 1; $i <= 4; $i++ ) {
      if( $i == $thread )
        continue;
      $v = sql_retrieve_persistent_vars( $login_uid, $login_sessions_id, $i );
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
    // create fork_form: submission will start new thread; different thread will enforce new window:
    //
    $fork_form_id = open_form( array( 'thread' => $thread_unused ) );
    close_form();
    js_on_exit( " submit_form( '$fork_form_id' ); " );
    unset( $_POST['action'] );
    logger( "forking: $thread -> $thread_unused", 'fork' );
  }
  /////////////////////

  switch( $login_authentication_method ) {
    case 'public':
      $path = "$jlf_application_name/public/$script.php";
      break;
    default:
      $path = "$jlf_application_name/windows/$script.php";
      break;
  }
  if( is_readable( $path ) ) {
    include( $path );
  } else {
    error( "invalid script: $script" );
  }

  // update_form: every page is supposed to have one.
  // scripts may declare a form to be the update form; if not, we insert one now:
  //
  if( ! $have_update_form ) {
    open_form( 'name=update_form' );
    close_form();
  }

  // all GET requests via load_url() and POST requests via submit_form() will pass current window scroll
  // position in paramater xoffs. restore position for 'self'-requests:
  //
  if( $parent_script === 'self' ) {
    // restore scroll position:
    init_global_var( 'offs', '', 'http', '0x0' );
    $offs = explode( 'x', $offs );
    $xoff = adefault( $offs, 0, 0 );
    $yoff = adefault( $offs, 1, 0 );
    js_on_exit( "window.scrollTo( $xoff, $yoff ); " );
  }

  set_persistent_var( 'thread_atime', 'thread', $mysql_now );
  sql_store_persistent_vars( $jlf_persistent_vars['self'],    $login_uid, $login_sessions_id, $thread, $script, $window, 1 );
  sql_store_persistent_vars( $jlf_persistent_vars['view'],    $login_uid, $login_sessions_id, $thread, $script, $window );
  sql_store_persistent_vars( $jlf_persistent_vars['script'],  $login_uid, $login_sessions_id, $thread, $script );
  sql_store_persistent_vars( $jlf_persistent_vars['window'],  $login_uid, $login_sessions_id, $thread, '',      $window );
  sql_store_persistent_vars( $jlf_persistent_vars['thread'],  $login_uid, $login_sessions_id, $thread );
  sql_store_persistent_vars( $jlf_persistent_vars['session'], $login_uid, $login_sessions_id );
  sql_store_persistent_vars( $jlf_persistent_vars['user'],    $login_uid );
  sql_store_persistent_vars( $jlf_persistent_vars['global'] );

} else {
  include('code/head.php');
  echo $problems;
  form_login();
}

open_table( 'footer', "width='100%'" );
  open_td( 'left' );
    echo "server: <kbd>". getenv('HOSTNAME').'/'.getenv('server') ."</kbd> | ";
    if( $logged_in )
      echo "user: <b>$login_uid</b> ";
    else
      echo "(anonymous access)";
    echo " | auth: <b>$login_authentication_method</b>";
  close_td();
  $version = file_exists( 'version.txt' ) ? file_get_contents( 'version.txt' ) : 'unknown';
  open_td( 'center', '', "powered by <a href='http://github.com/timof/jlf'>jlf</a> version $version" );
  open_td( 'right', '', "$mysql_now utc" );
  if( 0 ) {
    echo "<!-- thread/window/script: [$thread/$window/$script] -->";
    echo "<!-- parents: [$parent_thread/$parent_window/$parent_script] -->";
  }
  if( 0 )
    open_javascript( "document.write( 'current window name: ' + window.name ); " );
  if( 0 )
    prettydump( $js_on_exit_array );
  if( 0 )
    prettydump( $jlf_persistent_vars, 'jlf_persistent_vars' );
  if( 0 )
    prettydump( $filters, 'filters' );
close_table();

?>
