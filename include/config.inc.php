<?php
/*----------------------------------------------------------------------------*/
// Copyright (C) 2002-2010  Paul Yasi (paul at citrusdb.org)
// Read the README file for more information
/*----------------------------------------------------------------------------*/

// define variables
$sys_dbhost = 'localhost';
$sys_dbuser = 'citrus';
$sys_dbpasswd = 'citrus';
$sys_dbname = 'citrus';
$sys_dbtype = 'mysql';
$path_to_citrus = '/home/pyasi/Development/citrus_project/citrusdb/';
$hidden_hash_var='youmustchangethis';
$lang = './include/local/us-english.inc.php';

// these gpg commands are required for encrypted storage of credit card data
$gpg_command = "/usr/bin/gpg --homedir /home/www-data/.gnupg --armor --batch -e -r 'CitrusDB'";
$gpg_decrypt = "/usr/bin/gpg --homedir /home/www-data/.gnupg -v --passphrase-fd 0 --yes --no-tty --skip-verify --decrypt";
$gpg_sign = "/usr/bin/gpg --homedir /home/www-data/.gnupg --passphrase-fd 0 --yes --no-tty --clearsign -u 'CitrusDB'";

// use these url prefixes to redirect between the ssl carddata and regular pages
$url_prefix = "http://localhost/~pyasi/citrus_project/citrusdb/";
$ssl_url_prefix = "https://localhost/~pyasi/citrus_project/citrusdb/";

// jabber/xmpp server information
$xmpp_server = "";
$xmpp_user = "";
$xmpp_password = "";
$xmpp_domain = "";
