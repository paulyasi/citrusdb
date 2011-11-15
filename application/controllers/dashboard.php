<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends App_Controller {

	/**
	 * Dashboard overview of everything
	 */
	public function index() 
	{

		// show the header common to all dashboard/tool views
		$this->load->view('dashboard_header_view');

		$this->load->view('searchbox_view');

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
