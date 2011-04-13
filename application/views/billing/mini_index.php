<table width=720 cellpadding=3><tr bgcolor="#dddddd">
<td><b><?php echo lang('organizationname')?></b></td><td><b><?php echo lang('type')?></b></td>
<td><b><?php echo lang('status')?></b></td><td><b><?php echo lang('newcharges')?></b></td>
<td><b><?php echo lang('tax')?></b></td><td><b><?php echo lang ('pastcharges')?></b></td>
<td><b><?php echo lang('total');?></b></td><tr>
<?php 

foreach ($record as $billing_record) {
	$billing_id = $billing_record['b_id'];
	$billing_type = $billing_record['t_name'];
	$billing_orgname = $billing_record['g_org_name'];
	$not_removed_id = $billing_record['not_removed_id'];
	$mystatus = $billing_record['mystatus'];
	$newcharges = $billing_record['newcharges'];
	$pastcharges = $billing_record['pastcharges'];
	$newtaxes = $billing_record['newtaxes'];
	$newtotal = $billing_record['newtotal'];

	// show active billing services that are authorized new or free in green
	// show active billing services not in good standing in red
	if ($billing_id == $not_removed_id) 
	{
    	if (($mystatus == lang('authorized'))
			OR ($mystatus == lang('new'))
			OR ($mystatus == lang('free'))
			OR ($mystatus == lang('pastdueexempt'))) 
		{
      		echo "<tr style=\"background-color: bdd;\">";
    	} 
    	else 
    	{
      		echo "<tr style=\"background-color: fbb;\">";
    	}
  	} 
  	else 
  	{
    	// show inactive billing services that are in not in good standing in red
    	// show inactive billing services in other status in grey
    	if (($mystatus == $l_pastdue)
			OR ($mystatus == $l_waiting)
			OR ($mystatus == $l_noticesent)
			OR ($mystatus == $l_turnedoff)
			OR ($mystatus == $l_declined)
			OR ($mystatus == $l_initialdecline)
			OR ($mystatus == $l_declined2x)) 
		{
      		echo "<tr style=\"background-color: fbb;\">";
    	} 
    	else 
    	{ 
      		// print in grey if all services are removed from that billing id
      		echo "<tr style=\"background-color: eee; color: aaa;\">";
    	}
  	}

  	$edit_billing_url = $this->ssl_url_prefix . "index.php/billing/edit/" . $billing_id;
}
?>
<td style="font-weight: bold;"><?=$billing_orgname?>&nbsp;
<a href="<?=$edit_billing_url?>"><?php echo lang('edit') . " " . $billing_id;?></a>
</td><td>$billing_type</td><td>$mystatus</td>
<td><?=$newcharges?></td><td><?=$newtaxes?></td><td><?=$pastcharges?></td>
<td><?=$newtotal?></td>
</table>
<p>
