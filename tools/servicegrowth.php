<?php
// Copyright (C) 2011 Paul Yasi (paul at citrusdb.org)
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


// GET Variables
if (!isset($base->input['year'])) { $base->input['year'] = ""; }
$year = $base->input['year'];

if (!isset($base->input['month'])) { $base->input['month'] = ""; }
$month = $base->input['month'];

if (!isset($base->input['category'])) { $base->input['category'] = ""; }
$category = $base->input['category'];

if ($year) {
  // print out a graph that compares each of the last 12 months
  // of service start_dates compared to end_dates
  $current  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
  echo "$year service churn, month: $month<p>\n";

  echo "Gained:<br>\n";
    $query = "SELECT ms.service_description, ms.id, monthname(us.start_datetime) AS month, year(start_datetime), ".
      "count(*) AS count FROM user_services us ".
      "LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
      "WHERE YEAR(us.start_datetime) = '$year' ".
      "AND MONTH(us.start_datetime) = '$month' GROUP BY ms.id";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("$query $l_queryfailed");
    while ($myresult = $result->FetchRow()) {
      $count = $myresult['count'];
      $service_description = $myresult['service_description'];
      $mymonthname = $myresult['month'];
      $msid = $myresult['id'];
      echo "$service_description $count<br>\n";
    }

  echo "<p>Lost<br>\n";  

    $query = "SELECT ms.service_description, ms.id, monthname(us.end_datetime) AS month, year(end_datetime), ".
      "count(*) AS count FROM user_services us ".
      "LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
      "WHERE YEAR(us.end_datetime) = '$year' ".
      "AND MONTH(us.end_datetime) = '$month' GROUP BY ms.id";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("$query $l_queryfailed");
    while ($myresult = $result->FetchRow()) {
      $count = $myresult['count'];
      $service_description = $myresult['service_description'];
      $mymonthname = $myresult['month'];
      $msid = $myresult['id'];
      echo "$service_description $count<br>\n";
    }
    
 } else {
  
  // print a form to ask what date you want to view
  echo "Enter year of to see service growth:";
echo "<FORM ACTION=\"index.php\" METHOD=\"GET\">".
  "Year: <input type=text name=\"year\" value=\"$year\" size=4>".
  "Month <select name=\"month\">".
  "<option>1</option>".
    "<option>2</option>".
    "<option>3</option>".
    "<option>4</option>".
    "<option>5</option>".
  "<option>6</option>".
  "<option>7</option>".
  "<option>8</option>".
  "<option>9</option>".
  "<option>10</option>".
  "<option>11</option>".  
  "<option>12<option></select>";

/*
  "Category: <select name=\"category\">";
// print list of categories to choose from
 $query = "SELECT DISTINCT category FROM master_services ".
   "ORDER BY category";
 $DB->SetFetchMode(ADODB_FETCH_ASSOC);
 $result = $DB->Execute($query) or die ("$l_queryfailed");
 while ($myresult = $result->FetchRow()) {
   $categoryname = $myresult['category'];
   echo "<option value=\"$categoryname\">$categoryname</option> \n";
 }
*/

echo  "</select><input type=hidden name=type value=tools>".
  "<input type=hidden name=load value=servicegrowth>".
  "&nbsp;<input type=submit name=\"$l_submit\" value=\"submit\">".
  "</form> <p>";
}
?>
