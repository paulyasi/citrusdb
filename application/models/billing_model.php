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
		if (!isset($myresult->cancel_date)) { $myresult->cancel_date = "";}	
		$cancel_date = $myresult->cancel_date;

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


}
