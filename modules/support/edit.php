<?php   
// Copyright (C) 2002-2009  Paul Yasi <paul@citrusdb.org>
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

// GET Variables
if (!isset($base->input['id'])) { $base->input['id'] = ""; }
if (!isset($base->input['pending'])) { $base->input['pending'] = ""; }
if (!isset($base->input['completed'])) { $base->input['completed'] = ""; }
if (!isset($base->input['showall'])) { $base->input['showall'] = ""; }

$id = $base->input['id'];
$pending = $base->input['pending'];
$completed = $base->input['completed'];
$showall = $base->input['showall'];

if ($pending) {
  // mark the customer_history id as pending
  $query = "UPDATE customer_history SET status = \"pending\" WHERE id = $id";
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  print "<script language=\"JavaScript\">window.location.href = \"index.php?load=support&type=module&edit=on\";</script>";			
 } else if ($completed) {
  // make the customer_history id as completed
  $query = "UPDATE customer_history SET status = \"completed\" WHERE id = $id";
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  print "<script language=\"JavaScript\">window.location.href = \"index.php?load=support&type=module&edit=on\";</script>";	
 } else {
  echo "<a href=\"index.php?load=support&type=module&edit=on&showall=on\">$l_showlast50</a>";
  echo '<table cellspacing=1 cellpadding=5 border=0 width=720>';
  
  // find notes for groups that this user belongs to
  
  print "<tr><td bgcolor=\"#ffffff\" width=100% colspan=9>
		<b>$l_notesforgroups $user $l_belongsto</b></td>";
  echo "<tr><td bgcolor=\"#ccccdd\" width=10%><b>$l_ticketnumber</b></td>
		<td bgcolor=\"#ccccdd\" width=10%><b>$l_datetime</b></td>
        <td bgcolor=\"#ccccdd\" width=10%><b>$l_from</b></td>
		<td bgcolor=\"#ccccdd\" width=10%><b>$l_to</b></td>
        <td bgcolor=\"#ccccdd\" width=10%><b>$l_account</b></td>
        <td bgcolor=\"#ccccdd\" width=10%><b>$l_status</b></td>
        <td bgcolor=\"#ccccdd\" width=30%><b>$l_description</b></td>
		<td bgcolor=\"#ccccdd\" width=5%>$l_pending</td>
		<td bgcolor=\"#ccccdd\" width=5%>$l_finished</td>";
  
  $query = "SELECT * FROM groups WHERE groupmember = '$user' ";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  
  while ($myresult = $result->FetchRow()) {
    $groupname = $myresult['groupname'];
    if ($showall) {
      $query = "SELECT ch.id, ch.creation_date, ch.notify, ch.created_by, ch.account_number, ch.status, ch.description, ch.linkname, ch.linkurl, c.name FROM customer_history ch
			INNER JOIN customer c ON ch.account_number = c.account_number 
			WHERE notify = '$groupname' ORDER BY creation_date DESC LIMIT 50";
    } else {
      $query = "SELECT ch.id, ch.creation_date, ch.notify, ch.created_by, ch.account_number, 
			ch.status, ch.description, ch.linkname, ch.linkurl, c.name FROM customer_history ch
			INNER JOIN customer c ON ch.account_number = c.account_number 
			WHERE notify = '$groupname' AND status IN ('not done','pending') AND to_days(now()) >= to_days(creation_date) ORDER BY creation_date DESC";
    }
    $gpresult = $DB->Execute($query) or die ("$l_queryfailed");
    
    while ($groupresult = $gpresult->FetchRow()) {
      $id = $groupresult['id'];
      $creation_date = $groupresult['creation_date'];
      $notify = $groupresult['notify'];
      $created_by = $groupresult['created_by'];
      $accountnum = $groupresult['account_number'];
      $status = $groupresult['status'];
      $description = $groupresult['description'];
      $name = $groupresult['name'];
      $linkname = $groupresult['linkname'];
      $linkurl = $groupresult['linkurl'];
      
      if ($status == "not done"){ print "<tr onmouseover='h(this);' onmouseout='dehnew(this);' bgcolor=\"#ddeeff\">"; }
      else { print "<tr  onmouseover='h(this);' onmouseout='deh(this);' bgcolor=\"#ddddee\">"; }
      
      print "<td><a href=\"index.php?load=support&type=module&editticket=on&id=$id\">$id</a></td>";
      print "<td>$creation_date</td>";
      print "<td>$created_by</td>";
      print "<td>$notify</td>";
      print "<td><a href=\"index.php?load=viewaccount&type=fs&acnum=$accountnum\">$name</a></td>";
      print "<td>$status</td>";
      print "<td>$description <a href=\"$linkurl\">$linkname</a></td>";
      print "<td><a href=\"index.php?load=support&type=module&edit=on&pending=on&id=$id\">$l_pending</a></td>"; 
      print "<td><a href=\"index.php?load=support&type=module&edit=on&completed=on&id=$id\">$l_finished</a></td>";
    }
  }
  
  
  // find notes for that user
  
  print "<tr><td bgcolor=\"#ffffff\" width=100% colspan=9><br><b>$l_notesforuser $user</td>";
  echo "<tr><td bgcolor=\"#ccccdd\" width=10%><b>$l_ticketnumber</b></td>
		<td bgcolor=\"#ccccdd\" width=10%><b>$l_datetime</b></td>
        <td bgcolor=\"#ccccdd\" width=10%><b>$l_from</b></td>
		<td bgcolor=\"#ccccdd\" width=10%><b>$l_to</b></td>
        <td bgcolor=\"#ccccdd\" width=10%><b>$l_account</b></td>
        <td bgcolor=\"#ccccdd\" width=10%><b>$l_status</b></td>
        <td bgcolor=\"#ccccdd\" width=30%><b>$l_description</b></td>
		<td bgcolor=\"#ccccdd\" width=5%>$l_pending</td>
		<td bgcolor=\"#ccccdd\" width=5%>$l_finished</td>";

  if ($showall) {
    $query = "SELECT  ch.id, ch.creation_date, ch.notify, ch.created_by, ch.account_number, 
			ch.status, ch.description, ch.linkname, ch.linkurl, c.name FROM customer_history ch
			INNER JOIN customer c ON ch.account_number = c.account_number 
			WHERE notify = '$user' ORDER BY creation_date DESC LIMIT 50";
  } else {
    $query = "SELECT  ch.id, ch.creation_date, ch.notify, ch.created_by, ch.account_number, 
			ch.status, ch.description, ch.linkname, ch.linkurl, c.name FROM customer_history ch
			INNER JOIN customer c ON ch.account_number = c.account_number 
			WHERE notify = '$user' AND status IN ('not done','pending') AND to_days(now()) >= to_days(creation_date) ORDER BY creation_date DESC";
  }
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  
  
  while ($myresult = $result->FetchRow()) {
    $id = $myresult['id'];
    $creation_date = $myresult['creation_date'];
    $created_by = $myresult['created_by'];
    $notify = $myresult['notify'];
    $accountnum = $myresult['account_number'];
    $notify = $myresult['notify'];
    $status = $myresult['status'];
    $description = $myresult['description'];
    $name = $myresult['name'];
    $linkname = $myresult['linkname'];
    $linkurl = $myresult['linkurl'];
    
    if ($status == "not done"){ print "<tr onmouseover='h(this);' onmouseout='dehnew(this);' bgcolor=\"#ddeeff\">"; }
    else { print "<tr onmouseover='h(this);' onmouseout='deh(this);' bgcolor=\"#ddddee\">"; }
    
    print "<td><a href=\"index.php?load=support&type=module&editticket=on&id=$id\">$id</a></td>";
    print "<td>$creation_date</td>";
    print "<td>$created_by</td>";
    print "<td>$notify</td>";
    print "<td><a href=\"index.php?load=viewaccount&type=fs&acnum=$accountnum\">$name</a></td>";
    print "<td>$status</td>";
    print "<td>$description  <a href=\"$linkurl\">$linkname</a></td>";
    print "<td><a href=\"index.php?load=support&type=module&edit=on&pending=on&id=$id\">$l_pending</a></td>"; 
    print "<td><a href=\"index.php?load=support&type=module&edit=on&completed=on&id=$id\">$l_finished</a></td>";

  }

  echo '</table><br>';	

}
?>
