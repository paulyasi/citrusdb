<?php
// Copyright (C) 2003-2005  Paul Yasi <paul@citrusdb.org>
//read the README file for more information

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

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['manager'] == 'n') {
	echo "$l_youmusthaveadmin<br>";
        exit; 
}
	
	//
	// get the list of unique account numbers that have new services today
	//
	$query = "SELECT c.account_number c_ac, c.name c_name, c.company c_company, 
	c.street c_street, c.city c_city, c.state c_state, c.country c_country, c.zip c_zip, 
	c.signup_date c_signup_date, 
	g.org_name g_org_name, g.org_street g_org_street, g.org_city g_org_city, 
	g.org_state g_org_state, g.org_zip g_org_zip, g.org_country g_org_country, 
	g.phone_custsvc g_phone_custsvc, g.email_custsvc g_email_custsvc  
	FROM customer c
	LEFT JOIN general g ON g.id = 1 
	WHERE CURRENT_DATE = c.signup_date";
	
	$ac_result = $DB->Execute($query) or die ("$l_queryfailed");
	
	//
	// loop through results and print out a letter with new services for each account with a matching distinct ID
	//
	while ($myac_result = $ac_result->FetchRow())
	{
		$result_acctnumber = $myac_result['c_ac'];
		$result_name = $myac_result['c_name'];
		$result_company = $myac_result['c_company'];
		$result_street = $myac_result['c_street'];
		$result_city = $myac_result['c_city'];
		$result_state = $myac_result['c_state'];
		$result_country = $myac_result['c_country'];
		$result_zip = $myac_result['c_zip'];
		$result_org_name = $myac_result['g_org_name'];
		$result_org_street = $myac_result['g_org_street'];
		$result_org_city = $myac_result['g_org_city'];
		$result_org_state = $myac_result['g_org_state'];
		$result_org_zip = $myac_result['g_org_zip'];
		$result_phone_custsvc = $myac_result['g_phone_custsvc'];
		$result_email_custsvc = $myac_result['g_email_custsvc'];
		
		print "<div style=\"page: auto\">";
		print "<center><b>$result_org_name</b><br>";
		print "$result_org_street<br>$result_org_city $result_org_state $result_org_zip</center><p>";
		print "$l_accountnumber: $result_acctnumber<p>";
		print "$result_name<br>";
		print "$result_company<br>";
		print "$result_street<br>";
		print "$result_city $result_state $result_zip<br>";
		print "$result_country<br>";
		print "<p>$l_thankyouforordering:<br><ul>";

			
		//
		// get the list of new services for that customer
		//
		//$DB->debug = true;
		
		$query = "SELECT u.id u_id, u.account_number u_ac, u.master_service_id u_master_service_id, 
		u.billing_id u_bid, u.start_datetime u_start, u.removed u_rem, u.usage_multiple u_usage, 
		m.service_description m_service_description, m.id m_id, m.pricerate m_pricerate, 
		m.frequency m_freq, c.account_number c_account_number,  
		c.name c_name,c.company c_company, c.street c_street, c.city c_city, c.state c_state, 
		c.country c_country, c.zip c_zip, g.id g_id, g.org_name g_org_name
		FROM user_services u 
		LEFT JOIN master_services m ON m.id = u.master_service_id 
		LEFT JOIN customer c ON c.account_number = u.account_number 
		LEFT JOIN general g ON g.id = 1 
		WHERE to_days(now()) = to_days(u.start_datetime) AND u.account_number = $result_acctnumber";	
		
		$result = $DB->Execute($query) or die ("$l_queryfailed");
	
		while ($myresult = $result->FetchRow())
		{
	    	$user_services_id = $myresult['u_id'];
			$service_description = $myresult['m_service_description'];
			$account_number = $myresult['u_ac'];
			$name = $myresult['c_name'];
			$company = $myresult['c_company'];
			$street = $myresult['c_street'];
			$city = $myresult['c_city'];
			$state = $myresult['c_state'];
			$zip = $myresult['c_zip'];
			$country = $myresult['c_country'];
			$org_name = $myresult['g_org_name'];
		
			// print the services
			
			print "<li>$service_description";
			
		}
		
		print "</ul><p></p>";
		
		// print generic terms of service and other account info for the customer
		
		echo "$l_yourserviceisactive $result_phone_custsvc $l_oremail 
		$result_email_custsvc
		<p>$l_pleaseseeourterms";
		
		print "</div>";
		print "<DIV style=\"page-break-after:always\"></DIV>\n"; // puts in a page-break
	

	// end table listing
    print "<p>\n";
	}

?>

