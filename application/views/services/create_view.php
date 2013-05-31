<a href="<?php echo $this->url_prefix; ?>/index.php/services"> [ 
<?php echo lang('undochanges');?> ]</a>

<?php
// if the user is admin or manager then have the option to show all services
if ($showall_permission == TRUE)
{
	echo "<a href=\"$this->url_prefix/index.php/services/create/showall\">".
		"[ ". lang('showall') ." ]</a>";
}
?>

<p>

<?php
if ($showall == 'y' & $showall_permission == true) 
{
	// print list of categories to choose from
	foreach ($service_categories as $myresult) 
	{
		$categoryname = $myresult['category'];
		echo "<a href=\"$this->url_prefix/index.php/services/create/showall#$categoryname\">$categoryname</a> | \n";
	}

} 
else 
{
	// print list of categories to choose from
	foreach ($service_categories as $myresult) 
	{
		$categoryname = $myresult['category'];
		echo "<a href=\"$this->url_prefix/index.php/services/create#$categoryname\">$categoryname</a> | \n";
	}

}
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

foreach ($master_service_list as $myrow) 
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
		"onmousedown='window.location.href=\"".$this->url_prefix."/index.php/services/add_options/".
		$myrow['id']."\";' bgcolor=\"#ddddee\">".
		"<td>".$myrow['id']."</td><td>".$myrow['service_description']."</td>".
		"<td>".$myrow['pricerate']."</td><td>".$myrow['frequency']."</td>".
		"<td>".$myrow['organization_id']."</td><td align=center>".
		"<form style=\"margin-bottom:0;\" ".
		"action=\"".$this->url_prefix."/index.php/services/add_options/".$myrow['id']."\" method=post>".
		"<input name=addbutton type=submit value=\"".lang('add')."\" ".
		"class=smallbutton>".
		"</form></td></tr>\n"; 

	// print the list of linked services under the service they are linked to      
	$linked_services = $this->service_model->linked_services($myrow['id']);

	foreach ($linked_services as $lmyresult) 
	{
		$todesc = $lmyresult['mto_description'];
		$toprice = $lmyresult['mto_pricerate'];
		print "<tr bgcolor=\"#ffffff\" cellpadding=0 cellspacing=0><td></td><td style=\"font-size: 10px\"; colspan=5 bgcolor=\"#eeeeff\"> + $todesc ".lang('currency')."$toprice</td></tr>\n";
	}
} 
?>
</table>

