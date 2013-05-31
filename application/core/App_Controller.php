<?php
class App_Controller extends CI_Controller
{
    var $user, $account_number, $url_prefix, $ssl_url_prefix;

    function __construct()
    {
        // set the software version number
        $this->softwareversion = "3.0";

        parent::__construct();

        // make sure this is not a cli request, if it is don't load sessions or urls
        if(!$this->input->is_cli_request())
        {
            // load the session library and url helper
            $this->load->library('session');
            $this->load->helper('url'); 

            if(!$this->session->userdata('logged_in'))
            {
                //$this->load->view('loginform');
                redirect('session/login');
                exit;
            } 
            else
            {
                // setup variables that are used everywhere
                $this->user = $this->session->userdata('user_name');
                $this->account_number = $this->session->userdata('account_number');
                $this->url_prefix = $this->config->item('base_url');
                $this->ssl_url_prefix = $this->config->item('ssl_base_url');
            }
        }
    }	
}
