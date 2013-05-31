<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/billing/savepayment" METHOD="POST">
<?php echo lang('enteroneofthesethreevalues')?>:
<table><td>
<b><?php echo lang('accountnumber')?>:</b></td><td>
<input type="text" name="account_num" size="20" maxlength="32">
(<?php echo lang('applytodefaultbillingid')?>)
</td><tr><td>
<B><?php echo lang('billingid')?>:</B></td><td>
<INPUT TYPE="TEXT" NAME="billing_id" SIZE="20" MAXLENGTH="32">
(<?php echo lang('applytospecificbillingid')?>)
</td><tr><td>
<B><?php echo lang('invoicenumber')?>:</B></td><td>
<INPUT TYPE="TEXT" NAME="invoice_number" SIZE="20" MAXLENGTH="32" value="<?php echo $invoice_number?>">
(<?php echo lang('applytospecificinvoice')?>)
</td><tr><td>
&nbsp;
</td><tr><td>
<B><?php echo lang('amount')?>:</B></td><td>
<INPUT TYPE="TEXT" NAME="amount" SIZE="20" MAXLENGTH="32" value="<?php echo $amount?>">
</td><tr><td>
<B><?php echo lang('type')?></B></td><td>
<select name="payment_type">
<?php echo $payment_options?>	
</select>
</td><tr><td>
<b><?php echo lang('checknumber')?>:</b></td>
<td><input type="text" name="check_number" size="15"></td>
<tr>
<td></td><td>
<INPUT TYPE="SUBMIT" NAME="submit" value="<?php echo lang('submit')?>">
</td></table>
</FORM>

