<a href="index.php/services"> [ <?php echo lang('undochanges');?> ]</a>

<?php
// if the user is admin or manager then have the option to show all services
// query user properties
$query = "SELECT * FROM user WHERE username='$user'";
$userresult = $DB->Execute($query) or die ("query failed");
$myuserresult = $userresult->row_array();
$showall_permission = false;
if (($myuserresult['manager'] == 'y') OR ($myuserresult['admin'] == 'y')) {
	$showall_permission = true;
	echo "<a href=\"index.php?load=services&type=module&create=on&showall=y\">".
		"[ ". lang('showall') ." ]</a>";
}
?>


<?php
$my_organization_id = $this->billing_model->get_organization_id($this->account_number);
?>
<p>

<?php
if ($showall == 'y' & $showall_permission == true) {
	// print list of categories to choose from
	$query = "SELECT DISTINCT category FROM master_services ".
		"ORDER BY category";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	while ($myresult = $result->FetchRow()) {
		$categoryname = $myresult['category'];
		echo "<a href=\"index.php?load=services&type=module&create=on&showall=y#$categoryname\">$categoryname</a> | \n";
	}

	// set the query for the service listing
	$query = "SELECT * FROM master_services m ".
		"LEFT JOIN general g ON g.id = m.organization_id ".
		"WHERE selling_active = 'y' ".
		"ORDER BY category, pricerate, service_description";
} else {
	// print list of categories to choose from
	$query = "SELECT DISTINCT category FROM master_services ".
		"WHERE organization_id = '$my_organization_id' ORDER BY category";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	while ($myresult = $result->FetchRow()) {
		$categoryname = $myresult['category'];
		echo "<a href=\"index.php?load=services&type=module&create=on#$categoryname\">$categoryname</a> | \n";
	}

	// set the query for the service listing
	$query = "SELECT * FROM master_services m ".
		"LEFT JOIN general g ON g.id = m.organization_id ".
		"WHERE selling_active = 'y' AND hide_online <> 'y' ".
		"AND organization_id = '$my_organization_id' ".
		"ORDER BY category, pricerate, service_description";
}
$DB->SetFetchMode(ADODB_FETCH_NUM);
$result = $DB->Execute($query) or die ("$l_queryfailed");
// Print HTML table of servicesresults

echo '<table border=0 cellspacing=1 cellpadding=5 width=720>';
echo "<tr><td bgcolor=\"#ccccdd\"><b>$l_id</b></td>".
"<td bgcolor=\"#ccccdd\"><b>$l_description</b></td>".
"<td bgcolor=\"#ccccdd\"><b>$l_rate</b></td>".
"<td bgcolor=\"#ccccdd\"><b>$l_frequency</b></td>".
"<td bgcolor=\"#ccccdd\"><b>$l_organizationname</b></td>".
"<td bgcolor=\"#ccccdd\">&nbsp;</td></tr>";

$previouscategory = "";

while ($myrow = $result->FetchRow()) {
	$category = $myrow[5];
	// print category heading
	if ($category <> $previouscategory) {
		echo "<tr bgcolor=\"#ccccdd\"><td colspan=6><b>$l_category: $category".
			"</b><a name=\"$category\"></td></tr>\n";
	}
	$previouscategory = $category;

	// print service listing
	printf("<tr onmouseover='h(this);' onmouseout='deh(this);' onmousedown='window.location.href=\"index.php?load=services&type=module&create=on&serviceid=$myrow[0]&addbutton=Add\";' bgcolor=\"#ddddee\"><td>%s</td><td>%s</td><td>$%s</td><td>%s</td><td>%s</td><td align=center><form style=\"margin-bottom:0;\" action=\"index.php\" method=post><input type=hidden name=load value=services><input type=hidden name=type value=module><input type=hidden name=create value=on><input type=hidden name=serviceid value=$myrow[0]><input name=addbutton type=submit value=\"$l_add\" class=smallbutton></form></td></tr>\n", $myrow[0], $myrow[1], $myrow[2], $myrow[3], $myrow[13]); 

	// print the list of linked services under the service they are linked to      
	$query = "SELECT mfrom.id mfrom_id, ".
		"mfrom.service_description mfrom_description, ".
		"mto.id mto_id, mto.service_description mto_description, ".
		"mto.pricerate mto_pricerate, l.linkfrom, l.linkto ".
		"FROM linked_services l ".
		"LEFT JOIN master_services mfrom ON mfrom.id = l.linkfrom ".
		"LEFT JOIN master_services mto ON mto.id = l.linkto ".
		"WHERE l.linkfrom = $myrow[0]";

	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$lresult = $DB->Execute($query) or die ("$l_queryfailed");

	while ($lmyresult = $lresult->FetchRow()) {
		$todesc = $lmyresult['mto_description'];
		$toprice = $lmyresult['mto_pricerate'];
		print "<tr bgcolor=\"#ffffff\" cellpadding=0 cellspacing=0><td></td><td style=\"font-size: 10px\"; colspan=5 bgcolor=\"#eeeeff\"> + $todesc $l_currency$toprice</td></tr>\n";
	}
} 
echo "</table>\n";

}

?>
