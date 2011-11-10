<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>

<a href="<?php echo $this->url_prefix?>/index.php/support">[ <?php echo lang('undochanges')?> ]</a>
&nbsp; <a href="<?php echo $this->url_prefix?>/index.php/support/tickets">
[ $l_checknotes ]</a>".
<h3>$l_ticketnumber $id</h3>".
<form style="margin-bottom:0;" action="<?php echo $this->url_prefix?>/index.php/support/saveeditticket" 
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
	echo "<a href=\"index.php/services/edit/$ticket['serviceid']\">".
		"$ticket['serviceid'] $ticket['service_description']</a>";
}
?>
&nbsp;&nbsp;<input type=text value="<?php echo $ticket['serviceid']?>" name="serviceid" size=10>
</td><tr>

<td bgcolor="#ccccdd"><b><?php echo lang('notify')?></b></td>
td bgcolor="#ddddee">


<select name="notify">
<option selected value="<?php echo $ticket['notify']?>"><?php echo $ticket['notify']?></option>
<option value="nobody"><?php echo lang('nobody')?></option>
<optgroup label="<?php echo lang('groups')?>">

// print the list of groups
$query = "SELECT DISTINCT groupname FROM groups ";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");

<?php   
while ($myresult = $result->FetchRow()) {
	$groupname = $myresult['groupname'];          
	print "<option>$groupname</option>\n";
}

// print a seperator
print "</optgroup><optgroup label=\"$l_users\">\n"; 


// print the list of users
$query = "SELECT username FROM user ORDER BY username";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("users list $l_queryfailed");

<?php   
while ($myresult = $result->FetchRow()) {
	$username = $myresult['username'];
	print "<option>$username</option>\n";
}

print "</optgroup></select>\n";


echo "
</td>
<td bgcolor=\"#ccccdd\"><b>$l_status</b></td><td bgcolor=\"#ddddee\">
<select name=\"status\">
<option selected value=\"$status\">$status</option>
<option value=\"not done\">$l_notdone</option>
<option value=\"pending\">$l_pending</option>
<option value=\"completed\">$l_completed</option>
</select>
<input type=hidden name=oldstatus value=\"$status\">
</td><tr>
<td bgcolor=\"#ccccdd\"><b>$l_reminderdate</b></td>
<td bgcolor=\"#ddddee\"><input type=text value=\"$creation_date\" name=\"reminderdate\">
<a href=\"#\" onClick=\"cal.select(document.forms['form1'].reminderdate,'anchor1','yyyy-MM-dd'); return false;\"NAME=\"anchor1\" ID=\"anchor1\" style=\"color:blue\">[$l_select]</a></td>

<td bgcolor=\"#ccccdd\"><b>$l_link</b></td><td bgcolor=\"#ddddee\">
<a href=\"$linkurl\">$linkname</a></td><tr>

<td bgcolor=\"#ccccdd\"><b>$l_description</b></td><td colspan=3 bgcolor=\"#ddddee\">";
if (($user == $created_by) && ($status != 'completed') && ($status != 'pending')) {
	// let the user edit their own descriptions if not yet completed or pending
	echo "<textarea name=\"description\" rows=4 cols=70>$description</textarea></td><tr>";
} else {
	// fix the description to print with line breaks here
	echo nl2br ($description);
	echo "<input type=hidden name=\"description\" value=\"$description\"></td><tr>";
}

// print the current notes attached to this item
$query = "SELECT * FROM sub_history WHERE customer_history_id = $id";
$subresult = $DB->Execute($query) or die ("sub_history $l_queryfailed");

<?php   
while ($mysubresult = $subresult->FetchRow()) {
	$sub_creation_date = $mysubresult['creation_date'];
	$sub_created_by = $mysubresult['created_by'];
	$sub_description = $mysubresult['description'];

	print "<td bgcolor=\"#ccccdd\"><b>$sub_created_by<br>$sub_creation_date</b></td><td colspan=3 bgcolor=\"#ddddee\">";
	echo nl2br($sub_description);
	echo "</td><tr>\n";
}
?>
<td bgcolor=\"#ccccdd\"><b>Add Note:</b></td><td colspan=3 bgcolor=\"#ddddee\"><textarea name=\"addnote\" rows=5 cols=70></textarea></td>
<tr>
<td colspan=4 align=center>
<input type=hidden name=load value=support>
<input type=hidden name=type value=module>
<input type=hidden name=editticket value=on>
<input type=hidden name=id value=$id>
<input name=savechanges type=submit value=\"$l_savechanges\" class=smallbutton>
</td>
</table>
</form>

