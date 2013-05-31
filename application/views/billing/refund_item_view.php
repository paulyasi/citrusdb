<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed')?>
<FORM ACTION="<?php echo $this->url_prefix;?>/index.php/billing/saverefunditem" METHOD="POST">
<input type=hidden name=method value="<?php echo $method?>">
<input type=hidden name=detailid value="<?php echo $detailid?>">
<input type=hidden name=billingid value="<?php echo $billingid?>">

<p><table>
<td><b><?php echo lang('id')?></b></td><td><?php echo $id?></td><tr>
<td><b><?php echo lang('date')?></b></td><td><?php echo $date?></td><tr>
<td><b><?php echo lang('description')?></b></td><td><?php echo $description?></td><tr>
<td><b><?php echo lang('invoice')?></b></td><td><?php echo $invoice?></td><tr>
<td><b><?php echo lang('billedamount')?></b></td><td><?php echo $billedamount?></td><tr>
<td><b><?php echo lang('paidamount')?></b></td><td><?php echo $paidamount?></td></tr>
<td><b><?php echo lang('refundamount')?></b></td>
<td><input type=text name="refundamount" value="<?php echo $refundamount?>">
</td><tr>	
<td></td>
<td><INPUT TYPE="SUBMIT" NAME="submit" value="<?php echo lang('submitrequest');?>"></td>
</table></form>

