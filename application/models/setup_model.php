<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * ----------------------------------------------------------------------------
 *  perform tasks that check the software is installed correctly and 
 *  setup the the initial database schema
 * ----------------------------------------------------------------------------
 */

class Setup_model extends CI_Model
{
	function __construct()
	{
	    parent::__construct();
	}

  public function index()
  {
    /*
     * ------------------------------------------------------------------------------
     *  start the setup of a new installation
     * ------------------------------------------------------------------------------
     */

  }

}
