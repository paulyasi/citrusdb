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
			WHERE account_number = ?";
		$result = $this->db->query($query, array($my_account_number)) or die ("create_record queryfailed");
		$myresult = $result->row_array();
		$default_billing_id = $myresult['default_billing_id'];

		// query the default billing record for default name and address info to
		// insert into the new alternate billing record
		$query = "SELECT * FROM billing WHERE id = ?";
		$result = $this->db->query($query, array($default_billing_id)) or die ("create_record queryfailed");
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
			SET name = ?,
				company = ?,
				street = ?,
				city = ?,
				state = ?,
				country = ?,
				zip = ?,
				phone = ?,
				fax = ?,
				contact_email = ?,
				account_number = ?,
				billing_type = ?,
				creditcard_number = ?,
				creditcard_expire = ?,
				next_billing_date = ?,
				from_date = ?,
				to_date = ?,
				payment_due_date = ?,
				pastdue_exempt = ?,
				po_number = ?,
				encrypted_creditcard_number = ?,
				automatic_receipt = ?,
				organization_id = ?";
		$result = $this->db->query($query, array($name,
					$company,
					$street,
					$city,
					$state,
					$country,
					$zip,
					$phone,
					$fax,
					$contact_email,
					$my_account_number,
					$billing_type,
					$creditcard_number,
					$creditcard_expire,
					$next_billing_date,
					$from_date,
					$to_date,
					$payment_due_date,
					$pastdue_exempt,
					$po_number,
					$encrypted_creditcard_number,
					$automatic_receipt,
					$organization_id))
						or die ("create_record billing insert failed");
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
			"WHERE account_number = ?";
		$result = $this->db->query($query, array($account_number))
			or die ("default_billing_id queryfailed");
		$myresult = $result->row();	

		return $myresult->default_billing_id;
	}


	/*
	 * ------------------------------------------------------------------------
	 *  get alternate billing types for this billing id
	 * ------------------------------------------------------------------------
	 */
	public function alternates($account_number, $billing_id)
	{
		// print a list of alternate billing id's if any
		$query = "SELECT b.id b_id, g.org_name g_org_name, t.name t_name ".
			"FROM billing b ".	
			"LEFT JOIN billing_types t ON b.billing_type = t.id ".
			"LEFT JOIN general g ON b.organization_id = g.id ".
			"WHERE b.id != ? AND b.account_number = ?";
		$result = $this->db->query($query, array($billing_id, $account_number)) 
			or die ("alternates queryfailed");

		return $result->result_array();

	}


	/*
	 * ------------------------------------------------------------------------
	 *  get alternate billing types for this service orgnization id
	 * ------------------------------------------------------------------------
	 */
	public function org_alternates($account_number, $org_id)
	{
		$query = "SELECT b.id,bt.name,g.org_name FROM billing b ".
			"LEFT JOIN general g ON g.id = b.organization_id ".
			"LEFT JOIN billing_types bt ON b.billing_type = bt.id  ".
			"WHERE b.account_number = ? AND g.id = ?";

		$result = $this->db->query($query, array($account_number, $org_id)) or die ("query failed");
		return $result;
	}


	/*
	 * -------------------------------------------------------------------------
	 *  save record information
	 * -------------------------------------------------------------------------
	 */
	public function save_record($billing_id, $billing_data)
	{
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
			"WHERE b.id = ?";

		$result = $this->db->query($query, array($billing_id)) or die ("record queryfailed");
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
		$data['billing_type'] = $myresult['b_type'];
		$data['billing_type_name'] = $myresult['t_name'];
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
			"WHERE b.account_number = ?";
		$record_result = $this->db->query($query, array($account_number)) or die ("record_list query failed");

		// check if billing id has active services and what it's status is
		$i = 0; 
		// make a multi dimensional array of these results for each record assigned to this user
		foreach($record_result->result() as $myrecord)
		{
			$billing_id = $myrecord->b_id;
			$query = "SELECT billing_id FROM user_services ".
				"WHERE removed = 'n' AND billing_id = ? LIMIT 1";
			$usresult = $this->db->query($query, array($billing_id)) or die ("user_service queryfailed");
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
			$query = "SELECT holiday_date from holiday WHERE holiday_date = ?";
			$result = $this->db->query($query, array($mydate)) or die ("Holiday date Query Failed");
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
			WHERE billing_id = ? ORDER BY id DESC LIMIT 2";
		$result = $this->db->query($query, array($billing_id)) or die ("billingstatus queryfailed");

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
			WHERE b.id = ?";
		$result = $this->db->query($query, array($billing_id)) or die("billingstatus method query failed");;
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
				$status = lang('initialdecline');
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
		$query = "SELECT pastdue_exempt FROM billing WHERE id = ?";
		$result = $this->db->query($query, array($billing_id))
			or die("billingstatus pastdue_exempt queryfailed");;
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
			WHERE default_billing_id = ? LIMIT 1";
		$result = $this->db->query($query, array($billing_id)) or die("billingstatus cancel_date queryfailed");;
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


	/*
	 * -------------------------------------------------------------------------
	 * Add taxes together to get total for this billing id
	 * -------------------------------------------------------------------------
	 */
	function total_taxitems($bybillingid)
	{
		// query for taxed services that are billed by a specific billing id
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
			"WHERE b.id = ? AND us.removed <> 'y'";

		$taxresult = $this->db->query($query, array($bybillingid)) or die ("total_taxitems Query Failed");

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
			if ($tax_exempt_rate_id <> $tax_rate_id)
			{
				// check the if_field before adding to see if
				// the tax applies to this customer
				if ($if_field <> '')
				{
					$ifquery = "SELECT $if_field FROM customer ".
						"WHERE account_number = ?";
					$ifresult = $this->db->query($ifquery, array($my_account_number))
						or die ("total_taxitems if_field Query Failed");
					$myifresult = $ifresult->row_array();
					$checkvalue = $myifresult[$if_field];
				}
				else
				{
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


	/*
	 * -------------------------------------------------------------------------
	 *  Add services together to get total for this billing id
	 * -------------------------------------------------------------------------
	 */
	function total_serviceitems($bybillingid)
	{
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
			"WHERE b.id = ? ".
			"AND u.removed <> 'y'";

		$result = $this->db->query($query, array($bybillingid)) or die ("total_serviceitems Query Failed");

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


	/*
	 * -------------------------------------------------------------------------
	 * get the amounts for the past due charges for that billing id
	 * -------------------------------------------------------------------------
	 */
	function total_pastdueitems($mybilling_id)
	{
		$query = "SELECT d.billing_id d_billing_id, ".
			"d.paid_amount d_paid_amount, d.billed_amount d_billed_amount ".
			"FROM billing_details d ".
			"LEFT JOIN user_services u ON d.user_services_id = u.id ".
			"LEFT JOIN master_services m ON u.master_service_id = m.id ".
			"LEFT JOIN taxed_services ts ON d.taxed_services_id = ts.id ".
			"LEFT JOIN tax_rates tr ON ts.tax_rate_id = tr.id ".
			"WHERE d.billing_id = ? ".
			"AND d.paid_amount != d.billed_amount"; 	

		$invoiceresult = $this->db->query($query, array($mybilling_id)) or die ("total_pastdueitems Query Failed");

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


	/*
	 * -------------------------------------------------------------------------
	 *  get the data from the billing tables to compare service and billing frequency
	 * -------------------------------------------------------------------------
	 */
	public function frequency_and_organization($billing_id)
	{
		$query = "SELECT * FROM billing b ".
			"LEFT JOIN billing_types t ON b.billing_type = t.id ".
			"LEFT JOIN general g ON g.id = b.organization_id ".
			"WHERE b.id = ?";
		$freqoutput = $this->db->query($query, array($billing_id)) or die ("frequency_and_organization queryfailed");

		return $freqoutput->row();

	}


	function billing_and_organization($billingid)
	{
		$query = "SELECT * FROM billing b ".
			"LEFT JOIN general g ON g.id = b.organization_id ".
			"WHERE b.id = ?";
		$result = $this->db->query($query, array($billingid)) or die ("billing_and_organization Query Failed");
		return $result->row_array();
	}


	public function get_organization_id($account_number)
	{
		$query = "SELECT organization_id FROM billing ".
			"WHERE account_number = ? LIMIT 1";
		$orgresult = $this->db->query($query, array($account_number)) or die ("$l_queryfailed");
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
			$query = "UPDATE billing SET to_date = NULL WHERE id = ?";
			$updateresult = $this->db->query($query, array($billing_id)) or die ("automatic_to_date query failed");
		} else {
			$query = "SELECT * FROM billing_types WHERE id = ?";
			$result = $this->db->query($query, array($billing_type)) or die ("automatic_to_date type id query failed");
			$myresult = $result->row_array();
			$frequency = $myresult['frequency'];
			// add the number of frequency months to the from_date 
			// to get what the to_date should be set to
			$query = "UPDATE billing SET to_date = DATE_ADD(?, INTERVAL ? MONTH) WHERE id = ?";
			$updateresult = $this->db->query($query, array($from_date, $frequency, $billing_id))
				or die ("automatic_to_date update query failed");	
		}
	}


	/*
	 * -------------------------------------------------------------------------
	 *  select the billing detail items that are unpaid
	 * -------------------------------------------------------------------------		
	 */
	function rerunitems ($billing_id)
	{
		$query = "SELECT bd.id bd_id, bd.user_services_id, bd.billed_amount, ".
			"bd.original_invoice_number, ".
			"bd.creation_date, bd.paid_amount, us.id us_id, ms.service_description ".
			"FROM billing_details bd ".
			"LEFT JOIN user_services us ON us.id = bd.user_services_id ".
			"LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
			"WHERE bd.billing_id = ? ".
			"AND bd.billed_amount > bd.paid_amount";
		$result = $this->db->query($query, array($billing_id)) or die ("Detail Query Failed"); 

		return $result->result_array();
	}


	/*
	 * -------------------------------------------------------------------------			  
	 *  clear the rerun date in the billing record to get ready to reset it
	 * -------------------------------------------------------------------------
	 */
	function clearrerundate($billing_id)
	{

		$query = "UPDATE billing SET rerun_date = NULL WHERE id = ?";
		$updatedetail = $this->db->query($query, array($billing_id))
			or die ("clearrerundate Update Failed");
	}


	function clearrerunflag($detail_id)
	{
		$query = "UPDATE billing_details SET ".
			"rerun = 'n' WHERE id = ?";
		$updatedetail = $this->db->query($query, array($detail_id))
			or die ("clearrerunflag Update Failed");
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
					WHERE h.billing_id  = ? GROUP BY h.id 
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
					WHERE h.billing_id  = ? GROUP BY h.id 
					ORDER BY h.id DESC LIMIT 6";     
		}

		$result = $this->db->query($query, array($billingid))
			or die ("list_invoices query failed");

		return $result->result_array();
	}


	/*
	 * -------------------------------------------------------------------------
	 *  Show the billing details that belong to that billing id:
	 * -------------------------------------------------------------------------		
	 */
	function billing_details($billingid)
	{
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
				WHERE d.billing_id = ? ORDER BY d.id DESC";

		$result = $this->db->query($query, array($billingid))
			or die ("billing_details queryfailed");

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
				WHERE d.id = ?";

		$result = $this->db->query($query, array($detailid))
			or die ("billing_detail_item queryfailed");

		return $result->row_array();

	}


	function turnedoff_status($billing_id)
	{
		$query = "INSERT INTO payment_history 
			(creation_date, billing_id, status) 
			VALUES (CURRENT_DATE, ?, 'turnedoff')";
		$paymentresult = $this->db->query($query, array($billing_id))
			or die ("turnedoff_status query failed");

	}


	function waiting_status($billing_id)
	{
		$query = "INSERT INTO payment_history 
			(creation_date, billing_id, status) 
			VALUES (CURRENT_DATE, ?, 'waiting')";
		$paymentresult = $this->db->query($query, array($billing_id))
			or die ("waiting_status query failed");

	}


	function authorized_status($billing_id)
	{
		$query = "INSERT INTO payment_history 
			(creation_date, billing_id, status) 
			VALUES (CURRENT_DATE, ?, 'authorized')";
		$paymentresult = $this->db->query($query, array($billing_id))
			or die ("authorized_status query failed");

	}


	/*
	 * -------------------------------------------------------------------------
	 * check that the account is canceled first before allowing it to be marked
	 * with this type of message
	 * -------------------------------------------------------------------------	 
	 */
	function check_canceled($account_number)
	{
		$query = "SELECT cancel_date FROM customer ".
			"WHERE account_number = ?";
		$result = $this->db->query($query, array($account_number))
			or die ("check_canceled query failed");
		$myresult = $result->row_array();

		return $myresult['cancel_date'];
	}


	function cancelwfee_status($billing_id)
	{
		$query = "INSERT INTO payment_history 
			(creation_date, billing_id, status) 
			VALUES (CURRENT_DATE, ?, 'cancelwfee')";
		$paymentresult = $this->db->query($query, array($billing_id))
			or die ("cancelwfee_status query failed");

	}


	function collections_status($billing_id)
	{
		$query = "INSERT INTO payment_history 
			(creation_date, billing_id, status) 
			VALUES (CURRENT_DATE, ?, 'collections')";
		$paymentresult = $this->db->query($query, array($billing_id))
			or die ("collections_status query failed");
	}


	function get_billing_method($billing_id)
	{
		// figure out the billing method so that this can make invoices for any method

		$query = "SELECT t.method FROM billing b LEFT JOIN billing_types t ".
			"ON t.id = b.billing_type WHERE b.id = ?";
		$result = $this->db->query($query, array($billing_id)) or die ("get_billing_method Query Failed");
		$myresult = $result->row_array();	

		return $myresult['method'];
	}


	/*
	 * -------------------------------------------------------------------------
	 *  determine if they are a prepaycc or creditcard type
	 *  if they are prepaycc then update the billing dates
	 * -------------------------------------------------------------------------
	 */
	function get_billing_method_attributes($billing_id)
	{
		$query = "SELECT b.id b_id, b.billing_type b_billing_type, 
			b.next_billing_date b_next_billing_date, 
			b.from_date b_from_date, b.to_date b_to_date,
			b.contact_email b_contact_email, 
			t.frequency t_frequency,
			t.id t_id, t.method t_method FROM billing b 
				LEFT JOIN billing_types t ON b.billing_type = t.id
				WHERE b.id = ?";
		$typeresult = $this->db->query($query, array($billing_id))
			or die ("get_billing_method_attributes query failed");

		return $typeresult->row_array();
	}


	/*
	 * -------------------------------------------------------------------------
	 * determine the next available batch number
	 * -------------------------------------------------------------------------
	 */
	function get_nextbatchnumber() 
	{	
		// insert empty into the batch to generate next value
		$query = "INSERT INTO batch () VALUES ()";
		$batchquery = $this->db->query($query) or die ("get_nextbatchnumber Query Failed");
		// get the value just inserted
		$batchid = $this->db->insert_id();
		return $batchid;
	}


	/*
	 * -------------------------------------------------------------------------
	 *  Add taxes to the bill
	 *  database connector, billing date, billing method (invoice, prepay , 
	 *  creditcard etc.), batch id
	 *  do this before services to make sure one time services get taxed
	 *  before they are removed
	 * -------------------------------------------------------------------------
	 */
	function add_taxdetails($billingdate, $bybillingid, $billingmethod, 
			$batchid, $organization_id) 
	{
		// query for taxed services that are billed on the specified date
		// or a specific billing id
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
				"WHERE b.next_billing_date = ? ".
				"AND b.organization_id = ? ".
				"AND t.method = ? AND us.removed <> 'y'";

			$taxresult = $this->db->query($query, array($billingdate, $organization_id, $billingmethod))
				or die ("add_taxdetails 1 Query Failed");

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
				"WHERE b.id = ? AND t.method = ? ".
				"AND us.removed <> 'y'";

			$taxresult = $this->db->query($query, array($bybillingid, $billingmethod))
				or die ("add_taxdetails 2 Query Failed");

		}	


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
						"WHERE account_number = ?";
					$ifresult = $this->db->query($ifquery, array($my_account_number))
						or die ("add_taxdetails ifquery Query Failed");	
					$myifresult = $ifresult->row_array();
					$checkvalue = $myifresult[$if_field];
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
						"VALUES (?, CURRENT_DATE, ?, ?, ?, ?)";
					$invoiceresult = $this->db->query($query, array($billing_id,
								$user_services_id,
								$taxed_services_id,
								$tax_amount,
								$batchid))
						or die ("add_taxdetails INSERT Query Failed");
					$i++;
				} //endif if_field/billing_freq
			} // endif exempt
		}

		return $i; // send back number of taxes found
	}


	/*
	 * -------------------------------------------------------------------------
	 *  add services to the bill
	 * -------------------------------------------------------------------------
	 */
	function add_servicedetails($billingdate, $bybillingid, $billingmethod, 
			$batchid, $organization_id) 
	{
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
				WHERE b.next_billing_date = ? 
				AND b.organization_id = ? 
				AND t.method = ? AND u.removed <> 'y'";

			$result = $this->db->query($query, array($billingdate, $organization_id, $billingmethod))
				or die ("add_servicedetails 1 Query Failed");

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
					WHERE b.id = ? AND t.method = ? AND u.removed <> 'y'";

			$result = $this->db->query($query, array($bybillingid, $billingmethod))
				or die ("add_servicedetails 2 Query Failed");

		}


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
					$this->service_model->delete_service($user_services_id, 'onetime', $today);
				} // end if
			}

			// insert this into the billing_details
			$query = "INSERT INTO billing_details (billing_id, 
				creation_date, user_services_id, billed_amount, batch) 
				VALUES (?, CURRENT_DATE, ?, ?, ?)";
			$invoiceresult = $this->db->query($query, array($billing_id, $user_services_id, $billed_amount, $batchid))
				or die ("add_servicedetails insert billing details Query Failed");

			$i ++;
		} // end while

		//echo "$i services for found!<br>";
		return $i; 
		// return the number of services found
	}


	/*
	 * ----------------------------------------------------------------
	 *  Update Reruns to the bill
	 * ----------------------------------------------------------------
	 */
	function update_rerundetails($billingdate, $batchid, $organization_id)
	{
		// select the billing id's that have matching rerun dates
		$query = "SELECT id, rerun_date FROM billing ".
			"WHERE rerun_date = ? ".
			"AND organization_id = ?";
		$result = $this->db->query($query, array($billingdate, $organization_id))
			or die ("update_rerundetails Query Failed"); 

		$i = 0;

		foreach ($result->result_array() AS $myresult) 
		{
			$billing_id = $myresult['id'];
			$rerun_date = $myresult['rerun_date'];

			// set the item to be rerun that is unpaid and has the rerun flag set
			// set the recent_invoice_number to NULL so it is replaced when creating billing_history
			$query = "UPDATE billing_details SET ".         
				"batch = ?, ".        
				"recent_invoice_number = NULL, ".
				"rerun_date = ? ".
				"WHERE billing_id = ? ".
				"AND billed_amount > paid_amount ".
				"AND rerun = 'y'";

			$updateresult = $this->db->query($query, array($batchid, $rerun_date, $billing_id))
				or die ("update_rerundetails billing_details Query Failed");

			$i++;	
		}		

		//echo "$i accounts rerun<p>";
		return $i; // return the number of reruns updated
	}


	function create_billinghistory($batchid, $billingmethod, $user)
	{
		// go through a list of billing_id's that are to be billed in today's batch
		$query = "SELECT DISTINCT billing_id FROM billing_details ".
			"WHERE batch = ?";
		$distinctresult = $this->db->query($query, array($batchid))
			or die ("create_billinghistory select distinct billing id failed");

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
				"VALUES (CURRENT_DATE, ?,'bill', ?, ?)";
			$historyresult = $this->db->query($query, array($user, $billingmethod, $mybilling_id))
				or die ("create_billinghistory insert billing_histoyr Failed");	

			// set the invoice number for billing_details that have 
			// the corresponding billing_id 
			// and haven't been assigned an invoice number yet
			$myinsertid = $this->db->insert_id();
			$invoice_number=$myinsertid;
			$query = "UPDATE billing_details ".
				"SET invoice_number = ? ".
				"WHERE billing_id = ? ".
				"AND invoice_number IS NULL";
			$invnumresult = $this->db->query($query, array($invoice_number, $mybilling_id))
				or die ("create_billinghistory update billling_details Failed");

			// set the original_invoice_number here for billing_details with a
			// null original_invoice_number (eg: a brand new one)
			// NOTE: recent changes 1/27/2011 make the original_invoice_number obsolete
			// but it is still printed and required for old items to look the same
			$query = "UPDATE billing_details SET ".
				"original_invoice_number = ? ".
				"WHERE invoice_number = ? ".
				"AND original_invoice_number IS NULL";
			$originalinvoice = $this->db->query($query, array($invoice_number, $invoice_number))
				or die ("create_billinghistory update original_invoice_number Failed");

			// set the recent_invoice_number here for billing_details with a
			// null recent_invoice_number, items included as new charges or pastdue on that most recent invoice
			// used for credit card imports to assign payments to hightest recent_invoice number
			$query = "UPDATE billing_details ".
				"SET recent_invoice_number = ? ".
				"WHERE billing_id = ? ".
				"AND batch = ? ". 
				"AND recent_invoice_number IS NULL";
			$recentinvoice = $this->db->query($query, array($invoice_number, $mybilling_id, $batchid))
				or die ("create_billinghistory update recent_invoice_number Failed");


			// Apply Credits
			// check if they have any credits and apply as payments
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
				"WHERE d.billing_id = ? ".
				"AND ".
				"((d.paid_amount != d.billed_amount AND b.rerun_date IS NULL) ".
				"OR (d.rerun = 'y' AND d.rerun_date = b.rerun_date)) ".
				"ORDER BY d.taxed_services_id";

			$invoiceresult = $this->db->query($query, array($mybilling_id))
				or die ("create_billinghistory select billing_details failed"); 
			// used to show the invoice

			// get the data for the billing dates from the billing table
			$query = "SELECT * FROM billing WHERE id = ?";
			$billingresult = $this->db->query($query, array($mybilling_id))
				or die ("create_billinghistory select billing Failed");

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
				"WHERE b.id = ?";
			$generalresult = $this->db->query($query, array($mybilling_id))
				or die ("create_billinghistory seelct invoice notes failed");

			$mygenresult = $generalresult->row_array();
			$default_invoicenote = $mygenresult['default_invoicenote'];
			$pastdue_invoicenote = $mygenresult['pastdue_invoicenote'];
			$turnedoff_invoicenote = $mygenresult['turnedoff_invoicenote'];
			$collections_invoicenote = $mygenresult['collections_invoicenote'];


			// if the individual customer's billing notes are blank
			// then use the automatic invoice notes from general config
			if (empty($billing_notes)) 
			{
				if ($status == lang('pastdue')) 
				{
					$billing_notes = $pastdue_invoicenote;
				} 
				elseif ($status == lang('turnedoff')) 
				{
					$billing_notes = $turnedoff_invoicenote;
				} 
				elseif ($status == lang('collections')) 
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
				"from_date = ?, ".
				"to_date = ?, ".
				"payment_due_date = ?, ".
				"new_charges = ?, ".
				"past_due = ?, ".
				"credit_applied = ?, ".
				"late_fee = '0', ".
				"tax_due = ?, ".
				"total_due = ?, ".
				"notes = ? ".
				"WHERE id = ?";
			$historyresult = $this->db->query($query, array($billing_fromdate,
						$billing_todate,
						$billing_payment_due_date,
						$new_charges,
						$pastdue,
						$creditsapplied,
						$tax_due,
						$total_due,
						$billing_notes,
						$invoice_number))
				or die("create_billinghistory update billing_history failed");

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
					"WHERE b.id = ?";		
				$billingqueryresult = $this->db->query($query, array($mybilling_id))
					or die ("create_billinghistory select billing type Failed");

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
						"SET next_billing_date =  DATE_ADD(?, ".
						"INTERVAL ? MONTH), ".
						"from_date = DATE_ADD(?, ". 
						"INTERVAL ? MONTH), ".
						"to_date = DATE_ADD(?, ". 
						"INTERVAL ? MONTH), ".
						"payment_due_date = DATE_ADD(?, ".	
						"INTERVAL ? MONTH) ".
						"WHERE id = ?";		
					$updateresult = $this->db->query($query, array($mybillingdate,
								$mybillingfreq,
								$myfromdate,
								$mybillingfreq,
								$myfromdate,
								$doublefreq,
								$myfromdate,
								$mybillingfreq,
								$mybilling_id))
						or die ("create_billinghistory update billing dates Failed");

				} // endif prepaycc

			} // endif billing rerun

			// set the rerun date back to NULL now that we are done with it
			if ($billing_rerun_date) 
			{
				$query = "UPDATE billing SET rerun_date = NULL WHERE id = ?";
				$billing_rerun_null_result = $this->db->query($query, array($mybilling_id))
					or die("create_billinghistory set rerun null failed");
			}

		} // end while

	} // end create_billinghistory function


	/*
	 * ----------------------------------------------------------------------------
	 *  Apply Credits towards amounts owed
	 * ----------------------------------------------------------------------------
	 */
	function apply_credits($billing_id, $invoicenumber)
	{
		$total_credit_applied = 0;

		// find the credits
		$query = "SELECT * from billing_details ". 
			"WHERE paid_amount > billed_amount ".
			"AND billing_id = ?";
		$creditresult = $this->db->query($query, array($billing_id))
			or die ("apply_credits select failed");

		foreach ($creditresult->result_array() as $mycreditresult) 
		{
			$credit_id = $mycreditresult['id'];
			$credit_billed_amount = $mycreditresult['billed_amount'];
			$credit_paid_amount = $mycreditresult['paid_amount']; 

			// calculate credit amount
			$credit_amount = abs($credit_billed_amount - $credit_paid_amount);
			$amount = $credit_amount;	

			// find things to credit, apply only to items on current invoice with largest billed amount first
			$query = "SELECT * FROM billing_details ".
				"WHERE paid_amount < billed_amount AND invoice_number = ? ORDER BY billed_amount DESC";
			$findresult = $this->db->query($query, array($invoicenumber))
				or die ("apply_credits select billing details failed");

			$i = 0;
			while (($myfindresult = $findresult->row_array($i)) and ($amount > 0)) 
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
						"SET paid_amount = ?, ".
						"payment_applied = CURRENT_DATE ".
						"WHERE id = ?";
					$greaterthanresult = $this->db->query($query, array($fillamount, $id))
						or die ("apply_credits update billing_details failed");
				} 
				else 
				{ 
					// amount is  less than owed
					$available = $amount;
					$amount = 0;
					$fillamount = $available + $paid_amount;
					$query = "UPDATE billing_details ".
						"SET paid_amount = ?, ".
						"payment_applied = CURRENT_DATE ".
						"WHERE id = ?";
					$lessthenresult = $this->db->query($query, array($fillamount, $id))
						or die ("apply_credits update billing_details failed");
				}

				// increment the row counter
				$i++;	
			}

			// reduce the amount of the credit left by the amount applied

			// TODO: I think this query should also update the payment_applied date
			// for the credit amount

			$credit_applied = $credit_amount - $amount;
			$credit_paid_total = $credit_paid_amount - $credit_applied;
			$query = "UPDATE billing_details ".
				"SET paid_amount = ?, ".
				"payment_applied = CURRENT_DATE ".      
				"WHERE id = ?";	
			$totalcreditresult = $this->db->query($query, array($credit_paid_total, $credit_id))
				or die ("apply_credits update paid total query failed");

			$total_credit_applied = $total_credit_applied + $credit_applied;
		}
		return $total_credit_applied;
	}


	function update_invoice_duedate($duedate, $invoicenum)
	{
		$query = "UPDATE billing_history SET payment_due_date = ? WHERE id = ?";
		$result = $this->db->query($query, array($duedate, $invoicenum))
			or die ("due date update query failed");
	}


	/*
	 * -------------------------------------------------------------------------
	 *  calculate their cancel_date and turnoff date
	 * -------------------------------------------------------------------------
	 */
	function notice_dates($billing_id)
	{
		$query = "SELECT bi.id, bi.account_number, bh.payment_due_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_turnoff DAY) ".
			"AS turnoff_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_canceled DAY) ".
			"AS cancel_date ".
			"FROM billing_details bd ".
			"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
			"LEFT JOIN general g ON bi.organization_id = g.id ".
			"WHERE bd.billed_amount > bd.paid_amount ".
			"AND bd.billing_id = ? GROUP BY bi.id";

		$result = $this->db->query($query, array($billing_id))
			or die ("notice_dates query failed");

		return $result->row_array();
	}


	/*
	 * ------------------------------------------------------------------------
	 * output invoices in text or pdf format
	 * ------------------------------------------------------------------------
	 */
	function outputinvoice($invoiceid, $printtype, $pdfobject, $email = NULL) 
	{
		// get the invoice data to print on the bill
		$invoice_number = $invoiceid;

		$query = "SELECT h.id h_id, h.billing_date h_billing_date, ".
			"h.created_by h_created_by, h.billing_id h_billing_id, ".
			"h.from_date h_from_date, h.to_date h_to_date, ".
			"h.payment_due_date h_payment_due_date, ".
			"h.new_charges h_new_charges, h.past_due h_past_due, ".
			"h.late_fee h_late_fee, h.tax_due h_tax_due, ".
			"h.total_due h_total_due, h.notes h_notes, ".
			"h.credit_applied h_credit_applied, ".
			"b.id b_id, b.name b_name, b.company b_company, ".
			"b.street b_street, b.city b_city, b.state b_state, ".
			"b.country b_country, b.zip b_zip, b.po_number b_po_number, ".
			"b.contact_email b_contact_email, b.account_number b_acctnum, ".
			"b.creditcard_number b_ccnum, b.creditcard_expire b_ccexp ". 
			"FROM billing_history h ".
			"LEFT JOIN billing b ON h.billing_id = b.id ". 
			"WHERE h.id = ?";
		$invoiceresult = $this->db->query($query, array($invoice_number)) 
			or die ("query failed");	
		$myinvresult = $invoiceresult->row_array();
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
		$billing_credit_applied = sprintf("%.2f",$myinvresult['h_credit_applied']);
		$billing_email = $myinvresult['b_contact_email'];
		$billing_po_number = $myinvresult['b_po_number'];

		// get the organization info to print on the bill
		$query = "SELECT g.org_name,g.org_street,g.org_city,g.org_state,g.org_zip,".
			"g.phone_billing,g.email_billing,g.invoice_footer,g.einvoice_footer ".
			"FROM billing b ".
			"LEFT JOIN general g ON g.id = b.organization_id ".
			"WHERE b.id = ?";
		$generalresult = $this->db->query($query, array($mybilling_id)) 
			or die ("query failed");
		$mygenresult = $generalresult->row_array();
		$org_name = $mygenresult['org_name'];
		$org_street = $mygenresult['org_street'];
		$org_city = $mygenresult['org_city'];
		$org_state = $mygenresult['org_state'];
		$org_zip = $mygenresult['org_zip'];
		$phone_billing = $mygenresult['phone_billing'];
		$email_billing = $mygenresult['email_billing'];
		$invoice_footer = $mygenresult['invoice_footer'];
		$einvoice_footer = $mygenresult['einvoice_footer'];

		// output the invoice page

		// load the html to ascii helper for character conversions	
		$this->load->helper('htmlascii');

		// convert dates to human readable form using my date helper
		$this->load->helper('date');
		$billing_mydate = humandate($mydate);
		$billing_fromdate = humandate($billing_fromdate);
		$billing_todate = humandate($billing_todate);
		$billing_payment_due_date = humandate($billing_payment_due_date);

		if ($printtype == "pdf") 
		{
			$this->load->library('fpdf');    
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

			// get the page the current invoice in the batch starts on
			// necessary for batches with multiple invoices
			$invoicestartpage = $pdf->PageNo();

			$pdf->SetFont('Arial','B',18);
			$pdf->Cell(60,10,"$org_name",0);    
			$pdf->SetXY(10,20);
			$pdf->SetFont('Arial','',9);    
			$pdf->MultiCell(80,4,"$org_street\n$org_city, $org_state $org_zip\n$phone_billing\n$email_billing",0);
			$pdf->Rect(135,10,1,36,"F");

			$pdf->SetXY(140,10);
			$pdf->SetFontSize(10);
			$pdf->MultiCell(70,6,"$billing_mydate\n".lang('accountnumber').": $billing_acctnum\n".lang('invoicenumber').": $invoiceid\n$billing_fromdate ".lang('to')." $billing_todate\n".lang('paymentdue').": $billing_payment_due_date\n".lang('total').": $billing_total_due",0);
			$pdf->SetXY(10,60);
			$pdf->SetFontSize(10);

			if ($billing_po_number) 
			{
				// only print the po number if they have one
				$pdf->MultiCell(60,5,"$billing_name\n$billing_company\n$billing_street\n$billing_city $billing_state $billing_zip\n$l_po_number: $billing_po_number",0);
			} 
			else 
			{
				$pdf->MultiCell(60,5,"$billing_name\n$billing_company\n$billing_street\n$billing_city $billing_state $billing_zip\n",0);
			}

			$pdf->SetXY(130,60);

			$pdf->Line(5,102,200,102);
			$pdf->SetXY(10,103);
			$pdf->Cell(100,5,lang('description'));
			$pdf->SetXY(160,103);
			$pdf->Cell(50,5,lang('amount'));

		} 
		else 
		{
			$output = "$billing_mydate\n".lang('accountnumber').": $billing_acctnum\n\n";
			$output .= lang('invoicenumber').": $invoiceid\n";
			$output .= "$billing_fromdate - $billing_todate \n";
			$output .= lang('paymentduedate').": $billing_payment_due_date\n";
			$output .= "\n\n";

			$output .= lang('to').": $billing_email\n";
			$output .= "$billing_name $billing_company\n";
			$output .= "$billing_street ";
			$output .= "$billing_city $billing_state ";

			$output .= "$billing_zip\n";

			if ($billing_po_number) 
			{
				// only print the po number if they have one
				$output .= lang('po_number').": $billing_po_number\n";
			} 
			else 
			{
				$output .= "\n";
			}

			$output .= "----------------------------------------";
			$output .= "----------------------------------------\n";

		} // end if

		// Select the new charge details for a specific invoice number
		$query = "SELECT d.user_services_id d_user_services_id, ".
			"d.invoice_number d_invoice_number, ".
			"d.billed_amount d_billed_amount, ".
			"d.billing_id d_billing_id, ".
			"d.taxed_services_id d_taxed_services_id, ".
			"u.id u_id, u.master_service_id u_master_service_id, ".
			"u.usage_multiple u_usage_multiple, ".
			"m.options_table m_options_table, ".
			"m.id m_id, m.service_description m_service_description, m.pricerate, ".
			"ts.id ts_id, ts.master_services_id ts_master_services_id, ".
			"ts.tax_rate_id ts_tax_rate_id, tr.id tr_id, ".
			"tr.description tr_description ".
			"FROM billing_details d ".
			"LEFT JOIN user_services u ON d.user_services_id = u.id ".
			"LEFT JOIN master_services m ON u.master_service_id = m.id ".
			"LEFT JOIN taxed_services ts ON d.taxed_services_id = ts.id ".
			"LEFT JOIN tax_rates tr ON ts.tax_rate_id = tr.id ".
			"WHERE d.invoice_number = ? ORDER BY u.id DESC, tr.id ASC";

		$result = $this->db->query($query, array($invoiceid)) 
			or die ("select new charge details queryfailed");

		// initialize line item counters
		$myline = 1;
		$lineYoffset = 105;
		$fillcolor = 200;
		$lastserviceid = 0;

		// Print the invoice line items
		foreach($result->result_array() AS $myresult) 
		{
			// check if it's a tax with a tax id or service with
			// no tax idfirst to set detail items
			$serviceid = $myresult['u_id'];
			$taxid = $myresult['tr_id'];
			if ($taxid == NULL) 
			{
				// it's a service
				// select the options_table to get data for the details
				$options_table = $myresult['m_options_table'];
				$id = $myresult['u_id'];
				if ($options_table <> '') 
				{
					// get the data from the options table and put into variables
					$myoptions = $this->service_model->options_attributes($id, $options_table);
					//echo "$myoptions->username";
					if (count($myoptions) >= 3) {
						$optiondetails = $myoptions[2];
					} else {
						$optiondetails = '';
					}
				} 
				else 
				{
					$optiondetails = '';	
				}
				$service_description = $myresult['m_service_description'];
				$tax_description = '';
			} else {
				// it's a tax
				$tax_description = "     ".$myresult['tr_description'];
				$service_description = '';
				$optiondetails = '';
			}

			$billed_amount = sprintf("%.2f",$myresult['d_billed_amount']);

			// calculate the month multiple, only print for services, not taxes
			$pricerate = $myresult['pricerate'];
			if (($pricerate > 0) AND ($taxid == NULL)) 
			{
				$monthmultiple = $billed_amount/$pricerate;
			} 
			else 
			{
				$monthmultiple = 0;
			}

			if ($printtype == "pdf") 
			{
				// printing pdf invoice

				// alternate fill color
				if ($serviceid <> $lastserviceid) 
				{
					$lastserviceid = $serviceid;
					if ($fillcolor == 200) 
					{
						$fillcolor = 255;
						$pdf->SetFillColor($fillcolor);
					} 
					else 
					{
						$fillcolor = 200;
						$pdf->SetFillColor($fillcolor);
					}
				}

				$service_description = html_to_ascii($service_description);
				$tax_description = html_to_ascii($tax_description);
				$optiondetails = html_to_ascii($optiondetails);
				$lineY = $lineYoffset + ($myline*5);
				$pdf->SetXY(10,$lineY);

				if ($monthmultiple > 1) 
				{
					$pdf->Cell(151,5,"$serviceid $service_description $tax_description ($pricerate x $monthmultiple) $optiondetails", 0, 0, "L", TRUE);
				} 
				else 
				{
					$pdf->Cell(151,5,"$serviceid $service_description $tax_description $optiondetails", 0, 0, "L", TRUE);
				}

				//$pdf->SetXY(110,$lineY);
				//$pdf->Cell(110,5,"$optiondetails");
				$pdf->SetXY(160,$lineY);
				$pdf->Cell(40,5,"$billed_amount", 0, 0, "L", TRUE);
			} 
			else 
			{
				// printing text invoice
				if ($monthmultiple > 1) 
				{
					$output .= "$serviceid \t $service_description $tax_description ($pricerate x $monthmultiple) \t $optiondetails \t $billed_amount\n";
				} 
				else 
				{
					$output .= "$serviceid \t $service_description $tax_description \t $optiondetails \t $billed_amount\n";
				}
			}

			$myline++;		  


			if ($printtype == "pdf") 
			{
				// add a new page if there are many line items
				// TODO: check for page number here
				// if page number greater than 1, then myline would be larger
				// set an invoicestartpage at the start of each invoice for multi invoice batches
				$pagenumber = $pdf->PageNo();

				if ($pagenumber - $invoicestartpage > 0) 
				{
					$linetotal = 44;
				} 
				else 
				{
					$linetotal = 27;
				}

				if ($myline > $linetotal) 
				{
					$pdf->AddPage();
					$pdf->SetXY(10,20);
					$myline = 1;
					$lineYoffset = 20;
				}
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


		// print the notes and totals at the bottom of the invoice
		if ($email == TRUE)
		{
			// set the invoice footer to use the one for email invoices
			$invoice_footer = $einvoice_footer;
		}

		if ($printtype == "pdf") 
		{
			// fix html characters
			$billing_notes = html_to_ascii($billing_notes);
			$invoice_footer = html_to_ascii($invoice_footer);

			$lineY = $lineY + 10;
			$pdf->SetXY(10,$lineY);
			$pdf->MultiCell(100,5,"$billing_notes");
			$pdf->SetXY(135,$lineY);
			$pdf->MultiCell(100,5,lang('credit').": $billing_credit_applied\n".lang('newcharges').": $billing_new_charges\n".lang('pastdue').": $billing_past_due\n".lang('tax').": $billing_tax_due\n");
			$pdf->SetXY(135,$lineY+20);
			$pdf->SetFont('Arial','BU',10);
			$pdf->Cell(100,5,lang('total').": $billing_total_due");
			$lineY = $lineY + 10;
			$pdf->SetFont('Arial','',9);
			$pdf->SetXY(10,$lineY);
			$pdf->MultiCell(110,4,"$invoice_footer");
		} 
		else 
		{		
			$output .= "$billing_notes\n";
			$output .= lang('credit').": $billing_credit_applied\n";
			$output .= lang('newcharges').": $billing_new_charges\n";
			$output .= lang('pastdue').": $billing_past_due\n";
			$output .= lang('tax').": $billing_tax_due\n";
			$output .= lang('total').": $billing_total_due\n";

			$output .= "\n$invoice_footer\n";


		}

		if ($printtype == "pdf")
		{	
			return $pdf;
		}
		else
		{
			return $output;
		}
	} // end outputinvoice



	// output invoices in text or pdf format
	function outputextendedinvoice($invoiceid, $printtype, $pdfobject) 
	{
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
				WHERE h.id = ?";
		$invoiceresult = $this->db->query($query, array($invoice_number))
			or die ("output extended select failed");	
		$myinvresult = $invoiceresult->row_array();
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
		$query = "SELECT g.org_name,g.org_street,g.org_city,g.org_state,
			g.org_zip,g.phone_billing,g.email_billing,g.invoice_footer  
				FROM billing b
				LEFT JOIN general g ON g.id = b.organization_id 
				WHERE b.id = ?";
		$generalresult = $this->db->query($query, array($mybilling_id))
			or die ("output extended select organization failed");
		$mygenresult = $generalresult->row_array();
		$org_name = $mygenresult['org_name'];
		$org_street = $mygenresult['org_street'];
		$org_city = $mygenresult['org_city'];
		$org_state = $mygenresult['org_state'];
		$org_zip = $mygenresult['org_zip'];
		$phone_billing = $mygenresult['phone_billing'];
		$email_billing = $mygenresult['email_billing'];
		$invoice_footer = $mygenresult['invoice_footer'];


		// output the invoice page

		// load the html to ascii helper for character conversions	
		$this->load->helper('htmlascii');

		// convert dates to human readable form using my date helper
		$this->load->helper('date');
		// convert dates to human readable form
		$billing_mydate = humandate($mydate);
		$billing_fromdate = humandate($billing_fromdate);
		$billing_todate = humandate($billing_todate);
		$billing_payment_due_date = humandate($billing_payment_due_date);

		if ($printtype == "pdf")
		{
			$this->load->library('fpdf');    
			$pdf = $pdfobject;

			// convert html character codes to ascii for pdf
			$billing_name = html_to_ascii($billing_name);
			$billing_company = html_to_ascii($billing_company);
			$billing_street = html_to_ascii($billing_street);
			$billing_city = html_to_ascii($billing_city);
			$org_name = html_to_ascii($org_name);
			$org_street = html_to_ascii($org_street);
			$org_city = html_to_ascii($org_city);

			// make new page
			$pdf->AddPage();

			// get the page the current invoice in the batch starts on
			// necessary for batches with multiple invoices
			$invoicestartpage = $pdf->PageNo();

			$pdf->SetFont('Arial','B',18);
			$pdf->Cell(60,10,"$org_name",0);    
			$pdf->SetXY(10,20);
			$pdf->SetFont('Arial','',9);    
			$pdf->MultiCell(80,4,"$org_street\n$org_city, $org_state $org_zip\n$phone_billing\n$email_billing",0);
			$pdf->Rect(135,10,1,30,"F");

			$pdf->SetXY(140,10);
			$pdf->SetFontSize(10);
			$pdf->MultiCell(70,6,lang('accountnumber').": $billing_acctnum\n".lang('invoicenumber').": $invoiceid\n$billing_fromdate ".lang('to')." $billing_todate\n".lang('paymentdue').": $billing_payment_due_date\n".lang('total').": $billing_total_due",0);
			$pdf->SetXY(10,60);
			$pdf->SetFontSize(10);
			$pdf->MultiCell(60,5,"$billing_name\n$billing_company\n$billing_street\n$billing_city $billing_state $billing_zip",0);

			$pdf->SetXY(130,60);

			$pdf->Line(5,102,200,102);
			$pdf->SetXY(10,103);
			$pdf->Cell(100,5,lang('description'));
			$pdf->SetXY(160,103);
			$pdf->Cell(50,5,lang('amount'));

		} else {
			$output = lang('accountnumber').": $billing_acctnum\n\n";
			$output .= lang('invoicenumber').": $invoiceid\n";
			$output .= "$billing_fromdate - $billing_todate \n";
			$output .= lang('paymentduedate').": $billing_payment_due_date\n";
			$output .= lang('total').": $billing_total_due\n\n";

			$output .= lang('to').": $billing_email\n";
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
				WHERE d.invoice_number = ? ORDER BY u.id DESC, tr.id ASC";

		$result = $this->db->query($query, array($invoiceid))
			or die ("select new charge query failed");

		// initialize line counters
		$myline = 1;
		$lineYoffset = 105;
		$fillcolor = 200;
		$lastserviceid = 0;

		// Print the invoice line items
		foreach ($result->result_array() AS $myresult) 
		{
			// check if it's a tax with a tax id or service with
			// no tax idfirst to set detail items
			$serviceid = $myresult['u_id'];
			$taxid = $myresult['tr_id'];
			if ($taxid == NULL) 
			{
				// it's a service
				// select the options_table to get data for the details
				$options_table = $myresult['m_options_table'];
				$id = $myresult['u_id'];
				if ($options_table <> '') 
				{
					// get the data from the options table and put into variables
					$myoptions = $this->service_model->options_attributes($id, $options_table);
					//echo "$myoptions->username";
					if (count($myoptions) >= 3) {
						$optiondetails1 = $myoptions[2];
					} else {
						$optiondetails1 = '';
					}
					if (count($myoptions) >= 4) {
						$optiondetails2 = $myoptions[3];
					} else {
						$optiondetails2 = '';
					}
					$optiondetails = $optiondetails2 . "\t" . $optiondetails1;
				} 
				else 
				{
					$optiondetails = '';	
				}
				$service_description = $myresult['m_service_description'];
				$tax_description = '';
			} else {
				// it's a tax
				$tax_description = "     ".$myresult['tr_description'];
				$service_description = '';
				$optiondetails = '';
			}

			$billed_amount = sprintf("%.2f",$myresult['d_billed_amount']);

			// calculate the month multiple, only print for services, not taxes
			$pricerate = $myresult['pricerate'];
			if (($pricerate > 0) AND ($taxid == NULL))
			{
				$monthmultiple = $billed_amount/$pricerate;
			}
			else
			{
				$monthmultiple = 0;
			}

			if ($printtype == "pdf") {
				// printing pdf invoice

				// alternate fill color
				if ($serviceid <> $lastserviceid) {
					$lastserviceid = $serviceid;
					if ($fillcolor == 200) {
						$fillcolor = 255;
						$pdf->SetFillColor($fillcolor);
					} else {
						$fillcolor = 200;
						$pdf->SetFillColor($fillcolor);
					}
				}

				$service_description = html_to_ascii($service_description);
				$tax_description = html_to_ascii($tax_description);
				$optiondetails = html_to_ascii($optiondetails);
				$lineY = $lineYoffset + ($myline*5);
				$pdf->SetXY(10,$lineY);

				if ($monthmultiple > 1) {
					$pdf->Cell(151,5,"$serviceid $service_description $tax_description ($pricerate x $monthmultiple) $optiondetails", 0, 0, "L", TRUE);
				} else {
					$pdf->Cell(151,5,"$serviceid $service_description $tax_description $optiondetails", 0, 0, "L", TRUE);
				}

				//$pdf->SetXY(110,$lineY);
				//$pdf->Cell(110,5,"$optiondetails");
				$pdf->SetXY(160,$lineY);
				$pdf->Cell(40,5,"$billed_amount", 0, 0, "L", TRUE);
			} else {
				// printing text invoice
				if ($monthmultiple > 1) {
					if ($tax_description) {
						$output .= "$serviceid \t $service_description $tax_description ($pricerate x $monthmultiple) \t $optiondetails \t $billed_amount\n";
					} else {
						$output .= "$serviceid \t $service_description $tax_description ($pricerate x $monthmultiple) \t $optiondetails \t \t $billed_amount\n";	  
					}
				} else {
					if ($tax_description) {
						$output .= "$serviceid \t $service_description $tax_description \t $optiondetails \t \t $billed_amount\n";
					} else {
						$output .= "$serviceid \t $service_description $tax_description \t $optiondetails \t $billed_amount\n";	  
					}
				}
			}

			$myline++;		  


			if ($printtype == "pdf") {
				// add a new page if there are many line items
				// TODO: check for page number here
				// if page number greater than 1, then myline would be larger
				// set an invoicestartpage at the start of each invoice for multi invoice batches
				$pagenumber = $pdf->PageNo();

				if ($pagenumber - $invoicestartpage > 0) {
					$linetotal = 48;
				} else {
					$linetotal = 28;
				}

				if ($myline > $linetotal) {
					$pdf->AddPage();
					$pdf->SetXY(10,20);
					$myline = 1;
					$lineYoffset = 20;
				}
			}
		}
		if ($printtype == "pdf") {  
			$lineY = $lineYoffset + ($myline*5);
			$pdf->Line(5,$lineY,200,$lineY);
		} else {	
			$output .= "----------------------------------------";
			$output .= "----------------------------------------\n";
		}


		// print the notes and totals at the bottom of the invoice
		if ($printtype == "pdf") {
			// fix html characters
			$billing_notes = html_to_ascii($billing_notes);
			$invoice_footer = html_to_ascii($invoice_footer);

			$lineY = $lineY + 10;
			$pdf->SetXY(10,$lineY);
			$pdf->MultiCell(100,5,"$billing_notes");
			$pdf->SetXY(135,$lineY);
			$pdf->MultiCell(100,5,lang('newcharges').": $billing_new_charges\n".lang('pastdue').": $billing_past_due\n".lang('tax').": $billing_tax_due\n");
			$pdf->SetXY(135,$lineY+15);
			$pdf->SetFont('Arial','BU',10);
			$pdf->Cell(100,5,lang('total').": $billing_total_due");
			$lineY = $lineY + 10;
			$pdf->SetFont('Arial','',9);
			$pdf->SetXY(10,$lineY);
			$pdf->MultiCell(110,4,"$invoice_footer");
		} else {		
			$output .= "$billing_notes\n";

			$output .= lang('newcharges').": $billing_new_charges\n";
			$output .= lang('pastdue').": $billing_past_due\n";
			$output .= lang('tax').": $billing_tax_due\n";
			$output .= lang('total').": $billing_total_due\n";

			$output .= "\n$invoice_footer\n";


		}

		if ($printtype == "pdf")
		{	
			return $pdf;
		}
		else
		{
			return $output;
		}
	} // end outputextendedinvoice


	function get_billing_email($invoice_number) 
	{

		$query = "SELECT b.contact_email ".
			"FROM billing_history h ".
			"LEFT JOIN billing b ON h.billing_id = b.id ".
			"WHERE h.id = ?";
		$result = $this->db->query($query, array($invoice_number))
			or die ("get_billing_email queryfailed");
		$myresult = $result->row_array();

		return $myresult['contact_email'];

	}


	/*
	 * --------------------------------------------------------------------------
	 *  Create and send email invoice with pdf attached using swiftmailer
	 * --------------------------------------------------------------------------
	 */     
	function emailinvoice ($invoice_number,$contact_email,$invoice_billing_id) 
	{
		// include fpdf library and swiftmailer
		$this->load->library('fpdf');    

		// This can be removed if you use __autoload() in config.php OR use Modular Extensions
		require APPPATH.'/libraries/swift/lib/swift_required.php';

		// create a new pdf invoice
		$pdf = new FPDF();

		// check whether they want a pdf or txt invoice
		$query = "SELECT einvoice_type FROM billing WHERE id = ?";
		$type_result = $this->db->query($query, array($invoice_billing_id))
			or die ("einvoice_type query failed");
		$mytype_result = $type_result->row_array();
		$einvoice_type = $mytype_result['einvoice_type'];

		if ($einvoice_type == 'txt')
		{
			$txt = $this->outputinvoice($invoice_number, "html", $pdf, TRUE);
		}
		else
		{
			$pdf = $this->outputinvoice($invoice_number, "pdf", $pdf, TRUE);
			$pdfdata = $pdf->Output("invoice.pdf",'S');
		}

		// get the org billing email address for from address		
		$query = "SELECT g.org_name, g.org_street, g.org_city, ".
			"g.org_state, g.org_zip, g.phone_billing, g.email_billing ".
			"FROM billing b ".
			"LEFT JOIN general g ON g.id = b.organization_id  ".
			"WHERE b.id = ?";
		$ib_result = $this->db->query($query, array($invoice_billing_id))
			or die ("email invoice org query failed");
		$mybillingresult = $ib_result->row_array();
		$billing_email = $mybillingresult['email_billing'];
		$org_name = $mybillingresult['org_name'];
		$org_street = $mybillingresult['org_street'];
		$org_city = $mybillingresult['org_city'];
		$org_state = $mybillingresult['org_state'];
		$org_zip = $mybillingresult['org_zip'];
		$phone_billing = $mybillingresult['phone_billing'];

		// get the total due from the billing_history
		$query = "SELECT total_due FROM billing_history ".
			"WHERE id = ?";
		$iv_result = $this->db->query($query, array($invoice_number))
			or die ("total_due query failed");
		$myinvoiceresult = $iv_result->row_array();
		$total_due = sprintf("%.2f",$myinvoiceresult['total_due']);

		// build email message above invoice
		$email_message = lang('email_heading_thankyou')." $org_name.\n\n".
			lang('email_heading_presenting') .
			"$total_due ".lang('to_lc')." \n\n".
			"$org_name\n".
			"$org_street\n".
			"$org_city $org_state $org_zip\n".
			"$phone_billing\n\n".
			lang('email_heading_include').".\n\n";

		//Create the message
		$message = Swift_Message::newInstance();

		//Give the message a subject
		$message->setSubject(lang('einvoice')." $org_name");

		//Set the From address with an associative array
		$message->setFrom("$billing_email");

		//Set the To addresses with an associative array
		$message->setTo("$contact_email");

		if ($einvoice_type == 'txt')
		{
			$email_message .= $txt;
			$message->setBody("$email_message");
		}
		else
		{
			//Give it a body
			$message->setBody("$email_message");

			$filename = "invoice$invoice_number.pdf";
			$attachment = Swift_Attachment::newInstance($pdfdata, "$filename", 'application/pdf');  

			//Attach it to the message
			$message->attach($attachment);
		}

		//Create the Transport
		$transport = Swift_MailTransport::newInstance();

		//Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance($transport);

		//Send the message
		$mailresult = $mailer->send($message);
	}


	function get_payment_modes()
	{
		$query = "SELECT name FROM payment_mode";
		$result = $this->db->query($query) or die ("get_payment_modes failed");
		return $result->result_array();
	}


	function get_account_number($billing_id)
	{
		// get their account_number first
		$query = "SELECT account_number FROM billing WHERE id = ?";
		$bidresult = $this->db->query($query, array($billing_id)) 
			or die ("get_account_number queryfailed");
		$mybidresult = $bidresult->fields;

		return $mybidresult['account_number'];
	}


	function insert_card_payment_history($type, $transaction_code, $billing_id, 
			$cardnumber, $cardexp, $response_code, $amount, $avs_response)
	{
		$query = "INSERT INTO payment_history ".
			"(creation_date, transaction_code, billing_id, ".
			"creditcard_number,creditcard_expire, response_code, ".
			"billing_amount, status, payment_type, avs_response) ".
			"VALUES(CURRENT_DATE, ?, ?, ?, ?, ?, ?, ?,'creditcard', ?)";

		$result = $this->db->query($query, array($transaction_code,
					$billing_id,
					$cardnumber,
					$cardexp,
					$response_code,
					$amount,
					$type,
					$avs_response)) 
			or die ("insert_card_payment_history failed");

		return $this->db->insert_id();
	}


	function update_billing_dates($mybillingdate, $mybillingfreq,
			$myfromdate, $billing_id)
	{
		// to get the to_date, double the frequency
		$doublefreq = $mybillingfreq * 2;

		// insert the new dates
		$query = "UPDATE billing SET ".
			"next_billing_date = DATE_ADD(?, INTERVAL ? MONTH), ".
			"from_date = DATE_ADD(?, INTERVAL ? MONTH), ".
			"to_date = DATE_ADD(?, INTERVAL ? MONTH), ".
			"payment_due_date = DATE_ADD(?, INTERVAL ? MONTH) ".
			"WHERE id = ?";
		$updateresult = $this->db->query($query, array($mybillingdate, $mybillingfreq,
					$myfromdate, $mybillingfreq,
					$myfromdate, $doublefreq,
					$myfromdate, $mybillingfreq,
					$billing_id))
			or die ("update_billing_dates failed");
	}


	/*
	 * -------------------------------------------------------------------------
	 *  update the billing_details for things that still need to be paid
	 *  order by most recent invoice in desc order to pay newest run items first
	 *  thereby makeing sure to pay the correct rerun items too
	 * -------------------------------------------------------------------------
	 */
	function pay_billing_details($payment_history_id, $billing_id, $amount)
	{
		echo "paying";

		$query = "SELECT * FROM billing_details ". 
			"WHERE paid_amount < billed_amount ".
			"AND billing_id = ? ".
			"ORDER BY recent_invoice_number DESC";
		$result = $this->db->query($query, array($billing_id))
			or die ("pay_billing_details select failed");

		$i = 0;
		while (($myresult = $result->row_array($i)) and (round($amount,2) > 0)) 
		{
			$id = $myresult['id'];
			$paid_amount = sprintf("%.2f",$myresult['paid_amount']);
			$billed_amount = sprintf("%.2f",$myresult['billed_amount']);

			// do stuff 
			$owed = round($billed_amount - $paid_amount,2);

			if (round($amount,2) >= round($owed,2)) 
			{
				$amount = round($amount - $owed,2);
				$fillamount = round($owed + $paid_amount,2);
				$query = "UPDATE billing_details ".
					"SET paid_amount = ?, ".
					"payment_applied = CURRENT_DATE, ".
					"payment_history_id = ? ".	    
					"WHERE id = ?";
				$greaterthanresult = $this->db->query($query, array($fillamount,
							$payment_history_id,
							$id)) 
					or die ("pay_billing_details greater than queryfailed");
			} 
			else 
			{ 
				// amount is  less than owed
				$available = $amount;
				$amount = 0;
				$fillamount = round($available + $paid_amount,2);
				$query = "UPDATE billing_details ".
					"SET paid_amount = ?, ".
					"payment_applied = CURRENT_DATE, ".
					"payment_history_id = ? ".	    
					"WHERE id = ?";
				$lessthanresult = $this->db->query($query, array($fillamount,
							$payment_history_id,
							$id)) 
					or die ("pay_billing_details less than queryfailed");
			} //end if amount

			// increment row counter
			$i++;
		} // end while fetchrow

		// return the amount of money left after payment applied to this item
		return $amount;
	}


	/*
	 * ------------------------------------------------------------------------
	 * enter payments for this account, billing id, or invoice number
	 * returns left of amount and info about what account was paid in an array
	 * ------------------------------------------------------------------------
	 */
	function enter_invoice_payment($account_num, $billing_id, $amount, $payment_type, $invoice_number, $check_number)
	{
		if ($invoice_number == '') { $invoice_number = 0; }

		// set the payment to the amount entered
		$payment = $amount;


		// enter payments by invoice number				
		if ($invoice_number > 0)
		{
			$query = "SELECT * FROM billing_details ".
				"WHERE paid_amount < billed_amount AND invoice_number = ?";
			$result = $this->db->query($query, array($invoice_number))
				or die ("enter_invoice_payment by invoice num failed");
			$invoiceresult = $this->db->query($query, array($invoice_number))
				or die ("enter_invoice_payment by inv number failed");

			// update values with missing information
			$myresult = $invoiceresult->row_array();
			$billing_id = $myresult['billing_id'];

		}
		// enter payments by account number
		elseif ($account_num > 0) 
		{
			$query = "SELECT bd.id, bd.paid_amount, bd.billed_amount, bd.billing_id ".
				"FROM billing_details bd ".
				"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
				"LEFT JOIN customer cu ON bi.id = cu.default_billing_id ".
				"WHERE bd.paid_amount < bd.billed_amount ".
				"AND cu.account_number = ?";
			$result = $this->db->query($query, array($account_num))
				or die ("select acctnum query failed");
			$accountresult = $this->db->query($query, array($account_num))
				or die ("select acctnum query failed");

			// update values with missing information
			$myresult = $accountresult->row_array();
			$billing_id = $myresult['billing_id'];

		}
		// enter payments by billing id
		else 
		{
			$query = "SELECT * FROM billing_details ".
				"WHERE paid_amount < billed_amount AND billing_id = ?";
			$result = $this->db->query($query, array($billing_id))
				or die ("select billing_detail paid failed");	
		}

		// make sure the result of the above queries returned some rows
		$myrowcount = $result->num_rows();
		if ($myrowcount > 0) 
		{
			// insert info into the payment history
			$query = "INSERT INTO payment_history (creation_date, billing_id, ".
				"billing_amount, status, payment_type, invoice_number, check_number) ".
				"VALUES (CURRENT_DATE, ?, ?, 'authorized', ?, ?, ?)";
			$paymentresult = $this->db->query($query, array($billing_id,
						$payment,
						$payment_type,
						$invoice_number,
						$check_number))
				or die ("insert history query failed");

			// get the payment history id that will be inserted into the billing_details
			// items that are paid by this entry
			$payment_history_id = $this->db->insert_id();

			// go through the billing details items
			$i = 0;
			while (($myresult = $result->row_array($i)) and (round($amount,2) > 0)) 
			{
				$id = $myresult['id'];
				$paid_amount = sprintf("%.2f",$myresult['paid_amount']);
				$billed_amount = sprintf("%.2f",$myresult['billed_amount']);

				// calculate amount owed
				$owed = round($billed_amount - $paid_amount,2);

				// fix float precision too
				if (round($amount,2) >= round($owed,2)) {
					$amount = round($amount - $owed, 2);
					$fillamount = round($owed + $paid_amount,2);

					$query = "UPDATE billing_details ".
						"SET paid_amount = ?, ".
						"payment_applied = CURRENT_DATE, ".
						"payment_history_id = ? ".	    
						"WHERE id = ?";
					$greaterthanresult = $this->db->query($query, array($fillamount,
								$payment_history_id,
								$id))
						or die ("greater than detail update query failed");
				} 
				else 
				{ 
					// amount is  less than owed
					$available = $amount;
					$amount = 0;
					$fillamount = round($available + $paid_amount,2);

					$query = "UPDATE billing_details ".
						"SET paid_amount = ?, ".
						"payment_applied = CURRENT_DATE, ".
						"payment_history_id = ? ".	    	
						"WHERE id = ?";
					$lessthenresult = $this->db->query($query, array($fillamount,
								$payment_history_id,
								$id))
						or die ("less than detail update query failed");
				} // end if amount >= owed

				// increment row counter
				$i++;

			} // end while myresult and amount > 0


			/*--------------------------------------------------------------------*/
			// If the payment is made towards a prepaid account, then move
			// the billing and payment dates forward for payment terms
			//
			// update the Next Billing Date to whatever the 
			// billing type dictates +1 +2 +6 etc.
			// get the current billing type
			//
			// Also select the customer's billing name, company, and address
			// here to show up at the end to show what account paid.
			/*--------------------------------------------------------------------*/			

			$query = "SELECT b.billing_type b_billing_type, ".
				"b.next_billing_date b_next_billing_date, ".
				"b.from_date b_from_date, b.to_date b_to_date, ".
				"b.name, b.company, b.street, b.city, b.state, ".
				"b.account_number b_account_number, ".
				"t.id t_id, t.frequency t_frequency, t.method t_method ".
				"FROM billing b ".
				"LEFT JOIN billing_types t ON b.billing_type = t.id ".
				"WHERE b.id = ?";
			$result = $this->db->query($query, array($billing_id))
				or die ("select next billing query failed");
			$billingresult = $result->row_array();
			$method = $billingresult['t_method'];
			$billing_name = $billingresult['name'];
			$billing_company = $billingresult['company'];
			$billing_street = $billingresult['street'];
			$billing_city = $billingresult['city'];
			$billing_state = $billingresult['state'];
			$billing_account_number = $billingresult['b_account_number'];

			// if they are prepay accounts the update their billing dates
			if ($method == 'prepay' OR $method == 'prepaycc') 
			{
				$mybillingdate = $billingresult['b_next_billing_date'];
				$myfromdate = $billingresult['b_from_date'];
				$mytodate = $billingresult['b_to_date'];
				$mybillingfreq = $billingresult['t_frequency'];

				$this->update_billing_dates($mybillingdate, $mybillingfreq,
						$myfromdate, $billing_id);
			}


			// return amount left, zero or more info about who payment was applied to for
			// confirmation that it was applied to the right account by the user
			$data['amount'] = $amount;
			$data['billing_account_number'] = $billing_account_number;
			$data['billing_name'] = $billing_name;
			$data['billing_company'] = $billing_company;
			$data['billing_street'] = $billing_street;
			$data['billing_city'] = $billing_city;
			$data['billing_state'] = $billing_state;
			return $data; 

		} // end if result row_count > 0 

		else 
		{
			echo "ERROR: no matching rows, queryfailed";
		}

	}


	/*
	 * ------------------------------------------------------------------------
	 *  get the credit card export variables and file prefix
	 * ------------------------------------------------------------------------
	 */
	function ccexportvars($organization_id)
	{
		// select the info from general to get the export variables
		$query = "SELECT ccexportvarorder,exportprefix ".
			"FROM general WHERE id = ?";
		$ccvarresult = $this->db->query($query, array($organization_id))
			or die ("ccexportvars query failed");

		return $ccvarresult->row_array();
	}


	/*
	 * ------------------------------------------------------------------------
	 *  export credit card batch, print the credit card billing to a file
	 * ------------------------------------------------------------------------
	 */
	function export_card_batch($organization_id, $batchid, $path_to_ccfile, $passphrase)
	{
		// get the card export variables and filename prefix
		$myccvarresult = $this->ccexportvars($organization_id);
		$ccexportvarorder = $myccvarresult['ccexportvarorder'];
		$exportprefix = $myccvarresult['exportprefix'];	

		// convert the $ccexportvarorder &#036; 
		// dollar signs back to actual dollar signs and &quot; back to quotes
		$ccexportvarorder = str_replace( "&#036;", "$"        , $ccexportvarorder );
		$ccexportvarorder = str_replace( "&quot;", "\\\""        , $ccexportvarorder );

		// open the file
		$filename = "$path_to_ccfile" . "/" . "$exportprefix" . "export" . "$batchid.csv";
		$handle = fopen($filename, 'w') or die ("cannot open $filename"); // open the file

		// query the batch for the invoices to do
		$query = "SELECT DISTINCT d.recent_invoice_number FROM billing_details d 
			WHERE batch = ?";
		$result = $this->db->query($query, array($batchid))
			or die ("select recent_invoice_numbers failed");

		foreach ($result->result_array() AS $myresult) 
		{
			// get the recent invoice data to process now
			$invoice_number = $myresult['recent_invoice_number'];

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
				b.creditcard_number b_ccnum, b.creditcard_expire b_ccexp,
				b.encrypted_creditcard_number b_enc_ccnum 
					FROM billing_history h 
					LEFT JOIN billing b ON h.billing_id = b.id  
					WHERE h.id = ?";
			$invoiceresult = $this->db->query($query, array($invoice_number))
				or die ("query recent invoice data failed");	
			$myinvresult = $invoiceresult->row_array();
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
			$billing_ccnum = $myinvresult['b_ccnum'];
			$billing_ccexp = $myinvresult['b_ccexp'];
			$billing_fromdate = $myinvresult['h_from_date'];
			$billing_todate = $myinvresult['h_to_date'];
			$billing_payment_due_date = $myinvresult['h_payment_due_date'];
			$precisetotal = $myinvresult['h_total_due'];
			$encrypted_creditcard_number = $myinvresult['b_enc_ccnum'];

			// get the absolute value of the total
			$abstotal = abs($precisetotal);

			// decrypt the encrypted_creditcard and replace the billing_ccnum value with it
			// write the encrypted_creditcard_number to a temporary file
			// and decrypt that file to stdout to get the CC

			// open the file
			$cipherfilename = "$path_to_ccfile/ciphertext.tmp";
			$cipherhandle = fopen($cipherfilename, 'w') or die ("cannot open $cipherfilename");

			// write the ciphertext we want to decrypt into the file
			fwrite($cipherhandle, $encrypted_creditcard_number);

			// close the file
			fclose($cipherhandle);

			// destroy the output array before we use it again
			unset($decrypted);

			$gpgcommandline = $this->config->item('gpg_decrypt')." $cipherfilename";
			$decrypted = decrypt_command($gpgcommandline, $passphrase);

			// if there is a gpg error, stop here
			if (substr($decrypted,0,5) == "error") 
			{
				die ("Credit Card Encryption Error: $decrypted ".lang('billingid').": $mybilling_id");
			}

			// set the billing_ccnum to the decrypted_creditcard_number
			$decrypted_creditcard_number = $decrypted;
			$billing_ccnum = $decrypted_creditcard_number;		

			// determine the variable export order values
			eval ("\$exportstring = \"$ccexportvarorder\";");

			// print the line in the exported data file
			// don't print them to billing if the amount is less than or equal to zero
			if ($precisetotal > 0) 
			{
				$newline = "\"CHARGE\",$exportstring\n";
				fwrite($handle, $newline); // write to the file
			}
		} // end while

		// close the file
		fclose($handle); // close the file

		$filename = "$exportprefix" . "export" . "$batchid.csv";

		// return the name of the file where the export data is
		return $filename;
	}


	/*
	 * ------------------------------------------------------------------------
	 *  send a declined notice to the customer email address
	 * ------------------------------------------------------------------------
	 */
	function send_declined_email($mybilling_id)
	{
		// This can be removed if you use __autoload() in config.php OR use Modular Extensions
		require APPPATH.'/libraries/swift/lib/swift_required.php';

		// select the info for emailing the customer
		// get the org billing email address for from address           
		$query = "SELECT g.email_billing, g.declined_subject, g.declined_message, ".
			"b.contact_email, b.account_number, b.creditcard_number, b.creditcard_expire ". 
			"FROM billing b ".
			"LEFT JOIN general g ON b.organization_id = g.id ".
			"WHERE b.id = ?";
		$result = $this->db->query($query, array($mybilling_id))
			or die ("email declined query failed");
		$myresult = $result->row_array();
		$billing_email = $myresult['email_billing'];
		$subject = $myresult['declined_subject'];
		$myaccountnum = $myresult['account_number'];

		// wipe out the middle of the creditcard_number before it gets inserted
		$firstdigit = substr($myresult['creditcard_number'], 0,1);
		$lastfour = substr($myresult['creditcard_number'], -4);
		$maskedcard = "$firstdigit" . "***********" . "$lastfour";  

		$expdate = $myresult['creditcard_expire'];
		$declined_message = $myresult['declined_message'];
		$messagebody = lang('account').": $myaccountnum\n".lang('creditcard').
			": $maskedcard $expdate\n\n$declined_message";
		$contact_email = $myresult['contact_email'];

		//Create the message
		$message = Swift_Message::newInstance();

		//Give the message a subject
		$message->setSubject("$subject");

		//Set the From address with an associative array
		$message->setFrom("$billing_email");

		//Set the To addresses with an associative array
		$message->setTo("$contact_email");

		// set the message body
		$message->setBody("$messagebody");

		//Create the Transport
		$transport = Swift_MailTransport::newInstance();

		//Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance($transport);

		//Send the message
		$mailresult = $mailer->send($message);
		echo "sent decline to $contact_email<br>\n";
	}


	function export_card_refunds($organization_id, $path_to_ccfile, $passphrase)
	{
		$myccvarresult = $this->ccexportvars($organization_id);
		$ccexportvarorder = $myccvarresult['ccexportvarorder'];
		$exportprefix = $myccvarresult['exportprefix'];

		// convert the $ccexportvarorder &#036; dollar signs back 
		// to actual dollar signs and &quot; back to quotes
		$ccexportvarorder = str_replace( "&#036;"           , "$"        , $ccexportvarorder );
		$ccexportvarorder = str_replace( "&quot;"           , "\\\""        , $ccexportvarorder );

		// open the file
		$today = date("Y-m-d");
		$filename = "$path_to_ccfile" . "/" . "$exportprefix" . "refund" . "$today.csv";

		$handle = fopen($filename, 'w') or die ("cannot open $filename"); // open the file

		// query from billing_details the refunds to do
		$query = "SELECT ROUND(SUM(bd.refund_amount),2) AS RefundTotal, 
			b.id b_id, b.name b_name, b.company b_company, 
			b.street b_street, b.city b_city, 
			b.state b_state, b.zip b_zip, 
			b.account_number b_acctnum, 
			b.creditcard_number b_ccnum, b.encrypted_creditcard_number b_enc_num, 
			b.creditcard_expire b_ccexp, 
			b.from_date b_from_date, 
			b.to_date b_to_date, 
			b.payment_due_date b_payment_due_date,  
			bd.invoice_number bd_invoice_number, 
			bd.batch bd_batch   
				FROM billing_details bd
				LEFT JOIN billing b ON bd.billing_id = b.id 
				LEFT JOIN billing_types bt ON bt.id = b.billing_type 
				WHERE bd.refunded <> 'y' AND bd.refund_amount > 0 
				AND bt.method = 'creditcard' 
				AND b.organization_id = ? GROUP BY b.id";
		$result = $this->db->query($query, array($organization_id))
			or die ("export_card_refunds billing_details failed");

		foreach ($result->result_array() AS $myresult) 
		{
			$batchid = $myresult['bd_batch'];
			$invoice_number = $myresult['bd_invoice_number'];
			$user = "refund";
			$mydate = $today;
			$mybilling_id = $myresult['b_id'];
			$billing_name = $myresult['b_name'];
			$billing_company = $myresult['b_company'];
			$billing_street =  $myresult['b_street'];
			$billing_city = $myresult['b_city'];
			$billing_state = $myresult['b_state'];
			$billing_zip = $myresult['b_zip'];
			$billing_acctnum = $myresult['b_acctnum'];
			$billing_ccnum = $myresult['b_ccnum'];
			$billing_ccexp = $myresult['b_ccexp'];
			$billing_fromdate = $myresult['b_from_date'];
			$billing_todate = $myresult['b_to_date'];
			$billing_payment_due_date = $myresult['b_payment_due_date'];
			$precisetotal = $myresult['RefundTotal'];
			$encrypted_creditcard_number = $myresult['b_enc_num'];

			// get the absolute value of the total
			$abstotal = abs($precisetotal);

			// open the file
			$cipherfilename = "$path_to_ccfile/ciphertext.tmp";
			$cipherhandle = fopen($cipherfilename, 'w') or die ("cannot open $cipherfilename");

			// write the ciphertext we want to decrypt into the file
			fwrite($cipherhandle, $encrypted_creditcard_number);

			// close the file
			fclose($cipherhandle);

			//$gpgcommandline = "echo $passphrase | $gpg_decrypt $cipherfilename";

			// destroy the output array before we use it again
			unset($decrypted);

			//$gpgresult = exec($gpgcommandline, $decrypted, $errorcode);

			$gpgcommandline = $this->config->item('gpg_decrypt')." $cipherfilename";
			$decrypted = decrypt_command($gpgcommandline, $passphrase);

			// if there is a gpg error, stop here
			if (substr($decrypted,0,5) == "error") 
			{
				die ("Credit Card Encryption Error: $decrypted");
			}

			// set the billing_ccnum to the decrypted_creditcard_number
			$decrypted_creditcard_number = $decrypted;
			$billing_ccnum = $decrypted_creditcard_number;

			// determine the variable export order values
			eval ("\$exportstring = \"$ccexportvarorder\";");

			// print the line in the exported data file
			// don't print them to billing if the amount is less than or equal to zero
			$newline = "\"CREDIT\",$exportstring\n";

			fwrite($handle, $newline); // write to the file

			// mark the refunds as refunded
			$query ="UPDATE billing_details 
				SET refunded = 'y' 
				WHERE refunded <> 'y' AND refund_amount > 0 
				AND billing_id = ?";		
				$detailresult = $this->db->query($query, array($mybilling_id))
				or die ("update refund details query failed");	

		} // end while

		// close the file
		fclose($handle); // close the file

		$filename = "$exportprefix" . "refund" . "$today.csv";

		// return the name of the file where the export data is
		return $filename;
	}


	function reset_detail_refund_amount($detailid)
	{
		$query = "UPDATE billing_details SET ".
			"refund_amount = 0.00, ".
			"refund_date = null ".
			"WHERE id = ?";
		$result = $this->db->query($query, array($detailid))
			or die ("reset_detail_refund_amount failed");
	} 


	function update_detail_refund_amount($detailid, $amount)
	{
		$query = "UPDATE billing_details SET ".
			"refund_amount = '$amount', ".
			"refund_date = CURRENT_DATE ".
			"WHERE id = ?";
		$result = $this->db->query($query, array($detailid))
			or die ("update_detail_refund_amount failed");
	}


	function manual_detail_refund_amount($detailid)
	{
		// if billing method is not credit card they must be done manually
		// just mark the amount as refunded in the database
		$query ="UPDATE billing_details SET refunded = 'y' ".
			"WHERE refunded <> 'y' AND refund_amount > 0 ". 
			"AND id = ?";		
		$detailresult = $this->db->query($query, array($detailid))
			or die ("manual_detail_refund_amount failed");	
	}


	/*
	 * ------------------------------------------------------------------------
	 *  select the ascii armored card and card data from billing data
	 * ------------------------------------------------------------------------
	 */
	function get_ascii_armor($billing_id)
	{
		$query = "SELECT encrypted_creditcard_number, creditcard_number, ".
			"creditcard_expire FROM billing WHERE id = ?";
		$result = $this->db->query($query, array($billing_id))
			or die ("get_ascii_armor failed");

		return $result->row_array();	
	}


	/*
	 * ------------------------------------------------------------------------
	 *  input the ascii armor card data into billing
	 * ------------------------------------------------------------------------
	 */
	function input_ascii_armor($encrypted, $creditcard_number, $creditcard_expire, $billing_id)
	{
		// update the billing record with the new info
		$query = "UPDATE billing SET ".
			"encrypted_creditcard_number = ?, ".
			"creditcard_number = ?, ".
			"creditcard_expire = ? ".
			"WHERE id = ? LIMIT 1";
		$billingupdate = $this->db->query($query, array($encrypted,
					$creditcard_number, 
					$creditcard_expire,
					$billing_id))
			or die ("input_ascii_armor failed");
	}


	/*
	 * ------------------------------------------------------------------------
	 *  get some billing history data for history tab
	 * ------------------------------------------------------------------------
	 */
	function billinghistory($account_number)
	{
		$query = "SELECT h.id h_id, h.billing_id h_bid, h.billing_date h_bdate, 
			h.billing_type h_btype, h.from_date h_from, h.to_date h_to, h.total_due 
			h_total, h.new_charges h_new_charges,
			h.payment_due_date h_payment_due_date,
			c.account_number c_acctnum, b.account_number b_acctnum, b.id b_id 
				FROM billing_history h 
				LEFT JOIN billing b ON h.billing_id = b.id  
				LEFT JOIN customer c ON b.account_number = c.account_number
				WHERE b.account_number = ? ORDER BY h.id DESC LIMIT 24";

		$result = $this->db->query($query, array($account_number)) 
			or die ("billinghistory query failed");

		return $result->result_array();
	}	


	/*
	 * ------------------------------------------------------------------------
	 *  get all billing history data for history tab
	 * ------------------------------------------------------------------------
	 */
	function allbillinghistory($account_number)
	{
		$query = "SELECT h.id h_id, h.billing_id h_bid, h.billing_date h_bdate, 
			h.billing_type h_btype, h.from_date h_from, h.to_date h_to, h.total_due 
			h_total, h.new_charges h_new_charges,
			h.payment_due_date h_payment_due_date,
			c.account_number c_acctnum, b.account_number b_acctnum, b.id b_id 
				FROM billing_history h 
				LEFT JOIN billing b ON h.billing_id = b.id  
				LEFT JOIN customer c ON b.account_number = c.account_number
				WHERE b.account_number = ? ORDER BY h.id DESC";

		$result = $this->db->query($query, array($account_number)) 
			or die ("allbillinghistory query failed");

		return $result->result_array();
	}	


	/*
	 * ------------------------------------------------------------------------
	 *  get some payment history data for history tab
	 * ------------------------------------------------------------------------
	 */
	function paymenthistory($account_number)
	{
		$query = "SELECT p.id p_id, p.creation_date p_cdate, p.payment_type ".
			"p_payment_type, p.status p_status, p.billing_id p_billing_id, ".
			"p.invoice_number p_invoice_number, ".
			"p.billing_amount p_billing_amount, p.response_code p_response_code, ".
			"p.avs_response p_avs_response, p.check_number p_check_number, ".
			"c.account_number c_acctnum, p.creditcard_number p_creditcard_number, ".
			"p.creditcard_expire p_creditcard_expire, b.account_number b_acctnum, ".
			"b.id b_id ".
			"FROM payment_history p ".
			"LEFT JOIN billing b ON p.billing_id = b.id ".
			"LEFT JOIN customer c ON b.account_number = c.account_number ".
			"WHERE b.account_number = ? ORDER BY p.id DESC LIMIT 24";
		$result = $this->db->query($query, array($account_number))
			or die ("paymenthistory query failed");

		return $result->result_array();
	}


	/*
	 * ------------------------------------------------------------------------
	 *  get all payment history data for history tab
	 * ------------------------------------------------------------------------
	 */
	function allpaymenthistory($account_number)
	{
		$query = "SELECT p.id p_id, p.creation_date p_cdate, p.payment_type ".
			"p_payment_type, p.status p_status, p.billing_id p_billing_id, ".
			"p.invoice_number p_invoice_number, ".
			"p.billing_amount p_billing_amount, p.response_code p_response_code, ".
			"p.avs_response p_avs_response, p.check_number p_check_number, ".
			"c.account_number c_acctnum, p.creditcard_number p_creditcard_number, ".
			"p.creditcard_expire p_creditcard_expire, b.account_number b_acctnum, ".
			"b.id b_id ".
			"FROM payment_history p ".
			"LEFT JOIN billing b ON p.billing_id = b.id ".
			"LEFT JOIN customer c ON b.account_number = c.account_number ".
			"WHERE b.account_number = ? ORDER BY p.id DESC";
		$result = $this->db->query($query, array($account_number))
			or die ("all_payment_history query failed");

		return $result->result_array();
	}


	/*
	 * ------------------------------------------------------------------------
	 *  get some detail history data for history tab
	 * ------------------------------------------------------------------------
	 */
	function detailhistory($account_number)
	{
		$query = "SELECT d.id d_id, d.billing_id d_billing_id, ".
			"d.creation_date d_creation_date, d.user_services_id d_user_services_id, ".
			"d.taxed_services_id d_taxed_services_id, d.invoice_number d_invoice_number, ".
			"d.billed_amount d_billed_amount, d.paid_amount d_paid_amount, ".
			"d.refund_amount d_refund_amount, d.refunded d_refunded, ".
			"d.rerun d_rerun, d.original_invoice_number d_original_invoice, ".
			"m.service_description m_description, r.description r_description, ".
			"bh.from_date bh_from_date, bh.to_date bh_to_date, ".
			"bh.payment_due_date bh_due_date, ph.creation_date ph_creation_date ".
			"FROM billing_details d ".
			"LEFT JOIN billing b ON b.id = d.billing_id ".
			"LEFT JOIN billing_history bh ON bh.id = d.original_invoice_number ".
			"LEFT JOIN payment_history ph ON ph.id = d.payment_history_id ".
			"LEFT JOIN user_services u ON u.id = d.user_services_id ".
			"LEFT JOIN master_services m ON m.id = u.master_service_id ".
			"LEFT JOIN taxed_services t ON t.id = d.taxed_services_id ".
			"LEFT JOIN tax_rates r ON t.tax_rate_id = r.id ".
			"WHERE b.account_number = ? ORDER BY d.id DESC LIMIT 400";
		$result = $this->db->query($query, array($account_number))
			or die ("detailhistory query failed");

		return $result->result_array();
	}


	/*
	 * ------------------------------------------------------------------------
	 *  get all detail history data for history tab
	 * ------------------------------------------------------------------------
	 */
	function alldetailhistory($account_number)
	{
		$query = "SELECT d.id d_id, d.billing_id d_billing_id, ".
			"d.creation_date d_creation_date, d.user_services_id d_user_services_id, ".
			"d.taxed_services_id d_taxed_services_id, d.invoice_number d_invoice_number, ".
			"d.billed_amount d_billed_amount, d.paid_amount d_paid_amount, ".
			"d.refund_amount d_refund_amount, d.refunded d_refunded, ".
			"d.rerun d_rerun, d.original_invoice_number d_original_invoice, ".
			"m.service_description m_description, r.description r_description, ".
			"bh.from_date bh_from_date, bh.to_date bh_to_date, ".
			"bh.payment_due_date bh_due_date, ph.creation_date ph_creation_date ".
			"FROM billing_details d ".
			"LEFT JOIN billing b ON b.id = d.billing_id ".
			"LEFT JOIN billing_history bh ON bh.id = d.original_invoice_number ".
			"LEFT JOIN payment_history ph ON ph.id = d.payment_history_id ".
			"LEFT JOIN user_services u ON u.id = d.user_services_id ".
			"LEFT JOIN master_services m ON m.id = u.master_service_id ".
			"LEFT JOIN taxed_services t ON t.id = d.taxed_services_id ".
			"LEFT JOIN tax_rates r ON t.tax_rate_id = r.id ".
			"WHERE b.account_number = ? ORDER BY d.id DESC";
		$result = $this->db->query($query, array($account_number))
			or die ("alldetailhistory query failed");

		return $result->result_array();
	}


	function set_nsf_funds($paymentid, $amount, $invoicenum, $billingid)
	{	
		// set the account payment_history to nsf
		$query = "UPDATE payment_history ".
			"SET payment_type = 'nsf', ".
			"status = 'declined' ".
			"WHERE id = ?";
		$paymentresult = $this->db->query($query, array($paymentid)) 
			or die ("nsf payment history query failed");

		// remove paid_amounts from billing_details
		// get the resulting list of services to have payments removed from
		if ($invoice_number > 0) 
		{
			// remove payments by invoice number if paid to a particular invoice
			$query = "SELECT * FROM billing_details ".
				"WHERE paid_amount > 0 AND invoice_number = ?";
			$result = $this->db->query($query, array($invoice_number)) 
				or die ("set_nsf invoice1 Query Failed");
		} 
		else 
		{
			// else remove payments where paid anything
			$query = "SELECT * FROM billing_details ". 
				"WHERE paid_amount > 0 AND billing_id = ?";
			$result = $this->db->query($query, array($billingid)) 
				or die ("set_nsf billingid query failed");	
		}

		// go through the list and subtract the payment from each until
		// the amount is depleted
		$i = 0;
		while (($myresult = $result->row_array($i)) and (round($amount,2) > 0)) 
		{
			$id = $myresult['id'];
			$paid_amount = sprintf("%.2f",$myresult['paid_amount']);

			// fix float precision too    
			if (round($amount,2) >= round($paid_amount,2)) 
			{
				$amount = round($amount - $paid_amount, 2);
				$fillamount = 0;

				$query = "UPDATE billing_details ".
					"SET paid_amount = ? ".
					"WHERE id = ?";
				$greaterthanresult = $this->db->query($query, array($fillamount, $id))
					or die ("nsf greaterthan query failed");

			} 
			else 
			{ 
				// amount is less than paid_amount
				$available = $amount;
				$amount = 0;
				$fillamount = round($paid_amount - $available,2);

				$query = "UPDATE billing_details ".
					"SET paid_amount = ? ".
					"WHERE id = ?";
				$lessthenresult = $this->db->query($query, array($fillamount, $id)) 
					or die ("nsf lessthan query failed");
			}

			// increment row count
			$i++;
		}
	}


	function delete_payment($paymentid)
	{	
		// delete the payment history item
		$query = "DELETE FROM payment_history ".
			"WHERE id = ? LIMIT 1";
		$paymentresult = $this->db->query($query, array($paymentid)) 
			or die ("delete_payment query failed");
	}	


	function get_payment_type($paymentid)
	{
		// grab the payment type, cardnumber, and check number
		$query = "SELECT payment_type, creditcard_number, check_number ".
			"FROM payment_history WHERE id = ?";
		$result = $this->db->query($query, array($paymentid))
			or die ("get_payment_type Query Failed");
		return $result->row_array();
	}


	function payment_details($paymentid)
	{
		$query = "SELECT bd.original_invoice_number, bd.paid_amount,".
			"bh.from_date, bh.to_date, bd.user_services_id, ".
			"bd.billed_amount, ms.options_table, ms.service_description, ".
			"tr.description ".
			"FROM billing_details bd ".
			"LEFT JOIN user_services us ON us.id = bd.user_services_id ".
			"LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
			"LEFT JOIN taxed_services ts ON ts.id = bd.taxed_services_id ".
			"LEFT JOIN tax_rates tr ON tr.id = ts.tax_rate_id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.original_invoice_number ".
			"WHERE bd.payment_history_id = ? ORDER BY bd.taxed_services_id";
		$result = $this->db->query($query, array($paymentid))
			or die ("payment_details  Query Failed");

		return $result->result_array();
	}


	/*
	 * ------------------------------------------------------------------------
	 *  get the invoice numbers for printed or einvoice batches 
	 *  NOTE: not recent_invoice_number used for credit card batches
	 * ------------------------------------------------------------------------
	 */
	function get_invoice_batch($batchid)
	{
		$query = "SELECT DISTINCT d.invoice_number, b.contact_email, b.id, ".
			"b.account_number ".
			"FROM billing_details d ".
			"LEFT JOIN billing b on b.id = d.billing_id ".
			"WHERE d.batch = ?";
		$result = $this->db->query($query, array($batchid))
			or die ("get_invoice_batch queryfailed");
		return $result->result_array();
	}


	/*
	 * ------------------------------------------------------------------------
	 *  get the next billing date for the default billing id for this account
	 * ------------------------------------------------------------------------
	 */
	function default_next_billing_date($account_number)
	{
		$query = "SELECT b.next_billing_date FROM customer c " .
			"LEFT JOIN billing b ON c.default_billing_id = b.id ".
			"WHERE c.account_number = ?";
		$result = $this->db->query($query, array($account_number)) 
			or die ("default_next_billing_date queryfailed");
		$myresult = $result->row_array();
		return $myresult['next_billing_date'];
	}


	/*
	 * ------------------------------------------------------------------------
	 *  get the next billing date for the specific billing id 
	 * ------------------------------------------------------------------------
	 */
	function billing_next_billing_date($billing_id)
	{
		$query = "SELECT next_billing_date FROM billing WHERE id = ?";
		$result = $this->db->query($query, array($billing_id)) 
			or die ("billing_next_billing_date queryfailed");
		$myresult = $result->row_array();	
		return $myresult['next_billing_date'];
	}


	function set_rerun_date($mydate, $billing_id)
	{
		$query = "UPDATE billing SET rerun_date = ? ".
			"WHERE id = ?";
		$result = $this->db->query($query, array($mydate, $billing_id)) 
			or die ("set_rerun_date queryfailed");
	}


	function set_rerun_item($id)
	{
		$query = "UPDATE billing_details SET rerun = 'y' ".
			"WHERE id = ?";
		$result = $this->db->query($query, array($id)) 
			or die ("set_rerun_item queryfailed");
	}


	function delete_invoice($invoicenum)
	{
		// Delete the invoice, delete from billing history where id = $invoicenum
		$query = "DELETE FROM billing_history WHERE id = ?";
		$result = $this->db->query($query, array($invoicenum)) 
			or die ("delete_invoice billing_history failed");

		// delete from billing_details where invoice_number = $invoicenum
		$query = "DELETE FROM billing_details ".
			"WHERE invoice_number = ?";                                          
		$result = $this->db->query($query, array($invoicenum)) 
			or die ("delete_invoice billing_details failed");
	}


	// save the tax exempt status information, make the customer tax exempt
	function set_tax_exempt($account_number, $tax_rate_id, $customer_tax_id, $expdate)
	{
		$query = "INSERT INTO tax_exempt ".
			"(account_number, tax_rate_id, customer_tax_id, expdate) ". 
			"VALUES (?, ?, ?, ?)";
		$result = $this->db->query($query, array($account_number,
					$tax_rate_id,
					$customer_tax_id,
					$expdate))
			or die ("save_tax_exempt queryfailed");
	}


	// make the customer tax not-exempt
	function remove_tax_exempt($account_number, $tax_rate_id)
	{
		$query = "DELETE FROM tax_exempt WHERE tax_rate_id = ? ".
			"AND account_number = ?";
		$result = $this->db->query($query, array($tax_rate_id, $account_number))
			or die ("remove_tax_exempt queryfailed");
	}


	// when re-keying credit cards use this to get the list of credit cards to decrypt
	// use result to walk through results one by one and process them
	function list_encrypted_creditcards()
	{
		$query = "SELECT id, creditcard_number, encrypted_creditcard_number ".
			"FROM billing WHERE encrypted_creditcard_number IS NOT NULL";
		$result =  $this->db->query($query) or die ("list_encrypted_creditcards Query Failed");

		return $result->result_array();
	}


	// when re-keying credit cards use this function to insert the card as plaintext
	function input_decrypted_card($decrypted_creditcard_number, $id)
	{
		$query = "UPDATE billing SET creditcard_number = ? WHERE id = ?";
		$cardupdate = $this->db->query($query, array($decrypted_creditcard_number, $id))
			or die ("input_decrypted_card query failed");
	}


	// get list of card numbers to encrypt
	// use result to walk through results one by one and process them
	function list_creditcards()
	{		
		$query = "SELECT id, creditcard_number FROM billing ".
			"WHERE creditcard_number IS NOT NULL AND creditcard_number <> '0' ".
			"AND creditcard_number <> ''";
		$result = $this->db->query($query) or die ("list_creditcards Query Failed");

		return $result->result_array();;
	}


	// update billing with a new encrypted card from encryptcard command
	function input_encrypted_card($creditcard_number, $encrypted_creditcard_number, $id)
	{
		$query = "UPDATE billing SET creditcard_number = ?, ".
			"encrypted_creditcard_number = ? WHERE id = ?";

		$cardupdate = $this->db->query($query, array($creditcard_number, $encrypted_creditcard_number, $id))
			or die ("card update query failed");
	}


	// get the list of all pending creditcard refunds
	function pendingrefunds()
	{
		// query from billing_details ALL the refunds that are pending
		$query = "SELECT ROUND(SUM(bd.refund_amount),2) AS RefundTotal, 
			b.id b_id, b.name b_name, b.company b_company, 
			b.street b_street, b.city b_city, 
			b.state b_state, b.zip b_zip, 
			b.account_number b_acctnum, 
			b.from_date b_from_date, 
			b.to_date b_to_date, 
			b.payment_due_date b_payment_due_date,  
			bd.invoice_number bd_invoice_number, 
			bd.batch bd_batch, g.org_name   
				FROM billing_details bd
				LEFT JOIN billing b ON bd.billing_id = b.id 
				LEFT JOIN billing_types bt ON bt.id = b.billing_type 
				LEFT JOIN general g ON g.id = b.organization_id 
				WHERE bd.refunded <> 'y' AND bd.refund_amount > 0 
				AND bt.method = 'creditcard' 
				GROUP BY b.id";
		$result = $this->db->query($query) or die ("pendingrefunds query failed");
		return $result->result_array();
	}


	/*
	 * ---------------------------------------------------------------------------
	 *  get accounts with a payment history of today for enable/status updates
	 * ---------------------------------------------------------------------------
	 */
	function payment_history_today($today)
	{
		$query = "SELECT p.billing_id, b.id, b.account_number ".
			"FROM payment_history p ".
			"LEFT JOIN billing b ON p.billing_id = b.id ".
			"WHERE p.creation_date = '$today' ".
			"AND p.status = 'authorized'";
		$result = $this->db->query($query) or die ("queryfailed");

		return $result->result_array();
	}


	function todays_receipts()
	{
		// Select records that paid today and requested a receipt from the database
		$query = "SELECT b.id b_id, b.contact_email, b.name, b.company, ".
			"b.account_number, ph.id ph_id, ph.billing_amount, ph.creation_date, ".
			"ph.payment_type, ph.creditcard_number, ph.check_number ".
			"FROM billing b ".
			"LEFT JOIN customer c on c.account_number = b.account_number ".
			"LEFT JOIN payment_history ph ON ph.billing_id = b.id ".
			"WHERE b.automatic_receipt = 'y' AND ph.creation_date = CURRENT_DATE ".
			"AND ph.billing_amount > 0";

		$result = $this->db->query($query) or die ("queryfailed");

		return $result->result_array();
	}


	/*
	 * ------------------------------------------------------------------------
	 *  select customers who should get a reminder about their upcoming card run
	 * ------------------------------------------------------------------------
	 */
	function ccrunreminder()
	{
		$query = "SELECT b.id, b.contact_email, b.name, b.next_billing_date, bt.name bt_name, b.account_number ".
			"FROM billing b ".
			"LEFT JOIN customer c on c.account_number = b.account_number ".
			"LEFT JOIN billing_types bt ON bt.id = b.billing_type ".
			"WHERE ((b.billing_type = 40) OR (b.billing_type = 4) OR (b.billing_type = 6)) ".
			"AND b.next_billing_date = DATE_ADD(CURRENT_DATE, INTERVAL 21 DAY) ".
			"AND c.cancel_date IS NULL";

		$result = $this->db->query($query) or die ("queryfailed");

		return $result->result_array();
	}

}


/* End of file billing_model */
/* Location: ./application/models/billing_model.php */
