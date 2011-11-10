<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('fieldassets')?></h3>


<table cellpadding=5 cellspacing=1><tr bgcolor="#eeeeee">
<td><b><?php echo lang('id')?></b></td> 
<td><b><?php echo lang('description')?></b></td> 
<td><b><?php echo lang('status')?></b></td> 
<td><b><?php echo lang('weight')?></b></td>
<td><b><?php echo lang('category')?></b></td>
<td><b><?php echo lang('changestatus')?></b></td></tr>

<?php
foreach ($master_field_assets AS $myresult) 
{
	$id = $myresult['id'];
	$desc = $myresult['description'];
	$status = $myresult['status'];
	$weight = $myresult['weight'];
	$category = $myresult['category'];
	print "<tr bgcolor=\"#eeeeee\"><td>$id</td><td>$desc</td><td>$status</td><td>$weight</td><td>$category</td>";
	print "<td><a href=\"$this->url_prefix/index.php/tools/admin/changefieldassetstatus/$id/old\">old</a> | ".
		"<a href=\"$this->url_prefix/index.php/tools/admin/changefieldassetstatus/$id/current\">current</a></td></tr>\n";
}
?>

</table><p>
<b><?php echo lang('add')?>:</b><br>
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/admin/addfieldasset" METHOD="POST">
<table>
<td><?php echo lang('description')?>: </td><td><input type=text name="description"></td><tr>
<td><?php echo lang('status')?>: </td><td>
<label><input type=radio name="status" value="current">Current</label> 
<label><input type=radio name="status" value="old">Old</label> 
</td><tr>
<td><?php echo lang('weight')?>: </td><td><input type=text name="weight"></td><tr>
<td><?php echo lang('category')?>: </td><td><select name="category">";

<?php
foreach ($service_categories AS $myresult) 
{
	$category = $myresult['category'];

	echo "<option value=\"$category\">$category</option>";

}
?>

</select></td><tr>
<td></td><td>
<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo lang('add')?>">
</td></table>
</FORM>
<p>

</body>
</html>
