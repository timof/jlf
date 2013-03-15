<?php
//
// common.php: declare and set global variables, include other scripts, do early stuff:
//

error_reporting( E_ALL );

require_once('code/config.php');

require_once('code/global.php');

// low-level functions: don't _require_ (but may use if available) database connection:
//
require_once('code/err_functions.php');

require_once('code/basic.php');
if( is_readable( "$jlf_application_name/basic.php" ) ) {
  require_once( "$jlf_application_name/basic.php" );
}

require_once('code/html.php');

if( 0 and $allow_setup_from ) {
  error( 'please deactivate setup.php in code/config.php!', LOG_FLAG_SYSTEM, 'config' );
  exit(1);
}


// get to sane and well-defined state:
//
date_default_timezone_set('UTC');

// we take exactly _one_ wall clock reading per script run and store the result in $now_unix:
// $now_mysql is to be used instead of NOW() (in sql) and repeated calls of date() (in php), because:
//  - can be quoted (in sql)
//  - use same time everywhere during one script run
$now_unix = time();
$utc = $now_canonical = datetime_unix2canonical( $now_unix );
$current_year = substr( $utc, 0, 4 );
$current_month = substr( $utc, 4, 2 );
$current_day = substr( $utc, 6, 2 );
$today_canonical = substr( $utc, 0, 8 );
$today_mysql = date_canonical2weird( $today_canonical );
$now_mysql = $today_mysql . ' ' . time_canonical2weird( $now_canonical );


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

// from here, we read subproject-specific scripts first and check for already-defined
// functions in the global scripts (as php cannot redefine functions)
//
if( is_readable( "$jlf_application_name/inlinks.php" ) )
  require_once( "$jlf_application_name/inlinks.php" );
require_once('code/inlinks.php');
$cgi_get_vars = ( isset( $cgi_get_vars ) ? tree_merge( $jlf_cgi_get_vars, $cgi_get_vars ) : $jlf_cgi_get_vars );
$cgi_vars = ( isset( $cgi_vars ) ? tree_merge( $jlf_cgi_vars, $cgi_vars ) : $jlf_cgi_vars );
$cgi_vars = tree_merge( $cgi_vars, $cgi_get_vars );

foreach( $cgi_vars as $name => $var ) {
  $cgi_vars[ $name ] = jlf_complete_type( $var );
}

expand_table_macros(); // this is a function in structure.php, because it is also required by setup.rphp!

foreach( $tables as $name => $table ) {
  foreach( $table['cols'] as $col => $props ) {
    $tables[ $name ]['cols'][ $col ] = jlf_complete_type( $props );
  }
}

if( is_readable( "$jlf_application_name/views.php" ) )
  require_once( "$jlf_application_name/views.php" );
require_once( 'code/views.php' );

if( is_readable( "$jlf_application_name/mysql.php" ) )
  require_once( "$jlf_application_name/mysql.php" );
require_once( 'code/mysql.php' );

if( is_readable( "$jlf_application_name/forms.php" ) )
  require_once( "$jlf_application_name/forms.php" );

// if( is_readable( "$jlf_application_name/ldap.php" ) )
//   require_once( "$jlf_application_name/ldap.php" );
// require_once('code/ldap.php');

if( is_readable( "$jlf_application_name/html.php" ) )
  require_once( "$jlf_application_name/html.php" );
// ... code/html is already read (above)

if( is_readable( "$jlf_application_name/gadgets.php" ) )
  require_once( "$jlf_application_name/gadgets.php" );
require_once('code/gadgets.php');

switch( $global_format ) {
  case 'pdf':
    require_once('code/tex2pdf.php');
    break;
  default:
    break;
}


// application-specific code to be _executed_ for all goes into <application>/common.php, and will
// be read from index.php somewhat later, when $sessions_id, ... are available!
//
// if( is_readable( "$jlf_application_name/common.php" ) )
//   require_once( "$jlf_application_name/common.php" );


// open database connection:
//
$jlf_db_handle = mysql_connect( $jlf_mysql_db_server, $jlf_mysql_db_user, $jlf_mysql_db_password );
if( $jlf_db_handle ) {
  if( ! mysql_select_db( $jlf_mysql_db_name, $jlf_db_handle ) ) {
    $jlf_db_handle = false;
  }
}
if( ! $jlf_db_handle ) {
  error( 'database error: connection to database server failed', LOG_FLAG_SYSTEM, 'config' );
  exit(2);
}

// read more config from table:
//
require_once('code/leitvariable.php');
if( is_readable( "$jlf_application_name/leitvariable.php" ) ) {
  $jlf_leitvariable = $leitvariable;
  require_once( "$jlf_application_name/leitvariable.php" );
  $leitvariable = tree_merge( $jlf_leitvariable, $leitvariable );
  unset( $jlf_leitvariable );
}
$dbresult = mysql2array(
  mysql_query( "SELECT name, value FROM leitvariable WHERE application='$jlf_application_name-$jlf_application_instance'" )
, 'name', 'value'
);
foreach( $leitvariable as $name => $props ) {
  if( ( ( ! isset( $props['readonly'] ) ) || ( ! $props['readonly'] ) ) && isset( $dbresult[ $name ] ) ) {
    $$name = $dbresult[ $name ];
  } else {
    $$name = $props['default'];
  }
}

need( mysql_query( 'START TRANSACTION' ), 'sql: START TRANSACTION failed' );
$initialization_steps['db_ready'] = true;

if( function_exists( 'update_database' ) ) {
  global $database_version;
  $version_old = $database_version;
  update_database();
  if( $version_old != $database_version ) {
    need( mysql_query( 'COMMIT AND CHAIN' ), 'sql: COMMIT failed' );
  }
}

?>
