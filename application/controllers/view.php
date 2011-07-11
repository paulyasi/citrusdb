<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class View extends App_Controller
{
	function __construct()
	{
		parent::__construct();	
	}
	
	/**
	 * Redirects the view to a new customer account view
	 * @param: account number
	 */
	public function account($account_number)
	{
		//$_SESSION['account_number'] = $account_number;
		$this->account_number = $account_number;
		
		// log this account view
		$this->log_model->activity($this->user, $this->account_number, 'view', 'customer', 0, 'success');

		redirect('customer');
	}
	
	/**
	 * Redirects the view to a service listing
	 * @param: service id
	 */
	public function service($service_id)
	{		
		
	}
	
	/**
	 * Redirects the view to a ticket listing
	 * @param: ticket id
	 */
	public function ticket($ticket_id)
	{
		
	}
			
}