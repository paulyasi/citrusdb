<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends App_Controller {

	/**
	 * Dashboard overview of everything
	 */
	public function index() 
	{
		// show header first
		$this->load->view('header_with_sidebar');

		// show recently viewed customers using a query to the log
		$this->load->model('log_model');
		$data['recent'] = $this->log_model->recently_viewed(
		$this->session->userdata('user_name')
		);
		$this->load->view('recently_viewed', $data);

		$this->load->model('ticket_model');
		$this->load->view('messagetabs');

		$this->load->view('buttonbar');

		$this->load->view('searchbox');

		// include module searches below here
		// TODO: put this into a permission model?
		// First check for permissions to view search modules
		$groupname = array();
		$modulelist = array();
		$query = $this->db->get_where('groups', array('groupmember' => $this->user));

		foreach($query->result() as $myresult) {
			array_push($groupname,$myresult->groupname);
		}
		$groups = array_unique($groupname);
		array_push($groups,$this->user);

		while (list($key,$value) = each($groups)) {
			$query = $this->db->get_where('module_permissions', array('user' => $value));
			foreach($query->result() as $myresult) {
				array_push($modulelist,$myresult->modulename);
			}
		}
		$viewable = array_unique($modulelist);


		// Search Modules Menu

		//$query = "SELECT * FROM modules ORDER BY sortorder";
		//$result = $this->db->query($query) or die ("$l_queryfailed");
		$query = $this->db->order_by('sortorder', "asc")->get('modules');
		foreach($query->result() as $myresult) {
			$commonname = $myresult->commonname;
			$modulename = $myresult->modulename;

			if (in_array ($modulename, $viewable)) {
				$viewthis = $modulename . '/search';
				$this->load->view($viewthis);
			}
		}

		// show html footer
		$this->load->view('html_footer');


	}
}

/* End of file dashboard */
/* Location: ./application/controllers/dashboard.php */