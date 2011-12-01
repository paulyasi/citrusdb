<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

  // print the customer edit links
  echo "<a href=\"customer/edit\">".
    "[ " . lang('editcustomer') . "]</a>";
  if ($cancel_date) {
    echo "<a href=\"customer/uncancel\">".
      "[ " . lang('uncancelcustomer') . " ]</a>";
  } else {
    echo "<a href=\"customer/cancel\">".
      "[ " . lang('cancelcustomer') . " ]</a>";
   }
?>
<table cellpadding=0 border=0 cellspacing=1 width=719>
<td valign=top width=259 style="background-color: #dde;">
<table cellpadding=4 cellspacing=0 border=0 width=259>
  
<tr>
<td style="font-size: 12pt;"><?php echo $name?></td>

<tr>
<td style="font-size: 12pt;"><?php echo $company?></td>
  
<tr>
<td><?php echo $street?></td>
  
<tr>
<td><?php echo "$city $state $zip";?></td>
  
<tr>
<td><?php echo lang('phone') . ": " . $phone;?></td>
  
<tr>
<td><?php echo lang('alt_phone') . ": " . $alt_phone;?></td>
  
<tr>
<td><?php echo lang('fax') . ": " . $fax;?></td>

<tr>
<td><?php echo lang('notes') . ": " . $notes;?></td>

</table></td><td valign=top width=360 style="background-color: #dde;">
<table cellpadding=3 cellspacing=0 border=0 width=360>

<td width=160><b><?php echo lang('signupdate');?></b></td>
<td width=200><?php echo $signup_date?></td><tr>

<td valign=top><b><?php echo lang('canceldate');?></b></td>
<td class=redbold><?php echo $cancel_date?><br> <?php echo $cancel_reason?></td><tr>
  
<td><b><?php echo lang('source');?></b></td>
<td><?php echo $source?></td><tr>
  
<td><b><?php echo lang('contactemail');?></b></td>
<td><?php echo $contactemail?></td><tr>
  
<td><b><?php echo lang('secret_question');?></b></td>
<td><?php echo $secret_question?></td><tr>
  
<td><b><?php echo lang('secret_answer');?></b></td>
<td><?php echo $secret_answer?></td><tr>
 
<td><b><?php echo lang('defaultbillingid');?></b></td>
<td><?php echo $default_billing_id?></td><tr>
  
<td><b><?php echo lang('country');?></b></td>
<td><?php echo $country?></td><tr>
  
<td><b><?php echo lang('acctmngrpasswd');?></b></td>

<?php
  if ($account_manager_password) { 
    echo "<td><a href=\"" . $this->ssl_url_prefix . "/index.php/customer/resetamp\">" . lang('reset') . "</a></td><tr>";
  } else {
    echo "<td><a href=\"" . $this->ssl_url_prefix . "/index.php/customer/resetamp\">" . lang('notset') . "</a></td><tr>";
  }
?>

</table></td></table></form>
<p>
 
