<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tools extends App_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('customer_model');
		$this->load->model('module_model');
		$this->load->model('billing_model');
		$this->load->model('user_model');
	}		

	/*
	 * ------------------------------------------------------------------------
	 *  Show the tools to this user they have permission to view
	 * ------------------------------------------------------------------------
	 */
	public function index()
	{
		// load the module header common to all module views
		$this->load->view('module_header_view');

		// show user tools that everyone can use
		$this->load->view('tools/usertools_view');

		// check for user privileges to see if the are manager or admin
		$privileges = $this->user_model->user_privileges($this->user);

		if (($privileges['manager'] == 'y') OR ($privileges['admin'] == 'y'))
		{
			// get list of the modules that are installed
			$result = $this->module_model->modulelist();

			foreach($result->result() as $myresult)
			{
				$modulename = $myresult->modulename;

				// load the tools view for this module
				// view file in the format modulenametools_view, eg: customertools_view
				$this->load->view("tools/".$modulename."tools_view");
			}

			// Show Reports Tools for manager and admin
			$this->load->view('tools/reporttools_view');
		}

		if ($privileges['admin'] == 'y')
		{
			// Show Admin Tools for admin
			$this->load->view('tools/admintools_view');
		}

		// the html page footer
		$this->load->view('html_footer_view');

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

		$this->load->view('tools/payment_view', $data);

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

		$p = $this->billing_model->enter_payment($account_num, $billing_id, $amount, 
				$payment_type, $invoice_number, $check_number);

		if ($p['amount'] > 0) 
		{
			// if there is an over payment show the amount in red
			//and prompt to add as a credit		
			print "<h3 style=\"color: red;\">".lang('paymentsaved').", ".lang('currency').$p['amount'] .
				lang('leftover').", <a href=\"index.php/tools/addcredit/".$p['amount']."/$billing_id\">".
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

		$this->load->view('tools/payment_view', $data);
	}


	function changepass()
	{
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/changepass_view');
	}

	function savechangepass()
	{
		$feedback = $this->input->post('feedback');
		$new_password1 = $this->input->post('new_password1');
		$new_password2 = $this->input->post('new_password2');
		$old_password = $this->input->post('old_password');

		$real_name = $this->user_model->user_getrealname($this->user);
		echo "$real_name, ".lang('youareloggedinas')." ".$this->user."<br>";

		$feedback = $this->user_model->user_change_password($new_password1,$new_password2,$this->user,$old_password);

		echo '<FONT COLOR="RED"><H2>'.$feedback.'</H2></FONT>';

	}

	function version()
	{
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/version_view');
	}

	function notifications()
	{
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$privileges = $this->user_model->user_privileges($this->user);

		$this->load->view('tools/notifications_view', $privileges);

	}

	function savenotifications()
	{
		$email = $this->input->post('email');
		$screenname = $this->input->post('screenname');
		$email_notify = $this->input->post('email_notify');
		$screenname_notify = $this->input->post('screenname_notify');

		$this->user_model->update_usernotifications($email, $screenname, $email_notify, $screenname_notify);
		print "<h3>".lang('changessaved')."</h3>";

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		// show the info you just changed for the user
		$privileges = $this->user_model->user_privileges($this->user);

		$this->load->view('tools/notifications_view', $privileges);
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

		$this->load->view('tools/importnew_view');
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
		$config['allowed_types'] = 'csv|txt|tab';
		$config['file_name'] = 'newaccounts.txt';
		$config['max_size'] = 0;

		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload())
		{
			$error = array('error' => $this->upload->display_errors());

			$this->load->view('upload_form', $error);
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
						$query = "SELECT * FROM linked_services WHERE linkfrom = $serviceid";
						$result = $this->db->query($query) or die ("$l_queryfailed");
						foreach($result->result_array() AS $myresult) 
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
		$this->load->view('tools/exportcc_view', $data);
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
		// TODO: make a file and sign it first to verify the passphrase entered
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
			$this->billing_model->export_card_batch($organization_id, $batchid, 
					$path_to_ccfile, $passphrase);

			// log this export activity
			log_activity($user,0,'export','creditcard',$batchid,'success');

			echo lang('wrotefile')." $filename<br><a href=\"index.php/tools/downloadfile/$exportprefix" . "export" . "$batchid.csv\"><u class=\"bluelink\">".lang('downloadfile')." $exportprefix"."export"."$batchid.csv</u></a><p>";	
		} // end if totalall
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

		$this->load->view('tools/importcc_view');
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
		$config['allowed_types'] = 'csv|txt|tab';
		$config['file_name'] = 'newfile.txt';
		$config['max_size'] = 0;

		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload())
		{
			$error = array('error' => $this->upload->display_errors());

			$this->load->view('upload_form', $error);
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
						$updateresult = $DB->Execute($query) 
							or die ("update billing $l_queryfailed $query");
					} // end if billing method

					// update the billing_details for things that still need to be paid
					// order by most recent invoice in desc order to pay newest run items first
					// thereby makeing sure to pay the correct rerun items too
					$query = "SELECT * FROM billing_details ". 
						"WHERE paid_amount < billed_amount ".
						"AND billing_id = $billing_id ".
						"ORDER BY recent_invoice_number DESC";
					$result = $DB->Execute($query)
						or die ("select billing details $l_queryfailed $query");

					while (($myresult = $result->FetchRow()) and (round($amount,2) > 0)) 
					{
						$id = $myresult['id'];
						$paid_amount = sprintf("%.2f",$myresult['paid_amount']);
						$billed_amount = sprintf("%.2f",$myresult['billed_amount']);

						// do stuff 
						$owed = round($billed_amount - $paid_amount,2);

						if (round($amount,2) >= round($owed,2)) 
						{
							$amount = round($amount - $owed,2);
							$fillamount = round($owed + $paid_amount,2);
							$query = "UPDATE billing_details ".
								"SET paid_amount = '$fillamount', ".
								"payment_applied = CURRENT_DATE, ".
								"payment_history_id = '$payment_history_id' ".	    
								"WHERE id = $id";
							$greaterthanresult = $DB->Execute($query) 
								or die ("greater than $l_queryfailed $query");
						} 
						else 
						{ 
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

			foreach ($declined as $key=>$mybillingid) 
			{
				// select the info for emailing the customer
				// get the org billing email address for from address           
				$query = "SELECT g.email_billing, g.declined_subject, g.declined_message, ".
					"b.contact_email, b.account_number, b.creditcard_number, b.creditcard_expire ". 
					"FROM billing b ".
					"LEFT JOIN general g ON b.organization_id = g.id ".
					"WHERE b.id = $mybillingid";
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
			echo "<p>".lang('done')."</p>";

			// log this import activity
			$this->log_model->activity($this->user,0,'import','creditcard',0,'success');

		}
	}


}
