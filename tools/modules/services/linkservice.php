<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_linkservices</h3>";

// Copyright (C) 2002  Paul Yasi <paul@citrusdb.org>, read the README file for more information
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

//GET Variables
if (!isset($base->input['linkfrom'])) { $base->input['linkfrom'] = ""; }
if (!isset($base->input['linkto'])) { $base->input['linkto'] = ""; }
if (!isset($base->input['unlink'])) { $base->input['unlink'] = ""; }

$submit = $base->input['submit'];
$linkfrom = $base->input['linkfrom'];
$linkto = $base->input['linkto'];
$unlink = $base->input['unlink'];

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
        echo "$l_youmusthaveadmin";
        exit;
}

if ($submit) 
{
// on submit have it link the linkto and linkfrom id's together by putting them into the linked_services table
	if ($unlink == 'on')
	{
		// remove the link
		$query = "DELETE FROM linked_services WHERE linkfrom = $linkfrom AND linkto = $linkto LIMIT 1";
		$result = $DB->Execute($query) or die ("$Lqueryfailed");
		print "<h3>$l_changessaved</h3> [<a href=\"index.php?load=services&tooltype=module&type=tools\">$l_done</a>]";
	} else {
		// add a link
		$query = "INSERT INTO linked_services (linkfrom,linkto) VALUES ($linkfrom,$linkto)";
        $result = $DB->Execute($query) or die ("$l_queryfailed");
        print "<h3>$l_changessaved</h3> [<a href=\"index.php?load=services&tooltype=module&type=tools\">$l_done</a>]";
	}
}

// add new link html form
echo"<p><b>$l_addnewlink</b><br>
<FORM ACTION=\"index.php\" METHOD=\"GET\">
$l_from: <select name=linkfrom>";
        // get the list of services from the table
        $query = "SELECT * FROM master_services ORDER BY service_description";
        $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	
	while ($myresult = $result->FetchRow())
	{
	$id = $myresult['id'];
        $description = $myresult['service_description'];
	print "<option value=\"$id\">$description</option>\n";
	}

	echo "</select><p>$l_to: <select name=linkto>";
	
        $query = "SELECT * FROM master_services ORDER BY service_description";
       $DB->SetFetchMode(ADODB_FETCH_ASSOC);
       $result = $DB->Execute($query) or die ("$l_queryfailed");

	while ($myresult = $result->FetchRow())
        {
        $id = $myresult['id'];
        $description = $myresult['service_description'];
        print "<option value=\"$id\">$description</option>\n";
        }
        echo "</select>&nbsp;
	
<input type=hidden name=load value=services>
<input type=hidden name=tooltype value=module>
<input type=hidden name=type value=tools>
<input type=hidden name=link value=on>
<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_add\">
</FORM><p>";

// print the list of linked services        
$query = "SELECT mfrom.id mfrom_id, mfrom.service_description mfrom_description, mto.id mto_id, mto.service_description mto_description, 
l.linkfrom, l.linkto FROM linked_services l LEFT JOIN master_services mfrom ON mfrom.id = l.linkfrom LEFT JOIN master_services mto ON mto.id = 
l.linkto";

$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");

echo "<table cellpadding=5 cellspacing=1><tr bgcolor=\"#eeeeee\"><td><b>$l_from</b></td><td><b>$l_to</b></td></tr>";
while ($myresult = $result->FetchRow())
{
	$fromid = $myresult['mfrom_id'];
        $fromdesc = $myresult['mfrom_description'];
	$toid = $myresult['mto_id'];
        $todesc = $myresult['mto_description'];
	print "<tr bgcolor=\"#eeeeee\"><td>$fromdesc</td><td>$todesc</td><td><a href=\"index.php?load=services&tooltype=module&type=tools&link=on&unlink=on&linkfrom=$fromid&linkto=$toid&submit=Link\">$l_unlink</a></td></tr>\n";
}

echo "</table><p>";

?>
<p>
</body>
</html>
