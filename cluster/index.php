<?php

$window = 'menu';     // preliminary settings for login script, or very early errors
$window_id = 'main';
require_once('code/common.php');

$user = getenv('user');
$auth = getenv('auth');

// require_once( 'code/login.php' );
$angemeldet = ( $auth == 'ssl' );
if( ! $angemeldet ) {
  div_msg( 'warn', "Bitte erst <a href='/foodsoft/index.php'>Anmelden...</a>" );
  exit();
}


get_http_var( 'window', 'w', 'menu', true );         // eigentlich: name des skriptes
get_http_var( 'window_id', 'w', 'main', true );   // ID des browserfensters
// setWikiHelpTopic( "foodsoft:$window" );

include('head.php');
if( is_readable( "windows/$window.php" ) ) {
  include( "windows/$window.php" );
} else {
  div_msg( 'warn', "Ung&uuml;ltiger Bereich: $window" );
  include('windows/menu.php');
}

open_table( 'footer', "width='100%'" );
  open_td( '', '', "server: <kbd>". getenv('HOSTNAME') .'/'. getenv('server') ."</kbd> | user: <b>$user</b> | auth: <b>$auth</b>" );
  open_td( 'right', '', "$mysqljetzt utc" );
close_table();

// force new iTAN (this form must still be submittable after any other):
//
get_itan( true );
open_form( 'name=update_form', 'action=nop,message=' );

?>
