<html>
<body bgcolor="#ffffff">
<p>
<?php
echo "<h3>$l_removepermissions</h3>";
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
//GET Variables
$module = $base->input['module'];
$pid = $base->input['pid'];

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
        echo "$l_youmusthaveadmin<br>";
        exit;
}

$query = "DELETE FROM module_permissions WHERE id=$pid";
$result = $DB->Execute($query) or die ("$l_queryfailed");


print "$l_changessaved";

?>
</table>
</body>
</html>

