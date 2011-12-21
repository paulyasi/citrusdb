<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>

<a href="<?php echo $this->ssl_url_prefix; ?>
/index.php/billing/edit/<?php echo $billing_id;?>">
[ <?php echo lang('editdefaultbilling');?> ]</a>

<a href="<?php echo $this->url_prefix?>/index.php/billing/resetaddr/<?php echo $this->account_number;?>">[ <?php echo lang('resetaddresstocustomer');?> ]</a>
<a href=<?php echo $this->url_prefix?>/index.php/billing/rerun/<?php echo $billing_id;?>>[ <?php echo lang('rerun');?> ]</a>

<?php
if (($userprivileges['manager'] == 'y') OR ($userprivileges['admin'] == 'y')) {
	echo "<br><a href=\"$this->url_prefix/index.php/billing/invmaint/$billing_id\">".lang('invoicemaintenance')."</a> | 
	<a href=\"$this->url_prefix/index.php/billing/refund/$billing_id\">".lang('refundreport')."</a> | 
	<a href=\"$this->url_prefix/index.php/billing/turnoff/$billing_id\">".lang('turnoff')."</a> | 
	<a href=\"$this->url_prefix/index.php/billing/waiting/$billing_id\">".lang('waiting')."</a> |
	<a href=\"$this->url_prefix/index.php/billing/authorized/$billing_id\">".lang('authorized')."</a> | 
	<a href=\"$this->url_prefix/index.php/billing/cancelwfee/$billing_id\">".lang('cancelwithfee')."</a> |
<a href=\"$this->url_prefix/index.php/billing/collections/$billing_id\">".lang('collections')."</a> |

	<a href=\"$this->url_prefix/index.php/billing/createinvoice/$billing_id\">".lang('createinvoice')."</a> | 
	<a href=\"$this->url_prefix/index.php/billing/cancelnotice/$billing_id\">".lang('cancel_notice')."</a> | 
	<a href=\"$this->url_prefix/index.php/billing/shutoffnotice/$billing_id\">".lang('shutoff_notice')."</a> | 
	<a href=\"$this->url_prefix/index.php/billing/collectionsnotice/$billing_id\">".lang('collections_notice')."</a>
";
}
?>
<h3><?php echo lang('organizationname');?>: <?php echo $organization_name?></h3>

<table cellpadding=0 border=0 cellspacing=0 width=720>
<td valign=top width=360>		
	<table cellpadding=5 border=0 cellspacing=1 width=360>
	<td bgcolor="#ccccdd" width=180><b>ID</b></td><td bgcolor="#ddddee" width=180><?php echo $billing_id;?></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('name')?></b></td><td bgcolor="#ddddee"><?php echo $name;?></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('company')?></b></td><td bgcolor="#ddddee"><?php echo $company;?></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('street')?></b></td><td bgcolor="#ddddee"><?php echo $street;?></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('city')?></b></td><td bgcolor="#ddddee"><?php echo $city;?></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('state')?></b></td><td bgcolor="#ddddee"><?php echo $state;?></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('zip')?></b></td><td bgcolor="#ddddee"><?php echo $zip;?></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('country')?></b></td><td bgcolor="#ddddee"><?php echo $country;?></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('phone')?></b></td><td bgcolor="#ddddee"><?php echo $phone;?></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('fax')?></b></td><td bgcolor="#ddddee"><?php echo $fax;?></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('contactemail')?></b></td><td bgcolor="#ddddee">
	 <?php echo mailto($contact_email,$contact_email);?></td><tr>
	</table>
</td>
<td valign=top width=360>		
	<table cellpadding=5 border=0 cellspacing=1 width=360>
	<td bgcolor="#ccccdd"><b><?php echo lang('billingstatus')?></b></td><td bgcolor="#ddddee"><b><?php echo $mystatus;?></b></td><tr>
	<td bgcolor="#ccccdd" width=180><b><?php echo lang('billingtype')?></b></td><td bgcolor="#ddddee" width=180><?php echo $billing_type_name;?></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('ccnumber')?></b></td><td bgcolor="#ddddee"><?php echo $creditcard_number;?></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('ccexpire')?></b></td><td bgcolor="#ddddee"><?php echo $creditcard_expire;?></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('nextbillingdate')?></b></td><td bgcolor="#ddddee"><?php echo $next_billing_date;?></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('from')." ".lang('date');?></b></td><td bgcolor="#ddddee"><?php echo $from_date;?></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('to')." ".lang('date');?></b></td><td bgcolor="#ddddee"><?php echo $to_date;?></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('paymentduedate')?></b></td><td bgcolor="#ddddee"><?php echo $payment_due_date;?></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('rerun')." ".lang('date');?></b></td><td bgcolor="#ddddee"><?php echo $rerun_date;?></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('po_number')?></b></td><td bgcolor="#ddddee"><?php echo $po_number;?></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('notes')?></b></td><td bgcolor="#ddddee"><?php echo $notes;?></td>
	</table>
</td>
</table>
<p>

