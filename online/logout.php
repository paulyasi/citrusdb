<?php
// Copyright (C) 2002-2005  Paul Yasi <paul@citrusdb.org>, read the README file for more information
include('./include/config.inc.php');
require_once('./include/database.inc.php');
require_once('./include/user.class.php');
$u = new user();

if ($u->user_isloggedin()) {
	$u->user_logout();
	$user_name='';
}

echo '<html><body bgcolor=#ffffff><center><table><td><center>';
echo "<H3>$l_youarenowloggedout</H3>";
echo "<p><a href=\"index.php\">$l_loginagain</a></center></td></table></center></body></html>";

?>
