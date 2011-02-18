<?php

function create_service($account_number, $master_service_id, $billing_id,
			$usage_multiple, $options_table_name,
			$attribute_fieldname_string,
			$attribute_fieldvalue_string)
{
  global $DB, $user;

  $mydate = date("Y-m-d H:i:s");
  
  // insert the new service into the user_services table
  $query = "INSERT into user_services (account_number, master_service_id, ".
    "billing_id, start_datetime, salesperson, usage_multiple) ".
    "VALUES ('$account_number', '$master_service_id', '$billing_id',".
    "'$mydate', '$user', '$usage_multiple')";
  $result = $DB->Execute($query) or die ("create_service $l_queryfailed");

  // use the mysql_insert_id command to get the ID of the row the insert
  // was to for the options table query
  $myinsertid = $DB->Insert_ID();

  // insert values into the options table
  // skip this if there is no options_table_name for this service
  if ($options_table_name <> '') {
    $query = "INSERT into $options_table_name ".
      "(user_services,$attribute_fieldname_string) ".
      "VALUES ($myinsertid,$attribute_fieldvalue_string)";
    $result = $DB->Execute($query) or die ("create_service $query");
  }

  return $myinsertid;
  
}

function delete_service($userserviceid, $service_notify_type, $removal_date)
{
	global $DB, $user;

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

	
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	
	// put a note in the customer_history that this service was removed
    	// get the account_number and master_service_id first
	$query = "SELECT * FROM user_services WHERE id = '$userserviceid'";
    	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	$myresult = $result->fields;
    	$account_number = $myresult['account_number'];
	$master_service_id = $myresult['master_service_id'];

	if ($service_notify_type <> "change") {
	  service_message($service_notify_type, $account_number,
			  $master_service_id, $userserviceid, NULL, NULL);
	}	
} // end delete_service


function undelete_service($userserviceid, $message)
{
	global $DB, $user;

	$query = "UPDATE user_services SET removed = 'n',  
	end_datetime = NULL, 
	removal_date = NULL   
	WHERE id = $userserviceid";
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	
	// put a note in the customer_history that this service was undeleted
    	// get the name of the service
	$query = "SELECT * FROM user_services WHERE id = '$userserviceid'";
    	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	$myresult = $result->fields;
    	$master_service_id = $myresult['master_service_id'];
	$account_number = $myresult['account_number'];

	service_message('undelete', $account_number,
			$master_service_id, $userserviceid, NULL, NULL);

} // end undelete_service

function carrier_dependent($account_number)
{
  // check for carrier_dependent services that are still active

  global $DB;

  $query = "SELECT us.*,ms.carrier_dependent ".
    "FROM user_services us ".
    "LEFT JOIN master_services ms ".
    "ON ms.id = us.master_service_id ".
    "WHERE us.account_number = $account_number ".
    "AND us.removed <> 'y' AND ms.carrier_dependent = 'y'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $removedresult = $DB->Execute($query) or die ("$l_queryfailed");

  // get the rows returned by the dependent query
  $count = $removedresult->RowCount();

  // set carrier dependent value
  if ($count > 0) {
    $dependent = true;
  } else {
    $dependent = false;
  }

  return $dependent;
}