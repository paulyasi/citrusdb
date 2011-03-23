<?php
echo "<html>
<body bgcolor=\"#ffffff\">
<h3>$l_modulepermissions</h3>
<p>";

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
if (!isset($base->input['module'])) { $base->input['module'] = ""; }
if (!isset($base->input['save'])) { $base->input['save'] = ""; }
if (!isset($base->input['padd'])) { $base->input['padd'] = ""; }
if (!isset($base->input['permission'])) { $base->input['permission'] = ""; }
if (!isset($base->input['usergroup'])) { $base->input['usergroup'] = ""; }

$module = $base->input['module'];
$save = $base->input['save'];
$padd = $base->input['padd'];
$permission = $base->input['permission'];
$usergroup = $base->input['usergroup'];


// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
        echo "$l_youmusthaveadmin<br>";
        exit;
}

if ($save)
{
    $query = "INSERT INTO module_permissions (modulename,permission,user) values('$module','$permission','$usergroup')";
    $result = $DB->Execute($query) or die ("$l_queryfailed");
    print "$l_changessaved";

} else {

$usergroup = "<option value=\"\">-- $l_groups --</option>";
$group = array();
$query = "SELECT groupname FROM groups ORDER BY groupname";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
while ($myresult = $result->FetchRow())
{
    array_push($group,$myresult['groupname']);
}
$group = array_unique($group);
while (list($key,$value) = each($group))
{
    $usergroup .= "<option value=\"".$value."\">".$value."</option>";

}
$usergroup .= "<option value=\"\">-- $l_users --</option>";
$query = "SELECT username,real_name FROM user ORDER BY real_name";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
while ($myresult = $result->FetchRow())
{
    $usergroup .= "<option value=\"".$myresult['username']."\">".$myresult['real_name']."</option>";
}

echo "
<form name=\"form1\" method=\"post\" action=\"index.php\">
    <input type=\"hidden\" name=\"load\" value=\"apermissions\">
    <input type=hidden name=type value=tools>
    <input name=\"module\" type=\"hidden\" id=\"module\" value=\"$module\">
  <p>$l_users/$l_groups:
    <select name=\"usergroup\">
    $usergroup
    </select>
  </p>
  <p>$l_permission:
    <select name=\"permission\">
      <option value=\"r\">$l_view</option>
      <option value=\"c\">$l_create</option>
      <option value=\"m\">$l_modify</option>
      <option value=\"d\">$l_remove</option>
      <option value=\"f\">$l_fullcontrol</option>
    </select>
  </p>
  <p>
    <input name=\"padd\" type=\"submit\" id=\"padd\" value=\"$l_add\">
    <input name=\"save\" type=\"hidden\" id=\"save\" value=\"yes\">
  </p>
</form>
"; // end

}

?>
</table>
</body>
</html>
