<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

  // print the customer edit links
  echo "<a href=\"index.php/customer/edit\">".
    "[ " . lang('editcustomer') . "]</a>";
  if ($cancel_date) {
    echo "<a href=\"index.php/customer/undelete\">".
      "[ " . lang('uncancelcustomer') . " ]</a>";
  } else {
    echo "<a href=\"index.php/customer/delete\">".
      "[ " . lang('cancelcustomer') . " ]</a>";
   }
?>
<table cellpadding=0 border=0 cellspacing=1 width=719>
<td valign=top width=259 style="background-color: #dde;">
<table cellpadding=4 cellspacing=0 border=0 width=259>
  
<tr>
<td style=\"font-size: 12pt;\"><?=$name?></td>

<tr>
<td style=\"font-size: 12pt;\"><?=$company?></td>
  
<tr>
<td><?=$street?></td>
  
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
<td width=200><?=$signup_date?></td><tr>

<td valign=top><b><?php echo lang('canceldate');?></b></td>
<td class=redbold><?=$cancel_date?><br> <?=$cancel_reason?></td><tr>
  
<td><b><?php echo lang('source');?></b></td>
<td><?=$source?></td><tr>
  
<td><b><?php echo lang('contactemail');?></b></td>
<td><?=$contactemail?></td><tr>
  
<td><b><?php echo lang('secret_question');?></b></td>
<td><?=$secret_question?></td><tr>
  
<td><b><?php echo lang('secret_answer');?></b></td>
<td><?=$secret_answer?></td><tr>
 
<td><b><?php echo lang('defaultbillingid');?></b></td>
<td><?=$default_billing_id?></td><tr>
  
<td><b><?php echo lang('country');?></b></td>
<td><?=$country?></td><tr>
  
<td><b><?php echo lang('acctmngrpasswd');?></b></td>

<?php
  if ($account_manager_password) { 
    echo "<td><a href=\"" . $this->url_prefix . "index.php/customer/resetamp\">" . lang('reset') . "</a></td><tr>";
  } else {
    echo "<td><a href=\"" . $this->url_prefix . "index.php/customer/resetamp\">" . lang('notset') . "</a></td><tr>";
  }
?>

</table></td></table></form>
<p>
 
