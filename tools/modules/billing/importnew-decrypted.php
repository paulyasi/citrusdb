<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_importnewaccounts 2.0 migration version</h3>";
// Copyright (C) 2002-2010  Paul Yasi (paul at citrusdb.org)
// Read the README file for more information
//
// takes the 2.0 formatted new account import file and imports it into a 1.3 database
// to be used when migrating from a 1.3 to 2.0 compatible order system
//
// the 1.3 config will need the new gpg variables and 2.0's citrus_base.php include file
//
// This will decrypt carddata while it's being imported so it need your passphrase here
// remove this passphrase after you are done migrating your database and order system
//
$passphrase = "";
//
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
  
  $myfile = "$path_to_ccfile/newaccounts.txt";
  
  print "$l_fileuploaded: $myfile<p>";
  
  // OPEN THE FILE AND PROCESS IT
  
  $fp = @fopen($myfile, "r") or die ("$l_cannotopen $myfile");
  
  /*------------------------*/
  // Import format (3+ lines in database field order:
  // customer record line 1:  customer table fields
  // billing record line 2: billing table fields
  // services record line 3: service id, options table fields
  // (optional 3+n) more service items ...
  // -----BEGIN PGP MESSAGE-----
  // ascii armored lines with credit card info
  // -----END PGP MESSAGE-----
  // line 1 etc...
  /*------------------------*/

  // initialize variables
  $linecount = 0;
  $armordata = "";
  $asciiarmor = FALSE;
  
  // get each whole line
  while ($line = @fgetcsv($fp, 4096)) {
    
    $linecount++;
    //$DB->debug = true;
    
    if ($linecount == 1) {
      // make the customer record	
      $query = "INSERT into customer (".
	"source,".
	"signup_date,".
	"name,".
	"company,".
	"street,".
	"city,".
	"state,".
	"country,".
	"zip,".
	"phone,".
	"alt_phone,".
	"fax,".
	"contact_email,".
	"secret_question,".
	"secret_answer,".
	"account_manager_password) ".
	"VALUES (".
	"'$line[0]',".
	"CURRENT_DATE,".
	"'$line[1]',".
	"'$line[2]',".
	"'$line[3]',".
	"'$line[4]',".
	"'$line[5]',".
	"'$line[6]',".
	"'$line[7]',".
	"'$line[8]',".
	"'$line[9]',".
	"'$line[10]',".
	"'$line[11]',".
	"'$line[13]',".
	"'$line[14]',".
	"'$line[15]')";
      
      $result = $DB->Execute($query) 
	or die ("customer insert $l_queryfailed");
      
      // get the inserted id of the customer record
      $myinsertid = $DB->Insert_ID();  
      $account_number=$myinsertid;
      echo "$l_id: $account_number<p>";
      
      // get the next billing date value
      $mydate = get_nextbillingdate();
      $from_date = $mydate;

      $organization_id = $line[16];
      
      // make a new default billing record
      $query = "INSERT into billing ".
	"(account_number,next_billing_date,from_date, payment_due_date, organization_id) ".
	"VALUES ('$account_number','$mydate','$mydate','$mydate', '$organization_id')";
      $result = $DB->Execute($query) or die ("insert billing $l_queryfailed");
      
      //
      // set the default billing ID for the customer record
      //
      $billingid = $DB->Insert_ID();
      $query = "UPDATE customer ". 
	"SET default_billing_id = '$billingid' ".
	"WHERE account_number = $account_number";
      $result = $DB->Execute($query)
	or die ("customer update $l_queryfailed");
      
      echo "$l_added $l_accountnumber: $account_number<p>";
    }
    elseif ($linecount == 2) {
      // make the billing record
      $query = "UPDATE billing ".
	"SET name = '$line[0]',".
	"company = '$line[1]',".
	"street = '$line[2]',".
	"city = '$line[3]',".
	"state = '$line[4]',".
	"country = '$line[5]',".
	"zip = '$line[6]',".
	"phone = '$line[7]',".
	"fax = '$line[8]',".
	"contact_email = '$line[9]',".
	"billing_type = '$line[10]',".
	"creditcard_number = '$line[11]',".
	"creditcard_expire = '$line[12]' ".
	"WHERE id = $billingid";
      
      $result = $DB->Execute($query) 
	or die ("billing update $l_queryfailed");
      
      // add the to_date automatically
      $billing_type = $line[10];
      automatic_to_date($DB, $from_date, $billing_type, $billingid);
      echo "$l_added $l_billingid: $billingid<p>";		
    } else {
      // look for the BEGIN PGP MESSAGE block text after all service items
      if (($line[0] == "-----BEGIN PGP MESSAGE-----") OR ($asciiarmor == TRUE)) {
	// set a boolean to indicate reading of ascii armor
	// and not anything else
	$asciiarmor = TRUE;
	
	// read in the ASCII ARMORED credit card data ad the end
	// and put it into the billing record
	$armordata .= "$line[0]\n";

	if ($line[0] == "-----END PGP MESSAGE-----") {
	  // decrypt the card number

	  // write the encrypted_creditcard_number to a temporary file
	  // and decrypt that file to stdout to get the CC
	  // select the path_to_ccfile from settings
	  $query = "SELECT path_to_ccfile FROM settings WHERE id = '1'";
	  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	  $ccfileresult = $DB->Execute($query) 
	    or die ("$l_queryfailed");
	  $myccfileresult = $ccfileresult->fields;
	  $path_to_ccfile = $myccfileresult['path_to_ccfile'];
	  
	  // open the file
	  $cipherfilename = "$path_to_ccfile/ciphertext.tmp";
	  $cipherhandle = fopen($cipherfilename, 'w') or die ("cannot open $cipherfilename");
	  
	  // write the ciphertext we want to decrypt into the file
	  fwrite($cipherhandle, $encrypted_creditcard_number);
	  
	  // close the file
	  fclose($cipherhandle);
	  
	  // destroy the variable before we use it again
	  unset($decrypted);
	  
	  $gpgcommandline = "$gpg_decrypt $cipherfilename";
	  $decrypted = decrypt_command($gpgcommandline, $passphrase);
	  
	  // if there is a gpg error, stop here
	  if (substr($decrypted,0,5) == "error") {
	    die ("Credit Card Encryption Error: $decrypted");
	  }
	  
	  // insert the card number into the billing table
	  $query = "UPDATE billing SET creditcard_number = '$cardnumber' WHERE id = $billingid";
	  $result = $DB->Execute($query) or die ("billing card update $l_queryfailed");  

	  // reset line count and other markers when done
	  echo "RESET LINECOUNT<p>";
	  $linecount = 0;
	  $armordata = "";
	  $asciiarmor = FALSE;
	}
      } else {
	// get the first number for the service id
	// shift everything else up one
	
	$serviceid = array_shift($line);
	
	// make fieldvalues string with the rest of the items
	$fieldvalues = ""; // empty it out
	foreach ($line as $mykey => $myvalue) {
	  $fieldvalues .= ',\'' . $myvalue . '\'';
	}
	$fieldvalues = substr($fieldvalues, 1);
	
	//echo "fieldvalules: $fieldvalues<P>";
	// insert the rest into options_table if necessary
	
	// make the creation date YYYY-MM-DD HOUR:MIN:SEC
	$mydate = date("Y-m-d H:i:s");
	
	// get the default billing id
	$default_billing_id = $billingid;
	
	// insert the new service into user_services table
	$query = "INSERT into user_services (account_number, ".
	  "master_service_id, billing_id, start_datetime, ".
	  "salesperson) ".
	  "VALUES ('$account_number', '$serviceid', ".
	  "'$default_billing_id', '$mydate', '$user')";
	$result = $DB->Execute($query) 
	  or die ("user_services insert $l_queryfailed");
	
	// Get the ID of the row the insert was to for 
	// the options_table query
	$myinsertid = $DB->Insert_ID();
	
	// insert values into the options table
	// skip this if there is no options_table_name 
	// for this service
	
	// get the info about the service
	$query = "SELECT * FROM master_services WHERE id = $serviceid";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$query master_services select $l_queryfailed");
	$myresult = $result->fields;	
	$servicename = $myresult['service_description'];
	$activate_notify = $myresult['activate_notify'];
	$optionstable = $myresult['options_table'];
	
	// insert data into the options table if there is one
	if ($optionstable <> '') {
	  $query = "INSERT into $optionstable () ".
	    "VALUES ('',$myinsertid,$fieldvalues)";
	  $result = $DB->Execute($query) 
	    or die ("options table insert $l_queryfailed");
	}
	
	// insert any linked_services into the user_services table
	$query = "SELECT * FROM linked_services ". 
	  "WHERE linkfrom = $serviceid";
	$result = $DB->Execute($query) 
	  or die ("$l_queryfailed");
	while ($myresult = $result->FetchRow()) {
	  $linkto = $myresult['linkto'];
	  // insert the linked service now
	  $query = "INSERT into user_services (account_number, master_service_id, billing_id, start_datetime, salesperson) VALUES ('$account_number', '$linkto', '$default_billing_id', '$mydate', '$user')";
	  $result = $DB->Execute($query) or die ("linked services insert $l_queryfailed");
	}
	
	// add an entry to the customer history about service
	service_message('added', $account_number,
			$serviceid, $myinsertid, NULL, NULL);
	
	/*******************************************
	 // add an entry to the customer_history and 
	 //the activate_notify support user/group 
	 //that this service was added
	 
	 // if there is an activate_notify user/group make 
	 // the history say not done instead of automatic
	 if ($activate_notify <> '') {$status = "not done";} 
	 else {$status = "automatic";}
	 
	 // add to customer_history
	 $desc = "$l_added $servicename [ <a target=\"_parent\"href=\"index.php?load=services&type=module&edit=on&userserviceid=$myinsertid&servicedescription=ADDED%20$servicename&optionstable=$optionstable&editbutton=Edit\">$l_view</a> ]";
	 $query = "INSERT into customer_history (creation_date,created_by,notify,account_number,status,description) VALUES (CURRENT_TIMESTAMP,'$user','$activate_notify','$account_number','$status','$desc')";
	 $result = $DB->Execute($query) or die ("$l_queryfailed");			*******************************************/
	echo "$l_added $l_service: $serviceid $l_to $account_number<p>";		
      } // end if for "-" line record seperator
    } // end if make service record
  } // end while	       
  
  // close the file
  @fclose($fp) or die ("$l_cannotclose $myfile");
  
  // delete the file when we are done
  unlink($myfile);

  // log the importing of accounts
  log_activity($DB,$user,$account_number,'import','customer',0,'success');  
 }

// uploadnew will redirect back to this file to perform the submit processing

echo "<FORM ACTION=\"index.php?load=billing&tooltype=module&type=tools&uploadnew=on\" METHOD=\"POST\" enctype=\"multipart/form-data\">
<table>
<td>$l_importfile:</td><td><input type=file name=\"userfile\"></td><tr> 
<td></td><td><br><input type=submit name=\"$l_import\" value=\"$l_import\"></td>
</table>
</form> 
";

?>
</body>
</html>
