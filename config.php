<?php
$archiver_config = array();
// -----------------------------------------------------------
// FOLDER CONFIG
// e.g. if your script is at /chandl/ these should be set to /chandl/arch/
// -----------------------------------------------------------

// where to store files, this folder should probably get made by you with 777 perms
$archiver_config[ 'storage' ] = "/home/<user>/public_html/chandl/arch/";

// the publicly accessible link to the file store
$archiver_config[ 'pubstorage' ] = "http://example.com/chandl/arch/";

// -----------------------------------------------------------
// MYSQL CONFIG
// self explanatory
// -----------------------------------------------------------

$archiver_config[ 'mysql_host' ] = "localhost";
$archiver_config[ 'mysql_user' ] = "anon";
$archiver_config[ 'mysql_pass' ] = "omglegionlol";
$archiver_config[ 'mysql_db' ]   = "chanarchive";

// -----------------------------------------------------------
// ACCESS CONTROL
// if all these are false login is disabled
// -----------------------------------------------------------

// if this is set to true you need to login to manually check threads
$archiver_config[ 'login_chk' ] = false;

// if this is set to true you need to login to add threads
$archiver_config[ 'login_add' ] = false;

// if this is set to true you need to login to delete or change description of threads
$archiver_config[ 'login_del' ] = false;

// username & password for login
$archiver_config[ 'login_user' ] = "eggman";
$archiver_config[ 'login_pass' ] = "implying";

// -----------------------------------------------------------
// INTERNAL STUFF
// leave this alone
// -----------------------------------------------------------

$archiver_config[ 'login_enabled' ] = $archiver_config[ 'login_del' ] || $archiver_config[ 'login_add' ] || $archiver_config[ 'login_chk' ];
?>