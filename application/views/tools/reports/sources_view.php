<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_sourcereport: ";
// Copyright (C) 2008  Paul Yasi (paul at citrusdb dot org)
// Read the README file for more information
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
if ($myresult['manager'] == 'n') {
	echo "$l_youmusthaveadmin<br>";
        exit; 
}


$empty_day_1  = date("Y-m-d", mktime(0, 0, 0, date("m")-1  , date("d"), date("Y")));
$empty_day_2  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));

// Get Variables
if (!isset($base->input['day1'])) { $base->input['day1'] = "$empty_day_1"; }
if (!isset($base->input['day2'])) { $base->input['day2'] = "$empty_day_2"; }
if (!isset($base->input['category'])) { $base->input['category'] = ""; }

$day1 = $base->input['day1'];
$day2 = $base->input['day2'];
$category = $base->input['category'];

if ($category) {

  echo "$category</h3>";

  //$DB->debug = true;
  /*--------------------------------------------------------------------------*/
  // NUMBER ADDED DURING THIS PERIOD and billing type for each added
  $query = "SELECT DISTINCT us.id us_id, cu.source " .
    "FROM user_services us " .
    "LEFT JOIN master_services ms ON ms.id = us.master_service_id " .
    "LEFT JOIN customer cu ON cu.account_number = us.account_number " .
    "WHERE ms.category = '$category' ".
    "AND date(us.start_datetime) BETWEEN '$day1' AND '$day2'";
  //$query = "SELECT COUNT(us.id) AS ServiceCount,ms.service_description FROM user_services us LEFT JOIN master_services ms ON ms.id = us.master_service_id WHERE ms.id = $service_id AND date(start_datetime) BETWEEN '$day1' AND '$day2' GROUP BY ms.id";
  $result = $DB->Execute($query) or die ("$query $l_queryfailed");

  $sourcearray = array();

  while ($myresult = $result->FetchRow()) {
    $sourcename = $myresult['source'];
    $sourcearray["$sourcename"]++;
    $service_count++;
  }
  echo "<h2>$l_added: $service_count</h2><table>\n";

  arsort ($sourcearray);

  foreach ($sourcearray as $source=>$value) {
    echo "<td>$source</td><td>$value</td><tr>\n";
  }  

  echo "</table>\n";

 } else {

echo "<FORM ACTION=\"index.php\" METHOD=\"GET\"><table>
<select name=\"category\">";

// get the list of service categories from the master_services table
$query = "SELECT DISTINCT(category) FROM master_services ORDER BY category";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");

while ($myresult = $result->FetchRow()) {
  $category = $myresult['category'];
  
  echo "<option value=\"$category\">$category</option>";
 
}

echo "</select>
	From: <input type=text name=\"day1\" value=\"$day1\"> - 
	To: <input type=text name=\"day2\" value=\"$day2\">
<input type=hidden name=type value=tools>
	<input type=hidden name=load value=sourcereport>
	</td><tr> 
	<td></td><td><br><input type=submit name=\"$l_submit\" value=\"submit\"></td>
	</table>
	</form> <p>";
 }
		
?>
</body>
</html>







