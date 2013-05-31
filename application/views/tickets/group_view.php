<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<b style="font-size: 14pt;"><?php echo lang('notesforgroups')?>: <?php echo $notify?></b>
	<?php   
foreach ($tickets AS $groupresult) 
{
	$id = $groupresult['id'];
	$creation_date = $groupresult['creation_date'];
	$mydatetime = $groupresult['mydatetime'];
	$notify = $groupresult['notify'];
	$created_by = $groupresult['created_by'];
	$accountnum = $groupresult['account_number'];
	$status = $groupresult['status'];
	$description = $groupresult['description'];
	$name = $groupresult['name'];
	$linkname = $groupresult['linkname'];
	$linkurl = $groupresult['linkurl'];
	$serviceid = $groupresult['user_services_id'];
	$service_description = $groupresult['service_description'];

	if ($serviceid == 0) {
		$serviceid = '';
		$servicedescription = '';
	}

	print "<tr>";

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
		print "<table onmouseover='h(this);' onmouseout='deh(this);' ".
			"bgcolor=\"#ddddee\" width=\"720\" cellpadding=5 ".
			"style=\"border-top: 1px solid #888; border-bottom: 1px solid #888;\">";
	}

	print "<td width=10%><a href=\"$this->url_prefix/index.php?load=viewticket&type=fs&ticket=$id&acnum=$accountnum\">$id</a></td>";
	print "<td width=20%>$creation_date</td>";
	print "<td width=10%>$created_by</td>";
	print "<td width=20%><a href=\"$this->url_prefix/index.php?load=viewaccount&type=fs&acnum=$accountnum\">$name</a></td>";
	print "<td width=10%>$status</td>";
	print "<td width=50% colspan=3><a href=\"$this->url_prefix/index.php?load=viewservice&type=fs&userserviceid=$serviceid&acnum=$accountnum\">$serviceid $service_description</a></td>";

	print "<tr><td width=100% colspan=8> &nbsp; ";
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
	print "<a href=\"$this->url_prefix/index.php/support/editticket/$id\">".
		lang('edit')."</a> | ";
	print "</td><td>";
	print "<a href=\"$this->url_prefix/index.php/tickets/pending/$id/$notify\">\n".
		lang('pending')."</a> | ";
	print "</td><td>";
	print "<a href=\"$this->url_prefix/index.php/tickets/complete/$id/$notify\">".
		lang('finished')."</a>";

	print "</td></table>";
	print "</td></table>";            
}

