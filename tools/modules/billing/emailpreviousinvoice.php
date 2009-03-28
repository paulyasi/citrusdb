<?php
// Copyright (C) 2002-2006  Paul Yasi <paul@citrusdb.org>
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

// include the billing functions
//include('include/billing.inc.php');

/*
//GET Variables
//$invoiceid = $base->input['invoiceid'];

//$query = "SELECT d.invoice_number, b.contact_email, b.id 
//FROM billing_details d
//LEFT JOIN billing b ON d.billing_id = b.id 
//WHERE d.invoice_number = '$invoiceid'";
//$DB->SetFetchMode(ADODB_FETCH_ASSOC);
//$result = $DB->Execute($query) or die ("$l_queryfailed");
//$myresult = $result->fields;

//$invoice_billing_id = $myresult['id'];
//$contact_email = $myresult['contact_email'];
//$message = outputinvoice($DB, $invoiceid, $lang, "html", NULL);		
		
// get the org billing email address for from address		
//$query = "SELECT g.email_billing 
	FROM billing b
	LEFT JOIN general g ON g.id = b.organization_id  
	WHERE b.id = $invoice_billing_id";
//$DB->SetFetchMode(ADODB_FETCH_ASSOC);
//$ib_result = $DB->Execute($query) or die ("$l_queryfailed");
//$mybillingresult = $ib_result->fields;
//$billing_email = $mybillingresult['email_billing'];	

// HTML Email Headers
//$headers = "From: $billing_email \n";
//$headers .= "Mime-Version: 1.0 \n";
//$headers .= "Content-type: text/html; charset=iso-8859-1 \n";
//$to = $contact_email;
//$subject = $l_einvoice;
//$message = "<html><body>" . $message . "</body></html>";
// send the mail
//mail ($to, $subject, $message, $headers);
//echo "sent invoice to $to\n";
*/

/*------------- NEW ------------*/

// get the invoice data to process now
$invoice_number = $base->input['invoiceid'];


$query = "SELECT d.invoice_number, b.contact_email, b.id 
FROM billing_details d
LEFT JOIN billing b ON d.billing_id = b.id 
WHERE d.invoice_number = '$invoice_number'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;

$contact_email = $myresult['contact_email'];
$invoice_billing_id = $myresult['id'];
$message = outputinvoice($DB, $invoice_number, $lang, "html", NULL);		

// get the org billing email address for from address		
$query = "SELECT g.org_name, g.org_street, g.org_city, ".
  "g.org_state, g.org_zip, g.email_billing ".
  "FROM billing b ".
  "LEFT JOIN general g ON g.id = b.organization_id  ".
  "WHERE b.id = $invoice_billing_id";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$ib_result = $DB->Execute($query) or die ("$query ib $l_queryfailed");
$mybillingresult = $ib_result->fields;
$billing_email = $mybillingresult['email_billing'];
$org_name = $mybillingresult['org_name'];
$org_street = $mybillingresult['org_street'];
$org_city = $mybillingresult['org_city'];
$org_state = $mybillingresult['org_state'];
$org_zip = $mybillingresult['org_zip'];

// get the total due from the billing_history
$query = "SELECT total_due FROM billing_history ".
  "WHERE id = '$invoice_number'";
$iv_result = $DB->Execute($query) or die ("iv $l_queryfailed");
$myinvoiceresult = $iv_result->fields;
$total_due = sprintf("%.2f",$myinvoiceresult['total_due']);

// build email message above invoice
$email_message = "$l_email_heading_thankyou $org_name.\n\n".
  "$l_email_heading_presenting ".
  "$total_due $l_to_lc \n\n".
  "$org_name\n".
  "$org_street\n".
  "$org_city $org_state $org_zip\n\n".
  "$l_email_heading_include.\n\n";

// HTML Email Headers
$headers = "From: $billing_email \n";
//$headers .= "Mime-Version: 1.0 \n";
//$headers .= "Content-type: text/html; charset=iso-8859-1 \n";
$to = $contact_email;
$subject = "$l_einvoice $org_name";
$message = "$email_message$message";
// send the mail
mail ($to, $subject, $message, $headers);
echo "sent invoice to $to<br>\n";