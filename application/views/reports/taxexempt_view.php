<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('exemptreport')?>: 

<?php echo lang('taxexempt')?><p>
<table><tr style="font-weight: bold;">
<td><?php echo lang('accountnumber')?></td>
<td><?php echo lang('description')?></td>
<td><?php echo lang('name')?></td>
<td><?php echo lang('company')?></td>
<td><?php echo lang('taxexemptid')?></td>
<td><?php echo lang('expirationdate')?></td><tr>
<?
foreach ($taxexempt AS $myresult) 
{
	$description = $myresult['description'];
	$acctnum = $myresult['account_number'];       
	$name = $myresult['name'];
	$company = $myresult['company'];
	$customertaxid = $myresult['customer_tax_id'];
	$customertaxexpdate = $myresult['expdate'];
	echo "<td>$acctnum</td>".
		"<td>$description</td>".
		"<td>$name</td>".
		"<td>$company</td>".
		"<td>$customertaxid</td>".
		"<td>$customertaxexpdate</td><tr>";
}

echo "</table>";

?>
