<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed')?>
<br><br>
<table cellpadding=15 cellspacing=0 border=0 width=520><td><center>
<form style="margin-bottom:0;" action="<?php echo $this->url_prefix?>/index.php/billing/savecancelnotice" method=post name="form1">

<?php echo lang('send_cancel_notice_question') . " " . $human_cancel_date;?>? 

<p>

<input type=hidden name=cancel_date value=<?php echo $cancel_date?>>
<input type=hidden name=billing_id value=<?php echo $billing_id?>>

<input name=save type=submit value=" <?php echo lang('yes');?> " class=smallbutton></form></center></td>
</td><td><form style="margin-bottom:0;" action="<?php echo $this->url_prefix?>/index.php/billing" method=post>
<input name=done type=submit value=" <?php echo lang('no');?> " class=smallbutton>
<input type=hidden name=load value=billing>
<input type=hidden name=type value=module>
</form></td></table>
