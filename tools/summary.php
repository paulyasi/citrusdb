<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_customersummary</h3>";
// Copyright (C) 2003-2008  Paul Yasi (paul at citrusdb.org), read the README file for more information

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

// GET Variables
if (!isset($base->input['organization_id'])) { $base->input['organization_id'] = "1"; }
$organization_id = $base->input['organization_id'];


// select the path_to_ccfile from settings
$query = "SELECT path_to_ccfile FROM settings WHERE id = '1'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$ccfileresult = $DB->Execute($query)
  or die ("$l_queryfailed");
$myccfileresult = $ccfileresult->fields;
$path_to_ccfile = $myccfileresult['path_to_ccfile'];

// open the file
$filename = "$path_to_ccfile/summary.csv";
$handle = fopen($filename, 'w'); // open the file

// ask for the organization that they want to view
echo "<FORM ACTION=\"index.php\" METHOD=\"GET\" name=\"form1\">
	<input type=hidden name=load value=summary>
	<input type=hidden name=type value=tools>";
	// print list of organizations to choose from
	$query = "SELECT id,org_name FROM general";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	echo "<b>$l_organizationname</b>
		<td><select name=\"organization_id\">
		<option value=\"\">$l_choose</option>";
	while ($myresult = $result->FetchRow()) {
		$myid = $myresult['id'];
		$myorg = $myresult['org_name'];
		echo "<option value=\"$myid\">$myorg</option>";
	}
echo "</select><input type=\"SUBMIT\" NAME=\"submit\" value=\"$l_submit\"><p>";


	echo "<table cellpadding=2><td><b>$l_services</b></td>
		<td><b>$l_organizationname</b></td>
		<td><b>$l_total</b></td><tr>";

	// initialize the count of paid monthly services
	$paidsubscriptions = 0;
	$count_creditcard = 0;
	$count_invoice = 0;
	$count_einvoice = 0;
	$count_prepay = 0;
	$count_prepaycc = 0;
	
	// get the number of customers per service
	$query = "SELECT m.id m_id, m.service_description m_servicedescription, m.pricerate m_pricerate, 
	m.frequency m_frequency, m.organization_id m_organization_id, g.org_name g_org_name,  
	u.removed u_removed, u.master_service_id u_msid, count(m.id) AS TotalNumber, 
	b.id b_id, b.billing_type b_billing_type, bt.id bt_id, bt.method bt_method 
	FROM user_services u
	LEFT JOIN master_services m ON u.master_service_id = m.id
	LEFT JOIN billing b ON b.id = u.billing_id 
	LEFT JOIN billing_types bt ON b.billing_type = bt.id
	LEFT JOIN general g ON m.organization_id = g.id
	WHERE u.removed <> 'y' AND bt.method <> 'free' AND b.organization_id = '$organization_id' GROUP BY m.id ORDER BY TotalNumber";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	while ($myresult = $result->FetchRow()) {
		$service = $myresult['m_servicedescription'];
		$count = $myresult['TotalNumber'];
		$pricerate = $myresult['m_pricerate'];
		$billingmethod = $myresult['bt_method'];
		$servicefrequency = $myresult['m_frequency'];
		$org_name = $myresult['g_org_name'];
		echo "<td>$service</td><td>$org_name</td><td>$count</td><tr>";
		$newline = "$service,$count\n";
		fwrite($handle, $newline); // write to the file

		// check if the are a paid monthly service and add to count
		if (($pricerate > 0) AND ($billingmethod <> 'free') AND ($servicefrequency > 0)) {
			$paidsubscriptions = $paidsubscriptions + $count;
		}		
	}
fclose($handle);
// print link to download the summary file
echo "<a href=\"index.php?load=tools/downloadfile&type=dl&filename=summary.csv\"><u class=\"bluelink\">$l_download summary.csv</u></a><p>";


	echo "</table><p>

	$l_paidsubscriptions: $paidsubscriptions
	<p>";

	// get the total services for each billing type
	$query = "SELECT m.id m_id, m.service_description m_servicedescription, m.pricerate m_pricerate, 
	m.frequency m_frequency, m.organization_id m_organization_id, g.org_name g_org_name,  
	u.removed u_removed, u.master_service_id u_msid, count(bt.method) AS TotalNumber, 
	b.id b_id, b.billing_type b_billing_type, bt.id bt_id, bt.method bt_method 
	FROM user_services u
	LEFT JOIN master_services m ON u.master_service_id = m.id
	LEFT JOIN billing b ON b.id = u.billing_id 
	LEFT JOIN billing_types bt ON b.billing_type = bt.id
	LEFT JOIN general g ON m.organization_id = g.id
	WHERE u.removed <> 'y' AND bt.method <> 'free' AND b.organization_id = '$organization_id' AND m.pricerate > '0' 
		AND m.frequency > '0' GROUP BY bt.method ORDER BY TotalNumber";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	echo "<blockquote>";
	while ($myresult = $result->FetchRow()) {
		$count = $myresult['TotalNumber'];
		$billingmethod = $myresult['bt_method'];
		echo "$billingmethod: $count<br>\n";	
	}
	echo "</blockquote>";
	
	// get the number of customers
    $query = "SELECT COUNT(*) FROM customer WHERE cancel_date is NULL";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	$myresult = $result->fields;
    $numofcustomers = $myresult['COUNT(*)'];
    
    print "<hr>$l_totalcustomers: $numofcustomers<p>";
    
    
    // get the number of customers who are not free
    $query = "SELECT COUNT(*) FROM customer c
	LEFT JOIN billing b ON b.id = c.default_billing_id 
	LEFT JOIN billing_types bt ON b.billing_type = bt.id
	WHERE cancel_date is NULL AND bt.method <> 'free'";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	$myresult = $result->fields;
    $numofcustomers = $myresult['COUNT(*)'];

	print "$l_totalpayingcustomers: $numofcustomers<p>";
	
?>
</body>
</html>







