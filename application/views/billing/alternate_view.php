<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
[ <a href="<?php echo $this->url_prefix; ?>index.php/billing/addbilling">
<?php echo lang('addaltbilling');?></a> ]<br>

<table width=720><tr bgcolor="#ddddee">
<?php 
foreach ($alternate as $myresult)
{
	$billing_id = $myresult['b_id'];
	$billing_type = $myresult['t_name'];
	$billing_orgname = $myresult['g_org_name'];

	$mystatus = $this->billing_model->billingstatus($billing_id);

	$alternate_billing_id_url = $this->ssl_url_prefix . "index.php/billing/edit/$billing_id";

	print "<td><b>$billing_orgname</b> &nbsp;<a
href=\"$alternate_billing_id_url\">$billing_id</a></td><td>$billing_type</td><td>$mystatus</td>";

	// TODO: check if they are billing or manager and show the maintenance and rerun type links
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

?>