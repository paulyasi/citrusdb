<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>

<h2><?php echo $org_name?></h2>
<?php echo $org_street?><br>
<?php echo $org_city.", ".$org_state." ".$org_zip;?><br>
<h3><?php echo lang('paymentreceipt')?></h3>
<?php echo lang('accountnumber')?>: <?php echo $billing_account_number?><br><br><br>
<?php echo $billing_name?><br>
<?php echo $billing_company?><br>
<?php echo $billing_street?><br>
<?php echo $billing_city." ".$billing_state." ".$billing_zip;?><br><br><br>

<?php
if ($payment_type == "creditcard") {    
  // wipe out the middle of the card number
  $length = strlen($creditcard_number);
  $firstdigit = substr($creditcard_number, 0,1);
  $lastfour = substr($creditcard_number, -4);
  $creditcard_number = "$firstdigit" . "***********" . "$lastfour";
  
  echo lang('paid')." with $payment_type ($creditcard_number), ".
    "$amount on $human_date for:\n<br><br>";
 } else {
  echo lang('paid')." with $payment_type ($check_number), ".
    "$amount on $human_date for:\n<br><br>";    
 }
?>

<table>
<td><?php echo lang('invoice')?></td>
<td><?php echo lang('id')?></td>
<td><?php echo lang('description')?></td>
<td><?php echo lang('paid')?></td><tr>

<?php
foreach ($payment_details AS $myresult) 
{
	$user_services_id = $myresult['user_services_id'];
	$invoice = $myresult['original_invoice_number'];
	$description = $myresult['service_description'];
	$tax_description = $myresult['description'];
	$from_date = humandate($myresult['from_date']);
	$to_date = humandate($myresult['to_date']);
	$paid_amount = sprintf("%.2f",$myresult['paid_amount']);
	$billed_amount = sprintf("%.2f",$myresult['billed_amount']);
	$options_table = $myresult['options_table'];

	$owed_amount = sprintf("%.2f",$billed_amount - $paid_amount);

	// check if it's a tax with a tax id or service with
	// no tax idfirst to set detail items
	if ($options_table <> '') 
	{
		// get the data from the options table 
		// and put into variables
		// get the data from the options table and put into variables
		$myoptions = $this->service_model->options_attributes($user_services_id, $options_table);
		$optiondetails = $myoptions[2];
	} 
	else 
	{
		$optiondetails = '';	
	}

	if ($tax_description) 
	{
		// print the tax as description instead
		echo "<td>$invoice</td><td>$user_services_id</td><td>&nbsp;&nbsp;&nbsp;".
			"$tax_description &nbsp;&nbsp; $optiondetails <td>$paid_amount</td><tr>";
	} 
	else 
	{
		echo "<td>$invoice</td><td>$user_services_id</td><td>$description &nbsp;&nbsp; ".
			"$optiondetails ($from_date ".lang('to')." $to_date)</td><td>$paid_amount</td><tr>";
	}
}
?>
</table>
