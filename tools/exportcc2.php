<?php
// Copyright (C) 2002-2005  Paul Yasi <paul@citrusdb.org>
// read the README file for more information

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

echo "<h3>$l_exportcreditcards</h3>
<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"include/CalendarPopup.js\"></SCRIPT>
	<SCRIPT LANGUAGE=\"JavaScript\">
	var cal = new CalendarPopup();
	</SCRIPT>
";

//Include the billing functions
include('include/billing.inc.php');

//GET Variables
$submit = $base->input['submit'];
$billingdate = $base->input['billingdate'];

// make sure the user is in a group that is allowed to run this

if ($submit) {
	
	//$DB->debug = true;

	// determine the next available batch number
        $batchid = get_nextbatchnumber($DB);
	echo "BATCH: $batchid<p>\n";
	
	// Add creditcard taxes and services to the bill
	$numtaxes = add_taxdetails($DB, $billingdate, creditcard, $batchid);
	$numservices = add_servicedetails($DB, $billingdate, creditcard, $batchid);
	echo "creditcard: $numtaxes added, $numservices added<p>\n";

	// Add prepaycc taxes and services to the bill
	$numpptaxes = add_taxdetails($DB, $billingdate, prepaycc, $batchid);
	$numppservices = add_servicedetails($DB, $billingdate, prepaycc, $batchid);
	echo "prepaycc: $numpptaxes added, $numppservices added<p>\n";

	// Update Reruns to the bill
	$numreruns = update_rerundetails($DB, $billingdate);
	echo "$numreruns reruns<p>\n";
	
	// create billinghistory
	create_billinghistory($DB, $batchid);

	// print the credit card billing to a file
	// TODO
	echo "done";	
}
else {
// ask for the billing date that they want to invoice
echo '<FORM ACTION="'. $PHP_SELF .'" METHOD="POST" name="form1">
	<input type=hidden name=load value=exportcc2>
	<input type=hidden name=type value=tools>
	<table>
	<td></td><td>YYYY-MM-DD
	
	</td><tr>
	<td>What date would you like to bill:</td><td><input type=text name=billingdate>
	<A HREF="#"
	onClick="cal.select(document.forms[\'form1\'].billingdate,\'anchor1\',\'yyyy-MM-dd\'); 
	return false;"
	NAME="anchor1" ID="anchor1" style="color:blue">[select]</A></td><tr>
	<td></td><td><INPUT TYPE="SUBMIT" NAME="submit" value="submit"></td>
	</form>';
}

?>

