<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<SCRIPT LANGUAGE="JavaScript" SRC="include/CalendarPopup.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
var cal = new CalendarPopup();
</SCRIPT>

// ask for the billing date that they want to invoice
echo "<h3>$l_emailinvoices</h3>";
echo "<FORM ACTION=\"index.php/tools/sendeinvoice\" METHOD=\"GET\" name=\"form1\">
<input type=hidden name=load value=billing>
<input type=hidden name=tooltype value=module>
<input type=hidden name=type value=tools>
<input type=hidden name=einvoice value=on>
<table>";
// print list of organizations to choose from
$query = "SELECT id,org_name FROM general";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
echo "<td><b>$l_organizationname</b></td>
<td><select name=\"organization_id\">
<option value=\"\">$l_choose</option>";
while ($myresult = $result->FetchRow()) {
	$myid = $myresult['id'];
	$myorg = $myresult['org_name'];
	echo "<option value=\"$myid\">$myorg</option>";
}
echo "</select></td><tr>

<td>$l_whatdatewouldyouliketobill:</td>
<td><input type=text name=billingdate value=\"YYYY-MM-DD\">
<A HREF=\"#\"
onClick=\"cal.select(document.forms['form1'].billingdate,'anchor1','yyyy-MM-dd'); 
return false;\"
NAME=\"anchor1\" ID=\"anchor1\" style=\"color:blue\">[$l_select]</A>
</td><tr>
<td></td><td><INPUT TYPE=\"SUBMIT\" NAME=\"submit\" value=\"$l_submit\"></td>
</table></form>";

// print the date range form
echo "<FORM ACTION=\"index.php\" METHOD=\"GET\" name=\"form2\" onsubmit=\"toggleOn();\">
<input type=hidden name=load value=billing>
<input type=hidden name=tooltype value=module>
<input type=hidden name=type value=tools>
<input type=hidden name=einvoice value=on>
<table>";
// print list of organizations to choose from
$query = "SELECT id,org_name FROM general";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
echo "<td><b>$l_organizationname</b></td>
<td><select name=\"organization_id\">
<option value=\"\">$l_choose</option>";
while ($myresult = $result->FetchRow()) {
	$myid = $myresult['id'];
	$myorg = $myresult['org_name'];
	echo "<option value=\"$myid\">$myorg</option>";
}

echo "</select></td><tr>
<td>$l_whatdatewouldyouliketobill:</td>
<td><input type=text name=billingdate1 value=\"YYYY-MM-DD\" size=12>
<A HREF=\"#\" 
onClick=\"cal.select(document.forms['form2'].billingdate1
,'anchorb1','yyyy-MM-dd'); return false;\"
NAME=\"anchorb1\" ID=\"anchorb1\" style=\"color:blue\">[$l_select]</A>
</td> 
<td> to <input type=text name=billingdate2 value=\"YYYY-MM-DD\" size=12>
<A HREF=\"#\" 
onClick=\"cal.select(document.forms['form2'].billingdate2
,'anchorb2','yyyy-MM-dd'); return false;\"
NAME=\"anchorb2\" ID=\"anchorb2\" style=\"color:blue\">[$l_select]</A>
</td>

<tr>
<td></td><td><INPUT TYPE=\"SUBMIT\" NAME=\"submit\" value=\"$l_submitrequest\">
</td>
</form>
</table><p>";

// or ask the the single billing ID they want to invoice
echo "<p>$l_or<p><FORM ACTION=\"index.php\" METHOD=\"GET\" onsubmit=\"toggleOn();\">
<input type=hidden name=load value=billing>
<input type=hidden name=tooltype value=module>
<input type=hidden name=type value=tools>
<input type=hidden name=einvoice value=on>
<table>
<td></td><td>$l_billingid</td><tr>
<td>$l_whatidwouldyouliketobill:</td><td><input type=text name=billingid>
</td><tr>
<td></td><td><INPUT TYPE=\"SUBMIT\" NAME=\"submit\" value=\"$l_submit\"></td>
</table></form>";

// or ask what customer id they want to invoice (uses the default_billing_id)
echo "<p>$l_or<p><FORM ACTION=\"index.php\" METHOD=\"GET\" onsubmit=\"toggleOn();\">
<input type=hidden name=load value=billing>
<input type=hidden name=tooltype value=module>
<input type=hidden name=type value=tools>
<input type=hidden name=einvoice value=on>	
<table>
<td></td><td>$l_accountnumber</td><tr>
<td>$l_whataccountnumberwouldyouliketobill:</td><td><input type=text name=acctnum>
</td><tr>
<td></td><td><input type=\"submit\" name=\"submit\" value=\"$l_submit\"></td>
</table></form>";

// print the WaitingMessage
echo "<div id=\"WaitingMessage\" style=\"border: 0px double black; ".
"background-color: #fff; position: absolute; text-align: center; ".
"top: 50px; width: 550px; height: 300px;\">".
"<BR><BR><BR><h3>$l_processing...</h3>".
"<p><img src=\"images/spinner.gif\"></p>".
"</div>";

