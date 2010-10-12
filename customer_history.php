<html>
<head>
<LINK href="citrus.css" type=text/css rel=STYLESHEET>
<LINK href="fullscreen.css" type=text/css rel=STYLESHEET>
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
</head>
<body bgcolor="#eeeedd" marginheight=0 marginwidth=1 leftmargin=1 rightmargin=0>
<?php
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
$account_number = $base->input['account_number'];
	
echo "<table cellspacing=0 cellpadding=0 border=0>".
"<td bgcolor=\"#eeeedd\" style=\"padding: 4px;\" width=60>".
"<b>$l_ticketnumber</b></td>".
"<td bgcolor=\"#eeeedd\" style=\"padding: 4px;\" width=150>".
"<b>$l_datetime</b></td>".
"<td bgcolor=\"#eeeedd\" style=\"padding: 4px;\" width=70>".
"<b>$l_createdby</b></td>".
"<td bgcolor=\"#eeeedd\" style=\"padding: 4px;\" width=70>".
"<b>$l_notify</b></td>".
"<td bgcolor=\"#eeeedd\" style=\"padding: 4px;\" width=60>".
"<b>$l_status</b></td>".
"<td bgcolor=\"#eeeedd\" style=\"padding: 4px;\" width=261>".
"<b>$l_service</b></td>";

$query = "SELECT  ch.id, ch.creation_date, ".
  "ch.created_by, ch.notify, ch.status, ch.description, ch.linkname, ".
  "ch.linkname, ch.linkurl, ch.user_services_id, us.master_service_id, ".
  "ms.service_description FROM customer_history ch ".
  "LEFT JOIN user_services us ON us.id = ch.user_services_id ".
  "LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
  "WHERE ch.account_number = '$account_number' ORDER BY ch.id DESC LIMIT 25";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$linecount = 0;
while ($myresult = $result->FetchRow()) {
  $id = $myresult['id'];
  $creation_date = $myresult['creation_date'];
  $created_by = $myresult['created_by'];
  $notify = $myresult['notify'];
  $status = $myresult['status'];
  $description = $myresult['description'];
  $linkname = $myresult['linkname'];
  $linkurl = $myresult['linkurl'];
  $serviceid = $myresult['user_services_id'];
  $service_description = $myresult['service_description'];

  // translate the status
  switch($status) {
  case 'automatic':
    $status = $l_automatic;
    break;
  case 'not done':
    $status = $l_notdone;
    break;
  case 'pending':
    $status = $l_pending;
    break;
  case 'completed':
    $status = $l_completed;
    break;
  }

  // alternate line colors
  if ($linecount & 1) {
    print "<tr bgcolor=\"#ffffee\">";
  } else {
    print "<tr bgcolor=\"#ffffdd\">";
  }

  
  // THIS WILL NOT BE BLOCKED ANYMORE, ESPECIALLY WHEN THE SUBNOTES ARE ADDED
  // BEING ABLE TO CLICK ON TICKETS WILL BE A REQUIRED FEATURE
  // if the user is and admin or manager then have the ability to click a
  // link and open the ticket editor
  // also allow users to edit their own notes
  //  $query = "SELECT * FROM user WHERE username='$user'";
  //$DB->SetFetchMode(ADODB_FETCH_ASSOC);
  //$userresult = $DB->Execute($query) or die ("$l_queryfailed");
  //$myuserresult = $userresult->fields;
  //$showall_permission = false;
  //if (($myuserresult['manager'] == 'y') OR ($myuserresult['admin'] == 'y')
  //    OR ($user == $created_by)) {
    print "<td style=\"border-top: 1px solid grey; padding-top: 2px; ".
      "padding-bottom: 2px; font-size: 9pt; font-weight: bold;\"><a target=\"_parent\" ". 
      "href=\"index.php?load=support&type=module&editticket=on&id=$id\">".
      "$id</a> &nbsp;</td>";
    //} else {
    //print "<td style=\"border-top: 1px solid grey; padding-top: 2px; ".
    //  "padding-bottom: 2px; font-size: 9pt;\">$id &nbsp;</td>";
    //}
  
  print "<td style=\"border-top: 1px solid grey; padding-top: 2px; ".
    "padding-bottom: 2px; font-size: 9pt; font-weight: bold;\">$creation_date &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey; padding-top: 2px; ".
    "padding-bottom: 2px; font-size: 9pt; font-weight: bold;\">$created_by &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey; padding-top: 2px; ".
    "padding-bottom: 2px; font-size: 9pt; font-weight: bold;\">$notify &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey; padding-top: 2px; ".
    "padding-bottom: 2px; font-size: 9pt; font-weight: bold;\">$status &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey; padding-top: 2px; ".
    "padding-bottom: 2px; font-size: 9pt; font-weight: bold;\">";

  // if they have a valid service id that is greater than zero, print the link to it here
  if ($serviceid > 0) {
    print "<a href=\"index.php?load=services&type=module&edit=on&userserviceid=$serviceid&editbutton=Edit\" target=\"_parent\">$serviceid $service_description</a>&nbsp;";
  }
  
  if ($linkurl) {
    print "<a href=\"$linkurl\" target=\"_new\">$linkname</a>";
  }
  
  print "&nbsp;</td>";

  // alternate line colors
  if ($linecount & 1) {
    print "<tr bgcolor=\"#ffffee\">";
  } else {
    print "<tr bgcolor=\"#ffffdd\">";
  }

  print "<td colspan=6 style=\"font-size: 10pt; padding-bottom: 5px;\">&nbsp;$description<br>";

  // get the sub_history printed here
  $query = "SELECT month(creation_date) as month, day(creation_date) as day, ".
    "hour(creation_date) as hour, LPAD(minute(creation_date),2,'00') as minute, ".
    "created_by, description FROM sub_history WHERE customer_history_id = $id";
  $subresult = $DB->Execute($query) or die ("sub_history $l_queryfailed");
  
  while ($mysubresult = $subresult->FetchRow()) {
    $mydatetime = $mysubresult['month']."/".$mysubresult['day']." ".$mysubresult['hour'].":".$mysubresult['minute'];
    $sub_created_by = $mysubresult['created_by'];
    $sub_description = $mysubresult['description'];

    // if today, show time
    // if creation date not today, show date/time
    
    print "$mydatetime $sub_created_by: $sub_description<br>\n";
    }

  // end this table block
  echo "</td>";
  
  // increment line count to make even/odd coloring
  $linecount++;
 }

echo "<tr bgcolor=\"#dddddd\"><td style=\"padding: 5px; \"colspan=6><a href=\"index.php?load=all_customer_history&type=fs&account_number=$account_number\">$l_showall...</a></td>";
echo '</table>';

?>
</body>
</html>
