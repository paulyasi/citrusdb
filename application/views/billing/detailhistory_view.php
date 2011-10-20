<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<table cellspacing=0 cellpadding=4 border=0>
<td bgcolor="#dddddd" width=100><b><?php echo lang('id')?></b></td>
<td bgcolor="#dddddd" width=130><b><?php echo lang('date')?></b></td>
<td bgcolor="#dddddd" width=200><b><?php echo lang('description')?></b></td>
<td bgcolor="#dddddd" width=100><b><?php echo lang('invoice')?>(<?php echo lang('original')?>)</b></td>
<td bgcolor="#dddddd" width=100><b><?php echo lang('billingid')?></b></td>
<td bgcolor="#dddddd" width=100><b><?php echo lang('from')?></b></td>
<td bgcolor="#dddddd" width=100><b><?php echo lang('to')?></b></td>
<td bgcolor="#dddddd" width=100><b><?php echo lang('duedate')?></b></td>
<td bgcolor="#dddddd" width=100><b><?php echo lang('paid')?></b></td>
<td bgcolor="#dddddd" width=100><b><?php echo lang('billedamount')?></b></td>
<td bgcolor="#dddddd" width=150><b><?php echo lang('paidamount')?></b></td>

<?php
foreach ($history AS $myresult) 
{
  $id = $myresult['d_id'];
  $date = $myresult['d_creation_date'];
  if ($myresult['d_taxed_services_id']) 
  { 
    // it's a tax
    $description = $myresult['r_description'];
  } 
  else 
  {
    // it's a service
    $description = $myresult['m_description'];
  }

  $invoice = $myresult['d_invoice_number'];
  $billedamount = sprintf("%.2f",$myresult['d_billed_amount']);
  $paidamount = sprintf("%.2f",$myresult['d_paid_amount']);
  $refunded = $myresult['d_refunded'];
  $refundamount = sprintf("%.2f",$myresult['d_refund_amount']);
  $rerun = $myresult['d_rerun'];
  $original_invoice = $myresult['d_original_invoice'];
  $billing_id = $myresult['d_billing_id'];
  $from_date = $myresult['bh_from_date'];
  $to_date = $myresult['bh_to_date'];
  $due_date = $myresult['bh_due_date'];
  $payment_date = $myresult['ph_creation_date'];

  print "<tr style=\"font-size: 9pt;\" bgcolor=\"#eeeeee\">";
  print "<td style=\"border-top: 1px solid grey;\">$id &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey;\">$date &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey;\">$description &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey;\">[ <a href=\"$this->url_prefix/index.php/tools/billing/htmlpreviousinvoice/$this->account_number/$invoice\" target=\"_blank\">$invoice</a> ]($original_invoice)</td>";	

  print "<td style=\"border-top: 1px solid grey;\">$billing_id</td>";
  print "<td style=\"border-top: 1px solid grey;\">$from_date</td>";
  print "<td style=\"border-top: 1px solid grey;\">$to_date</td>";
  print "<td style=\"border-top: 1px solid grey;\">$due_date</td>";
  print "<td style=\"border-top: 1px solid grey;\">$payment_date&nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey;\">$billedamount &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey;\">$paidamount &nbsp;";

  // check for refund
  if ($refunded == 'y') 
  {
    echo "<i>".lang('refunded')." $refundamount</i>";
  }

  // check for rerun
  if ($rerun == 'y') 
  {
    echo "<i>".lang('rerun')."</i>";
  }  

  echo "</td>";

 } // end loop

?>

<tr bgcolor="#dddddd"><td style="padding: 5px;" colspan=6><a href="
<?php echo $this->url_prefix?>/index.php/billing/detailhistory/all">
<?php lang('showall')?>...</a></td>
</table>

</body>
</html>
