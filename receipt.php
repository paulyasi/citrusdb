<?php   
// Copyright (C) 2009  Paul Yasi (paul at citrusdb.org)
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

// GET Variables
$paymentid = $base->input['paymentid'];
$amount = $base->input['amount'];
$invoice_number = $base->input['invoicenum'];
$billingid = $base->input['billingid'];
$payment_date = $base->input['date'];

/*-------------------------------------------------------------------------*/
// print paid_amounts from billing_details
/*-------------------------------------------------------------------------*/

$query = "SELECT * FROM billing b ".
  "LEFT JOIN general g ON g.id = b.organization_id ".
  "WHERE b.id = $billingid";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("billing table Query Failed");
$myresult = $result->fields;
$billing_name = $myresult['name'];
$billing_company = $myresult['company'];
$billing_street = $myresult['street'];
$billing_city = $myresult['city'];
$billing_state = $myresult['state'];
$billing_zip = $myresult['zip'];
$billing_account_number = $myresult['account_number'];
$org_name = $myresult['org_name'];

echo "<h2>$org_name</h2>".
"<h3>$l_paymentreceipt</h3>".
"$billing_name<br>".
"$billing_company<br>".
"$billing_street<br>".
"$billing_city $billing_state $billing_zip<p>";

$payment_date = humandate($payment_date, $lang);

echo "$l_paid: $amount on $payment_date<p>";

/*--------------------------------------------------------------------
// get the resulting list of services to have payments removed from

if ($invoice_number > 0) {
  $query = "SELECT * FROM billing_details ".
    "WHERE paid_amount > 0 AND invoice_number = $invoice_number";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("invoice1 Query Failed");
  $invoiceresult = $DB->Execute($query) or die ("invoice2 $l_queryfailed");
  
  // update values with missing information
  $myresult = $invoiceresult->fields;
  $billingid = $myresult['billing_id'];
  
  // else remove payments by billing id
 } else {
  $query = "SELECT * FROM billing_details 
		WHERE paid_amount > 0 AND billing_id = $billingid";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("billingid $l_queryfailed");	
 }


// go through the list and subtract the payment from each until
// the amount is depleted

while (($myresult = $result->FetchRow()) and (round($amount,2) > 0)) {
  $id = $myresult['id'];
  $paid_amount = sprintf("%.2f",$myresult['paid_amount']);
  
 }
-------------------------------------------------------------------------*/

?>
