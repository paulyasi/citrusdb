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
    
	function recently_viewed($user)
	{
		$query = "SELECT a.account_number, c.name FROM activity_log a 
			LEFT JOIN customer c ON c.account_number = a.account_number 
			WHERE a.user = '$user' AND activity_type = 'view' 
			AND record_type = 'customer' ORDER BY datetime DESC limit 10";
		
		$result = $this->db->query($query) or die ("$l_queryfailed");

		return $result;
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