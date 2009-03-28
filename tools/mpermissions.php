<?php
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

echo "
<html>
<body bgcolor=\"#ffffff\">
<h3>$l_module ( $module ) $l_permission</h3>
[ <a href=\"index.php?load=apermissions&type=tools&module=$module\">
$l_addpermission</a> ]
<p>";

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
        echo "$l_youmusthaveadmin<br>";
        exit;
}

$query = "SELECT * FROM module_permissions WHERE modulename='$module' ORDER BY user";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");

echo "<table cellpadding=5 cellspacing=1><tr bgcolor=\"#eeeeee\"><td><b>$l_modulename</b></td><td><b>$l_permission</b></td><td><b>$l_user/$l_groups</b></td><td><b>$l_remove</b></td></tr>";

while ($myresult = $result->FetchRow())
{
	$pid = $myresult['id'];
        $permission = $myresult['permission'];
        $user = $myresult['user'];

        $query = "SELECT real_name FROM user WHERE username='$user'";
        $uresult = $DB->Execute($query) or die ("$l_queryfailed");
        $myuresult = $uresult->fields;
        $name = $myuresult['real_name'];
        if ($name == "") { $name=$user; }
        if ($permission == "r") { $permission="$l_view"; }
        if ($permission == "c") { $permission="$l_create"; }
        if ($permission == "m") { $permission="$l_modify"; }
        if ($permission == "d") { $permission="$l_remove"; }
        if ($permission == "f") { $permission="$l_fullcontrol"; }
	print "<tr bgcolor=\"#eeeeee\"><td>$module</td><td>$permission</td><td>$name</td><td><a href=\"index.php?load=rpermissions&module=$module&type=tools&pid=$pid\">[ $l_remove ]</a></td></tr>";

}

?>
</table>
</body>
</html>
