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



	// generic ticket creation function
	function create_ticket($user, $notify, $account_number, $status,
			$description, $linkname = NULL, $linkurl = NULL,
			$reminderdate = NULL, $user_services_id = NULL)
	{
		if ($reminderdate) 
		{
			if ($user_services_id) 
			{
				// add ticket to customer_history table
				$query = "INSERT into customer_history ".
					"(creation_date, created_by, notify, account_number,".
					"status, description, linkurl, linkname, user_services_id) ".
					"VALUES ('$reminderdate', '$user', '$notify', '$account_number',".
					"'$status', '$description', '$linkurl', '$linkname', '$user_services_id')";
			} 
			else 
			{
				$query = "INSERT into customer_history ".
					"(creation_date, created_by, notify, account_number,".
					"status, description, linkurl, linkname) ".
					"VALUES ('$reminderdate', '$user', '$notify', '$account_number',".
					"'$status', '$description', '$linkurl', '$linkname')";
			}
		} 
		else 
		{
			if ($user_services_id) 
			{
				// add ticket to customer_history table
				$query = "INSERT into customer_history ".
					"(creation_date, created_by, notify, account_number,".
					"status, description, linkurl, linkname, user_services_id) ".
					"VALUES (CURRENT_TIMESTAMP, '$user', '$notify', '$account_number',".
					"'$status', '$description', '$linkurl', '$linkname', '$user_services_id')";
			} 
			else 
			{
				$query = "INSERT into customer_history ".
					"(creation_date, created_by, notify, account_number,".
					"status, description, linkurl, linkname) ".
					"VALUES (CURRENT_TIMESTAMP, '$user', '$notify', '$account_number',".
					"'$status', '$description', '$linkurl', '$linkname')";      
			}
		}

		$result = $this->db->query($query) or die ("create_ticket query failed");
		$ticketnumber = $this->db->insert_id();

		$url = "$this->url_prefix/index.php?load=support&type=module&editticket=on&id=$ticketnumber";
		$message = "$notify: $description $url";

		// if the notify is a group or a user, if a group, then get all the users and notify each individual
		$query = "SELECT * FROM groups WHERE groupname = '$notify'";
		$result = $this->db->query($query) or die ("Group Query Failed");

		if ($result->RowCount() > 0) 
		{
			// we are notifying a group of users
			foreach ($result->result_array() as $myresult) 
			{
				$groupmember = $myresult['groupmember'];
				this->enotify($groupmember, $message, $ticketnumber, $user, $notify, 
						$description);
			} // end while    
		} 
		else 
		{
			// we are notifying an individual user
			this->enotify($notify, $message, $ticketnumber, $user, $notify, 
					$description);
		} // end if result

		return $ticketnumber;

	} // end create_ticket function


	/*
	 * ------------------------------------------------------------------------------
	 *  send notifications to the jabber id or email address
	 * ------------------------------------------------------------------------------
	 */
	function enotify($user, $message, $ticketnumber, $fromuser, $tousergroup, 
			$description)
	{
		$query = "SELECT email,screenname,email_notify,screenname_notify ".
			"FROM user WHERE username = '$user'";
		$result = $this->db->query($query) or die ("select screename queryfailed");
		$myresult = $result->row_array();
		$email = $myresult['email'];
		$screenname = $myresult['screenname'];
		$email_notify = $myresult['email_notify'];
		$screenname_notify = $myresult['screenname_notify'];

		include 'config.inc.php'; // include this here for jabber server config vars

		// if they have specified a screenname then send them a jabber notification
		if (($xmpp_server) && ($screenname) && ($screenname_notify == 'y')) {
			include 'XMPPHP/XMPP.php';

			// edit this to use database jabber user defined in config file
			$conn = new XMPPHP_XMPP("$xmpp_server", 5222, "$xmpp_user", "$xmpp_password", 'xmpphp', "$xmpp_domain", $printlog=false, $loglevel=XMPPHP_Log::LEVEL_INFO);

			try {
				$conn->connect();
				$conn->processUntil('session_start');
				$conn->presence();
				$conn->message("$screenname", "$message");
				$conn->disconnect();
			} catch(XMPPHP_Exception $e) {
				//die($e->getMessage());
				$xmppmessage = $e->getMessage();
				echo "$xmppmessage";
			}
		}

		// if they have specified an email then send them an email notification
		if (($email) && ($email_notify == 'y')) {

			// HTML Email Headers
			$to = $email;
			// truncate the description to fit in the subject
			$description = substr($description, 0, 40);    
			$subject = "$l_ticketnumber$ticketnumber $l_to: $tousergroup $l_from: $fromuser $description";
			mail ($to, $subject, $message);

		}
	}
}
