<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>

<br><br>
<h4>&nbsp;&nbsp;&nbsp; <?php echo lang('areyousurensf')." ".$amount;?></h4>
<table cellpadding=15 cellspacing=0 border=0 width=720>
<td align=right width=360>

<form style="margin-bottom:0;" 
action="<?php echo $this->url_prefix?>/index.php/billing/savensf" 
method=post>
<input type=hidden name=paymentid value="<?php echo $paymentid?>">
<input type=hidden name=amount value="<?php echo $amount?>">
<input type=hidden name=invoicenum value="<?php echo $invoice_number?>">
<input type=hidden name=billingid value="<?php echo $billingid?>">
<input name=save type=submit value=" <?php echo lang('yes')?> " class=smallbutton></form></td>
<td align=left width=360><form style="margin-bottom:0;" 
action="<?php echo $this->url_prefix?>/index.php/billing" method=post>
<input name=done type=submit value=" <?php echo lang('no')?>  " class=smallbutton>
</form></td></table>
