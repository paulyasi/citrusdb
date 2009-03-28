<?php
// Copyright (C) 2003-2005  Paul Yasi <paul@citrusdb.org>
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


//GET Variables
$submit = $base->input['submit'];
$billingdate = $base->input['billingdate'];
$batchid = $base->input['batchid'];

if ($submit) {  // maybe this can be combined with the exportcc.php once it's all worked out

	// go through a list of billing_id's that are to be billed today
	$query = "SELECT DISTINCT billing_id FROM billing_details WHERE creation_date = CURRENT_DATE AND batch = '$batchid'";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
        $distinctresult = $DB->Execute($query) or die ("$l_queryfailed");

	// make a new billing_history record for each billing id group - use the billing_history_id as the invoice number
	while ($myresult = $distinctresult->FetchRow())
        {
        	$mybilling_id = $myresult['billing_id'];
		$query = "INSERT INTO billing_history (billing_date, created_by, record_type, billing_type, billing_id) 
			VALUES (CURRENT_DATE,'$user','bill','prepay','$mybilling_id')";
		$historyresult = $DB->Execute($query) or die ("$l_queryfailed");
		
		// set the invoice number for billing_details that have the corresponding billing_id 
		// and haven't been assigned an invoice number yet
		$myinsertid = $DB->Insert_ID();
		$invoice_number=$myinsertid;
		$query = "UPDATE billing_details SET invoice_number = '$invoice_number' 
			WHERE billing_id = '$mybilling_id' AND invoice_number IS NULL";
		$invnumresult = $DB->Execute($query) or die ("Update Details Query Failed");
			
		// get the billing name and address for the export

		// get the data for the service charges still pending and what services they have
		$query = "SELECT d.billing_id d_billing_id, d.creation_date d_creation_date, d.user_services_id d_user_services_id,
	        d.invoice_number d_invoice_number, d.billed_amount d_billed_amount, d.paid_amount d_paid_amount, 
		u.id u_id, u.account_number u_account_number, 
		m.id m_id, m.service_description m_description, m.options_table m_options_table
	        FROM billing_details d
	        LEFT JOIN user_services u ON d.user_services_id = u.id
	        LEFT JOIN master_services m ON u.master_service_id = m.id
	        WHERE d.billing_id = $mybilling_id";
        
	        $invoiceresult = $DB->Execute($query) or die ("$l_queryfailed"); // used to show the invoice
		$totalresult = $DB->Execute($query) or die ("$l_queryfailed");  // used to add up the total charges

		// get the data for the billing name and address and stuff from the billing table
		$query = "SELECT * FROM billing WHERE id = $mybilling_id";
		$billingresult = $DB->Execute($query) or die ("$l_queryfailed");

		$mybillingresult = $billingresult->fields;
		$billing_name = $mybillingresult['name'];
		$billing_company = $mybillingresult['company'];
		$billing_street = $mybillingresult['street'];
		$billing_city = $mybillingresult['city'];
		$billing_state = $mybillingresult['state'];
		$billing_zip = $mybillingresult['zip'];
		$billing_acctnum = $mybillingresult['account_number'];
		$billing_ccnum = $mybillingresult['creditcard_number'];
		$billing_ccexp = $mybillingresult['creditcard_expire'];
		$billing_fromdate = $mybillingresult['from_date'];
		$billing_todate = $mybillingresult['to_date'];
		$billing_payment_due_date = $mybillingresult['payment_due_date'];
		$billing_email = $mybillingresult['contact_email'];

		// get the data for the general company/organization info printed on the bill
                $query = "SELECT * FROM general";
                $generalresult = $DB->Execute($query) or die ("$l_queryfailed");

		$mygeneralresult = $generalresult->fields;
                $org_name = $mygeneralresult['org_name'];
                $org_street = $mygeneralresult['org_street'];
                $org_city = $mygeneralresult['org_city'];
                $org_state = $mygeneralresult['org_state'];
                $org_zip = $mygeneralresult['org_zip'];
                $phone_billing = $mygeneralresult['phone_billing'];
                $email_billing = $mygeneralresult['email_billing'];

		$invoicetotal = 0;
		$mydate = date("Y-m-d");

		// get the invoicetotal
		while ($mytotalresult = $totalresult->FetchRow())
                {
                        $billed_amount = $mytotalresult['d_billed_amount'] - $mytotalresult['d_paid_amount'];
                        $invoicetotal = $billed_amount + $invoicetotal;
                }
		$precisetotal = sprintf("%.2f", $invoicetotal);

		// print out the information to be exported (save this to a file once we know it's right)
		// charge type, account num, tracking num (invoice#), CC num, CC exp, amount, zip code, street addr

		print "REMINDER,$billing_email,$billing_acctnum,$invoice_number,$billing_ccnum,$billing_ccexp,$precisetotal,$billing_zip,$billing_street<br>\n";

		// put a record in the billing_history to note 
		// the invoice total, tax, and details
		$query = "UPDATE billing_history 
			  SET from_date = '$billing_fromdate',
			  to_date = '$billing_todate',
			  payment_due_date = '$billing_payment_to_date',
			  total_due = '$precisetotal'
			  WHERE id = '$invoice_number'";
                $historyresult = $DB->Execute($query) 
			or die ("$l_queryfailed");
        
		
	}

}

?>

