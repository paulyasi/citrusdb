<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search extends App_Controller {

	/**
	 * Search function
	 */
	
	function __construct() 
	{
    	parent::__construct();
    	
		// get the inputs from the search
		// form uses post, but next/prev links use get
		$this->id = $this->input->get_post('id');
		$this->s1 = $this->input->get_post('s1');
		$this->s2 = $this->input->get_post('s2');
		$this->s3 = $this->input->get_post('s3');
		$this->s4 = $this->input->get_post('s4');
		$this->s5 = $this->input->get_post('s5');

		// figure out which type of search it is from the searches table
		$query = $this->db->get_where('searches', array('id' => $this->id));
		$myresult = $query->row();
		
		// assign the query from the search to the query string
		// replace the s1 thru s5 etc place holders with the actual variables
		$this->searchquery = str_replace("%s1%", $this->s1, $myresult->query);
		$this->searchquery = str_replace("%s2%", $this->s2, $this->searchquery);
		$this->searchquery = str_replace("%s3%", $this->s3, $this->searchquery);
		$this->searchquery = str_replace("%s4%", $this->s4, $this->searchquery);
		$this->searchquery = str_replace("%s5%", $this->s5, $this->searchquery);

		$this->result = $this->db->query($this->searchquery) or die ("search result queryfailed");
		
		// print the search page heading
		echo "<h3>";
		echo lang('foundset');
		echo "</h3>";
		echo "<table cellpadding=5 cellspacing=1 border=0>";
	}

	
	/*
	 * -------------------------------------------------------------------------
	 *  show search results
	 * -------------------------------------------------------------------------
	 */
	public function results($page, $perpage, $recordview = NULL)
	{
		$fields = $this->result->list_fields();

		// print the the column titles
		$i = 0;

		// check for the array and print any results
		if (is_array($fields)) 
		{
			echo "<tr bgcolor=#ccccdd><td></td>";
  			foreach ($fields as $key => $value) 
    		{          
      			echo "<td>$value</td>\n";
      			$i++;
    		} 
 		}


		// if there is only 1 result go to that customer record
		// else print out the listing of results

		// get the number of results
		$num_of_results = $this->result->num_rows();
		echo "$num_of_results " . lang('found');
		if ($num_of_results > $perpage)
		{			
			// load the pager helper to calculate page numbers
			$this->load->helper('pager');
  			$pager = getPagerData($num_of_results, $perpage, $page);
  			$offset = $pager->offset;
  			$limit = $pager->limit;
  			$page = $pager->page;
  			$numpages = $pager->numPages;
  			$pagedquery = $this->searchquery . " limit $offset, $limit"; 
  			$this->result = $this->db->query($pagedquery) or die ("page query failed");
  			echo lang('page') . " " . $page . " " . lang('of') . " " . $numpages . " | ";
  
  			if($page == 1)
   			{
				// disabled button
      			echo "<form name=prevform>".
					"<input type=submit value=\"".lang('previous')."\" disabled>".
					"</form>";	
    		} 
    		else 
    		{
    			echo "<form name=prevform action=\"$this->url_prefix/index.php/search/results/".
    				($page - 1) . "/$perpage/";

				if ($recordview)
				{
					echo "record";
				}

				echo "\" method=POST>".
					"<input type=hidden name=id value=\"$this->id\">".
					"<input type=hidden name=s1 value=\"$this->s1\">".				
					"<input type=hidden name=s2 value=\"$this->s2\">".
					"<input type=hidden name=s3 value=\"$this->s3\">".
					"<input type=hidden name=s4 value=\"$this->s4\">".				
					"<input type=hidden name=s5 value=\"$this->s5\">".
    				"<input type=submit value=\"".lang('previous')."\">".
					"</form>";	
  			}
  
  			if($page == $pager->numPages)
    		{
				echo "<form name=nextform action=\"$this->url_prefix/index.php/search/results/".
					($page +1)."/$perpage/";
				echo "\" method=POST>".
					"<input type=submit value=\"".lang('next')."\" disabled>".
					"</form>";
    		} 
    		else 
    		{
    			echo "<form name=nextform action=\"$this->url_prefix/index.php/search/results/".
					($page +1)."/$perpage/";

				if ($recordview)
				{
					echo "record";
				}

				echo "\" method=POST>".
					"<input type=hidden name=id value=\"$this->id\">".
					"<input type=hidden name=s1 value=\"$this->s1\">".
					"<input type=hidden name=s2 value=\"$this->s2\">".
					"<input type=hidden name=s3 value=\"$this->s3\">".
					"<input type=hidden name=s4 value=\"$this->s4\">".
					"<input type=hidden name=s5 value=\"$this->s5\">".
					"<input type=submit value=\"".lang('next')."\">".
					"</form>";
			}
    			echo "<form name=nextform action=\"$this->url_prefix/index.php/search/results/".
					$numpages."/$perpage/";

				if ($recordview)
				{
					echo "record";
				}

				echo "\" method=POST>".
					"<input type=hidden name=id value=\"$this->id\">".
					"<input type=hidden name=s1 value=\"$this->s1\">".
					"<input type=hidden name=s2 value=\"$this->s2\">".
					"<input type=hidden name=s3 value=\"$this->s3\">".
					"<input type=hidden name=s4 value=\"$this->s4\">".
					"<input type=hidden name=s5 value=\"$this->s5\">".
					"<input type=submit value=\"".lang('last')."\">".
					"</form>";
				

			echo " | ";
			echo lang('results_per_page');
			echo " <form name=\"resultsper\" method=POST>".
				"<input type=hidden name=id value=\"$this->id\">".
				"<input type=hidden name=s1 value=\"$this->s1\">".
				"<input type=hidden name=s2 value=\"$this->s2\">".
				"<input type=hidden name=s3 value=\"$this->s3\">".
				"<input type=hidden name=s4 value=\"$this->s4\">".
				"<input type=hidden name=s5 value=\"$this->s5\">".
				
				"<select name=\"perchoice\" onchange=\"".
				"this.form.action=this.options[this.selectedIndex].value;".
				"this.form.submit()\">".

                "<option selected value=\"$this->url_prefix/index.php/search/results/$page/$perpage\">$perpage</option>".

				"<option value=\"$this->url_prefix/index.php/search/results/".
				"$page/20/\">20</option>".

				"<option value=\"$this->url_prefix/index.php/search/results/".
				"$page/50\">50</option>".

				"<option value=\"$this->url_prefix/index.php/search/results/".
				"$page/100\">100</option>".

				"<option value=\"$this->url_prefix/index.php/search/results/".
				"$page/1000\">1000</option>".
				
				"</select></form>";
		}

		// record view link
		echo "&nbsp;&nbsp;&nbsp;&nbsp; <form method=post ".
			"action=\"$this->url_prefix/index.php/search/results/$page/1/record/\">".
			"<input type=hidden name=id value=\"$this->id\">".
			"<input type=hidden name=s1 value=\"$this->s1\">".
			"<input type=hidden name=s2 value=\"$this->s2\">".
			"<input type=hidden name=s3 value=\"$this->s3\">".
			"<input type=hidden name=s4 value=\"$this->s4\">".
			"<input type=hidden name=s5 value=\"$this->s5\">".
			"<input type=submit value=\"".lang('recordview')."\">".
			"</form> ";

		echo "<form method=post action=\"$this->url_prefix/index.php/search/results/$page/20\">".
			"<input type=hidden name=id value=\"$this->id\">".
			"<input type=hidden name=s1 value=\"$this->s1\">".
			"<input type=hidden name=s2 value=\"$this->s2\">".
			"<input type=hidden name=s3 value=\"$this->s3\">".
			"<input type=hidden name=s4 value=\"$this->s4\">".
			"<input type=hidden name=s5 value=\"$this->s5\">".
			"<input type=submit value=\"".lang('listview')."\">".
			"</form><br>";

		foreach($this->result->result() as $myresult)
		{
			// initialize variables;
			$acnum = 0;
			$serviceid = 0;
			$id = 0;
			$removed = '';
			$cancel_date = '';

			// get the account_number or service id in the search result
			foreach ($myresult as $key => $value) 
			{
				if ($key == "account_number") 
				{
					$acnum = $value;
				}

				if ($key == "user_services_id" OR $key == "user_services") 
				{
					$serviceid = $value;
				}

				if ($key == "id") 
				{
					$id = $value;
				}

				if ($key == 'removed') 
				{
					$removed = $value;
				}

				if ($key == 'cancel_date') 
				{
					$cancel_date = $value;
				}
			}	

			// if the row is a removed service, grey it out
			if ($removed == 'y' OR !empty($cancel_date)) 
			{
				$rowstyle = "background-color: eee; color: aaa;";
			} 
			else 
			{
				$rowstyle = "background-color: dde; color: black;";
			}

			if ($num_of_results == 1) 
			{
				// check if this is a service item and redirect to the service item
				if ($serviceid) 
				{
					print "<script language=\"JavaScript\">window.location.href = ".
						"\"$this->url_prefix/index.php/view/service/$serviceid/$acnum\";</script>";      
				} 
				else 
				{
					// else just redirect the account by account_number      				
					print "<script language=\"JavaScript\">window.location.href = ".
						"\"$this->url_prefix/index.php/view/account/$acnum\";</script>";

				}
			} 
			else 
			{
				// check if this is a service item and link to the service item
				if ($serviceid) 
				{
					echo "<tr style=\"$rowstyle\"><td><a href=\"$this->url_prefix/index.php/view/service/".
						"$serviceid\">".lang('view').": ".lang('service')."</a></td>";
				} 
				else 
				{      
					// else just link to the account by account number
					echo "<tr style=\"$rowstyle\"><td><a href=\"$this->url_prefix/index.php/view/account/".
						"$acnum\">". lang('view') . ": " . lang('account') ."</a></td>";
				}
			}

			if ($recordview == TRUE)
			{
				echo "<hr noshade>";
				
				// set the new account number to view
				$this->session->set_userdata('account_number', $acnum);
				$this->account_number = $acnum;
				
				// load the models necessary
				$this->load->model('customer_model');
				$this->load->model('module_model');
				$this->load->model('user_model');
				$this->load->model('support_model');
				$this->load->model('user_model');
				
				/*
				 * ---------------------------------------------------------------------
				 *  below here is the similar to the customer controller's index method
				 * ---------------------------------------------------------------------
				 */
				/*
				// load the module header common to all module views
				$this->load->view('module_header_view');
				*/
				// show the customer information (name, address, etc)
				$data = $this->customer_model->record($this->account_number);
				$this->load->view('customer/index_view', $data);
				
				// show a small preview of billing info
				$this->load->model('billing_model');
				$data['record'] = $this->billing_model->record_list($this->account_number);
				$this->load->view('billing/mini_index_view', $data);
				
				// show the services that they have assigned to them
				$this->load->model('service_model');			
				$data['categories'] = $this->service_model->service_categories(
					$this->account_number);
				$this->load->view('services/heading_view', $data);
				
				// output the list of services
				$data['services'] = $this->service_model->list_services(
					$this->account_number);
				$this->load->view('services/index_view', $data);

				/*
				// the history listing tabs
				$this->load->view('historyframe_tabs_view');			
				
				// the html page footer
				$this->load->view('html_footer_view');
				*/
				
			}
			else
			{
				foreach ($myresult as $key => $value) 
				{
					echo "<td>$value</td>\n";   
				}
			}
		} 

		if (empty($key)) 
		{
			echo "<tr><td><b>".lang('sorrynorecordsfound')."</b></td></tr>\n";
			echo "<tr><td><a href=\"$this->url_prefix/index.php/dashboard\"> ".
				lang('clickheretotryagain')."</a>";
		} 

	} // end results function


}

/* End of file search */
/* Location: ./application/controllers/search.php */
