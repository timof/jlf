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

// debug( $_SERVER['SCRIPT_URL'], 'SCRIPT_URL' );

// debug( $_POST, '_POST' );

// POST parameter l is treated special: we evaluate and sanitize it early and store into $login; this can be
// used to pass small amounts of data e.g. to the code handling login, before a session is established:
//
if( isset( $_POST['l'] ) ) {
  $login = $_POST['l'];
  unset( $_POST['l'] );
  if( ! preg_match( '/^[A-Za-z_]{1,16}$/', $login ) )
    $login = '';
} else {
  $login = '';
}
switch( $login ) {
  case 'Lcsv':
    $global_format = 'csv';
    $login = '';
    break;
  case 'Lpdf':
    $global_format = 'pdf';
    $login = '';
    break;
  default:
    $global_format = 'html';
}

// debug( $login, 'index: login' );

handle_login();

if( function_exists( 'init_session' ) ) {
  init_session( $login_sessions_id );
}

if( $login_sessions_id ) {

  init_var( 'me', array( 'global' => true, 'sources' => 'http', 'default' => 'menu,menu,1' ) );

  $me = explode( ',', $me );
  $script = adefault( $me, 0, 'menu' );
  $window = adefault( $me, 1, 'menu' );
  $thread = adefault( $me, 2, '1' );
  need( preg_match( '/^[1-4]$/', $thread ) );
  $parent_script = adefault( $me, 3, $script );
  $parent_script or $parent_script = $script;
  $parent_window = adefault( $me, 4, $window );
  $parent_window or $parent_window = $window;
  $parent_thread = adefault( $me, 5, $thread );
  $parent_thread or $parent_thread = $thread;
  need( preg_match( '/^[1-4]$/', $parent_thread ) );

  $script_basename = basename( $_SERVER['SCRIPT_URL'] ); // SCRIPT_URL: script relative to document root
  if( $script_basename == 'index.php' ) { // SCRIPT_URL: script relative to document root
    switch( $global_format ) {
      case 'html':
        switch( $window ) {
          case 'IFRAME': // complete html, but no browser window
            $global_context = CONTEXT_IFRAME;
            break;
          case 'DIV':  // html fragment only
            $global_context = CONTEXT_DIV;
            break;
          default:  // complete html with :
            $global_context = CONTEXT_WINDOW;
            break;
        }
        break;
      case 'pdf':
        header( 'Content-Type: application/pdf' );
        $global_context = CONTEXT_DIV; // not really, but we don't need the html header and stuff either in the .pdf
        break;
      case 'csv':
        header( 'Content-Type: text/plain' );
        $global_context = CONTEXT_DIV;
        break;
    }
  } else if( $script_basename == 'get.rphp' ) {
    $global_context = CONTEXT_DOWNLOAD;
    $script = 'download';
  } else if( $script_basename == 'get.php' ) {
    $global_context = CONTEXT_DIV;
  } else {
    error( 'invalid script requested: ['. $script_basename . ']' );
  }

  if( $global_context >= CONTEXT_WINDOW )
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
  init_var( 'debug', 'global,type=u,sources=http window,default=0,set_scopes=window' );

  init_var( 'language', 'global,sources=http persistent,default=D,type=W1,pattern=/^[DE]$/,set_scopes=session' );

  if( is_readable( "$jlf_application_name/common.php" ) ) {
    include( "$jlf_application_name/common.php" );
  }

  // head.php:
  // - print <doctype> babble and html <head> section
  // - open update form
  // - output actual page head
  //
  include('code/head.php');

  init_var( 'action', 'global,type=w,default=nop,sources=http' );

  /////////////////////
  // thread support: check whether we are requested to fork:
  //
  if( ( $global_context >= CONTEXT_WINDOW ) && ( $action === 'fork' ) ) {
    // find new thread id:
    // 
    $tmin = $now_canonical;
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

  if( $login == 'login' ) { // request: show paleolithic-style login form:
    form_login();
  } else {
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
    if( $global_context >= CONTEXT_IFRAME )
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
  if( $global_context >= CONTEXT_IFRAME ) {
    $debug = 0;
    setcookie( cookie_name(), 'probe', 0, '/' );
    include('code/head.php');
    flush_problems();
    form_login();
  } else {
    error( 'no public access to this item' );
  }
}

if( $global_context >= CONTEXT_WINDOW ) {
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
    open_td( 'right', "$now_mysql utc" );
    if( 0 ) {
      open_html_comment( "thread/window/script: [$thread/$window/$script]" );
      open_html_comment( "parents: [$parent_thread/$parent_window/$parent_script]" );
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
    if( 0 )
      debug( $login_sessions_id, 'login_sessions_id' );

  close_table();
}

if( $global_context >= CONTEXT_IFRAME ) {
  // insert an invisible submit button to allow to submit the update_form by pressing ENTER:
  open_span( 'nodisplay', html_tag( 'input', 'type=submit', NULL ) );
}

if( $login_sessions_id ) {
  if( getenv('robot') || ! $valid_cookie_received ) {
    // client is a spider or has cookies switched off - don't store session data:
    sql_delete( 'persistent_vars', array( 'sessions_id' => $login_sessions_id ) );
  }
}

if( substr( get_itan(), -2 ) == '00' ) {
  logger( 'starting garbage collection', 'maintenance' );
  sql_garbage_collection();
}

?>
