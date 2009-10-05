<?php
// Copyright (C) 2002-2008  Paul Yasi <paul@citrusdb.org>
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
//include('include/billing.inc.php');

//GET Variables
if (!isset($base->input['billingdate'])) { $base->input['billingdate'] = ""; }
if (!isset($base->input['organization_id'])) { $base->input['organization_id'] = ""; }
if (!isset($base->input['billingdate1'])) { $base->input['billingdate1'] = ""; }
if (!isset($base->input['billingdate2'])) { $base->input['billingdate2'] = ""; }

$submit = $base->input['submit'];
$billingdate = $base->input['billingdate'];
$organization_id = $base->input['organization_id'];
$billingdate1 = $base->input['billingdate1'];
$billingdate2 = $base->input['billingdate2'];

// make sure the user is in a group that is allowed to run this

if ($submit) {
	
	//$DB->debug = true;

	/*--------------------------------------------------------------------*/
	// Create the billing data
	/*--------------------------------------------------------------------*/

	// determine the next available batch number
        $batchid = get_nextbatchnumber($DB);
	echo "$l_batch: $batchid<p>\n";
	
	//
	// Check if they are doing a billing date range or just one date
	//
	
	$totalall = 0;

	if ($billingdate2) {
		$startdate = $billingdate1;
		$enddate = $billingdate2;
		$mydate = $startdate;
		echo "Date Range: $startdate - $enddate<p>\n";
		while ($mydate <= $enddate) {
        		echo "Processing $mydate<br>\n";
        		
			// Add creditcard taxes and services to the bill
			$numtaxes = add_taxdetails($DB, $mydate, NULL, 
			'creditcard', $batchid, $organization_id);
			$numservices = add_servicedetails($DB, $mydate, NULL, 
			'creditcard', $batchid, $organization_id);
			echo "$l_creditcard: $numtaxes $l_added, 
				$numservices $l_added<p>\n";

			// Add prepaycc taxes and services to the bill
			$numpptaxes = add_taxdetails($DB, $mydate, NULL, 
			'prepaycc', $batchid, $organization_id);
			$numppservices = add_servicedetails($DB, $mydate, NULL,
			'prepaycc', $batchid, $organization_id);
			echo "$l_prepay $l_creditcard: $numpptaxes $l_added, 
				$numppservices $l_added<p>\n";

			// Update Reruns to the bill
			$numreruns = update_rerundetails($DB, $mydate, 
				$batchid, $organization_id);
			echo "$numreruns $l_rerun<p>\n";

			// make the next date to check	
			list($myyear, $mymonth, $myday) = split('-', $mydate);
        		$nextday = date("Y-m-d", mktime(0, 0, 0, $mymonth, $myday+1, $myyear));
        		$totalall = $numreruns + $numservices + $numtaxes + $numpptaxes + $numppservices + $totalall;
			$mydate = $nextday;
		} // end while	
	} else {
		// for a single date run
		// Add creditcard taxes and services to the bill
		$numtaxes = add_taxdetails($DB, $billingdate, NULL, 
			'creditcard', $batchid, $organization_id);
		$numservices = add_servicedetails($DB, $billingdate, NULL, 
			'creditcard', $batchid, $organization_id);
		echo "$l_creditcard: $numtaxes $l_added, 
			$numservices $l_added<p>\n";

		// Add prepaycc taxes and services to the bill
		$numpptaxes = add_taxdetails($DB, $billingdate, NULL, 
			'prepaycc', $batchid, $organization_id);
		$numppservices = add_servicedetails($DB, $billingdate, NULL,  
			'prepaycc', $batchid, $organization_id);
		echo "$l_prepay $l_creditcard: $numpptaxes $l_added, 
			$numppservices $l_added<p>\n";

		// Update Reruns to the bill
		$numreruns = update_rerundetails($DB, $billingdate, 
			$batchid, $organization_id);
		echo "$numreruns $l_rerun<p>\n";

		$totalall = $numreruns + $numservices + $numtaxes + $numpptaxes + $numppservices + $totalall;
	} // endif for billingdate range

	// show message if no records have been found
	if ($totalall == 0) {
	  echo "<b>$l_sorrynorecordsfound<b><p>\n";
	} else {

	// create billinghistory
	create_billinghistory($DB, $batchid, 'creditcard', $user);

	/*--------------------------------------------------------------------*/
	// print the credit card billing to a file
	/*--------------------------------------------------------------------*/

	// select the path_to_ccfile from settings
	$query = "SELECT path_to_ccfile FROM settings WHERE id = '1'";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$ccfileresult = $DB->Execute($query) 
		or die ("$l_queryfailed");
	$myccfileresult = $ccfileresult->fields;
	$path_to_ccfile = $myccfileresult['path_to_ccfile'];	

	// select the info from general to get the path_to_ccfile
	$query = "SELECT ccexportvarorder FROM general WHERE id = '$organization_id'";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$ccvarresult = $DB->Execute($query) 
		or die ("$l_queryfailed");
	$myccvarresult = $ccvarresult->fields;
	$ccexportvarorder = $myccvarresult['ccexportvarorder'];	
	
	// convert the $ccexportvarorder &#036; dollar signs back to actual dollar signs and &quot; back to quotes
	$ccexportvarorder = str_replace( "&#036;"           , "$"        , $ccexportvarorder );
	$ccexportvarorder = str_replace( "&quot;"           , "\\\""        , $ccexportvarorder );

	// open the file
	$filename = "$path_to_ccfile/export$batchid.csv";
	$handle = fopen($filename, 'w'); // open the file

	// query the batch for the invoices to do
	$query = "SELECT DISTINCT d.invoice_number FROM billing_details d 
	WHERE batch = '$batchid'";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) 
		or die ("$l_queryfailed");

	while ($myresult = $result->FetchRow()) {

		// get the invoice data to process now
		$invoice_number = $myresult['invoice_number'];

		$query = "SELECT h.id h_id, h.billing_date h_billing_date, 
		h.created_by h_created_by, h.billing_id h_billing_id, 
		h.from_date h_from_date, h.to_date h_to_date, 
		h.payment_due_date h_payment_due_date, 
		h.new_charges h_new_charges, h.past_due h_past_due, 
		h.late_fee h_late_fee, h.tax_due h_tax_due, 
		h.total_due h_total_due, h.notes h_notes, 
		b.id b_id, b.name b_name, b.company b_company, 
		b.street b_street, b.city b_city, b.state b_state, 
		b.country b_country, b.zip b_zip, 
		b.contact_email b_contact_email, b.account_number b_acctnum, 
		b.creditcard_number b_ccnum, b.creditcard_expire b_ccexp,
b.encrypted_creditcard_number b_enc_ccnum 
		FROM billing_history h 
		LEFT JOIN billing b ON h.billing_id = b.id  
		WHERE h.id = '$invoice_number'";
		$invoiceresult = $DB->Execute($query)
			or die ("$l_queryfailed");	
		$myinvresult = $invoiceresult->fields;
		$user = $myinvresult['h_created_by'];
		$mydate = $myinvresult['h_billing_date'];
		$mybilling_id = $myinvresult['b_id'];
		$billing_name = $myinvresult['b_name'];
		$billing_company = $myinvresult['b_company'];
		$billing_street =  $myinvresult['b_street'];
		$billing_city = $myinvresult['b_city'];
		$billing_state = $myinvresult['b_state'];
		$billing_zip = $myinvresult['b_zip'];
		$billing_acctnum = $myinvresult['b_acctnum'];
		$billing_ccnum = $myinvresult['b_ccnum'];
		$billing_ccexp = $myinvresult['b_ccexp'];
		$billing_fromdate = $myinvresult['h_from_date'];
		$billing_todate = $myinvresult['h_to_date'];
		$billing_payment_due_date = $myinvresult['h_payment_due_date'];
		$precisetotal = $myinvresult['h_total_due'];
		$encrypted_creditcard = $myinvresult['b_enc_ccnum'];

		// get the absolute value of the total
		$abstotal = abs($precisetotal);
				
		// determine the variable export order values
		eval ("\$exportstring = \"$ccexportvarorder\";");

		// print the line in the exported data file
		// don't print them to billing if the amount is less than or equal to zero
		if ($precisetotal > 0) {
		  $newline = "\"CHARGE\",$exportstring\n$encrypted_creditcard\n";
		  fwrite($handle, $newline); // write to the file
		}
	} // end while
	
	// close the file
	fclose($handle); // close the file

	echo "$l_wrotefile $filename<br><a href=\"index.php?load=tools/downloadfile&type=dl&filename=export$batchid.csv\"><u class=\"bluelink\">$l_download export$batchid.csv</u></a><p>";	
 } // end if totalall
}
else {
// select the organizations from a list

// ask for the billing date that they want to invoice
echo "<FORM ACTION=\"index.php\" METHOD=\"GET\" name=\"form1\" onsubmit=\"toggleOn();\">
	<input type=hidden name=load value=exportcc>
	<input type=hidden name=type value=tools>
	<table>";
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
echo "</select></td><tr>
	
	<td>$l_whatdatewouldyouliketobill:</td>
	<td><input type=text name=billingdate value=\"YYYY-MM-DD\" size=12>
	<A HREF=\"#\" 
	onClick=\"cal.select(document.forms['form1'].billingdate
	,'anchor1','yyyy-MM-dd'); return false;\"
	NAME=\"anchor1\" ID=\"anchor1\" style=\"color:blue\">[$l_select]</A>
	</td><tr>
	<td></td><td><INPUT TYPE=\"SUBMIT\" NAME=\"submit\" value=\"$l_submitrequest\">
	</td>
	</form>
	</table><br><br><br>";
	
	// print the date range form
echo "<FORM ACTION=\"index.php\" METHOD=\"GET\" name=\"form2\" onsubmit=\"toggleOn();\">
	<input type=hidden name=load value=exportcc>
	<input type=hidden name=type value=tools>
	<table>";
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
echo "</select></td><tr>
	
	<td>$l_whatdatewouldyouliketobill:</td>
	<td><input type=text name=billingdate1 value=\"YYYY-MM-DD\" size=12>
	<A HREF=\"#\" 
	onClick=\"cal.select(document.forms['form2'].billingdate1
	,'anchorb1','yyyy-MM-dd'); return false;\"
	NAME=\"anchorb1\" ID=\"anchorb1\" style=\"color:blue\">[$l_select]</A>
	</td> 
	<td> to <input type=text name=billingdate2 value=\"YYYY-MM-DD\" size=12>
	<A HREF=\"#\" 
	onClick=\"cal.select(document.forms['form2'].billingdate2
	,'anchorb2','yyyy-MM-dd'); return false;\"
	NAME=\"anchorb2\" ID=\"anchorb2\" style=\"color:blue\">[$l_select]</A>
	</td>

	<tr>
	<td></td><td><INPUT TYPE=\"SUBMIT\" NAME=\"submit\" value=\"$l_submitrequest\">
	</td>
	</form>
	</table><p>";

	// print the WaitingMessage
	echo "<div id=\"WaitingMessage\" style=\"border: 0px double black; ".
	  "background-color: #fff; position: absolute; text-align: center; ".
	  "top: 50px; width: 550px; height: 300px;\">".
	  "<BR><BR><BR><h3>$l_processing...</h3>".
	  "<p><img src=\"images/spinner.gif\"></p>".
	  "</div>";	
}

?>

