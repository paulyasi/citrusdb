<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>

<a href="<?php echo $this->ssl_url_prefix; ?>
index.php/billing/edit/<?php echo $billing_id;?>">
[ <?php echo lang('editdefaultbilling');?> ]</a>

<a href=index.php?load=billing&type=module&resetaddr=on&account_number=$account_number>[ $l_resetaddresstocustomer ]</a>
<a href=index.php?load=billing&type=module&rerun=on&billing_id=$billing_id>[ $l_rerun ]</a>

<?php
// query user properties
$query = "SELECT * FROM user WHERE username='$user' LIMIT 1";
$userresult = $this->db->query($query) or die ("$l_queryfailed");
$myuserresult = $userresult->row_array();
if (($myuserresult['manager'] == 'y') OR ($myuserresult['admin'] == 'y')) {
	echo "<br><a href=\"index.php?load=invmaint&type=tools&billingid=$billing_id&submit=Submit\">$l_invoicemaintenance</a> | 
	<a href=\"index.php?load=refund&type=tools&billingid=$billing_id&submit=Submit\">$l_refundreport</a> | 
	<a href=\"index.php?load=billing&type=module&turnoff=on&billing_id=$billing_id\">$l_turnoff</a> | 
	<a href=\"index.php?load=billing&type=module&waiting=on&billing_id=$billing_id\">$l_waiting</a> |
	<a href=\"index.php?load=billing&type=module&authorized=on&billing_id=$billing_id\">$l_authorized</a> | 
	<a href=\"index.php?load=billing&type=module&cancelwfee=on&billing_id=$billing_id\">$l_cancelwithfee</a> |
<a href=\"index.php?load=billing&type=module&collections=on&billing_id=$billing_id\">$l_collections</a> |

	<a href=\"index.php?load=billing&type=module&createinvoice=on&billing_id=$billing_id\">$l_createinvoice</a> | 
	<a href=\"index.php?load=billing&type=module&cancelnotice=on&billing_id=$billing_id\">$l_cancel_notice</a> | 
	<a href=\"index.php?load=billing&type=module&shutoffnotice=on&billing_id=$billing_id\">$l_shutoff_notice</a> | 
	<a href=\"index.php?load=billing&type=module&collectionsnotice=on&billing_id=$billing_id\">$l_collections_notice</a>
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
[ <a href="<?php echo $this->ssl_url_prefix; ?>index.php/billing/create">
<?php echo lang('addaltbilling');?></a> ]<br>

<?php 
// print a list of alternate billing id's if any
$query = "SELECT b.id b_id, g.org_name g_org_name, t.name t_name 
FROM billing b 
LEFT JOIN billing_types t ON b.billing_type = t.id 
LEFT JOIN general g ON b.organization_id = g.id 
WHERE b.id != $default_billing_id AND b.account_number = $account_number";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");

echo '<table width=720><tr bgcolor="#ddddee">';
while ($myresult = $result->FetchRow())
{
        $billing_id = $myresult['b_id'];
        $billing_type = $myresult['t_name'];
	$billing_orgname = $myresult['g_org_name'];

	$mystatus = billingstatus($billing_id);

	$alternate_billing_id_url = "$ssl_url_prefix" . "index.php?load=billing&type=module&edit=on&billing_id=$billing_id";

	print "<td><b>$billing_orgname</b> &nbsp;<a
href=\"$alternate_billing_id_url\">$billing_id</a></td><td>$billing_type</td><td>$mystatus</td>";

if (($myuserresult['manager'] == 'y') OR ($myuserresult['admin'] == 'y')) {
  echo "<td>".
    "<a href=\"index.php?load=billing&type=module&rerun=on&billing_id=$billing_id\">$l_rerun</a> | ".
    "<a href=\"index.php?load=invmaint&type=tools&billingid=$billing_id&submit=Submit\">$l_invoicemaintenance</a> | ".
    "<a href=\"index.php?load=refund&type=tools&billingid=$billing_id&submit=Submit\">$l_refund</a>".
    "</td><td>".
    "<form name=status$billing_id style=\"margin-bottom:0;\" method=post>".
    "<select style=\"font-size: 80%;\" name=menu onChange=\"location=document.status$billing_id.menu.options[document.status$billing_id.menu.selectedIndex].value;\" value=GO>".
    "<option value=\"\">$l_changestatus</option>".
    "<option value=\"index.php?load=billing&type=module&turnoff=on&billing_id=$billing_id\">- $l_turnoff</option>".
    "<option value=\"index.php?load=billing&type=module&waiting=on&billing_id=$billing_id\">- $l_waiting</optoin>".
    "<option value=\"index.php?load=billing&type=module&authorized=on&billing_id=$billing_id\">- $l_authorized</option>".
    "<option value=\"index.php?load=billing&type=module&cancelwfee=on&billing_id=$billing_id\">- $l_cancelwithfee</option>".
    "<option value=\"index.php?load=billing&type=module&collections=on&billing_id=$billing_id\">- $l_collections</option>".    
    "</select></form>".
    "</td><td>".
    "<form name=notice$billing_id style=\"margin-bottom:0;\" method=post>".
    "<select style=\"font-size: 80%;\" name=menu onChange=\"location=document.notice$billing_id.menu.options[document.notice$billing_id.menu.selectedIndex].value;\" value=GO>".
    "<option value=\"\">$l_invoiceornotice</option>".
    "<option value=\"index.php?load=billing&type=module&createinvoice=on&billing_id=$billing_id\">- $l_createinvoice</option> | ".
    "<option value=\"index.php?load=billing&type=module&cancelnotice=on&billing_id=$billing_id\">- $l_cancel_notice</option> | ".
    "<option value=\"index.php?load=billing&type=module&shutoffnotice=on&billing_id=$billing_id\">- $l_shutoff_notice</option> | ".
    "<option value=\"index.php?load=billing&type=module&collectionsnotice=on&billing_id=$billing_id\">- $l_collections_notice</option>".
    "</select></form>".
    "</td>";
}

echo "<tr bgcolor=\"#ddddee\">";

}

echo '</table>';


} else permission_error();
?>
