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

  /*
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
  */
  
  // churn, number of customers lost in that period divided by total number of customers we had at the end of the month

  echo "<table>";
  echo "<td>Service</td><td>Canceled<td><td>Total</td><td>Churn</td><tr>";

  // get a total of customers for of all services at end of that month/year period
  $daysinmonth = date("t");
  $firstofmonth = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
  $lastofmonth = date("Y-m-d", mktime(0, 0, 0, $month, $daysinmonth, $year));

  $query = "SELECT us.start_datetime, us.end_datetime, ms.service_description, ms.id, count(*) AS monthtotal ".
    "FROM user_services us LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
    "WHERE date(us.start_datetime) <= '$lastofmonth' ".
    "AND date(us.end_datetime) BETWEEN '$firstofmonth' AND '$lastofmonth' OR us.end_datetime IS NULL ".
    "AND ms.frequency > 0 GROUP BY ms.id";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $totalresult = $DB->Execute($query) or die ("$query $l_queryfailed");
  while ($mytotalresult = $totalresult->FetchRow()) {
    $service_description = $mytotalresult['service_description'];
    $msid = $mytotalresult['id'];
    $totalformonth = $mytotalresult['monthtotal'];
      
    // search for customers with recurring service charges have an end_datetime in that month and year period
    //   us.end_datetime >= first of month AND us.end_datetime <= end of month
    $query = "SELECT count(*) AS count FROM user_services us ".
      "WHERE YEAR(us.end_datetime) = '$year' ".
      "AND MONTH(us.end_datetime) = '$month' AND us.master_service_id = '$msid'";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $endresult = $DB->Execute($query) or die ("$query $l_queryfailed");
    $myendresult = $endresult->fields;
    $lostcount = $myendresult['count'];

    $percentchurn = sprintf("%.2f",($lostcount/$totalformonth)*100);
    
    echo "<td>$service_description</td><td>$lostcount<td><td>$totalformonth</td><td>$percentchurn&percnt;</td><tr>";

    }

  echo "</table>";

 } else {
  
  // print a form to ask what date you want to view
  echo "Enter year and month of to see service churn:";
echo "<FORM ACTION=\"index.php\" METHOD=\"GET\">".
  "Year: <input type=text name=\"year\" value=\"$year\" size=4>".
  "Month <select name=\"month\">".
  "<option>01</option>".
    "<option>02</option>".
    "<option>03</option>".
    "<option>04</option>".
    "<option>05</option>".
  "<option>06</option>".
  "<option>07</option>".
  "<option>08</option>".
  "<option>09</option>".
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
