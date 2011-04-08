<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends App_Controller {

	/**
	 * Dashboard overview of everything
	 */
	public function index()
	{
		// TODO: put header here
		$this->load->view('header');
		
		// show recently viewed customers
		$this->load->model('log');
		$data['recent'] = $this->log->recently_viewed($this->session->userdata('user_name'));
		$this->load->view('recently_viewed', $data);
		
		// TODO: replace this dashboard view with messages tabnav
		//$this->load->view('dashboard');
		
		// TODO: put search boxes
		// TODO: put footer here
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */