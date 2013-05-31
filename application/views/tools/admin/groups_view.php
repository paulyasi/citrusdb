<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('groups')?></h3>

[ <a href="<?php echo $this->url_prefix?>/index.php/tools/admin/addgroup"><?php echo lang('add')?></a> ]

<p><table cellpadding=5 cellspacing=1><tr bgcolor="#eeeeee">
<td><b><?php echo lang('groupname')?></b></td>
<td><b><?php echo lang('membername')?></b></td>
<td></td><tr bgcolor="#eeeeee">

<?php
foreach ($groups AS $g)
{
	print "<td>".$g['groupname']."</td><td>".$g['groupmember']."</td>".
		"<td><a href=\"$this->url_prefix/index.php/tools/admin/deletegroup/".$g['id']."\">".
		lang('delete')."</a></td><tr bgcolor=\"#eeeeee\">\n";
}
?>
</table>
</body>
</html>
