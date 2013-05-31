<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('sourcereport')?>: 
<?php

echo "$category</h3>";

// intialize source array and count
$service_count = 0;
$sourcearray = array();

// initialize the indexes of source array
foreach ($servicesources AS $myresult) 
{
	$sourcename = $myresult['source'];
	$sourcearray["$sourcename"] = 0;
}

// populate the array
foreach ($servicesources AS $myresult) 
{
	$sourcename = $myresult['source'];
	$usid = $myresult['us_id'];
	$sourcearray["$sourcename"]++;
	$service_count++;
}

echo "<h2>".lang('added').": $service_count</h2><table>\n";

arsort ($sourcearray);

foreach ($sourcearray as $source=>$value) 
{
	echo "<td>$source</td><td>$value</td><tr>\n";
}  

echo "</table>\n";
?>
</body>
</html>
