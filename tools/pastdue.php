<html>
<body bgcolor="#ffffff">
<?php
// Copyright (C) 2003-2008  Paul Yasi (paul at citrusdb.org)
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

// GET Variables
if (!isset($base->input['organization_id'])) { 
  $base->input['organization_id'] = "1"; 
}
$organization_id = $base->input['organization_id'];

// ask for the organization that they want to view
echo "<FORM ACTION=\"index.php\" METHOD=\"GET\" name=\"form1\"> ".
"<input type=hidden name=load value=pastdue>".
"<input type=hidden name=type value=tools>";

// print list of organizations to choose from
$query = "SELECT id,org_name FROM general";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
echo "<b>$l_organizationname</b><td><select name=\"organization_id\"> ".
"<option value=\"\">$l_choose</option>";
while ($myresult = $result->FetchRow()) {
  $myid = $myresult['id'];
  $myorg = $myresult['org_name'];
  echo "<option value=\"$myid\">$myorg</option>";
 }
echo "</select><input type=\"SUBMIT\" NAME=\"submit\" value=\"$l_submit\">".
"</form><p>";


//$DB->debug = true;
/*--------------------------------------------------------------------*/
// Get the regular pastdue days from the general table
/*--------------------------------------------------------------------*/
$query = "SELECT * from general WHERE id = $organization_id";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
$org_name = $myresult['org_name'];
$regular_pastdue = $myresult['regular_pastdue'];
$regular_turnoff = $myresult['regular_turnoff'];
$regular_canceled = $myresult['regular_canceled'];
  
echo "<h2>$org_name</h2>";

/*--------------------------------------------------------------------*/
// customers with past due amounts regular pastdue days (late) days
/*--------------------------------------------------------------------*/
  
// get all the over due amounts between a certain date range
// and add the sum of the invoice items


$query = "SELECT bd.creation_date bd_creation_date, bd.user_services_id ".
  "bd_user_services_id, bd.invoice_number bd_invoice_number, ".
  "bd.billed_amount bd_billed_amount, bd.paid_amount bd_paid_amount, ".
  "bd.billing_id bd_billing_id, bh.payment_due_date bh_payment_due_date, ".
  "bh.id bh_id, us.id us_id, us.master_service_id us_master_service_id, ".
  "ms.id ms_id, ms.service_description ms_service_description, ".
  "bi.id bi_id, bi.account_number bi_account_number, bi.name bi_name, ".
  "bi.phone bi_phone, ROUND(SUM(bd.billed_amount),2) as total_billed_amount, ".
  "ROUND(SUM(bd.paid_amount), 2) AS total_paid_amount ".
  "FROM billing_details bd ".
  "LEFT JOIN user_services us ON bd.user_services_id = us.id ".
  "LEFT JOIN master_services ms ON us.master_service_id = ms.id ".
  "LEFT JOIN billing bi ON bd.billing_id = bi.id ".
  "LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
  "WHERE bd.billed_amount > bd.paid_amount ".
  "AND ms.organization_id = $organization_id ".
  "AND CURRENT_DATE >= DATE_ADD(bh.payment_due_date, ".
  "INTERVAL $regular_pastdue DAY) ".
  "AND CURRENT_DATE < DATE_ADD(bh.payment_due_date, ".
  "INTERVAL $regular_turnoff DAY) ".
  "AND bi.pastdue_exempt <> 'y' AND bi.pastdue_exempt <> 'bad_debt' ".
  "GROUP BY bd.invoice_number";

$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
	
// print heading	
print "<h3>$regular_pastdue $l_dayspastdue</h3><table cellpadding=10>";
print "<td><b>$l_duedate</b></td>".
"<td><b>$l_name</b></td>".
"<td><b>$l_service</b></td>".
"<td><b>$l_invoicenumber</b></td>".
"<td><b>$l_amount</b></td>".
"<td><b>$l_pastdue</b></td>".
"<td><b>$l_accountnumber<b></td>".
"<tr bgcolor=\"#eeeeee\">";

// loop through results and print out each
while ($myresult = $result->FetchRow()) {
  $payment_due_date = $myresult['bh_payment_due_date'];
  $creation_date = $myresult['bd_creation_date'];
  $user_services_id = $myresult['bd_user_services_id'];
  $invoice_number = $myresult['bd_invoice_number'];
  $amount_owed = $myresult['total_billed_amount'] - $myresult['total_paid_amount'];
  $service_description = $myresult['ms_service_description'];
  $account_num = $myresult['bi_account_number'];
  $billing_name = $myresult['bi_name'];
  $total_billed_amount = $myresult['total_billed_amount'];
	
  print "<td>$payment_due_date</td>".
    "<td>$billing_name</td>".
    "<td>$service_description</td>".
    "<td>$invoice_number</td>".
    "<td>$total_billed_amount</td>".
    "<td>$amount_owed</td>".
    "<td><a href=\"index.php?load=viewaccount&type=fs&acnum=$account_num\" ".
    "target=\"_blank\">$account_num</a></td>".
    "<tr bgcolor=\"#eeeeee\">\n";
 }

// end table listing
print "</table><p>";

/*--------------------------------------------------------------------*/
// customers with past due amounts regular_turnoff days (turn off) days
/*--------------------------------------------------------------------*/
        
// get all the over due amounts between a certain date range and
// add the sum of the invoice items


$query = "SELECT bd.creation_date bd_creation_date, ".
  "bd.user_services_id bd_user_services_id, bd.invoice_number ".
  "bd_invoice_number, bd.billed_amount bd_billed_amount, ".
  "bd.paid_amount bd_paid_amount, bd.billing_id bd_billing_id, ".
  "bh.payment_due_date bh_payment_due_date, bh.id bh_id, ". 
  "us.id us_id, us.master_service_id us_master_service_id, ".
  "ms.id ms_id, ms.service_description ms_service_description, ".
  "bi.id bi_id, bi.account_number bi_account_number, bi.name bi_name, ".
  "bi.phone bi_phone, ROUND(SUM(bd.billed_amount),2) as total_billed_amount, ".
  "ROUND(SUM(bd.paid_amount), 2) AS total_paid_amount ".
  "FROM billing_details bd ".
  "LEFT JOIN user_services us ON bd.user_services_id = us.id ".
  "LEFT JOIN master_services ms ON us.master_service_id = ms.id ".
  "LEFT JOIN billing bi ON bd.billing_id = bi.id ".
  "LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
  "WHERE bd.billed_amount > bd.paid_amount ".
  "AND ms.organization_id = $organization_id ".
  "AND CURRENT_DATE >= DATE_ADD(bh.payment_due_date, ".
  "INTERVAL $regular_turnoff DAY) ".
  "AND CURRENT_DATE < DATE_ADD(bh.payment_due_date, ".
  "INTERVAL $regular_canceled DAY) ".
  "AND bi.pastdue_exempt <> 'y' AND bi.pastdue_exempt <> 'bad_debt' ".
  "GROUP BY bd.invoice_number";

$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
        
// print heading
print "<h3>$regular_turnoff $l_days $l_turnedoff</h3><table cellpadding=10>";
print "<td><b>$l_duedate</b></td>".
"<td><b>$l_name</b></td>".
"<td><b>$l_service</b></td>".
"<td><b>$l_invoicenumber</b></td>".
"<td><b>$l_amount</b></td>".
"<td><b>$l_pastdue</b></td>".
"<td><b>$l_accountnumber<b></td>".
"<tr bgcolor=\"#eeeeee\">";

// loop through results and print out each
while ($myresult = $result->FetchRow()) {
  $payment_due_date = $myresult['bh_payment_due_date'];
  $user_services_id = $myresult['bd_user_services_id'];
  $invoice_number = $myresult['bd_invoice_number'];
  $amount_owed = $myresult['total_billed_amount'] - $myresult['total_paid_amount'];
  $service_description = $myresult['ms_service_description'];
  $account_num = $myresult['bi_account_number'];
  $billing_name = $myresult['bi_name'];
  $total_billed_amount = $myresult['total_billed_amount'];
	
  print "<td>$payment_due_date</td>".
    "<td>$billing_name</td>".
    "<td>$service_description</td>".
    "<td>$invoice_number</td>".
    "<td>$total_billed_amount</td>".
    "<td>$amount_owed</td>".
    "<td><a href=\"index.php?load=viewaccount&type=fs&acnum=$account_num\" target=\"_blank\">$account_num</a></td>".
    "<tr bgcolor=\"#eeeeee\">\n";
 }
	
// end table listing
print "</table><p>";

/*--------------------------------------------------------------------*/
// customers with past due amounts over regular_canceled (collections) days
// ONLY for services that are carrier_dependent
/*--------------------------------------------------------------------*/

// get all the over due amounts between a certain date range and
// add the sum of the invoice items


$query = "SELECT bd.creation_date bd_creation_date, bd.user_services_id ".
  "bd_user_services_id, bd.invoice_number bd_invoice_number, ".
  "bd.billed_amount bd_billed_amount, bd.paid_amount bd_paid_amount, ".
  "bd.billing_id bd_billing_id, bh.payment_due_date bh_payment_due_date, ".
  "bh.id bh_id, us.id us_id, us.master_service_id us_master_service_id, ".
  "ms.id ms_id, ms.service_description ms_service_description, ".
  "bi.id bi_id, bi.account_number bi_account_number, bi.name bi_name, ".
  "bi.phone bi_phone, ROUND(SUM(bd.billed_amount),2) as total_billed_amount, ".
  "ROUND(SUM(bd.paid_amount), 2) AS total_paid_amount ".
  "FROM billing_details bd ".
  "LEFT JOIN user_services us ON bd.user_services_id = us.id ".
  "LEFT JOIN master_services ms ON us.master_service_id = ms.id ".
  "LEFT JOIN billing bi ON bd.billing_id = bi.id ".
  "LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
  "WHERE bd.billed_amount > bd.paid_amount ".
  "AND ms.organization_id = $organization_id ".
  "AND CURRENT_DATE >= DATE_ADD(bh.payment_due_date, ".
  "INTERVAL $regular_canceled DAY) ".
  "AND bi.pastdue_exempt <> 'y' AND bi.pastdue_exempt <> 'bad_debt' ".
  "GROUP BY bd.invoice_number";
	
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
        
// print heading
print "<h3>$l_over $regular_canceled $l_days $l_collections</h3>".
"<table cellpadding=10>";
print "<td><b>$l_duedate</b></td>".
"<td><b>$l_name</b></td>".
"<td><b>$l_service</b></td>".
"<td><b>$l_invoicenumber</b></td>".
"<td><b>$l_amount</b></td>".
"<td><b>$l_pastdue</b></td>".
"<td><b>$l_accountnumber<b></td>".
"<tr bgcolor=\"#eeeeee\">";

// loop through results and print out each
while ($myresult = $result->FetchRow()) {
  $payment_due_date = $myresult['bh_payment_due_date'];
  $user_services_id = $myresult['bd_user_services_id'];
  $invoice_number = $myresult['bd_invoice_number'];
  $amount_owed = $myresult['total_billed_amount'] - $myresult['total_paid_amount'];
  $service_description = $myresult['ms_service_description'];
  $account_num = $myresult['bi_account_number'];
  $billing_name = $myresult['bi_name'];
  $total_billed_amount = $myresult['total_billed_amount'];
  
  // TODO: check each service assigned to this invoice to
  // see if it has the carrier_dependent status
  // before printing it out in this list
  $printthis = FALSE;
  $query = "SELECT ms.carrier_dependent ".
    "FROM billing_details bd ".
    "LEFT JOIN user_services us ON us.id = bd.user_services_id ".
    "LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
    "WHERE bd.invoice_number = $invoice_number";
  $detailresult = $DB->Execute($query) or die ("detailresult $query $l_queryfailed");
  while ($mydetailresult = $detailresult->FetchRow()) {
    $carrier_dependent = $mydetailresult['carrier_dependent'];
    if ($carrier_dependent == 'y') {
      $printthis = TRUE;
    }
  }

  if ($printthis == TRUE) {
    print "<td>$payment_due_date</td>".
      "<td>$billing_name</td>".
      "<td>$service_description</td>".
      "<td>$invoice_number</td>".
      "<td>$total_billed_amount</td>".
      "<td>$amount_owed</td>".
      "<td><a href=\"index.php?load=viewaccount&type=fs&".
      "acnum=$account_num\" target=\"_blank\">$account_num</a></td>".
      "<tr bgcolor=\"#eeeeee\">\n";
  }
 }

// end table listing
print "</table><p>";


?>
</body>
</html>
