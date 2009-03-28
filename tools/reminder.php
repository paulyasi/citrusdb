<html>
<body bgcolor="#ffffff">
<SCRIPT LANGUAGE="JavaScript" SRC="include/CalendarPopup.js"></SCRIPT>
	<SCRIPT LANGUAGE="JavaScript">
	var cal = new CalendarPopup();
	</SCRIPT>
<?php
echo "<h3>$l_sendreminders</h3>";
// Copyright (C) 2003  Paul Yasi <paul@citrusdb.org>, read the README file for more information
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
if (!isset($base->input['billingdate'])) { $base->input['billingdate'] = ""; }

$submit = $base->input['submit'];
$billingdate = $base->input['billingdate'];

// make sure the user is in a group that is allowed to run this

if ($submit) {
	// join the user_services, billing, biling_types, and master_services together to find what to put into billing_details
	 
	//$DB->debug = true;
			
	$query = "SELECT u.id u_id, u.account_number u_ac, u.master_service_id u_msid, 
	u.billing_id u_bid, 
	u.removed u_rem, u.usage_multiple u_usage, 
	b.next_billing_date b_next_billing_date, b.id b_id, b.billing_type b_type, 
	t.id t_id, t.frequency t_freq, t.method t_method, 
	m.id m_id, m.pricerate m_pricerate, m.frequency m_freq 
	FROM user_services u
	LEFT JOIN master_services m ON u.master_service_id = m.id
	LEFT JOIN billing b ON u.billing_id = b.id
	LEFT JOIN billing_types t ON b.billing_type = t.id
	WHERE b.next_billing_date = '$billingdate' AND t.method = 'prepay' AND u.removed <> 'y'";

	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");

	// determine the next available batch number
        // insert empty into the batch to generate next value
        $query = "INSERT INTO batch () VALUES ()";
        $batchquery = $DB->Execute($query) or die ("$l_queryfailed");
        // get the value just inserted
        $batchid = $DB->Insert_ID();

	$i = 0; // count the billing services
	while ($myresult = $result->FetchRow())
	{
		$billing_id = $myresult['u_bid'];
		$user_services_id = $myresult['u_id'];
		$pricerate = $myresult['m_pricerate'];
		$usage_multiple = $myresult['u_usage'];
		$service_freq = $myresult['m_freq'];
		$billing_freq = $myresult['t_freq'];

		if ($service_freq > 0)
		{
			$billed_amount = ($billing_freq/$service_freq)*($pricerate*$usage_multiple);
		}
		else
		{
			$billed_amount = ($pricerate*$usage_multiple);
			// then set the service to removed - since it's a one time thing
			$query = "UPDATE user_services SET removed = 'y' WHERE id = '$user_services_id'";
			$onetimeresult = $DB->Execute($query) or die ("$l_queryfailed");
		}

		print "$billing_id $user_services_id $pricerate<br>";
		
		// insert this into the billing_details
		$query = "INSERT INTO billing_details (billing_id, creation_date, user_services_id, billed_amount, batch) 
			VALUES ('$billing_id',CURRENT_DATE,'$user_services_id','$billed_amount','$batchid')";
		$invoiceresult = $DB->Execute($query) or die ("$l_queryfailed");

		$i ++;
	}

	echo "$i $l_services $billingdate<br>";

	echo "<FORM ACTION=\"index.php\" METHOD=\"GET\">
        	<input type=hidden name=load value=remindersave>
		<input type=hidden name=type value=tools>
		<input type=hidden name=mydate value=\"$billingdate\">
		<input type=hidden name=batchid value=\"$batchid\">
		<input type=submit name=\"submit\" value=\"$l_submit\">
		</form>";
}
else {
// ask for the billing date that they want to invoice
echo "<FORM ACTION=\"index.php\" METHOD=\"GET\" name=\"form1\">
	<input type=hidden name=load value=reminder>
	<input type=hidden name=type value=tools>
	<table>
	<td></td><td>YYYY-MM-DD</td><tr>
	<td>$l_whatdatewouldyouliketoremind:</td><td><input type=text name=billingdate>
	<A HREF=\"#\"
	onClick=\"cal.select(document.forms['form1'].billingdate,'anchor1','yyyy-MM-dd'); 
	return false;\"
	NAME=\"anchor1\" ID=\"anchor1\" style=\"color:blue\">[$l_select]</A></td><tr>
	<td></td><td><INPUT TYPE=\"SUBMIT\" NAME=\"submit\" value=\"$l_submit\"></td>
	</form>";
}

?>
