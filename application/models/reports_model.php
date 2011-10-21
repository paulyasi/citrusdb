<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * Reports class to make database queries that create reports tools
 * 
 * @author pyasi
 *
 */

class Reports_Model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}	


	function services_by_org($organization_id)
	{
		// services by organization
		$query = "SELECT u.id u_id, u.account_number u_ac, ".
			"u.master_service_id u_msid, u.billing_id u_bid, ".
			"u.removed u_rem, u.usage_multiple u_usage, ".
			"b.next_billing_date b_next_billing_date, b.id b_id, ".
			"b.billing_type b_type, t.id t_id, t.frequency t_freq, ".
			"t.method t_method, m.service_description m_service_description, ".
			"m.id m_id, m.pricerate m_pricerate, m.frequency m_freq ".
			"FROM user_services u ".
			"LEFT JOIN master_services m ON u.master_service_id = m.id ".
			"LEFT JOIN billing b ON u.billing_id = b.id ".
			"LEFT JOIN billing_types t ON b.billing_type = t.id ".
			"WHERE b.organization_id = '$organization_id' ".
			"AND t.method <> 'free' AND u.removed <> 'y'";

		$result = $this->db->query($query) or die ("Services Query Failed");

		return $result->result_array();

	}


	function taxes_by_org($organization_id)
	{
		$query = "SELECT ts.id ts_id, ts.master_services_id ts_serviceid, ".
			"ts.tax_rate_id ts_rateid, ms.id ms_id, ".
			"ms.service_description ms_description, ms.pricerate ms_pricerate, ".
			"ms.frequency ms_freq, tr.id tr_id, tr.description tr_description, ".
			"tr.rate tr_rate, tr.if_field tr_if_field, tr.if_value tr_if_value, ".
			"tr.percentage_or_fixed tr_percentage_or_fixed, ".
			"us.master_service_id us_msid, us.billing_id us_bid, us.id us_id, ".
			"us.removed us_removed, us.account_number us_account_number, ". 
			"us.usage_multiple us_usage_multiple,  ".
			"te.account_number te_account_number, te.tax_rate_id te_tax_rate_id, ".
			"b.id b_id, b.billing_type b_billing_type, ".
			"t.id t_id, t.frequency t_freq, t.method t_method ".
			"FROM taxed_services ts ".
			"LEFT JOIN user_services us ON ".
			"us.master_service_id = ts.master_services_id ".
			"LEFT JOIN master_services ms ON ms.id = ts.master_services_id ".
			"LEFT JOIN tax_rates tr ON tr.id = ts.tax_rate_id ".
			"LEFT JOIN tax_exempt te ON te.account_number = us.account_number ".
			"AND te.tax_rate_id = tr.id ".
			"LEFT JOIN billing b ON us.billing_id = b.id ".
			"LEFT JOIN billing_types t ON b.billing_type = t.id ".
			"WHERE b.organization_id = '$organization_id' ".
			"AND us.removed <> 'y'";

		$taxresult = $this->db->query($query) or die ("Taxes Query Failed");

		return $taxresult->result_array;
	}

	/*
	 * ------------------------------------------------------------------------
	 *  get master services description, price, category, and frequency by id
	 * ------------------------------------------------------------------------
	 */
	function master_service_info($id)
	{
		$query = "SELECT ms.service_description, ms.pricerate, ms.category, ".
			"ms.frequency FROM master_services ms ".
			"WHERE ms.id = '$id'";
		$serviceresult = $this->db->query($query) or die ("Services Query Failed");

		return $serviceresult->result_array();
	}

	function taxed_services($id)
	{
		$query = "SELECT tr.description, tr.rate, ms.service_description, ".
			"ms.category FROM tax_rates tr ".
			"LEFT JOIN taxed_services ts ON ts.tax_rate_id = tr.id ".
			"LEFT JOIN master_services ms ON ms.id = ts.master_services_id ".	
			"WHERE ts.id = '$id'";
		$taxresult = $this->db->query($query) or die ("Taxes Query Failed");

		return $taxresult->result_array();
	}


	function total_services($organization_id)
	{
		// get the total services for each billing type
		$query = "SELECT m.id m_id, m.service_description m_servicedescription, ".
			"m.pricerate m_pricerate, m.frequency m_frequency, ".
			"m.organization_id m_organization_id, g.org_name g_org_name, ".
			"u.removed u_removed, u.master_service_id u_msid, ".
			"count(bt.method) AS TotalNumber, ".
			"b.id b_id, b.billing_type b_billing_type, bt.id bt_id, ".
			"bt.method bt_method ". 
			"FROM user_services u ".
			"LEFT JOIN master_services m ON u.master_service_id = m.id ".
			"LEFT JOIN billing b ON b.id = u.billing_id ".
			"LEFT JOIN billing_types bt ON b.billing_type = bt.id ".
			"LEFT JOIN general g ON m.organization_id = g.id ".
			"WHERE u.removed <> 'y' AND bt.method <> 'free' ".
			"AND b.organization_id = '$organization_id' AND m.pricerate > '0' ". 
			"AND m.frequency > '0' GROUP BY bt.method ORDER BY TotalNumber";
		$result = $this->db->query($query) or die ("query failed");

		return $result->result_array();

	}


	function services_in_categories($organization_id)
	{
		// get the number of services in each category
		$query = "SELECT m.id m_id, m.service_description m_servicedescription, ".
			"m.pricerate m_pricerate, m.category m_category, m.frequency m_frequency, ".
			"m.organization_id m_organization_id, g.org_name g_org_name, ".
			"u.removed u_removed, u.master_service_id u_msid, ".
			"count(bt.method) AS TotalNumber, ".
			"b.id b_id, b.billing_type b_billing_type, bt.id bt_id, bt.method bt_method ".
			"FROM user_services u ".
			"LEFT JOIN master_services m ON u.master_service_id = m.id ".
			"LEFT JOIN billing b ON b.id = u.billing_id ".
			"LEFT JOIN billing_types bt ON b.billing_type = bt.id ".
			"LEFT JOIN general g ON m.organization_id = g.id ".
			"WHERE u.removed <> 'y' AND b.organization_id = '$organization_id' ".
			"AND m.frequency > '0' GROUP BY m.category ORDER BY TotalNumber";
		$result = $this->db->query($query) or die ("query failed");

		return $result->result_array();

	}

	function number_of_customers()
	{
		// get the number of customers
		$query = "SELECT COUNT(*) FROM customer WHERE cancel_date is NULL";
		$result = $this->db->query($query) or die ("query failed");
		$myresult = $result->row_array();
		return $myresult['COUNT(*)'];

	}

	function number_of_non_free_customers()
	{
		// get the number of customers who are not free
		$query = "SELECT COUNT(*) FROM customer c
			LEFT JOIN billing b ON b.id = c.default_billing_id 
			LEFT JOIN billing_types bt ON b.billing_type = bt.id
			WHERE cancel_date is NULL AND bt.method <> 'free'";
		$result = $this->db->query($query) or die ("query failed");
		$myresult = $result->row_array();
		return $myresult['COUNT(*)'];
	}


	function servicerevenue($day1, $day2, $org_id)
	{
		// show payments for a specified date range according to 
		// their service category
		$query = "SELECT ROUND(SUM(bd.paid_amount),2) AS CategoryTotal, 
			COUNT(DISTINCT us.id) As ServiceCount, 
			ms.category service_category, 
			ms.service_description, service_description,  
			g.org_name g_org_name 
				FROM billing_details bd 
				LEFT JOIN user_services us ON us.id = bd.user_services_id 
				LEFT JOIN master_services ms ON us.master_service_id = ms.id 
				LEFT JOIN general g ON ms.organization_id = g.id 
				WHERE bd.creation_date BETWEEN ? AND ? 
				AND bd.taxed_services_id IS NULL AND g.id = ? 
				GROUP BY ms.id ORDER BY ms.category";

		$result = $this->db->query($query, array($day1, $day2, $org_id)) or die ("query failed");

		return $result->result_array();

	}


	function creditrevenue($day1, $day2, $org_id)
	{
		// show credits for a specified date range according to 
		// their credit_options description
		$query = "SELECT ROUND(SUM(bd.paid_amount),2) AS CategoryTotal, 
			COUNT(DISTINCT us.id) As ServiceCount, 
			cr.description credit_description, 
			g.org_name g_org_name 
				FROM billing_details bd
				LEFT JOIN user_services us ON us.id = bd.user_services_id 
				LEFT JOIN master_services ms ON us.master_service_id = ms.id 
				LEFT JOIN credit_options cr ON cr.user_services = us.id
				LEFT JOIN general g ON g.id = ms.organization_id 
				WHERE bd.creation_date BETWEEN ? AND ? 
				AND bd.taxed_services_id IS NULL AND g.id = ? 
				AND ms.id = 1  
				GROUP BY cr.description"; 

		$result = $this->db->query($query, array($day1, $day2, $org_id)) or die ("query failed");
		
		return $result->result_array();
	}


	function refundrevenue($day1, $day2, $org_id)
	{
		// show service refunds for a specified date range according to 
		// their refund_date
		$query = "SELECT ROUND(SUM(bd.refund_amount),2) AS CategoryTotal,
			COUNT(DISTINCT us.id) As ServiceCount,  
			ms.category service_category, 
			ms.service_description service_description, 
			g.org_name g_org_name    
				FROM billing_details bd
				LEFT JOIN user_services us 
				ON us.id = bd.user_services_id 
				LEFT JOIN master_services ms 
				ON us.master_service_id = ms.id
				LEFT JOIN general g 
				ON g.id = ms.organization_id  
				WHERE bd.refund_date BETWEEN ? AND ? 
				AND bd.taxed_services_id IS NULL and g.id = ? 
				GROUP BY ms.id"; 

		$result = $this->db->query($query, array($day1, $day2, $org_id)) or die ("query failed");
		
		return $result->result_array();

	}


	function discountrevenue($day1, $day2, $org_id)
	{
		// show discounts entered for a specified date range
		$query = "SELECT ph.billing_amount, ph.invoice_number, ".
			"ph.creation_date, bi.name, bi.company ".
			"FROM payment_history ph ".
			"LEFT JOIN billing bi ON ph.billing_id = bi.id ".
			"WHERE ph.creation_date BETWEEN ? AND ? ".
			"AND ph.payment_type = 'discount' AND bi.organization_id = ?";

		$result = $this->db->query($query, array($day1, $day2, $org_id)) or die ("query failed");
		
		return $result->result_array();
	}

	function taxrevenue($day1, $day2)
	{
		// show taxes for a specified date range according to
		// their tax rate description
		$query = "SELECT ROUND(SUM(bd.paid_amount),2)
			AS CategoryTotal,
			   COUNT(DISTINCT bd.id) As ServiceCount,
			   tr.description tax_description
				   FROM billing_details bd
				   LEFT JOIN taxed_services ts
				   ON bd.taxed_services_id = ts.id
				   LEFT JOIN tax_rates tr ON ts.tax_rate_id = tr.id
				   WHERE bd.creation_date BETWEEN ? AND ? 
				   AND bd.taxed_services_id IS NOT NULL
				   GROUP BY tr.id";

		$result = $this->db->query($query, array($day1, $day2)) or die ("query failed");
		
		return $result->result_array();

	}


	function taxrefunds($day1, $day2)
	{
		// show tax refunds for a specified date range according to 
		// their tax rate description
		$query = "SELECT ROUND(SUM(bd.refund_amount),2) 
			AS CategoryTotal,
			   COUNT(DISTINCT bd.id) As ServiceCount,  
			   tr.description tax_description  
				   FROM billing_details bd 
				   LEFT JOIN taxed_services ts 
				   ON bd.taxed_services_id = ts.id 
				   LEFT JOIN tax_rates tr ON ts.tax_rate_id = tr.id 
				   WHERE bd.refund_date BETWEEN ? AND ? 
				   AND bd.taxed_services_id IS NOT NULL 
				   GROUP BY tr.id";

		$result = $this->db->query($query, array($day1, $day2)) or die ("query failed");
		
		return $result->result_array();

	}


	function refunds($organization_id, $day1, $day2)
	{
		$query = "SELECT ph.creation_date, ph.billing_id, ph.creditcard_number, ".
			"ph.billing_amount, bi.name, bi.account_number FROM payment_history ph  ".
			"LEFT JOIN billing bi ON bi.id = ph.billing_id ".
			"WHERE ph.status = 'credit' AND bi.organization_id = ? ".
			"AND ph.creation_date BETWEEN ? AND ?";
		$result = $this->db->query($query, array($organization_id, $day1, $day2)) or die ("query failed");
		
		return $result->result_array();
	}


	function recentpayments($organization_id, $viewstatus)
	{
		// get the most recent payment history id for each billing id in that org
		$query = "SELECT max(ph.id) my_id, ph.billing_id my_bid ".
			"FROM payment_history ph ".
			"LEFT JOIN billing b ON b.id = ph.billing_id ".
			"WHERE b.organization_id = ? ".
			"GROUP BY ph.billing_id ORDER BY ph.billing_id";

		$result = $this->db->query($query, array($organization_id)) or die ("recentpayments query failed");

		// initialize for multidimensional result array
		$i = 0;
		$payments = array();

		// go through each one and find one with status we want to show
		foreach ($result->result_array() AS $myresult) 
		{
			$recentpaymentid = $myresult['my_id'];

			if (($viewstatus == 'authorized') OR ($viewstatus == 'declined') 
					OR ($viewstatus == 'pending') OR ($viewstatus == 'turnedoff') 
					OR ($viewstatus == 'pastdue') OR ($viewstatus == 'noticesent')
					OR ($viewstatus == 'waiting')) 
			{
				// don't include past due exempts in this listing
				$query = "SELECT ph.billing_id, b.account_number, b.name, b.company, ".
					"ph.status, bd.invoice_number, bh.payment_due_date, bh.from_date, bh.to_date, c.cancel_date ".
					"FROM payment_history ph ".
					"LEFT JOIN billing b ON b.id = ph.billing_id ".
					"LEFT JOIN billing_details bd ON bd.billing_id = b.id ".
					"LEFT JOIN billing_history bh ON bd.invoice_number = bh.id ".
					"LEFT JOIN customer c ON c.account_number = b.account_number ".
					"WHERE ph.id = $recentpaymentid AND b.pastdue_exempt <> 'y' AND ".
					"c.cancel_date IS NULL AND ".
					"ph.status = '$viewstatus' AND bd.billed_amount > bd.paid_amount LIMIT 1";
			} 
			elseif (($viewstatus == 'cancelwfee') OR ($viewstatus == 'canceled') 
					OR ($viewstatus == 'collections')) 
			{
				// ok to include pastdue exempt accounts in this listing
				$query = "SELECT ph.billing_id, b.account_number, b.name, b.company, ".
					"ph.status, bd.invoice_number, bh.payment_due_date, bh.from_date, bh.to_date, c.cancel_date ".
					"FROM payment_history ph ".
					"LEFT JOIN billing b ON b.id = ph.billing_id ".
					"LEFT JOIN billing_details bd ON bd.billing_id = b.id ".
					"LEFT JOIN billing_history bh ON bd.invoice_number = bh.id ".
					"LEFT JOIN customer c ON c.account_number = b.account_number ".
					"WHERE ph.id = $recentpaymentid AND ".
					"ph.status = '$viewstatus' AND bd.billed_amount > bd.paid_amount LIMIT 1";
			} 
			elseif ($viewstatus == 'pastdueexempt') 
			{
				$query = "SELECT ph.billing_id, b.account_number, b.name, b.company, ".
					"ph.status, bd.invoice_number, bh.payment_due_date, bh.from_date, bh.to_date, c.cancel_date ".
					"FROM payment_history ph ".
					"LEFT JOIN billing b ON b.id = ph.billing_id ".
					"LEFT JOIN billing_details bd ON bd.billing_id = b.id ".
					"LEFT JOIN billing_history bh ON bd.invoice_number = bh.id ".
					"LEFT JOIN customer c ON c.account_number = b.account_number ".
					"WHERE ph.id = $recentpaymentid AND b.pastdue_exempt = 'y' ".
					"AND c.cancel_date IS NULL AND bd.billed_amount > bd.paid_amount LIMIT 1";	
			}

			$paymentresult = $this->db->query($query) or die ("paymentresult $l_queryfailed");
			foreach ($paymentresult->result_array() AS $mypaymentresult) 
			{    
				$account_number = $mypaymentresult['account_number'];
				$billing_id = $mypaymentresult['billing_id'];    
				$name = $mypaymentresult['name'];
				$company = $mypaymentresult['company'];
				$status = $mypaymentresult['status'];
				$invoice_number = $mypaymentresult['invoice_number'];
				$from_date = $mypaymentresult['from_date'];
				$to_date = $mypaymentresult['to_date'];
				$payment_due_date = $mypaymentresult['payment_due_date'];

				// TODO: select unique categories of service for this billing id from
				$categorylist = "";
				$query = "SELECT DISTINCT m.category FROM user_services u ".
					"LEFT JOIN master_services m ON u.master_service_id = m.id ".
					"WHERE u.billing_id = '$billing_id' AND removed <> 'y'";
				$categoryresult = $DB->Execute($query) or die ("category $l_queryfailed");
				while($mycategoryresult = $categoryresult->FetchRow()) 
				{
					$categorylist .= $mycategoryresult['category'];
					$categorylist .= "<br>";
				}

				$pastcharges = sprintf("%.2f",total_pastdueitems($DB, $billing_id));

				// put the data in an array to return
				$payments[$i] = array(
						'account_number' => $account_number,
						'billing_id' => $billing_id,
						'name' => $name,
						'company' => $company,
						'status' => $status,
						'invoice_number' => $invoice_number,
						'from_date' => $from_date,
						'to_date' => $to_date,
						'payment_due_date' => $payment_due_date,
						'categorylist' => $categorylist,
						'pastcharges' => $pastcharges
						);

				$i++;

			}

		}

		return $payments;

	}

}
