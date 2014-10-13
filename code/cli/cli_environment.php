<?php // /code/cli/cli_environment.php

// require_once('code/config.php');

require_once('code/basic.php');
if( is_readable( "$jlf_application_name/basic.php" ) ) {
  require_once( "$jlf_application_name/basic.php" );
}

$request_method = 'CLI';
require_once('code/global.php');

require_once('code/err_functions.php');


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

$cgi_get_vars = array();
$cgi_vars = array();


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

if( is_readable( "$jlf_application_name/ldap.php" ) )
  require_once( "$jlf_application_name/ldap.php" );
require_once('code/ldap.php');

require_once('code/html.php');

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
need( mysql_query( 'SET autocommit=0' ), 'failed: sql: SET autocommit=0' );
$initialization_steps['db_ready'] = true;

// read more config from table:
//
require_once( "code/leitvariable.php" );
if( is_readable( "$jlf_application_name/leitvariable.php" ) ) {
  $jlf_leitvariable = $leitvariable;
  require_once( "$jlf_application_name/leitvariable.php" );
  $leitvariable = tree_merge( $jlf_leitvariable, $leitvariable );
  unset( $jlf_leitvariable );
}

sql_transaction_boundary( 'leitvariable' );
// $dbresult = mysql2array( mysql_query( "SELECT name, value FROM leitvariable" ) , 'name', 'value' );
$dbresult = sql_query( 'leitvariable', array( 'selects' => 'name, value', 'key_col' => 'name', 'val_col' => 'value' ) );
$sql_global_lock_id = sql_query( 'leitvariable', 'filters=name=global_lock-*,single_field=leitvariable_id' );
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

// if( function_exists( 'update_database' ) ) {
//   global $database_version;
//   $version_old = $database_version;
//   update_database();
//   if( $version_old != $database_version ) {
//     need( mysql_query( 'COMMIT AND CHAIN' ), 'sql: COMMIT failed' );
//   }
// }


if( is_readable( "$jlf_application_name/common.php" ) ) {
  require_once( "$jlf_application_name/common.php" );
}

if( is_readable( "$jlf_application_name/cli_commands.php" ) ) {
  require_once( "$jlf_application_name/cli_commands.php" );
}
require_once( "code/cli/cli_commands.php" );

// stub (used from cli) for function init_debugger( $debug_default = 0 ) {

//  global $debug_requests, $script, $show_debug_button, $initialization_steps, $sql_delayed_inserts;

  $debug = 0; // not (yet) available here: DEBUG_FLAG_ERROR;
  $max_debug_messages_display = 10;
  $max_debug_messages_dump = 100;
  $max_debug_chars_display = 200;

  $debug_requests = array( 'raw' => array() , 'cooked' => array( 'variables' => array() ) );

  $debug_requests_raw = ''; // FIXME: allow to actually initialize this!

  if( $debug_requests_raw ) {
    foreach( explode( ' ', $debug_requests_raw ) as $r ) {
      if( ! $r ) {
        continue;
      }
      $pair = explode( ':', $r, 2 );
      $name = $pair[ 0 ];
      if( isset( $pair[ 1 ] ) ) {
        if( $pair[ 1 ] ) {
          $lreqs = explode( ',', $pair[ 1 ] );
          foreach( $lreqs as $r ) {
            $action = explode( '.', $r );
            $debug_requests['cooked'][ $name ][ $action[ 0 ] ] = ( isset( $action[ 1 ] ) ? $action[ 1 ] : 1 );
          }
        } else {
          $debug_requests['cooked'][ $name ] = 1;
        }
      } else {
        $debug_requests['cooked']['variables'][ $name ] = 1;
      }
    }

    sql_transaction_boundary( '', 'debug' );
      sql_delete( 'debug', "script=$script", 'authorized=1' );
    sql_transaction_boundary();
  }
  if( $debug & DEBUG_FLAG_PROFILE ) {
    sql_transaction_boundary( '', 'profile' );
      sql_delete( 'profile', "script=$script", 'authorized=1' );
    sql_transaction_boundary();
  } else {
    unset( $sql_delayed_inserts['profile'] );
  }
  $initialization_steps['debugger_ready'] = true;

  $sql_delayed_inserts['debug_raw'] = array();
// }

?>
