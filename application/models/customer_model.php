<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * Customer model access to customer table data
 * 
 * @author pyasi
 *
 */

class Customer_Model extends CI_Model
{
	function __construct()
    {
        parent::__construct();
    }
    
    function sidebar($account_number)
    {
    	// show customer account number, name, and company for sidebar
		$query = "SELECT name,company FROM customer ".
	  		"WHERE account_number = $this->account_number";
		$result = $this->db->query($query) or die ("$l_queryfailed");
		$myresult = $result->row();
		$data['account_number'] = $this->account_number;
		$data['acct_name'] = $myresult->name;
		$data['acct_company'] = $myresult->company;
		
		return $data;
    }
	
}