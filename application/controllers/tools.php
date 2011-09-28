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
			$payment_options = $payment_options . "<option>". $mypayresult['name'] . "</option>\n";
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

		/*--------------------------------------------------------------------*/
		// enter payments by invoice number	
		/*--------------------------------------------------------------------*/
		if ($invoice_number > 0) {
			$query = "SELECT * FROM billing_details ".
				"WHERE paid_amount < billed_amount AND invoice_number = $invoice_number";
			$result = $this->db->query($query) or die ("Query Failed");
			$invoiceresult = $this->db->query($query) or die ("$l_queryfailed");

			// update values with missing information
			$myresult = $invoiceresult->row_array();
			$billing_id = $myresult['billing_id'];

			/*--------------------------------------------------------------------*/
			// enter payments by account number
			/*--------------------------------------------------------------------*/
		} 
		elseif ($account_num > 0) 
		{
			$query = "SELECT bd.id, bd.paid_amount, bd.billed_amount, bd.billing_id ".
				"FROM billing_details bd ".
				"LEFT JOIN billing bi ON bd.billing_id = bi.id ".
				"LEFT JOIN customer cu ON bi.id = cu.default_billing_id ".
				"WHERE bd.paid_amount < bd.billed_amount ".
				"AND cu.account_number = $account_num";
			$result = $this->db->query($query) or die ("select acctnum query failed");
			$accountresult = $this->db->query($query) or die ("select acctnum query failed");

			// update values with missing information
			$myresult = $accountresult->row_array();
			$billing_id = $myresult['billing_id'];

			/*--------------------------------------------------------------------*/
			// enter payments by billing id
			/*--------------------------------------------------------------------*/
		} 
		else 
		{
			$query = "SELECT * FROM billing_details ".
				"WHERE paid_amount < billed_amount AND billing_id = $billing_id";
			$result = $this->db->query($query) or die ("select detail query failed");	
		}

		// make sure the result of the above queries returned some rows
		$myrowcount = $result->num_rows();
		if ($myrowcount > 0) 
		{
			// insert info into the payment history
			$query = "INSERT INTO payment_history (creation_date, billing_id, ".
				"billing_amount, status, payment_type, invoice_number, check_number) ".
				"VALUES (CURRENT_DATE,'$billing_id','$payment',".
				"'authorized','$payment_type','$invoice_number','$check_number')";
			$paymentresult = $this->db->query($query) or die ("$query insert history query failed");

			// get the payment history id that will be inserted into the billing_details
			// items that are paid by this entry
			$payment_history_id = $this->db->insert_id();

			/*------------------------------------------------------------------------*/
			// go through the billing details items
			/*------------------------------------------------------------------------*/  
			$i = 0;
			while (($myresult = $result->row_array($i)) and (round($amount,2) > 0)) 
			{
				$id = $myresult['id'];
				$paid_amount = sprintf("%.2f",$myresult['paid_amount']);
				$billed_amount = sprintf("%.2f",$myresult['billed_amount']);

				// calculate amount owed
				$owed = round($billed_amount - $paid_amount,2);

				// fix float precision too
				if (round($amount,2) >= round($owed,2)) {
					$amount = round($amount - $owed, 2);
					$fillamount = round($owed + $paid_amount,2);

					$query = "UPDATE billing_details ".
						"SET paid_amount = '$fillamount', ".
						"payment_applied = CURRENT_DATE, ".
						"payment_history_id = '$payment_history_id' ".	    
						"WHERE id = $id";
					$greaterthanresult = $this->db->query($query)
						or die ("detail update query failed");
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
					$lessthenresult = $this->db->query($query) or die ("detail update query failed");
				} // end if amount >= owed

				// increment row counter
				$i++;

			} // end while myresult and amount > 0


			/*--------------------------------------------------------------------*/
			// If the payment is made towards a prepaid account, then move
			// the billing and payment dates forward for payment terms
			/*--------------------------------------------------------------------*/

			//
			// update the Next Billing Date to whatever the 
			// billing type dictates +1 +2 +6 etc.
			// get the current billing type
			//

			// Also select the customer's billing name, company, and address
			// here to show up at the end to show what account paid.

			$query = "SELECT b.billing_type b_billing_type, ".
				"b.next_billing_date b_next_billing_date, ".
				"b.from_date b_from_date, b.to_date b_to_date, ".
				"b.name, b.company, b.street, b.city, b.state, ".
				"b.account_number b_account_number, ".
				"t.id t_id, t.frequency t_frequency, t.method t_method ".
				"FROM billing b ".
				"LEFT JOIN billing_types t ON b.billing_type = t.id ".
				"WHERE b.id = $billing_id";
			$result = $this->db->query($query) or die ("select next billing query failed");
			$billingresult = $result->row_array();
			$method = $billingresult['t_method'];
			$billing_name = $billingresult['name'];
			$billing_company = $billingresult['company'];
			$billing_street = $billingresult['street'];
			$billing_city = $billingresult['city'];
			$billing_state = $billingresult['state'];
			$billing_account_number = $billingresult['b_account_number'];

			// if they are prepay accounts the update their billing dates
			if ($method == 'prepay' OR $method == 'prepaycc') 
			{
				$mybillingdate = $billingresult['b_next_billing_date'];
				$myfromdate = $billingresult['b_from_date'];
				$mytodate = $billingresult['b_to_date'];
				$mybillingfreq = $billingresult['t_frequency'];

				// to get the to_date, need to double the frequency added
				$doublefreq = $mybillingfreq * 2;	

				//
				// insert the new next_billing_date
				// and a new from_date and to_date 
				// and payment_due_date based on from_date
				//

				$query = "UPDATE billing SET ".
					"next_billing_date = DATE_ADD('$mybillingdate', ".
					"INTERVAL '$mybillingfreq' MONTH), ".
					"from_date = DATE_ADD('$myfromdate', ".
					"INTERVAL '$mybillingfreq' MONTH), ".
					"to_date = DATE_ADD('$myfromdate', ".
					"INTERVAL '$doublefreq' MONTH), ".
					"payment_due_date = DATE_ADD('$myfromdate', ".
					"INTERVAL '$mybillingfreq' MONTH) ".
					"WHERE id = '$billing_id'"; 
				$updateresult = $this->db->query($query) or die ("update query failed");
			}

			if ($amount > 0) {
				// if there is an over payment show the amount in red
				//and prompt to add as a credit		
				print "<h3 style=\"color: red;\">".lang('paymentsaved').", ".lang('currency')."$amount ".
					lang('leftover').", <a href=\"index.php/tools/addcredit/$amount/$billing_id\">".lang('addcreditfor')." $amount</a></h3>";
			} else {
				print "<h3>".lang('paymentsaved').": </h3>";
			}

			// print the customer billing information to confirm who the payment
			// was entered for.

			echo "<blockquote>$billing_account_number<br>$billing_name<br>".
				"$billing_company<br>$billing_street<br>$billing_city $billing_state</blockquote><p>";

		} // end if result RowCount > 0 

		else {
			echo "no matching rows, queryfailed";
		}

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
