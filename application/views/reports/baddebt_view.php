<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('exemptreport')?>: 

<?php echo lang('bad_debt')?><p>
<table><tr style="font-weight: bold;">
<td><?php echo lang('accountnumber')?></td>
<td><?php echo lang('name')?></td>
<td><?php echo lang('company')?></td>
<td><?php echo lang('street')?></td><tr>

<?php
foreach ($baddebt AS $myresult) 
{
	$acctnum = $myresult['account_number'];     
	$name = $myresult['name'];
	$company = $myresult['company'];
	$street = $myresult['street'];
	echo "<td>$acctnum</td>".
		"<td>$name</td>".
		"<td>$company</td>".
		"<td>$street</td><tr>";
}     
echo "</table>";
?>

