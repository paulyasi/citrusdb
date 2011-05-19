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
			$this->load->view('header_with_sidebar');
			
			// show the customer title info, name and company
			$data = $this->customer_model->title($this->account_number);
			$this->load->view('customer_in_sidebar', $data);

			// show the module tab listing (customer, services, billing, etc.)
			$this->load->view('moduletabs');

			// show the tickets messages tabs for this user 
			$this->load->model('ticket_model');
			$this->load->view('messagetabs');

			// show the buttons across the top (new, search, tools, etc)
			$this->load->view('buttonbar');

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
		// check permissions
		$permission = $this->module_model->permission($this->user, 'customer');
		
		if ($permission['modify'])
		{
			$this->load->view('header_with_sidebar');
			
			// show the customer title info, name and company
			$data = $this->customer_model->title($this->account_number);
			$this->load->view('customer_in_sidebar', $data);

			// show the module tab listing (customer, services, billing, etc.)
			$this->load->view('moduletabs');

			// show the tickets messages tabs for this user 
			$this->load->model('ticket_model');
			$this->load->view('messagetabs');

			// show the buttons across the top (new, search, tools, etc)
			$this->load->view('buttonbar');

			// -- TODO: make this edit view work --
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

	public function update_billingaddress()
	{
		$this->customer_model->update_billingaddress();
			
		print "<h3>$l_changessaved<h3>";
		
		// redirect them back to the customer record view
		redirect('/customer');
		
	}

	public function save()
	{
		// GET Variables
		$name = $base->input['name'];
		$company = $base->input['company'];
		$street = $base->input['street'];
		$city = $base->input['city'];
		$state = $base->input['state'];
		$country = $base->input['country'];
		$zip = $base->input['zip'];
		$phone = $base->input['phone'];
		$alt_phone = $base->input['alt_phone'];
		$fax = $base->input['fax'];
		$source = $base->input['source'];
		$contact_email = $base->input['contact_email'];
		$secret_question = $base->input['secret_question'];
		$secret_answer = $base->input['secret_answer'];
		$cancel_date = $base->input['cancel_date'];
  		//$removal_date = $base->input['removal_date'];
		$account_number = $_SESSION['account_number'];
		$cancel_reason = $base->input['cancel_reason'];
		$notes = $base->input['notes'];
  
		// get the old values to compare with the new ones to check if we need 
		// to update the billing record also
		$old_street = $base->input['old_street'];
		$old_city = $base->input['old_city'];
		$old_state = $base->input['old_state'];
		$old_zip = $base->input['old_zip'];	
		$old_country = $base->input['old_country'];
		$old_phone = $base->input['old_phone'];
		$old_fax = $base->input['old_fax'];
		$old_contact_email = $base->input['old_contact_email'];
  
		// build update query
		// if the cancel date is empty, then put NULL in the cancel and removal date
		if ($cancel_date == "") 
		{                
			$query = "UPDATE customer ".
				"SET name = '$name', ".
				"company = '$company', ".
				"street = '$street', ".
				"city = '$city', ".
				"state = '$state', ".
				"country = '$country', ".
				"zip = '$zip', ".
				"phone = '$phone', ".
				"alt_phone = '$alt_phone', ".
				"fax = '$fax', ".
				"source = '$source', ".
				"contact_email = '$contact_email', ".
				"secret_question = '$secret_question', ".
				"secret_answer = '$secret_answer', ".
				"cancel_date = NULL, ".
				"cancel_reason = NULL, ".
				"notes = '$notes' ".
				"WHERE account_number = '$account_number'";
  		} else {
			// there is a cancel date, so put there in there
			$query = "UPDATE customer ".
				"SET name = '$name', ".
				"company = '$company', ".
				"street = '$street', ".
				"city = '$city', ".
				"state = '$state', ".
				"country = '$country', ".
				"zip = '$zip', ".
				"phone = '$phone', ".
				"alt_phone = '$alt_phone', ".
				"fax = '$fax', ".
				"source = '$source', ".
				"contact_email = '$contact_email', ".
				"secret_question = '$secret_question', ".
				"secret_answer = '$secret_answer', ".
				"cancel_date = '$cancel_date', ".
				"cancel_reason = '$cancel_reason', ".
				"notes = '$notes' ".
				"WHERE account_number = '$account_number'";
		}
		$result = $DB->Execute($query) or die ("$l_queryfailed");

		// add a log entry that this customer record was viewed
		log_activity($DB,$user,$account_number,'edit','customer',0,'success');  

		print "<h3>$l_changessaved<h3>";

		// if the name, company, street, city, state, zip, phone, fax, or contact_email 		// changed, ask if they want to update 
		// the default billing record address also.
		if ( ($street != $old_street) OR ($city != $old_city)
				OR ($state != $old_state) OR ($zip != $old_zip) 
				OR ($country != $old_country) OR ($phone != $old_phone)
				OR ($fax != $old_fax) OR ($contact_email != $old_contact_email) ) {
			echo "$l_addresschange<p>";

			print "<table cellpadding=15 cellspacing=0 border=0 width=720>".
				"<td align=right width=360>";

			// if they hit yes, this will sent them into the billingaddress update

			print "<form style=\"margin-bottom:0;\" action=\"index.php\" method=post>";
			print "<input type=hidden name=load value=customer>";
			print "<input type=hidden name=type value=module>";
			print "<input type=hidden name=edit value=on>";
			print "<input name=billingaddress type=submit value=\" $l_yes \" ".
				"class=smallbutton></form></td>";

			// if they hit no, send them back to the service edit screen

			print "<td align=left width=360><form style=\"margin-bottom:0;\" ".
				"action=\"index.php\" method=post>";
			print "<input name=done type=submit value=\" $l_no \" class=smallbutton>";
			print "<input type=hidden name=load value=customer>";        
			print "<input type=hidden name=type value=module>";
			print "</form></td></table>";
			print "</blockquote>";
		} 
		else 
		{
			print "<script language=\"JavaScript\">window.location.href = ".
				"\"index.php?load=customer&type=module\";</script>";
		}
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
