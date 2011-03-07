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

// get the variables passed to it by the ajax call, the datetime when the page was loaded first
// if the notes have a newer datetime then show them in red to indicate they are newer
//if (!isset($base->input['datetime'])) { $base->input['datetime'] = ""; }
//$pagedatetime = $base->input['datetime'];

// make an empty array to hold the message count and initialize nummessages
$messagearray = array();
$nummessages = 0;
$num_rows = 0;
$created = 0;

echo "<div id=\"tabnav\">\n";

// get the ticketdatetime for the user, the last time tickets.php was loaded for $user
$ticketdatetime = $user . 'datetime';
if (!isset($_COOKIE[$ticketdatetime])) { 
  $_COOKIE[$ticketdatetime] = ""; 
}
$usernamedatetime = $_COOKIE[$ticketdatetime];

// query the customer_history for the number of 
// waiting messages sent to that user
$supportquery = "SELECT id, DATE_FORMAT(creation_date, '%Y%m%d%H%i%s') AS mydatetime ".
  "FROM customer_history WHERE notify = '$user' ".
  "AND status = \"not done\" AND date(creation_date) <= CURRENT_DATE ORDER BY id DESC";
$supportresult = $DB->Execute($supportquery) or die ("$l_queryfailed");

//while ($mysupportresult = $supportresult->FetchRow()) {
//  $num_rows++;
//  if ($num_rows == 1) { $created = $mysupportresult['mydatetime']; }
//}

$num_rows = $supportresult->RowCount();
if ($num_rows > 0) {
  $mysupportresult = $supportresult->fields;
  $created = $mysupportresult['mydatetime'];
}

//$nummessages = $nummessages + $num_rows;

// assign the count of messages to the user message associative array
//$messagearray[$user] = $num_rows;

if (!empty($usernamedatetime) AND $created > $usernamedatetime) {
  $bgstyle = "style = \"background-color: #AFA;\"";
} elseif ($ticketuser == $user) {
  $bgstyle = "class = \"active\"";
} else {
  $bgstyle = "";
}

if ($num_rows == 0) {
  echo "<a href=\"$url_prefix/index.php?load=tickets&type=base&ticketuser=$user&lastview=$usernamedatetime\" $bgstyle>".
    "<b style=\"font-weight:normal;\">$user($num_rows)</b></a>\n";
} else {
  echo "<a href=\"$url_prefix/index.php?load=tickets&type=base&ticketuser=$user&lastview=$usernamedatetime\" $bgstyle>$user($num_rows)</a>\n";    
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

  // initialize num_rows
  $num_rows = 0;
  $created = 0;

  // get the ticketdatetime for this group, the last time tickets.php was loaded for $groupname
  $ticketdatetime = $groupname . 'datetime';
  if (!isset($_COOKIE[$ticketdatetime])) { 
    $_COOKIE[$ticketdatetime] = ""; 
  }
  $groupnamedatetime = $_COOKIE[$ticketdatetime];
  
  
  // query each group

  $query = "SELECT id, DATE_FORMAT(creation_date, '%Y%m%d%H%i%s') AS mydatetime FROM customer_history WHERE notify = '$groupname' ".
    "AND status = \"not done\" AND date(creation_date) <= CURRENT_DATE ORDER BY id DESC";
  $gpresult = $DB->Execute($query) or die ("$l_queryfailed");

  $num_rows = $gpresult->RowCount();
  if ($num_rows > 0) {
    $mygpresult = $gpresult->fields;
    $created = $mygpresult['mydatetime'];
  }

  if (!empty($groupnamedatetime) AND $created > $groupnamedatetime) {
    $bgstyle = "style = \"background-color: #AFA;\"";
  } elseif ($ticketgroup == $groupname) {
    $bgstyle = "class = \"active\"";
  } else {
    $bgstyle = "";
  }

  if ($num_rows == 0) {
    echo "<a href=\"$url_prefix/index.php?load=tickets&type=base&ticketgroup=$groupname&lastview=groupnamedatetime\" $bgstyle><b style=\"font-weight:normal;\">$groupname($num_rows)</b></a>\n";
  } else {
    echo "<a href=\"$url_prefix/index.php?load=tickets&type=base&ticketgroup=$groupname&lastview=$groupnamedatetime\" $bgstyle>$groupname($num_rows)</a>\n";    
  }

}


echo "</div>\n";
