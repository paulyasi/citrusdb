<?php   
// Copyright (C) 2002-2005  Paul Yasi <paul@citrusdb.org>, read the README file for more information

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
	// set the rerun date to the next available billing date
	$mydate = get_nextbillingdate();

	// make sure the rerun date is not set to the same as the next_billing_date
	$query = "SELECT next_billing_date FROM billing WHERE id = '$billing_id'";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	$myresult = $result->fields;	
	$next_billing_date = $myresult['next_billing_date'];
	
	if ($next_billing_date == $mydate) {
		echo "<h3>$l_rerundateerror</h3>
		<center><form style=\"margin-bottom:0;\" action=\"index.php\">
    	<input name=done type=submit value=\" $l_ok  \" class=smallbutton><p></center>";
	} else {
		$query = "UPDATE billing SET rerun_date = '$mydate' WHERE id = '$billing_id'";
		$result = $DB->Execute($query) or die ("$l_queryfailed");
		print "<h3>$l_changessaved<h3>";
        print "<script language=\"JavaScript\">window.location.href = \"index.php?load=billing&type=module\";</script>";
	}
}
else
{	
	print "<br><br>";
	print "<h4>$l_areyousurereruncreditcard</h4>";
    print "<table cellpadding=15 cellspacing=0 border=0 width=720><td align=right width=360>";
    print "<form style=\"margin-bottom:0;\" action=\"index.php\">";
    print "<input type=hidden name=load value=billing>";
    print "<input type=hidden name=type value=module>";
    print "<input type=hidden name=rerun value=on>";
	print "<input type=hidden name=billing_id value=$billing_id>";
    print "<input name=save type=submit value=\" $l_yes \" class=smallbutton></form></td>";
    print "<td align=left width=360><form style=\"margin-bottom:0;\" action=\"index.php\">";
    print "<input name=done type=submit value=\" $l_no  \" class=smallbutton>";
    print "<input type=hidden name=load value=billing>";
    print "<input type=hidden name=type value=module>";
	print "</form></td></table>";
}
?>
