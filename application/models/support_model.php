<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * Support class to show support tickets to users
 * 
 * @author pyasi
 *
 */

class Support_Model extends CI_Model
{
	function __construct()
    {
        parent::__construct();
    }


	/*
	 * ---------------------------------------------------------------------------
	 * query the customer_history for the specific account_number
	 * ---------------------------------------------------------------------------
	 */
	function customer_history($account_number)
	{
		$query = "SELECT  ch.id, ch.creation_date, ".
			"ch.created_by, ch.notify, ch.status, ch.description, ch.linkname, ".
			"ch.linkname, ch.linkurl, ch.user_services_id, us.master_service_id, ".
			"ms.service_description FROM customer_history ch ".
			"LEFT JOIN user_services us ON us.id = ch.user_services_id ".
			"LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
			"WHERE ch.account_number = ? ORDER BY ch.id DESC LIMIT 25";
		$result = $this->db->query($query, array($account_number))
			or die ("customer_history queryfailed");
		
		return $result;
	}

	
	/*
	 * ------------------------------------------------------------------------
	 *  get customer history notes for just this service
	 * ------------------------------------------------------------------------
	 */
	function service_history($userserviceid)
	{
		$query = "SELECT  ch.id, ch.creation_date, ".
			"ch.created_by, ch.notify, ch.status, ch.description, ch.linkname, ".
			"ch.linkname, ch.linkurl, ch.user_services_id, us.master_service_id, ".
			"ms.service_description FROM customer_history ch ".
			"LEFT JOIN user_services us ON us.id = ch.user_services_id ".
			"LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
			"WHERE ch.user_services_id = ? ORDER BY ch.creation_date DESC";
		$result = $this->db->query($query, array($userserviceid)) 
			or die ("service history queryfailed");
		return $result->result_array();
	}


	/*
	 * -------------------------------------------------------------------------
	 *  query the customer_history for the number of 
	 *  waiting messages sent to that user
	 * -------------------------------------------------------------------------
	 */
	function user_count($user)
	{
		$supportquery = "SELECT id, DATE_FORMAT(creation_date, '%Y%m%d%H%i%s') AS mydatetime ".
			"FROM customer_history WHERE notify = ? ".
			"AND status = \"not done\" AND date(creation_date) <= CURRENT_DATE ORDER BY id DESC";
		$supportresult = $this->db->query($supportquery, array($user))
			or die ("user_count queryfailed");
		
		$num_rows = $supportresult->num_rows();

		if ($num_rows > 0) 
		{
			$mysupportresult = $supportresult->row();
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
			"FROM customer_history WHERE notify = ? ".
			"AND status = \"not done\" AND date(creation_date) <= CURRENT_DATE ORDER BY id DESC";
		$gpresult = $this->db->query($query, array($groupname))
			or die ("group_count queryfailed");

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


	/*
	 * -------------------------------------------------------------------------
	 *  generic ticket creation function
	 * -------------------------------------------------------------------------
	 */
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
					"VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

				$result = $this->db->query($query, array($reminderdate,
														 $user,
														 $notify,
														 $account_number,
														 $status,
														 $description,
														 $linkurl,
														 $linkname,
														 $user_services_id))
					or die ("create_ticket query failed");				
			} 
			else 
			{
				$query = "INSERT into customer_history ".
					"(creation_date, created_by, notify, account_number,".
					"status, description, linkurl, linkname) ".
					"VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

				$result = $this->db->query($query, array($reminderdate,
														 $user,
														 $notify,
														 $account_number,
														 $status,
														 $description,
														 $linkurl,
														 $linkname))
					or die ("create_ticket query failed");
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
					"VALUES (CURRENT_TIMESTAMP, ?, ?, ?,?, ?, ?, ?, ?)";
				
				$result = $this->db->query($query, array($user,
														 $notify,
														 $account_number,
														 $status,
														 $description,
														 $linkurl,
														 $linkname,
														 $user_services_id))
					or die ("create_ticket query failed");				
			} 
			else 
			{
				$query = "INSERT into customer_history ".
					"(creation_date, created_by, notify, account_number,".
					"status, description, linkurl, linkname) ".
					"VALUES (CURRENT_TIMESTAMP, ?, ?, ?, ?, ?, ?, ?)";
				
				$result = $this->db->query($query, array($user,
														 $notify,
														 $account_number,
														 $status,
														 $description,
														 $linkurl,
														 $linkname))
					or die ("create_ticket query failed");
			}
		}

		$ticketnumber = $this->db->insert_id();

		$url = $this->config->item('base_url')."/index.php/support/editticket/$ticketnumber";
		$message = "$notify: $description $url";

		// if the notify is a group or a user, if a group, then get all the 
		// users and notify each individual
		$query = "SELECT * FROM groups WHERE groupname = ?";
		$result = $this->db->query($query, array($notify))
			or die ("create_ticket group Query Failed");

		if ($result->num_rows() > 0) 
		{
			// we are notifying a group of users
			foreach ($result->result_array() as $myresult) 
			{
				$groupmember = $myresult['groupmember'];
				$this->enotify($groupmember, $message, $ticketnumber, $user, 
						$notify, $description);
			} // end while    
		} 
		else 
		{
			// we are notifying an individual user
			if ($notify <> '')
			{
				$this->enotify($notify, $message, $ticketnumber, $user, $notify, 
						$description);
			}
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
		if ($user == 'system')
		{
			// an automatic script is running to set some default user info
			$email = 'system@localhost';
			$screenname = '';
			$email_notify = 'n';
			$screenname_notify = 'n';
		}
		else
		{
			// get the real user info
			$query = "SELECT email,screenname,email_notify,screenname_notify ".
					"FROM user WHERE username = ?";
			$result = $this->db->query($query, array($user))
			or die ("select screename queryfailed");
			$myresult = $result->row_array();
			
			$email = $myresult['email'];
			$screenname = $myresult['screenname'];
			$email_notify = $myresult['email_notify'];
			$screenname_notify = $myresult['screenname_notify'];
		}		

		// if they have specified a screenname then send them a jabber notification
		if (($this->config->item('xmpp_server')) && ($screenname) && ($screenname_notify == 'y'))
		{
			include 'libraries/XMPPHP/XMPP.php';

			// edit this to use database jabber user defined in config file
			$conn = new XMPPHP_XMPP("$xmpp_server", 5222, "$this->config->item('xmpp_user')",
									"$this->config->item('xmpp_password')", 'xmpphp',
									"$this->config->item('xmpp_domain')",
									$printlog=false, $loglevel=XMPPHP_Log::LEVEL_INFO);

			try
			{
				$conn->connect();
				$conn->processUntil('session_start');
				$conn->presence();
				$conn->message("$screenname", "$message");
				$conn->disconnect();
			}
			catch(XMPPHP_Exception $e)
			{
				//die($e->getMessage());
				$xmppmessage = $e->getMessage();
				echo "$xmppmessage";
			}
			
		}

		// if they have specified an email then send them an email notification
		if (($email) && ($email_notify == 'y'))
		{
			// HTML Email Headers
			$to = $email;
			// truncate the description to fit in the subject
			$description = substr($description, 0, 40);    
			$subject = lang('ticketnumber') . "$ticketnumber" . lang('to') . ": $tousergroup ".
				lang('from') . ": $fromuser $description";
			mail ($to, $subject, $message);

		}
	}

	function get_ticket($id)
	{
		// get the ticket info to edit
		$query = "SELECT ch.id ch_id, ch.creation_date ch_creation_date, ".
			"ch.created_by ch_created_by, ch.notify ch_notify, ".
			"ch.account_number ch_account_number, ch.status ch_status, ".
			"ch.description ch_description, ch.linkname, ch.linkurl, c.name c_name, ".
			"ch.user_services_id ch_user_services_id, ch.closed_by ch_closed_by, ch.closed_date ch_closed_date, ".
			"ms.service_description service_description ".
			"FROM customer_history ch ".
			"LEFT JOIN customer c ON c.account_number = ch.account_number ".
			"LEFT JOIN user_services us ON us.id = ch.user_services_id ".
			"LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
			"WHERE ch.id = ?";
		$result = $this->db->query($query, array($id))
			or die ("get_ticket query failed");
		$myresult = $result->row_array();

		$data['id'] = $myresult['ch_id'];
		$data['creation_date'] = $myresult['ch_creation_date'];
		$data['created_by'] = $myresult['ch_created_by'];
		$data['notify'] = $myresult['ch_notify'];
		$data['accountnum'] = $myresult['ch_account_number'];
		$data['status'] = $myresult['ch_status'];
		$data['description'] = $myresult['ch_description'];
		$data['name'] = $myresult['c_name'];
		$data['linkname'] = $myresult['linkname'];
		$data['linkurl'] = $myresult['linkurl'];
		$data['serviceid'] = $myresult['ch_user_services_id'];
		$data['closed_by'] = $myresult['ch_closed_by'];
		$data['closed_date'] = $myresult['ch_closed_date'];
		$data['service_description'] = $myresult['service_description'];

		return $data;
	}

	
	function get_sub_history($id)
	{
		// print the current notes attached to this item
		//$query = "SELECT * FROM sub_history WHERE customer_history_id = ?";
		$query = "SELECT month(creation_date) as month, day(creation_date) as day, ".
			"hour(creation_date) as hour, ".
			"LPAD(minute(creation_date),2,'00') as minute, ".
			"creation_date, created_by, description FROM sub_history ".
			"WHERE customer_history_id = ?";
		$result = $this->db->query($query, array($id))
			or die ("sub_history query failed");
		
		return $result->result_array();
	}


	function update_ticket($id, $notify, $status, $description, $reminderdate, 
			$user_services_id, $oldstatus, $addnote)
	{
		$data = array(
				'notify' => $notify,
				'status' => $status,
				'description' => $description,
				);

		// check if user_services_id is not empty and not zero before input it
		if (($user_services_id <> '') AND ($user_services_id <> 0)) 
		{
			$data['user_services_id'] = $user_services_id;
		}

		// check if reminder date exists, if so, make that the creation date
		if ($reminderdate)
		{
			$data['creation_date'] = $reminderdate;
		}

		// if the oldstatus changed from not done or pending to completed
		if ((($oldstatus == "not done") OR ($oldstatus == "pending"))
				AND ($status == "completed")) 
		{
			$data['closed_by'] = $this->user;
			$data['closed_date'] = date('Y-m-d H:i:s');
		}

		// update the customer history table with data
		$this->db->where('id', $id);
		$this->db->update('customer_history', $data);

		// if there is a new note added, put that into the sub_history
		if ($addnote) 
		{
			$query = "INSERT sub_history SET customer_history_id = ?, ".
				"creation_date = CURRENT_TIMESTAMP, created_by = ?, description = ?";
			$result = $this->db->query($query, array($id, $this->user, $addnote)) 
				or die ("update_ticket sub_history insert queryfailed");

			// TODO: send email/xmpp notification if new note added to notify user
			$url = "$this->url_prefix/index.php/support/editticket/$id";
			$message = "$notify: $addnote $url";

			// if the notify is a group or a user, if a group, then get all the users and notify each individual
			$query = "SELECT * FROM groups WHERE groupname = ?";
			$result = $this->db->query($query, array($notify)) or die ("update_ticket Group Query Failed");

			if ($result->num_rows() > 0) 
			{
				// we are notifying a group of users
				foreach ($result->result_array() AS $myresult) 
				{
					$groupmember = $myresult['groupmember'];
					$this->enotify($groupmember, $message, $id, $user, $notify, $addnote);
				} // end while    
			} 
			else 
			{
				// we are notifying an individual user
				$this->enotify($notify, $message, $id, $user, $notify, $addnote);
			} // end if result    

		} // end if addnote

	}
	

	function list_tickets($notify, $showall = NULL)
	{
		if ($showall) 
		{
			$query = "SELECT  ch.id, ch.creation_date, ch.notify, ch.created_by, ".
				"DATE_FORMAT(creation_date, '%Y%m%d%H%i%s') AS mydatetime, ".
				"ch.account_number, ch.status, ch.description, ch.linkname, ".
				"ch.linkurl, ch.user_services_id, ms.service_description, c.name ".
				"FROM customer_history ch ".
				"INNER JOIN customer c ON ch.account_number = c.account_number ".
				"LEFT JOIN user_services us ON us.id = ch.user_services_id ".
				"LEFT JOIN master_services ms ON ms.id = us.master_service_id ".      
				"WHERE notify = ? ORDER BY creation_date DESC LIMIT 50";
			$result = $this->db->query($query, array($notify)) 
				or die ("list tickets query failed");
			
			return $result->result_array();
		} 
		else 
		{
			$query = "SELECT  ch.id, ch.creation_date, ch.notify, ch.created_by, ".
				"DATE_FORMAT(creation_date, '%Y%m%d%H%i%s') AS mydatetime, ".
				"ch.account_number, ch.status, ch.description, ch.linkname, ".
				"ch.linkurl, ch.user_services_id, ms.service_description, c.name ".
				"FROM customer_history ch ".
				"INNER JOIN customer c ON ch.account_number = c.account_number ".
				"LEFT JOIN user_services us ON us.id = ch.user_services_id ".
				"LEFT JOIN master_services ms ON ms.id = us.master_service_id ".      
				"WHERE notify = ? AND status IN ('not done','pending') ".
				"AND to_days(now()) >= to_days(creation_date) ".
				"ORDER BY creation_date DESC";
			$result = $this->db->query($query, array($notify)) 
				or die ("list tickets query failed");
			
			return $result->result_array();
		}
	}


	/*
	 * -------------------------------------------------------------------------
	 * mark the customer_history id as pending
	 * -------------------------------------------------------------------------
	 */
	function pending_ticket($id)
	{
		$query = "UPDATE customer_history SET status = 'pending' WHERE id = ?";
		$result = $this->db->query($query, array($id)) or die ("pending ticket query failed");
	}


	/*
	 * -------------------------------------------------------------------------
	 * make the customer_history id as completed
	 * -------------------------------------------------------------------------
	 */
	function complete_ticket($id)
	{
		$mydate = date("Y-m-d H:i:s");
		$query = "UPDATE customer_history SET status = 'completed', ".
			"closed_by = ?, closed_date = ? WHERE id = ?";
		$result = $this->db->query($query, array($this->user, $mydate, $id)) 
			or die ("complete ticket query failed");

	}


}

/* end support_model */
