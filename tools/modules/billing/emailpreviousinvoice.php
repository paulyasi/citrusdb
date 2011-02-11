<?php
// Copyright (C) 2002-2010  Paul Yasi <paul@citrusdb.org>
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

// get the invoice data to process now
$invoice_number = $base->input['invoiceid'];

$query = "SELECT h.billing_id, b.contact_email ".
  "FROM billing_history h ".
  "LEFT JOIN billing b ON h.billing_id = b.id ".
  "WHERE h.id = '$invoice_number'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;

$contact_email = $myresult['contact_email'];
$invoice_billing_id = $myresult['billing_id'];

emailinvoice ($invoice_number,$contact_email,$invoice_billing_id);
echo "sent invoice to $contact_email<br>\n";
