<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_importccupdates</h3>";
// Copyright (C) 2002-2005  Paul Yasi <paul@citrusdb.org>, 
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

//GET Variables
$submit = $base->input['submit'];

if ($submit) {
// save information
	
	// get the path_to_citrus
	$query = "SELECT path_to_ccfile FROM settings WHERE id = 1";
        $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	$myresult = $result->fields;
        $path_to_ccfile = $myresult['path_to_ccfile'];
	
	$myfile = "$path_to_ccfile/newccupdates.txt";

	print "$l_fileuploaded: $myfile<p>";

	// OPEN THE FILE AND PROCESS IT

	$fp = @fopen($myfile, "r") or die ("$l_cannotopen $myfile");
	
	/*------------------------*/
	// Import format:
	// account_number, name, street, city, state, zip, cc number (masked), cc expiration
	// -----BEGIN PGP MESSAGE-----
	// ascii armored lines with credit card info
	// -----END PGP MESSAGE-----
	/*------------------------*/

	$linecount = 0;
	$armordata = "";
	$asciiarmor = FALSE;
	// get each whole line
	while ($line = @fgetcsv($fp, 4096)) {
	  
	  $linecount++;
	  //$DB->debug = true;

	  if ($linecount == 1) {
	    // update the plaintext billing record info
	    list($account_number, $name, $street, $city, $state, $zip, $cardnumber, $cardexp) = $line;
	    
	    // Update the billing record	
	    $query = "UPDATE billing SET
		name = '$name',
		street = '$street',
		city = '$city',
		state = '$state',
		zip = '$zip',
		creditcard_number = '$cardnumber',
		creditcard_expire = '$cardexp' 
		WHERE account_number = '$account_number'";	
	    
	    $result = $DB->Execute($query) or die ("$l_queryfailed");
	    echo "$query $l_changessaved, $l_account: $account_number<p>";		
	  }
	  else {
	    // update the asciiarmor encrypted_creditcard_number field
	    if (($line[0] == "-----BEGIN PGP MESSAGE-----") OR ($asciiarmor == TRUE)) {

	      // set a boolean to indicate we are reading the ascii armor now
	      $asciiarmor = TRUE;

	      // read in the line of asciiarmored data
	      $armordate .= "$line[0]\n";

	      // when we reach the end of the PGP message insert the data into the billing table
	      if ($line[0] == "-----END PGP MESSAGE-----") {
		$query = "UPDATE billing SET encrypted_creditcard_number = '$armordata' WHERE account_number = '$account_number'";
		$result = $DB->Execute($query) or die ("billing card update $l_queryfailed");  

		// reset line count and other markers when done
		echo "RESET LINECOUNT<p>";
		$linecount = 0;
		$armordata = "";
		$asciiarmor = FALSE;
		
	      } // end if END PGP MESSAGE

	    }// end if php message
	    
	  }// end if lincount
	  
	} // end while	       
	
	// close the file
	@fclose($fp) or die ("$l_cannotclose $myfile");
	
	// delete the file when we are done
	unlink($myfile);	
}

// uploadnew will redirect back to this file to perform the submit processing

echo "<FORM ACTION=\"index.php?load=billing&tooltype=module&type=tools&uploadccupdates=on\" METHOD=\"POST\" enctype=\"multipart/form-data\">
<table>
<td>$l_importfile:</td><td><input type=file name=\"userfile\"></td><tr> 
<td></td><td><br><input type=submit name=\"$l_import\" value=\"$l_import\"></td>
</table>
</form> 
";

?>
</body>
</html>
