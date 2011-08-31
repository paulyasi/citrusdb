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
			// get the variables for service id if some were passed to us	
			$serviceid = $this->input->post('serviceid');

			// load the module header common to all module views
			$this->load->view('module_header_view');
		
			// show the support note form
			$data = $this->support_model->get_service_desc_and_notify($serviceid);
			$this->load->view('support/index_view', $data);

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


	public function editticket($id)
	{
		if ($pallow_modify)
		{
			include('./modules/support/editticket.php');
		} else permission_error();
	}


	public function create()
	{
		if ($pallow_create)
		{
			$newticketnumber = create_ticket($DB, $user, $notify, $account_number,
					$status, $description, NULL, NULL, $reminderdate,
					$user_services_id);

			// if the note is marked as completed, insert the completed by data too
			if ($status == 'completed') {
				$query = "UPDATE customer_history SET ".
					"closed_by = '$user', ".
					"closed_date = CURRENT_TIMESTAMP ".
					"WHERE id = $newticketnumber";
				$result = $DB->Execute($query) or die ("closed by $l_queryfailed"); 
			}

			print "<script language=\"JavaScript\">window.location.href = \"index.php?load=customer&type=module\";</script>";
		} else permission_error();
	}

}
