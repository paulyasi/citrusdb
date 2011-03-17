<?php
/*----------------------------------------------------------------------------*/
// CitrusDB - The Open Source Customer Database
// Copyright (C) 2005-2011 Paul Yasi
//
// This program is free software; you can redistribute it and/or modify it under
// the terms of the GNU General Public License as published by the Free Software 
// Foundation; either version 2 of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful, but WITHOUT 
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License along with 
// this program; if not, write to the Free Software Foundation, Inc., 59 Temple 
// Place, Suite 330, Boston, MA 02111-1307 USA
//
// http://www.citrusdb.org
// Read the README file for more details
/*----------------------------------------------------------------------------*/

// Includes
include('./include/config.inc.php');
include('./include/database.inc.php');

// Get our user class
include('./include/PasswordHash.php');
include('./include/user.class.php');
$u = new user();

// Get our base include class and functions
require './include/citrus_base.php';
$base = new citrus_base();

// kick you out if you have 5 failed logins from the same ip
$failures = checkfailures();
if ($failures) {
  echo "Login Failure.  Please Contact Customer Service";
  die;
}

if (!isset($base->input['submit'])) { $base->input['submit'] = ""; }
if (!isset($base->input['cmd'])) { $base->input['cmd'] = ""; }

$submit = $base->input['submit'];

if ($submit) {
  // check the user login information
  $u->user_login($base->input['user_name'],$base->input['password']);
  
  // redirect back to this page to set any cookies properly
  print "<script language=\"JavaScript\">window.location.href = \"index.php\";</script>";
}

//echo '<pre>';
//var_dump($_SERVER);
//die;

if ($u->user_isloggedin()) {
	session_start();

	// define this constant
	define('INDEX_CITRUS',1);

	// this user variable holds the account number
	$user =  $u->user_getname(); 

	//GET Variables (sorta like the old way for now)
	$cmd = $base->input['cmd'];
	
	// print the top of the page
	echo "<html>
	<head>
	<title>Customer Account Manager</title>
	<LINK href=\"citrus.css\" type=text/css rel=STYLESHEET>
	<script language=\"JavaScript\">
	function h(oR) {
		oR.style.background-color='ffdd77';
	}	
	function deh(oR) {
		oR.style.background-color='ddddee';
	}
	function dehnew(oR) {
		oR.style.background-color='ddeeff';
	}
	</script>
	</head>
	<body bgcolor=\"#ffffff\">";
		
	// Print heading and links
	echo "<h2>$l_customeraccountmanager: $l_account $user</h2>";
	echo "<a href=\"index.php\">$l_profile</a> | ";
	echo "<a href=\"index.php?cmd=view_services\">$l_services</a> | ";
        echo "<a href=\"index.php?cmd=view_bill\">$l_billing</a> | ";
	echo "<a href=\"index.php?cmd=support\">$l_supportrequest</a> | ";
	echo "<a href=\"logout.php\">$l_logout</a>\n";
	
	switch ($cmd) {
		
	/*--------------------------------------------------------------------*/
	// Services
	/*--------------------------------------------------------------------*/
	case 'view_services':
		/*----------------------------------------------------*/
                // list of services and service details
		/*----------------------------------------------------*/
	
		// Get their service information
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
		u.account_number u_account_number, u.master_service_id u_master_service_id, 
		u.billing_id u_billing_id, u.start_datetime u_start_datetime, 
		u.salesperson u_salesperson, u.usage_multiple u_usage_multiple, 
		u.id u_id, u.removed u_removed, 
		m.id m_id, m.service_description m_service_description, m.pricerate m_pricerate, 
		m.frequency m_frequency, m.options_table m_options_table
		FROM customer c LEFT JOIN billing b ON b.id = c.default_billing_id 
		LEFT JOIN user_services u ON u.account_number = c.account_number 
		LEFT JOIN master_services m ON u.master_service_id = m.id 
		WHERE c.account_number = '$user' AND removed <> 'y'";
			
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	        //$DB->debug = true;
		$result = $DB->Execute($query) or die ("$l_queryfailed");
					
		echo "<p><h3>$l_services</h3><table><td>$l_id</td><td>$l_description</td><td>$l_details</td><td>$l_price</td><tr>";
		while ($myresult = $result->FetchRow())
		{
			$options_table = $myresult['m_options_table'];
			$u_id = $myresult['u_id'];
	
			// get the data from the options table and put into variables
			$query = "SELECT * FROM $options_table WHERE user_services = '$u_id'";
			$DB->SetFetchMode(ADODB_FETCH_NUM);
			$optionsresult = $DB->Execute($query);
			$myoptions = $optionsresult->fields;
			$optiondetails = $myoptions[2];
			
			$m_service_description = $myresult['m_service_description'];
			$m_pricerate = $myresult['m_pricerate'];
			$m_frequency = $myresult['m_frequency'];
			echo "<td>$u_id</td><td>$m_service_description</td><td>$optiondetails</td><td>\$$m_pricerate</td><tr>";
		}
		echo "</table>";
		echo "<p><a href=\"index.php?cmd=add_services\">$l_addnewservice</a>";

                break;

	case 'add_services':
		echo "<h3>$l_addservice</h3>";
		
		// get the list of services
		$query = "SELECT * FROM master_services WHERE selling_active = 'y' 
		AND hide_online <> 'y' ORDER BY service_description";

		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("$l_queryfailed");

		echo "<table><td>$l_description</td><td>$l_rate</td><td></td><tr>";
		while ($myresult = $result->FetchRow())
		{
			$id = $myresult['id'];
			$service_description = $myresult['service_description'];
			$pricerate = $myresult['pricerate'];
			
			echo "<td>$service_description</td><td>\$$pricerate</td>
			<td><a href=\"index.php?cmd=addingservice&id=$id\">$l_add</a></td><tr>";
		}
		echo '</table>';
		break;
		
	case 'addingservice':
		//GET Variables (sorta like the old way for now)
		$id = $base->input['id'];

		// get the service name and information from the master list
		$query = "SELECT * FROM master_services WHERE id = $id";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("$l_queryfailed");
		$myresult = $result->fields;		
		$servicename = $myresult['service_description'];
		$options_table_name = $myresult['options_table'];
		
		echo "<h3>$l_addservice: $servicename</h3>\n";
		echo "<form action=\"index.php\" method=\"POST\"><table>\n";
		
		// get a list of the service options to fill in
		if ($options_table_name <> '') {
			$query = "SHOW COLUMNS FROM $options_table_name";
			$DB->SetFetchMode(ADODB_FETCH_ASSOC);
			$result = $DB->Execute($query) or die ("$l_queryfailed");
		
			while ($myresult = $result->FetchRow()) {
				$fieldname = $myresult['Field'];
				$fulltype = $myresult['Type'];
				$fieldtype = substr($myresult['Type'], 0, 4);
				if($fieldname <> 'id' AND $fieldname <> 'user_services') {
					// print the fields in a form
					if ($fieldtype == "enum")
					{
						echo "<td bgcolor=\"ccccdd\"width=180><b>" . $fieldname . "</b></td><td bgcolor=\"#ddddee\"><select name=$fieldname>";

						# print all the items listed in the enum
						$enums = substr($fulltype,5,-1); 
						$enums = ereg_replace("'","",$enums); 
						$enums = explode(",",$enums); 
						foreach($enums as $val) { 
							echo "<option value='$val'>$val</option>\n\t"; 
						}//----end foreach 			
						echo "</select></td><tr>\n";
					} else {
						echo "<td bgcolor=\"ccccdd\"width=180><b>" . $fieldname . "</b></td><td bgcolor=\"#ddddee\"><input type=text name=$fieldname value=$myresult[$i]></td><tr>\n";
					}
					$fieldlist .= ',' . $fieldname;
				}
			}
			print "<input type=hidden name=fieldlist value=$fieldlist>\n";
		}
		print "<input type=hidden name=cmd value=servicerequest>";
		print "<input type=hidden name=servicename value=\"$servicename\">";
		print "<input type=hidden name=options_table_name value=$options_table_name><input type=hidden name=serviceid value=$id>";
		print "<td></td><td><input name=newservice type=submit value=\"$l_submitrequest\" class=smallbutton></td></table></form>";
		
		break;
	
	case 'servicerequest':
		// send a note via the customer_history to the activate_notify
		// or designate an online-notify citrusdb staff user for this stuff
		
		//GET Variables (sorta like the old way for now)
		$serviceid = $base->input['serviceid'];
		$servicename = $base->input['servicename'];
		$fieldlist = $base->input['fieldlist'];
		$fieldlist = substr($fieldlist, 1); 
		// loop through post_vars associative/hash to get field values
		
		$array_fieldlist = explode(",",$fieldlist);
		
		foreach ($base->input as $mykey => $myvalue)
		{
			foreach ($array_fieldlist as $myfield)
			{
				//print "$mykey<br>";
				if ($myfield == $mykey)
				{
					$fieldvalues .= ',\'' . $myvalue . '\'';
					//print "$fieldvalues<br>";
				}
			}
		}
		$fieldvalues = substr($fieldvalues, 1);
		
		// insert the information into customer history
		$account_number = $user;
		$status = "not done";
		
		$fieldlist = ereg_replace("'","",$fieldlist);
		$fieldvalues = ereg_replace("'","",$fieldvalues);
		
		$fieldlist = explode(",",$fieldlist); 
		$fieldvalues = explode(",",$fieldvalues); 
		$i = 0;
		foreach($fieldlist as $value) { 
			$myitem = $value . ': ' . $fieldvalues[$i]; 
			$descriptionlist .= ' ' . $myitem;
			$i++;
		}

		$description = "$l_onlineservicerequest:\n $l_add $servicename\n $descriptionlist";
				
		$query = "INSERT into customer_history (creation_date,created_by,notify,account_number,status,description) VALUES (CURRENT_TIMESTAMP,'online','$notify_user','$account_number','$status','$description')";
		$result = $DB->Execute($query) or die ("$l_queryfailed");
	
		// print a message and link back home
		echo "<p>$l_sentrequesttoaddnew $servicename<p><a href=\"index.php\">Home</a><br>";
		break;
		
	/*--------------------------------------------------------------------*/
	// Support Request
	/*--------------------------------------------------------------------*/
	case 'support':
		
		echo "<h3>$l_supportrequest:</h3>\n";
		echo "<form action=\"index.php\" method=\"POST\"><table>\n";
		echo "Message:<br><textarea rows=5 cols=40 name=message></textarea>";		
		print "<input type=hidden name=cmd value=supportrequest>";
		print "<td></td><td><input name=newservice type=submit value=\"Submit Request\" class=smallbutton></td></table></form>";
		break;
	
	case 'supportrequest':
		// send a note via the customer_history to the activate_notify
		// or designate an online-notify citrusdb staff user for this stuff
		
		//GET Variables (sorta like the old way for now)
		$message = $base->input['message'];
		
		// insert the information into customer history
		$account_number = $user;
		$status = "not done";

		$description = "$l_onlinesupportrequest:\n $message";
			
		$query = "INSERT into customer_history (creation_date,created_by,notify,account_number,status,description) VALUES (CURRENT_TIMESTAMP,'online','$notify_user','$account_number','$status','$description')";
		$result = $DB->Execute($query) or die ("$l_queryfailed");
	
		// print a message and link back home
		echo "<p>$l_messagesent. <p><a href=\"index.php\">$l_home</a><br>";
		break;
		
	/*--------------------------------------------------------------------*/
	// Billing
	/*--------------------------------------------------------------------*/
	case 'view_bill':
        	// view their invoices (much like the invoice maintenance tool)
		echo "<h3>$l_recentpayments</h3>";
		echo "<table cellspacing=2 cellpadding=2 border=0>
		<td bgcolor=\"#ddcccc\" width=100><b>$l_id</b></td>
		<td bgcolor=\"#ddcccc\" width=130><b>$l_date</b></td>
		<td bgcolor=\"#ddcccc\" width=200><b>$l_type</b></td>
		<td bgcolor=\"#ddcccc\" width=100><b>$l_status</b></td>
		<td bgcolor=\"#ddcccc\" width=100><b>$l_response</b></td>
		<td bgcolor=\"#ddcccc\" width=150><b>$l_amount</b></td>";

		// get the billing_history for this account, the account number 
		// is stored in the corresponding billing record

		$query = "SELECT p.id p_id, p.creation_date p_cdate, p.payment_type 
		p_payment_type, p.status p_status, p.billing_amount p_billing_amount, 
		p.response_code p_response_code, c.account_number c_acctnum,
		b.account_number b_acctnum, b.id b_id
		FROM payment_history p 
		LEFT JOIN billing b ON p.billing_id = b.id
		LEFT JOIN customer c ON b.account_number = c.account_number
		WHERE b.account_number = '$user' ORDER BY p.id DESC LIMIT 3";
		
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("$l_queryfailed");
		

		while ($myresult = $result->FetchRow())
		{
			$id = $myresult['p_id'];
			$date = $myresult['p_cdate'];
			$type = $myresult['p_payment_type'];
			$status = $myresult['p_status'];
			$response = $myresult['p_response_code'];
			$amount = $myresult['p_billing_amount'];

			print "<tr bgcolor=\"#ffeeee\">";
			print "<td>$id</td>";
			print "<td>$date</td>";
			print "<td>$type</td>";
			print "<td>$status</td>";
			print "<td>$response</td>";
                	print "<td>$amount</td>";
		}

		echo '</table>';
		
		echo "<h3>$l_recentbilling</h3>";
		echo "<table cellspacing=2 cellpadding=2 border=0>
		<td bgcolor=\"#ccdddd\" width=100><b>$l_invoicenum</b></td>
		<td bgcolor=\"#ccdddd\" width=130><b>$l_date</b></td>
		<td bgcolor=\"#ccdddd\" width=200><b>$l_type</b></td>
		<td bgcolor=\"#ccdddd\" width=100><b>$l_from</b></td>
		<td bgcolor=\"#ccdddd\" width=100><b>$l_to</b></td>
		<td bgcolor=\"#ccdddd\" width=100><b>$l_newcharges</b></td>
		<td bgcolor=\"#ccdddd\" width=150><b>$l_total</b></td>";

		// get the billing_history for this account, the account number is 
		// stored in the corresponding billing record
	
		$query = "SELECT h.id h_id, h.billing_id h_bid, h.billing_date h_bdate, 
		h.billing_type h_btype, h.from_date h_from, h.to_date h_to, h.total_due 
		h_total, h.new_charges h_new_charges, c.account_number c_acctnum, 
		b.account_number b_acctnum, b.id b_id 
		FROM billing_history h 
		LEFT JOIN billing b ON h.billing_id = b.id  
		LEFT JOIN customer c ON b.account_number = c.account_number
		WHERE b.account_number = '$user' ORDER BY h.id DESC LIMIT 3";
        
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("$l_queryfailed");
		
		while ($myresult = $result->FetchRow())
		{
			$id = $myresult['h_id'];
			$billing_date = $myresult['h_bdate'];
			$billing_type = $myresult['h_btype'];
			$from_date = $myresult['h_from'];
			$to_date = $myresult['h_to'];
			$new_charges = $myresult['h_new_charges'];
			$total_due = $myresult['h_total'];

			print "<tr bgcolor=\"#eeffff\">";
			print "<td>$id</td>";
			print "<td>$billing_date</td>";
			print "<td>$billing_type</td>";
			print "<td>$from_date</td>";
			print "<td>$to_date</td>";
			print "<td>$new_charges</td>";
			print "<td>$total_due</td>";
		}
	
		echo '</table>';
		echo "<p><a href=\"$payment_url\">$l_paybillonline</a>";
                break;

	case 'pay_bill':
        	// link to external online payment form?
                break;
		
	/*--------------------------------------------------------------------*/
	// Profile
	/*--------------------------------------------------------------------*/
	default:
		
		/*----------------------------------------------------*/
                // print customer and billing info
		/*----------------------------------------------------*/
		$query = "SELECT c.signup_date c_signup_date, c.name c_name, c.company c_company, c.street c_street, c.city c_city, 
		c.state c_state, c.zip c_zip, c.country c_country, c.phone c_phone, c.fax c_fax, c.source c_source, 
		c.contact_email c_contact_email, c.default_billing_id c_default_billing_id, 
		c.cancel_date c_cancel_date, c.removal_date c_removal_date, 
		b.name b_name, b.company b_company, b.street b_street, b.city b_city, b.state b_state, 
		b.country b_country, b.zip b_zip, b.phone b_phone, b.fax b_fax, b.contact_email b_contact_email, 
		b.account_number b_account_number, b.billing_type b_billing_type, 
		b.creditcard_number b_creditcard_number, b.creditcard_expire b_creditcard_expire, 
		b.billing_status b_billing_status, b.next_billing_date b_next_billing_date, 
		b.prev_billing_date b_prev_billing_date  
		FROM customer c LEFT JOIN billing b 
		ON b.id = c.default_billing_id 
		WHERE c.account_number = '$user'";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		//$DB->debug = true;
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
		$billingemail = $myresult['b_contact_email'];
                       
		echo "<h3>$l_profile</h3>";
		echo "Name: $name<br>
		Company: $company<br>
		Street: $street<br>
		City: $city<br>
		State: $state<br>
		Zip: $zip<br>
		Country: $country<br>
		Phone: $phone<br>
		Fax: $fax<br>
		Customer Contact Email: $contactemail<br>
		Billing Contact Email: $billingemail<br>";  
                break;
	}
}
else // show the login screen
{
echo "
<html>
<head>
<title>Customer Account Manager</title>
<LINK href=\"citrus.css\" type=text/css rel=STYLESHEET>
<script language=\"JavaScript\">
function h(oR) {
	oR.style.background-color='ffdd77';
}	
function deh(oR) {
	oR.style.background-color='ddddee';
}
function dehnew(oR) {
	oR.style.background-color='ddeeff';
}
</script>
</head>
<body bgcolor=\"#ffffff\">
	<script language=\"JavaScript\" src=\"include/md5.js\"></script>
	<div id=horizon>
		<div id=loginbox>
	<center><table><td valign=top>
	<h3>$l_customeraccountmanager</h3>
	<P>
	$l_logintext
	<P>
	<FORM ACTION=\"$ssl_url_prefix/index.php\" METHOD=\"POST\" AUTOCOMPLETE=\"off\">
	<B>$l_accountnumber:</B><BR>
	<INPUT TYPE=\"TEXT\" NAME=\"user_name\" VALUE=\"\" SIZE=\"15\" MAXLENGTH=\"15\">
	<P>
	<B>$l_password:</B><BR>
	<INPUT TYPE=\"password\" NAME=\"password\" VALUE=\"\" SIZE=\"15\" MAXLENGTH=\"32\">
	<P>
	<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_login\" class=smallbutton>
	</FORM>
	<P></td></table></div></div></body></html>";
}

?>
