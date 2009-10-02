<html>
<head>
<LINK href="citrus.css" type=text/css rel=STYLESHEET>
<LINK href="fullscreen.css" type=text/css rel=STYLESHEET>
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
</head>
<body bgcolor="#eedddd" marginheight=0 marginwidth=1 leftmargin=1 rightmargin=0>
<?php
/*--------------------------------------------------------------------*/
// Check for authorized accesss
/*--------------------------------------------------------------------*/
if(constant("INDEX_CITRUS") <> 1){
  echo "You must be logged in to run this.  Goodbye.";
  exit;	
}
	
if (!defined("INDEX_CITRUS")) {
  echo "You must be logged in to run this.  Goodbye.";
  exit;
}
	
// GET Variables
$account_number = $base->input['account_number'];

echo "<table cellspacing=0 cellpadding=2 border=0>".
"<td bgcolor=\"#eedddd\" width=40><b>$l_id</b></td>".
"<td bgcolor=\"#eedddd\" width=40><b>$l_invoice</b></td>".
"<td bgcolor=\"#eedddd\" width=100><b>$l_date</b></td>".
"<td bgcolor=\"#eedddd\" width=75><b>$l_type</b></td>".
"<td bgcolor=\"#eedddd\" width=100><b>$l_status</b></td>".
"<td bgcolor=\"#eedddd\" width=50><b>$l_avs</b></td>".
"<td bgcolor=\"#eedddd\" width=190><b>$l_number</b></td>".
"<td bgcolor=\"#eedddd\" width=100><b>$l_amount</b></td>";

// get the billing_history for this account
// the account number is stored in the corresponding billing record

$query = "SELECT p.id p_id, p.creation_date p_cdate, p.payment_type ".
  "p_payment_type, p.status p_status, p.billing_id p_billing_id, ".
  "p.invoice_number p_invoice_number, ".
  "p.billing_amount p_billing_amount, p.response_code p_response_code, ".
  "p.avs_response p_avs_response, p.check_number p_check_number, ".
  "c.account_number c_acctnum, p.creditcard_number p_creditcard_number, ".
  "b.account_number b_acctnum, b.id b_id ".
  "FROM payment_history p ".
  "LEFT JOIN billing b ON p.billing_id = b.id ".
  "LEFT JOIN customer c ON b.account_number = c.account_number ".
  "WHERE b.account_number = '$account_number' ORDER BY p.id DESC";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");

// initialize nsf counter to print only 3 recent ones
$nsfcount = 0;

while ($myresult = $result->FetchRow()) {
  $id = $myresult['p_id']; // payment id
  $bid = $myresult['b_id']; // billind id
  $date = $myresult['p_cdate'];
  $type = $myresult['p_payment_type'];
  $status = $myresult['p_status'];
  $response = $myresult['p_response_code'];
  $avs_response = $myresult['p_avs_response'];
  $check_number = $myresult['p_check_number'];
  $creditcard_number = $myresult['p_creditcard_number'];
  $amount = sprintf("%.2f",$myresult['p_billing_amount']);
  $billingid = $myresult['p_billing_id'];
  $invoice_number = $myresult['p_invoice_number'];
  
  // translate the status
  switch ($status) 
    {
    case 'authorized':
      $status = $l_authorized;
      break;
    case 'declined':
      $status = $l_declined;
      break;
    case 'pending':
      $status = $l_pending;
      break;
    case 'donotreactivate':
      $status = $l_donotreactivate;
      break;
    case 'collections':
      $status = $l_collections;
      break;
    case 'pastdue':
      $status = $l_pastdue;
      break;
    case 'noticesent':
      $status = $l_noticesent;
      break;
    case 'waiting':
      $status = $l_waiting;
      break;      
    case 'turnedoff':
      $status = $l_turnedoff;
      break;
    case 'credit':
      $status = $l_credit;
      break;
    case 'canceled':
      $status = $l_canceled;
      break;
    case 'cancelwfee':
      $status = $l_cancelwithfee;
      break;
    }
  
  print "<tr bgcolor=\"#ffeeee\">";
  print "<td style=\"border-top: 1px solid grey;\">$bid &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey;\">$invoice_number &nbsp;</td>";  
  print "<td style=\"border-top: 1px solid grey;\">$date &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey;\">$type &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey;\">$status &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey;\">$avs_response &nbsp;</td>";
  print "<td style=\"border-top: 1px solid grey;\">$creditcard_number $check_number &nbsp;";

  // check if we should print the nsf link
  // make sure that the amount is greater than zero and only
  // print the three most recent ones

  // query user properties to see if we should print this special stuff
  $query = "SELECT * FROM user WHERE username='$user' LIMIT 1";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $userresult = $DB->Execute($query) or die ("$l_queryfailed");
  $myuserresult = $userresult->fields;
  
  if (($amount > 0) AND ($nsfcount < 3)
      AND (($type == 'check') OR ($type == 'cash') OR ($type == 'eft'))) {
    if (($myuserresult['manager'] == 'y') OR ($myuserresult['admin'] == 'y')) {
      echo "<a href=\"index.php?load=billing&type=module&nsf=on=&".
	"paymentid=$id&amount=$amount&invoicenum=$invoice_number&".
	"billingid=$billingid\" target=\"_parent\" style=\"font-size: 8pt;\">Mark as NSF</a>";    }
    $nsfcount++;
  }

  if (($status == $l_pastdue) OR ($status == $l_turnedoff) OR ($status == $l_canceled) OR ($status == $l_declined) OR ($status == $l_waiting) OR ($status == $l_noticesent) OR ($status == $l_cancelwithfee)) {
    if (($myuserresult['manager'] == 'y') OR ($myuserresult['admin'] == 'y')) {
      echo "<a href=\"index.php?load=billing&type=module&deletepayment=on=&".
	"paymentid=$id\" target=\"_parent\" style=\"font-size: 8pt;\">Delete</a>";
    }
  }

  // check if we should print the delete status link
  
  
  print "</td>";
  print "<td style=\"border-top: 1px solid grey;\">$amount &nbsp;";

  if (($amount > 0) AND ($status == $l_authorized)) {
    echo "<a href=\"index.php?load=receipt&type=fs&".
      "paymentid=$id&amount=$amount&invoicenum=$invoice_number&".
      "billingid=$billingid&date=$date\" target=\"_parent\" style=\"font-size: 8pt;\">Receipt</a>";     
  }
  
  print "</td>";
 }


echo '</table>';
?>
</body>
</html>
