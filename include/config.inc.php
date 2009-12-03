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
$gpg_command = "/usr/bin/gpg --homedir /home/www-data/.gnupg --armor --always-trust --batch --no-secmem-warning -e -r 'CitrusDB'";
$gpg_decrypt = "/usr/bin/gpg --homedir /home/www-data/.gnupg --passphrase-fd 0 --yes --no-tty --skip-verify --decrypt";
//$path_to_home = '/home/pyasi'; // user with gpg keyring

// use these url prefixes to redirect between the ssl carddata and regular pages
// also maybe can be used with base href to help limit cross site scripting
$url_prefix = "http://localhost/~pyasi/citrus_project/citrusdb-gpg/";
$ssl_url_prefix = "https://localhost/~pyasi/citrus_project/citrusdb-gpg/";

