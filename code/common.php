<?php

error_reporting( E_ALL );

require_once('code/config.php');
if( 0 and $allow_setup_from ) {
  echo "<html><body> error: please deactivate <code>setup.php</code> in <code>code/config.php</code>!</body></html>";
  exit(1);
}

global $jlf_db_handle;
$jlf_db_handle = false;

// low-level functions: don't _require_ (but may use if available) database handle:
//
require_once('code/err_functions.php');
require_once('code/html.php');

// open database connection:
//
$jlf_db_handle = mysql_connect( $jlf_mysql_db_server, $jlf_mysql_db_user, $jlf_mysql_db_password );
if( $jlf_db_handle ) {
  if( ! mysql_select_db( $jlf_mysql_db_name, $jlf_db_handle ) ) {
    $jlf_db_handle = false;
  }
}
if( ! $jlf_db_handle ) {
  ?> <html><body><h1>database error</h1>connection to database server failed</body></html> <?php
  exit();
}

// read more config from table:
//
global $leitvariable;
require_once( "$jlf_application_name/leitvariable.php" );
foreach( $leitvariable as $name => $props ) {
  global $$name;
  $$name = $props['default'];
  if( isset( $props['readonly'] ) ? ( ! $props['readonly'] ) : true ) {
    $result = mysql_query( "SELECT * FROM leitvariable WHERE name='$name'" );
    if( $result and ( $row = mysql_fetch_array( $result ) ) ) {
      $$name = $row['value'];
    }
  }
}

global $mysqlheute, $mysqljetzt;
// $mysqljetzt: Alternative zu NOW(), Vorteile:
//  - kann quotiert werden
//  - in einem Skriptlauf wird garantiert immer dieselbe Zeit verwendet
$now = explode( ',' , date( 'Y,m,d,H,i,s' ) );
$mysqlheute = $now[0] . '-' . $now[1] . '-' . $now[2];
$mysqljetzt = $mysqlheute . ' ' . $now[3] . ':' . $now[4] . ':' . $now[5];


global $header_printed;
$header_printed = false;

// $jlf_persistent_vars_self: variable, die in der url uebergeben werden, werden hier gesammelt:
global $jlf_persistent_vars;
$jlf_persistent_vars = array();

// Benutzerdaten:
global $logged_in, $login_people_id, $login_sessions_id, $login_authentication_method;
$logged_in = false;
$login_sessions_id = 0;

require_once('code/login.php');
require_once( "$jlf_application_name/structure.php" );

require_once('code/basic.php');

foreach( $tables as $name => $table ) {
  global $jlf_defaults;
  foreach( $table['cols'] as $col => $props ) {
    if( ! isset( $props['pattern'] ) )
      $tables[$name]['cols'][$col]['pattern'] = 'h';
    if( ! isset( $props['default'] ) )
      $tables[$name]['cols'][$col]['default']
        = adefault( $jlf_defaults, $tables[$name]['cols'][$col]['pattern'], '' );
  }
}

if( is_readable( "$jlf_application_name/basic.php" ) )
  require_once( "$jlf_application_name/basic.php" );

require_once('code/inlinks.php');
if( is_readable( "$jlf_application_name/inlinks.php" ) )
  require_once( "$jlf_application_name/inlinks.php" );

if( is_readable( "$jlf_application_name/views.php" ) )
  require_once( "$jlf_application_name/views.php" );
require_once( 'code/views.php' );

if( is_readable( "$jlf_application_name/mysql.php" ) )
  require_once( "$jlf_application_name/mysql.php" );
require_once( 'code/mysql.php' );

if( is_readable( "$jlf_application_name/forms.php" ) )
  require_once( "$jlf_application_name/forms.php" );
require_once('code/forms.php');

if( is_readable( "$jlf_application_name/ldap.php" ) )
  require_once( "$jlf_application_name/ldap.php" );
require_once('code/ldap.php');

if( is_readable( "$jlf_application_name/html.php" ) )
  require_once( "$jlf_application_name/html.php" );
// ... code/html is already read (above)

if( is_readable( "$jlf_application_name/gadgets.php" ) )
  require_once( "$jlf_application_name/gadgets.php" );
require_once('code/gadgets.php');

// application-specific code to be _executed_ for all goes into common.php, and will
// be read from index.php somewhat later, when $sessions_id, ... are available!
//
// if( is_readable( "$jlf_application_name/common.php" ) )
//   require_once( "$jlf_application_name/common.php" );

if( function_exists( 'update_database' ) ) {
  update_database();
}

?>
