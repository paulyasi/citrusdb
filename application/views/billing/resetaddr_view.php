<br><br>
<h4><?php echo lang('areyousuredefaultbillingaddress');?></h4>
<table cellpadding=15 cellspacing=0 border=0 width=720><td align=right width=360>
<form style="margin-bottom:0;" action="<?php echo $this->url_prefix;?>/index.php/billing/saveresetaddr" method=post>
<input type=hidden name=account_number value=$account_number>
<input name=save type=submit value=" <?php echo lang('yes');?> " class=smallbutton>
</form></td>

<td align=left width=360><form style="margin-bottom:0;" action="<?php echo $this->url_prefix?>/index.php/billing" method=post>
<input name=done type=submit value=" <?php echo lang('no');?>  " class=smallbutton>
<input type=hidden name=load value=billing>
<input type=hidden name=type value=module>

</form></td></table>
