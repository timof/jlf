<?php
//
// common.php: declare and set global variables, include other scripts, do early stuff:
//

error_reporting( E_ALL );

require_once('code/config.php');

// low-level functions: don't _require_ (but may use if available) database handle:
//
require_once('code/err_functions.php');
require_once('code/html.php');


if( 0 and $allow_setup_from ) {
  error( 'please deactivate setup.php in code/config.php!' );
  exit(1);
}


// open database connection:
//
$jlf_db_handle = mysql_connect( $jlf_mysql_db_server, $jlf_mysql_db_user, $jlf_mysql_db_password );
if( $jlf_db_handle ) {
  if( ! mysql_select_db( $jlf_mysql_db_name, $jlf_db_handle ) ) {
    $jlf_db_handle = false;
  }
}
if( ! $jlf_db_handle ) {
  error( 'database error: connection to database server failed' );
  exit();
}

require_once('code/basic.php');
if( is_readable( "$jlf_application_name/basic.php" ) )
  require_once( "$jlf_application_name/basic.php" );

// read more config from table:
//
global $leitvariable;
require_once( "code/leitvariable.php" );
if( is_readable( "$jlf_application_name/leitvariable.php" ) ) {
  $jlf_leitvariable = $leitvariable;
  require_once( "$jlf_application_name/leitvariable.php" );
  $leitvariable = tree_merge( $jlf_leitvariable, $leitvariable );
  unset( $jlf_leitvariable );
}
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

global $mysql_today, $mysql_now;
// $mysql_now: to be used instead of NOW() (in sql) and repeated calls of date() (in php), because:
//  - can be quoted (in sql)
//  - use same time everywhere during one script run
$now = explode( ',' , gmdate( 'Y,m,d,H,i,s' ) );
$canonical_today = $now[0] . $now[1] . $now[2];
$mysql_today = $now[0] . '-' . $now[1] . '-' . $now[2];
$utc = $canonical_now = $canonical_today . '.' . $now[3] . $now[4] . $now[5];
$mysql_now = $mysql_today . ' ' . $now[3] . ':' . $now[4] . ':' . $now[5];


global $jlf_persistent_vars;
$jlf_persistent_vars = array();

require_once('code/login.php');
init_login(); // initialize global user info (to state "not logged in (yet)")

require_once( "code/structure.php" );
if( is_readable( "$jlf_application_name/structure.php" ) ) {
  $jlf_tables = $tables;
  require_once( "$jlf_application_name/structure.php" );
  $tables = tree_merge( $jlf_tables, $tables );
  unset( $jlf_tables );
}

foreach( $tables as $name => $table ) {
  foreach( $table['cols'] as $col => $props ) {
    if( ! isset( $props['pattern'] ) )
      $tables[ $name ]['cols'][ $col ]['pattern'] = 'h';
    if( ! isset( $props['default'] ) )
      $tables[ $name ]['cols'][ $col ]['default'] = jlf_pattern_default( $tables[$name]['cols'][$col]['pattern'], '' );
  }
}


// from here, we read subproject-specific scripts first and check for already-defined
// functions in the global scripts (as php cannot redefine functions)
//
if( is_readable( "$jlf_application_name/inlinks.php" ) )
  require_once( "$jlf_application_name/inlinks.php" );
require_once('code/inlinks.php');
$cgi_get_vars = ( isset( $cgi_get_vars ) ? tree_merge( $jlf_cgi_get_vars, $cgi_get_vars ) : $jlf_cgi_get_vars );
$cgi_vars = ( isset( $cgi_vars ) ? tree_merge( $jlf_cgi_vars, $cgi_vars ) : $jlf_cgi_vars );
$cgi_vars = tree_merge( $cgi_vars, $cgi_get_vars );

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
