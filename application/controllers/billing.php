<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Billing extends App_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('customer_model');
		$this->load->model('billing_model');
		$this->load->model('module_model');
    }		
	
    /*
     * ------------------------------------------------------------------------
     * Customer overview of everything
     * ------------------------------------------------------------------------
     */
    public function index()
    {
		// check permissions	
		$permission = $this->module_model->permission($this->user, 'billing');

		if ($permission['view'])
		{
			// load the module header common to all module views
			$this->load->view('module_header_view');
			
			// show the billing information (name, address, etc)
			$data = $this->billing_model->record($billing_id);
			$this->load->view('billing/index_view', $data);
			
			// show any alternate billing types
			$data['billing_types'] = $this->billing_model->alternates(
					$this->account_number);
			$this->load->view('billing/alternates_view', $data);
			
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
					'secret_answer' => $this->input->post('secret_answer')
					);

			// put the data in a new customer record
			$data = $this->customer_model->create_record($customer_data);		

			// log this record creation
			$this->log_model->activity($this->user,$this->account_number,'create',
					'customer',0,'success');

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

			// check if the services on the account are carrier_dependent
			// if it is carrier dependent, then send user to the
			// carrier dependent cancel form instead of the regular cancel system
			$dependent = $this->service_model->carrier_dependent($this->account_number);

			if ($dependent == true) {
				// print a message that this customer is carrier dependent
				echo "<h3>" . lang('carrierdependentmessage') . "</h3><p align=center>";

				// get the dependent_cancel_url from the settings table
				$query = "SELECT dependent_cancel_url FROM settings WHERE id = 1";
				$result = $this->db->query($query) or die ("$l_queryfailed");
				$myresult = $result->row();
				$dependent_cancel_url = $myresult->dependent_cancel_url;

				// print a link to the url to fill out the carrier dependent cancel form
				print "<a href=\"$dependent_cancel_url$this->account_number\" target=\"_BLANK\">" . lang('cancelcustomer') . "</a></p>";

			}

			// check if the user has manager privileges
			$result = $this->db->get_where('user', array('username' => $this->user), 1);
			//$query = "SELECT * FROM user WHERE username='$user'";
			//$result = $DB->Execute($query) or die ("$l_queryfailed");
			$myresult = $result->row();
			$manager = $myresult->manager;

			if ($dependent == false OR $manager == 'y') {
				// show the regular cancel form for non carrier dependent and for managers
				// ask if they are sure they want to cancel this customer
				print "<br><br>";
				print "<h4>" . lang('areyousurecancel') .": $this->account_number</h4>";
				print "<table cellpadding=15 cellspacing=0 border=0 width=720>";
				print "<td align=right width=240>";

				// if they hit yes, this will sent them into the delete.php file and remove the service on their next billing anniversary

				print "<form style=\"margin-bottom:0;\" action=\"" . $this->url_prefix . "index.php/customer/whycancel\" method=post>";
				print "<input name=whycancel type=submit value=\"". lang('yes') . "\" class=smallbutton></form></td>";

				// if they hit no, send them back to the service edit screen

				print "<td align=left width=240>";
				print "<form style=\"margin-bottom:0;\" action=\"" . $this->url_prefix . "index.php/customer\" method=post>";
				print "<input name=done type=submit value=\" ". lang('no') . " \" class=smallbutton>";
				print "</form></td>";

				// if they hit Remove Now, send them to delete.php and remove the 
				// service on the next available work date, the next valid billing date

				print "<td align=left width=240>";
				print "<form style=\"margin-bottom:0;\" action=\"" . $this->url_prefix . "index.php/customer/whycancel/now\" method=post>";
				print "<input name=whycancel type=submit value=\"". lang('remove_now') . "\" class=smallbutton>";  
				print "</form></td>";

				print "</table>";
				print "</blockquote>";
			}

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
			$this->load->model('ticket_model');			
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
				$query = "SELECT b.next_billing_date FROM customer c " .
					"LEFT JOIN billing b ON c.default_billing_id = b.id ".
					"WHERE c.account_number = '$this->account_number'";
				$result = $this->db->query($query) or die ("$query $l_queryfailed");
				$myresult = $result->row_array();
				$next_billing_date = $myresult['next_billing_date'];

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
			$query = "SELECT * FROM user_services 
				WHERE account_number = '$this->account_number' AND removed <> 'y'";
			$result = $this->db->query($query) or die ("query failed");
			foreach ($result->result_array() as $myserviceresult) 
			{
				$userserviceid = $myserviceresult['id'];
				$this->service_model->delete_service($userserviceid,'canceled',
						$removal_date);
				$this->log_model->activity($this->user,$this->account_number,
						'delete','service',$userserviceid,'success');
			}

			// set cancel date and removal date of customer record
			$query = "UPDATE customer ".
				"SET cancel_date = CURRENT_DATE, ". 
				"cancel_reason = '$cancel_reason' ".
				"WHERE account_number = '$this->account_number'";
			$result = $this->db->query($query) or die ("query failed");

			// set next_billing_date to NULL since it normally won't be billed again
			$query = "UPDATE billing ".
				"SET next_billing_date = NULL ". 
				"WHERE account_number = '$this->account_number'";
			$result = $this->db->query($query) or die ("query failed");   

			// get the text of the cancel reason to use in the note
			$query = "SELECT * FROM cancel_reason " . 
				"WHERE id = '$cancel_reason'";
			$result = $this->db->query($query) or die ("query failed");
			$myresult = $result->row_array();
			$cancel_reason_text = $myresult['reason'];

			// add cancel ticket to customer_history
			// if they are carrier dependent, send a note to
			// the billing_noti
			$desc = lang('canceled') . ": $cancel_reason_text";
			$this->ticket_model->create_ticket($this->user, NULL, 
					$this->account_number, 'automatic', $desc);

			// get the billing_id for the customer's payment_history
			$query = "SELECT default_billing_id FROM customer " . 
				"WHERE account_number = '$this->account_number'";
			$result = $this->db->query($query) or die ("$l_queryfailed");
			$myresult = $result->row_array();
			$default_billing_id = $myresult['default_billing_id'];

			// add a canceled entry to the payment_history
			$query = "INSERT INTO payment_history ".
				"(creation_date, billing_id, status) ".
				"VALUES (CURRENT_DATE,'$default_billing_id','canceled')";
			$paymentresult = $this->db->query($query) or die ("$l_queryfailed");

			// log this customer being canceled/deleted
			$this->log_model->activity($this->user,$this->account_number,
					'cancel','customer',0,'success');

			// redirect them to the customer page	
			redirect('/customer');

		}
		else 
		{
			$this->module_model->permission_error();
		}


	}

	public function rerun()
	{
	if ($pallow_modify)    
	{       
		include('./modules/billing/rerun.php');    
	}  else permission_error();
	}

	/*
	 * --------------------------------------------------------------------------------
	 *  ask the user if they are sure they want to uncancel this customer
	 * --------------------------------------------------------------------------------
	 */
	public function turnoff()
	{
		
	if ($pallow_modify)    
	{       
		include('./modules/billing/turnoff.php');    
	}  else permission_error();
	}

	
	/*
	 * --------------------------------------------------------------------------------
	 *  perform the uncancel by undeleting the customer record and associated things
	 * --------------------------------------------------------------------------------
	 */


	public function resetaddr()
	{
		// reset the address to the current customer address
	    if ($pallow_modify)
    	{
       		include('./modules/billing/resetaddr.php');
    	} else permission_error();
	}
	


	public function cancelwfee()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/cancelwfee.php');
		}  else permission_error();
	}


	public function collections()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/collections.php');
		}  else permission_error();
	}


	public function waiting()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/waiting.php');
		}  else permission_error();
	}


	public function authorized()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/authorized.php');
		}  else permission_error();
	}


	public function asciiarmor()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/asciiarmor.php');
		}  else permission_error();
	}


	public function nsf()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/nsf.php');
		}  else permission_error();
	}


	public function receipt()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/receipt.php');
		}  else permission_error();
	}

	public function deletepayment()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/deletepayment.php');
		}  else permission_error();
	}


	public function createinvoice()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/createinvoice.php');
		}  else permission_error();
	}


	public function cancelnotice()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/cancelnotice.php');
		}  else permission_error();
	}


	public function shutoffnotice()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/shutoffnotice.php');
		}  else permission_error();
	}


	public function collectionsnotice()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/collectionsnotice.php');
		}  else permission_error();
	}



}

/* End of file customer */
/* Location: ./application/controllers/customer.php */
