<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">
<h3>$l_editbillingtypes</h3>";

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
<?php
foreach ($billingtypes AS $myresult)
{
	$id = $myresult['id'];
        $name = $myresult['name'];
        $frequency = $myresult['frequency'];
	$method = $myresult['method'];
	print "<tr bgcolor=\"#eeeeee\"><td>$id</td><td>$name</td><td>$frequency</td><td>$method</td><td><a href=\"index.php?load=billing&tooltype=module&type=tools&billingtypes=on&remove=on&typeid=$id&submit=Link\">$l_remove</a></td></tr>\n";
}
?>
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
