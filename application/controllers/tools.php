<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tools extends App_Controller
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
	 *  Show the tools to this user they have permission to view
	 * ------------------------------------------------------------------------
	 */
	public function index()
	{
		// load the module header common to all module views
		$this->load->view('module_header_view');

		// show user tools that everyone can use
		$this->load->view('tools/usertools_view');
		
		// check which modules we are allowed to view
		$viewable = $this->module_model->module_permission_list($this->user);

		// get list of the modules that are installed
		$result = $this->module_model->modulelist();

		foreach($result->result() as $myresult)
		{
			$commonname = $myresult->commonname;
			$modulename = $myresult->modulename;

			// change the commonname for base modules to a language compatible name
			if ($commonname == "Customer") { $commonname = lang('customer'); }
			if ($commonname == "Services") { $commonname = lang('services'); }
			if ($commonname == "Billing") { $commonname = lang('billing'); }
			if ($commonname == "Support") { $commonname = lang('support'); }

			$myuri = $this->uri->segment(1);

			if (in_array ($modulename, $viewable))
			{
				if ($myuri == $modulename) {
					echo "<div><a class=\"active\" href=\"" . $this->url_prefix . "/index.php/$modulename\">$commonname</a></div>";
				} else {
					echo "<div><a href=\"" . $this->url_prefix . "/index.php/$modulename\">$commonname</a></div>";
				}
			}

		}

		// get the billing id
		$billing_id = $this->billing_model->default_billing_id($this->account_number);

		// show the billing information (name, address, etc)
		$data = $this->billing_model->record($billing_id);
		$this->load->view('tools/index_view', $data);

		// show any alternate billing types
		$data['alternate'] = $this->billing_model->alternates($this->account_number, $billing_id);
		$data['userprivileges'] = $this->user_model->user_privileges($this->user);
		$this->load->view('billing/alternate_view', $data);

		// the history listing tabs
		$this->load->view('historyframe_tabs_view');			

		// the html page footer
		$this->load->view('html_footer_view');

	}
}
