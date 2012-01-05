<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tickets extends App_Controller {

	function __construct()
	{
		parent::__construct();	
		$this->load->model('service_model');
		$this->load->model('user_model');
		$this->load->model('module_model');
		$this->load->model('customer_model');
		$this->load->model('billing_model');
		$this->load->model('support_model');
	}



	/*
	 * -------------------------------------------------------------------------
	 *  load the message tabs output for ajax requests
	 * -------------------------------------------------------------------------
	 */
	function messagetabs()
	{
		$data['usergroups'] = $this->user_model->user_groups($this->user);
		$this->load->view('messagetabs_view', $data);
	}


	/*
	 * ------------------------------------------------------------------------
	 *  list tickets for this user
	 * ------------------------------------------------------------------------
	 */
	function user($user = NULL, $lastview = NULL)
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'support');
		if ($permission['view'])
		{
			// if no user specified, then set to current user
			if (!$user) 
			{
				$user = $this->user;
			}			

			// show the special html header for ticket pages
			$data['ticketuser'] = $user;
			$this->load->view('header_for_tickets_view.php', $data);
			
			// show the sidebar and heading for tickets
			$this->load->view('ticket_header_view');

			$data['user'] = $user;
			$data['tickets'] = $this->support_model->list_tickets($user);
			$this->load->view('tickets/user_view', $data);

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
	function group($group, $lastview = NULL)
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'support');
		if ($permission['view'])
		{
			// show the special html header for ticket pages
			$data['ticketgroup'] = $group;
			$this->load->view('header_for_tickets_view.php', $data);
			
			// show the sidebar and heading for tickets
			$this->load->view('ticket_header_view');
			
			$data['notify'] = $group;
			$data['tickets'] = $this->support_model->list_tickets($group);
			$this->load->view('tickets/group_view', $data);

			// show html footer
			$this->load->view('html_footer_view');
		}
		else
		{
			$this->module_model->permission_error();
		}	

	}

	function pending($id, $ticketgroup = NULL)
	{
		echo "$id $pending $ticketgroup";

		$this->support_model->pending_ticket($id);

		if ($ticketgroup) 
		{
			redirect('tickets/group/'.$ticketgroup);
		} 
		else 
		{
			redirect('tickets/user');
		}
	}

	function complete($id, $ticketgroup = NULL) 
	{

		$this->support_model->complete_ticket($id);

		if ($ticketgroup) 
		{
			redirect('tickets/group/'.$ticketgroup);
		} 
		else 
		{
			redirect('tickets/user');
		}
	}


}
