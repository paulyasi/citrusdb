<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">
<?php   
echo "<h3>$l_deleteuser</h3>";
/*----------------------------------------------------------------------------*/
// Check for authorized accesss
/*----------------------------------------------------------------------------*/
if(constant("INDEX_CITRUS") <> 1){
	echo "You must be logged in to run this.  Goodbye.";
	exit;	
}

if (!defined("INDEX_CITRUS")) {
	echo "You must be logged in to run this.  Goodbye.";
        exit;
}

// Copyright (C) 2002-2006  Paul Yasi <paul@citrusdb.org>
// Read the README file for more information


// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
        echo '$l_youmusthaveadmin<br>';
        exit;
}
if (!isset($base->input['deletenow'])) { $base->input['deletenow'] = ""; }
if (!isset($base->input['uid'])) { $base->input['uid'] = ""; }
$deletenow = $base->input['deletenow'];
$uid = $base->input['uid'];

if ($deletenow) {

	// get their username so we can delete them from groups later
	$query = "SELECT username FROM user WHERE id = '$uid'";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	$myresult = $result->fields;
	$username = $myresult['username'];

	// delete the user with that ID
	$query = "DELETE FROM user WHERE id = '$uid'";
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	
	// remove the user from groups they are a member of
	$query = "DELETE FROM groups WHERE groupmember = '$username'"; 
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	
	// redirect back to the user list page
	print "<script language=\"JavaScript\">window.location.href = \"index.php?load=users&type=tools\";</script>";
}
else
{
	print "<br><br>";
	print "<h4>$l_areyousureyouwanttoremovetheuserid: $uid</h4>";
	print "<table cellpadding=15 cellspacing=0 border=0 width=720><td align=right width=360>";

	// if they hit yes, this will sent them into the deletegroup.php file and remove the service

	print "<form style=\"margin-bottom:0;\" action=\"index.php\" method=post>";
	print "<input type=hidden name=load value=deleteuser>";
	print "<input type=hidden name=type value=tools>";
	print "<input type=hidden name=uid value=$uid>";
	print "<input name=deletenow type=submit value=\"  $l_yes  \" class=smallbutton></form></td>";

	// if they hit no, send them back to the service edit screen

	print "<td align=left width=360><form style=\"margin-bottom:0;\" action=\"index.php\" method=post>";
	print "<input name=done type=submit value=\"  $l_no  \" class=smallbutton>";
        print "<input type=hidden name=load value=users>";  
	print "<input type=hidden name=type value=tools>";        
	print "</form></td></table>";
	print "</blockquote>";
}
?>
