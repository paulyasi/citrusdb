<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_services</h3>";
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
if (!isset($base->input['new'])) { $base->input['new'] = ""; }
if (!isset($base->input['link'])) { $base->input['link'] = ""; }
if (!isset($base->input['tax'])) { $base->input['tax'] = ""; }
if (!isset($base->input['options'])) { $base->input['options'] = ""; }
if (!isset($base->input['editoptions'])) { $base->input['editoptions'] = ""; }

$new = $base->input['new'];
$link = $base->input['link'];
$edit = $base->input['edit'];
$tax = $base->input['tax'];
$options = $base->input['options'];
$editoptions = $base->input['editoptions'];


// print links to all service functions
echo "[ <a href=\"index.php?load=services&tooltype=module&type=tools\">$l_editservices</a> ]";
echo "&nbsp;&nbsp;[ <a href=\"index.php?load=services&tooltype=module&new=on&type=tools\">$l_addnewservice</a> ]";
echo "&nbsp; &nbsp; [ <a href=\"index.php?load=services&tooltype=module&link=on&type=tools\">$l_linkservices</a> ]";
echo "&nbsp; &nbsp; [ <a href=\"index.php?load=services&tooltype=module&options=on&type=tools\">$l_optionstables</a> ]";
echo "&nbsp; &nbsp; [ <a href=\"index.php?load=services&tooltype=module&tax=on&type=tools\">$l_taxes</a> ]";


if ($new)
{
        include('newservice.php');
}
else if ($tax)
{
	include('taxes.php');
}
else if ($link)
{
        include('linkservice.php');
}
else if ($edit)
{
        include('editservice.php');
}
else if ($options)
{
        include('options.php');
}
else if ($editoptions)
{
        include('editoptions.php');
}
else
{

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
        echo "$l_youmusthaveadmin";
        exit;
}

print "<p><table cellpadding=5 cellspacing=1><tr bgcolor=\"#eeeeee\">
<td><b>$l_id</b></td>
<td><b>$l_description</b></td>
<td><b>$l_price</b></td>
<td><b>$l_frequency</b></td>
<td><b>$l_category</b></td>
<td><b>$l_activatenotify</b></td>
<td><b>$l_shutoffnotify</b></td>
<td></td><tr bgcolor=\"#eeeeee\">";

        // get the list of users from the table
        $query = "SELECT * FROM master_services ORDER BY category, pricerate, service_description";
        $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");

	$previouscategory = "";
	
	while ($myresult = $result->FetchRow())
	{
	$id = $myresult['id'];
    	$description = $myresult['service_description'];
    	$pricerate = $myresult['pricerate'];
	$frequency = $myresult['frequency'];
	$category = $myresult['category'];
	$activate_notify = $myresult['activate_notify'];
	$shutoff_notify = $myresult['shutoff_notify'];

	if($category <> $previouscategory) {
		print "<tr bgcolor=\"#dddddd\"><td colspan=8><b>$category</b></td></tr>\n";
		$previouscategory = $category;
	}

	print "<tr bgcolor=\"#eeeeee\"><td>$id</td><td>$description</td><td>$pricerate</td><td>$frequency</td><td>$category</td><td>$activate_notify</td><td>$shutoff_notify</td><td><a href=\"index.php?load=services&tooltype=module&edit=on&sid=$id&type=tools\">$l_edit</a></td><tr bgcolor=\"#eeeeee\">\n";
}
}
?>
</table>
</body>
</html>
