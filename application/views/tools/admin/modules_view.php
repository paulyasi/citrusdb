<?php
echo "<html>
<body bgcolor=\"#ffffff\">
<h3>$l_modules</h3>
[ <a href=\"index.php?load=addmodule&type=tools\">$l_addmodule</a> ]
<p>
<table cellpadding=5 cellspacing=1><tr bgcolor=\"#eeeeee\"><td>
<b>$l_modulename</b></td><td></td></tr>
";

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

$query = "SELECT * FROM modules ORDER BY sortorder";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
while ($myresult = $result->FetchRow())
{
	$commonname = $myresult['commonname'];
	$modulename = $myresult['modulename'];

	print "<tr bgcolor=\"#eeeeee\"><td><a href=\"index.php?load=$modulename&tooltype=module&type=tools\">$commonname</a></td><td><a href=\"index.php?load=mpermissions&module=$modulename&type=tools\">[ $l_edit $l_permission ]</a></td></tr>";

}

?>
</table>
</body>
</html>
