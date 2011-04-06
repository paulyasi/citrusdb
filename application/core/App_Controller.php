<?php
class App_Controller extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		
		// load the session library and url helper
		$this->load->library('session');
		$this->load->helper('url'); 
		
		if(!$this->session->userdata('loggedin'))
		{
			//$this->load->view('loginform');
			redirect('session/login');
			exit;
		} 
	}	
}