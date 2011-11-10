<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('taxes')?></h3>

[ <a href="<?php echo $this->url_prefix?>/index.php/tools/admin/taxes"><?php echo lang('taxrates')?></a> ] 
[ <a href="<?php echo $this->url_prefix?>/index.php/tools/admin/taxedservices"><?php echo lang('taxedservices')?></a> ]
	
<p><b><?php echo lang('taxrates')?></b><p>


<table cellpadding=5 cellspacing=1><tr bgcolor="#eeeeee">
<td><b><?php echo lang('id')?></b></td> 
<td><b><?php echo lang('description')?></b></td> 
<td><b><?php echo lang('rate')?></b></td> 
<td><b><?php echo lang('iffield')?></b></td>
<td><b><?php echo lang('ifvalue')?></b></td>
<td><b><?php echo lang('percentage_or_fixed')?></b></td> 
<td></td>
</tr>

<?php
foreach ($tax_rates AS $myresult)
{
	$id = $myresult['id'];
	$desc = $myresult['description'];
	$rate = $myresult['rate'];
	$if_field = $myresult['if_field'];
	$if_value = $myresult['if_value'];
	$percentage_or_fixed = $myresult['percentage_or_fixed'];
	print "<tr bgcolor=\"#eeeeee\"><td>$id</td><td>$desc</td><td>$rate</td>".
		"<td>$if_field</td><td>$if_value</td><td>$percentage_or_fixed</td>";
	print "<td><a href=\"$this->url_prefix/index.php/tools/admin/deletetaxrate/$id\">".
		lang('delete')."</a></td></tr>\n";
}
?>

</table><p>
<b><?php echo lang('add')?>:</b><br>
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/admin/addtaxrate" METHOD="POST">
<?php echo lang('description')?>: <input type=text name="description"><br>
<?php echo lang('rate')?>: <input type=text name="rate"><br>
<?php echo lang('iffield')?> <input type=text name="if_field"><br>
<?php echo lang('ifvalue')?> <input type=text name="if_value"><br>
<?php echo lang('percentage_or_fixed')?>
<select name="percentage_or_fixed">
<option value="percentage">percentage</option>
<option value="fixed">fixed</option>
</select>
<br>
<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo lang('add')?>">
</FORM>
<p>
</body>
</html>
