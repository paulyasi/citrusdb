<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<h4>Replace the ciphertext<br>(in ascii armor format)</h4>
<table cellpadding=15 cellspacing=0 border=0 width=620>
<td align=center width=360>
<form style="margin-bottom:0;" 
action="<?php echo $this->ssl_url_prefix?>/index.php/billing/saveasciiarmor" method=post>
<textarea name=encrypted cols=70 rows=20><?php echo $encrypted_creditcard_number?></textarea><br>
<?php echo lang('masked_ccnumber')?>: 
<input type=text name=creditcard_number value="<?php echo $creditcard_number?>"><br>
<input type=text name=creditcard_expire value="<?php echo $creditcard_expire?>"><br>
<input type=hidden name=billing_id value=<?php echo $billing_id?>>
<input name=save type=submit value=" <?php echo lang('replace')?> " class=smallbutton>
</form>
<br><form style="margin-bottom:0;" action="<?php echo $this->url_prefix?>/index.php/billing" method=post>
<input name=done type=submit value=" <?php echo lang('cancel')?>  " class=smallbutton>
<input type=hidden name=load value=billing>
<input type=hidden name=type value=module>
</form></td></table>
