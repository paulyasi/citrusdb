<html>
<body bgcolor="#ffffff">
<?php
// Copyright (C) 2002-2008  Paul Yasi (paul at citrusdb.org)
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

//GET Variables
$submit = $base->input['submit'];

echo "<h3>$l_importcreditcards</h3>";

if ($submit) {
  // save information
	
  // get the path_to_citrus
  $query = "SELECT path_to_ccfile FROM settings WHERE id = 1";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("file path $l_queryfailed $query");
  $myresult = $result->fields;
  $path_to_ccfile = $myresult['path_to_ccfile'];
  
  $myfile = "$path_to_ccfile/newfile.txt";
  
  print "$l_fileuploaded: $myfile<p>";
  
  // OPEN THE FILE AND PROCESS IT
  // line format: "transaction code","card number","card expire",
  // "amount","billing id","approved or declined","avs response"
  // line example: "CHARGE","4111111111111111","0202","18.95","1","Yes","A"
  
  // make an empty declined array to hold billing_id's of declined 
  // customers
  $declined = array();
  
  $fp = @fopen($myfile, "r") or die ("$l_cannotopen $myfile");
  
  while ($line = @fgetcsv($fp, 0)) {

    list($transaction_code, $cardnumber, $cardexp, $amount, 
	 $billing_id, $response_code, $avs_response) = $line;
    
    // letters Y or N at the beginning, the rest does not matter 
    $response_id = substr ($response_code,0,1);
    
    
    // determine if they are a prepaycc or creditcard type
    // if they are prepaycc then update the billing dates
    $query = "SELECT b.id b_id, b.billing_type b_billing_type, 
		b.next_billing_date b_next_billing_date, 
		b.from_date b_from_date, b.to_date b_to_date,
		b.contact_email b_contact_email, 
		t.frequency t_frequency,
		t.id t_id, t.method t_method FROM billing b 
		LEFT JOIN billing_types t ON b.billing_type = t.id
		WHERE b.id = '$billing_id'";
    $typeresult = $DB->Execute($query) or die ("billing type $l_queryfailed $query");
    $mytyperesult = $typeresult->fields;
    $billingmethod = $mytyperesult['t_method'];
    $mybillingdate = $mytyperesult['b_next_billing_date'];
    $myfromdate = $mytyperesult['b_from_date'];
    $mytodate = $mytyperesult['b_to_date'];
    $mybillingfreq = $mytyperesult['t_frequency'];
    $contact_email = $mytyperesult['b_contact_email'];
    
    // declined or credit (first letter of response code is an 'N')
    if ($response_id == 'N') {
      if ($transaction_code == 'CREDIT' OR $transaction_code == '38') {	
	// if it's a credit
	$query = "INSERT INTO payment_history 
			(creation_date, transaction_code, billing_id, 
			creditcard_number,creditcard_expire, response_code, 
			billing_amount, status, payment_type, avs_response)
			VALUES(CURRENT_DATE,'$transaction_code','$billing_id',
			'$cardnumber','$cardexp','$response_code','$amount',
			'credit','$billingmethod','$avs_response')";
	$result = $DB->Execute($query) or die ("authorized payment history insert $l_queryfailed $query");
      } else { 
	// else it's a declined transaction
	$query = "INSERT INTO payment_history 
			(creation_date, transaction_code, billing_id, 
			creditcard_number,creditcard_expire, response_code, 
			billing_amount, status, payment_type, avs_response)
			VALUES(CURRENT_DATE,'$transaction_code','$billing_id',
			'$cardnumber','$cardexp','$response_code','$amount',
			'declined','$billingmethod','$avs_response')";
	$result = $DB->Execute($query) 
	  or die ("declined payment history insert $l_queryfailed $query");
	
	// push the customers email address into the 
	// declined array
	array_push($declined, $billing_id);
	
	// put a message in the customer notes that 
	// a declined email was sent to their contact_email
	
	// get their account_number first
	$query = "SELECT account_number FROM billing WHERE 
				id = '$billing_id'";
	$bidresult = $DB->Execute($query) 
	  or die ("select account number $l_queryfailed $query");
	$mybidresult = $bidresult->fields;
	$myaccountnumber = $mybidresult['account_number'];
	
	// put a note in the customer history
	// add to customer_history
	$status = "automatic";
	$desc = "$l_declinedmessagesentto $contact_email";
	create_ticket($DB, $user, 'nobody', $myaccountnumber,
		      $status, $desc);
	
      }	// end if transaction code		
      
    } // end if response id = n
    
    // authorized (first letter of response code is a 'Y')
    if ($response_id == 'Y') {
      $query = "INSERT INTO payment_history (creation_date, 
			transaction_code, billing_id, creditcard_number, 
			creditcard_expire, response_code, billing_amount, 
			status, payment_type,avs_response) 
			VALUES(CURRENT_DATE,'$transaction_code','$billing_id',
			'$cardnumber','$cardexp','$response_code','$amount',
			'authorized','$billingmethod','$avs_response')";
      $result = $DB->Execute($query) or die ("insert payment history $l_queryfailed $query");

      // get the payment_history_id that is associated with the billing_details
      // for this payment
      $payment_history_id = $DB->Insert_ID();
      
      // update the next_billing_date, to_date, 
      // from_date, and payment_due_date for prepay/prepaycc 
      if ($billingmethod == 'prepaycc' OR $billingmethod == 'prepay') {
	// to get the to_date, double the frequency
	$doublefreq = $mybillingfreq * 2;
	
	// insert the new dates
	$query = "UPDATE billing SET 
                        	next_billing_date = DATE_ADD('$mybillingdate', 
			        INTERVAL '$mybillingfreq' MONTH),
				from_date = DATE_ADD('$myfromdate', 
				INTERVAL '$mybillingfreq' MONTH),
				to_date = DATE_ADD('$myfromdate', 
				INTERVAL '$doublefreq' MONTH),
				payment_due_date = DATE_ADD('$myfromdate', 
				INTERVAL '$mybillingfreq' MONTH)
				WHERE id = '$billing_id'";
	$updateresult = $DB->Execute($query) or die ("update billing $l_queryfailed $query");
      } // end if billing method
      
      // update the billing_details for things that still 
      // need to be paid up
      $query = "SELECT * FROM billing_details 
			WHERE paid_amount < billed_amount 
			AND billing_id = $billing_id";
      $DB->SetFetchMode(ADODB_FETCH_ASSOC);
      $result = $DB->Execute($query) or die ("select billing details $l_queryfailed $query");
      
      while (($myresult = $result->FetchRow()) and (round($amount,2) > 0)) {
	$id = $myresult['id'];
	$paid_amount = sprintf("%.2f",$myresult['paid_amount']);
	$billed_amount = sprintf("%.2f",$myresult['billed_amount']);
	
	// do stuff 
	$owed = round($billed_amount - $paid_amount,2);
	
	if (round($amount,2) >= round($owed,2)) {
	  $amount = round($amount - $owed,2);
	  $fillamount = round($owed + $paid_amount,2);
	  $query = "UPDATE billing_details ".
	    "SET paid_amount = '$fillamount', ".
	    "payment_applied = CURRENT_DATE, ".
	    "payment_history_id = '$payment_history_id' ".	    
	    "WHERE id = $id";
	  $greaterthanresult = $DB->Execute($query) 
	    or die ("greater than $l_queryfailed $query");
	} else { 
	  // amount is  less than owed
	  $available = $amount;
	  $amount = 0;
	  $fillamount = round($available + $paid_amount,2);
	  $query = "UPDATE billing_details ".
	    "SET paid_amount = '$fillamount', ".
	    "payment_applied = CURRENT_DATE, ".
	    "payment_history_id = '$payment_history_id' ".	    
	    "WHERE id = $id";
	  $lessthanresult = $DB->Execute($query) 
	    or die ("less than $l_queryfailed $query");
	} //end if amount
      } // end while fetchrow
    } // end if response_id = y
  } // end while fgetcsv

  // close the file
  @fclose($fp) or die ("$l_cannotclose $myfile");
  
  // delete the file
  unlink($myfile);
  
  // send email messages to declined customers listed in the
  // declined array
  
  foreach ($declined as $key=>$mybillingid) {
    // select the info for emailing the customer
    // get the org billing email address for from address           
    $query = "SELECT g.email_billing, g.declined_subject, g.declined_message, ".
      "b.contact_email, b.account_number, b.creditcard_number, b.creditcard_expire ". 
      "FROM billing b ".
      "LEFT JOIN general g ON b.organization_id = g.id ".
      "WHERE b.id = $mybillingid";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("email declined $l_queryfailed $query");
    $myresult = $result->fields;
    $billing_email = $myresult['email_billing'];
    $subject = $myresult['declined_subject'];
    $myaccountnum = $myresult['account_number'];

    
    // wipe out the middle of the creditcard_number before it gets inserted
    $firstdigit = substr($myresult['creditcard_number'], 0,1);
    $lastfour = substr($myresult['creditcard_number'], -4);
    $maskedcard = "$firstdigit" . "***********" . "$lastfour";  
    
    $expdate = $myresult['creditcard_expire'];
    $declined_message = $myresult['declined_message'];
    $message = "$l_account: $myaccountnum\n$l_creditcard: $maskedcard $expdate\n\n$declined_message";
    $myemail = $myresult['contact_email'];
    
    // HTML Email Headers
    $headers = "From: $billing_email \n";
    $to = $myemail;
    // send the mail
    mail ($to, $subject, $message, $headers);
    echo "sent decline to $to\n";
  } // end foreach
  echo "<p>$l_done</p>";

  // log this import activity
  log_activity($DB,$user,0,'import','creditcard',0,'success');
  
 } // end if submit

// use the uploadcc.php file to upload the file to the io directory
// uploadcc will redirect back to this file to perform the submit processing

echo "<FORM ACTION=\"index.php?load=uploadcc&type=tools\" METHOD=\"POST\" enctype=\"multipart/form-data\">
<table>
<td>$l_importfile:</td><td><input type=file name=\"userfile\"></td><tr> 
<td></td><td><br><input type=submit name=\$l_import\" value=\"$l_import\"></td>
</table>
</form> 
";

?>
</body>
</html>
