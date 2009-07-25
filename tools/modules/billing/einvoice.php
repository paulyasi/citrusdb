<SCRIPT LANGUAGE="JavaScript" SRC="include/CalendarPopup.js"></SCRIPT>
	<SCRIPT LANGUAGE="JavaScript">
	var cal = new CalendarPopup();
	</SCRIPT>
<?php
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

// Copyright (C) 2002-2008  Paul Yasi <paul@citrusdb.org>
// read the README file for more information

//GET Variables
if (!isset($base->input['billingdate'])) { $base->input['billingdate'] = ""; }
if (!isset($base->input['billingid'])) { $base->input['billingid'] = ""; }
if (!isset($base->input['acctnum'])) { $base->input['acctnum'] = ""; }
if (!isset($base->input['organization_id'])) { $base->input['organization_id'] = ""; }
if (!isset($base->input['billingdate1'])) { $base->input['billingdate1'] = ""; }
if (!isset($base->input['billingdate2'])) { $base->input['billingdate2'] = ""; }

$submit = $base->input['submit'];
$billingdate = $base->input['billingdate'];
$bybillingid = $base->input['billingid'];
$byacctnum = $base->input['acctnum'];
$organization_id = $base->input['organization_id'];
$billingdate1 = $base->input['billingdate1'];
$billingdate2 = $base->input['billingdate2'];

// make sure the user is in a group that is allowed to run this

if ($submit) {
	/*--------------------------------------------------------------------*/
	// Check if they entered by account number and change to bybillingid
	/*--------------------------------------------------------------------*/
	if ($byacctnum <> NULL)
	{
		$query = "SELECT default_billing_id FROM customer
			WHERE account_number = '$byacctnum'";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("$l_queryfailed");
		$myresult = $result->fields;
		$bybillingid = $myresult['default_billing_id'];	
	}

	/*--------------------------------------------------------------------*/
	// Create the billing data
	/*--------------------------------------------------------------------*/
	
	// determine the next available batch number
        $batchid = get_nextbatchnumber($DB);
	echo "BATCH: $batchid<p>\n";
        
	// query for taxed services that are billed on the specified date
	// and for a specific organization

	
	if ($billingdate2 <> NULL) {
	  $startdate = $billingdate1;
	  $enddate = $billingdate2;
	  $mydate = $startdate;
	  echo "Date Range: $startdate - $enddate<p>\n";
	  while ($mydate <= $enddate) {
	    echo "Processing $mydate<br>\n";
	    
	    // Add creditcard taxes and services to the bill
	    $numtaxes = add_taxdetails($DB, $mydate, NULL, 'einvoice', $batchid, $organization_id);
	    $numservices = add_servicedetails($DB, $mydate, NULL, 'einvoice', $batchid, $organization_id);
	    echo "$l_taxes $numtaxes $l_added, $l_services $numservices $l_added<p>\n";

	    // make the next date to check	
	    list($myyear, $mymonth, $myday) = split('-', $mydate);
	    $nextday = date("Y-m-d", mktime(0, 0, 0, $mymonth, $myday+1, $myyear));
	    $totalall = $numreruns + $numservices + $numtaxes + $numpptaxes + $numppservices + $totalall;
	    $mydate = $nextday;
	  } // end while	
	} elseif ($billingdate <> NULL) { // by billing date
	  $numtaxes = add_taxdetails($DB, $billingdate, NULL, 'einvoice', $batchid, $organization_id);
	  $numservices = add_servicedetails($DB, $billingdate, NULL,'einvoice', $batchid, $organization_id);
	} else { // by billing id
	  $numtaxes = add_taxdetails($DB, NULL, $bybillingid,'einvoice', $batchid, NULL);
	  $numservices = add_servicedetails($DB, NULL, $bybillingid,'einvoice', $batchid, NULL);
	}
	echo "taxes: $numtaxes, services: $numservices<p>";

	// create billinghistory
	create_billinghistory($DB, $batchid, 'einvoice', $user);	

	/*-------------------------------------------------------------------*/	
	// Email the invoice
	/*-------------------------------------------------------------------*/

	// query the batch for the invoices to do
        $query = "SELECT DISTINCT d.invoice_number, b.contact_email, b.id, b.account_number  
	FROM billing_details d 
	LEFT JOIN billing b ON b.id = d.billing_id
	WHERE d.batch = '$batchid'";
        $DB->SetFetchMode(ADODB_FETCH_ASSOC);
        $result = $DB->Execute($query)
                or die ("$l_queryfailed");

	while ($myresult = $result->FetchRow()) {
		// get the invoice data to process now
		$invoice_number = $myresult['invoice_number'];
		$contact_email = $myresult['contact_email'];
		$invoice_account_number = $myresult['account_number'];
		$invoice_billing_id = $myresult['id'];
		$message = outputinvoice($DB, $invoice_number, $lang, "html", NULL);		

		// get the org billing email address for from address		
		$query = "SELECT g.org_name, g.org_street, g.org_city, ".
		  "g.org_state, g.org_zip, g.email_billing ".
		  "FROM billing b ".
		  "LEFT JOIN general g ON g.id = b.organization_id  ".
		  "WHERE b.id = $invoice_billing_id";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$ib_result = $DB->Execute($query) or die ("ib $l_queryfailed");
		$mybillingresult = $ib_result->fields;
		$billing_email = $mybillingresult['email_billing'];
		$org_name = $mybillingresult['org_name'];
		$org_street = $mybillingresult['org_street'];
		$org_city = $mybillingresult['org_city'];
		$org_state = $mybillingresult['org_state'];
		$org_zip = $mybillingresult['org_zip'];

		// get the total due from the billing_history
		$query = "SELECT total_due FROM billing_history ".
		  "WHERE id = '$invoice_number'";
		$iv_result = $DB->Execute($query) or die ("iv $l_queryfailed");
		$myinvoiceresult = $iv_result->fields;
		$total_due = sprintf("%.2f",$myinvoiceresult['total_due']);

		// build email message above invoice
		$email_message = "$l_email_heading_thankyou $org_name.\n\n".
		  "$l_email_heading_presenting ".
		  "$total_due $l_to_lc \n\n".
		  "$org_name\n".
		  "$org_street\n".
		  "$org_city $org_state $org_zip\n\n".
		  "$l_email_heading_include.\n\n";

		// HTML Email Headers
		$headers = "From: $billing_email \n";
		//$headers .= "Mime-Version: 1.0 \n";
		//$headers .= "Content-type: text/html; charset=iso-8859-1 \n";
		$to = $contact_email;
		$subject = "$l_einvoice $org_name";
		$message = "$email_message$message";
		// send the mail
		mail ($to, $subject, $message, $headers);
		echo "sent invoice to $to<br>\n";
	}
	
} else {

// ask for the billing date that they want to invoice
echo "<h3>$l_emailinvoices</h3>";
echo "<FORM ACTION=\"index.php\" METHOD=\"GET\" name=\"form1\">
	<input type=hidden name=load value=billing>
	<input type=hidden name=tooltype value=module>
	<input type=hidden name=type value=tools>
	<input type=hidden name=einvoice value=on>
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
	<td><input type=text name=billingdate value=\"YYYY-MM-DD\">
	<A HREF=\"#\"
	onClick=\"cal.select(document.forms['form1'].billingdate,'anchor1','yyyy-MM-dd'); 
	return false;\"
	NAME=\"anchor1\" ID=\"anchor1\" style=\"color:blue\">[$l_select]</A>
	</td><tr>
	<td></td><td><INPUT TYPE=\"SUBMIT\" NAME=\"submit\" value=\"$l_submit\"></td>
	</table></form>";

 	// print the date range form
echo "<FORM ACTION=\"index.php\" METHOD=\"GET\" name=\"form2\" onsubmit=\"toggleOn();\">
	<input type=hidden name=load value=billing>
	<input type=hidden name=tooltype value=module>
	<input type=hidden name=type value=tools>
	<input type=hidden name=einvoice value=on>
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

// or ask the the single billing ID they want to invoice
echo "<p>$l_or<p><FORM ACTION=\"index.php\" METHOD=\"GET\" onsubmit=\"toggleOn();\">
	<input type=hidden name=load value=billing>
	<input type=hidden name=tooltype value=module>
	<input type=hidden name=type value=tools>
	<input type=hidden name=einvoice value=on>
	<table>
	<td></td><td>$l_billingid</td><tr>
	<td>$l_whatidwouldyouliketobill:</td><td><input type=text name=billingid>
	</td><tr>
	<td></td><td><INPUT TYPE=\"SUBMIT\" NAME=\"submit\" value=\"$l_submit\"></td>
	</table></form>";

// or ask what customer id they want to invoice (uses the default_billing_id)
echo "<p>$l_or<p><FORM ACTION=\"index.php\" METHOD=\"GET\" onsubmit=\"toggleOn();\">
	<input type=hidden name=load value=billing>
	<input type=hidden name=tooltype value=module>
	<input type=hidden name=type value=tools>
	<input type=hidden name=einvoice value=on>	
	<table>
	<td></td><td>$l_accountnumber</td><tr>
	<td>$l_whataccountnumberwouldyouliketobill:</td><td><input type=text name=acctnum>
	</td><tr>
	<td></td><td><input type=\"submit\" name=\"submit\" value=\"$l_submit\"></td>
	</table></form>";

	// print the WaitingMessage
	echo "<div id=\"WaitingMessage\" style=\"border: 0px double black; ".
	  "background-color: #fff; position: absolute; text-align: center; ".
	  "top: 50px; width: 550px; height: 300px;\">".
	  "<BR><BR><BR><h3>$l_processing...</h3>".
	  "<p><img src=\"images/spinner.gif\"></p>".
	  "</div>";
 
}

?>
