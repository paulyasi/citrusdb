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

		$this->load->view('tools/payment_view', $data);
	}

}
