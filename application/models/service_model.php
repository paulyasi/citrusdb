<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Service_model extends CI_Model
{

	function __construct()
	{
		    parent::__construct();
	}

	/*
	 * ------------------------------------------------------------------------
	 *  create service record
	 * ------------------------------------------------------------------------
	 */
	function create_service($account_number, $master_service_id, $billing_id,
			$usage_multiple, $options_table_name,
			$attribute_fieldname_string,
			$attribute_fieldvalue_string)
	{
		$mydate = date("Y-m-d H:i:s");

		// insert the new service into the user_services table
		$query = "INSERT into user_services (account_number, master_service_id, ".
			"billing_id, start_datetime, salesperson, usage_multiple) ".
			"VALUES ('$account_number', '$master_service_id', '$billing_id',".
			"'$mydate', '$this->user', '$usage_multiple')";
		$result = $this->db->query($query) or die ("create_service $l_queryfailed");

		// use the mysql_insert_id command to get the ID of the row the insert
		// was to for the options table query
		$myinsertid = $this->db->insert_id();

		// insert values into the options table
		// skip this if there is no options_table_name for this service
		if ($options_table_name <> '') {
			$query = "INSERT into $options_table_name ".
				"(user_services,$attribute_fieldname_string) ".
				"VALUES ($myinsertid,$attribute_fieldvalue_string)";
			$result = $this->db->query($query) or die ("create_service $query");
		}

		return $myinsertid;

	}


	/*
	 * ------------------------------------------------------------------------
	 *  Save changes to edited service
	 * ------------------------------------------------------------------------
	 */
	function save_changes($userserviceid, $optionstable, $fieldvalues)
	{
		$query = "UPDATE $optionstable SET $fieldvalues ".
			"WHERE user_services = $userserviceid";
		$result = $this->db->query($query) or die ("save_changes query failed");
	}	

	/*
	 * ------------------------------------------------------------------------
	 *  Change the usage multiple (eg: unit quantity) for this service
	 * ------------------------------------------------------------------------
	 */
	function change_usage($userserviceid, $usage_multiple)
	{
		// update the database if they changed the usage_multiple
		$query = "UPDATE user_services SET usage_multiple = $usage_multiple ".
			"WHERE id = $userserviceid";
		$result = $this->db->query($query) or die ("$l_queryfailed");
	}


	/*
	 * ------------------------------------------------------------------------
	 *  Change the billing id for this service
	 * ------------------------------------------------------------------------
	 */
	function change_billing($userserviceid, $billing_id) 
	{
		// update the database if they changed the billing ID
		$query = "UPDATE user_services SET billing_id = $billing_id ".
			"WHERE id = $userserviceid";
		$result = $this->db->query($query) or die ("$l_queryfailed");
	
	}


	/*
	 * ------------------------------------------------------------------------
	 *  Change the service type and move attributes and notes to new service
	 * ------------------------------------------------------------------------
	 */
	function change_servicetype($userserviceid, $master_service_id)
	{
		// get the old master service id
		$query = "SELECT billing_id, usage_multiple, master_service_id ".
			"FROM user_services ".
			"WHERE id = $userserviceid";
		$result = $this->db->query($query) or die ("$l_queryfailed");
		$oldmasterresult = $result->row_array();
		$old_master_service_id = $oldmasterresult['master_service_id'];
		$billing_id = $oldmasterresult['billing_id'];
		$usage_multiple = $oldmasterresult['usage_multiple'];

		// get the name of the options table, always the same
		$query = "SELECT options_table FROM master_services ".
			"WHERE id = $master_service_id";
		$result = $this->db->query($query) or die ("$l_queryfailed");
		$master_service_results = $result->row_array();
		$options_table_name = $master_service_results['options_table'];

		// get the field names and values from the options_table
		$fields = $this->schema_model->columns($this->db->database, $options_table_name);
		
		// initialize fieldlist
		$fieldlist = '';
		$fieldvalues = '';
		
		foreach($fields->result() as $f) {
			$fieldname = $f->COLUMN_NAME;
			if ($fieldname <> "id" AND $fieldname <> "user_services") {
				$fieldlist .= ',' . $fieldname;
			}
		}
		$fieldlist = substr($fieldlist, 1);

		// get the values out of those fields from the options table
		$query = "SELECT $fieldlist from $options_table_name ".
			"WHERE user_services = $userserviceid";
		$result = $this->db->query($query) or die ("$l_queryfailed");
		$options_table_result = $result->row_array();

		$array_fieldlist = explode(",",$fieldlist);
		foreach($array_fieldlist as $myfield) {
			$myvalue = $options_table_result["$myfield"];
			$fieldvalues .= ',\'' . $myvalue . '\'';
		}  

		$fieldvalues = substr($fieldvalues, 1);


		// TODO: make a new service with the new information from above
		$new_user_service_id = $this->create_service($this->account_number, $master_service_id,
				$billing_id, $usage_multiple, 
				$options_table_name,
				$fieldlist, $fieldvalues);

		// delete the old service but with no removal date and no delete message
		$this->delete_service($userserviceid, 'change', '');

		// add an entry to the customer_history to modify_notify for the new service
		$this->service_message('change', $this->account_number,
				$old_master_service_id, $userserviceid, 
				$master_service_id, $new_user_service_id);

		// move the notes from the old service to the new service
		$query = "UPDATE customer_history ".
			"SET user_services_id = '$new_user_service_id' ".
			"WHERE user_services_id = '$userserviceid'";
		$updateresult = $this->db->query($query) or die ("$query $l_queryfailed");


	}
	

	/*
	 * ------------------------------------------------------------------------
	 *  return service options table name and organization info
	 * ------------------------------------------------------------------------
	 */
	function service_with_org($serviceid) 
	{
		$query = "SELECT * FROM master_services ms ". 
			"LEFT JOIN general g ON g.id = ms.organization_id ". 
			"WHERE ms.id = $serviceid";
		$result = $this->db->query($query) or die ("query failed");
		$myresult = $result->row_array();	

		return $myresult;
	}


	function org_and_options($userserviceid)
	{
		// get the organization_id and options_table and name and details for this service
		$query = "SELECT ms.organization_id, ms.options_table, ".
			"ms.service_description, ms.support_notify, g.org_name, us.removed, ".
			"date(us.end_datetime) AS end_datetime, ".
			"date(us.start_datetime) AS start_datetime, ".
			"ms.usage_label, us.usage_multiple, us.billing_id, us.master_service_id ".
			"FROM user_services us ".
			"LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
			"LEFT JOIN general g ON g.id = ms.organization_id ".
			"WHERE us.id = '$userserviceid'";
		$orgresult = $this->db->query($query) or die ("$l_queryfailed");
		$myorgresult = $orgresult->row_array();

		return $myorgresult;
	}


	function service_categories($account_number)
	{
		$query = "SELECT DISTINCT category FROM user_services AS user, ".
			"master_services AS master ".
			"WHERE user.master_service_id = master.id ".
			"AND user.account_number = '$account_number' AND removed <> 'y' ".
			"ORDER BY category";
		$result = $this->db->query($query) or die ("$l_queryfailed");

		return $result->result_array();
	}


	function list_services($account_number, $category = NULL)
	{
		if ($category)
		{
			$query = "SELECT user.*, master.service_description, master.options_table, ".
				"master.pricerate, master.frequency, master.support_notify, ".
				"master.organization_id master_organization_id ".
				"FROM user_services AS user, master_services AS master ".
				"WHERE user.master_service_id = master.id ".
				"AND user.account_number = '$account_number' AND removed <> 'y' ".
				"AND master.category = '$category' ".
				"ORDER BY user.usage_multiple DESC, master.pricerate DESC";		
		}
		else
		{
			$query = "SELECT user.*, master.service_description, master.options_table, ".
				"master.pricerate, master.frequency, master.support_notify, ".
				"master.organization_id master_organization_id ".
				"FROM user_services AS user, master_services AS master ".
				"WHERE user.master_service_id = master.id ".
				"AND user.account_number = '$account_number' AND removed <> 'y' ".
				"ORDER BY user.usage_multiple DESC, master.pricerate DESC";		
		}

		$result = $this->db->query($query) or die ("$l_queryfailed");

		return $result->result_array();
	}


	function list_history($account_number)
	{

		$query = "SELECT user.*, master.service_description, master.options_table, 
			master.pricerate, master.frequency 
			FROM user_services AS user, master_services AS master 
			WHERE user.master_service_id = master.id 
			AND user.account_number = '$account_number' AND user.removed = 'y' 
			ORDER BY user.end_datetime DESC,user.usage_multiple DESC, master.pricerate DESC";
		$result = $this->db->query($query) or die ("queryfailed");

		return $result;
	}


	function list_services_in_category($account_number, $category)
	{
		$query = "SELECT user.*, master.service_description, master.options_table, ".
			"master.pricerate, master.frequency, master.support_notify, ".
			"master.organization_id master_organization_id ".
			"FROM user_services AS user, master_services AS master ".
			"WHERE user.master_service_id = master.id ".
			"AND user.account_number = '$account_number' AND removed <> 'y' ".
			"AND master.category = '$category' ".
			"ORDER BY user.usage_multiple DESC, master.pricerate DESC";
	}

	function options_attributes($service_id, $options_table)
	{
		$query = "SELECT * FROM $options_table WHERE user_services = '$service_id'";
		$optionsresult = $this->db->query($query) or die ("$l_queryfailed");
		if ($optionsresult->num_rows() > 0)
		{
			$myoptions = $optionsresult->row_array();

			// convert the associative array into a numbered array
			// since we don't know the names of customer attribute fields
			$i = 0;
			foreach($myoptions AS $myfieldname)
			{
				$data[$i] = $myfieldname;
				$i++;
			}

			return $data;
		}
	}


	/*
	 * ------------------------------------------------------------------------
	 * get the named fields from the options attributes not numbered
	 * ------------------------------------------------------------------------
	 */
	function options_values($service_id, $options_table)
	{
		$query = "SELECT * FROM $options_table WHERE user_services = '$service_id'";
		$optionsresult = $this->db->query($query) or die ("$l_queryfailed");
		return $optionsresult->row_array();
	}

	function options_urls($fieldname)
	{
		// list any applicable options attribute url links
		$query = "SELECT * FROM options_urls WHERE fieldname = ?";				
		$urlresult = $this->db->query($query, array($fieldname)) or die ("URL $l_queryfailed");
		return $urlresult->result_array();
	}

	function removed_status($userserviceid)
	{
		// check if the service is removed or and not canceled
		// show the undelete button only if an account it not canceled
		$query = "SELECT us.removed, c.cancel_date FROM user_services us ".
			"LEFT JOIN customer c ON c.account_number = us.account_number ".
			"WHERE us.id = $userserviceid";
		$removedresult = $this->db->query($query) or die ("query failed");
		return $removedresult->row_array();
	}
	
	function field_inventory($userserviceid)
	{
		$query = "SELECT afa.id, mfa.description, afa.creation_date, afa.serial_number, ".
			"afa.status, afa.sale_type, afa.shipping_tracking_number, afa.shipping_date, ".
			"afa.return_date, afa.return_notes ".
			"FROM field_asset_items afa ".
			"LEFT JOIN master_field_assets mfa ON mfa.id = afa.master_field_assets_id ".
			"LEFT JOIN user_services us ON us.id = afa.user_services_id ".
			"WHERE us.id = '$userserviceid'";

		$result = $this->db->query($query) or die ("$query query failed");
		return $result->result_array();
	}

	// query the taxes and fees that this service has
	function checktaxes($user_services_id) 
	{
		// make a new multi dimensional array to hold the results
		$tax_array = array();
		$i = 0;

		$query = "SELECT ts.id ts_id, ts.master_services_id ts_serviceid, ".
			"ts.tax_rate_id ts_rateid, ms.id ms_id, ".
			"ms.service_description ms_description, ms.pricerate ms_pricerate, ".
			"ms.frequency ms_freq, tr.id tr_id, tr.description tr_description, ".
			"tr.rate tr_rate, tr.if_field tr_if_field, tr.if_value tr_if_value, ".
			"tr.percentage_or_fixed tr_percentage_or_fixed, ".
			"us.master_service_id us_msid, us.billing_id us_bid, us.removed us_removed, ".
			"us.account_number us_account_number, te.account_number te_account_number, ".
			"te.tax_rate_id te_tax_rate_id, te.customer_tax_id te_customer_tax_id, ".
			"te.expdate te_expdate ".
			"FROM taxed_services ts ".
			"LEFT JOIN user_services us ON us.master_service_id = ts.master_services_id ".
			"LEFT JOIN master_services ms ON ms.id = ts.master_services_id ".
			"LEFT JOIN tax_rates tr ON tr.id = ts.tax_rate_id ". 
			"LEFT JOIN tax_exempt te ON te.account_number = us.account_number ".
			"AND te.tax_rate_id = tr.id ".
			"WHERE us.removed = 'n' AND us.id = '$user_services_id'";

		$result = $this->db->query($query) or die ("$l_queryfailed");

		foreach ($result->result_array() as $taxresult) {
			$account_number = $taxresult['us_account_number'];
			$service_description = $taxresult['ms_description'];
			$tax_description = $taxresult['tr_description'];
			$freqmultiplier = $taxresult['ms_freq'];
			$if_field = $taxresult['tr_if_field'];
			$if_value = $taxresult['tr_if_value'];
			$tax_rate_id = $taxresult['tr_id'];
			$percentage_or_fixed = $taxresult['tr_percentage_or_fixed'];
			$tax_exempt_rate_id = $taxresult['te_tax_rate_id'];
			$customer_tax_id = $taxresult['te_customer_tax_id'];
			$customer_tax_id_expdate = $taxresult['te_expdate'];

			// check the if_field before printing to see if the tax applies
			// to this customer
			if ($if_field <> '')
			{
				$checkvalue = $this->customer_model->check_if_field($if_field, $account_number);
			} else {
				$checkvalue = TRUE;
				$if_value = TRUE;
			}

			// check for any case, so lower them here
			$checkvalue = strtolower($checkvalue);
			$if_value = strtolower($if_value);

			if ($checkvalue == $if_value) {
				// check that they are not exempt
				if ($tax_exempt_rate_id <> $tax_rate_id) {
					// check if it is a percentage or fixed amount
					if ($percentage_or_fixed == "percentage") {
						if ($freqmultiplier > 0) {
							$tax_amount = $taxresult['tr_rate']
								* $taxresult['ms_pricerate'] * $freqmultiplier;
						} else {
							$tax_amount = $taxresult['tr_rate']
								* $taxresult['ms_pricerate'];
						}
					} else {
						// then it is a fixed amount not multiplied by the price
						$tax_amount = $taxresult['tr_rate'];
					}

					// round the tax to two decimal places
					$tax_amount = sprintf("%.2f", $tax_amount);

					$tax_array[$i] = array(
							'tax_description' => $tax_description,
							'tax_amount' => $tax_amount,
							'tax_rate_id' => $tax_rate_id,
							'exempt' => FALSE
							);

					/*
					   print "<tr><td></td>".
					   "<td bgcolor=\"#eeeeff\" style=\"font-size: 8pt;\" ".
					   "colspan=3>$tax_description</td>".
					   "<td bgcolor=\"#eeeeff\"  style=\"font-size: 8pt;\" ".
					   "colspan=4>$tax_amount</td>".
					   "<td bgcolor=\"#eeeeff\" style=\"font-size: 8pt;\">".
					   "<form style=\"margin-bottom:0;\" action=\"index.php\" method=post>".
					   "<input type=hidden name=load value=services>".
					   "<input type=hidden name=type value=module>".
					   "<input type=hidden name=edit value=on>".
					   "<input type=hidden name=taxrate value=\"$tax_rate_id\">".
					   "<input name=exempt type=submit value=\"$l_exempt\" ".
					   "class=smallbutton></form></td></tr>";
					 */
				} else {
					$tax_array[$i] = array(
							'tax_description' => $tax_description,
							'tax_amount' => $tax_amount,
							'tax_rate_id' => $tax_rate_id,
							'exempt' => TRUE,
							'customer_tax_id' => $customer_tax_id,
							'customer_tax_id_expdate' => $customer_tax_id_expdate
							);
					/*
					// print the exempt tax
					print "<tr style=\"font-size: 9pt;\"><td></td>".
					"<td bgcolor=\"#eeeeff\" style=\"font-size: 8pt;\" ".
					"colspan=3>$tax_description</td>".
					"<td bgcolor=\"#eeeeff\" style=\"font-size: 8pt;\" ".
					"colspan=4>$l_exempt: $customer_tax_id ".
					"$customer_tax_id_expdate</td>".
					"<td bgcolor=\"#eeeeff\" style=\"font-size: 8pt;\">".
					"<form style=\"margin-bottom:0;\" action=\"index.php\" method=post>".
					"<input type=hidden name=load value=services>".
					"<input type=hidden name=type value=module>".
					"<input type=hidden name=edit value=on>".
					"<input type=hidden name=taxrate value=\"$tax_rate_id\">".
					"<input name=notexempt type=submit value=\"$l_notexempt\" ".
					"class=smallbutton></form></td></tr>";
					 */
				} // end if exempt tax

			} // end if_field

			// increment array counter
			$i++;

		} // end foreach

		return $tax_array;

	} // end checktaxes function


	function carrier_dependent($account_number)
	{
		// check for carrier_dependent services that are still active

		$query = "SELECT us.*,ms.carrier_dependent ".
			"FROM user_services us ".
			"LEFT JOIN master_services ms ".
			"ON ms.id = us.master_service_id ".
			"WHERE us.account_number = $account_number ".
			"AND us.removed <> 'y' AND ms.carrier_dependent = 'y'";
		$removedresult = $this->db->query($query) or die ("$l_queryfailed");

		// get the rows returned by the dependent query
		$count = $removedresult->num_rows();

		// set carrier dependent value
		if ($count > 0) {
			$dependent = true;
		} else {
			$dependent = false;
		}

		return $dependent;
	}


	function delete_service($userserviceid, $service_notify_type, $removal_date)
	{
		// check if there is a removal date or blank
		if (empty($removal_date)) {
			$query = "UPDATE user_services SET removed = 'y', ".
				"end_datetime = NOW() WHERE id = $userserviceid";	  
		} else {
			$query = "UPDATE user_services SET removed = 'y', ".
				"end_datetime = NOW(), ".
				"removal_date = '$removal_date' ".
				"WHERE id = $userserviceid";
		}

		$result = $this->db->query($query) or die ("query failed");

		// put a note in the customer_history that this service was removed
		// get the account_number and master_service_id first
		$query = "SELECT * FROM user_services WHERE id = '$userserviceid'";
		$result = $this->db->query($query) or die ("query failed");
		$myresult = $result->row_array();
		$account_number = $myresult['account_number'];
		$master_service_id = $myresult['master_service_id'];

		if ($service_notify_type <> "change") {
			$this->service_message($service_notify_type, $account_number,
					$master_service_id, $userserviceid, NULL, NULL);
		}	
	} // end delete_service


	function update_removal_date($serviceid, $removaldate)
	{
		$query = "UPDATE user_services SET removal_date = '$removaldate' ".
			"WHERE id = '$serviceid'";
		$result = $this->db->query($query) or die ("due date update $l_queryfailed");
	}


	/*	
	 * -------------------------------------------------------------------------------
	 *  add a service message ticket for new, modified, or shutoff services
	 * -------------------------------------------------------------------------------
	 */
	function service_message($service_notify_type, $account_number,
			$master_service_id, $user_service_id,
			$new_master_service_id, $new_user_service_id)

	{			
		/*- Service Notify Types -*/
		// added
		// change - uses both user_service_id and new_user_service_id
		//   the change function will need to create a new_user_service_id
		//   like it should have been doing
		//
		// onetime - for one time billing removals
		// undelete
		//
		// removed
		// canceled
		// turnoff
		/*-------------------------*/

		// get the name of the service
		$query = "SELECT * FROM master_services WHERE id = $master_service_id";
		$result = $this->db->query($query) or die ("service_message query failed");
		$myresult = $result->row_array();	
		$servicename = $myresult['service_description'];
		$activate_notify = $myresult['activate_notify']; // added
		$modify_notify = $myresult['modify_notify'];     // change,undelete
		$shutoff_notify = $myresult['shutoff_notify'];   // turnoff, removed, canceled

		// set a different notify and description depending on service_notify_type

		// ADDED
		if ($service_notify_type == "added") {
			$description = lang('added') ." $servicename $user_service_id";
			if ($activate_notify <> '') {
				$status = "not done";
				$notify = $activate_notify;
			} else {
				$status = "automatic";
				$notify = "";
			}
		}

		// CHANGE
		if ($service_notify_type == "change") { 
			// get the name of the new service
			$query = "SELECT * FROM master_services WHERE id = $new_master_service_id";
			$result = $this->db->query($query)
				or die ("service_message_modify query failed");
			$myresult = $result->row_array();	
			$new_servicename = $myresult['service_description'];
			// use the new services modify notify, maybe different from old one
			$modify_notify = $myresult['modify_notify'];

			$description = lang('change') . " $servicename $user_service_id -> $new_servicename $new_user_service_id";
			if ($modify_notify <> '') {
				$status = "not done";
				$notify = $modify_notify;
			} else {
				$status = "automatic";
				$notify = "";
			} 
		}

		// UNDELETE
		if ($service_notify_type == "undelete") {
			$description = lang('undelete') . " $servicename $user_service_id";    
			if ($modify_notify <> '') {
				$status = "not done";
				$notify = $modify_notify;
			} else {
				$status = "automatic";
				$notify = "";
			} 
		}

		// ONETIME
		if ($service_notify_type == "onetime") {
			$description = lang('onetimebilled') . "$servicename $user_service_id";    
			if ($shutoff_notify <> '') {
				$status = "not done";
				$notify = $shutoff_notify;
			} else {
				$status = "automatic";
				$notify = "";
			} 
		}

		// REMOVED
		if ($service_notify_type == "removed") {
			$description = lang('removed') ." $servicename $user_service_id";    

			if ($shutoff_notify <> '') {
				$status = "not done";
				$notify = $shutoff_notify;
			} else {
				$status = "automatic";
				$notify = "";
			}

		}  

		// CANCELED
		if ($service_notify_type == "canceled") {
			$description = lang('canceled') ." $servicename $user_service_id";    

			if ($shutoff_notify <> '') {
				$status = "not done";
				$notify = $shutoff_notify;
			} else {
				$status = "automatic";
				$notify = "";
			}

		}


		// TURNOFF
		if ($service_notify_type == "turnoff") {
			$description = lang('turnoff') ." $servicename $user_service_id";    

			if ($shutoff_notify <> '') {
				$status = "not done";
				$notify = $shutoff_notify;
			} else {
				$status = "automatic";
				$notify = "";
			}

		}

		// create the ticket with the service message
		$this->support_model->create_ticket($this->user, $notify, $account_number, 
				$status, $description, NULL, NULL, NULL, $user_service_id);
	}


	public function get_service_desc_and_notify($user_services_id)
	{	
		// to prepare support note form get the service description and support_notify
		$query = "SELECT us.id user_services_id, us.master_service_id, ms.id, ".
			"ms.service_description, ms.support_notify ".
			"FROM user_services us ".
			"LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
			"WHERE us.id = '$user_services_id' LIMIT 1";
		$result = $this->db->query($query) 
			or die ("get service desc and notify queryfailed");

		return $result->row_array();
	}

	public function get_service_info($serviceid)
	{
		// get the info about the service
		$query = "SELECT * FROM master_services WHERE id = $serviceid";
		$result = $this->db->query($query) or die ("$query master_services select $l_queryfailed");

		return $result->row_array();	
	}


	public function get_service_name($service_id)
	{
		$query = "SELECT service_description FROM master_services WHERE id = ?";
		$result = $this->db->query($query, array($service_id)) or die ("service name $l_queryfailed");
		$myresult = $result->row_array();

		return $myresult['service_description'];
	}


	/*
	 * ------------------------------------------------------------------------
	 *  get the list of service categories from the master_services table
	 * ------------------------------------------------------------------------
	 */
	public function distinct_service_categories()
	{
		$query = "SELECT DISTINCT(category) FROM master_services ORDER BY category";
		$result = $this->db->query($query) or die ("distince service categories query failed");

		return $result->result_array();
	}


	function field_asset_description($master_field_assets_id)
	{
		// get the name of the item being assigned from master_field_assets
		$query = "SELECT description FROM master_field_assets ".
			"WHERE id = ?";
		$result = $this->db->query($query, array($master_field_assets_id)) 
			or die ("field assets description query failed");
		$myresult = $result->row_array();

		return $myresult['description'];
	}

	function field_asset_item_description($item_id)
	{
		// get the name of the item being updated from master_field_assets
		$query = "SELECT ma.description FROM field_asset_items fa ".
			"LEFT JOIN master_field_assets ma ON ma.id = fa.master_field_assets_id ".
			"WHERE fa.id = ?";
		$result = $this->db->query($query, array($item_id)) 
			or die ("item description query failed");
		$myresult = $result->row_array();
		return $myresult['description'];
	}

	function assign_field_asset($master_field_assets_id, $serial_number, 
			$sale_type, $tracking_number, $shipping_date, $userserviceid)
	{
		$query = "INSERT into field_asset_items (master_field_assets_id, ".
			"creation_date, serial_number, status, sale_type, ".
			"shipping_tracking_number, shipping_date, user_services_id ) ".
			"VALUES (?, CURRENT_DATE, ?, 'infield', ?, ?, ?, ?)";
		$result = $this->db->query($query, array($master_field_assets_id, $serial_number, 
					$sale_type, $tracking_number, $shipping_date, $userserviceid)) 
			or die ("insert field asset item query failed");
	}


	function return_field_asset($return_date, $return_notes, $item_id)
	{
		// put this inventory item into a return status  
		$query = "UPDATE field_asset_items SET ".
			"status = 'returned', return_date = ?, ".
			"return_notes = ? WHERE id = ? LIMIT 1";
		$result = $this->db->query($query, array($return_date, $return_notes, 
					$item_id)) or die ("return field asset query failed");
	}


	function vendor_history($userserviceid)
	{
		$query = "SELECT * FROM vendor_history WHERE user_services_id = ? ".
			"ORDER BY datetime DESC";
		$result = $this->db->query($query, array($userserviceid)) 
			or die ("select vendor_history query failed");
		return $result->result_array();
	}

	function vendor_names()
	{
		// get the list of service categories from the master_services table
		$query = "SELECT name FROM vendor_names ORDER BY name";
		$result = $this->db->query($query) or die ("vendor names query failed");
		return $result->result_array();
	}


	function get_status_and_price($userserviceid)
	{
		// grab the current account_status, and total_price
		$query = "SELECT SUM(bd.billed_amount) as billed_amount, bd.billing_id ".
			"FROM billing_details bd ".
			"LEFT JOIN user_services us ON us.id = bd.user_services_id ".
			"WHERE bd.user_services_id = ? GROUP BY bd.invoice_number ".
			"ORDER BY invoice_number DESC LIMIT 1";
		$result = $this->db->query($query, array($userserviceid)) or die ("get status query failed");
		return $result->row_array();
	}


	function add_vendor_history($entry_type, $entry_date, $vendor_name, 
			$vendor_bill_id, $vendor_cost, $vendor_tax, $vendor_item_id, 
			$vendor_invoice_number, $vendor_from_date, $vendor_to_date, 
			$userserviceid, $account_status, $billing_id)
	{
		$query = "INSERT into vendor_history ".
			"(datetime, entry_type, entry_date, vendor_name, vendor_bill_id, ".
			"vendor_cost, vendor_tax, vendor_item_id, vendor_invoice_number, vendor_from_date, ".
			"vendor_to_date, user_services_id, account_status, billed_amount) VALUES ".
			"(NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?)";
		$result = $this->db->query($query, array($entry_type, $entry_date, $vendor_name, 
					$vendor_bill_id, $vendor_cost, $vendor_tax, $vendor_item_id, 
					$vendor_invoice_number, $vendor_from_date, $vendor_to_date, 
					$userserviceid, $account_status, $billed_amount)) or die ("add vendor query failed");
	}

	function get_field_assets($userserviceid)
	{
		$query = "SELECT m.category FROM master_services m ".
			"LEFT JOIN user_services u ON u.master_service_id = m.id ".
			"WHERE u.id = ?";
		$result = $this->db->query($query, array($userserviceid)) or die ("$l_queryfailed");
		$myresult = $result->row_array();
		$category = $myresult['category'];

		$query = "SELECT * FROM master_field_assets WHERE status = 'current' ".
			"AND category = ?";
		$result = $this->db->query($query, array($category)) or die ("$query $l_queryfailed");
		return $result;
	}

	
	/*
	 * ------------------------------------------------------------------------
	 *  get the list of all active master services
	 * ------------------------------------------------------------------------
	 */
	function get_master_service_list()
	{
		// set the query for the service listing
		$query = "SELECT * FROM master_services m ".
			"LEFT JOIN general g ON g.id = m.organization_id ".
			"WHERE selling_active = 'y' ".
			"ORDER BY category, pricerate, service_description";
		$result = $this->db->query($query) or die ("get master service list queryfailed");
		return $result->result_array();
	} 


	/*
	 * ------------------------------------------------------------------------
	 *  get the list of master services that can be assigned to this organization
	 * ------------------------------------------------------------------------
	 */
	function get_org_master_service_list($organization_id)
	{
		$query = "SELECT * FROM master_services m ".
			"WHERE selling_active = 'y' AND hide_online <> 'y' ".
			"AND organization_id = ? ".
			"ORDER BY category, pricerate, service_description";
		$result = $this->db->query($query, array($organization_id)) 
			or die ("get org master service list queryfailed");
		return $result->result_array();
	}


	function linked_services($master_service_id)
	{
		$query = "SELECT mfrom.id mfrom_id, ".
			"mfrom.service_description mfrom_description, ".
			"mto.id mto_id, mto.service_description mto_description, ".
			"mto.pricerate mto_pricerate, l.linkfrom, l.linkto ".
			"FROM linked_services l ".
			"LEFT JOIN master_services mfrom ON mfrom.id = l.linkfrom ".
			"LEFT JOIN master_services mto ON mto.id = l.linkto ".
			"WHERE l.linkfrom = ?";

		$result = $this->db->query($query, array($master_service_id)) 
			or die ("query failed");

		return $result->result_array();
	}

	/*
	 * ------------------------------------------------------------------------
	 *  get a list of services that use the same options tables
	 * ------------------------------------------------------------------------
	 */
	function services_sharing_options($optionstable, $service_org_id) 
	{
		$query = "SELECT * FROM master_services ".
			"WHERE options_table = '$optionstable' ".
			"AND selling_active = 'y' ".
			"AND organization_id = $service_org_id";
		$result = $this->db->query($query, array($optionstable, $service_org_id)) 
			or die ("$l_queryfailed");
		return $result->result_array();
	}

}

/* end service_model.php */
