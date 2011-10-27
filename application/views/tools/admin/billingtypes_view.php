<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_editbillingtypes</h3>";
// Copyright (C) 2003  Paul Yasi <paul@citrusdb.org>
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


//GET Variables
if (!isset($base->input['method'])) { $base->input['method'] = ""; }
if (!isset($base->input['frequency'])) { $base->input['frequency'] = ""; }
if (!isset($base->input['name'])) { $base->input['name'] = ""; }
if (!isset($base->input['remove'])) { $base->input['remove'] = ""; }
if (!isset($base->input['typeid'])) { $base->input['typeid'] = ""; }
if (!isset($base->input['deletenow'])) { $base->input['deletenow'] = ""; }

$submit = $base->input['submit'];
$method = $base->input['method'];
$frequency = $base->input['frequency'];
$name = $base->input['name'];
$remove = $base->input['remove'];
$typeid = $base->input['typeid'];
$deletenow = $base->input['deletenow'];

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryrailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
        echo "$l_youmusthaveadmin<br>";
        exit;
}

if ($submit == $l_submit) 
{
// add a billing type
	$query = "INSERT INTO billing_types (name,frequency,method) VALUES ('$name','$frequency','$method')";
        $result = $DB->Execute($query) or die ("$l_queryfailed");
        print "<h3>$l_changessaved</h3> [<a href=\"index.php?load=billing&tooltype=module&type=tools\">done</a>]";
}

if ($remove == 'on')
{
	if ($deletenow) 
	{

		// delete the grouping with that ID

		// remove the billing type
		$query = "DELETE FROM billing_types WHERE id = $typeid";
		$result = $DB->Execute($query) or die ("$l_queryfailed");
		print "<h3>$l_changessaved</h3> [<a href=\"index.php?load=billing&tooltype=module&type=tools\">done</a>]";

	}
	else
	{
		print "<br><br>";
		print "<h4>$l_areyousuredelete: $typeid</h4>";
		print "<table cellpadding=15 cellspacing=0 border=0 width=720>".
			"<td align=right width=360>";

		// if they hit yes, this will sent them into the delete and remove the service

		print "<form style=\"margin-bottom:0;\" action=\"index.php\" method=post>";	
		print "<input type=hidden name=load value=billing>";
		print "<input type=hidden name=type value=tools>";
		print "<input type=hidden name=tooltype value=module>";
		print "<input type=hidden name=typeid value=$typeid>";
		print "<input type=hidden name=remove value=on>";
		print "<input type=hidden name=billingtypes value=on>";
		print "<input name=deletenow type=submit value=\"  $l_yes  \" class=smallbutton>".
			"</form></td>";

		// if they hit no, send them back to the service edit screen

		print "<td align=left width=360><form style=\"margin-bottom:0;\" action=\"index.php\" method=post>";
		print "<input name=done type=submit value=\"  $l_no  \" class=smallbutton>";
        print "<input type=hidden name=load value=billing>";  
		print "<input type=hidden name=type value=tools>";        
		print "<input type=hidden name=tooltype value=module>";
		print "<input type=hidden name=billingtypes value=on>";
		print "</form></td></table>";
		print "</blockquote>";
	}

}



$query = "SELECT * FROM billing_types ORDER BY name";

$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");

echo "<table cellpadding=5 cellspacing=1><tr bgcolor=\"#eeeeee\">
<td><b>$l_id</b></td>
<td><b>$l_name</b></td>
<td><b>$l_frequency</b></td>
<td><b>$l_method</b></td>
<td></td>
</tr>";
while ($myresult = $result->FetchRow())
{
	$id = $myresult['id'];
        $name = $myresult['name'];
        $frequency = $myresult['frequency'];
	$method = $myresult['method'];
	print "<tr bgcolor=\"#eeeeee\"><td>$id</td><td>$name</td><td>$frequency</td><td>$method</td><td><a href=\"index.php?load=billing&tooltype=module&type=tools&billingtypes=on&remove=on&typeid=$id&submit=Link\">$l_remove</a></td></tr>\n";
}

echo "</table><p>
<b>$l_add:</b><br>
<FORM ACTION=\"index.php\" METHOD=\"PUT\">
<table>
<td>$l_name:</td><td><input name=\"name\" type=text></td><tr>
<td>$l_frequency:</td><td><input name=\"frequency\" type=text></td><tr>
<td>$l_method:</td><td><select name=\"method\">
		<option value=\"creditcard\">creditcard</option>
		<option value=\"invoice\">invoice</option>
		<option value=\"einvoice\">einvoice</option>
		<option value=\"prepay\">prepay</option>
		<option value=\"prepaycc\">prepaycc</option>
		<option value=\"free\">free</option>
	</select></td><tr>
</table>
<input type=hidden name=load value=billing>
<input type=hidden name=tooltype value=module>
<input type=hidden name=type value=tools>
<input type=hidden name=billingtypes value=on>
<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_submit\">
</FORM>
";
?>
<p>
<!--
A frequency of zero is a one time charge, all other frequency are in number of months between recurring charges, 1 = monthly, 2 = 
bi-monthly, etc.
-->
</body>
</html>
