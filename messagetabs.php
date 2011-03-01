<?php
// Copyright (C) 2011  Paul Yasi (paul at citrusdb.org)
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

// make an empty array to hold the message count and initialize nummessages
$messagearray = array();
$nummessages = 0;

// query the customer_history for the number of 
// waiting messages sent to that user
$supportquery = "SELECT * FROM customer_history WHERE notify = '$user' ".
  "AND status = \"not done\" AND date(creation_date) <= CURRENT_DATE";
$supportresult = $DB->Execute($supportquery) or die ("$l_queryfailed");
$num_rows = $supportresult->RowCount();

$nummessages = $nummessages + $num_rows;

// assign the count of messages to the user message associative array
$messagearray[$user] = $num_rows;

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
  
  // assign the count of messages to the user message associative array
  $messagearray[$groupname] = $num_rows;  
  
}

echo "<div id=\"tabnav\">\n";
foreach ($messagearray as $recipient => $messagecount) {
  if ($messagecount == 0) {
    echo "<a href=\"$url_prefix/index.php?load=tickets&type=base#$recipient\"><b style=\"font-weight:normal;\">$recipient($messagecount)</b></a>\n";
  } else {
    echo "<a href=\"$url_prefix/index.php?load=tickets&type=base#$recipient\">$recipient($messagecount)</a>\n";    
  }
}
echo "</div>\n";
