<a href="<?php echo $this->url_prefix; ?>index.php/services"> [ 
<?php echo lang('undochanges');?> ]</a>

<?php
// if the user is admin or manager then have the option to show all services
// query user properties
$query = "SELECT * FROM user WHERE username='$this->user'";
$userresult = $this->db->query($query) or die ("query failed");
$myuserresult = $userresult->row_array();
$showall_permission = false;
if (($myuserresult['manager'] == 'y') OR ($myuserresult['admin'] == 'y')) 
{
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
if ($showall == 'y' & $showall_permission == true) 
{
	// print list of categories to choose from
	$query = "SELECT DISTINCT category FROM master_services ".
		"ORDER BY category";
	$result = $this->db->query($query) or die ("query failed");
	foreach ($result->result_array() as $myresult) 
	{
		$categoryname = $myresult['category'];
		echo "<a href=\"index.php?/services/create/showall#$categoryname\">$categoryname</a> | \n";
	}

	// set the query for the service listing
	$query = "SELECT * FROM master_services m ".
		"LEFT JOIN general g ON g.id = m.organization_id ".
		"WHERE selling_active = 'y' ".
		"ORDER BY category, pricerate, service_description";
} 
else 
{
	// print list of categories to choose from
	$query = "SELECT DISTINCT category FROM master_services ".
		"WHERE organization_id = '$my_organization_id' ORDER BY category";
	$result = $this->db->query($query) or die ("query failed");
	foreach ($result->result_array() as $myresult) 
	{
		$categoryname = $myresult['category'];
		echo "<a href=\"index.php/services/create#$categoryname\">$categoryname</a> | \n";
	}

	$query = "SELECT * FROM master_services m ".
		"WHERE selling_active = 'y' AND hide_online <> 'y' ".
		"AND organization_id = '$my_organization_id' ".
		"ORDER BY category, pricerate, service_description";
}
$result = $this->db->query($query) or die ("$l_queryfailed");
// Print HTML table of servicesresults
?>
<table border=0 cellspacing=1 cellpadding=5 width=720>
<tr><td bgcolor="#ccccdd"><b><?php echo lang('id');?></b></td>
<td bgcolor="#ccccdd"><b><?php echo lang('description');?></b></td>
<td bgcolor="#ccccdd"><b><?php echo lang('rate');?></b></td>
<td bgcolor="#ccccdd"><b><?php echo lang('frequency');?></b></td>
<td bgcolor="#ccccdd"><b><?php echo lang('organizationname');?></b></td>
<td bgcolor="#ccccdd">&nbsp;</td></tr>
<?php
$previouscategory = "";

foreach ($result->result_array() as $myrow) 
{
	// print category heading
	if ($myrow['category'] <> $previouscategory) 
	{
		echo "<tr bgcolor=\"#ccccdd\"><td colspan=6><b>". lang('category') . 
			": ". $myrow['category']."</b><a name=\"".$myrow['category']."\"></td></tr>\n";
	}
	$previouscategory = $myrow['category'];

	// print service listing
	print "<tr onmouseover='h(this);' onmouseout='deh(this);' ".
		"onmousedown='window.location.href=\"".$this->url_prefix."index.php/services/add_options/".
		$myrow['id']."\";' bgcolor=\"#ddddee\">".
		"<td>".$myrow['id']."</td><td>".$myrow['service_description']."</td>".
		"<td>".$myrow['pricerate']."</td><td>".$myrow['frequency']."</td>".
		"<td>".$myrow['organization_id']."</td><td align=center>".
		"<form style=\"margin-bottom:0;\" ".
		"action=\"".$this->url_prefix."index.php/services/add_options/".$myrow['id']."\" method=post>".
		"<input name=addbutton type=submit value=\"".lang('add')."\" ".
		"class=smallbutton>".
		"</form></td></tr>\n"; 

	// print the list of linked services under the service they are linked to      
	$query = "SELECT mfrom.id mfrom_id, ".
		"mfrom.service_description mfrom_description, ".
		"mto.id mto_id, mto.service_description mto_description, ".
		"mto.pricerate mto_pricerate, l.linkfrom, l.linkto ".
		"FROM linked_services l ".
		"LEFT JOIN master_services mfrom ON mfrom.id = l.linkfrom ".
		"LEFT JOIN master_services mto ON mto.id = l.linkto ".
		"WHERE l.linkfrom = ".$myrow['id'];

	$lresult = $this->db->query($query) or die ("query failed");

	foreach ($lresult->result_array() as $lmyresult) 
	{
		$todesc = $lmyresult['mto_description'];
		$toprice = $lmyresult['mto_pricerate'];
		print "<tr bgcolor=\"#ffffff\" cellpadding=0 cellspacing=0><td></td><td style=\"font-size: 10px\"; colspan=5 bgcolor=\"#eeeeff\"> + $todesc ".lang('currency')."$toprice</td></tr>\n";
	}
} 
echo "</table>\n";

?>
