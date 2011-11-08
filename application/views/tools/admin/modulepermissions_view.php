<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>

<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('module')?> ( <?php echo $module?> ) <?php echo lang('permission')?></h3>
[ <a href="<?php echo $this->url_prefix?>/index.php/tools/admin/addmodulepermissions/<?php echo $module?>">
<?php echo lang('addpermission')?></a> ]
<p>

<table cellpadding=5 cellspacing=1><tr bgcolor="#eeeeee"><td>
<b><?php echo lang('modulename')?></b></td>
<td><b><?php echo lang('permission')?></b></td><td>
<b><?php echo lang('user')?>/<?php echo lang('groups')?></b></td>
<td><b><?php echo lang('remove')?></b></td></tr>

<?php
foreach ($permissions AS $myresult)
{
  $pid = $myresult['id'];
  $permission = $myresult['permission'];
  $user = $myresult['user'];

  if ($permission == "r") { $permission=lang('view'); }
  if ($permission == "c") { $permission=lang('create'); }
  if ($permission == "m") { $permission=lang('modify'); }
  if ($permission == "d") { $permission=lang('remove'); }
  if ($permission == "f") { $permission=lang('fullcontrol'); }

  print "<tr bgcolor=\"#eeeeee\"><td>$module</td><td>$permission</td><td>$user".
    "</td><td><a href=\"$this->url_prefix/index.php/tools/admin/".
	"removemodulepermissions/$pid/$module\">[ ".lang('remove')." ]</a></td></tr>";
}

?>
</table>
</body>
</html>
