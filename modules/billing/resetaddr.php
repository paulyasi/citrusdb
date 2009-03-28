<?php   
// Copyright (C) 2002-2005  Paul Yasi <paul@citrusdb.org>, read the README file for more information

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

if ($save) {
	// get the customer information
	$query = "SELECT * FROM customer WHERE account_number = $account_number";
        $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	$myresult = $result->fields;

        $name = $myresult['name'];
        $company = $myresult['company'];
        $street = $myresult['street'];
        $city = $myresult['city'];
        $state = $myresult['state'];
        $zip = $myresult['zip'];
        $country = $myresult['country'];
        $phone = $myresult['phone'];
        $fax = $myresult['fax'];
        $contact_email = $myresult['contact_email'];
	$default_billing_id = $myresult['default_billing_id'];	

	// save billing address
	$query = "UPDATE billing 
	SET name = '$name',
	company = '$company',
	street = '$street',
	city = '$city',
	state = '$state',
	zip = '$zip',
	country = '$country',
	phone = '$phone',
	fax = '$fax',
	contact_email = '$contact_email' WHERE id = $default_billing_id";
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	print "<h3>$l_changessaved<h3>";
        print "<script language=\"JavaScript\">window.location.href = \"index.php?load=billing&type=module\";</script>";
}
else
{	

print "<br><br>";
        print "<h4>$l_areyousuredefaultbillingaddress</h4>";
        print "<table cellpadding=15 cellspacing=0 border=0 width=720><td align=right width=360>";

        print "<form style=\"margin-bottom:0;\" action=\"index.php\">";
        print "<input type=hidden name=load value=billing>";
        print "<input type=hidden name=type value=module>";
        print "<input type=hidden name=resetaddr value=on>";
	print "<input type=hidden name=account_number value=$account_number>";
        print "<input name=save type=submit value=\" $l_yes \" class=smallbutton></form></td>";

        print "<td align=left width=360><form style=\"margin-bottom:0;\" action=\"index.php\">";
        print "<input name=done type=submit value=\" $l_no  \" class=smallbutton>";
        print "<input type=hidden name=load value=billing>";
        print "<input type=hidden name=type value=module>";
	
        print "</form></td></table>";

}
?>
