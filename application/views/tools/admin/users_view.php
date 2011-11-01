<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('users')?></h3>
[ <a href="<?php echo $this->url_prefix?>/index.php/tools/admin/newuser"><?php echo lang('addnewuser')?></a> ]


<p><table cellpadding=5 cellspacing=1>
<tr bgcolor="#eeeeee">
<td><b><?php echo lang('username')?></b></td>
<td><b><?php echo lang('name')?></b></td>
<td><?php echo lang('admin')?></td>
<td><?php echo lang('manager')?></td>
<td><?php echo lang('email')?></td>
<td><?php echo lang('screenname')?></td>
<td></td><tr bgcolor="#eeeeee">

<?php
foreach ($users AS $user)
{
	print "<td>".$user['username']."</td><td>".$user['real_name']."</td><td>".$user['admin']."</td>".
		"<td>".$user['manager']."</td><td>".$user['email']."</td><td>".$user['screenname']."</td>
		<td><a href=\"$this->url_prefix/index.php/tools/admin/edituser/".$user['id']."\">".lang('edit')."</a></td>
		<td><a href=\"$this->url_prefix/index.php/tools/admin/deleteuser/".$user['id']."\">".lang('delete')."</a></td>
		<tr bgcolor=\"#eeeeee\">\n";
}
?>
</table>
</body>
</html>
