<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('generalconfiguration')?></h3>
<form name="addform" method=post action="<?php echo $this->url_prefix?>/index.php/tools/admin/addorganization">
<input type=submit value="<?php echo lang('add')?>"></form>

<table cellpadding=5 cellspacing=1><tr bgcolor="#eeeeee">
<?php
foreach ($org_list AS $myresult) 
{
  // print a table of all organizations
  $myid = $myresult['id'];
  $myorg = $myresult['org_name'];
  echo "<td>$myid</td><td>$myorg</td><td><a href=\"$this->url_prefix/index.php/tools/admin/organization/$myid\">".lang('edit')."</a></td><tr bgcolor=\"#eeeeee\">";
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
<B><?php echo lang('street')?>:</B></td><td>
<INPUT TYPE="TEXT" NAME="org_street" VALUE="<?php echo $org['org_street']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B><?php echo lang('city')?>:</B></td><td>
<INPUT TYPE="TEXT" NAME="org_city" VALUE="<?php echo $org['org_city']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B><?php echo lang('state')?>:</B></td><td>
<INPUT TYPE="TEXT" NAME="org_state" VALUE="<?php echo $org['org_state']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B><?php echo lang('zip')?>:</B></td><td>
<INPUT TYPE="TEXT" NAME="org_zip" VALUE="<?php echo $org['org_zip']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B><?php echo lang('country')?>:</B></td><td>
<INPUT TYPE="TEXT" NAME="org_country" VALUE="<?php echo $org['org_country']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B><?php echo lang('salesphone')?>:</B></td><td>
<INPUT TYPE="TEXT" NAME="phone_sales" VALUE="<?php echo $org['phone_sales']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B><?php echo lang('salesemail')?>:</B></td><td>
<INPUT TYPE="TEXT" NAME="email_sales" VALUE="<?php echo $org['email_sales']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B><?php echo lang('billingphone')?>:</B></td><td>
<INPUT TYPE="TEXT" NAME="phone_billing" VALUE="<?php echo $org['phone_billing']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B><?php echo lang('billingemail')?>:</B></td><td>
<INPUT TYPE="TEXT" NAME="email_billing" VALUE="<?php echo $org['email_billing']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B><?php echo lang('customerservicephone')?>:</B></td><td>
<INPUT TYPE="TEXT" NAME="phone_custsvc" VALUE="<?php echo $org['phone_custsvc']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B><?php echo lang('customerserviceemail')?>:</B></td><td>
<INPUT TYPE="TEXT" NAME="email_custsvc" VALUE="<?php echo $org['email_custsvc']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td> 
<B><?php echo lang('creditcardexportvars')?></B></td><td>
<INPUT TYPE="TEXT" NAME="ccexportvarorder" VALUE="<?php echo $org['ccexportvarorder']?>" SIZE="50" MAXLENGTH="255">
</td><tr><td>
<B><?php echo lang('exportfileprefix')?></B></td><td>
<INPUT TYPE="TEXT" NAME="exportprefix" VALUE="<?php echo $org['exportprefix']?>" SIZE="32" MAXLENGTH="64">
</td><tr><td>


<B><?php echo lang('pastduedays')?></B></td><td>
<?php echo lang('pastdue')?>: <INPUT TYPE="TEXT" NAME="regular_pastdue" VALUE="<?php echo $org['regular_pastdue']?>" SIZE="2" MAXLENGTH="3"> &nbsp; 
<?php echo lang('turnedoff')?>: <INPUT TYPE="TEXT" NAME="regular_turnoff" VALUE="<?php echo $org['regular_turnoff']?>" SIZE="2" MAXLENGTH="3"> &nbsp; 
<?php echo lang('canceled')?>: <INPUT TYPE="TEXT" NAME="regular_canceled" VALUE="<?php echo $org['regular_canceled']?>" SIZE="2" MAXLENGTH="3"> &nbsp; 
</td><tr><td>

<B><?php echo lang('carrierdependent')?> <?php echo lang('pastduedays')?></B></td><td>
<?php echo lang('pastdue')?>: <INPUT TYPE="TEXT" NAME="dependent_pastdue" VALUE="<?php echo $org['dependent_pastdue']?>" SIZE="2" MAXLENGTH="3"> &nbsp;
<?php echo lang('shutoffnotice')?> <INPUT TYPE="TEXT" NAME="dependent_shutoff_notice" VALUE="<?php echo $org['dependent_shutoff_notice']?>" SIZE="2" MAXLENGTH="3"> &nbsp;
<?php echo lang('turnedoff')?>: <INPUT TYPE="TEXT" NAME="dependent_turnoff" VALUE="<?php echo $org['dependent_turnoff']?>" SIZE="2" MAXLENGTH="3"> &nbsp; 
<?php echo lang('canceled')?>: <INPUT TYPE="TEXT" NAME="dependent_canceled" VALUE="<?php echo $org['dependent_canceled']?>" SIZE="2" MAXLENGTH="3"> &nbsp; 
</td><tr><td>

<B><?php echo lang('defaultinvoicenote')?></B></td><td>
<INPUT TYPE="TEXT" NAME="default_invoicenote" 
VALUE="<?php echo $org['default_invoicenote']?>" SIZE="50" MAXLENGTH="255">
</td><tr><td>
<B><?php echo lang('pastdueinvoicenote')?></B></td><td>
<INPUT TYPE="TEXT" NAME="pastdue_invoicenote" 
VALUE="<?php echo $org['pastdue_invoicenote']?>" SIZE="50" MAXLENGTH="255">
</td><tr><td>
<B><?php echo lang('turnedoffinvoicenote')?></B></td><td>
<INPUT TYPE="TEXT" NAME="turnedoff_invoicenote" 
VALUE="<?php echo $org['turnedoff_invoicenote']?>" SIZE="50" MAXLENGTH="255">
</td><tr><td>
<B><?php echo lang('collectionsinvoicenote')?></B></td><td>
<INPUT TYPE="TEXT" NAME="collections_invoicenote" 
VALUE="<?php echo $org['collections_invoicenote']?>" SIZE="50" MAXLENGTH="255">
</td><tr><td>

<B><?php echo lang('declined_subject')?></B></td><td>
<INPUT TYPE="TEXT" NAME="declined_subject" 
VALUE="<?php echo $org['declined_subject']?>" SIZE="50" MAXLENGTH="255">
</td><tr><td>
<B><?php echo lang('declined_message')?></B></td><td>
<textarea NAME="declined_message" rows=8 cols=50><?php echo $org['declined_message']?></textarea>
</td><tr><td>

<B><?php echo lang('invoice_footer')?></B></td><td>
<textarea NAME="invoice_footer" rows=2 cols=50><?php echo $org['invoice_footer']?></textarea>
</td><tr><td>

<B><?php echo lang('einvoice_footer')?></B></td><td>
<textarea NAME="einvoice_footer" rows=2 cols=50><?php echo $org['einvoice_footer']?></textarea>
</td><tr><td>
</td><td>
<INPUT TYPE="SUBMIT" NAME="submit" value="<?php echo lang('savechanges')?>">
</FORM>

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
</td></table>

</body>
</html>







