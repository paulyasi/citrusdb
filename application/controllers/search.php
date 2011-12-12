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

		echo "$this->id $this->s1";

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
	
	public function listresults($page, $perpage)
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
    			echo "<form name=prevform action=\"$this->url_prefix/index.php/search/listresults/".
    				($page - 1) . "/$perpage/\" method=POST>".
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
				echo "<form name=nextform action=\"$this->url_prefix/index.php/search/listresults/".
					($page +1)."/$perpage/\" method=POST>".
					"<input type=submit value=\"".lang('next')."\" disabled>".
					"</form>";
    		} 
    		else 
    		{
    			echo "<form name=nextform action=\"$this->url_prefix/index.php/search/listresults/".
					($page +1)."/$perpage/\" method=POST>".
					"<input type=hidden name=id value=\"$this->id\">".
					"<input type=hidden name=s1 value=\"$this->s1\">".
					"<input type=hidden name=s2 value=\"$this->s2\">".
					"<input type=hidden name=s3 value=\"$this->s3\">".
					"<input type=hidden name=s4 value=\"$this->s4\">".
					"<input type=hidden name=s5 value=\"$this->s5\">".
					"<input type=submit value=\"".lang('next')."\">".
					"</form>";
			}
    			echo "<form name=nextform action=\"$this->url_prefix/index.php/search/listresults/".
					$numpages."/$perpage/\" method=POST>".
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
			echo " <form name=\"resultsper\" action=\"$this->url_prefix/index.php/search/listresults\" ".
				"method=POST>".
				"<input type=hidden name=id value=\"$this->id\">".
				"<input type=hidden name=s1 value=\"$this->s1\">".
				"<input type=hidden name=s2 value=\"$this->s2\">".
				"<input type=hidden name=s3 value=\"$this->s3\">".
				"<input type=hidden name=s4 value=\"$this->s4\">".
				"<input type=hidden name=s5 value=\"$this->s5\">".
				
				"<select name=\"perchoice\" onChange=\"window.location=".
				"document.resultsper.perchoice.options[document.resultsper.perchoice.selectedIndex].value\">".

                "<option selected value=\"$this->url_prefix/index.php/search/listresults/".
				"$page/$perpage\">$perpage</option>".

				"<option value=\"$this->url_prefix/index.php/search/listresults/".
				"$page/20/\">20</option>".

				"<option value=\"$this->url_prefix/index.php/search/listresults/".
				"$page/50\">50</option>".

				"<option value=\"$this->url_prefix/index.php/search/listresults/".
				"$page/100/&id=$this->id&s1=$this->s1&s2=$this->s2&s3=$this->s3".
				"&s4=$this->s4&s5=$this->s5&page=$page&perpage=20\">100</option>".

				"<option value=\"$this->url_prefix/index.php/search/listresults/".
				"$page/1000/&id=$this->id&s1=$this->s1&s2=$this->s2&s3=$this->s3".
				"&s4=$this->s4&s5=$this->s5&page=$page&perpage=20\">1000</option>".
				
				"</select></form>";
		}

		// record view link
		echo "&nbsp;&nbsp;&nbsp;&nbsp; <a href=\"$this->url_prefix/index.php/recordview/".
			"$page/1/?type=fs&id=$this->id&s1=$this->s1&s2=$this->s2".
			"&s3=$this->s3&s4=$this->s4&s5=$this->s5\">".
			lang ('recordview') . "</a> | ";
		echo "<a href=\"$this->url_prefix/index.php/listresults/$page/$perpage/".
			"?type=fs&id=$this->id&s1=$this->s1&s2=$this->s2&s3=$this->s3&s4=$this->s4".
			"&s5=$this->s5\">" . lang('listview') . "</a><br>";

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

			foreach ($myresult as $key => $value) 
			{
				echo "<td>$value</td>\n";   
			}	
		} 

		if (empty($key)) 
		{
			echo "<tr><td><b>".lang('sorrynorecordsfound')."</b></td></tr>\n";
			echo "<tr><td><a href=\"$this->url_prefix/index.php/dashboard\"> ".
				lang('clickheretotryagain')."</a>";
		} 

	} // end listrecords


	public function recordview()
	{
		// record view link
		echo "&nbsp;&nbsp;&nbsp;&nbsp; <a href=\"$this->url_prefix/index.php?load=dosearch&type=fs&id=$id&s1=$s1&s2=$s2&s3=$s3&s4=$s4&s5=$s5&page=$page&perpage=1&pagetype=record\">$l_recordview</a> | ";
		echo "<a href=\"$this->url_prefix/index.php?load=dosearch&type=fs&id=$id&s1=$s1&s2=$s2&s3=$s3&s4=$s4&s5=$s5&page=$page&perpage=20&pagetype=list\">$l_listview</a><br>";

		// set the new account number to view
		$account_number = $acnum;
		$_SESSION['account_number'] = $account_number;

		// print the customer record within the result page
		echo "<hr noshde>";
		$load = "customer"; // allow load of customer record
		$type = "module";
		$this->load->view('customer/index_view');
		$load = "dosearch"; // allow search result load after
		$type = "fs";   
		echo '</table>'; 
	}

}

/* End of file search */
/* Location: ./application/controllers/search.php */
