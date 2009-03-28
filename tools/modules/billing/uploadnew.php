<html>
<body>
<?php
// Copyright (C) 2003-2005  Paul Yasi <paul@citrusdb.org>, read the README file for more information

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

// POST Variables
$userfile = $_FILES['userfile']['tmp_name'];

// get the path_to_citrus
        $query = "SELECT path_to_ccfile FROM settings WHERE id = 1";
        $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	$myresult = $result->fields;
        $path_to_ccfile = $myresult['path_to_ccfile'];

        // upload the file
        if(!empty($userfile)) {

        //copy the file to some permanent location
        copy($userfile, "$path_to_ccfile/newaccounts.txt");

        //destroy the file
        unlink($userfile);

        //display message
        echo("$l_uploadcomplete $userfile");
        }
	
	// redirect back to importnew page
	print "<script language=\"JavaScript\">window.location.href =\"index.php?load=billing&tooltype=module&type=tools&importnew=on&submit=on\";</script>";

?>

</body>
</html>
