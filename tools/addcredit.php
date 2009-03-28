<?php
echo "<h3>$l_addcredit</h3>";
// Copyright (C) 2002-2009  Paul Yasi (paul at citrusdb.org)
// read the README file for more information
/*----------------------------------------------------------------------------*/
// Check for authorized accesss
/*----------------------------------------------------------------------------*/
if(constant("INDEX_CITRUS") <> 1){
  echo "You must be logged in to run this.  Goodbye.";
  exit;	
}

if (!defined("INDEX_CITRUS")) {
  echo "You must be logged in to run this.  Goodbye.";
  exit;
}

//GET Variables
if (!isset($base->input['billing_id'])) { $base->input['billing_id'] = ""; }
if (!isset($base->input['amount'])) { $base->input['amount'] = ""; }

$billing_id = $base->input['billing_id'];
$amount = $base->input['amount'];

if ($amount) {

  //$DB->debug = true;

  // lookup the account number
  $query = "SELECT * FROM billing WHERE id = '$billing_id'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  $myresult = $result->fields;
  $account_number = $myresult['account_number'];
  $organization_id = $myresult['organization_id'];
  
  // credit master service id, add the one for that organization
  // select from services where category = credit
  // and organization_id = the one we want,
  // if only one, then use that credit service id
  $query = "SELECT id,options_table FROM master_services ".
    "WHERE category = 'credit' ".
    "AND organization_id = '$organization_id' LIMIT 1";
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  $myresult = $result->fields;
  $master_service_id = $myresult['id'];
  $options_table_name = $myresult['options_table'];
  
  // credit options fieldname strings
  $attribute_fieldname_string = "description";

  // credit options fieldname values
  $mydate = date("F j, Y");
  $attribute_fieldvalue_string = "'$l_foroverpayment $mydate'";

  // set the usage multiple to the amount
  $usage_multiple = $amount;
  
  // add a credit service type to the account
  $new_service_id = create_service($account_number, $master_service_id,
				 $billing_id, $usage_multiple,
				 $options_table_name,
				 $attribute_fieldname_string,
				 $attribute_fieldvalue_string);

  // add a note to the customer history
  service_message('added', $account_number, $master_service_id,
		  $new_service_id, NULL, NULL);
  
  // redirect back to the services record for their account
  echo "<script language=\"JavaScript\">window.location.href ".
    "= \"index.php?load=payment&type=tools\";</script>"; 
  
 } // end if
?>
