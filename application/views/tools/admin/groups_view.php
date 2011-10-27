<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_groups</h3>";
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
        echo '$l_youmusthaveadmin<br>';
        exit;
}

echo "[ <a href=\"index.php?load=newgroup&type=tools\">$l_add</a> ]";

print "<p><table cellpadding=5 cellspacing=1><tr bgcolor=\"#eeeeee\">
<td><b>$l_groupname</b></td><td><b>$l_membername</b></td><td></td><tr bgcolor=\"#eeeeee\">";

        // get the list of users from the table
        $query = "SELECT * FROM groups ORDER BY groupname";
        $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	while ($myresult = $result->FetchRow())
	{
		$myid = $myresult['id'];
		$mygroupname = $myresult['groupname'];
		$mymembername = $myresult['groupmember'];

		print "<td>$mygroupname</td><td>$mymembername</td><td><a href=\"index.php?load=deletegroup&type=tools&gid=$myid\">$l_delete</a></td><tr bgcolor=\"#eeeeee\">\n";
	}
?>
</table>
</body>
</html>
