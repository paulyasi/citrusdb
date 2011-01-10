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
$path_to_citrus = '/home/pyasi/citrus_project/citrusdb/';
$hidden_hash_var='youmustchangethis';
$lang = './include/local/us-english.inc.php';
$url_prefix = "http://localhost/~pyasi/citrus_project/citrusdb/";
$ssl_url_prefix = "http://localhost/~pyasi/citrus_project/citrusdb/";

// these gpg commands are required for encrypted storage of credit card data
$gpg_command = "/usr/bin/gpg --homedir /home/www-data/.gnupg --armor --batch -e -r 'CitrusDB'";
$gpg_decrypt = "/usr/bin/gpg --homedir /home/www-data/.gnupg -v --passphrase-fd 0 --yes --no-tty --skip-verify --decrypt";
$gpg_sign = "/usr/bin/gpg --homedir /home/www-data/.gnupg --passphrase-fd 0 --yes --no-tty --clearsign -u 'CitrusDB'";

// ldap server settings (optional for authentication)
$ldap_enable = FALSE;
$ldap_host = 'ldaps://localhost';
$ldap_dn = 'ou=webapps,dc=localhost';
$ldap_protocol_version = 3;
$ldap_uid_field = 'uid';

// jabber/xmpp server information (optional for ticket notification)
$xmpp_server = "";
$xmpp_user = "";
$xmpp_password = "";
$xmpp_domain = "";

// shipping tracking url
$tracking_url = "http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum=";
