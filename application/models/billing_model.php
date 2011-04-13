<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Billing_Model extends CI_Model
{
	public function record_list($account_number)
	{
		// show the billing record info
		// print a list of alternate billing id's if any
		$query = "SELECT b.id b_id, g.org_name g_org_name, t.name t_name ".
			"FROM billing b ".
			"LEFT JOIN billing_types t ON b.billing_type = t.id ".
			"LEFT JOIN general g ON b.organization_id = g.id ".
			"WHERE b.account_number = $account_number";
		$record_result = $this->db->query($query) or die ("$l_queryfailed");
		
		// check if billing id has active services and what it's status is
		$i = 0; // make a multi dimensional array of these results for each record assigned to this user
		foreach($record_result->result() as $myrecord)
		{
			$billing_id = $myrecord->b_id;
			$query = "SELECT billing_id FROM user_services ".
				"WHERE removed = 'n' AND billing_id = $billing_id LIMIT 1";
			$usresult = $this->db->query($query) or die ("user_service $l_queryfailed");
			$myusresult = $usresult->row();
			$not_removed_id = $myusresult->billing_id;		

			$mystatus = $this->billingstatus($billing_id);
								
			$newtaxes = sprintf("%.2f",$this->total_taxitems($billing_id));
  			$newcharges = sprintf("%.2f",$this->total_serviceitems($billing_id)+$newtaxes);
  			$pastcharges = sprintf("%.2f",$this->total_pastdueitems($billing_id));
  			$newtotal = sprintf("%.2f",$newcharges + $pastcharges);
  	
			$result[$i] = array(
				'b_id' => $myrecord->b_id,
				'g_org_name' => $myrecord->g_org_name,
				't_name' => $myrecord->t_name,
				'not_removed_id' => $myusresult->billing_id,
				'mystatus' => $mystatus,
				'newtaxes' => $newtaxes,
				'newcharges' => $newcharges,
				'pastcharges' => $pastcharges,
				'newtotal' => $newtotal
			);
			
			$i++;
		}
		
		
		return $result;
	}
		
	/*---------------------------------------------------------------------------*/
	// lookup the billing status and return it in the local language
	/*---------------------------------------------------------------------------*/
	public function billingstatus($billing_id)
	{
  
		$status = "";
		$todaydate = date("Ymd");
	
		// get the two latest payment_history status values
		$query="SELECT * FROM payment_history 
			WHERE billing_id = '$billing_id' ORDER BY id DESC LIMIT 2";
		$result = $this->db->query($query) or die ("$l_queryfailed");
	
		//"New", - new account with no billing details	
		$rowcount = $result->num_rows();
		if ($rowcount < 1) {$status = lang('new'); }
  
		// get the first and second payment_history results
		$i = 0;
		if (!isset($myresult->status)) { $myresult->status = ""; }
		if (!isset($firststatus)) { $firststatus = ""; }
		if (!isset($secondstatus)) { $secondstatus = ""; }

  		foreach($result->result() as $myresult)
  		{
    		if ($i == 0) 
    		{
      			// the most recent payment_history status
      			$firststatus = $myresult->status;
    		}

    		if ($i == 1) 
    		{
      			// the second most recent payment_history status
				$secondstatus = $myresult->status;
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
			WHERE b.id = '$billing_id'";
		$result = $this->db->query($query) or die("$l_queryfailed");;
		$myresult = $result->row();
		if (!isset($myresult->method)) { $myresult->method = ""; }	
		if (!isset($myresult->to_date)) { $myresult->to_date = ""; }	
		if (!isset($myresult->next_billing_date)) { $myresult->next_billing_date = ""; }	
		$method = $myresult->method;
		$todate = $myresult->to_date;
		$next_billing_date = $myresult->next_billing_date;
  
  
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
  		if ($firststatus == "declined") 
  		{
    		$status = "$l_declined";
    		if ($rowcount == 1) 
    		{
      			// Initial Decline
      			$status = "$l_initialdecline";
    		}
    		if ($secondstatus == "declined") 
    		{
      			// Declined 2X
      			$status = "$l_declined2x";
    		}
  		}
  
  
  		//"Pending", - not being billed, pending an account change
  		// if last payment_history = pending
  		if (empty($next_billing_date) OR $next_billing_date == '0000-00-00') 
  		{
			$status = "$l_pending";
  		}

		//"Turned Off", - turned off by us
		// if last payment_history is turned off
		// The middle past due days
		if ($firststatus == "turnedoff") 
		{
			$status = "$l_turnedoff";
  		}

  		//"Notice Sent", - sent notice about to be shutoff
  		// for carrier dependent services
  		if ($firststatus == "noticesent") 
  		{
    		$status = "$l_noticesent";
  		}

  		//"Waiting", - waiting for payment, stops pastdue process
  		// for carrier dependent services
  		if ($firststatus == "waiting") 
  		{
			$status = "$l_waiting";
  		} 
  
  		// Past Due  - status set by the activator when run daily
  		//"Turned Off", - turned off by us
  		// if last payment_history is turned off
  		// The middle past due days
  		if ($firststatus == "pastdue") 
  		{
			$status = "$l_pastdue";
  		}

  		// get pastdue_exempt status
  		$query = "SELECT pastdue_exempt FROM billing WHERE id = $billing_id";
  		$result = $this->db->query($query) or die("$l_queryfailed");;
  		$myresult = $result->row();
  		$pastdue_exempt = $myresult->pastdue_exempt;
  		if ($pastdue_exempt == 'y') { $status = "$l_pastdueexempt"; }
  		if ($pastdue_exempt == 'bad_debt') { $status = "$l_bad_debt"; }
  
  		//"Free", - an account with the free billing type
  		// overrides other billing types
  		if ($method == "free") 
  		{
			$status = "$l_free";
  		}

  		//"Canceled" - canceled, has a cancel date
  		// if they have a cancel date
  		$query = "SELECT cancel_date FROM customer
			WHERE default_billing_id = '$billing_id' LIMIT 1";
  		$result = $this->db->query($query) or die("$l_queryfailed");;
  		$myresult = $result->row();
  		if (!isset($myresult->cancel_date)) { $myresult->cancel_date = "";}	
  		$cancel_date = $myresult->cancel_date;
  
  		if ($cancel_date) 
  		{
    		if ($firststatus == "cancelwfee") 
    		{
      			$status = "$l_cancelwithfee";
    		} 
    		elseif ($firststatus == "collections") 
    		{
      			$status = "$l_collections";
    		} 
    		else 
    		{
      			$status = "$l_canceled";
    		}
  		}
  
		return $status;	
	
	} // end billingstatus
	
	
	function total_taxitems($bybillingid)
	{
		/*--------------------------------------------------------------------*/
		// Add taxes together to get total for this billing id
		/*--------------------------------------------------------------------*/

		//
		// query for taxed services that are billed by a specific billing id
		//

		$query = "SELECT ts.id ts_id, ts.master_services_id ts_serviceid, ".
    "ts.tax_rate_id ts_rateid, ms.id ms_id, ".
    "ms.service_description ms_description, ms.pricerate ms_pricerate, ".
    "ms.frequency ms_freq, tr.id tr_id, tr.description tr_description, ".
    "tr.rate tr_rate, tr.if_field tr_if_field, tr.if_value tr_if_value, ".
    "tr.percentage_or_fixed tr_percentage_or_fixed, ". 
    "us.master_service_id us_msid, us.billing_id us_bid, us.id us_id, ".
    "us.removed us_removed, us.account_number us_account_number, ".
    "us.usage_multiple us_usage_multiple, ".
    "te.account_number te_account_number, te.tax_rate_id te_tax_rate_id, ".
    "b.id b_id, b.billing_type b_billing_type, ". 
    "t.id t_id, t.frequency t_freq, t.method t_method ".
    "FROM taxed_services ts ".
    "LEFT JOIN user_services us ".
    "ON us.master_service_id = ts.master_services_id ".
    "LEFT JOIN master_services ms ON ms.id = ts.master_services_id ".
    "LEFT JOIN tax_rates tr ON tr.id = ts.tax_rate_id ".
    "LEFT JOIN tax_exempt te ON te.account_number = us.account_number ".
    "AND te.tax_rate_id = tr.id ".
    "LEFT JOIN billing b ON us.billing_id = b.id ".
    "LEFT JOIN billing_types t ON b.billing_type = t.id ".
    "WHERE b.id = '$bybillingid' AND us.removed <> 'y'";

		$taxresult = $this->db->query($query) or die ("Taxes Query Failed");

		// initialize to add up the total amount of taxes
		$total_taxes = 0;
		
		foreach ($taxresult->result() as $mytaxresult)
		{
			$billing_id = $mytaxresult->b_id;
			$taxed_services_id = $mytaxresult->ts_id;
			$user_services_id = $mytaxresult->us_id;
			$service_freq = $mytaxresult->ms_freq;
			$billing_freq = $mytaxresult->t_freq;
			$if_field = $mytaxresult->tr_if_field;
			$if_value = $mytaxresult->tr_if_value;
			$percentage_or_fixed = $mytaxresult->tr_percentage_or_fixed;
			$my_account_number = $mytaxresult->us_account_number;
			$usage_multiple = $mytaxresult->us_usage_multiple;
			$pricerate = $mytaxresult->ms_pricerate;
			$taxrate = $mytaxresult->tr_rate;
			$tax_rate_id = $mytaxresult->tr_id;
			$tax_exempt_rate_id = $mytaxresult->te_tax_rate_id;

			// check that they are not exempt
			if ($tax_exempt_rate_id <> $tax_rate_id) {
				// check the if_field before adding to see if
				// the tax applies to this customer
				if ($if_field <> '') {
					$ifquery = "SELECT $if_field FROM customer ".
	  					"WHERE account_number = '$my_account_number'";
					$ifresult = $this->db->query($ifquery) or die ("Query Failed");
					$myifresult = $ifresult->row_array();
					$checkvalue = $myifresult[0];
				} else {
					$checkvalue = TRUE;
					$if_value = TRUE;
				}

				// check for any case, so lower them here
				$checkvalue = strtolower($checkvalue);
				$if_value = strtolower($if_value);

				if (($checkvalue == $if_value) AND ($billing_freq > 0)) {
					if ($percentage_or_fixed == 'percentage') {
						if ($service_freq > 0) {
							$servicecost = sprintf("%.2f",$taxrate * $pricerate);
							$tax_amount = sprintf("%.2f",$servicecost * $billing_freq * $service_freq * $usage_multiple);
						} else {
							$servicecost = $pricerate * $usage_multiple;
							$tax_amount = $taxrate * $servicecost;
						}
					} else {
						// fixed fee amount, does not depend on price or usage
						$tax_amount = $taxrate * $billing_freq;
					}


					// round the tax to two decimal places
					$tax_amount = sprintf("%.2f", $tax_amount);

					// add to total taxes
					$total_taxes = $total_taxes + $tax_amount;

				} //endif if_field/billing_freq

			} // endif exempt

		}

		// send back total new taxes for that customer billingid
		return $total_taxes;

	}


	function total_serviceitems($bybillingid)
	{
		/*--------------------------------------------------------------------*/
		// Add services together to get total for this billing id
		/*--------------------------------------------------------------------*/

		$query = "SELECT u.id u_id, u.account_number u_ac, ".
    "u.master_service_id u_msid, u.billing_id u_bid, ".
    "u.removed u_rem, u.usage_multiple u_usage, ".
    "b.next_billing_date b_next_billing_date, b.id b_id, ".
    "b.billing_type b_type, ".
    "t.id t_id, t.frequency t_freq, t.method t_method, ".
    "m.id m_id, m.pricerate m_pricerate, m.frequency m_freq ".
    "FROM user_services u ".
    "LEFT JOIN master_services m ON u.master_service_id = m.id ".
    "LEFT JOIN billing b ON u.billing_id = b.id ".
    "LEFT JOIN billing_types t ON b.billing_type = t.id ".
    "WHERE b.id = '$bybillingid' ".
    "AND u.removed <> 'y'";

		$result = $this->db->query($query) or die ("Services Query Failed");

		// initialize the service totals
		$total_service = 0;

		foreach ($result->result() as $myresult)
		{
			$billing_id = $myresult->u_bid;
			$user_services_id = $myresult->u_id;
			$pricerate = $myresult->m_pricerate;
			$usage_multiple = $myresult->u_usage;
			$service_freq = $myresult->m_freq;
			$billing_freq = $myresult->t_freq;

			if ($billing_freq > 0) {
				if ($service_freq > 0) {
					$billed_amount = ($billing_freq/$service_freq)
					*($pricerate*$usage_multiple);
				} else {
					// one time services
					$billed_amount = ($pricerate*$usage_multiple);
				} // end if

				// round the tax to two decimal places
				$billed_amount = sprintf("%.2f", $billed_amount);

				// add to the total service cost
				$total_service = $total_service + $billed_amount;

			} // end if billing_freq

		} // end while

		// return the total amount of service charges for the billingid
		return $total_service;

	}



	function total_pastdueitems($mybilling_id)
	{
		/*-------------------------------------------------------------------------*/
		// get the amounts for the past due charges for that billing id
		/*-------------------------------------------------------------------------*/

		$query = "SELECT d.billing_id d_billing_id, ".
    "d.paid_amount d_paid_amount, d.billed_amount d_billed_amount ".
    "FROM billing_details d ".
    "LEFT JOIN user_services u ON d.user_services_id = u.id ".
    "LEFT JOIN master_services m ON u.master_service_id = m.id ".
    "LEFT JOIN taxed_services ts ON d.taxed_services_id = ts.id ".
    "LEFT JOIN tax_rates tr ON ts.tax_rate_id = tr.id ".
    "WHERE d.billing_id = $mybilling_id ".
    "AND d.paid_amount != d.billed_amount"; 	

		$invoiceresult = $this->db->query($query) or die ("This Query Failed");

		// initialize past amounts
		$pastdue = 0;

		foreach($invoiceresult->result() as $myinvresult)
		{
			$billed_amount = $myinvresult->d_billed_amount;
			$paid_amount = $myinvresult->d_paid_amount;

			// get the difference between billed and paid amount
			$billed_amount = $billed_amount - $paid_amount;

			// add to past due charges
			$pastdue = sprintf("%.2f",$pastdue + $billed_amount);

		} // end while

		// return the pastdue total to them
		return $pastdue;

	} // end total_pastdueitems
	
		
}