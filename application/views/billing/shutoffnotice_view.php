<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed')?>
<br><br>
<table cellpadding=15 cellspacing=0 border=0 width=520><td><center>
<form style=\"margin-bottom:0;\" action=\"index.php\" name=\"form1\" method=post>

$l_send_shutoff_notice_question $human_shutoff? <p>
<input type=hidden name=cancel_date value=$cancel_date>
<input type=hidden name=turnoff_date value=$turnoff_date>

<input type=hidden name=load value=billing>
<input type=hidden name=type value=module>
<input type=hidden name=shutoffnotice value=on>
<input type=hidden name=billing_id value=$billing_id>

<input name=save type=submit value=\" $l_yes \" class=smallbutton></form></center></td>
</td><td><form style=\"margin-bottom:0;\" action=\"index.php\" method=post>
<input name=done type=submit value=\" $l_no  \" class=smallbutton>
<input type=hidden name=load value=billing>
<input type=hidden name=type value=module>
</form></td></table>
