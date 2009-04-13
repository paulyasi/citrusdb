<?php   
// Copyright (C) 2002-2007  Paul Yasi (paul at citrusdb.org)
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

$id = $base->input['id'];
$notify = $base->input['notify'];
$status = $base->input['status'];
$description = $base->input['description'];
$savechanges = $base->input['savechanges'];

if ($savechanges)
{
  // save the changes

  $query = "UPDATE customer_history SET notify = '$notify', ".
    "status = '$status', description = '$description' ".
    "WHERE id = $id";
  $result = $DB->Execute($query) or die ("$l_queryfailed");
	
  // redirect back to the account record
  print "<script language=\"JavaScript\">window.location.href = \"index.php?load=support&type=module&edit=on\";</script>";			
	
 } else {
  // show the ticket info to edit
  $query = "SELECT ch.id ch_id, ch.creation_date ch_creation_date, ".
    "ch.created_by ch_created_by, ch.notify ch_notify, ".
    "ch.account_number ch_account_number, ch.status ch_status, ".
    "ch.description ch_description, ch.linkname, ch.linkurl, c.name c_name, ".
    "ch.user_services_id ch_user_services_id, ".
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
  $service_description = $myresult['service_description'];
	
	
  echo "<a href=\"index.php?load=customer&type=module\">[ $l_undochanges ]</a>".
    "&nbsp; <a href=\"index.php?load=support&type=module&edit=on\">".
    "[ $l_checknotes ]</a>".
    "<h3>$l_ticketnumber $id</h3>".
    "<table cellpadding=5 border=0 cellspacing=1 width=720>".
    "<td bgcolor=\"#ccccdd\"><b>$l_createdby</b></td>".
    "<td bgcolor=\"#ddddee\">$created_by</td><tr>".
    "<td bgcolor=\"#ccccdd\"><b>$l_creation</b></td>".
    "<td bgcolor=\"#ddddee\">$creation_date</td><tr>".
    "<td bgcolor=\"#ccccdd\"><b>$l_customer</b></td>".
    "<td bgcolor=\"#ddddee\">".
    "<a href=\"index.php?load=viewaccount&type=fs&acnum=$accountnum\">".
    "$name</a></td><tr>";

  if ($serviceid > 0) {
    echo "<td bgcolor=\"#ccccdd\"><b>$l_service</b></td>".
      "<td bgcolor=\"#ddddee\">$serviceid $service_description</td>".
      "<tr>";
  }
      
  echo "<td bgcolor=\"#ccccdd\"><b>$l_notify</b></td>".
    "<td bgcolor=\"#ddddee\">".
    "<form style=\"margin-bottom:0;\" action=\"index.php\">"; //end

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
  $result = $DB->Execute($query) or die ("$l_queryfailed");
	
  while ($myresult = $result->FetchRow()) {
    $username = $myresult['username'];
    print "<option>$username</option>\n";
  }

  print "</optgroup></select>\n";


  echo "
	</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_status</b></td><td bgcolor=\"#ddddee\">
	<select name=\"status\">
	<option selected value=\"$status\">$status</option>
	<option value=\"not done\">$l_notdone</option>
	<option value=\"pending\">$l_pending</option>
	<option value=\"completed\">$l_completed</option>
	</select>
	</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_description</b></td><td bgcolor=\"#ddddee\">
	<textarea name=\"description\" rows=8 cols=50>$description</textarea></td><tr>
	<tr>
	<td bgcolor=\"#ccccdd\"><b>$l_link</b></td><td bgcolor=\"#ddddee\">
	<a href=\"$linkurl\">$linkname</a></td><tr>
	<tr>
	<td colspan=2 align=center>
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
