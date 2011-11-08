<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('modulepermissions')." ".$module?></h3>
<p>
<form name="form1" method="post" action="<?php echo $this->url_prefix?>/index.php/tools/admin/savemodulepermissions">
<input name="module" type="hidden" id="module" value="<?php echo $module?>">
<p><?php echo lang('users')?>/<?php echo lang('groups')?>:
<select name="usergroup">
<?php
// print the list of groups and users to choose from
// print a seperator
print "<optgroup label=\"".lang('groups')."\">\n"; 

foreach ($groupslist as $myresult)
{
	$groupname = $myresult['groupname'];          
	print "<option>$groupname</option>\n";
}

// print a seperator
print "</optgroup><optgroup label=\"".lang('users')."\">\n"; 

foreach ($userslist as $myresult)
{
	$username = $myresult['username'];
	print "<option>$username</option>\n";
}

print "</optgroup></select>\n";

?>
</select>
</p>
<p><?php echo lang('permission')?>:
<select name="permission">
<option value="r"><?php echo lang('view')?></option>
<option value="c"><?php echo lang('create')?></option>
<option value="m"><?php echo lang('modify')?></option>
<option value="d"><?php echo lang('remove')?></option>
<option value="f"><?php echo lang('fullcontrol')?></option>
</select>
</p>
<p>
<input name="padd" type="submit" id="padd" value="<?php echo lang('add')?>">
</p>
</form>
</table>
</body>
</html>
