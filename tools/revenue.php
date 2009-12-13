<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_revenuereport</h3>";
// Copyright (C) 2003-2009  Paul Yasi <paul at citrusdb.org>
// Read the README file for more information
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


$empty_day_1  = date("Y-m-d", mktime(0, 0, 0, date("m")-1  , date("d"), date("Y")));
$empty_day_2  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));

// Get Variables
if (!isset($base->input['day1'])) { $base->input['day1'] = "$empty_day_1"; }
if (!isset($base->input['day2'])) { $base->input['day2'] = "$empty_day_2"; }

$day1 = $base->input['day1'];
$day2 = $base->input['day2'];

echo "<FORM ACTION=\"index.php\" METHOD=\"GET\">
$l_foritemsbilledduringthisperiod<p>
	<table>
	From: <input type=text name=\"day1\" value=\"$day1\"> - 
	To: <input type=text name=\"day2\" value=\"$day2\">
	<input type=hidden name=type value=tools>
	<input type=hidden name=load value=revenue>
	</td><tr> 
	<td></td><td><br><input type=submit name=\"$l_submit\" value=\"submit\"></td>
	</table>
	</form> ";

if ($day1) {

  //$DB->debug = true;
  
  // show payments for a specified date range according to 
  // their service category
  
  $query = "SELECT ROUND(SUM(bd.paid_amount),2) AS CategoryTotal, 
		COUNT(DISTINCT us.id) As ServiceCount, 
		ms.category service_category, 
		ms.service_description, service_description,  
		g.org_name g_org_name
		FROM billing_details bd
		LEFT JOIN user_services us ON us.id = bd.user_services_id 
		LEFT JOIN master_services ms ON us.master_service_id = ms.id 
		LEFT JOIN general g ON ms.organization_id = g.id 
		WHERE bd.creation_date BETWEEN '$day1' AND '$day2'
		AND bd.taxed_services_id IS NULL 
		GROUP BY ms.id ORDER BY ms.category";
	
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");

  echo "<table><td>$l_service</td><td>$l_organizationname</td><td>$l_category</td><td>$l_amount</td>
		<tr>";
  while ($myresult = $result->FetchRow()) {
    $category_total = $myresult['CategoryTotal'];
    $service_description = $myresult['service_description'];
    $service_category = $myresult['service_category'];
    $count = $myresult['ServiceCount'];
    $org_name = $myresult['g_org_name'];
    echo "<td>$service_description</td><td>$org_name</td>
			<td>$service_category</td>
			<td>$category_total ($count)</td><tr>";
  }
  echo "</table>";
  
  // show taxes for a specified date range according to 
  // their tax rate description
  $query = "SELECT ROUND(SUM(bd.paid_amount),2) 
				AS CategoryTotal,
			COUNT(DISTINCT bd.id) As ServiceCount,  
			tr.description tax_description  
			FROM billing_details bd 
			LEFT JOIN taxed_services ts 
				ON bd.taxed_services_id = ts.id 
			LEFT JOIN tax_rates tr ON ts.tax_rate_id = tr.id 
			WHERE bd.creation_date BETWEEN '$day1' AND '$day2' 
			AND bd.taxed_services_id IS NOT NULL 
			GROUP BY tr.id";
  
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  
  echo "<p><table><td>$l_tax</td><td>$l_amount</td><tr>";
  while ($myresult = $result->FetchRow()) {
    $category_total = $myresult['CategoryTotal'];
    $count = $myresult['ServiceCount'];
    $tax_description = $myresult['tax_description'];
    echo "<td>$tax_description</td><td>$category_total ($count)</td><tr>";
  }
  echo "</table>";
  
  // show credits for a specified date range according to 
  // their credit_options description
  $query = "SELECT ROUND(SUM(bd.paid_amount),2) AS CategoryTotal, 
			COUNT(DISTINCT us.id) As ServiceCount, 
			cr.description credit_description, 
			g.org_name g_org_name 
			FROM billing_details bd
			LEFT JOIN user_services us ON us.id = bd.user_services_id 
			LEFT JOIN master_services ms ON us.master_service_id = ms.id 
			LEFT JOIN credit_options cr ON cr.user_services = us.id
			LEFT JOIN general g ON g.id = ms.organization_id 
			WHERE bd.creation_date BETWEEN '$day1' AND '$day2' 
			AND bd.taxed_services_id IS NULL 
			AND ms.id = 1  
			GROUP BY cr.description"; 
	
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");

  echo "<p><table><td>$l_credit</td><td>$l_organizationname</td><td>$l_amount</td><tr>";
  while ($myresult = $result->FetchRow()) {
    $category_total = $myresult['CategoryTotal'];
    $count = $myresult['ServiceCount'];
    $credit_description = $myresult['credit_description'];
    $org_name = $myresult['g_org_name'];
    echo "<td>$credit_description</td><td>$org_name</td><td>$category_total ($count)</td><tr>";
  }
  echo "</table>";
  
  // show service refunds for a specified date range according to 
  // their refund_date
  $query = "SELECT ROUND(SUM(bd.refund_amount),2) AS CategoryTotal,
			COUNT(DISTINCT us.id) As ServiceCount,  
			ms.category service_category, 
			ms.service_description service_description, 
			g.org_name g_org_name    
			FROM billing_details bd
			LEFT JOIN user_services us 
				ON us.id = bd.user_services_id 
			LEFT JOIN master_services ms 
				ON us.master_service_id = ms.id
			LEFT JOIN general g 
				ON g.id = ms.organization_id  
			WHERE bd.refund_date BETWEEN '$day1' AND '$day2' 
			AND bd.taxed_services_id IS NULL 
			GROUP BY ms.id"; 
	
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
	
  echo "<p><table><td>$l_refund</td><td>$l_organizationname</td><td>$l_category</td><td>$l_amount</td><tr>";
  while ($myresult = $result->FetchRow()) {
    $category_total = $myresult['CategoryTotal'];
    $service_category = $myresult['service_category'];
    $count = $myresult['ServiceCount'];
    $org_name = $myresult['g_org_name'];
    $service_description = $myresult['service_description'];
    echo "<td>$service_description</td><td>$org_name</td><td>$service_category</td><td>$category_total ($count)</td><tr>";
  }
  

  // show tax refunds for a specified date range according to 
  // their tax rate description
  $query = "SELECT ROUND(SUM(bd.refund_amount),2) 
				AS CategoryTotal,
			COUNT(DISTINCT bd.id) As ServiceCount,  
			tr.description tax_description  
			FROM billing_details bd 
			LEFT JOIN taxed_services ts 
				ON bd.taxed_services_id = ts.id 
			LEFT JOIN tax_rates tr ON ts.tax_rate_id = tr.id 
			WHERE bd.refund_date BETWEEN '$day1' AND '$day2' 
			AND bd.taxed_services_id IS NOT NULL 
			GROUP BY tr.id";
	
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");

  while ($myresult = $result->FetchRow()) {
    $category_total = $myresult['CategoryTotal'];
    $tax_description = $myresult['tax_description'];
    $count = $myresult['ServiceCount'];
    echo "<td>$tax_description</td><td></td><td>$l_tax</td><td>$category_total($count)</td><tr>";
  }
  echo "</table>";


  
  // show discounts entered for a specified date range
  $query = "SELECT ph.billing_amount, ph.invoice_number, ".
    "ph.creation_date, bi.name, bi.company ".
    "FROM payment_history ph ".
    "LEFT JOIN billing bi ON ph.billing_id = bi.id ".
    "WHERE ph.creation_date BETWEEN '$day1' AND '$day2' ".
    "AND ph.payment_type = 'discount'";
  
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");

  echo "<p><table><td>$l_discount</td><td>$l_name</td><td>$l_amount</td><tr>";
  while ($myresult = $result->FetchRow()) {
    $invoice_number = $myresult['invoice_number'];    
    $date = humandate($myresult['creation_date'], $lang);    
    $name = $myresult['name'];
    $company = $myresult['company'];    
    $amount = $myresult['billing_amount'];    
    echo "<td>$date ($invoice_number)</td><td>$name $company</td><td>$amount</td><tr>";
  }
  echo "</table>";
  
  
}
	
?>
</body>
</html>







