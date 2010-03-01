<?php   
// Copyright (C) 2003-2007  Paul Yasi (paul at citrusdb.org)
// read the README file for more information

/*----------------------------------------------------------------------------*/
// Check for authorized accesss
/*----------------------------------------------------------------------------*/
if(constant("INDEX_CITRUS") <> 1){
	echo "You must be logged in to run this.  Goodbye.";
	exit;	
}

if (!defined("INDEX_CITRUS")) {
	echo "You must be logged in to run this.  Goodbye.";
        exit;
}

//Includes
require_once('./include/permissions.inc.php');

if (!isset($base->input['save'])) { $base->input['save'] = ""; }
if (!isset($base->input['resetaddr'])) { $base->input['resetaddr'] = ""; }
if (!isset($base->input['rerun'])) { $base->input['rerun'] = ""; }
if (!isset($base->input['turnoff'])) { $base->input['turnoff'] = ""; }
if (!isset($base->input['cancelwfee'])) { $base->input['cancelwfee'] = ""; }
if (!isset($base->input['collections'])) { $base->input['collections'] = ""; }
if (!isset($base->input['waiting'])) { $base->input['waiting'] = ""; }
if (!isset($base->input['authorized'])) { $base->input['authorized'] = ""; }
if (!isset($base->input['asciiarmor'])) { $base->input['asciiarmor'] = ""; }


// GET Variables
$save = $base->input['save'];
$resetaddr = $base->input['resetaddr'];
$rerun = $base->input['rerun'];
$turnoff = $base->input['turnoff'];
$cancelwfee = $base->input['cancelwfee'];
$collections = $base->input['collections'];
$waiting = $base->input['waiting'];
$authorized = $base->input['authorized'];
$asciiarmor = $base->input['asciiarmor'];
$createinvoice = $base->input['createinvoice'];
$cancelnotice = $base->input['cancelnotice'];
$shutoffnotice = $base->input['shutoffnotice'];
$collectionsnotice = $base->input['collectionsnotice'];
$nsf = $base->input['nsf'];
$receipt = $base->input['receipt'];
$deletepayment = $base->input['deletepayment'];

if ($edit)
{
    if ($pallow_modify)
    {
       include('./modules/billing/edit.php');
    }  else permission_error();
}
else if ($create)
{
    if ($pallow_create)
    {
       include('./modules/billing/create.php');
    } else permission_error();
}
else if ($delete)
{
    if ($pallow_remove)
    {
       include('./modules/billing/delete.php');
    } else permission_error();
}
else if ($resetaddr)
{
    if ($pallow_modify)
    {
       include('./modules/billing/resetaddr.php');
    } else permission_error();
}
else if ($rerun) 
{    
	if ($pallow_modify)    
	{       
		include('./modules/billing/rerun.php');    
	}  else permission_error();
}
else if ($turnoff) 
{    
	if ($pallow_modify)    
	{       
		include('./modules/billing/turnoff.php');    
	}  else permission_error();
}
else if ($cancelwfee) 
{    
	if ($pallow_modify)    
	{       
		include('./modules/billing/cancelwfee.php');    
	}  else permission_error();
}
else if ($collections) 
{    
	if ($pallow_modify)    
	{       
		include('./modules/billing/collections.php');    
	}  else permission_error();
}
else if ($waiting) 
{    
	if ($pallow_modify)    
	{       
		include('./modules/billing/waiting.php');    
	}  else permission_error();
}
else if ($authorized) 
{    
	if ($pallow_modify)    
	{       
		include('./modules/billing/authorized.php');    
	}  else permission_error();
}
else if ($asciiarmor) 
{    
	if ($pallow_modify)    
	{       
		include('./modules/billing/asciiarmor.php');    
	}  else permission_error();
}
else if ($nsf) 
{    
	if ($pallow_modify)    
	{       
		include('./modules/billing/nsf.php');    
	}  else permission_error();
}
else if ($receipt) 
{    
	if ($pallow_modify)    
	{       
		include('./modules/billing/receipt.php');    
	}  else permission_error();
}
else if ($deletepayment) 
{    
	if ($pallow_modify)    
	{       
		include('./modules/billing/deletepayment.php');    
	}  else permission_error();
}
else if ($createinvoice) 
{    
	if ($pallow_modify)    
	{       
		include('./modules/billing/createinvoice.php');    
	}  else permission_error();
}
else if ($cancelnotice) 
{    
	if ($pallow_modify)    
	{       
		include('./modules/billing/cancelnotice.php');    
	}  else permission_error();
}
else if ($shutoffnotice) 
{    
	if ($pallow_modify)    
	{       
		include('./modules/billing/shutoffnotice.php');    
	}  else permission_error();
}
else if ($collectionsnotice) 
{    
	if ($pallow_modify)    
	{       
		include('./modules/billing/collectionsnotice.php');    
	}  else permission_error();
}

 else if ($pallow_view)
{
// get the billing id
$query = "SELECT * FROM customer WHERE account_number = $account_number";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;	
$default_billing_id = $myresult['default_billing_id'];

// $DB->debug=true;

// get the billing record for that default_billing_id
$query = "SELECT b.id b_id, b.name b_name, b.company b_company, b.street b_street, 
b.city b_city, b.state b_state, b.zip b_zip, b.phone b_phone, b.fax b_fax, b.country b_country, 
b.contact_email b_email, b.creditcard_number b_ccnum, b.creditcard_expire b_ccexp, 
b.billing_status b_status, b.billing_type b_type, b.next_billing_date b_next_billing_date, 
b.prev_billing_date b_prev_billing_date, b.from_date b_from_date,
b.to_date b_to_date, b.payment_due_date b_payment_due_date, b.rerun_date b_rerun_date, 
b.po_number b_po_number, b.notes b_notes, b.organization_id b_organization_id,  
t.id t_id, t.name t_name FROM billing b LEFT JOIN billing_types t 
ON b.billing_type = t.id WHERE b.id = '$default_billing_id'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
while ($myresult = $result->FetchRow())
{
	$billing_id = $myresult['b_id'];
	$name = $myresult['b_name'];
	$company = $myresult['b_company'];
	$street = $myresult['b_street'];
	$city = $myresult['b_city'];
	$state = $myresult['b_state'];
	$zip = $myresult['b_zip'];
	$country = $myresult['b_country'];
	$phone = $myresult['b_phone'];
	$fax = $myresult['b_fax'];
	$contact_email = $myresult['b_email'];
	$billing_type = $myresult['t_name'];
	$creditcard_number = $myresult['b_ccnum'];
	$creditcard_expire = $myresult['b_ccexp'];
	$billing_status = $myresult['b_status'];
	$next_billing_date = $myresult['b_next_billing_date'];
	$prev_billing_date = $myresult['b_prev_billing_date'];
	$from_date = $myresult['b_from_date'];
	$to_date = $myresult['b_to_date'];
	$payment_due_date = $myresult['b_payment_due_date'];
	$rerun_date = $myresult['b_rerun_date'];
	$notes = $myresult['b_notes'];
	$po_number = $myresult['b_po_number'];
	$organization_id = $myresult['b_organization_id'];
}

// if the card number is not blank, wipe out the middle of the card number
 if ($creditcard_number <> '') {
   $length = strlen($creditcard_number);
   $firstdigit = substr($creditcard_number, 0,1);
   $lastfour = substr($creditcard_number, -4);
   $creditcard_number = "$firstdigit" . "***********" . "$lastfour";
 }
 
// get the billing status
$mystatus = billingstatus($default_billing_id);

// print the default billing information
$edit_default_billing_url = "$ssl_url_prefix" . "index.php?load=billing&type=module&edit=on&billing_id=$billing_id";
echo "
<a href=$edit_default_billing_url>[ $l_editdefaultbilling ]</a>
<a href=index.php?load=billing&type=module&resetaddr=on&account_number=$account_number>[ $l_resetaddresstocustomer ]</a>
<a href=index.php?load=billing&type=module&rerun=on&billing_id=$billing_id>[ $l_rerun ]</a>
";
// query user properties
$query = "SELECT * FROM user WHERE username='$user' LIMIT 1";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$userresult = $DB->Execute($query) or die ("$l_queryfailed");
$myuserresult = $userresult->fields;
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

// get the organization info
$query = "SELECT org_name FROM general WHERE id = $organization_id LIMIT 1";
$orgresult = $DB->Execute($query) or die ("$l_queryfailed");
$myorgresult = $orgresult->fields;
$organization_name = $myorgresult['org_name']; 
echo "<h3>$l_organizationname: $organization_name</h3>";
echo "
<table cellpadding=0 border=0 cellspacing=0 width=720>
<td valign=top width=360>		
	<table cellpadding=5 border=0 cellspacing=1 width=360>
	<td bgcolor=\"#ccccdd\" width=180><b>ID</b></td><td bgcolor=\"#ddddee\" width=180>$billing_id</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_name</b></td><td bgcolor=\"#ddddee\">$name</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_company</b></td><td bgcolor=\"#ddddee\">$company</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_street</b></td><td bgcolor=\"#ddddee\">$street</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_city</b></td><td bgcolor=\"#ddddee\">$city</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_state</b></td><td bgcolor=\"#ddddee\">$state</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_zip</b></td><td bgcolor=\"#ddddee\">$zip</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_country</b></td><td bgcolor=\"#ddddee\">$country</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_phone</b></td><td bgcolor=\"#ddddee\">$phone</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_fax</b></td><td bgcolor=\"#ddddee\">$fax</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_contactemail</b></td><td bgcolor=\"#ddddee\">$contact_email</td><tr>
	</table>
</td>
<td valign=top width=360>		
	<table cellpadding=5 border=0 cellspacing=1 width=360>
	<td bgcolor=\"#ccccdd\"><b>$l_billingstatus</b></td><td bgcolor=\"#ddddee\"><b>$mystatus</b></td><tr>
	<td bgcolor=\"#ccccdd\" width=180><b>$l_billingtype</b></td><td bgcolor=\"#ddddee\" width=180>$billing_type</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_ccnumber</b></td><td bgcolor=\"#ddddee\">$creditcard_number</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_ccexpire</b></td><td bgcolor=\"#ddddee\">$creditcard_expire</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_nextbillingdate</b></td><td bgcolor=\"#ddddee\">$next_billing_date</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_from $l_date</b></td><td bgcolor=\"#ddddee\">$from_date</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_to $l_date</b></td><td bgcolor=\"#ddddee\">$to_date</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_paymentduedate</b></td><td bgcolor=\"#ddddee\">$payment_due_date</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_rerun $l_date</b></td><td bgcolor=\"#ddddee\">$rerun_date</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_po_number</b></td><td bgcolor=\"#ddddee\">$po_number</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_notes</b></td><td bgcolor=\"#ddddee\">$notes</td>
	</table>
</td>
</table>
<p>
[ <a href=\"index.php?load=billing&type=module&create=on\">$l_addaltbilling</a> ]<br>
";

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

if (($myuserresult['manager'] == 'y') OR ($myuserresult['admin'] == 'y')) 
{
	echo "<td>
	<a href=\"index.php?load=billing&type=module&rerun=on&billing_id=$billing_id\">[ $l_rerun ]</a> &nbsp;&nbsp;&nbsp; 
	<a href=\"index.php?load=invmaint&type=tools&billingid=$billing_id&submit=Submit\">[
	$l_invoicemaintenance ]</a> &nbsp;&nbsp;&nbsp; 
	<a href=\"index.php?load=refund&type=tools&billingid=$billing_id&submit=Submit\">[ $l_refundreport ]</a></td>";
}

echo "<tr bgcolor=\"#ddddee\">";

}

echo '</table>';


} else permission_error();
?>
