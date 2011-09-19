<?php

class Notice
{
	public $contactemail;
	public $pdfname;
	private $notice_type;
	private $billing_id;
	private $method;
	private $noticeheading;
	private $message;
	private $billingemail;
	private $today;
	private $payment_due_date;
	private $turnoff_date;
	private $cancel_date;

	/*---------------------------------------------------------------------------*/
	// construct notice message
	/*---------------------------------------------------------------------------*/

	function Notice($config = array()) {

		$this->notice_type = $config['notice_type'];
		$this->billing_id = $config['billing_id'];
		$this->method = $config['method'];
		$this->payment_due_date = $config['payment_due_date'];
		$this->turnoff_date = $config['turnoff_date'];
		$this->cancel_date = $config['cancel_date'];

		//Date for today
		$this->today = date("Y-m-d");
		$this->humanday = date("F j, Y");

		$this->create();

		// look for method type
		switch ($this->method) {
			case 'email':
				$this->send_email();
				break;

			case 'pdf':
				$this->print_pdf();
				break;

			case 'both':
				$this->send_email();
				$this->print_pdf();
				break;
		}
	} // end constructor


	/*--------------------------------------------------------------------------*/
	// create notice
	//
	// by email or make a pdf link for billing to print out and send
	// method is 'email', 'pdf', or 'both'
	// notice type is 'pastdue', 'shutoff', 'turnoff', 'cancel', 'cancelwfee'
	//
	/*--------------------------------------------------------------------------*/
	public function create () {
		// globals
		global $DB, $lang;
		require('./include/fpdf.php');

		// convert the dates into normal human readable format instead of ISO
		$payment_due_date = humandate($this->payment_due_date, $lang);
		$turnoff_date = humandate($this->turnoff_date, $lang);
		$cancel_date = humandate($this->cancel_date, $lang);


		// get the info for the middle body of the message:
		// get the total owed for any pastdue services on the account
		$query = "SELECT bi.id bi_id, bi.account_number bi_account_number, ".
			"bi.contact_email bi_contact_email,  bi.name bi_name, ".
			"bi.company bi_company, bi.street bi_street, bi.city bi_city, ".
			"bi.state bi_state, bi.zip bi_zip, r.description r_description, ".
			"ms.service_description ms_description, ".
			"bd.taxed_services_id, bd.billed_amount, bd.paid_amount, ".
			"g.org_name, g.org_street, g.org_city, g.org_state, g.org_zip, ".
			"g.phone_billing, g.email_billing, bh.payment_due_date, bh.id bh_id ".
			"FROM billing_details bd ".
			"LEFT JOIN user_services us ON bd.user_services_id = us.id ".
			"LEFT JOIN master_services ms ON us.master_service_id = ms.id ".
			"LEFT JOIN taxed_services t ON t.id = bd.taxed_services_id ".
			"LEFT JOIN tax_rates r ON t.tax_rate_id = r.id ".
			"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
			"LEFT JOIN billing_history bh ON bh.id = bd.invoice_number ".
			"LEFT JOIN general g ON bi.organization_id = g.id ".
			"WHERE bd.billed_amount > bd.paid_amount ".
			"AND bi.pastdue_exempt <> 'y' ".
			"AND bh.payment_due_date < '$this->today' ".
			"AND bi.id = $this->billing_id";

		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("Send Notice Query Failed");
		// initialize variables
		$total_owed = 0;

		// get the items that are past due to print out
		while ($myresult = $result->FetchRow()) {
			// organization info
			$org_name = $myresult['org_name'];
			$org_street = $myresult['org_street'];
			$org_city = $myresult['org_city'];
			$org_state = $myresult['org_state'];
			$org_zip = $myresult['org_zip'];
			$phone_billing = $myresult['phone_billing'];

			// customer info
			$this->billingemail = $myresult['email_billing'];
			$billing_account_number = $myresult['bi_account_number'];
			$invoice_number = $myresult['bh_id'];
			$billing_name = $myresult['bi_name'];
			$billing_company = $myresult['bi_company'];
			$billing_street = $myresult['bi_street'];
			$billing_city = $myresult['bi_city'];
			$billing_state = $myresult['bi_state'];
			$billing_zip = $myresult['bi_zip'];

			$this->contactemail = $myresult['bi_contact_email'];
			$billed_amount = round($myresult['billed_amount'], 2);
			$paid_amount = round($myresult['paid_amount'], 2);

			// set the description for a service or a tax
			if ($myresult['taxed_services_id']) {
				$service_description = $myresult['r_description'];
			} else {
				$service_description = $myresult['ms_description'];
			}

			$owed = round($billed_amount - $paid_amount, 2);

			$owed = sprintf("%.2f", $owed);
			$service_list .= "$service_description $owed\n";

			$total_owed = round($owed + $total_owed, 2);
		}

		// set the message body
		$total_owed = sprintf("%.2f", $total_owed);

		// include the language file here now that the notice_text variables are set
		include ("$lang");

		$message_body = "$l_account: $billing_account_number\n".
			"$l_invoice: $invoice_number\n".
			"$l_amount_due: $total_owed\n";

		// look for notice type and create proper message
		switch($this->notice_type) {
			case 'pastdue':
				// create the notice text with embedded information
				eval ("\$notice_text = \"$l_notice_text_pastdue\";");

				// create body of message, header, footer, and content
				$this->noticeheading = "$org_name\n$l_pastdue_notice\n";

				$this->message .= "$message_body\n\n";

				$this->message .= "$billing_name\n";

				if ($billing_company) {
					$this->message .= "$billing_company\n";
				} else {
					$this->message .= "\n";
				}

				$this->message .= "$billing_street\n";
				$this->message .= "$billing_city $billing_state $billing_zip\n\n\n\n";

				$this->message .= "$notice_text\n\n";

				$this->message .= "$l_pastdue_heading:\n";
				$this->message .= "$service_list\n\n";

				$this->message .= "$l_notice_text_footer\n\n";

				$this->message .= "$org_name\n";
				$this->message .= "$org_street\n";
				$this->message .= "$org_city, $org_state $org_zip\n";
				$this->message .= "$phone_billing\n";
				break;

			case 'shutoff':
				// create the notice text with embedded information
				eval ("\$notice_text = \"$l_notice_text_shutoff\";");
				eval ("\$notice_footer_shutoff = \"$l_notice_footer_shutoff\";");            

				// create body of message, header, footer, and content
				$this->noticeheading = "$org_name\n$l_shutoff_notice\n";

				$this->message .= "$message_body\n\n";

				$this->message .= "$billing_name\n";

				if ($billing_company) {
					$this->message .= "$billing_company\n";
				} else {
					$this->message .= "\n";
				}

				$this->message .= "$billing_street\n";
				$this->message .= "$billing_city $billing_state $billing_zip\n\n\n\n";

				$this->message .= "$notice_text\n\n";

				$this->message .= "$l_shutoff_heading:\n";
				$this->message .= "$service_list\n";

				$this->message .= "$l_notice_text_footer\n\n";     

				$this->message .= "$org_name\n";
				$this->message .= "$org_street\n";
				$this->message .= "$org_city, $org_state $org_zip\n";
				$this->message .= "$phone_billing\n\n";

				$this->message .= "$notice_footer_shutoff\n";
				break;      

			case 'cancel':
				// create the notice text with embedded information
				eval ("\$notice_text = \"$l_notice_text_cancel\";");

				// create body of message, header, footer, and content
				$this->noticeheading = "$org_name\n$l_cancel_notice\n";

				$this->message .= "$message_body\n\n";

				$this->message .= "$billing_name\n";

				if ($billing_company) {
					$this->message .= "$billing_company\n";
				} else {
					$this->message .= "\n";
				}

				$this->message .= "$billing_street\n";
				$this->message .= "$billing_city $billing_state $billing_zip\n\n\n\n";

				$this->message .= "$l_cancel_heading:\n";
				$this->message .= "$service_list\n";

				$this->message .= "$l_notice_text_footer\n\n";

				$this->message .= "$notice_text\n\n";

				$this->message .= "$org_name\n";
				$this->message .= "$org_street\n";
				$this->message .= "$org_city, $org_state $org_zip\n";
				$this->message .= "$phone_billing\n";
				break;
			case 'collections':
				// create the notice text with embedded information
				eval ("\$notice_text = \"$l_notice_text_collections\";");

				// create body of message, header, footer, and content
				$this->noticeheading = "$org_name\n$l_collections_notice\n";

				$this->message .= "$message_body\n\n";

				$this->message .= "$billing_name\n";

				if ($billing_company) {
					$this->message .= "$billing_company\n";
				} else {
					$this->message .= "\n";
				}

				$this->message .= "$billing_street\n";
				$this->message .= "$billing_city $billing_state $billing_zip\n\n\n\n";

				$this->message .= "$notice_text\n\n";

				$this->message .= "$l_collections_heading:\n";
				$this->message .= "$service_list\n";

				$this->message .= "$l_collections_notice_text_footer\n\n";

				$this->message .= "$org_name\n";
				$this->message .= "$org_street\n";
				$this->message .= "$org_city, $org_state $org_zip\n";
				$this->message .= "$phone_billing\n";
				break;   


		}
	} // end create function


	private function send_email() {
		// globals
		global $DB, $lang;
		include ("$lang");

		// Create Email Headers
		$headers = "From: $this->billingemail\n";
		// $headers .= "Cc: $this->billingemail\n";
		$to = $this->contactemail;
		$subject = "$this->noticeheading";

		// send the email message
		mail ($to, $subject, $this->message, $headers);

		// for testing:
		echo "$headers\n $to\n $subject\n $this->message\n";
	}

	private function print_pdf() {
		// globals
		global $DB, $lang;
		include ("$lang");
		require('./include/fpdf.php');

		// select the path_to_ccfile from settings
		$query = "SELECT path_to_ccfile FROM settings WHERE id = '1'";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$ccfileresult = $DB->Execute($query) 
			or die ("$l_queryfailed");
		$myccfileresult = $ccfileresult->fields;
		$path_to_ccfile = $myccfileresult['path_to_ccfile'];

		$this->pdfname = "$this->notice_type"."$this->billing_id"."-"."$this->today".".pdf";
		$filepath = "$path_to_ccfile/"."$this->pdfname";
		$filedestination = "F";
		$pdf = new FPDF();
		$pdf->AddPage();
		// heading
		$pdf->SetFont('Arial', 'B', 16);
		$pdf->Write(8, "$this->noticeheading");
		// message body
		$pdf->SetFont('Arial','B', 10);
		$pdf->Write(5,"$this->humanday\n");

		// convert message text from html codes to ascii for pdf first
		$message_text = html_to_ascii($this->message);
		$pdf->Write(5,"$message_text");

		// write the pdf file to output
		$pdf->Output($filepath, $filedestination);
	}

} // end notice class
