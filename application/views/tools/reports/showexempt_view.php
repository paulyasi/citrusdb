<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('exemptreport')?>: 

<?php

if ($exempttype == "pastdueexempt") 
{
	echo "$l_pastdueexempt<p>";
	echo "<table><tr style=\"font-weight: bold;\">".
		"<td>$l_accountnumber</td><td>$l_name</td><td>$l_company</td><td>$l_street</td><tr>";

	$query = "SELECT * FROM billing WHERE pastdue_exempt = 'y'";
	$result = $this->db->query($query) or die ("$l_queryfailed");

	foreach ($result->result_array() AS $myresult) 
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

} 
elseif ($exempttype == "baddebt") 
{
	echo "$l_bad_debt<p>";
	echo "<table><tr style=\"font-weight: bold;\">".
		"<td>$l_accountnumber</td><td>$l_name</td><td>$l_company</td><td>$l_street</td><tr>";

	$query = "SELECT * FROM billing WHERE pastdue_exempt = 'bad_debt'";
	$result = $this->db->query($query) or die ("$l_queryfailed");

	foreach ($result->result_array() AS $myresult) 
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


} 
elseif ($exempttype == "taxexempt") 
{
	echo "$l_taxexempt<p>";
	echo "<table><tr style=\"font-weight: bold;\">".
		"<td>$l_accountnumber</td>".
		"<td>$l_description</td><td>$l_name</td><td>$l_company</td>".
		"<td>$l_taxexemptid</td><td>$l_expirationdate</td><tr>";

	$query = "SELECT tr.description, c.account_number, c.name, c.company, ".
		"te.customer_tax_id, ".
		"te.expdate FROM tax_exempt te ".
		"LEFT JOIN customer c ON c.account_number = te.account_number ".
		"LEFT JOIN tax_rates tr ON tr.id = tax_rate_id";
	$result = $this->db->query($query) or die ("$l_queryfailed");

	foreach ($result->result_array() AS $myresult) 
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

}

?>
</body>
</html>
