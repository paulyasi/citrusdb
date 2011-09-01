<a href="<?php echo $this->url_prefix?>/index.php/customer">
[ <?php echo lang('undochanges');?> ]</a> &nbsp; 
<a href="<?php echo $this->url_prefix?>/index.php/support/edit">
[ <?php echo lang('checknotes')?> ]</a>
<table cellpadding=5 border=0 cellspacing=1 width=720>
<td bgcolor="#ccccdd"><b><?php echo lang('createdby');?></b></td>
<td bgcolor="#ddddee"><?php echo $this->user;?></td><tr>
<td bgcolor="#ccccdd"><b><?php echo lang('service');?></b></td>
<td bgcolor="#ddddee"><?php echo $user_services_id . " " . $service_description;?></td><tr>
<td bgcolor="#ccccdd"><b><?php echo lang('notify');?></b></td>
<td bgcolor="#ddddee">
<form style="margin-bottom:0;" action="index.php" name="form1" method=post>
<select name="notify">\n";
<option value="<?php echo $support_notify?>"><?php echo $support_notify?></option>\n";
<option value="nobody"><?php echo lang('nobody');?></option>\n";
<optgroup label="<?php echo lang('groups');?>">\n";
<?php
// print the list of groups
$query = "SELECT DISTINCT groupname FROM groups ORDER BY groupname";
$result = $this->db->query($query) or die ("query failed");

foreach ($result->result_array() as $myresult)
{
	$groupname = $myresult['groupname'];          
	print "<option>$groupname</option>\n";
}

// print a seperator
print "</optgroup><optgroup label=\"$l_users\">\n"; 


// print the list of users
$query = "SELECT username FROM user ORDER BY username";
$result = $this->db->query($query) or die ("query failed");

foreach ($result->result_array() as $myresult)
{
	$username = $myresult['username'];
	print "<option>$username</option>\n";
}

print "</optgroup></select>\n";

?>
</td><tr>
<td bgcolor="#ccccdd"><b><?php echo lang('status');?></b></td><td bgcolor="#ddddee">
<select name="status">
<option value=""></option>
<option value="not done" selected><?php echo lang('notdone');?></option>
<option value="pending"><?php echo lang('pending');?></option>
<option value="completed"><?php echo lang('completed');?></option>
</select>
</td><tr>
<td bgcolor="#ccccdd"><b><?php echo lang('description');?></b></td>
<td bgcolor="#ddddee">
<textarea name="description" rows=8 cols=50></textarea></td><tr>

<td bgcolor="ccccdd"><b><?php echo lang('reminderdate');?></b></td><td bgcolor="#ddddee">
<input name="reminderdate" type=text size=12>
<A HREF="#" onClick="cal.select(document.forms['form1'].reminderdate,'anchor1','yyyy-MM-dd'); return false;"NAME="anchor1" ID="anchor1" style="color:blue">[
<?php echo lang('select');?>]</A>&nbsp; 
</td>

<tr>
<td colspan=2 align=center>
<input type=hidden name=serviceid value=<?php echo $user_services_id?>>
<input type=hidden name=load value=support>
<input type=hidden name=type value=module>
<input type=hidden name=create value=on>
<input name=addnow type=submit value="<?php echo lang('add');?>" class=smallbutton>
</td>
</table>
</form>
