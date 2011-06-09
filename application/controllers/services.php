<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Services extends App_Controller {

	function __construct()
	{
		parent::__construct();	
		$this->load->model('service_model');
		$this->load->model('module_model');
		$this->load->model('customer_model');
		$this->load->model('billing_model');
	}
	
	/**
	 * Customer overview of everything
	 */
	public function index()
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
	
	public function edit()
	{
		if ($pallow_modify) {
    	  include('./modules/customer/edit.php');
    	}  else permission_error();
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


	public function add_service()
	{
		// GET Variables
		//$this->id = $this->input->post('id');
		$addnow = $this->input->post('addnow');
		$addbutton = $this->input->post('addbutton');
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

		foreach ($base->input as $mykey => $myvalue) {
			foreach ($array_fieldlist as $myfield) {
				// print "$mykey<br>";
				if ($myfield == $mykey) {
					$fieldvalues .= ',\'' . $myvalue . '\'';
				}
			}
		}

		$fieldvalues = substr($fieldvalues, 1);

		// make the creation date YYYY-MM-DD HOUR:MIN:SEC
		$mydate = date("Y-m-d H:i:s");

		// if there is a create_billing request, create a billing record first
		if ($create_billing) {
			$billing_id = create_billing_record($create_billing, $account_number, $DB);
		}

		$user_service_id = create_service($account_number, $serviceid, $billing_id,
				$usagemultiple, $options_table_name,
				$fieldlist, $fieldvalues);


		// insert any linked_services into the user_services table
		$query = "SELECT * FROM linked_services WHERE linkfrom = $serviceid";
		$result = $DB->Execute($query) or die ("$l_queryfailed");
		while ($myresult = $result->FetchRow()) {
			$linkto = $myresult['linkto'];

			create_service($account_number, $linkto, $billing_id,
					$usagemultiple, NULL, NULL, NULL);
		}	

		// add an entry to the customer_history to the activate_notify user
		service_message('added', $account_number, $serviceid,
				$user_service_id, NULL, NULL);

		// add a log entry that this service was added
		log_activity($DB,$user,$account_number,'create','service',$user_service_id,'success');

		print "$l_addedservice<p>";
		print "<script language=\"JavaScript\">window.location.href = ".
			"\"index.php?load=services&type=module\";</script>";
		/*-------------------------------------------------------------------*/
	}


	public function add_options()
	{
		// list the service options after they clicked on the add button.
		echo "<a href=\"index.php?load=services&type=module\">[ $l_undochanges ]</a>";
		$query = "SELECT * FROM master_services ms ". 
			"LEFT JOIN general g ON g.id = ms.organization_id ". 
			"WHERE ms.id = $serviceid";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("$l_queryfailed");
		$myresult = $result->fields;	
		$servicename = $myresult['service_description'];
		$options_table_name = $myresult['options_table'];
		$usage_label = $myresult['usage_label'];
		$service_org_id = $myresult['organization_id'];
		$service_org_name = $myresult['org_name'];

		echo "<script language=javascript>".
			"function popupURL(url,value) { ".
			"newurl = \"url + value\";".
			"window.open(\"newurl\");".
			"}".
			"</script>";	

		print "<h4>$l_addingservice: $servicename ($service_org_name)</h4>".
			"<form action=\"index.php\" name=\"AddService\" method=post>".
			"<table width=720 cellpadding=5 cellspacing=1 border=0>\n";
		print "<input type=hidden name=load value=services>\n";
		print "<input type=hidden name=type value=module>\n";
		print "<input type=hidden name=create value=on>\n";
		print "<input type=hidden name=options_table_name value=$options_table_name>".
			"<input type=hidden name=serviceid value=$serviceid>";

		// check that there is an options_table_name, if so, show the options choices

		if ($options_table_name <> '') {
			// ADODB MetaColumns function gives a field object for table columns
			$fields = $DB->MetaColumns($options_table_name);
			$i = 0;
			foreach($fields as $v) {
				//echo "Name: $v->name ";
				//echo "Type: $v->type ";

				$fieldname = $v->name;
				$fieldflags = $v->type;

				// Added the default value from the database schema
				// so we can use it when needed - by RTC
				//$default_value = $v->default_value;
				// removed the above line since it appears default_value is not returned
				if ($detail1 <> '' AND $i == 2) {
					// if the first attbibute has a prefilled in value, use that as default value
					$default_value = $detail1;
				} else {
					$default_value = '';
				}

				//echo "Default: $default_value<br>";

				if ($fieldname <> "id" AND $fieldname <> "user_services") {
					if ($fieldflags == "enum") {
						echo "<td bgcolor=\"ccccdd\"width=180><b>$fieldname</b></td>".
							"<td bgcolor=\"#ddddee\">";

						// print all the items listed in the enum
						enum_select($options_table_name, $fieldname, $default_value);

						echo "</select></td><tr>\n";
					} elseif ($fieldname == "description"){
						echo "<td bgcolor=\"ccccdd\"width=180><b>$fieldname</b></td>".
							"<td bgcolor=\"#ddddee\"><input size=40 maxlength=44 type=text name=\"$fieldname\" ".
							"value=\"$myresult[$i]\">";
						echo "</td><tr>";
					} else {
						// print fields for each attributes
						echo "<td bgcolor=\"ccccdd\"width=180>".
							"<b>$fieldname</b></td>".
							"<td bgcolor=\"#ddddee\">".
							"<input type=text name=$fieldname id=\"$fieldname\" ".
							"value=\"$default_value\">\n";
						echo "</td><tr>\n";
					}
					$fieldlist .= ',' . $fieldname;
				}
				$i++;
			} //endforeach

			print "<input type=hidden name=fieldlist value=$fieldlist>";
		} //endwhile

		// print the usage_multiple entry field
		// if there is a usage label, use it instead of the generic name
		if($usage_label) {
			print "<tr><td bgcolor=\"#ccccdd\"><b>$usage_label</b></td>";
		} else {
			print "<tr><td bgcolor=\"#ccccdd\"><b>$l_usagemultiple</b></td>";
		}

		print"<td bgcolor=\"#ddddee\"><input type=text name=\"usagemultiple\" ".
			"value=\"1\"></td><tr>";

		// print the billing id choices available to this service type
		// if no billing id choices match, then ask them to create a billing
		// record for this service with a matching billing org

		print "<td bgcolor=\"#ddaaee\"><b>$l_organizationname</b></td>".
			"<td bgcolor=\"#ddaaee\">";

		$query = "SELECT b.id,bt.name,g.org_name FROM billing b ".
			"LEFT JOIN general g ON g.id = b.organization_id ".
			"LEFT JOIN billing_types bt ON b.billing_type = bt.id  ".
			"WHERE b.account_number = '$account_number' AND ".
			"g.id = '$service_org_id'";

		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("$l_queryfailed");
		if (!$result || $result->RowCount() < 1){
			echo "<b>$l_willcreatebillingrecord $service_org_name</b>".
				"<input type=hidden name=create_billing value=$service_org_id>";	
		} else {
			echo "<select name=billing_id>";
			while ($myresult = $result->FetchRow()) {
				$billing_id = $myresult['id'];
				$org_name = $myresult['org_name'];
				$billing_type = $myresult['name'];
				print "<option value=$billing_id>$billing_id ($org_name) $billing_type</option>";
			}
		}
		echo "</select></td><tr>";

		print "<td></td><td><input name=addnow type=submit value=\"$l_add\" ".
			"class=smallbutton></td></table></form>";

	}

	public function delete()
	{
		if ($pallow_remove) {
			include('./modules/customer/delete.php');
		} else permission_error();
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
