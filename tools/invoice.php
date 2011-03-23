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

$submit = $base->input['submit'];
$billingdate = $base->input['billingdate'];
$bybillingid = $base->input['billingid'];
$byacctnum = $base->input['acctnum'];
$organization_id = $base->input['organization_id'];

// make sure the user is in a group that is allowed to run this

if ($submit) {
  // select the path_to_ccfile from settings
  $query = "SELECT path_to_ccfile FROM settings WHERE id = '1'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $ccfileresult = $DB->Execute($query)
    or die ("$l_queryfailed");
  $myccfileresult = $ccfileresult->fields;
  $path_to_ccfile = $myccfileresult['path_to_ccfile'];
  
  /*--------------------------------------------------------------------*/
  // Check if they entered by account number and change to bybillingid
  /*--------------------------------------------------------------------*/
  if ($byacctnum <> NULL) {
    $query = "SELECT default_billing_id FROM customer ".
      "WHERE account_number = '$byacctnum'";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ($l_queryfailed);
    $myresult = $result->fields;
    $bybillingid = $myresult['default_billing_id'];	
  }
  
  /*--------------------------------------------------------------------*/
  // Create the billing data
  /*--------------------------------------------------------------------*/
  
  // determine the next available batch number
  $batchid = get_nextbatchnumber($DB);
  //echo "BATCH: $batchid<p>\n";
  
  // query for taxed services that are billed on the specified date
  // and a specific organization
  if ($billingdate <> NULL) { // by billing date
    $numtaxes = add_taxdetails($DB, $billingdate, NULL, 
			       'invoice', $batchid, $organization_id);
    $numservices = add_servicedetails($DB, $billingdate, NULL,
				      'invoice', $batchid, $organization_id);
  }  else { // by billing id
    $numtaxes = add_taxdetails($DB, NULL, $bybillingid,
			       'invoice', $batchid, NULL);
    $numservices = add_servicedetails($DB, NULL, $bybillingid,
				      'invoice', $batchid, NULL);
  }
  //echo "taxes: $numtaxes, services: $numservices<p>";
  
  // create billinghistory
  create_billinghistory($DB, $batchid, 'invoice', $user);	
  
  /*-------------------------------------------------------------------*/	
  // Print the invoice
  /*-------------------------------------------------------------------*/
  // query the batch for the invoices to do
  $query = "SELECT DISTINCT d.invoice_number FROM billing_details d 
        WHERE batch = '$batchid'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query)
    or die ("$l_queryfailed");
  
  // Show the control box on top of everthing else
  //echo "<div id=\"popbox\">
  //<a href=\"javascript:print()\">[Print]</a></div>";
  
  require('./include/fpdf.php');
  $pdf = new FPDF();
  
  while ($myresult = $result->FetchRow()) {
    // get the invoice data to process now
    $invoice_number = $myresult['invoice_number'];
    $pdf = outputinvoice($DB, $invoice_number, $lang, "pdf", $pdf);	
  }
  
  $filename = "$path_to_ccfile/invoice$batchid.pdf";
  $pdf->Output($filename,F);
  
  
  // output the link to the pdf file
  echo "$l_wrotefile $filename<br><a href=\"index.php?load=tools/downloadfile&type=dl&filename=invoice$batchid.pdf\"><u class=\"bluelink\">$l_download invoice$batchid.pdf</u></a><p>";	
  
 } else {
  
  // ask for the billing date that they want to invoice
  echo "<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"include/CalendarPopup.js\"></SCRIPT>
	<SCRIPT LANGUAGE=\"JavaScript\">
	var cal = new CalendarPopup();
	</SCRIPT>
	<h3>$l_printinvoices</h3>";
  echo "<FORM ACTION=\"index.php\" METHOD=\"POST\" name=\"form1\" onsubmit=\"toggleOn();\">
	<input type=hidden name=load value=tools/invoice>
	<input type=hidden name=type value=dl>
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

  // or ask the the single billing ID they want to invoice
  echo "<p>$l_or<p><FORM ACTION=\"index.php\" METHOD=\"POST\" onsubmit=\"toggleOn();\">
	<input type=hidden name=load value=tools/invoice>
	<input type=hidden name=type value=dl>
	<table>
	<td></td><td>$l_billingid</td><tr>
	<td>$l_whatidwouldyouliketobill:</td><td><input type=text name=billingid>
	</td><tr>
	<td></td><td><INPUT TYPE=\"SUBMIT\" NAME=\"submit\" value=\"$l_submit\"></td>
	</table></form>";

  // or ask what customer id they want to invoice (uses the default_billing_id)
  echo "<p>$l_or<p><FORM ACTION=\"index.php\" METHOD=\"POST\" onsubmit=\"toggleOn();\">
	<input type=hidden name=load value=tools/invoice>
	<input type=hidden name=type value=dl>
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
