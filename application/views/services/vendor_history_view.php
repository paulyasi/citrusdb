<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>

<h3><?php echo lang('vendor_history')?></h3>
    
<?php
// print form to edit the things in the options table
print "<h4>$userserviceid $servicedescription ($service_org_name) $optionsdetails $optionsdetails2".
"&nbsp;&nbsp;&nbsp; ".lang('createdon').": $creationdate, ";
if ($removed == 'y') {
	print lang('removed').": $enddate</h4>";
} else {
	print lang('active')."</h4>";
}
?>

<table cellpadding=5 cellspacing=1><tr bgcolor="#dddddd">
<td><?php echo lang('entry_type')?></td>
<td><?php echo lang('entry_date')?></td>
<td><?php echo lang('vendor_name')?></td>
<td><?php echo lang('vendor_bill_id')?></td>
<td><?php echo lang('vendor_cost')?></td>
<td><?php echo lang('vendor_tax')?></td>
<td><?php echo lang('vendor_item_id')?></td>
<td><?php echo lang('vendor_invoice_number')?></td>
<td><?php echo lang('vendor_from_date')?></td>
<td><?php echo lang('vendor_to_date')?></td>
<td><?php echo lang('status')?></td>
<td><?php echo lang('billedamount')?></td></tr>

<?php
foreach ($vendor_history AS $myresult) 
{
	$entry_type = $myresult['entry_type'];
	$entry_date = $myresult['entry_date'];
	$vendor_name = $myresult['vendor_name'];
	$vendor_bill_id = $myresult['vendor_bill_id'];
	$vendor_cost = $myresult['vendor_cost'];
	$vendor_tax = $myresult['vendor_tax'];
	$vendor_item_id = $myresult['vendor_item_id'];
	$vendor_invoice_number = $myresult['vendor_invoice_number'];
	$vendor_from_date = $myresult['vendor_from_date'];
	$vendor_to_date = $myresult['vendor_to_date'];
	$account_status = $myresult['account_status'];
	$billed_amount = $myresult['billed_amount'];

	echo "<tr bgcolor=\"#eeeeee\"><td>$entry_type</td>".
		"<td>$entry_date</td>".
		"<td>$vendor_name</td>".
		"<td>$vendor_bill_id</td>".
		"<td>$vendor_cost</td>".
		"<td>$vendor_tax</td>".
		"<td>$vendor_item_id</td>".
		"<td>$vendor_invoice_number</td>".
		"<td>$vendor_from_date</td>".
		"<td>$vendor_to_date</td>".
		"<td>$account_status</td>".
		"<td>$billed_amount</td>".
		"</tr>";
}
?>

</table>

<hr><h3><?php echo lang('add')?></h3>
<form action="<?php echo $this->url_prefix?>/index.php/savevendor" method=post>
<input type=hidden name=userserviceid value=<?php echo $userserviceid?>>
<table>

<td><?php echo lang('entry_type')?></td><td>
<select name=entry_type>
<option value=""><?php echo lang('choose')?></option>
<option value="order">order</option>
<option value="recurring bill">recurring bill</option>
<option value="onetime bill">onetime bill</option>
<option value="change">change</option>
<option value="disconnect">disconnect</option>
</select></td><tr>

<?php
$mydate = date("Y-m-d");
?>
<td><?php echo lang('entry_date')?></td>
<td><input type=text name=entry_date value="<?php echo $mydate?>"></td><tr>

<td><?php echo lang('vendor_name')?></td><td><select name=vendor_name>

<?php
foreach ($vendor_names AS $mynameresult) 
{
	$name = $mynameresult['name'];    
	echo "<option value=\"$name\">$name</option>";    
}
?>

</select></td><tr>

<td><?php echo lang('vendor_bill_id')?></td><td><input type=text name=vendor_bill_id value="0"></td><tr>
<td><?php echo lang('vendor_cost')?></td><td><input type=text name=vendor_cost value="0"></td><tr>
<td><?php echo lang('vendor_tax')?></td><td><input type=text name=vendor_tax value="0"></td><tr>
<td><?php echo lang('vendor_item_id')?></td><td><input type=text name=vendor_item_id value="0"></td><tr>

<td><?php echo lang('vendor_invoice_number')?></td><td><input type=text name=vendor_invoice_number value="0"></td><tr>
<td><?php echo lang('vendor_from_date')?></td><td><input type=text name=vendor_from_date value="0"></td><tr>
<td><?php echo lang('vendor_to_date')?></td><td><input type=text name=vendor_to_date value="0"></td><tr>

<td></td><td><input name=submit type=submit value="<?php echo lang('submit')?>" class=smallbutton></td></table></form><p>

</table>


