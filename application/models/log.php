<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * Log class keeps track of user activity for auditing and security
 * 
 * @author pyasi
 *
 */

class Log extends CI_Model
{
	function __construct()
    {
        parent::__construct();
    }
    
	function activity_log($user,$account_number,$activity_type,
		$record_type,$record_id,$result)
	{
		$sys_dbtype = $this->db->dbdriver;
  
		// make an entry into the activity_log table regarding the activity passed
		// to this function
		$datetime = date("Y-m-d H:i:s");
		$ip_address = $_SERVER["REMOTE_ADDR"];

		// take advantage of mysql insert delayed to speed up this function if we can
		if ($sys_dbtype == "mysql") 
		{  
			$query = "INSERT DELAYED INTO activity_log ".
				"VALUES ('$datetime','$user','$ip_address',$account_number,".
				"'$activity_type','$record_type','$record_id','$result')";
		} 
		else 
		{
			$query = "INSERT INTO activity_log ".
			"VALUES ('$datetime','$user','$ip_address',$account_number,".
			"'$activity_type','$record_type','$record_id','$result')";    
		}

		$result = $this->db->query($query) or die ("activity_log insert failed");
  
	} // end log_activity
	
}