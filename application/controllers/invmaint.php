<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * ----------------------------------------------------------------------------
 *  Invoice Maintenance
 *  present the list of invoices and maintenance tools to the user
 * ----------------------------------------------------------------------------
 */
class Invmaint extends App_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('customer_model');
		$this->load->model('billing_model');
		$this->load->model('module_model');
		$this->load->model('user_model');
	}		
	
    /*
     * ------------------------------------------------------------------------
     *  List of invoices for this billing id
     * ------------------------------------------------------------------------
     */
    public function index($billing_id)
    {
		// check permissions	
		$permission = $this->module_model->permission($this->user, 'billing');

		if ($permission['view'])
		{
			// load the module header common to all module views
			$this->load->view('module_header_view');
			
			// get the billing id
			$billing_id = $this->billing_model->default_billing_id($this->account_number);
			
			// show the billing information (name, address, etc)
			$data = $this->billing_model->record($billing_id);
			$this->load->view('invmaint_view', $data);
			
			// show any alternate billing types
			$data['alternate'] = $this->billing_model->alternates($this->account_number, $billing_id);
			$data['userprivileges'] = $this->user_model->user_privileges($this->user);
			$this->load->view('billing/alternate_view', $data);
			
			// the history listing tabs
			$this->load->view('historyframe_tabs_view');			
			
			// the html page footer
			$this->load->view('html_footer_view');
			
		}
		else
		{
			
			$this->module_model->permission_error();
			
		}	
		
	}


	/*
	 * ------------------------------------------------------------------------
	 *  call the invoice model to delete the indicated invoice
	 * ------------------------------------------------------------------------
	 */
	function delete() 
	{
		// get the invoicenum input
		$invoicenum = $this->input->post('invoicenum');

		// Delete the invoice, delete from billing history where id = $invoicenum
		$query = "DELETE FROM billing_history WHERE id = $invoicenum";
		$result = $DB->Execute($query) or die ("$l_queryfailed");

		// delete from billing_details where invoice_number = $invoicenum
		$query = "DELETE FROM billing_details ".
			"WHERE invoice_number = $invoicenum";                                          
		$result = $DB->Execute($query) or die ("$l_queryfailed");

		print "$l_deleted $invoicenum";
	}


	/*
	 * ------------------------------------------------------------------------
	 *  Prompt the user to ask if they are sure they want to remove the invoice
	 * ------------------------------------------------------------------------
	 */
	function remove() 
	{
		// Ask if they want to remove the invoice, print the yes/no form
		print "<b>$l_areyousureyouwanttoremoveinvoice $invoicenum</b>";

		print "<table cellpadding=15 cellspacing=0 border=0 width=720><td align=right width=360>";
		print "<form style=\"margin-bottom:0;\" action=\"index.php\" method=post>";
		print "<input type=hidden name=load value=invmaint>";
		print "<input type=hidden name=type value=tools>";
		print "<input type=hidden name=invoicenum value=$invoicenum>";
		print "<input type=hidden name=delete value=on>";
		print "<input name=deletenow type=submit value=\"  $l_yes  \" class=smallbutton></form></td>";
		print "<td align=left width=360><form style=\"margin-bottom:0;\" action=\"index.php\" method=post>";
		print "<input type=hidden name=type value=tools>";
		print "<input name=done type=submit value=\"  $l_no  \" class=smallbutton>";
		print "<input type=hidden name=load value=invmaint>";
		print "</form></td></table>";

	}



	/*
	 * ------------------------------------------------------------------------
	 *  Prompt the user to change the due date of the invoice
	 * ------------------------------------------------------------------------
	 */
	function editduedate() 
	{
		// to change the payment due date when a partial payment is made
		// and the customer wants to push their due date ahead
		// ask what they want to change the payment due date to

		echo "<FORM ACTION=\"index.php\" METHOD=\"POST\">".
			"<input type=hidden name=load value=invmaint>".
			"<input type=hidden name=type value=tools>".
			"<input type=hidden name=invoicenum value=\"$invoicenum\">".
			"<input type=hidden name=saveduedate value=\"on\">".     
			"<input type=hidden name=billingid value=\"$billingid\">".
			"<table>".
			"<td>$l_new $l_duedate:</td><td><input type=text name=duedate ".
			"value=\"$duedate\"></td><tr>".
			"<td></td><td><INPUT TYPE=\"SUBMIT\" NAME=\"submit\" ".
			"value=\"$l_submitrequest\"></td>".
			"</form>";

	}



	/*
	 * ------------------------------------------------------------------------
	 *  save the new due date invoice for that invoice number
	 * ------------------------------------------------------------------------
	 */
	function saveduedate() 
	{
		// get the invoicenum input, and billing id
		$invoicenum = $this->input->post('invoicenum');
		$billingid = $this->input->post('billingid');

		// TODO: save the new due date that was entered into the
		// billing_history.payment_due_date field

		$query = "UPDATE billing_history SET payment_due_date = '$duedate' ".
			"WHERE id = '$invoicenum'";
		$result = $DB->Execute($query) or die ("due date update $l_queryfailed");

		// redirect back to the services record for their account
		echo "<script language=\"JavaScript\">window.location.href ".
			"= \"index.php?load=invmaint&type=tools&billingid=$billingid&submit=Submit\";</script>";
		redirect('/invmaint/index/$billingid');

	}

}	
