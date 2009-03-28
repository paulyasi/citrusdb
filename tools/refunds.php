<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_creditcard $l_refundreport</h3>";
// Copyright (C) 2008  Paul Yasi (paul at citrusdb.org)
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
if (!isset($base->input['day1'])) { $base->input['day1'] = ""; }
if (!isset($base->input['day2'])) { $base->input['day2'] = ""; }
if (!isset($base->input['organization_id'])) { $base->input['organization_id'] = ""; }

$day1 = $base->input['day1'];
$day2 = $base->input['day2'];
$organization_id = $base->input['organization_id'];


echo "<FORM ACTION=\"index.php\" METHOD=\"GET\">
	<table>
	<td>From: <input type=text name=\"day1\" value=\"$day1\" size=10> - </td>
	<td>To: <input type=text name=\"day2\" value=\"$day2\" size=10></td>";

        // print list of organizations to choose from
        $query = "SELECT id,org_name FROM general";
        $DB->SetFetchMode(ADODB_FETCH_ASSOC);
        $result = $DB->Execute($query) or die ("$l_queryfailed");
        echo "<td><b>$l_organizationname</b></td>
                <td><select name=\"organization_id\">
                <option value=\"\">$l_choose</option>";
        while ($myresult = $result->FetchRow()) {
                $myid = $myresult['id'];
                $myorg = $myresult['org_name'];
                echo "<option value=\"$myid\">$myorg</option>";
        }

	echo "<input type=hidden name=type value=tools>
	<input type=hidden name=load value=refunds>
	</td><tr> 
	<td></td><td><br><input type=submit name=\"$l_submit\" value=\"submit\"></td>
	</table>
	</form> ";

if ($day1) {

	//$DB->debug = true;

	// show service refunds for a specified date range according to 
	// their refund_date
	/*
	$query = "SELECT bd.refund_amount, bd.refund_date, us.account_number, cu.name, 
		ms.category service_category, ms.service_description service_description, bd.invoice_number 
		FROM billing_details bd
		LEFT JOIN user_services us ON us.id = bd.user_services_id
		LEFT JOIN master_services ms ON us.master_service_id = ms.id
		LEFT JOIN customer cu ON us.account_number = cu.account_number
		WHERE bd.refund_date
		BETWEEN '$day1' AND '$day2' 
		AND bd.user_services_id IS NOT NULL AND bd.refunded = 'y' ORDER BY bd.refund_date"; 
	*/
	$query = "SELECT ph.creation_date, ph.billing_id, ph.creditcard_number, ph.billing_amount, bi.name, bi.account_number FROM payment_history ph 
			LEFT JOIN billing bi ON bi.id = ph.billing_id
			WHERE ph.status = 'credit' AND bi.organization_id = $organization_id 
			AND ph.creation_date BETWEEN '$day1' AND '$day2'";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
        $result = $DB->Execute($query) or die ("$l_queryfailed");


	// get the organization info
	$query = "SELECT org_name FROM general WHERE id = $organization_id LIMIT 1";
	$orgresult = $DB->Execute($query) or die ("$l_queryfailed");
	$myorgresult = $orgresult->fields;
	$organization_name = $myorgresult['org_name']; 

	echo "<b>$organization_name: $day1 $l_to $day2</b>";
	
	echo "<p><table cellpadding=5 border=1 cellspacing=0><td>$l_date</td><td>$l_accountnumber</td><td>$l_name</td><td>$l_creditcard</td><td>$l_amount</td><tr>";
	while ($myresult = $result->FetchRow()) {
		$creation_date = $myresult['creation_date'];
		$billing_id = $myresult['billing_id'];
		$creditcard_number = $myresult['creditcard_number'];
		$amount = $myresult['billing_amount'];
		$account_number = $myresult['account_number'];
		$name = $myresult['name'];
		echo "<td>$creation_date</td><td>$account_number</td><td>$name</td><td>$creditcard_number</td><td>$amount</td><tr>";
	}


	echo "</table>";

}
		
?>
</body>
</html>







