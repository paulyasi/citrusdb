<html>
<body bgcolor="#ffffff">
<?php
   echo "<h3>$l_fieldassets</h3>";

// Copyright (C) 2010  Paul Yasi (paul at citrusdb.org)
// read the README file for more information
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
if (!isset($base->input['id'])) { $base->input['id'] = ""; }
if (!isset($base->input['description'])) { $base->input['description'] = ""; }
if (!isset($base->input['status'])) { $base->input['status'] = ""; }
if (!isset($base->input['weight'])) { $base->input['weight'] = ""; }
if (!isset($base->input['category'])) { $base->input['category'] = ""; }
if (!isset($base->input['changestatus'])) { $base->input['changestatus'] = ""; }

$submit = $base->input['submit'];
$id = $base->input['id'];
$description = $base->input['description'];
$status = $base->input['status'];
$weight = $base->input['weight'];
$category = $base->input['category'];
$changestatus = $base->input['changestatus'];

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
  echo "$l_youmusthaveadmin";
  exit;
}

// show list of all field asset types

if ($submit) {
  // then we add a new field asset type
  $query = "INSERT INTO master_field_assets (description,status,weight, ".
    "category) VALUES ('$description','$status','$weight','$category')";
  
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  
  print "<h3>$l_changessaved</h3> 
	[<a href=\"index.php?load=services&tooltype=module&type=tools\">$l_done</a>]";
}

if ($changestatus) {
  // then we update the status of that id
  $query = "UPDATE master_field_assets SET status = '$changestatus' WHERE id = '$id'";
    
  $result = $DB->Execute($query) or die ("$query $l_queryfailed");  
}

$query = "SELECT * FROM master_field_assets";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");

echo '<table cellpadding=5 cellspacing=1><tr bgcolor="#eeeeee">';
echo "<td><b>$l_id</b></td> <td><b>$l_description</b></td> <td><b>$l_status</b></td> <td><b>$l_weight</b></td><td><b>$l_category</b></td><td><b>$l_changestatus</b></td></tr>";

while ($myresult = $result->FetchRow()) {
  $id = $myresult['id'];
  $desc = $myresult['description'];
  $status = $myresult['status'];
  $weight = $myresult['weight'];
  $category = $myresult['category'];
  print "<tr bgcolor=\"#eeeeee\"><td>$id</td><td>$desc</td><td>$status</td><td>$weight</td><td>$category</td>";
  print "<td><a href=\"index.php?load=services&tooltype=module&type=tools&fieldassets=on&changestatus=old&id=$id\">old</a> | ".
    "<a href=\"index.php?load=services&tooltype=module&type=tools&fieldassets=on&changestatus=current&id=$id\">current</a></td></tr>\n";
 }

echo "</table><p>
<b>$l_add:</b><br>
<FORM ACTION=\"index.php\" METHOD=\"GET\">
<table>
<td>$l_description: </td><td><input type=text name=\"description\"></td><tr>
<td>$l_status: </td><td>
<label><input type=radio name=\"status\" value=\"current\">Current</label> 
<label><input type=radio name=\"status\" value=\"old\">Old</label> 
</td><tr>
<td>$l_weight: </td><td><input type=text name=\"weight\"></td><tr>
<td>$l_category: </td><td><select name=\"category\">";

// get the list of service categories from the master_services table
$query = "SELECT DISTINCT(category) FROM master_services ORDER BY category";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("category $l_queryfailed");

while ($myresult = $result->FetchRow()) {
  $category = $myresult['category'];
  
  echo "<option value=\"$category\">$category</option>";
 
}

  echo "</select></td><tr>
<td></td><td>
<input type=hidden name=load value=services>
<input type=hidden name=tooltype value=module>
<input type=hidden name=type value=tools>
<input type=hidden name=fieldassets value=on>
<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_add\">
</td></table>
</FORM>
<p>";

?>

</body>
</html>
