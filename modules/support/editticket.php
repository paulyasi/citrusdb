<?php   
// Copyright (C) 2002-2009  Paul Yasi (paul at citrusdb.org)
// read the README file for more information

/*----------------------------------------------------------------------------*/
// Check for authorized accesss
/*----------------------------------------------------------------------------*/
if(constant("INDEX_CITRUS") <> 1){
  echo "You must be logged in to run this.  Goodbye.";
  exit;	
}

if (!defined("INDEX_CITRUS")) {
  echo "You must be logged in to run this.  Goodbye.";
  exit;
}

//Includes
require_once('./include/permissions.inc.php');

// GET Variables
if (!isset($base->input['id'])) { $base->input['id'] = ""; }
if (!isset($base->input['notify'])) { $base->input['notify'] = ""; }
if (!isset($base->input['status'])) { $base->input['status'] = ""; }
if (!isset($base->input['description'])) { $base->input['description'] = ""; }
if (!isset($base->input['savechanges'])) { $base->input['savechanges'] = ""; }
if (!isset($base->input['reminderdate'])) { $base->input['reminderdate'] = ""; }
if (!isset($base->input['serviceid'])) { $base->input['serviceid'] = ""; }
if (!isset($base->input['addnote'])) { $base->input['addnote'] = ""; }
if (!isset($base->input['oldstatus'])) { $base->input['oldstatus'] = ""; }

$id = $base->input['id'];
$notify = $base->input['notify'];
$status = $base->input['status'];
$description = $base->input['description'];
$savechanges = $base->input['savechanges'];
$reminderdate = $base->input['reminderdate'];
$serviceid = $base->input['serviceid'];
$addnote = $base->input['addnote'];
$oldstatus = $base->input['oldstatus'];

if ($savechanges) {


  // first check if user_services_id is empty or zero, if so, set to NULL
  if (($user_services_id == '') OR ($user_services_id == 0)) {
    $user_services_string = "";
  } else {
    $user_services_string = ", user_services_id = '$serviceid' ";
  }
  
  // save the changes to the customer_history  
  if ($reminderdate <> '') {
    $query = "UPDATE customer_history SET notify = '$notify', ".
      "status = '$status', description = '$description', ".
      "creation_date = '$reminderdate' $user_services_string".
      "WHERE id = $id";
  } else {
    $query = "UPDATE customer_history SET notify = '$notify', ".
      "description = '$description', ".
      "status = '$status' $user_services_string".
      "WHERE id = $id";   
  }
  
  $result = $DB->Execute($query) or die ("result $l_queryfailed $query");

  // if the oldstatus changed from not done or pending to completed
  // then mark this user as the one who closed this ticket
  if ((($oldstatus == "not done") OR ($oldstatus == "pending"))
      AND ($status == "completed")) {
    $query = "UPDATE customer_history SET ".
      "closed_by = '$user', ".
      "closed_date = CURRENT_TIMESTAMP ".
      "WHERE id = $id";
    $result = $DB->Execute($query) or die ("result $l_queryfailed");    
  }

  // if there is a new note added, put that into the sub_history
  if ($addnote) {
    $query = "INSERT sub_history SET customer_history_id = '$id', creation_date = CURRENT_TIMESTAMP, created_by = '$user', description = '$addnote'";
    $result = $DB->Execute($query) or die ("sub_history insert $l_queryfailed");

    // TODO: send email/xmpp notification if new note added to notify user
    $url = "$url_prefix/index.php?load=support&type=module&editticket=on&id=$id";
    $message = "$notify: $addnote $url";
    
    // if the notify is a group or a user, if a group, then get all the users and notify each individual
    $query = "SELECT * FROM groups WHERE groupname = '$notify'";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("Group Query Failed");
    
    if ($result->RowCount() > 0) {
      // we are notifying a group of users
      while ($myresult = $result->FetchRow()) {
	$groupmember = $myresult['groupmember'];
	enotify($DB, $groupmember, $message, $id);
      } // end while    
    } else {
      // we are notifying an individual user
      enotify($DB, $notify, $message, $id);
    } // end if result    
    
  } // end if addnote

  // redirect back to the account record
  print "<script language=\"JavaScript\">window.location.href = \"index.php?load=tickets&type=base\";</script>";			
	
 } else {
  // show the ticket info to edit
  $query = "SELECT ch.id ch_id, ch.creation_date ch_creation_date, ".
    "ch.created_by ch_created_by, ch.notify ch_notify, ".
    "ch.account_number ch_account_number, ch.status ch_status, ".
    "ch.description ch_description, ch.linkname, ch.linkurl, c.name c_name, ".
    "ch.user_services_id ch_user_services_id, ch.closed_by ch_closed_by, ch.closed_date ch_closed_date, ".
    "ms.service_description service_description ".
    "FROM customer_history ch ".
    "LEFT JOIN customer c ON c.account_number = ch.account_number ".
    "LEFT JOIN user_services us ON us.id = ch.user_services_id ".
    "LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
    "WHERE ch.id = $id";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  $myresult = $result->fields;
	
  $id = $myresult['ch_id'];
  $creation_date = $myresult['ch_creation_date'];
  $created_by = $myresult['ch_created_by'];
  $notify = $myresult['ch_notify'];
  $accountnum = $myresult['ch_account_number'];
  $status = $myresult['ch_status'];
  $description = $myresult['ch_description'];
  $name = $myresult['c_name'];
  $linkname = $myresult['linkname'];
  $linkurl = $myresult['linkurl'];
  $serviceid = $myresult['ch_user_services_id'];
  $closed_by = $myresult['ch_closed_by'];
  $closed_date = $myresult['ch_closed_date'];
  $service_description = $myresult['service_description'];	
	
  echo "<a href=\"index.php?load=customer&type=module\">[ $l_undochanges ]</a>".
    "&nbsp; <a href=\"index.php?load=tickets&type=base\">".
    "[ $l_checknotes ]</a>".
    "<h3>$l_ticketnumber $id</h3>".
    "<form style=\"margin-bottom:0;\" action=\"index.php\" name=\"form1\" method=post>".
    "<table cellpadding=5 border=0 cellspacing=1 width=720>".
    "<td bgcolor=\"#ccccdd\"><b>$l_createdby</b></td>".
    "<td bgcolor=\"#ddddee\">$created_by $creation_date</td>".
    "<td bgcolor=\"#ccccdd\"><b>$l_closed_by</b></td>".
    "<td bgcolor=\"#ddddee\">$closed_by $closed_date</td><tr>".
    "<td bgcolor=\"#ccccdd\"><b>$l_customer</b></td>".
    "<td bgcolor=\"#ddddee\">".
    "<a href=\"index.php?load=viewaccount&type=fs&acnum=$accountnum\">".
    "$name</a>
&nbsp;&nbsp;($accountnum)</td>";

  echo "<td bgcolor=\"#ccccdd\"><b>$l_service</b></td><td bgcolor=\"#ddddee\">";

  // print a service id link if there is an associated service
  if ($serviceid > 0) {
    echo "<a href=\"index.php?load=services&type=module&edit=on&userserviceid=$serviceid&editbutton=Edit\">".
      "$serviceid $service_description</a>";
  }
  echo "&nbsp;&nbsp;<input type=text value=\"$serviceid\" name=\"serviceid\" size=10>".
      "</td><tr>";
      
  echo "<td bgcolor=\"#ccccdd\"><b>$l_notify</b></td>".
    "<td bgcolor=\"#ddddee\">";


  print "<select name=\"notify\">\n";
  print "<option selected value=\"$notify\">$notify</option>\n";
  print "<option value=\"nobody\">$l_nobody</option>\n";
  print "<optgroup label=\"$l_groups\">\n";
  // print the list of groups
  $query = "SELECT DISTINCT groupname FROM groups ";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");

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
    echo "<textarea name=\"description\" rows=2 cols=70>$description</textarea></td><tr>";
  } else {
    echo "<br>$description<input type=hidden name=\"description\" value=\"$description\"<br><br></td><tr>";
  }
  
  // print the current notes attached to this item
  $query = "SELECT * FROM sub_history WHERE customer_history_id = $id";
  $subresult = $DB->Execute($query) or die ("sub_history $l_queryfailed");

  while ($mysubresult = $subresult->FetchRow()) {
    $sub_creation_date = $mysubresult['creation_date'];
    $sub_created_by = $mysubresult['created_by'];
    $sub_description = $mysubresult['description'];
    
    print "<td bgcolor=\"#ccccdd\"><b>$sub_created_by<br>$sub_creation_date</b></td><td colspan=3 bgcolor=\"#ddddee\">$sub_description</td><tr>\n";
  }

  // print the end of the form  
  echo"<td bgcolor=\"#ccccdd\"><b>Add Note:</b></td><td colspan=3 bgcolor=\"#ddddee\"><textarea name=\"addnote\" rows=5 cols=70></textarea></td>
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
	";
	
}

?>
