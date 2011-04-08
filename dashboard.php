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
