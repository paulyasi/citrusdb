<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Billing extends App_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('customer_model');
		$this->load->model('module_model');
		$this->load->model('billing_model');
		$this->load->model('user_model');
	}		


	function printpreviousinvoice($billingid, $invoiceid)
	{
		// load the service model to query for detail items in outputinvoice
		$this->load->model('service_model');

		//require('./include/fpdf.php');
		$this->load->library('fpdf');    

		$pdf = new FPDF();

		// print the invoice
		$pdf = $this->billing_model->outputinvoice($invoiceid, "pdf", $pdf);

		$pdf->Output();

		echo "printing pdf";
	}


	function htmlpreviousinvoice($billingid, $invoiceid)
	{
		// load the service model to query for detail items in outputinvoice
		$this->load->model('service_model');

		// get the data for the html invoice
		$html = $this->billing_model->outputinvoice($invoiceid, "html", NULL);

		echo "<pre>$html</pre>";
	}


	function extendedpreviousinvoice($billingid, $invoiceid)
	{
		// load the service model to query for detail items in outputinvoice
		$this->load->model('service_model');

		// get the data for the html invoice
		$html = $this->billing_model->outputextendedinvoice($invoiceid, "html", NULL);

		echo "<pre>$html</pre>";
	}

	function emailpreviousinvoice($billingid, $invoiceid)
	{
		// load the service model to query for detail items in outputinvoice
		$this->load->model('service_model');

		$contact_email = $this->billing_model->get_billing_email($invoiceid); 

		$this->billing_model->emailinvoice ($invoiceid,$contact_email,$billingid);
		echo "sent invoice to $contact_email<br>\n";
	}


	function payment($invoice_number = NULL, $amount = NULL)
	{
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		// Read the list of payment modes to give the operator for paying the bill
		$payment_modes = $this->billing_model->get_payment_modes();
		$payment_options = "";

		foreach ($payment_modes AS $mypayresult)
		{
			$payment_options = $payment_options . "<option>". $mypayresult['name'] . 
				"</option>\n";
		}

		$data['payment_options'] = $payment_options;
		$data['invoice_number'] = $invoice_number;
		$data['amount'] = $amount;

		$this->load->view('tools/billing/payment_view', $data);

	}


	function savepayment()
	{
		$account_num = $this->input->post('account_num');
		$billing_id = $this->input->post('billing_id');
		$amount = $this->input->post('amount');
		$payment_type = $this->input->post('payment_type');
		$invoice_number = $this->input->post('invoice_number');
		if ($invoice_number == '') { $invoice_number = 0; }
		$check_number = $this->input->post('check_number');

		// set the payment to the amount entered
		$payment = $amount;

		$p = $this->billing_model->enter_invoice_payment($account_num, $billing_id, $amount, 
				$payment_type, $invoice_number, $check_number);

		if ($p['amount'] > 0) 
		{
			// if there is an over payment show the amount in red
			//and prompt to add as a credit		
			print "<h3 style=\"color: red;\">".lang('paymentsaved').", ".lang('currency').$p['amount'] .
				lang('leftover').", <a href=\"index.php/tools/billing/addcredit/".$p['amount']."/$billing_id\">".
				lang('addcreditfor')." ".$p['amount']."</a></h3>";
		} 
		else 
		{
			print "<h3>".lang('paymentsaved').": </h3>";
		}

		// print the customer billing information to confirm who the payment
		// was entered for.

		echo "<blockquote>".$p['billing_account_number']."<br>".$p['billing_name']."<br>".
			$p['billing_company']."<br>".$p['billing_street']."<br>".$p['billing_city']." ".
			$p['billing_state']."</blockquote><p>";

		// show the payment form if the user wants to enter more payments		
		// Read the list of payment modes to give the operator for paying the bill
		$payment_modes = $this->billing_model->get_payment_modes();
		$payment_options = "";

		foreach ($payment_modes AS $mypayresult)
		{
			$payment_options = $payment_options . "<option>". $mypayresult['name'] . "</option>\n";
		}

		$data['payment_options'] = $payment_options;
		$data['invoice_number'] = $invoice_number;
		$data['amount'] = 0;

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/billing/payment_view', $data);
	}


	/*
	 * ------------------------------------------------------------------------
	 *  show the screen to used to choose the file to import
	 * ------------------------------------------------------------------------
	 */
	function importnew()
	{
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/billing/importnew_view', array('error' => ''));
	}

	/*
	 * ------------------------------------------------------------------------
	 *  upload and process the file chosen during import
	 * ------------------------------------------------------------------------
	 */
	function uploadnew()
	{
		// load the service model to be able to input new services
		$this->load->model('service_model');
		$this->load->model('schema_model');
		$this->load->model('support_model');
		$this->load->model('settings_model');

		// POST Variables
		$userfile = $_FILES['userfile']['tmp_name'];

		// get the path where to store the cc data
		$path_to_ccfile = $this->settings_model->get_path_to_ccfile();

		// upload the file
		$config['upload_path'] = $path_to_ccfile;
		$config['allowed_types'] = 'csv|txt|tab|zip';
		$config['file_name'] = 'newaccounts.txt';
		$config['max_size'] = 0;

		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload())
		{
			$error = array('error' => $this->upload->display_errors());

			$this->load->view('tools/billing/importnew_view', $error);
		}
		else
		{
			$data = array('upload_data' => $this->upload->data());

			echo lang('uploadcomplete')." ".$userfile;

			// continue processing the uploaded file

			$myfile = "$path_to_ccfile/newaccounts.txt";

			// OPEN THE FILE AND PROCESS IT

			$fp = @fopen($myfile, "r") or die ("cannot open $myfile");

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
			while ($line = @fgetcsv($fp, 4096)) 
			{

				$linecount++;

				if ($linecount == 1) 
				{
					// make the values in the array safe for import

					$i = 0;
					foreach ($line as $key  => $value) 
					{
						//$line[$key] = import_safe_value($value);
						$line[$key] = $value;
						$i++;
					}

					// also check that there are 17 inputs to help check file format
					if ($i != 17) 
					{
						die ("File Format Error: Wrong number of inputs");
					}

					// make the customer record	data array
					$data['source'] = $line[0];
					$data['name'] = $line[1];
					$data['company'] = $line[2];
					$data['street'] = $line[3];
					$data['city'] = $line[4];
					$data['state'] = $line[5];
					$data['country'] = $line[6];
					$data['zip'] = $line[7];
					$data['phone'] = $line[8];
					$data['alt_phone'] = $line[9];
					$data['fax'] = $line[10];
					$data['contact_email'] = $line[11];
					$data['secret_question'] = $line[13];
					$data['secret_answer'] = $line[14];
					$data['account_manager_password'] = $line[15];
					$data['organization_id'] = $line[16];

					// create the new customer record and set the account_number variable
					$newdata = $this->customer_model->create_record($data);
					$account_number = $newdata['account_number'];
					$billingid = $newdata['billingid'];
					$from_date = $newdata['from_date'];

					echo lang('added') .": ". lang('accountnumber') .": ". $account_number."<p>";
				}
				elseif ($linecount == 2) 
				{
					// make the values in the array safe for import
					foreach ($line as $key  => $value) 
					{
						//$line[$key] = import_safe_value($value);
						$line[$key] = $value;
					}

					// update the billing record
					$billing_data = array(
							'name' => $line[0],
							'company' => $line[1],
							'street' => $line[2],
							'city' => $line[3],
							'state' => $line[4],
							'country' => $line[5],
							'zip' => $line[6],
							'phone' => $line[7],
							'fax' => $line[8],
							'contact_email' => $line[9],
							'billing_type' => $line[10],
							'creditcard_number' => $line[11],
							'creditcard_expire' => $line[12]
							);
					$this->billing_model->save_record($billingid, $billing_data);

					// set the to_date
					$billing_type = $line[10];
					$this->billing_model->automatic_to_date($from_date, $billing_type, $billingid);
					echo lang('added')." ". lang('billingid').": ".$billingid."<p>";		
				} 
				else 
				{
					// look for the BEGIN PGP MESSAGE block text after all service items
					if (($line[0] == "-----BEGIN PGP MESSAGE-----") OR ($asciiarmor == TRUE)) 
					{
						// set a boolean to indicate reading of ascii armor
						// and not anything else
						$asciiarmor = TRUE;

						// read in the ASCII ARMORED credit card data ad the end
						// and put it into the billing record
						// don't put a new line character after the last line
						if ($line[0] == "-----END PGP MESSAGE-----") 
						{
							$armordata .= "$line[0]";	  
						} 
						else 
						{
							$armordata .= "$line[0]\n";
						}

						if ($line[0] == "-----END PGP MESSAGE-----") 
						{
							// into the billing table encrypted_creditcard_number field
							$card_data['encrypted_creditcard_number'] = $armordata;
							$this->billing_model->save_record($billingid, $card_data);
							//echo "$armordata";	  

							// reset line count and other markers when done
							echo "RESET LINECOUNT<p>";
							$linecount = 0;
							$armordata = "";
							$asciiarmor = FALSE;
						}
					} 
					else 
					{
						// get the first number for the service id
						// shift everything else up one

						$serviceid = array_shift($line);

						// make fieldvalues string with the rest of the items
						$fieldvalues = ""; // empty it out
						foreach ($line as $mykey => $myvalue) 
						{
							//$myvalue = import_safe_value($myvalue);
							$myvalue = $this->db->escape($myvalue);
							$fieldvalues .= "," . $myvalue;
						}
						$fieldvalues = substr($fieldvalues, 1);

						// get the services options table name
						$serviceinfo = $this->service_model->get_service_info($serviceid);	
						$servicename = $serviceinfo['service_description'];
						$activate_notify = $serviceinfo['activate_notify'];
						$optionstable = $serviceinfo['options_table'];

						// list out the fields in the options table for that service
						$fields = $this->schema_model->columns($this->db->database, $optionstable);

						// put the fields into a fieldnames string
						$fieldnames = "";
						$i = 0;
						foreach($fields->result() as $v) 
						{
							//echo "Name: $v->name ";
							//echo "Type: $v->type <br>";

							$fieldname = $v->COLUMN_NAME;
							$fieldflags = $v->DATA_TYPE;
							$fieldtype = $v->COLUMN_TYPE; // for enum has value: enum('1','2') etc.

							if ($fieldname <> "id" AND $fieldname <> "user_services") 
							{		
								$fieldnames .= "," . $fieldname;
							}
						}
						$fieldnames = substr($fieldnames, 1);

						// create the service
						$user_service_id = $this->service_model->create_service(
								$account_number, 
								$serviceid, 
								$billingid, 
								1, 
								$optionstable,
								$fieldnames, 
								$fieldvalues
								);

						// insert any linked_services into the user_services table
						$linked_services = $this->service_model->linked_services($serviceid);
						foreach($linked_services AS $myresult) 
						{
							$linkto = $myresult['linkto'];
							// insert the linked service now
							$this->service_model->create_service($account_number, 
									$linkto, $billingid, 1, NULL, NULL, NULL);
						}

						// add an entry to the customer history about service
						$this->service_model->service_message('added', $account_number,
								$serviceid, $user_service_id, NULL, NULL);

						echo lang('added')." ".lang('service').": ".$serviceid." ".lang('to').$account_number."<p>";		
					} // end if for "-" line record seperator
				} // end if make service record
			} // end while	       

			// close the file
			@fclose($fp) or die ("cannot close $myfile");

			// delete the file when we are done
			unlink($myfile);

			// log the importing of accounts
			$this->log_model->activity($this->user,$this->account_number,'import','customer',0,'success');  
		}
	}


	/*
	 * ------------------------------------------------------------------------
	 *  Show the form to select credit card data for export
	 * ------------------------------------------------------------------------
	 */
	function exportcc()
	{
		// load the general model so we can get a list of organizations
		$this->load->model('general_model');

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$data['orglist'] = $this->general_model->list_organizations();
		$this->load->view('tools/billing/exportcc_view', $data);
	}


	/*
	 * ------------------------------------------------------------------------
	 *  Export the cards specified
	 * ------------------------------------------------------------------------
	 */
	function saveexportcc()
	{
		// load the settings model for later use
		$this->load->model('settings_model');

		$billingdate = $this->input->post('billingdate');
		$organization_id = $this->input->post('organization_id');
		$billingdate1 = $this->input->post('billingdate1');
		$billingdate2 = $this->input->post('billingdate2');
		$passphrase = $this->input->post('passphrase');

		print "$billingdate";

		/*--------------------------------------------------------------------------*/
		// make a file and sign it first to verify the passphrase entered
		// before we start making a new batch for them
		/*--------------------------------------------------------------------------*/
		// get the path where to store the cc data
		$path_to_ccfile = $this->settings_model->get_path_to_ccfile();
		
		// make a file to sign
		$signfilename = "$path_to_ccfile/signtext.tmp";
		$signhandle = fopen($signfilename, 'w') or die ("cannot open $signfilename");

		// write some example text to sign with a private key
		$signtext = "Sign this";
		fwrite($signhandle, $signtext);

		// close the file
		fclose($signhandle);

		// load the encryption helper for use when calling gpg things
		$this->load->helper('encryption');
		
		$gpgsigncommand = $this->config->item('gpg_sign')." $signfilename";
		$signed = sign_command($gpgsigncommand, $passphrase);

		// if there is a gpg error, stop here
		if (substr($signed,0,5) == "error") 
		{
			die ("Signature Error: $signed");
		}      

		/*--------------------------------------------------------------------*/
		// Create the billing data
		/*--------------------------------------------------------------------*/

		// determine the next available batch number
		$batchid = $this->billing_model->get_nextbatchnumber();
		echo lang('batch').": $batchid<p>\n";

		//
		// Check if they are doing a billing date range or just one date
		//

		$totalall = 0;

		if ($billingdate2) 
		{
			$startdate = $billingdate1;
			$enddate = $billingdate2;
			$mydate = $startdate;
			echo "Date Range: $startdate - $enddate<p>\n";
			while ($mydate <= $enddate) 
			{
				echo "Processing $mydate<br>\n";

				// Add creditcard taxes and services to the bill
				$numtaxes = $this->billing_model->add_taxdetails($mydate, NULL, 
						'creditcard', $batchid, $organization_id);
				$numservices = $this->billing_model->add_servicedetails($mydate, NULL, 
						'creditcard', $batchid, $organization_id);
				echo lang('creditcard').": $numtaxes ".lang('added').", 
					$numservices ".lang('added')."<p>\n";

				// Add prepaycc taxes and services to the bill
				$numpptaxes = $this->billing_model->add_taxdetails($mydate, NULL, 
						'prepaycc', $batchid, $organization_id);
				$numppservices = $this->billing_model->add_servicedetails($mydate, NULL,
						'prepaycc', $batchid, $organization_id);
				echo lang('prepay')." ".lang('creditcard').": $numpptaxes ".lang('added').", 
					$numppservices ".lang('added')."<p>\n";

				// Update Reruns to the bill
				$numreruns = $this->billing_model->update_rerundetails($mydate, 
						$batchid, $organization_id);
				echo "$numreruns ".lang('rerun')."<p>\n";

				// make the next date to check	
				list($myyear, $mymonth, $myday) = split('-', $mydate);
				$nextday = date("Y-m-d", mktime(0, 0, 0, $mymonth, $myday+1, $myyear));
				$totalall = $numreruns + $numservices + $numtaxes + $numpptaxes + $numppservices + $totalall;
				$mydate = $nextday;
			} // end while	
		} 
		else 
		{
			// for a single date run
			// Add creditcard taxes and services to the bill
			$numtaxes = $this->billing_model->add_taxdetails($billingdate, NULL, 
					'creditcard', $batchid, $organization_id);
			$numservices = $this->billing_model->add_servicedetails($billingdate, NULL, 
					'creditcard', $batchid, $organization_id);
			echo lang('creditcard').": $numtaxes ".lang('_added').", 
				$numservices ".lang('added')."<p>\n";

			// Add prepaycc taxes and services to the bill
			$numpptaxes = $this->billing_model->add_taxdetails($billingdate, NULL, 
					'prepaycc', $batchid, $organization_id);
			$numppservices = $this->billing_model->add_servicedetails($billingdate, NULL,  
					'prepaycc', $batchid, $organization_id);
			echo lang('prepay')." ".lang('creditcard').": $numpptaxes ".lang('added').", 
				$numppservices ".lang('added')."<p>\n";

			// Update Reruns to the bill
			$numreruns = $this->billing_model->update_rerundetails($billingdate, 
					$batchid, $organization_id);
			echo "$numreruns ".lang('rerun')."<p>\n";

			$totalall = $numreruns + $numservices + $numtaxes + $numpptaxes + $numppservices + $totalall;
		} // endif for billingdate range

		// show message if no records have been found
		if ($totalall == 0) 
		{
			echo "<b>".lang('sorrynorecordsfound')."<b><p>\n";
		} 
		else 
		{
			// create billinghistory
			$this->billing_model->create_billinghistory($batchid, 'creditcard', $this->user);

			// export the batch file
			$exportfilename = $this->billing_model->export_card_batch($organization_id, $batchid, 
					$path_to_ccfile, $passphrase);

			// log this export activity
			$this->log_model->activity($this->user,0,'export','creditcard',$batchid,'success');

			echo lang('wrotefile')." $exportfilename<br><a href=\"$this->ssl_url_prefix/index.php/tools/dashboard/downloadfile/$exportfilename\"><u class=\"bluelink\">".lang('downloadfile')." $exportfilename</u></a><p>";	
		} // end if totalall
	}


	/*
	 * ------------------------------------------------------------------------
	 *  show form to re-export a credit card batch by id
	 * ------------------------------------------------------------------------
	 */
	function fixexportcc()
	{
		// load the general model so we can get a list of organizations
		$this->load->model('general_model');

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$data['orglist'] = $this->general_model->list_organizations();
		$this->load->view('tools/billing/fixexportcc_view', $data);
	}


	/*
	 * ------------------------------------------------------------------------
	 *  perform re-export of credit card batch by id
	 * ------------------------------------------------------------------------
	 */
	function savefixexportcc()
	{
		// load the settings model for later use
		$this->load->model('settings_model');
		$this->load->model('general_model');

		$organization_id = $this->input->post('organization_id');
		$passphrase = $this->input->post('passphrase');
		$batchid = $this->input->post('batchid');

		/*--------------------------------------------------------------------------*/
		// make a file and sign it first to verify the passphrase entered
		// before we start making a new batch for them
		/*--------------------------------------------------------------------------*/
		// get the path where to store the cc data
		$path_to_ccfile = $this->settings_model->get_path_to_ccfile();
		
		// make a file to sign
		$signfilename = "$path_to_ccfile/signtext.tmp";
		$signhandle = fopen($signfilename, 'w') or die ("cannot open $signfilename");

		// write some example text to sign with a private key
		$signtext = "Sign this";
		fwrite($signhandle, $signtext);

		// close the file
		fclose($signhandle);

		// load the encryption helper for use when calling gpg things
		$this->load->helper('encryption');
		
		$gpgsigncommand = $this->config->item('gpg_sign')." $signfilename";
		$signed = sign_command($gpgsigncommand, $passphrase);

		// if there is a gpg error, stop here
		if (substr($signed,0,5) == "error") 
		{
			die ("Signature Error: $signed");
		}      

		echo lang('batch').": $batchid<p>\n";

		// export the batch file
		$exportfilename = $this->billing_model->export_card_batch($organization_id, $batchid, 
				$path_to_ccfile, $passphrase);

		// log this export activity
		$this->log_model->activity($this->user,0,'export','creditcard',$batchid,'success');

		echo lang('wrotefile')." $exportfilename<br><a href=\"$this->ssl_url_prefix/index.php/tools/dashboard/downloadfile/$exportfilename\"><u class=\"bluelink\">".lang('downloadfile')." $exportfilename</u></a><p>";	
	}


	/*
	 * ------------------------------------------------------------------------
	 *  show the importcc form page
	 * ------------------------------------------------------------------------
	 */
	function importcc()
	{
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/billing/importcc_view', array('error' => ''));
	}


	/*
	 * ------------------------------------------------------------------------
	 *  input the importcc data
	 * ------------------------------------------------------------------------
	 */
	function saveimportcc()
	{
		// load the settings model 
		$this->load->model('settings_model');

		// POST Variables
		$userfile = $_FILES['userfile']['tmp_name'];

		// get the path where to store the cc data
		$path_to_ccfile = $this->settings_model->get_path_to_ccfile();

		// upload the file
		$config['upload_path'] = $path_to_ccfile;
		$config['allowed_types'] = 'csv|txt|tab|zip';
		$config['file_name'] = 'newfile.txt';
		$config['max_size'] = 0;

		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload())
		{
			$error = array('error' => $this->upload->display_errors());

			$this->load->view('tools/billing/importcc_view', $error);
		}
		else
		{
			$data = array('upload_data' => $this->upload->data());

			echo lang('uploadcomplete')." ".$userfile;

			// continue processing the uploaded file

			$myfile = "$path_to_ccfile/newfile.txt";

			print lang('fileuploaded').": $myfile<p>";

			// OPEN THE FILE AND PROCESS IT
			// line format: "transaction code","card number","card expire",
			// "amount","billing id","approved or declined","avs response"
			// line example: "CHARGE","4111111111111111","0202","18.95","1","Yes","A"

			// make an empty declined array to hold billing_id's of declined 
			// customers
			$declined = array();

			$fp = @fopen($myfile, "r") or die ("$l_cannotopen $myfile");

			while ($line = @fgetcsv($fp, 0)) 
			{

				list($transaction_code, $cardnumber, $cardexp, $amount, 
						$billing_id, $response_code, $avs_response) = $line;

				// letters Y or N at the beginning, the rest does not matter 
				$response_id = substr ($response_code,0,1);    

				$mytyperesult = $this->billing_model->get_billing_method_attributes($billing_id);

				$billingmethod = $mytyperesult['t_method'];
				$mybillingdate = $mytyperesult['b_next_billing_date'];
				$myfromdate = $mytyperesult['b_from_date'];
				$mytodate = $mytyperesult['b_to_date'];
				$mybillingfreq = $mytyperesult['t_frequency'];
				$contact_email = $mytyperesult['b_contact_email'];

				// declined or credit (first letter of response code is an 'N')
				if ($response_id == 'N') 
				{
					if ($transaction_code == 'CREDIT' OR $transaction_code == '38') 
					{	
						$this->billing_model->insert_card_payment_history('credit',
								$transaction_code, $billing_id, $cardnumber, 
								$cardexp, $response_code, $amount, $avs_response);
					} 
					else 
					{ 
						$this->billing_model->insert_card_payment_history('declined',
								$transaction_code, $billing_id, $cardnumber, 
								$cardexp, $response_code, $amount, $avs_response);

						// push the customers email address into the 
						// declined array
						array_push($declined, $billing_id);

						// put a message in the customer notes that 
						// a declined email was sent to their contact_email

						$myaccountnumber = $this->billing_model->get_account_number($billing_id);

						// put a note in the customer history
						// add to customer_history
						$status = "automatic";
						$desc = lang('declinedmessagesentto')." $contact_email";
						$this->support_model->create_ticket($this->user, 'nobody', 
								$myaccountnumber, $status, $desc);

					}	// end if transaction code		

				} // end if response id = n

				// authorized (first letter of response code is a 'Y')
				if ($response_id == 'Y') 
				{
					$payment_history_id = $this->billing_model->insert_card_payment_history(
							'authorized', 
							$transaction_code, $billing_id, $cardnumber, $cardexp, 
							$response_code, $amount, $avs_response
							);

					// update the next_billing_date, to_date, 
					// from_date, and payment_due_date for prepay/prepaycc 
					if ($billingmethod == 'prepaycc' OR $billingmethod == 'prepay') 
					{
						$this->billing_model->update-billing_dates($mybillingdate, $mybillingfreq,
								$myfromdate, $billing_id);
					}

					$this->billing_model->pay_billing_details($payment_history_id, $billing_id, $amount);

				} // end if response_id = y
			} // end while fgetcsv

			// close the file
			@fclose($fp) or die ("$l_cannotclose $myfile");

			// delete the file
			unlink($myfile);

			// send email messages to declined customers listed in the
			// declined array

			foreach ($declined as $key=>$mybillingid) 
			{
				$this->billing_model->send_declined_email($mybillingid);

			}

			echo "<p>".lang('done')."</p>";

			// log this import activity
			$this->log_model->activity($this->user,0,'import','creditcard',0,'success');

		}
	}


	/*
	 * ------------------------------------------------------------------------
	 *  show the form to allow printing of new invoice batches
	 * ------------------------------------------------------------------------
	 */
	function invoice()
	{
		// load the general model so we can get a list of organizations
		$this->load->model('general_model');

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$data['orglist'] = $this->general_model->list_organizations();
		$this->load->view('tools/billing/invoice_view', $data);
	}


	function printinvoice()
	{
		// load the settings model and service model
		$this->load->model('settings_model');
		$this->load->model('service_model');

		$billingdate = $this->input->post('billingdate');
		$bybillingid = $this->input->post('billingid');
		$byacctnum = $this->input->post('acctnum');
		$organization_id = $this->input->post('organization_id');
		$billingdate1 = $this->input->post('billingdate1');
		$billingdate2 = $this->input->post('billingdate2');

		// make sure the user is in a group that is allowed to run this

		// select the path_to_ccfile from settings
		$path_to_ccfile = $this->settings_model->get_path_to_ccfile();

		/*--------------------------------------------------------------------*/
		// Check if they entered by account number and change to bybillingid
		/*--------------------------------------------------------------------*/
		if ($byacctnum <> NULL) 
		{
			$bybillingid = $this->billing_model->default_billing_id($byacctnum);
		}

		/*--------------------------------------------------------------------*/
		// Create the billing data
		/*--------------------------------------------------------------------*/

		// determine the next available batch number
		$batchid = $this->billing_model->get_nextbatchnumber();

		// query for taxed services that are billed on the specified date
		// and a specific organization
		if ($billingdate2 <> NULL)
		{
			$startdate = $billingdate1;
			$enddate = $billingdate2;
			$mydate = $startdate;
			echo "Date Range: $startdate - $enddate<p>\n";
			while ($mydate <= $enddate)
			{
				echo "Processing $mydate<br>\n";

				// Add creditcard taxes and services to the bill
				$numtaxes = $this->billing_model->add_taxdetails($mydate, NULL, 
						'invoice', $batchid, $organization_id);
				$numservices = $this->billing_model->add_servicedetails($mydate, NULL, 
						'invoice', $batchid, $organization_id);
				echo lang('taxes')." $numtaxes ".lang('added').", ".lang('services').
					" $numservices ".lang('added')."<p>\n";

				// make the next date to check	
				list($myyear, $mymonth, $myday) = split('-', $mydate);
				$nextday = date("Y-m-d", mktime(0, 0, 0, $mymonth, $myday+1, $myyear));
				$totalall = $numreruns + $numservices + $numtaxes + $numpptaxes + $numppservices + $totalall;
				$mydate = $nextday;
			} // end while	
		}
		elseif ($billingdate <> NULL)
		{
			// by billing date
			$numtaxes = $this->billing_model->add_taxdetails($billingdate, 
					NULL, 'invoice', $batchid, $organization_id);
			$numservices = $this->billing_model->add_servicedetails($billingdate, 
					NULL,'invoice', $batchid, $organization_id);
		}
		else
		{
			// by billing id
			$numtaxes = $this->billing_model->add_taxdetails(NULL, 
					$bybillingid,'invoice', $batchid, NULL);
			$numservices = $this->billing_model->add_servicedetails(NULL, 
					$bybillingid,'invoice', $batchid, NULL);
		}
		//echo "taxes: $numtaxes, services: $numservices<p>";

		// create billinghistory
		$this->billing_model->create_billinghistory($batchid, 'invoice', $this->user);	

		/*-------------------------------------------------------------------*/	
		// Print the invoice
		/*-------------------------------------------------------------------*/
		// query the batch for the invoices to do
		$invoice_batch = $this->billing_model->get_invoice_batch($batchid);

		// Show the control box on top of everthing else
		//echo "<div id=\"popbox\">
		//<a href=\"javascript:print()\">[Print]</a></div>";

		//require('./include/fpdf.php');
		$this->load->library('fpdf');    

		$pdf = new FPDF();

		foreach ($invoice_batch AS $myresult) 
		{
			// get the invoice data to process now
			$invoice_number = $myresult['invoice_number'];
			$pdf = $this->billing_model->outputinvoice($invoice_number, "pdf", $pdf);	
		}

		$filename = "$path_to_ccfile/invoice$batchid.pdf";
		$pdf->Output($filename,'F');

		// output the link to the pdf file
		echo lang('wrotefile')." $filename<br>".
			"<a href=\"$this->url_prefix/index.php/tools/dashboard/downloadfile/invoice$batchid.pdf\">".
			"<u class=\"bluelink\">".lang('download')." invoice$batchid.pdf</u></a><p>";	
	}





	/*
	 * ------------------------------------------------------------------------
	 *  show the form to allow emailing of einvoice batches
	 * ------------------------------------------------------------------------
	 */
	function einvoice()
	{
		// load the general model so we can get a list of organizations
		$this->load->model('general_model');

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$data['orglist'] = $this->general_model->list_organizations();
		$this->load->view('tools/billing/einvoice_view', $data);
	}


	/*
	 * ------------------------------------------------------------------------
	 *  send the batch of einvoices indicated by the input from einvoice tool
	 * ------------------------------------------------------------------------
	 */
	function sendeinvoice()
	{
		// load the service model
		$this->load->model('service_model');

		$billingdate = $this->input->post('billingdate');
		$bybillingid = $this->input->post('billingid');
		$byacctnum = $this->input->post('acctnum');
		$organization_id = $this->input->post('organization_id');
		$billingdate1 = $this->input->post('billingdate1');
		$billingdate2 = $this->input->post('billingdate2');

		/*--------------------------------------------------------------------*/
		// Check if they entered by account number and change to bybillingid
		/*--------------------------------------------------------------------*/
		if ($byacctnum <> NULL) 
		{
			$bybillingid = $this->billing_model->default_billing_id($byacctnum);
		}

		/*--------------------------------------------------------------------*/
		// Create the billing data
		/*--------------------------------------------------------------------*/

		// determine the next available batch number
		$batchid = $this->billing_model->get_nextbatchnumber();
		echo "BATCH: $batchid<p>\n";

		// query for taxed services that are billed on the specified date
		// and for a specific organization

		if ($billingdate2 <> NULL) 
		{
			$startdate = $billingdate1;
			$enddate = $billingdate2;
			$mydate = $startdate;
			echo "Date Range: $startdate - $enddate<p>\n";
			while ($mydate <= $enddate) 
			{
				echo "Processing $mydate<br>\n";

				// Add creditcard taxes and services to the bill
				$numtaxes = $this->billing_model->add_taxdetails($mydate, NULL, 
						'einvoice', $batchid, $organization_id);
				$numservices = $this->billing_model->add_servicedetails($mydate, NULL, 
						'einvoice', $batchid, $organization_id);
				echo lang('taxes')." $numtaxes ".lang('added').", ".lang('services')." 
					$numservices ".lang('added')."<p>\n";

				// make the next date to check	
				list($myyear, $mymonth, $myday) = split('-', $mydate);
				$nextday = date("Y-m-d", mktime(0, 0, 0, $mymonth, $myday+1, $myyear));
				$totalall = $numreruns + $numservices + $numtaxes + $numpptaxes + $numppservices + $totalall;
				$mydate = $nextday;
			} // end while	
		} 
		elseif ($billingdate <> NULL) 
		{ 
			// by billing date
			$numtaxes = $this->billing_model->add_taxdetails($billingdate, NULL, 
					'einvoice', $batchid, $organization_id);
			$numservices = $this->billing_model->add_servicedetails($billingdate, NULL,
					'einvoice', $batchid, $organization_id);
		} 
		else 
		{ 
			// by billing id
			$numtaxes = $this->billing_model->add_taxdetails(NULL, $bybillingid,
					'einvoice', $batchid, NULL);
			$numservices = $this->billing_model->add_servicedetails(NULL, $bybillingid,
					'einvoice', $batchid, NULL);
		}
		echo "taxes: $numtaxes, services: $numservices<p>";

		// create billinghistory
		$this->billing_model->create_billinghistory($batchid, 'einvoice', $this->user);	

		/*-------------------------------------------------------------------*/	
		// Email the invoice
		/*-------------------------------------------------------------------*/

		// query the batch for the invoices to do
		$invoice_batch = $this->billing_model->get_invoice_batch($batchid);

		foreach ($invoice_batch AS $myresult) 
		{
			// get the invoice data to process now
			$invoice_number = $myresult['invoice_number'];
			$contact_email = $myresult['contact_email'];
			$invoice_account_number = $myresult['account_number'];
			$invoice_billing_id = $myresult['id'];

			$this->billing_model->emailinvoice ($invoice_number,$contact_email,$invoice_billing_id);
			echo "sent invoice to $contact_email<br>\n";
		}

		echo "<b style=\"color:red;\">done</b>";

	}


	/*
	 * ------------------------------------------------------------------------
	 *  show the refund processing form to choose which refund org to export
	 * ------------------------------------------------------------------------
	 */
	function refundcc()
	{
		// load the general model so we can get a list of organizations
		$this->load->model('general_model');

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$data['orglist'] = $this->general_model->list_organizations();
		$this->load->view('tools/billing/refundcc_view', $data);
	}


	/*
	 * ------------------------------------------------------------------------
	 *  create the refund batch requested by the refundcc form user
	 * ------------------------------------------------------------------------
	 */
	function saverefundcc()
	{
		// load the settings model and service model
		$this->load->model('settings_model');
		$this->load->model('service_model');

		// load the encryption helper for use by export card refunds
		$this->load->helper('encryption');

		$organization_id = $this->input->post('organization_id');
		$passphrase = $this->input->post('passphrase');

		// select the path_to_ccfile from settings
		$path_to_ccfile = $this->settings_model->get_path_to_ccfile();

		// create the refund batch for this choice
		$exportfilename = $this->billing_model->export_card_refunds($organization_id, 
				$path_to_ccfile, $passphrase);

		// log this export activity
		$this->log_model->activity($this->user,0,'export','creditcard',0,'success');

		$today = date("Y-m-d");

		echo lang('wrotefile')." $exportfilename<br><a href=\"$this->ssl_url_prefix/index.php/tools/dashboard/downloadfile/$exportfilename\"><u class=\"bluelink\">".lang('download')." $exportfilename</u></a><p>";	

	}

}


/* End of file tools/billing */
/* Location: ./application/controllers/tools/billing.php */
