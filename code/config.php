<?php

// how to connect to the MySQL database:
//
// the following lines assume that the information is set in the web server configuraton:
// (see apache.sample.conf for how to do this):
//
$jlf_mysql_db_server      =  getenv( 'jlf_mysql_db_server' );   // server address: hostname or IP number
$jlf_mysql_db_name        =  getenv( 'jlf_mysql_db_name' );     // name of MySQL database
$jlf_mysql_db_user        =  getenv( 'jlf_mysql_db_user' );     // user to connect as
$jlf_mysql_db_password    =  getenv( 'jlf_mysql_db_password' ); // password to authenticate with
$jlf_application_name     =  getenv( 'jlf_application_name' );  // where to find the non-common (application-specific) files of this jlf instance
$jlf_application_instance =  getenv( 'jlf_application_instance' );  // instance of same application (using same private scripts)


// alternatively, store literal information like in the following lines:
//
// $jlf_mysql_db_server  = "127.0.0.1";
// $jlf_mysql_db_name    = "INSERT_NAME_OF_DATABASE";
// $jlf_mysql_db_user    = "INSERT_NAME_OF_DATABASE_USER";
// $jlf_mysql_db_pwd     = "INSERT_PASSWORD"; 
// $jlf_application_name = "PATH_TO_SCRIPTS"; // if you need several, use the approach above (or insert ugly conditionals here)
// $jlf_application_instance = "PATH_TO_SCRIPTS"; // if you need several, use the approach above (or insert ugly conditionals here)

// $allow_setup_from = '141.89.116.94'; // allow to run setup.rphp from these IPs
$allow_setup_from = false;       // uncomment this after installation!

// ... that's it! (all further configuration will be stored in the database)

?>
