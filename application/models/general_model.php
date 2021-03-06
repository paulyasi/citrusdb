<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * General model class gives you access to the general table
 * that control organization settings for citrus
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
		$result = $this->db->query($query) or die ("list organizations query failed");
	
		return $result->result_array();
	}

	
	/*
	 * -------------------------------------------------------------------------
	 *  return the list of name of the organization
	 * -------------------------------------------------------------------------
	 */
	function get_org_name($id)
	{
		// get the organization info
		$query = "SELECT org_name FROM general WHERE id = ? LIMIT 1";
		$orgresult = $this->db->query($query, array($id)) or die ("queryfailed");
		$myorgresult = $orgresult->row_array();
		return $myorgresult['org_name']; 
	}
}
