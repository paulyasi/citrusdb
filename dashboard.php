<?php
// Copyright (C) 2009  Paul Yasi (paul at citrusdb.org) 
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


echo "<p align=center><b>Recently Viewed:</b>";
echo "<table cellpadding=10><td class=\"smalltext\">";
// show recent customers viewed
$query = "SELECT a.account_number, c.name FROM activity_log a LEFT JOIN customer c ON c.account_number = a.account_number WHERE a.user = '$user' AND activity_type = 'view' AND record_type = 'customer' ORDER BY datetime DESC limit 10";
$result = $DB->Execute($query) or die ("$l_queryfailed");

while ($myresult = $result->FetchRow()) {
  $account_number = $myresult['account_number'];
  $name = $myresult['name'];

  echo "<a href=\"index.php?load=viewaccount&type=fs&acnum=$account_number\">$account_number: $name</a><br>";

 }
echo "</td></table></p>";


// Print Number of New Support Messages

// query the customer_history for the number of 
// waiting messages sent to that user
$supportquery = "SELECT * FROM customer_history WHERE notify = '$user' ".
  "AND status = \"not done\" AND date(creation_date) <= CURRENT_DATE";
$supportresult = $DB->Execute($supportquery) or die ("$l_queryfailed");
$num_rows = $supportresult->RowCount();

$nummessages = 0;

$nummessages = $nummessaes + $num_rows;

// query the customer_history for messages sent to 
// groups the user belongs to
$query = "SELECT * FROM groups WHERE groupmember = '$user' ";
$supportresult = $DB->Execute($query) 
  or die ("$l_queryfailed");

while ($mygroupresult = $supportresult->FetchRow()) {
  if (!isset($mygroupresult['groupname']))  { 
    $mygroupresult['groupname'] = ""; 
  }

  // query each group
  $groupname = $mygroupresult['groupname'];
  $query = "SELECT * FROM customer_history WHERE notify = '$groupname' ".
    "AND status = \"not done\" AND date(creation_date) <= CURRENT_DATE";
  $gpresult = $DB->Execute($query) or die ("$l_queryfailed");
  $num_rows = $gpresult->RowCount();

  $nummessages = $nummessages + $num_rows;
  
 }

// put the num messages link inside of tabnav

echo "<div id=\"tabnav\">";

if ($nummessages > 0) {
  if ($nummessages > 1) {
    // plural messages
    if ($load == "tickets") {   
      print "<div <a class = \"active\" href=\"index.php?load=tickets&type=base\">$nummessages new messages</a></div>";
    } else {
      print "<div <a href=\"index.php?load=tickets&type=base\">$nummessages new messages</a></div>";      
    }
  } else {
    // singular message
    if ($load == "tickets") {
      print "<div><a class = \"active\" href=\"index.php?load=tickets&type=base\">$nummessages new message</a></div>";
    } else {
      print "<div><a href=\"index.php?load=tickets&type=base\">$nummessages new message</a></div>";      
    }
  }
 } else {
  if ($load == "tickets") {
    echo "<div><a href=\"index.php?load=tickets&type=base\">No New Messages</a><div>";
  } else {
    echo "<div><a href=\"index.php?load=tickets&type=base\">No New Messages</a><div>";
  }
 }

// show other dashboard specifictabs down here:


?>
