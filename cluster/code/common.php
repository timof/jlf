<?php

// if( ! isset( $foodsoftpath ) ) {
//   $foodsoftpath = realpath( dirname( __FILE__ ) . '/../' );
// }
// global $foodsoftdir;   // noetig wenn aufruf aus wiki
// if( ! isset( $foodsoftdir ) ) {
//   $foodsoftdir = ereg_replace( '/[^/]+$', '', $_SERVER['SCRIPT_NAME'] );
//   // ausnahme: aufruf aus dem wiki heraus:
//   $foodsoftdir = ereg_replace( '/wiki$', '/foodsoft', $foodsoftdir );
// }

require_once('code/config.php');
// if( $allow_setup_from ) {
//  echo "<html><body> Fehler: bitte <code>setup.php</code> deaktivieren in <code>code/config.php</code>!</body></html>";
//  exit(1);
//}

// lese low-level Funktionen, die keine Datenbankverbindung benoetigen:
//
require_once('code/err_functions.php');
require_once('code/html.php');


// verbindung gleich aufbauen:
global $db_handle;
if( ! ( $db_handle = mysql_connect($db_server,$db_user,$db_pwd ) ) || !mysql_select_db( $db_name ) ) {
  echo "<html><body><h1>Datenbankfehler!</h1>Konnte keine Verbindung zur Datenbank herstellen... Bitte später nochmal versuchen.</body></html>";
  exit();
}

// die restliche konfiguration koennen wir aus der leitvariablen-tabelle lesen
// (skripte koennen dann persistente variable einfach speichern, aendern, und
//  an slave (im keller) uebertragen)
//
global $leitvariable;
require_once('leitvariable.php');
foreach( $leitvariable as $name => $props ) {
  global $$name;
  $result = mysql_query( "SELECT * FROM leitvariable WHERE name='$name'" );
  if( $result and ( $row = mysql_fetch_array( $result ) ) ) {
    $$name = $row['value'];
  } else {
    $$name = $props['default'];
  }
}

global $mysqlheute, $mysqljetzt;
// $mysqljetzt: Alternative zu NOW(), Vorteile:
//  - kann quotiert werden
//  - in einem Skriptlauf wird garantiert immer dieselbe Zeit verwendet
$now = explode( ',' , date( 'Y,m,d,H,i,s' ) );
$mysqlheute = $now[0] . '-' . $now[1] . '-' . $now[2];
$mysqljetzt = $mysqlheute . ' ' . $now[3] . ':' . $now[4] . ':' . $now[5];


// $self_fields: variable, die in der url uebergeben werden, werden hier gesammelt:
global $self_fields, $self_post_fields;
$self_fields = array();
$self_post_fields = array();

// Benutzerdaten:
global $angemeldet, $login_gruppen_id, $login_gruppen_name, $session_id;
$angemeldet = false;

require_once('structure.php');
require_once('code/views.php');
require_once('code/inlinks.php');
require_once('code/zuordnen.php');
require_once('code/forms.php');
require_once('code/ldap.php');

init_ldap_handle();

// update_database($database_version);

?>
