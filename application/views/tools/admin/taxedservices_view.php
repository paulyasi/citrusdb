<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<table cellpadding=5 cellspacing=1><tr bgcolor="#eeeeee">
<td><b><?php echo lang('id')?></b></td> 
<td><b><?php echo lang('serviceid')?></b></td> 
<td><b><?php echo lang('service')?> <?php echo lang('description')?></b></td> 
<td><b><?php echo lang('taxrateid')?></b></td> 
<td><b><?php echo lang('tax')?> <?php echo lang('description')?></b></td> 
<td></td> </tr>

<?php
foreach ($taxed_services AS $myresult)
{
	$id = $myresult['ts_id'];
	$serviceid = $myresult['ts_serviceid'];
	$rateid = $myresult['ts_rateid'];
	$service_description = $myresult['ms_description'];
	$rate_description = $myresult['tr_description'];
	print "<tr bgcolor=\"#eeeeee\"><td>$id</td><td>$serviceid</td> ".
		"<td>$service_description</td><td>$rateid</td> ".
		"<td>$rate_description</td>";
	print "<td><a href=\"$this->url_prefix/index.php/tools/admin/deletetaxedservice/$id\">".
		lang('delete')."</a></td></tr>\n";
}
?>

</table><p>
<b><?php echo lang('add')?>:</b><br>
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/admin/addtaxedservice" METHOD="POST">
<?php echo lang('linkservice')?>: 
<select name=linkedservice>

<?php
foreach ($master_services AS $myresult)
{
	$id = $myresult['id'];
	$description = $myresult['service_description'];
	print "<option value=\"$id\">$description</option>\n";
}
?>

</select>
<?php echo lang('totaxrate')?>: <select name=torate>

<?php
foreach ($tax_rates AS $myresult)
{
	$id = $myresult['id'];
	$description = $myresult['description'];
	$rate = $myresult['rate'];
	print "<option value=\"$id\">$description ($rate)</option>\n";
}
?>

</select>
<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo lang('add')?>">
</FORM>
<p>

