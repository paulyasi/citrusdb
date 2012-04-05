<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * Update model class includes functions used by statusupdate and weekendupdate
 * 
 * @author pyasi
 *
 */

class Update_Model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}	


	/*
	 * ------------------------------------------------------------------------
	 *  create entries for the ADD status
	 * ------------------------------------------------------------------------
	 */
	function add($handle, $activatedate)
	{
		// get the list of new services added today
		$result = $this->service_model->new_services_today($today);

		$adds = 0;

		// loop through results and print out each
		foreach($result AS $myresult) 
		{
			$user_services_id = $myresult['u_id'];
			$service_description = $myresult['m_service_description'];
			$account_number = $myresult['u_ac'];
			$options_table = $myresult['m_options_table'];
			$activation_string = $myresult['m_activation_string'];
			$customer_name = $myresult['c_name'];
			$customer_company = $myresult['c_company'];
			$customer_street = $myresult['c_street'];
			$customer_city = $myresult['c_city'];
			$customer_state = $myresult['c_state'];
			$customer_country = $myresult['c_country'];
			$customer_zip = $myresult['c_zip'];
			$category = $myresult['m_category'];
			$removed = $myresult['u_rem'];

			// query this with the option_table for that service to get the 
			// activation_string variables
			$mystring = split(",", $activation_string);

			$newline = "\"ADD\",\"$category\",\"$customer_name\",\"$service_description\"";

			if ($options_table <> '') 
			{
				$myoptresult = $this->service_model->options_values($user_services_id, $optionstable);

				$fields = $this->schema_model->columns($this->db->database, $optionstable);

				$i = 0;        
				$pstring = "";	
				foreach($fields->result() as $v) 
				{                
					//echo "Name: $v->name ";
					$fieldname = $v->COLUMN_NAME;

					//check matching fieldname in the options table
					foreach($mystring as $s) 
					{
						if($fieldname == $s) 
						{
							//$pstring = $pstring.$s;
							$myline = $myoptresult[$s];
							$newline .= ",\"$myline\"";
						}	
					}

				} //endforeach
			} //endif

			$newline .= "\n"; // end the line

			// write the file if the service has not been removed
			if ($removed <> 'y') {
				fwrite($handle, $newline); // write to the file
				$adds++;
			}

		} //endwhile

	}



	/*
	 * ------------------------------------------------------------------------
	 *  set the ENABLE status
	 * ------------------------------------------------------------------------
	 */
	function enable($handle, $activatedate)
	{
		/*-------------------------------------------------------------------*/
		// ENABLE
		//
		// if the account has an authorized status payment_history today and 
		// it's previous payment_history was bad: 
		// (turnedoff, canceled, cancelwfee, collections)
		// or if they are in waiting status today
		/*-------------------------------------------------------------------*/

		// select all the accounts with a payment_history of today
		$result->$this->billing_model->payment_history_today($today);

		$enables = 0;

		foreach ($result AS $myresult) 
		{
			// go through those accounts and find out which one has 
			//a previous payment_history that was declined, 
			//turnedoff, collections or canceled	

			$billingid = $myresult['billing_id'];	
			$account_number = $myresult['account_number'];

			$query = "SELECT * FROM payment_history ".
				"WHERE billing_id = ? ORDER BY id DESC LIMIT 1,1";
			$historyresult = $this->db->query($query, array($billingid)) or die ("select payment_history queryfailed");
			$myhistoryresult = $historyresult->row_array();
			$secondstatus = $myhistoryresult['status'];

			if ($secondstatus == "turnedoff" 
					OR $secondstatus == "waiting" 
					OR $secondstatus == "collections" 
					OR $secondstatus == "cancelwfee" 
					OR $secondstatus == "canceled") 
			{
				// enable services for the account

				$query = "SELECT u.id u_id, u.account_number u_ac, ".
					"u.master_service_id u_master_service_id, ".
					"u.billing_id u_bid, ".
					"u.start_datetime u_start, u.removed u_rem, ".
					"u.usage_multiple u_usage, ".
					"m.service_description m_service_description, ".
					"m.id m_id, m.pricerate m_pricerate, ".
					"m.frequency m_freq, ".
					"m.activation_string m_activation_string, ".
					"m.category m_category, m.activate_notify m_activate_notify, ".
					"m.options_table m_options_table, c.name c_name, ".
					"c.company c_company, c.street c_street, c.city c_city, ".
					"c.state c_state, c.country c_country, ".
					"c.zip c_zip, c.phone c_phone, ".
					"c.contact_email c_contact_email ".
					"FROM user_services u ".
					"LEFT JOIN master_services m ON m.id = u.master_service_id ".
					"LEFT JOIN customer c ON c.account_number = u.account_number ".
					"WHERE c.account_number = ?";
				$serviceresult = $this->db->query($query, array($account_number)) or die ("queryfailed");

				// loop through results and print out each
				foreach ($serviceresult->result_array() AS $myserviceresult) 
				{
					$user_services_id = $myserviceresult['u_id'];
					$service_description = $myserviceresult['m_service_description'];
					$account_number = $myserviceresult['u_ac'];
					$options_table = $myserviceresult['m_options_table'];
					$activation_string = $myserviceresult['m_activation_string'];
					$customer_name = $myserviceresult['c_name'];
					$customer_company = $myserviceresult['c_company'];
					$customer_street = $myserviceresult['c_street'];
					$customer_city = $myserviceresult['c_city'];
					$customer_state = $myserviceresult['c_state'];
					$customer_country = $myserviceresult['c_country'];
					$customer_zip = $myserviceresult['c_zip'];
					$category = $myserviceresult['m_category'];
					$removed = $myserviceresult['u_rem']; // y or n
					$activate_notify = $myserviceresult['m_activate_notify'];

					// query this with the option_table for 
					// that service to get the 
					// activation_string variables
					$mystring = split(",", $activation_string);

					$newline = "\"ENABLE\",\"$category\",\"$customer_name\",\"$service_description\"";

					if ($options_table <> '') 
					{
						$myoptresult = $this->service_model->options_values($user_services_id, $optionstable);

						$fields = $this->schema_model->columns($this->db->database, $optionstable);

						$i = 0;        
						$pstring = "";	
						foreach($fields->result() as $v) 
						{                
							//echo "Name: $v->name ";
							$fieldname = $v->COLUMN_NAME;

							//check matching fieldname in the options table
							foreach($mystring as $s) 
							{
								if($fieldname == $s) 
								{
									//$pstring = $pstring.$s;
									$myline = $myoptresult[$s];
									$newline .= ",\"$myline\"";
								}	
							}

						} //endforeach
					} //endif

					$newline .= "\n"; // end the line

					// write to the file if the service has not already been removed
					if ($removed <> 'y') 
					{
						fwrite($handle, $newline); // write to the file
						$enables++;

						// CREATE TICKET TO the activate_notify user if there is one
						if ($activate_notify) 
						{
							$notify = "$activate_notify";
							$description = "ENABLE $category $customer_name $service_description";
							$status = "not done";
							$this->support_model->create_ticket($this->user, $notify, $account_number, $status,
									$description, NULL, NULL, NULL, $user_services_id);
						}

					}
				} //endwhile
			} // endif
		} //endwhile

	}


}
