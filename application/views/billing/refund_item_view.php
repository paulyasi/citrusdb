<FORM ACTION="<?php echo $this->url_prefix;?>/index.php/" METHOD="POST">
<input type=hidden name=load value=refund>
<input type=hidden name=type value=tools>
<input type=hidden name=refundnow value=on>
<input type=hidden name=method value=\"$method\">
<input type=hidden name=detailid value=\"$detailid\">
<input type=hidden name=billingid value=\"$billingid\">";

<p><table>
<td><b>$l_id</b></td><td>$id</td><tr>
<td><b>$l_date</b></td><td>$date</td><tr>
<td><b>$l_description</b></td><td>$description</td><tr>
<td><b>$l_invoice</b></td><td>$invoice</td><tr>
<td><b>$l_billedamount</b></td><td>$billedamount</td><tr>
<td><b>$l_paidamount</b></td><td>$paidamount</td></tr>
<td><b>$l_refundamount</b></td>
<td><input type=text name=\"refundamount\" value=\"$refundamount\">
</td><tr>	
<td></td>
<td><INPUT TYPE=\"SUBMIT\" NAME=\"submit\" value=\"$l_submitrequest\"></td>
</table></form>";

