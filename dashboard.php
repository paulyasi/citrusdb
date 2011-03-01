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

echo "<hr size=2 style=\"color:#eee;\">";

// print the new message count tabs using ajax so they refresh
echo "<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"include/prototype.js\"></SCRIPT>\n";
echo "<script language=\"javascript\">
new Ajax.PeriodicalUpdater('messagetabs', 'index.php?load=messagetabs&type=dl',
{ method: 'get', frequency: 300 }); </script>";

echo "<div id=\"messagetabs\">";
echo "</div>";


// show other dashboard specifictabs down here:


?>
