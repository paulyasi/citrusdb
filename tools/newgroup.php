<?php
// Copyright (C) 2002  Paul Yasi <paul@citrusdb.org>, read the README file for more information
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

// GET & POST Variables
if (!isset($_POST['feedback'])) { $_POST['feedback'] = ""; }
if (!isset($base->input['membername'])) { $base->input['membername'] = ""; }
if (!isset($base->input['groupname'])) { $base->input['groupname'] = ""; }

$feedback = $_POST['feedback'];
$submit = $base->input['submit'];
$membername = $base->input['membername'];
$groupname = $base->input['groupname'];

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
        echo "$l_youmusthaveadmin<br>";
        exit;
}

if ($submit) {
	// insert groupname and username into the groups table
	$query = "INSERT INTO groups (groupname,groupmember) VALUES ('$groupname','$membername')";
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	print "<h3>$l_changessaved</h3>";
}

echo "<H3>$l_addnewgroup</H3>
	<P>
	<FORM ACTION=\"index.php\" METHOD=\"POST\">
	<B>$l_addmember:</B><BR>
	<SELECT NAME=\"membername\">";
	
	$query = "SELECT * FROM user";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	while ($myresult = $result->FetchRow())
	{
		$myusername = $myresult['username'];
		print "<option>$myusername</option>";
	}	

echo "</SELECT><P>
	<B>$l_togroupnamed:</B><BR>
        <INPUT TYPE=\"TEXT\" NAME=\"groupname\" VALUE=\"\" SIZE=\"20\" MAXLENGTH=\"32\">
        <P>
	<input type=hidden name=load value=newgroup>
	<input type=hidden name=type value=tools>	
	<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_add\">
	</FORM>";


?>



