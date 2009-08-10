<html>
<body bgcolor="#ffffff">
<?php
// Copyright (C) 2003-2008  Paul Yasi (paul at citrusdb.org)
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

// GET Variables
if (!isset($base->input['organization_id'])) {
  $base->input['organization_id'] = "1"; }
if (!isset($base->input['changestatus'])) {
  $base->input['changestatus'] = ""; }
if (!isset($base->input['viewstatus'])) {
  $base->input['viewstatus'] = "waiting"; }
if (!isset($base->input['billingid'])) {
  $base->input['billingid'] = ""; }

$organization_id = $base->input['organization_id'];
$changestatus = $base->input['changestatus'];
$viewstatus = $base->input['viewstatus'];
$billingid = $base->input['billingid'];


// ask for the organization that they want to view
echo "<FORM ACTION=\"index.php\" METHOD=\"GET\" name=\"form1\"> ".
"<input type=hidden name=load value=billing_status>".
"<input type=hidden name=type value=tools>";

// print list of organizations to choose from
$query = "SELECT id,org_name FROM general";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
echo "<b>$l_organizationname</b><td><select name=\"organization_id\"> ";
echo "<option> </option> ";
while ($myresult = $result->FetchRow()) {
  $myid = $myresult['id'];
  $myorg = $myresult['org_name'];
  echo "<option value=\"$myid\">$myorg</option>";
 }
echo "</select>";

// ask what status they want to view
echo "&nbsp;&nbsp; $l_billingstatus: <select name=\"viewstatus\">";
echo "<option> </option> ";
echo "<option value=\"authorized\">$l_authorized</option>";
echo "<option value=\"declined\">$l_declined</option>";
echo "<option value=\"pending\">$l_pending</option>";
echo "<option value=\"collections\">$l_collections</option>";
echo "<option value=\"turnedoff\">$l_turnedoff</option>";
echo "<option value=\"canceled\">$l_canceled</option>";
echo "<option value=\"cancelwfee\">$l_cancelwithfee</option>";
echo "<option value=\"pastdue\">$l_pastdue</option>";
echo "<option value=\"noticesent\">$l_noticesent</option>";
echo "<option value=\"waiting\">$l_waiting</option>";
echo "</select>";

echo "<input type=\"SUBMIT\" NAME=\"submit\" value=\"$l_submit\"></form><p>";

//$DB->debug = true;

/*---------------------------------------------------------------------------*/
// set the status for the indicated account
/*---------------------------------------------------------------------------*/
switch ($changestatus) {


 case 'waiting':
   $query = "INSERT INTO payment_history ".
     "(creation_date, billing_id, status) ".
     "VALUES (CURRENT_DATE,'$billingid','waiting')";
   $paymentresult = $DB->Execute($query) or die ("$l_queryfailed");
   break;

 case 'turnedoff':
   $query = "INSERT INTO payment_history ".
     "(creation_date, billing_id, status) ".
     "VALUES (CURRENT_DATE,'$billingid','turnedoff')";
   $paymentresult = $DB->Execute($query) or die ("$l_queryfailed");
   break;
   
 case 'canceled':
   $query = "INSERT INTO payment_history ".
     "(creation_date, billing_id, status) ".
     "VALUES (CURRENT_DATE,'$billingid','canceled')";
   $paymentresult = $DB->Execute($query) or die ("$l_queryfailed");
   break;
   
 case 'cancelwfee':
   // set the payment_history status to canceled      
   $query = "INSERT INTO payment_history ".
     "(creation_date, billing_id, status) ".
     "VALUES (CURRENT_DATE,'$billingid','cancelwfee')";
   $paymentresult = $DB->Execute($query) or die ("$l_queryfailed");
   break;
   
 case 'collections':
   // set the payment_history status to canceled      
   $query = "INSERT INTO payment_history ".
     "(creation_date, billing_id, status) ".
     "VALUES (CURRENT_DATE,'$billingid','collections')";
   $paymentresult = $DB->Execute($query) or die ("$l_queryfailed");
   break;
   
 }

/*---------------------------------------------------------------------------*/
// print the listing of accounts
/*---------------------------------------------------------------------------*/

// print the organization being viewed
$query = "SELECT org_name from general WHERE id = $organization_id";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
$org_name = $myresult['org_name'];
  
echo "<h3>$org_name: ";

// print the status being viewed
switch ($viewstatus) {
 case 'authorized':
   echo "$l_authorized</h3>";
   break;
 case 'declined':
   echo "$l_declined</h3>";
   break;
 case 'pending':
   echo "$l_pending</h3>";
   break;
 case 'collections':
   echo "$l_collections</h3>";
   break;
 case 'turnedoff':
   echo "$l_turnedoff</h3>";
   break;
 case 'canceled':
   echo "$l_canceled</h3>";
   break;
 case 'cancelwfee':
   echo "$l_cancelwithfee</h3>";
   break;
 case 'pastdue':
   echo "$l_pastdue</h3>";
   break;
 case 'noticesent':
   echo "$l_noticesent</h3>";
   break;
 case 'waiting':
   echo "$l_waiting</h3>";
   break;
 }


// print column heading
echo "<table cellpadding=5><tr style=\"background: #bbb;\"><td>Status</td>".
"<td>Acct Number</td><td>Past Invoice</td><td>Name</td><td>Company</td>";
echo "<td>Past Amount Due</td><td>Due Date</td>";
echo "<td>Modify Status</td></tr>";

// get the most recent payment history id for each billing id
$query = "SELECT max(ph.id) my_id, ph.billing_id my_bid ".
  "FROM payment_history ph ".
  "LEFT JOIN billing b ON b.id = ph.billing_id ".
  "WHERE b.organization_id = $organization_id ".
  "GROUP BY ph.billing_id ORDER BY ph.billing_id";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("result $l_queryfailed");
while($myresult = $result->FetchRow()) {
  $recentpaymentid = $myresult['my_id'];

  $query = "SELECT ph.billing_id, b.account_number, b.name, b.company, ".
    "ph.status, bd.invoice_number, bh.payment_due_date, c.cancel_date ".
    "FROM payment_history ph ".
    "LEFT JOIN billing b ON b.id = ph.billing_id ".
    "LEFT JOIN billing_details bd ON bd.billing_id = b.id ".
    "LEFT JOIN billing_history bh ON bd.invoice_number = bh.id ".
    "LEFT JOIN customer c ON c.account_number = b.account_number ".
    "WHERE ph.id = $recentpaymentid AND ".
    "ph.status = '$viewstatus' AND bd.billed_amount <> bd.paid_amount LIMIT 1";
  
  $paymentresult = $DB->Execute($query) or die ("paymentresult $l_queryfailed");
  while($mypaymentresult = $paymentresult->FetchRow()) {    
    $account_number = $mypaymentresult['account_number'];
    $billing_id = $mypaymentresult['billing_id'];    
    $name = $mypaymentresult['name'];
    $company = $mypaymentresult['company'];
    $status = $mypaymentresult['status'];
    $invoice_number = $mypaymentresult['invoice_number'];
    $payment_due_date = $mypaymentresult['payment_due_date'];

    $pastcharges = sprintf("%.2f",total_pastdueitems($DB, $billing_id));
    
    echo "<tr style=\"background: #ccc;\"><td>$status</td>";
    echo "<td><a href=\"index.php?load=viewaccount&type=fs&acnum=$account_number\" target=\"_blank\">$account_number</a></td>".
      "<td>$invoice_number</td>".
      "<td>$name</td>".
      "<td>$company</td>".
      "<td>$pastcharges</td>".
      "<td>$payment_due_date</td>";
    

    // show status modification dropdown menu
    echo "<td><form style=\"margin-bottom:0;\" action=\"index.php\">".
      "<input type=hidden name=load value=billing_status>".
      "<input type=hidden name=type value=tools>".

      "<select name=\"changestatus\" style=\"font-size: 9px;\">".
      "<option value=\"\">$l_choose</option>".     
      "<option value=\"collections\">$l_collections</option>".
      "<option value=\"turnedoff\">$l_turnedoff</option>".
      "<option value=\"canceled\">$l_canceled</option>".
      "<option value=\"cancelwfee\">$l_cancelwithfee</option>".
      "<option value=\"waiting\">$l_waiting</option>".
      "</select>".
      
      "<input type=hidden name=billingid value=$billing_id>".
      "<input type=hidden name=organization_id value=$organization_id>".
      "<input type=hidden name=viewstatus value=$viewstatus>".
      "<input type=\"SUBMIT\" NAME=\"submit\" value=\"$l_submit\" class=\"smallbutton\"> ".
      "</form></td>";
    echo "</tr>";
  }
 }

?>
</table>
</body>
</html>
