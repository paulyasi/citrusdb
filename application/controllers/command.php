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
	 * php index.php command statusupdate
	 * -------------------------------------------------------------------------
	 */	 
	function statusupdate()
	{
		$this->user = "update";

		// load models
		$this->load->model('billing_model');
		$this->load->model('support_model');
		$this->load->model('settings_model');
		$this->load->model('service_model');
		$this->load->model('settings_model');
		$this->load->model('schema_model');
		$this->load->model('update_model');

		// todays' date
		$activatedate = date("Y-m-d");

		// get the path_to_ccfile and default_billing_group
		$path_to_ccfile = $this->settings_model->get_path_to_ccfile();
		$default_billing_group = $this->settings_model->get_default_billing_group(); 

		// open the file
		$today = $activatedate;
		$filename = "$path_to_ccfile/accounts$today.csv";
		$handle = fopen($filename, 'a') or die ("cannot open $filename"); // open the file

		$adds = $this->update_model->add($handle, $activatedate);
		echo "$adds ADDs\n";

		$enables = $this->update_model->enable($handle, $activatedate);
		echo "$enables ENABLEs\n";

		$regularpastdues = $this->update_model->regular_past_due($handle, $activatedate);

		$carrierdependentpastdues = $this->update_model->carrier_dependent_past_due($handle, $activatedate);

		$carrierdependentshutoffs = $this->update_model->carrier_dependent_shutoff_notice($handle, $activatedate);

		$regulardisables = $this->update_model->regular_disable($handle, $activatedate);

		$carrierdependentdisables = $this->update_model->carrier_dependent_disable($handle, $activatedate);

		$disables = $this->update_model->disable_accounts($handle, $activatedate);
		echo "$disables DISABLEs\n";

		$regulardeletes = $this->update_model->regular_delete($handle, $activatedate);

		$carrierdependentdeletes = $this->update_model->carrier_dependent_delete($handle, $activatedate);

		$deletes = $this->update_model->delete_accounts($handle, $activatedate);
		echo "$deletes DELETEs\n";

		fclose($handle); // close the file

		echo lang('wrotefile')." ".$filename."\n";	

	}



	/*
	 * -------------------------------------------------------------------------	 
	 * weekend update function
	 *
	 * "Goodnight, and have a pleasant tomorrow"
	 * 
	 * The same as the satusupdate script but does not automatically mark accounts for turnoff or delete services
	 * updates the account status on weekends for when billing is not done
	 * - run on weekends (in cron)
	 * - creates text file of account changes
	 * - process this text file with your own account processing system
	 * - you can move this script elsewhere and copy the include files there too
	 *--------------------------------------------------------------------------
	 */
	function weekendupdate()
	{
		$this->user = "update";

		// load models
		$this->load->model('billing_model');
		$this->load->model('support_model');
		$this->load->model('settings_model');
		$this->load->model('service_model');
		$this->load->model('settings_model');
		$this->load->model('schema_model');
		$this->load->model('update_model');

		// todays' date
		$activatedate = date("Y-m-d");

		// get the path_to_ccfile and default_billing_group
		$path_to_ccfile = $this->settings_model->get_path_to_ccfile();
		$default_billing_group = $this->settings_model->get_default_billing_group(); 

		// open the file
		$today = $activatedate;
		$filename = "$path_to_ccfile/accounts$today.csv";
		$handle = fopen($filename, 'a') or die ("cannot open $filename"); // open the file
		
		// add
		$adds = $this->update_model->add($handle, $activatedate);
		echo "$adds ADDs\n";
		
		// enable
		$enables = $this->update_model->enable($handle, $activatedate);
		echo "$enables ENABLEs\n";
		
		// regular past due
		$regularpastdues = $this->update_model->regular_past_due($handle, $activatedate);
		
        // carrier dependent past due
		$carrierdependentpastdues = $this->update_model->carrier_dependent_past_due($handle, $activatedate);
		
		// carrier dependent shutoff notice
		$carrierdependentshutoffs = $this->update_model->carrier_dependent_shutoff_notice($handle, $activatedate);

		// disable accounts (marked by database ops)
		$disables = $this->update_model->disable_accounts($handle, $activatedate);
		echo "$disables DISABLEs\n";

		// delete accounts (marked by database ops)		
		$deletes = $this->update_model->delete_accounts($handle, $activatedate);
		echo "$deletes DELETEs\n";

		fclose($handle); // close the file

		echo lang('wrotefile')." ".$filename."\n";	
		
	}


	/*
	 * ------------------------------------------------------------------------
	 * This script will email a receipt to customers who have the automatic receipt
	 * field marked Yes in their billing record
	 * This script should be put into the cron to be run nightly
	 * ------------------------------------------------------------------------
	 */
	function autoreceipt()
	{
		$this->load->model('billing_model');
		$this->load->helper('date');

		// set the email address that this message comes from
		$from_email = "yourname@example.com";

		$subject = "Receipt for Internet Service";

		$receiptresult = $this->billing_model->todays_receipts();

		// go through each receipt recipient
		foreach ($receiptresult AS $myreceiptresult) 
		{
			// initialize the message body to be empty
			$message = "";

			$paymentid = $myreceiptresult['ph_id'];
			$amount = $myreceiptresult['billing_amount'];
			$billingid = $myreceiptresult['b_id'];
			$payment_date = $myreceiptresult['creation_date'];
			$payment_type = $myreceiptresult['payment_type'];
			$creditcard_number = $myreceiptresult['creditcard_number'];
			$check_number = $myreceiptresult['check_number'];

			// print paid_amounts from billing_details
			$myresult = $this->billing_model->billing_and_organization($billingid);
			$billing_name = $myresult['name'];
			$billing_company = $myresult['company'];
			$billing_street = $myresult['street'];
			$billing_city = $myresult['city'];
			$billing_state = $myresult['state'];
			$billing_zip = $myresult['zip'];
			$billing_account_number = $myresult['account_number'];
			$billing_email = $myresult['contact_email'];
			$org_name = $myresult['org_name'];

			$message .= "$org_name\n".
				lang('paymentreceipt')."\n".
				lang('accountnumber').": $billing_account_number\n\n".
				"$billing_name\n".
				"$billing_company\n".
				"$billing_street\n".
				"$billing_city $billing_state $billing_zip\n\n";

			$human_date = humandate($payment_date);

			if ($payment_type == "creditcard") 
			{    
				// wipe out the middle of the card number
				$length = strlen($creditcard_number);
				$firstdigit = substr($creditcard_number, 0,1);
				$lastfour = substr($creditcard_number, -4);
				$creditcard_number = "$firstdigit" . "***********" . "$lastfour";

				$message .= lang('paid')." with $payment_type ($creditcard_number), ".
					"$amount on $human_date for:\n";
			} 
			else 
			{
				$message .= lang('paid')." with $payment_type (number: $check_number), ".
					"$amount on $human_date for:\n";    
			}

			// get the resulting list of services that have payment applied with
			// the matching payment_history_id
			$result = $this->billing_model->payment_details($paymentid);

			foreach($result AS $myresult) 
			{
				$invoice = $myresult['original_invoice_number'];
				$description = $myresult['service_description'];
				$tax_description = $myresult['description'];
				$from_date = humandate($myresult['from_date']);
				$to_date = humandate($myresult['to_date']);
				$paid_amount = sprintf("%.2f",$myresult['paid_amount']);
				$billed_amount = sprintf("%.2f",$myresult['billed_amount']);  

				$owed_amount = sprintf("%.2f",$billed_amount - $paid_amount);

				if ($tax_description) 
				{
					// print the tax as description instead
					$message .= "$invoice\t   $tax_description\t$paid_amount\n";
				} 
				else 
				{
					$message .= "$invoice\t$description ($from_date ".lang('to')." $to_date)\t$paid_amount\n";
				}
			}
			echo "\n";
			echo "$message";
			// send the email message for this user
			$headers = "From: $from_email \n";
			mail ($billing_email, $subject, $message, $headers);

		} // end while myreceipt result

	}


	/*
	 * ------------------------------------------------------------------------
	 * This script will email a reminder to multi-month creditcard customers
	 * that they will be billed automatically in 3 weeks time.
	 * 	
 	 * You must edit the $message_body variable to say what you would like it
 	 * to say about the upcoming transaction  and the $from_email to indicate
 	 * the address the email will come from
	 * ------------------------------------------------------------------------
	 */
	function ccrunreminder()
	{
		$this->load->model('billing_model');
		$this->load->model('support_model');
		$this->load->helper('date');

		$message_body = "Thank you for choosing [YOUR COMPANY]. We would like to remind you that your $billingtype ".
			"account will renew automatically on $next_billing_date using the credit card on file. ".
			"Your current cost for service is \$$newtotal\n\n".
			"Please call with any updates or changes to your billing information.\n\n".
			"If you have any questions, please call our offices at [YOUR PHONE NUMBER]";

		$from_email = "yourname@example.com";
		
		$result = $this->billing_model->ccrunreminder();

		foreach ($result AS $myresult)
		{
			$billing_id = $myresult['id'];
			$to = $myresult['contact_email'];
			$name = $myresult['name'];
			$next_billing_date = $myresult['next_billing_date'];
			$next_billing_date = humandate($next_billing_date);
			$billingtype = $myresult['bt_name'];
			$account_number = $myresult['account_number'];

			$newtaxes = sprintf("%.2f",$this->billing_model->total_taxitems($billing_id));
			$newcharges = sprintf("%.2f",$this->billing_model->total_serviceitems($billing_id)+$newtaxes);
			$pastcharges = sprintf("%.2f",$this->billing_model->total_pastdueitems($billing_id));
			$newtotal = sprintf("%.2f",$newcharges + $pastcharges);

			$subject = "Time to renew your internet account!";

			$message = "$name,\n $message_body";

			echo "sending a reminder to $to for $newtotal\n";	
			
			$headers = "From: $from_email \n";
			mail ($to, $subject, $message, $headers);

			// put a ticket to say that this message was sent
			$user = "system";
			$notify = "nobody";
			$status = "automatic";
			$description = "Sent reminder for $newtotal to $to";
			$this->support_model->create_ticket($user, $notify, $account_number, $status, $description);

		}

	}


	/*
	 * ------------------------------------------------------------------------
	 * This script will write out the activity log from the day before to a log file
	 * This script should be put into the cron to be run each morning before logwatch
	 * log format should be similar to syslog:
	 * Nov 11 15:54:14 citrusdb citrusdb/activity: admin 127.0.0.1 10005 delete service 50 success
	 * ------------------------------------------------------------------------
	 */
	function logactivity()
	{
		// set the path and name of the log file
		$logfile = "/var/log/citrusdb.log";

		// set the hostname to be included in the log
		$hostname = "citrusdb";

		// open the log file to append to it
		$filehandle = fopen($logfile, 'a') or die("can't open $logfile file");

		$yesterday  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));

		$activitylog = $this->log_model->activity_on_date($yesterday);

		// go through each receipt recipient
		foreach ($activitylog AS $myactivitylog) 
		{
			// get each item from the activity log
			$date = $myactivitylog['date'];
			$time = $myactivitylog['time'];
			$user = $myactivitylog['user'];
			$ip_address = $myactivitylog['ip_address'];
			$account_number = $myactivitylog['account_number'];
			$activity_type = $myactivitylog['activity_type'];
			$record_type = $myactivitylog['record_type'];
			$record_id = $myactivitylog['record_id'];
			$result = $myactivitylog['result'];

			/*-------------------------------------------------------------------------*/
			// append items to the citrusdb.log
			/*-------------------------------------------------------------------------*/
			
			// split the iso date into parts
			list($myyear, $mymonth, $myday) = preg_split('/-/', $date);

			// assign the month it's written name
			switch($mymonth) {
				case "01":
					$mymonth = "Jan";
					break;
				case "02":
					$mymonth = "Feb";
					break;
				case "03":
					$mymonth = "Mar";
					break;
				case "04":
					$mymonth = "Apr";
					break;
				case "05":
					$mymonth = "May";
					break;
				case "06":
					$mymonth = "Jun";
					break;
				case "07":
					$mymonth = "Jul";
					break;
				case "08":
					$mymonth = "Aug";
					break;
				case "09":
					$mymonth = "Sep";
					break;
				case "10":
					$mymonth = "Oct";
					break;
				case "11":
					$mymonth = "Nov";
					break;
				case "12":
					$mymonth = "Dec";
					break;    
			}

			// replace the zero with a space for single digit days
			if ($myday < 10) {
				$myday = str_replace( "0" , " " , $myday );
			}

			// Nov 11 15:54:14 citrusdb citrusdb: admin 127.0.0.1 10005 delete service 50 success
			$logtext = "$mymonth $myday $time $hostname citrusdb: $user $ip_address $account_number $activity_type $record_type $record_id $result\n";

			// write to the log file
			fwrite($filehandle, $logtext);

		} // end while activity log

		// close the log file
		fclose($filehandle);

	}

	
	// TODO: test from ccexpire on down

	/*
	 * --------------------------------------------------------------------------
	 *  send a reminder to customers the month their credit card expires
	 *  eg: on 2011-02-01 remind all users with 0211 expiration dates
	 * ---------------------------------------------------------------------------
	 */
	function ccexpire()
	{
		$this->load->model('billing_model');
		$this->load->model('support_model');

		// calculate the expire date for this month
		$nextexpdate = date("my", mktime(0, 0, 0, date("m"), date("y")));

		$result = $this->billing_model->find_expired_cards($nextexpdate);

		foreach ($result AS $myresult)
		{
			$billing_id = $myresult['id'];
			$to = $myresult['contact_email'];
			$name = $myresult['name'];
			$next_billing_date = $myresult['next_billing_date'];
			$next_billing_date = humandate($next_billing_date, $lang);
			$billingtype = $myresult['bt_name'];
			$account_number = $myresult['account_number'];
			$creditcard_number = $myresult['creditcard_number'];
			$creditcard_expire = $myresult['creditcard_expire'];

			$subject = "Your credit card is about to expire";

			// fix any ascii characters in their name
			$name = html_to_ascii($name);

			$message = "Account Number: $account_number\n\n".
				"$name,\n\n".
				"Thank you for choosing [company]. We would like to remind you that your ".
				"creditcard on file is about to expire at the end of this month.\n".
				"\n".
				"$creditcard_number, $creditcard_expire\n".
				"\n".
				"Please contact us with your new credit card expiration date so we may ".
				"continue providing service without any billing interruptions.\n\n".
				"If you have any questions, please call our offices at (XXX) XXX-XXXX";

			echo "sending a expiration reminder to $to $account_number\n";	
			$headers = "From: billing@example.com \n";
			mail ($to, $subject, $message, $headers);

			// put a ticket to say that this message was sent
			$user = "system";
			$notify = "nobody";
			$status = "automatic";
			$description = "Sent card expiration reminder to $to";
			$this->support_model->create_ticket($user, $notify, $account_number, $status, $description);

		}

	}


	/*
	 * ------------------------------------------------------------------------
	 * This script will send einvoices to customers who have the einvoice
	 * as their chosen billing type on the current date.  It is set to
	 * only send to those in the first organization specified.  If you have multiple
	 * brand organizations in your configuration you will need to duplicate this
	 * script and set the organization_id variable for those also.
	 * 	
	 * This script should be put into the cron to be run nightly
	 * ------------------------------------------------------------------------
	 */
	function autoeinvoice()
	{
		$this->load->model('billing_model');
		$this->load->model('support_model');

		// set the billing date to today
		$billingdate = date("Y-m-d");

		$organization_id = 1;

		/*-------------------------------------------------------------------*/
		// Create the billing data
		/*-------------------------------------------------------------------*/

		// determine the next available batch number
		$batchid = $this->billing_model->get_nextbatchnumber($DB);
		echo "BATCH: $batchid<p>\n";

		// query for taxed services that are billed on the specified date
		// and for a specific organization
		$numtaxes = $this->billing_model->add_taxdetails($billingdate, NULL, 
				'einvoice', $batchid, $organization_id);
		$numservices = $this->billing_model->add_servicedetails($billingdate, 
				NULL,'einvoice', $batchid, $organization_id);

		echo "taxes: $numtaxes, services: $numservices<p>";

		// create billinghistory
		$user = "autoeinvoice";
		create_billinghistory($DB, $batchid, 'einvoice', $user);	

		/*-------------------------------------------------------------------*/	
		// Email the invoice
		/*-------------------------------------------------------------------*/

		// query the batch for the invoices to do
		$result = $this->billing_model->get_invoice_batch($batchid);

		foreach ($result AS $myresult) 
		{
			// get the invoice data to process now
			$invoice_number = $myresult['invoice_number'];
			$contact_email = $myresult['contact_email'];
			$invoice_account_number = $myresult['account_number'];
			$invoice_billing_id = $myresult['id'];

			$this->billing_model->emailinvoice($invoice_number,$contact_email,
					$invoice_billing_id);
		}

	}


	/*
	 * ------------------------------------------------------------------------
	 * This script will run today's credit card billing via the authorize.net gateway
	 * To run this script, copy this script to the root of your citrus folder
	 * It can be executed from the command line or in a cron job
	 * When you run this script you must supply the GPG passphrase to decrypt the card
	 * data stored in citrusdb before it can be sent to authorize.net
	 * 	
	 * This script requires php with the cURL module installed.
	 * For Debian/Ubuntu it is available to install as a package named php5-curl
	 * 	
 	 * This script also needs the two auth_ variables filled in to define the
 	 * Authorize.Net setup information
	 * 
	 * takes $organization_id as input on command line
	 * 
	 * ------------------------------------------------------------------------
	 */
	function authorizenet($organization_id)
	{
		$this->load->model('billing_model');
		$this->load->model('settings_model');

		// the login name for your authorize.net api
		$auth_api_login='';

		// the transaction key for your authorize.net gateway
		$auth_transaction_key='';

		// get the passphrase from the command line
		$passphrase = $argv[1];
	
		$billingdate = date("Y-m-d");

		/*--------------------------------------------------------------------*/
		// Create the billing data
		/*--------------------------------------------------------------------*/
		// determine the next available batch number
		$batchid = $this->billing_model->get_nextbatchnumber();
		echo "Batch ID: $batchid<p>\n";
		echo "Billing Date: $billingdate<p>\n";
		echo "Organization ID: $organization_id<p>\n";

		$totalall = 0;

		// for a single date run
		// Add creditcard taxes and services to the bill
		$numtaxes = $this->billing_model->add_taxdetails($billingdate, NULL, 
				'creditcard', $batchid, $organization_id);
		$numservices = $this->billing_model->add_servicedetails($billingdate, 
				NULL, 'creditcard', $batchid, $organization_id);
		echo "Credit Cards:: $numtaxes ".lang('added').", 
			$numservices ".lang('added')."<p>\n";

		// Add prepaycc taxes and services to the bill
		$numpptaxes = $this->billing_model->add_taxdetails($billingdate, NULL, 
				'prepaycc', $batchid, $organization_id);
		$numppservices = $this->billing_model->add_servicedetails($billingdate, NULL,  
				'prepaycc', $batchid, $organization_id);
		echo "Pre-Pay: $l_creditcard: $numpptaxes $l_added, 
			$numppservices $l_added<p>\n";

		// Update Reruns to the bill
		$numreruns = $this->billing_model->update_rerundetails($billingdate, 
				$batchid, $organization_id);
		echo "$numreruns ".lang('rerun')."<p>\n";

		$totalall = $numreruns + $numservices + $numtaxes + $numpptaxes + $numppservices + $totalall;


		// show message if no records have been found
		if ($totalall == 0) 
		{
			echo "<b>No Records Found<b><p>\n";
		} 
		else 
		{
			// create billinghistory
			$this->billing_model->create_billinghistory($batchid, 'creditcard', $user);

			/*--------------------------------------------------------------------*/
			// print the credit card billing to a file
			/*--------------------------------------------------------------------*/

			// select the info from general to get the ccexport variable order
			$myccvarresult = $this->ccexportvars($organization_id);
			$ccexportvarorder = $myccvarresult['ccexportvarorder'];
			$exportprefix = $myccvarresult['exportprefix'];	

			// convert the $ccexportvarorder &#036; dollar signs back to actual dollar signs and &quot; back to quotes
			$ccexportvarorder = str_replace( "&#036;"           , "$"        , $ccexportvarorder );
			$ccexportvarorder = str_replace( "&quot;"           , "\\\""        , $ccexportvarorder );

			// query the batch for the invoices to do
			$result = $this->billing_model->get_recent_invoice_numbers($batchid);

			foreach ($result AS $myresult) 
			{
				// get the invoice data to process now
				$invoice_number = $myresult['recent_invoice_number'];

				$myinvresult = $this->billing_model->get_invoice_data($invoice_number);

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

				// don't bill them if the amount is less than or equal to zero
				if ($precisetotal > 0) 
				{
					echo "account num: $billing_acctnum\n";
					echo "ccnum: $billing_ccnum\n";
					echo "ccexp: $billing_ccexp\n";
					echo "Amount: $precisetotal\n";

					// write the encrypted_creditcard_number to a temporary file
					// and decrypt that file to stdout to get the CC
					// select the path_to_ccfile from settings
					$path_to_ccfile = $this->settings_model->get_path_to_ccfile();

					// open the file
					$cipherfilename = "$path_to_ccfile/ciphertext.tmp";
					$cipherhandle = fopen($cipherfilename, 'w') or die ("cannot open $cipherfilename");

					// write the ciphertext we want to decrypt into the file
					fwrite($cipherhandle, $encrypted_creditcard_number);

					// close the file
					fclose($cipherhandle);

					// destroy the output array before we use it again
					unset($decrypted);

					$gpgcommandline = $this->config->item('gpg_decrypt')." $cipherfilename";
					$decrypted = decrypt_command($gpgcommandline, $passphrase);

					// if there is a gpg error, stop here
					if (substr($decrypted,0,5) == "error") 
					{
						die ("Credit Card Encryption Error: $decrypted ".lang('billingid').": $mybilling_id");
					}

					// set the billing_ccnum to the decrypted_creditcard_number
					$decrypted_creditcard_number = $decrypted;
					$billing_ccnum = $decrypted_creditcard_number;

					//Send charge to authorize.net	
					$charge_result = authorizenet_charge_card("CC", $billing_ccnum, $billing_ccexp, $precisetotal, "Bill for Account #: " . $billing_acctnum, $invoice_number, $billing_name, NULL, $billing_street, $billing_state, $billing_zip, "1");	

					$response_array = explode("|",$charge_result);			

					switch ($response_array[0]) {
						case 1:
							echo "Transaction Approved<p>\n";
							authorizenet_card_approved($response_array[4], $mybilling_id, $billing_ccnum, $billing_ccexp, $charge_result, $precisetotal, "creditcard", "", $auth_api_login, $auth_transaction_key);
							break;
						case 2:
							echo "Transaction Declined<p>\n";
							authorizenet_card_declined($response_array[4], $mybilling_id, $billing_ccnum, $billing_ccexp, $charge_result, $precisetotal, "creditcard", "", $auth_api_login, $auth_transaction_key);
							break;
						case 3:
							echo "Transaction Error<p>\n";
							authorizenet_card_declined($response_array[4], $mybilling_id, $billing_ccnum, $billing_ccexp, $charge_result, $precisetotal, "creditcard", "", $auth_api_login, $auth_transaction_key);
							break;
						case 4:
							echo "Hold For Review<p>\n";
							authorizenet_card_declined($response_array[4], $mybilling_id, $billing_ccnum, $billing_ccexp, $charge_result, $precisetotal, "creditcard", "", $auth_api_login, $auth_transaction_key);
							break;
					}
				}
			} // end while

		} // end if totalall

		// individual elements of the array could be accessed to read certain response
		// fields.  For example, response_array[0] would return the Response Code,
		// response_array[2] would return the Response Reason Code.
		// for a list of response fields, please review the AIM Implementation Guide
	}


	// transaction approved
	function authorizenet_card_approved($transaction_code, $billing_id, $cardnumber,
			$cardexp, $response_code, $amount, $billingmethod, $avs_response) 
	{
		$payment_history_id = $this->billing_method->insert_card_payment_history(
				'authorized',
				$transaction_code,
				$billing_id,
				$cardnumber,
				$cardexp,
				$response_code,
				$amount,
				$avs_response
				);

		// update the next_billing_date, to_date, 
		// from_date, and payment_due_date for prepay/prepaycc 
		if ($billingmethod == 'prepaycc' OR $billingmethod == 'prepay') 
		{
			$this->billing_method->update_billing_dates($mybillingdate,
					$mybillingfreq, $myfromdate, $billing_id);
		} // end if billing method

		$this->billing_model->pay_billing_details($payment_history_id,
				$billing_id, $amount);
	}


	function authorizenet_card_declined($transaction_code, $billing_id, $cardnumber,
			$cardexp, $response_code, $amount, $billingmethod, $avs_response) 
	{
		$typeresult = $this->billing_model->get_billing_method_attributes($billing_id);

		$billingmethod = $mytyperesult['t_method'];
		$mybillingdate = $mytyperesult['b_next_billing_date'];
		$myfromdate = $mytyperesult['b_from_date'];
		$mytodate = $mytyperesult['b_to_date'];
		$mybillingfreq = $mytyperesult['t_frequency'];
		$contact_email = $mytyperesult['b_contact_email'];

		$this->billing_model->insert_card_payment_history('declined',
				$transaction_code, $billing_id, $cardnumber, 
				$cardexp, $response_code, $amount, $avs_response);

		// put a message in the customer notes that 
		// a declined email was sent to their contact_email

		// get their account_number first
		$myaccountnumber = $this->billing_model->get_account_number($billing_id);

		// put a note in the customer history
		// add to customer_history
		$status = "automatic";
		$desc = lang('declinedmessagesentto')." $contact_email";
		$this->support_model->create_ticket($this->user, 'nobody', 
				$myaccountnumber, $status, $desc);

		//send email			  
		$this->billing_model->send_declined_email($mybillingid);
	}


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
	function authorizenet_charge_card($Type, $CardNumber, $ExpDate, $Amount, $Description, $Invoice, $FirstName, $LastName, $Address, $State, $Zip, $Test, $auth_api_login, $auth_transaction_key) {

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

}

/* end file command.php */
?>
