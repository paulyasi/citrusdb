<?php
/*
 * -----------------------------------------------------------------------------
 *  this class holds functions that are run from the command line only
 *  eg: php index.php command <function>
 * -----------------------------------------------------------------------------
 */
class Command extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		
		// if this is not a cli request then exit
		if (!$this->input->is_cli_request())
		{
			exit;
		}
	}

	
    /*
	 * -----------------------------------------------------------------------
	 * decrypt the credit cards in the citrus database using
	 * the gpg settings from the new config file
	 * this is to be used when re-keying the database with a new gpg key
	 * with a proper install and configuration this script is only run when
	 * one has to re-key their database with new gpg keys.  Before this script
	 * is run you'll have to move the secret keys back onto the server to run it
	 *
	 * get the passphrase from the command line
	 * cd into your citrusdb folder
	 * run as the www-data user or whatever user has the gpg ring
	 *
	 * su - www-data
	 * php index.php command decryptcards <passphrase>
	 * -----------------------------------------------------------------------
	 */
	public function decryptcards($passphrase)
	{
        // load models
		$this->load->model('billing_model');
		$this->load->model('support_model');
		$this->load->model('settings_model');
		
		// load the encryption helper for use when calling gpg things
		$this->load->helper('encryption');

		$card_result = $this->billing_model->list_encrypted_creditcards();

		// walk forwards one at by counting each row
		foreach ($card_result AS $myresult)
		{			
			$id = $myresult['id'];
			$creditcard_number = $myresult['creditcard_number'];
			$encrypted_creditcard_number = $myresult['encrypted_creditcard_number'];

			// check if there is a non-masked credit card number in the input
			// if the second cararcter is a * then it's already masked
  
			// check if the credit card entered already masked
			// eg: a replacement was not entered
			if ($creditcard_number[1] == '*')
			{
				// write the encrypted_creditcard_number to a temporary file
				// and decrypt that file to stdout to get the CC
				// select the path_to_ccfile from settings
				$path_to_ccfile = $this->settings_model->get_path_to_ccfile();
 
				// open the file
				$filename = "$path_to_ccfile/ciphertext.tmp";
				$handle = fopen($filename, 'w') or die("cannot open $filename");

				// write the ciphertext we want to decrypt into the file
				fwrite($handle, $encrypted_creditcard_number);

				// close the file
				fclose($handle);
	
				// destroy the output array before we use it again
				unset($decrypted);
      
				// try the new decrypt_command function
				$gpgcommandline = $this->config->item('gpg_decrypt')." $filename";
				$decrypted = decrypt_command($gpgcommandline, $passphrase);
    
				// if there is a gpg error, stop here
				if (substr($decrypted,0,5) == "error")
				{
					die ("Credit Card Encryption Error: $decrypted".lang('billingid').": $id\n");
				}

				echo "$decrypted";
				// remove extra line endings from the decrypted output
				//$decrypted_creditcard_number = str_replace( '\n', '', $decrypted );
				$decrypted_creditcard_number = $decrypted;


				$this->billing_model->input_decrypted_card($decrypted_creditcard_number, $id);
    
				print "$id creditcard updated $decrypted_creditcard_number\n";


			}
			else
			{
				print "$id skipped\n";
			} // end if creditcard_number

		} // end while myresult

	} // end function decrypt cards



	
	/*
	 * --------------------------------------------------------------------------
	 * encrypt the credit cards in the citrus database using the gpg settings
	 * from the configuration
	 * php index.php command encryptcards
	 * --------------------------------------------------------------------------
	 */
	public function encryptcards()
	{		
        // load models
		$this->load->model('billing_model');
		$this->load->model('support_model');
		$this->load->model('settings_model');
		
		// load the encryption helper for use when calling gpg things
		$this->load->helper('encryption');
		
		$result = $this->billing_model->list_creditcards();

		// walk through each individual result
		foreach ($result AS $myresult)
		{
			echo "counter: $i\n";
			$id = $myresult['id'];
			$creditcard_number = $myresult['creditcard_number'];

			// check if there is a non-masked credit card number in the input
			// if the second cararcter is a * then it's already masked
  
			// check if the credit card entered already masked
			// eg: a replacement was not entered
			if ($creditcard_number[1] <> '*')
			{
				// destroy the output array before we use it again
				unset($encrypted);

				$encrypted = encrypt_command($this->config->item('gpg_command'),
											 $creditcard_number);

				// if there is a gpg error, stop here
				if (substr($encrypted,0,5) == "error")
				{
					die ("Credit Card Encryption Error: $encrypted");
				}

				$encrypted_creditcard_number = $encrypted;
    
				// wipe out the middle of the creditcard_number before it gets inserted
				$firstdigit = substr($creditcard_number, 0,1);
				$lastfour = substr($creditcard_number, -4);
				$creditcard_number = "$firstdigit" . "***********" . "$lastfour";    

				//echo "$gpgcommandline<br><pre>$encrypted_creditcard_number</pre>\n";

				$this->billing_model->input_encrypted_card($creditcard_number,
														   $encrypted_creditcard_number,
														   $id);
    
				print "$id creditcard updated $encrypted_creditcard_number\n";

			} else {
				print "$id skipped\n";
			}// end if creditcard_number

		} // end while myresult

	} // end encryptcards function


	/*
	 * -------------------------------------------------------------------------
	 * status activator function
	 * updates the account status
	 * run daily after billing for the day is over (in cron)
	 * creates text file of account changes
	 * process this text file with your own account processing system
	 * you can move this script elsewhere and copy the include files there too
	 * -------------------------------------------------------------------------
	 */	 
	function statusupdate()
	{
		// load models
		
		$this->load->model('billing_model');
		$this->load->model('support_model');
		$this->load->model('settings_model');
		$this->load->model('service_model');
		$this->load->model('settings_model');
		
		// load the Notice library
		$config = array (
				'notice_type' => 'cancel', 
				'billing_id' => $billing_id, 
				'method' => 'both', 
				'payment_due_date' => $cancel_date, 
				'turnoff_date' => $cancel_date, 
				'cancel_date' => $cancel_date
				);
		$this->load->library('Notice', $config);    
		
		// todays' date
		$activatedate = date("Y-m-d");
		
/*--------------------------------------------------------------------------*/
// Get the path to the file location and open a new file to write data to
/*--------------------------------------------------------------------------*/
// select the info from general to get the path_to_ccfile
		$query = "SELECT * FROM settings WHERE id = '1'";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$ccfileresult = $DB->Execute($query) or die ("$l_queryfailed");
		$myccfileresult = $ccfileresult->fields;
		$path_to_ccfile = $myccfileresult['path_to_ccfile'];
		$default_billing_group = $myccfileresult['default_billing_group'];

// open the file
		$today = $activatedate;
		$filename = "$path_to_ccfile/accounts$today.csv";
		$handle = fopen($filename, 'a') or die ("cannot open $filename"); // open the file


/*-------------------------------------------------------------------*/
// ADD
/*-------------------------------------------------------------------*/
// get the list of new services added today

		$query = "SELECT u.id u_id, u.account_number u_ac, ".
			"u.master_service_id u_master_service_id, u.billing_id u_bid, ".
			"u.start_datetime u_start, u.removed u_rem, u.usage_multiple ".
			"u_usage, m.service_description m_service_description, ".
			"m.id m_id, m.pricerate m_pricerate, m.frequency m_freq, ".
			"m.activation_string m_activation_string, m.category m_category, ".
			"m.options_table m_options_table, c.name c_name, c.company c_company, ".
			"c.street c_street, c.city c_city, c.state c_state, c.country c_country, ".
			"c.zip c_zip, c.phone c_phone, c.contact_email c_contact_email ".
			"FROM user_services u ".
			"LEFT JOIN master_services m ON m.id = u.master_service_id ".
			"LEFT JOIN customer c ON c.account_number = u.account_number ".
			"WHERE to_days('$today') = to_days(u.start_datetime)";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("$l_queryfailed");

		
		$adds = 0;

// loop through results and print out each
		while ($myresult = $result->FetchRow()) {
			$user_services_id = $myresult['u_id'];
			$service_description = $myresult['m_service_description'];
			$account_number = $myresult['u_ac'];
			$options_table = $myresult['m_options_table'];
			$activation_string = $myresult['m_activation_string'];
			$customer_name = $myresult['c_name'];
			$customer_company = $myresult['c_company'];
			$customer_street = $myresult['c_street'];
			$customer_city = $myresult['c_city'];
			$customer_state = $myresult['c_state'];
			$customer_country = $myresult['c_country'];
			$customer_zip = $myresult['c_zip'];
			$category = $myresult['m_category'];
			$removed = $myresult['u_rem'];
  
			// query this with the option_table for that service to get the 
			// activation_string variables
			$mystring = split(",", $activation_string);
  
			$newline = "\"ADD\",\"$category\",\"$customer_name\",\"$service_description\"";
  
			if ($options_table <> '') {
				$query = "SELECT * FROM $options_table ".
					"WHERE user_services = '$user_services_id'";
				$DB->SetFetchMode(ADODB_FETCH_ASSOC);
				$optresult = $DB->Execute($query) 
					or die ("$l_queryfailed");
				$myoptresult = $optresult->fields;

				$fields = $DB->MetaColumns($options_table);        
				$i = 0;        
				$pstring = "";	
				foreach($fields as $v) {                
					//echo "Name: $v->name ";
					$fieldname = $v->name;

					//check matching fieldname in the options table
					foreach($mystring as $s) {
						if($fieldname == $s) {
							//$pstring = $pstring.$s;
							$myline = $myoptresult["$s"];
							$newline .= ",\"$myline\"";
						}	
					}
				
				} //endforeach
			} //endif

			$newline .= "\n"; // end the line
    
			// write the file if the service has not been removed
			if ($removed <> 'y') {
				fwrite($handle, $newline); // write to the file
				$adds++;
			}

		} //endwhile
  
		echo "$adds ADDs\n";

  
/*-------------------------------------------------------------------*/
// ENABLE
//
// if the account has an authorized status payment_history today and 
// it's previous payment_history was bad: 
// (turnedoff, canceled, cancelwfee, collections)
// or if they are in waiting status today
/*-------------------------------------------------------------------*/

// select all the accounts with a payment_history of today
		$query = "SELECT p.billing_id, b.id, b.account_number ".
			"FROM payment_history p ".
			"LEFT JOIN billing b ON p.billing_id = b.id ".
			"WHERE p.creation_date = '$today' ".
			"AND p.status = 'authorized'";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("$l_queryfailed");

		$enables = 0;

		while ($myresult = $result->FetchRow()) {
			// go through those accounts and find out which one has 
			//a previous payment_history that was declined, 
			//turnedoff, collections or canceled	
		
			$billingid = $myresult['billing_id'];	
			$account_number = $myresult['account_number'];
  
			$query = "SELECT * FROM payment_history ".
				"WHERE billing_id = '$billingid' ORDER BY id DESC LIMIT 1,1";
			$historyresult = $DB->Execute($query) or die ("$l_queryfailed");
			$myhistoryresult = $historyresult->fields;
			$secondstatus = $myhistoryresult['status'];
			if ($secondstatus == "turnedoff" 
				OR $secondstatus == "waiting" 
				OR $secondstatus == "collections" 
				OR $secondstatus == "cancelwfee" 
				OR $secondstatus == "canceled") {
				// enable services for the account
    
				$query = "SELECT u.id u_id, u.account_number u_ac, ".
					"u.master_service_id u_master_service_id, ".
					"u.billing_id u_bid, ".
					"u.start_datetime u_start, u.removed u_rem, ".
					"u.usage_multiple u_usage, ".
					"m.service_description m_service_description, ".
					"m.id m_id, m.pricerate m_pricerate, ".
					"m.frequency m_freq, ".
					"m.activation_string m_activation_string, ".
					"m.category m_category, m.activate_notify m_activate_notify, ".
					"m.options_table m_options_table, c.name c_name, ".
					"c.company c_company, c.street c_street, c.city c_city, ".
					"c.state c_state, c.country c_country, ".
					"c.zip c_zip, c.phone c_phone, ".
					"c.contact_email c_contact_email ".
					"FROM user_services u ".
					"LEFT JOIN master_services m ON m.id = u.master_service_id ".
					"LEFT JOIN customer c ON c.account_number = u.account_number ".
					"WHERE c.account_number = $account_number";
				$DB->SetFetchMode(ADODB_FETCH_ASSOC);
				$serviceresult = $DB->Execute($query) or die ("$l_queryfailed");

				// loop through results and print out each
				while ($myserviceresult = $serviceresult->FetchRow()) {
					$user_services_id = $myserviceresult['u_id'];
					$service_description = $myserviceresult['m_service_description'];
					$account_number = $myserviceresult['u_ac'];
					$options_table = $myserviceresult['m_options_table'];
					$activation_string = $myserviceresult['m_activation_string'];
					$customer_name = $myserviceresult['c_name'];
					$customer_company = $myserviceresult['c_company'];
					$customer_street = $myserviceresult['c_street'];
					$customer_city = $myserviceresult['c_city'];
					$customer_state = $myserviceresult['c_state'];
					$customer_country = $myserviceresult['c_country'];
					$customer_zip = $myserviceresult['c_zip'];
					$category = $myserviceresult['m_category'];
					$removed = $myserviceresult['u_rem']; // y or n
					$activate_notify = $myserviceresult['m_activate_notify'];
	
					// query this with the option_table for 
					// that service to get the 
					// activation_string variables
					$mystring = split(",", $activation_string);
      
					$newline = "\"ENABLE\",\"$category\",\"$customer_name\",\"$service_description\"";
      
					if ($options_table <> '') {
						$query = "SELECT * FROM $options_table ".
							"WHERE user_services = '$user_services_id'";
						$DB->SetFetchMode(ADODB_FETCH_ASSOC);
						$optresult = $DB->Execute($query) or die ("$l_queryfailed");
						$myoptresult = $optresult->fields;
	
						$fields = $DB->MetaColumns($options_table);        
						$i = 0;        
						$pstring = "";	
						foreach($fields as $v) {                
							//echo "Name: $v->name ";                
							$fieldname = $v->name;                
	  
							// check matching fieldname in 
							// the options table
							foreach($mystring as $s) {
								if($fieldname == $s) {
									//$pstring = $pstring.$s;
									$myline = $myoptresult["$s"];
									$newline .= ",\"$myline\"";
								}	
							}
	  
						} //endforeach
					} //endif
					$newline .= "\n"; // end the line
      
					// write to the file if the service has not already been removed
					if ($removed <> 'y') {
						fwrite($handle, $newline); // write to the file
						$enables++;

						// CREATE TICKET TO the activate_notify user if there is one
						if ($activate_notify) {
							$notify = "$activate_notify";
							$description = "ENABLE $category $customer_name $service_description";
							$status = "not done";
							create_ticket($DB, $user, $notify, $account_number, $status,
										  $description, NULL, NULL, NULL, $user_services_id);
						}
	
					}
				} //endwhile
			} // endif
		} //endwhile

		echo "$enables ENABLEs\n";



/*-------------------------------------------------------------------*/
// REGULAR PAST DUE
//
// set the pastdue status for accounts that have a payment_due_date
// more than g.regular_pastdue days ago (usually one day)
// and do not have carrier_dependent services
//
/*-------------------------------------------------------------------*/
		$query = "SELECT bi.id, bi.account_number, bh.payment_due_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.regular_turnoff DAY) AS turnoff_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.regular_canceled DAY) AS cancel_date ".
			"FROM billing_details bd ".
			"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
			"LEFT JOIN general g ON bi.organization_id = g.id ".
			"WHERE bd.billed_amount > bd.paid_amount ".
			"AND bi.pastdue_exempt <> 'y' ".
			"AND bi.rerun_date IS NULL ".
			"AND '$today' >= DATE_ADD(bh.payment_due_date, INTERVAL g.regular_pastdue DAY) ".
			"AND '$today' < DATE_ADD(bh.payment_due_date, INTERVAL g.regular_turnoff DAY) ".
			"GROUP BY bi.id";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("regular past due $l_queryfailed");

		while ($myresult = $result->FetchRow()) {
			// set these services to be turned off
			$billing_id = $myresult['id'];	
			$account_number = $myresult['account_number'];
			$payment_due_date = $myresult['payment_due_date'];
			$turnoff_date = $myresult['turnoff_date'];
			$cancel_date = $myresult['cancel_date'];
  
			$dependent = carrier_dependent($account_number);
  
			if ($dependent == false) {
    
				// check recent history to see if we already set them to pastdue
				$query = "SELECT status FROM payment_history ".
					"WHERE billing_id = $billing_id ORDER BY id DESC LIMIT 1";
				$DB->SetFetchMode(ADODB_FETCH_ASSOC);
				$statusresult = $DB->Execute($query) or die ("$l_queryfailed");
				$mystatusresult = $statusresult->fields;
				$mystatus = $mystatusresult['status'];
    
				if ($mystatus <> "pastdue"
					AND $mystatus <> "noticesent" 
					AND $mystatus <> "turnedoff"
					AND $mystatus <> "collections"
					AND $mystatus <> "canceled"
					AND $mystatus <> "cancelwfee"
					AND $mystatus <> "waiting") {
					// set the account payment_history to pastdue
					$query = "INSERT INTO payment_history ".
						"(creation_date, billing_id, status) ".
						"VALUES (CURRENT_DATE,'$billing_id','pastdue')";
					$paymentresult = $DB->Execute($query) or die ("$l_queryfailed");

					echo "regular pastdue: $account_number\n";


					// get the payment_due_date, turnoff_date, and cancel_date
      
					// SEND PASTDUE NOTICE BY EMAIL
					$mynotice = new notice('pastdue',$billing_id, 'email', $payment_due_date, $turnoff_date, $cancel_date);
      
					$contactemail = $mynotice->contactemail;      
					$notify = "";
					$description = "Past Due Notice Sent $contactemail";
					$status = "automatic";
					// CREATE TICKET TO NOBODY
					create_ticket($DB, $user, $notify, $account_number, $status,
								  $description, $linkname, $linkurl);
      
				}
    
			}
  
		}


/*-------------------------------------------------------------------*/
// CARRIER DEPENDENT PAST DUE
//
// set the pastdue status for accounts that have a payment_due_date
// more than g.dependent_pastdue days ago (usually one day)
// and do have carrier_dependent services
//
// insert a ticket to billing if they have carrier_dependent services
//
/*-------------------------------------------------------------------*/
		$query = "SELECT bi.id, bi.account_number, bh.payment_due_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_turnoff DAY) AS turnoff_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_canceled DAY) AS cancel_date ".
			"FROM billing_details bd ".
			"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
			"LEFT JOIN general g ON bi.organization_id = g.id ".
			"WHERE bd.billed_amount > bd.paid_amount ".
			"AND bi.pastdue_exempt <> 'y' ".
			"AND bi.rerun_date IS NULL ".
			"AND '$today' >= DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.dependent_pastdue DAY) ".
			"AND '$today' < DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.dependent_shutoff_notice DAY) GROUP BY bi.id";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("carrier dependent past due $l_queryfailed");

		while ($myresult = $result->FetchRow()) {
			// set these services to be turned off
			$billing_id = $myresult['id'];	
			$account_number = $myresult['account_number'];
			$payment_due_date = $myresult['payment_due_date'];
			$turnoff_date = $myresult['turnoff_date'];
			$cancel_date = $myresult['cancel_date'];
  
			$dependent = carrier_dependent($account_number);
  
			if ($dependent == true) {
        
				// check recent history to see if we already set them to pastdue
				$query = "SELECT status FROM payment_history ".
					"WHERE billing_id = $billing_id ORDER BY id DESC LIMIT 1";
				$DB->SetFetchMode(ADODB_FETCH_ASSOC);
				$statusresult = $DB->Execute($query) or die ("$l_queryfailed");
				$mystatusresult = $statusresult->fields;
				$mystatus = $mystatusresult['status'];
    
				if ($mystatus <> "pastdue"
					AND $mystatus <> "turnedoff"
					AND $mystatus <> "noticesent" 
					AND $mystatus <> "collections"
					AND $mystatus <> "canceled"
					AND $mystatus <> "cancelwfee"
					AND $mystatus <> "waiting") {
					// set the account payment_history to pastdue
					$query = "INSERT INTO payment_history ".
						"(creation_date, billing_id, status) ".
						"VALUES (CURRENT_DATE,'$billing_id','pastdue')";
					$paymentresult = $DB->Execute($query) or die ("$l_queryfailed");

					echo "carrier dependent pastdue: $account_number\n";
      
					// SEND PASTDUE NOTICE BY BOTH PRINT and EMAIL
					$mynotice = new notice('pastdue',$billing_id, 'both', $payment_due_date, $turnoff_date, $cancel_date);
      
					$linkname = $mynotice->pdfname;
					$contactemail = $mynotice->contactemail;
					$linkurl = "index.php?load=tools/downloadfile&type=dl&filename=$linkname";
					$notify = "";
					$description = "Past Due Notice Sent $contactemail $url";
					$status = "not done";
					// CREATE TICKET TO default_billing_group
					create_ticket($DB, $user, $notify, $account_number, $status,
								  $description, $linkname, $linkurl);
      
				}
    
			}
  
		}

/*-------------------------------------------------------------------*/
// CARRIER DEPENDENT SHUTOFF NOTICE
//
// send a shutoff notice to carrier dependent services that are
// about to be turned off in a few days
/*-------------------------------------------------------------------*/
		$query = "SELECT bi.id, bi.account_number, bh.payment_due_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_turnoff DAY) AS turnoff_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_canceled DAY) AS cancel_date ".
			"FROM billing_details bd ".
			"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
			"LEFT JOIN general g ON bi.organization_id = g.id ".
			"WHERE bd.billed_amount > bd.paid_amount ".
			"AND bi.pastdue_exempt <> 'y' ".
			"AND bi.rerun_date IS NULL ".
			"AND '$today' >= DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.dependent_shutoff_notice DAY) ".
			"AND '$today' < DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.dependent_turnoff DAY) GROUP BY bi.id";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("carrier dependent shutoff $l_queryfailed");

		while ($myresult = $result->FetchRow()) {
			// set these services to be turned off
			$billing_id = $myresult['id'];	
			$account_number = $myresult['account_number'];
			$payment_due_date = $myresult['payment_due_date'];
			$turnoff_date = $myresult['turnoff_date'];
			$cancel_date = $myresult['cancel_date'];
  
			$dependent = carrier_dependent($account_number);
  
			if ($dependent == true) {
        
				// check recent history to see if we already set them to turned off
				$query = "SELECT status FROM payment_history ".
					"WHERE billing_id = $billing_id ORDER BY id DESC LIMIT 1";
				$DB->SetFetchMode(ADODB_FETCH_ASSOC);
				$statusresult = $DB->Execute($query) or die ("$l_queryfailed");
				$mystatusresult = $statusresult->fields;
				$mystatus = $mystatusresult['status'];
    
				if ($mystatus <> "turnedoff"
					AND $mystatus <> "noticesent" 
					AND $mystatus <> "collections"
					AND $mystatus <> "canceled"
					AND $mystatus <> "cancelwfee"
					AND $mystatus <> "waiting") {
					// set the account payment_history to noticesent
					$query = "INSERT INTO payment_history ".
						"(creation_date, billing_id, status) ".
						"VALUES (CURRENT_DATE,'$billing_id','noticesent')";
					$paymentresult = $DB->Execute($query) or die ("$l_queryfailed");

					// SEND SHUTOFF NOTICE BY BOTH PRINT and EMAIL
					$mynotice = new notice('shutoff',$billing_id, 'both', $payment_due_date, $turnoff_date, $cancel_date);
      
					$linkname = $mynotice->pdfname;      
					$contactemail = $mynotice->contactemail;
					$linkurl = "index.php?load=tools/downloadfile&type=dl&filename=$linkname";
					$notify = "$default_billing_group";
					$description = "Shutoff Notice Sent $contactemail $url";
					$status = "not done";
					// TODO: CREATE TICKET TO NOBODY
					create_ticket($DB, $user, $notify, $account_number, $status,
								  $description, $linkname, $linkurl);

				}
    
			}
  
		}



/*-------------------------------------------------------------------*/
// REGULAR DISABLE
//
// check if the account should be turned off if it's over
// regular_turnoff days AND HAS NO CARRIER DEPENDENT SERVICES
// disable the account
/*-------------------------------------------------------------------*/

		$query = "SELECT bi.id, bi.account_number, bh.payment_due_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.regular_turnoff DAY) AS turnoff_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.regular_canceled DAY) AS cancel_date ".
			"FROM billing_details bd ".
			"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
			"LEFT JOIN general g ON bi.organization_id = g.id ".
			"WHERE bd.billed_amount > bd.paid_amount ".
			"AND bi.pastdue_exempt <> 'y' ".
			"AND bi.rerun_date IS NULL ".
			"AND '$today' >= DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.regular_turnoff DAY) ".
			"AND '$today' < DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.regular_canceled DAY) GROUP BY bi.id";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("regular disable $l_queryfailed");

		while ($myresult = $result->FetchRow()) {
			// set these services to be turned off
			$billing_id = $myresult['id'];	
			$account_number = $myresult['account_number'];
			$payment_due_date = $myresult['payment_due_date'];
			$turnoff_date = $myresult['turnoff_date'];
			$cancel_date = $myresult['cancel_date'];
  
			$dependent = carrier_dependent($account_number);
  
			if ($dependent == false) {
    
				// check recent history to see if we already set them to turned off
				$query = "SELECT status FROM payment_history ".
					"WHERE billing_id = $billing_id ORDER BY id DESC LIMIT 1";
				$DB->SetFetchMode(ADODB_FETCH_ASSOC);
				$statusresult = $DB->Execute($query) or die ("$l_queryfailed");
				$mystatusresult = $statusresult->fields;
				$mystatus = $mystatusresult['status'];
    
				if ($mystatus <> "turnedoff"
					AND $mystatus <> "collections"
					AND $mystatus <> "canceled"
					AND $mystatus <> "cancelwfee"
					AND $mystatus <> "waiting") {
					// set the account payment_history to turnedoff
					$query = "INSERT INTO payment_history ".
						"(creation_date, billing_id, status) ".
						"VALUES (CURRENT_DATE,'$billing_id','turnedoff')";
					$paymentresult = $DB->Execute($query) or die ("$l_queryfailed");
      
				}
    
			}
  
		}


/*-------------------------------------------------------------------*/
// CARRIER DEPENDENT DISABLE
//
// check if the account should be turned off if it's over
// dependent_turnoff days AND DOES HAVE CARRIER DEPENDENT SERVICES
// disable the account
/*-------------------------------------------------------------------*/
		$query = "SELECT bi.id, bi.account_number, bh.payment_due_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_turnoff DAY) AS turnoff_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_canceled DAY) AS cancel_date ".
			"FROM billing_details bd ".
			"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
			"LEFT JOIN general g ON bi.organization_id = g.id ".
			"WHERE bd.billed_amount > bd.paid_amount ".
			"AND bi.pastdue_exempt <> 'y' ".
			"AND bi.rerun_date IS NULL ".
			"AND '$today' >= DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.dependent_turnoff DAY) ".
			"AND '$today' < DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.dependent_canceled DAY) GROUP BY bi.id";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("carrier dependent disable $l_queryfailed");

		while ($myresult = $result->FetchRow()) {
			// set these services to be turned off
			$billing_id = $myresult['id'];	
			$account_number = $myresult['account_number'];
			$payment_due_date = $myresult['payment_due_date'];
			$turnoff_date = $myresult['turnoff_date'];
			$cancel_date = $myresult['cancel_date'];
  
			$dependent = carrier_dependent($account_number);
  
			if ($dependent == true) {
        
				// check recent history to see if we already set them to turned off
				$query = "SELECT status FROM payment_history ".
					"WHERE billing_id = $billing_id ORDER BY id DESC LIMIT 1";
				$DB->SetFetchMode(ADODB_FETCH_ASSOC);
				$statusresult = $DB->Execute($query) or die ("$l_queryfailed");
				$mystatusresult = $statusresult->fields;
				$mystatus = $mystatusresult['status'];
    
				if ($mystatus <> "turnedoff"
					AND $mystatus <> "collections"
					AND $mystatus <> "canceled"
					AND $mystatus <> "cancelwfee"
					AND $mystatus <> "waiting") {
					// set the account payment_history to turnedoff
					$query = "INSERT INTO payment_history ".
						"(creation_date, billing_id, status) ".
						"VALUES (CURRENT_DATE,'$billing_id','turnedoff')";
					$paymentresult = $DB->Execute($query) or die ("$l_queryfailed");

					// SEND CANCEL NOTICE BY BOTH PRINT and EMAIL
					$mynotice = new notice('cancel',$billing_id, 'both', $payment_due_date, $turnoff_date, $cancel_date);
      
					$linkname = $mynotice->pdfname;      
					$contactemail = $mynotice->contactemail;
					$linkurl = "index.php?load=tools/downloadfile&type=dl&filename=$linkname";
					$notify = "$default_billing_group";
					$description = "Turnoff and Sent Cancellation Notice to $contactemail";
					$status = "not done";
					// CREATE TICKET TO default_billing_group about turn off
					create_ticket($DB, $user, $notify, $account_number, $status,
								  $description, $linkname, $linkurl);

      
				}
    
			}
  
		}


/*-------------------------------------------------------------------------*/
// DISABLE ACCOUNTS MARKED FROM ABOVE
/*-------------------------------------------------------------------------*/
// disable any services with a turnedoff payment_history from today that
// have not already been marked as removed
		$query = "SELECT u.id u_id, u.account_number u_ac, ".
			"u.master_service_id u_master_service_id, u.billing_id u_bid, ".
			"u.start_datetime u_start, u.removed u_rem, u.usage_multiple ".
			"u_usage, m.service_description m_service_description, ".
			"m.id m_id, m.pricerate m_pricerate, m.frequency m_freq, ".
			"m.activation_string m_activation_string, m.category m_category, ".
			"m.options_table m_options_table, c.name c_name, c.company c_company, ".
			"c.street c_street, c.city c_city, c.state c_state, c.country c_country, ".
			"c.zip c_zip, c.phone c_phone, c.contact_email c_contact_email ".
			"FROM user_services u ".
			"LEFT JOIN master_services m ON m.id = u.master_service_id ".
			"LEFT JOIN customer c ON c.account_number = u.account_number ".
			"LEFT JOIN payment_history p ON p.billing_id = u.billing_id ".
			"WHERE (to_days(now()) = to_days(p.creation_date)) ".
			"AND (p.status = 'turnedoff') AND u.removed <> 'y'";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("$l_queryfailed");
  
		$disables = 0;

// loop through results and print out each
		while ($myresult = $result->FetchRow()) {
			$user_services_id = $myresult['u_id'];
			$master_service_id = $myresult['u_master_service_id'];
			$service_description = $myresult['m_service_description'];
			$account_number = $myresult['u_ac'];
			$options_table = $myresult['m_options_table'];
			$activation_string = $myresult['m_activation_string'];
			$customer_name = $myresult['c_name'];
			$customer_company = $myresult['c_company'];
			$customer_street = $myresult['c_street'];
			$customer_city = $myresult['c_city'];
			$customer_state = $myresult['c_state'];
			$customer_country = $myresult['c_country'];
			$customer_zip = $myresult['c_zip'];
			$category = $myresult['m_category'];
    
			// query this with the option_table for that service to get the 
			// activation_string variables
			$mystring = split(",", $activation_string);
  
			$newline = "\"DISABLE\",\"$category\",\"$customer_name\",\"$service_description\"";
  
			if ($options_table <> '') {
				$query = "SELECT * FROM $options_table ".
					"WHERE user_services = '$user_services_id'";
				$DB->SetFetchMode(ADODB_FETCH_ASSOC);
				$optresult = $DB->Execute($query) or die ("$l_queryfailed");
				$myoptresult = $optresult->fields;
    
				$fields = $DB->MetaColumns($options_table);        
				$i = 0;        
				$pstring = "";	
				foreach($fields as $v) {                
					//echo "Name: $v->name ";                
					$fieldname = $v->name;                
      
					//check matching fieldname in the options table
					foreach($mystring as $s) {
						if($fieldname == $s) {
							//$pstring = $pstring.$s;
							$myline = $myoptresult["$s"];
							$newline .= ",\"$myline\"";
						}	
					}
      
				} //endforeach
			} //endif
			$newline .= "\n"; // end the line
			fwrite($handle, $newline); // write to the file

			// send service_message about turnoff
			$service_notify_type = "turnoff";
			service_message($service_notify_type, $account_number,
							$master_service_id, $user_services_id, NULL, NULL);
  
			$disables++;
		} //endwhile

		echo "$disables DISABLEs\n";
	
	
/*-------------------------------------------------------------------*/
// REGULAR DELETE
/*-------------------------------------------------------------------*/
// check if any services on the account are regular_canceled days late 
// and also DO NOT HAVE CARRIER DEPENDENT SERVICES
// if so, change the payment history status to cancelwfee
// and set the Removal date to today

		$query = "SELECT DISTINCT bi.id, bi.account_number ".
			"FROM billing_details bd ".
			"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
			"LEFT JOIN general g ON bi.organization_id = g.id ".
			"WHERE bd.billed_amount > bd.paid_amount ".
			"AND bi.pastdue_exempt <> 'y' ".
			"AND bi.rerun_date IS NULL ".  
			"AND '$today' >= DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.regular_canceled DAY)";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("$l_queryfailed");

		while ($myresult = $result->FetchRow()) {
  
			// get the result values
			$billing_id = $myresult['id'];
			$account_number = $myresult['account_number'];
  
			$dependent = carrier_dependent($account_number);
  
			if ($dependent == false) {

				// check recent history to see if we already set them to be canceled or waiting
				$query = "SELECT status FROM payment_history ".
					"WHERE billing_id = $billing_id ORDER BY id DESC LIMIT 1";
				$DB->SetFetchMode(ADODB_FETCH_ASSOC);
				$statusresult = $DB->Execute($query) or die ("$l_queryfailed");
				$mystatusresult = $statusresult->fields;
				$mystatus = $mystatusresult['status'];
    
				if ($mystatus <> "collections"
					AND $mystatus <> "canceled"
					AND $mystatus <> "cancelwfee"
					AND $mystatus <> "waiting") {
    
					// initialize the removed services boolean
					$removed_services = false;
      
					$query = "SELECT * FROM user_services us ".
						"WHERE account_number = $account_number AND removed <> 'y'";
					$DB->SetFetchMode(ADODB_FETCH_ASSOC);
					$removedresult = $DB->Execute($query) or die ("$l_queryfailed");
      
					while ($myserviceresult = $removedresult->FetchRow()) {
						$userserviceid = $myserviceresult['id'];
	
						// TODO: CREATE TICKET TO SHUTOFF_NOTIFY IF NECESSARY
						// FOR EACH SERVICE THAT IS ABOUT TO BE DELETED
	
						delete_service($userserviceid,'removed', $today);
	
						// set this to true since services were removed
						$removed_services = true;
					}
    
					if ($removed_services) {
						// set cancel date and removal date of customer record
						$query = "UPDATE customer ".
							"SET cancel_date = CURRENT_DATE, ".
							"removal_date = CURRENT_DATE ".
							"WHERE account_number = $account_number";
						$cancelresult = $DB->Execute($query) 
							or die ("$l_queryfailed");
	
						// set the payment_history status to canceled      
						$query = "INSERT INTO payment_history ".
							"(creation_date, billing_id, status) ".
							"VALUES (CURRENT_DATE,'$billing_id','canceled')";
						$paymentresult = $DB->Execute($query) or die ("$l_queryfailed");
	
					}
				}    
			}  
		}	


/*-------------------------------------------------------------------*/
// CARRIER DEPENDENT DELETE
/*-------------------------------------------------------------------*/
// check if any services on the account are dependent_canceled days late 
// and also DO HAVE CARRIER DEPENDENT SERVICES
// if so, change the payment history status to cancelwfee
// and set the Removal date to today

		$query = "SELECT DISTINCT bi.id, bi.account_number ".
			"FROM billing_details bd ".
			"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
			"LEFT JOIN general g ON bi.organization_id = g.id ".
			"WHERE bd.billed_amount > bd.paid_amount ".
			"AND bi.pastdue_exempt <> 'y' ".
			"AND bi.rerun_date IS NULL ".  
			"AND '$today' >= DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.dependent_canceled DAY)";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("$l_queryfailed");

		while ($myresult = $result->FetchRow()) {
  
			// get the result values
			$billing_id = $myresult['id'];
			$account_number = $myresult['account_number'];
  
			$dependent = carrier_dependent($account_number);
  
			if ($dependent == true) {

				// check recent history to see if we already set them to be canceled or waiting
				$query = "SELECT status FROM payment_history ".
					"WHERE billing_id = $billing_id ORDER BY id DESC LIMIT 1";
				$DB->SetFetchMode(ADODB_FETCH_ASSOC);
				$statusresult = $DB->Execute($query) or die ("$l_queryfailed");
				$mystatusresult = $statusresult->fields;
				$mystatus = $mystatusresult['status'];
    
				if ($mystatus <> "collections"
					AND $mystatus <> "canceled"
					AND $mystatus <> "cancelwfee"
					AND $mystatus <> "waiting") {
    
					// initialize the removed services boolean
					$removed_services = false;
      
					$query = "SELECT * FROM user_services us ".
						"WHERE account_number = $account_number AND removed <> 'y'";
					$DB->SetFetchMode(ADODB_FETCH_ASSOC);
					$removedresult = $DB->Execute($query) or die ("$l_queryfailed");
      
					while ($myserviceresult = $removedresult->FetchRow()) {
						$userserviceid = $myserviceresult['id'];
	
						delete_service($userserviceid,'removed', $today);
	
						// set this to true since services were removed
						$removed_services = true;
					}
      
					if ($removed_services) {
						// set cancel date and removal date of customer record
						$query = "UPDATE customer ".
							"SET cancel_date = CURRENT_DATE, ".
							"removal_date = CURRENT_DATE ".
							"WHERE account_number = $account_number";
						$cancelresult = $DB->Execute($query) or die ("$l_queryfailed");
	
						// set the payment_history status to cancelwfee 
						$query = "INSERT INTO payment_history ".
							"(creation_date, billing_id, status) ".
							"VALUES (CURRENT_DATE,'$billing_id','cancelwfee')";
						$paymentresult = $DB->Execute($query) or die ("$l_queryfailed");
	
	
						// send a collections notice by both print and email
						$mynotice = new notice('collections',$billing_id, 'both', $payment_due_date, $turnoff_date, $cancel_date);
						$linkname = $mynotice->pdfname;      
						$contactemail = $mynotice->contactemail;
						$linkurl = "index.php?load=tools/downloadfile&type=dl&filename=$linkname";
						$notify = "$default_billing_group";
						$description = "Cancel w/Fee Collections Notice Sent $contactemail $url";
						$status = "not done";
						// CREATE TICKET TO default_billing_group about cancelwfee
						create_ticket($DB, $user, $notify, $account_number, $status,
									  $description, $linkname, $linkurl);

					}
				}    
			}
  
		}	


/*-------------------------------------------------------------------------*/
// DELETE ACCOUNTS MARKED FROM ABOVE
/*-------------------------------------------------------------------------*/
// delete any services if their removal_date date is today
		$query = "SELECT u.id u_id, u.account_number u_ac, ".
			"u.master_service_id u_master_service_id, u.billing_id u_bid, ".
			"u.start_datetime u_start, u.removed u_rem, u.usage_multiple ".
			"u_usage, m.service_description m_service_description, ".
			"m.id m_id, m.pricerate m_pricerate, m.frequency m_freq, ".
			"m.activation_string m_activation_string, m.category m_category, ".
			"m.options_table m_options_table, c.name c_name, c.company c_company, ".
			"c.street c_street, c.city c_city, c.state c_state, c.country c_country, ".
			"c.zip c_zip, c.phone c_phone, c.contact_email c_contact_email ".
			"FROM user_services u ".
			"LEFT JOIN master_services m ON m.id = u.master_service_id ".
			"LEFT JOIN customer c ON c.account_number = u.account_number ".
			"WHERE to_days('$today') = to_days(u.removal_date)";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("$l_queryfailed");

		$deletes = 0;
	
// loop through results and print out each
		while ($myresult = $result->FetchRow()) {
			$user_services_id = $myresult['u_id'];
			$service_description = $myresult['m_service_description'];
			$account_number = $myresult['u_ac'];
			$options_table = $myresult['m_options_table'];
			$activation_string = $myresult['m_activation_string'];
			$customer_name = $myresult['c_name'];
			$customer_company = $myresult['c_company'];
			$customer_street = $myresult['c_street'];
			$customer_city = $myresult['c_city'];
			$customer_state = $myresult['c_state'];
			$customer_country = $myresult['c_country'];
			$customer_zip = $myresult['c_zip'];
			$category = $myresult['m_category'];
  
			// query this with the option_table for that service to get the 
			// activation_string variables
			$mystring = split(",", $activation_string);
  
			$newline = "\"DELETE\",\"$category\",\"$customer_name\",\"$service_description\"";
  
			if ($options_table <> '') {
				$query = "SELECT * FROM $options_table ".
					"WHERE user_services = '$user_services_id'";
				$DB->SetFetchMode(ADODB_FETCH_ASSOC);
				$optresult = $DB->Execute($query) or die ("$l_queryfailed");
				$myoptresult = $optresult->fields;
    
				$fields = $DB->MetaColumns($options_table);        
				$i = 0;        
				$pstring = "";	
				foreach($fields as $v) {                
					//echo "Name: $v->name ";                
					$fieldname = $v->name;                
      
					//check matching fieldname in the options table
					foreach($mystring as $s) {
						if($fieldname == $s) {
							//$pstring = $pstring.$s;
							$myline = $myoptresult["$s"];
							$newline .= ",\"$myline\"";
						}	
					}
      
				} //endforeach
			} //endif
			$newline .= "\n"; // end the line
			fwrite($handle, $newline); // write to the file
			$deletes++;
		} //endwhile

		echo "$deletes DELETEs\n";

		fclose($handle); // close the file

		echo "$l_wrotefile $filename\n";	


		?>
		</body>
			  </html>

			  }


	function weekendupdate()
	{
// weekend activator function
// The same as the satusupdate script but does not automatically mark accounts for turnoff or delete services
// updates the account status on weekends for when billing is not done
// - run on weekends (in cron)
// - creates text file of account changes
// - process this text file with your own account processing system
// - you can move this script elsewhere and copy the include files there too

// Includes and Requires
		$this->load->model('billing_model');

		include('./include/database.inc.php');
		include('./include/billing.inc.php');
		include('./include/support.inc.php');
		include('./include/services.inc.php');
		require './include/citrus_base.php';
		include('./include/notice.class.php'); // to send notices

//$DB->debug = true;

//Activation Date for today
		$activatedate = date("Y-m-d");

/*--------------------------------------------------------------------------*/
// Get the path to the file location and open a new file to write data to
/*--------------------------------------------------------------------------*/
// select the info from general to get the path_to_ccfile
		$query = "SELECT * FROM settings WHERE id = '1'";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$ccfileresult = $DB->Execute($query) or die ("$l_queryfailed");
		$myccfileresult = $ccfileresult->fields;
		$path_to_ccfile = $myccfileresult['path_to_ccfile'];
		$default_billing_group = $myccfileresult['default_billing_group'];

// open the file
		$today = $activatedate;
		$filename = "$path_to_ccfile/accounts$today.csv";
		$handle = fopen($filename, 'a') or die ("cannot open $filename"); // open the file


/*-------------------------------------------------------------------*/
// ADD
/*-------------------------------------------------------------------*/
// get the list of new services added today

		$query = "SELECT u.id u_id, u.account_number u_ac, ".
			"u.master_service_id u_master_service_id, u.billing_id u_bid, ".
			"u.start_datetime u_start, u.removed u_rem, u.usage_multiple ".
			"u_usage, m.service_description m_service_description, ".
			"m.id m_id, m.pricerate m_pricerate, m.frequency m_freq, ".
			"m.activation_string m_activation_string, m.category m_category, ".
			"m.options_table m_options_table, c.name c_name, c.company c_company, ".
			"c.street c_street, c.city c_city, c.state c_state, c.country c_country, ".
			"c.zip c_zip, c.phone c_phone, c.contact_email c_contact_email ".
			"FROM user_services u ".
			"LEFT JOIN master_services m ON m.id = u.master_service_id ".
			"LEFT JOIN customer c ON c.account_number = u.account_number ".
			"WHERE to_days('$today') = to_days(u.start_datetime)";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("$l_queryfailed");

		
		$adds = 0;

// loop through results and print out each
		while ($myresult = $result->FetchRow()) {
			$user_services_id = $myresult['u_id'];
			$service_description = $myresult['m_service_description'];
			$account_number = $myresult['u_ac'];
			$options_table = $myresult['m_options_table'];
			$activation_string = $myresult['m_activation_string'];
			$customer_name = $myresult['c_name'];
			$customer_company = $myresult['c_company'];
			$customer_street = $myresult['c_street'];
			$customer_city = $myresult['c_city'];
			$customer_state = $myresult['c_state'];
			$customer_country = $myresult['c_country'];
			$customer_zip = $myresult['c_zip'];
			$category = $myresult['m_category'];
			$removed = $myresult['u_rem'];
  
			// query this with the option_table for that service to get the 
			// activation_string variables
			$mystring = split(",", $activation_string);
  
			$newline = "\"ADD\",\"$category\",\"$customer_name\",\"$service_description\"";
  
			if ($options_table <> '') {
				$query = "SELECT * FROM $options_table ".
					"WHERE user_services = '$user_services_id'";
				$DB->SetFetchMode(ADODB_FETCH_ASSOC);
				$optresult = $DB->Execute($query) 
					or die ("$l_queryfailed");
				$myoptresult = $optresult->fields;

				$fields = $DB->MetaColumns($options_table);        
				$i = 0;        
				$pstring = "";	
				foreach($fields as $v) {                
					//echo "Name: $v->name ";
					$fieldname = $v->name;

					//check matching fieldname in the options table
					foreach($mystring as $s) {
						if($fieldname == $s) {
							//$pstring = $pstring.$s;
							$myline = $myoptresult["$s"];
							$newline .= ",\"$myline\"";
						}	
					}
				
				} //endforeach
			} //endif

			$newline .= "\n"; // end the line
    
			// write the file if the service has not been removed
			if ($removed <> 'y') {
				fwrite($handle, $newline); // write to the file
				$adds++;
			}

		} //endwhile
  
		echo "$adds ADDs\n";

  
/*-------------------------------------------------------------------*/
// ENABLE
//
// if the account has an authorized status payment_history today and 
// it's previous payment_history was bad: 
// (turnedoff, canceled, cancelwfee, collections)
// or if they are in waiting status today
/*-------------------------------------------------------------------*/

// select all the accounts with a payment_history of today
		$query = "SELECT p.billing_id, b.id, b.account_number ".
			"FROM payment_history p ".
			"LEFT JOIN billing b ON p.billing_id = b.id ".
			"WHERE p.creation_date = '$today' ".
			"AND p.status = 'authorized'";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("$l_queryfailed");

		$enables = 0;

		while ($myresult = $result->FetchRow()) {
			// go through those accounts and find out which one has 
			//a previous payment_history that was declined, 
			//turnedoff, collections or canceled	
		
			$billingid = $myresult['billing_id'];	
			$account_number = $myresult['account_number'];
  
			$query = "SELECT * FROM payment_history ".
				"WHERE billing_id = '$billingid' ORDER BY id DESC LIMIT 1,1";
			$historyresult = $DB->Execute($query) or die ("$l_queryfailed");
			$myhistoryresult = $historyresult->fields;
			$secondstatus = $myhistoryresult['status'];
			if ($secondstatus == "turnedoff" 
				OR $secondstatus == "waiting" 
				OR $secondstatus == "collections" 
				OR $secondstatus == "cancelwfee" 
				OR $secondstatus == "canceled") {
				// enable services for the account
    
				$query = "SELECT u.id u_id, u.account_number u_ac, ".
					"u.master_service_id u_master_service_id, ".
					"u.billing_id u_bid, ".
					"u.start_datetime u_start, u.removed u_rem, ".
					"u.usage_multiple u_usage, ".
					"m.service_description m_service_description, ".
					"m.id m_id, m.pricerate m_pricerate, ".
					"m.frequency m_freq, ".
					"m.activation_string m_activation_string, ".
					"m.category m_category, m.activate_notify m_activate_notify, ".
					"m.options_table m_options_table, c.name c_name, ".
					"c.company c_company, c.street c_street, c.city c_city, ".
					"c.state c_state, c.country c_country, ".
					"c.zip c_zip, c.phone c_phone, ".
					"c.contact_email c_contact_email ".
					"FROM user_services u ".
					"LEFT JOIN master_services m ON m.id = u.master_service_id ".
					"LEFT JOIN customer c ON c.account_number = u.account_number ".
					"WHERE c.account_number = $account_number";
				$DB->SetFetchMode(ADODB_FETCH_ASSOC);
				$serviceresult = $DB->Execute($query) or die ("$l_queryfailed");

				// loop through results and print out each
				while ($myserviceresult = $serviceresult->FetchRow()) {
					$user_services_id = $myserviceresult['u_id'];
					$service_description = $myserviceresult['m_service_description'];
					$account_number = $myserviceresult['u_ac'];
					$options_table = $myserviceresult['m_options_table'];
					$activation_string = $myserviceresult['m_activation_string'];
					$customer_name = $myserviceresult['c_name'];
					$customer_company = $myserviceresult['c_company'];
					$customer_street = $myserviceresult['c_street'];
					$customer_city = $myserviceresult['c_city'];
					$customer_state = $myserviceresult['c_state'];
					$customer_country = $myserviceresult['c_country'];
					$customer_zip = $myserviceresult['c_zip'];
					$category = $myserviceresult['m_category'];
					$removed = $myserviceresult['u_rem']; // y or n
					$activate_notify = $myserviceresult['m_activate_notify'];
	
					// query this with the option_table for 
					// that service to get the 
					// activation_string variables
					$mystring = split(",", $activation_string);
      
					$newline = "\"ENABLE\",\"$category\",\"$customer_name\",\"$service_description\"";
      
					if ($options_table <> '') {
						$query = "SELECT * FROM $options_table ".
							"WHERE user_services = '$user_services_id'";
						$DB->SetFetchMode(ADODB_FETCH_ASSOC);
						$optresult = $DB->Execute($query) or die ("$l_queryfailed");
						$myoptresult = $optresult->fields;
	
						$fields = $DB->MetaColumns($options_table);        
						$i = 0;        
						$pstring = "";	
						foreach($fields as $v) {                
							//echo "Name: $v->name ";                
							$fieldname = $v->name;                
	  
							// check matching fieldname in 
							// the options table
							foreach($mystring as $s) {
								if($fieldname == $s) {
									//$pstring = $pstring.$s;
									$myline = $myoptresult["$s"];
									$newline .= ",\"$myline\"";
								}	
							}
	  
						} //endforeach
					} //endif
					$newline .= "\n"; // end the line
      
					// write to the file if the service has not already been removed
					if ($removed <> 'y') {
						fwrite($handle, $newline); // write to the file
						$enables++;

						// CREATE TICKET TO the activate_notify user if there is one
						if ($activate_notify) {
							$notify = "$activate_notify";
							$description = "ENABLE $category $customer_name $service_description";
							$status = "not done";
							create_ticket($DB, $user, $notify, $account_number, $status,
										  $description, NULL, NULL, NULL, $user_services_id);
						}
	
					}
				} //endwhile
			} // endif
		} //endwhile

		echo "$enables ENABLEs\n";



/*-------------------------------------------------------------------*/
// REGULAR PAST DUE
//
// set the pastdue status for accounts that have a payment_due_date
// more than g.regular_pastdue days ago (usually one day)
// and do not have carrier_dependent services
//
/*-------------------------------------------------------------------*/
		$query = "SELECT bi.id, bi.account_number, bh.payment_due_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.regular_turnoff DAY) AS turnoff_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.regular_canceled DAY) AS cancel_date ".
			"FROM billing_details bd ".
			"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
			"LEFT JOIN general g ON bi.organization_id = g.id ".
			"WHERE bd.billed_amount > bd.paid_amount ".
			"AND bi.pastdue_exempt <> 'y' ".
			"AND bi.rerun_date IS NULL ".
			"AND '$today' >= DATE_ADD(bh.payment_due_date, INTERVAL g.regular_pastdue DAY) ".
			"AND '$today' < DATE_ADD(bh.payment_due_date, INTERVAL g.regular_turnoff DAY) ".
			"GROUP BY bi.id";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("regular past due $l_queryfailed");

		while ($myresult = $result->FetchRow()) {
			// set these services to be turned off
			$billing_id = $myresult['id'];	
			$account_number = $myresult['account_number'];
			$payment_due_date = $myresult['payment_due_date'];
			$turnoff_date = $myresult['turnoff_date'];
			$cancel_date = $myresult['cancel_date'];
  
			$dependent = carrier_dependent($account_number);
  
			if ($dependent == false) {
    
				// check recent history to see if we already set them to pastdue
				$query = "SELECT status FROM payment_history ".
					"WHERE billing_id = $billing_id ORDER BY id DESC LIMIT 1";
				$DB->SetFetchMode(ADODB_FETCH_ASSOC);
				$statusresult = $DB->Execute($query) or die ("$l_queryfailed");
				$mystatusresult = $statusresult->fields;
				$mystatus = $mystatusresult['status'];
    
				if ($mystatus <> "pastdue"
					AND $mystatus <> "noticesent" 
					AND $mystatus <> "turnedoff"
					AND $mystatus <> "collections"
					AND $mystatus <> "canceled"
					AND $mystatus <> "cancelwfee"
					AND $mystatus <> "waiting") {
					// set the account payment_history to pastdue
					$query = "INSERT INTO payment_history ".
						"(creation_date, billing_id, status) ".
						"VALUES (CURRENT_DATE,'$billing_id','pastdue')";
					$paymentresult = $DB->Execute($query) or die ("$l_queryfailed");

					echo "regular pastdue: $account_number\n";


					// get the payment_due_date, turnoff_date, and cancel_date
      
					// SEND PASTDUE NOTICE BY EMAIL
					$mynotice = new notice('pastdue',$billing_id, 'email', $payment_due_date, $turnoff_date, $cancel_date);
      
					$contactemail = $mynotice->contactemail;      
					$notify = "";
					$description = "Past Due Notice Sent $contactemail";
					$status = "automatic";
					// CREATE TICKET TO NOBODY
					create_ticket($DB, $user, $notify, $account_number, $status,
								  $description, $linkname, $linkurl);
      
				}
    
			}
  
		}


/*-------------------------------------------------------------------*/
// CARRIER DEPENDENT PAST DUE
//
// set the pastdue status for accounts that have a payment_due_date
// more than g.dependent_pastdue days ago (usually one day)
// and do have carrier_dependent services
//
// insert a ticket to billing if they have carrier_dependent services
//
/*-------------------------------------------------------------------*/
		$query = "SELECT bi.id, bi.account_number, bh.payment_due_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_turnoff DAY) AS turnoff_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_canceled DAY) AS cancel_date ".
			"FROM billing_details bd ".
			"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
			"LEFT JOIN general g ON bi.organization_id = g.id ".
			"WHERE bd.billed_amount > bd.paid_amount ".
			"AND bi.pastdue_exempt <> 'y' ".
			"AND bi.rerun_date IS NULL ".
			"AND '$today' >= DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.dependent_pastdue DAY) ".
			"AND '$today' < DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.dependent_shutoff_notice DAY) GROUP BY bi.id";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("carrier dependent past due $l_queryfailed");

		while ($myresult = $result->FetchRow()) {
			// set these services to be turned off
			$billing_id = $myresult['id'];	
			$account_number = $myresult['account_number'];
			$payment_due_date = $myresult['payment_due_date'];
			$turnoff_date = $myresult['turnoff_date'];
			$cancel_date = $myresult['cancel_date'];
  
			$dependent = carrier_dependent($account_number);
  
			if ($dependent == true) {
        
				// check recent history to see if we already set them to pastdue
				$query = "SELECT status FROM payment_history ".
					"WHERE billing_id = $billing_id ORDER BY id DESC LIMIT 1";
				$DB->SetFetchMode(ADODB_FETCH_ASSOC);
				$statusresult = $DB->Execute($query) or die ("$l_queryfailed");
				$mystatusresult = $statusresult->fields;
				$mystatus = $mystatusresult['status'];
    
				if ($mystatus <> "pastdue"
					AND $mystatus <> "turnedoff"
					AND $mystatus <> "noticesent" 
					AND $mystatus <> "collections"
					AND $mystatus <> "canceled"
					AND $mystatus <> "cancelwfee"
					AND $mystatus <> "waiting") {
					// set the account payment_history to pastdue
					$query = "INSERT INTO payment_history ".
						"(creation_date, billing_id, status) ".
						"VALUES (CURRENT_DATE,'$billing_id','pastdue')";
					$paymentresult = $DB->Execute($query) or die ("$l_queryfailed");

					echo "carrier dependent pastdue: $account_number\n";
      
					// SEND PASTDUE NOTICE BY BOTH PRINT and EMAIL
					$mynotice = new notice('pastdue',$billing_id, 'both', $payment_due_date, $turnoff_date, $cancel_date);
      
					$linkname = $mynotice->pdfname;
					$contactemail = $mynotice->contactemail;
					$linkurl = "index.php?load=tools/downloadfile&type=dl&filename=$linkname";
					$notify = "";
					$description = "Past Due Notice Sent $contactemail $url";
					$status = "not done";
					// CREATE TICKET TO default_billing_group
					create_ticket($DB, $user, $notify, $account_number, $status,
								  $description, $linkname, $linkurl);
      
				}
    
			}
  
		}

/*-------------------------------------------------------------------*/
// CARRIER DEPENDENT SHUTOFF NOTICE
//
// send a shutoff notice to carrier dependent services that are
// about to be turned off in a few days
/*-------------------------------------------------------------------*/
		$query = "SELECT bi.id, bi.account_number, bh.payment_due_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_turnoff DAY) AS turnoff_date, ".
			"DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_canceled DAY) AS cancel_date ".
			"FROM billing_details bd ".
			"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
			"LEFT JOIN general g ON bi.organization_id = g.id ".
			"WHERE bd.billed_amount > bd.paid_amount ".
			"AND bi.pastdue_exempt <> 'y' ".
			"AND bi.rerun_date IS NULL ".
			"AND '$today' >= DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.dependent_shutoff_notice DAY) ".
			"AND '$today' < DATE_ADD(bh.payment_due_date, ".
			"INTERVAL g.dependent_turnoff DAY) GROUP BY bi.id";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("carrier dependent shutoff $l_queryfailed");

		while ($myresult = $result->FetchRow()) {
			// set these services to be turned off
			$billing_id = $myresult['id'];	
			$account_number = $myresult['account_number'];
			$payment_due_date = $myresult['payment_due_date'];
			$turnoff_date = $myresult['turnoff_date'];
			$cancel_date = $myresult['cancel_date'];
  
			$dependent = carrier_dependent($account_number);
  
			if ($dependent == true) {
        
				// check recent history to see if we already set them to turned off
				$query = "SELECT status FROM payment_history ".
					"WHERE billing_id = $billing_id ORDER BY id DESC LIMIT 1";
				$DB->SetFetchMode(ADODB_FETCH_ASSOC);
				$statusresult = $DB->Execute($query) or die ("$l_queryfailed");
				$mystatusresult = $statusresult->fields;
				$mystatus = $mystatusresult['status'];
    
				if ($mystatus <> "turnedoff"
					AND $mystatus <> "noticesent" 
					AND $mystatus <> "collections"
					AND $mystatus <> "canceled"
					AND $mystatus <> "cancelwfee"
					AND $mystatus <> "waiting") {
					// set the account payment_history to noticesent
					$query = "INSERT INTO payment_history ".
						"(creation_date, billing_id, status) ".
						"VALUES (CURRENT_DATE,'$billing_id','noticesent')";
					$paymentresult = $DB->Execute($query) or die ("$l_queryfailed");

					// SEND SHUTOFF NOTICE BY BOTH PRINT and EMAIL
					$mynotice = new notice('shutoff',$billing_id, 'both', $payment_due_date, $turnoff_date, $cancel_date);
      
					$linkname = $mynotice->pdfname;      
					$contactemail = $mynotice->contactemail;
					$linkurl = "index.php?load=tools/downloadfile&type=dl&filename=$linkname";
					$notify = "$default_billing_group";
					$description = "Shutoff Notice Sent $contactemail $url";
					$status = "not done";
					// TODO: CREATE TICKET TO NOBODY
					create_ticket($DB, $user, $notify, $account_number, $status,
								  $description, $linkname, $linkurl);

				}
    
			}
  
		}



/*-------------------------------------------------------------------------*/
// DISABLE ACCOUNTS MARKED BY DATABASE OPERATOR
/*-------------------------------------------------------------------------*/
// disable any services with a turnedoff payment_history from today that
// have not already been marked as removed
		$query = "SELECT u.id u_id, u.account_number u_ac, ".
			"u.master_service_id u_master_service_id, u.billing_id u_bid, ".
			"u.start_datetime u_start, u.removed u_rem, u.usage_multiple ".
			"u_usage, m.service_description m_service_description, ".
			"m.id m_id, m.pricerate m_pricerate, m.frequency m_freq, ".
			"m.activation_string m_activation_string, m.category m_category, ".
			"m.options_table m_options_table, c.name c_name, c.company c_company, ".
			"c.street c_street, c.city c_city, c.state c_state, c.country c_country, ".
			"c.zip c_zip, c.phone c_phone, c.contact_email c_contact_email ".
			"FROM user_services u ".
			"LEFT JOIN master_services m ON m.id = u.master_service_id ".
			"LEFT JOIN customer c ON c.account_number = u.account_number ".
			"LEFT JOIN payment_history p ON p.billing_id = u.billing_id ".
			"WHERE (to_days(now()) = to_days(p.creation_date)) ".
			"AND (p.status = 'turnedoff') AND u.removed <> 'y'";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("$l_queryfailed");
  
		$disables = 0;

// loop through results and print out each
		while ($myresult = $result->FetchRow()) {
			$user_services_id = $myresult['u_id'];
			$master_service_id = $myresult['u_master_service_id'];
			$service_description = $myresult['m_service_description'];
			$account_number = $myresult['u_ac'];
			$options_table = $myresult['m_options_table'];
			$activation_string = $myresult['m_activation_string'];
			$customer_name = $myresult['c_name'];
			$customer_company = $myresult['c_company'];
			$customer_street = $myresult['c_street'];
			$customer_city = $myresult['c_city'];
			$customer_state = $myresult['c_state'];
			$customer_country = $myresult['c_country'];
			$customer_zip = $myresult['c_zip'];
			$category = $myresult['m_category'];
    
			// query this with the option_table for that service to get the 
			// activation_string variables
			$mystring = split(",", $activation_string);
  
			$newline = "\"DISABLE\",\"$category\",\"$customer_name\",\"$service_description\"";
  
			if ($options_table <> '') {
				$query = "SELECT * FROM $options_table ".
					"WHERE user_services = '$user_services_id'";
				$DB->SetFetchMode(ADODB_FETCH_ASSOC);
				$optresult = $DB->Execute($query) or die ("$l_queryfailed");
				$myoptresult = $optresult->fields;
    
				$fields = $DB->MetaColumns($options_table);        
				$i = 0;        
				$pstring = "";	
				foreach($fields as $v) {                
					//echo "Name: $v->name ";                
					$fieldname = $v->name;                
      
					//check matching fieldname in the options table
					foreach($mystring as $s) {
						if($fieldname == $s) {
							//$pstring = $pstring.$s;
							$myline = $myoptresult["$s"];
							$newline .= ",\"$myline\"";
						}	
					}
      
				} //endforeach
			} //endif
			$newline .= "\n"; // end the line
			fwrite($handle, $newline); // write to the file

			// send service_message about turnoff
			$service_notify_type = "turnoff";
			service_message($service_notify_type, $account_number,
							$master_service_id, $user_services_id, NULL, NULL);
  
			$disables++;
		} //endwhile

		echo "$disables DISABLEs\n";
	
	
/*-------------------------------------------------------------------------*/
// DELETE ACCOUNTS MARKED BY DATABASE OPERATOR
/*-------------------------------------------------------------------------*/
// delete any services if their removal_date date is today
		$query = "SELECT u.id u_id, u.account_number u_ac, ".
			"u.master_service_id u_master_service_id, u.billing_id u_bid, ".
			"u.start_datetime u_start, u.removed u_rem, u.usage_multiple ".
			"u_usage, m.service_description m_service_description, ".
			"m.id m_id, m.pricerate m_pricerate, m.frequency m_freq, ".
			"m.activation_string m_activation_string, m.category m_category, ".
			"m.options_table m_options_table, c.name c_name, c.company c_company, ".
			"c.street c_street, c.city c_city, c.state c_state, c.country c_country, ".
			"c.zip c_zip, c.phone c_phone, c.contact_email c_contact_email ".
			"FROM user_services u ".
			"LEFT JOIN master_services m ON m.id = u.master_service_id ".
			"LEFT JOIN customer c ON c.account_number = u.account_number ".
			"WHERE to_days('$today') = to_days(u.removal_date)";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("$l_queryfailed");

		$deletes = 0;
	
// loop through results and print out each
		while ($myresult = $result->FetchRow()) {
			$user_services_id = $myresult['u_id'];
			$service_description = $myresult['m_service_description'];
			$account_number = $myresult['u_ac'];
			$options_table = $myresult['m_options_table'];
			$activation_string = $myresult['m_activation_string'];
			$customer_name = $myresult['c_name'];
			$customer_company = $myresult['c_company'];
			$customer_street = $myresult['c_street'];
			$customer_city = $myresult['c_city'];
			$customer_state = $myresult['c_state'];
			$customer_country = $myresult['c_country'];
			$customer_zip = $myresult['c_zip'];
			$category = $myresult['m_category'];
  
			// query this with the option_table for that service to get the 
			// activation_string variables
			$mystring = split(",", $activation_string);
  
			$newline = "\"DELETE\",\"$category\",\"$customer_name\",\"$service_description\"";
  
			if ($options_table <> '') {
				$query = "SELECT * FROM $options_table ".
					"WHERE user_services = '$user_services_id'";
				$DB->SetFetchMode(ADODB_FETCH_ASSOC);
				$optresult = $DB->Execute($query) or die ("$l_queryfailed");
				$myoptresult = $optresult->fields;
    
				$fields = $DB->MetaColumns($options_table);        
				$i = 0;        
				$pstring = "";	
				foreach($fields as $v) {                
					//echo "Name: $v->name ";                
					$fieldname = $v->name;                
      
					//check matching fieldname in the options table
					foreach($mystring as $s) {
						if($fieldname == $s) {
							//$pstring = $pstring.$s;
							$myline = $myoptresult["$s"];
							$newline .= ",\"$myline\"";
						}	
					}
      
				} //endforeach
			} //endif
			$newline .= "\n"; // end the line
			fwrite($handle, $newline); // write to the file
			$deletes++;
		} //endwhile

		echo "$deletes DELETEs\n";

		fclose($handle); // close the file

		echo "$l_wrotefile $filename\n";	

	}
	
}

/* end file command.php */
?>