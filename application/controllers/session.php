<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Session extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		
		$this->load->library('session');
		$this->load->model('user', '', true);		
	}
	
	function login()
	{
		// kick you out if you have 5 failed logins from the same ip
		if ($this->user->checkfailures()) 
		{
  			echo "Login Failure.  Please See Administrator";
  			die;
		}
		
		$this->load->view('loginform');
	}
	
	function auth()
	{		
		
		$username = $this->input->post('user_name');
		$password = $this->input->post('password');
		
		if ($this->user->user_login($username,$password)) 
		{	
			$newsession = array(
                   'user_name'  => $username,
					'account_number' => 1,
                   'logged_in' => TRUE
               );

			$this->session->set_userdata($newsession);
			
			redirect ('/');	
		} 
		else
		{
			redirect('session/login');
		}
	}
	
	function logout()
	{
		$this->session->sess_destroy();
		
		redirect('/');
	}
			
}