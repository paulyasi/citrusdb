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
			"WHERE account_number = $this->account_number";
		$result = $this->db->query($query) or die ("$l_queryfailed");
		$myresult = $result->row();
		$data['account_number'] = $this->account_number;
		$data['acct_name'] = $myresult->name;
		$data['acct_company'] = $myresult->company;

		return $data;
	}

	function record($account_number)
	{
		// get the all customer record information
		$query = "SELECT * FROM customer WHERE account_number = $account_number";
		$result = $this->db->query($query) or die ("customer info $l_queryfailed");
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

	public function save_record($account_number, $customer_data)
	{

		// build update query, try with ActiveRecord from CI
		// if the cancel date is empty, then put NULL in the cancel and removal date
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

	public function create_record($customer_data)
	{
		// customer_data in an assoc array with these values
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
		$query = "INSERT into customer (signup_date, name, company, street, city, 
			state, country, zip, phone, alt_phone, fax, contact_email, secret_question, 
			secret_answer, source) 
				VALUES (CURRENT_DATE, '$name', '$company', '$street', '$city', '$state', 
						'$country', '$zip', '$phone', '$alt_phone','$fax', '$contact_email', 
						'$secret_question','$secret_answer','$source')";
		$result = $this->db->query($query) or die ("query failed");

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
				VALUES ('$account_number','$mydate','$mydate','$mydate','$name','$company',
						'$street','$city','$state','$country','$zip','$phone','$fax',
						'$contact_email','$organization_id')";
		$result = $this->db->query($query) or die ("query failed");	

		// set the default billing ID for the customer record
		$billingid = $this->db->insert_id();
		$query = "UPDATE customer SET default_billing_id = '$billingid' ".
			"WHERE account_number = $account_number";
		$result = $this->db->query($query) or die ("query failed");

		// return the account number and default billing id of the new customer
		$data['account_number'] = $account_number;
		$data['billingid'] = $billingid;
		$data['from_date'] = $mydate;
		return $data;
	}


	public function select_cancel_reasons()
	{
		$query = "SELECT * FROM cancel_reason";
		$cancelreasonresult = $this->db->query($query) or die ("$l_queryfailed");

		return $cancelreasonresult->result_array();
	}

	public function update_billingaddress()
	{		
		// update their billing address after we prompt them if they want to
		// get the customer information
		$query = "SELECT * FROM customer WHERE account_number = $this->account_number";
		$result = $this->db->query($query) or die ("query failed");
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
			"SET street = '$street', ".
			"city = '$city', ".
			"state = '$state', ".
			"zip = '$zip', ".
			"country = '$country', ".
			"phone = '$phone', ".
			"fax = '$fax', ".
			"contact_email = '$contact_email' WHERE id = $default_billing_id";
		$result = $this->db->query($query) or die ("query failed");

	}

	public function cancel_reason($cancel_reason_id)
	{

		return $cancel_reason;
	}

	public function is_not_canceled($account_number)  
	{
		// hide the Add Service function if the customer is canceled
		$query = "SELECT cancel_date FROM customer ".
			"WHERE account_number = $account_number AND cancel_date is NULL";
		$result = $this->db->query($query) or die ("$l_queryfailed");
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
			WHERE account_number = '$account_number'";
		$result = $this->db->query($query) or die ("$l_queryfailed");
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
			"WHERE account_number = '$account_number'";
		$ifresult = $this->db->query($ifquery) or die ("Query Failed");	
		$myifresult = $ifresult->row_array();
		return $myifresult['$if_field'];
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
					"account_manager_password = '$newhash' ".
					"WHERE account_number = '$account_number'";
				$result = $this->db->query($query) or die ("new password update failed");
				return TRUE;
			}
		} 
		else 
		{
			// passwords do not match
			return FALSE;
		}
	}


}

/* end file models/customer_model.php */
