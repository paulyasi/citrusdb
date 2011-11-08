<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('services')?></h3>

<a href="<?php echo $this->url_prefix?>/index.php/tools/admin/addnewservice"><?php echo lang('addnewservice')?></a> | 
<a href="<?php echo $this->url_prefix?>/index.php/tools/admin/linkservices"><?php echo lang('linkservices')?></a> | 
<a href="<?php echo $this->url_prefix?>/index.php/tools/admin/optionstables"><?php echo lang('optionstables')?></a> | 
<a href="<?php echo $this->url_prefix?>/index.php/tools/admin/servicetaxes"><?php echo lang('taxes')?></a> | 
<a href="<?php echo $this->url_prefix?>/index.php/tools/admin/fieldassets"><?php echo lang('editfieldassets')?></a>

<p><table cellpadding=5 cellspacing=1><tr bgcolor="#eeeeee">
<td><b><?php echo lang('id')?></b></td>
<td><b><?php echo lang('description')?></b></td>
<td><b><?php echo lang('price')?></b></td>
<td><b><?php echo lang('frequency')?></b></td>
<td><b><?php echo lang('category')?></b></td>
<td><b><?php echo lang('activatenotify')?></b></td>
<td><b><?php echo lang('shutoffnotify')?></b></td>
<td><b><?php echo lang('modifynotify')?></b></td>
<td><b><?php echo lang('supportnotify')?></b></td>
<td></td><tr bgcolor="#eeeeee">

<?php
$previouscategory = "";
foreach ($masterservices AS $myresult)
{
	$id = $myresult['id'];
	$description = $myresult['service_description'];
	$pricerate = $myresult['pricerate'];
	$frequency = $myresult['frequency'];
	$category = $myresult['category'];
	$activate_notify = $myresult['activate_notify'];
	$shutoff_notify = $myresult['shutoff_notify'];
	$modify_notify = $myresult['modify_notify'];
	$support_notify = $myresult['support_notify'];

	if($category <> $previouscategory) {
		print "<tr bgcolor=\"#dddddd\"><td colspan=10><b>$category</b></td></tr>\n";
		$previouscategory = $category;
	}

	print "<tr bgcolor=\"#eeeeee\"><td>$id</td>".
		"<td>$description</td><td>$pricerate</td>".
		"<td>$frequency</td><td>$category</td>".
		"<td>$activate_notify</td><td>$shutoff_notify</td>".
		"<td>$modify_notify</td><td>$support_notify</td>".
		"<td><a href=\"$this->url_prefix/index.php/tools/admin/editservice/$id\">".
		lang('edit')."</a></td><tr bgcolor=\"#eeeeee\">\n";
}
?>
</table>
</body>
</html>
