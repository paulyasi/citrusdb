<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * Tickets class to show tickets to users
 * 
 * @author pyasi
 *
 */

class Ticket_Model extends CI_Model
{
	function __construct()
    {
        parent::__construct();
    }
    
	//---------------------------------------------------------------------------
	// query the customer_history for the specific account_number
	//---------------------------------------------------------------------------
	function customer_history($account_number)
	{
		$query = "SELECT  ch.id, ch.creation_date, ".
			"ch.created_by, ch.notify, ch.status, ch.description, ch.linkname, ".
			"ch.linkname, ch.linkurl, ch.user_services_id, us.master_service_id, ".
			"ms.service_description FROM customer_history ch ".
			"LEFT JOIN user_services us ON us.id = ch.user_services_id ".
			"LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
			"WHERE ch.account_number = '$account_number' ORDER BY ch.id DESC LIMIT 25";
		$result = $this->db->query($query) or die ("$l_queryfailed");
		
		return $result;
	}
	
	function customer_sub_history($customer_history_id)
	{
		$query = "SELECT month(creation_date) as month, day(creation_date) as day, ".
    		"hour(creation_date) as hour, LPAD(minute(creation_date),2,'00') as minute, ".
    		"created_by, description FROM sub_history WHERE customer_history_id = $id";
		$subresult = $this->db->query($query) or die ("sub_history $l_queryfailed");
		
		return $subresult;
	}
    
    function user_count($user)
	{
		// query the customer_history for the number of 
		// waiting messages sent to that user
		$supportquery = "SELECT id, DATE_FORMAT(creation_date, '%Y%m%d%H%i%s') AS mydatetime ".
  			"FROM customer_history WHERE notify = '$user' ".
  			"AND status = \"not done\" AND date(creation_date) <= CURRENT_DATE ORDER BY id DESC";
		$supportresult = $this->db->query($supportquery) or die ("$l_queryfailed");

		$num_rows = $supportresult->num_rows();
		if ($num_rows > 0) 
		{
  			$mysupportresult = $supportresult->row;
  			$created = $mysupportresult->mydatetime;
		}
		else
		{
			$created = NULL;
		}
		
		return array('num_rows' => $num_rows, 'created' => $created);
    }
    
    function group_count($groupname)
    {
		$query = "SELECT id, DATE_FORMAT(creation_date, '%Y%m%d%H%i%s') AS mydatetime ".
  		"FROM customer_history WHERE notify = '$groupname' ".
    	"AND status = \"not done\" AND date(creation_date) <= CURRENT_DATE ORDER BY id DESC";
  		$gpresult = $this->db->query($query) or die ("$l_queryfailed");

  		$num_rows = $gpresult->num_rows();
  		if ($num_rows > 0) 
  		{
    		$mygpresult = $gpresult->row();
    		$created = $mygpresult->mydatetime;
  		}
  		else 
  		{
  			$created = NULL;
  		}
  		
  		return array('num_rows' => $num_rows, 'created' => $created);
    }
	
}