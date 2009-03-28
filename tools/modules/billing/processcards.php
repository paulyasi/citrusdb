<?php
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

// get the filename to process
$fileid = $base->input['fileid'];

// select the info from general to get the path_to_ccfile
$query = "SELECT * FROM general WHERE id = '1'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$ccfileresult = $DB->Execute($query) 
	or die ("$l_queryfailed");
$myccfileresult = $ccfileresult->fields;
$path_to_ccfile = $myccfileresult['path_to_ccfile'];
$ccexportvarorder = $myccfileresult['ccexportvarorder'];	

// open the file
$filename = "$path_to_ccfile/export$fileid.csv";
$handle = fopen($filename, 'r'); // open the file

// do stuff in here with the file to process the transactions

fclose($handle); // close the file

echo "
<html>
<body bgcolor=\"#ffffff\">
filename: export$fileid.csv
<p>
test default credit card plugin, this will take the exported file and process it via the plugin that can be replaced to process whatever card processor you want.
</body>
</html>
";

?>
