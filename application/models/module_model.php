<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * Module class for citrus modules like customer, billing, services, and support
 * 
 * @author Paul Yasi and David Olivier
 *
 */

class Module_model extends CI_Model
{
	function __construct()
    {
        parent::__construct();
    }
    
    public function permission($user, $modulename)
    {
		// Check for permissions to view module
    	$groupname = array();
    	$modulelist = array();
    	$query = "SELECT * FROM groups WHERE groupmember = '$user'";
    	$result = $this->db->query($query) or die ("First Permission Query Failed");	
    	foreach ($result->result() as $myresult)
		{
			array_push($groupname,$myresult->groupname);
    	}
    	$groups = array_unique($groupname);
    	array_push($groups,$user);
	
	    $query = "SELECT user,permission FROM module_permissions WHERE modulename = '$modulename'";
	    $result = $this->db->query($query) or die ("Second Permission Query Failed");	
	    foreach ($result->result() as $myresult)
		{
			if (in_array ($myresult->user, $groups))
	        {
	            if ($myresult->permission == 'r')
	            {
	            	return array ('view' => TRUE);
	            }
    	        if ($myresult->permission == 'c')
       	     	{
       	     		return array ('create' => TRUE);
            	}
            	if ($myresult->permission == 'm')
            	{
            		return array ('modify' => TRUE);
            	}
            	if ($myresult->permission == 'd')
            	{
            		return array ('remove' => TRUE);
            	}
            	if ($myresult->permission == 'f')
            	{
                	return array ('view' => TRUE, 'create' => TRUE, 'modify' => TRUE, 'remove' => TRUE);
            	}
        	}
    	}
    } // end permission function

	function permission_error()
	{
		die ("You don't have permission to use this function");
	}
    
    
    public function module_permission_list($user)
    {
    	// get a list of modules we are allowed to view
    	$groupname = array();
    	$modulelist = array();
		$query = "SELECT * FROM groups WHERE groupmember = '$user'";
		$result = $this->db->query($query) or die ("$l_queryfailed");
		foreach($result->result() as $myresult)
		{
			array_push($groupname,$myresult->groupname);
		}
    	$groups = array_unique($groupname);
    	array_push($groups,$this->user);

    	while (list($key,$value) = each($groups))
    	{
        	$query = "SELECT * FROM module_permissions WHERE user = '$value' ";
			$result = $this->db->query($query) or die ("$l_queryfailed");
			foreach($result->result() as $myresult)
			{
        		array_push($modulelist,$myresult->modulename);
    		}
    	}
    	
    	return array_unique($modulelist);
    
    }
    
    public function modulelist()
    {
		$query = "SELECT * FROM modules ORDER BY sortorder";
		$result = $this->db->query($query) or die ("$l_queryfailed");
		
		return $result->result_array();
    }

	/*
	 * ------------------------------------------------------------------------
	 *  insert a new module into the modules table
	 * ------------------------------------------------------------------------
	 */
	public function addmodule($commonname, $modulename, $sortorder)
	{
		$query = "INSERT INTO modules (commonname,modulename,sortorder) ".
			"VALUES (?,?,?)";
		$result = $this->db->query($query, array($commonname, $modulename, $sortorder)) 
			or die ("addmodule query failed");
	}


	/*
	 * ------------------------------------------------------------------------
	 *  get module permission information
	 * ------------------------------------------------------------------------
	 */
	function get_module_permissions($module)
	{
		$query = "SELECT * FROM module_permissions WHERE modulename = ? ".
			"ORDER BY user";
		$result = $this->db->query($query, array($module)) 
			or die ("module permissions query failed");

		return $result->result_array();
	}

}
