<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_generalconfiguration</h3>";
// Copyright (C) 2002-2006  Paul Yasi <paul@citrusdb.org>
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

//GET Variables
if (!isset($base->input['id'])) { $base->input['id'] = "1"; }
if (!isset($base->input['addnew'])) { $base->input['addnew'] = ""; }
if (!isset($base->input['org_name'])) { $base->input['org_name'] = ""; }
if (!isset($base->input['org_street'])) { $base->input['org_street'] = ""; }
if (!isset($base->input['org_city'])) { $base->input['org_city'] = ""; }
if (!isset($base->input['org_state'])) { $base->input['org_state'] = ""; }
if (!isset($base->input['org_country'])) { $base->input['org_country'] = ""; }
if (!isset($base->input['org_zip'])) { $base->input['org_zip'] = ""; }
if (!isset($base->input['phone_sales'])) { $base->input['phone_sales'] = ""; }
if (!isset($base->input['email_sales'])) { $base->input['email_sales'] = ""; }
if (!isset($base->input['phone_billing'])) { $base->input['phone_billing'] = ""; }
if (!isset($base->input['email_billing'])) { $base->input['email_billing'] = ""; }
if (!isset($base->input['phone_custsvc'])) { $base->input['phone_custsvc'] = ""; }
if (!isset($base->input['email_custsvc'])) { $base->input['email_custsvc'] = ""; }
if (!isset($base->input['ccexportvarorder'])) { $base->input['ccexportvarorder'] = ""; }
if (!isset($base->input['regular_pastdue'])) { $base->input['regular_pastdue'] = ""; }
if (!isset($base->input['regular_turnoff'])) { $base->input['regular_turnoff'] = ""; }
if (!isset($base->input['regular_canceled'])) { $base->input['regular_canceled'] = ""; }

if (!isset($base->input['dependent_pastdue'])) { $base->input['dependent_pastdue'] = ""; }
if (!isset($base->input['dependent_shutoff_notice'])) { $base->input['dependent_shutoff_notice'] = ""; }
if (!isset($base->input['dependent_turnoff'])) { $base->input['dependent_turnoff'] = ""; }
if (!isset($base->input['dependent_canceled'])) { $base->input['dependent_canceled'] = ""; }

if (!isset($base->input['default_invoicenote'])) { $base->input['default_invoicenote'] = ""; }
if (!isset($base->input['pastdue_invoicenote'])) { $base->input['pastdue_invoicenote'] = ""; }
if (!isset($base->input['turnedoff_invoicenote'])) { $base->input['turnedoff_invoicenote'] = ""; }
if (!isset($base->input['collections_invoicenote'])) { $base->input['collections_invoicenote'] = ""; }
if (!isset($base->input['declined_subject'])) { $base->input['declined_subject'] = ""; }
if (!isset($base->input['declined_message'])) { $base->input['declined_message'] = ""; }
if (!isset($base->input['invoice_footer'])) { $base->input['invoice_footer'] = ""; }
if (!isset($base->input['einvoice_footer'])) { $base->input['einvoice_footer'] = ""; }
if (!isset($base->input['exportprefix'])) { $base->input['exportprefix'] = ""; }


$submit = $base->input['submit'];
$addnew = $base->input['addnew'];
$id = $base->input['id'];
$org_name = $base->input['org_name'];
$org_street = $base->input['org_street'];
$org_city = $base->input['org_city'];
$org_state = $base->input['org_state'];
$org_country = $base->input['org_country'];
$org_zip = $base->input['org_zip'];
$phone_sales = $base->input['phone_sales'];
$email_sales = $base->input['email_sales'];
$phone_billing = $base->input['phone_billing'];
$email_billing = $base->input['email_billing'];
$phone_custsvc = $base->input['phone_custsvc'];
$email_custsvc = $base->input['email_custsvc'];
$ccexportvarorder = $base->input['ccexportvarorder'];
$regular_pastdue = $base->input['regular_pastdue'];
$regular_turnoff = $base->input['regular_turnoff'];
$regular_canceled = $base->input['regular_canceled'];

$dependent_pastdue = $base->input['dependent_pastdue'];
$dependent_shutoff_notice = $base->input['dependent_shutoff_notice'];
$dependent_turnoff = $base->input['dependent_turnoff'];
$dependent_canceled = $base->input['dependent_canceled'];

$default_invoicenote = $base->input['default_invoicenote'];
$pastdue_invoicenote = $base->input['pastdue_invoicenote'];
$turnedoff_invoicenote = $base->input['turnedoff_invoicenote'];
$collections_invoicenote = $base->input['collections_invoicenote'];

$declined_subject = $base->input['declined_subject'];
$declined_message = $base->input['declined_message'];
$invoice_footer = $base->input['invoice_footer'];
$einvoice_footer = $base->input['einvoice_footer'];
$exportprefix = $base->input['exportprefix'];


// convert the $ccexportvarorder &#036; dollar signs back to actual dollar signs and &quot; back to quotes
//$ccexportvarorder = str_replace( "&#036;"           , "$"        , $ccexportvarorder );
//$ccexportvarorder = str_replace( "&quot;"           , "\""        , $ccexportvarorder );


/*
ccexportvarorder is a list of available variables that can be included in the export from billing

$user (the database user)
$batchid
$mybilling_id (the billing id being run)
$invoice_number
$billing_name
$billing_company
$billing_street
$billing_city
$billing_state
$billing_zip
$billing_acctnum
$billing_ccnum
$billing_ccexp
$billing_fromdate
$billing_todate
$billing_payment_due_date
$mydate (Y-m-d date format)
$abstotal (absolute value of total)

These will become part of the newline variable in the exportccsave.php file
*/


//$DB->debug = true;

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
  echo '$l_youmusthaveadmin<br>';
  exit; 
}

if ($submit) {
// save general information
  $query = "UPDATE general
	SET org_name = '$org_name',
	org_street = '$org_street',
	org_city = '$org_city',
	org_state = '$org_state',
	org_country = '$org_country',
	org_zip = '$org_zip',
	phone_sales = '$phone_sales',
	email_sales = '$email_sales',
	phone_billing = '$phone_billing',
	email_billing = '$email_billing',
	phone_custsvc = '$phone_custsvc',
	email_custsvc = '$email_custsvc',
	ccexportvarorder = '$ccexportvarorder',
	regular_pastdue = '$regular_pastdue',
	regular_turnoff = '$regular_turnoff',
	regular_canceled = '$regular_canceled',
dependent_pastdue = '$dependent_pastdue',
dependent_shutoff_notice = '$dependent_shutoff_notice',
dependent_turnoff = '$dependent_turnoff',
dependent_canceled = '$dependent_canceled',
default_invoicenote = '$default_invoicenote',
	pastdue_invoicenote = '$pastdue_invoicenote',
	turnedoff_invoicenote = '$turnedoff_invoicenote',
	collections_invoicenote = '$collections_invoicenote',
	declined_subject = '$declined_subject',
	declined_message = '$declined_message',
exportprefix = '$exportprefix',
	invoice_footer = '$invoice_footer',
	einvoice_footer = '$einvoice_footer' WHERE id = $id";
  $result = $DB->Execute($query) or die ("Query Failed");
  
  print "<h3>$l_changessaved</h3>";
        //print "<script language=\"JavaScript\">window.location.href = \"tools/general.php\";</script>";

}

if ($addnew) {
  $query = "INSERT INTO general (org_name) VALUES ('$l_new')";
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  $myinsertid = $DB->Insert_ID();
  print "<script language=\"JavaScript\">window.location.href = \"index.php?load=general&type=tools&id=$myinsertid\";</script>";
}

// show all the organizations that can be edited
$query = "SELECT id,org_name from general";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");

echo "<form action=\"index.php\" method=post name=\"addform\">".
  "<input type=hidden name=load value=general>".
  "<input type=hidden name=type value=tools>".
  "<input type=hidden name=addnew value=on>".
  "<input type=submit value=\"$l_add\"></form>";

echo "<table cellpadding=5 cellspacing=1><tr bgcolor=\"#eeeeee\">";
while ($myresult = $result->FetchRow()) {
  // print a table of all organizations
  $myid = $myresult['id'];
  $myorg = $myresult['org_name'];
  echo "<td>$myid</td><td>$myorg</td><td><a href=\"index.php?load=general&type=tools&id=$myid\">$l_edit</a></td><tr bgcolor=\"#eeeeee\">";
}

echo "</table>";


// get the variables out of the general configuration table
$query = "SELECT * FROM general WHERE id = $id";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
	
$myresult = $result->fields;
$org_name = $myresult['org_name'];
$org_street = $myresult['org_street'];
$org_city = $myresult['org_city'];
$org_state = $myresult['org_state'];
$org_zip = $myresult['org_zip'];
$org_country = $myresult['org_country'];
$phone_sales = $myresult['phone_sales'];
$email_sales = $myresult['email_sales'];
$phone_billing = $myresult['phone_billing'];
$email_billing = $myresult['email_billing'];
$phone_custsvc = $myresult['phone_custsvc'];
$email_custsvc = $myresult['email_custsvc'];
$ccexportvarorder = $myresult['ccexportvarorder'];
$regular_pastdue = $myresult['regular_pastdue'];
$regular_turnoff = $myresult['regular_turnoff'];
$regular_canceled = $myresult['regular_canceled'];

$dependent_pastdue = $myresult['dependent_pastdue'];
$dependent_shutoff_notice = $myresult['dependent_shutoff_notice'];
$dependent_turnoff = $myresult['dependent_turnoff'];
$dependent_canceled = $myresult['dependent_canceled'];

$default_invoicenote = $myresult['default_invoicenote'];
$pastdue_invoicenote = $myresult['pastdue_invoicenote'];
$turnedoff_invoicenote = $myresult['turnedoff_invoicenote'];
$collections_invoicenote = $myresult['collections_invoicenote'];
$declined_subject = $myresult['declined_subject'];
$declined_message = $myresult['declined_message'];
$invoice_footer = $myresult['invoice_footer'];
$einvoice_footer = $myresult['einvoice_footer'];
$exportprefix = $myresult['exportprefix'];

// print the general variables in a form
	
//echo "$l_databaseversion: $databaseversion<br>
//$l_softwareversion: $softwareversion<br>
echo "<hr><h2>$org_name</h2>
	<FORM ACTION=\"index.php\" METHOD=\"POST\" name=\"editform\">
	<input type=hidden name=load value=general>
	<input type=hidden name=type value=tools>
	<input type=hidden name=id value=\"$id\">
	<table><td>
	<B>$l_organizationname:</B></td><td>
	<INPUT TYPE=\"TEXT\" NAME=\"org_name\" VALUE=\"$org_name\" SIZE=\"20\" MAXLENGTH=\"32\">
	</td><tr><td>
	<B>$l_street:</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"org_street\" VALUE=\"$org_street\" SIZE=\"20\" MAXLENGTH=\"32\">
        </td><tr><td>
        <B>$l_city:</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"org_city\" VALUE=\"$org_city\" SIZE=\"20\" MAXLENGTH=\"32\">
        </td><tr><td>
        <B>$l_state:</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"org_state\" VALUE=\"$org_state\" SIZE=\"20\" MAXLENGTH=\"32\">
        </td><tr><td>
        <B>$l_zip:</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"org_zip\" VALUE=\"$org_zip\" SIZE=\"20\" MAXLENGTH=\"32\">
        </td><tr><td>
        <B>$l_country:</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"org_country\" VALUE=\"$org_country\" SIZE=\"20\" MAXLENGTH=\"32\">
        </td><tr><td>
        <B>$l_salesphone:</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"phone_sales\" VALUE=\"$phone_sales\" SIZE=\"20\" MAXLENGTH=\"32\">
        </td><tr><td>
        <B>$l_salesemail:</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"email_sales\" VALUE=\"$email_sales\" SIZE=\"20\" MAXLENGTH=\"32\">
        </td><tr><td>
        <B>$l_billingphone:</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"phone_billing\" VALUE=\"$phone_billing\" SIZE=\"20\" MAXLENGTH=\"32\">
        </td><tr><td>
        <B>$l_billingemail:</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"email_billing\" VALUE=\"$email_billing\" SIZE=\"20\" MAXLENGTH=\"32\">
        </td><tr><td>
        <B>$l_customerservicephone:</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"phone_custsvc\" VALUE=\"$phone_custsvc\" SIZE=\"20\" MAXLENGTH=\"32\">
        </td><tr><td>
        <B>$l_customerserviceemail:</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"email_custsvc\" VALUE=\"$email_custsvc\" SIZE=\"20\" MAXLENGTH=\"32\">
        </td><tr><td> 
        <B>$l_creditcardexportvars</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"ccexportvarorder\" VALUE=\"$ccexportvarorder\" SIZE=\"50\" MAXLENGTH=\"255\">
        </td><tr><td>
        <B>$l_exportfileprefix</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"exportprefix\" VALUE=\"$exportprefix\" SIZE=\"32\" MAXLENGTH=\"64\">
        </td><tr><td>


	<B>$l_pastduedays</B></td><td>
        $l_pastdue: <INPUT TYPE=\"TEXT\" NAME=\"regular_pastdue\" VALUE=\"$regular_pastdue\" SIZE=\"2\" MAXLENGTH=\"3\"> &nbsp; 
        $l_turnedoff: <INPUT TYPE=\"TEXT\" NAME=\"regular_turnoff\" VALUE=\"$regular_turnoff\" SIZE=\"2\" MAXLENGTH=\"3\"> &nbsp; 
        $l_canceled: <INPUT TYPE=\"TEXT\" NAME=\"regular_canceled\" VALUE=\"$regular_canceled\" SIZE=\"2\" MAXLENGTH=\"3\"> &nbsp; 
        </td><tr><td>

	<B>$l_carrierdependent $l_pastduedays</B></td><td>
        $l_pastdue: <INPUT TYPE=\"TEXT\" NAME=\"dependent_pastdue\" VALUE=\"$dependent_pastdue\" SIZE=\"2\" MAXLENGTH=\"3\"> &nbsp;
$l_shutoffnotice <INPUT TYPE=\"TEXT\" NAME=\"dependent_shutoff_notice\" VALUE=\"$dependent_shutoff_notice\" SIZE=\"2\" MAXLENGTH=\"3\"> &nbsp;
        $l_turnedoff: <INPUT TYPE=\"TEXT\" NAME=\"dependent_turnoff\" VALUE=\"$dependent_turnoff\" SIZE=\"2\" MAXLENGTH=\"3\"> &nbsp; 
        $l_canceled: <INPUT TYPE=\"TEXT\" NAME=\"dependent_canceled\" VALUE=\"$dependent_canceled\" SIZE=\"2\" MAXLENGTH=\"3\"> &nbsp; 
        </td><tr><td>

	<B>$l_defaultinvoicenote</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"default_invoicenote\" 
	VALUE=\"$default_invoicenote\" SIZE=\"50\" MAXLENGTH=\"255\">
        </td><tr><td>
	<B>$l_pastdueinvoicenote</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"pastdue_invoicenote\" 
	VALUE=\"$pastdue_invoicenote\" SIZE=\"50\" MAXLENGTH=\"255\">
        </td><tr><td>
	<B>$l_turnedoffinvoicenote</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"turnedoff_invoicenote\" 
	VALUE=\"$turnedoff_invoicenote\" SIZE=\"50\" MAXLENGTH=\"255\">
        </td><tr><td>
	<B>$l_collectionsinvoicenote</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"collections_invoicenote\" 
	VALUE=\"$collections_invoicenote\" SIZE=\"50\" MAXLENGTH=\"255\">
        </td><tr><td>

<B>$l_declined_subject</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"declined_subject\" 
	VALUE=\"$declined_subject\" SIZE=\"50\" MAXLENGTH=\"255\">
        </td><tr><td>
<B>$l_declined_message</B></td><td>
        <textarea NAME=\"declined_message\" rows=8 cols=50>$declined_message</textarea>
        </td><tr><td>


        <B>$l_invoice_footer</B></td><td>
        <textarea NAME=\"invoice_footer\" rows=2 cols=50>$invoice_footer</textarea>
        </td><tr><td>

        <B>$l_einvoice_footer</B></td><td>
        <textarea NAME=\"einvoice_footer\" rows=2 cols=50>$einvoice_footer</textarea>
        </td><tr><td>
	</td><td>
	<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" value=\"$l_savechanges\">
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







