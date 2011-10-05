<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * Settings class gives you access to the settings table info
 * that control overall settings for citrus
 * 
 * @author pyasi
 *
 */

class General_Model extends CI_Model
{
	function __construct()
    {
        parent::__construct();
    }	

	
	/*
	 * -------------------------------------------------------------------------
	 *  return the list of organizations from the general configuration
	 * -------------------------------------------------------------------------
	 */
	function list_organizations()
	{		
		$query = "SELECT id,org_name FROM general";
		$result = $this->db->query($query) or die ("$l_queryfailed");
	
		return $result->result_array();
	}

}
