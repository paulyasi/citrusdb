<html>
<body bgcolor="#ffffff">
<h3>Invoice Maintenance</h3>
<?php
// Copyright (C) 2002-2004  Paul Yasi <paul@citrusdb.org>
// read the README file for more information

// Includes                                                                                 
require_once('../include/citrus.inc.php');

if (!user_isloggedin()) {
	echo 'You must be logged in to use this feature<br>';
	exit;
	//user_logout();
	//$user_name='';
}

// make sure the user is in a group that is allowed to run this

//
//GET Variables
//
$billingid = $base->input['billingid'];
$remove = $base->input['remove'];
$delete = $base->input['delete'];
$details = $base->input['details'];
$invoicenum = $base->input['invoicenum'];

if ($delete) {

	//
	// Delete the invoice, delete from billing history where id = $invoicenum
	//
	$query = "DELETE FROM billing_history WHERE id = $invoicenum";
        $result = db_query($query) or die ("Query Failed".db_error());
	
	//
	// delete from billing_details where invoice_number = $invoicenum
	//
	$query = "DELETE FROM billing_details WHERE invoice_number = $invoicenum";                                          
        $result = db_query($query) or die ("Query Failed".db_error());

	print "Deleted invoice number $invoicenum";
}
else if ($remove) {

	//
	// Ask if they want to remove the invoice, print the yes/no form
	//
	print "<b>Are you sure you want to remove invoice number $invoicenum?</b>";

	print "<table cellpadding=15 cellspacing=0 border=0 width=720><td align=right width=360>";
	print "<form style=\"margin-bottom:0;\" action=\"index.php\">";
	print "<input type=hidden name=load value=invmaint>";
	print "<input type=hidden name=invoicenum value=$invoicenum>";
    print "<input type=hidden name=delete value=on>";
    print "<input name=deletenow type=submit value=\"  Yes  \" class=smallbutton></form></td>";
    print "<td align=left width=360><form style=\"margin-bottom:0;\" action=\"index.php\">";
    print "<input name=done type=submit value=\"  No  \" class=smallbutton>";
    print "<input type=hidden name=load value=invmaint>";
    print "</form></td></table>";

}

else if ($details) {

	//
	// Select the details for an specific invoice number
	//
	$query = "SELECT d.user_services_id d_user_services_id, d.invoice_number d_invoice_number, 
	d.billed_amount d_billed_amount, d.billing_id d_billing_id, d.taxed_services_id d_taxed_services_id, 
	u.id u_id, u.master_service_id u_master_service_id, u.usage_multiple u_usage_multiple, 
	m.id m_id, m.service_description m_service_description, 
	ts.id ts_id, ts.master_services_id ts_master_services_id, ts.tax_rate_id ts_tax_rate_id, 
	tr.id tr_id, tr.description tr_description
	FROM billing_details d
	LEFT  JOIN user_services u ON d.user_services_id = u.id
	LEFT  JOIN master_services m ON u.master_service_id = m.id
	LEFT JOIN taxed_services ts ON d.taxed_services_id = ts.id 
	LEFT JOIN tax_rates tr ON ts.tax_rate_id = tr.id 
	WHERE d.invoice_number = '$invoicenum'";
	
	$result = db_query($query) or die ("Query Failed".db_error());
	
	//
	// Print the invoice details
	//
	print "<b>Invoice Details</b><p>";
	
	print "[<a href="">Print This Invoice</a>] 
	      [Print Payment Receipt]<p>";

	print "New Charges:<br>";	
	while ($myresult = db_fetch_assoc($result))
	{
	
		$service_description = $myresult['m_service_description'];
		$tax_description = $myresult['tr_description'];
		$billed_amount = $myresult['d_billed_amount'];
		print "$service_description $tax_description $billed_amount<br>";
	}
		
}

else if ($submit) {

	//
	// Show the invoices that belong to that billing id:
	//
	$query = "SELECT h.id h_id, h.billing_date h_billing_date, h.from_date h_from_date, h.to_date h_to_date, 
		h.payment_due_date h_due_date, h.total_due h_total_due, b.name b_name, b.company b_company
		FROM billing_history h
        LEFT JOIN billing b ON h.billing_id = b.id
        WHERE h.billing_id  = '$billingid'";

        $result = db_query($query) or die ("Query Failed".db_error());

	print "<table cellpadding=5 cellspacing=1><tr bgcolor=\"#dddddd\">";
	print "<td>Invoice Number</td><td>Billing Date</td><td>Name</td><td>Company</td><td>From</td><td>To</td><td>Due Date</td><td>Total Due</td><td></td><td></td>";

	while ($myresult = db_fetch_assoc($result))
	{
		$invoice_number = $myresult['h_id'];
		$billing_date = $myresult['h_billing_date'];
		$name = $myresult['b_name'];
		$company = $myresult['b_company'];
		$from_date = $myresult['h_from_date'];
		$to_date = $myresult['h_to_date'];
		$due_date = $myresult['h_due_date'];
		$total_due = $myresult['h_total_due'];

		print "<tr bgcolor=\"#eeeeee\"><td>$invoice_number</td><td>$billing_date</td><td>$name</td><td>$company</td><td>$from_date</td><td>$to_date</td><td>$due_date</td><td>$total_due</td><td>[<a href=\"index.php?load=invmaint&billingid=$billingid&invoicenum=$invoice_number&details=on&submit=on\">Details</a>]</td><td>[<a href=\"index.php?load=invmaint&invoicenum=$invoice_number&remove=on&submit=on\">Remove</a>]</td><tr>";
		
		if (($details) and ($invoicenum == $invoice_number)) {
        	
        	//
        	// show the details for a specified invoice ID
			//
        	$query = "SELECT d.id d_id, d.billed_amount d_billed_amount, d.paid_amount d_paid_amount, d.batch d_batch,
                	u.master_service_id, m.id m_id, m.service_description m_service
        	FROM billing_details d
        	LEFT JOIN user_services u ON d.user_services_id = u.id
        	LEFT JOIN master_services m ON u.master_service_id = m.id
        	WHERE d.invoice_number  = '$invoicenum'";

        	$dresult = db_query($query) or die ("Query Failed".db_error());

        	print "<tr bgcolor=\"#eeeeee\"><td colspan=10><center><b>New Charges on invoice number $invoicenum</b></center></td><tr>";

        	while ($detailsresult = db_fetch_assoc($dresult))
        	{
                	$service = $detailsresult['m_service'];
                	$billed_amount = $detailsresult['d_billed_amount'];

                	print "<tr bgcolor=\"#eeeeee\"><td colspan=10><center>$service &nbsp; $billed_amount</center></td><tr>";
        	}

	}


	}

	print "</table>";

}
else {
//
// ask for the billing date that they want to invoice
//
echo '<FORM ACTION="'. $PHP_SELF .'" METHOD="GET">
	<input type=hidden name=load value=invmaint>
	<table>
	<td>Enter Billing ID:</td><td><input type=text name=billingid></td><tr>
	<td></td><td><INPUT TYPE="SUBMIT" NAME="submit" value="submit"></td>
	</form>';
}

?>
</body>
</html>
