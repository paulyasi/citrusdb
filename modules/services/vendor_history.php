<?php   
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



if (!isset($base->input['entry_type'])) { $base->input['entry_type'] = ""; }
if (!isset($base->input['entry_date'])) { $base->input['entry_date'] = ""; }
if (!isset($base->input['vendor_name'])) { $base->input['vendor_name'] = ""; }
if (!isset($base->input['vendor_bill_id'])) { $base->input['vendor_bill_id'] = ""; }
if (!isset($base->input['vendor_cost'])) { $base->input['vendor_cost'] = ""; }
if (!isset($base->input['vendor_tax'])) { $base->input['vendor_tax'] = ""; }
if (!isset($base->input['vendor_item_id'])) { $base->input['vendor_item_id'] = ""; }
if (!isset($base->input['userserviceid'])) { $base->input['userserviceid'] = ""; }
if (!isset($base->input['submit'])) { $base->input['submit'] = ""; }

// GET Variables
$entry_type = $base->input['entry_type'];
$entry_date = $base->input['entry_date'];
$vendor_name = $base->input['vendor_name'];
$vendor_bill_id = $base->input['vendor_bill_id'];
$vendor_cost = $base->input['vendor_cost'];
$vendor_tax = $base->input['vendor_tax'];
$vendor_item_id = $base->input['vendor_item_id'];
$userserviceid = $base->input['userserviceid'];
$submit = $base->input['submit'];

echo "<h3>$l_vendor_history</h3>";

if ($submit) {
  /*--------------------------------------------------------------------------*/
  // make a new entry in the vendor history
  /*--------------------------------------------------------------------------*/

  // grab the current account_status, and total_price
  $query = "SELECT SUM(bd.billed_amount) as billed_amount, bd.billing_id ".
    "FROM billing_details bd ".
    "LEFT JOIN user_services us ON us.id = bd.user_services_id ".
    "WHERE bd.user_services_id = '$userserviceid' GROUP BY bd.invoice_number ".
    "ORDER BY invoice_number DESC LIMIT 1";
  $result = $DB->Execute($query) or die ("$query $l_queryfailed");
  $myresult = $result->fields;
  $billed_amount = $myresult['billed_amount'];
  $userbillingid = $myresult['billing_id'];

  $account_status = billingstatus($userbillingid);

  // insert the new vendor history line item
  $query = "INSERT into vendor_history ".
    "(datetime, entry_type, entry_date, vendor_name, vendor_bill_id, ".
    "vendor_cost, vendor_tax, vendor_item_id, user_services_id, account_status, ".
    "billed_amount) VALUES ".
    "(NOW(), '$entry_type', '$entry_date', '$vendor_name', '$vendor_bill_id', ".
    "'$vendor_cost', '$vendor_tax', '$vendor_item_id', '$userserviceid', '$account_status', ".
    "'$billed_amount')";
  $result = $DB->Execute($query) or die ("$query $l_queryfailed");

  // redirect back to the vendory history, now showing new entry
  echo "entered";
  print "<script language=\"JavaScript\">window.location.href = ".
    "\"index.php?load=services&type=module&vendor=on&userserviceid=$userserviceid\";</script>";
  
 } else {

  /*--------------------------------------------------------------------------*/
  // print the current vendor history
  /*--------------------------------------------------------------------------*/
  $query = "SELECT * FROM vendor_history WHERE user_services_id = $userserviceid";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("select vendor_history $l_queryfailed");

  print "<table cellpadding=5 cellspacing=1><tr bgcolor=\"#dddddd\">";
  print "<td>$l_entry_type</td>
	<td>$l_entry_date</td>
	<td>$l_vendor_name</td>
	<td>$l_vendor_bill_id</td>
	<td>$l_vendor_cost</td>
	<td>$l_vendor_tax</td>
	<td>$l_vendor_item_id</td>
	<td>$l_status</td>
	<td>$l_billedamount</td></tr>";

   while ($myresult = $result->FetchRow()) {
     $entry_type = $myresult['entry_type'];
     $entry_date = $myresult['entry_date'];
     $vendor_name = $myresult['vendor_name'];
     $vendor_bill_id = $myresult['vendor_bill_id'];
     $vendor_cost = $myresult['vendor_cost'];
     $vendor_tax = $myresult['vendor_tax'];
     $vendor_item_id = $myresult['vendor_item_id'];
     $account_status = $myresult['account_status'];
     $billed_amount = $myresult['billed_amount'];
     
     echo "<tr bgcolor=\"#eeeeee\"><td>$entry_type</td>".
       "<td>$entry_date</td>".
       "<td>$vendor_name</td>".
       "<td>$vendor_bill_id</td>".
       "<td>$vendor_cost</td>".
       "<td>$vendor_tax</td>".
       "<td>$vendor_item_id</td>".
       "<td>$account_status</td>".
       "<td>$billed_amount</td>".
       "</tr>";
   }

  echo "</table>";
  
  /*--------------------------------------------------------------------------*/
  // print a form to make a new vendor history entry
  /*--------------------------------------------------------------------------*/
  echo "<hr><h3>$l_add</h3>";
  echo "<form action=\"index.php\">".
    "<input type=hidden name=userserviceid value=$userserviceid>".
    "<input type=hidden name=load value=services>".
    "<input type=hidden name=type value=module>".
    "<input type=hidden name=vendor value=on>";
  echo "<table>";

  // drop down list (order|disconnect|change|bill)
  echo "<td>$l_entry_type</td><td>".
    "<select name=entry_type>".
    "<option value=\"\">$l_choose</option>".
    "<option value=\"order\">order</option>".
    "<option value=\"bill\">bill</option>".
    "<option value=\"change\">change</option>".
    "<option value=\"disconnect\">disconnect</option>".
    "</select></td><tr>";

  $mydate = date("Y-m-d");
  echo "<td>$l_entry_date</td><td><input type=text name=entry_date value=\"$mydate\"></td><tr>";

  // drop down list generated by vendor_names table
  echo "<td>$l_vendor_name</td><td><select name=vendor_name>";
  
  // get the list of service categories from the master_services table
  $query = "SELECT name FROM vendor_names ORDER BY name";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $nameresult = $DB->Execute($query) or die ("$l_queryfailed");
  
  while ($mynameresult = $nameresult->FetchRow()) {
    $name = $mynameresult['name'];    
    echo "<option value=\"$name\">$name</option>";    
  }
  echo "</select></td><tr>";
  
  echo "<td>$l_vendor_bill_id</td><td><input type=text name=vendor_bill_id value=\"0\"></td><tr>";
  echo "<td>$l_vendor_cost</td><td><input type=text name=vendor_cost value=\"0\"></td><tr>";
  echo "<td>$l_vendor_tax</td><td><input type=text name=vendor_tax value=\"0\"></td><tr>";
  echo "<td>$l_vendor_item_id</td><td><input type=text name=vendor_item_id value=\"0\"></td><tr>";
  
  // print submit button
  print "<td></td><td><input name=submit type=submit value=\"$l_submit\" ".
    "class=smallbutton></td></table></form><p>";
  
  echo "</table>";
  
 }
?>

