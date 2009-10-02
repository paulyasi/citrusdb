<?php
// Copyright (C) 2002-2008  Paul Yasi <paul@citrusdb.org>, read the README file for more information

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
if (!isset($base->input['sid'])) { $base->input['sid'] = ""; }
if (!isset($base->input['service_description'])) { $base->input['service_description'] = ""; }
if (!isset($base->input['pricerate'])) { $base->input['pricerate'] = ""; }
if (!isset($base->input['frequency'])) { $base->input['frequency'] = ""; }
if (!isset($base->input['options_table'])) { $base->input['options_table'] = ""; }
if (!isset($base->input['category'])) { $base->input['category'] = ""; }
if (!isset($base->input['selling_active'])) { $base->input['selling_active'] = ""; }
if (!isset($base->input['hide_online'])) { $base->input['hide_online'] = ""; }
if (!isset($base->input['activate_notify'])) { $base->input['activate_notify'] = ""; }
if (!isset($base->input['shutoff_notify'])) { $base->input['shutoff_notify'] = ""; }
if (!isset($base->input['modify_notify'])) { $base->input['modify_notify'] = ""; }
if (!isset($base->input['support_notify'])) { $base->input['support_notify'] = ""; }
if (!isset($base->input['activation_string'])) { $base->input['activation_string'] = ""; }
if (!isset($base->input['usage_label'])) { $base->input['usage_label'] = ""; }
if (!isset($base->input['carrier_dependent'])) {$base->input['carrier_dependent'] = ""; }

$submit = $base->input['submit'];
$sid = $base->input['sid'];
$service_description = $base->input['service_description'];
$pricerate = $base->input['pricerate'];
$frequency = $base->input['frequency'];
$options_table = $base->input['options_table'];
$category = $base->input['category'];
$selling_active = $base->input['selling_active'];
$hide_online = $base->input['hide_online'];
$activate_notify = $base->input['activate_notify'];
$shutoff_notify = $base->input['shutoff_notify'];
$modify_notify = $base->input['modify_notify'];
$support_notify = $base->input['support_notify'];
$activation_string = $base->input['activation_string'];
$usage_label = $base->input['usage_label'];
$carrier_dependent = $base->input['carrier_dependent'];

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
        echo "$l_youmusthaveadmin<br>";
        exit;
}

if ($submit) {
  // update the table
  $query = "UPDATE master_services ".
    "SET service_description = '$service_description', ".
    "pricerate = '$pricerate', ".
    "frequency = '$frequency', ".
    "options_table = '$options_table', ".
    "category = '$category', ".
    "selling_active = '$selling_active', ".
    "hide_online = '$hide_online', ".
    "activate_notify = '$activate_notify', ".
    "shutoff_notify = '$shutoff_notify', ".
    "modify_notify = '$modify_notify',".
    "support_notify = '$support_notify',".    
    "activation_string = '$activation_string', ".
    "usage_label = '$usage_label', ".
    "carrier_dependent = '$carrier_dependent' ".
    "WHERE id = '$sid'";
  
  $result = $DB->Execute($query) or die ("$l_queryfailed");

  print "<h3>$l_changessaved</h3> [<a href=\"index.php?load=services&tooltype=module&type=tools\">$l_done</a>]";
}

$query = "SELECT * FROM master_services WHERE id = $sid";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");

$myresult = $result->fields;
$service_description = $myresult['service_description'];
$pricerate = $myresult['pricerate'];
$frequency = $myresult['frequency'];
$options_table = $myresult['options_table'];
$category = $myresult['category'];
$selling_active = $myresult['selling_active'];
$hide_online = $myresult['hide_online'];
$activate_notify = $myresult['activate_notify'];
$shutoff_notify = $myresult['shutoff_notify'];
$modify_notify = $myresult['modify_notify'];
$support_notify = $myresult['support_notify'];
$activation_string = $myresult['activation_string'];
$usage_label = $myresult['usage_label'];
$organization_id = $myresult['organization_id'];
$carrier_dependent = $myresult['carrier_dependent'];

echo "<H3>$l_editservices</H3><P>";

// get the organization info
$query = "SELECT org_name FROM general WHERE id = $organization_id LIMIT 1";
$orgresult = $DB->Execute($query) or die ("$l_queryfailed");
$myorgresult = $orgresult->fields;
$organization_name = $myorgresult['org_name'];

echo "<h3>$l_organizationname: $organization_name</h3>

	<FORM ACTION=\"index.php\" METHOD=\"GET\">
	<B>$l_description</B><BR>
        <INPUT TYPE=\"TEXT\" NAME=\"service_description\"
VALUE=\"$service_description\" MAXLENGTH=\"128\"><P>
	<B>$l_price</B><BR>
        <INPUT TYPE=\"TEXT\" NAME=\"pricerate\" VALUE=\"$pricerate\" SIZE=\"20\" MAXLENGTH=\"32\"><P>
	<B>$l_frequency</B><BR>
        <INPUT TYPE=\"TEXT\" NAME=\"frequency\" VALUE=\"$frequency\" SIZE=\"20\" MAXLENGTH=\"32\"><P>
	<B>$l_optionstable</B><BR>
        <INPUT TYPE=\"TEXT\" NAME=\"options_table\" VALUE=\"$options_table\" SIZE=\"20\" MAXLENGTH=\"32\"><P>
	<B>$l_category</B><BR>
        <INPUT TYPE=\"TEXT\" NAME=\"category\" VALUE=\"$category\" SIZE=\"20\" MAXLENGTH=\"32\"><P>";
	
	echo "<B>$l_sellingactive</B>";
	if ($selling_active == 'y')
        {
	echo "<input type=\"radio\" name=selling_active value=\"y\" checked>$l_yes<input type=\"radio\" name=selling_active value=\"n\">$l_no<p>";
	} else {
	echo "<input type=\"radio\" name=selling_active value=\"y\">$l_yes<input type=\"radio\" name=selling_active value=\"n\" checked>$l_no<p>";
	}
	
	echo "<B>$l_hideonline</B>";

	if ($hide_online == 'y')
        {
	echo "<input type=\"radio\" name=hide_online value=\"y\" checked>$l_yes
		<input type=\"radio\" name=hide_online value=\"n\">$l_no<p>";
	} else {
	echo "<input type=\"radio\" name=hide_online value=\"y\">$l_yes
		<input type=\"radio\" name=hide_online value=\"n\" checked>$l_no<p>";
	}
	
	echo "<B>$l_activatenotify</B>         
        <INPUT TYPE=\"text\" NAME=\"activate_notify\" VALUE=\"$activate_notify\"><P>
	<B>$l_shutoffnotify</B>         
        <INPUT TYPE=\"text\" NAME=\"shutoff_notify\" VALUE=\"$shutoff_notify\"><P>                                    
	<B>$l_modifynotify</B>         
        <INPUT TYPE=\"text\" NAME=\"modify_notify\" VALUE=\"$modify_notify\"><P>

	<B>$l_supportnotify</B>         
<INPUT TYPE=\"text\" NAME=\"support_notify\" VALUE=\"$support_notify\"><P>

	<b>$l_activationstring</b>
	<input type=text name=activation_string value=\"$activation_string\">
	<p>
	<b>$l_usagelabel</b>
	<input type=text name=usage_label value=\"$usage_label\">
	<p>
	<b>$l_carrierdependent</b>";


	if ($carrier_dependent == 'y')
        {
	echo "<input type=\"radio\" name=carrier_dependent value=\"y\" checked>$l_yes
		<input type=\"radio\" name=carrier_dependent value=\"n\">$l_no<p>";
	} else {
	echo "<input type=\"radio\" name=carrier_dependent value=\"y\">$l_yes
		<input type=\"radio\" name=carrier_dependent value=\"n\" checked>$l_no<p>";
	}

	echo "<p>
	<input type=hidden name=sid value=\"$sid\">
	<input type=hidden name=load value=services>
        <input type=hidden name=tooltype value=module>
	<input type=hidden name=type value=tools>
        <input type=hidden name=edit value=on>
	<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_submit\">
	</FORM>";


?>