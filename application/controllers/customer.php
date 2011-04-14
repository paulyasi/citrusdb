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
		
			// get the customer title info, name and company
			$data = $this->customer_model->title($this->account_number);
			$this->load->view('customer_in_sidebar', $data);
			
			$this->load->view('moduletabs');
			
			$this->load->model('ticket_model');
			$this->load->view('messagetabs');
			
			$this->load->view('buttonbar');
			
			$data = $this->customer_model->record($this->account_number);
			$this->load->view('customer/index', $data);
			
			$this->load->model('billing_model');
			$data['record'] = $this->billing_model->record_list($this->account_number);
			$this->load->view('billing/mini_index', $data);
			
			$this->load->model('service_model');
			
			// output the services headings
			$data['categories'] = $this->service_model->service_categories($this->account_number);
			$this->load->view('services/heading', $data);
			
			// output the list of services
			$data['services'] = $this->service_model->list_services($this->account_number);
			$this->load->view('services/index', $data);
			
			// TODO: add the history listing tabs
			
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
