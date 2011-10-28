<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends App_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('customer_model');
		$this->load->model('module_model');
		$this->load->model('billing_model');
		$this->load->model('user_model');
	}		


	function organization()
	{
		$addnew = $this->input->post('addnew');
		$id = $this->input->post('id');
		$org_name = $this->input->post('org_name');
		$org_street = $this->input->post('org_street');
		$org_city = $this->input->post('org_city');
		$org_state = $this->input->post('org_state');
		$org_country = $this->input->post('org_country');
		$org_zip = $this->input->post('org_zip');
		$phone_sales = $this->input->post('phone_sales');
		$email_sales = $this->input->post('email_sales');
		$phone_billing = $this->input->post('phone_billing');
		$email_billing = $this->input->post('email_billing');
		$phone_custsvc = $this->input->post('phone_custsvc');
		$email_custsvc = $this->input->post('email_custsvc');
		$ccexportvarorder = $this->input->post('ccexportvarorder');
		$regular_pastdue = $this->input->post('regular_pastdue');
		$regular_turnoff = $this->input->post('regular_turnoff');
		$regular_canceled = $this->input->post('regular_canceled');
		$dependent_pastdue = $this->input->post('dependent_pastdue');
		$dependent_shutoff_notice = $this->input->post('dependent_shutoff_notice');
		$dependent_turnoff = $this->input->postR('dependent_turnoff');
		$dependent_canceled = $this->input->post('dependent_canceled');
		$default_invoicenote = $this->input->post('default_invoicenote');
		$pastdue_invoicenote = $this->input->post('pastdue_invoicenote');
		$turnedoff_invoicenote = $this->input->post('turnedoff_invoicenote');
		$collections_invoicenote = $this->input->post('collections_invoicenote');
		$declined_subject = $this->input->post('declined_subject');
		$declined_message = $this->input->post('declined_message');
		$invoice_footer = $this->input->post('invoice_footer');
		$einvoice_footer = $this->input->post('einvoice_footer');
		$exportprefix = $this->input->post('exportprefix');

		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/organization_view', $data);
	}


	function updateorganization()
	{
		$id = $this->input->post('id');

		$org_data = array(
				'org_name' => $this->input->post('org_name'),
				'org_street' => $this->input->post('org_street'),
				'org_city' => $this->input->post('org_city'),
				'org_state' => $this->input->post('org_state'),
				'org_country' => $this->input->post('org_country'),
				'org_zip' => $this->input->post('org_zip'),
				'phone_sales' => $this->input->post('phone_sales'),
				'email_sales' => $this->input->post('email_sales'),
				'phone_billing' => $this->input->post('phone_billing'),
				'email_billing' => $this->input->post('email_billing'),
				'phone_custsvc' => $this->input->post('phone_custsvc'),
				'email_custsvc' => $this->input->post('email_custsvc'),
				'ccexportvarorder' => $this->input->post('ccexportvarorder'),
				'regular_pastdue' => $this->input->post('regular_pastdue'),
				'regular_turnoff' => $this->input->post('regular_turnoff'),
				'regular_canceled' => $this->input->post('regular_canceled'),
				'dependent_pastdue' => $this->input->post('dependent_pastdue'),
				'dependent_shutoff_notice' => $this->input->post('dependent_shutoff_notice'),
				'dependent_turnoff' => $this->input->postR('dependent_turnoff'),
				'dependent_canceled' => $this->input->post('dependent_canceled'),
				'default_invoicenote' => $this->input->post('default_invoicenote'),
				'pastdue_invoicenote' => $this->input->post('pastdue_invoicenote'),
				'turnedoff_invoicenote' => $this->input->post('turnedoff_invoicenote'),
				'collections_invoicenote' => $this->input->post('collections_invoicenote'),
				'declined_subject' => $this->input->post('declined_subject'),
				'declined_message' => $this->input->post('declined_message'),
				'invoice_footer' => $this->input->post('invoice_footer'),
				'einvoice_footer' => $this->input->post('einvoice_footer'),
				'exportprefix' => $this->input->post('exportprefix') 
					);

		$this->admin_model->updateorganization($id, $org_data);

		redirect('/tools/admin/organization');
	}


	function addorganization()
	{
		$newid = $this->admin_model->addorganization();
		redirect("/tools/admin/organization".$newid);
	}


	function settings()
	{
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/settings_view');
	}


	function users()
	{
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/users_view');
	}


	function groups()
	{
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/groups_view');
	}


	function modules()
	{
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/modules_view');
	}


	function billingtypes()
	{
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/billingtypes_view');
	}


	function services()
	{
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/services_view');
	}


	function mergeaccounts()
	{
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/mergeaccounts_view');
	}
}

/* End of file user */
/* Location: ./application/controllers/tools/user.php */
