<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('invoicemaintenance')?></h3>

[ <a href="<?php echo $this->url_prefix?>/index.php/billing"><?php echo lang('back')?></a> ]

// make sure the user is in a group that is allowed to run this
//
//GET Variables
//
if (!isset($base->input['billingid'])) { $base->input['billingid'] = ""; }
if (!isset($base->input['remove'])) { $base->input['remove'] = ""; }
if (!isset($base->input['delete'])) { $base->input['delete'] = ""; }
if (!isset($base->input['details'])) { $base->input['details'] = ""; }
if (!isset($base->input['invoicenum'])) { $base->input['invoicenum'] = ""; }
if (!isset($base->input['editduedate'])) { $base->input['editduedate'] = ""; }
if (!isset($base->input['saveduedate'])) { $base->input['saveduedate'] = ""; }
if (!isset($base->input['duedate'])) { $base->input['duedate'] = ""; }
if (!isset($base->input['showall'])) { $base->input['showall'] = ""; }

$submit = $base->input['submit'];
$billingid = $base->input['billingid'];
$remove = $base->input['remove'];
$delete = $base->input['delete'];
$details = $base->input['details'];
$invoicenum = $base->input['invoicenum'];
$editduedate = $base->input['editduedate'];
$saveduedate = $base->input['saveduedate'];
$duedate = $base->input['duedate'];
$showall = $base->input['showall'];
 else if ($submit) {
   
   //
   // Show the invoices that belong to that billing id:
   //
   if ($showall) {
     $query = "SELECT h.id h_id, h.billing_date h_billing_date, h.from_date 
	h_from_date, h.to_date h_to_date, h.payment_due_date h_due_date, 
	h.new_charges h_new_charges, h.total_due h_total_due,
	h.billing_type h_billing_type, 
	b.name b_name, b.company b_company, d.invoice_number, 
        SUM(d.paid_amount) as normal_paid_amount 
	FROM billing_history h
        LEFT JOIN billing b ON h.billing_id = b.id 
	LEFT JOIN billing_details d ON h.id = d.invoice_number 
        WHERE h.billing_id  = '$billingid' GROUP BY h.id 
	ORDER BY h.id DESC";
   } else {
     $query = "SELECT h.id h_id, h.billing_date h_billing_date, h.from_date 
	h_from_date, h.to_date h_to_date, h.payment_due_date h_due_date, 
	h.new_charges h_new_charges, h.total_due h_total_due,
	h.billing_type h_billing_type, 
	b.name b_name, b.company b_company, d.invoice_number, 
        SUM(d.paid_amount) as normal_paid_amount 
	FROM billing_history h
        LEFT JOIN billing b ON h.billing_id = b.id 
	LEFT JOIN billing_details d ON h.id = d.invoice_number 
        WHERE h.billing_id  = '$billingid' GROUP BY h.id 
	ORDER BY h.id DESC LIMIT 6";     
   }
   
   $DB->SetFetchMode(ADODB_FETCH_ASSOC);
   $result = $DB->Execute($query) or die ("$l_queryfailed");

   print "<table cellpadding=5 cellspacing=1><tr bgcolor=\"#dddddd\">";
   print "<td>$l_invoicenumber</td>
	<td>$l_billingdate</td>
	<td>$l_name</td>
	<td>$l_company</td>
	<td>$l_from</td>
	<td>$l_to</td>
	<td>$l_duedate</td>
	<td>$l_newcharges</td>
	<td>$l_total</td>
	<td>$l_billingtype</td>
	<td></td><td></td><td></td><td></td><td></td>";

   while ($myresult = $result->FetchRow()) {
     $invoice_number = $myresult['h_id'];
     $billing_date = $myresult['h_billing_date'];
     $name = $myresult['b_name'];
     $company = $myresult['b_company'];
     $from_date = $myresult['h_from_date'];
     $to_date = $myresult['h_to_date'];
     $due_date = $myresult['h_due_date'];
     $new_charges = sprintf("%.2f",$myresult['h_new_charges']);		
     $total_due = sprintf("%.2f",$myresult['h_total_due']);
     $billing_type = $myresult['h_billing_type'];
     $normal_sum = sprintf("%.2f",$myresult['normal_paid_amount']);

     
     print "<tr bgcolor=\"#eeeeee\">
		<td>$invoice_number</td>
		<td>$billing_date</td>
		<td>$name</td>
		<td>$company</td>
		<td>$from_date</td>
		<td>$to_date</td>
		<td><a href=\"index.php?load=invmaint&invoicenum=$invoice_number&editduedate=on&type=tools&duedate=$due_date&billingid=$billingid\">$due_date</a></td>
		<td>$new_charges</td>
		<td>$total_due</td>
		<td>$billing_type</td>
		<td>[<a href=\"index.php?load=tools/printpreviousinvoice&billingid=$billingid&invoiceid=$invoice_number&details=on&type=dl&submit=on\">$l_pdf</a>]</td>

	<td>[<a href=\"index.php?load=tools/modules/billing/htmlpreviousinvoice&billingid=$billingid&invoiceid=$invoice_number&details=on&type=fs&submit=on\" target=\"_blank\">$l_html</a>]</td>

	<td>[<a href=\"index.php?load=tools/modules/billing/extendedpreviousinvoice&billingid=$billingid&invoiceid=$invoice_number&details=on&type=fs&submit=on\" target=\"_blank\">$l_extended</a>]</td>

	<td>[<a
href=\"index.php?load=tools/modules/billing/emailpreviousinvoice&billingid=$billingid&invoiceid=$invoice_number&details=on&type=dl&submit=on\">$l_email</a>]</td>";
     if ($normal_sum == 0) {
       echo "<td>[<a href=\"index.php?load=invmaint&invoicenum=$invoice_number&remove=on&type=tools&submit=on\">$l_remove</a>]</td>";
     } else {
       echo "<td>$normal_sum $l_paid</td>";
     }
     // print payment link with prefilled in information if there are new charges to pay to this invoice
     echo "<td>";
     if ($new_charges > 0) {
       echo "<a href=# onclick=\"popupPage('index.php?".
	 "load=payment&type=tools&invoice_number=$invoice_number&amount=$new_charges'); return false;\">$l_enterpayments</a>";
     }
     echo "</td><tr>";
   }
   
   print "<td bgcolor=\"#dddddd\" colspan=16>";
   if (!$showall) { echo "<a href=\"index.php?load=invmaint&type=tools&billingid=$billingid&submit=Submit&showall=true\">$l_showall</a>"; }
   print "</td></table>";
   
 }
 else {
   //
   // ask for the billing date that they want to invoice
   //
   echo "<FORM ACTION=\"index.php\" METHOD=\"POST\">
	<input type=hidden name=load value=invmaint>
	<input type=hidden name=type value=tools>
	<table>
	<td>$l_billingid:</td><td><input type=text name=billingid></td><tr>
	<td></td><td><INPUT TYPE=\"SUBMIT\" NAME=\"submit\" value=\"$l_submitrequest\"></td>
	</form>";
 }

?>
</body>
</html>
