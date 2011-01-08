<?php

$window = 'menu';     // preliminary settings for login script or very early errors
$window_id = 'main';

require_once('code/common.php');

$problems = do_login();

if( $logged_in ) {

  get_http_var( 'window', 'w', 'menu', true );      // eigentlich: name des skriptes
  get_http_var( 'window_id', 'w', 'main', true );   // ID des browserfensters
  $session_vars = sql_get_session_vars( $login_sessions_id, $window, $window_id );
  // prettydump( $session_vars );
  include('code/head.php');
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
  sql_set_session_vars( $login_sessions_id, $self_fields, $window, $window_id );

} else {
  include('code/head.php');
  echo $problems;
  form_login();
}

open_table( 'footer', "width='100%'" );
  open_td( 'left', '', "server: <kbd>". getenv('HOSTNAME').'/'.getenv('server') ."</kbd> | user: <b>$login_uid</b> | auth: <b>$login_authentication_method</b>" );
  open_td( 'right', '', "$mysqljetzt utc" );
  if(0) {
    open_div();
      echo "<br>self_fields:";
      prettydump( $self_fields );
      echo "<br>filters:";
      prettydump( $filters );
    close_div();
  }
close_table();

?>
