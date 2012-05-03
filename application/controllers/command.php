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

	// TODO: make functions in an update_model from statusupdate above and use here too

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

}

/* end file command.php */
?>
