<?php
// Copyright (C) 2002-2008  Paul Yasi <paul@citrusdb.org>
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

// query user properties
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;

/*----------------------------------------------------------------------------*/
// Show User Functions - for all users
/*----------------------------------------------------------------------------*/
echo "<div class=toolblock><b>$l_user: $user</b><br>";

echo "
	<div class=icon>
	<a href=# onclick=\"popupPage('index.php?load=changepass&type=tools'); return false;\">
	<img src=\"images/icons/citrus_changepass.png\" border=0><br>
	$l_changeyourpassword</a>
	</div>
	
	<div class=icon>
	<a href=# onclick=\"popupPage('index.php?load=version&type=tools'); return false;\">
	<img src=\"images/icons/citrus_version.png\" border=0><br>
	$l_version</a>
	</div>
	</div>
";

/*----------------------------------------------------------------------------*/
// Load Module Listing
// For any user with manager access or higher
/*----------------------------------------------------------------------------*/
if (($myresult['manager'] == 'y') OR ($myresult['admin'] == 'y'))
{	
	// Print Modules Included Menu File
	$query = "SELECT * FROM modules ORDER BY sortorder";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");

	while ($mymoduleresult = $result->FetchRow())
	{
	$commonname = $mymoduleresult['commonname'];
        $modulename = $mymoduleresult['modulename'];
	include('./tools/modules/'.$modulename.'/menu.php');
	}
}

/*----------------------------------------------------------------------------*/
// Show Reports
// print reports for manager
/*----------------------------------------------------------------------------*/
if (($myresult['manager'] == 'y') OR ($myresult['admin'] == 'y'))
{
echo "<div class=toolblock style=\"height: 180px;\">
<b>$l_reports</b>
<br>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=summary&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_report.png\"><br>
$l_customersummary</a>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=revenue&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_report.png\"><br>
$l_revenuereport</a>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=refunds&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_report.png\"><br>
$l_refundreport</a>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=pastdue&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_declines.png\"><br>
$l_pastduereport</a>
</div>

<div class=icon>
<a href=# onclick = \"popupPage('index.php?load=billing&tooltype=module&type=tools&declined=on'); return false;\">
<img border=0 src=\"images/icons/citrus_declines.png\"><br>
$l_paymentstatus</a></div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=servicereport&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_report.png\"><br>
$l_servicereport</a>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=sourcereport&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_report.png\"><br>
$l_sourcereport</a>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=exemptreport&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_report.png\"><br>
$l_exemptreport</a>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=listpdf&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_report.png\"><br>
$l_printnotices</a>
</div>

</div>
";
}

/*----------------------------------------------------------------------------*/
// Show Admin Functions
/*----------------------------------------------------------------------------*/
if ($myresult['admin'] == 'y')
{
echo "<div class=toolblock>
<b>$l_admin</b>
<br>
<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=general&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_config.png\">
$l_generalconfiguration</a></li>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=settings&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_config.png\">
$l_settings</a></li>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=users&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_users.png\"><br>
$l_users</a>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=groups&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_groups.png\"><br>
$l_groups</a>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=modules&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_modules.png\"><br>
$l_editmodules</a>
</div>

<div class=icon>
<a href=# onclick = \"popupPage('index.php?load=billing&tooltype=module&type=tools&billingtypes=on'); return false;\">
<img border=0 src=\"images/icons/citrus_edit.png\"><br>
$l_editbillingtypes</a>
</div>

<div class=icon>
<a href=# onclick = \"popupPage('index.php?load=services&tooltype=module&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_edit.png\"><br>
$l_editservices</a>
</div>

</div>
";
}


?>

