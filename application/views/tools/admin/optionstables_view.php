<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('optionstables')?></h3>


<table cellpadding=5 cellspacing=1><tr bgcolor="#eeeeee">
<td><b><?php echo lang('name')?></b></td><td></td></tr>

<?php
foreach ($options_tables AS $myresult)
{
	// For ones the already exist put an Edit link
	// For ones that don't exist put a Create link
	$options_table = $myresult['options_table'];	

	$i = 0;

	foreach ($tableresult as $row) 
	{
		// check if a table named here matched the options_table 
		// specified, if it does set $i = 1

		if($row == $options_table) {$i = 1;};

		//print "Table: $row[0]<br>";
	}


	if (($i == 1) and ($options_table <> '')) // the table exists
	{
		print "<tr bgcolor=\"#eeeeee\"><td>$options_table</td><td>".lang('ready')."</td></tr>";
	}
	else // the table is new 
	{
		if ($options_table <> '') 
		{
			print "<tr bgcolor=\"#eeeeee\"><td>$options_table</td>".
				"<td><a href=\"$this->url_prefix/index.php//tools/admin/createoptionstable/$options_table\">".
				lang('create')."</a></td></tr>";
		}
	}

}

?>
</table>
<p>
To edit an options table you must use SQL queries to add fields to them or a 
utility like phpMyAdmin.  When new options tablesare created they are all 
created with an ID and user_services field to connect them to the user's 
services.  You may then addany fields you wish after those first two.
</body>
</html>
