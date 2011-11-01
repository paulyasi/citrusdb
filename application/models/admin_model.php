<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * Admin class to make database queries that perform admin tools functions
 * 
 * @author pyasi
 *
 */

class Admin_Model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}	


	/*
	 * -------------------------------------------------------------------------
	 * 	get the variables out of the general configuration table
	 * -------------------------------------------------------------------------
	 */
	function get_organization($id)
	{
		$query = "SELECT * FROM general WHERE id = $id";
		$result = $this->db->query($query) or die ("get organization query failed");
		return $result->row_array();
	}


	function update_organization($id, $org_data)
	{
		$this->db->where('id', $id);
		$this->db->update('general', $org_data);
	}


	function add_organization()
	{
		$query = "INSERT INTO general (org_name) VALUES ('".lang('new')."')";
		$result = $this->db->query($query) or die ("add organization query failed");
		return $this->db->insert_id();
	}


	/*
	 * ------------------------------------------------------------------------
	 *  show all the organizations that can be edited
	 * ------------------------------------------------------------------------
	 */
	function org_list()
	{
		$query = "SELECT id,org_name from general";
		$result = $this->db->query($query) or die ("org list query failed");
		return $result->result_array();
	}


	/*
	 * ------------------------------------------------------------------------
	 *  get the variables out of the id 1 settings configuration table
	 * ------------------------------------------------------------------------
	 */
	function get_settings()
	{
		$query = "SELECT * FROM settings WHERE id = 1";
		$result = $this->db->query($query) or die ("get settings query failed");
		return $result->row_array();
	}


	/*
	 * ------------------------------------------------------------------------
	 *  update the settings table with new input
	 * ------------------------------------------------------------------------
	 */
	function update_settings($settings_array)
	{
		$this->db->where('id', 1);
		$this->db->update('settings', $settings_array);
	}


	/*
	 * ------------------------------------------------------------------------
	 *  get the list of users from the table
	 * ------------------------------------------------------------------------
	 */
	function get_users()
	{
		$query = "SELECT * FROM user ORDER BY username";
		$result = $this->db->query($query) or die ("get users query failed");
		return $result->result_array();
	}
}
