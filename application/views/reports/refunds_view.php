<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<h3><?php echo lang('creditcard')." ".lang('refundreport')?></h3>

<FORM ACTION="<?php echo $this->url_prefix?>/index.php/reports/refunds" METHOD="POST">
<table>
<td><?php echo lang('from')?>: <input type=text name="day1" value="<?php echo $day1?>" size=10> - </td>
<td><?php echo lang('to')?>: <input type=text name="day2" value="<?php echo $day2?>" size=10></td>

<td><b><?php echo lang('organizationname')?></b></td>
<td><select name="organization_id">
<option value=""><?php echo lang('choose')?></option>
<?php
foreach ($orglist as $myresult) {
	$myid = $myresult['id'];
	$myorg = $myresult['org_name'];
	echo "<option value=\"$myid\">$myorg</option>";
}
?>
</select></td><tr>

</td><tr> 
<td></td><td><br><input type=submit name="<?php echo lang('submit')?>" value="submit"></td>
</table>
</form>


<b><?php echo $organization_name.": ".$day1." ".lang('to')." ".$day2."</b>";?>

<p><table cellpadding=5 border=1 cellspacing=0>
<td><?php echo lang('date')?></td>
<td><?php echo lang('accountnumber')?></td>
<td><?php echo lang('name')?></td>
<td><?php echo lang('creditcard')?></td>
<td><?php echo lang('amount')?></td><tr>
<?php
foreach ($refunds AS $myresult) 
{
	$refund_date = $myresult['refund_date'];
	$creditcard_number = $myresult['creditcard_number'];
	$amount = $myresult['refund_amount'];
	$account_number = $myresult['account_number'];
	$name = $myresult['name'];
	echo "<td>$refund_date</td><td>$account_number</td><td>$name</td><td>$creditcard_number</td><td>$amount</td><tr>";
}
?>

</table>

</body>
</html>







