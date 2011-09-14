<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed')?>
<?php
if ($cancel_date <> '') 
{
	print "<br><br>";
	print "<h4>".lang('areyousurecancelwfee')."</h4>";
	print "<table cellpadding=15 cellspacing=0 border=0 width=720><td align=right width=360>";
	print "<form style=\"margin-bottom:0;\" action=\"$this->url_prefix/index.php/billing/savecancelwfee\" method=POST>";
	print "<input type=hidden name=billing_id value=$billing_id>";
	print "<input name=save type=submit value=\" ".lang('yes')." \" class=smallbutton></form></td>";
	print "<td align=left width=360><form style=\"margin-bottom:0;\" action=\"$this->url_prefix/index.php/billing\" method=post>";
	print "<input name=done type=submit value=\" ".lang('no')."  \" class=smallbutton>";
	print "<input type=hidden name=load value=billing>";
	print "<input type=hidden name=type value=module>";
	print "</form></td></table>";
} 
else 
{ 
	echo "<p><br><b>".lang('error_account_not_canceled')."</b><br><br>";
} 
?>
