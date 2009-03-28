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
  $base->input['organization_id'] = "1"; 
}
$organization_id = $base->input['organization_id'];
$changestatus = $base->input['changestatus'];
$billingid = $base->input['billingid'];

//$DB->debug = true;

/*---------------------------------------------------------------------------*/
// set the status of the indicated account
/*---------------------------------------------------------------------------*/
if ($changestatus == "cancel") {
  // set the payment_history status to canceled      
  $query = "INSERT INTO payment_history ".
    "(creation_date, billing_id, status) ".
    "VALUES (CURRENT_DATE,'$billingid','canceled')";
  $paymentresult = $DB->Execute($query) or die ("$l_queryfailed");
 }

if ($changestatus == "collections") {
  // set the payment_history status to canceled      
  $query = "INSERT INTO payment_history ".
    "(creation_date, billing_id, status) ".
    "VALUES (CURRENT_DATE,'$billingid','collections')";
  $paymentresult = $DB->Execute($query) or die ("$l_queryfailed");
 }

/*---------------------------------------------------------------------------*/
// print the listing of accounts in cancelwfee or collections status
/*---------------------------------------------------------------------------*/
echo "<h3>$l_collections</h3>";

// print column heading
echo "<table cellpadding=5><tr style=\"background: #bbb;\"><td>Status</td>".
"<td>Acct Number</td><td>Billing ID</td><td>Name</td><td>Company</td>";
echo "<td>Modify Status</td></tr>";

// get the most recent payment history id for each billing id
$query = "SELECT max(id) my_id, billing_id my_bid FROM payment_history ".
  "GROUP BY billing_id ORDER BY billing_id";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
while($myresult = $result->FetchRow()) {
  $recentpaymentid = $myresult['my_id'];
  $query = "SELECT ph.billing_id, b.account_number, b.name, b.company, ".
    "ph.status, ms.service_description ".
    "FROM payment_history ph ".
    "LEFT JOIN billing b ON b.id = ph.billing_id ".
    "LEFT JOIN billing_details bd ON b.id = bd.billing_id ".
    "LEFT JOIN user_services us ON us.id = bd.user_services_id ".
    "LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
    "WHERE ph.id = $recentpaymentid AND ".
    "ph.status IN ('cancelwfee', 'collections') LIMIT 1";
  $paymentresult = $DB->Execute($query) or die ("$l_queryfailed");
  while($mypaymentresult = $paymentresult->FetchRow()) {
    $billing_id = $mypaymentresult['billing_id'];
    $account_number = $mypaymentresult['account_number'];
    $name = $mypaymentresult['name'];
    $company = $mypaymentresult['company'];
    $status = $mypaymentresult['status'];


    if ($status == 'cancelwfee') {
      echo "<tr style=\"background: #eae;\"><td>$status</td>";
    }
    else {
      echo "<tr style=\"background: #ccc;\"><td>$status</td>";
    }

    echo "<td><a href=\"index.php?load=viewaccount&type=fs&acnum=$account_number\" target=\"_blank\">$account_number</a></td>".
      "<td>$billing_id</td>".
      "<td>$name</td>".
      "<td>$company</td>";

    // show collections button for cancelwfee and cancel button for collections
    if ($status == 'cancelwfee') {
      echo "<td><form style=\"margin-bottom:0;\" action=\"index.php\">".
	"<input type=hidden name=load value=collections>".
	"<input type=hidden name=type value=tools>".
	"<input type=hidden name=changestatus value=collections>".
	"<input type=hidden name=billingid value=$billing_id>".
	"<input type=\"SUBMIT\" NAME=\"submit\" value=\"$l_collections\" class=\"smallbutton\"> ".
	"</form></td>";
    } else {
      echo "<td><form style=\"margin-bottom:0;\" action=\"index.php\">".
	"<input type=hidden name=load value=collections>".
	"<input type=hidden name=type value=tools>".
	"<input type=hidden name=changestatus value=cancel>".
	"<input type=hidden name=billingid value=$billing_id>".	
	"<input type=\"SUBMIT\" NAME=\"submit\" value=\"$l_cancelcustomer\" class=\"smallbutton\"> ".
	"</form></td>";
    }
    echo "</tr>";
  }
 }

// TODO: ADD A WAY TO MOVE CANCELWFEE INTO COLLECTIONS STATUS
// TODO: ADD A WAY TO MOVE COLLECTIONS STATUS INTO REGULAR CANCELED

?>
</body>
</html>
