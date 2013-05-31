<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<b style="font-size: 14pt;"><?php echo lang('notesforuser')?> <?php echo $user?></b>

<?php   
foreach ($tickets AS $myresult) 
{
	$id = $myresult['id'];
	$creation_date = $myresult['creation_date'];
	$mydatetime = $myresult['mydatetime'];
	$created_by = $myresult['created_by'];
	$notify = $myresult['notify'];
	$accountnum = $myresult['account_number'];
	$notify = $myresult['notify'];
	$status = $myresult['status'];
	$description = $myresult['description'];
	$name = $myresult['name'];
	$linkname = $myresult['linkname'];
	$linkurl = $myresult['linkurl'];
	$serviceid = $myresult['user_services_id'];
	$service_description = $myresult['service_description'];

	if ($serviceid == 0) {
		$serviceid = '';
		$service_description = '';
	}

	print "<tr>";

	// if this item has not been viewed yet, then print in green for brand new item
	if (!empty($lastview) AND $mydatetime > $lastview) {
		print "<table onmouseover='h(this);'".
			"onmouseout='dehnew(this);' bgcolor=\"#aaffaa\" width=\"720\" ".
			"cellpadding=5 style=\"border-top: 1px solid #888; ".
			"border-bottom: 1px solid #888;\">";      
	} elseif ($status == "not done"){
		print "<table onmouseover='h(this);'".
			"onmouseout='dehnew(this);' bgcolor=\"#ddeeff\" width=\"720\" ".
			"cellpadding=5 style=\"border-top: 1px solid #888; ".
			"border-bottom: 1px solid #888;\">";
	} else {
		// this is pending
		print "<table onmouseover='h(this);' onmouseout='deh(this);' ".
			"bgcolor=\"#ddddee\" width=\"720\" cellpadding=5 ".
			"style=\"border-top: 1px solid #888; border-bottom: 1px solid #888;\">";
	}

	print "<td width=10%><a href=\"$this->url_prefix/index.php/view/ticket/$id/$accountnum\">$id</a></td>";
	print "<td width=20%>$creation_date</td>";
	print "<td width=10%>$created_by</td>";
	print "<td width=20%><a href=\"$this->url_prefix/index.php/view/account/$accountnum\">$name</a></td>";
	print "<td width=10%>$status</td>";
	print "<td width=50% colspan=3><a href=\"$this->url_prefix/index.php/view/service/$serviceid/$accountnum\">$serviceid $service_description</a></td>";

	print "<tr><td width=100% colspan=8>&nbsp;";
	echo nl2br($description);
	echo "<a href=\"$linkurl\">$linkname</a>";

	// get the sub_history printed here
	$sub_history = $this->support_model->get_sub_history($id);

	foreach ($sub_history AS $mysubresult) 
	{
		$sub_creation_date = $mysubresult['creation_date'];
		$sub_created_by = $mysubresult['created_by'];
		$sub_description = $mysubresult['description'];

		print "<p>&nbsp;&nbsp;&nbsp;$sub_created_by: ";
		echo nl2br ($sub_description);
		echo "</p>\n";
	}

	// end the table block
	echo "</td>";

	print "<tr><td colspan=8 align=right>";
	print "<table><td>";
	print "<a href=\"$this->url_prefix/index.php/view/ticket/$id/$accountnum\">".
		lang('edit')."</a> | ";
	print "</td><td>";
	print "<a href=\"$this->url_prefix/index.php/tickets/pending/$id\">\n".
		lang('pending')."</a> | ";
	print "</td><td>";
	print "<a href=\"$this->url_prefix/index.php/tickets/complete/$id\">".
		lang('finished')."</a>";

	print "</td></table>";


	//<a href=\"$url_prefix/index.php?load=viewticket&type=fs&ticket=$id&acnum=$accountnum\">$l_edit</a>";
	//print " | <a href=\"$url_prefix/index.php?load=tickets&type=base&pending=on&id=$id\">$l_pending</a>"; 
	//print " | <a href=\"$url_prefix/index.php?load=tickets&type=base&completed=on&id=$id\">$l_finished</a></td></table>";
}
?>
</table><br>
