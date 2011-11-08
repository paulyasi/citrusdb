<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('editbillingtypes')?></h3>

<table cellpadding=5 cellspacing=1><tr bgcolor="#eeeeee">
<td><b><?php echo lang('id')?></b></td>
<td><b><?php echo lang('name')?></b></td>
<td><b><?php echo lang('frequency')?></b></td>
<td><b><?php echo lang('method')?></b></td>
<td></td>
</tr>
<?php
foreach ($billingtypes AS $myresult)
{
	$id = $myresult['id'];
	$name = $myresult['name'];
	$frequency = $myresult['frequency'];
	$method = $myresult['method'];
	print "<tr bgcolor=\"#eeeeee\"><td>$id</td><td>$name</td><td>$frequency</td>".
		"<td>$method</td><td><a href=\"$this->url_prefix/index.php/tools/admin/removebillingtype/$id\">".
		lang('remove')."</a></td></tr>\n";
}
?>
</table><p>
<b><?php echo lang('add')?>:</b><br>
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/admin/addbillingtype" METHOD="POST">
<table>
<td><?php echo lang('name')?>:</td><td><input name="name" type=text></td><tr>
<td><?php echo lang('frequency')?>:</td><td><input name="frequency" type=text></td><tr>
<td><?php echo lang('method')?>:</td><td><select name="method">
<option value="creditcard">creditcard</option>
<option value="invoice">invoice</option>
<option value="einvoice">einvoice</option>
<option value="prepay">prepay</option>
<option value="prepaycc">prepaycc</option>
<option value="free">free</option>
</select></td><tr>
</table>
<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo lang('submit')?>">
</FORM>
<p>
<!--
A frequency of zero is a one time charge, all other frequency are in number of months between recurring charges, 1 = monthly, 2 = 
bi-monthly, etc.
-->
</body>
</html>
