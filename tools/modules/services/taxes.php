<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_taxes</h3>

[ <a href=\"index.php?load=services&tooltype=module&type=tools&tax=on\">$l_taxrates</a> ] 
[ <a href=\"index.php?load=services&tooltype=module&type=tools&tax=on&taxedservices=on\">$l_taxedservices</a> ]";
	
// Copyright (C) 2002-2004  Paul Yasi <paul@citrusdb.org>, read the README file for more information
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
if (!isset($base->input['description'])) { $base->input['description'] = ""; }
if (!isset($base->input['rate'])) { $base->input['rate'] = ""; }
if (!isset($base->input['if_field'])) { $base->input['if_field'] = ""; }
if (!isset($base->input['if_value'])) { $base->input['if_value'] = ""; }
if (!isset($base->input['percentage_or_fixed'])) { $base->input['percentage_or_fixed'] = ""; }
if (!isset($base->input['id'])) { $base->input['id'] = ""; }
if (!isset($base->input['taxedservices'])) { $base->input['taxedservices'] = ""; }

$submit = $base->input['submit'];
$description = $base->input['description'];
$rate = $base->input['rate'];
$if_field = $base->input['if_field'];
$if_value = $base->input['if_value'];
$percentage_or_fixed = $base->input['percentage_or_fixed'];
$id = $base->input['id'];
$taxedservices = $base->input['taxedservices'];

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
        echo "$l_youmusthaveadmin";
        exit;
}

if ($taxedservices)
{
	// edit links from services to taxes assigned to them
	include('taxedservices.php');
}
else
{
// show all taxes for editing

if ($submit) {
	// then we add a new tax
	$query = "INSERT INTO tax_rates (description,rate,if_field,if_value,".
	  "percentage_or_fixed) 
	VALUES ('$description','$rate','$if_field','$if_value',".
	  "'$percentage_or_fixed')";
	
	$result = $DB->Execute($query) or die ("$l_queryfailed");

	print "<h3>$l_changessaved</h3> 
	[<a href=\"index.php?load=services&tooltype=module&type=tools\">$l_done</a>]";
}

if ($delete) {
	// then we delete a tax
	$query = "DELETE FROM tax_rates WHERE id = '$id'";
	$result = $DB->Execute($query) or die ("Query Failed");	

	print "<h3>$l_changessaved</h3> 
	[<a href=\"index.php?load=services&tooltype=module&type=tools\">$l_done</a>]";
}

echo "<p><b>$l_taxrates</b><p>";

$query = "SELECT * FROM tax_rates";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");

echo '<table cellpadding=5 cellspacing=1><tr bgcolor="#eeeeee">';
echo "<td><b>$l_id</b></td> <td><b>$l_description</b></td> <td><b>$l_rate</b></td> <td><b>$l_iffield</b></td><td><b>$l_ifvalue</b></td><td><b>$l_percentage_or_fixed</b></td> <td></td>
</tr>";

while ($myresult = $result->FetchRow())
{
	$id = $myresult['id'];
        $desc = $myresult['description'];
        $rate = $myresult['rate'];
	$if_field = $myresult['if_field'];
	$if_value = $myresult['if_value'];
	$percentage_or_fixed = $myresult['percentage_or_fixed'];
	print "<tr bgcolor=\"#eeeeee\"><td>$id</td><td>$desc</td><td>$rate</td><td>$if_field</td><td>$if_value</td><td>$percentage_or_fixed</td>";
	print "<td><a href=\"index.php?load=services&tooltype=module&type=tools&tax=on&delete=on&id=$id&delete=Delete\">$l_delete</a></td></tr>\n";
}

echo "</table><p>
<b>$l_add:</b><br>
<FORM ACTION=\"index.php\" METHOD=\"GET\">
$l_description: <input type=text name=\"description\"><br>
$l_rate: <input type=text name=\"rate\"><br>
$l_iffield <input type=text name=\"if_field\"><br>
$l_ifvalue <input type=text name=\"if_value\"><br>
$l_percentage_or_fixed
<select name=\"percentage_or_fixed\">
<option value=\"percentage\">percentage</option>
<option value=\"fixed\">fixed</option>
</select>
<br>
<input type=hidden name=load value=services>
<input type=hidden name=tooltype value=module>
<input type=hidden name=type value=tools>
<input type=hidden name=tax value=on>
<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_add\">
</FORM>
<p>";

}
?>

</body>
</html>
