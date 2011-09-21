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
			$modulename = $myresult->modulename;

			if (in_array ($modulename, $viewable))
			{
				// load the tools view for this module
				// view file in the format modulenametools_view, eg: customertools_view
				$this->load->view("tools/".$modulename."tools_view");
			}

		}

		// TODO
		// Show Reports Tools for manager and admin
		// Show Admin Tools for admin
		//

		// the history listing tabs
		$this->load->view('historyframe_tabs_view');			

		// the html page footer
		$this->load->view('html_footer_view');

	}
}
