<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('customersummary')?></h3>
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/reports/summary" 
METHOD="POST" name="form1">

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

<input type="SUBMIT" NAME="submit" value="<?php echo lang('submit')?>"><p>

<table cellpadding=2><td><b><?php echo lang('services')?></b></td>
<td><b>Frequency</b></td>
<td><b>Category</b></td>
<td><b>Customers</b></td>
<td><b>Service Cost</b></td>
<td><b>Monthly <?php echo lang('total')?></b></td><tr>

$newline = "$l_services,Frequency,Category,Customers,Service Cost,Monthly Total\n";
fwrite($handle, $newline); // write to the file	

// initialize the count of paid monthly services
$paidsubscriptions = 0;
$count_creditcard = 0;
$count_invoice = 0;
$count_einvoice = 0;
$count_prepay = 0;
$count_prepaycc = 0;
$total_customers = 0;
$total_service_cost = 0;
$total_monthly = 0;

/*--------------------------------------------------------------------*/
// Add services to the bill
/*--------------------------------------------------------------------*/
// join the user_services, billing, billing_types, and master_services 
// together to find what would be on upcoming bills
$query = "SELECT u.id u_id, u.account_number u_ac, 
	u.master_service_id u_msid, u.billing_id u_bid, 
	u.removed u_rem, u.usage_multiple u_usage, 
	b.next_billing_date b_next_billing_date, b.id b_id, 
	b.billing_type b_type, 
	t.id t_id, t.frequency t_freq, t.method t_method, m.service_description m_service_description, 
	m.id m_id, m.pricerate m_pricerate, m.frequency m_freq 
	FROM user_services u
	LEFT JOIN master_services m ON u.master_service_id = m.id
	LEFT JOIN billing b ON u.billing_id = b.id
	LEFT JOIN billing_types t ON b.billing_type = t.id
	WHERE b.organization_id = '$organization_id' 
	AND t.method <> 'free' AND u.removed <> 'y'";

	$result = $this->db->query($query) or die ("Services Query Failed");

	// initialize arrays to keep our results in
	// make hash/array of master service id and amount being charged
	// and another array to count how many of customers have that type of charge
	$price_array = array();
	$count_array = array();

	$i = 0; // count the billing services
while ($myresult = $result->FetchRow())
{
	$billing_id = $myresult['u_bid'];
	$user_services_id = $myresult['u_id'];
	$pricerate = $myresult['m_pricerate'];
	$usage_multiple = $myresult['u_usage'];
	$master_service_id = $myresult['m_id'];
	//$billing_freq = $myresult['t_freq'];

	/*
	   if ($billing_freq > 0) {
	   if ($service_freq > 0)
	   {
	   $billed_amount = ($billing_freq/$service_freq)
	 *($pricerate*$usage_multiple);
	 }
	 else
	 {
	// one time services
	$billed_amount = ($pricerate*$usage_multiple);
	} // end if
	}
	 */

	$billed_amount = ($pricerate*$usage_multiple);

	// round the tax to two decimal places
	$billed_amount = sprintf("%.2f", $billed_amount);

	//
	// Insert results into an array

	if (isset($price_array[$master_service_id])) {
		$price_array[$master_service_id] = $price_array[$master_service_id] + $billed_amount;
		$count_array[$master_service_id]++;
	} else {
		$price_array[$master_service_id] = $billed_amount;
		$count_array[$master_service_id] = 1;	    
	}    

} // end while


// print each item in the price and count arrays
foreach($price_array as $master_service_id_value => $total_billed) {
	$query = "SELECT ms.service_description, ms.pricerate, ms.category, ms.frequency FROM master_services ms ".
		"WHERE ms.id = '$master_service_id_value'";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$serviceresult = $DB->Execute($query) or die ("Services Query Failed");

	// count the number of taxes
	$count = $count_array[$master_service_id_value];

	while ($myserviceresult = $serviceresult->FetchRow()) {
		$service_description = $myserviceresult['service_description'];
		$rate = $myserviceresult['pricerate'];
		$frequency = $myserviceresult['frequency'];
		$category = $myserviceresult['category'];

		// add to the displayed paid subscription count total, do not count free or on time services as a subscription
		if (($rate > 0) AND ($frequency > 0)) {
			$paidsubscriptions = $paidsubscriptions + $count;
		}       

		// if frequency is greater than 1 divide the total amount by the frequency
		if ($frequency > 1) {
			$total_billed = $total_billed/$frequency;
		}

		echo "<td>$service_description</td><td>$frequency</td><td>$category</td><td>$count</td><td>$rate</td><td>$total_billed</td><tr>";
		$newline = "$service_description,$frequency,$category,$count,$rate,$total_billed\n";
		fwrite($handle, $newline); // write to the file

		// add totals
		$total_customers = sprintf("%.2f",$total_customers + $count);
		$total_service_cost = sprintf("%.2f",$total_service_cost + $rate);
		$total_monthly = sprintf("%.2f",$total_monthly + $total_billed);
	}
}


/*--------------------------------------------------------------------------*/
// calculate taxes for all taxed services at this time
// this part may take a long time
/*--------------------------------------------------------------------------*/

// initialize arrays to keep our results in
// make hash/array of tax rate id and number of customers being charged that tax rate
// and another array to count how many of those taxes are charged
$tax_array = array();
$count_array = array();

$query = "SELECT ts.id ts_id, ts.master_services_id ts_serviceid, ".
"ts.tax_rate_id ts_rateid, ms.id ms_id, ".
"ms.service_description ms_description, ms.pricerate ms_pricerate, ".
"ms.frequency ms_freq, tr.id tr_id, tr.description tr_description, ".
"tr.rate tr_rate, tr.if_field tr_if_field, tr.if_value tr_if_value, ".
"tr.percentage_or_fixed tr_percentage_or_fixed, ".
"us.master_service_id us_msid, us.billing_id us_bid, us.id us_id, ".
"us.removed us_removed, us.account_number us_account_number, ". 
"us.usage_multiple us_usage_multiple,  ".
"te.account_number te_account_number, te.tax_rate_id te_tax_rate_id, ".
"b.id b_id, b.billing_type b_billing_type, ".
"t.id t_id, t.frequency t_freq, t.method t_method ".
"FROM taxed_services ts ".
"LEFT JOIN user_services us ON ".
"us.master_service_id = ts.master_services_id ".
"LEFT JOIN master_services ms ON ms.id = ts.master_services_id ".
"LEFT JOIN tax_rates tr ON tr.id = ts.tax_rate_id ".
"LEFT JOIN tax_exempt te ON te.account_number = us.account_number ".
"AND te.tax_rate_id = tr.id ".
"LEFT JOIN billing b ON us.billing_id = b.id ".
"LEFT JOIN billing_types t ON b.billing_type = t.id ".
"WHERE b.organization_id = '$organization_id' ".
"AND us.removed <> 'y'";

$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$taxresult = $DB->Execute($query) or die ("Taxes Query Failed");

// count the number of taxes
$i = 0;

while ($mytaxresult = $taxresult->FetchRow()) {
	$billing_id = $mytaxresult['b_id'];
	$taxed_services_id = $mytaxresult['ts_id'];
	$user_services_id = $mytaxresult['us_id'];
	$service_freq = $mytaxresult['ms_freq'];
	$billing_freq = $mytaxresult['t_freq'];	
	$if_field = $mytaxresult['tr_if_field'];
	$if_value = $mytaxresult['tr_if_value'];
	$percentage_or_fixed = $mytaxresult['tr_percentage_or_fixed'];
	$my_account_number = $mytaxresult['us_account_number'];
	$usage_multiple = $mytaxresult['us_usage_multiple'];
	$pricerate = $mytaxresult['ms_pricerate'];
	$taxrate = $mytaxresult['tr_rate'];
	$tax_rate_id = $mytaxresult['tr_id'];
	$tax_exempt_rate_id = $mytaxresult['te_tax_rate_id'];

	// check that they are not exempt
	if ($tax_exempt_rate_id <> $tax_rate_id) {
		// check the if_field before adding to see if 
		// the tax applies to this customer
		if ($if_field <> '') {
			$ifquery = "SELECT $if_field FROM customer ".
				"WHERE account_number = '$my_account_number'";
			$DB->SetFetchMode(ADODB_FETCH_NUM);
			$ifresult = $DB->Execute($ifquery) or die ("Query Failed");	
			$myifresult = $ifresult->fields;
			$checkvalue = $myifresult[0];
		} else {
			$checkvalue = TRUE;
			$if_value = TRUE;
		}

		// check for any case, so lower them here
		$checkvalue = strtolower($checkvalue);
		$if_value = strtolower($if_value);

		if (($checkvalue == $if_value) AND ($billing_freq > 0)) {
			if ($percentage_or_fixed == 'percentage') {
				if ($service_freq > 0) {
					$servicecost = sprintf("%.2f",$taxrate * $pricerate);
					// removed freq from this calculation since it is just for a monthly snapshot
					$tax_amount = sprintf("%.2f",$servicecost * $usage_multiple); 
				} else {
					$servicecost = $pricerate * $usage_multiple;
					$tax_amount = $taxrate * $servicecost;
				}
			} else {
				// fixed fee amount does not depend on price or usage
				$tax_amount = $taxrate;
			}

			// round the tax to two decimal places
			$tax_amount = sprintf("%.2f", $tax_amount);

			//
			// Insert results into an array

			if (isset($tax_array[$taxed_services_id])) {
				$tax_array[$taxed_services_id] = $tax_array[$taxed_services_id] + $tax_amount;
				$count_array[$taxed_services_id]++;
			} else {
				$tax_array[$taxed_services_id] = $tax_amount;
				$count_array[$taxed_services_id] = 1;	    
			}

		} //endif if_field/billing_freq
	} // endif exempt
}

// print each item in the tax and count arrays
foreach($tax_array as $taxed_services_id_value => $total_taxed) {
	$query = "SELECT tr.description, tr.rate, ms.service_description, ms.category FROM tax_rates tr ".
		"LEFT JOIN taxed_services ts ON ts.tax_rate_id = tr.id ".
		"LEFT JOIN master_services ms ON ms.id = ts.master_services_id ".	
		"WHERE ts.id = '$taxed_services_id_value'";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$taxresult = $DB->Execute($query) or die ("Taxes Query Failed");

	// count the number of taxes
	$count = $count_array[$taxed_services_id_value];

	while ($mytaxresult = $taxresult->FetchRow()) {
		$description = $mytaxresult['description'];
		$service_description = $mytaxresult['service_description'];
		$rate = $mytaxresult['rate'];
		$category = $mytaxresult['category'];

		echo "<td>$description for $service_description</td><td></td><td>$category</td><td>$count</td><td>$rate</td><td>$total_taxed</td><tr>";
		$newline = "$description for $service_description,,$category,$count,$rate,$total_taxed\n";
		fwrite($handle, $newline); // write to the file

		// add totals
		$total_customers = sprintf("%.2f",$total_customers + $count);
		$total_service_cost = sprintf("%.2f",$total_service_cost + $rate);
		$total_monthly = sprintf("%.2f",$total_monthly + $total_taxed);

	}
}


// print the table footer
echo "<td style=\"border-top: 1px solid black; font-weight: bold;\">$l_total:</td>
<td style=\"border-top: 1px solid black; font-weight: bold;\">&nbsp;</td>
<td style=\"border-top: 1px solid black; font-weight: bold;\">&nbsp;</td>
<td style=\"border-top: 1px solid black; font-weight: bold;\">$total_customers</td>
<td style=\"border-top: 1px solid black; font-weight: bold;\">$total_service_cost</td>
<td style=\"border-top: 1px solid black; font-weight: bold;\">$total_monthly</td><tr>";

$newline = ",,,$total_customers,$total_service_cost,$total_monthly\n";
fwrite($handle, $newline); // write to the file

fclose($handle);

// print link to download the summary file
echo "<a href=\"index.php?load=tools/downloadfile&type=dl&filename=summary.csv\"><u class=\"bluelink\">$l_download summary.csv</u></a><p>";

echo "</table><p>

$l_paidsubscriptions: $paidsubscriptions
<p>";

// get the total services for each billing type
$query = "SELECT m.id m_id, m.service_description m_servicedescription, m.pricerate m_pricerate, 
	m.frequency m_frequency, m.organization_id m_organization_id, g.org_name g_org_name,  
	u.removed u_removed, u.master_service_id u_msid, count(bt.method) AS TotalNumber, 
	b.id b_id, b.billing_type b_billing_type, bt.id bt_id, bt.method bt_method 
	FROM user_services u
	LEFT JOIN master_services m ON u.master_service_id = m.id
	LEFT JOIN billing b ON b.id = u.billing_id 
	LEFT JOIN billing_types bt ON b.billing_type = bt.id
	LEFT JOIN general g ON m.organization_id = g.id
	WHERE u.removed <> 'y' AND bt.method <> 'free' AND b.organization_id = '$organization_id' AND m.pricerate > '0' 
	AND m.frequency > '0' GROUP BY bt.method ORDER BY TotalNumber";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	echo "<blockquote>";
	while ($myresult = $result->FetchRow()) {
		$count = $myresult['TotalNumber'];
		$billingmethod = $myresult['bt_method'];
		echo "$billingmethod: $count<br>\n";	
	}
echo "</blockquote>";


/*-----------------------------------------------*/
// get the number of services in each category
/*-----------------------------------------------*/
$query = "SELECT m.id m_id, m.service_description m_servicedescription, ".
"m.pricerate m_pricerate, m.category m_category, m.frequency m_frequency, ".
"m.organization_id m_organization_id, g.org_name g_org_name, ".
"u.removed u_removed, u.master_service_id u_msid, ".
"count(bt.method) AS TotalNumber, ".
"b.id b_id, b.billing_type b_billing_type, bt.id bt_id, bt.method bt_method ".
"FROM user_services u ".
"LEFT JOIN master_services m ON u.master_service_id = m.id ".
"LEFT JOIN billing b ON b.id = u.billing_id ".
"LEFT JOIN billing_types bt ON b.billing_type = bt.id ".
"LEFT JOIN general g ON m.organization_id = g.id ".
"WHERE u.removed <> 'y' AND b.organization_id = '$organization_id' ".
"AND m.frequency > '0' GROUP BY m.category ORDER BY TotalNumber";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
echo "<p><b>Service Category Totals: </b><br><i style=\"font-size: 8pt;\">total monthly services in each category, includes declined and turned off pending payment, does not include canceled services</i></p><blockquote>";
while ($myresult = $result->FetchRow()) {
	$count = $myresult['TotalNumber'];
	$category = $myresult['m_category'];
	echo "$category: $count<br>\n";	
}
echo "</blockquote>";



// get the number of customers
$query = "SELECT COUNT(*) FROM customer WHERE cancel_date is NULL";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
$numofcustomers = $myresult['COUNT(*)'];

print "<hr>$l_totalcustomers: $numofcustomers<p>";


// get the number of customers who are not free
$query = "SELECT COUNT(*) FROM customer c
LEFT JOIN billing b ON b.id = c.default_billing_id 
LEFT JOIN billing_types bt ON b.billing_type = bt.id
WHERE cancel_date is NULL AND bt.method <> 'free'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
$numofcustomers = $myresult['COUNT(*)'];

print "$l_totalpayingcustomers: $numofcustomers<p>";

?>
</body>
</html>







