<?php
// Copyright (C) 2002-2010  Paul Yasi (paul at citrusdb.org)
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

echo "<h3>fix broken batch id</h3>
<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"include/CalendarPopup.js\"></SCRIPT>
	<SCRIPT LANGUAGE=\"JavaScript\">
	var cal = new CalendarPopup();
	</SCRIPT>";

//GET Variables
if (!isset($base->input['billingdate'])) { $base->input['billingdate'] = ""; }
if (!isset($base->input['organization_id'])) { $base->input['organization_id'] = ""; }
if (!isset($base->input['billingdate1'])) { $base->input['billingdate1'] = ""; }
if (!isset($base->input['billingdate2'])) { $base->input['billingdate2'] = ""; }
if (!isset($base->input['passphrase'])) { $base->input['passphrase'] = ""; }
if (!isset($base->input['batchid'])) { $base->input['batchid'] = ""; }


$submit = $base->input['submit'];
$billingdate = $base->input['billingdate'];
$organization_id = $base->input['organization_id'];
$billingdate1 = $base->input['billingdate1'];
$billingdate2 = $base->input['billingdate2'];
$passphrase = $base->input['passphrase'];
$batchid = $base->input['batchid'];

print "$batchid";

// make sure the user is in a group that is allowed to run this

if ($submit) {
	
  //$DB->debug = true;

  /*--------------------------------------------------------------------------*/
  // TODO: make a file and sign it first to verify the passphrase entered
  // before we start making a new batch for them
  /*--------------------------------------------------------------------------*/
  // select the path_to_ccfile from settings
  $query = "SELECT path_to_ccfile FROM settings WHERE id = '1'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $ccfileresult = $DB->Execute($query)
    or die ("$l_queryfailed");
  $myccfileresult = $ccfileresult->fields;
  $path_to_ccfile = $myccfileresult['path_to_ccfile'];  
  
  // make a file to sign
  $signfilename = "$path_to_ccfile/signtext.tmp";
  $signhandle = fopen($signfilename, 'w') or die ("cannot open $signfilename");

  // write some example text to sign with a private key
  $signtext = "Sign this";
  fwrite($signhandle, $signtext);

  // close the file
  fclose($signhandle);

  $gpgsigncommand = "$gpg_sign $signfilename";
  $signed = sign_command($gpgsigncommand, $passphrase);

  // if there is a gpg error, stop here
  if (substr($signed,0,5) == "error") {
    die ("Signature Error: $signed");
  }      

	/*--------------------------------------------------------------------*/
	// print the credit card billing to a file
	/*--------------------------------------------------------------------*/

	// select the info from general to get the export variables
	$query = "SELECT ccexportvarorder,exportprefix FROM general WHERE id = '$organization_id'";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$ccvarresult = $DB->Execute($query) 
		or die ("$l_queryfailed");
	$myccvarresult = $ccvarresult->fields;
	$ccexportvarorder = $myccvarresult['ccexportvarorder'];
	$exportprefix = $myccvarresult['exportprefix'];	
	
	// convert the $ccexportvarorder &#036; dollar signs back to actual dollar signs and &quot; back to quotes
	$ccexportvarorder = str_replace( "&#036;"           , "$"        , $ccexportvarorder );
	$ccexportvarorder = str_replace( "&quot;"           , "\\\""        , $ccexportvarorder );

	// open the file
	$filename = "$path_to_ccfile" . "/" . "$exportprefix" . "export" . "$batchid.csv";
	$handle = fopen($filename, 'w') or die ("cannot open $filename"); // open the file

	// query the batch for the invoices to do
	$query = "SELECT DISTINCT d.invoice_number FROM billing_details d 
	WHERE batch = '$batchid'";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) 
		or die ("$l_queryfailed");

	while ($myresult = $result->FetchRow()) {

		// get the invoice data to process now
		$invoice_number = $myresult['invoice_number'];

		$query = "SELECT h.id h_id, h.billing_date h_billing_date, 
		h.created_by h_created_by, h.billing_id h_billing_id, 
		h.from_date h_from_date, h.to_date h_to_date, 
		h.payment_due_date h_payment_due_date, 
		h.new_charges h_new_charges, h.past_due h_past_due, 
		h.late_fee h_late_fee, h.tax_due h_tax_due, 
		h.total_due h_total_due, h.notes h_notes, 
		b.id b_id, b.name b_name, b.company b_company, 
		b.street b_street, b.city b_city, b.state b_state, 
		b.country b_country, b.zip b_zip, 
		b.contact_email b_contact_email, b.account_number b_acctnum, 
		b.creditcard_number b_ccnum, b.creditcard_expire b_ccexp,
b.encrypted_creditcard_number b_enc_ccnum 
		FROM billing_history h 
		LEFT JOIN billing b ON h.billing_id = b.id  
		WHERE h.id = '$invoice_number'";
		$invoiceresult = $DB->Execute($query)
			or die ("$l_queryfailed");	
		$myinvresult = $invoiceresult->fields;
		$user = $myinvresult['h_created_by'];
		$mydate = $myinvresult['h_billing_date'];
		$mybilling_id = $myinvresult['b_id'];
		$billing_name = $myinvresult['b_name'];
		$billing_company = $myinvresult['b_company'];
		$billing_street =  $myinvresult['b_street'];
		$billing_city = $myinvresult['b_city'];
		$billing_state = $myinvresult['b_state'];
		$billing_zip = $myinvresult['b_zip'];
		$billing_acctnum = $myinvresult['b_acctnum'];
		$billing_ccnum = $myinvresult['b_ccnum'];
		$billing_ccexp = $myinvresult['b_ccexp'];
		$billing_fromdate = $myinvresult['h_from_date'];
		$billing_todate = $myinvresult['h_to_date'];
		$billing_payment_due_date = $myinvresult['h_payment_due_date'];
		$precisetotal = $myinvresult['h_total_due'];
		$encrypted_creditcard_number = $myinvresult['b_enc_ccnum'];

		// get the absolute value of the total
		$abstotal = abs($precisetotal);

		// TODO: decrypt the encrypted_creditcard and replace the billing_ccnum value with it

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
		
		//$gpgcommandline = "echo $passphrase | $gpg_decrypt $cipherfilename";
		
		//$oldhome = getEnv("HOME");
		
		// destroy the output array before we use it again
		unset($decrypted);
		
		$gpgcommandline = "$gpg_decrypt $cipherfilename";
		$decrypted = decrypt_command($gpgcommandline, $passphrase);

		// if there is a gpg error, stop here
		if (substr($decrypted,0,5) == "error") {
		  die ("Credit Card Encryption Error: $decrypted $mybilling_id");
		}
 		 
 		// set the billing_ccnum to the decrypted_creditcard_number
 		$decrypted_creditcard_number = $decrypted;
		$billing_ccnum = $decrypted_creditcard_number;		
				
		// determine the variable export order values
		eval ("\$exportstring = \"$ccexportvarorder\";");

		// print the line in the exported data file
		// don't print them to billing if the amount is less than or equal to zero
		if ($precisetotal > 0) {
		  $newline = "\"CHARGE\",$exportstring\n";
		  fwrite($handle, $newline); // write to the file
		}
	} // end while
	
	// close the file
	fclose($handle); // close the file

	// log this export activity
	log_activity($DB,$user,0,'export','creditcard',$batchid,'success');
	

	echo "$l_wrotefile $filename<br><a href=\"index.php?load=tools/downloadfile&type=dl&filename=$exportprefix" . "export" . "$batchid.csv\"><u class=\"bluelink\">$l_download" . "$exportprefix" . "export" . "$batchid.csv</u></a><p>";	
}
else {
// select the organizations from a list

// ask for the billing date that they want to invoice
$form_action_url = "$ssl_url_prefix" . "index.php";
  
echo "<FORM ACTION=\"$form_action_url\" METHOD=\"POST\" name=\"form1\" onsubmit=\"toggleOn();\" AUTOCOMPLETE=\"off\">
	<input type=hidden name=load value=fixexportcc>
	<input type=hidden name=type value=tools>
	<table>";
	// print list of organizations to choose from
	$query = "SELECT id,org_name FROM general";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	echo "<td><b>$l_organizationname</b></td>
		<td><select name=\"organization_id\">
		<option value=\"\">$l_choose</option>";
	while ($myresult = $result->FetchRow()) {
		$myid = $myresult['id'];
		$myorg = $myresult['org_name'];
		echo "<option value=\"$myid\">$myorg</option>";
	}
echo "</select></td><tr>
	
	<td>batch to fix:</td>
	<td><input type=text name=batchid value=\"\" size=5>
	</td><tr>
<td align=right>$l_passphrase:</td><td><input type=password name=passphrase></td><tr>
	<td></td><td><INPUT TYPE=\"SUBMIT\" NAME=\"submit\" value=\"$l_submitrequest\">
	</td>
	</form>
	</table><br><br><br>";
	
	// print the WaitingMessage
	echo "<div id=\"WaitingMessage\" style=\"border: 0px double black; ".
	  "background-color: #fff; position: absolute; text-align: center; ".
	  "top: 50px; width: 550px; height: 300px;\">".
	  "<BR><BR><BR><h3>$l_processing...</h3>".
	  "<p><img src=\"images/spinner.gif\"></p>".
	  "</div>";	
}

?>

