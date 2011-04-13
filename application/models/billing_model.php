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
						
			$result[$i] = array(
				'b_id' => $myrecord->b_id,
				'g_org_name' => $myrecord->g_org_name,
				't_name' => $myrecord->t_name,
				'not_removed_id' => $myusresult->billing_id,
				'mystatus' => $mystatus
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
		
}