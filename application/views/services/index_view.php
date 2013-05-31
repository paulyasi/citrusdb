<table cellpadding=0 border=0 cellspacing=0 width=720><td valign=top>
<table cellpadding=3 cellspacing=1 border=0 width=720>
<td bgcolor="#ccccdd"><b><?php echo lang('id')?></b></td>
<td bgcolor="#ccccdd"><b><?php echo lang('service')?></b></td>
<td bgcolor="#ccccdd"><b><?php echo lang('detail1')?></b></td>
<td bgcolor="#ccccdd"><b><?php echo lang('detail2')?></b></td>
<td bgcolor="#ccccdd"><b><?php echo lang('price')?></b></td>
<td bgcolor="#ccccdd"><b><?php echo lang('freq')?></b></td>
<td bgcolor="#ccccdd"><b><?php echo lang('billingid')?></b></td>
<td bgcolor="#ccccdd"><b><?php echo lang('salesperson')?></b></td><td></td>
   

<?php 
foreach ($services as $myresult)
{
	// select the options_table to get data for the details column
	 
	$options_table = $myresult['options_table'];
	$id = $myresult['id'];
	
	if ($options_table <> '') {
		// get the data from the options table and put into variables
		$myoptions = $this->service_model->options_attributes($id, $options_table);
		//echo "$myoptions->username";
		if (count($myoptions) >= 3) {
			$optiondetails = $myoptions[2];
		} else {
			$optiondetails = '';
		}
		if (count($myoptions) >= 4) {
			$optiondetails2 = $myoptions[3];
		} else {
			$optiondetails2 = '';
		}
	} else {
		$optiondetails = '';
		$optiondetails2 = '';
	}
	
	$master_service_id = $myresult['master_service_id'];
	$service_organization_id = $myresult['master_organization_id'];
	$start_datetime = $myresult['start_datetime'];
	$billing_id = $myresult['billing_id'];
	$pricerate = $myresult['pricerate'];
	$usage_multiple = $myresult['usage_multiple'];
	$frequency = $myresult['frequency'];
	$salesperson = $myresult['salesperson'];
	$service_description = $myresult['service_description'];
	$support_notify = $myresult['support_notify'];
	
	// get the billing frequency and organization from billing model
	$freqresult = $this->billing_model->frequency_and_organization($billing_id);

	$billing_freq = $freqresult->frequency;
	$billing_organization_id = $freqresult->organization_id;
	$org_name = $freqresult->org_name;
	
	// multiply the pricerate and the usage_multiple to get the price to show
	$totalprice = sprintf("%.2f",$pricerate * $usage_multiple);

echo "<tr onMouseOver='h(this);' onmouseout='deh(this);' onmouseup='window.location.href=\"services/edit/$id\";' bgcolor=\"#ddddee\">";
?>
	<td><?php echo $id?></td>
	<td><?php echo $service_description?></td>
	<td><?php echo $optiondetails?></td>
	<td><?php echo $optiondetails2?></td>
	<td><?php echo $totalprice?></td>
	<td><?php echo $frequency?></td>
	<td><?php echo $billing_id?> (<?php echo $org_name?>)</td>
	<td><?php echo $salesperson?></td><td>
<?php
	if ($frequency > $billing_freq) {
		print "<b>" . lang('fixbillingfrequencyerror') . "</b>";
	};
	if ($service_organization_id <> $billing_organization_id) {
		print "<b>" . lang('orgmismatch') . "</b>";
	}
	print "<form style=\"margin-bottom:0;\" action=\"services/edit/$id\" method=get>";
	print "<input type=submit value=\"". lang('edit') . "\" ".
       "class=smallbutton></form>";

	print "<form style=\"margin-bottom:0;\" action=\"".$this->url_prefix."/index.php/support\" method=post>";
	print "<input type=hidden name=serviceid value=\"$id\">";
	if ($support_notify) {
		print "<input name=openticket type=submit value=\"" . lang('notify') . " $support_notify\" ".
	 "class=smallbutton></form></td></tr>";  	
	} else {
		print "<input name=openticket type=submit value=\"". lang('openticket') ."\" ".
	 "class=smallbutton></form></td></tr>";     
	}

	// check for taxes for this service
	$mytaxoutput = $this->service_model->checktaxes($id);

	foreach ($mytaxoutput AS $t)
	{
		if ($t['exempt'] == FALSE)
		{
			print "<tr><td></td>".
				"<td bgcolor=\"#eeeeff\" style=\"font-size: 8pt;\" ".
				"colspan=3>".$t['tax_description']."</td>".
				"<td bgcolor=\"#eeeeff\"  style=\"font-size: 8pt;\" ".
				"colspan=4>".$t['tax_amount']."</td>".
				"<td bgcolor=\"#eeeeff\" style=\"font-size: 8pt;\">".
				"<form style=\"margin-bottom:0;\" ".
				"action=\"$this->url_prefix/index.php/services/taxexempt\" method=post>".
				"<input type=hidden name=taxrateid value=\"".$t['tax_rate_id']."\">".
				"<input name=exempt type=submit value=\"".lang('exempt')."\" ".
				"class=smallbutton></form></td></tr>";
		} else {
			// print the exempt tax
			print "<tr style=\"font-size: 9pt;\"><td></td>".
				"<td bgcolor=\"#eeeeff\" style=\"font-size: 8pt;\" ".
				"colspan=3>".$t['tax_description']."</td>".
				"<td bgcolor=\"#eeeeff\" style=\"font-size: 8pt;\" ".
				"colspan=4>".lang('exempt').": ".$t['customer_tax_id'].
				" ".$t['customer_tax_id_expdate']."</td>".
				"<td bgcolor=\"#eeeeff\" style=\"font-size: 8pt;\">".
				"<form style=\"margin-bottom:0;\" ".
				"action=\"$this->url_prefix/index.php/services/nottaxexempt\" method=post>".
				"<input type=hidden name=taxrateid value=\"".$t['tax_rate_id']."\">".
				"<input name=notexempt type=submit value=\"".lang('notexempt')."\" ".
				"class=smallbutton></form></td></tr>";
		} // end if exempt tax
	}
	
	//echo $mytaxoutput;

}

print "</table></td></table></form>";




?>
