<html>
<head>
<LINK href="<?php echo $this->url_prefix?>/citrus.css" type=text/css rel=STYLESHEET>
<LINK href="<?php echo $this->url_prefix?>/fullscreen.css" type=text/css rel=STYLESHEET>
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
</head>
<body bgcolor="#eeeedd" marginheight=0 marginwidth=1 leftmargin=1 rightmargin=0>

<table cellspacing=0 cellpadding=0 border=0>
<td bgcolor="#eeeedd" style="padding: 4px; font-size: 9pt;" width=60><b><?php echo lang('ticketnumber')?>
</b>
</td>
<td bgcolor="#eeeedd" style="padding: 4px; font-size: 9pt;" width=150><b><?php echo lang('datetime')?>
</b>
</td>
<td bgcolor="#eeeedd" style="padding: 4px; font-size: 9pt;" width=80><b><?php echo lang('createdby')?>
</b>
</td>
<td bgcolor="#eeeedd" style="padding: 4px; font-size: 9pt;" width=70><b><?php echo lang('notify')?>
</b>
</td>
<td bgcolor="#eeeedd" style="padding: 4px; font-size: 9pt;" width=60><b><?php echo lang('status')?>
</b>
</td>
<td bgcolor="#eeeedd" style="padding: 4px; font-size: 9pt;" width=261><b><?php echo lang('service')?>
</b>
</td>

<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$linecount = 1;

foreach($history->result() as $myresult) 
{
	$id = $myresult->id;
	$creation_date = $myresult->creation_date;
	$created_by = $myresult->created_by;
	$notify = $myresult->notify;
	$status = $myresult->status;
	$description = $myresult->description;
	$linkname = $myresult->linkname;
	$linkurl = $myresult->linkurl;
	$serviceid = $myresult->user_services_id;
	$service_description = $myresult->service_description;
		
	// translate the status
	switch($status) 
	{
		case 'automatic':
			$status = lang('automatic');
			break;
		case 'not done':
			$status = lang('notdone');
			break;
		case 'pending':
			$status = lang('pending');
			break;
		case 'completed':
			$status = lang('completed');
			break;
	}
	
	// alternate line colors
	if ($linecount & 1) 
	{
		print "<tr bgcolor=\"#ffffee\">";
	} 
	else 
	{
		print "<tr bgcolor=\"#ffffdd\">";
 	}

	print "<td style=\"border-top: 1px solid grey; padding-top: 2px; ".
	"padding-bottom: 2px; font-size: 9pt; font-weight: bold;\"><a target=\"_parent\" ". 
	"href=\"$this->url_prefix/index.php/support/editticket/$id\">".
	"$id</a> &nbsp;</td>";

	print "<td style=\"border-top: 1px solid grey; padding-top: 2px; ".
    "padding-bottom: 2px; font-size: 9pt; font-weight: bold;\">$creation_date &nbsp;</td>";
	print "<td style=\"border-top: 1px solid grey; padding-top: 2px; ".
    "padding-bottom: 2px; font-size: 9pt; font-weight: bold;\">$created_by &nbsp;</td>";
	print "<td style=\"border-top: 1px solid grey; padding-top: 2px; ".
    "padding-bottom: 2px; font-size: 9pt; font-weight: bold;\">$notify &nbsp;</td>";
	print "<td style=\"border-top: 1px solid grey; padding-top: 2px; ".
    "padding-bottom: 2px; font-size: 9pt; font-weight: bold;\">$status &nbsp;</td>";
	print "<td style=\"border-top: 1px solid grey; padding-top: 2px; ".
    "padding-bottom: 2px; font-size: 9pt; font-weight: bold;\">";

	// if they have a valid service id that is greater than zero, print the link to it here
	if ($serviceid > 0) 
	{
		print "<a href=\"$this->url_prefix/index.php/services/edit/$serviceid\" target=\"_parent\">$serviceid $service_description</a>&nbsp;";
	}

	if ($linkurl) 
	{
		print "<a href=\"$linkurl\" target=\"_new\">$linkname</a>";
	}

	print "&nbsp;</td>";

	// alternate line colors
	if ($linecount & 1) 
	{
		print "<tr bgcolor=\"#ffffee\">";
	} 
	else 
	{
		print "<tr bgcolor=\"#ffffdd\">";
	}

	// add br tags to line breaks with nl2br
	print "<td colspan=6 style=\"font-size: 10pt; padding-bottom: 5px;\">&nbsp;";
	echo nl2br($description);
	echo "<br>";

	// get the sub_history printed here
	$sub_history = $this->support_model->get_sub_history($id);
	foreach($sub_history as $mysubresult) 
	{
		$mydatetime = $mysubresult['month']."/".$mysubresult['day']." ".$mysubresult['hour'].":".$mysubresult['minute'];
		$sub_created_by = $mysubresult['created_by'];
		$sub_description = $mysubresult['description'];

		// if today, show time
		// if creation date not today, show date/time

		// add br tags to line breaks
		print "$mydatetime $sub_created_by: ";
		echo nl2br($sub_description);
		echo "<br>\n";
	}
	
	// end this table block
	echo "</td>";
  
	// increment line count to make even/odd coloring
	$linecount++;
}

// print the show all link
echo "<tr bgcolor=\"#dddddd\"><td style=\"padding: 5px; \"colspan=6><a href=\"".
"$this->url_prefix/index.php/customer/history/all\">";
echo lang('showall');
echo "...</a></td>";

?>
</table>
</body>
</html>
