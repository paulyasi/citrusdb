<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * Settings class gives you access to the settings table info
 * that control overall settings for citrus
 * 
 * @author pyasi
 *
 */

class Settings_Model extends CI_Model
{
	function __construct()
    {
        parent::__construct();
    }	

	
	/*
	 * -------------------------------------------------------------------------
	 *  return the dependent cancel url
	 * -------------------------------------------------------------------------
	 */
	function dependent_cancel_url()
	{		
		// get the dependent_cancel_url from the settings table
		$query = "SELECT dependent_cancel_url FROM settings WHERE id = 1";
		$result = $this->db->query($query) or die ("$l_queryfailed");
		$myresult = $result->row();
		return $myresult->dependent_cancel_url;
	}


	/*
	 * ------------------------------------------------------------------------
	 *  get the path to the location to save the cc files and other files
	 * ------------------------------------------------------------------------
	 */
	function get_path_to_ccfile()
	{
		// get the path_to_citrus
		$query = "SELECT path_to_ccfile FROM settings WHERE id = 1";
		$result = $this->db->query($query) or die ("query failed");
		$myresult = $result->row_array();

		return $myresult['path_to_ccfile'];
	}


	/*
	 * ------------------------------------------------------------------------
	 *  get the default group setting
	 * ------------------------------------------------------------------------
	 */
	function get_default_group()
	{
		// check if there is a default_group setup
		$query = "SELECT default_group FROM settings WHERE id = 1";
		$result = $this->db->query($query) or die ("get default group query failed");
		$myresult = $result->row_array();
		return $myresult['default_group'];
	}


	function get_default_shipping_group()
	{
		$query = "SELECT default_shipping_group FROM settings WHERE id = '1'";
		$result = $this->db->query($query) or die ("default shipping query failed");
		$myresult = $result->row_array();
		return $myresult['default_shipping_group'];  
	}
}
