<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('invoicemaintenance')?></h3>

[ <a href="<?php echo $this->url_prefix?>/index.php/billing"><?php echo lang('back')?></a> ]

<table cellpadding=5 cellspacing=1><tr bgcolor="#dddddd">
<td><?php echo lang('invoicenumber');?></td>
<td><?php echo lang('billingdate');?></td>
<td><?php echo lang('name');?></td>
<td><?php echo lang('company');?></td>
<td><?php echo lang('from');?></td>
<td><?php echo lang('to');?></td>
<td><?php echo lang('duedate');?></td>
<td><?php echo lang('newcharges');?></td>
<td><?php echo lang('total');?></td>
<td><?php echo lang('billingtype');?></td>
<td></td><td></td><td></td><td></td><td></td>

<?php
foreach ($invoicelist as $myresult) 
{
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
		<td>[<a href=\"index.php?load=tools/printpreviousinvoice&billingid=$billingid&invoiceid=$invoice_number&details=on&type=dl&submit=on\">".lang('pdf')."</a>]</td>

		<td>[<a href=\"index.php?load=tools/modules/billing/htmlpreviousinvoice&billingid=$billingid&invoiceid=$invoice_number&details=on&type=fs&submit=on\" target=\"_blank\">".lang('html')."</a>]</td>

		<td>[<a href=\"index.php?load=tools/modules/billing/extendedpreviousinvoice&billingid=$billingid&invoiceid=$invoice_number&details=on&type=fs&submit=on\" target=\"_blank\">".lang('extended')."</a>]</td>

		<td>[<a
		href=\"index.php?load=tools/modules/billing/emailpreviousinvoice&billingid=$billingid&invoiceid=$invoice_number&details=on&type=dl&submit=on\">".lang('email')."</a>]</td>";
	if ($normal_sum == 0) {
		echo "<td>[<a href=\"index.php?load=invmaint&invoicenum=$invoice_number&remove=on&type=tools&submit=on\">".lang('remove')."</a>]</td>";
	} else {
		echo "<td>$normal_sum ".lang('paid')."</td>";
	}
	// print payment link with prefilled in information if there are new charges to pay to this invoice
	echo "<td>";
	if ($new_charges > 0) {
		echo "<a href=# onclick=\"popupPage('index.php?".
			"load=payment&type=tools&invoice_number=$invoice_number&amount=$new_charges'); return false;\">".lang('enterpayments')."</a>";
	}
	echo "</td><tr>";
}

print "<td bgcolor=\"#dddddd\" colspan=16>";
if (!$showall) 
{ 
	echo "<a href=\"index.php/billing/invmaint/$billingid/showall\">
	<?php echo lang('showall');?></a>"; 
}
print "</td></table>";

