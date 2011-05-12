<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Services extends App_Controller {

	function __construct()
	{
		parent::__construct();	
		$this->load->model('service_model');
		$this->load->model('module_model');
		$this->load->model('customer_model');
		$this->load->model('billing_model');
	}
	
	/**
	 * Customer overview of everything
	 */
	public function index()
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'services');
		if ($permission['view'])
		{
		
			$this->load->view('header_with_sidebar');
		
			// get the customer title info, name and company
			$data = $this->customer_model->title($this->account_number);
			$this->load->view('customer_in_sidebar', $data);
			
			$this->load->view('moduletabs');
			
			$this->load->model('ticket_model');
			$this->load->view('messagetabs');
			
			$this->load->view('buttonbar');
			
			$data['categories'] = $this->service_model->service_categories($this->account_number);
			$this->load->view('services/heading', $data);
						
			// output the list of services
			$data['services'] = $this->service_model->list_services($this->account_number);
			$this->load->view('services/index', $data);
						
			// the history listing tabs
			$this->load->view('historyframe_tabs');	
			
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
	
	public function fieldassets()
	{
	    if ($pallow_remove) {
	       include('./modules/customer/fieldassets');
	    } else permission_error();        
	}
	
	public function history()
	{
		if ($pallow_remove) {
  	  	include('./modules/customer/history');
 	 	} else permission_error();
	}
	
	public function vendor()
	{
		if ($pallow_remove) {
  	  	include('./modules/customer/vendor');
 	 	} else permission_error();
	}
  
}

/* End of file customer */
/* Location: ./application/controllers/customer.php */
