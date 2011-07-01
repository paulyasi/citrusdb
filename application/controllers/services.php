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
	}
	
	/*
	 * ------------------------------------------------------------------------
	 *  Customer overview of everything
	 * ------------------------------------------------------------------------
	 */
	public function index()
	{

		echo "blah";
		// check permissions
		$permission = $this->module_model->permission($this->user, 'services');
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

			$data['categories'] = $this->service_model->service_categories($this->account_number);
			$this->load->view('services/heading', $data);

			// output the list of services
			$data['services'] = $this->service_model->list_services($this->account_number);
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

			$this->load->view('header_with_sidebar');

			// get the customer title info, name and company
			$data = $this->customer_model->title($this->account_number);
			$this->load->view('customer_in_sidebar', $data);

			$this->load->view('moduletabs');

			$this->load->model('ticket_model');
			$this->load->view('messagetabs');

			$this->load->view('buttonbar');

			$data['categories'] = $this->service_model->service_categories($this->account_number);
			$this->load->view('services/heading', $data);

			// output the list of services
			$data['services'] = $this->service_model->list_services($this->account_number, $category);
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


	public function edit($userserviceid)
	{
		$this->load->view('header_with_sidebar');

		// get the customer title info, name and company
		$data = $this->customer_model->title($this->account_number);
		$this->load->view('customer_in_sidebar', $data);

		$this->load->view('moduletabs');

		$this->load->model('ticket_model');
		$this->load->view('messagetabs');

		$this->load->view('buttonbar');

		$data['userserviceid'] = $userserviceid;
		$this->load->view('services/edit', $data);	
		
		// the history listing tabs
		$this->load->view('historyframe_tabs');	

		// show html footer
		$this->load->view('html_footer');
	}

	public function save()
	{
		$userserviceid = $this->input->post['userserviceid'];
		$optionstable = $this->input->post['optionstable'];
		$fieldlist = $this->input->post['fieldlist'];

		$fieldlist = substr($fieldlist, 1); 

		// loop through post_vars associative/hash to get field values
		$array_fieldlist = explode(",",$fieldlist);

		// initialize fieldvalue variable
		$fieldvalues = "";

		foreach ($array_fieldlist as $myfield) {
			$fieldvalues .= ',\'' . $this->input->post($myfield) . '\'';
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
		$userserviceid = $this->input->post['userserviceid'];
		$usage_multiple = $this->input->post['usage_multiple'];

		$this->service_module->change_usage($userservicesid, $usage_multiple);
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
	public function changebilling()
	{
		$userserviceid = $this->input->post['userserviceid'];
		$billing_id = $this->input->post['billing_id'];

		$this->service_module->change_billing($userservicesid, $usage_multiple);
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
			"VALUES ('$account_number', '$tax_rate_id','$customer_tax_id','$expdate')";
		$result = $DB->Execute($query) or die ("$l_queryfailed");

		// redirect back to the service index
		print "<script language=\"JavaScript\">window.location.href = ".
			"\"index.php?load=services&type=module\";</script>";
	}


	public function savenottaxexempt() 
	{
		// make the customer tax not-exempt
		$query = "DELETE FROM tax_exempt WHERE tax_rate_id = '$tax_rate_id' ".
			"AND account_number = '$account_number'";
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
	public function servicetype() 
	{
		$userserviceid = $this->input->post['userserviceid'];
		$master_service_id = $this->input->post['master_service_id'];

		$this->service_model->change_servicetype($userserviceid, $master_service_id);

		// log an entry for a create and delete of the service as part of the change
		log_activity($this->user,$this->account_number,
				'create','service',$new_user_service_id,'success');
		log_activity($this->user,$this->account_number,
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
			$this->load->view('module_header');

			// show the services available to add to this customer
			$data['showall'] = $showall;
			$this->load->view('services/create', $data);	

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


	/*
	 * ------------------------------------------------------------------------
	 *  take input from the service add_options_form and make a new service
	 *  with those attributes
	 * ------------------------------------------------------------------------
	 */
	public function add_service()
	{
		$this->load->model('ticket_model');

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
		// load the module header common to all module views
		$this->load->view('module_header');

		// load the add service options view
		$data['serviceid'] = $serviceid;
		$data['detail1'] = $detail1;
		$this->load->view('services/add_options_form', $data);

		// the history listing tabs
		$this->load->view('historyframe_tabs');	

		// show html footer
		$this->load->view('html_footer');
	}

	public function delete()
	{
		$userserviceid = $this->input->post['userserviceid'];

		// figure out the signup anniversary removal date
		$query = "SELECT signup_date FROM customer 
			WHERE account_number = '$this->account_number'";
		$result = $this->db->query($query) or die ("$l_queryfailed");
		$myresult = $result->row_array();
		$signup_date = $myresult['signup_date'];
		list($myyear, $mymonth, $myday) = split('-', $signup_date);
		$removal_date  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("$myday"), date("Y")));
		$today  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
		if ($removal_date <= $today) {
			$removal_date  = date("Y-m-d", mktime(0, 0, 0, date("m")+1  , date("$myday"), date("Y")));
		}

		// prompt them to ask if they are sure they want to delete the service
		print "<br><br>";
		print "<h4>$l_areyousuredelete: $servicedescription</h4>";
		print "<table cellpadding=15 cellspacing=0 border=0 width=720>".
			"<td align=right>";

		// if they hit yes, this will sent them into the delete.php file
		// and remove the service

		print "<form style=\"margin-bottom:0;\" action=\"index.php\" method=post>".
			"<input type=hidden name=optionstable value=$optionstable>";
		print "<input type=hidden name=userserviceid value=$userserviceid>";
		print "<input type=hidden name=load value=services>";
		print "<input type=hidden name=type value=module>";
		print "<input type=hidden name=delete value=on>";
		print "<input name=deletenow type=submit value=\" $l_deleteservice_removeuser $removal_date\" ".
			"class=smallbutton></form></td>";

		print "<td align=left><form style=\"margin-bottom:0;\" action=\"index.php\" method=post>".
			"<input type=hidden name=optionstable value=$optionstable>";
		print "<input type=hidden name=userserviceid value=$userserviceid>";
		print "<input type=hidden name=load value=services>";
		print "<input type=hidden name=type value=module>";
		print "<input type=hidden name=delete value=on>";
		print "<input name=deletetoday type=submit value=\" $l_deleteservice_removetoday \" ".
			"class=smallbutton></form></td>";   

		// if they hit yes without automatic removal, this will sent them into the delete.php file
		// and remove the service


		print "<td align=left><form style=\"margin-bottom:0;\" action=\"index.php\" method=post>".
			"<input type=hidden name=optionstable value=$optionstable>";
		print "<input type=hidden name=userserviceid value=$userserviceid>";
		print "<input type=hidden name=load value=services>";
		print "<input type=hidden name=type value=module>";
		print "<input type=hidden name=delete value=on>";
		print "<input name=deletenoauto type=submit value=\" $l_deleteservice_activeuser \" ".
			"class=smallbutton></form></td>"; 

		// if they hit no, send them back to the service edit screen

		print "<td align=left><form style=\"margin-bottom:0;\" ".
			"action=\"index.php\" method=post>";
		print "<input name=done type=submit value=\" $l_no \" class=smallbutton>";
		print "<input type=hidden name=load value=services>";        
		print "<input type=hidden name=type value=module>";
		print "</form></td></table>";
		print "</blockquote>";

	}

	public function fieldassets()
	{
		if ($pallow_remove) {
			include('./modules/customer/fieldassets');
		} else permission_error();        
	}

	public function history()
	{
		if ($pallow_remove) {
			include('./modules/customer/history');
		} else permission_error();
	}

	public function vendor()
	{
		if ($pallow_remove) {
			include('./modules/customer/vendor');
		} else permission_error();
	}

}

/* End of file customer */
/* Location: ./application/controllers/customer.php */
