<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Session extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		
		$this->load->library('session');
	}
	
	function login()
	{
		$this->load->view('loginform');
	}
	
	function auth()
	{
		// load the PasswordHash library file
		// not using the CI loader because it instantiates immediately
		
		$this->load->model('user', '', true);
		
		$username = $this->input->post('user_name');
		$password = $this->input->post('password');
		
		if ($this->user->user_login($username,$password)) 
		{	
			//$this->session->set_userdata('loggedin', true);
			
			//redirect ('/');
			
			echo "Authenticated!";
		} 
		else
		{
			echo "Not Authenticated!";
		}
	}
	
	function logout()
	{
		$this->session->unset_userdata('loggedin');
		
		redirect('/');
	}
			
}