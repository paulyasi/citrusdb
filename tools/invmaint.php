<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_invoicemaintenance</h3>

[ <a href=\"index.php?load=billing&type=module\">$l_back</a> ]";

// Copyright (C) 2002-2009  Paul Yasi (paul at citrusdb.org)
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
if (!isset($base->input['remove'])) { $base->input['remove'] = ""; }
if (!isset($base->input['delete'])) { $base->input['delete'] = ""; }
if (!isset($base->input['details'])) { $base->input['details'] = ""; }
if (!isset($base->input['invoicenum'])) { $base->input['invoicenum'] = ""; }
if (!isset($base->input['editduedate'])) { $base->input['editduedate'] = ""; }
if (!isset($base->input['saveduedate'])) { $base->input['saveduedate'] = ""; }
if (!isset($base->input['duedate'])) { $base->input['duedate'] = ""; }

$submit = $base->input['submit'];
$billingid = $base->input['billingid'];
$remove = $base->input['remove'];
$delete = $base->input['delete'];
$details = $base->input['details'];
$invoicenum = $base->input['invoicenum'];
$editduedate = $base->input['editduedate'];
$saveduedate = $base->input['saveduedate'];
$duedate = $base->input['duedate'];

if ($delete) {

  //
  // Delete the invoice, delete from billing history where id = $invoicenum
  //
  $query = "DELETE FROM billing_history WHERE id = $invoicenum";
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  
  //
  // delete from billing_details where invoice_number = $invoicenum
  //
  $query = "DELETE FROM billing_details WHERE invoice_number = $invoicenum";                                          
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  
  print "$l_deleted $invoicenum";
 }
 else if ($remove) {
   
   //
   // Ask if they want to remove the invoice, print the yes/no form
   //

   print "<b>$l_areyousureyouwanttoremoveinvoice $invoicenum</b>";
   
   print "<table cellpadding=15 cellspacing=0 border=0 width=720><td align=right width=360>";
   print "<form style=\"margin-bottom:0;\" action=\"index.php\">";
   print "<input type=hidden name=load value=invmaint>";
   print "<input type=hidden name=type value=tools>";
   print "<input type=hidden name=invoicenum value=$invoicenum>";
   print "<input type=hidden name=delete value=on>";
   print "<input name=deletenow type=submit value=\"  $l_yes  \" class=smallbutton></form></td>";
   print "<td align=left width=360><form style=\"margin-bottom:0;\" action=\"index.php\">";
   print "<input type=hidden name=type value=tools>";
   print "<input name=done type=submit value=\"  $l_no  \" class=smallbutton>";
   print "<input type=hidden name=load value=invmaint>";
   print "</form></td></table>";
   
 }
 else if ($editduedate) {
   // to change the payment due date when a partial payment is made
   // and the customer wants to push their due date ahead
   // ask what they want to change the payment due date to

   echo "<FORM ACTION=\"index.php\" METHOD=\"GET\">".
     "<input type=hidden name=load value=invmaint>".
     "<input type=hidden name=type value=tools>".
     "<input type=hidden name=invoicenum value=\"$invoicenum\">".
     "<input type=hidden name=saveduedate value=\"on\">".     
     "<input type=hidden name=billingid value=\"$billingid\">".
     "<table>".
     "<td>$l_new $l_duedate:</td><td><input type=text name=duedate ".
     "value=\"$duedate\"></td><tr>".
     "<td></td><td><INPUT TYPE=\"SUBMIT\" NAME=\"submit\" ".
     "value=\"$l_submitrequest\"></td>".
     "</form>";
   
 }
 else if ($saveduedate) {
   // TODO: save the new due date that was entered into the
   // billing_history.payment_due_date field

   $query = "UPDATE billing_history SET payment_due_date = '$duedate' ".
     "WHERE id = '$invoicenum'";
   $result = $DB->Execute($query) or die ("due date update $l_queryfailed");

    // redirect back to the services record for their account
   echo "<script language=\"JavaScript\">window.location.href ".
     "= \"index.php?load=invmaint&type=tools&billingid=$billingid&submit=Submit\";</script>";

 }
 else if ($submit) {
   
   //
   // Show the invoices that belong to that billing id:
   //
   $query = "SELECT h.id h_id, h.billing_date h_billing_date, h.from_date 
	h_from_date, h.to_date h_to_date, h.payment_due_date h_due_date, 
	h.new_charges h_new_charges, h.total_due h_total_due,
	h.billing_type h_billing_type, 
	b.name b_name, b.company b_company, d.invoice_number, 
	SUM(d.billed_amount) as billed_amount, 
	SUM(d.paid_amount) as normal_paid_amount, 
	SUM(ABS(d.paid_amount)) as paid_amount
	FROM billing_history h
        LEFT JOIN billing b ON h.billing_id = b.id 
	LEFT JOIN billing_details d ON h.id = d.invoice_number 
        WHERE h.billing_id  = '$billingid' GROUP BY d.invoice_number 
	ORDER BY d.invoice_number DESC";

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
     $sum = $myresult['paid_amount'];
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
     // print payment link with prefilled in information
     
     echo "<td><a href=# onclick=\"popupPage('index.php?".
       "load=payment&type=tools&invoice_number=$invoice_number&amount=$new_charges')\">$l_enterpayments</a>".
       
       "</td><tr>";
   }
   
   print "</table>";
   
 }
 else {
   //
   // ask for the billing date that they want to invoice
   //
   echo "<FORM ACTION=\"index.php\" METHOD=\"GET\">
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
