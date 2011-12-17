<?php

// reasonable defaults for very early scripts (in particular: login.php):
//
$script = 'menu';
$window = 'menu';
$thread = '1';
$debug = 1; // good choice in case of very early errors

// activate this line to see very early errors:
//
// echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n\n";

require_once('code/common.php');

$problems = handle_login();

if( $login_sessions_id ) {

  init_var( 'me', array( 'global' => true, 'sources' => 'http' , 'default' => '1,menu,menu' ) );
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

  js_on_exit( sprintf( "window.name = {$H_SQ}%s{$H_SQ};", js_window_name( $window, $thread ) ) );

  $jlf_persistent_vars['global']  = sql_retrieve_persistent_vars();
  $jlf_persistent_vars['user']    = sql_retrieve_persistent_vars( $login_people_id );
  $jlf_persistent_vars['session'] = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id );
  $jlf_persistent_vars['thread']  = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id, $parent_thread );
  $jlf_persistent_vars['script']  = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id, $parent_thread, $script );
  $jlf_persistent_vars['window']  = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id, $parent_thread, '',      $window );
  $jlf_persistent_vars['view']    = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id, $parent_thread, $script, $window );

  if( $parent_script === 'self' ) {
    $jlf_persistent_vars['self'] = sql_retrieve_persistent_vars( $login_people_id, $login_sessions_id, $parent_thread, $script, $window, 1 );
  } else {
    $jlf_persistent_vars['self'] = array();
  }
  $jlf_persistent_vars['permanent'] = array(); // currently not used

  // debug: if set, will also be included in every url!
  init_var( 'debug', 'global,pattern=u,sources=http window,default=0,set_scopes=window' );

  if( is_readable( "$jlf_application_name/common.php" ) ) {
    include( "$jlf_application_name/common.php" );
  }

  // head.php:
  // - print <doctype> babble and html <head> section
  // - open update form
  // - output actual page head
  //
  include('code/head.php');

  init_var( 'action', 'global,pattern=w,default=nop,sources=http' );

  /////////////////////
  // thread support: check whether we are requested to fork:
  //
  if( $action === 'fork' ) {
    // find new thread id:
    // 
    $tmin = $mysql_now;
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
      // echo "($i / $t / $tmin / $thread_unused) ";
    }
    if( ! $thread_unused ) {
      $thread_unused = ( $thread == 4 ? 1 : $thread + 1 );
      logger( "last resort: [$thread_unused] ", 'fork' );
    }
    // create fork_form: submission will start new thread; different thread will enforce new window:
    //
    $fork_form_id = open_form( "thread=$thread_unused", '', 'hidden' );
    js_on_exit( " submit_form( {$H_SQ}$fork_form_id{$H_SQ} ); " );
    unset( $_GET['action'] );
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

  // all GET requests via load_url() and POST requests via submit_form() will pass current window scroll
  // position in paramater xoffs. restore position for 'self'-requests:
  //
  if( $parent_script === 'self' ) {
    // restore scroll position:
    $offs_field = init_var( 'offs', 'sources=http,default=0x0' );
    $offs = explode( 'x', $offs_field['value'] );
    $xoff = adefault( $offs, 0, 0 );
    $yoff = adefault( $offs, 1, 0 );
    js_on_exit( "window.scrollTo( $xoff, $yoff ); " );
  }

  set_persistent_var( 'thread_atime', 'thread', $utc );
  sql_store_persistent_vars( $jlf_persistent_vars['self'],    $login_people_id, $login_sessions_id, $thread, $script, $window, 1 );
  sql_store_persistent_vars( $jlf_persistent_vars['view'],    $login_people_id, $login_sessions_id, $thread, $script, $window );
  sql_store_persistent_vars( $jlf_persistent_vars['script'],  $login_people_id, $login_sessions_id, $thread, $script );
  sql_store_persistent_vars( $jlf_persistent_vars['window'],  $login_people_id, $login_sessions_id, $thread, '',      $window );
  sql_store_persistent_vars( $jlf_persistent_vars['thread'],  $login_people_id, $login_sessions_id, $thread );
  sql_store_persistent_vars( $jlf_persistent_vars['session'], $login_people_id, $login_sessions_id );
  sql_store_persistent_vars( $jlf_persistent_vars['user'],    $login_people_id );
  sql_store_persistent_vars( $jlf_persistent_vars['global'] );

} else {
  $debug = 0;
  include('code/head.php');
  flush_problems();
  form_login();
}

open_table( 'footer,style=width:100%;' );
  open_td( 'left' );
    echo 'server: ' . html_tag( 'span', 'bold', getenv('HOSTNAME').'/'.getenv('server') ) . ' | ';
    if( $logged_in )
      echo 'user: ' . html_tag( 'span', 'bold', $login_uid );
    else
      echo '(anonymous access)';
    echo ' | auth: ' .html_tag( 'span', 'bold', $login_authentication_method );
  close_td();

  $lines = file( 'version.txt' );
  $version = "jlf version " . adefault( $lines, 1, '(unknown)' );
  if( ( $url = adefault( $lines, 0, '' ) ) ) {
    $version = html_tag( 'a', "href=$url", $version );
  }
  open_td( 'center', $version );
  open_td( 'right', "$mysql_now utc" );
  if( 1 ) {
    html_comment( "thread/window/script: [$thread/$window/$script]" );
    html_comment( "parents: [$parent_thread/$parent_window/$parent_script]" );
  }
  if( 0 )
    debug( $_POST, '_POST' );
  if( 0 )
    open_javascript( "document.write( {$H_SQ}current window name: {$H_SQ} + window.name ); " );
  if( 0 )
    debug( $js_on_exit_array );
  if( 0 )
    debug( $jlf_persistent_vars, 'jlf_persistent_vars' );
  if( 0 )
    debug( isset( $fields ) ? $fields : $f, 'fields' );

close_table();

// insert an invisible submit button to allow to submit the update_form by pressing ENTER:
open_span( 'nodisplay', html_tag( 'input', 'type=submit', false ) );

?>
