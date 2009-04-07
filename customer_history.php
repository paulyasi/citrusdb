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
"<td bgcolor=\"#eeeedd\" style=\"padding: 4px;\" width=50>".
"<b>$l_ticketnumber</b></td>".
"<td bgcolor=\"#eeeedd\" style=\"padding: 4px;\" width=120>".
"<b>$l_datetime</b></td>".
"<td bgcolor=\"#eeeedd\" style=\"padding: 4px;\" width=70>".
"<b>$l_createdby</b></td>".
"<td bgcolor=\"#eeeedd\" style=\"padding: 4px;\" width=50>".
"<b>$l_notify</b></td>".
"<td bgcolor=\"#eeeedd\" style=\"padding: 4px;\" width=60>".
"<b>$l_status</b></td>".
"<td bgcolor=\"#eeeedd\" style=\"padding: 4px;\" width=321>".
"<b>$l_description</b></td>";

$query = "SELECT * FROM customer_history ".
  "WHERE account_number = '$account_number' ORDER BY id DESC LIMIT 500";
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

  // if the user is and admin or manager then have the ability to click a
  // link and open the ticket editor
  // also allow users to edit their own notes
  $query = "SELECT * FROM user WHERE username='$user'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $userresult = $DB->Execute($query) or die ("$l_queryfailed");
  $myuserresult = $userresult->fields;
  $showall_permission = false;
  if (($myuserresult['manager'] == 'y') OR ($myuserresult['admin'] == 'y')
      OR ($user == $created_by)) {
    print "<td style=\"border-top: 1px solid grey; padding-top: 2px; ".
      "padding-bottom: 2px;\"><a target=\"_parent\" ". 
      "href=\"index.php?load=support&type=module&editticket=on&id=$id\">".
      "$id</a> &nbsp;</td>";
  } else {
    print "<td style=\"border-top: 1px solid grey; padding-top: 2px; ".
      "padding-bottom: 2px;\">$id &nbsp;</td>";
  }
  
  print "<td style=\"border-top: 1px solid grey; padding-top: 2px; ".
    "padding-bottom: 2px;\">$creation_date &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey; padding-top: 2px; ".
    "padding-bottom: 2px;\">$created_by &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey; padding-top: 2px; ".
    "padding-bottom: 2px;\">$notify &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey; padding-top: 2px; ".
    "padding-bottom: 2px;\">$status &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey; padding-top: 2px; ".
    "padding-bottom: 2px;\">$description &nbsp; ";

  if ($linkurl) {
    print "<a href=\"$linkurl\">$linkname</a>";
  }
  
  print "</td>";
  
  // increment line count to make even/odd coloring
  $linecount++;
 }

echo '</table>';

?>
</body>
</html>
