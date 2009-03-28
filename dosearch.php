<?php
// Copyright (C) 2002-2008  Paul Yasi (paul at citrusdb.org)
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

// Variables
if (!isset($base->input['id'])) { $base->input['id'] = ""; }
if (!isset($base->input['s1'])) { $base->input['s1'] = ""; }
if (!isset($base->input['s2'])) { $base->input['s2'] = ""; }
if (!isset($base->input['s3'])) { $base->input['s3'] = ""; }
if (!isset($base->input['s4'])) { $base->input['s4'] = ""; }
if (!isset($base->input['s5'])) { $base->input['s5'] = ""; }
if (!isset($base->input['page'])) { $base->input['page'] = 1 ; }
if (!isset($base->input['perpage'])) { $base->input['perpage'] = 20 ; }
if (!isset($base->input['pagetype'])) { $base->input['pagetype'] = 'list' ; }

$id = $base->input['id'];
$s1 = $base->input['s1'];
$s2 = $base->input['s2'];
$s3 = $base->input['s3'];
$s4 = $base->input['s4'];
$s5 = $base->input['s5'];
$page = $base->input['page'];
$perpage = $base->input['perpage'];
$pagetype = $base->input['pagetype'];

$user =  $u->user_getname();

// figure out which type of search it is from the searches table
$query = "SELECT * FROM searches WHERE id = $id";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;	
	
// assign the query from the search to the query string
// replace the s1 thru s5 etc place holders with the actual variables
$query = str_replace("%s1%", $s1, $myresult['query']);
$query = str_replace("%s2%", $s2, $query);
$query = str_replace("%s3%", $s3, $query);
$query = str_replace("%s4%", $s4, $query);
$query = str_replace("%s5%", $s5, $query);

$result = $DB->Execute($query) or die ("$l_queryfailed");
$keyresult = $DB->Execute($query) or die ("$l_queryfailed");

echo "<h3>$l_foundset</h3>
<table cellpadding=5 cellspacing=1 border=0>";

        
$myresult = $keyresult->fields;	

// print the the column titles
$i = 0;

// check for the array and print any results
if (is_array($myresult) AND $pagetype == "list") 
{
echo "<tr bgcolor=#ccccdd><td></td>";
  foreach ($myresult as $key => $value) 
    {          
      echo "<td>$key</td>\n";
      $i++;
    } 
 }


// if there is only 1 result go to that customer record
// else print out the listing of results

// get the number of results
$num_of_results = $result->RowCount();
echo "$num_of_results $l_found: ";
if ($num_of_results > $perpage)
{
  $pager = getPagerData($num_of_results, $perpage, $page);
  $offset = $pager->offset;
  $limit = $pager->limit;
  $page = $pager->page;
  $numpages = $pager->numPages;
  $pagedquery = $query . " limit $offset, $limit"; 
  $result = $DB->Execute($pagedquery) or die ("this $l_queryfailed");
  echo "$l_page $page $l_of $numpages | ";
  
  if($page == 1)
    {
      echo "$l_previous ";
    } else {
    echo "<a
href=\"index.php?load=dosearch&type=fs&id=$id&s1=$s1&s2=$s2&s3=$s3&s4=$s4&s5=$s5&page=" . ($page - 1) . "&perpage=$perpage&pagetype=$pagetype\">$l_previous</a> ";	
  }
  
  if($page == $pager->numPages)
    {
      echo "$l_next";
    } else {
    echo "<a href=\"index.php?load=dosearch&type=fs&id=$id&s1=$s1&s2=$s2&".
      "s3=$s3&s4=$s4&s5=$s5&page="
      . ($page + 1) . "&perpage=$perpage&pagetype=$pagetype\">$l_next</a>";
  }

  if ($pagetype == "list") {
  
    echo " | $l_results_per_page: <form name=\"resultsper\">
	<select name=\"perchoice\"
onChange=\"window.location=document.resultsper.perchoice.options[document.resultsper.perchoice.selectedIndex].value\">
	<option selected value=\"index.php?load=dosearch&type=fs&id=$id&s1=$s1&s2=$s2&s3=$s3&s4=$s4&s5=$s5&page=$page&perpage=$perpage\">$perpage</option>
	<option
value=\"index.php?load=dosearch&type=fs&id=$id&s1=$s1&s2=$s2&s3=$s3&s4=$s4&s5=$s5&page=$page&perpage=20\">20</option>
	<option
value=\"index.php?load=dosearch&type=fs&id=$id&s1=$s1&s2=$s2&s3=$s3&s4=$s4&s5=$s5&page=$page&perpage=50\">50</option>
	<option
value=\"index.php?load=dosearch&type=fs&id=$id&s1=$s1&s2=$s2&s3=$s3&s4=$s4&s5=$s5&page=$page&perpage=100\">100</option>
	<option
value=\"index.php?load=dosearch&type=fs&id=$id&s1=$s1&s2=$s2&s3=$s3&s4=$s4&s5=$s5&page=$page&perpage=500\">500</option>
	<option
value=\"index.php?load=dosearch&type=fs&id=$id&s1=$s1&s2=$s2&s3=$s3&s4=$s4&s5=$s5&page=$page&perpage=1000\">1000</option>
	<option
value=\"index.php?load=dosearch&type=fs&id=$id&s1=$s1&s2=$s2&s3=$s3&s4=$s4&s5=$s5&page=$page&perpage=10000\">10000</option>
	</select>
	</form>";
  }

}

// record view
echo "&nbsp;&nbsp;&nbsp;&nbsp; <a href=\"index.php?load=dosearch&type=fs&id=$id&s1=$s1&s2=$s2&s3=$s3&s4=$s4&s5=$s5&page=$page&perpage=1&pagetype=record\">$l_recordview</a> | ";
echo "<a href=\"index.php?load=dosearch&type=fs&id=$id&s1=$s1&s2=$s2&s3=$s3&s4=$s4&s5=$s5&page=$page&perpage=20&pagetype=list\">$l_listview</a><br>";

while ($myresult = $result->FetchRow())
{	
  // get the account_number
  foreach ($myresult as $key => $value) 
    {
      if ($key == "account_number") 
	{
	  $acnum = $value;
	}
      if ($key == "id") 
	{
	  $id = $value;
	}
    }
  
  if ($num_of_results == 1) {
    print "<script language=\"JavaScript\">window.location.href = ".
      "\"index.php?load=viewaccount&type=fs&acnum=$acnum\";</script>";
  } else {
      echo "<tr bgcolor=#ddddee><td><a href=\"index.php?load=viewaccount&".
	"type=fs&acnum=$acnum\">$l_view</a></td>";
      if ($pagetype == "record") { echo "</table>"; }
  }

  if ($pagetype == "record") {
    // set the new account number to view
    $account_number = $acnum;
    $_SESSION['account_number'] = $account_number;

    // print the customer record within the result page
    echo "<hr noshde>";
    $load = "customer"; // allow load of customer record
    $type = "module";
    include('./modules/customer/index.php');
    $load = "dosearch"; // allow search result load after
    $type = "fs";
    
  } else {
    
    foreach ($myresult as $key => $value) {
      echo "<td>$value</td>\n";   
    }
    
  } // end if record
  
 } // end while

if (empty($key)) 
  {
    echo "<tr><td><b>$l_sorrynorecordsfound</b></td></tr>\n";
    echo "<tr><td><a href=\"index.php?load=search&type=base\"> $l_clickheretotryagain</a>";
  } 

echo '</table>';

?>
