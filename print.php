<?php
// Copyright (C) 2002-2006  Paul Yasi (paul at citrusdb.org)
// read the README file for more information
// this will print a full customer record including customer, billing, 
// service and support info

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

$account_number = $base->input['acnum'];

echo "<h3>$l_customerrecord</h3><blockquote>";

//
// get the customer information
//
$query = "SELECT c.signup_date c_signup_date, c.name c_name, c.company c_company, c.street c_street, c.city c_city, c.state c_state, c.zip c_zip, c.country c_country, c.phone c_phone, c.fax c_fax, c.source c_source, 
			c.contact_email c_contact_email, c.default_billing_id c_default_billing_id, 
			c.cancel_date c_cancel_date, 
			c.removal_date c_removal_date 
FROM customer c 
WHERE c.account_number = '$account_number'"; 
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;	

    
//
// Put values into variablies and Print basic customer info
//
$signup_date = $myresult['c_signup_date'];
$name = $myresult['c_name'];
$company = $myresult['c_company'];        
$street = $myresult['c_street'];
$city = $myresult['c_city'];
$state = $myresult['c_state'];
$zip = $myresult['c_zip'];
$country = $myresult['c_country'];
$phone = $myresult['c_phone'];
$fax = $myresult['c_fax'];
$source = $myresult['c_source'];
$contactemail = $myresult['c_contact_email'];
$default_billing_id = $myresult['c_default_billing_id'];
$cancel_date = $myresult['c_cancel_date'];
$removal_date = $myresult['c_removal_date'];

echo "$l_name: $name<br>
$l_company: $company<br>
$l_street: $street<br>
$l_city: $city<br>
$l_state: $state<br>
$l_zip: $zip<br>
$l_country: $country<br>
$l_phone: $phone<br>
$l_fax: $fax<br>
$l_source: $source<br>
$l_contactemail: $contactemail<br>";


//
// Get their service information
//
$query = "SELECT c.signup_date c_signup_date, c.name c_name, c.company c_company, c.street c_street, c.city c_city, 
			c.state c_state, c.zip c_zip, c.country c_country, c.phone c_phone, c.fax c_fax, c.source c_source, 
			c.contact_email c_contact_email, c.default_billing_id c_default_billing_id, 
			c.cancel_date c_cancel_date, c.removal_date c_removal_date, b.id b_id, 
			b.name b_name, b.company b_company, b.street b_street, b.city b_city, b.state b_state, 
			b.country b_country, b.zip b_zip, b.phone b_phone, b.fax b_fax, b.contact_email b_contact_email, 
			b.account_number b_account_number, b.billing_type b_billing_type, 
			b.creditcard_number b_creditcard_number, b.creditcard_expire b_creditcard_expire, 
			b.billing_status b_billing_status, b.next_billing_date b_next_billing_date, 
			b.prev_billing_date b_prev_billing_date, 
			u.account_number u_account_number, u.master_service_id u_master_service_id, u.billing_id u_billing_id, 
			u.start_datetime u_start_datetime, u.salesperson u_salesperson, u.usage_multiple u_usage_multiple, 
			u.removed u_removed, 
			m.id m_id, m.service_description m_service_description, m.pricerate m_pricerate, 
			m.frequency m_frequency, m.options_table m_options_table
			FROM customer c LEFT JOIN billing b ON b.id = c.default_billing_id 
			LEFT JOIN user_services u ON u.account_number = c.account_number 
			LEFT JOIN master_services m ON u.master_service_id = m.id 
			WHERE c.account_number = '$account_number' AND removed <> 'y'";
$result = $DB->Execute($query) or die ("$l_queryfailed");

echo "<p><h3>$l_services</h3>
	<table><td>$l_id</td>
	<td>$l_description</td>
	<td>$l_details</td>
	<td>$l_price</td><tr>";
while ($myresult = $result->FetchRow())
{
	$m_id = $myresult['m_id'];
	$m_service_description = $myresult['m_service_description'];
	$m_pricerate = $myresult['m_pricerate'];
	$m_frequency = $myresult['m_frequency'];
	echo "<td>$m_id</td><td>$m_service_description</td><td>$m_frequency</td><td>\$$m_pricerate</td><tr>";
}
echo "</table>";

//
// Get their billing information
//
$query = "SELECT c.signup_date c_signup_date, c.name c_name, c.company c_company, c.street c_street, c.city c_city, 
			c.state c_state, c.zip c_zip, c.country c_country, c.phone c_phone, c.fax c_fax, c.source c_source, 
			c.contact_email c_contact_email, c.default_billing_id c_default_billing_id, 
			c.cancel_date c_cancel_date, c.removal_date c_removal_date, b.id b_id, 
			b.name b_name, b.company b_company, b.street b_street, b.city b_city, b.state b_state, 
			b.country b_country, b.zip b_zip, b.phone b_phone, b.fax b_fax, b.contact_email b_contact_email, 
			b.account_number b_account_number, b.billing_type b_billing_type, 
			b.creditcard_number b_creditcard_number, b.creditcard_expire b_creditcard_expire, 
			b.billing_status b_billing_status, b.next_billing_date b_next_billing_date, 
			b.prev_billing_date b_prev_billing_date, 
			u.account_number u_account_number, u.master_service_id u_master_service_id, u.billing_id u_billing_id, 
			u.start_datetime u_start_datetime, u.salesperson u_salesperson, u.usage_multiple u_usage_multiple, 
			u.removed u_removed, 
			m.id m_id, m.service_description m_service_description, m.pricerate m_pricerate, 
			m.frequency m_frequency, m.options_table m_options_table
			FROM customer c LEFT JOIN billing b ON b.id = c.default_billing_id 
			LEFT JOIN user_services u ON u.account_number = c.account_number 
			LEFT JOIN master_services m ON u.master_service_id = m.id 
			WHERE c.account_number = '$account_number' AND removed <> 'y'";
$result = $DB->Execute($query) or die ("$l_queryfailed");

echo "<p><h3>$l_billing</h3>
	<table><td>$l_id</td>
	<td>$l_type</td>
	<td>$l_status</td>
	<td>$l_nextbillingdate</td><tr>";
while ($myresult = $result->FetchRow())
{
	$b_id = $myresult['b_id'];
	$b_billing_type = $myresult['b_billing_type'];
	$b_billing_status = $myresult['b_billing_status'];
	$b_next_billing_date = $myresult['b_next_billing_date'];
	echo "<td>$b_id</td><td>$b_billing_type</td><td>$b_billing_status</td><td>$b_next_billing_date</td><tr>";
}
echo "</table>";

echo "</blockquote>";

?>
