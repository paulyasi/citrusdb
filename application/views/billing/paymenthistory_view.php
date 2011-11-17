<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>

<table cellspacing=0 cellpadding=2 border=0>
<td bgcolor="#eedddd" width=40><b><?php echo lang('id')?></b></td>
<td bgcolor="#eedddd" width=40><b><?php echo lang('invoice')?></b></td>
<td bgcolor="#eedddd" width=100><b><?php echo lang('date')?></b></td>
<td bgcolor="#eedddd" width=75><b><?php echo lang('type')?></b></td>
<td bgcolor="#eedddd" width=100><b><?php echo lang('status')?></b></td>
<td bgcolor="#eedddd" width=50><b><?php echo lang('avs')?></b></td>
<td bgcolor="#eedddd" width=190><b><?php echo lang('number')?></b></td>
<td bgcolor="#eedddd" width=40><b><?php echo lang('exp')?></b></td>
<td bgcolor="#eedddd" width=100><b><?php echo lang('amount')?></b></td>
<?php
$nsfcount = 0;

foreach ($history AS $myresult) 
{
	$id = $myresult['p_id']; // payment id
	$bid = $myresult['b_id']; // billind id
	$date = $myresult['p_cdate'];
	$type = $myresult['p_payment_type'];
	$status = $myresult['p_status'];
	$response = $myresult['p_response_code'];
	$avs_response = $myresult['p_avs_response'];
	$check_number = $myresult['p_check_number'];
	$creditcard_number = $myresult['p_creditcard_number'];
	$creditcard_expire = $myresult['p_creditcard_expire'];
	$amount = sprintf("%.2f",$myresult['p_billing_amount']);
	$billingid = $myresult['p_billing_id'];
	$invoice_number = $myresult['p_invoice_number'];

	// translate the status to a language
	$langstatus = '';
	switch ($status) 
	{
		case 'authorized':
			$langstatus = lang('authorized');
			break;
		case 'declined':
			$langstatus = lang('declined');
			break;
		case 'pending':
			$langstatus = lang('pending');
			break;
		case 'donotreactivate':
			$langstatus = lang('donotreactivate');
			break;
		case 'collections':
			$langstatus = lang('collections');
			break;
		case 'pastdue':
			$langstatus = lang('pastdue');
			break;
		case 'noticesent':
			$langstatus = lang('noticesent');
			break;
		case 'waiting':
			$langstatus = lang('waiting');
			break;      
		case 'turnedoff':
			$langstatus = lang('turnedoff');
			break;
		case 'credit':
			$langstatus = lang('credit');
			break;
		case 'canceled':
			$langstatus = lang('canceled');
			break;
		case 'cancelwfee':
			$langstatus = lang('cancelwithfee');
			break;
	}

	print "<tr bgcolor=\"#ffeeee\">";
	print "<td style=\"border-top: 1px solid grey;\">$bid &nbsp;</td>";
	print "<td style=\"border-top: 1px solid grey;\">$invoice_number &nbsp;</td>";  
	print "<td style=\"border-top: 1px solid grey;\">$date &nbsp;</td>";
	print "<td style=\"border-top: 1px solid grey;\">$type &nbsp;</td>";
	print "<td style=\"border-top: 1px solid grey;\">$langstatus &nbsp;</td>";
	print "<td style=\"border-top: 1px solid grey;\">$avs_response &nbsp;</td>";
	print "<td style=\"border-top: 1px solid grey;\">$creditcard_number $check_number &nbsp;";

	// check if we should print the nsf link
	// make sure that the amount is greater than zero and only
	// print the three most recent ones

	if (($amount > 0) AND ($nsfcount < 3) 
			AND (($type == 'check') OR ($type == 'cash') OR ($type == 'eft') 
				OR ($type == 'discount'))) 
	{ 
		if (($userprivileges['manager'] == 'y') OR ($userprivileges['admin'] == 'y')) 
		{
			echo "<a href=\"$this->url_prefix/index.php/billing/nsf".
				"/$id/$invoice_number/$amount/$billingid\" ".
				"target=\"_parent\" style=\"font-size: 8pt;\">".lang('mark_as_nsf')."</a>";    
		}

		$nsfcount++;
	}

	if (($status == 'pastdue') OR ($status == 'turnedoff') OR ($status == 'canceled') 
			OR ($status == 'declined') OR ($status == 'waiting') 
			OR ($status == 'noticesent') OR ($status == 'cancelwfee')) 
	{
		if (($userprivileges['manager'] == 'y') OR ($userprivileges['admin'] == 'y')) 
		{
			echo "<a href=\"$this->url_prefix/index.php?load=billing&type=module&deletepayment=on=&".
				"paymentid=$id\" target=\"_parent\" style=\"font-size: 8pt;\">".lang('delete')."</a>";
		}
	}

	// check if we should print the delete status link


	print "</td>";
	print "<td style=\"border-top: 1px solid grey;\">$creditcard_expire &nbsp;</td>";
	print "<td style=\"border-top: 1px solid grey;\">$amount &nbsp;";

	if (($amount > 0) AND ($status == 'authorized')) 
	{
		echo "<a href=\"$this->url_prefix/index.php/billing/receipt/$id\"".
			" target=\"_parent\" style=\"font-size: 8pt;\">".lang('receipt')."</a>";     
	}

	print "</td>";
}
?>

<tr bgcolor="#dddddd"><td style="padding: 5px;" colspan=6><a href="
<?php echo $this->url_prefix?>/index.php/billing/paymenthistory/all">
<?php echo lang('showall')?>...</a></td>
</table>
</body>
</html>
