<?php
// Copyright (C) 2005-2007  Paul Yasi <paul@citrusdb.org>, read the README file for more information
/*----------------------------------------------------------------------------*/
// Check for authorized accesss
/*----------------------------------------------------------------------------*/
echo "<h3>$l_paymentreport</h3>";
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

// Get Variables
if (!isset($base->input['day'])) { $base->input['day'] = ""; }
$day = $base->input['day'];

if ($day) {

	echo "<b>$day</b>";
	
	// Today's Total:
	$query = "SELECT ROUND(SUM(billing_amount),2) AS TotalAmount FROM payment_history
	WHERE status = 'authorized' AND creation_date = '$day'";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("$l_queryfailed");
    $myresult = $result->fields;
    $total_amount = $myresult['TotalAmount'];
	echo "<p>$l_total: $l_currency$total_amount<p>";
	
	// show all authorized payments, eg any credit, eft, check, and cash payments made that day
	$query = "SELECT * FROM payment_history 
		WHERE status = 'authorized' AND creation_date = '$day'";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("$l_queryfailed");

	echo "<table><td>$l_billingid</td><td>$l_name</td><td>$l_type</td><td>$l_amount</td><td></td><tr>";
	
	while ($myresult = $result->FetchRow()) {
		$billing_id = $myresult['billing_id'];
		$name = $myresult['name'];
		$payment_type = $myresult['payment_type'];
		$response_code = $myresult['response_code'];
		$billing_amount = $myresult['billing_amount'];
		
		echo "<td>$billing_id</td>";
		echo "<td>$name</td>";
		echo "<td>$payment_type</td>";
		echo "<td>$billing_amount</td>";
		echo "<tr>";
	}
	echo "</table>";
}
else {
	// show the form to pick what day to view
	$day_1  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));
	$day_2  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-2, date("Y")));
	$day_3  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-3, date("Y")));
	$day_4  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-4, date("Y")));
	$day_5  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-5, date("Y")));
	
	echo "<FORM ACTION=\"index.php\" METHOD=\"GET\">
	<table>
	<td>$l_select:</td><td>
	<select name=\"day\">
	<option>$day_1</option>
	<option>$day_2</option>
	<option>$day_3</option>
	<option>$day_4</option>
	<option>$day_5</option>
	</select>
	<input type=hidden name=load value=billing>
	<input type=hidden name=tooltype value=module>
	<input type=hidden name=type value=tools>
	<input type=hidden name=paymentreport value=on>
	</td><tr> 
	<td></td><td><br><input type=submit name=\"$l_submit\"></td>
	</table>
	</form> ";
}	
?>
</body>
</html>







