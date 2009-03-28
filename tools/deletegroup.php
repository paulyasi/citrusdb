<html>
<body bgcolor="#ffffff">
<?php   
echo "<h3>$l_deletegroup</h3>";
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

// Copyright (C) 2002-2006  Paul Yasi <paul@citrusdb.org>, read the README file for more information


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
if (!isset($base->input['gid'])) { $base->input['gid'] = ""; }
$deletenow = $base->input['deletenow'];
$gid = $base->input['gid'];

if ($deletenow) {

	// delete the grouping with that ID

	$query = "DELETE FROM groups WHERE id = '$gid'";
	$result = $DB->Execute($query) or die ("$l_queryfailed");

	// redirect back to the group list page
	print "<script language=\"JavaScript\">window.location.href = \"index.php?load=groups&type=tools\";</script>";
}
else
{
	print "<br><br>";
	print "<h4>$l_areyousureyouwanttoremovethegroupid: $gid</h4>";
	print "<table cellpadding=15 cellspacing=0 border=0 width=720><td align=right width=360>";

	// if they hit yes, this will sent them into the deletegroup.php file and remove the service

	print "<form style=\"margin-bottom:0;\" action=\"index.php\">";
	print "<input type=hidden name=load value=deletegroup>";
	print "<input type=hidden name=type value=tools>";
	print "<input type=hidden name=gid value=$gid>";
	print "<input name=deletenow type=submit value=\"  $l_yes  \" class=smallbutton></form></td>";

	// if they hit no, send them back to the service edit screen

	print "<td align=left width=360><form style=\"margin-bottom:0;\" action=\"index.php\">";
	print "<input name=done type=submit value=\"  $l_no  \" class=smallbutton>";
        print "<input type=hidden name=load value=groups>";  
	print "<input type=hidden name=type value=tools>";        
	print "</form></td></table>";
	print "</blockquote>";
}
?>
