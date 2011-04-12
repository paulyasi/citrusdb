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