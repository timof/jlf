#!/usr/local/bin/php
<?php
// 
//  cli
// 
//  php script to be called via command line interface (not through apache)
//  it will obtain db config from environment and should be called from cli.sh
//  
//  syntax:
//    cli [<opt>...]command table [<filter>] 
//
//  db config and credentials must be passed in environment
//
$_GET = array( 'f' => 'cli' );

$jlf_mysql_db_server      =  getenv( 'jlf_mysql_db_server' );   // server address: hostname or IP number
$jlf_mysql_db_name        =  getenv( 'jlf_mysql_db_name' );     // name of MySQL database
$jlf_mysql_db_user        =  getenv( 'jlf_mysql_db_user' );     // user to connect as
$jlf_mysql_db_password    =  getenv( 'jlf_mysql_db_password' ); // password to authenticate with
$jlf_application_name     =  getenv( 'jlf_application_name' );  // where to find the non-common (application-specific) files of this jlf instance
$jlf_application_instance =  getenv( 'jlf_application_instance' );  // instance of same application (using same private scripts)

// $header_printed = true; //no header required

require_once('code/cli/cli_environment.php');

sql_transaction_boundary('*');
  need( try_cli_access(), 'failed to obtain cli session' );
sql_transaction_boundary();

if( $argv[ 1 ] === 'H' ) {
  $args = explode( '.', $argv[ 2 ] . '.........' );
  foreach( $args as & $ref_a ) {
    if( $ref_a ) {
      $ref_a = hex_decode( $ref_a );
      need( check_utf8( $ref_a ), 'cli argument: not valid utf-8' );
    }
  }
  unset( $ref_a );
} else {
  $args = $argv;
  unset( $args[ 0 ] );
}
$do_echo = false;
$verbose = false;
$command = $args[ 1 ];

while( true ) {
  // debug( $command, 'parsing:' );
  switch( $command[ 0 ] ) {
    case 'v':
      $verbose = true;
      $command = substr( $command, 1 );
      echo "cli: start: $utc\n";
      continue 2;
    case 'e':
      $do_echo = true;
      $command = substr( $command, 1 );
      continue 2;
    case 'g':
      need( isset( $args[ 3 ] ), 'usage: g <app_run> <app_spec>' );
      echo cli_garbage_collection( $args[ 2 ], $args[ 3 ] );
      break;
    case 'q':
      cli_query( $args );
      break;
    case 'i':
      echo cli_insert( $args[ 2 ] );
      break;
    case 'u':
      echo cli_update( $args[ 2 ], $args[ 3 ] );
      break;
    case 's':
      echo cli_sql( $args[ 2 ] );
      break;
    default:
      echo "invalid command\n";
      break;
  }
  break;
}

if( $verbose ) {
  echo "cli: end: " . datetime_unix2canonical( time() );
}

sql_commit_delayed_inserts();

sql_do( 'COMMIT AND NO CHAIN' );

?>
