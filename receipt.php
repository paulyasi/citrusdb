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

// grab the payment type, cardnumber, and check number
$query = "SELECT payment_type, creditcard_number, check_number ".
  "FROM payment_history WHERE id = '$paymentid'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("payment history Query Failed");
$myresult = $result->fields;
$payment_type = $myresult['payment_type'];
$creditcard_number = $myresult['creditcard_number'];
$check_number = $myresult['check_number'];

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
"$l_accountnumber: $billing_account_number<br><br>\n\n".
"$billing_name<br>".
"$billing_company<br>".
"$billing_street<br>".
"$billing_city $billing_state $billing_zip<p>";

$human_date = humandate($payment_date, $lang);

if ($payment_type == "creditcard") {    
  // wipe out the middle of the card number
  $length = strlen($creditcard_number);
  $firstdigit = substr($creditcard_number, 0,1);
  $lastfour = substr($creditcard_number, -4);
  $creditcard_number = "$firstdigit" . "***********" . "$lastfour";
  
  echo "$l_paid with $payment_type ($creditcard_number), ".
    "$amount on $human_date for:\n<br><br>";
 } else {
  echo "$l_paid with $payment_type (number: $check_number), ".
    "$amount on $human_date for:\n<br><br>";    
 }

// get the resulting list of services that have payment applied with
// the matching payment_history_id
//$DB->debug = true;

$query = "SELECT bd.original_invoice_number, bd.paid_amount,".
  "bd.billed_amount, ms.service_description, tr.description FROM ".
  "billing_details bd ".
  "LEFT JOIN user_services us ON us.id = bd.user_services_id ".
  "LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
  "LEFT JOIN taxed_services ts ON ts.id = bd.taxed_services_id ".
  "LEFT JOIN tax_rates tr ON tr.id = ts.tax_rate_id ".
  "WHERE bd.payment_history_id = '$paymentid' ORDER BY bd.taxed_services_id";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("Receipt Query Failed");

echo "<table>";
echo "<td>$l_invoice</td><td>$l_description</td><td>$l_paid</td><td>$l_stillowed</td><tr>";

while ($myresult = $result->FetchRow()) {
  $invoice = $myresult['original_invoice_number'];
  $description = $myresult['service_description'];
  $tax_description = $myresult['description'];
  $paid_amount = sprintf("%.2f",$myresult['paid_amount']);
  $billed_amount = sprintf("%.2f",$myresult['billed_amount']);  

  $owed_amount = sprintf("%.2f",$billed_amount - $paid_amount);

  if ($tax_description) {
    // print the tax as description instead
  echo "<td>$invoice</td><td>&nbsp;&nbsp;&nbsp;$tax_description</td><td>$paid_amount</td><td>$owed_amount</td><tr>";
  } else {
  echo "<td>$invoice</td><td>$description</td><td>$paid_amount</td><td>$owed_amount</td><tr>";
  }
 }
echo "</table>";

?>
