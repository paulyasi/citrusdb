<html>
<head> 
<LINK href="citrus.css" type=text/css rel=STYLESHEET>
<LINK href="fullscreen.css" type=text/css rel=STYLESHEET>
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
</head>
<body bgcolor="#eedddd" marginheight=0 marginwidth=1 leftmargin=1 rightmargin=0>
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

echo "<table cellspacing=0 cellpadding=4 border=0>".
"<td bgcolor=\"#dddddd\" width=100><b>$l_id</b></td>".
"<td bgcolor=\"#dddddd\" width=130><b>$l_date</b></td>".
"<td bgcolor=\"#dddddd\" width=200><b>$l_description</b></td>".
"<td bgcolor=\"#dddddd\" width=100><b>$l_invoice</b></td>".
"<td bgcolor=\"#dddddd\" width=100><b>$l_billedamount</b></td>".
"<td bgcolor=\"#dddddd\" width=150><b>$l_paidamount</b></td>";

// get the billing_details for this account
// the account number is stored in the corresponding billing record

$query = "SELECT d.id d_id, d.billing_id d_billing_id, ".
  "d.creation_date d_creation_date, d.user_services_id d_user_services_id, ".
  "d.taxed_services_id d_taxed_services_id, d.invoice_number d_invoice_number, ".
  "d.billed_amount d_billed_amount, d.paid_amount d_paid_amount, ".
  "d.refund_amount d_refund_amount, d.refunded d_refunded, ".
  "d.rerun d_rerun, ".
  "m.service_description m_description, r.description r_description ".
  "FROM billing_details d ".
  "LEFT JOIN billing b ON b.id = d.billing_id ".
  "LEFT JOIN user_services u ON u.id = d.user_services_id ".
  "LEFT JOIN master_services m ON m.id = u.master_service_id ".
  "LEFT JOIN taxed_services t ON t.id = d.taxed_services_id ".
  "LEFT JOIN tax_rates r ON t.tax_rate_id = r.id ".
  "WHERE b.account_number = '$account_number' ORDER BY d.id DESC";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");

while ($myresult = $result->FetchRow()) {
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
  $billedamount = sprintf("%.2f",$myresult['d_billed_amount']);
  $paidamount = sprintf("%.2f",$myresult['d_paid_amount']);
  $refunded = $myresult['d_refunded'];
  $refundamount = sprintf("%.2f",$myresult['d_refund_amount']);
  $rerun = $myresult['d_rerun'];

  print "<tr bgcolor=\"#eeeeee\">";
  print "<td style=\"border-top: 1px solid grey;\">$id &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey;\">$date &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey;\">$description &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey;\">[ <a href=\"index.php?load=tools/modules/billing/htmlpreviousinvoice&billingid=$account_number&invoiceid=$invoice&details=on&type=fs&submit=on\" target=\"_blank\">$invoice</a> ]</td>";	

  //print "<td style=\"border-top: 1px solid grey;\">$invoice &nbsp;</td>";

  print "<td style=\"border-top: 1px solid grey;\">$billedamount &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey;\">$paidamount &nbsp;";

  // check for refund
  if ($refunded == 'y') {
    echo "<i>$l_refunded $refundamount</i>";
  }

  // check for rerun
  if ($rerun == 'y') {
    echo "<i>$l_rerun</i>";
  }  
  

  echo "</td>";


  

 } // end while

echo '</table>';

?>
</body>
</html>
