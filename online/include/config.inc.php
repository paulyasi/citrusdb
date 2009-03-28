<?php
/*----------------------------------------------------------------------------*/
// Copyright (C) 2002-2005  Paul Yasi <paul@citrusdb.org>
// Read the README file for more information
/*----------------------------------------------------------------------------*/

/*----------------------------------------------------------------------------*/
// Define Variables
// The sys_dbuser should be a different user from the normal database user
// the should have fewer priveleges and only be able to select things from the 
// database and insert into only the few tables required, like customer_history
/*----------------------------------------------------------------------------*/
$sys_dbhost = 'localhost';
$sys_dbuser = 'citrus';
$sys_dbpasswd = 'citrus';
$sys_dbname = 'citrus';
$sys_dbtype = 'mysql';
$path_to_citrus = '/users/pyasi/sites/citrusdb/online';
$hidden_hash_var='youmusalsotchangethis';
$payment_url = 'https://www.example.com/payment.cgi';
$notify_user = 'online';

// include localization file
include('./include/local/us-english.inc.php');

?>
