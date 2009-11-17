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

// Print Number of New Support Messages

// query the customer_history for the number of 
// waiting messages sent to that user
$supportquery = "SELECT * FROM customer_history WHERE notify = '$user' ".
  "AND status = \"not done\" AND date(creation_date) <= CURRENT_DATE";
$supportresult = $DB->Execute($supportquery) or die ("$l_queryfailed");
$num_rows = $supportresult->RowCount();

echo "<hr>";

if ($num_rows > 0) {
  if ($num_rows > 1) {
    // plural messages
    print "<p align=center><b>$num_rows new $user messages</b><p>";
  } else {
    // singular message
    print "<p align=center><b>$num_rows new $user message</b><p>";    
  }
 }

// query the customer_history for messages sent to 
// groups the user belongs to
$query = "SELECT * FROM groups WHERE groupmember = '$user' ";
$supportresult = $DB->Execute($query) 
  or die ("$l_queryfailed");

while ($mygroupresult = $supportresult->FetchRow()) {
  if (!isset($mygroupresult['groupname']))  { 
    $mygroupresult['groupname'] = ""; 
  }
  
  $groupname = $mygroupresult['groupname'];
  $query = "SELECT * FROM customer_history WHERE notify = '$groupname' ".
    "AND status = \"not done\" AND date(creation_date) <= CURRENT_DATE";
  $gpresult = $DB->Execute($query) or die ("$l_queryfailed");
  $num_rows = $gpresult->RowCount();
  
  if ($num_rows > 0) {
    if ($num_rows > 1) {
      // plural messages
      print "<p align=center><b>$num_rows new $groupname messages</b><p>";
    } else {
      // singular message
      print "<p align=center><b>$num_rows new $groupname message</b><p>";      
    }
  }
  
 }

echo "<p align=center><a href=\"index.php?load=tickets&type=base\">View Messages</a></p>";
echo "<hr>";

?>
