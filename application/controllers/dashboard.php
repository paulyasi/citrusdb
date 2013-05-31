<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends App_Controller {

	/**
	 * Dashboard overview of everything
	 */
	public function index() 
	{
		$this->load->model('user_model');
		$this->load->model('support_model');
		$this->load->model('module_model');

		// show the header common to all dashboard/tool views
		$this->load->view('dashboard_header_view');

		$this->load->view('searchbox_view');

		// include module searches below here
		
		// First check for permissions to view search modules
		$viewable = $this->module_model->module_permission_list($this->user);

		// Search Modules Menu
		$modulelist = $this->module_model->modulelist();
		foreach($modulelist as $myresult) {
			$commonname = $myresult['commonname'];
			$modulename = $myresult['modulename'];

			if (in_array ($modulename, $viewable)) {
				$viewthis = $modulename . '/search_view';
				$this->load->view($viewthis);
			}
		}

		// show html footer
		$this->load->view('html_footer_view');

	}
}

/* End of file dashboard */
/* Location: ./application/controllers/dashboard.php */
