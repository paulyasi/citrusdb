<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer extends App_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('customer_model');
		$this->load->model('module_model');
		$this->load->model('user_model');
		$this->load->model('support_model');
    }		
	
    /*
     * ------------------------------------------------------------------------
     * Customer overview of everything
     * ------------------------------------------------------------------------
     */
    public function index()
    {
		// check permissions	
		$permission = $this->module_model->permission($this->user, 'customer');

		if ($permission['view'])
		{
			$this->load->model('support_model');
			$this->load->model('user_model');

			// load the module header common to all module views
			$this->load->view('module_header_view');

			// show the customer information (name, address, etc)
			$data = $this->customer_model->record($this->account_number);
			$this->load->view('customer/index_view', $data);

			// show a small preview of billing info
			$this->load->model('billing_model');
			$data['record'] = $this->billing_model->record_list($this->account_number);
			$this->load->view('billing/mini_index_view', $data);

			// show the services that they have assigned to them
			$this->load->model('service_model');			
			$data['categories'] = $this->service_model->service_categories(
					$this->account_number);
			$this->load->view('services/heading_view', $data);

			// output the list of services
			$data['services'] = $this->service_model->list_services(
					$this->account_number);
			$this->load->view('services/index_view', $data);

			// the history listing tabs
			$this->load->view('historyframe_tabs_view');			

			// the html page footer
			$this->load->view('html_footer_view');

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
			$this->load->view('module_header_view');

			// show the edit customer form
			$data = $this->customer_model->record($this->account_number);
			$data['cancelreasons'] = $this->customer_model->select_cancel_reasons();
			$this->load->view('customer/edit_view', $data);

			// the history listing tabs
			$this->load->view('historyframe_tabs_view');			

			// show html footer
			$this->load->view('html_footer_view');
		}
		else
		{
			$this->module_model->permission_error();
		}
	}


	public function updatebillingaddress()
	{
		$this->customer_model->update_billingaddress($this->account_number);

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
                                   'customer',0,'success',$_SERVER['REMOTE_ADDR']);

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
				$this->url_prefix . "/index.php/customer/updatebillingaddress\" 
				method=post>";
			print "<input name=billingaddress type=submit value=\"" . lang('yes') . 
				"\"class=smallbutton></form></td>";

			// if they hit no, send them back to the service edit screen

			print "<td align=left width=360><form style=\"margin-bottom:0;\" ".
				"action=\"" . $this->url_prefix . "/index.php/customer\" method=post>";
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
		$this->load->model('general_model');

		// the dashboard header common to all dashboard views
		$this->load->view('dashboard_header_view');

		// set the organization list and billed by
		$data['billedby'] = $billedby;
		$data['organizations'] = $this->general_model->list_organizations();

		// show the new customer form, if specified billed by, selected by default
		$this->load->view('new_customer_view', $data);

		// show html footer
		$this->load->view('html_footer_view');
	}


	/*
	 * ------------------------------------------------------------------------
	 *  called by the new form to insert/create a new customer record
	 * ------------------------------------------------------------------------
	 */
	public function create()
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'customer');

		if ($permission['create'])
		{
			// load the billing model too so we can create a billing record with create record
			$this->load->model('billing_model');			

			$customer_data = array(
					'name' => $this->input->post('name'),
					'company' => $this->input->post('company'),
					'street' => $this->input->post('street'),
					'city' => $this->input->post('city'),
					'state' => $this->input->post('state'),
					'country' => $this->input->post('country'),
					'zip' => $this->input->post('zip'),
					'phone' => $this->input->post('phone'),
					'fax' => $this->input->post('fax'),
					'source' => $this->input->post('source'),
					'contact_email' => $this->input->post('contact_email'),
					'secret_question' => $this->input->post('secret_question'),
					'secret_answer' => $this->input->post('secret_answer'),
					'organization_id' => $this->input->post('organization_id')
					);

			// put the data in a new customer record
			$data = $this->customer_model->create_record($customer_data);		

			// log this record creation
			$this->log_model->activity($this->user,$this->account_number,'create',
                                       'customer',0,'success', $_SERVER['REMOTE_ADDR']);

			redirect('customer');
		}
		else
		{
			$this->module_model->permission_error();
		}
	}


	/*
	 * ------------------------------------------------------------------------
	 *  asks the user whether they are sure they want to cancel this customer
	 * ------------------------------------------------------------------------
	 */
	public function cancel()
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'customer');

		if ($permission['remove'])
		{
			// load the service model to check carrier dependent
			$this->load->model('service_model');
			$this->load->model('user_model');

			// the module header common to all module views
			$this->load->view('module_header_view');

			// check if the services on the account are carrier_dependent
			// if it is carrier dependent, then send user to the
			// carrier dependent cancel form instead of the regular cancel system
			$dependent = $this->service_model->carrier_dependent($this->account_number);

			if ($dependent == true) {
				// load the settings model and then check the dependent url
				$this->load->model('settings_model');				
				$data['dependent_cancel_url'] = $this->settings_model->dependent_cancel_url();

				// show the dependent url view
				$this->load->view('customer/dependent_cancel_view', $data);

			}

			// check if the user has manager privileges
			$privileges = $this->user_model->user_privileges($this->user);

			if ($dependent == false OR $privileges['manager'] == 'y')
			{
				// show the regular cancel form for non carrier dependent and for managers
				// ask if they are sure they want to cancel this customer
				$this->load->view('customer/cancel_view.php');
			}

			// the history listing tabs
			$this->load->view('historyframe_tabs_view');			

			// show html footer
			$this->load->view('html_footer_view');

		}
		else 
		{
			$this->module_model->permission_error();
		}
	}


	/*
	 * -------------------------------------------------------------------------
	 *  asks the user why this customer is canceling their account
	 *  optionally add a /now to the input to cancel today intead of anniversary date
	 * -------------------------------------------------------------------------
	 */
	public function whycancel($now = NULL)
	{
		$data['now'] = $now;
		
		$data['cancelreasons'] = $this->customer_model->select_cancel_reasons();
		$this->load->view('customer/whycancel_view', $data);
	}


	/*
	 * -------------------------------------------------------------------------
	 *  marks a customer record as canceled and moves their services to history
	 *  optionally add a /now to the input to cancel today intead of anniversary date
	 * -------------------------------------------------------------------------
	 */
	public function delete($now = NULL)
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'customer');

		if ($permission['remove'])
		{
			// load the models for functions we use
			$this->load->model('billing_model');			
			$this->load->model('support_model');			
			$this->load->model('log_model');			
			$this->load->model('service_model');			

			// get the cancel reason input
			$cancel_reason = $this->input->post('cancel_reason');


			// set the removal date correctly for now or later
			if ($now == "on") {
				// they should be removed as immediately as possible
				//so use the next billing date as the removal date
				$removal_date = $this->billing_model->get_nextbillingdate();
			} else {
				// figure out the customer's current next billing anniversary date
				$next_billing_date = $this->billing_model->default_next_billing_date($this->account_number);

				// split date into pieces
				$datepieces = explode('-', $next_billing_date);

				$myyear = $datepieces[0];
				$mymonth = $datepieces[1]; 
				$myday = $datepieces[2]; 

				// removal date is normally the anniversary billing date
				$removal_date  = $next_billing_date;

				// today's date
				$today  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
				// if the next billing date is less than today, remove them next available day
				if ($removal_date < $today) 
				{
					$removal_date = $this->billing_model->get_nextbillingdate();
				}
			}

			// figure out all the services that the customer has and delete each one.
			$servicelisting = $this->service_model->list_services($this->account_number);
			foreach ($servicelisting as $myserviceresult) 
			{
				$userserviceid = $myserviceresult['id'];
				$this->service_model->delete_service($userserviceid,'canceled',
						$removal_date);
				$this->log_model->activity($this->user,$this->account_number,
                                           'delete','service',$userserviceid,'success', $_SERVER['REMOTE_ADDR']);
			}

			// set cancel date and leave cancel histories for this customer
			$cancelticket = $this->customer_model->cancel_customer($cancel_reason,
																   $this->account_number);

			// log this customer being canceled/deleted
			$this->log_model->activity($this->user,$this->account_number,
                                       'cancel','customer',0,'success', $_SERVER['REMOTE_ADDR']);

			// redirect them to the customer page	
			redirect('/customer');

		}
		else 
		{
			$this->module_model->permission_error();
		}


	}



	/*
	 * --------------------------------------------------------------------------------
	 *  ask the user if they are sure they want to uncancel this customer
	 * --------------------------------------------------------------------------------
	 */
	public function uncancel()
	{
		// show the regular cancel form for non carrier dependent and for managers
		// ask if they are sure they want to cancel this customer
		print "<br><br>";
		print "<h4>". lang('areyousureuncancel') . ": $this->account_number</h4>";
		print "<table cellpadding=15 cellspacing=0 border=0 width=720>";
		print "<td align=right width=240>";

		// if they hit yes, this will sent them into the undelete.php file
		// and remove the service on their next billing anniversary

		print "<form style=\"margin-bottom:0;\" action=\"". $this->url_prefix . "/index.php/customer/undelete\" method=post>";
		print "<input name=undeletenow type=submit value=\"". lang('yes') . " \" class=smallbutton></form></td>";

		// if they hit no, send them back to the service edit screen

		print "<td align=left width=240>";
		print "<form style=\"margin-bottom:0;\" action=\"". $this->url_prefix ."/index.php/customer\" method=post>";
		print "<input name=done type=submit value=\"" . lang('no') . " \" class=smallbutton>";
		print "</form></td>";

		print "</table>";
		print "</blockquote>";
	}


	/*
	 * --------------------------------------------------------------------------------
	 *  perform the uncancel by undeleting the customer record and associated things
	 * --------------------------------------------------------------------------------
	 */
	public function undelete()
	{
		// load the billing model so I can get the next billingdate			
		$this->load->model('billing_model');			
		$this->load->model('log_model');			

		$this->customer_model->undelete_customer($this->account_number);

		// log this uncancel
		$this->log_model->activity($this->user,$this->account_number,'uncancel','customer',0,'success', $_SERVER['REMOTE_ADDR']);

		// redirect them to the customer page	
		redirect('/customer');
	}

	public function history($all = NULL)
	{
        $this->load->model('support_model');

        if($all)
        {
		    $data['history'] = $this->support_model->all_customer_history($this->account_number);
        }
        else
        {
            $data['history'] = $this->support_model->customer_history($this->account_number);
        }

		$this->load->view('customer/history_view', $data);
	}


	/*
	 * ------------------------------------------------------------------------
	 *  show the view for reset account manager password
	 * ------------------------------------------------------------------------
	 */
	public function resetamp()
	{
		$this->load->view('customer/resetamp_view');
	}


	/*
	 * ------------------------------------------------------------------------
	 *  reset account manager password for this customer
	 * ------------------------------------------------------------------------
	 */
	public function saveresetamp()
	{
		$new_password1 = $this->input->post('new_password1');
		$new_password2 = $this->input->post('new_password2');

		$result = $this->customer_model->change_account_manager_password(
				$this->account_number, 
				$new_password1, 
				$new_password2
				);

		if ($result == TRUE) 
		{
			print "<h3>".lang('passwordchanged')."</h3>";
			redirect('/customer');
		} 
		else 
		{
			echo "<h3>".lang('error')."</h3>";
		}

	}

}
/* End of file customer */
/* Location: ./application/controllers/customer.php */
