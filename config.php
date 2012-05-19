<?php
$archiver_config = array();

// where to store files, this folder should probably get made by you with 777 perms
$archiver_config[ 'storage' ] = "/home/<username>/public_html/chandl/arch/";

// the publicly accessible link to the file store
$archiver_config[ 'pubstorage' ] = "http://example.com/chandl/arch/";

// self explanatory
$archiver_config[ 'mysql_host' ] = "localhost";
$archiver_config[ 'mysql_user' ] = "mrnigger";
$archiver_config[ 'mysql_pass' ] = "buhdahbuhbuhbuh";
$archiver_config[ 'mysql_db' ]   = "chanarchive";
?>