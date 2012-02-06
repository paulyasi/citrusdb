<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
echo "<h3>".lang('pending')." ".lang('refund')."</h3>";
echo "<table>";

foreach ($pendingrefund AS $myresult) 
{
	$batchid = $myresult['bd_batch'];
	$invoice_number = $myresult['bd_invoice_number'];
	$user = "refund";
	$org_name = $myresult['org_name'];
	$mybilling_id = $myresult['b_id'];
	$billing_name = $myresult['b_name'];
	$billing_company = $myresult['b_company'];
	$billing_street =  $myresult['b_street'];
	$billing_city = $myresult['b_city'];
	$billing_state = $myresult['b_state'];
	$billing_zip = $myresult['b_zip'];
	$billing_acctnum = $myresult['b_acctnum'];
	$billing_fromdate = $myresult['b_from_date'];
	$billing_todate = $myresult['b_to_date'];
	$billing_payment_due_date = $myresult['b_payment_due_date'];
	$precisetotal = $myresult['RefundTotal'];

	echo "<td>$org_name</td><td>$mybilling_id</td><td>$billing_name</td><td>$billing_company</td><td>$precisetotal</td></tr>";

} // end while

echo "</table>";


?>



</body>
</html>







