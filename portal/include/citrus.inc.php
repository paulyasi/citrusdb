<?php
// Copyright (C) 2005  Paul Yasi <paul@citrusdb.org>, read the README file for more information
// File to be included in all parts of citrus

// require the user functions here
require_once('user.inc.php');

if (!user_isloggedin()) {
	echo 'You must be logged in to CitrusDB.<br>';
	exit;
}

?>
