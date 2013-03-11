<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reports extends App_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('customer_model');
		$this->load->model('module_model');
		$this->load->model('user_model');
		$this->load->model('support_model');
		$this->load->model('billing_model');
		$this->load->model('reports_model');
	}		


	/*
	 * ------------------------------------------------------------------------
	 *  Show the reports user if they have permission to view
	 * ------------------------------------------------------------------------
	 */
	public function index()
	{
		// show the header common to all dashboard/tool views
		$this->load->view('dashboard_header_view');

		// check for user privileges to see if the are manager or admin
		$privileges = $this->user_model->user_privileges($this->user);

		if (($privileges['manager'] == 'y') OR ($privileges['admin'] == 'y'))
		{
			// Show Reports Tools for manager and admin
			$this->load->view('reports/index_view');
		}

		// the html page footer
		$this->load->view('html_footer_view');

	}

	/*
	 * ------------------------------------------------------------------------
	 *  sends customer summary to view or summary file for download
	 *  input: style (view|file)
	 *  input: organization id (for file generated), optional
	 * ------------------------------------------------------------------------
	 */
	function summary($style, $organization_id = NULL)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		// load settings and general
		$this->load->model('settings_model');
		$this->load->model('general_model');

		// set org id input or default to org id 1 if none specified
		if ($organization_id)
		{
			$dataview['organization_id'] = $organization_id;
		} 
		else
		{
			if (!$this->input->post('organization_id'))
			{
				$dataview['organization_id'] = 1;
				$organization_id = 1;
			}
			else
			{
				$dataview['organization_id'] = $this->input->post('organization_id');
				$organization_id = $this->input->post('organization_id');
			}
		}

		if ($style == 'file')
		{
			// load the download helper
			$this->load->helper('download');

			$filename = "summary.csv";

			$datafile = lang('services').",Frequency,Category,Customers,Service Cost,".
				"Monthly Total\n";
		}

		// initialize the count of paid monthly services
		$paidsubscriptions = 0;
		$count_creditcard = 0;
		$count_invoice = 0;
		$count_einvoice = 0;
		$count_prepay = 0;
		$count_prepaycc = 0;
		$total_customers = 0;
		$total_service_cost = 0;
		$total_monthly = 0;

		$services_by_org = $this->reports_model->services_by_org($organization_id);

		// initialize arrays to keep our results in
		// make hash/array of master service id and amount being charged
		// and another array to count how many of customers have that type of charge
		$price_array = array();
		$count_array = array();
		$category_array = array();

		$i = 0; // count the billing services
		foreach($services_by_org AS $myresult)
		{
			$billing_id = $myresult['u_bid'];
			$user_services_id = $myresult['u_id'];
			$pricerate = $myresult['m_pricerate'];
			$usage_multiple = $myresult['u_usage'];
			$master_service_id = $myresult['m_id'];

			$billed_amount = ($pricerate*$usage_multiple);

			// round the tax to two decimal places
			$billed_amount = sprintf("%.2f", $billed_amount);

			// Insert results into an array
			if (isset($price_array[$master_service_id])) 
			{
				$price_array[$master_service_id] = 
					$price_array[$master_service_id] + $billed_amount;
				$count_array[$master_service_id]++;
			} 
			else 
			{
				$price_array[$master_service_id] = $billed_amount;
				$count_array[$master_service_id] = 1;	    
			}    

		} // end while


		// print each item in the price and count arrays
		foreach($price_array as $master_service_id_value => $total_billed) 
		{
			$servicearray = $this->reports_model->master_service_info($master_service_id_value);

			// count the number of taxes
			$count = $count_array[$master_service_id_value];

			// initialize dataview service listing
			$dataview['service_listing'] = '';

			foreach ($servicearray AS $myserviceresult) 
			{
				$service_description = $myserviceresult['service_description'];
				$rate = $myserviceresult['pricerate'];
				$frequency = $myserviceresult['frequency'];
				$category = $myserviceresult['category'];

				// add to the displayed paid subscription count total, 
				// do not count free or on time services as a subscription
				if (($rate > 0) AND ($frequency > 0)) 
				{
					$paidsubscriptions = $paidsubscriptions + $count;
				}       

				// if frequency is greater than 1 divide the total amount by the frequency
				if ($frequency > 1) 
				{
					$total_billed = $total_billed/$frequency;
                }

                // add the monthly total to the category array
                if (isset($category_array[$category])) {
                    $category_array[$category] = $category_array[$category] + $total_billed;
                } else {
                    $category_array[$category] = $total_billed;
                }

				if ($style == 'view')
				{
					$dataview['service_listing'] .= "<td>$service_description</td>".
						"<td>$frequency</td>".
						"<td>$category</td><td>$count</td><td>$rate</td>".
						"<td>$total_billed</td><tr>";
				}
				else
				{
					$datafile .= "$service_description,$frequency,$category,$count,".
						"$rate,$total_billed\n";
				}

				// add totals
				$total_customers = sprintf("%.2f",$total_customers + $count);
				$total_service_cost = sprintf("%.2f",$total_service_cost + $rate);
				$total_monthly = sprintf("%.2f",$total_monthly + $total_billed);
			}
		}


		/*--------------------------------------------------------------------------*/
		// calculate taxes for all taxed services at this time
		// this part may take a long time
		/*--------------------------------------------------------------------------*/

		// initialize arrays to keep our results in
		// make hash/array of tax rate id and number of customers being charged that tax rate
		// and another array to count how many of those taxes are charged
		$tax_array = array();
		$count_array = array();

		$taxresults = $this->reports_model->taxes_by_org($organization_id);

		// count the number of taxes
		$i = 0;

		foreach ($taxresults AS $mytaxresult) 
		{
			$billing_id = $mytaxresult['b_id'];
			$taxed_services_id = $mytaxresult['ts_id'];
			$user_services_id = $mytaxresult['us_id'];
			$service_freq = $mytaxresult['ms_freq'];
			$billing_freq = $mytaxresult['t_freq'];	
			$if_field = $mytaxresult['tr_if_field'];
			$if_value = $mytaxresult['tr_if_value'];
			$percentage_or_fixed = $mytaxresult['tr_percentage_or_fixed'];
			$my_account_number = $mytaxresult['us_account_number'];
			$usage_multiple = $mytaxresult['us_usage_multiple'];
			$pricerate = $mytaxresult['ms_pricerate'];
			$taxrate = $mytaxresult['tr_rate'];
			$tax_rate_id = $mytaxresult['tr_id'];
			$tax_exempt_rate_id = $mytaxresult['te_tax_rate_id'];

			// check that they are not exempt
			if ($tax_exempt_rate_id <> $tax_rate_id) 
			{
				// check the if_field before adding to see if 
				// the tax applies to this customer
				if ($if_field <> '') 
				{
					$checkvalue = $this->customer_model->check_if_field($if_field, $my_account_number);
				} 
				else 
				{
					$checkvalue = TRUE;
					$if_value = TRUE;
				}

				// check for any case, so lower them here
				$checkvalue = strtolower($checkvalue);
				$if_value = strtolower($if_value);

				if (($checkvalue == $if_value) AND ($billing_freq > 0)) 
				{
					if ($percentage_or_fixed == 'percentage') 
					{
						if ($service_freq > 0) 
						{
							$servicecost = sprintf("%.2f",$taxrate * $pricerate);
							// removed freq from this calculation since it is just for a monthly snapshot
							$tax_amount = sprintf("%.2f",$servicecost * $usage_multiple); 
						} 
						else 
						{
							$servicecost = $pricerate * $usage_multiple;
							$tax_amount = $taxrate * $servicecost;
						}
					} 
					else 
					{
						// fixed fee amount does not depend on price or usage
						$tax_amount = $taxrate;
					}

					// round the tax to two decimal places
					$tax_amount = sprintf("%.2f", $tax_amount);

					// Insert results into an array

					if (isset($tax_array[$taxed_services_id])) 
					{
						$tax_array[$taxed_services_id] = $tax_array[$taxed_services_id] + $tax_amount;
						$count_array[$taxed_services_id]++;
					} 
					else 
					{
						$tax_array[$taxed_services_id] = $tax_amount;
						$count_array[$taxed_services_id] = 1;	    
					}

				} //endif if_field/billing_freq
			} // endif exempt
		}

		// initialize dataview tax listing
		$dataview['tax_listing'] = '';

		// print each item in the tax and count arrays
		foreach($tax_array as $taxed_services_id_value => $total_taxed) 
		{
			$taxresults = $this->reports_model->taxed_services($taxed_services_id_value);

			// count the number of taxes
			$count = $count_array[$taxed_services_id_value];

			foreach ($taxresults AS $mytaxresult) 
			{
				$description = $mytaxresult['description'];
				$service_description = $mytaxresult['service_description'];
				$rate = $mytaxresult['rate'];
                $category = $mytaxresult['category'];

                // add total taxed to category array
                $category_array[$category] = $category_array[$category] + $total_taxed;

				if ($style == 'view')
				{
					$dataview['tax_listing'] .= "<td>$description for $service_description</td>".
						"<td></td>".
						"<td>$category</td><td>$count</td>".
						"<td>$rate</td><td>$total_taxed</td><tr>";
				}
				else
				{
					$datafile .= "$description for $service_description,,$category,".
						"$count,$rate,$total_taxed\n";
				}

				// add totals
				$total_customers = sprintf("%.2f",$total_customers + $count);
				$total_service_cost = sprintf("%.2f",$total_service_cost + $rate);
				$total_monthly = sprintf("%.2f",$total_monthly + $total_taxed);

			}
		}


		if ($style == 'view')
		{
			// print the table footer
			$dataview['listing_footer'] = "<td style=\"border-top: 1px solid black; font-weight: bold;\">".
				lang('total').":</td> ".
				"<td style=\"border-top: 1px solid black; font-weight: bold;\">&nbsp;</td> ".
				"<td style=\"border-top: 1px solid black; font-weight: bold;\">&nbsp;</td> ".
				"<td style=\"border-top: 1px solid black; font-weight: bold;\">$total_customers</td> ".
				"<td style=\"border-top: 1px solid black; font-weight: bold;\">$total_service_cost</td> ".
				"<td style=\"border-top: 1px solid black; font-weight: bold;\">$total_monthly</td><tr>";
		}
		else
		{
			$datafile .= ",,,$total_customers,$total_service_cost,$total_monthly\n";
		}

		$dataview['paidsubscriptions'] = $paidsubscriptions;

		// initialize billing methods dataview
		$dataview['billing_methods'] = '';

		$results = $this->reports_model->total_services($organization_id);

		foreach ($results AS $myresult) 
		{
			$count = $myresult['TotalNumber'];
			$billingmethod = $myresult['bt_method'];
			$dataview['billing_methods'] .= "$billingmethod: $count<br>\n";	
		}

		// initialize data view service_categories
		$dataview['service_categories'] = '';

		$results = $this->reports_model->services_in_categories($organization_id);

		foreach ($results as $myresult) 
		{
			$count = $myresult['TotalNumber'];
			$category = $myresult['m_category'];
	        $category_total = sprintf("%.2f", $category_array[$category]);
			$dataview['service_categories'] .= "$category: $count \$$category_total<br>\n";	
		}
		echo "</blockquote>";

		$dataview['totalcustomers'] = $this->reports_model->number_of_customers();

		$dataview['totalpayingcustomers'] = $this->reports_model->number_of_non_free_customers();

		if ($style == 'view')
		{
			// load the header without the sidebar to get the stylesheet in there
			//$this->load->view('header_no_sidebar_view');

			$dataview['orglist'] = $this->general_model->list_organizations();
			$this->load->view('reports/summary_view', $dataview);
		}
		else
		{
			// file style
			force_download($filename, $datafile);
		}
	}


	/*
	 * ------------------------------------------------------------------------
	 *  show the revenue report
	 * ------------------------------------------------------------------------
	 */
	function revenue()
	{
		// load settings and general
		$this->load->model('settings_model');
		$this->load->model('general_model');

		// make sure they have manager privileges
		$myresult = $this->user_model->user_privileges($this->user);
		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$empty_day_1  = date("Y-m-d", mktime(0, 0, 0, date("m")-1  , date("d"), date("Y")));
		$empty_day_2  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));

		$day1 = $this->input->post('day1');
		$day2 = $this->input->post('day2');
		$org_id = $this->input->post('organization_id');

		// if nothing was entered for the day, then put in the defaults month period
		if ($day1 =='') { $day1 = $empty_day_1; }
		if ($day2 =='') { $day2 = $empty_day_2; }

		$data['day1'] = $day1;
		$data['day2'] = $day2;

		$data['orglist'] = $this->general_model->list_organizations();
		$data['servicerevenue'] = $this->reports_model->servicerevenue($day1, $day2, $org_id);
		$data['creditrevenue'] = $this->reports_model->creditrevenue($day1, $day2, $org_id);
		$data['refundrevenue'] = $this->reports_model->refundrevenue($day1, $day2, $org_id);
		$data['discountrevenue'] = $this->reports_model->discountrevenue($day1, $day2, $org_id);
		$data['taxrevenue'] = $this->reports_model->taxrevenue($day1, $day2);
		$data['taxrefunds'] = $this->reports_model->taxrefunds($day1, $day2);

		// load the header without the sidebar to get the stylesheet in there
		//$this->load->view('header_no_sidebar_view');

		$this->load->view('reports/revenue_view', $data);
	}

	function refunds()
	{
		// load settings and general
		$this->load->model('settings_model');
		$this->load->model('general_model');

		// make sure they have manager privileges
		$myresult = $this->user_model->user_privileges($this->user);
		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$empty_day_1  = date("Y-m-d", mktime(0, 0, 0, date("m")-1  , date("d"), date("Y")));
		$empty_day_2  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));

		$day1 = $this->input->post('day1');
		$day2 = $this->input->post('day2');
		$organization_id = $this->input->post('organization_id');

		// if nothing was entered for the day, then put in the defaults month period
		if ($day1 =='') { $day1 = $empty_day_1; }
		if ($day2 =='') { $day2 = $empty_day_2; }
		if ($organization_id =='') { $organization_id = 1; }

		$data['day1'] = $day1;
		$data['day2'] = $day2;

		$data['orglist'] = $this->general_model->list_organizations();
		$data['organization_name'] = $this->general_model->get_org_name($organization_id);
		$data['refunds'] = $this->reports_model->refunds($organization_id, $day1, $day2);

		// load the header without the sidebar to get the stylesheet in there
		//$this->load->view('header_no_sidebar_view');

		$this->load->view('reports/refunds_view', $data);
	}


	function pastdue()
	{
		// load settings and general
		$this->load->model('settings_model');
		$this->load->model('general_model');

		// make sure they have manager privileges
		$myresult = $this->user_model->user_privileges($this->user);
		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$organization_id = $this->input->post('organization_id');
		$changestatus = $this->input->post('changestatus');
		$viewstatus = $this->input->post('viewstatus');
		$history_date = $this->input->post('history_date');
		$history_date2 = $this->input->post('history_date2');
		$billingid = $this->input->post('billingid');

		if ($organization_id == '') { $organization_id = 1; }
		if ($viewstatus == '') { $viewstatus = 'pastdue'; }

		// change the status if changestatus is input
		switch ($changestatus) 
		{
			case 'waiting':
				$this->billing_model->waiting_status($billingid);
				break;

			case 'turnedoff':
				$this->billing_model->turnedoff_status($billingid);
				break;

			case 'canceled':
				$this->billing_model->canceled_status($billingid);
				break;

			case 'cancelwfee':
				$this->billing_model->cancelwfee_status($billingid);
				break;

			case 'collections':
				$this->billing_model->collections_status($billingid);
				break;
		}

		$data['organization_id'] = $organization_id;
		$data['changestatus'] = $changestatus;
		$data['viewstatus'] = $viewstatus;
		$data['history_date'] = $history_date;
		$data['history_date2'] = $history_date2;
		$data['billingid'] = $billingid;

		$data['org_name'] = $this->general_model->get_org_name($organization_id);
		$data['recentpayments'] = $this->reports_model->recentpayments($organization_id, $viewstatus);
		$data['orglist'] = $this->general_model->list_organizations();

		// load the header without the sidebar to get the stylesheet in there
		//$this->load->view('header_no_sidebar_view');

		$this->load->view('reports/pastdue_view', $data);
	}


	function paymentstatus()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		// load settings and general
		$this->load->model('settings_model');
		$this->load->model('general_model');
		
			
		// set the organization id to default 1
		if (!$this->input->post('organization_id'))
		{
			$data['organization_id'] = 1;
			$organization_id = 1;
		}
		else
		{
			$data['organization_id'] = $this->input->post('organization_id');
			$organization_id = $this->input->post('organization_id');
		}

		// get input
		$day1 = $this->input->post('day1');
		$day2 = $this->input->post('day2');
		$showpaymenttype = $this->input->post('showpaymenttype');
		$showstatus = $this->input->post('showstatus');

		// get input
		$data['day1'] = $this->input->post('day1');
		$data['day2'] = $this->input->post('day2');
		$data['showpaymenttype'] = $this->input->post('showpaymenttype');
		$data['showstatus'] = $this->input->post('showstatus');

		// set the organization name, if all set to All, else normal org name
		if ($organization_id == 'all')
		{
			$data['org_name'] = lang('all');
		}
		else
		{
			$data['org_name'] = $this->general_model->get_org_name($organization_id);
		}

		$data['paymentstatus'] = $this->reports_model->paymentstatus($day1, $day2, 
				$organization_id, $showpaymenttype);
		$data['distinctdeclined'] = $this->reports_model->distinctdeclined($day1, $day2, $organization_id);
		$data['noncardpayments'] = $this->reports_model->noncardpayments($day1, $day2, $organization_id);
		$data['orglist'] = $this->general_model->list_organizations();

		// load the header without the sidebar to get the stylesheet in there
		//$this->load->view('header_no_sidebar_view');

		$this->load->view('reports/paymentstatus_view', $data);
	}


	/*
	 * ------------------------------------------------------------------------
	 *  show the menu that lets you pick services to report on
	 * ------------------------------------------------------------------------
	 */
	function services()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		// load settings and general
		$this->load->model('settings_model');
		$this->load->model('general_model');
		$data['listservices'] = $this->reports_model->listservices();

		// load the header without the sidebar to get the stylesheet in there
		//$this->load->view('header_no_sidebar_view');

		$this->load->view('reports/services_view', $data);
	}


	function showservices()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		// load settings, general, and service models
		$this->load->model('settings_model');
		$this->load->model('general_model');
		$this->load->model('service_model');

		// get the service id input
		$service_id = $this->input->post('service_id');

		// initialize variables
		$data['service_count'] = 0;
		
		$billing_type_array = array();
		$billing_type_array['creditcard'] = 0;
		$billing_type_array['invoice'] = 0;
		$billing_type_array['prepay'] = 0;
		$billing_type_array['free'] = 0;
		$billing_type_array['prepaycc'] = 0;
		$billing_type_array['einvoice'] = 0;
		
		$billing_status_array = array();
		$billing_status_array['Authorized'] = 0;
		$billing_status_array['New'] = 0;
		$billing_status_array['Past Due'] = 0;
		$billing_status_array['Declined'] = 0;
		$billing_status_array['Turned Off'] = 0;
		$billing_status_array['Collections'] = 0;
		$billing_status_array['Canceled'] = 0;
		$billing_status_array['Canceled w/Fee'] = 0;
		
		$collections_type_array = array();
		$collections_type_array['creditcard'] = 0;
		$collections_type_array['invoice'] = 0;
		$collections_type_array['prepay'] = 0;
		$collections_type_array['free'] = 0;
		$collections_type_array['prepaycc'] = 0;
		$collections_type_array['einvoice'] = 0;
		
		$authorized_type_array = array();
		$authorized_type_array['creditcard'] = 0;
		$authorized_type_array['invoice'] = 0;
		$authorized_type_array['prepay'] = 0;
		$authorized_type_array['free'] = 0;
		$authorized_type_array['prepaycc'] = 0;
		$authorized_type_array['einvoice'] = 0;
		
		$turnedoff_type_array = array();
		$turnedoff_type_array['creditcard'] = 0;
		$turnedoff_type_array['invoice'] = 0;
		$turnedoff_type_array['prepay'] = 0;
		$turnedoff_type_array['free'] = 0;
		$turnedoff_type_array['prepaycc'] = 0;
		$turnedoff_type_array['einvoice'] = 0;
		
		$new_type_array = array();
		$new_type_array['creditcard'] = 0;
		$new_type_array['invoice'] = 0;
		$new_type_array['prepay'] = 0;
		$new_type_array['free'] = 0;
		$new_type_array['prepaycc'] = 0;
		$new_type_array['einvoice'] = 0;
		
		$canceled_type_array = array();
		$canceled_type_array['creditcard'] = 0;
		$canceled_type_array['invoice'] = 0;
		$canceled_type_array['prepay'] = 0;
		$canceled_type_array['free'] = 0;
		$canceled_type_array['prepaycc'] = 0;
		$canceled_type_array['einvoice'] = 0;
		
		$pastdue_type_array = array();
		$pastdue_type_array['creditcard'] = 0;
		$pastdue_type_array['invoice'] = 0;
		$pastdue_type_array['prepay'] = 0;
		$pastdue_type_array['free'] = 0;
		$pastdue_type_array['prepaycc'] = 0;
		$pastdue_type_array['einvoice'] = 0;
		
		$canceled_reason_array = array();
		$canceled_type_array['creditcard'] = 0;
		$canceled_type_array['invoice'] = 0;
		$canceled_type_array['prepay'] = 0;
		$canceled_type_array['free'] = 0;
		$canceled_type_array['prepaycc'] = 0;
		$canceled_type_array['einvoice'] = 0;

		$distinctservices = $this->reports_model->distinctservices($service_id);

		foreach ($distinctservices AS $myresult) 
		{
			// get the invoice data to process now

			//$user_service_id = $myresult['us_id'];
			$billing_id = $myresult['bi_id'];
			$cancel_reason = $myresult['cancel_reason'];

			// increment the billing method counter
			$billing_method = $myresult['bt_method'];
			$billing_type_array["$billing_method"]++;

			// increment the billing status counter
			$billing_status = $this->billing_model->billingstatus($billing_id);

			// count the canceled w/fee with all canceled
			if ($billing_status == "Canceled w/Fee"){
				$billing_status = "Canceled";
			}

			$billing_status_array["$billing_status"]++;


			if ($billing_status == "Collections") {
				$collections_type_array["$billing_method"]++;
			}

			if ($billing_status == "Authorized") {
				$authorized_type_array["$billing_method"]++;
			}

			if ($billing_status == "Turned Off" ) {
				$turnedoff_type_array["$billing_method"]++;
			}

			if ($billing_status == "New" ) {
				$new_type_array["$billing_method"]++;
			}

			if ($billing_status == "Past Due" ) {
				$pastdue_type_array["$billing_method"]++;
			}

			if ($billing_status == "Canceled") {
				$canceled_type_array["$billing_method"]++;
				$canceled_reason_array["$cancel_reason"]++;
			}

			$data['service_count']++;
		}

		$data['active'] = "";
		$data['inactive'] = "";
		$data['other'] = "";
		$data['declinedvalue'] = 0;

		ksort ($billing_status_array);

		foreach ($billing_status_array as $status=>$value) {

			if ($status == "Authorized" OR $status == "New" OR $status == "Past Due") {
				$data['active'] .= "<p><b>$status $value</b>\n";

				if ($status == "Authorized") {
					ksort ($authorized_type_array);
					foreach ($authorized_type_array as $method=>$value) {
						$data['active'] .= "&nbsp;&nbsp;&nbsp;$method: $value\n";
					}
				}

				if ($status == "New") {
					ksort ($new_type_array);
					foreach ($new_type_array as $method=>$value) {
						$data['active'] .="&nbsp;&nbsp;&nbsp;$method: $value\n";
					}
				}

				if ($status == "Past Due") {
					ksort ($pastdue_type_array);
					foreach ($pastdue_type_array as $method=>$value) {
						$data['active'] .="&nbsp;&nbsp;&nbsp;$method: $value\n";
					}
				}

			} else {

				if ($status == "Declined" OR $status == "Initial Decline") {
					$data['declinedvalue'] = $data['declinedvalue'] + $value;

				} else {

					if ($status == "Collections" OR $status == "Turned Off" OR $status == "Canceled" OR $status == "Canceled w/Fee") {
						$data['inactive'] .= "<p><b>$status: $value</b>\n";

						if ($status == "Collections") {
							ksort ($collections_type_array);
							foreach ($collections_type_array as $method=>$value) {
								$data['inactive'] .= "&nbsp;&nbsp;&nbsp;$method: $value\n";
							}
						}

						if ($status == "Turned Off") {
							ksort ($turnedoff_type_array);
							foreach ($turnedoff_type_array as $method=>$value) {
								$data['inactive'] .= "&nbsp;&nbsp;&nbsp;$method: $value\n";
							}
						}

						if ($status == "Canceled") {
							ksort ($canceled_type_array);
							foreach ($canceled_type_array as $method=>$value) {
								$data['inactive'] .= "&nbsp;&nbsp;&nbsp;$method: $value\n";
							}

							// print cancel reasons
							$data['inactive'] .= "<br>\n";
							arsort ($canceled_reason_array);
							foreach ($canceled_reason_array as $method=>$value) {
								$data['inactive'] .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$method: $value<br>\n";
							}

						}
					} else {
						$data['other'] .= "<p><b>$status: $value</b>\n";
					}
				}
			}
		}  

		$data['description'] = $this->service_model->get_service_name($service_id);

		// load the header without the sidebar to get the stylesheet in there
		//$this->load->view('header_no_sidebar_view');

		$this->load->view('reports/showservices_view', $data);
	}


	function sources()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$this->load->model('service_model');
		$data['servicecategories'] = $this->service_model->distinct_service_categories();

		// load the header without the sidebar to get the stylesheet in there
		//$this->load->view('header_no_sidebar_view');

		$this->load->view('reports/sources_view', $data);
	}


	function showsources()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$day1 = $this->input->post('day1');
		$day2 = $this->input->post('day2');
		$category = $this->input->post('category');
		$data['category'] = $category;

		$data['servicesources'] = $this->reports_model->servicesources($day1, $day2, $category);

		// load the header without the sidebar to get the stylesheet in there
		//$this->load->view('header_no_sidebar_view');

		$this->load->view('reports/showsources_view', $data);
	}


	function exempt()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		// load the header without the sidebar to get the stylesheet in there
		//$this->load->view('header_no_sidebar_view');

		$this->load->view('reports/exempt_view');
	}


	function showexempt()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['exempttype'] = $this->input->post('exempttype');

		// load the header without the sidebar to get the stylesheet in there
		//$this->load->view('header_no_sidebar_view');

		// load the view for the specific exempt type
		switch ($data['exempttype']) 
		{
			case 'pastdueexempt':
				$data['pastdueexempt'] = $this->reports_model->pastdueexempt();
				$this->load->view('reports/pastdueexempt_view', $data);
				break;
			case 'baddebt':
				$data['baddebt'] = $this->reports_model->baddebt();
				$this->load->view('reports/baddebt_view', $data);
				break;
			case 'taxexempt':
				$data['taxexempt'] = $this->reports_model->taxexempt();
				$this->load->view('reports/taxexempt_view', $data);
				break;
		}

	}


	function printnotices()
	{
		// load the settings model 
		$this->load->model('settings_model');

		// load the header without the sidebar to get the stylesheet in there
		//$this->load->view('header_no_sidebar_view');

		// get the path where to store the cc data
		$data['path_to_ccfile'] = $this->settings_model->get_path_to_ccfile();

		// get the day input, if any
		$data['day'] = $this->input->post('day');

		$this->load->view('reports/printnotices_view', $data);
	}


	function servicechurn()
	{
		// Variables
		$data['year'] = $this->input->post('year');
		$data['month'] = $this->input->post('month');
		$data['category'] = $this->input->post('category');

		$data['servicechurn'] = $this->reports_model->servicechurn($data['month'], $data['year']);

		// load the header without the sidebar to get the stylesheet in there
		//$this->load->view('header_no_sidebar_view');

		$this->load->view('reports/servicechurn_view', $data);
    }

    function largecustomers()
    {
		// Variables
		$data['day1'] = $this->input->post('day1');
		$data['day2'] = $this->input->post('day2');

		$data['largecustomers'] = $this->reports_model->largecustomers($data['day1'], $data['day2']);

		$this->load->view('reports/largecustomers_view', $data);
    }

}

/* End of file reports */
/* Location: ./application/controllers/reports.php */
