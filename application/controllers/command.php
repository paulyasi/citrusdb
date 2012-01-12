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
		$this->load->model('service_model');
		$this->load->model('settings_model');
		
		// load the encryption helper for use when calling gpg things
		$this->load->helper('encryption');

		$creditcard_list = $this->billing_model->list_creditcards();

		// walk forwards one at a time with next_row
		while ($myresult = $creditcard_list->next_row('array'))
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
	
	
}

/* end file command.php */
?>