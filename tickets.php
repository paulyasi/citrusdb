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

// GET Variables
if (!isset($base->input['id'])) { $base->input['id'] = ""; }
if (!isset($base->input['pending'])) { $base->input['pending'] = ""; }
if (!isset($base->input['completed'])) { $base->input['completed'] = ""; }
if (!isset($base->input['showall'])) { $base->input['showall'] = ""; }
if (!isset($base->input['anchor'])) { $base->input['anchor'] = ""; }

$id = $base->input['id'];
$pending = $base->input['pending'];
$completed = $base->input['completed'];
$showall = $base->input['showall'];
$anchor = $base->input['anchor'];

if ($pending) {
  /*--------------------------------------------------------------------------*/
  // mark the customer_history id as pending
  /*--------------------------------------------------------------------------*/
  $query = "UPDATE customer_history SET status = \"pending\" WHERE id = $id";
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  print "<script language=\"JavaScript\">window.location.href = \"$url_prefix/index.php?load=tickets&type=base#$anchor\";</script>";			
 } else if ($completed) {
  /*--------------------------------------------------------------------------*/
  // make the customer_history id as completed
  /*--------------------------------------------------------------------------*/
  $mydate = date("Y-m-d H:i:s");
  $query = "UPDATE customer_history SET status = 'completed', closed_by = '$user', closed_date = '$mydate' WHERE id = $id";
  $result = $DB->Execute($query) or die ("$query $l_queryfailed");
  print "<script language=\"JavaScript\">window.location.href = \"$url_prefix/index.php?load=tickets&type=base#$anchor\";</script>";	
 } else {
  /*--------------------------------------------------------------------------*/
  // print the listing of tickets for this database user
  /*--------------------------------------------------------------------------*/

  // set the cookie that will allow us to determine if they have viewed the ticket screen or not
  // to figure out whether to show ticket tabs in green/unread
  // ticket cookie will be set to datetime YmdHis
  $ticketdatetime = date('YmdHis');
  setcookie('ticketdatetime',$ticketdatetime,(time()+3600000),'/','',0);
  
  echo "<a href=\"$url_prefix/index.php?load=tickets&type=base&showall=on\">$l_showlast50</a><br>";
  echo "<table cellpadding=0 border=0 width=720>";
  
  // find notes for groups that this user belongs to
  
  $query = "SELECT * FROM groups WHERE groupmember = '$user' ";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  
  while ($myresult = $result->FetchRow()) {
    $groupname = $myresult['groupname'];
    if ($showall) {
      $query = "SELECT ch.id, ch.creation_date, ch.notify, ch.created_by, ".
	"ch.account_number, ch.status, ch.description, ch.linkname, ".
	"ch.linkurl, ch.user_services_id, ms.service_description, c.name ".
	"FROM customer_history ch ".
	"INNER JOIN customer c ON ch.account_number = c.account_number ".
	"LEFT JOIN user_services us ON us.id = ch.user_services_id ".
	"LEFT JOIN master_services ms ON ms.id = us.master_service_id ".	
	"WHERE notify = '$groupname' ORDER BY notify,creation_date DESC ".
	"LIMIT 50";
    } else {
      //add "LEFT JOIN user_services us ON us.id = ch.".      
      $query = "SELECT ch.id, ch.creation_date, ch.notify, ch.created_by, ".
	"ch.account_number, ch.status, ch.description, ch.linkname, ".
	"ch.linkurl, ch.user_services_id, ms.service_description, c.name ".
	"FROM customer_history ch ".
	"INNER JOIN customer c ON ch.account_number = c.account_number ".
	"LEFT JOIN user_services us ON us.id = ch.user_services_id ".
	"LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
	"WHERE notify = '$groupname' AND status IN ('not done','pending') ".
	"AND to_days(now()) >= to_days(creation_date) ".
	"ORDER BY notify,creation_date DESC";
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
      $serviceid = $groupresult['user_services_id'];
      $service_description = $groupresult['service_description'];

      if ($serviceid == 0) {
	$serviceid = '';
	$servicedescription = '';
      }

      // print the heading for each group listing
      if (!isset($previousnotify)) { $previousnotify = ""; }
      if ($previousnotify <> $notify) {
	print "<a name=\"$notify\"><p><td bgcolor=\"#ffffff\" width=100% colspan=8> ".
	  "<b style=\"font-size: 14pt;\">$l_notesforgroups: $notify</b></td></a>";
	$previousnotify = $notify;
      }
      
      print "<tr>";

    if ($status == "not done"){
      print "<table onmouseover='h(this);'".
	"onmouseout='dehnew(this);' bgcolor=\"#ddeeff\" width=\"720\" ".
	"cellpadding=5 style=\"border-top: 1px solid #888; ".
	"border-bottom: 1px solid #888;\">";
    } else {
      print "<table onmouseover='h(this);' onmouseout='deh(this);' ".
	"bgcolor=\"#ddddee\" width=\"720\" cellpadding=5 ".
	"style=\"border-top: 1px solid #888; border-bottom: 1px solid #888;\">";
    }
    
    print "<td width=10%><a href=\"$url_prefix/index.php?load=viewticket&type=fs&ticket=$id&acnum=$accountnum\">$id</a></td>";
    print "<td width=20%>$creation_date</td>";
    print "<td width=10%>$created_by</td>";
    print "<td width=20%><a href=\"$url_prefix/index.php?load=viewaccount&type=fs&acnum=$accountnum\">$name</a></td>";
    print "<td width=10%>$status</td>";
    print "<td width=50% colspan=3><a href=\"$url_prefix/index.php?load=viewservice&type=fs&userserviceid=$serviceid&acnum=$accountnum\">$serviceid $service_description</a></td>";

    print "<tr><td width=100% colspan=8> &nbsp; ";
    echo nl2br($description);
    echo "<a href=\"$linkurl\">$linkname</a>";

    // get the sub_history printed here
    $query = "SELECT * FROM sub_history WHERE customer_history_id = $id";
    $subresult = $DB->Execute($query) or die ("sub_history $l_queryfailed");
    
    while ($mysubresult = $subresult->FetchRow()) {
      $sub_creation_date = $mysubresult['creation_date'];
      $sub_created_by = $mysubresult['created_by'];
      $sub_description = $mysubresult['description'];
      
      print "<p>&nbsp;&nbsp;&nbsp;$sub_created_by: ";
      echo nl2br($sub_description);
      echo "</p>\n";
    }

    // end the table block
    echo "</td>";
        
    print "<tr><td colspan=8 style=\"text-align: right;\"><a href=\"$url_prefix/index.php?load=viewticket&type=fs&ticket=$id&acnum=$accountnum\">$l_edit</a>";  
    print " | <a href=\"$url_prefix/index.php?load=tickets&type=base&pending=on&anchor=$notify&id=$id\">$l_pending</a>"; 
    print " | <a href=\"$url_prefix/index.php?load=tickets&type=base&completed=on&anchor=$notify&id=$id\">$l_finished</a></td></table>";
      
      
    }
  }
  
  
  // find notes for that user
  
  print "<a name=\"$user\"><tr><td bgcolor=\"#ffffff\" width=100% colspan=8><br>".
    "<b style=\"font-size: 14pt;\">$l_notesforuser $user</td></a>";
    //echo "<tr><td bgcolor=\"#ccccdd\" width=10%><b>$l_ticketnumber</b></td>".
    //"<td bgcolor=\"#ccccdd\" width=20%><b>$l_datetime</b></td>".
    //"<td bgcolor=\"#ccccdd\" width=10%><b>$l_from</b></td>".
    //"<td bgcolor=\"#ccccdd\" width=20%><b>$l_account</b></td>".
    //"<td bgcolor=\"#ccccdd\" width=10%><b>$l_status</b></td>".
    //"<td bgcolor=\"#ccccdd\" width=30%><b>$l_service</b></td>".
    //"<td bgcolor=\"#ccccdd\" width=10%></td>".
    //"<td bgcolor=\"#ccccdd\" width=10%></td>";

  if ($showall) {
    $query = "SELECT  ch.id, ch.creation_date, ch.notify, ch.created_by, ".
      "ch.account_number, ch.status, ch.description, ch.linkname, ".
      "ch.linkurl, ch.user_services_id, ms.service_description, c.name ".
      "FROM customer_history ch ".
      "INNER JOIN customer c ON ch.account_number = c.account_number ".
      "LEFT JOIN user_services us ON us.id = ch.user_services_id ".
      "LEFT JOIN master_services ms ON ms.id = us.master_service_id ".      
      "WHERE notify = '$user' ORDER BY creation_date DESC LIMIT 50";
  } else {
    $query = "SELECT  ch.id, ch.creation_date, ch.notify, ch.created_by, ".
      "ch.account_number, ch.status, ch.description, ch.linkname, ".
      "ch.linkurl, ch.user_services_id, ms.service_description, c.name ".
      "FROM customer_history ch ".
      "INNER JOIN customer c ON ch.account_number = c.account_number ".
      "LEFT JOIN user_services us ON us.id = ch.user_services_id ".
      "LEFT JOIN master_services ms ON ms.id = us.master_service_id ".      
      "WHERE notify = '$user' AND status IN ('not done','pending') ".
      "AND to_days(now()) >= to_days(creation_date) ".
      "ORDER BY creation_date DESC";
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
    $serviceid = $myresult['user_services_id'];
    $service_description = $myresult['service_description'];

    if ($serviceid == 0) {
      $serviceid = '';
      $service_description = '';
    }

    print "<tr>";

    if ($status == "not done"){
      print "<table onmouseover='h(this);'".
	"onmouseout='dehnew(this);' bgcolor=\"#ddeeff\" width=\"720\" ".
	"cellpadding=5 style=\"border-top: 1px solid #888; ".
	"border-bottom: 1px solid #888;\">";
    } else {
      print "<table onmouseover='h(this);' onmouseout='deh(this);' ".
	"bgcolor=\"#ddddee\" width=\"720\" cellpadding=5 ".
	"style=\"border-top: 1px solid #888; border-bottom: 1px solid #888;\">";
    }
    
    print "<td width=10%><a href=\"$url_prefix/index.php?load=viewticket&type=fs&ticket=$id&acnum=$accountnum\">$id</a></td>";
    print "<td width=20%>$creation_date</td>";
    print "<td width=10%>$created_by</td>";
    print "<td width=20%><a href=\"$url_prefix/index.php?load=viewaccount&type=fs&acnum=$accountnum\">$name</a></td>";
    print "<td width=10%>$status</td>";
    print "<td width=50% colspan=3><a href=\"$url_prefix/index.php?load=viewservice&type=fs&userserviceid=$serviceid&acnum=$accountnum\">$serviceid $service_description</a></td>";
    
    print "<tr><td width=100% colspan=8>&nbsp;";
    echo nl2br($description);
    echo "<a href=\"$linkurl\">$linkname</a>";

    // get the sub_history printed here
    $query = "SELECT * FROM sub_history WHERE customer_history_id = $id";
    $subresult = $DB->Execute($query) or die ("sub_history $l_queryfailed");
    
    while ($mysubresult = $subresult->FetchRow()) {
      $sub_creation_date = $mysubresult['creation_date'];
      $sub_created_by = $mysubresult['created_by'];
      $sub_description = $mysubresult['description'];
      
      print "<p>&nbsp;&nbsp;&nbsp;$sub_created_by: ";
      echo nl2br ($sub_description);
      echo "</p>\n";
    }

    // end the table block
    echo "</td>";
    
    print "<tr><td colspan=8 style=\"text-align: right;\"><a href=\"$url_prefix/index.php?load=viewticket&type=fs&ticket=$id&acnum=$accountnum\">$l_edit</a>";
    print " | <a href=\"$url_prefix/index.php?load=tickets&type=base&pending=on&id=$id\">$l_pending</a>"; 
    print " | <a href=\"$url_prefix/index.php?load=tickets&type=base&completed=on&id=$id\">$l_finished</a></td></table>";
  }

  echo '</table><br>';	

}
?>
