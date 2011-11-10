<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * Admin class to make database queries that perform admin tools functions
 * 
 * @author pyasi
 *
 */

class Admin_Model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}	


	/*
	 * -------------------------------------------------------------------------
	 * 	get the variables out of the general configuration table
	 * -------------------------------------------------------------------------
	 */
	function get_organization($id)
	{
		$query = "SELECT * FROM general WHERE id = $id";
		$result = $this->db->query($query) or die ("get organization query failed");
		return $result->row_array();
	}


	function update_organization($id, $org_data)
	{
		$this->db->where('id', $id);
		$this->db->update('general', $org_data);
	}


	function add_organization()
	{
		$query = "INSERT INTO general (org_name) VALUES ('".lang('new')."')";
		$result = $this->db->query($query) or die ("add organization query failed");
		return $this->db->insert_id();
	}


	/*
	 * ------------------------------------------------------------------------
	 *  show all the organizations that can be edited
	 * ------------------------------------------------------------------------
	 */
	function org_list()
	{
		$query = "SELECT id,org_name from general";
		$result = $this->db->query($query) or die ("org list query failed");
		return $result->result_array();
	}


	/*
	 * ------------------------------------------------------------------------
	 *  get the variables out of the id 1 settings configuration table
	 * ------------------------------------------------------------------------
	 */
	function get_settings()
	{
		$query = "SELECT * FROM settings WHERE id = 1";
		$result = $this->db->query($query) or die ("get settings query failed");
		return $result->row_array();
	}


	/*
	 * ------------------------------------------------------------------------
	 *  update the settings table with new input
	 * ------------------------------------------------------------------------
	 */
	function update_settings($settings_array)
	{
		$this->db->where('id', 1);
		$this->db->update('settings', $settings_array);
	}


	/*
	 * ------------------------------------------------------------------------
	 *  get the list of users from the table
	 * ------------------------------------------------------------------------
	 */
	function get_users()
	{
		$query = "SELECT * FROM user ORDER BY username";
		$result = $this->db->query($query) or die ("get users query failed");
		return $result->result_array();
	}


	/*
	 * ------------------------------------------------------------------------
	 * get the list of users from the table
	 * ------------------------------------------------------------------------
	 */
	function get_groups()
	{
		$query = "SELECT * FROM groups ORDER BY groupname";
		$result = $this->db->query($query) or die ("get groups query failed");
		return $result->result_array();
	}


	function get_billing_types()
	{
		$query = "SELECT * FROM billing_types ORDER BY name";
		$result = $this->db->query($query) or die ("get billing types query failed");
		return $result->result_array();
	}


	/*
	 * ------------------------------------------------------------------------
	 *  add a billing type
	 * ------------------------------------------------------------------------
	 */
	function add_billing_type($name, $frequency, $method)
	{
		$query = "INSERT INTO billing_types (name,frequency,method) VALUES (?,?,?)";
		$result = $this->db->query($query, array($name, $frequency, $method)) 
			or die ("add billing type query failed");
	}


	/*
	 * ------------------------------------------------------------------------
	 *  remove billing type
	 * ------------------------------------------------------------------------
	 */
	function remove_billing_type($typeid)
	{
		$query = "DELETE FROM billing_types WHERE id = ?";
		$result = $this->db->query($query, array($typeid)) or die ("remove billing type query failed");
	}


	function get_master_services()
	{
        $query = "SELECT * FROM master_services ORDER BY category, pricerate, service_description";
		$result = $this->db->query($query) or die ("master services query failed");
		return $result->result_array();
	}

	function add_master_service($servicearray)
	{
		$this->db->insert('master_services', $servicearray);
	}

	function get_service_info($sid)
	{
		$query = "SELECT * FROM master_services WHERE id = ?";
		$result = $this->db->query($query, array($sid)) or die ("get service info query failed");
		return $result->row_array();
	}

	function update_service_info($id, $service_data)
	{
		$this->db->where('id', $id);
		$this->db->update('master_services', $service_data);
	}


	/*
	 * ------------------------------------------------------------------------
	 *  print the list of linked services        
	 * ------------------------------------------------------------------------
	 */
	function linked_services()
	{
		$query = "SELECT mfrom.id mfrom_id, ".
			"mfrom.service_description mfrom_description, mto.id mto_id, ".
			"mto.service_description mto_description, ".
			"l.linkfrom, l.linkto ".
			"FROM linked_services l ".
			"LEFT JOIN master_services mfrom ON mfrom.id = l.linkfrom ".
			"LEFT JOIN master_services mto ON mto.id = l.linkto";
		$result = $this->db->query($query) or die ("linked services query failed");

		return $result->result_array();
	}


	function remove_service_link($linkfrom, $linkto)
	{
		// remove the link
		$query = "DELETE FROM linked_services WHERE linkfrom = ? AND linkto = ? LIMIT 1";
		$result = $this->db->query($query, array($linkfrom, $linkto)) or die ("remove link queryfailed");
	} 

	function add_service_link($linkfrom, $linkto)
	{
		// add a link
		$query = "INSERT INTO linked_services (linkfrom,linkto) VALUES (?,?)";
		$result = $this->db->query($query, array($linkfrom, $linkto)) or die ("add link queryfailed");
	}


	/*
	 * ------------------------------------------------------------------------
	 *  Get a list of unique options_tables named in the master_services table
	 * ------------------------------------------------------------------------
	 */
	function options_tables()
	{
		$query = "SELECT DISTINCT options_table FROM master_services";
		$result = $this->db->query($query) or die ("options tables query failed");
		return $result->result_array();
	}

	/*
	 * ------------------------------------------------------------------------
	 *  create a table, then go to the editoptions.php file		
	 * ------------------------------------------------------------------------
	 */
	function create_options_table($tablename)
	{
		// load the dbforge for table creation
		$this->load->dbforge();

		// put an id field into it by default
		$this->dbforge->add_field('id');
		
		// and a user_services id field
		$this->dbforge->add_field("user_services int(10) NOT NULL");

		// now finally create the table
		$this->dbforge->create_table($tablename);
	}

	function get_tax_rates()
	{
		$query = "SELECT * FROM tax_rates";
		$result = $this->db->query($query) or die ("get tax rates query failed");
		return $result->result_array();
	}

	
	function add_tax_rate($description, $rate, $if_field, $if_value, $percentage_or_fixed)
	{
		$query = "INSERT INTO tax_rates (description,rate,if_field,if_value,".
			"percentage_or_fixed) VALUES (?,?,?,?,?)";

		$result = $this->db->query($query, array($description, $rate, $if_field, 
					$if_value, $percentage_or_fixed)) or die ("add tax rate queryfailed");
	}

	function delete_tax_rate($id)
	{
		$query = "DELETE FROM tax_rates WHERE id = ?";
		$result = $this->db->query($query, array($id)) or die ("delete tax rate query failed");	
	}


}
/* end admin_model.php */
