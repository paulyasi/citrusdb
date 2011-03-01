<html>
<head>
<LINK href="citrus.css" type=text/css rel=STYLESHEET>
<LINK href="fullscreen.css" type=text/css rel=STYLESHEET>
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
</head>
<body bgcolor="#ddeeee" marginheight=0 marginwidth=1 leftmargin=1 rightmargin=0>
	<?php
	/*--------------------------------------------------------------------*/
	// Check for authorized accesss
	/*--------------------------------------------------------------------*/
	
	if(constant("INDEX_CITRUS") <> 1){
		echo "You must be logged in to run this.  Goodbye.";
		exit;	
	}
	
	if (!defined("INDEX_CITRUS")) {
		echo "You must be logged in to run this.  Goodbye.";
	        exit;
	}

	// GET Variables
        $account_number = $base->input['account_number'];
	
	echo "<table cellspacing=0 cellpadding=4 border=0>
		<td bgcolor=\"#ddeeee\" width=100><b>$l_invoicenum</b></td>
		<td bgcolor=\"#ddeeee\" width=130><b>$l_billingid</b></td>		
		<td bgcolor=\"#ddeeee\" width=130><b>$l_date</b></td>
		<td bgcolor=\"#ddeeee\" width=200><b>$l_type</b></td>
		<td bgcolor=\"#ddeeee\" width=100><b>$l_from</b></td>
		<td bgcolor=\"#ddeeee\" width=100><b>$l_to</b></td>
		<td bgcolor=\"#ddeeee\" width=100><b>$l_duedate</b></td>
		<td bgcolor=\"#ddeeee\" width=100><b>$l_newcharges</b></td>
		<td bgcolor=\"#ddeeee\" width=150><b>$l_total</b></td>";

	// get the billing_history for this account, the account number is 
	// stored in the corresponding billing record

	$query = "SELECT h.id h_id, h.billing_id h_bid, h.billing_date h_bdate, 
	h.billing_type h_btype, h.from_date h_from, h.to_date h_to, h.total_due 
	h_total, h.new_charges h_new_charges,
h.payment_due_date h_payment_due_date,
c.account_number c_acctnum, b.account_number b_acctnum, b.id b_id 
	FROM billing_history h 
	LEFT JOIN billing b ON h.billing_id = b.id  
	LEFT JOIN customer c ON b.account_number = c.account_number
	WHERE b.account_number = '$account_number' ORDER BY h.id DESC";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	while ($myresult = $result->FetchRow())
	{
		$id = $myresult['h_id'];
		$billing_id = $myresult['h_bid'];
		$billing_date = $myresult['h_bdate'];
		$billing_type = $myresult['h_btype'];
		$payment_due_date = $myresult['h_payment_due_date'];
		$from_date = $myresult['h_from'];
		$to_date = $myresult['h_to'];
		$new_charges = sprintf("%.2f",$myresult['h_new_charges']);
		$total_due = sprintf("%.2f",$myresult['h_total']);

		print "<tr bgcolor=\"#eeffff\">";
		print "<td style=\"border-top: 1px solid grey;\">[ <a href=\"$url_prefix/index.php?load=tools/modules/billing/htmlpreviousinvoice&billingid=$account_number&invoiceid=$id&details=on&type=fs&submit=on\" target=\"_blank\">$id</a> ]</td>";
		print "<td style=\"border-top: 1px solid grey;\">$billing_id &nbsp;</td>";
		print "<td style=\"border-top: 1px solid grey;\">$billing_date &nbsp;</td>";
		print "<td style=\"border-top: 1px solid grey;\">$billing_type &nbsp;</td>";
		print "<td style=\"border-top: 1px solid grey;\">$from_date &nbsp;</td>";
		print "<td style=\"border-top: 1px solid grey;\">$to_date &nbsp;</td>";
		print "<td style=\"border-top: 1px solid grey;\">$payment_due_date &nbsp;</td>";
		print "<td style=\"border-top: 1px solid grey;\">$new_charges &nbsp;</td>";
                print "<td style=\"border-top: 1px solid grey;\">$total_due &nbsp;</td>";
	}

	echo '</table>';

	?>
</body>
</html>
