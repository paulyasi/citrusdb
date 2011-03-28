<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_refundreport</h3>

[ <a href=\"index.php?load=billing&type=module\">$l_back</a> ]";

// Copyright (C) 2007-2010  Paul Yasi (paul at citrusdb.org)
// Includes code contributed by Eric Cho (twitter.com/myfoxfree)
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

// make sure the user is in a group that is allowed to run this

//
//GET Variables
//
if (!isset($base->input['billingid'])) { $base->input['billingid'] = ""; }
if (!isset($base->input['detailid'])) { $base->input['detailid'] = ""; }
if (!isset($base->input['refund'])) { $base->input['refund'] = ""; }
if (!isset($base->input['refundnow'])) { $base->input['refundnow'] = ""; }
if (!isset($base->input['refundamount'])) { $base->input['refundamount'] = ""; }
if (!isset($base->input['method'])) { $base->input['method'] = ""; }


$submit = $base->input['submit'];
$billingid = $base->input['billingid'];
$detailid = $base->input['detailid'];
$refund = $base->input['refund'];
$refundnow = $base->input['refundnow'];
$refundamount = $base->input['refundamount'];
$method = $base->input['method'];


if ($refundnow) {
  // reset the refund if amount entered is zero
  if ($refundamount == 0) {
    $query = "UPDATE billing_details SET
        refund_amount = 0.00,
        refund_date = null
        WHERE id = $detailid";
    $result = $DB->Execute($query) or die ("$query Query Failed");  
  } else {
    $query = "UPDATE billing_details SET 
	refund_amount = '$refundamount',
	refund_date = CURRENT_DATE 
	WHERE id = $detailid";
    $result = $DB->Execute($query) or die ("$query Query Failed");
  }
  
  // if billing method is not credit card they must be done manually
  // just mark the amount as refunded in the database
  if ($method <> 'creditcard') {
    $query ="UPDATE billing_details SET refunded = 'y' ".
      "WHERE refunded <> 'y' AND refund_amount > 0 ". 
      "AND id = $detailid";		
    $detailresult = $DB->Execute($query) or die ("$query $l_queryfailed");	
    
    print "<h2 style=\"color: red;\">$l_method_warning</h2>";
    
  }
  
  print "<h3>$l_changessaved<h3>";
}
else if ($refund) {
	$query = "SELECT d.id d_id, d.billing_id d_billing_id, 
	d.creation_date d_creation_date, d.user_services_id d_user_services_id, 	d.taxed_services_id d_taxed_services_id, 
	d.invoice_number d_invoice_number, d.billed_amount d_billed_amount, 
	d.paid_amount d_paid_amount, d.refund_amount d_refund_amount, 
	d.refunded d_refunded, b.creditcard_number,   
	m.service_description m_description, 
	r.description r_description
	FROM billing_details d
	LEFT JOIN billing b ON b.id = d.billing_id 	
	LEFT JOIN user_services u ON u.id = d.user_services_id 
	LEFT JOIN master_services m ON m.id = u.master_service_id
	LEFT JOIN taxed_services t ON t.id = d.taxed_services_id
	LEFT JOIN tax_rates r ON t.tax_rate_id = r.id
	WHERE d.id = '$detailid'";

	if ($method <> 'creditcard') {
	  echo "<h2>$l_method_warning</h2>";
	}

	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	$myresult = $result->fields;

	$id = $myresult['d_id'];
	$date = $myresult['d_creation_date'];
	if ($myresult['d_taxed_services_id']) { 
		// it's a tax
		$description = $myresult['r_description'];
	} else {
		// it's a service
		$description = $myresult['m_description'];
	}
	$invoice = $myresult['d_invoice_number'];
	$billedamount = $myresult['d_billed_amount'];
	$paidamount = $myresult['d_paid_amount'];
	$refundamount = $myresult['d_refund_amount'];
	$refunded = $myresult['d_refunded'];

	// print refund form
	echo "<FORM ACTION=\"index.php\" METHOD=\"POST\">
	<input type=hidden name=load value=refund>
	<input type=hidden name=type value=tools>
	<input type=hidden name=refundnow value=on>
<input type=hidden name=method value=\"$method\">
	<input type=hidden name=detailid value=\"$detailid\"";

	echo "
	<p><table>
	<td><b>$l_id</b></td><td>$id</td><tr>
	<td><b>$l_date</b></td><td>$date</td><tr>
	<td><b>$l_description</b></td><td>$description</td><tr>
	<td><b>$l_invoice</b></td><td>$invoice</td><tr>
	<td><b>$l_billedamount</b></td><td>$billedamount</td><tr>
	<td><b>$l_paidamount</b></td><td>$paidamount</td></tr>
	<td><b>$l_refundamount</b></td>
	<td><input type=text name=\"refundamount\" value=\"$refundamount\">
	</td><tr>	
	<td></td>
	<td><INPUT TYPE=\"SUBMIT\" NAME=\"submit\" value=\"$l_submitrequest\"></td>
	</table></form>";

}

else if ($submit) {

	//$DB->debug = true;
	//
	// Show the billing details that belong to that billing id:
	//
	$query = "SELECT d.id d_id, d.billing_id d_billing_id, 
	d.creation_date d_creation_date, d.user_services_id d_user_services_id, 	d.taxed_services_id d_taxed_services_id, 
	d.invoice_number d_invoice_number, d.billed_amount d_billed_amount, 
	d.paid_amount d_paid_amount, d.refund_amount d_refund_amount, 
	d.refunded d_refunded, d.refund_date d_refund_date,  
	m.service_description m_description, 
	r.description r_description, 
	b.billing_type b_billing_type, bt.method bt_method 
	FROM billing_details d
	LEFT JOIN billing b ON b.id = d.billing_id 	
	LEFT JOIN user_services u ON u.id = d.user_services_id 
	LEFT JOIN master_services m ON m.id = u.master_service_id
	LEFT JOIN taxed_services t ON t.id = d.taxed_services_id
	LEFT JOIN tax_rates r ON t.tax_rate_id = r.id
	LEFT JOIN billing_types bt ON b.billing_type = bt.id  
	WHERE d.billing_id = '$billingid' ORDER BY d.id DESC";

	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");

	// print column headings
	echo "<table cellspacing=0 cellpadding=4 border=0>
	<td bgcolor=\"#dddddd\" width=100><b>$l_id</b></td>
	<td bgcolor=\"#dddddd\" width=130><b>$l_date</b></td>
	<td bgcolor=\"#dddddd\" width=200><b>$l_description</b></td>
	<td bgcolor=\"#dddddd\" width=100><b>$l_invoice</b></td>
	<td bgcolor=\"#dddddd\" width=100><b>$l_billedamount</b></td>
	<td bgcolor=\"#dddddd\" width=150><b>$l_paidamount</b></td>
	<td bgcolor=\"#dddddd\" width=150><b>$l_refundamount</b></td>
	<td bgcolor=\"#dddddd\" width=150><b>$l_refunddate</b></td>
	<td bgcolor=\"#dddddd\" width=150><b>$l_refunded</b></td>";	

	// print the column rows	
	while ($myresult = $result->FetchRow())
	{
		$id = $myresult['d_id'];
		$date = $myresult['d_creation_date'];
		if ($myresult['d_taxed_services_id']) { 
			// it's a tax
			$description = $myresult['r_description'];
		} else {
			// it's a service
			$description = $myresult['m_description'];
		}
		
		$invoice = $myresult['d_invoice_number'];
		$method = $myresult['bt_method'];
		$billedamount = $myresult['d_billed_amount'];
		$paidamount = $myresult['d_paid_amount'];
		$refundamount = $myresult['d_refund_amount'];
		$refunded = $myresult['d_refunded'];
		$refunddate = $myresult['d_refund_date'];

		print "<tr bgcolor=\"#eeeeee\">";
		print "<td style=\"border-top: 1px solid grey;\">
			$id &nbsp;</td>";
		print "<td style=\"border-top: 1px solid grey;\">
			$date &nbsp;</td>";
		print "<td style=\"border-top: 1px solid grey;\">
			$description &nbsp;</td>";
		print "<td style=\"border-top: 1px solid grey;\">
		[ <a href=\"index.php?load=tools/modules/billing/
		htmlpreviousinvoice&billingid=$billingid
		&invoiceid=$invoice&details=on&type=fs&submit=on\" 
		target=\"_blank\">$invoice</a> ]</td>";	
		print "<td style=\"border-top: 1px solid grey;\">
			$billedamount &nbsp;</td>";
                print "<td style=\"border-top: 1px solid grey;\">
			$paidamount $method</td>";
		print "<td style=\"border-top: 1px solid grey;\">
			$refundamount &nbsp;</td>";
		print "<td style=\"border-top: 1px solid grey;\">
			$refunddate &nbsp;</td>";
		print "<td style=\"border-top: 1px solid grey;\">
			$refunded &nbsp;";
		if ($refunded <> 'y' AND $paidamount > 0) {
			echo "[ <a href=\"index.php?load=refund&type=tools&detailid=$id&refund=on&method=$method\">$l_refund</a> ]";
		}	
		echo "</td>";

		
	} // end while

	print "</table>";
	
}
else {
//
// ask for the billing id that they want to refund services for
//
echo "<FORM ACTION=\"index.php\" METHOD=\"POST\">
	<input type=hidden name=load value=refund>
	<input type=hidden name=type value=tools>
	<table>
	<td>$l_billingid:</td><td><input type=text name=billingid></td><tr>
	<td></td><td><INPUT TYPE=\"SUBMIT\" NAME=\"submit\" value=\"$l_submitrequest\"></td>
	</form>";
}

?>
</body>
</html>
