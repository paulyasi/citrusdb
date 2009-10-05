<?php
/*----------------------------------------------------------------------------*/
// Copyright (C) 2002-2009  Paul Yasi (paul at citrusdb.org)
// Read the README file for more information
/*----------------------------------------------------------------------------*/

// define variables
$sys_dbhost = 'localhost';
$sys_dbuser = 'citrus-gpg';
$sys_dbpasswd = 'citrus-gpg';
$sys_dbname = 'citrus-gpg';
$sys_dbtype = 'mysql';
$path_to_citrus = '/home/pyasi/citrus_project/citrusdb-gpg';
$hidden_hash_var='youmustchangethis';
$lang = './include/local/us-english.inc.php';
$gpg_command = "/usr/bin/gpg --armor --always-trust --batch --no-secmem-warning -e -u 'Paul Yasi' -r 'Paul Yasi'";
$path_to_home = '/home/pyasi'; // user with the gpg keyring
