<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Service_Model extends CI_Model
{
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

	function service_categories($account_number)
	{
		$query = "SELECT DISTINCT category FROM user_services AS user, ".
			"master_services AS master ".
			"WHERE user.master_service_id = master.id ".
			"AND user.account_number = '$account_number' AND removed <> 'y' ".
			"ORDER BY category";
		$result = $this->db->query($query) or die ("$l_queryfailed");

		return $result;
	}


	function list_services($account_number)
	{
		$query = "SELECT user.*, master.service_description, master.options_table, ".
			"master.pricerate, master.frequency, master.support_notify, ".
			"master.organization_id master_organization_id ".
			"FROM user_services AS user, master_services AS master ".
			"WHERE user.master_service_id = master.id ".
			"AND user.account_number = '$account_number' AND removed <> 'y' ".
			"ORDER BY user.usage_multiple DESC, master.pricerate DESC";		

		$result = $this->db->query($query) or die ("$l_queryfailed");

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
		$myoptions = $optionsresult->row();

		return $myoptions;
	}


	// query the taxes and fees that this customer has
	function checktaxes($user_services_id) 
	{

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

		foreach ($result->result() as $taxresult) {
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
				$ifquery = "SELECT $if_field FROM customer ".
					"WHERE account_number = '$account_number'";
				$DB->SetFetchMode(ADODB_FETCH_NUM);
				$ifresult = $DB->Execute($ifquery) or die ("$l_queryfailed");
				$myifresult = $ifresult->fields;
				$checkvalue = $myifresult[0];
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

				} else {
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
				} // end if exempt tax

			} // end if_field

		} // end while

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
		$this->ticket_model->create_ticket($user, $notify, $account_number, $status, $description, NULL, NULL, NULL, $user_service_id);
	}

}
