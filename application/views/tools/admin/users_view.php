<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_users</h3>";
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

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
        echo "$l_youmusthaveadmin<br>";
        exit;
}

echo "[ <a href=\"index.php?load=newuser&type=tools\">$l_addnewuser</a> ]";


print "<p><table cellpadding=5 cellspacing=1><tr bgcolor=\"#eeeeee\"><td><b>$l_username</b></td><td><b>$l_name</b></td><td>$l_admin</td><td>$l_manager</td><td>$l_email</td><td>$l_screenname</td><td></td><tr bgcolor=\"#eeeeee\">";

        // get the list of users from the table
        $query = "SELECT * FROM user ORDER BY username";
        $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	while ($myresult = $result->FetchRow())
	{
	$myid = $myresult['id'];
        $myusername = $myresult['username'];
        $myrealname = $myresult['real_name'];
	$myadmin = $myresult['admin'];
	$mymanager = $myresult['manager'];
	$myemail = $myresult['email'];
	$myscreenname = $myresult['screenname'];

	print "<td>$myusername</td><td>$myrealname</td><td>$myadmin</td>".
	  "<td>$mymanager</td><td>$myemail</td><td>$myscreenname</td>
	<td><a href=\"index.php?load=edituser&type=tools&userid=$myid\">$l_edit</a></td>
	<td><a href=\"index.php?load=deleteuser&type=tools&uid=$myid\">$l_delete</a></td>
	<tr bgcolor=\"#eeeeee\">\n";
	}
?>
</table>
</body>
</html>
