
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<h3>$l_exportcreditcards</h3>
<SCRIPT LANGUAGE="JavaScript" SRC="include/CalendarPopup.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
var cal = new CalendarPopup();
</SCRIPT>
<FORM ACTION="<?php echo $ssl_url_prefix?>/index.php/tools/saveexportcc" METHOD="POST" name="form1" onsubmit="toggleOn();" AUTOCOMPLETE="off">
<table>
<?php
// print list of organizations to choose from
$query = "SELECT id,org_name FROM general";
$result = $this->db->query($query) or die ("$l_queryfailed");
echo "<td><b>".lang('organizationname')."</b></td>
<td><select name=\"organization_id\">
<option value=\"\">".lang('choose')."</option>";
foreach ($result->result_array() as $myresult) {
	$myid = $myresult['id'];
	$myorg = $myresult['org_name'];
	echo "<option value=\"$myid\">$myorg</option>";
}
?>
</select></td><tr>

<td><?php echo lang('whatdatewouldyouliketobill')?>:</td>
<td><input type=text name=billingdate value="YYYY-MM-DD" size=12>
<A HREF="#" 
onClick="cal.select(document.forms['form1'].billingdate
,'anchor1','yyyy-MM-dd'); return false;"
NAME="anchor1" ID="anchor1" style="color:blue">[<?php echo lang('select')?>]</A>
</td><tr>
<td align=right><?php echo lang('passphrase')?>:</td><td><input type=password name=passphrase></td><tr>
<td></td><td><INPUT TYPE="SUBMIT" NAME="submit" value="<?php echo lang('submitrequest')?>">
</td>
</form>
</table><br><br><br>

// print the date range form
echo "<FORM ACTION=\"$form_action_url\" METHOD=\"POST\" name=\"form2\" onsubmit=\"toggleOn();\" AUTOCOMPLETE=\"off\">
<input type=hidden name=load value=exportcc>
<input type=hidden name=type value=tools>
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
</td><tr>
<td align=right>$l_passphrase:</td><td><input type=password name=passphrase></td><tr>
<td></td><td><INPUT TYPE=\"SUBMIT\" NAME=\"submit\" value=\"$l_submitrequest\">
</td>
</form>
</table><p>";

// print the link to the fixexportcc
echo "<a href=\"index.php?load=fixexportcc&type=tools\">$l_exportpreviousbatchid</a><p>";

// print the WaitingMessage
echo "<div id=\"WaitingMessage\" style=\"border: 0px double black; ".
"background-color: #fff; position: absolute; text-align: center; ".
"top: 50px; width: 550px; height: 300px;\">".
"<BR><BR><BR><h3>$l_processing...</h3>".
"<p><img src=\"images/spinner.gif\"></p>".
"</div>";	
}

?>

