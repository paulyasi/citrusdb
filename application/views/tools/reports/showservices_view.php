<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>".lang('servicereport').": ".$description."</h3>";

$billing_type_array = array();
$billing_status_array = array();
$collections_type_array = array();
$authorized_type_array = array();
$turnedoff_type_array = array();
$new_type_array = array();
$canceled_type_array = array();
$pastdue_type_array = array();
$canceled_reason_array = array();

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

