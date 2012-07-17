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
	 *  get list of billing profile
	 *  TODO: change this to just get the default billing profile
	 *  only get the billing profile if all active alternate billing types are
	 *  the same as the default billing type (eg all creditcard or all invoice)
	 *  ten update them all with the same information if the update the default
	 *  when one updated the default profile update alternate profiles
	 *  with the same information automatically or else it's really confusing
	 *  to customers online
	 * ------------------------------------------------------------------------
	 */
	function billing_get()
	{
		// load the customer model we are about to use
		$this->load->model('billing_model');
		
		// get the list of all billing types to check they have the same method so customers can update them
		// online instead of need a customer service representative to do that manually
		$record = $this->billing_model->record_list($this->authuser);
		
		// set a boolean to change if we don't want to allow edits if they have different billing methods
		$allow_edit = TRUE;
		$num_of_billing_records = 0;
		
		foreach ($record as $billing_record) 
		{
			$num_of_billing_records++;
			
			$billing_id = $billing_record['b_id'];
			$billing_method = $billing_record['t_method'];
			$not_removed_id = $billing_record['not_removed_id'];
			
			if (($not_removed_id > 0 AND $num_of_billing_records > 1))
			{
				if ($billing_method <> $previous_method)
				{
					// set allow_edit to false since they have multiple active billing methods
					$allow_edit = FALSE;
				}
			}
			$previous_method = $billing_method;
		}
		
		
		$default_billing_id = $this->billing_model->default_billing_id($this->authuser);

		// return billing record data
		$data = $this->billing_model->record($billing_id);
		$data['allow_edit'] = $allow_edit;
		$data['num_of_billing_records'] = $num_of_billing_records;
		$this->response($data);
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
