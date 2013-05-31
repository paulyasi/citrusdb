<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('linkservices')?></h3>

<p><b><?php echo lang('addnewlink')?></b><br>
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/admin/savelinkservices" METHOD="POST">

<?php echo lang('from')?>: <select name=linkfrom>

<?php
foreach ($master_services AS $myresult)
{
	$id = $myresult['id'];
	$description = $myresult['service_description'];
	print "<option value=\"$id\">$description</option>\n";
}
?>

</select>
<p><?php echo lang('to')?>: <select name=linkto>

<?php
foreach ($master_services AS $myresult)
{
	$id = $myresult['id'];
	$description = $myresult['service_description'];
	print "<option value=\"$id\">$description</option>\n";
}
?>

</select>&nbsp;

<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo lang('add')?>">
</FORM><p>


<table cellpadding=5 cellspacing=1><tr bgcolor="#eeeeee">
<td><b><?php echo lang('from')?></b></td>
<td><b><?php echo lang('to')?></b></td></tr>

<?php
foreach ($linkedservices AS $myresult)
{
	$fromid = $myresult['mfrom_id'];
	$fromdesc = $myresult['mfrom_description'];
	$toid = $myresult['mto_id'];
	$todesc = $myresult['mto_description'];
	print "<tr bgcolor=\"#eeeeee\"><td>$fromdesc</td><td>$todesc</td><td><a href=\"$this->url_prefix/index.php/tools/admin/removelinkservices/$fromid/$toid\">".lang('unlink')."</a></td></tr>\n";
}
?>

</table><p>

<p>
</body>
</html>
