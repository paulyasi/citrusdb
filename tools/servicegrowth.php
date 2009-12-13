<?php
// Copyright (C) 2009 Paul Yasi (paul at citrusdb.org)
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

if (!isset($base->input['category'])) { $base->input['category'] = ""; }
$category = $base->input['category'];

if ($year) {
  // print out a graph that compares each of the last 12 months
  // of service start_dates compared to end_dates
  $current  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
  echo "$year: $category";
  echo "<table border=1>";

  $month = array(1, 2, 3, 4, 5, 6, 7, 8, 9 , 10, 11, 12);

  foreach($month as $mymonth) {
    $query = "SELECT monthname(us.start_datetime) AS month, year(start_datetime), ".
      "count(*) AS count FROM user_services us ".
      "LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
      "WHERE ms.category = '$category' AND YEAR(us.start_datetime) = '$year' ".
      "AND MONTH(us.start_datetime) = '$mymonth' GROUP BY MONTH(us.start_datetime)";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("$query $l_queryfailed");
    $myresult = $result->fields;
    $count = $myresult['count'];
    $monthvar = $monthname[$mymonth];
    echo "<td style=\"color: green; text-align: center;\">$count </td>\n";
  }
  
  echo "<tr>";

  // print months
  echo "<td>$l_january</td><td>$l_february</td><td>$l_march</td><td>$l_april</td><td>$l_may</td><td>$l_june</td><td>$l_july</td><td>$l_august</td><td>$l_september</td><td>$l_october</td><td>$l_november</td><td>$l_december</td><tr>\n";


  foreach($month as $mymonth) {
    $query = "SELECT monthname(us.end_datetime) AS month, year(end_datetime), ".
      "count(*) AS count FROM user_services us ".
      "LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
      "WHERE ms.category = '$category' AND YEAR(us.end_datetime) = '$year' ".
      "AND MONTH(us.end_datetime) = '$mymonth' GROUP BY MONTH(us.end_datetime)";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("$query $l_queryfailed");
    $myresult = $result->fields;
    $count = $myresult['count'];
    $monthvar = $monthname[$mymonth];
    $countmultiplier = $count * 10;
    $countpixels = "$countmultiplier" . "px";
    echo "<td valign=top><div style=\"height: $countpixels; background-color: red;\"></div></td>\n";
  }
  
  echo "</table>";

 } else {
  
  // print a form to ask what date you want to view
  echo "Enter year of to see service growth:";
echo "<FORM ACTION=\"index.php\" METHOD=\"GET\">".
  "Year: <input type=text name=\"year\" value=\"$year\" size=4>".
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
 
echo  "</select><input type=hidden name=type value=tools>".
  "<input type=hidden name=load value=servicegrowth>".
  "&nbsp;<input type=submit name=\"$l_submit\" value=\"submit\">".
  "</form> <p>";
}
?>
