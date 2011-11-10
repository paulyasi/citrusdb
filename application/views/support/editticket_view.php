<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>

<a href="<?php echo $this->url_prefix?>/index.php/support">[ <?php echo lang('undochanges')?> ]</a>
&nbsp; <a href="<?php echo $this->url_prefix?>/index.php/support/tickets">
[ <?php echo lang('checknotes')?> ]</a>".
<h3><?php echo lang('ticketnumber')." ".$ticket['id'];?></h3>
<form style="margin-bottom:0;" 
action="<?php echo $this->url_prefix?>/index.php/support/saveeditticket" 
name="form1" method=post>
<table cellpadding=5 border=0 cellspacing=1 width=720>
<td bgcolor="#ccccdd"><b><?php echo lang('createdby')?></b></td>
<td bgcolor="#ddddee"><?php echo $ticket['created_by']." ".$ticket['creation_date'];?></td>
<td bgcolor="#ccccdd"><b><?php echo lang('closed_by')?></b></td>
<td bgcolor="#ddddee"><?php echo $ticket['closed_by']." ".$ticket['closed_date'];?></td><tr>
<td bgcolor="#ccccdd"><b><?php echo lang('customer')?></b></td>
<td bgcolor="#ddddee">
<a href="<?php echo $this->url_prefix?>/index.php/view/account/<?php echo $ticket['accountnum']?>">
<?php echo $ticket['name']?></a>
&nbsp;&nbsp;(<?php echo $ticket['accountnum']?>)</td>

<td bgcolor="#ccccdd"><b><?php echo lang('service')?></b></td><td bgcolor="#ddddee">

<?php
// print a service id link if there is an associated service
if ($ticket['serviceid'] > 0) 
{
	echo "<a href=\"index.php/services/edit/".$ticket['serviceid']."\">".
		$ticket['serviceid']." ".$ticket['service_description']."</a>";
}
?>
&nbsp;&nbsp;<input type=text value="<?php echo $ticket['serviceid']?>" name="serviceid" size=10>
</td><tr>

<td bgcolor="#ccccdd"><b><?php echo lang('notify')?></b></td>
<td bgcolor="#ddddee">


<select name="notify">
<option selected value="<?php echo $ticket['notify']?>"><?php echo $ticket['notify']?></option>
<option value="nobody"><?php echo lang('nobody')?></option>
<optgroup label="<?php echo lang('groups')?>">

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

</td>
<td bgcolor="#ccccdd"><b><?php echo lang('status')?></b></td>
<td bgcolor="#ddddee">
<select name="status">
<option selected value="<?php echo $ticket['status']?>"><?php echo $ticket['status']?></option>
<option value="not done"><?php echo lang('notdone')?></option>
<option value="pending"><?php echo lang('pending')?></option>
<option value="completed"><?php echo lang('completed')?></option>
</select>
<input type=hidden name=oldstatus value="<?php echo $ticket['status']?>">
</td><tr>
<td bgcolor="#ccccdd"><b><?php echo lang('reminderdate')?></b></td>
<td bgcolor="#ddddee"><input type=text value="<?php echo $ticket['creation_date']?>" name="reminderdate">
<a href="#" onClick="cal.select(document.forms['form1'].reminderdate,'anchor1','yyyy-MM-dd'); return false;"NAME="anchor1" ID="anchor1" style="color:blue">[<?php echo lang('select')?>]</a></td>

<td bgcolor="#ccccdd"><b>$l_link</b></td><td bgcolor="#ddddee">
<a href="<?php echo $ticket['linkurl']?>"><?php echo $ticket['linkname']?></a></td><tr>

<td bgcolor="#ccccdd"><b><?php echo lang('description')?></b></td><td colspan=3 bgcolor="#ddddee">

<?php
if (($this->user == $ticket['created_by']) && ($ticket['status'] != 'completed') 
		&& ($ticket['status'] != 'pending')) 
{
	// let the user edit their own descriptions if not yet completed or pending
	echo "<textarea name=\"description\" rows=4 cols=70>".$ticket['description']."</textarea></td><tr>";
} 
else 
{
	// fix the description to print with line breaks here
	echo nl2br ($ticket['description']);
	echo "<input type=hidden name=\"description\" value=\"".$ticket['description']."\"></td><tr>";
}

foreach ($sub_history AS $mysubresult) 
{
	$sub_creation_date = $mysubresult['creation_date'];
	$sub_created_by = $mysubresult['created_by'];
	$sub_description = $mysubresult['description'];

	print "<td bgcolor=\"#ccccdd\"><b>$sub_created_by<br>$sub_creation_date</b></td><td colspan=3 bgcolor=\"#ddddee\">";
	echo nl2br($sub_description);
	echo "</td><tr>\n";
}
?>

<td bgcolor="#ccccdd"><b>Add Note:</b></td><td colspan=3 bgcolor="#ddddee">
<textarea name="addnote" rows=5 cols=70></textarea></td>
<tr>
<td colspan=4 align=center>
<input type=hidden name=id value=<?php echo $ticket['id']?>>
<input name=savechanges type=submit value="<?php echo lang('savechanges')?>" class=smallbutton>
</td>
</table>
</form>

