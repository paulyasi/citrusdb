<?php

// Copyright (C) 2002-2008  Paul Yasi (paul at citrusdb.org)
// read the README file for more information


function automatic_to_date ($DB, $from_date, $billing_type, $billing_id) {
  /*--------------------------------------------------------------------*/
  // set the to_date automatically according 
  // to the from_date and billing_type
  /*--------------------------------------------------------------------*/
  // figure out the billing frequency
  if ($from_date == "" OR $from_date == "0000-00-00") {
    $query = "UPDATE billing SET to_date = NULL WHERE id = '$billing_id'";
    $updateresult = $DB->Execute($query) or die ("query failed");
  } else {
    $query = "SELECT * FROM billing_types WHERE id = $billing_type";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("query failed");
    $myresult = $result->fields;
    $frequency = $myresult['frequency'];
    // add the number of frequency months to the from_date 
    // to get what the to_date should be set to
    $query = "UPDATE billing 
	SET to_date = DATE_ADD('$from_date', INTERVAL '$frequency' MONTH) 
	WHERE id = '$billing_id'";
    $updateresult = $DB->Execute($query) or die ("query failed");	
  }
}

//$DB->debug = true;
function get_nextbatchnumber($DB) {	
	/*--------------------------------------------------------------------*/
	// determine the next available batch number
	/*--------------------------------------------------------------------*/
        // insert empty into the batch to generate next value
        $query = "INSERT INTO batch () VALUES ()";
        $batchquery = $DB->Execute($query) or die ("Batch ID Query Failed");
        // get the value just inserted
        $batchid = $DB->Insert_ID();
	return $batchid;
}

function add_taxdetails($DB, $billingdate, $bybillingid, $billingmethod, $batchid, $organization_id) {
	/*--------------------------------------------------------------------*/
	// Add taxes to the bill
	// database connector, billing date, billing method (invoice, prepay , 
	// creditcard etc.), batch id
	// do this before services to make sure one time services get taxed
	// before they are removed
	/*--------------------------------------------------------------------*/

	//
	// query for taxed services that are billed on the specified date
	// or a specific billing id
	//
	if ($bybillingid == NULL) {
		$query = "SELECT ts.id ts_id, 
		ts.master_services_id ts_serviceid, 
		ts.tax_rate_id ts_rateid, 
		ms.id ms_id, ms.service_description ms_description, 
		ms.pricerate ms_pricerate, ms.frequency ms_freq, 
		tr.id tr_id, tr.description tr_description, tr.rate tr_rate, 
		tr.if_field tr_if_field, tr.if_value tr_if_value,
tr.percentage_or_fixed tr_percentage_or_fixed, 
		us.master_service_id us_msid, us.billing_id us_bid, 
		us.removed us_removed, us.account_number us_account_number, 
        	us.usage_multiple us_usage_multiple,  te.account_number te_account_number, te.tax_rate_id te_tax_rate_id, 
		b.id b_id, b.billing_type b_billing_type, 
		t.id t_id, t.frequency t_freq, t.method t_method  
		FROM taxed_services ts
		LEFT JOIN user_services us ON us.master_service_id = ts.master_services_id 
		LEFT JOIN master_services ms ON ms.id = ts.master_services_id 
		LEFT JOIN tax_rates tr ON tr.id = ts.tax_rate_id 
		LEFT JOIN tax_exempt te ON te.account_number = us.account_number 
			AND te.tax_rate_id = tr.id 
		LEFT JOIN billing b ON us.billing_id = b.id 
		LEFT JOIN billing_types t ON b.billing_type = t.id
        	WHERE b.next_billing_date = '$billingdate' 
		AND b.organization_id = '$organization_id' 
		AND t.method = '$billingmethod' AND us.removed <> 'y'";
	} else {
		$query = "SELECT ts.id ts_id, 
		ts.master_services_id ts_serviceid, 
		ts.tax_rate_id ts_rateid, 
		ms.id ms_id, ms.service_description ms_description, 
		ms.pricerate ms_pricerate, ms.frequency ms_freq, 
		tr.id tr_id, tr.description tr_description, tr.rate tr_rate, 
		tr.if_field tr_if_field, tr.if_value tr_if_value,
tr.percentage_or_fixed tr_percentage_or_fixed, 
		us.master_service_id us_msid, us.billing_id us_bid, 
		us.removed us_removed, us.account_number us_account_number, 
        	us.usage_multiple us_usage_multiple, te.account_number te_account_number, te.tax_rate_id te_tax_rate_id, 
		b.id b_id, b.billing_type b_billing_type,  
		t.id t_id, t.frequency t_freq, t.method t_method  
		FROM taxed_services ts
		LEFT JOIN user_services us ON us.master_service_id = ts.master_services_id
		LEFT JOIN master_services ms ON ms.id = ts.master_services_id
		LEFT JOIN tax_rates tr ON tr.id = ts.tax_rate_id 
		LEFT JOIN tax_exempt te ON te.account_number = us.account_number 
			AND te.tax_rate_id = tr.id 
		LEFT JOIN billing b ON us.billing_id = b.id 
		LEFT JOIN billing_types t ON b.billing_type = t.id
        	WHERE b.id = '$bybillingid' AND us.removed <> 'y'";
	}	
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$taxresult = $DB->Execute($query) or die ("Taxes Query Failed");
	
	// count the number of taxes
	$i = 0;

	while ($mytaxresult = $taxresult->FetchRow())
	{
		$billing_id = $mytaxresult['b_id'];
		$taxed_services_id = $mytaxresult['ts_id'];
		$service_freq = $mytaxresult['ms_freq'];
		$billing_freq = $mytaxresult['t_freq'];	
		$if_field = $mytaxresult['tr_if_field'];
		$if_value = $mytaxresult['tr_if_value'];
		$percentage_or_fixed = $mytaxresult['tr_percentage_or_fixed'];
		$my_account_number = $mytaxresult['us_account_number'];
		$usage_multiple = $mytaxresult['us_usage_multiple'];
		$pricerate = $mytaxresult['ms_pricerate'];
		$taxrate = $mytaxresult['tr_rate'];
		$tax_rate_id = $mytaxresult['tr_id'];
		$tax_exempt_rate_id = $mytaxresult['te_tax_rate_id'];

		// check that they are not exempt
		if ($tax_exempt_rate_id <> $tax_rate_id)
		{
			// check the if_field before adding to see if 
			// the tax applies to this customer
			if ($if_field <> '') {
			$ifquery = "SELECT $if_field FROM customer 
			WHERE account_number = '$my_account_number'";
			$DB->SetFetchMode(ADODB_FETCH_NUM);
			$ifresult = $DB->Execute($ifquery) or die ("Query Failed");	
			$myifresult = $ifresult->fields;
        		$checkvalue = $myifresult[0];
			} else {
				$checkvalue = TRUE;
				$if_value = TRUE;
			}

			if ($checkvalue == $if_value) {
			  if ($percentage_or_fixed == 'percentage') {
			    if ($service_freq > 0) {
			      $servicecost = ($billing_freq * $service_freq)
				* ($pricerate * $usage_multiple);
			      $tax_amount = $taxrate * $servicecost; 
			    } else {
			      $servicecost = $pricerate * $usage_multiple;
			      $tax_amount = $taxrate * $servicecost;
			    }
			  } else {
			    // fixed fee amount
			    $tax_amount = $taxrate;
			  }
		
				// round the tax to two decimal places
				$tax_amount = sprintf("%.2f", $tax_amount);

				//
				// Insert tax result into billing_details
				//
				$query = "INSERT INTO billing_details 
				(billing_id, creation_date, taxed_services_id, 
				billed_amount, batch)
        	                VALUES ('$billing_id',CURRENT_DATE,
			'$taxed_services_id','$tax_amount','$batchid')";
				$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		                $invoiceresult = $DB->Execute($query) 
					or die ("Query Failed");	
				$i++;
			} //endif if_field
		} // endif exempt
	}

	return $i; // send back number of taxes found
}
	

function add_servicedetails($DB, $billingdate, $bybillingid, $billingmethod, $batchid, $organization_id) {
	/*--------------------------------------------------------------------*/
	// Add services to the bill
	/*--------------------------------------------------------------------*/
	// join the user_services, billing, billing_types, and master_services 
	// together to find what to put into billing_details
	if ($bybillingid == NULL) {
		$query = "SELECT u.id u_id, u.account_number u_ac, 
		u.master_service_id u_msid, u.billing_id u_bid, 
		u.removed u_rem, u.usage_multiple u_usage, 
		b.next_billing_date b_next_billing_date, b.id b_id, 
		b.billing_type b_type, 
		t.id t_id, t.frequency t_freq, t.method t_method, 
		m.id m_id, m.pricerate m_pricerate, m.frequency m_freq 
		FROM user_services u
		LEFT JOIN master_services m ON u.master_service_id = m.id
		LEFT JOIN billing b ON u.billing_id = b.id
		LEFT JOIN billing_types t ON b.billing_type = t.id
		WHERE b.next_billing_date = '$billingdate' 
		AND b.organization_id = '$organization_id' 
		AND t.method = '$billingmethod' AND u.removed <> 'y'";
	} else {
		$query = "SELECT u.id u_id, u.account_number u_ac, 
		u.master_service_id u_msid, u.billing_id u_bid, 
		u.removed u_rem, u.usage_multiple u_usage, 
		b.next_billing_date b_next_billing_date, b.id b_id, 
		b.billing_type b_type, 
		t.id t_id, t.frequency t_freq, t.method t_method, 
		m.id m_id, m.pricerate m_pricerate, m.frequency m_freq 
		FROM user_services u
		LEFT JOIN master_services m ON u.master_service_id = m.id
		LEFT JOIN billing b ON u.billing_id = b.id
		LEFT JOIN billing_types t ON b.billing_type = t.id
		WHERE b.id = '$bybillingid' 
		AND t.method = '$billingmethod' AND u.removed <> 'y'";
	}
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("Services Query Failed");

	$i = 0; // count the billing services
	while ($myresult = $result->FetchRow())
	{
		$billing_id = $myresult['u_bid'];
		$user_services_id = $myresult['u_id'];
		$pricerate = $myresult['m_pricerate'];
		$usage_multiple = $myresult['u_usage'];
		$service_freq = $myresult['m_freq'];
		$billing_freq = $myresult['t_freq'];

		if ($service_freq > 0)
		{
			$billed_amount = ($billing_freq/$service_freq)
				*($pricerate*$usage_multiple);
		}
		else
		{
			// Remove one time services
			$billed_amount = ($pricerate*$usage_multiple);
			$today = date("Y-m-d");
			delete_service($user_services_id, 'onetime', $today);
		} // end if

		//print "$billing_id $user_services_id $pricerate<br>";
		
		// insert this into the billing_details
		$query = "INSERT INTO billing_details (billing_id, 
		creation_date, user_services_id, billed_amount, batch) 
		VALUES ('$billing_id',CURRENT_DATE,'$user_services_id',
		'$billed_amount','$batchid')";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$invoiceresult = $DB->Execute($query) or die ("Query Failed");

		$i ++;
	} // end while

	//echo "$i services for found!<br>";
	return $i; 
	// return the number of services found
}


function update_rerundetails($DB, $billingdate, $batchid, $organization_id) {
	/*----------------------------------------------------------------*/
	// Update Reruns to the bill
	/*----------------------------------------------------------------*/
	
	//$DB->debug = true;
	
	// select the billing id's that have matching rerun dates
	$query = "SELECT id, rerun_date FROM billing 
	WHERE rerun_date = '$billingdate' AND organization_id = '$organization_id'";
        $DB->SetFetchMode(ADODB_FETCH_ASSOC);        
	$result = $DB->Execute($query) or die ("Rerun Query Failed"); 
	$i = 0;
	while ($myresult = $result->FetchRow()) {
		$billing_id = $myresult['id'];
		
		$query = "UPDATE billing_details SET         
		batch = '$batchid',        
		invoice_number = NULL, 
		creation_date = CURRENT_DATE         
		WHERE billing_id = $billing_id AND billed_amount > paid_amount";
		
		$updateresult = $DB->Execute($query) or die ("Update Details Query Failed");
		
		$i++;	
	}		
	
	//echo "$i accounts rerun<p>";
	return $i; // return the number of reruns updated
}

function create_billinghistory($DB, $batchid, $billingmethod, $user) {
  global $lang;
  include ("$lang");  
  
	// go through a list of billing_id's that are to be billed today
	$query = "SELECT DISTINCT billing_id FROM billing_details 
	WHERE creation_date = CURRENT_DATE AND batch = '$batchid'";
        $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$distinctresult = $DB->Execute($query) or die ("Query Failed");

	// set billingdate as today for checking rerun dates and stuff
	$billingdate = date("Y-m-d");

	// make a new billing_history record for each billing id group 
	// - use the billing_history_id as the invoice number
	while ($myresult = $distinctresult->FetchRow()) {
        	$mybilling_id = $myresult['billing_id'];
		$query = "INSERT INTO billing_history 
		(billing_date, created_by, record_type, billing_type, 
		billing_id) 
		VALUES (CURRENT_DATE,'$user','bill','$billingmethod',
		'$mybilling_id')";
		$historyresult = $DB->Execute($query) 
			or die ("Query Failed");
	
	
		// set the invoice number for billing_details that have 
		// the corresponding billing_id 
		// and haven't been assigned an invoice number yet
		$myinsertid = $DB->Insert_ID();
		$invoice_number=$myinsertid;
		$query = "UPDATE billing_details 
			SET invoice_number = '$invoice_number' 
			WHERE billing_id = '$mybilling_id' 
			AND invoice_number IS NULL";
		$invnumresult = $DB->Execute($query) or die ("Query Failed");
			
		// get the data for the service charges still pending 
		// and what services they have
		
		$query = "SELECT d.billing_id d_billing_id, 
		d.creation_date d_creation_date, 
		d.user_services_id d_user_services_id,
	        d.invoice_number d_invoice_number, 
		d.paid_amount d_paid_amount, d.billed_amount d_billed_amount,
		d.taxed_services_id d_taxed_services_id, 
		u.id u_id, u.account_number u_account_number, 
		m.id m_id, m.service_description m_description, 
		m.options_table m_options_table, 
		ts.id ts_id, ts.master_services_id 
		ts_master_services_id, ts.tax_rate_id ts_tax_rate_id, 
		tr.id tr_id, tr.description tr_description  
	        FROM billing_details d
	        LEFT JOIN user_services u ON d.user_services_id = u.id
	        LEFT JOIN master_services m ON u.master_service_id = m.id 
		LEFT JOIN taxed_services ts ON d.taxed_services_id = ts.id 
		LEFT JOIN tax_rates tr ON ts.tax_rate_id = tr.id 
	        WHERE d.billing_id = $mybilling_id 
		AND d.paid_amount != d.billed_amount 
		ORDER BY d.taxed_services_id"; 	
        
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	        $invoiceresult = $DB->Execute($query) or die ("Query Failed"); 
		// used to show the invoice
		$totalresult = $DB->Execute($query) or die ("Query Failed"); 
		// used to add up the total charges

		// get the data for the billing dates from the billing table
		$query = "SELECT * FROM billing WHERE id = $mybilling_id";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$billingresult = $DB->Execute($query) or die ("Query Failed");

		$myresult = $billingresult->fields;
		$billing_name = $myresult['name'];
		$billing_company = $myresult['company'];
		$billing_street = $myresult['street'];
		$billing_zip = $myresult['zip'];
		$billing_acctnum = $myresult['account_number'];
		$billing_ccnum = $myresult['creditcard_number'];
		$billing_ccexp = $myresult['creditcard_expire'];
		$billing_fromdate = $myresult['from_date'];
		$billing_todate = $myresult['to_date'];
		$billing_payment_due_date = $myresult['payment_due_date'];
		$billing_rerun_date = $myresult['rerun_date'];
		$billing_notes = $myresult['notes'];	
		$invoicetotal = 0;
	
		// if this is a rerun, then set the payment_due_date to today
		if ($billing_rerun_date == $billingdate) {
			$billing_payment_due_date = $billing_rerun_date;
			$billing_fromdate = $billing_rerun_date;
			$billing_todate = $billing_rerun_date;
		}
	
		//
		// calculate amounts for history
		//

		// initialize past amounts
		$pastdue = 0;
		$new_charges = 0;
		$tax_due = 0;
	
		while($myinvresult = $invoiceresult->FetchRow())
		{
			$user_services_id = $myinvresult['d_user_services_id'];	
			$currinvoicenumber = $myinvresult['d_invoice_number'];
			$billing_id = $myinvresult['d_billing_id'];
			$creation_date = $myinvresult['d_creation_date'];
			$service = $myinvresult['m_description'];
			$taxdescription = $myinvresult['tr_description'];
			$billed_amount = $myinvresult['d_billed_amount'];
			$paid_amount = $myinvresult['d_paid_amount'];

			// get the difference between billed and paid amount
			$billed_amount = $billed_amount - $paid_amount;

			if ($currinvoicenumber == $invoice_number) {
				// new charges
				$billed_amount = sprintf("%.2f",$billed_amount);
				$new_charges = $new_charges + $billed_amount;
				// new taxes
				if ($taxdescription <> NULL) {
					$tax_due = $tax_due + $billed_amount;
				}
			} else {
				// past due charges
				$pastdue = $pastdue + $billed_amount;
			} // end if
		} // end while
	
		//
		// Apply Credits
		// check if they have any credits and apply as payments
		//
		apply_credits($DB, $mybilling_id);

		// calculate the invoicetotal
		while ($myresult = $totalresult->FetchRow())
                {
                        $billed_amount = $myresult['d_billed_amount'] - $myresult['d_paid_amount'];
                        $invoicetotal = $billed_amount + $invoicetotal;
                }
	
		$precisetotal = sprintf("%.2f", $invoicetotal);

		// get the absolute value of the total
		$abstotal = abs($precisetotal);
	
		$total_due = $precisetotal;
		$new_charges = sprintf("%.2f",$new_charges);
		$pastdue = sprintf("%.2f",$pastdue);
		$tax_due = sprintf("%.2f",$tax_due);
		$total_due = sprintf("%.2f",$total_due);
	
		// check the pastdue status to see what note to include
		$status = billingstatus($mybilling_id);
	
		// get the invoice notes from general
		$query = "SELECT g.default_invoicenote, g.pastdue_invoicenote, 
			g.turnedoff_invoicenote, g.collections_invoicenote
			FROM billing b
			LEFT JOIN general g ON g.id = b.organization_id 
			WHERE b.id = $mybilling_id";
		$generalresult = $DB->Execute($query) 
			or die ("$l_queryfailed");
		$mygenresult = $generalresult->fields;
		$default_invoicenote = $mygenresult['default_invoicenote'];
		$pastdue_invoicenote = $mygenresult['pastdue_invoicenote'];
		$turnedoff_invoicenote = $mygenresult['turnedoff_invoicenote'];
		$collections_invoicenote = $mygenresult['collections_invoicenote'];

	
		// if the individual customer's billing notes are blank
		// then use the automatic invoice notes from general config
		if ($billing_notes == '')
		{
		  if ($status == $l_pastdue) {
		    $billing_notes = $pastdue_invoicenote;
		  } elseif ($status == $l_turnedoff) {
		    $billing_notes = $turnedoff_invoicenote;
		  } elseif ($status == $l_collections) {
		     $billing_notes = $collections_invoicenote;
		  } else {
		    $billing_notes = $default_invoicenote;
		  }  
		}

		// update the billing history record	
		$query = "UPDATE billing_history SET
		  from_date = '$billing_fromdate',
		  to_date = '$billing_todate',
		  payment_due_date = '$billing_payment_due_date',
		  new_charges = '$new_charges',
		  past_due = '$pastdue',
		  late_fee = '0',
		  tax_due = '$tax_due',
		  total_due = '$total_due',
		  notes = '$billing_notes' 
		WHERE id = '$invoice_number'";
		$historyresult = $DB->Execute($query) 
			or die("history update failed");
		
		if ($billing_rerun_date <> $billingdate) {
			
			//// update the Next Billing Date to whatever 
			// the billing type dictates +1 +2 +6 etc.			
			// get the current billing type
			$query = "SELECT b.billing_type b_billing_type, 
			b.next_billing_date b_next_billing_date, 
			b.from_date b_from_date, b.to_date b_to_date, 
			t.id t_id, t.frequency t_frequency, 
			t.method t_method  
			FROM billing b
			LEFT JOIN billing_types t 
			ON b.billing_type = t.id
			WHERE b.id = '$mybilling_id'";		
			$DB->SetFetchMode(ADODB_FETCH_ASSOC);
			$billingqueryresult = $DB->Execute($query) 
				or die ("Query Failed");
			$billingresult = $billingqueryresult->fields;
			$method = $billingresult['t_method'];
			
			// update the next billing dates for anything not a prepay
			if ($method <> 'prepaycc' & $method <> 'prepay') {
				$mybillingdate = $billingresult['b_next_billing_date'];
				$myfromdate = $billingresult['b_from_date'];
				$mytodate = $billingresult['b_to_date'];
				$mybillingfreq = $billingresult['t_frequency'];
				
				// double frequency to add to the to_date
				$doublefreq = $mybillingfreq * 2;
	
				// insert the new next_billing_date, from_date, 
				// to_date, and payment_due_date to next from_date
				$query = "UPDATE billing
				SET next_billing_date = 
				DATE_ADD('$mybillingdate', 
				INTERVAL '$mybillingfreq' MONTH),
				from_date = 
				DATE_ADD('$myfromdate', 
				INTERVAL '$mybillingfreq' MONTH),
	                        to_date = 
				DATE_ADD('$myfromdate', 
				INTERVAL '$doublefreq' MONTH),
				payment_due_date = 
				DATE_ADD('$myfromdate', 	
				INTERVAL '$mybillingfreq' MONTH)
				WHERE id = '$mybilling_id'";		
				$updateresult = $DB->Execute($query) 
					or die ("Query Failed");

			} // endif prepaycc

		} // endif billing rerun				
        
		//
		// Apply Credits
		// check if they have any credits and apply as payments
		//
		//apply_credits($DB, $mybilling_id);
			
	} // endwhile

} // end create_billinghistory


// output invoices in text or pdf format
function outputinvoice($DB, $invoiceid, $lang, $printtype, $pdfobject) {

	include ("$lang");
		
	// get the invoice data to print on the bill
	$invoice_number = $invoiceid;

		$query = "SELECT h.id h_id, h.billing_date h_billing_date, 
		h.created_by h_created_by, h.billing_id h_billing_id, 
		h.from_date h_from_date, h.to_date h_to_date, 
		h.payment_due_date h_payment_due_date, 
		h.new_charges h_new_charges, h.past_due h_past_due, 
		h.late_fee h_late_fee, h.tax_due h_tax_due, 
		h.total_due h_total_due, h.notes h_notes, 
		b.id b_id, b.name b_name, b.company b_company, 
		b.street b_street, b.city b_city, b.state b_state, 
		b.country b_country, b.zip b_zip, 
		b.contact_email b_contact_email, b.account_number b_acctnum, 
		b.creditcard_number b_ccnum, b.creditcard_expire b_ccexp  
		FROM billing_history h 
		LEFT JOIN billing b ON h.billing_id = b.id  
		WHERE h.id = '$invoice_number'";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$invoiceresult = $DB->Execute($query)
			or die ("$l_queryfailed");	
		$myinvresult = $invoiceresult->fields;
		$user = $myinvresult['h_created_by'];
		$mydate = $myinvresult['h_billing_date'];
		$mybilling_id = $myinvresult['b_id'];
		$billing_name = $myinvresult['b_name'];
		$billing_company = $myinvresult['b_company'];
		$billing_street =  $myinvresult['b_street'];
		$billing_city = $myinvresult['b_city'];
		$billing_state = $myinvresult['b_state'];
		$billing_zip = $myinvresult['b_zip'];
		$billing_acctnum = $myinvresult['b_acctnum'];
		$billing_fromdate = $myinvresult['h_from_date'];
		$billing_todate = $myinvresult['h_to_date'];
		$billing_payment_due_date = $myinvresult['h_payment_due_date'];
		$billing_notes = $myinvresult['h_notes'];	
		$billing_new_charges = sprintf("%.2f",$myinvresult['h_new_charges']);
		$billing_past_due = sprintf("%.2f",$myinvresult['h_past_due']);
		$billing_late_fee = sprintf("%.2f",$myinvresult['h_late_fee']);
		$billing_tax_due = sprintf("%.2f",$myinvresult['h_tax_due']);
		$billing_total_due = sprintf("%.2f",$myinvresult['h_total_due']);	
		$billing_email = $myinvresult['b_contact_email'];

		// get the organization info to print on the bill
		$query = "SELECT g.org_name,g.org_street,g.org_city,g.org_state,g.org_zip,g.phone_billing,g.email_billing,g.invoice_footer  
                        FROM billing b
			LEFT JOIN general g ON g.id = b.organization_id 
			WHERE b.id = $mybilling_id";
		$generalresult = $DB->Execute($query) 
			or die ("$l_queryfailed");
		$mygenresult = $generalresult->fields;
		$org_name = $mygenresult['org_name'];
		$org_street = $mygenresult['org_street'];
		$org_city = $mygenresult['org_city'];
		$org_state = $mygenresult['org_state'];
		$org_zip = $mygenresult['org_zip'];
		$phone_billing = $mygenresult['phone_billing'];
		$email_billing = $mygenresult['email_billing'];
		$invoice_footer = $mygenresult['invoice_footer'];

		/*------------------------------------------------------------*/
		// output the invoice page
		/*------------------------------------------------------------*/
					
	       	// convert dates to human readable form
	       	$billing_fromdate = humandate($billing_fromdate, $lang);
	       	$billing_todate = humandate($billing_todate, $lang);
	       	$billing_payment_due_date = humandate($billing_payment_due_date, $lang);
		
		if ($printtype == "pdf")
		{
			require ('./include/fpdf.php');
			$pdf = $pdfobject;
			// convert html character codes to ascii for pdf
			$billing_name = html_to_ascii($billing_name);
			$billing_company = html_to_ascii($billing_company);
			$billing_street = html_to_ascii($billing_street);
			$billing_city = html_to_ascii($billing_city);
			$org_name = html_to_ascii($org_name);
			$org_street = html_to_ascii($org_street);
			$org_city = html_to_ascii($org_city);
			
			//$pdf=new FPDF();
			$pdf->AddPage();
			$pdf->SetFont('Arial','B',18);
			$pdf->Cell(60,10,"$org_name",0);

			$pdf->SetXY(10,20);
			$pdf->SetFont('Arial','',9);

			$pdf->MultiCell(80,4,"$org_street\n$org_city, $org_state $org_zip\n$phone_billing\n$email_billing",0);

			$pdf->Rect(135,10,1,30,"F");

			//$pdf->MultiCell(60,5,"$billing_name\n$billing_company\n$billing_street\n$billing_city $billing_state $billing_zip",0);
			$pdf->SetXY(140,10);
			$pdf->SetFontSize(10);
			$pdf->MultiCell(70,6,"$l_accountnumber: $billing_acctnum\n$l_invoicenumber: $invoiceid\n$billing_fromdate $l_to $billing_todate\n$l_paymentdue: $billing_payment_due_date\n$l_total: $billing_total_due",0);
			$pdf->SetXY(10,60);
			$pdf->SetFontSize(10);
			$pdf->MultiCell(60,5,"$billing_name\n$billing_company\n$billing_street\n$billing_city $billing_state $billing_zip",0);

			$pdf->SetXY(130,60);

			$pdf->Line(5,102,200,102);
			$pdf->SetXY(10,103);
			$pdf->Cell(100,5,"$l_description");
			//$pdf->SetXY(110,103);
			//$pdf->Cell(50,5,"$l_details");
			$pdf->SetXY(160,103);
			$pdf->Cell(50,5,"$l_amount");
			
		}
		else
		  {
			$output = "$l_accountnumber: $billing_acctnum\n\n";
			$output .= "$l_invoicenumber: $invoiceid\n";
			$output .= "$billing_fromdate - $billing_todate \n";
			$output .= "$l_paymentduedate: $billing_payment_due_date\n";
			$output .= "$l_total: $billing_total_due\n\n";
			
			$output .= "$l_to: $billing_email\n";
			$output .= "$billing_name $billing_company\n";
			$output .= "$billing_street ";
			$output .= "$billing_city $billing_state ";
			$output .= "$billing_zip\n\n";

			$output .= "----------------------------------------";
			$output .= "----------------------------------------\n";

		} // end if

		// Select the new charge details for a specific invoice number
		$query = "SELECT d.user_services_id d_user_services_id, 
		d.invoice_number d_invoice_number, 
		d.billed_amount d_billed_amount, 
		d.billing_id d_billing_id, 
		d.taxed_services_id d_taxed_services_id, 
		u.id u_id, u.master_service_id u_master_service_id, 
		u.usage_multiple u_usage_multiple, 
		m.options_table m_options_table, 
		m.id m_id, m.service_description m_service_description, m.pricerate,  
		ts.id ts_id, ts.master_services_id ts_master_services_id, 
		ts.tax_rate_id ts_tax_rate_id, tr.id tr_id, 
		tr.description tr_description
		FROM billing_details d
		LEFT JOIN user_services u ON d.user_services_id = u.id
		LEFT JOIN master_services m ON u.master_service_id = m.id
		LEFT JOIN taxed_services ts ON d.taxed_services_id = ts.id 
		LEFT JOIN tax_rates tr ON ts.tax_rate_id = tr.id 
		WHERE d.invoice_number = '$invoiceid' ORDER BY tr.id ASC, d.billed_amount DESC, u.master_service_id ASC";
		
		$result = $DB->Execute($query) or die ("$l_queryfailed");
		
		/*------------------------------------------------------------*/
		// Print the invoice line items
		/*------------------------------------------------------------*/
		$myline = 1;
		$lineYoffset = 105;
		while ($myresult = $result->FetchRow())
		{
			// select the options_table to get data for the details column
			$options_table = $myresult['m_options_table'];
			$id = $myresult['u_id'];
			if ($options_table <> '') {
				// get the data from the options table 
				// and put into variables
				$query = "SELECT * FROM $options_table 
					WHERE user_services = '$id'";
				$DB->SetFetchMode(ADODB_FETCH_NUM);
				$optionsresult = $DB->Execute($query) 
					or die ("$l_queryfailed");
				$myoptions = $optionsresult->fields;
				$optiondetails = $myoptions[2];
			} else {
				$optiondetails = '';	
			}

			$service_description = $myresult['m_service_description'];
			$tax_description = $myresult['tr_description'];
			$billed_amount = sprintf("%.2f",$myresult['d_billed_amount']);
			// the month mulptile
			$pricerate = $myresult['pricerate'];
			if ($pricerate > 0) {
			  $monthmultiple = $billed_amount/$pricerate;
			} else {
			  $monthmultiple = 0;
			}

			if ($printtype == "pdf")
			{
				// fix html characters
				$service_description = html_to_ascii($service_description);
				$tax_description = html_to_ascii($tax_description);
				$optiondetails = html_to_ascii($optiondetails);			
	
				$lineY = $lineYoffset + ($myline*5);
				$pdf->SetXY(10,$lineY);

				if ($monthmultiple > 1) {
				  $pdf->Cell(0,5,"$service_description $tax_description ($pricerate x $monthmultiple) $optiondetails");
				} else {
				  $pdf->Cell(0,5,"$service_description $tax_description $optiondetails");
				}

				//$pdf->SetXY(110,$lineY);
				//$pdf->Cell(110,5,"$optiondetails");
				$pdf->SetXY(160,$lineY);
				$pdf->Cell(160,5,"$billed_amount");
			}
			else
			{
			  if ($monthmultiple > 1) {
				$output .= "$service_description $tax_description ($pricerate x $monthmultiple) $optiondetails \t $billed_amount\n";
			  } else {
			    $output .= "$service_description $tax_description $optiondetails \t $billed_amount\n";
			  }
			}
			$myline++;

			// add a new page if there are many line items
			if (($myline > 28) AND ($printtype == "pdf")) {
				$pdf->AddPage();
				$pdf->SetXY(10,20);
				$myline = 1;
				$lineYoffset = 20;
			}
		}
	
		if ($printtype == "pdf")
		{
		  $lineY = $lineYoffset + ($myline*5);
		  $pdf->Line(5,$lineY,200,$lineY);
		}
		else
		{	
		  $output .= "----------------------------------------";
		  $output .= "----------------------------------------\n";
		}

		/*------------------------------------------------------------*/
		// print the notes and totals at the bottom of the invoice
		/*------------------------------------------------------------*/
		if ($printtype == "pdf")
		{
			// fix html characters
			$billing_notes = html_to_ascii($billing_notes);

			$lineY = $lineY + 10;
			$pdf->SetXY(10,$lineY);
			$pdf->MultiCell(100,5,"$billing_notes");
			$pdf->SetXY(135,$lineY);
			$pdf->MultiCell(100,5,"$l_newcharges: $billing_new_charges\n$l_pastdue: $billing_past_due\n$l_tax: $billing_tax_due\n");
			$pdf->SetXY(135,$lineY+15);
			$pdf->SetFont('Arial','BU',10);
			$pdf->Cell(100,5,"$l_total: $billing_total_due");
			$lineY = $lineY + 10;
			$pdf->SetFont('Arial','',9);
			$pdf->SetXY(10,$lineY);
			$pdf->MultiCell(0,2,"$invoice_footer");
		}
		else
		{		
		  $output .= "$billing_notes\n";

		  $output .= "$l_newcharges: $billing_new_charges\n";
		  $output .= "$l_pastdue: $billing_past_due\n";
		  $output .= "$l_tax: $billing_tax_due\n";
		  $output .= "$l_total: $billing_total_due\n";

		  $output .= "$invoice_footer\n";
				       

		}
	
		if ($printtype == "pdf")
		{	
			return $pdf;
		}
		else
		{
			return $output;
		}
} // end pdfinvoice


/*---------------------------------------------------------------------------*/
// lookup the billing status and return it in the local language
/*---------------------------------------------------------------------------*/
function billingstatus($billingid) {

  global $DB, $lang;
  include ("$lang");

  //$DB->debug = true;
  
  $status = "";
  $todaydate = date("Ymd");
	
  // get the two latest payment_history status values
  $query="SELECT * FROM payment_history 
	WHERE billing_id = '$billingid' ORDER BY id DESC LIMIT 2";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
	
  //"New", - new account with no billing details	
  $rowcount = $result->RowCount();
  if ($rowcount < 1) {$status = "$l_new"; }
  
  // get the first and second payment_history results
  $i = 0;
  if (!isset($myresult['status'])) { $myresult['status'] = ""; }
  if (!isset($firststatus)) { $firststatus = ""; }
  if (!isset($secondstatus)) { $secondstatus = ""; }

  while ($myresult = $result->FetchRow()) {
    if ($i == 0) {
      // the most recent payment_history status
      $firststatus = $myresult['status'];
    }
    if ($i == 1) {
      // the second most recent payment_history status
      $secondstatus = $myresult['status'];
    }
    
    // skip credit status
    if (($firststatus != 'credit') AND ($secondstatus != 'credit'))
      {
	$i++;
      }
  }
	
  // Get the billing method
  $query = "SELECT b.next_billing_date, b.billing_type, b.to_date, b.id, 
	t.id, t.method FROM billing b 
	LEFT JOIN billing_types t ON t.id = b.billing_type 
	WHERE b.id = '$billingid'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die("$l_queryfailed");;
  $myresult = $result->fields;
  if (!isset($myresult['method'])) { $myresult['method'] = ""; }	
  if (!isset($myresult['to_date'])) { $myresult['to_date'] = ""; }	
  if (!isset($myresult['next_billing_date'])) { $myresult['next_billing_date'] = ""; }	
  $method = $myresult['method'];
  $todate = $myresult['to_date'];
  $next_billing_date = $myresult['next_billing_date'];
  
  
  //"Not Renewed", - a past due prepaid account
  // if method = prepay and today is greater than the billing to_date
  $todate = str_replace( "-", "", $todate );
  if (($method == "prepay") and ($todaydate > $todate)) {
    $status = "$l_notrenewed";
  }
  
  //"Authorized", - an authorized credit card or invoice account
  // if last payment_history = authorized
  if ($firststatus == "authorized") {
    $status = "$l_authorized";
  }
  
  //"Declined", - a declined credit card account
  // if last payment_history = declined
  if ($firststatus == "declined") {
    $status = "$l_declined";
    if ($rowcount == 1) {
      // Initial Decline
      $status = "$l_initialdecline";
    }
    if ($secondstatus == "declined") {
      // Declined 2X
      $status = "$l_declined2x";
    }
  }
  
  
  //"Pending", - not being billed, pending an account change
  // if last payment_history = pending
  if ($next_billing_date == "" OR $next_billing_date == "0000-00-00") {
    $status = "$l_pending";
  }
  
  
  //"Turned Off", - turned off by us
  // if last payment_history is turned off
  // The middle past due days
  if ($firststatus == "turnedoff") {
    $status = "$l_turnedoff";
  }

  //"Notice Sent", - sent notice about to be shutoff
  // for carrier dependent services
  if ($firststatus == "noticesent") {
    $status = "$l_noticesent";
  }

  //"Waiting", - waiting for payment, stops pastdue process
  // for carrier dependent services
  if ($firststatus == "waiting") {
    $status = "$l_waiting";
  } 
  
  // Past Due  - status set by the activator when run daily
  //"Turned Off", - turned off by us
  // if last payment_history is turned off
  // The middle past due days
  if ($firststatus == "pastdue") {
    $status = "$l_pastdue";
  }

  // get pastdue_exempt status
  $query = "SELECT pastdue_exempt FROM billing WHERE id = $billingid";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die("$l_queryfailed");;
  $myresult = $result->fields;
  $pastdue_exempt = $myresult['pastdue_exempt'];
  if ($pastdue_exempt == 'y') { $status = "$l_pastdueexempt"; }
  if ($pastdue_exempt == 'bad_debt') { $status = "$l_bad_debt"; }
  
  //"Free", - an account with the free billing type
  // overrides other billing types
  if ($method == "free") {
    $status = "$l_free";
  }

  //"Canceled" - canceled, has a cancel date
  // if they have a cancel date
  $query = "SELECT cancel_date FROM customer
	WHERE default_billing_id = '$billingid'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die("$l_queryfailed");;
  $myresult = $result->fields;
  if (!isset($myresult['cancel_date'])) { $myresult['cancel_date'] = "";}	
  $cancel_date = $myresult['cancel_date'];
  
  if ($cancel_date) {
    if ($firststatus == "cancelwfee") {
      $status = "$l_cancelwithfee";
    } elseif ($firststatus == "collections") {
      $status = "$l_collections";
    } else {
      $status = "$l_canceled";
    }
  }
  
  return $status;	
} // end billingstatus


/*----------------------------------------------------------------------------*/
// Convert html character codes to ascii for pdf printing
/*----------------------------------------------------------------------------*/
function html_to_ascii($value) {
	$value = str_replace( "&amp;" , "&" , $value );
	$value = str_replace( "&gt;" , ">" , $value );
	$value = str_replace( "&lt;" , "<" , $value );
	$value = str_replace( "&quot;" , "\"" , $value );
	$value = str_replace( "&#036;", "$" , $value );
	$value = str_replace( "&#33;" , "!" , $value );
	$value = str_replace( "&#39;" , "'" , $value );	
	return $value;
} // end html_to_ascii


/*----------------------------------------------------------------------------*/
// Convert the ISO database date to a friendly human format
function humandate($date, $lang) {

  include ($lang);

  // split the iso date into parts
  list($myyear, $mymonth, $myday) = split('-', $date);

  // assign the month it's written name
  switch($mymonth) {
  case "01":
    $mymonth = "$l_january";
    break;
  case "02":
    $mymonth = "$l_february";
    break;
  case "03":
    $mymonth = "$l_march";
    break;
  case "04":
    $mymonth = "$l_april";
    break;
  case "05":
    $mymonth = "$l_may";
    break;
  case "06":
    $mymonth = "$l_june";
    break;
  case "07":
    $mymonth = "$l_july";
    break;
  case "08":
    $mymonth = "$l_august";
    break;
  case "09":
    $mymonth = "$l_september";
    break;
  case "10":
    $mymonth = "$l_october";
    break;
  case "11":
    $mymonth = "$l_november";
    break;
  case "12":
    $mymonth = "$l_december";
    break;
  }

  // put it all back together
  $date = "$mymonth $myday, $myyear";

  return $date;
}

/*----------------------------------------------------------------------------*/
// Apply Credits
/*----------------------------------------------------------------------------*/
function apply_credits($DB, $billing_id) {
	// find the credits
	$query = "SELECT * from billing_details  
	WHERE paid_amount > billed_amount
	AND billing_id = '$billing_id'";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$creditresult = $DB->Execute($query) or die ("$l_queryfailed");
	
	while ($mycreditresult = $creditresult->FetchRow())
	{
		$credit_id = $mycreditresult['id'];
		$credit_billed_amount = $mycreditresult['billed_amount'];
		$credit_paid_amount = $mycreditresult['paid_amount']; 
	
		//
		// apply those credits towards amounts owed
		//

		// BELOW IS FROM THE PAYMENT UTILITY, MODIFY IT TO WORK
		// calculate credit amount
		$credit_amount = abs($credit_billed_amount - $credit_paid_amount);
		$amount = $credit_amount;	
		// find the amounts owed
	
		$query = "SELECT * FROM billing_details 
		WHERE paid_amount < billed_amount AND billing_id = $billing_id";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$findresult = $DB->Execute($query) or die ("$l_queryfailed");

		while (($myfindresult = $findresult->FetchRow()) and ($amount > 0))
		{	
			$id = $myfindresult['id'];
			$paid_amount = $myfindresult['paid_amount'];
			$billed_amount = $myfindresult['billed_amount'];
	
			$owed = $billed_amount - $paid_amount;		
		
			if ($amount >= $owed) {
				$amount = $amount - $owed;
				$fillamount = $owed + $paid_amount;
				$query = "UPDATE billing_details 
					SET paid_amount = '$fillamount' 
					WHERE id = $id";
				$greaterthanresult = $DB->Execute($query) or die ("$l_queryfailed");
			} else { 
			// amount is  less than owed
				$available = $amount;
				$amount = 0;
				$fillamount = $available + $paid_amount;
				$query = "UPDATE billing_details 
					SET paid_amount = '$fillamount' 
					WHERE id = $id";
				$lessthenresult = $DB->Execute($query) 
					or die ("$l_queryfailed");
			}
		}

		//
		// reduce the amount of the credit left by the amount applied
		
		$credit_applied = $credit_amount - $amount;
		$credit_paid_total = $credit_paid_amount - $credit_applied;
		$query = "UPDATE billing_details 
			SET paid_amount = '$credit_paid_total' 
			WHERE id = '$credit_id'";	
		$totalcreditresult = $DB->Execute($query) 
				or die ("$l_queryfailed");
	}
}

/*---------------------------------------------------------------------------*/
// Create Billing Record
/*---------------------------------------------------------------------------*/
function create_billing_record($organization_id, $my_account_number, $DB)
{
// query the customer record for the default billing id
	$query = "SELECT default_billing_id FROM customer 
			WHERE account_number = $my_account_number";
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	$myresult = $result->fields;
        $default_billing_id = $myresult['default_billing_id'];

	// query the default billing record for default name and address info to
	// insert into the new alternate billing record
	$query = "SELECT * FROM billing WHERE id = $default_billing_id";
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	$myresult = $result->fields;
	$name = $myresult['name'];
	$company = $myresult['company'];
	$street = $myresult['street'];
	$city = $myresult['city'];
	$state = $myresult['state'];
	$country = $myresult['country'];
	$zip = $myresult['zip'];
	$phone = $myresult['phone'];
	$fax = $myresult['fax'];
	$contact_email = $myresult['contact_email'];
	$billing_type = $myresult['billing_type'];
	$creditcard_number = $myresult['creditcard_number'];
	$creditcard_expire = $myresult['creditcard_expire'];
	$billing_status = $myresult['billing_status'];
	$disable_billing = $myresult['disable_billing'];
	$next_billing_date = $myresult['next_billing_date'];
	$prev_billing_date = $myresult['prev_billing_date'];
	$from_date = $myresult['from_date'];
	$to_date = $myresult['to_date'];
	$payment_due_date = $myresult['payment_due_date'];
	$rerun_date = $myresult['rerun_date'];
	$pastdue_exempt = $myresult['pastdue_exempt'];
	$po_number = $myresult['po_number'];	

	// save billing information
        $query = "INSERT into billing
        SET name = '$name',
        company = '$company',
        street = '$street',
        city = '$city',
        state = '$state',
	country = '$country',
        zip = '$zip',
        phone = '$phone',
        fax = '$fax',
	contact_email = '$contact_email',
	account_number = '$my_account_number',
	billing_type = '$billing_type',
	creditcard_number = '$creditcard_number',
	creditcard_expire = '$creditcard_expire',
	disable_billing = '$disable_billing',
	next_billing_date = '$next_billing_date',
	from_date = '$from_date',
	to_date = '$to_date',
	payment_due_date = '$payment_due_date',
	pastdue_exempt = '$pastdue_exempt',
	po_number = '$po_number',
	organization_id = '$organization_id'";
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	$myinsertid = $DB->Insert_ID();

	return $myinsertid;
}

?>