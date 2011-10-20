<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>

<table cellspacing=0 cellpadding=4 border=0>
<td bgcolor="#ddeeee" width=100><b><?php echo lang('invoicenum')?></b></td>
<td bgcolor="#ddeeee" width=130><b><?php echo lang('billingid')?></b></td>		
<td bgcolor="#ddeeee" width=130><b><?php echo lang('date')?></b></td>
<td bgcolor="#ddeeee" width=200><b><?php echo lang('type')?></b></td>
<td bgcolor="#ddeeee" width=100><b><?php echo lang('from')?></b></td>
<td bgcolor="#ddeeee" width=100><b><?php echo lang('to')?></b></td>
<td bgcolor="#ddeeee" width=100><b><?php echo lang('duedate')?></b></td>
<td bgcolor="#ddeeee" width=100><b><?php echo lang('newcharges')?></b></td>
<td bgcolor="#ddeeee" width=150><b><?php echo lang('total')?></b></td>
<?php 
foreach ($history as $myresult)
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
	print "<td style=\"border-top: 1px solid grey;\">[ <a href=\"$this->url_prefix/index.php/tools/billing/htmlpreviousinvoice/$this->account_number/$id\" target=\"_blank\">$id</a> ]</td>";
	print "<td style=\"border-top: 1px solid grey;\">$billing_id &nbsp;</td>";
	print "<td style=\"border-top: 1px solid grey;\">$billing_date &nbsp;</td>";
	print "<td style=\"border-top: 1px solid grey;\">$billing_type &nbsp;</td>";
	print "<td style=\"border-top: 1px solid grey;\">$from_date &nbsp;</td>";
	print "<td style=\"border-top: 1px solid grey;\">$to_date &nbsp;</td>";
	print "<td style=\"border-top: 1px solid grey;\">$payment_due_date &nbsp;</td>";
	print "<td style=\"border-top: 1px solid grey;\">$new_charges &nbsp;</td>";
	print "<td style=\"border-top: 1px solid grey;\">$total_due &nbsp;</td>";
}
?>


<tr bgcolor="#dddddd"><td style="padding: 5px;" colspan=6>
<a href="<?php echo $this->url_prefix?>/index.php/billing/billinghistory/all">
<?php echo lang('showall')?>...</a></td>

</table>

</body>
</html>
