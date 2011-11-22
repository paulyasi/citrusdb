<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends App_Controller
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
		$this->load->view('tools/user/index_view');

		// check for user privileges to see if the are manager or admin
		$privileges = $this->user_model->user_privileges($this->user);

		if (($privileges['manager'] == 'y') OR ($privileges['admin'] == 'y'))
		{
			// get list of the modules that are installed
			$result = $this->module_model->modulelist();

			foreach($result as $myresult)
			{
				$modulename = $myresult['modulename'];

				// load the tools view for this module
				// view file in the format modulenametools_view, eg: customertools_view
				$this->load->view("tools/".$modulename."/index_view");
			}

		}

		if ($privileges['admin'] == 'y')
		{
			// Show Admin Tools for admin
			$this->load->view('tools/admin/index_view');
		}

		// the html page footer
		$this->load->view('html_footer_view');

	}


	/*
	 * ------------------------------------------------------------------------
	 *  downloadfile allows you to link to files for users like invoice pdfs
	 * ------------------------------------------------------------------------
	 */
	function downloadfile($filename)
	{
		// load the settings model
		$this->load->model('settings_model');

		// load the file download helper
		$this->load->helper('download');
		
		// check if it is a pdf file that we allow anyone to open
		// or something else that only admin can open
		$filetype = substr($filename,-3);
		if (($filetype != "pdf") AND ($filename != "summary.csv") 
				AND ($filename != "summary.tab")) 
		{
			// check that the user has admin privileges
			$myresult = $this->user_model->user_privileges($this->user);
			if ($myresult['admin'] == 'n') 
			{
				echo lang('youmusthaveadmin')."<br>";
				exit; 
			}
		}

		// select the path_to_ccfile from settings
		$path_to_ccfile = $this->settings_model->get_path_to_ccfile();

		$myfile = "$path_to_ccfile/$filename";

		// OPEN THE FILE AND PROCESS IT
		$data = file_get_contents($myfile); // Read the file's contents

		force_download($filename, $data); 
	}

}
/* End of file tools/dashboard */
/* Location: ./application/controllers/tools/dashboard.php */
