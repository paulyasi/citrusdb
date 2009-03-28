<?php
// Copyright (C) 2002-2007  Paul Yasi <paul@citrusdb.org>, read the README file for more information

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

if (!isset($base->input['save'])) { $base->input['save'] = ""; }

// check if a billed by default is specified
if (!isset($base->input['bby'])) { $base->input['bby'] = ""; }
$bby = $base->input['bby'];

if ($base->input['save']) {

	//
	// GET Variables
	//
	$name = $base->input['name'];
    $company = $base->input['company'];
    $street = $base->input['street'];
    $city = $base->input['city'];
    $state = $base->input['state'];
	$country = $base->input['country'];
    $zip = $base->input['zip'];
    $phone = $base->input['phone'];
	$fax = $base->input['fax'];
	$contact_email = $base->input['contact_email'];
	$secret_question = $base->input['secret_question'];
	$secret_answer = $base->input['secret_answer'];
	$source = $base->input['source'];
	$organization_id = $base->input['organization_id'];

	//
    // make a new customer record
    //
	$query = "INSERT into customer (signup_date, name, company, street, city, state, country, 
		zip, phone, fax, contact_email, secret_question, secret_answer, source) 
		VALUES (CURRENT_DATE, '$name', '$company', '$street', '$city', '$state', '$country', 
		'$zip', '$phone', '$fax', '$contact_email', '$secret_question','$secret_answer','$source')";
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	
	$myinsertid = $DB->Insert_ID();  // is this the upcoming insert, not the previous one?
	$account_number=$myinsertid;
	
	//
	// start the session variables to hold the account number
	//
	//session_start();
	$_SESSION['account_number'] = $account_number;
	
	//
	// get the next billing date value
	//
	$mydate = get_nextbillingdate();
	
	// make a new billing record
	// set the next billing date and from date to the date determined from above for the first billing
	//
	$query = "INSERT into billing (account_number,next_billing_date,from_date,payment_due_date,
		name,company,street,city,state,country,zip,phone,fax,contact_email,organization_id) 
		VALUES ('$account_number','$mydate','$mydate','$mydate',
		'$name','$company','$street','$city','$state','$country','$zip','$phone','$fax','$contact_email','$organization_id')";
	$result = $DB->Execute($query) or die ("$l_queryfailed");	
	
	//
	// set the default billing ID for the customer record
	//
	$billingid = $DB->Insert_ID();
	$query = "UPDATE customer SET default_billing_id = '$billingid' WHERE account_number = $account_number";
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	
	print "<script language=\"JavaScript\">window.location.href = \"index.php?load=customer&type=module\";</script>";
}
else
{
//
// prompt for some standard information to put in the new customer record
//
echo "
<a href=\"index.php?load=customer&type=module\">[ $l_undochanges ]</a>
<table cellpadding=0 border=0 cellspacing=0 width=720>
<td valign=top width=720>
<form action=\"index.php\">
        <table cellpadding=5 cellspacing=1 border=0 width=720>
";

// print list of organizations to choose from
$query = "SELECT id,org_name FROM general";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
echo "<td bgcolor=\"#ccccdd\"><b>$l_organizationname</b></td><td bgcolor=\"#ddddee\"><select name=\"organization_id\">";
while ($myresult = $result->FetchRow()) {
	$myid = $myresult['id'];
	$myorg = $myresult['org_name'];
	if ($myid == $bby) {
	  echo "<option value=\"$myid\" selected>$myorg</option>";
	} else {
	  echo "<option value=\"$myid\">$myorg</option>";
	}
}
echo "</select></td><tr>";

echo "<td bgcolor=\"#ccccdd\"><b>$l_name</b></td><td bgcolor=\"#ddddee\"><input name=\"name\" type=text></td><tr>
        <td bgcolor=\"#ccccdd\"><b>$l_company</b></td><td bgcolor=\"#ddddee\"><input name=\"company\" type=text></td><tr>
        <td bgcolor=\"#ccccdd\"><b>$l_street</b></td><td bgcolor=\"#ddddee\"><input name=\"street\" type=text></td><tr>
        <td bgcolor=\"#ccccdd\"><b>$l_city</b></td><td bgcolor=\"#ddddee\"><input name=\"city\" type=text></td><tr>
        <td bgcolor=\"#ccccdd\"><b>$l_state</b></td><td bgcolor=\"#ddddee\"><input name=\"state\" type=text size=2></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_country</b></td><td bgcolor=\"#ddddee\"><input name=\"country\" type=text></td><tr>
        <td bgcolor=\"#ccccdd\"><b>$l_zip</b></td><td bgcolor=\"#ddddee\"><input name=\"zip\" size=5 type=text></td><tr>
        <td bgcolor=\"#ccccdd\"><b>$l_phone</b></td><td bgcolor=\"#ddddee\"><input name=\"phone\" type=text></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_fax</b></td><td bgcolor=\"#ddddee\"><input name=\"fax\" type=text></td><tr>
        <td bgcolor=\"#ccccdd\"><b>$l_contactemail</b></td><td bgcolor=\"#ddddee\"><input name=\"contact_email\" type=text></td><tr>
        <td bgcolor=\"#ccccdd\"><b>$l_secret_question</b></td><td bgcolor=\"#ddddee\"><input name=\"secret_question\" type=text></td><tr>
        <td bgcolor=\"#ccccdd\"><b>$l_secret_answer</b></td><td bgcolor=\"#ddddee\"><input name=\"secret_answer\" type=text></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_source</b></td><td bgcolor=\"#ddddee\"><input name=\"source\" type=text></td><tr>
        </table>
<br />
<center>
<input name=save type=submit class=smallbutton value=\"$l_add\">
<input type=hidden name=load value=customer>
<input type=hidden name=type value=module>
<input type=hidden name=create value=on>
<input type=hidden name=save value=on>

</center>
</td>
</table>
</form>
";


}
?>
