<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * Update model class includes functions used by statusupdate and weekendupdate
 * 
 * @author pyasi
 *
 */

class Update_Model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}	


	/*
	 * ------------------------------------------------------------------------
	 *  create entries for the ADD status
	 * ------------------------------------------------------------------------
	 */
	function add($handle, $today)
	{
		// get the list of new services added today
		$result = $this->service_model->new_services_today($today);

		$adds = 0;

		// loop through results and print out each
		foreach($result AS $myresult) 
		{
			$user_services_id = $myresult['u_id'];
			$service_description = $myresult['m_service_description'];
			$account_number = $myresult['u_ac'];
			$options_table = $myresult['m_options_table'];
			$activation_string = $myresult['m_activation_string'];
			$customer_name = $myresult['c_name'];
			$customer_company = $myresult['c_company'];
			$customer_street = $myresult['c_street'];
			$customer_city = $myresult['c_city'];
			$customer_state = $myresult['c_state'];
			$customer_country = $myresult['c_country'];
			$customer_zip = $myresult['c_zip'];
			$category = $myresult['m_category'];
			$removed = $myresult['u_rem'];

			// query this with the option_table for that service to get the 
			// activation_string variables
			$mystring = split(",", $activation_string);

			$newline = "\"ADD\",\"$category\",\"$customer_name\",\"$service_description\"";

			if ($options_table <> '') 
			{
				$myoptresult = $this->service_model->options_values($user_services_id, $optionstable);

				$fields = $this->schema_model->columns($this->db->database, $optionstable);

				$i = 0;        
				$pstring = "";	
				foreach($fields->result() as $v) 
				{                
					//echo "Name: $v->name ";
					$fieldname = $v->COLUMN_NAME;

					//check matching fieldname in the options table
					foreach($mystring as $s) 
					{
						if($fieldname == $s) 
						{
							//$pstring = $pstring.$s;
							$myline = $myoptresult[$s];
							$newline .= ",\"$myline\"";
						}	
					}

				} //endforeach
			} //endif

			$newline .= "\n"; // end the line

			// write the file if the service has not been removed
			if ($removed <> 'y') {
				fwrite($handle, $newline); // write to the file
				$adds++;
			}

		} //endwhile

	}



	/*
	 * ------------------------------------------------------------------------
	 *  set the ENABLE status
	 * ------------------------------------------------------------------------
	 */
	function enable($handle, $today)
	{
		/*-------------------------------------------------------------------*/
		// ENABLE
		//
		// if the account has an authorized status payment_history today and 
		// it's previous payment_history was bad: 
		// (turnedoff, canceled, cancelwfee, collections)
		// or if they are in waiting status today
		/*-------------------------------------------------------------------*/

		// select all the accounts with a payment_history of today
		$result = $this->billing_model->payment_history_today($today);

		$enables = 0;

		foreach ($result AS $myresult) 
		{
			// go through those accounts and find out which one has 
			//a previous payment_history that was declined, 
			//turnedoff, collections or canceled	

			$billingid = $myresult['billing_id'];	
			$account_number = $myresult['account_number'];

			$query = "SELECT * FROM payment_history ".
				"WHERE billing_id = ? ORDER BY id DESC LIMIT 1,1";
			$historyresult = $this->db->query($query, array($billingid)) or die ("select payment_history queryfailed");
			$myhistoryresult = $historyresult->row_array();
			$secondstatus = $myhistoryresult['status'];

			if ($secondstatus == "turnedoff" 
					OR $secondstatus == "waiting" 
					OR $secondstatus == "collections" 
					OR $secondstatus == "cancelwfee" 
					OR $secondstatus == "canceled") 
			{
				// enable services for the account

				$query = "SELECT u.id u_id, u.account_number u_ac, ".
					"u.master_service_id u_master_service_id, ".
					"u.billing_id u_bid, ".
					"u.start_datetime u_start, u.removed u_rem, ".
					"u.usage_multiple u_usage, ".
					"m.service_description m_service_description, ".
					"m.id m_id, m.pricerate m_pricerate, ".
					"m.frequency m_freq, ".
					"m.activation_string m_activation_string, ".
					"m.category m_category, m.activate_notify m_activate_notify, ".
					"m.options_table m_options_table, c.name c_name, ".
					"c.company c_company, c.street c_street, c.city c_city, ".
					"c.state c_state, c.country c_country, ".
					"c.zip c_zip, c.phone c_phone, ".
					"c.contact_email c_contact_email ".
					"FROM user_services u ".
					"LEFT JOIN master_services m ON m.id = u.master_service_id ".
					"LEFT JOIN customer c ON c.account_number = u.account_number ".
					"WHERE c.account_number = ?";
				$serviceresult = $this->db->query($query, array($account_number)) or die ("queryfailed");

				// loop through results and print out each
				foreach ($serviceresult->result_array() AS $myserviceresult) 
				{
					$user_services_id = $myserviceresult['u_id'];
					$service_description = $myserviceresult['m_service_description'];
					$account_number = $myserviceresult['u_ac'];
					$options_table = $myserviceresult['m_options_table'];
					$activation_string = $myserviceresult['m_activation_string'];
					$customer_name = $myserviceresult['c_name'];
					$customer_company = $myserviceresult['c_company'];
					$customer_street = $myserviceresult['c_street'];
					$customer_city = $myserviceresult['c_city'];
					$customer_state = $myserviceresult['c_state'];
					$customer_country = $myserviceresult['c_country'];
					$customer_zip = $myserviceresult['c_zip'];
					$category = $myserviceresult['m_category'];
					$removed = $myserviceresult['u_rem']; // y or n
					$activate_notify = $myserviceresult['m_activate_notify'];

					// query this with the option_table for 
					// that service to get the 
					// activation_string variables
					$mystring = split(",", $activation_string);

					$newline = "\"ENABLE\",\"$category\",\"$customer_name\",\"$service_description\"";

					if ($options_table <> '') 
					{
						$myoptresult = $this->service_model->options_values($user_services_id, $optionstable);

						$fields = $this->schema_model->columns($this->db->database, $optionstable);

						$i = 0;        
						$pstring = "";	
						foreach($fields->result() as $v) 
						{                
							//echo "Name: $v->name ";
							$fieldname = $v->COLUMN_NAME;

							//check matching fieldname in the options table
							foreach($mystring as $s) 
							{
								if($fieldname == $s) 
								{
									//$pstring = $pstring.$s;
									$myline = $myoptresult[$s];
									$newline .= ",\"$myline\"";
								}	
							}

						} //endforeach
					} //endif

					$newline .= "\n"; // end the line

					// write to the file if the service has not already been removed
					if ($removed <> 'y') 
					{
						fwrite($handle, $newline); // write to the file
						$enables++;

						// CREATE TICKET TO the activate_notify user if there is one
						if ($activate_notify) 
						{
							$notify = "$activate_notify";
							$description = "ENABLE $category $customer_name $service_description";
							$status = "not done";
							$this->support_model->create_ticket("update", $notify, $account_number, $status,
									$description, NULL, NULL, NULL, $user_services_id);
						}

					}
				} //endwhile
			} // endif
		} //endwhile

	}


	/*
	 * ------------------------------------------------------------------------
	 *  set the pastdue status for regular accounts (not carrier dependent)
	 * ------------------------------------------------------------------------
	 */
	function regular_past_due($handle, $today)
	{
		/*-------------------------------------------------------------------*/
		// REGULAR PAST DUE
		//
		// set the pastdue status for accounts that have a payment_due_date
		// more than g.regular_pastdue days ago (usually one day)
		// and do not have carrier_dependent services
		//
		/*-------------------------------------------------------------------*/
		$query = "SELECT bi.id, bi.account_number, bh.payment_due_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.regular_turnoff DAY) AS turnoff_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.regular_canceled DAY) AS cancel_date ".
			"FROM billing_details bd ".
			"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
			"LEFT JOIN general g ON bi.organization_id = g.id ".
			"WHERE bd.billed_amount > bd.paid_amount ".
			"AND bi.pastdue_exempt <> 'y' ".
			"AND bi.rerun_date IS NULL ".
			"AND ? >= DATE_ADD(bh.payment_due_date, INTERVAL g.regular_pastdue DAY) ".
			"AND ? < DATE_ADD(bh.payment_due_date, INTERVAL g.regular_turnoff DAY) ".
			"GROUP BY bi.id";
		$result = $this->db->query($query, array($today, $today)) or die ("regular past due queryfailed");

		foreach ($result->result_array() AS $myresult) 
		{
			// set these services to be turned off
			$billing_id = $myresult['id'];	
			$account_number = $myresult['account_number'];
			$payment_due_date = $myresult['payment_due_date'];
			$turnoff_date = $myresult['turnoff_date'];
			$cancel_date = $myresult['cancel_date'];

			$dependent = $this->service_model->carrier_dependent($account_number);

			if ($dependent == false) 
			{
				// check recent history to see if we already set them to pastdue
				$query = "SELECT status FROM payment_history ".
					"WHERE billing_id = ? ORDER BY id DESC LIMIT 1";
				$statusresult = $this->db->query($query, array($billing_id)) or die ("queryfailed");
				$mystatusresult = $statusresult->row_array();
				$mystatus = $mystatusresult['status'];

				if ($mystatus <> "pastdue"
						AND $mystatus <> "noticesent" 
						AND $mystatus <> "turnedoff"
						AND $mystatus <> "collections"
						AND $mystatus <> "canceled"
						AND $mystatus <> "cancelwfee"
						AND $mystatus <> "waiting") 
				{
					// set the account payment_history to pastdue
					$query = "INSERT INTO payment_history ".
						"(creation_date, billing_id, status) ".
						"VALUES (CURRENT_DATE,?,'pastdue')";
					$paymentresult = $this->db->query($query, array($billing_id)) 
						or die ("queryfailed");

					echo "regular pastdue: $account_number\n";


					// get the payment_due_date, turnoff_date, and cancel_date

					// SEND PASTDUE NOTICE BY EMAIL
					$config = array (
							'notice_type' => 'pastdue',
							'billing_id' => $billing_id,
							'method' => 'email',
							'payment_due_date' => $payment_due_date,
							'turnoff_date' => $turnoff_date,
							'cancel_date' => $cancel_date
							);
					$this->load->library('Notice', $config);

					$contactemail = $this->notice->contactemail;      
					$notify = "";
					$description = "Past Due Notice Sent $contactemail";
					$status = "automatic";

					// CREATE TICKET TO NOBODY
					$this->support_model->create_ticket("update", $notify, $account_number, $status,
							$description, $linkname, $linkurl);

				}

			}

		}
	}


	/*
	 * ------------------------------------------------------------------------
	 *  set the carrier dependent pastdue status
	 * ------------------------------------------------------------------------
	 */
	function carrier_dependent_past_due($handle, $today)
	{
		/*-------------------------------------------------------------------*/
		// CARRIER DEPENDENT PAST DUE
		//
		// set the pastdue status for accounts that have a payment_due_date
		// more than g.dependent_pastdue days ago (usually one day)
		// and do have carrier_dependent services
		//
		// insert a ticket to billing if they have carrier_dependent services
		//
		/*-------------------------------------------------------------------*/
		$query = "SELECT bi.id, bi.account_number, bh.payment_due_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_turnoff DAY) AS turnoff_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_canceled DAY) AS cancel_date ".
			"FROM billing_details bd ".
			"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
			"LEFT JOIN general g ON bi.organization_id = g.id ".
			"WHERE bd.billed_amount > bd.paid_amount ".
			"AND bi.pastdue_exempt <> 'y' ".
			"AND bi.rerun_date IS NULL ".
			"AND ? >= DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.dependent_pastdue DAY) ".
			"AND ? < DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.dependent_shutoff_notice DAY) GROUP BY bi.id";
		$result = $this->db->query($query, array($today, $today)) 
			or die ("carrier dependent past due queryfailed");

		foreach ($result->result_array() AS $myresult) 
		{
			// set these services to be turned off
			$billing_id = $myresult['id'];	
			$account_number = $myresult['account_number'];
			$payment_due_date = $myresult['payment_due_date'];
			$turnoff_date = $myresult['turnoff_date'];
			$cancel_date = $myresult['cancel_date'];

			$dependent = $this->service_model->carrier_dependent($account_number);

			if ($dependent == true) 
			{
				// check recent history to see if we already set them to pastdue
				$query = "SELECT status FROM payment_history ".
					"WHERE billing_id = ? ORDER BY id DESC LIMIT 1";
				$statusresult = $this->db->query($query, array($billing_id)) or die ("queryfailed");
				$mystatusresult = $statusresult->row_array();
				$mystatus = $mystatusresult['status'];

				if ($mystatus <> "pastdue"
						AND $mystatus <> "turnedoff"
						AND $mystatus <> "noticesent" 
						AND $mystatus <> "collections"
						AND $mystatus <> "canceled"
						AND $mystatus <> "cancelwfee"
						AND $mystatus <> "waiting") 
				{
					// set the account payment_history to pastdue
					$query = "INSERT INTO payment_history ".
						"(creation_date, billing_id, status) ".
						"VALUES (CURRENT_DATE,?,'pastdue')";
					$paymentresult = $this->db->query($query, array($billing_id)) 
						or die ("queryfailed");

					echo "carrier dependent pastdue: $account_number\n";

					// SEND PASTDUE NOTICE BY BOTH PRINT and EMAIL
					$config = array (
							'notice_type' => 'pastdue',
							'billing_id' => $billing_id,
							'method' => 'both',
							'payment_due_date' => $payment_due_date,
							'turnoff_date' => $turnoff_date,
							'cancel_date' => $cancel_date
							);
					$this->load->library('Notice', $config);

					$linkname = $this->notice->pdfname;
					$contactemail = $this->notice->contactemail;
					$linkurl = "index.php?load=tools/downloadfile&type=dl&filename=$linkname";
					$notify = "";
					$description = "Past Due Notice Sent $contactemail $url";
					$status = "not done";

					// CREATE TICKET TO default_billing_group
					$this->support_model->create_ticket("update", $notify, $account_number, $status,
							$description, $linkname, $linkurl);

				}

			}

		}
	}


	/*
	 * ------------------------------------------------------------------------
	 *  set the shutoff notice for carrier dependent services
	 * ------------------------------------------------------------------------
	 */
	function carrier_dependent_shutoff_notice($handle, $today)
	{
		/*-------------------------------------------------------------------*/
		// CARRIER DEPENDENT SHUTOFF NOTICE
		//
		// send a shutoff notice to carrier dependent services that are
		// about to be turned off in a few days
		/*-------------------------------------------------------------------*/
		$query = "SELECT bi.id, bi.account_number, bh.payment_due_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_turnoff DAY) AS turnoff_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_canceled DAY) AS cancel_date ".
			"FROM billing_details bd ".
			"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
			"LEFT JOIN general g ON bi.organization_id = g.id ".
			"WHERE bd.billed_amount > bd.paid_amount ".
			"AND bi.pastdue_exempt <> 'y' ".
			"AND bi.rerun_date IS NULL ".
			"AND ? >= DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.dependent_shutoff_notice DAY) ".
			"AND ? < DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.dependent_turnoff DAY) GROUP BY bi.id";
		$result = $this->db->query($query, array($today, $today)) 
			or die ("carrier dependent shutoff queryfailed");

		foreach ($result->result_array() AS $myresult) 
		{
			// set these services to be turned off
			$billing_id = $myresult['id'];	
			$account_number = $myresult['account_number'];
			$payment_due_date = $myresult['payment_due_date'];
			$turnoff_date = $myresult['turnoff_date'];
			$cancel_date = $myresult['cancel_date'];

			$dependent = $this->service_model->carrier_dependent($account_number);

			if ($dependent == true) 
			{
				// check recent history to see if we already set them to turned off
				$query = "SELECT status FROM payment_history ".
					"WHERE billing_id = $billing_id ORDER BY id DESC LIMIT 1";
				$statusresult = $this->db->query($query, array($billing_id)) 
					or die ("queryfailed");
				$mystatusresult = $statusresult->row_array();
				$mystatus = $mystatusresult['status'];

				if ($mystatus <> "turnedoff"
						AND $mystatus <> "noticesent" 
						AND $mystatus <> "collections"
						AND $mystatus <> "canceled"
						AND $mystatus <> "cancelwfee"
						AND $mystatus <> "waiting") 
				{
					// set the account payment_history to noticesent
					$query = "INSERT INTO payment_history ".
						"(creation_date, billing_id, status) ".
						"VALUES (CURRENT_DATE,?,'noticesent')";
					$paymentresult = $this->db->query($query, array($billing_id)) 
						or die ("queryfailed");

					// SEND SHUTOFF NOTICE BY BOTH PRINT and EMAIL
					$config = array (
							'notice_type' => 'shutoff',
							'billing_id' => $billing_id,
							'method' => 'both',
							'payment_due_date' => $payment_due_date,
							'turnoff_date' => $turnoff_date,
							'cancel_date' => $cancel_date
							);
					$this->load->library('Notice', $config);

					$linkname = $this->notice->pdfname;
					$contactemail = $this->notice->contactemail;
					$linkurl = "index.php?load=tools/downloadfile&type=dl&filename=$linkname";
					$notify = "$default_billing_group";
					$description = "Shutoff Notice Sent $contactemail $url";
					$status = "not done";

					// CREATE TICKET TO NOBODY
					$this->support_model->create_ticket("update", $notify, $account_number, $status,
							$description, $linkname, $linkurl);

				}

			}

		}

	}



	/*
	 * ------------------------------------------------------------------------
	 *  mark accounts to disable that are not carrier dependent 
	 * ------------------------------------------------------------------------
	 */
	function regular_disable($handle, $today)
	{
		/*-------------------------------------------------------------------*/
		// REGULAR DISABLE
		//
		// check if the account should be turned off if it's over
		// regular_turnoff days AND HAS NO CARRIER DEPENDENT SERVICES
		// disable the account
		/*-------------------------------------------------------------------*/

		$query = "SELECT bi.id, bi.account_number, bh.payment_due_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.regular_turnoff DAY) AS turnoff_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.regular_canceled DAY) AS cancel_date ".
			"FROM billing_details bd ".
			"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
			"LEFT JOIN general g ON bi.organization_id = g.id ".
			"WHERE bd.billed_amount > bd.paid_amount ".
			"AND bi.pastdue_exempt <> 'y' ".
			"AND bi.rerun_date IS NULL ".
			"AND ? >= DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.regular_turnoff DAY) ".
			"AND ? < DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.regular_canceled DAY) GROUP BY bi.id";
		$result = $this->db->query($query, array($today, $today)) or die ("regular disable queryfailed");

		foreach ($result->result_array() AS $myresult) 
		{
			// set these services to be turned off
			$billing_id = $myresult['id'];	
			$account_number = $myresult['account_number'];
			$payment_due_date = $myresult['payment_due_date'];
			$turnoff_date = $myresult['turnoff_date'];
			$cancel_date = $myresult['cancel_date'];

			$dependent = $this->service_model->carrier_dependent($account_number);

			if ($dependent == false) 
			{
				// check recent history to see if we already set them to turned off
				$query = "SELECT status FROM payment_history ".
					"WHERE billing_id = ? ORDER BY id DESC LIMIT 1";
				$statusresult = $this->db->query($query, array($billing_id)) or die ("queryfailed");
				$mystatusresult = $statusresult->row_array();
				$mystatus = $mystatusresult['status'];

				if ($mystatus <> "turnedoff"
						AND $mystatus <> "collections"
						AND $mystatus <> "canceled"
						AND $mystatus <> "cancelwfee"
						AND $mystatus <> "waiting") 
				{
					// set the account payment_history to turnedoff
					$query = "INSERT INTO payment_history ".
						"(creation_date, billing_id, status) ".
						"VALUES (CURRENT_DATE,?,'turnedoff')";
					$paymentresult = $this->db->query($query, array($billing_id)) or die ("queryfailed");

				}

			}

		}
	}


	/*
	 * ------------------------------------------------------------------------
	 *   mark accounts to disable that are carrier dependent
	 * ------------------------------------------------------------------------
	 */
	function carrier_dependent_disable($handle, $today)
	{
		/*-------------------------------------------------------------------*/
		// CARRIER DEPENDENT DISABLE
		//
		// check if the account should be turned off if it's over
		// dependent_turnoff days AND DOES HAVE CARRIER DEPENDENT SERVICES
		// disable the account
		/*-------------------------------------------------------------------*/
		$query = "SELECT bi.id, bi.account_number, bh.payment_due_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_turnoff DAY) AS turnoff_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_canceled DAY) AS cancel_date ".
			"FROM billing_details bd ".
			"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
			"LEFT JOIN general g ON bi.organization_id = g.id ".
			"WHERE bd.billed_amount > bd.paid_amount ".
			"AND bi.pastdue_exempt <> 'y' ".
			"AND bi.rerun_date IS NULL ".
			"AND ? >= DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.dependent_turnoff DAY) ".
			"AND ? < DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.dependent_canceled DAY) GROUP BY bi.id";
		$result = $this->db->query($query, array($today, $today)) or die ("carrier dependent disable queryfailed");

		foreach ($result->result_array() AS $myresult) 
		{
			// set these services to be turned off
			$billing_id = $myresult['id'];	
			$account_number = $myresult['account_number'];
			$payment_due_date = $myresult['payment_due_date'];
			$turnoff_date = $myresult['turnoff_date'];
			$cancel_date = $myresult['cancel_date'];

			$dependent = $this->service_model->carrier_dependent($account_number);

			if ($dependent == true) 
			{
				// check recent history to see if we already set them to turned off
				$query = "SELECT status FROM payment_history ".
					"WHERE billing_id = ? ORDER BY id DESC LIMIT 1";
				$statusresult = $this->db->query($query, array($billing_id)) or die ("queryfailed");
				$mystatusresult = $statusresult->row_array();
				$mystatus = $mystatusresult['status'];

				if ($mystatus <> "turnedoff" AND $mystatus <> "collections" AND $mystatus <> "canceled"
						AND $mystatus <> "cancelwfee" AND $mystatus <> "waiting") 
				{
					// set the account payment_history to turnedoff
					$query = "INSERT INTO payment_history ".
						"(creation_date, billing_id, status) ".
						"VALUES (CURRENT_DATE,?,'turnedoff')";
					$paymentresult = $this->db->query($query, array($billing_id)) or die ("queryfailed");

					// SEND CANCEL NOTICE BY BOTH PRINT and EMAIL
					$config = array (
							'notice_type' => 'cancel',
							'billing_id' => $billing_id,
							'method' => 'both',
							'payment_due_date' => $payment_due_date,
							'turnoff_date' => $turnoff_date,
							'cancel_date' => $cancel_date
							);
					$this->load->library('Notice', $config);

					$linkname = $this->notice->pdfname;
					$contactemail = $this->notice->contactemail;
					$linkurl = "index.php?load=tools/downloadfile&type=dl&filename=$linkname";
					$notify = "$default_billing_group";
					$description = "Turnoff and Sent Cancellation Notice to $contactemail";
					$status = "not done";

					// CREATE TICKET TO default_billing_group about turn off
					$this->support_model->create_ticket("update", $notify, $account_number, $status,
							$description, $linkname, $linkurl);


				}

			}

		}

	}



	/*
	 * -------------------------------------------------------------------------
	 *  DISABLE ACCOUNTS MARKED FROM ABOVE
	 * -------------------------------------------------------------------------
	 */
	function disable_accounts($handle, $today)
	{
		// disable any services with a turnedoff payment_history from today that
		// have not already been marked as removed
		$query = "SELECT u.id u_id, u.account_number u_ac, ".
			"u.master_service_id u_master_service_id, u.billing_id u_bid, ".
			"u.start_datetime u_start, u.removed u_rem, u.usage_multiple ".
			"u_usage, m.service_description m_service_description, ".
			"m.id m_id, m.pricerate m_pricerate, m.frequency m_freq, ".
			"m.activation_string m_activation_string, m.category m_category, ".
			"m.options_table m_options_table, c.name c_name, c.company c_company, ".
			"c.street c_street, c.city c_city, c.state c_state, c.country c_country, ".
			"c.zip c_zip, c.phone c_phone, c.contact_email c_contact_email ".
			"FROM user_services u ".
			"LEFT JOIN master_services m ON m.id = u.master_service_id ".
			"LEFT JOIN customer c ON c.account_number = u.account_number ".
			"LEFT JOIN payment_history p ON p.billing_id = u.billing_id ".
			"WHERE (to_days(now()) = to_days(p.creation_date)) ".
			"AND (p.status = 'turnedoff') AND u.removed <> 'y'";
		$result = $this->db->query($query) or die ("queryfailed");

		$disables = 0;

		// loop through results and print out each
		foreach ($result->result_array() AS $myresult) 
		{
			$user_services_id = $myresult['u_id'];
			$master_service_id = $myresult['u_master_service_id'];
			$service_description = $myresult['m_service_description'];
			$account_number = $myresult['u_ac'];
			$options_table = $myresult['m_options_table'];
			$activation_string = $myresult['m_activation_string'];
			$customer_name = $myresult['c_name'];
			$customer_company = $myresult['c_company'];
			$customer_street = $myresult['c_street'];
			$customer_city = $myresult['c_city'];
			$customer_state = $myresult['c_state'];
			$customer_country = $myresult['c_country'];
			$customer_zip = $myresult['c_zip'];
			$category = $myresult['m_category'];

			// query this with the option_table for that service to get the 
			// activation_string variables
			$mystring = split(",", $activation_string);

			$newline = "\"DISABLE\",\"$category\",\"$customer_name\",\"$service_description\"";

			if ($options_table <> '') 
			{
				$myoptresult = $this->service_model->options_values($user_services_id, $optionstable);

				$fields = $this->schema_model->columns($this->db->database, $optionstable);

				$i = 0;        
				$pstring = "";	
				foreach($fields->result() as $v) 
				{                
					//echo "Name: $v->name ";
					$fieldname = $v->COLUMN_NAME;

					//check matching fieldname in the options table
					foreach($mystring as $s) 
					{
						if($fieldname == $s) 
						{
							//$pstring = $pstring.$s;
							$myline = $myoptresult[$s];
							$newline .= ",\"$myline\"";
						}	
					}

				} //endforeach
			} //endif
			$newline .= "\n"; // end the line
			fwrite($handle, $newline); // write to the file

			// send service_message about turnoff
			$service_notify_type = "turnoff";
			$this->service_model->service_message($service_notify_type, $account_number,
					$master_service_id, $user_services_id, NULL, NULL);

			$disables++;
		} //endwhile

		return $disables;
	}


	/*
	 * ------------------------------------------------------------------------
	 *  mark services over canceled days late for delete
	 * ------------------------------------------------------------------------
	 */
	function regular_delete($handle, $today)
	{
		/*-------------------------------------------------------------------*/
		// REGULAR DELETE
		/*-------------------------------------------------------------------*/
		// check if any services on the account are regular_canceled days late 
		// and also DO NOT HAVE CARRIER DEPENDENT SERVICES
		// if so, change the payment history status to cancelwfee
		// and set the Removal date to today

		$query = "SELECT DISTINCT bi.id, bi.account_number ".
			"FROM billing_details bd ".
			"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
			"LEFT JOIN general g ON bi.organization_id = g.id ".
			"WHERE bd.billed_amount > bd.paid_amount ".
			"AND bi.pastdue_exempt <> 'y' ".
			"AND bi.rerun_date IS NULL ".  
			"AND ? >= DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.regular_canceled DAY)";
		$result = $this->db->query($query, array($today)) or die ("queryfailed");

		foreach ($result->result_array() AS $myresult) 
		{
			// get the result values
			$billing_id = $myresult['id'];
			$account_number = $myresult['account_number'];

			$dependent = $this->service_model->carrier_dependent($account_number);

			if ($dependent == false) 
			{
				// check recent history to see if we already set them to be canceled or waiting
				$query = "SELECT status FROM payment_history ".
					"WHERE billing_id = ? ORDER BY id DESC LIMIT 1";
				$statusresult = $this->db->query($query, array($billing_id)) or die ("queryfailed");
				$mystatusresult = $statusresult->row_array();
				$mystatus = $mystatusresult['status'];

				if ($mystatus <> "collections"
						AND $mystatus <> "canceled"
						AND $mystatus <> "cancelwfee"
						AND $mystatus <> "waiting") 
				{

					// initialize the removed services boolean
					$removed_services = false;

					$query = "SELECT * FROM user_services us ".
						"WHERE account_number = ? AND removed <> 'y'";
					$removedresult = $this->db->query($query, array($account_number)) or die ("queryfailed");

					foreach ($removedresult->result_array() AS $myserviceresult) 
					{
						$userserviceid = $myserviceresult['id'];

						$this->service_model->delete_service($userserviceid,'removed', $today);

						// set this to true since services were removed
						$removed_services = true;
					}

					if ($removed_services) 
					{
						// set cancel date and removal date of customer record
						$query = "UPDATE customer ".
							"SET cancel_date = CURRENT_DATE, ".
							"removal_date = CURRENT_DATE ".
							"WHERE account_number = ?";
						$cancelresult = $this->db->query($query, array($account_number)) 
							or die ("queryfailed");

						// set the payment_history status to canceled      
						$query = "INSERT INTO payment_history ".
							"(creation_date, billing_id, status) ".
							"VALUES (CURRENT_DATE,?,'canceled')";
						$paymentresult = $this->db->query($query, array($billing_id)) or die ("queryfailed");

					}
				}    
			}  
		}
	}



	/*
	 * --------------------------------------------------------------------
	 *  mark carrier dependent services over canceled days late for delete
	 * --------------------------------------------------------------------
	 */
	function carrier_dependent_delete($handle, $today)
	{
		/*-------------------------------------------------------------------*/
		// CARRIER DEPENDENT DELETE
		/*-------------------------------------------------------------------*/
		// check if any services on the account are dependent_canceled days late 
		// and also DO HAVE CARRIER DEPENDENT SERVICES
		// if so, change the payment history status to cancelwfee
		// and set the Removal date to today

		$query = "SELECT DISTINCT bi.id, bi.account_number ".
			"FROM billing_details bd ".
			"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
			"LEFT JOIN general g ON bi.organization_id = g.id ".
			"WHERE bd.billed_amount > bd.paid_amount ".
			"AND bi.pastdue_exempt <> 'y' ".
			"AND bi.rerun_date IS NULL ".  
			"AND ? >= DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.dependent_canceled DAY)";
		$result = $this->db->query($query, array($today)) or die ("queryfailed");

		foreach ($result->result_array() AS $myresult) 
		{
			// get the result values
			$billing_id = $myresult['id'];
			$account_number = $myresult['account_number'];

			$dependent = $this->service_model->carrier_dependent($account_number);

			if ($dependent == true) 
			{
				// check recent history to see if we already set them to be canceled or waiting
				$query = "SELECT status FROM payment_history ".
					"WHERE billing_id = ? ORDER BY id DESC LIMIT 1";
				$statusresult = $this->db->query($query, array($billing_id)) or die ("queryfailed");
				$mystatusresult = $statusresult->row_array();
				$mystatus = $mystatusresult['status'];

				if ($mystatus <> "collections"
						AND $mystatus <> "canceled"
						AND $mystatus <> "cancelwfee"
						AND $mystatus <> "waiting") 
				{
					// initialize the removed services boolean
					$removed_services = false;

					$query = "SELECT * FROM user_services us ".
						"WHERE account_number = $account_number AND removed <> 'y'";
					$removedresult = $this->db->query($query, array($account_number)) 
						or die ("queryfailed");

					foreach ($removedresult->result_array() AS $myserviceresult) 
					{
						$userserviceid = $myserviceresult['id'];

						$this->service_model->delete_service($userserviceid,'removed', $today);

						// set this to true since services were removed
						$removed_services = true;
					}

					if ($removed_services) 
					{
						// set cancel date and removal date of customer record
						$query = "UPDATE customer ".
							"SET cancel_date = CURRENT_DATE, ".
							"removal_date = CURRENT_DATE ".
							"WHERE account_number = ?";
						$cancelresult = $this->db->query($query, array($account_number)) or die ("queryfailed");

						// set the payment_history status to cancelwfee 
						$query = "INSERT INTO payment_history ".
							"(creation_date, billing_id, status) ".
							"VALUES (CURRENT_DATE,?,'cancelwfee')";
						$paymentresult = $this->db->query($query, array($billing_id)) or die ("queryfailed");

						// send a collections notice by both print and email
						$config = array (
								'notice_type' => 'collections',
								'billing_id' => $billing_id,
								'method' => 'both',
								'payment_due_date' => $payment_due_date,
								'turnoff_date' => $turnoff_date,
								'cancel_date' => $cancel_date
								);
						$this->load->library('Notice', $config);

						$linkname = $this->notice->pdfname;
						$contactemail = $this->notice->contactemail;
						$linkurl = "index.php?load=tools/downloadfile&type=dl&filename=$linkname";
						$notify = "$default_billing_group";
						$description = "Cancel w/Fee Collections Notice Sent $contactemail $url";
						$status = "not done";

						// CREATE TICKET TO default_billing_group about cancelwfee
						$this->support_model->create_ticket("update", $notify, $account_number, $status,
								$description, $linkname, $linkurl);

					}
				}    
			}

		}
	}


	/*
	 * ------------------------------------------------------------------------
	 *  DELETE ACCOUNTS MARKED FROM ABOVE
	 * ------------------------------------------------------------------------
	 */
	function delete_accounts($handle, $today)
	{
		// delete any services if their removal_date date is today
		$query = "SELECT u.id u_id, u.account_number u_ac, ".
			"u.master_service_id u_master_service_id, u.billing_id u_bid, ".
			"u.start_datetime u_start, u.removed u_rem, u.usage_multiple ".
			"u_usage, m.service_description m_service_description, ".
			"m.id m_id, m.pricerate m_pricerate, m.frequency m_freq, ".
			"m.activation_string m_activation_string, m.category m_category, ".
			"m.options_table m_options_table, c.name c_name, c.company c_company, ".
			"c.street c_street, c.city c_city, c.state c_state, c.country c_country, ".
			"c.zip c_zip, c.phone c_phone, c.contact_email c_contact_email ".
			"FROM user_services u ".
			"LEFT JOIN master_services m ON m.id = u.master_service_id ".
			"LEFT JOIN customer c ON c.account_number = u.account_number ".
			"WHERE to_days(?) = to_days(u.removal_date)";
		$result = $this->db->query($query, array($today)) or die ("queryfailed");

		$deletes = 0;

		// loop through results and print out each
		foreach ($result->result_array() AS $myresult) 
		{
			$user_services_id = $myresult['u_id'];
			$service_description = $myresult['m_service_description'];
			$account_number = $myresult['u_ac'];
			$options_table = $myresult['m_options_table'];
			$activation_string = $myresult['m_activation_string'];
			$customer_name = $myresult['c_name'];
			$customer_company = $myresult['c_company'];
			$customer_street = $myresult['c_street'];
			$customer_city = $myresult['c_city'];
			$customer_state = $myresult['c_state'];
			$customer_country = $myresult['c_country'];
			$customer_zip = $myresult['c_zip'];
			$category = $myresult['m_category'];

			// query this with the option_table for that service to get the 
			// activation_string variables
			$mystring = split(",", $activation_string);

			$newline = "\"DELETE\",\"$category\",\"$customer_name\",\"$service_description\"";

			if ($options_table <> '') 
			{
				$myoptresult = $this->service_model->options_values($user_services_id, $options_table);

				$fields = $this->schema_model->columns($this->db->database, $options_table);

				$i = 0;        
				$pstring = "";	
				foreach($fields->result() as $v) 
				{                
					//echo "Name: $v->name ";
					$fieldname = $v->COLUMN_NAME;

					//check matching fieldname in the options table
					foreach($mystring as $s) 
					{
						if($fieldname == $s) 
						{
							//$pstring = $pstring.$s;
							$myline = $myoptresult[$s];
							$newline .= ",\"$myline\"";
						}	
					}

				} //endforeach
			} //endif
			$newline .= "\n"; // end the line
			fwrite($handle, $newline); // write to the file
			$deletes++;
		} //endwhile

		return $deletes;
	}

}
