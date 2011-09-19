<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Billing_Model extends CI_Model
{
	/*
	 * ---------------------------------------------------------------------------
	 *  Create Billing Record
	 * ---------------------------------------------------------------------------
	 */
	function create_record($organization_id, $my_account_number)
	{
		// query the customer record for the default billing id
		$query = "SELECT default_billing_id FROM customer 
			WHERE account_number = $my_account_number";
		$result = $this->db->query($query) or die ("$l_queryfailed");
		$myresult = $result->row_array();
		$default_billing_id = $myresult['default_billing_id'];

		// query the default billing record for default name and address info to
		// insert into the new alternate billing record
		$query = "SELECT * FROM billing WHERE id = $default_billing_id";
		$result = $this->db->query($query) or die ("$l_queryfailed");
		$myresult = $result->row_array();
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
		$next_billing_date = $myresult['next_billing_date'];
		$from_date = $myresult['from_date'];
		$to_date = $myresult['to_date'];
		$payment_due_date = $myresult['payment_due_date'];
		$rerun_date = $myresult['rerun_date'];
		$pastdue_exempt = $myresult['pastdue_exempt'];
		$po_number = $myresult['po_number'];
		$encrypted_creditcard_number = $myresult['encrypted_creditcard_number'];
		$automatic_receipt = $myresult['automatic_receipt'];	

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
				next_billing_date = '$next_billing_date',
				from_date = '$from_date',
				to_date = '$to_date',
				payment_due_date = '$payment_due_date',
				pastdue_exempt = '$pastdue_exempt',
				po_number = '$po_number',
				encrypted_creditcard_number = '$encrypted_creditcard_number',
				automatic_receipt = '$automatic_receipt',
				organization_id = '$organization_id'";
		$result = $this->db->query($query) or die ("$l_queryfailed");
		$myinsertid = $this->db->insert_id();

		return $myinsertid;
	}
	
	/*
	 * ------------------------------------------------------------------------
	 *  get the default billing id for this account
	 * ------------------------------------------------------------------------
	 */
	public function default_billing_id($account_number)
	{
		$query = "SELECT default_billing_id FROM customer ".
			"WHERE account_number = $account_number";
		$result = $this->db->query($query) or die ("$l_queryfailed");
		$myresult = $result->row();	
		
		return $myresult->default_billing_id;
	}
	
	
	/*
	 * ------------------------------------------------------------------------
	 *  get alternate billing types
	 * ------------------------------------------------------------------------
	 */
	public function alternates($account_number, $billing_id)
	{
		// print a list of alternate billing id's if any
		$query = "SELECT b.id b_id, g.org_name g_org_name, t.name t_name ".
			"FROM billing b ".	
			"LEFT JOIN billing_types t ON b.billing_type = t.id ".
			"LEFT JOIN general g ON b.organization_id = g.id ".
			"WHERE b.id != $billing_id AND b.account_number = $this->account_number";
		$result = $this->db->query($query) or die ("$l_queryfailed");
		
		return $result->result_array();
	
	}

	/*
	 * -------------------------------------------------------------------------
	 *  save record information
	 * -------------------------------------------------------------------------
	 */
	public function save_record($billing_id, $billing_data)
	{
		// using active record, yipee!
		$this->db->where('id', $billing_id);
		$this->db->update('billing', $billing_data);
	}
	
	
	/*
	 * ------------------------------------------------------------------------
	 *  return information from the billing record
	 * ------------------------------------------------------------------------
	 */
	public function record($billing_id)
	{
		$query = "SELECT b.id b_id, b.name b_name, b.company b_company, b.street ".
			"b_street, b.city b_city, b.state b_state, b.zip b_zip, b.phone ".
			"b_phone, b.fax b_fax, b.country b_country, b.contact_email b_email, ".
			"b.creditcard_number b_ccnum, b.creditcard_expire b_ccexp, ".
			"b.billing_status b_status, b.billing_type b_type, ".
			"b.next_billing_date b_next_billing_date, b.pastdue_exempt b_pastdue_exempt, ".
			"b.prev_billing_date b_prev_billing_date, b.from_date b_from_date, ".
			"b.to_date b_to_date, b.payment_due_date b_payment_due_date, ".
			"b.rerun_date b_rerun_date, b.po_number b_po_number, b.notes b_notes, ".
		  "b.automatic_receipt b_automatic_receipt, ".
			"b.organization_id b_organization_id,  t.id t_id, t.name t_name ".
			"FROM billing b ".
			"LEFT JOIN billing_types t ON b.billing_type = t.id ".
			"WHERE b.id = '$billing_id'";

		$result = $this->db->query($query) or die ("queryfailed");
		$myresult = $result->row_array();

		$data['billing_id'] = $myresult['b_id'];
		$data['name'] = $myresult['b_name'];
		$data['company'] = $myresult['b_company'];
		$data['street'] = $myresult['b_street'];
		$data['city'] = $myresult['b_city'];
		$data['state'] = $myresult['b_state'];
		$data['zip'] = $myresult['b_zip'];
		$data['country'] = $myresult['b_country'];
		$data['phone'] = $myresult['b_phone'];
		$data['fax'] = $myresult['b_fax'];
		$data['contact_email'] = $myresult['b_email'];
		$data['billing_type'] = $myresult['t_name'];
		$data['creditcard_number'] = $myresult['b_ccnum'];
		$data['creditcard_expire'] = $myresult['b_ccexp'];
		$data['billing_status'] = $myresult['b_status'];
		$data['next_billing_date'] = $myresult['b_next_billing_date'];
		$data['prev_billing_date'] = $myresult['b_prev_billing_date'];
		$data['from_date'] = $myresult['b_from_date'];
		$data['to_date'] = $myresult['b_to_date'];
		$data['payment_due_date'] = $myresult['b_payment_due_date'];
		$data['rerun_date'] = $myresult['b_rerun_date'];
		$data['notes'] = $myresult['b_notes'];
		$data['po_number'] = $myresult['b_po_number'];
		$data['pastdue_exempt'] = $myresult['b_pastdue_exempt'];
		$data['automatic_receipt'] = $myresult['b_automatic_receipt'];		
		$data['organization_id'] = $myresult['b_organization_id'];

		// if the card number is not blank, wipe out the middle of the card number
		if ($data['creditcard_number'] <> '') {
			$length = strlen($data['creditcard_number']);
			$firstdigit = substr($data['creditcard_number'], 0,1);
			$lastfour = substr($data['creditcard_number'], -4);
			$data['creditcard_number'] = "$firstdigit" . "***********" . "$lastfour";
		}
		
		// get the billing status for this record
		$data['mystatus'] = $this->billingstatus($billing_id);		

		// get the organization info
		$query = "SELECT org_name FROM general WHERE id = ".$data['organization_id']." LIMIT 1";
		$orgresult = $this->db->query($query) or die ("queryfailed");
		$myorgresult = $orgresult->row();
		$data['organization_name'] = $myorgresult->org_name;
		
		return $data;

	}


	public function record_list($account_number)
	{
		// show the billing record info
		// print a list of alternate billing id's if any
		$query = "SELECT b.id b_id, g.org_name g_org_name, t.name t_name ".
			"FROM billing b ".
			"LEFT JOIN billing_types t ON b.billing_type = t.id ".
			"LEFT JOIN general g ON b.organization_id = g.id ".
			"WHERE b.account_number = $account_number";
		$record_result = $this->db->query($query) or die ("query failed");

		// check if billing id has active services and what it's status is
		$i = 0; 
		// make a multi dimensional array of these results for each record assigned to this user
		foreach($record_result->result() as $myrecord)
		{
			$billing_id = $myrecord->b_id;
			$query = "SELECT billing_id FROM user_services ".
				"WHERE removed = 'n' AND billing_id = $billing_id LIMIT 1";
			$usresult = $this->db->query($query) or die ("user_service queryfailed");
			if ($usresult->num_rows() > 0) 
			{
				$myusresult = $usresult->row_array();
				$not_removed_id = $myusresult['billing_id'];		
			}
			else
			{
				$not_removed_id = 0;
			}
			$mystatus = $this->billingstatus($billing_id);

			$newtaxes = sprintf("%.2f",$this->total_taxitems($billing_id));
			$newcharges = sprintf("%.2f",$this->total_serviceitems($billing_id)+$newtaxes);
			$pastcharges = sprintf("%.2f",$this->total_pastdueitems($billing_id));
			$newtotal = sprintf("%.2f",$newcharges + $pastcharges);

			$result[$i] = array(
					'b_id' => $myrecord->b_id,
					'g_org_name' => $myrecord->g_org_name,
					't_name' => $myrecord->t_name,
					'not_removed_id' => $not_removed_id,
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


	/*
	 * -------------------------------------------------------------------------------
	 *  determine the next available billing date according to the rollover time
	 *  and holiday dates that are excluded
	 * -------------------------------------------------------------------------------
	 */
	function get_nextbillingdate()
	{
		// get the current date and time in the SQL query format
		$mydate = date("Y-m-d");
		$mytime = date("H:i:s");

		// check if it's after the dayrollover time and get tomorrow's date if it is
		$query = "SELECT billingdate_rollover_time from settings WHERE id = 1";
		$result = $this->db->query($query) or die ("Billingdate rollover Query Failed");
		$myresult = $result->row_array();
		$rollover_time = $myresult['billingdate_rollover_time'];
		if ($mytime > $rollover_time) 
		{
			$mydate = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));
		}

		// check if the date is in the holiday table, move up one day until not matched
		$holiday = true;
		while ($holiday == true)
		{
			$query = "SELECT holiday_date from holiday WHERE holiday_date = '$mydate'";
			$result = $this->db->query($query) or die ("Holiday date Query Failed");
			$myresult = $result->row_array();
			if ($result->num_rows() > 0)
			{	
				$myholiday = $myresult['holiday_date'];
			}
			else
			{
				$myholiday = NULL;
			}

			// check for billing weekend days
			// check the database for what days are marked as billing weekends
			$query = "SELECT billingweekend_sunday, billingweekend_monday, ".
				"billingweekend_tuesday, billingweekend_wednesday, ".
				"billingweekend_thursday, billingweekend_friday, ".
				"billingweekend_saturday FROM settings WHERE id = 1";
			$result = $this->db->query($query) or die ("Weekend Query Failed");
			$myresult = $result->row_array();
			$sunday = $myresult['billingweekend_sunday'];
			$monday = $myresult['billingweekend_monday'];
			$tuesday = $myresult['billingweekend_tuesday'];
			$wednesday = $myresult['billingweekend_wednesday'];
			$thursday = $myresult['billingweekend_thursday'];
			$friday = $myresult['billingweekend_friday'];
			$saturday = $myresult['billingweekend_saturday'];                                

			// check the date we have agains those billing weekends
			$datepieces = explode('-', $mydate); 
			$myyear = $datepieces[0];
			$mymonth = $datepieces[1];
			$myday = $datepieces[2];

			$day_of_week = date("w", mktime(0, 0, 0, $mymonth, $myday, $myyear));

			// if the weekday is a billing weekend, 
			// then make it a holiday so it gets moved forward
			if ($sunday == 'y' && $day_of_week == 0) { $myholiday = $mydate; }
			if ($monday == 'y' && $day_of_week == 1) { $myholiday = $mydate; }
			if ($tuesday == 'y' && $day_of_week == 2) { $myholiday = $mydate; }
			if ($wednesday == 'y' && $day_of_week == 3) { $myholiday = $mydate; }
			if ($thursday == 'y' && $day_of_week == 4) { $myholiday = $mydate; }
			if ($friday == 'y' && $day_of_week == 5) { $myholiday = $mydate; }
			if ($saturday == 'y' && $day_of_week == 6) { $myholiday = $mydate; }

			if($myholiday == $mydate) 
			{
				// holiday is still true move up one day and test that one
				$mydate = date("Y-m-d", mktime(0, 0, 0, $mymonth , $myday+1, $myyear));
			} 
			else 
			{
				$holiday = false;
			}

			//echo "holiday $mydate<br>";
		}
		return $mydate;
	}

	/*
	 * ---------------------------------------------------------------------------
	 *  lookup the billing status and return it in the local language
	 * ---------------------------------------------------------------------------
	 */
	public function billingstatus($billing_id)
	{

		$status = "";
		$todaydate = date("Ymd");

		// get the two latest payment_history status values
		$query="SELECT * FROM payment_history 
			WHERE billing_id = '$billing_id' ORDER BY id DESC LIMIT 2";
		$result = $this->db->query($query) or die ("queryfailed");

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
			$status = lang('notrenewed');
		}

		//"Authorized", - an authorized credit card or invoice account
		// if last payment_history = authorized
		if ($firststatus == "authorized") {
			$status = lang('authorized');
		}

		//"Declined", - a declined credit card account
		// if last payment_history = declined
		if ($firststatus == "declined") 
		{
			$status = lang('declined');
			if ($rowcount == 1) 
			{
				// Initial Decline
				$status = "$l_initialdecline";
			}
			if ($secondstatus == "declined") 
			{
				// Declined 2X
				$status = lang('declined2x');
			}
		}


		//"Pending", - not being billed, pending an account change
		// if last payment_history = pending
		if (empty($next_billing_date) OR $next_billing_date == '0000-00-00') 
		{
			$status = lang('pending');
		}

		//"Turned Off", - turned off by us
		// if last payment_history is turned off
		// The middle past due days
		if ($firststatus == "turnedoff") 
		{
			$status = lang('turnedoff');
		}

		//"Notice Sent", - sent notice about to be shutoff
		// for carrier dependent services
		if ($firststatus == "noticesent") 
		{
			$status = lang('noticesent');
		}

		//"Waiting", - waiting for payment, stops pastdue process
		// for carrier dependent services
		if ($firststatus == "waiting") 
		{
			$status = lang('waiting');
		} 

		// Past Due  - status set by the activator when run daily
		//"Turned Off", - turned off by us
		// if last payment_history is turned off
		// The middle past due days
		if ($firststatus == "pastdue") 
		{
			$status = lang('pastdue');
		}

		// get pastdue_exempt status
		$query = "SELECT pastdue_exempt FROM billing WHERE id = $billing_id";
		$result = $this->db->query($query) or die("$l_queryfailed");;
		$myresult = $result->row();
		$pastdue_exempt = $myresult->pastdue_exempt;
		if ($pastdue_exempt == 'y') { $status = lang('pastdueexempt'); }
		if ($pastdue_exempt == 'bad_debt') { $status = lang('bad_debt'); }

		//"Free", - an account with the free billing type
		// overrides other billing types
		if ($method == "free") 
		{
			$status = lang('free');
		}

		//"Canceled" - canceled, has a cancel date
		// if they have a cancel date
		$query = "SELECT cancel_date FROM customer
			WHERE default_billing_id = '$billing_id' LIMIT 1";
		$result = $this->db->query($query) or die("$l_queryfailed");;
		$myresult = $result->row();
		if (!isset($myresult->cancel_date))
		{
			$cancel_date = NULL;
		}
		else
		{
			$cancel_date = $myresult->cancel_date;
		}

		if ($cancel_date) 
		{
			if ($firststatus == "cancelwfee") 
			{
				$status = lang('cancelwithfee');
			} 
			elseif ($firststatus == "collections") 
			{
				$status = lang('collections');
			} 
			else 
			{
				$status = lang('canceled');
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

	public function frequency_and_organization($billing_id)
	{
		// get the data from the billing tables to compare service and billing frequency
		$query = "SELECT * FROM billing b ".
			"LEFT JOIN billing_types t ON b.billing_type = t.id ".
			"LEFT JOIN general g ON g.id = b.organization_id ".
			"WHERE b.id = '$billing_id'";
		$freqoutput = $this->db->query($query) or die ("$l_queryfailed");

		return $freqoutput->row();

	}

	public function get_organization_id($account_number)
	{
		$query = "SELECT organization_id FROM billing ".
			"WHERE account_number = '$account_number' LIMIT 1";
		$orgresult = $this->db->query($query) or die ("$l_queryfailed");
		$myorgresult = $orgresult->row_array();

		return $myorgresult['organization_id'];

	}


	/*
	 * --------------------------------------------------------------------
	 *  set the to_date automatically according 
	 *  to the from_date and billing_type
	 * ---------------------------------------------------------------------
	 */
	function automatic_to_date ($from_date, $billing_type, $billing_id) {
		// figure out the billing frequency
		if (empty($from_date) OR $from_date == '0000-00-00') {
			$query = "UPDATE billing SET to_date = NULL WHERE id = '$billing_id'";
			$updateresult = $this->db->query($query) or die ("query failed");
		} else {
			$query = "SELECT * FROM billing_types WHERE id = $billing_type";
			$result = $this->db->query($query) or die ("query failed");
			$myresult = $result->row_array();
			$frequency = $myresult['frequency'];
			// add the number of frequency months to the from_date 
			// to get what the to_date should be set to
			$query = "UPDATE billing 
				SET to_date = DATE_ADD('$from_date', INTERVAL '$frequency' MONTH) 
				WHERE id = '$billing_id'";
			$updateresult = $this->db->query($query) or die ("query failed");	
		}
	}

	
	function rerunitems ($billing_id)
	{
		// select the billing detail items that are unpaid
		$query = "SELECT bd.id bd_id, bd.user_services_id, bd.billed_amount, ".
			"bd.original_invoice_number, ".
			"bd.creation_date, bd.paid_amount, us.id us_id, ms.service_description ".
			"FROM billing_details bd ".
			"LEFT JOIN user_services us ON us.id = bd.user_services_id ".
			"LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
			"WHERE bd.billing_id = '$billing_id' ".
			"AND bd.billed_amount > bd.paid_amount";
		$result = $this->db->query($query) or die ("Detail Query Failed"); 

		return $result->result_array();
	}


	function clearrerundate($billing_id)
	{
		// clear the rerun date in the billing record to get ready to reset it  
		$query = "UPDATE billing SET ".
			"rerun_date = NULL WHERE id = '$billing_id'";
		$updatedetail = $this->db->query($query) or die ("Detail Update Failed");
	}


	function clearrerunflag($detail_id)
	{
		$query = "UPDATE billing_details SET ".
			"rerun = 'n' WHERE id = '$detail_id'";
		$updatedetail = $this->db->query($query) or die ("Detail Update Failed");
	}

	/*
	 * -------------------------------------------------------------------------
	 *  Show the invoices that belong to that billing id
	 * -------------------------------------------------------------------------
	 */
	function list_invoices($billingid,$showall)
	{
		if ($showall) 
		{
			$query = "SELECT h.id h_id, h.billing_date h_billing_date, h.from_date 
				h_from_date, h.to_date h_to_date, h.payment_due_date h_due_date, 
				h.new_charges h_new_charges, h.total_due h_total_due,
				h.billing_type h_billing_type, 
				b.name b_name, b.company b_company, d.invoice_number, 
				SUM(d.paid_amount) as normal_paid_amount 
					FROM billing_history h
					LEFT JOIN billing b ON h.billing_id = b.id 
					LEFT JOIN billing_details d ON h.id = d.invoice_number 
					WHERE h.billing_id  = '$billingid' GROUP BY h.id 
					ORDER BY h.id DESC";
		} 
		else 
		{
			$query = "SELECT h.id h_id, h.billing_date h_billing_date, h.from_date 
				h_from_date, h.to_date h_to_date, h.payment_due_date h_due_date, 
				h.new_charges h_new_charges, h.total_due h_total_due,
				h.billing_type h_billing_type, 
				b.name b_name, b.company b_company, d.invoice_number, 
				SUM(d.paid_amount) as normal_paid_amount 
					FROM billing_history h
					LEFT JOIN billing b ON h.billing_id = b.id 
					LEFT JOIN billing_details d ON h.id = d.invoice_number 
					WHERE h.billing_id  = '$billingid' GROUP BY h.id 
					ORDER BY h.id DESC LIMIT 6";     
		}

		$result = $this->db->query($query) or die ("query failed");

		return $result->result_array();
	}


	function billing_details($billingid)
	{
		//
		// Show the billing details that belong to that billing id:
		//
		$query = "SELECT d.id d_id, d.billing_id d_billing_id, 
			d.creation_date d_creation_date, d.user_services_id d_user_services_id, 	
			d.taxed_services_id d_taxed_services_id, 
			d.invoice_number d_invoice_number, d.billed_amount d_billed_amount, 
			d.paid_amount d_paid_amount, d.refund_amount d_refund_amount, 
			d.refunded d_refunded, d.refund_date d_refund_date,  
			m.service_description m_description, 
			r.description r_description, 
			b.billing_type b_billing_type, bt.method bt_method 
				FROM billing_details d
				LEFT JOIN billing b ON b.id = d.billing_id 	
				LEFT JOIN user_services u ON u.id = d.user_services_id 
				LEFT JOIN master_services m ON m.id = u.master_service_id
				LEFT JOIN taxed_services t ON t.id = d.taxed_services_id
				LEFT JOIN tax_rates r ON t.tax_rate_id = r.id
				LEFT JOIN billing_types bt ON b.billing_type = bt.id  
				WHERE d.billing_id = '$billingid' ORDER BY d.id DESC";

		$result = $this->db->query($query) or die ("$l_queryfailed");

		return $result->result_array();

	}

	function billing_detail_item($detailid)
	{
		$query = "SELECT d.id d_id, d.billing_id d_billing_id, 
			d.creation_date d_creation_date, d.user_services_id d_user_services_id, 	
			d.taxed_services_id d_taxed_services_id, 
			d.invoice_number d_invoice_number, d.billed_amount d_billed_amount, 
			d.paid_amount d_paid_amount, d.refund_amount d_refund_amount, 
			d.refunded d_refunded, b.creditcard_number,   
			m.service_description m_description, 
			r.description r_description
				FROM billing_details d
				LEFT JOIN billing b ON b.id = d.billing_id 	
				LEFT JOIN user_services u ON u.id = d.user_services_id 
				LEFT JOIN master_services m ON m.id = u.master_service_id
				LEFT JOIN taxed_services t ON t.id = d.taxed_services_id
				LEFT JOIN tax_rates r ON t.tax_rate_id = r.id
				WHERE d.id = '$detailid'";

		$result = $this->db->query($query) or die ("$l_queryfailed");

		return $result->row_array();

	}

	function turnedoff_status($billing_id)
	{
		$query = "INSERT INTO payment_history 
			(creation_date, billing_id, status) 
			VALUES (CURRENT_DATE,'$billing_id','turnedoff')";
		$paymentresult = $this->db->query($query) or die ("query failed");

	}


	function waiting_status($billing_id)
	{
		$query = "INSERT INTO payment_history 
			(creation_date, billing_id, status) 
			VALUES (CURRENT_DATE,'$billing_id','waiting')";
		$paymentresult = $this->db->query($query) or die ("query failed");

	}


	function authorized_status($billing_id)
	{
		$query = "INSERT INTO payment_history 
			(creation_date, billing_id, status) 
			VALUES (CURRENT_DATE,'$billing_id','authorized')";
		$paymentresult = $this->db->query($query) or die ("query failed");

	}


	function check_canceled($account_number)
	{
		// check that the account is canceled first before allowing it to be marked
		// with this type of message
		$query = "SELECT cancel_date FROM customer ".
			"WHERE account_number = $account_number";
		$result = $this->db->query($query) or die ("query failed");
		$myresult = $result->row_array();

		return $myresult['cancel_date'];
	}


	function cancelwfee_status($billing_id)
	{
		$query = "INSERT INTO payment_history 
			(creation_date, billing_id, status) 
			VALUES (CURRENT_DATE,'$billing_id','cancelwfee')";
		$paymentresult = $this->db->query($query) or die ("query failed");

	}


	function collections_status($billing_id)
	{
		$query = "INSERT INTO payment_history 
			(creation_date, billing_id, status) 
			VALUES (CURRENT_DATE,'$billing_id','collections')";
		$paymentresult = $this->db->query($query) or die ("query failed");

	}

	
	function get_billing_method($billing_id)
	{
		// figure out the billing method so that this can make invoices for any method

		$query = "SELECT t.method FROM billing b LEFT JOIN billing_types t ".
			"ON t.id = b.billing_type WHERE b.id = $billing_id";
		$result = $this->db->query($query) or die ("Method Query Failed");
		$myresult = $result->row_array();	
		
		return $myresult['method'];
	}


	function get_nextbatchnumber() 
	{	
		/*--------------------------------------------------------------------*/
		// determine the next available batch number
		/*--------------------------------------------------------------------*/
		// insert empty into the batch to generate next value
		$query = "INSERT INTO batch () VALUES ()";
		$batchquery = $this->db->query($query) or die ("Batch ID Query Failed");
		// get the value just inserted
		$batchid = $this->db->insert_id();
		return $batchid;
	}



	function add_taxdetails($billingdate, $bybillingid, $billingmethod, 
			$batchid, $organization_id) 
	{
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
			$query = "SELECT ts.id ts_id, ts.master_services_id ts_serviceid, ".
				"ts.tax_rate_id ts_rateid, ms.id ms_id, ".
				"ms.service_description ms_description, ms.pricerate ms_pricerate, ".
				"ms.frequency ms_freq, tr.id tr_id, tr.description tr_description, ".
				"tr.rate tr_rate, tr.if_field tr_if_field, tr.if_value tr_if_value, ".
				"tr.percentage_or_fixed tr_percentage_or_fixed, ".
				"us.master_service_id us_msid, us.billing_id us_bid, us.id us_id, ".
				"us.removed us_removed, us.account_number us_account_number, ". 
				"us.usage_multiple us_usage_multiple,  ".
				"te.account_number te_account_number, te.tax_rate_id te_tax_rate_id, ".
				"b.id b_id, b.billing_type b_billing_type, ".
				"t.id t_id, t.frequency t_freq, t.method t_method ".
				"FROM taxed_services ts ".
				"LEFT JOIN user_services us ON ".
				"us.master_service_id = ts.master_services_id ".
				"LEFT JOIN master_services ms ON ms.id = ts.master_services_id ".
				"LEFT JOIN tax_rates tr ON tr.id = ts.tax_rate_id ".
				"LEFT JOIN tax_exempt te ON te.account_number = us.account_number ".
				"AND te.tax_rate_id = tr.id ".
				"LEFT JOIN billing b ON us.billing_id = b.id ".
				"LEFT JOIN billing_types t ON b.billing_type = t.id ".
				"WHERE b.next_billing_date = '$billingdate' ".
				"AND b.organization_id = '$organization_id' ".
				"AND t.method = '$billingmethod' AND us.removed <> 'y'";
		} else {
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
				"WHERE b.id = '$bybillingid' AND t.method = '$billingmethod' ".
				"AND us.removed <> 'y'";
		}	
		$taxresult = $this->db->query($query) or die ("Taxes Query Failed");

		// count the number of taxes
		$i = 0;

		foreach ($taxresult->result_array() as $mytaxresult) 
		{
			$billing_id = $mytaxresult['b_id'];
			$taxed_services_id = $mytaxresult['ts_id'];
			$user_services_id = $mytaxresult['us_id'];
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
				if ($if_field <> '') 
				{
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

				if (($checkvalue == $if_value) AND ($billing_freq > 0)) 
				{
					if ($percentage_or_fixed == 'percentage') 
					{
						if ($service_freq > 0) 
						{
							$servicecost = sprintf("%.2f",$taxrate * $pricerate);
							$tax_amount = sprintf("%.2f",$servicecost * $billing_freq * $service_freq * $usage_multiple); 
						} 
						else 
						{
							$servicecost = $pricerate * $usage_multiple;
							$tax_amount = $taxrate * $servicecost;
						}
					} 
					else 
					{
						// fixed fee amount does not depend on price or usage
						$tax_amount = $taxrate * $billing_freq;
					}

					// round the tax to two decimal places
					$tax_amount = sprintf("%.2f", $tax_amount);

					//
					// Insert tax result into billing_details
					//
					$query = "INSERT INTO billing_details (billing_id, creation_date, ".
						"user_services_id, taxed_services_id, billed_amount, batch) ".
						"VALUES ('$billing_id',CURRENT_DATE, '$user_services_id',".
						"'$taxed_services_id','$tax_amount','$batchid')";
					$invoiceresult = $this->db->query($query) or die ("Query Failed");
					$i++;
				} //endif if_field/billing_freq
			} // endif exempt
		}

		return $i; // send back number of taxes found
	}


	function add_servicedetails($billingdate, $bybillingid, $billingmethod, 
			$batchid, $organization_id) 
	{
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
		$result = $this->db->query($query) or die ("Services Query Failed");

		$i = 0; // count the billing services
		foreach ($result->result_array() as $myresult)
		{
			$billing_id = $myresult['u_bid'];
			$user_services_id = $myresult['u_id'];
			$pricerate = $myresult['m_pricerate'];
			$usage_multiple = $myresult['u_usage'];
			$service_freq = $myresult['m_freq'];
			$billing_freq = $myresult['t_freq'];

			if ($billing_freq > 0) 
			{
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
			}

			// insert this into the billing_details
			$query = "INSERT INTO billing_details (billing_id, 
				creation_date, user_services_id, billed_amount, batch) 
				VALUES ('$billing_id',CURRENT_DATE,'$user_services_id',
						'$billed_amount','$batchid')";
			$invoiceresult = $this->db->query($query) or die ("Query Failed");

			$i ++;
		} // end while

		//echo "$i services for found!<br>";
		return $i; 
		// return the number of services found
	}


	function create_billinghistory($batchid, $billingmethod, $user)
	{
		// go through a list of billing_id's that are to be billed in today's batch
		$query = "SELECT DISTINCT billing_id FROM billing_details ".
			"WHERE batch = '$batchid'";
		$distinctresult = $this->db->query($query) or die ("Query Failed");

		// set billingdate as today for checking rerun dates and stuff
		$billingdate = date("Y-m-d");

		// make a new billing_history record for each billing id group 
		// use the billing_history_id as the invoice number
		foreach ($distinctresult->result_array() as $myresult) 
		{
			$mybilling_id = $myresult['billing_id'];
			$query = "INSERT INTO billing_history ".
				"(billing_date, created_by, record_type, billing_type, ".
				"billing_id) ".
				"VALUES (CURRENT_DATE,'$user','bill','$billingmethod', ".
				"'$mybilling_id')";
			$historyresult = $this->db->query($query) or die ("Query Failed");	

			// set the invoice number for billing_details that have 
			// the corresponding billing_id 
			// and haven't been assigned an invoice number yet
			$myinsertid = $this->db->insert_id();
			$invoice_number=$myinsertid;
			$query = "UPDATE billing_details ".
				"SET invoice_number = '$invoice_number' ".
				"WHERE billing_id = '$mybilling_id' ".
				"AND invoice_number IS NULL";
			$invnumresult = $this->db->query($query) or die ("Query Failed");

			// set the original_invoice_number here for billing_details with a
			// null original_invoice_number (eg: a brand new one)
			// NOTE: recent changes 1/27/2011 make the original_invoice_number obsolete
			// but it is still printed and required for old items to look the same
			$query = "UPDATE billing_details SET ".
				"original_invoice_number = '$invoice_number' ".
				"WHERE invoice_number = '$invoice_number' ".
				"AND original_invoice_number IS NULL";
			$originalinvoice = $this->db->query($query) or die ("Query Failed");

			// set the recent_invoice_number here for billing_details with a
			// null recent_invoice_number, items included as new charges or pastdue on that most recent invoice
			// used for credit card imports to assign payments to hightest recent_invoice number
			$query = "UPDATE billing_details ".
				"SET recent_invoice_number = '$invoice_number' ".
				"WHERE billing_id = '$mybilling_id' ".
				"AND batch = '$batchid' ". 
				"AND recent_invoice_number IS NULL";
			$recentinvoice = $this->db->query($query) or die ("Query Failed");

			//
			// Apply Credits
			// check if they have any credits and apply as payments
			//
			$creditsapplied = $this->apply_credits($mybilling_id, $invoice_number);            

			// get the data for the service charges still pending 
			// and what services they have

			$query = "SELECT d.billing_id d_billing_id, ".
				"d.creation_date d_creation_date, ".
				"d.user_services_id d_user_services_id, ".
				"d.invoice_number d_invoice_number, ".
				"d.paid_amount d_paid_amount, d.billed_amount d_billed_amount, ".
				"d.taxed_services_id d_taxed_services_id, ".
				"b.rerun_date b_rerun_date, ".
				"u.id u_id, u.account_number u_account_number, ".
				"m.id m_id, m.service_description m_description, ".
				"m.options_table m_options_table, ".
				"ts.id ts_id, ts.master_services_id ".
				"ts_master_services_id, ts.tax_rate_id ts_tax_rate_id, ".
				"tr.id tr_id, tr.description tr_description ".
				"FROM billing_details d ".
				"LEFT JOIN billing b ON d.billing_id = b.id ".
				"LEFT JOIN user_services u ON d.user_services_id = u.id ".
				"LEFT JOIN master_services m ON u.master_service_id = m.id ".
				"LEFT JOIN taxed_services ts ON d.taxed_services_id = ts.id ".
				"LEFT JOIN tax_rates tr ON ts.tax_rate_id = tr.id ".
				"WHERE d.billing_id = $mybilling_id ".
				"AND ".
				"((d.paid_amount != d.billed_amount AND b.rerun_date IS NULL) ".
				"OR (d.rerun = 'y' AND d.rerun_date = b.rerun_date)) ".
				"ORDER BY d.taxed_services_id";

			$invoiceresult = $this->db->query($query) or die ("Query Failed"); 
			// used to show the invoice

			// get the data for the billing dates from the billing table
			$query = "SELECT * FROM billing WHERE id = $mybilling_id";
			$billingresult = $this->db->query($query) or die ("Query Failed");

			$myresult = $billingresult->row_array();
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

			// if this is a rerun, then set the payment_due_date to today for history
			if ($billing_rerun_date == $billingdate) 
			{
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

			foreach($invoiceresult->result_array() as $myinvresult) 
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

				if ($currinvoicenumber == $invoice_number) 
				{
					// new charges
					$billed_amount = sprintf("%.2f",$billed_amount);
					$new_charges = $new_charges + $billed_amount;
					// new taxes
					if ($taxdescription <> NULL) 
					{
						$tax_due = $tax_due + $billed_amount;
					}
				} 
				else 
				{
					// past due charges
					$pastdue = $pastdue + $billed_amount;
				} // end if

				// add to the running total
				//$invoicetotal = $billed_amount + $invoicetotal;

			} // end while

			//calculate the invoicetotal after credits applied
			$new_charges = sprintf("%.2f",$new_charges);
			$pastdue = sprintf("%.2f",$pastdue);
			$tax_due = sprintf("%.2f",$tax_due);

			// calculate total due
			// if new_charges is negative don't reduce pastdue with it
			if ($new_charges < 0) 
			{
				$total_due = sprintf("%.2f",$pastdue);
			} 
			else 
			{
				$total_due = sprintf("%.2f",$pastdue+$new_charges);
			}

			// check the pastdue status to see what note to include
			$status = $this->billingstatus($mybilling_id);

			// get the invoice notes from general
			$query = "SELECT g.default_invoicenote, g.pastdue_invoicenote, ".
				"g.turnedoff_invoicenote, g.collections_invoicenote ".
				"FROM billing b ".
				"LEFT JOIN general g ON g.id = b.organization_id ".
				"WHERE b.id = $mybilling_id";
			$generalresult = $this->db->query($query) or die ("query failed");

			$mygenresult = $generalresult->row_array();
			$default_invoicenote = $mygenresult['default_invoicenote'];
			$pastdue_invoicenote = $mygenresult['pastdue_invoicenote'];
			$turnedoff_invoicenote = $mygenresult['turnedoff_invoicenote'];
			$collections_invoicenote = $mygenresult['collections_invoicenote'];


			// if the individual customer's billing notes are blank
			// then use the automatic invoice notes from general config
			if (empty($billing_notes)) 
			{
				if ($status == $l_pastdue) 
				{
					$billing_notes = $pastdue_invoicenote;
				} 
				elseif ($status == $l_turnedoff) 
				{
					$billing_notes = $turnedoff_invoicenote;
				} 
				elseif ($status == $l_collections) 
				{
					$billing_notes = $collections_invoicenote;
				} 
				else 
				{
					$billing_notes = $default_invoicenote;
				}  
			}

			// update the billing history record	
			$query = "UPDATE billing_history SET ".
				"from_date = '$billing_fromdate', ".
				"to_date = '$billing_todate', ".
				"payment_due_date = '$billing_payment_due_date', ".
				"new_charges = '$new_charges', ".
				"past_due = '$pastdue', ".
				"credit_applied = '$creditsapplied', ".
				"late_fee = '0', ".
				"tax_due = '$tax_due', ".
				"total_due = '$total_due', ".
				"notes = '$billing_notes' ".
				"WHERE id = '$invoice_number'";
			$historyresult = $this->db->query($query) or die("history update failed");

			if ($billing_rerun_date <> $billingdate) 
			{      
				//// update the Next Billing Date to whatever 
				// the billing type dictates +1 +2 +6 etc.			
				// get the current billing type
				$query = "SELECT b.billing_type b_billing_type, ".
					"b.next_billing_date b_next_billing_date, ".
					"b.from_date b_from_date, b.to_date b_to_date, ".
					"t.id t_id, t.frequency t_frequency, ".
					"t.method t_method ".
					"FROM billing b ".
					"LEFT JOIN billing_types t ".
					"ON b.billing_type = t.id ".
					"WHERE b.id = '$mybilling_id'";		
				$billingqueryresult = $this->db->query($query) or die ("Query Failed");

				$billingresult = $billingqueryresult->row_array();
				$method = $billingresult['t_method'];

				// update the next billing dates for anything not a prepay
				if ($method <> 'prepaycc' & $method <> 'prepay') 
				{
					$mybillingdate = $billingresult['b_next_billing_date'];
					$myfromdate = $billingresult['b_from_date'];
					$mytodate = $billingresult['b_to_date'];
					$mybillingfreq = $billingresult['t_frequency'];

					// double frequency to add to the to_date
					$doublefreq = $mybillingfreq * 2;

					// insert the new next_billing_date, from_date, 
					// to_date, and payment_due_date to next from_date
					$query = "UPDATE billing ".
						"SET next_billing_date =  DATE_ADD('$mybillingdate', ".
						"INTERVAL '$mybillingfreq' MONTH), ".
						"from_date = DATE_ADD('$myfromdate', ". 
						"INTERVAL '$mybillingfreq' MONTH), ".
						"to_date = DATE_ADD('$myfromdate', ". 
						"INTERVAL '$doublefreq' MONTH), ".
						"payment_due_date = DATE_ADD('$myfromdate', ".	
						"INTERVAL '$mybillingfreq' MONTH) ".
						"WHERE id = '$mybilling_id'";		
					$updateresult = $this->db->query($query) or die ("Query Failed");

				} // endif prepaycc

			} // endif billing rerun

			// set the rerun date back to NULL now that we are done with it
			if ($billing_rerun_date) 
			{
				$query = "UPDATE billing SET rerun_date = NULL ".
					"WHERE id = '$mybilling_id'";
				$billing_rerun_null_result = $this->db->query($query)
					or die ("Rerun NULL Update Failed");      
			}

		} // end while

	} // end create_billinghistory function


	/*----------------------------------------------------------------------------*/
	// Apply Credits
	/*----------------------------------------------------------------------------*/
	function apply_credits($billing_id, $invoicenumber)
	{
		$total_credit_applied = 0;

		// find the credits
		$query = "SELECT * from billing_details ". 
			"WHERE paid_amount > billed_amount ".
			"AND billing_id = '$billing_id'";
		$creditresult = $this->db->query($query) or die ("query failed");

		foreach ($creditresult->result_array() as $mycreditresult) 
		{
			$credit_id = $mycreditresult['id'];
			$credit_billed_amount = $mycreditresult['billed_amount'];
			$credit_paid_amount = $mycreditresult['paid_amount']; 

			//
			// apply those credits towards amounts owed
			//

			// calculate credit amount
			$credit_amount = abs($credit_billed_amount - $credit_paid_amount);
			$amount = $credit_amount;	

			// find things to credit, apply only to items on current invoice with largest billed amount first
			$query = "SELECT * FROM billing_details ".
				"WHERE paid_amount < billed_amount AND invoice_number = $invoicenumber ORDER BY billed_amount DESC";
			$findresult = $this->db->query($query) or die ("query failed");

			while (($myfindresult = $findresult->result_array()) and ($amount > 0)) 
			{	
				$id = $myfindresult['id'];
				$paid_amount = $myfindresult['paid_amount'];
				$billed_amount = $myfindresult['billed_amount'];

				$owed = $billed_amount - $paid_amount;		

				// TODO the queries below should update the payment_applied date
				// for that item being paid for by the credit amount

				if ($amount >= $owed) 
				{
					$amount = $amount - $owed;
					$fillamount = $owed + $paid_amount;
					$query = "UPDATE billing_details ".
						"SET paid_amount = '$fillamount', ".
						"payment_applied = CURRENT_DATE ".
						"WHERE id = $id";
					$greaterthanresult = $this->db->query($query) or die ("query failed");
				} 
				else 
				{ 
					// amount is  less than owed
					$available = $amount;
					$amount = 0;
					$fillamount = $available + $paid_amount;
					$query = "UPDATE billing_details ".
						"SET paid_amount = '$fillamount', ".
						"payment_applied = CURRENT_DATE ".
						"WHERE id = $id";
					$lessthenresult = $this->db->query($query) or die ("query failed");
				}
			}

			//
			// reduce the amount of the credit left by the amount applied

			// TODO: I think this query should also update the payment_applied date
			// for the credit amount

			$credit_applied = $credit_amount - $amount;
			$credit_paid_total = $credit_paid_amount - $credit_applied;
			$query = "UPDATE billing_details ".
				"SET paid_amount = '$credit_paid_total', ".
				"payment_applied = CURRENT_DATE ".      
				"WHERE id = '$credit_id'";	
			$totalcreditresult = $this->db->query($query) or die ("query failed");

			$total_credit_applied = $total_credit_applied + $credit_applied;
		}
		return $total_credit_applied;
	}


	function update_invoice_duedate($duedate, $invoicenum)
	{
		$query = "UPDATE billing_history SET payment_due_date = '$duedate' ".
			"WHERE id = '$invoicenum'";
		$result = $this->db->query($query) or die ("due date update query failed");

	}


	function cancel_notice_canceldate($billing_id)
	{
		// calculate their cancel_date

		$query = "SELECT bi.id, bi.account_number, bh.payment_due_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_canceled DAY) ".
			"AS cancel_date ".
			"FROM billing_details bd ".
			"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
			"LEFT JOIN general g ON bi.organization_id = g.id ".
			"WHERE bd.billed_amount > bd.paid_amount ".
			"AND bd.billing_id = '$billing_id' GROUP BY bi.id";

		$result = $this->db->query($query) or die ("query failed");
		$myresult = $result->row_array();
		return $myresult['cancel_date'];
	}

}
