<?php   
// Copyright (C) 2008  Paul Yasi <paul@citrusdb.org>, read the README file for more information

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
$billing_id = $base->input['billing_id'];

if ($save) {
	// set the account payment_history to turnedoff
	$query = "INSERT INTO payment_history 
		(creation_date, billing_id, status) 
		VALUES (CURRENT_DATE,'$billing_id','turnedoff')";
	$paymentresult = $DB->Execute($query) or die ("$l_queryfailed");

	print "<script language=\"JavaScript\">window.location.href = \"index.php?load=billing&type=module\";</script>";

}
else
{	
	print "<br><br>";
	print "<h4>$l_areyousureturnedoff</h4>";
    print "<table cellpadding=15 cellspacing=0 border=0 width=720><td align=right width=360>";
    print "<form style=\"margin-bottom:0;\" action=\"index.php\" method=POST>";
    print "<input type=hidden name=load value=billing>";
    print "<input type=hidden name=type value=module>";
    print "<input type=hidden name=turnoff value=on>";
	print "<input type=hidden name=billing_id value=$billing_id>";
    print "<input name=save type=submit value=\" $l_yes \" class=smallbutton></form></td>";
    print "<td align=left width=360><form style=\"margin-bottom:0;\" action=\"index.php\" method=post>";
    print "<input name=done type=submit value=\" $l_no  \" class=smallbutton>";
    print "<input type=hidden name=load value=billing>";
    print "<input type=hidden name=type value=module>";
	print "</form></td></table>";
}
?>
