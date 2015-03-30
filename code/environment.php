<?php
//
// environment.php: include other scripts and prepare basic environment, up to db connection
//

// low-level functions for error handling: don't _require_ (but may use if available) database connection:
//
require_once('code/err_functions.php');

// general functions and constant definitions:
//
require_once('code/basic.php');
if( is_readable( "$jlf_application_name/basic.php" ) ) {
  require_once( "$jlf_application_name/basic.php" );
}

$request_method = $_SERVER['REQUEST_METHOD']; // GET or POST here (via http), or CLI in cli/cli_environment.php (via cli)
switch( $request_method ) {
  case 'GET':
    $_POST = array();
  case 'POST':
    break;
  default:
    error( 'REQUEST_METHOD not supported' );
}

require_once('code/global.php');

require_once('code/html.php');

if( $allow_setup_from ) {
  error( 'please deactivate setup.rphp in code/config.php!', LOG_FLAG_SYSTEM, 'config' );
  exit(1);
}

require_once('code/login.php');
init_login(); // initialize global user info (to state "not logged in (yet)")

// db structure definition:
//
require_once( "code/structure.php" );
if( is_readable( "$jlf_application_name/structure.php" ) ) {
  $jlf_tables = $tables;
  require_once( "$jlf_application_name/structure.php" );
  $tables = tree_merge( $jlf_tables, $tables );
  unset( $jlf_tables );
}

// `locks` and `canary` are dummy-tables used exclusively to get table locking right; they must
// never be actually used for data:
//
unset( $tables['locks'] );
unset( $tables['canary'] );

// from here on, we read subproject-specific scripts first and check for already-defined
// functions in the global scripts (as php cannot redefine functions)

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


if( is_readable( "$jlf_application_name/mysql.php" ) ) {
  require_once( "$jlf_application_name/mysql.php" );
}
require_once( 'code/mysql.php' );

if( is_readable( "$jlf_application_name/forms.php" ) ) {
  require_once( "$jlf_application_name/forms.php" );
}

// if( is_readable( "$jlf_application_name/ldap.php" ) )
//   require_once( "$jlf_application_name/ldap.php" );
// require_once('code/ldap.php');

if( is_readable( "$jlf_application_name/html.php" ) ) {
  require_once( "$jlf_application_name/html.php" );
}
// ... code/html is already read (above)

require_once( 'code/lists.php' );
// ...no application-specific addenda (yet)

if( is_readable( "$jlf_application_name/views.php" ) ) {
  require_once( "$jlf_application_name/views.php" );
} else {
  require_once( 'code/views.php' );
}

if( is_readable( "$jlf_application_name/gadgets.php" ) ) {
  require_once( "$jlf_application_name/gadgets.php" );
} else {
  require_once('code/gadgets.php');
}

switch( $global_format ) {
  case 'pdf':
    require_once('code/tex2pdf.php');
    break;
  default:
    break;
}


// open database connection:
//

// obtain default handle for actual db operations:
//
$jlf_db_handle = mysql_connect( $jlf_mysql_db_server, $jlf_mysql_db_user, $jlf_mysql_db_password, true );
if( $jlf_db_handle ) {
  if( ! mysql_select_db( $jlf_mysql_db_name, $jlf_db_handle ) ) {
    $jlf_db_handle = false;
  }
}

// obtain a second handle to be used as a semaphore:
//
$jlf_lock_handle = mysql_connect( $jlf_mysql_db_server, $jlf_mysql_db_user, $jlf_mysql_db_password, true );
if( $jlf_lock_handle ) {
  if( ! mysql_select_db( $jlf_mysql_db_name, $jlf_lock_handle ) ) {
    $jlf_lock_handle = false;
  }
}

if( ( ! $jlf_db_handle ) || ( ! $jlf_lock_handle ) ) {
  error( 'database error: connection to database server failed', LOG_FLAG_SYSTEM, 'config' );
  exit(2);
}
need( mysql_query( 'SET autocommit=0', $jlf_db_handle ), 'failed: sql: SET autocommit=0 for db handle' );
need( mysql_query( 'SET autocommit=0', $jlf_lock_handle ), 'failed: sql: SET autocommit=0 for lock handle' );

$initialization_steps['db_ready'] = true;


// read more config from table:
//
require_once('code/leitvariable.php');
if( is_readable( "$jlf_application_name/leitvariable.php" ) ) {
  $jlf_leitvariable = $leitvariable;
  require_once( "$jlf_application_name/leitvariable.php" );
  $leitvariable = tree_merge( $jlf_leitvariable, $leitvariable );
  unset( $jlf_leitvariable );
}

sql_transaction_boundary( 'leitvariable' );
$dbresult = sql_query( 'leitvariable', array( 'selects' => 'name, value', 'key_col' => 'name', 'val_col' => 'value', 'authorized' => 1 ) );
sql_transaction_boundary();

foreach( $leitvariable as $name => $props ) {
  $dbkey = "$name-" . ( adefault( $props, 'per_application' ) ? $jlf_application_name : '*' ); 
  if( adefault( $props, 'readonly' ) || ! isset( $dbresult[ $dbkey ] ) ) {
    $$name = $props['default'];
  } else {
    $$name = $dbresult[ $dbkey ];
  }
}
need( $jlf_application_instance === $db_application_instance, 'application instance: mismatch - accessing the wrong db?' );

require_once('code/robots.php');

// if( function_exists( 'update_database' ) ) {
//   global $database_version;
//   $version_old = $database_version;
//   menatwork(); // locking everything on every call is awfully inefficient!!!
//   sql_transaction_boundary( false ); // write-lock global semaphore
//   update_database();
// }


?>
