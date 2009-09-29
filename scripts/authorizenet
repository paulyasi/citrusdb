#!/usr/bin/php
<?php
// Copyright (C) 2009 Paul Yasi(citrusdb.org), Mike Myers(geniusideastudio.com)
// read the README file for more information
//
// This script will run today's credit card billing via the authorize.net gateway
// To run this script, copy this script to the root of your citrus folder
// It can be executed from the command line or in a cron job
//
// This script requires php with the cURL module installed.
// For Debian/Ubuntu it is available to install as a package named php5-curl
//
// This script also needs the two auth_ variables filled in to define the
// Authorize.Net setup information
//

// the login name for your authorize.net api
$auth_api_login='';

// the transaction key for your authorize.net gateway
$auth_transaction_key='';

//Include the billing functions
include('./include/config.inc.php');
include('./include/database.inc.php');
include('./include/billing.inc.php');
include('./include/services.inc.php');
include('./include/support.inc.php');
include('./include/local/us-english.inc.php');

function card_approved($DB, $transaction_code, $billing_id, $cardnumber,
		       $cardexp, $response_code, $amount, $billingmethod, $avs_response) {
  $query = "INSERT INTO payment_history (creation_date, 
			transaction_code, billing_id, creditcard_number, 
			creditcard_expire, response_code, billing_amount, 
			status, payment_type,avs_response) 
			VALUES(CURRENT_DATE,'$transaction_code','$billing_id',
			'$cardnumber','$cardexp','$response_code','$amount',
			'authorized','$billingmethod','$avs_response')";
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  
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
    $updateresult = $DB->Execute($query) or die ("$l_queryfailed");
  } // end if billing method
  
  // update the billing_details for things that still 
  // need to be paid up
  $query = "SELECT * FROM billing_details 
			WHERE paid_amount < billed_amount 
			AND billing_id = $billing_id";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  
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
	"payment_applied = CURRENT_DATE ".
	"WHERE id = $id";
      $greaterthanresult = $DB->Execute($query) 
	or die ("$l_queryfailed");
    } else { 
      // amount is  less than owed
      $available = $amount;
      $amount = 0;
      $fillamount = round($available + $paid_amount,2);
      $query = "UPDATE billing_details ".
	"SET paid_amount = '$fillamount', ".
	"payment_applied = CURRENT_DATE ".
	"WHERE id = $id";
      $lessthanresult = $DB->Execute($query) 
	or die ("$l_queryfailed");
    } //end if amount
  } // end while fetchrow
}

function card_declined($DB, $transaction_code, $billing_id, $cardnumber,
		       $cardexp, $response_code, $amount, $billingmethod, $avs_response) {
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
  $typeresult = $DB->Execute($query) or die ("$l_queryfailed");
  $mytyperesult = $typeresult->fields;
  $billingmethod = $mytyperesult['t_method'];
  $mybillingdate = $mytyperesult['b_next_billing_date'];
  $myfromdate = $mytyperesult['b_from_date'];
  $mytodate = $mytyperesult['b_to_date'];
  $mybillingfreq = $mytyperesult['t_frequency'];
  $contact_email = $mytyperesult['b_contact_email'];
  
  $query = "INSERT INTO payment_history 
			(creation_date, transaction_code, billing_id, 
			creditcard_number,creditcard_expire, response_code, 
			billing_amount, status, payment_type, avs_response)
			VALUES(CURRENT_DATE,'$transaction_code','$billing_id',
			'$cardnumber','$cardexp','$response_code','$amount',
			'declined','$billingmethod','$avs_response')";
  $result = $DB->Execute($query) 
    or die ("$l_queryfailed");
  
  // put a message in the customer notes that 
  // a declined email was sent to their contact_email
  
  // get their account_number first
  $query = "SELECT account_number FROM billing WHERE 
				id = '$billing_id'";
  $bidresult = $DB->Execute($query) 
    or die ("$l_queryfailed");
  $mybidresult = $bidresult->fields;
  $myaccountnumber = $mybidresult['account_number'];
  
  // put a note in the customer history
  // add to customer_history
  $status = "automatic";
  $desc = "$l_declinedmessagesentto $contact_email";
  create_ticket($DB, $user, 'nobody', $myaccountnumber,
		$status, $desc);
  
  //send email			  
  $query = "SELECT g.email_billing, g.declined_subject, 
			g.declined_message, b.contact_email  
			FROM billing b
                        LEFT JOIN general g ON b.organization_id = g.id  
			WHERE b.id = $mybillingid";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ($l_queryfailed);
  $myresult = $result->fields;
  $billing_email = $myresult['email_billing'];
  $subject = $myresult['declined_subject'];
  $message = $myresult['declined_message'];
  $myemail = $myresult['contact_email'];
    
  // HTML Email Headers
  $headers = "From: $billing_email \n";
  $to = $myemail;
  // send the mail
  mail ($to, $subject, $message, $headers);
  echo "sent decline to $to<p>\n";			  
}



if (!isset($base->input['organization_id'])) { $base->input['organization_id'] = "1"; }

$billingdate = date("Y-m-d");
$organization_id = $base->input['organization_id'];

// make sure the user is in a group that is allowed to run this
	
//$DB->debug = true;

/*--------------------------------------------------------------------*/
// Create the billing data
/*--------------------------------------------------------------------*/

// determine the next available batch number
$batchid = get_nextbatchnumber($DB);
echo "Batch ID: $batchid<p>\n";
echo "Billing Date: $billingdate<p>\n";
echo "Organization ID: $organization_id<p>\n";

//
// Check if they are doing a billing date range or just one date
//

$totalall = 0;

// for a single date run
// Add creditcard taxes and services to the bill
$numtaxes = add_taxdetails($DB, $billingdate, NULL, 
			   'creditcard', $batchid, $organization_id);
$numservices = add_servicedetails($DB, $billingdate, NULL, 
				  'creditcard', $batchid, $organization_id);
echo "Credit Cards:: $numtaxes $l_added, 
		$numservices $l_added<p>\n";

// Add prepaycc taxes and services to the bill
$numpptaxes = add_taxdetails($DB, $billingdate, NULL, 
			     'prepaycc', $batchid, $organization_id);
$numppservices = add_servicedetails($DB, $billingdate, NULL,  
				    'prepaycc', $batchid, $organization_id);
echo "Pre-Pay: $l_creditcard: $numpptaxes $l_added, 
		$numppservices $l_added<p>\n";

// Update Reruns to the bill
$numreruns = update_rerundetails($DB, $billingdate, 
				 $batchid, $organization_id);
echo "$numreruns $l_rerun<p>\n";

$totalall = $numreruns + $numservices + $numtaxes + $numpptaxes + $numppservices + $totalall;


// show message if no records have been found
if ($totalall == 0) {
  echo "<b>No Records Found<b><p>\n";
 } else {
  
  // create billinghistory
  create_billinghistory($DB, $batchid, 'creditcard', $user);
  
  /*--------------------------------------------------------------------*/
  // print the credit card billing to a file
  /*--------------------------------------------------------------------*/
  
  // select the path_to_ccfile from settings
  $query = "SELECT path_to_ccfile FROM settings WHERE id = '1'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $ccfileresult = $DB->Execute($query) 
    or die ("$l_queryfailed");
  $myccfileresult = $ccfileresult->fields;
  $path_to_ccfile = $myccfileresult['path_to_ccfile'];	
  
  // select the info from general to get the path_to_ccfile
  $query = "SELECT ccexportvarorder FROM general WHERE id = '$organization_id'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $ccvarresult = $DB->Execute($query) 
    or die ("$l_queryfailed");
  $myccvarresult = $ccvarresult->fields;
  $ccexportvarorder = $myccvarresult['ccexportvarorder'];	
  
  // convert the $ccexportvarorder &#036; dollar signs back to actual dollar signs and &quot; back to quotes
  $ccexportvarorder = str_replace( "&#036;"           , "$"        , $ccexportvarorder );
  $ccexportvarorder = str_replace( "&quot;"           , "\\\""        , $ccexportvarorder );
  
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
		b.creditcard_number b_ccnum, b.creditcard_expire b_ccexp 
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
    
    // get the absolute value of the total
    $abstotal = abs($precisetotal);
    
    // don't bill them if the amount is less than or equal to zero
    if ($precisetotal > 0) {
      echo "account num: $billing_acctnum\n";
      echo "ccnum: $billing_ccnum\n";
      echo "ccexp: $billing_ccexp\n";
      echo "Amount: $precisetotal\n";
      
      //Send charge to authorize.net	
      $charge_result = charge_card("CC", $billing_ccnum, $billing_ccexp, $precisetotal, "GIS Bill for Account #: " . $billing_acctnum, $invoice_number, $billing_name, NULL, $billing_street, $billing_state, $billing_zip, "1");	
      
      $response_array = explode("|",$charge_result);			
      
      switch ($response_array[0]) {
      case 1:
	echo "Transaction Approved<p>\n";
	card_approved($DB, $response_array[4], $mybilling_id, $billing_ccnum, $billing_ccexp, $charge_result, $precisetotal, "creditcard", "");
	break;
      case 2:
	echo "Transaction Declined<p>\n";
	card_declined($DB, $response_array[4], $mybilling_id, $billing_ccnum, $billing_ccexp, $charge_result, $precisetotal, "creditcard", "");
	break;
      case 3:
	echo "Transaction Error<p>\n";
	card_declined($DB, $response_array[4], $mybilling_id, $billing_ccnum, $billing_ccexp, $charge_result, $precisetotal, "creditcard", "");
	break;
      case 4:
	echo "Hold For Review<p>\n";
	card_declined($DB, $response_array[4], $mybilling_id, $billing_ccnum, $billing_ccexp, $charge_result, $precisetotal, "creditcard", "");
	break;
      }
    }
  } // end while
  
 } // end if totalall


/**
 * function: charge_card
 * 
 * Parameters:
 *	Type - Either CC for Credit Card or ECHECK for electronic check
 * 	CardNumber - Credit Card Number - no dashes or spaces
 * 	ExpDate - Expiration Date in mmyy format
 * 	Amount - Amount to charge in xxxxx.xx format (up to 7 digits)
 * 	Description - (Optional) Description of the transaction.  If no description set to NULL
 * 	Invoice - (Optional) If you want to include an invoice number stick it here.  If no invoice # then set to NULL
 * 	FirstName - (Optional) First name of card holder, leave NULL if no First Name is required
 * 	LastName - (Optional) Last name of card hold, leave NULL if no last name is required
 * 	Address - (Optional) Address of card holder, leave NULL if no address is required
 * 	State - (Optional) City of card holder, leave NULL if no city required
 * 	Zip - (Optional) Zip of card hodler, leave NULL if no zip required
 * 	Test - If not set to NULL, will send transactions to the test server at authorize.net
 * @return 
 * @param object $test
 */
function charge_card($Type, $CardNumber, $ExpDate, $Amount, $Description, $Invoice, $FirstName, $LastName, $Address, $State, $Zip, $Test) {

global $auth_api_login, $auth_transaction_key;

// if the test variable is set to anything other than NULL the transactions will be sent to the test server at authorize.net
 if ($Test == NULL) {
   $post_url = "https://secure.authorize.net/gateway/transact.dll";
 } else {
   $post_url = "https://test.authorize.net/gateway/transact.dll";	
 }
 
 $post_values = array(
		      
		      // the API Login ID and Transaction Key must be replaced with valid values
		      "x_login"			=> $auth_api_login,
		      "x_tran_key"		=> $auth_transaction_key,
		      
		      "x_version"			=> "3.1",
		      "x_delim_data"		=> "TRUE",
		      "x_delim_char"		=> "|",
		      "x_relay_response"	=> "FALSE",
		      
		      "x_type"			=> "AUTH_CAPTURE",
		      "x_method"			=> $Type,
		      "x_card_num"		=> $CardNumber,
		      "x_exp_date"		=> $ExpDate,
		      
		      "x_amount"			=> $Amount
		      );
 
 if ($Description != NULL) {
   $post_values["x_description"] = $Description;
 }
 
 if ($Invoice != NULL) {
   $post_values["x_invoice_num"] = $Invoice;
 }
 
 if ($FirstName != NULL) {
   $post_values["x_first_name"] = $FirstName;
 }
 
 if ($LastName != NULL) {
   $post_values["x_last_name"] = $LastName;
 }
 
 if ($Address != NULL) {
   $post_values["x_address"] = $Address;
 }
 
 if ($State != NULL) {
   $post_values["x_state"] = $State;
 }
 
 if ($Zip != NULL) {
   $post_values["x_zip"] = $Zip;
 }
 
 
 // This section takes the input fields and converts them to the proper format
 // for an http post.  For example: "x_login=username&x_tran_key=a1B2c3D4"
 $post_string = "";
 foreach( $post_values as $key => $value )
   { $post_string .= "$key=" . urlencode( $value ) . "&"; }
 $post_string = rtrim( $post_string, "& " );
 
 // This sample code uses the CURL library for php to establish a connection,
 // submit the post, and record the response.
 // If you receive an error, you may want to ensure that you have the curl
 // library enabled in your php configuration
 $request = curl_init($post_url); // initiate curl object
 curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
 curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
 curl_setopt($request, CURLOPT_POSTFIELDS, $post_string); // use HTTP POST to send form data
 curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response.
 $post_response = curl_exec($request); // execute curl post and store results in $post_response
 // additional options may be required depending upon your server configuration
 // you can find documentation on curl options at http://www.php.net/curl_setopt
 curl_close ($request); // close curl object
 
 // This line takes the response and breaks it into an array using the specified delimiting character
 $response_array = explode($post_values["x_delim_char"],$post_response);
 
 // The results are parsed and the first 4 items added to a response array to be processed later.
 
 $return_value="";
 $item_count=0;
 
 foreach ($response_array as $value) {
   $return_value = $return_value . $value . "|";
   if (++$item_count > 7)
     return $return_value;
 }
 
}
// individual elements of the array could be accessed to read certain response
// fields.  For example, response_array[0] would return the Response Code,
// response_array[2] would return the Response Reason Code.
// for a list of response fields, please review the AIM Implementation Guide
?>
