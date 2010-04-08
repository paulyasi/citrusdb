<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_settings</h3>";
// Copyright (C) 2008  Paul Yasi <paul@citrusdb.org>
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
if (!isset($base->input['path_to_ccfile'])) { $base->input['path_to_ccfile'] = ""; }
if (!isset($base->input['default_group'])) { $base->input['default_group'] = ""; }
if (!isset($base->input['default_billing_group'])) { $base->input['default_billing_group'] = ""; }
if (!isset($base->input['default_shipping_group'])) { $base->input['default_shipping_group'] = ""; }
if (!isset($base->input['billingdate_rollover_time'])) { $base->input['billingdate_rollover_time'] = ""; } 
if (!isset($base->input['billingweekend_sunday'])) { $base->input['billingweekend_sunday'] = ""; }
if (!isset($base->input['billingweekend_monday'])) { $base->input['billingweekend_monday'] = ""; }
if (!isset($base->input['billingweekend_tuesday'])) { $base->input['billingweekend_tuesday'] = ""; }
if (!isset($base->input['billingweekend_wednesday'])) { $base->input['billingweekend_wednesday'] = ""; }
if (!isset($base->input['billingweekend_thursday'])) { $base->input['billingweekend_thursday'] = ""; }
if (!isset($base->input['billingweekend_friday'])) { $base->input['billingweekend_friday'] = ""; }
if (!isset($base->input['billingweekend_saturday'])) { $base->input['billingweekend_saturday'] = ""; }
if (!isset($base->input['dependent_cancel_url'])) { $base->input['dependent_cancel_url'] = ""; }


$submit = $base->input['submit'];
$path_to_ccfile = $base->input['path_to_ccfile'];
$default_group = $base->input['default_group'];
$default_billing_group = $base->input['default_billing_group'];
$default_shipping_group = $base->input['default_shipping_group'];
$billingdate_rollover_time = $base->input['billingdate_rollover_time'];
$billingweekend_sunday = $base->input['billingweekend_sunday'];
$billingweekend_monday = $base->input['billingweekend_monday'];
$billingweekend_tuesday = $base->input['billingweekend_tuesday'];
$billingweekend_wednesday = $base->input['billingweekend_wednesday'];
$billingweekend_thursday = $base->input['billingweekend_thursday'];
$billingweekend_friday = $base->input['billingweekend_friday'];
$billingweekend_saturday = $base->input['billingweekend_saturday'];
$dependent_cancel_url = $base->input['dependent_cancel_url'];

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
// save settings information
  $query = "UPDATE settings ".
    "SET path_to_ccfile = '$path_to_ccfile', ".
    "default_group = '$default_group', ".
    "default_billing_group = '$default_billing_group', ".
    "default_shipping_group = '$default_shipping_group', ".
    "billingdate_rollover_time = '$billingdate_rollover_time', ".
    "billingweekend_sunday = '$billingweekend_sunday', ".
    "billingweekend_monday = '$billingweekend_monday', ".
    "billingweekend_tuesday = '$billingweekend_tuesday', ".
    "billingweekend_wednesday = '$billingweekend_wednesday', ".
    "billingweekend_thursday = '$billingweekend_thursday', ".
    "billingweekend_friday = '$billingweekend_friday', ".
    "billingweekend_saturday = '$billingweekend_saturday', ".
    "dependent_cancel_url = '$dependent_cancel_url' ".
    "WHERE id = 1";
  $result = $DB->Execute($query) or die ("Query Failed");
  
  print "<h3>$l_changessaved</h3>";
  //print "<script language=\"JavaScript\">window.location.href = \"tools/settings.php\";</script>";
  
 }

// get the variables out of the id 1 settings configuration table
$query = "SELECT * FROM settings WHERE id = 1";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");

$myresult = $result->fields;
$databaseversion = $myresult['version'];
$path_to_ccfile = $myresult['path_to_ccfile'];
$default_group = $myresult['default_group'];
$default_billing_group = $myresult['default_billing_group'];
$default_shipping_group = $myresult['default_shipping_group'];
$billingdate_rollover_time = $myresult['billingdate_rollover_time'];
$billingweekend_sunday = $myresult['billingweekend_sunday'];
$billingweekend_monday = $myresult['billingweekend_monday'];
$billingweekend_tuesday = $myresult['billingweekend_tuesday'];
$billingweekend_wednesday = $myresult['billingweekend_wednesday'];
$billingweekend_thursday = $myresult['billingweekend_thursday'];
$billingweekend_friday = $myresult['billingweekend_friday'];
$billingweekend_saturday = $myresult['billingweekend_saturday'];
$dependent_cancel_url = $myresult['dependent_cancel_url'];

// print the settings variables in a form
	
echo "$l_databaseversion: $databaseversion<br>
$l_softwareversion: $softwareversion<br>
<FORM ACTION=\"index.php\" METHOD=\"GET\">
	<input type=hidden name=load value=settings>
	<input type=hidden name=type value=tools>
	<table><td>
        <B>$l_pathtocreditcardfile</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"path_to_ccfile\" VALUE=\"$path_to_ccfile\" SIZE=\"50\" MAXLENGTH=\"128\">
        </td><tr><td>
	<B>$l_defaultgroup</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"default_group\" VALUE=\"$default_group\" SIZE=\"20\" MAXLENGTH=\"32\">
        </td><tr><td>
	<B>$l_defaultbillinggroup</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"default_billing_group\" VALUE=\"$default_billing_group\" SIZE=\"20\" MAXLENGTH=\"32\">
        </td><tr><td>
	<B>$l_defaultshippinggroup</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"default_shipping_group\" VALUE=\"$default_shipping_group\" SIZE=\"20\" MAXLENGTH=\"32\">
        </td><tr><td>
	<B>$l_carrierdependentcancelurl</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"dependent_cancel_url\" VALUE=\"$dependent_cancel_url\" SIZE=\"20\" MAXLENGTH=\"32\">
        </td><tr><td>
<B>$l_billingdaterollovertime:</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"billingdate_rollover_time\" VALUE=\"$billingdate_rollover_time\" SIZE=\"20\" MAXLENGTH=\"32\">
        </td><tr><td valign=top>	
	<B>$l_billingweekend_sunday</B></td><td>
    <INPUT TYPE=\"radio\" NAME=\"billingweekend_sunday\" VALUE=\"y\" ";
	if ($billingweekend_sunday == 'y') { echo "checked"; } echo "> $l_yes 
    <INPUT TYPE=\"radio\" NAME=\"billingweekend_sunday\" VALUE=\"n\" ";
	if ($billingweekend_sunday == 'n') { echo "checked"; } echo "> $l_no 
    </td><tr><td>

<B>$l_billingweekend_monday</B></td><td>
    <INPUT TYPE=\"radio\" NAME=\"billingweekend_monday\" VALUE=\"y\" ";
	if ($billingweekend_monday == 'y') { echo "checked"; } echo "> $l_yes 
    <INPUT TYPE=\"radio\" NAME=\"billingweekend_monday\" VALUE=\"n\" ";
	if ($billingweekend_monday == 'n') { echo "checked"; } echo "> $l_no 
    </td><tr><td>

<B>$l_billingweekend_tuesday</B></td><td>
    <INPUT TYPE=\"radio\" NAME=\"billingweekend_tuesday\" VALUE=\"y\" ";
	if ($billingweekend_tuesday == 'y') { echo "checked"; } echo "> $l_yes 
    <INPUT TYPE=\"radio\" NAME=\"billingweekend_tuesday\" VALUE=\"n\" ";
	if ($billingweekend_tuesday == 'n') { echo "checked"; } echo "> $l_no 
    </td><tr><td>

<B>$l_billingweekend_wednesday</B></td><td>
    <INPUT TYPE=\"radio\" NAME=\"billingweekend_wednesday\" VALUE=\"y\" ";
	if ($billingweekend_wednesday == 'y') { echo "checked"; } echo "> $l_yes 
    <INPUT TYPE=\"radio\" NAME=\"billingweekend_wednesday\" VALUE=\"n\" ";
	if ($billingweekend_wednesday == 'n') { echo "checked"; } echo "> $l_no 
    </td><tr><td>

<B>$l_billingweekend_thursday</B></td><td>
    <INPUT TYPE=\"radio\" NAME=\"billingweekend_thursday\" VALUE=\"y\" ";
	if ($billingweekend_thursday == 'y') { echo "checked"; } echo "> $l_yes 
    <INPUT TYPE=\"radio\" NAME=\"billingweekend_thursday\" VALUE=\"n\" ";
	if ($billingweekend_thursday == 'n') { echo "checked"; } echo "> $l_no 
    </td><tr><td>

<B>$l_billingweekend_friday</B></td><td>
    <INPUT TYPE=\"radio\" NAME=\"billingweekend_friday\" VALUE=\"y\" ";
	if ($billingweekend_friday == 'y') { echo "checked"; } echo "> $l_yes 
    <INPUT TYPE=\"radio\" NAME=\"billingweekend_friday\" VALUE=\"n\" ";
	if ($billingweekend_friday == 'n') { echo "checked"; } echo "> $l_no 
    </td><tr><td>

<B>$l_billingweekend_saturday</B></td><td>
    <INPUT TYPE=\"radio\" NAME=\"billingweekend_saturday\" VALUE=\"y\" ";
	if ($billingweekend_saturday == 'y') { echo "checked"; } echo "> $l_yes 
    <INPUT TYPE=\"radio\" NAME=\"billingweekend_saturday\" VALUE=\"n\" ";
	if ($billingweekend_saturday == 'n') { echo "checked"; } echo "> $l_no 
    </td><tr><td>



	</td><td>
	<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" value=\"$l_savechanges\">
	</FORM>";
	

?>
</body>
</html>







