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
		
		// setup some variables from the App_Controller
		$this->url_prefix = $this->config->item('base_url');
		$this->ssl_url_prefix = $this->config->item('ssl_base_url');
    }


    /*
     * -----------------------------------------------------------------------
     * php index.php command setup
     * run this to setup a new database automatically
     * it will use the setup model to make a new database as specified in your
     * config information
     * -----------------------------------------------------------------------
     */
    public function setup()
    {
        $this->load->model('setup_model');
        $this->setup_model->setup_database();
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
			$notify = "";
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
		$this->load->helper('date');
		$this->load->helper('htmlascii');

		// calculate the expire date for this month
		$nextexpdate = date("my", mktime(0, 0, 0, date("m"), date("y")));

		$result = $this->billing_model->find_expired_cards($nextexpdate);

		foreach ($result AS $myresult)
		{
			$billing_id = $myresult['id'];
			$to = $myresult['contact_email'];
			$name = $myresult['name'];
			$next_billing_date = $myresult['next_billing_date'];
			$next_billing_date = humandate($next_billing_date);
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
			$notify = "";
			$status = "automatic";
			$description = "Sent card expiration reminder to $to";
			$this->support_model->create_ticket($user, $notify, $account_number,
					 $status, $description);

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
		$this->load->model('service_model');

		// set the billing date to today
		$billingdate = date("Y-m-d");

		$organization_id = 1;

		/*-------------------------------------------------------------------*/
		// Create the billing data
		/*-------------------------------------------------------------------*/

		// determine the next available batch number
		$batchid = $this->billing_model->get_nextbatchnumber();
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
		$this->billing_model->create_billinghistory($batchid, 'einvoice', $user);	

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


	// TODO: test from here on down

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
	 * takes passphrase and organization id as input on command line
	 * 
	 * ------------------------------------------------------------------------
	 */
	function authorizenet($passphrase, $organization_id = 1)
	{
		$this->load->model('billing_model');
		$this->load->model('settings_model');

		// the login name for your authorize.net api
		$auth_api_login='';

		// the transaction key for your authorize.net gateway
		$auth_transaction_key='';

		$billingdate = date("Y-m-d");

		$user = "system";

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
		echo "Pre-Pay: ".lang('creditcard').": $numpptaxes ".lang('added').", 
			$numppservices ".lang('added')."<p>\n";

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
			$myccvarresult = $this->billing_model->ccexportvars($organization_id);
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
			
					// load the encryption helper for use when calling gpg things
					$this->load->helper('encryption');

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
					$charge_result = $this->authorizenet_charge_card(
								"CC", 
								$billing_ccnum, 
								$billing_ccexp, 
								$precisetotal, 
								"Bill for Account #: " . $billing_acctnum, 
								$invoice_number, 
								$billing_name, 
								NULL, 
								$billing_street, 
								$billing_state, 
								$billing_zip, 
								"1",
								$auth_api_login,
								$auth_transaction_key);	

					$response_array = explode("|",$charge_result);			

					switch ($response_array[0]) {
						case 1:
							echo "Transaction Approved<p>\n";
							$this->authorizenet_card_approved($response_array[4], $mybilling_id, $billing_ccnum, $billing_ccexp, $charge_result, $precisetotal, "creditcard", "");
							break;
						case 2:
							echo "Transaction Declined<p>\n";
							$this->authorizenet_card_declined($response_array[4], $mybilling_id, $billing_ccnum, $billing_ccexp, $charge_result, $precisetotal, "creditcard", "");
							break;
						case 3:
							echo "Transaction Error<p>\n";
							$this->authorizenet_card_declined($response_array[4], $mybilling_id, $billing_ccnum, $billing_ccexp, $charge_result, $precisetotal, "creditcard", "");
							break;
						case 4:
							echo "Hold For Review<p>\n";
							$this->authorizenet_card_declined($response_array[4], $mybilling_id, $billing_ccnum, $billing_ccexp, $charge_result, $precisetotal, "creditcard", "");
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
		$this->load->model('billing_model');
		$this->load->model('settings_model');

		$payment_history_id = $this->billing_model->insert_card_payment_history(
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
			$this->billing_model->update_billing_dates($mybillingdate,
					$mybillingfreq, $myfromdate, $billing_id);
		} // end if billing method

		$this->billing_model->pay_billing_details($payment_history_id,
				$billing_id, $amount);
	}


	function authorizenet_card_declined($transaction_code, $billing_id, $cardnumber,
			$cardexp, $response_code, $amount, $billingmethod, $avs_response) 
	{
		$this->load->model('billing_model');
		$this->load->model('settings_model');
		$this->load->model('support_model');

		$mytyperesult = $this->billing_model->get_billing_method_attributes($billing_id);

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
		$user = "system";
		$status = "automatic";
		$notify = "";
		$desc = lang('declinedmessagesentto')." $contact_email";
		$this->support_model->create_ticket($user, $notify, 
				$myaccountnumber, $status, $desc);

		//send email			  
		$this->billing_model->send_declined_email($billing_id);
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
	function authorizenet_charge_card($Type, $CardNumber, $ExpDate, $Amount, 
			$Description, $Invoice, $FirstName, $LastName, $Address, $State, 
			$Zip, $Test, $auth_api_login, $auth_transaction_key) 
	{
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


    /*
     * command to update the database to the next release
     * php index.php command update
     * TODO change this to output to command line instead of html like 2.0 did
     * TODO use the schema_model to hold these update queries
     */
    public function update() 
    {
        $this->load->model('schema_model');

        $databaseversion = $this->schema_model->databaseversion();
        
        if ($databaseversion == "")
        {
            // if databaseversion is empty then query the settings table
            $query = "SELECT version FROM settings";
            $DB->SetFetchMode(ADODB_FETCH_ASSOC);
            $result = $DB->Execute($query) or die ("query failed");
            $myresult = $result->fields;
            $databaseversion = $myresult['version'];
            if ($databaseversion == "") {
                $databaseversion = "0.9.2 or older";
            }
        }
        echo "<center>	
            <p>
            Your database version: <b>$databaseversion</b><p>

            This script will update it to version: <b>3.0 Development</b></h3>";

        if ($databaseversion == "3.0-BETA1") {
            echo "<p><b>Nothing to update</b>";
        } else {
            echo "
                <form action=update.php>
                <input type=hidden name=databaseversion 
                value=\"$databaseversion\">
                <input name=submit type=submit value=\"Update\">
                </form>"; 
        }

        if (!isset($base->input['submit'])) { $base->input['submit'] = ""; }
            if ($base->input['submit'] == "Update")
            {
                $databaseversion = $base->input['databaseversion'];
                if ($databaseversion == "0.9.2 or older")
                {
                    echo "Updating to 0.9.3<br>\n";

                    $query = "ALTER TABLE `billing` 
                        CHANGE `state` `state` char( 3 ) NOT NULL";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $query = "ALTER TABLE `customer` 
                        CHANGE `state` `state` char( 3 ) NOT NULL";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $query = "ALTER TABLE `general` 
                        ADD `version` VARCHAR( 12 ) DEFAULT '0.9.3' 
                        NOT NULL AFTER `id`";	
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $databaseversion = "0.9.3";
                }
                if ($databaseversion == "0.9.3")
                {
                    echo "Updating to 0.9.4<br>\n";
                    $query = "ALTER TABLE `general` CHANGE `version` `version` VARCHAR( 12 ) NOT NULL";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";		

                    $query = "UPDATE `general` SET `version` = '0.9.4' WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";	

                    $databaseversion = "0.9.4";
                }
                if ($databaseversion == "0.9.4")
                {
                    echo "Updating to 0.9.5<br>\n";

                    $query = "UPDATE `general` SET `version` = '0.9.5' WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";	

                    $databaseversion = "0.9.5";

                }
                if ($databaseversion == "0.9.5")
                {
                    echo "Updating to 1.0 RC1<br>\n";
                    $query = "ALTER TABLE `payment_history` 
                        ADD `check_number` VARCHAR( 32 ) NULL";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";	

                    $query = "ALTER TABLE `payment_history` 
                        ADD `avs_response` VARCHAR( 32 ) NULL";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $query = "UPDATE `general` SET `version` = '1.0 RC1' 
                        WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";	

                    $databaseversion = "1.0 RC1";	
                }
                if ($databaseversion == "1.0 RC1")
                {
                    $query = "UPDATE `general` SET `version` = '1.0 RC2' 
                        WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";	

                    $databaseversion = "1.0 RC2";
                }
                if ($databaseversion == "1.0 RC2")
                {
                    $query = "ALTER TABLE `master_services` DROP `postbilled`";
                    $result = $DB->Execute($query);
                    echo "$query<br>\n";	

                    $query = "UPDATE `general` SET `version` = '1.0' 
                        WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";	

                    $databaseversion = "1.0";
                }
                if ($databaseversion == "1.0")
                {
                    // create a tax_exempt table
                    $query = "CREATE TABLE `tax_exempt` (
                        `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                        `account_number` INT( 11 ) NOT NULL ,
                        `tax_rate_id` INT( 11 ) NOT NULL ,
                        `customer_tax_id` VARCHAR( 64 ) NULL ,
                        `expdate` DATE NULL
                    ) TYPE = MyISAM";
                    $result = $DB->Execute($query);
                    echo "$query<br>\n";

                    // remove the tax_exempt_id field from customer
                    $query = "ALTER TABLE `customer` DROP `tax_exempt_id`";
                    $result = $DB->Execute($query);
                    echo "$query<br>\n";

                    $query = "UPDATE `general` SET `version` = '1.0.1' 
                        WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";	

                    $databaseversion = "1.0.1";
                }
                if ($databaseversion == "1.0.1")
                {
                    //change search 8 for support ticket number
                    $query = "UPDATE `searches` 
                        SET `query` = 'SELECT * FROM customer_history WHERE id = %s1%' 
                        WHERE `id` =8 LIMIT 1";
                    $result = $DB->Execute($query);
                    echo "$query<br>\n";

                    // remove the sortorder field from the billing_types table
                    $query = "ALTER TABLE `billing_types` DROP `sortorder`";
                    $result = $DB->Execute($query);
                    echo "$query<br>\n";

                    $query = "UPDATE `general` SET `version` = '1.0.2' 
                        WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";	

                    $databaseversion = "1.0.2";
                }
                if ($databaseversion == "1.0.2")
                {
                    $query = "UPDATE `general` SET `version` = '1.0.3' 
                        WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";	

                    $databaseversion = "1.0.3";
                }
                if ($databaseversion == "1.0.3")
                {
                    $query = "UPDATE `general` SET `version` = '1.0.4' 
                        WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $query = "ALTER TABLE `general` 
                        ADD `default_invoicenote` VARCHAR( 255 ) NULL ,
                            ADD `pastdue_invoicenote` VARCHAR( 255 ) NULL ,
                            ADD `turnedoff_invoicenote` VARCHAR( 255 ) NULL ,
                            ADD `collections_invoicenote` VARCHAR( 255 ) NULL"; 
                    $result = $DB->Execute($query) or die ("FAILED: $query");
                    echo "$query<br>\n";

                    $databaseversion = "1.0.4";
                }
                if ($databaseversion == "1.0.4")
                {
                    $query = "UPDATE `general` SET `version` = '1.1' 
                        WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // update for 1.1 with adodb sessions support
                    $query = "CREATE TABLE sessions2(
                        sesskey VARCHAR( 64 ) NOT NULL DEFAULT '',
                        expiry TIMESTAMP NOT NULL ,
                        expireref VARCHAR( 250 ) DEFAULT '',
                        created TIMESTAMP NOT NULL ,
                        modified TIMESTAMP NOT NULL ,
                        sessdata LONGTEXT DEFAULT '',
                        PRIMARY KEY ( sesskey ) ,
                        INDEX sess2_expiry( expiry ),
                    INDEX sess2_expireref( expireref )
                )";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // update for 1.1 with payment_mode table
                    $query = "CREATE TABLE `payment_mode` (
                        `id` int(11) NOT NULL auto_increment,
                        `name` varchar(32),
            PRIMARY KEY (`id`)
        ) TYPE=MyISAM AUTO_INCREMENT=1 ";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $query = "INSERT INTO `payment_mode` 
                        VALUES (1, 'check'), (2, 'eft'), (3, 'cash')";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $databaseversion = "1.1";
                }
                if ($databaseversion == "1.1")
                {
                    $query = "UPDATE `general` SET `version` = '1.1.1' 
                        WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $databaseversion = "1.1.1";
                }
                if ($databaseversion == "1.1.1")
                {
                    $query = "UPDATE `general` SET `version` = '1.1.2' 
                        WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $databaseversion = "1.1.2";
                }
                if ($databaseversion == "1.1.2")
                {
                    // add a po_number field to billing
                    $query = "ALTER TABLE `billing` 
                        ADD `po_number` VARCHAR( 64 ) NULL";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // update the version number
                    $query = "UPDATE `general` SET `version` = '1.1.3' 
                        WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $databaseversion = "1.1.3";
                }
                if ($databaseversion == "1.1.3")
                {
                    $query = "UPDATE `general` SET `version` = '1.1.4' 
                        WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $databaseversion = "1.1.4";
                }

                if ($databaseversion == "1.1.4")
                {
                    // update the general table with billingweekend fields
                    $query = "ALTER TABLE `general` 
                        ADD `billingweekend_sunday` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'y',
                            ADD `billingweekend_monday` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n',
                            ADD `billingweekend_tuesday` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n',
                            ADD `billingweekend_wednesday` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n',
                            ADD `billingweekend_thursday` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n',
                            ADD `billingweekend_friday` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n',
                            ADD `billingweekend_saturday` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'y'";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // update the version number
                    $query = "UPDATE `general` SET `version` = '1.1.5' 
                        WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $databaseversion = "1.1.5";
                }

                if ($databaseversion == "1.1.5") {

                    // add the refund fields to billing details
                    $query = "ALTER TABLE `billing_details` 
                        ADD `refund_amount` FLOAT NOT NULL DEFAULT '0',
                        ADD `refunded` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n';";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // update the version number
                    $query = "UPDATE `general` SET `version` = '1.1.6' 
                        WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $databaseversion = "1.1.6";
                }

                if ($databaseversion == "1.1.6") {
                    // add refund_date field
                    $query = "ALTER TABLE `billing_details` 
                        ADD `refund_date` DATE NULL ;";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";


                    // add a credit status to the payment_history
                    $query = "ALTER TABLE `payment_history` CHANGE `status` `status` SET( 'authorized', 'declined', 'pending', 'donotreactivate', 'collections', 'turnedoff', 'credit' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // update the version number
                    $query = "UPDATE `general` SET `version` = '1.2' 
                        WHERE `id` =1 LIMIT 1";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $databaseversion = "1.2";
                }

                if ($databaseversion == "1.2")
                {
                    $query = "UPDATE `general` SET `version` = '1.2.1' 
                        WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $databaseversion = "1.2.1";
                }
                if ($databaseversion == "1.2.1")
                {
                    $query = "ALTER TABLE `payment_history` CHANGE `transaction_code` `transaction_code` VARCHAR( 32 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $query = "ALTER TABLE `general` ADD `declined_subject` VARCHAR( 64 ) NULL , ADD `declined_message` TEXT NULL";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $query = "UPDATE `general` SET `version` = '1.2.2' 
                        WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $databaseversion = "1.2.2";
                }

                if ($databaseversion == "1.2.2") {
                    // add new settings table to hold citrus specific settings
                    $query = "CREATE TABLE `settings` (".
                        "`id` int(11) NOT NULL auto_increment, ".
                        "`version` varchar(12) NOT NULL, ".
                        "`default_group` varchar(30) default NULL, ".
                        "`path_to_ccfile` varchar(255) default NULL, ".
                        "`billingdate_rollover_time` time default NULL, ".  
                        "`billingweekend_sunday` enum('y','n') NOT NULL default 'y', ".
                        "`billingweekend_monday` enum('y','n') NOT NULL default 'n', ".
                        "`billingweekend_tuesday` enum('y','n') NOT NULL default 'n', ".
                        "`billingweekend_wednesday` enum('y','n') NOT NULL default 'n', ".
                        "`billingweekend_thursday` enum('y','n') NOT NULL default 'n', ".
                        "`billingweekend_friday` enum('y','n') NOT NULL default 'n', ".
                        "`billingweekend_saturday` enum('y','n') NOT NULL default 'y', ".
                        "PRIMARY KEY  (`id`) ) TYPE=MyISAM";
                    $result = $DB->Execute($query) or die ("$query query failed");
                    echo "$query<br>\n";

                    //
                    // GRAB THE VALUES FROM GENERAL FIRST BEFORE INSERTING THEM 
                    // INTO THE NEW
                    // SETTINGS TABLE AND REMOVING THE GENERAL FIELDS
                    //
                    $query = "SELECT * FROM general WHERE id = 1";
                    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
                    $result = $DB->Execute($query) or die ("query failed");
                    $myresult = $result->fields;
                    $default_group = $myresult['default_group'];
                    $path_to_ccfile = $myresult['path_to_ccfile'];
                    $billingdate_rollover_time = $myresult['billingdate_rollover_time'];
                    $billingweekend_sunday = $myresult['billingweekend_sunday'];
                    $billingweekend_monday = $myresult['billingweekend_monday'];
                    $billingweekend_tuesday = $myresult['billingweekend_tuesday'];
                    $billingweekend_wednesday = $myresult['billingweekend_wednesday'];
                    $billingweekend_thursday = $myresult['billingweekend_thursday'];
                    $billingweekend_friday = $myresult['billingweekend_friday'];	
                    $billingweekend_saturday = $myresult['billingweekend_saturday'];

                    // insert the values from general into the new settings table
                    $query="INSERT INTO `settings` VALUES (1, '1.2.3', 'default_group', 
                        '$path_to_ccfile','$billingdate_rollover_time',
                        '$billingweekend_sunday',
                        '$billingweekend_monday',
                        '$billingweekend_tuesday',
                        '$billingweekend_wednesday',
                        '$billingweekend_thursday',
                        '$billingweekend_friday',
                        '$billingweekend_saturday')";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // remove the old fields from general
                    $query = "ALTER TABLE `general` DROP `version` ,
                        DROP `default_group` ,
                        DROP `path_to_ccfile`, 
                        DROP `billingdate_rollover_time`,
                        DROP `billingweekend_sunday`,
                        DROP `billingweekend_monday`,
                        DROP `billingweekend_tuesday`,
                        DROP `billingweekend_wednesday`,
                        DROP `billingweekend_thursday`,
                        DROP `billingweekend_friday`,
                        DROP `billingweekend_saturday`";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // add a new footer field to general for invoices
                    $query = "ALTER TABLE `general` ADD `invoice_footer` TEXT NULL";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";	

                    // add the organization id to the billing table
                    $query = "ALTER TABLE `billing` 
                        ADD `organization_id` INT NOT NULL DEFAULT '1'";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // add the organization id to the master services
                    $query = "ALTER TABLE `master_services` 
                        ADD `organization_id` INT NOT NULL DEFAULT '1';";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $query = "ALTER TABLE `billing` CHANGE `pastdue_exempt` `pastdue_exempt` ENUM( 'y', 'n', 'bad_debt' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'n'";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // Add a cancel reason table to hold cancel reasons to choose from
                    $query = " CREATE TABLE `cancel_reason` ".
                        "(`id` INT NOT NULL AUTO_INCREMENT ,".
                        "`reason` VARCHAR( 128 ) NOT NULL , ".
                        "PRIMARY KEY ( `id` )) TYPE = MYISAM ";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // Add cancel reasons to the new table
                    $query = "INSERT INTO `cancel_reason` (`id`, `reason`) VALUES " .
                        "(1, 'Closing Business'), " .
                        "(2, 'Computer Broken/Unavailable'), " .
                        "(3, 'Connection Problems'), " .
                        "(4, 'Does not use service'), " .
                        "(5, 'Does not want service'), " .
                        "(6, 'Duplicate Account'), " .
                        "(7, 'Fraud'), ". 
                        "(8, 'Moving'), ". 
                        "(9, 'Non-Payment'), " .
                        "(10, 'Outside Coverage Area'), " .
                        "(11, 'Switched to other service provider'), " .
                        "(12, 'Transient Account'), " .
                        "(13, 'Unserviceable Address'), " .
                        "(14, 'Vacation');";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";	  

                    // Add a cancel reason ID number to the customer table to connect it
                    $query = "ALTER TABLE `customer` ADD `cancel_reason` INT NULL";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $databaseversion = "1.2.3";
                }

                if ($databaseversion == "1.2.3") {

                    // add the modify_notify field to master_services
                    $query = "ALTER TABLE `master_services` ADD `modify_notify` ".
                        "VARCHAR( 32 ) NULL AFTER `activate_notify` ;";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // add the canceled and cancelwfee and pastdue and notice status to payment_history status
                    $query = " ALTER TABLE `payment_history` CHANGE `status` `status` ".
                        "SET( 'authorized', 'declined', 'pending', 'donotreactivate', ".
                        "'collections', 'turnedoff', 'credit', 'canceled', 'cancelwfee',".
                        "'pastdue', 'noticesent','waiting') ".
                        "CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // add a login failures table to track failure and eventually
                    // restrict them from trying at all
                    $query = "CREATE TABLE `login_failures` (".
                        "`ip` VARCHAR( 64 ) NOT NULL ,".
                        "`logintime` DATETIME NOT NULL ) TYPE = MYISAM ";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // add carrier_dependent field to indicate they get canceled with fee
                    // if they owe money and are on a different past due days track
                    $query = "ALTER TABLE `master_services` ADD `carrier_dependent` ".
                        "ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n';";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // rename the pastdue_days fields to regular_ fields
                    // as opposed to the new dependent type fields
                    $query = "ALTER TABLE `general` ".
                        "CHANGE `pastdue_days1` `regular_pastdue` INT( 11 ) ".
                        "NOT NULL DEFAULT '0',".
                        "CHANGE `pastdue_days2` `regular_turnoff` INT( 11 ) ".
                        "NOT NULL DEFAULT '0',".
                        "CHANGE `pastdue_days3` `regular_canceled` INT( 11 ) ".
                        "NOT NULL DEFAULT '0' ";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";


                    // add the pastdue counters for carrier_dependent service types
                    $query = "ALTER TABLE `general` ADD ".
                        "`dependent_pastdue` INT NOT NULL DEFAULT '0',".
                        "ADD `dependent_shutoff_notice` INT NOT NULL DEFAULT '0',".
                        "ADD `dependent_turnoff` INT NOT NULL DEFAULT '0',".
                        "ADD `dependent_canceled` INT NOT NULL DEFAULT '0';";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // add linkurl and linkname to the customer_history for file linking and other links
                    $query = "ALTER TABLE `customer_history` ".
                        "ADD `linkurl` VARCHAR( 255 ) NULL , ".
                        "ADD `linkname` VARCHAR( 64 ) NULL ";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $query = "ALTER TABLE `settings` ADD `dependent_cancel_url` VARCHAR( 255 ) NULL ;";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";	  

                    $query = "ALTER TABLE `settings` ADD `default_billing_group` VARCHAR( 32 ) NOT NULL DEFAULT 'billing'";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";	  

                    // add nsf type for non-sufficient fund indicator
                    $query = " ALTER TABLE `payment_history` ".
                        "CHANGE `payment_type` `payment_type` ".
                        "SET( 'creditcard', 'check', 'cash', 'eft', 'nsf' ) ".
                        "CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL  ";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";	  

                    // set the version, using the new settings field
                    $query = "UPDATE `settings` SET `version` = '1.3' WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $databaseversion = "1.3";

                }

                if ($databaseversion == "1.3") {

                    // add percentage_or_fixed multiply indicator field to tax_rates
                    $query = "ALTER TABLE `tax_rates` ADD `percentage_or_fixed` ".
                        "ENUM( 'percentage', 'fixed' ) NOT NULL DEFAULT 'percentage';";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // add user_services_id to the customer_history table
                    $query = "ALTER TABLE `customer_history` ADD `user_services_id` ".
                        "INT NULL ;";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // add rerun flag to billing details
                    $query = "ALTER TABLE `billing_details` ".
                        "ADD `rerun` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n';";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // add a rerun_date field to the billing_details
                    $query = "ALTER TABLE `billing_details` ADD `rerun_date` DATE NULL;";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // add payment_applied date to billing details
                    $query = "ALTER TABLE `billing_details` ".
                        "ADD `payment_applied` DATE NULL ;";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";	  

                    // set current rerun dates to null, NULL is now important
                    // if you have upcoming reruns, must reset them after
                    $query = "UPDATE billing SET rerun_date = NULL";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // add an original_invoice_number field to keep that number around
                    // even when making reruns on new invoices
                    $query =" ALTER TABLE `billing_details` ".
                        "ADD `original_invoice_number` INT NULL";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";	  

                    // set the version, using the new settings field
                    $query = "UPDATE `settings` SET `version` = '1.3.1' ".
                        "WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";
                }


                if ($databaseversion == "1.3.1") {

                    $query = "ALTER TABLE `user_services` ".
                        "ADD INDEX `master_service_id_index` ( `master_service_id` )";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $query = "ALTER TABLE `user_services` ".
                        "ADD INDEX `billing_id_index` ( `billing_id` )";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $query = "ALTER TABLE `billing` ".
                        "ADD INDEX `billing_type_index` ( `billing_type` )";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $query = "ALTER TABLE `tax_exempt` ".
                        "ADD INDEX `account_number_index` ( `account_number` )";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $query = "ALTER TABLE `billing_details` ".
                        "ADD INDEX `billing_id_index` ( `billing_id` )";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $query ="ALTER TABLE `payment_history` ".
                        "ADD INDEX `billing_id_index` ( `billing_id` )";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // set the version, using the new settings field
                    $query = "UPDATE `settings` SET `version` = '1.3.2' ".
                        "WHERE `id` =1 LIMIT 1";		
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // add notes field to customer table
                    $query = "ALTER TABLE `customer` ADD `notes` TEXT NULL";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // add support_notify field to master_services table
                    $query = "ALTER TABLE `master_services` ADD `support_notify` VARCHAR( 32 ) NULL ;";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // change most of the float fields to decimal type
                    // to fix large number precision
                    $query = "ALTER TABLE `user_services` ".
                        "CHANGE `usage_multiple` `usage_multiple` ".
                        "DECIMAL( 9, 2 ) NOT NULL DEFAULT '1'";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $query = "ALTER TABLE `payment_history` ".
                        "CHANGE `billing_amount` `billing_amount` ".
                        "DECIMAL( 9, 2 ) NULL DEFAULT NULL";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $query = "ALTER TABLE `billing_history` ".
                        "CHANGE `new_charges` `new_charges` ".
                        "DECIMAL( 9, 2 ) NOT NULL DEFAULT '0', ".
                        "CHANGE `past_due` `past_due` ".
                        "DECIMAL( 9, 2 ) NULL DEFAULT '0', ".
                        "CHANGE `late_fee` `late_fee` ".
                        "DECIMAL( 9, 2 ) NOT NULL DEFAULT '0', ".
                        "CHANGE `tax_due` `tax_due` ".
                        "DECIMAL( 9, 2 ) NOT NULL DEFAULT '0', ".
                        "CHANGE `total_due` `total_due` ".
                        "DECIMAL( 9, 2 ) NOT NULL DEFAULT '0'";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $query="ALTER TABLE `billing_details` ".
                        "CHANGE `billed_amount` `billed_amount` ".
                        "DECIMAL( 9, 2 ) NOT NULL DEFAULT '0', ".
                        "CHANGE `paid_amount` `paid_amount` ".
                        "DECIMAL( 9, 2 ) NOT NULL DEFAULT '0', ".
                        "CHANGE `refund_amount` `refund_amount` ".
                        "DECIMAL( 9, 2 ) NOT NULL DEFAULT '0'";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $query = " ALTER TABLE `general` CHANGE ".
                        "`email_sales` `email_sales` VARCHAR( 128 ) ".
                        "CHARACTER SET latin1 COLLATE latin1_swedish_ci ".
                        "NULL DEFAULT NULL , ".
                        "CHANGE `email_billing` `email_billing` VARCHAR( 128 ) ".
                        "CHARACTER SET latin1 COLLATE latin1_swedish_ci ".
                        "NULL DEFAULT NULL , ".
                        "CHANGE `email_custsvc` `email_custsvc` VARCHAR( 128 ) ".
                        "CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    $query = "INSERT INTO `payment_mode` VALUES (NULL, 'discount')";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";	  

                    $query = " ALTER TABLE `payment_history` CHANGE `payment_type` `payment_type` SET( 'creditcard', 'check', 'cash', 'eft', 'nsf', 'discount' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL  ";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                }

                if ($databaseversion == "1.3.2") {

                    // change the normal card number to varchar so it can hold ***
                    // for the truncated card numbers we show to regular users
                    $query = " ALTER TABLE `billing` CHANGE `creditcard_number` ".
                        "`creditcard_number` VARCHAR( 16 ) NULL DEFAULT NULL  ";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    // add the TEXT field that will hold the ascii armored encrypted card number
                    $query = "ALTER TABLE `billing` ADD `encrypted_creditcard_number` TEXT NULL";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    // add the export prefix field that holds a prefix for the organization being exported
                    $query = "ALTER TABLE `general` ADD `exportprefix` VARCHAR( 64 ) NULL ;";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    // add payment_history_id to link individual payments to items
                    $query = "ALTER TABLE `billing_details` ADD `payment_history_id` ".
                        "INT NULL ;";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    // create the new activity_log table
                    $query = "CREATE TABLE IF NOT EXISTS `activity_log` (".
                        "`datetime` datetime NOT NULL,".
                        "`user` varchar(128) NOT NULL,".
                        "`ip_address` varchar(64) NOT NULL,".
                        "`account_number` int(11) default NULL,".
                        "`activity_type` enum('login','logout','view','edit','create',".
                        "'delete','undelete','export','import','cancel','uncancel') ".
                        "NOT NULL,".
                        "`record_type` enum('dashboard','customer','billing','service',".
                        "'creditcard') NOT NULL,".
                        "`record_id` int(11) default NULL,".
                        "`result` enum('success','failure') NOT NULL".
                        ") ENGINE=MyISAM DEFAULT CHARSET=latin1;";	  

                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";	  	  

                    // add automatic_receipt marker to know who wants automatic receipts
                    $query = "ALTER TABLE `billing` ADD `automatic_receipt` ".
                        "ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n';";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    // add closed_by and closed_date fields to customer_history table
                    $query = "ALTER TABLE `customer_history` ADD `closed_by` ".
                        "VARCHAR( 64 ) NULL ,ADD `closed_date` DATETIME NULL ;";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    // sub_history table to hold new entries associated with the same customer history entry
                    $query = "CREATE TABLE IF NOT EXISTS `sub_history` (".
                        "`id` int(10) unsigned NOT NULL auto_increment,".
                        "`creation_date` datetime NOT NULL default '0000-00-00 00:00:00',".
                        "`created_by` varchar(20) NOT NULL default 'citrus',".
                        "`customer_history_id` int(11) NOT NULL default '0',".
                        "`description` text NOT NULL,".
                        "PRIMARY KEY  (`id`)".
                        ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
                    $result = $DB->Execute($query) or die ("$query query failed");
                    echo "$query<br>\n";

                    // set the version number in the database to 2.0
                    $query = "UPDATE `settings` SET `version` = '2.0' ".
                        "WHERE `id` =1 LIMIT 1";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";


                }

                if ($databaseversion == "2.0") {
                    // set the version number in the database to 2.0
                    $query = "UPDATE `settings` SET `version` = '2.0.1' ".
                        "WHERE `id` =1 LIMIT 1";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";
                }

                if ($databaseversion == "2.0.1") {
                    // add screenname field for xmpp to user table
                    $query = "ALTER TABLE `user` ADD `screenname` VARCHAR( 254 ) NULL";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    // add notify variables too
                    $query = "ALTER TABLE `user` ADD `email_notify` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n', ADD `screenname_notify` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n';";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    // add credit_applied field to billing_history	  
                    $query = "ALTER TABLE `billing_history` ADD `credit_applied` DECIMAL( 9, 2 ) NOT NULL DEFAULT '0.00'";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    // set the version number in the database to 2.0.2
                    $query = "UPDATE `settings` SET `version` = '2.0.2' ".
                        "WHERE `id` =1 LIMIT 1";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";
                }

                if ($databaseversion == "2.0.2") {
                    // add master_field_assets table
                    $query = "CREATE TABLE `master_field_assets` ( ".
                        "`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY , ".
                        "`description` VARCHAR( 128 ) NOT NULL , ".
                        "`status` ENUM( 'current', 'old' ) NOT NULL DEFAULT 'current', ".
                        "`weight` FLOAT NULL, ".
                        "`category` VARCHAR( 128) NOT NULL ".
                        ") ENGINE = MYISAM  ";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    // add field_asset_items table
                    $query = "CREATE TABLE `field_asset_items` ( ".
                        "`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY , ".
                        "`master_field_assets_id` INT NOT NULL , ".
                        "`creation_date` DATE NOT NULL , ".
                        "`serial_number` VARCHAR( 254 ) NULL , ".
                        "`status` ENUM( 'infield', 'returned' ) NOT NULL , ".
                        "`sale_type` ENUM( 'included','rent', 'purchase' ) NOT NULL , ".
                        "`user_services_id` INT NULL , ".
                        "`shipping_tracking_number` VARCHAR( 254 ) NULL , ".
                        "`shipping_date` DATE NULL , ".
                        "`return_date` DATE NULL , ".
                        "`return_notes` VARCHAR( 254 ) NULL ".
                        ") ENGINE = MYISAM ";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    // add default shipping group field
                    $query = "ALTER TABLE `settings` ADD `default_shipping_group` VARCHAR( 32 ) NOT NULL DEFAULT 'shipping';";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    // set the version number in the database to 2.1
                    $query = "UPDATE `settings` SET `version` = '2.1' ".
                        "WHERE `id` =1 LIMIT 1";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";	  

                }

                if ($databaseversion == "2.1") {
                    $query = "CREATE TABLE `vendor_names` (".
                        "`name` VARCHAR( 64 ) NOT NULL ".
                        ") ENGINE = MYISAM ";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    $query = "CREATE TABLE `vendor_history` ( ".
                        "`id` INT NOT NULL AUTO_INCREMENT ,".
                        "`datetime` DATETIME NOT NULL ,".
                        "`entry_type` ENUM( 'order','change','recurring bill','onetime bill','disconnect' ) NOT NULL ,".
                        "`entry_date` DATE NOT NULL ,".
                        "`vendor_name` VARCHAR( 64 ) NOT NULL ,".
                        "`vendor_bill_id` VARCHAR( 128 ) NULL ,".
                        "`vendor_cost` DECIMAL( 9, 2 ) NULL ,".
                        "`vendor_tax` DECIMAL( 9, 2 ) NULL ,".
                        "`vendor_item_id` VARCHAR( 128 ) NULL ,".
                        "`vendor_invoice_number` VARCHAR( 64 ) NULL ,".
                        "`vendor_from_date` VARCHAR( 32 ) NULL ,".
                        "`vendor_to_date` VARCHAR( 32 ) NULL ,".
                        "`user_services_id` INT NOT NULL ,".
                        "`account_status` VARCHAR( 64 ) NULL ,".
                        "`billed_amount` DECIMAL( 9, 2 ) NULL ,".
                        "PRIMARY KEY ( `id` )".
                        ") ENGINE = MYISAM ";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    // index the notify column of customer_history for speed up
                    $query = " ALTER TABLE `customer_history` ADD INDEX ( `notify` )  ";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    // allow the next_billing_date to be set NULL
                    $query = " ALTER TABLE `billing` CHANGE `next_billing_date` `next_billing_date` DATE NULL  ";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    // set the version number in the database to 2.1.1
                    $query = "UPDATE `settings` SET `version` = '2.1.1' ".
                        "WHERE `id` =1 LIMIT 1";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                }

                if ($databaseversion == "2.1.1") {
                    // set the version number in the database to 2.2
                    $query = "UPDATE `settings` SET `version` = '2.2' ".
                        "WHERE `id` =1 LIMIT 1";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";	  
                }

                if ($databaseversion == "2.2") {
                    // add the recent_invoice_number to billing details for new credit card rerun method
                    // that keeps the regular invoice number the same and just makes a new invoice
                    // with a pastdue amount, keeping items on old invoice itself
                    $query = "ALTER TABLE  `billing_details` ADD  `recent_invoice_number` INT NULL DEFAULT NULL";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    // add indexes
                    $query = "ALTER TABLE `billing_history` ADD INDEX  `billing_id_index` ( `billing_id` )";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    $query = "ALTER TABLE `billing_details` ADD INDEX  `invoice_number_index` ( `invoice_number` )";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    $query = "ALTER TABLE `customer_history` ADD INDEX  `account_number_index` (  `account_number` )";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    $query = "ALTER TABLE `billing` ADD INDEX  `account_number_index` (  `account_number` )";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    // set the version number in the database to 2.3
                    $query = "UPDATE `settings` SET `version` = '2.3' ".
                        "WHERE `id` =1 LIMIT 1";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";	  
                }

                if ($databaseversion == "2.3") {
                    // make sure the user table unique
                    $query = "ALTER TABLE  `user` ADD UNIQUE (`username`)";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";	  	  

                    // increase size of password field to hold new bcrypt length passwords
                    $query = "ALTER TABLE  `user` CHANGE  `password`  `password` VARCHAR( 60 ) NOT NULL DEFAULT  ''";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    // increase size of account_manager_password field to hold bcrypt length passwords
                    $query = "ALTER TABLE  `customer` CHANGE  `account_manager_password`  ".
                        "`account_manager_password` VARCHAR( 60 ) NULL DEFAULT NULL";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";

                    // set the version number in the database to 2.4
                    $query = "UPDATE `settings` SET `version` = '2.4' ".
                        "WHERE `id` =1 LIMIT 1";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";	  
                }

                if ($databaseversion == "2.4") {

                    // make new session table for codeigniter
                    $query = "CREATE TABLE IF NOT EXISTS  `ci_sessions` (
                        session_id varchar(40) DEFAULT '0' NOT NULL,
                        ip_address varchar(45) DEFAULT '0' NOT NULL,
                        user_agent varchar(50) NOT NULL,
                        last_activity int(10) unsigned DEFAULT 0 NOT NULL,
                        user_data text DEFAULT '' NOT NULL,
                        PRIMARY KEY (session_id)
                    );";

                    // drop old session table used by adodb
                    $query = "DROP TABLE session2";

                    // add new api_keys table
                    $query = "CREATE TABLE `api_keys` (".
                        "`id` int(11) NOT NULL AUTO_INCREMENT,".
                        "`key` varchar(40) NOT NULL,".
                        "`level` int(2) NOT NULL,".
                        "`ignore_limits` tinyint(1) NOT NULL DEFAULT '0',".
                        "`date_created` int(11) NOT NULL,".
                        "PRIMARY KEY (`id`)".
                        ") ENGINE=MyISAM DEFAULT CHARSET=utf8;";

                    // change ccexportvarorder to TEXT field
                    $query = "ALTER TABLE `general` 
                        CHANGE `ccexportvarorder` `ccexportvarorder` text NOT NULL";
                    $result = $DB->Execute($query) or die ("query failed");
                    echo "$query<br>\n";

                    // set the version number in the database to 3.0
                    $query = "UPDATE `settings` SET `version` = '3.0' ".
                        "WHERE `id` =1 LIMIT 1";
                    $result = $DB->Execute($query) or die ("$query failed");
                    echo "$query<br>\n";	  
                }


                echo "<center><h2>Database Updated</h2></center>";
            }
            else 
            {
                echo "<p style=\"font-weight: bold;\">Upgrading version 1.3.0 or ".
                    "older will reset the rerun dates ".
                    "to NULL when running this upgrade script.  Please make sure you ".
                    "check for pending reruns before running this script on an active ".
                    "system.</p>";


            }
    }

}

/* end file command.php */
?>
