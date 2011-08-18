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
    
}