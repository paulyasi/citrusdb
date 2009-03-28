<?php
// Copyright (C) 2006 Paul Yasi <paul@citrusdb.org>
// Read the README file for more information

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

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
	echo '$l_youmusthaveadmin<br>';
        exit; 
}

// GET Variables
if (!isset($base->input['filename'])) { $base->input['filename'] = ""; }
$filename = $base->input['filename'];


// get the path_to_citrus
	$query = "SELECT path_to_ccfile FROM settings WHERE id = 1";
        $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	$myresult = $result->fields;
        $path_to_ccfile = $myresult['path_to_ccfile'];
	
	$myfile = "$path_to_ccfile/$filename";

	// OPEN THE FILE AND PROCESS IT

	$fp = @fopen($myfile, "r") or die ("$l_cannotopen $myfile");

	// output the content headers
	header("Content-type: application/zip");
	header("Content-Disposition: filename=\"$filename\"");
	
	// output each whole line
	while ($line = @fgets($fp, 4096)) 
	{
		print "$line";
	} // end while

	// close the file
	@fclose($fp) or die ("$l_cannotclose $myfile");
?>
