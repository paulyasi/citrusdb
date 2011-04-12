<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer extends App_Controller {

	function __construct()
	{
		parent::__construct();	
		$this->load->model('customer_model');
		$this->load->model('module_model');
	}
	
	/**
	 * Customer overview of everything
	 */
	public function index()
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'customer');
		if ($permission['view'])
		{
		
			$this->load->view('header_with_sidebar');
		
			$data = $this->customer_model->sidebar($this->account_number);
			$this->load->view('customer_in_sidebar', $data);
			
			$this->load->view('moduletabs');
			
			$this->load->model('ticket_model');
			$this->load->view('messagetabs');
			
			$this->load->view('buttonbar');
			
			$data = $this->customer_model->record($this->account_number);
			$this->load->view('customer/index', $data);
			//$this->load->view('billing/mini');
			//$this->load->view('services/index');
			
			// show html footer
			$this->load->view('html_footer');
		}
		else
		{
			$this->module_model->permission_error();
		}	
		
	}
	
	public function edit()
	{
		if ($pallow_modify) {
    	  include('./modules/customer/edit.php');
    	}  else permission_error();
	}
	
	public function create()
	{
  		if ($pallow_create) {
      	include('./modules/customer/create.php');
    	} else permission_error();
	}
	
	public function delete()
	{
	    if ($pallow_remove) {
	       include('./modules/customer/delete.php');
	    } else permission_error();
	}
	
	public function resetamp()
	{
	    if ($pallow_remove) {
	       include('./modules/customer/resetamp.php');
	    } else permission_error();        
	}
	
	public function undelete()
	{
		if ($pallow_remove) {
  	  	include('./modules/customer/undelete.php');
 	 	} else permission_error();
	}
  
}

/* End of file customer */
/* Location: ./application/controllers/customer.php */