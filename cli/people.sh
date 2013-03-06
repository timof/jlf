#!/usr/local/bin/php
<?php
#
# template/sample shell script to access sql:
# obtain mysql config, then call the php script on the server
#


$jlf_mysql_db_server = '127.0.0.1';
$jlf_mysql_db_name = 'pi-potsdam';
$jlf_mysql_db_user = 'pi-potsdam';
$jlf_application_name = 'pi';
$jlf_application_instance= 'potsdam';
$pwfile = '/keys/mysql.pi-potsdam.selene';
$scriptdir = '/Users/jlf';

// read pw, discard ending newline if any:
//
sscanf( file_get_contents( $pwfile ), '%s', $jlf_mysql_db_password );
chdir( $scriptdir );

$_GET = array( 'f' => 'cli' );

echo "[$jlf_mysql_db_password]";

require_once('cli/cli_common.php');

debug( $argv, 'argv' );
switch( $argv[ 1 ]  ) {
  case 'peoplelist_html':
    echo cli_peoplelist_html();
  break;
  case 'persondetails_html':
    echo cli_persondetails_html( $argv[ 1 ] );
  break;
}

?>
