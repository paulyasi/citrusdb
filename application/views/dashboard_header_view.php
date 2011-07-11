<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// show header first
$this->load->view('header_with_sidebar_view');

// show recently viewed customers using a query to the log
$this->load->model('log_model');
$data['recent'] = $this->log_model->recently_viewed(
$this->session->userdata('user_name')
);
$this->load->view('recently_viewed_view', $data);

$this->load->model('ticket_model');
$this->load->view('messagetabs_view');

$this->load->view('buttonbar_view');

$this->load->view('searchbox_view');

