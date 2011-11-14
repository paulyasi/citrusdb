<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tickets extends App_Controller {

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
	 *  list tickets for this user
	 * ------------------------------------------------------------------------
	 */
	function user($user, $lastview = NULL)
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
	function group($group, $lastview = NULL)
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

	function pending($id, $ticketgroup = NULL)
	{
		$id = $base->input['id'];
		$pending = $base->input['pending'];
		$completed = $base->input['completed'];
		$showall = $base->input['showall'];
		$lastview = $base->input['lastview'];

		echo "$id $pending $ticketgroup";

		/*--------------------------------------------------------------------------*/
		// mark the customer_history id as pending
		/*--------------------------------------------------------------------------*/
		$query = "UPDATE customer_history SET status = \"pending\" WHERE id = $id";
		$result = $DB->Execute($query) or die ("$l_queryfailed");
		if ($ticketgroup) {
			print "<script language=\"JavaScript\">window.location.href = \"$url_prefix/index.php?load=tickets&type=base&ticketgroup=$ticketgroup\";</script>";
		} 
		else 
		{
			print "<script language=\"JavaScript\">window.location.href = \"$url_prefix/index.php?load=tickets&type=base&ticketuser=$user\";</script>";
		}
	}

	function complete($id, $ticketgroup = NULL) 
	{
		/*--------------------------------------------------------------------------*/
		// make the customer_history id as completed
		/*--------------------------------------------------------------------------*/
		$mydate = date("Y-m-d H:i:s");
		$query = "UPDATE customer_history SET status = 'completed', closed_by = '$user', closed_date = '$mydate' WHERE id = $id";
		$result = $DB->Execute($query) or die ("$query $l_queryfailed");
		
		if ($ticketgroup) 
		{
			print "<script language=\"JavaScript\">window.location.href = \"$url_prefix/index.php?load=tickets&type=base&ticketgroup=$ticketgroup\";</script>";
		} 
		else 
		{
			print "<script language=\"JavaScript\">window.location.href = \"$url_prefix/index.php?load=tickets&type=base&ticketuser=$user\";</script>";
		}
	}


}
