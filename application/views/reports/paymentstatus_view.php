<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<h3><?php echo lang('paymentstatus')?></h3>

<?php
// show the form to pick what day to view
$day_1  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
?>
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/reports/paymentstatus" METHOD="POST">
<table>
<td><?php echo lang('from')?>: <input type=text name="day1" value="<?php echo $day_1?>"></td>
<td> - <?php echo lang('to')?>: <input type=text name="day2"value="<?php echo $day_1?>"></td>

<tr><td colspan=2><?php echo lang('organizationname')?>:
<select name="organization_id">
<option value=""><?php echo lang('choose')?></option>
<?php
foreach ($orglist as $myresult) {
	$myid = $myresult['id'];
	$myorg = $myresult['org_name'];
	echo "<option value=\"$myid\">$myorg</option>";
}
?>
</select></td>

<tr><td> <?php echo lang('billingtype')?>: 
<select name="showpaymenttype">
<option value="creditcard">creditcard</option>
<option value="check">check</option>
<option value="cash">cash</option>
<option value="eft">eft</option>
<option value="nsf">nsf</option>
</select></td>

<tr><td><?php echo lang('status')?>: 
<select name="showstatus">
<option value="declined">declined</option>   
<option value="authorized">authorized</option>
<option value="credit">credit</option>
</select></td>

<input type=hidden name=load value=billing>
<input type=hidden name=tooltype value=module>
<input type=hidden name=type value=tools>
<input type=hidden name=declined value=on>
<td><input type=submit name="<?php echo lang('submit')?>"></td>
</table>
</form>



<hr>
<b><?php echo $org_name.", ".$day1." ".lang('to')." ".$day2." ".$showpaymenttype." ".$showstatus?></b>
<table>
<tr style="text-decoration: underline;">
<td><?php echo lang('name')?></td>
<td><?php echo lang('phone')?></td>
<td><?php echo lang('billingid')?></td>
<td><?php echo lang('number')?></td>
<td><?php echo lang('response')?></td>
<td><?php echo lang('amount')?></td><tr>

<?php
$totalrun = 0;
$statusrun = 0;

foreach ($paymentstatus as $myresult) 
{
	$billing_id = $myresult['billing_id'];
	$creditcard_number = $myresult['creditcard_number'];
	$check_number = $myresult['check_number'];
	$response_code = $myresult['response_code'];
	$billing_amount = $myresult['billing_amount'];
	$status = $myresult['status'];
	$name = $myresult['name'];
	$phone = $myresult['phone'];

	if ($status == $showstatus) {
		echo "<td>$name</td>";
		echo "<td>$phone</td>";
		echo "<td>$billing_id</td>";
		echo "<td>$creditcard_number $check_number</td>";
		echo "<td>$response_code</td>";
		echo "<td>$billing_amount</td>";
		echo "<tr>";
		$statusrun++;
	}

	// add to total for authorized also, don't count other status like credits
	if (($status == 'authorized')
			OR ($status == 'declined')
			OR ($status == 'credit')) {
		$totalrun++;
	}
}
echo "</table>";

// print the total number of transactions run and the total number of declines
echo "<p>".lang('total')." $showpaymenttype: $totalrun, $showstatus: $statusrun";

$totalrun = 0;
$declinedrun = 0;

foreach ($distinctdeclined as $myresult) 
{
	$declinedrun++;
}

// print the total DISTINCT number of declines
echo "<p>DISTINCT".lang('declined').": $declinedrun";

echo "<p>Other Payments<br>";
$check = 0;
$cash = 0;
$eft = 0;
$other = 0;

foreach ($noncardpayments AS $myresult) 
{
	$billing_id = $myresult['billing_id'];
	$payment_type = $myresult['payment_type'];
	$response_code = $myresult['response_code'];
	$billing_amount = $myresult['billing_amount'];
	$status = $myresult['status'];
	$name = $myresult['name'];
	$phone = $myresult['phone'];

	switch ($payment_type) 
	{
		case "check":
			$check++;
		break;
		case "cash":
			$cash++;
		break;
		case "eft":
			$eft++;
		break;
		default:
			$other++;
	}
}

echo "cash: $cash <br> check: $check <br> eft: $eft <br> other: $other";
?>
</body>
</html>







