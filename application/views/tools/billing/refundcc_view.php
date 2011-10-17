<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<br>
<h3><?php echo lang('refund')?></h3>
<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"include/CalendarPopup.js\"></SCRIPT>
<SCRIPT LANGUAGE=\"JavaScript\">
var cal = new CalendarPopup();
</SCRIPT>
<FORM ACTION="<?php echo $this->ssl_url_prefix?>/index.php/tools/saverefundcc" 
METHOD="POST" name="form1" AUTOCOMPLETE="off">
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

<td align=right><?php echo lang('passphrase')?>:</td>
<td><input type=password name=passphrase></td><tr>
<td><?php echo lang('processoutstandingrefunds')?>:</td>
<td><INPUT TYPE="SUBMIT" NAME="submit" value="<?php echo lang('yes')?>"></td>
</form></table><br><br><br>

