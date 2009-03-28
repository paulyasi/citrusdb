<SCRIPT LANGUAGE="JavaScript" SRC="include/CalendarPopup.js"></SCRIPT>
	<SCRIPT LANGUAGE="JavaScript">
	var cal = new CalendarPopup();
	</SCRIPT>
<?php   
// Copyright (C) 2002-2007  Paul Yasi <paul@citrusdb.org>,
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

//Get Variables
if (!isset($base->input['addnow'])) { $base->input['addnow'] = ""; }
if (!isset($base->input['organization_id'])) { $base->input['organization_id'] = ""; }

$addnow = $base->input['addnow'];
$organization_id = $base->input['organization_id'];

if ($addnow) // add the new billing record to the billing table
{
	$myinsertid = create_billing_record($organization_id, $account_number, $DB);
	print "<script language=\"JavaScript\">window.location.href =
\"index.php?load=billing&type=module&edit=on&billing_id=$myinsertid\";</script>";
}
else // list the service options after they clicked on the add button.
{
echo "
<a href=\"index.php?load=billing&type=module\">[ $l_undochanges ]</a>
<p>
$l_areyousureadd $account_number
<p>
<form style=\"margin-bottom:0;\" action=\"index.php\" name=\"form1\">
<input type=hidden name=load value=billing>
<input type=hidden name=type value=module>
<input type=hidden name=create value=on>
";

// print list of organizations to choose from
$query = "SELECT id,org_name FROM general";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
echo "<b>$l_organizationname</b> <select name=\"organization_id\">";
while ($myresult = $result->FetchRow()) {
	$myid = $myresult['id'];
	$myorg = $myresult['org_name'];
	echo "<option value=\"$myid\">$myorg</option>";
}

echo "</select>&nbsp;&nbsp;
<input name=addnow type=submit value=\"$l_addbilling\" class=smallbutton>
</form>
";

}

?>
