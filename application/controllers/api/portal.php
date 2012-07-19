<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Portal API Controller
 *
 * This is the portal API that allows the customer self-service portal
 * communicate with the main citrusdb system
 *
 * view customer profile 
 * view billing info (without encrypted card data) - TODO maybe only for default
 * billing id and upate the other billing id's with the same info if a customer
 * updates it online since otherwise it's really confusing
 * view services list and attributes
 * view invoice history
 * view past invoices as text or pdf format
 * view payment history
 * request new services
 * update billing info 
 */

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Portal extends REST_Controller
{

	/*
	 * -------------------------------------------------------------------------
	 *  get customer profile
	 * ------------------------------------------------------------------------
	 */
	function customer_get()
	{
		// load the customer model we are about to use
		$this->load->model('customer_model');

		// grab customer information (name, address, etc)
		// use the authuser var we set after authentication
		$data = $this->customer_model->record($this->authuser);
		$this->response($data);
	}


	/*
	 * -------------------------------------------------------------------------
	 *  get default billing profile
	 * -------------------------------------------------------------------------
	 */
	function billing_get()
	{
		// load the customer model we are about to use
		$this->load->model('billing_model');
		
		$default_billing_id = $this->billing_model->default_billing_id($this->authuser);

		// return billing record data
		$data = $this->billing_model->portal_record($default_billing_id);
		$this->response($data);
	}
	
	
	/* 
	 * ------------------------------------------------------------------------
	 *  update all billing profiles with the same information, do not allow
	 *  updates of the billing type or due dates etc, just billing address, 
	 *  email address and card information
	 *
	 *  TODO: add an edit all billing records function to billing model that can 
	 *  be used by csr and customer portal to update billing address and card 
	 *  number on all billing records regardless of type, but cannot change 
	 *  billing type, due dates, etc.
	 *
	 *  USE billing_model->save_record with array of only fields I want it to change
	 *  do the save_record on each billing id they have, not just default
	 *  so that all their bill information is up-to-date with new address and card
	 * ------------------------------------------------------------------------
	 */
	function billing_post()
	{		
		// TODO: sorta will work like the save controller copied below:
		// TODO: get a list of all billing id's for $this->authuser
		
		// check if there is a non-masked credit card number in the input
		// if the second cararcter is a * then it's already masked
		$newcc = FALSE; // set to false so we don't replace it unnecessarily		
		$creditcard_number = $this->post('creditcard_number');
		
		// check if the credit card entered already masked and not blank
		// eg: a replacement was not entered
		if ($creditcard_number[1] <> '*' AND $creditcard_number <> '')
		{			
			// destroy the output array before we use it again
			unset($encrypted);

			// load the encryption helper for use when calling gpg things
			$this->load->helper('encryption');

			// run the gpg command
			$encrypted = encrypt_command($this->config->item('gpg_command'), $creditcard_number);
			
			// if there is a gpg error, stop here
			if (substr($encrypted,0,5) == "error")
			{				
				die ("Credit Card Encryption Error: $encrypted");
			}

			// change the ouput array into ascii ciphertext block
			$encrypted_creditcard_number = $encrypted;

			// wipe out the middle of the creditcard_number before it gets inserted
			$length = strlen($creditcard_number);
			$firstdigit = substr($creditcard_number, 0,1);
			$lastfour = substr($creditcard_number, -4);
			$creditcard_number = "$firstdigit" . "***********" . "$lastfour";
			
			$newcc = TRUE;
		}
		
		// fill in the billing data array with new address
		$billing_data = array(
			'name' => $this->input->post('name'),
			'company' => $this->input->post('company'),
			'street' => $this->input->post('street'),
			'city' => $this->input->post('city'),
			'state'=> $this->input->post('state'),
			'zip' => $this->input->post('zip'),
			'country' => $this->input->post('country'),
			'phone' => $this->input->post('phone'),
			'fax' => $this->input->post('fax'),
			'contact_email' => $this->input->post('contact_email')
			);
		
		// if they are providing a new credit card, put that in the array too
		if ($newcc == TRUE)
		{
			// insert with a new credit card and encrypted ciphertext
			$billing_data['encrypted_creditcard_number'] = $encrypted_creditcard_number;
			$billing_data['creditcard_number'] = $creditcard_number;
			$billing_data['creditcard_expire'] = $this->input->post('creditcard_expire');
		}
		
		// TODO: do this for each billing ID they have, for now do it for the default at least
		$this->load->model('billing_model');
		$billing_id = $this->billing_model->default_billing_id($this->authuser);

		// save the data to the customer record
		$data = $this->billing_model->save_record($billing_id, $billing_data);			

		// add a log entry that this billing record was edited
		$this->log_model->activity("portal",$this->authuser,'edit','billing',$billing_id,'success');

		$this->response(array('success' => 'Input Saved'), 200);
	}


	/* EXAMPLES BELOW ------------------------------------------
	
	function user_get()
    {
        if(!$this->get('id'))
        {
        	$this->response(NULL, 400);
        }

        // $user = $this->some_model->getSomething( $this->get('id') );
    	$users = array(
			1 => array('id' => 1, 'name' => 'Some Guy', 'email' => 'example1@example.com', 'fact' => 'Loves swimming'),
			2 => array('id' => 2, 'name' => 'Person Face', 'email' => 'example2@example.com', 'fact' => 'Has a huge face'),
			3 => array('id' => 3, 'name' => 'Scotty', 'email' => 'example3@example.com', 'fact' => 'Is a Scott!'),
		);
		
    	$user = @$users[$this->get('id')];
    	
        if($user)
        {
            $this->response($user, 200); // 200 being the HTTP response code
        }

        else
        {
            $this->response(array('error' => 'User could not be found'), 404);
        }
    }
    
    function user_post()
    {
        //$this->some_model->updateUser( $this->get('id') );
        $message = array('id' => $this->get('id'), 'name' => $this->post('name'), 'email' => $this->post('email'), 'message' => 'ADDED!');
        
        $this->response($message, 200); // 200 being the HTTP response code
    }
    
    function user_delete()
    {
    	//$this->some_model->deletesomething( $this->get('id') );
        $message = array('id' => $this->get('id'), 'message' => 'DELETED!');
        
        $this->response($message, 200); // 200 being the HTTP response code
    }
    
    function users_get()
    {
        //$users = $this->some_model->getSomething( $this->get('limit') );
        $users = array(
			array('id' => 1, 'name' => 'Some Guy', 'email' => 'example1@example.com'),
			array('id' => 2, 'name' => 'Person Face', 'email' => 'example2@example.com'),
			array('id' => 3, 'name' => 'Scotty', 'email' => 'example3@example.com'),
		);
        
        if($users)
        {
            $this->response($users, 200); // 200 being the HTTP response code
        }

        else
        {
            $this->response(array('error' => 'Couldn\'t find any users!'), 404);
        }
    }
	*/
}
