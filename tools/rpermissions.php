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

if (!isset($base->input['deletenow'])) { $base->input['deletenow'] = ""; }

//GET Variables
$module = $base->input['module'];
$deletenow = $base->input['deletenow'];
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

if ($deletenow) {  
  $query = "DELETE FROM module_permissions WHERE id=$pid";
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  print "$l_changessaved";
} else {
  echo "$l_areyousureyouwanttoremovethis<p>";

  // print yes button to send them to deletenow
  echo "<table><td>";
  print "<form style=\"margin-bottom:0;\" action=\"index.php\" method=post>";
  print "<input type=hidden name=load value=rpermissions>";
  print "<input type=hidden name=type value=tools>";
  print "<input type=hidden name=module value=customer>";
  print "<input type=hidden name=pid value=$pid>";
  print "<input name=deletenow type=submit value=\"  $l_yes  \" ".
    "class=smallbutton></form></td><td>";
  
  // if they hit no, send them back to the service edit screen
  print "<form style=\"margin-bottom:0;\" ".
    "action=\"index.php\" method=post>";
  print "<input name=done type=submit value=\"  $l_no  \" class=smallbutton>";
  print "<input type=hidden name=load value=mpermissions>";  
  print "<input type=hidden name=type value=tools>";
  print "<input type=hidden name=module value=customer>";        
  print "</form></td></table>";
    
}
?>
</table>
</body>
</html>

