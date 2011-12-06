<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Services extends App_Controller {

	function __construct()
	{
		parent::__construct();	
		$this->load->model('service_model');
		$this->load->model('schema_model');
		$this->load->model('module_model');
		$this->load->model('customer_model');
		$this->load->model('billing_model');
		$this->load->model('user_model');
		$this->load->model('support_model');
	}
	
	
	/*
	 * ------------------------------------------------------------------------
	 *  Customer overview of everything
	 * ------------------------------------------------------------------------
	 */
	public function index()
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'services');
		if ($permission['view'])
		{
			// load the module header common to all module views
			$this->load->view('module_header_view');
			
			$data['categories'] = $this->service_model->service_categories($this->account_number);
			$this->load->view('services/heading_view', $data);

			// output the list of services
			$data['services'] = $this->service_model->list_services($this->account_number);
			$this->load->view('services/index_view', $data);

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
	 *  Customer overview of a specified category
	 * ------------------------------------------------------------------------
	 */
	public function category($category)
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'services');
		if ($permission['view'])
		{
			// load the module header common to all module views
			$this->load->view('module_header_view');
			
			$data['categories'] = $this->service_model->service_categories($this->account_number);
			$this->load->view('services/heading_view', $data);

			// output the list of services
			$data['services'] = $this->service_model->list_services($this->account_number, $category);
			$this->load->view('services/index_view', $data);

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
	 *  show the edit service screen
	 * ------------------------------------------------------------------------
	 */
	public function edit($userserviceid)
	{
		// load the module header common to all module views
		$this->load->view('module_header_view');

		// load the date helper for use when printing service start/end dates
		$this->load->helper('date');

		$data['userserviceid'] = $userserviceid;
		
		// get the privileges for this citrus user
		$data['privileges'] = $this->user_model->user_privileges($this->user);

		// get the organization info for the service
		$myorgresult = $this->service_model->org_and_options($userserviceid);
		$data['service_org_id'] = $myorgresult['organization_id'];
		$data['service_org_name'] = $myorgresult['org_name'];
		$data['optionstable'] = $myorgresult['options_table'];
		$data['master_service_id'] = $myorgresult['master_service_id'];
		$data['servicedescription'] = $myorgresult['service_description'];
		$data['creationdate'] = humandate($myorgresult['start_datetime']);
		$data['enddate'] = humandate($myorgresult['end_datetime']);
		$data['removed'] = $myorgresult['removed'];
		$data['support_notify'] = $myorgresult['support_notify'];
		$data['usage_multiple'] = $myorgresult['usage_multiple'];
		$data['usage_label'] = $myorgresult['usage_label'];
		$data['billing_id'] = $myorgresult['billing_id'];

		// check if the service has been removed
		$data['removedstatus'] = $this->service_model->removed_status($userserviceid);

		// get the field inventory assigned to this service
		$data['fieldinventory'] = $this->service_model->field_inventory($userserviceid);

		// get a list of field assets that can be assigned
		$data['field_asset_result'] = $this->service_model->get_field_assets($userserviceid);

		// get a list of the billing types that could be assigned to this service
		$data['org_billing_types'] = $this->billing_model->org_alternates(
				$this->account_number, 
				$data['service_org_id']);

		// get customer history for this service
		$data['servicehistory'] = $this->support_model->service_history($userserviceid);

		// show the edit view
		$this->load->view('services/edit_view', $data);	

		// the history listing tabs
		$this->load->view('historyframe_tabs_view');	

		// show html footer
		$this->load->view('html_footer_view');
	}

	public function save()
	{
		$userserviceid = $this->input->post('userserviceid');
		$optionstable = $this->input->post('optionstable');
		$fieldlist = $this->input->post('fieldlist');

		$fieldlist = substr($fieldlist, 1); 

		// loop through post_vars associative/hash to get field values
		$array_fieldlist = explode(",",$fieldlist);

		// initialize fieldvalue variable
		$fieldvalues = "";

		foreach ($array_fieldlist as $myfield) 
		{
			$fieldvalues .= ', ' . $myfield . ' = \'' . $this->input->post($myfield) . '\'';
		}

		$fieldvalues = substr($fieldvalues, 1);

		$this->service_model->save_changes($userserviceid, $optionstable, $fieldvalues);

		// log that this was changed
		$this->log_model->activity($this->user,$this->account_number,
				'edit','service',$userserviceid,'success');  

		redirect('/services');
	}


	/*
	 * ------------------------------------------------------------------------
	 *  take input to change the usage multiple (eg: unit quantity) of this
	 * ------------------------------------------------------------------------
	 */
	public function usage()
	{
		$userserviceid = $this->input->post('userserviceid');
		$usage_multiple = $this->input->post('usage_multiple');

		$this->service_model->change_usage($userserviceid, $usage_multiple);
		// add a log entry that this service was edited
		$this->log_model->activity($this->user,$this->account_number,
				'edit','service',$userserviceid,'success');    

		redirect('/services');
	}

	/*
	 * ------------------------------------------------------------------------
	 *  take input to change the billing id for the service
	 * ------------------------------------------------------------------------
	 */
	public function changebillingid()
	{
		$userserviceid = $this->input->post('userserviceid');
		$billing_id = $this->input->post('billing_id');

		$this->service_model->change_billing($userserviceid, $billing_id);
		// add a log entry that this service was edited
		$this->log_model->activity($this->user,$this->account_number,
				'edit','service',$userserviceid,'success');    

		redirect('/services');
	}


	public function taxexempt() 
	{
		// ask the user for customer tax id, and exempt id expiration date
		print "<a href=\"index.php?load=services&type=module\">[ $l_undochanges ]</a>";
		print "<h4>$l_exempt</h4><form action=\"index.php\" method=post>".
			"<table width=720 cellpadding=5 cellspacing=1 border=0>";
		print "<input type=hidden name=load value=services>";
		print "<input type=hidden name=type value=module>";
		print "<input type=hidden name=edit value=on>";
		print "<input type=hidden name=taxrate value=\"$tax_rate_id\">";
		echo "<td bgcolor=\"ccccdd\"width=180><b>$l_taxexemptid</b></td>".
			"<td bgcolor=\"#ddddee\"><input type=text name=customer_tax_id></td><tr>\n";
		echo "<td bgcolor=\"ccccdd\"width=180><b>$l_expirationdate</b></td>".
			"<td bgcolor=\"#ddddee\"><input type=text name=expdate></td><tr>\n";
		print "<td></td><td><input name=saveexempt type=submit ".
			"value=\"$l_savechanges\" class=smallbutton></td></table></form>";
	}


	public function savetaxexempt() 
	{ 
		// save the tax exempt status information, make the customer tax exempt
		$query = "INSERT INTO tax_exempt ".
			"(account_number, tax_rate_id, customer_tax_id, expdate) ". 
			"VALUES ('$this->account_number', '$tax_rate_id','$customer_tax_id','$expdate')";
		$result = $DB->Execute($query) or die ("$l_queryfailed");

		// redirect back to the service index
		print "<script language=\"JavaScript\">window.location.href = ".
			"\"index.php?load=services&type=module\";</script>";
	}


	public function savenottaxexempt() 
	{
		// make the customer tax not-exempt
		$query = "DELETE FROM tax_exempt WHERE tax_rate_id = '$tax_rate_id' ".
			"AND account_number = '$this->account_number'";
		$result = $DB->Execute($query) or die ("$l_queryfailed");

		// redirect back to the service index
		print "<script language=\"JavaScript\">window.location.href = ".
			"\"index.php?load=services&type=module\";</script>";
	}

	/*
	 * ------------------------------------------------------------------------
	 *  take input to change the service type
	 * ------------------------------------------------------------------------
	 */
	public function changeservicetype() 
	{
		// load the ticket model to save a note about this
		$this->load->model('support_model');

		$userserviceid = $this->input->post('userserviceid');
		$master_service_id = $this->input->post('master_service_id');

		$this->service_model->change_servicetype($userserviceid, $master_service_id);

		// log an entry for a create and delete of the service as part of the change
		$this->log_model->activity($this->user,$this->account_number,
				'create','service',$new_user_service_id,'success');
		$this->log_model->activity($this->user,$this->account_number,
				'delete','service',$userserviceid,'success');  

		redirect('/services');
	}


	public function create($showall = NULL)
	{

		// check permissions
		$permission = $this->module_model->permission($this->user, 'services');
		if ($permission['create'])
		{
			// load the module header common to all module views
			$this->load->view('module_header_view');

			// get the privileges for this citrus user
			$data['privileges'] = $this->user_model->user_privileges($this->user);
			if (($data['privileges']['manager'] == 'y') OR ($data['privileges']['admin'] == 'y'))
			{
				$data['showall_permission'] = TRUE;
			}
			else
			{
				$data['showall_permission'] = FALSE;
			}

			// show the services available to add to this customer
			if ($showall) 
			{ 
				$data['showall'] = 'y'; 
			}
			else
			{
				$data['showall'] = 'n'; 
			}


			// get the default billing organization id for this account
			$org_id = $this->billing_model->get_organization_id($this->account_number);

			$this->load->model('admin_model');
			// get list of service categories if they have asked to show the all
			if ($data['showall'] == 'y' AND $data['showall_permission'] == TRUE)
			{
				$data['service_categories'] = $this->admin_model->get_service_categories();
				$data['master_service_list'] = $this->service_model->get_master_service_list();
			}
			else
			{
				$data['service_categories'] = $this->admin_model->get_org_service_categories($org_id);
				$data['master_service_list'] = $this->service_model->get_org_master_service_list($org_id);
			}

			// load the create service view
			$this->load->view('services/create_view', $data);	

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
	 *  take input from the service add_options_form and make a new service
	 *  with those attributes
	 * ------------------------------------------------------------------------
	 */
	public function add_service()
	{
		$this->load->model('support_model');

		// GET Variables
		//$this->id = $this->input->post('id');
		$serviceid = $this->input->post('serviceid');
		$usagemultiple = $this->input->post('usagemultiple');
		$options_table_name = $this->input->post('options_table_name');
		$fieldlist = $this->input->post('fieldlist');
		$billing_id = $this->input->post('billing_id');
		$create_billing = $this->input->post('create_billing');
		$detail1 = $this->input->post('detail1');

		// add the services to the user_services table and the options table
		$fieldlist = substr($fieldlist, 1); 

		// loop through post_vars associative/hash to get field values
		$array_fieldlist = explode(",",$fieldlist);

		// initialize fieldvalue variable
		$fieldvalues = "";

		foreach ($array_fieldlist as $myfield) {
			$fieldvalues .= ',\'' . $this->input->post($myfield) . '\'';
		}

		$fieldvalues = substr($fieldvalues, 1);

		// make the creation date YYYY-MM-DD HOUR:MIN:SEC
		$mydate = date("Y-m-d H:i:s");

		// if there is a create_billing request, create a billing record first
		if ($create_billing) {
			$billing_id = $this->billing_model->create_record($create_billing, 
					$this->account_number);
		}

		$user_service_id = $this->service_model->create_service($this->account_number, 
				$serviceid, $billing_id,
				$usagemultiple, $options_table_name,
				$fieldlist, $fieldvalues);


		// insert any linked_services into the user_services table
		$query = "SELECT * FROM linked_services WHERE linkfrom = $serviceid";
		$result = $this->db->query($query) or die ("$l_queryfailed");
		foreach($result->result_array() AS $myresult) {
			$linkto = $myresult['linkto'];

			$this->service_model->create_service($this->account_number, $linkto, $billing_id,
					$usagemultiple, NULL, NULL, NULL);
		}	

		// add an entry to the customer_history to the activate_notify user
		$this->service_model->service_message('added', $this->account_number, $serviceid,
				$user_service_id, NULL, NULL);

		// add a log entry that this service was added
		$this->log_model->activity($this->user,$this->account_number,'create',
				'service',$user_service_id,'success');

		print "$l_addedservice<p>";

		// go back to the customer's services listing
		redirect('/services');
	}

	/*
	 * ------------------------------------------------------------------------
	 *  first step when adding a new service is to add the options/attributes
	 *  serviceid
	 *  detail1
	 * ------------------------------------------------------------------------
	 */
	public function add_options($serviceid, $detail1 = NULL)
	{
		// show the header for module views
		$this->load->view('module_header_view');

		// load the add service options view
		$data['serviceid'] = $serviceid;
		$data['detail1'] = $detail1;

		// get the service organization info to show when adding the service
		$myresult = $this->service_model->service_with_org($serviceid);
		$data['servicename'] = $myresult['service_description'];
		$data['options_table_name'] = $myresult['options_table'];
		$data['usage_label'] = $myresult['usage_label'];
		$data['service_org_id'] = $myresult['organization_id'];
		$data['service_org_name'] = $myresult['org_name'];

		// get a list of the billing types that could be assigned to this service
		$data['org_billing_types'] = $this->billing_model->org_alternates(
				$this->account_number, 
				$data['service_org_id']);

		// load the add service options form view
		$this->load->view('services/add_options_form_view', $data);

		// the history listing tabs
		$this->load->view('historyframe_tabs_view');	

		// show html footer
		$this->load->view('html_footer_view');
	}

	/*
	 * ------------------------------------------------------------------------
	 *  prompt the user if they are sure they want to delete the service
	 * ------------------------------------------------------------------------
	 */
	public function delete()
	{
		// figure out the signup anniversary removal date
		$removal_date = $this->customer_model->get_anniversary_removal_date(
				$this->account_number);

		// show the header for module views
		$this->load->view('module_header_view');

		// delete service prompt
		$data['userserviceid'] = $this->input->post('userserviceid');
		$data['servicedescription'] = $this->input->post('servicedescription');
		$data['removal_date'] = $removal_date;
		$this->load->view('services/delete_prompt', $data);

		// the history listing tabs
		$this->load->view('historyframe_tabs_view');	

		// show html footer
		$this->load->view('html_footer_view');
	}


	/*
	 * -------------------------------------------------------------------------
	 *  delete the service on normal removal date
	 * -------------------------------------------------------------------------
	 */
	public function deletenow($userserviceid)
	{
		// load the ticket model so that the delete service can leave a note
		$this->load->model('support_model');

		// figure out the signup anniversary removal date
		$removal_date = $this->customer_model->get_anniversary_removal_date(
				$this->account_number);

		// delete the service and do other notifications
		$this->service_model->delete_service($userserviceid, 'removed', $removal_date);

		// add a log entry that this service was deleted
		$this->log_model->activity($this->user,$this->account_number,'delete','service',
				$userserviceid,'success');	

		redirect('/services');
	}	


	/*
	 * -------------------------------------------------------------------------
	 *  delete the service with removal date of today
	 * -------------------------------------------------------------------------
	 */
	public function deletetoday($userserviceid)
	{
		// load the ticket model so that the delete service can leave a note
		$this->load->model('support_model');

		// delete the service today, not on billing anniversary
		$today  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));

		$this->service_model->delete_service($userserviceid, 'removed', $today);

		// add to log that the service was removed
		$this->log_model->activity($this->user,$this->account_number,'delete',
				'service', $userserviceid,'success');	  

		redirect('/services');
	}	


	/*
	 * -------------------------------------------------------------------------
	 *  delete the service without automatic removal date set
	 * -------------------------------------------------------------------------
	 */
	public function deletenoauto($userserviceid)
	{
		// load the ticket model so that the delete service can leave a note
		$this->load->model('support_model');

		// delete the service without an automatic removal dateand do other notifications
		$this->service_model->delete_service($userserviceid, 'removed', '');

		redirect('/services');
	}	


	public function shipfieldassets()
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'services');
		if ($permission['view'])
		{
			// load the module header common to all module views
			$this->load->view('module_header_view');

			// show the services history for this customer
			$data['userserviceid'] = $this->input->post('userserviceid');
			$data['master_field_assets_id'] = $this->input->post('master_field_assets_id');
			$data['description'] = $this->service_model->field_asset_description(
					$this->input->post('master_field_assets_id'));
			$this->load->view('services/shipfieldassets_view', $data);	

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

	function assignfieldasset() 
	{
		// load the settings model to get the default shipping group	
		$this->load->model('settings_model');
		$this->load->model('support_model');

		// GET Variables
		$userserviceid = $this->input->post('userserviceid');
		$master_field_assets_id = $this->input->post('master_field_assets_id');
		$serial_number = $this->input->post('serial_number');
		$sale_type = $this->input->post('sale_type');
		$tracking_number = $this->input->post('tracking_number');
		$shipping_date = $this->input->post('shipping_date');

		// assign the field asset to this service
		$this->service_model->assign_field_asset($master_field_assets_id, $serial_number, 
				$sale_type, $tracking_number, $shipping_date, $userserviceid);

		// get the description of the field asset just inserted
		$description = $this->service_model->field_asset_description($master_field_assets_id);

		// get the default shipping group
		$default_shipping_group = $this->settings_model->get_default_shipping_group();

		// leave a note that the item was assigned
		$status = "not done";
		$description = lang('shipped')." $description, ".lang('trackingnumber').": $tracking_number";
		$trackinglink = $this->config->item('tracking_url')."$tracking_number";

		$this->support_model->create_ticket($this->user, $default_shipping_group, 
				$this->account_number, $status, $description, lang('trackpackage'), 
				$trackinglink, NULL, $userserviceid);

		// redirect back to the service edit screen, now showing the assigned inventory listed there
		echo "assigned";
		redirect('services/edit/'.$userserviceid);
	}


	function returnfieldasset($item_id, $userserviceid)
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'services');
		if ($permission['view'])
		{
			// load the module header common to all module views
			$this->load->view('module_header_view');

			$data['item_id'] = $item_id;
			$data['userserviceid'] = $userserviceid;
			$this->load->view('services/returnfieldassets_view', $data);	

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

	function savereturnfieldasset()
	{
		// load the settings model to get the default shipping group	
		$this->load->model('settings_model');
		$this->load->model('support_model');

		// GET Variables
		$item_id = $this->input->post('item_id');
		$userserviceid = $this->input->post('userserviceid');
		$return = $this->input->post('return');
		$returned = $this->input->post('returned');
		$return_date = $this->input->post('return_date');
		$return_notes = $this->input->post('return_notes');

		// mark the field asset as returned
		$this->service_model->return_field_asset($return_date, $return_notes, $item_id);

		$description = $this->service_model->field_asset_item_description($item_id);

		$default_billing_group = $this->settings_model->get_default_billing_group();

		// leave a note to the billing group that the item was returned
		$status = "not done";
		$description = lang('returned')." $description $return_date $return_notes";
		$this->support_model->create_ticket($this->user, $default_billing_group, $this->account_number, $status, $description, NULL, NULL, NULL, $userserviceid);

		// redirect back to the service edit screen, now showing the returned inventory listed there
		echo "returned";
		redirect('services/edit/'.$userserviceid);
	} 

	public function history()
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'services');
		if ($permission['view'])
		{
			// load the module header common to all module views
			$this->load->view('module_header_view');

			// show the services history for this customer
			$data['services'] = $this->service_model->list_history($this->account_number);
			$this->load->view('services/history_view', $data);	

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


	function editremovaldate($serviceid, $removaldate) 
	{
		// load the module header common to all module views
		$this->load->view('module_header_view');

		$data['serviceid'] = $serviceid;
		$data['removaldate'] = $removaldate;
		$this->load->view('services/edit_removal_date_view', $data);	

		// the history listing tabs
		$this->load->view('historyframe_tabs_view');	

		// show html footer
		$this->load->view('html_footer_view');
	}

	function saveremovaldate() 
	{
		// TODO: check that the new date entered is blank or today or in the future, 
		// not the past
		// allow blank to be inserted that will make it so there is no removal, eg NULL?

		$removaldate = $this->input->post('removaldate');
		$serviceid = $this->input->post('serviceid');

		$this->service_model->update_removal_date($serviceid, $removaldate);

		redirect('/services');
	}

	// print the history
	public function vendor($userserviceid)
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'services');
		if ($permission['create'])
		{
			// load the date helper for use when printing service start/end dates
			$this->load->helper('date');

			// load the module header common to all module views
			$this->load->view('module_header_view');

			$data['userserviceid'] = $userserviceid;

			// show the vendor history info
			$myorgresult = $this->service_model->org_and_options($userserviceid);
			$data['service_org_id'] = $myorgresult['organization_id'];
			$data['service_org_name'] = $myorgresult['org_name'];
			$data['optionstable'] = $myorgresult['options_table'];
			$data['servicedescription'] = $myorgresult['service_description'];
			$data['creationdate'] = humandate($myorgresult['start_datetime']);
			$data['enddate'] = humandate($myorgresult['end_datetime']);
			$data['removed'] = $myorgresult['removed'];

			if ($data['optionstable'] <> '') {
				$myoptions = $this->service_model->options_attributes($userserviceid, $data['optionstable']);
				$data['optionsdetails'] = $myoptions[2];
				$data['optionsdetails2'] = $myoptions[3];
			}

			$data['vendor_history'] = $this->service_model->vendor_history($userserviceid);
			$data['vendor_names'] = $this->service_model->vendor_names();

			$this->load->view('services/vendor_history_view', $data);	

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


	public function savevendor()
	{
		// GET Variables
		$entry_type = $this->input->post('entry_type');
		$entry_date = $this->input->post('entry_date');
		$vendor_name = $this->input->post('vendor_name');
		$vendor_bill_id = $this->input->post('vendor_bill_id');
		$vendor_cost = $this->input->post('vendor_cost');
		$vendor_tax = $this->input->post('vendor_tax');
		$vendor_item_id = $this->input->post('vendor_item_id');
		$vendor_invoice_number = $this->input->post('vendor_invoice_number');
		$vendor_from_date = $this->input->post('vendor_from_date');
		$vendor_to_date = $this->input->post('vendor_to_date');
		$userserviceid = $this->input->post('userserviceid');

		$billing_status = $this->service_model->get_status_and_price($userserviceid);
		$billed_amount = $billing_status['billed_amount'];
		$userbillingid = $billing_status['billing_id'];

		$account_status = $this->billing_model->billingstatus($userbillingid);

		// insert the new vendor history line item
		$this->service_model->add_vendor_history($entry_type, $entry_date, $vendor_name, 
				$vendor_bill_id, $vendor_cost, $vendor_tax, $vendor_item_id, 
				$vendor_invoice_number, $vendor_from_date, $vendor_to_date, 
				$userserviceid, $account_status, $billing_id);

		// redirect back to the vendory history, now showing new entry
		echo "entered";
		redirect('/services/vendor/'.$userserviceid);

	}

}

/* End of file services */
/* Location: ./application/controllers/services.php */
