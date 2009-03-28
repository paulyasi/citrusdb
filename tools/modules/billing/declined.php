<?php
// Copyright (C) 2005-2009  Paul Yasi (paul at citrusdb.org)
// read the README file for more information
/*----------------------------------------------------------------------------*/
// Check for authorized accesss
/*----------------------------------------------------------------------------*/
echo "<h3>$l_paymentstatus</h3>";
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

// Get Variables
if (!isset($base->input['day1'])) { $base->input['day1'] = ""; }
if (!isset($base->input['day2'])) { $base->input['day2'] = ""; }
if (!isset($base->input['organization_id']))
  { $base->input['organization_id'] = ""; }
if (!isset($base->input['showpaymenttype']))
  { $base->input['showpaymenttype'] = ""; }
if (!isset($base->input['showstatus'])) { $base->input['showstatus'] = ""; }

$day1 = $base->input['day1'];
$day2 = $base->input['day2'];
$organization_id = $base->input['organization_id'];
$showpaymenttype = $base->input['showpaymenttype'];
$showstatus = $base->input['showstatus'];

if ($day1) {

  // get the organization info
  $query = "SELECT org_name FROM general WHERE id = $organization_id LIMIT 1";
  $orgresult = $DB->Execute($query) or die ("$l_queryfailed");
  $myorgresult = $orgresult->fields;
  $organization_name = $myorgresult['org_name']; 
  
  echo "<b>$organization_name, $day1 $l_to $day2 $showpaymenttype $showstatus</b>";
  
  $query = "SELECT p.*, b.name, b.phone FROM payment_history p ".
    "LEFT JOIN billing b ON p.billing_id = b.id WHERE p.creation_date ".
    "BETWEEN '$day1' AND '$day2' AND b.organization_id = $organization_id ".
    "AND p.payment_type = '$showpaymenttype'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");

  echo "<table><tr style=\"text-decoration: underline;\"><td>$l_name</td><td>$l_phone</td><td>$l_billingid</td><td>$l_number</td><td>$l_response</td><td>$l_amount</td><tr>";
  $totalrun = 0;
  $statusrun = 0;

  while ($myresult = $result->FetchRow()) {
    $billing_id = $myresult['billing_id'];
    $creditcard_number = $myresult['creditcard_number'];
    $check_number = $myresult['check_number'];
    $response_code = $myresult['response_code'];
    $billing_amount = $myresult['billing_amount'];
    $status = $myresult['status'];
    $name = $myresult['name'];
    $phone = $myresult['phone'];
    
    if ($status == $showstatus) {
      echo "<td>$name</td>";
      echo "<td>$phone</td>";
      echo "<td>$billing_id</td>";
      echo "<td>$creditcard_number $check_number</td>";
      echo "<td>$response_code</td>";
      echo "<td>$billing_amount</td>";
      echo "<tr>";
      $statusrun++;
    }
    
    // add to total for authorized also, don't count other status like credits
    if (($status == 'authorized')
	OR ($status == 'declined')
	OR ($status == 'credit')) {
      $totalrun++;
    }
  }
  echo "</table>";
  
  // print the total number of transactions run and the total number of declines
  echo "<p>$l_total $showpaymenttype: $totalrun, $showstatus: $statusrun";
  
  /*--------------------------------------------------------------------*/
  // get the number of DISTINCT cards, run vs. declined
  /*--------------------------------------------------------------------*/
  $query = "SELECT DISTINCT(p.creditcard_number), p.status, b.name, b.phone ".
    "FROM payment_history p ".
    "LEFT JOIN billing b ON p.billing_id = b.id ".
    "WHERE p.creation_date BETWEEN '$day1' AND '$day2' ".
    "AND b.organization_id = $organization_id ".
    "AND p.payment_type = 'creditcard' AND p.status = 'declined'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  
  $totalrun = 0;
  $declinedrun = 0;
  
  while ($myresult = $result->FetchRow()) {
    $declinedrun++;
  }
  
  // print the total DISTINCT number of declines
  echo "<p>DISTINCT $l_declined: $declinedrun";
  
  
  // print the number of non-creditcard payments
  
  $query = "SELECT p.*, b.name, b.phone FROM payment_history p ".
    "LEFT JOIN billing b ON p.billing_id = b.id WHERE p.creation_date ".
    "BETWEEN '$day1' AND '$day2' AND b.organization_id = $organization_id ".
    "AND p.payment_type <> 'creditcard'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");

  echo "<p>Other Payments<br>";
  $check = 0;
  $cash = 0;
  $eft = 0;
  $other = 0;
  
  while ($myresult = $result->FetchRow()) {
    $billing_id = $myresult['billing_id'];
    $payment_type = $myresult['payment_type'];
    $response_code = $myresult['response_code'];
    $billing_amount = $myresult['billing_amount'];
    $status = $myresult['status'];
    $name = $myresult['name'];
    $phone = $myresult['phone'];
    
    switch ($payment_type) {
    case "check":
      $check++;
      break;
    case "cash":
      $cash++;
      break;
    case "eft":
      $eft++;
      break;
    default:
      $other++;
    }
  }
  
  echo "cash: $cash <br> check: $check <br> eft: $eft <br> other: $other";
  
 } else {
  // show the form to pick what day to view
  $day_1  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
  $day_2  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));
  $day_3  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-2, date("Y")));
  $day_4  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-3, date("Y")));
  $day_5  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-4, date("Y")));
  
  echo "<FORM ACTION=\"index.php\" METHOD=\"GET\">".
    "<table>".
    "<td>$l_from: <input type=text name=\"day1\" value=\"$day_1\"></td>".
    "<td> - $l_to: <input type=text name=\"day2\"value=\"$day_1\"></td>";

  // print list of organizations to choose from
  $query = "SELECT id,org_name FROM general";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  echo "<tr><td>$l_organizationname: ".
    "<select name=\"organization_id\">".
    "<option value=\"\">$l_choose</option>";
  while ($myresult = $result->FetchRow()) {
    $myid = $myresult['id'];
    $myorg = $myresult['org_name'];
    echo "<option value=\"$myid\">$myorg</option>";
  }

  echo "</td><td> $l_billingtype: ".
    "<select name=\"showpaymenttype\">".
    "<option value=\"creditcard\">creditcard</option>".
    "<option value=\"check\">check</option>".
    "<option value=\"cash\">cash</option>".
    "<option value=\"eft\">eft</option>".
    "<option value=\"nsf\">nsf</option>".
    "</select></td>";

  echo "<tr><td>$l_status: ".
    "<select name=\"showstatus\">".
    "<option value=\"declined\">declined</option>".    
    "<option value=\"authorized\">authorized</option>".
    "<option value=\"credit\">credit</option>".
    "</select></td>";

  echo "<input type=hidden name=load value=billing>".
    "<input type=hidden name=tooltype value=module>".
    "<input type=hidden name=type value=tools>".
    "<input type=hidden name=declined value=on>".
    "<td><input type=submit name=\"$l_submit\"></td>".
    "</table>".
    "</form> ";
 }	
?>
</body>
</html>







