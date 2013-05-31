<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
	<script language="JavaScript">
function checkitems(fieldArray)
{
	for (i = 0; i < fieldArray.length; i++)
	{
		if (fieldArray[i].checked == true) 
		{
			field[i].checked = false;
		} 
		else 
		{
			field[i].checked = true ;
		}
	}
}
</script>

<br><br>
<h4><?php lang('areyousurereruncreditcard');?></h4>
<form style="margin-bottom:0;" action="<?php echo $this->url_prefix;?>/index.php/billing/savererun" name="rerunform" method=post>

<blockquote><table border=0 cellspacing=0 cellpadding=3>
<?php
// initialize rerun items count to check later
$itemcount = 0;
$fieldlist = "";

$current_invoice_line = 0;
$current_invoice_total = 0;
$bgcolor = "#EEEEEE";
if(!$rerunitems)
{
	echo lang('rerunitemerror');
	echo "</table></blockquote>\n";
}
else
{
	echo "<td></td><td><?php echo lang('invoice');?></td>
		<td><?php echo lang('id');?></td><td><?php echo lang('date');?></td>
		<td><?php echo lang('service');?></td><td><?php echo lang('price');?></td><tr>";

	foreach ($rerunitems as $myresult) 
	{
		$detail_id = $myresult['bd_id'];
		$original_invoice_number = $myresult['original_invoice_number'];
		$service_id = $myresult['us_id'];
		$creation_date = humandate($myresult['creation_date']);
		$user_services_id = $myresult['user_services_id'];
		$detail_total = sprintf("%.2f",$myresult['billed_amount'] - $myresult['paid_amount']);
		$description = $myresult['service_description'];

		// check if we are on a new invoice line item that needs a heading for this invoice
		// to seperate them from the other invoice line items in the view
		if ($current_invoice_line <> $original_invoice_number) 
		{
			if ($current_invoice_total <> 0) 
			{
				// print a line with the previous invoices current total before resetting it
				echo "<td bgcolor=\"$bgcolor\" colspan=6 align=right ".
					"style=\"border-bottom: 1px solid black;\"><b>". 
					sprintf("%.2f",$current_invoice_total) ."</b></td><tr>";
			}

			// reset the bgcolor
			if ($bgcolor == "#EEEEEE") 
			{
				$bgcolor = "#FFFFFF";
			} 
			else 
			{
				$bgcolor = "#EEEEEE";
			}

			$current_invoice_total = 0;
			echo "<td colspan=6 bgcolor=\"$bgcolor\"><b>".
				"&nbsp; ". lang('invoice') .": $original_invoice_number ".
				"</b></td><tr>";
		}	 	 

		// clear the rerun flag on all the items shown before you allow setting
		// of new rerun flags
		$this->billing_model->clearrerunflag($detail_id);

		// print the detail items that are unpaid and can be rerun

		echo "<td bgcolor=\"$bgcolor\"><input type=checkbox name=\"rerun_service_$detail_id\" value=\"$detail_id\"></td>\n";
		echo "<td bgcolor=\"$bgcolor\">$original_invoice_number</td>\n";
		echo "<td bgcolor=\"$bgcolor\">$service_id</td>\n";
		echo "<td bgcolor=\"$bgcolor\">$creation_date</td>\n";
		echo "<td bgcolor=\"$bgcolor\">$description</td>\n";
		echo "<td bgcolor=\"$bgcolor\">$detail_total</td>\n";

		echo "<tr>\n";

		// set the current invoice line to the original invoice number of this item
		$current_invoice_line = $original_invoice_number;

		// update the current invoice total with this new item
		$current_invoice_total = $current_invoice_total + $detail_total;

		$fieldname = "rerun_service_$detail_id";

		$fieldlist .= ',' . $fieldname;

		// add to the number of items rerun
		$itemcount++;
	}

	// print the last line with the previous invoices current total before resetting it
	echo "<td colspan=6 align=right bgcolor=\"$bgcolor\"><b>". sprintf("%.2f",$current_invoice_total) ."</b></td><tr>";

	print "<input type=hidden name=fieldlist value=$fieldlist>";

	echo "</table></blockquote>\n";


	// print the yes/no buttons if there are items to rerun
	// else print an error that there are no items to be rerun

	if ($itemcount > 0) 
	{
		print "<table cellpadding=15 cellspacing=0 border=0 width=720>".
			"<td align=right width=360>";

		print "<input type=hidden name=load value=billing>";
		print "<input type=hidden name=type value=module>";
		print "<input type=hidden name=rerun value=on>";
		print "<input type=hidden name=billing_id value=$billing_id>";
		print "<input name=save type=submit value=\" ".lang('yes')." \" class=smallbutton>".
			"</form></td>";
		print "<td align=left width=360><form style=\"margin-bottom:0;\" ".
			"action=\"$this->url_prefix/index.php/billing\" method=post>";

		print "<input name=done type=submit value=\" ".lang('no')."  \" class=smallbutton>";
		print "</form></td></table>";
	} 
	else 
	{
		print "<p><b>".lang('rerunitemerror')."</b></p>";
	}
}
