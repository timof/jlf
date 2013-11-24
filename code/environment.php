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

require_once('code/global.php');

require_once('code/html.php');

if( 0 and $allow_setup_from ) {
  error( 'please deactivate setup.php in code/config.php!', LOG_FLAG_SYSTEM, 'config' );
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
need( mysql_query( 'SET autocommit=0' ), 'failed: sql: SET autocommit=0' );
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
// $dbresult = mysql2array( mysql_query( "SELECT name, value FROM leitvariable" ) , 'name', 'value' );
$dbresult = sql_query( 'leitvariable', array(
  'filters' => array( 'application' => array( '', $jlf_application_name ) )
, 'orderby' => 'application' // thus, per-application setting overrides global setting (if any)
, 'selects' => 'name, value'
, 'key_col' => 'name'
, 'val_col' => 'value'
) );
$sql_global_lock_id = sql_query( 'leitvariable', 'filters=name=global_lock,single_field=leitvariable_id' );
sql_transaction_boundary();

foreach( $leitvariable as $name => $props ) {
  if( adefault( $props, 'readonly' ) || ! isset( $dbresult[ $name ] ) ) {
    $$name = $props['default'];
  } else {
    $$name = $dbresult[ $name ];
  }
}
if( adefault( $_ENV, 'robot' ) ) {
  $insert_nonce_in_urls = 0;
}

// if( function_exists( 'update_database' ) ) {
//   global $database_version;
//   $version_old = $database_version;
//   menatwork(); // locking everything on every call is awfully inefficient!!!
//   sql_transaction_boundary( false ); // write-lock global semaphore
//   update_database();
// }


?>
