<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<html>
<head>
<script language="JavaScript">
	function h(oR) {
		oR.style.backgroundColor='ffdd77';
	}	
	function deh(oR) {
		oR.style.backgroundColor='ddddee';
	}
	function dehnew(oR) {
		oR.style.backgroundColor='ddeeff';
	}
	function popupPage(page) {
		window.open(page, "Tools", "height=400,width=600,location=0,scrollbars=1,menubar=1,toolbar=0,resizeable=1,left=100,top=100");
	}
</script>
</head>
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
	$paid = sprintf("%.2f",$myresult['normal_paid_amount']);
	$billed = sprintf("%.2f",$myresult['normal_billed_amount']);
	$owed = sprintf("%.2f", $billed - $paid);

	print "<tr bgcolor=\"#eeeeee\">
		<td>$invoice_number</td>
		<td>$billing_date</td>
		<td>$name</td>
		<td>$company</td>
		<td>$from_date</td>
		<td>$to_date</td>
		<td><a href=\"$this->url_prefix/index.php/billing/editinvoiceduedate/$billingid/$invoice_number/$due_date\">$due_date</a></td>
		<td>$new_charges</td>
		<td>$total_due</td>
		<td>$billing_type</td>
		<td>[<a href=\"$this->url_prefix/index.php/tools/billing/printpreviousinvoice/$billingid/$invoice_number\">".lang('pdf')."</a>]</td>

		<td>[<a href=\"$this->url_prefix/index.php/tools/billing/htmlpreviousinvoice/$billingid/$invoice_number\" target=\"_blank\">".lang('html')."</a>]</td>

		<td>[<a href=\"$this->url_prefix/index.php/tools/billing/extendedpreviousinvoice/$billingid/$invoice_number\" target=\"_blank\">".lang('extended')."</a>]</td>

		<td>[<a
		href=\"$this->url_prefix/index.php/tools/billing/emailpreviousinvoice/$billingid/$invoice_number\">".lang('email')."</a>]</td>";
	if ($paid == 0) {
		echo "<td>[<a href=\"$this->url_prefix/index.php/billing/removeinvoice/$invoice_number\">".lang('remove')."</a>]<br \>";
		echo "$paid $owed ".lang('due')."</td>";
	} else {
		echo "<td>$paid $owed ".lang('due')."</td>";
	}
	// print payment link with prefilled in information if there are new charges to pay to this invoice
	echo "<td>";
	if ($new_charges > 0) {
		echo "<a href=# onclick=\"popupPage('$this->url_prefix/index.php/tools/billing/payment/$invoice_number/$new_charges".
			"'); return false;\">".lang('enterpayments')."</a>";
	}
	echo "</td><tr>";
}

print "<td bgcolor=\"#dddddd\" colspan=16>";
if (!$showall) 
{ 
	echo "<a href=\"$this->url_prefix/index.php/billing/invmaint/$billingid/showall\">
	<?php echo lang('showall');?></a>"; 
}
print "</td></table>";
?>
</body>
</html>
