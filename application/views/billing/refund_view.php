<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed')?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('refundreport');?></h3>

[ <a href="<?php echo $this->url_prefix;?>/index.php/billing"><?php echo lang('back');?></a> ]
<table cellspacing=0 cellpadding=4 border=0>
<td bgcolor="#dddddd" width=100><b><?php echo lang('id');?></b></td>
<td bgcolor="#dddddd" width=130><b><?php echo lang('date');?></b></td>
<td bgcolor="#dddddd" width=200><b><?php echo lang('description');?></b></td>
<td bgcolor="#dddddd" width=100><b><?php echo lang('invoice');?></b></td>
<td bgcolor="#dddddd" width=100><b><?php echo lang('billedamount');?></b></td>
<td bgcolor="#dddddd" width=150><b><?php echo lang('paidamount');?></b></td>
<td bgcolor="#dddddd" width=150><b><?php echo lang('refundamount');?></b></td>
<td bgcolor="#dddddd" width=150><b><?php echo lang('refunddate');?></b></td>
<td bgcolor="#dddddd" width=150><b><?php echo lang('refunded');?></b></td>	

<?php
// print the column rows	
foreach ($details as $myresult)
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
		echo "[ <a href=\"$this->url_prefix/index.php/refunditem/$id/$method/$billingid\">".lang('refund')."</a> ]";
	}	
	echo "</td>";


} // end while

print "</table>";

