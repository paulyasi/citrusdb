<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// show header first
$this->load->view('header_with_sidebar_view');

// show recently viewed customers using a query to the log
$this->load->model('log_model');
$data['recent'] = $this->log_model->recently_viewed(
$this->session->userdata('user_name')
);
$this->load->view('recently_viewed_view', $data);

$data['usergroups'] = $this->user_model->user_groups($this->user);
$this->load->view('messagetabs_view', $data);

$this->load->view('buttonbar_view');

