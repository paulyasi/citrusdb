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

  echo "<a href=\"$url_prefix/index.php?load=viewaccount&type=fs&acnum=$account_number\">$account_number: $name</a><br>";

 }
echo "</td></table></p>";

echo "<hr size=2 style=\"color:#eee;\">";


echo "<form id=\"messagetabform\">";
//echo "<input type=hidden name=\"blah\" value=\"1\">";
echo "<div id=\"messagetabs\">";
echo "</div>";

if ($ticketgroup) {
  $messagetabsurl = 'index.php?load=messagetabs&type=dl&ticketgroup=' . $ticketgroup;
} elseif ($ticketuser) {
  $messagetabsurl = 'index.php?load=messagetabs&type=dl&ticketuser=' . $ticketuser;
} else {
  $messagetabsurl = 'index.php?load=messagetabs&type=dl';
}

// print the new message count tabs using ajax so they refresh periodically
echo "
<script language=\"javascript\">
new Ajax.PeriodicalUpdater('messagetabs', '$messagetabsurl',
{
method: 'get',
frequency: 300,
});
</script></form>";


// show other dashboard specifictabs down here:


?>
