<div id="toolcontent">
<?php
// Copyright (C) 2002-2006  Paul Yasi <paul@citrusdb.org>
// read the README file for more information

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


/*----------------------------------------------------------------------------*/
// Load tools when link clicked
/*----------------------------------------------------------------------------*/
if (!isset($base->input['load'])) { $base->input['load'] = ""; }
if (!isset($base->input['tooltype'])) { $base->input['tooltype'] = ""; }

$loadname = $base->input["load"];
$tooltype = $base->input["tooltype"];

if ($loadname == "") {
	include('./tools/version.php');
}
else
{
if ($tooltype == "module")
        {
		$filepath = "$path_to_citrus/tools/modules/$loadname/index.php";
		if (file_exists($filepath)) {
			include('./tools/modules/'.$loadname.'/index.php');
			echo "<center><b><a target=\"_blank\"
		       	href=\"help.html#tools_$loadname\"
			style=\"color: red; font-size: 10pt;\">?</a></b>
			</center>";
		}
        }

else 	{
	$filepath = "$path_to_citrus/tools/$loadname.php";
		if (file_exists($filepath)) {
			include('./tools/'.$loadname.'.php');
			echo "<center><b><a  target=\"_blank\" 
			href=\"help.html#tools_$loadname\"
			style=\"color: red; font-size: 10pt;\">?</a></b>
			</center>";
		}	
	}

}
?>


</div>
