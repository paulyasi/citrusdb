<SCRIPT LANGUAGE="JavaScript" SRC="include/CalendarPopup.js"></SCRIPT>
   <SCRIPT LANGUAGE="JavaScript">
   var cal = new CalendarPopup();

function cardval(s) 
{
  // remove non-numerics
  var v = "0123456789";
  var w = "";
  for (i=0; i < s.length; i++) 
    {
      x = s.charAt(i);
      if (v.indexOf(x,0) != -1)
	{
	  w += x;
	}
    }
  
  // validate number
  j = w.length / 2;
  if (j < 6.5 || j > 8 || j == 7) 
    {
      return false;
    }
  
  k = Math.floor(j);
  m = Math.ceil(j) - k;
  c = 0;
  for (i=0; i<k; i++) 
    {
      a = w.charAt(i*2+m) * 2;
      c += a > 9 ? Math.floor(a/10 + a%10) : a;
    }
  
  for (i=0; i<k+m; i++) c += w.charAt(i*2+1-m) * 1;
  {
    return (c%10 == 0);
  }
}
</SCRIPT>
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

if (!isset($base->input['editticket'])) { $base->input['editticket'] = ""; }
if (!isset($base->input['notify'])) { $base->input['notify'] = ""; }
if (!isset($base->input['status'])) { $base->input['status'] = ""; }
if (!isset($base->input['dtext'])) { $base->input['dtext'] = ""; }
if (!isset($base->input['reminderdate'])) { $base->input['reminderdate'] = ""; }
if (!isset($base->input['serviceid'])) { $base->input['serviceid'] = ""; }

$editticket = $base->input['editticket'];
$notify = $base->input['notify'];
$status = $base->input['status'];
$dtext = $base->input['dtext'];
$reminderdate = $base->input['reminderdate'];
$user_services_id = $base->input['serviceid'];
$description = $base->input['description'];

// grab the description manually to preserve newlines
$description = $_POST['description'];
$description = safe_value_with_newlines($description);

if ($edit)
{
    if ($pallow_modify)
    {
       include('./modules/support/edit.php');
    }  else permission_error();
}
else if ($editticket)
{
	if ($pallow_modify)
	{
		include('./modules/support/editticket.php');
	} else permission_error();
}
else if ($create) // add the message to customer history
{
	if ($pallow_create)
    	{
	  $newticketnumber = create_ticket($DB, $user, $notify, $account_number,
			$status, $description, NULL, NULL, $reminderdate,
			$user_services_id);

	  // if the note is marked as completed, insert the completed by data too
	  if ($status == 'completed') {
	    $query = "UPDATE customer_history SET ".
	      "closed_by = '$user', ".
	      "closed_date = CURRENT_TIMESTAMP ".
	      "WHERE id = $newticketnumber";
	    $result = $DB->Execute($query) or die ("closed by $l_queryfailed"); 
	  }

	print "<script language=\"JavaScript\">window.location.href = \"index.php?load=customer&type=module\";</script>";
	} else permission_error();
}

else if ($delete)
{
    if ($pallow_remove)
    {
       include('./modules/support/delete.php');
    } else permission_error();
}

else if ($pallow_view)
{

  // if it is regarding a service,
  // get the service description and support_notify
  $query = "SELECT us.id, us.master_service_id, ms.id, ".
    "ms.service_description, ms.support_notify ".
    "FROM user_services us ".
    "LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
    "WHERE us.id = '$user_services_id' LIMIT 1";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("this $l_queryfailed");
  $myresult = $result->fields;	
  $service_description = $myresult['service_description'];
  $support_notify = $myresult['support_notify'];
  
  // print the form
  echo "
<a href=\"index.php?load=customer&type=module\">[ $l_undochanges ]</a> &nbsp; 
<a href=\"index.php?load=support&type=module&edit=on\">[ $l_checknotes ]</a>
<table cellpadding=5 border=0 cellspacing=1 width=720>
<td bgcolor=\"#ccccdd\"><b>$l_createdby</b></td><td bgcolor=\"#ddddee\">$user</td><tr>
<td bgcolor=\"#ccccdd\"><b>$l_service</b></td><td bgcolor=\"#ddddee\">$user_services_id $service_description</td><tr>
<td bgcolor=\"#ccccdd\"><b>$l_notify</b></td><td bgcolor=\"#ddddee\">
<form style=\"margin-bottom:0;\" action=\"index.php\" name=\"form1\" method=post>
"; //end

        print "<select name=\"notify\">\n";
	print "<option value=\"$support_notify\">$support_notify</option>\n";
	print "<option value=\"nobody\">$l_nobody</option>\n";
	print "<optgroup label=\"$l_groups\">\n";
	// print the list of groups
        $query = "SELECT DISTINCT groupname FROM groups ";
        $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");

	while ($myresult = $result->FetchRow())
        {
                $groupname = $myresult['groupname'];          
                print "<option>$groupname</option>\n";
        }

	// print a seperator
	print "</optgroup><optgroup label=\"$l_users\">\n"; 


	// print the list of users
        $query = "SELECT username FROM user ORDER BY username";
        $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	
	while ($myresult = $result->FetchRow())
        {
                $username = $myresult['username'];
                print "<option>$username</option>\n";
        }

        print "</optgroup></select>\n";


echo "
</td><tr>
<td bgcolor=\"#ccccdd\"><b>$l_status</b></td><td bgcolor=\"#ddddee\">
<select name=\"status\">
<option value=\"\"></option>
<option value=\"not done\" selected>$l_notdone</option>
<option value=\"pending\">$l_pending</option>
<option value=\"completed\">$l_completed</option>
</select>
</td><tr>
<td bgcolor=\"#ccccdd\"><b>$l_description</b></td><td bgcolor=\"#ddddee\">
<textarea name=\"description\" rows=8 cols=50>$dtext</textarea></td><tr>

<td bgcolor=\"ccccdd\"><b>$l_reminderdate</b></td><td bgcolor=\"#ddddee\">
<input name=\"reminderdate\" type=text value=\"$reminderdate\" size=12>
<A HREF=\"#\" onClick=\"cal.select(document.forms['form1'].reminderdate,'anchor1','yyyy-MM-dd'); return false;\"NAME=\"anchor1\" ID=\"anchor1\" style=\"color:blue\">[$l_select]</A>&nbsp; 
</td>

<tr>
<td colspan=2 align=center>
<input type=hidden name=serviceid value=$user_services_id>
<input type=hidden name=load value=support>
<input type=hidden name=type value=module>
<input type=hidden name=create value=on>
<input name=addnow type=submit value=\"$l_add\" class=smallbutton>
</td>
</table>
</form>
"; //end

} else permission_error();

?>
