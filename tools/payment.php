<?php
echo "<h3>$l_enterpayments</h3>";
// Copyright (C) 2002-2009  Paul Yasi (paul at citrusdb.org)
// read the README file for more information
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

//GET Variables
if (!isset($base->input['account_num'])) { $base->input['account_num'] = ""; }
if (!isset($base->input['billing_id'])) { $base->input['billing_id'] = ""; }
if (!isset($base->input['amount'])) { $base->input['amount'] = ""; }
if (!isset($base->input['payment_type'])) { $base->input['payment_type'] = ""; }
if (!isset($base->input['invoice_number'])) { $base->input['invoice_number'] = ""; }
if (!isset($base->input['check_number'])) { $base->input['check_number'] = ""; }

$submit = $base->input['submit'];
$account_num = $base->input['account_num'];
$billing_id = $base->input['billing_id'];
$amount = $base->input['amount'];
$payment_type = $base->input['payment_type'];
$invoice_number = $base->input['invoice_number'];
if ($invoice_number == '') { $invoice_number = 0; }
$check_number = $base->input['check_number'];

if ($submit) {

  //$DB->debug = true;
  
  // set the payment to the amount entered
  $payment = $amount;

  /*--------------------------------------------------------------------*/
  // enter payments by invoice number	
  /*--------------------------------------------------------------------*/
  if ($invoice_number > 0) {
    $query = "SELECT * FROM billing_details ".
      "WHERE paid_amount < billed_amount AND invoice_number = $invoice_number";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("Query Failed");
    $invoiceresult = $DB->Execute($query) or die ("$l_queryfailed");

    // update values with missing information
    $myresult = $invoiceresult->fields;
    $billing_id = $myresult['billing_id'];
    
    /*--------------------------------------------------------------------*/
    // enter payments by account number
    /*--------------------------------------------------------------------*/
  } elseif ($account_num > 0) {
    $query = "SELECT bd.id, bd.paid_amount, bd.billed_amount, bd.billing_id, bd.discount_amount ".
      "FROM billing_details bd ".
      "LEFT JOIN billing bi ON bd.billing_id = bi.id ".
      "LEFT JOIN customer cu ON bi.id = cu.default_billing_id ".
      "WHERE bd.paid_amount < bd.billed_amount ".
      "AND cu.account_number = $account_num";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("select acctnum $l_queryfailed");
    $accountresult = $DB->Execute($query) or die ("select acctnum $l_queryfailed");
		
    // update values with missing information
    $myresult = $accountresult->fields;
    $billing_id = $myresult['billing_id'];
	
    /*--------------------------------------------------------------------*/
    // enter payments by billing id
    /*--------------------------------------------------------------------*/
  } else {
    $query = "SELECT * FROM billing_details ".
      "WHERE paid_amount < billed_amount AND billing_id = $billing_id";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("select detail $l_queryfailed");	
  }  
  
  /*-------------------------------------------------------------------*/
  // Insert result into the Payment History
  /*--------------------------------------------------------------------*/	
  
  // insert info into the payment history
  $query = "INSERT INTO payment_history (creation_date, billing_id, ".
    "billing_amount, status, payment_type, invoice_number, check_number) ".
    "VALUES (CURRENT_DATE,'$billing_id','$payment',".
    "'authorized','$payment_type','$invoice_number','$check_number')";
  $paymentresult = $DB->Execute($query) or die ("$query insert history $l_queryfailed");

  // get the payment history id that will be inserted into the billing_details
  // items that are paid by this entry
  $payment_history_id = $DB->Insert_ID();

  /*------------------------------------------------------------------------*/
  // go through the billing details items
  /*------------------------------------------------------------------------*/  
  while (($myresult = $result->FetchRow()) and (round($amount,2) > 0)) {
    $id = $myresult['id'];
    $paid_amount = sprintf("%.2f",$myresult['paid_amount']);
    $billed_amount = sprintf("%.2f",$myresult['billed_amount']);

    if ($payment_type == 'discount') {
      // get the discount amount and update it also
      $discount_amount = sprintf("%.2f",$myresult['discount_amount']);
    }
    
    // calculate amount owed
    $owed = round($billed_amount - $paid_amount,2);
    
    // fix float precision too
    
    if (round($amount,2) >= round($owed,2)) {
      $amount = round($amount - $owed, 2);
      $fillamount = round($owed + $paid_amount,2);

      if ($payment_type == 'discount') {
	$filldiscount = round($owed + $discount_amount,2);
	$discount_string = "discount_amount = '$filldiscount', ";
      } else {
	$discount_string = "";
      }
      
      $query = "UPDATE billing_details ".
	"SET paid_amount = '$fillamount', ".
	"payment_applied = CURRENT_DATE, $discount_string".
	"payment_history_id = '$payment_history_id' ".	    
	"WHERE id = $id";
      $greaterthanresult = $DB->Execute($query)
	or die ("detail update $l_queryfailed");
    } else { 
      // amount is  less than owed
      $available = $amount;
      $amount = 0;
      $fillamount = round($available + $paid_amount,2);

      if ($payment_type == 'discount') {
	$filldiscount = round($available + $discount_amount,2);
	$discount_string = "discount_amount = '$filldiscount', ";
      } else {
	$discount_string = "";
      }
      
      $query = "UPDATE billing_details ".
	"SET paid_amount = '$fillamount', ".
	"payment_applied = CURRENT_DATE, $discount_string".
	"payment_history_id = '$payment_history_id' ".	    	
	"WHERE id = $id";
      $lessthenresult = $DB->Execute($query) or die ("detail update $l_queryfailed");
    } // end if amount >= owed
  } // end while myresult and amount > 0


  /*--------------------------------------------------------------------*/
  // If the payment is made towards a prepaid account, then move
  // the billing and payment dates forward for payment terms
  /*--------------------------------------------------------------------*/
  
  //
  // update the Next Billing Date to whatever the 
  // billing type dictates +1 +2 +6 etc.
  // get the current billing type
  //

  // Also select the customer's billing name, company, and address
  // here to show up at the end to show what account paid.

  $query = "SELECT b.billing_type b_billing_type, ".
    "b.next_billing_date b_next_billing_date, ".
    "b.from_date b_from_date, b.to_date b_to_date, ".
    "b.name, b.company, b.street, b.city, b.state, ".
    "b.account_number b_account_number, ".
    "t.id t_id, t.frequency t_frequency, t.method t_method ".
    "FROM billing b ".
    "LEFT JOIN billing_types t ON b.billing_type = t.id ".
    "WHERE b.id = $billing_id";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("select next billing $l_queryfailed");
  $billingresult = $result->fields;
  $method = $billingresult['t_method'];
  $billing_name = $billingresult['name'];
  $billing_company = $billingresult['company'];
  $billing_street = $billingresult['street'];
  $billing_city = $billingresult['city'];
  $billing_state = $billingresult['state'];
  $billing_account_number = $billingresult['b_account_number'];
  
  // if they are prepay accounts the update their billing dates
  if ($method == 'prepay' OR $method == 'prepaycc') {
    $mybillingdate = $billingresult['b_next_billing_date'];
    $myfromdate = $billingresult['b_from_date'];
    $mytodate = $billingresult['b_to_date'];
    $mybillingfreq = $billingresult['t_frequency'];
    
    // to get the to_date, need to double the frequency added
    $doublefreq = $mybillingfreq * 2;	
    
    //
    // insert the new next_billing_date
    // and a new from_date and to_date 
    // and payment_due_date based on from_date
    //
    
    $query = "UPDATE billing SET ".
      "next_billing_date = DATE_ADD('$mybillingdate', ".
      "INTERVAL '$mybillingfreq' MONTH), ".
      "from_date = DATE_ADD('$myfromdate', ".
      "INTERVAL '$mybillingfreq' MONTH), ".
      "to_date = DATE_ADD('$myfromdate', ".
      "INTERVAL '$doublefreq' MONTH), ".
      "payment_due_date = DATE_ADD('$myfromdate', ".
      "INTERVAL '$mybillingfreq' MONTH) ".
      "WHERE id = '$billing_id'"; 
    $updateresult = $DB->Execute($query) or die ("update $l_queryfailed");
  }

  if ($amount > 0) {
    // if there is an over payment show the amount in red
    //and prompt to add as a credit		
    print "<h3 style=\"color: red;\">$l_paymentsaved, $l_currency$amount ".
      "$l_leftover, <a href=\"index.php?load=addcredit&type=tools&amount=$amount&billing_id=$billing_id\">$l_addcreditfor $amount</a></h3>";
  } else {
    print "<h3>$l_paymentsaved: </h3>";
  }

  // print the customer billing information to confirm who the payment
  // was entered for.

  echo "<blockquote>$billing_account_number<br>$billing_name<br>".
    "$billing_company<br>$billing_street<br>$billing_city $billing_state</blockquote><p>";
  
  
 } // end payment submit


//
// Read the list of payment modes to give the operator
// for paying the bill
//
$query = "SELECT name FROM payment_mode";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$payresult = $DB->Execute($query) or die ("$l_queryfailed");
$payment_options = "";
while (($mypayresult = $payresult->FetchRow()))
{
  $payment_options = $payment_options . "<option>". $mypayresult['name'] . "</option>\n";
}

/*----------------------------------------------------------------------------*/
// print the by billing id form
/*----------------------------------------------------------------------------*/
echo "<FORM ACTION=\"index.php\" METHOD=\"GET\">
        <input type=hidden name=load value=payment>
	<input type=hidden name=type value=tools>
	$l_enteroneofthesethreevalues:
	<table><td>
	<b>$l_accountnumber:</b></td><td>
	<input type=\"text\" name=\"account_num\" size=\"20\" maxlength=\"32\">
	($l_applytodefaultbillingid)
	</td><tr><td>
	<B>$l_billingid:</B></td><td>
	<INPUT TYPE=\"TEXT\" NAME=\"billing_id\" SIZE=\"20\" MAXLENGTH=\"32\">
	($l_applytospecificbillingid)
	</td><tr><td>
	<B>$l_invoicenumber:</B></td><td>
	<INPUT TYPE=\"TEXT\" NAME=\"invoice_number\" SIZE=\"20\" MAXLENGTH=\"32\" value=\"$invoice_number\">
	($l_applytospecificinvoice)
	</td><tr><td>
	&nbsp;
	</td><tr><td>
	<B>$l_amount:</B></td><td>
        <INPUT TYPE=\"TEXT\" NAME=\"amount\" SIZE=\"20\" MAXLENGTH=\"32\" value=\"$amount\">
        </td><tr><td>
        <B>$l_type</B></td><td>
	<select name=\"payment_type\">
	$payment_options	
	</select>
        </td><tr><td>
	<b>$l_checknumber:</b></td>
	<td><input type=\"text\" name=\"check_number\" size=\"5\"></td>
	<tr>
	<td></td><td>
	<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" value=\"$l_submit\">
	</td></table>
	</FORM>";
?>
