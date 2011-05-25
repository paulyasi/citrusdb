<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer extends App_Controller
{
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
			// load the module header common to all module views
			$this->load->view('module_header');
			
			// show the customer information (name, address, etc)
			$data = $this->customer_model->record($this->account_number);
			$this->load->view('customer/index', $data);

			// show a small preview of billing info
			$this->load->model('billing_model');
			$data['record'] = $this->billing_model->record_list($this->account_number);
			$this->load->view('billing/mini_index', $data);

			// show the services that they have assigned to them
			$this->load->model('service_model');			
			$data['categories'] = $this->service_model->service_categories(
					$this->account_number);
			$this->load->view('services/heading', $data);
			
			// output the list of services
			$data['services'] = $this->service_model->list_services(
					$this->account_number);
			$this->load->view('services/index', $data);
			
			// the history listing tabs
			$this->load->view('historyframe_tabs');			
			
			// the html page footer
			$this->load->view('html_footer');
			
		}
		else
		{
			
			$this->module_model->permission_error();
			
		}	
		
	}
	
	public function edit()
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'customer');
		
		if ($permission['modify'])
		{
			// the module header common to all module views
			$this->load->view('module_header');
			
			// show the edit customer form
			$data = $this->customer_model->record($this->account_number);
			$this->load->view('customer/edit', $data);

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

	public function updatebillingaddress()
	{
		$this->customer_model->update_billingaddress();
			
		print "<h3>" . lang('changessaved') . "<h3>";
		
		// redirect them back to the customer record view
		redirect('/customer');
		
	}

	public function save()
	{
		// GET Variables into array to send to save record function
		//$this->id = $this->input->post('id');

		$customer_data = array(
			'name' => $this->input->post('name'),
			'company' => $this->input->post('company'),
			'street' => $this->input->post('street'),
			'city' => $this->input->post('city'),
			'state' => $this->input->post('state'),
			'country' => $this->input->post('country'),
			'zip' => $this->input->post('zip'),
			'phone' => $this->input->post('phone'),
			'alt_phone' => $this->input->post('alt_phone'),
			'fax' => $this->input->post('fax'),
			'source' => $this->input->post('source'),
			'contact_email' => $this->input->post('contact_email'),
			'secret_question' => $this->input->post('secret_question'),
			'secret_answer' => $this->input->post('secret_answer'),
			'cancel_date' => $this->input->post('cancel_date'),
			'cancel_reason' => $this->input->post('cancel_reason'),
			'notes' => $this->input->post('notes')
		);

		$old_street = $this->input->post('old_street');
		$old_city = $this->input->post('old_city');
		$old_state = $this->input->post('old_state');
		$old_zip = $this->input->post('old_zip');	
		$old_country = $this->input->post('old_country');
		$old_phone = $this->input->post('old_phone');
		$old_fax = $this->input->post('old_fax');
		$old_contact_email = $this->input->post('old_contact_email');

		// save the data to the customer record
		$data = $this->customer_model->save_record(
				$this->account_number, $customer_data);
  
		// add a log entry that this customer record was viewed
		$this->log_model->activity($this->user,$this->account_number,'edit',
				'customer',0,'success');

		print "<h3>". lang('changessaved') ."<h3>";

		// if the name, company, street, city, state, zip, phone, fax, or contact_email 		// changed, ask if they want to update 
		// the default billing record address also.
		if ( ($customer_data['street'] != $old_street) 
				OR ($customer_data['city'] != $old_city)
				OR ($customer_data['state'] != $old_state) 
				OR ($customer_data['zip'] != $old_zip) 
				OR ($customer_data['country'] != $old_country) 
				OR ($customer_data['phone'] != $old_phone)
				OR ($customer_data['fax'] != $old_fax) 
				OR ($customer_data['contact_email'] != $old_contact_email)) 
		{
			// TODO: put this stuff below in a view with proper html headers etc.
			echo lang('addresschange') ."<p>";

			print "<table cellpadding=15 cellspacing=0 border=0 width=720>".
				"<td align=right width=360>";

			// if they hit yes, this will sent them into the billingaddress update

			print "<form style=\"margin-bottom:0;\" action=\"".
				$this->url_prefix . "index.php/customer/updatebillingaddress\" 
				method=post>";
			print "<input name=billingaddress type=submit value=\"" . lang('yes') . 
				"\"class=smallbutton></form></td>";

			// if they hit no, send them back to the service edit screen

			print "<td align=left width=360><form style=\"margin-bottom:0;\" ".
				"action=\"" . $this->url_prefix . "index.php/customer\" method=post>";
			print "<input name=done type=submit value=\"" . lang('no') . 
				"\"class=smallbutton>";
			print "</form></td></table>";
			print "</blockquote>";
		} 
		else 
		{
			redirect('/customer');
		}
	}


	// show the form for adding a new customer
	public function newcustomer($billedby = NULL)
	{
		// set the billed by for the data array passed to the view
		$data['billedby'] = $billedby;

		// the module header common to all module views
		$this->load->view('module_header');
			
		// show the new customer form, if specified billed by, selected by default
		$this->load->view('new_customer', $data);
		
		// the history listing tabs
		$this->load->view('historyframe_tabs');			
			
		// show html footer
		$this->load->view('html_footer');
	}


	/*
	 * ------------------------------------------------------------------------
	 * called by the new form to insert/create a new customer record
	 * ------------------------------------------------------------------------
	 */
	public function create()
	{
		// put the data in a new customer record
		$data = $this->customer_model->create_record($customer_data);		

		// log this record creation
		log_activity($DB,$user,$account_number,'create','customer',0,'success');

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

	public function history()
	{
		// load the ticket model
		$this->load->model('ticket_model');

		// get the customer_history
		$data['history'] = $this->ticket_model->customer_history($this->account_number);
		$this->load->view('customer_history', $data);
	}



}

/* End of file customer */
/* Location: ./application/controllers/customer.php */
