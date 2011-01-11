<?php

$window = 'menu';     // preliminary settings for login script or very early errors
$window_id = 'main';

require_once('code/common.php');

$problems = do_login();

if( $logged_in ) {

  get_http_var( 'window', 'w', 'menu', true );         // eigentlich: name des skriptes
  get_http_var( 'window_id', 'w', 'main', true );      // ID des browserfensters
  get_http_var( 'parent_window', 'w', '' );
  get_http_var( 'parent_window_id', 'w', $window_id ); // nur fuer fork - meistens $window_id!
  if( preg_match( '/_B/', $window_id ) ) {
    $session_branch = preg_replace( '/^.*_B(.*)$/', '\1', $window_id );
    $base_window_id = preg_replace( '/_B.*$/', '', $window_id );
  } else {
    $session_branch = '';
    $base_window_id = $window_id;
  }
  $session_vars = array_merge(
    sql_get_session_vars( $login_sessions_id, '', '' )
  , sql_get_session_vars( $login_sessions_id, $window, $parent_window_id )
  );
  if( $parent_window == 'self' ) {
    $session_vars = array_merge(
      $session_vars
    , sql_get_session_vars( $login_sessions_id, 'S_'.$window, $parent_window_id )
    );
  }

  // prettydump( $session_vars );
  include('code/head.php');

  // check whether we are requested to fork. strategy when forking:
  //   - 
  get_http_var( 'action', 'w', '' );
  if( $action == 'fork' ) {
    $tmin = $mysqljetzt;
    $bmin = 0;
    for( $i = 1; $i <= 4; $i++ ) {
      if( $i == $session_branch )
        continue;
      $t = adefault( $session_vars, 'branch_atime_'.$i, 0 );
      // echo "<!-- ($i,$t,$tmin) -->";
      if( $t < $tmin ) {
        $tmin = $t;
        $bmin = $i;
      }
    }
    if( ! $bmin ) {
      $bmin = ( $session_branch == 4 ? 1 : $session_branch + 1 );
      echo "<!-- bmin: last resort: [$bmin] -->";
    }
    $fork_form_id = open_form( array( 'parent_window_id' => $window_id, 'window_id' => $base_window_id .'_B' . $bmin ) );
    close_form();
    js_on_exit( "submit_form( 'form_$fork_form_id' );" );
    unset( $_POST['action'] );
  }

  if( is_readable( "$jlf_application_name/windows/$window.php" ) ) {
    include( "$jlf_application_name/windows/$window.php" );
  } else {
    div_msg( 'warn', "invalid window: $window" );
    include( "$jlf_application_name/windows/menu.php" );
  }

  if( ! $have_update_form ) {
    open_form( 'name=update_form', 'action=nop,message=0' );
    close_form();
  }
  if( $session_branch ) {
    $jlf_session_fields[ 'branch_atime_'.$session_branch ] = $mysqljetzt;
  }
  sql_set_session_vars( $login_sessions_id, $self_fields, 'S_'.$window, $window_id );
  sql_set_session_vars( $login_sessions_id, $jlf_window_fields, $window, $window_id );
  sql_set_session_vars( $login_sessions_id, $jlf_session_fields, '', '' );

} else {
  include('code/head.php');
  echo $problems;
  form_login();
}

open_table( 'footer', "width='100%'" );
  open_td( 'left', '', "server: <kbd>". getenv('HOSTNAME').'/'.getenv('server') ."</kbd> | user: <b>$login_uid</b> | auth: <b>$login_authentication_method</b>" );
  open_td( 'right', '', "$mysqljetzt utc" );
  echo "<!-- window_id: $window_id -->";
  echo "<!-- session_branch: $session_branch -->";
  echo "<!-- base_window_id: $base_window_id -->";
  echo "<!-- parent_window_id: $parent_window_id -->";
  if(0) {
    open_div();
      echo "<br>self_fields:";
      prettydump( $self_fields );
      echo "<br>filters:";
      prettydump( $filters );
      echo "<br>jlf_session_fields:";
      prettydump( $jlf_session_fields );
      echo "<br>jlf_window_fields:";
      prettydump( $jlf_window_fields );
    close_div();
  }
close_table();

?>
