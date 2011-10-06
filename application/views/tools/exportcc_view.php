<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<h3><?php echo lang('exportcreditcards')?></h3>
<SCRIPT LANGUAGE="JavaScript" SRC="include/CalendarPopup.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
var cal = new CalendarPopup();
</SCRIPT>
<table>
<td><b><?php echo lang('organizationname')?></b></td>
<td><select name="organization_id">
<option value=""><?php echo lang('choose')?></option>
<?php
foreach ($orglist as $myresult) {
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

<FORM ACTION="<?php echo $this->ssl_url_prefix?>/index.php/tools/saveexportcc" METHOD="POST" name="form2" onsubmit="toggleOn();" AUTOCOMPLETE="off">
<table>

<td><b><?php echo lang('organizationname')?></b></td>
<td><select name="organization_id">
<option value=""><?php echo lang('choose')?></option>
<?php
foreach ($orglist as $myresult) {
	$myid = $myresult['id'];
	$myorg = $myresult['org_name'];
	echo "<option value=\"$myid\">$myorg</option>";
}
?>
</select></td><tr>

<td><?php echo lang('whatdatewouldyouliketobill')?>:</td>
<td><input type=text name=billingdate1 value="YYYY-MM-DD" size=12>
<A HREF="#" onClick="cal.select(document.forms['form2'].billingdate1
,'anchorb1','yyyy-MM-dd'); return false;"
NAME="anchorb1" ID="anchorb1" style="color:blue">[<?php echo lang('select')?>]</A>
</td> 
<td> to <input type=text name=billingdate2 value="YYYY-MM-DD" size=12>
<A HREF="#" 
onClick="cal.select(document.forms['form2'].billingdate2
,'anchorb2','yyyy-MM-dd'); return false;"
NAME="anchorb2" ID="anchorb2" style="color:blue">[<?php echo lang('select')?>]</A>
</td><tr>
<td align=right><?php echo lang('passphrase')?>:</td><td>
<input type=password name=passphrase></td><tr>
<td></td><td><INPUT TYPE="SUBMIT" NAME="submit" value="<?php echo lang('submitrequest')?>">
</td>
</form>
</table><p>

<a href="<?php echo $this->ssl_url_prefix?>/index.php/tools/fixexportcc">
<?php echo lang('exportpreviousbatchid')?></a><p>

<div id="WaitingMessage" style="border: 0px double black; 
background-color: #fff; position: absolute; text-align: center; top: 50px; width: 550px; height: 300px;">
<BR><BR><BR><h3><?php echo lang('processing')?>...</h3>
<p><img src="images/spinner.gif"></p>
</div>	

