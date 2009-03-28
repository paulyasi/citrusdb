<?php
// Copyright (C) 2002  Paul Yasi <paul@citrusdb.org>, read the README file for more information
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
if (!isset($base->input['carrier_dependent'])) { $base->input['carrier_dependent'] = ""; }
if (!isset($base->input['activation_string'])) { $base->input['activation_string'] = ""; }
if (!isset($base->input['usage_label'])) { $base->input['usage_label'] = ""; }
if (!isset($base->input['organization_id'])) { $base->input['organization_id'] = ""; }


$submit = $base->input['submit'];
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
$carrier_dependent = $base->input['carrier_dependent'];
$activation_string = $base->input['activation_string'];
$usage_label = $base->input['usage_label'];
$organization_id = $base->input['organization_id'];

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
        echo "$l_youmusthaveadmin";
        exit;
}

if ($submit) {
	// insert groupname and username into the groups table
	$query = "INSERT INTO master_services 
(service_description,pricerate,frequency,options_table,category,selling_active,activate_notify,shutoff_notify,hide_online,activation_string,usage_label,organization_id, modify_notify, carrier_dependent) 
VALUES
('$service_description','$pricerate','$frequency','$options_table','$category','$selling_active','$activate_notify','$shutoff_notify','$hide_online','$activation_string','$usage_label','$organization_id', '$modify_notify', '$carrier_dependent')";
	$result = $DB->Execute($query) or die ("$l_queryfailed");

	print "<h3>$l_changessaved</h3> [<a href=\"index.php?load=services&tooltype=module&type=tools\">done</a>]";
}

echo "<H3>$l_addservice</H3>
	<P>
	<FORM ACTION=\"index.php\" METHOD=\"GET\">";

// pick an organization that this service belongs to
// print list of organizations to choose from
$query = "SELECT id,org_name FROM general";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
echo "<b>$l_organizationname</b><br><select name=\"organization_id\">";
while ($myresult = $result->FetchRow()) {
        $myid = $myresult['id'];
        $myorg = $myresult['org_name'];
        echo "<option value=\"$myid\">$myorg</option>";
}
echo "</select><p>";


echo "<B>$l_description</B><BR>
        <INPUT TYPE=\"TEXT\" NAME=\"service_description\" VALUE=\"\"
SIZE=\"20\" MAXLENGTH=\"128\"><P>
	<B>$l_price</B><BR>
        <INPUT TYPE=\"TEXT\" NAME=\"pricerate\" VALUE=\"\" SIZE=\"20\" MAXLENGTH=\"32\"><P>
	<B>$l_frequency</B><BR>
        <INPUT TYPE=\"TEXT\" NAME=\"frequency\" VALUE=\"\" SIZE=\"20\" MAXLENGTH=\"32\"><P>
	<B>$l_optionstables</B><BR>
        <INPUT TYPE=\"TEXT\" NAME=\"options_table\" VALUE=\"\" SIZE=\"20\" MAXLENGTH=\"32\"><P>
	<B>$l_category</B><BR>
        <INPUT TYPE=\"TEXT\" NAME=\"category\" VALUE=\"\" SIZE=\"20\" MAXLENGTH=\"32\"><P>
	<B>$l_sellingactive</B> 
	<input type=\"radio\" name=selling_active value=\"y\" checked>$l_yes<input type=\"radio\" name=selling_active value=\"n\">$l_no<p>
<B>$l_hideonline</B> 
	<input type=\"radio\" name=hide_online value=\"y\">$l_yes<input
type=\"radio\" name=hide_online value=\"n\" checked>$l_no<p>
        <B>$l_activatenotify</B>         
        <INPUT TYPE=\"text\" NAME=\"activate_notify\" VALUE=\"\"><P>
	<B>$l_shutoffnotify</B>         
        <INPUT TYPE=\"text\" NAME=\"shutoff_notify\" VALUE=\"\"><P>
	<B>$l_modifynotify</B>         
        <INPUT TYPE=\"text\" NAME=\"modify_notify\" VALUE=\"\"><P>
<B>$l_activationstring</B>         
        <INPUT TYPE=\"text\" NAME=\"activation_string\" VALUE=\"\"><P>
<B>$l_usagelabel</B>         
        <INPUT TYPE=\"text\" NAME=\"usage_label\" VALUE=\"\">";

	echo "<p>
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
	<input type=hidden name=load value=services>
	<input type=hidden name=tooltype value=module>
	<input type=hidden name=type value=tools>
	<input type=hidden name=new value=on>
	<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_add\">
	</FORM>";


?>



