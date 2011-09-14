<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed')?>
<FORM ACTION="<?php echo $this->url_prefix;?>/index.php/saverefunditem" METHOD="POST">
<input type=hidden name=load value=refund>
<input type=hidden name=type value=tools>
<input type=hidden name=refundnow value=on>
<input type=hidden name=method value="$method">
<input type=hidden name=detailid value="$detailid">
<input type=hidden name=billingid value="$billingid">";

<p><table>
<td><b>".lang('id')."</b></td><td>$id</td><tr>
<td><b>".lang('date')."</b></td><td>$date</td><tr>
<td><b>".lang('description')."</b></td><td>$description</td><tr>
<td><b>".lang('invoice')."</b></td><td>$invoice</td><tr>
<td><b>".lang('billedamount')."</b></td><td>$billedamount</td><tr>
<td><b>".lang('paidamount')."</b></td><td>$paidamount</td></tr>
<td><b>".lang('refundamount')."</b></td>
<td><input type=text name="refundamount" value="$refundamount">
</td><tr>	
<td></td>
<td><INPUT TYPE="SUBMIT" NAME="submit" value="<?php echo lang('submitrequest');?>"></td>
</table></form>

