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
    		$query = "SELECT reason FROM cancel_reason WHERE id = $cancel_reason_id";
    		$cancelreasonresult = $this->db->query($query) or die ("$l_queryfailed");
    		$mycancelreasonresult = $result->row();
    		$cancel_reason = $myresult->reason;
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

	
	public function select_cancel_reasons()
	{
		$query = "SELECT * FROM cancel_reason";
    	$cancelreasonresult = $this->db->query($query) or die ("$l_queryfailed");

		return $cancelreasonresult;
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
	
}
