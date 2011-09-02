<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>

<a href="<?php echo $this->ssl_url_prefix; ?>
/index.php/billing/edit/<?php echo $billing_id;?>">
[ <?php echo lang('editdefaultbilling');?> ]</a>

<a href="<?php echo $this->url_prefix?>/index.php/billing/resetaddr/<?php echo $this->account_number;?>">[ <?php echo lang('resetaddresstocustomer');?> ]</a>
<a href=index.php?load=billing&type=module&rerun=on&billing_id=$billing_id>[ <?php echo lang('rerun');?> ]</a>

<?php
// query user properties
$query = "SELECT * FROM user WHERE username='$this->user' LIMIT 1";
$userresult = $this->db->query($query) or die ("$l_queryfailed");
$myuserresult = $userresult->row_array();
if (($myuserresult['manager'] == 'y') OR ($myuserresult['admin'] == 'y')) {
	echo "<br><a href=\"index.php?load=invmaint&type=tools&billingid=$billing_id&submit=Submit\">".lang('invoicemaintenance')."</a> | 
	<a href=\"index.php/billing/refund/$billing_id\">".lang('refundreport')."</a> | 
	<a href=\"index.php?load=billing&type=module&turnoff=on&billing_id=$billing_id\">".lang('turnoff')."</a> | 
	<a href=\"index.php?load=billing&type=module&waiting=on&billing_id=$billing_id\">".lang('waiting')."</a> |
	<a href=\"index.php?load=billing&type=module&authorized=on&billing_id=$billing_id\">".lang('authorized')."</a> | 
	<a href=\"index.php?load=billing&type=module&cancelwfee=on&billing_id=$billing_id\">".lang('cancelwithfee')."</a> |
<a href=\"index.php?load=billing&type=module&collections=on&billing_id=$billing_id\">".lang('collections')."</a> |

	<a href=\"index.php?load=billing&type=module&createinvoice=on&billing_id=$billing_id\">".lang('createinvoice')."</a> | 
	<a href=\"index.php?load=billing&type=module&cancelnotice=on&billing_id=$billing_id\">".lang('cancel_notice')."</a> | 
	<a href=\"index.php?load=billing&type=module&shutoffnotice=on&billing_id=$billing_id\">".lang('shutoff_notice')."</a> | 
	<a href=\"index.php?load=billing&type=module&collectionsnotice=on&billing_id=$billing_id\">".lang('collections_notice')."</a>
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
	<td bgcolor="#ccccdd"><b><?php echo lang('contactemail')?></b></td><td bgcolor="#ddddee"><?php echo $contact_email;?></td><tr>
	</table>
</td>
<td valign=top width=360>		
	<table cellpadding=5 border=0 cellspacing=1 width=360>
	<td bgcolor="#ccccdd"><b><?php echo lang('billingstatus')?></b></td><td bgcolor="#ddddee"><b><?php echo $mystatus;?></b></td><tr>
	<td bgcolor="#ccccdd" width=180><b><?php echo lang('billingtype')?></b></td><td bgcolor="#ddddee" width=180><?php echo $billing_type;?></td><tr>
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

