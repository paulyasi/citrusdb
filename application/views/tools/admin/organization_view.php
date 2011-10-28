<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">
<h3>$l_generalconfiguration</h3>";
<form name="addform" method=post action="<?php echo $this->url_prefix?>/index.php/tools/admin/addorganization">
<input type=submit value="<?php echo lang('add')?>"></form>

<table cellpadding=5 cellspacing=1><tr bgcolor="#eeeeee">
<?php
foreach ($org_list AS $myresult) 
{
  // print a table of all organizations
  $myid = $myresult['id'];
  $myorg = $myresult['org_name'];
  echo "<td>$myid</td><td>$myorg</td><td><a href=\"index.php?load=general&type=tools&id=$myid\">$l_edit</a></td><tr bgcolor=\"#eeeeee\">";
}
?>
</table>


	
<hr><h2><?php echo $org['org_name']?></h2>
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/admin/updateorganization" 
METHOD="POST" name="editform">
<input type=hidden name=id value="<?php echo $org['id']?>">
<table><td>
<B><?php echo lang('organizationname')?>:</B></td><td>
<INPUT TYPE="TEXT" NAME="org_name" VALUE="<?php echo $org['org_name']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B>$l_street:</B></td><td>
<INPUT TYPE="TEXT" NAME="org_street" VALUE="<?php echo $org['org_street']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B>$l_city:</B></td><td>
<INPUT TYPE="TEXT" NAME="org_city" VALUE="<?php echo $org['org_city']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B>$l_state:</B></td><td>
<INPUT TYPE="TEXT" NAME="org_state" VALUE="<?php echo $org['org_state']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B>$l_zip:</B></td><td>
<INPUT TYPE="TEXT" NAME="org_zip" VALUE="<?php echo $org['org_zip']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B>$l_country:</B></td><td>
<INPUT TYPE="TEXT" NAME="org_country" VALUE="<?php echo $org['org_country']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B>$l_salesphone:</B></td><td>
<INPUT TYPE="TEXT" NAME="phone_sales" VALUE="<?php echo $org['phone_sales']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B>$l_salesemail:</B></td><td>
<INPUT TYPE="TEXT" NAME="email_sales" VALUE="$email_sales" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B>$l_billingphone:</B></td><td>
<INPUT TYPE="TEXT" NAME="phone_billing" VALUE="$phone_billing" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B>$l_billingemail:</B></td><td>
<INPUT TYPE="TEXT" NAME="email_billing" VALUE="$email_billing" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B>$l_customerservicephone:</B></td><td>
<INPUT TYPE="TEXT" NAME="phone_custsvc" VALUE="$phone_custsvc" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B>$l_customerserviceemail:</B></td><td>
<INPUT TYPE="TEXT" NAME="email_custsvc" VALUE="$email_custsvc" SIZE="20" MAXLENGTH="32">
</td><tr><td> 
<B>$l_creditcardexportvars</B></td><td>
<INPUT TYPE="TEXT" NAME="ccexportvarorder" VALUE="$ccexportvarorder" SIZE="50" MAXLENGTH="255">
</td><tr><td>
<B>$l_exportfileprefix</B></td><td>
<INPUT TYPE="TEXT" NAME="exportprefix" VALUE="$exportprefix" SIZE="32" MAXLENGTH="64">
</td><tr><td>


<B>$l_pastduedays</B></td><td>
$l_pastdue: <INPUT TYPE="TEXT" NAME="regular_pastdue" VALUE="$regular_pastdue" SIZE="2" MAXLENGTH="3"> &nbsp; 
$l_turnedoff: <INPUT TYPE="TEXT" NAME="regular_turnoff" VALUE="$regular_turnoff" SIZE="2" MAXLENGTH="3"> &nbsp; 
$l_canceled: <INPUT TYPE="TEXT" NAME="regular_canceled" VALUE="$regular_canceled" SIZE="2" MAXLENGTH="3"> &nbsp; 
</td><tr><td>

<B>$l_carrierdependent $l_pastduedays</B></td><td>
$l_pastdue: <INPUT TYPE="TEXT" NAME="dependent_pastdue" VALUE="$dependent_pastdue" SIZE="2" MAXLENGTH="3"> &nbsp;
$l_shutoffnotice <INPUT TYPE="TEXT" NAME="dependent_shutoff_notice" VALUE="$dependent_shutoff_notice" SIZE="2" MAXLENGTH="3"> &nbsp;
$l_turnedoff: <INPUT TYPE="TEXT" NAME="dependent_turnoff" VALUE="$dependent_turnoff" SIZE="2" MAXLENGTH="3"> &nbsp; 
$l_canceled: <INPUT TYPE="TEXT" NAME="dependent_canceled" VALUE="$dependent_canceled" SIZE="2" MAXLENGTH="3"> &nbsp; 
</td><tr><td>

<B>$l_defaultinvoicenote</B></td><td>
<INPUT TYPE="TEXT" NAME="default_invoicenote" 
VALUE="$default_invoicenote" SIZE="50" MAXLENGTH="255">
</td><tr><td>
<B>$l_pastdueinvoicenote</B></td><td>
<INPUT TYPE="TEXT" NAME="pastdue_invoicenote" 
VALUE="$pastdue_invoicenote" SIZE="50" MAXLENGTH="255">
</td><tr><td>
<B>$l_turnedoffinvoicenote</B></td><td>
<INPUT TYPE="TEXT" NAME="turnedoff_invoicenote" 
VALUE="$turnedoff_invoicenote" SIZE="50" MAXLENGTH="255">
</td><tr><td>
<B>$l_collectionsinvoicenote</B></td><td>
<INPUT TYPE="TEXT" NAME="collections_invoicenote" 
VALUE="$collections_invoicenote" SIZE="50" MAXLENGTH="255">
</td><tr><td>

<B>$l_declined_subject</B></td><td>
<INPUT TYPE="TEXT" NAME="declined_subject" 
VALUE="$declined_subject" SIZE="50" MAXLENGTH="255">
</td><tr><td>
<B>$l_declined_message</B></td><td>
<textarea NAME="declined_message" rows=8 cols=50>$declined_message</textarea>
</td><tr><td>


<B>$l_invoice_footer</B></td><td>
<textarea NAME="invoice_footer" rows=2 cols=50>$invoice_footer</textarea>
</td><tr><td>

<B>$l_einvoice_footer</B></td><td>
<textarea NAME="einvoice_footer" rows=2 cols=50>$einvoice_footer</textarea>
</td><tr><td>
</td><td>
<INPUT TYPE="SUBMIT" NAME="submit" value="$l_savechanges">
</FORM>";

echo '
<p>
You can use any combination of the following variables in the Credit Card Export Variable Order:<br>
$user (the database user)<br>
$batchid<br>
$mybilling_id (the billing id being run)<br>
$invoice_number<br>
$billing_name<br>
$billing_company<br>
$billing_street<br>
$billing_city<br>
$billing_state<br>
$billing_zip<br>
$billing_acctnum<br>
$billing_ccnum<br>
$billing_ccexp<br>
$billing_fromdate<br>
$billing_todate<br>
$billing_payment_due_date<br>
$mydate (Y-m-d date format)<br>
$abstotal (absolute value of total, if precise total is negative it will not export until credits are used up)<br>
</td></table>';

?>
</body>
</html>







