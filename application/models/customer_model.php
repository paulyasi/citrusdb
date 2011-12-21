<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * Customer model access to customer table data
 * 
 * @author pyasi
 *
 */

class Customer_Model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function title($account_number)
	{
		// get the customer name, and company
		$query = "SELECT name,company FROM customer ".
			"WHERE account_number = ?";
		$result = $this->db->query($query, array($account_number))
			or die ("customer title queryfailed");
		
		$myresult = $result->row();
		$data['account_number'] = $this->account_number;
		$data['acct_name'] = $myresult->name;
		$data['acct_company'] = $myresult->company;

		return $data;
	}

	function record($account_number)
	{
		// get the all customer record information
		$query = "SELECT * FROM customer WHERE account_number = ?";
		$result = $this->db->query($query, array($account_number))
			or die ("customer record queryfailed");
		$myresult = $result->row();	

		// get the cancel reason text
		if ($myresult->cancel_reason > 0)
		{
			$query = "SELECT reason FROM cancel_reason ".
				"WHERE id = $myresult->cancel_reason";
			$cancelreasonresult = $this->db->query($query) or die ("query failed");
			$mycancelreasonresult = $cancelreasonresult->row();
			if ($cancelreasonresult->num_rows() > 0)
			{
				$cancel_reason = $mycancelreasonresult->reason;
			}
			else
			{
				$cancel_reason = NULL;
			}
		} 
		else 
		{
			$cancel_reason = "";
		}

		// Put values into an array to return

		return array (
				'signup_date' => $myresult->signup_date,
				'name' => $myresult->name,
				'company' => $myresult->company,
				'street' => $myresult->street,
				'city' => $myresult->city,
				'state' => $myresult->state,
				'zip' => $myresult->zip,
				'country' => $myresult->country,
				'phone' => $myresult->phone,
				'alt_phone' => $myresult->alt_phone,
				'fax' => $myresult->fax,
				'source' => $myresult->source,
				'contactemail' => $myresult->contact_email,
				'secret_question' => $myresult->secret_question,
				'secret_answer' => $myresult->secret_answer,
				'default_billing_id' => $myresult->default_billing_id,
				'cancel_date' => $myresult->cancel_date,
				'account_manager_password' => $myresult->account_manager_password,
				'cancel_reason_id' => $myresult->cancel_reason,
				'notes' => $myresult->notes,
				'cancel_reason' => $cancel_reason);	
	}


	/*
	 * -------------------------------------------------------------------------
	 * if the cancel date is empty, then put NULL in the cancel and removal date
	 * -------------------------------------------------------------------------
	 */
	public function save_record($account_number, $customer_data)
	{

		if ($customer_data['cancel_date'] == "")
		{               
			$customer_data['cancel_date'] = NULL;
			$customer_data['cancel_reason'] = NULL;
			$this->db->where('account_number', $account_number);
			$this->db->update('customer', $customer_data); 

		} else {
			// there is a cancel date, so put everything in there
			$this->db->where('account_number', $account_number);
			$this->db->update('customer', $customer_data); 
		}

	}


	/*
	 * -------------------------------------------------------------------------
	 *  customer_data in an assoc array with these values	 
	 * -------------------------------------------------------------------------
	 */
	public function create_record($customer_data)
	{
		$name = $customer_data['name'];
		$company = $customer_data['company'];
		$street = $customer_data['street'];
		$city = $customer_data['city'];
		$state = $customer_data['state'];
		$country = $customer_data['country'];
		$zip = $customer_data['zip'];
		$phone = $customer_data['phone'];
		$alt_phone = $customer_data['alt_phone'];
		$fax = $customer_data['fax'];
		$contact_email = $customer_data['contact_email'];
		$secret_question = $customer_data['secret_question'];
		$secret_answer = $customer_data['secret_answer'];
		$source = $customer_data['source'];
		$organization_id = $customer_data['organization_id'];
		$account_manager_password = $customer_data['account_manager_password'];

		// insert a new customer record
		$query = "INSERT into customer (signup_date, name, company, street, city, ".
			"state, country, zip, phone, alt_phone, fax, contact_email, ".
			"secret_question, secret_answer, source) ".
			"VALUES (CURRENT_DATE, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$result = $this->db->query($query, array($name,
												 $company,
												 $street,
												 $city,
												 $state,
												 $country,
												 $zip,
												 $phone,
												 $alt_phone,
												 $fax,
												 $contact_email,
												 $secret_question,
												 $secret_answer,
												 $source))
			or die ("create_record query failed");

		$myinsertid = $this->db->insert_id();  

		// set the active session account number to the one just created
		$account_number=$myinsertid;

		// start the session variables to hold the account number
		$this->session->set_userdata('account_number', $account_number);

		// get the next billing date value
		$mydate = $this->billing_model->get_nextbillingdate();

		// make a new billing record
		// set the next billing date and from date to the date determined from above 
		$query = "INSERT into billing (account_number,next_billing_date,from_date,
			payment_due_date,name,company,street,city,state,country,zip,phone,fax,
			contact_email,organization_id) 
				VALUES (?,?,?,?,?,?,
						?,?,?,?,?,?,?,
					    ?,?)";
		$result = $this->db->query($query, array($account_number, $mydate, $mydate, $mydate, $name, $company,
												 $street, $city, $state, $country, $zip, $phone, $fax,
												 $contact_email, $organization_id))
			or die ("customer_record billing insert query failed");	

		// set the default billing ID for the customer record
		$billingid = $this->db->insert_id();
		$query = "UPDATE customer SET default_billing_id = ? ".
			"WHERE account_number = ?";
		$result = $this->db->query($query, account($billingid, $account_number))
			or die ("update customer default_billing_id failed");

		// return the account number and default billing id of the new customer
		$data['account_number'] = $account_number;
		$data['billingid'] = $billingid;
		$data['from_date'] = $mydate;
		return $data;
	}


	public function select_cancel_reasons()
	{
		$query = "SELECT * FROM cancel_reason";
		$cancelreasonresult = $this->db->query($query) or die ("select_cancel_reasons failed");

		return $cancelreasonresult->result_array();
	}

	
	public function update_billingaddress($account_number)
	{		
		// update their billing address after we prompt them if they want to
		// get the customer information
		$query = "SELECT * FROM customer WHERE account_number = ?";
		$result = $this->db->query($query, array($account_number))
			or die ("select customer update billingaddress failed");
		$myresult = $result->row_array();

		$street = $myresult['street'];
		$city = $myresult['city'];
		$state = $myresult['state'];
		$zip = $myresult['zip'];
		$country = $myresult['country'];
		$phone = $myresult['phone'];
		$fax = $myresult['fax'];
		$contact_email = $myresult['contact_email'];
		$default_billing_id = $myresult['default_billing_id'];  

		// save billing address
		$query = "UPDATE billing ".
			"SET street = ?, ".
			"city = ?, ".
			"state = ?, ".
			"zip = ?, ".
			"country = ?, ".
			"phone = ?, ".
			"fax = ?, ".
			"contact_email = ? WHERE id = ?";
		$result = $this->db->query($query, array($street,
												 $city,
												 $state,
												 $zip,
												 $country,
												 $phone,
												 $fax,
												 $contact_email,
												 $default_billing_id))
			or die ("update_billingaddress failed");

	}

	
	public function is_not_canceled($account_number)  
	{
		// hide the Add Service function if the customer is canceled
		$query = "SELECT cancel_date FROM customer ".
			"WHERE account_number = ? AND cancel_date is NULL";
		$result = $this->db->query($query, array($account_number))
			or die ("is_not_canceled failed");
		$notcanceled = $result->num_rows();
		if ($notcanceled == 1) 
		{ 
			return TRUE; 
		} 
		else 
		{ 
			return FALSE; 
		}
	}


	public function get_anniversary_removal_date($account_number)
	{
		// figure out the signup anniversary removal date
		$query = "SELECT signup_date FROM customer 
			WHERE account_number = ?";
		$result = $this->db->query($query, array($account_number))
			or die ("get_anniversary_removal_date failed");
		$myresult = $result->row_array();	
		$signup_date = $myresult['signup_date'];
		list($myyear, $mymonth, $myday) = preg_split("/-/", $signup_date);
		$removal_date  = date("Y-m-d", 
				mktime(0, 0, 0, date("m")  , date("$myday"), date("Y")));
		$today  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));

		if ($removal_date <= $today) 
		{
			$removal_date  = date("Y-m-d", 
					mktime(0, 0, 0, date("m")+1  , date("$myday"), date("Y")));
		}

		return $removal_date;
	}


	/*
	 * ------------------------------------------------------------------------
	 *  check the customer if_field used to specify what field to test for
	 *  certain things like taxes applied if customer in state = massachusetts etc.
	 * ------------------------------------------------------------------------
	 */
	public function check_if_field($if_field, $account_number)
	{
		$ifquery = "SELECT $if_field FROM customer ".
			"WHERE account_number = ?";
		$ifresult = $this->db->query($ifquery, array($account_number))
			or die ("check_if_field failed");	
		$myifresult = $ifresult->row_array();
		return $myifresult[$if_field];
	}

	
	/*
	 * ------------------------------------------------------------------------
	 *  change the account manager password by a customer service rep
	 * ------------------------------------------------------------------------
	 */ 
	function change_account_manager_password($account_number, 
			$new_password1, $new_password2)
	{
		// load the PasswordHash library
		$config = array (
				'iteration_count_log2' => '8', 
				'portable_hashes' => 'FALSE'
				);
		$this->load->library('PasswordHash', $config);    

		if ($new_password1 == $new_password2) 
		{
			// hash the new password
			$newhash = $this->passwordhash->HashPassword($new_password1);
			// hash always greater than 20 chars, if not something went wrong
			if (strlen($newhash) < 20) 
			{
				// failed to hash new password
				return FALSE;
			} 
			else 
			{
				// set the new password value
				$query = "UPDATE customer SET ".
					"account_manager_password = ? ".
					"WHERE account_number = ?";
				$result = $this->db->query($query, array($newhash, $account_number))
					or die ("new password update failed");
				return TRUE;
			}
		} 
		else 
		{
			// passwords do not match
			return FALSE;
		}
	}


	/*
	 * ------------------------------------------------------------------------
	 *  make customer canceled on record and in histories
	 * ------------------------------------------------------------------------
	 */
	function cancel_customer($cancel_reason, $account_number)
	{
		$query = "UPDATE customer ".
			"SET cancel_date = CURRENT_DATE, ". 
			"cancel_reason = ? ".
			"WHERE account_number = ?";
		$result = $this->db->query($query, array($cancel_reason, $account_number))
			or die ("cancel_customer update customer query failed");
			
		// set next_billing_date to NULL since it normally won't be billed again
		$query = "UPDATE billing ".
			"SET next_billing_date = NULL ". 
			"WHERE account_number = ?";
		$result = $this->db->query($query, array($account_number))
			or die ("cancel_customer update billing query failed");   
		
		// get the text of the cancel reason to use in the note
		$query = "SELECT * FROM cancel_reason " . 
			"WHERE id = ?";
		$result = $this->db->query($query, array($cancel_reason))
			or die ("cancel_customer select reason query failed");
		$myresult = $result->row_array();
		$cancel_reason_text = $myresult['reason'];

		// add cancel ticket to customer_history
		// if they are carrier dependent, send a note to
		// the billing_noti
		$desc = lang('canceled') . ": $cancel_reason_text";
		
		// leave ticket and return the ticket number to customer service user
		$cancelticket = $this->support_model->create_ticket($this->user, '', 
				$account_number, 'automatic', $desc);

		// get the billing_id for the customer's payment_history
		$default_billing_id = $this->billing_model->default_billing_id($account_number);

		// add a canceled entry to the payment_history
		$query = "INSERT INTO payment_history ".
			"(creation_date, billing_id, status) ".
			"VALUES (CURRENT_DATE, ?,'canceled')";
		$paymentresult = $this->db->query($query, array($default_billing_id))
			or die ("cancel_customer payment insert queryfailed");

		return $cancelticket;
	}


	function undelete_customer($account_number)
	{
		// undelete the customer record
		$query = "UPDATE customer ".
			"SET cancel_date = NULL, ".
			"cancel_reason = NULL ".
			"WHERE account_number = ?";
		$result = $this->db->query($query, array($account_number))
			or die ("undelete_customer update customer query failed");

		// update the default billing records with new billing dates
		$mydate = $this->billing_model->get_nextbillingdate();

		$query = "UPDATE billing ".
			"SET next_billing_date = ?, ".
			"from_date = ?, ".
			"payment_due_date = ? ".
			"WHERE account_number = ?";
		$result = $this->db->query($query, array($mydate, $mydate, $mydate, $account_number))
			or die ("undelete_customer update billing failed");  

		// get the default billing id and billing type for automati_to_date
		$query = "SELECT c.default_billing_id,b.billing_type,b.from_date ".
			"FROM customer c ".
			"LEFT JOIN billing b ON b.id = c.default_billing_id ".
			"WHERE c.account_number = ?";
		$result = $this->db->query($query, array($account_number))
			or die ("undelete_customer select billing failed");
		$myresult = $result->row_array();	
		$billing_id = $myresult['default_billing_id'];
		$billing_type = $myresult['billing_type'];
		$from_date = $myresult['from_date'];

		// set the to_date automatically
		$this->billing_model->automatic_to_date($from_date, $billing_type, $billing_id);

	}


}

/* end file models/customer_model.php */
