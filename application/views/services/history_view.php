<?php   
?>
<h3><?php echo lang('service') . " " . lang('history');?></h3>
<a href="index.php/services">[ <?php echo lang('back');?> ]</a>
<table cellpadding=0 border=0 cellspacing=0 width=720><td valign=top>		
	<table cellpadding=5 cellspacing=1 border=0 width=720>
	<td bgcolor="#ccccdd"><b><?php echo lang('id');?></b></td>
	<td bgcolor="#ccccdd"><b><?php echo lang('service');?></b></td>
	<td bgcolor="#ccccdd"><b><?php echo lang('details');?></b></td>
	<td bgcolor="#ccccdd"><b><?php echo lang('started');?></b></td>
	<td bgcolor="#ccccdd"><b><?php echo lang('ended');?></b></td>
	<td bgcolor="#ccccdd"><b><?php echo lang('removaldate');?></b></td>
	<td bgcolor="#ccccdd"><b><?php echo lang('billingid');?></b></td>
	<td bgcolor="#ccccdd"><b><?php echo lang('price');?></b></td>
        <td></td>
	<td></td>
<?php 
foreach ($services->result_array() as $myresult)
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
	$start_datetime = $myresult['start_datetime'];
	$end_datetime = $myresult['end_datetime'];
	$removal_date = $myresult['removal_date'];
	$billing_id = $myresult['billing_id'];
	$pricerate = $myresult['pricerate'];
	$usage_multiple = $myresult['usage_multiple'];
	$frequency = $myresult['frequency'];
	$removed = $myresult['removed'];
	$service_description = $myresult['service_description']; 

	// get the billing frequency and organization from billing model
	$freqresult = $this->billing_model->frequency_and_organization($billing_id);

	$billing_freq = $freqresult->frequency;
	$billing_organization_id = $freqresult->organization_id;
	$org_name = $freqresult->org_name;
	
	// multiply the pricerate and the usage_multiple to get the price to show
	$totalprice = $pricerate * $usage_multiple;

	//print "\n<tr onMouseOver='h(this);' onmouseout='deh(this);' onmousedown='window.location.href=\"index.php?load=services&type=module&edit=on&userserviceid=$id&servicedescription=$service_description&optionstable=$options_table&editbutton=Edit\";' bgcolor=\"#ddddee\">";
	
	print "<tr bgcolor=\"#ddddee\">\n
	<td>$id</td>
	<td>$service_description</td>
	<td>$optiondetails</td>
	<td>$start_datetime</td>
	<td>$end_datetime</td>
	<td><a href=\"index.php?load=services&type=module&history=on&serviceid=$id&editremovaldate=on&removaldate=$removal_date\">$removal_date</a></td>
	<td>$billing_id</td>
	<td>$totalprice</td>
<td>
<form style=\"margin-bottom:0;\" action=\"index.php\" method=post>
<input type=hidden name=load value=services>
<input type=hidden name=type value=module>
<input type=hidden name=edit value=on>
<input type=hidden name=userserviceid value=\"$id\">
   <input type=hidden name=servicedescription value=\"$service_description\">
<input type=hidden name=optionstable value=\"$options_table\">
<input name=editbutton type=submit value=\"".lang('edit')."\" class=smallbutton></form>
</td>
	</td></tr>";
} 
?>
</table>
