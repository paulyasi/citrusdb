<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_servicereport: ";
// Copyright (C) 2008  Paul Yasi (paul at citrusdb dot org)
// Read the README file for more information
/*----------------------------------------------------------------------------*/
// Check for authorized accesss
/*----------------------------------------------------------------------------*/
if(constant("INDEX_CITRUS") <> 1){
	echo "You must be logged in to run this.  Goodbye.";
	exit;	
}

if (!defined("INDEX_CITRUS")) {
	echo "You must be logged in to run this.  Goodbye.";
        exit;
}

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['manager'] == 'n') {
	echo "$l_youmusthaveadmin<br>";
        exit; 
}

/*
$empty_day_1  = date("Y-m-d", mktime(0, 0, 0, date("m")-1  , date("d"), date("Y")));
$empty_day_2  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));

// Get Variables
if (!isset($base->input['day1'])) { $base->input['day1'] = "$empty_day_1"; }
if (!isset($base->input['day2'])) { $base->input['day2'] = "$empty_day_2"; }
*/
if (!isset($base->input['service_id'])) { $base->input['service_id'] = ""; }

//$day1 = $base->input['day1'];
//$day2 = $base->input['day2'];
$service_id = $base->input['service_id'];

if ($service_id) {

  //GET SERVICE NAME
  $query = "SELECT service_description FROM master_services WHERE id = $service_id";
  $result = $DB->Execute($query) or die ("service name $l_queryfailed");
  $myresult = $result->fields;
  $description = $myresult['service_description'];

  echo "$description</h3>";

  //$DB->debug = true;
  /*--------------------------------------------------------------------------*/
  // NUMBER ADDED DURING THIS PERIOD and billing type for each added
  $query = "SELECT DISTINCT us.id us_id, bi.id bi_id, bt.method bt_method, " .
    "cr.reason cancel_reason FROM user_services us " .
    "LEFT JOIN master_services ms ON ms.id = us.master_service_id " .
    "LEFT JOIN billing bi ON bi.id = us.billing_id " .
    "LEFT JOIN billing_types bt ON bt.id = bi.billing_type " .
    "LEFT JOIN customer cu ON us.account_number = cu.account_number " .
    "LEFT JOIN cancel_reason cr ON cu.cancel_reason = cr.id " .
    "WHERE ms.id = '$service_id'";
  //$query = "SELECT COUNT(us.id) AS ServiceCount,ms.service_description FROM user_services us LEFT JOIN master_services ms ON ms.id = us.master_service_id WHERE ms.id = $service_id AND date(start_datetime) BETWEEN '$day1' AND '$day2' GROUP BY ms.id";
  $result = $DB->Execute($query) or die ("$query $l_queryfailed");

  $billing_type_array = array();
  $billing_status_array = array();
  $collections_type_array = array();
  $authorized_type_array = array();
  $turnedoff_type_array = array();
  $new_type_array = array();
  $canceled_type_array = array();
  $pastdue_type_array = array();
  $canceled_reason_array = array();

  while ($myresult = $result->FetchRow()) {
		// get the invoice data to process now
		//$user_service_id = $myresult['us_id'];
		$billing_id = $myresult['bi_id'];
		$cancel_reason = $myresult['cancel_reason'];
		// increment the billing method counter
		$billing_method = $myresult['bt_method'];
		$billing_type_array["$billing_method"]++;
		

		// increment the billing status counter
		$billing_status = billingstatus($billing_id);
			
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

		
		
		$service_count++;
  }
  echo "<h2>$l_added: $service_count</h2>\n";

  /*
  echo "<h4>$l_billingtype</h4><blockquote>\n";

  foreach ($billing_type_array as $method=>$value) {
    echo "$method: $value<br>\n";
  }
  */

  $active= "";
  $inactive = "";
  $other = "";
  $declinedvalue = 0;

  ksort ($billing_status_array);

  foreach ($billing_status_array as $status=>$value) {
    
    if ($status == "Authorized" OR $status == "New" OR $status == "Past Due") {
      $active .= "<p><b>$status $value</b>\n";

      if ($status == "Authorized") {
	ksort ($authorized_type_array);
	foreach ($authorized_type_array as $method=>$value) {
	  $active .= "&nbsp;&nbsp;&nbsp;$method: $value\n";
	}
      }

      if ($status == "New") {
	ksort ($new_type_array);
	foreach ($new_type_array as $method=>$value) {
	  $active .="&nbsp;&nbsp;&nbsp;$method: $value\n";
	}
      }

      if ($status == "Past Due") {
	ksort ($pastdue_type_array);
	foreach ($pastdue_type_array as $method=>$value) {
	  $active .="&nbsp;&nbsp;&nbsp;$method: $value\n";
	}
      }
    
    } else {
  
      if ($status == "Declined" OR $status == "Initial Decline") {
	$declinedvalue = $declinedvalue + $value;
      
      } else {

	if ($status == "Collections" OR $status == "Turned Off" OR $status == "Canceled" OR $status == "Canceled w/Fee") {
	  $inactive .= "<p><b>$status: $value</b>\n";

	  if ($status == "Collections") {
	    ksort ($collections_type_array);
	    foreach ($collections_type_array as $method=>$value) {
	      $inactive .= "&nbsp;&nbsp;&nbsp;$method: $value\n";
	    }
	  }

	  if ($status == "Turned Off") {
	    ksort ($turnedoff_type_array);
	    foreach ($turnedoff_type_array as $method=>$value) {
	      $inactive .= "&nbsp;&nbsp;&nbsp;$method: $value\n";
	    }
	  }

	  if ($status == "Canceled") {
	    ksort ($canceled_type_array);
	    foreach ($canceled_type_array as $method=>$value) {
	      $inactive .= "&nbsp;&nbsp;&nbsp;$method: $value\n";
	    }

            // print cancel reasons
            $inactive .= "<br>\n";
	    arsort ($canceled_reason_array);
	    foreach ($canceled_reason_array as $method=>$value) {
	      $inactive .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$method: $value<br>\n";
	    }
	    
	  }
	} else {
	  $other .= "<p><b>$status: $value</b>\n";
	}
      }
    }
  }  

  echo "<h3>Active</h3><blockquote>$active <p><b>Declined: $declinedvalue</b></blockquote><p><h3>Inactive</h3><blockquote>$inactive </blockquote><p><h3>Other</h3><blockquote>$other</blockquote>\n";


 } else {

echo "<FORM ACTION=\"index.php\" METHOD=\"GET\"><table>
<select name=\"service_id\">";

// get the list of services from the table
$query = "SELECT * FROM master_services WHERE frequency > 0 ORDER BY service_description";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");

while ($myresult = $result->FetchRow()) {
  $id = $myresult['id'];
  $description = $myresult['service_description'];
  
  echo "<option value=\"$id\">$description</option>";
 
}

echo "</select><input type=hidden name=type value=tools>
	<input type=hidden name=load value=servicereport>
	</td><tr> 
	<td></td><td><br><input type=submit name=\"$l_submit\" value=\"submit\"></td>
	</table>
	</form> <p>";
 }
		
?>
</body>
</html>







