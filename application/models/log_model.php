<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * Log class keeps track of user activity for auditing and security
 * 
 * @author pyasi
 *
 */

class Log_Model extends CI_Model
{
	
	function __construct()
    {
        parent::__construct();
    }

	
	function recently_viewed($user)
	{
		$query = "SELECT a.account_number, c.name FROM activity_log a 
			LEFT JOIN customer c ON c.account_number = a.account_number 
			WHERE a.user = ? AND activity_type = 'view' 
			AND record_type = 'customer' ORDER BY datetime DESC limit 10";
		
		$result = $this->db->query($query, array($user))
			or die ("recently_viewed queryfailed");

		return $result;
	}


	function activity_on_date($date)
	{
		// Select activity log records for citrusdb users from a specific day
		$query = "SELECT date(datetime) AS date, time(datetime) AS time, user, ".
			"ip_address, account_number, activity_type, record_type, record_id, result ".
			"FROM activity_log WHERE date(datetime) = '$date'";

		$result = $this->db->query($query) or die ("queryfailed");

		return $result->result_array();
	}
	
    
	function activity($user,$account_number,$activity_type,
		$record_type,$record_id,$result,$ipaddress)
	{
		$sys_dbtype = $this->db->dbdriver;
  
		// make an entry into the activity_log table regarding the activity passed
		// to this function
		$datetime = date("Y-m-d H:i:s");

		// take advantage of mysql insert delayed to speed up this function if we can
		if ($sys_dbtype == "mysql") 
		{  
			$query = "INSERT DELAYED INTO activity_log ".
				"VALUES (?,?,?,?,?,?,?,?)";
		} 
		else 
		{
			$query = "INSERT INTO activity_log ".
			"VALUES (?,?,?,?,?,?,?,?)";    
		}

		$result = $this->db->query($query, array($datetime,
												 $user,
												 $ipaddress,
												 $account_number,
												 $activity_type,
												 $record_type,
												 $record_id,
												 $result))
			or die ("activity_log insert failed");
  
	} // end log_activity
	
}

/* end log_model */
