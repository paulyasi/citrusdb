<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Support extends App_Controller {

	function __construct()
	{
		parent::__construct();	
		$this->load->model('service_model');
		$this->load->model('schema_model');
		$this->load->model('module_model');
		$this->load->model('customer_model');
		$this->load->model('billing_model');
		$this->load->model('support_model');
	}
	
	
	/*
	 * ------------------------------------------------------------------------
	 *  Show form to add support note, the default view
	 * ------------------------------------------------------------------------
	 */
	public function index()
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'support');
		if ($permission['view'])
		{
			// load user model so can show list of users to send note to	
			$this->load->model('user_model');

			// get the variables for service id if some were passed to us	
			$serviceid = $this->input->post('serviceid');

			// load the module header common to all module views
			$this->load->view('module_header_view');
		
			// show the support note form
			if ($serviceid)
			{
				$data = $this->service_model->get_service_desc_and_notify($serviceid);
				$this->load->view('support/index_view', $data);
			}
			else
			{
				$data['user_services_id'] = NULL;
				$data['service_description'] = NULL;
				$data['support_notify'] = NULL;
				$this->load->view('support/index_view', $data);
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
	   if (!isset($base->input['notify'])) { $base->input['notify'] = ""; }
	   if (!isset($base->input['status'])) { $base->input['status'] = ""; }
	   if (!isset($base->input['dtext'])) { $base->input['dtext'] = ""; }
	   if (!isset($base->input['reminderdate'])) { $base->input['reminderdate'] = ""; }
	   if (!isset($base->input['serviceid'])) { $base->input['serviceid'] = ""; }

	   $editticket = $base->input['editticket'];
	   $notify = $base->input['notify'];
	   $status = $base->input['status'];
	   $dtext = $base->input['dtext'];
	   $reminderdate = $base->input['reminderdate'];
	   $user_services_id = $base->input['serviceid'];

	// grab the description manually to preserve newlines
	//if (!isset($_POST['description'])) { $_POST['description'] = ''; }
	$description = $_POST['description'];
	$description = safe_value_with_newlines($description);
	 */


	public function create()
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'support');
		if ($permission['create'])
		{
			$notify = $this->input->post('notify');
			$status = $this->input->post('status');
			$description = $this->input->post('description');
			$reminderdate = $this->input->post('reminderdate');
			$user_services_id = $this->input->post('serviceid');

			$newticketnumber = $this->support_model->create_ticket(
					$this->user, $notify, $this->account_number,
					$status, $description, NULL, NULL, $reminderdate,
					$user_services_id);

			// if the note is marked as completed, insert the completed by data too
			if ($status == 'completed')
			{
				$this->support_model->complete_ticket($newticketnumber);
			}

			redirect('/customer');	
		}
		else
		{
			$this->module_model->permission_error();
		}	

	}


	public function editticket($id)
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'support');
		if ($permission['view'])
		{
			// load user model so can show list of users to send note to	
			$this->load->model('user_model');

			// get the variables for service id if some were passed to us	
			$serviceid = $this->input->post('serviceid');

			// load the module header common to all module views
			$this->load->view('module_header_view');

			$data['groupslist'] = $this->user_model->list_groups();
			$data['userslist'] = $this->user_model->list_users();
			$data['ticket'] = $this->support_model->get_ticket($id);
			$data['sub_history'] = $this->support_model->get_sub_history($data['ticket']['id']);
			$this->load->view('support/editticket_view', $data);

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


	function saveeditticket() 
	{
		$id = $this->input->post('id');
		$notify = $this->input->post('notify');
		$status = $this->input->post('status');
		$savechanges = $this->input->post('savechanges');
		$reminderdate = $this->input->post('reminderdate');
		$serviceid = $this->input->post('serviceid');
		$oldstatus = $this->input->post('oldstatus');
		$description = $this->input->post('description');
		$addnote = $this->input->post('addnote');

		$this->support_model->update_ticket($id, $notify, $status, $description, 
				$reminderdate, $user_services_id, $oldstatus, $addnote);

		// redirect back to ticket listing
		redirect('/tickets/user/'.$this->user);

	}


	/*
	 * ------------------------------------------------------------------------
	 *  list tickets for this user
	 * ------------------------------------------------------------------------
	 */
	function usertickets($user, $lastview = NULL)
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'support');
		if ($permission['view'])
		{
			// load the module header common to all module views
			$this->load->view('module_header_view');

			$data['tickets'] = $this->support_model->list_tickets($user);
			$this->load->view('support/ticketuser_view', $data);

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
	 * ------------------------------------------------------------------------
	 *  list tickets for this group
	 * ------------------------------------------------------------------------
	 */
	function grouptickets($group, $lastview = NULL)
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'support');
		if ($permission['view'])
		{
			// load the module header common to all module views
			$this->load->view('module_header_view');

			$data['group'] = $group;
			$data['tickets'] = $this->support_model->list_tickets($group);
			$this->load->view('support/ticketgroup_view', $data);

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



}
