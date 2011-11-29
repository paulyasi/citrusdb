<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<h3><?php echo lang('exportpreviousbatchid')?></h3>

<FORM ACTION="<?php echo $this->ssl_url_prefix?>/index.php/tools/billing/savefixexportcc" METHOD="POST" name="form1" onsubmit="toggleOn();" AUTOCOMPLETE="off">
<table>
<td><b><?php echo lang('organizationname')?></b></td>
<td><select name="organization_id">
<option value=""><?php echo lang('choose')?></option>
<?php
foreach ($orglist AS $myresult) 
{
	$myid = $myresult['id'];
	$myorg = $myresult['org_name'];
	echo "<option value=\"$myid\">$myorg</option>";
}
?>
</select></td><tr>

<td>batch to fix:</td>
<td><input type=text name=batchid value="" size=5>
</td><tr>
<td align=right><?php echo lang('passphrase')?>:</td><td><input type=password name=passphrase></td><tr>
<td></td><td><INPUT TYPE="SUBMIT" NAME="submit" value="<?php echo lang('submitrequest')?>">
</td>
</form>
</table><br><br><br>

<div id="WaitingMessage" style="border: 0px double black; 
background-color: #fff; position: absolute; text-align: center; top: 0px; width: 550px; height: 400px;">
<BR><BR><BR><h3><?php echo lang('processing')?>...</h3>
<p><img src="<?php echo $this->ssl_url_prefix?>/images/spinner.gif"></p>
</div>	
