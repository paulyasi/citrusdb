<?php   
// Copyright (C) 2009 Paul Yasi (paul at citrusdb.org)
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

// GET Variables
$paymentid = $base->input['paymentid'];
$amount = $base->input['amount'];
$invoice_number = $base->input['invoicenum'];
$billingid = $base->input['billingid'];
$payment_type = $base->input['payment_type'];

if ($save) {
  // set the account payment_history to nsf
  $query = "UPDATE payment_history ".
    "SET payment_type = 'nsf', ".
    "status = 'declined' ".
    "WHERE id = $paymentid";
  $paymentresult = $DB->Execute($query) or die ("$l_queryfailed");
  
  /*-------------------------------------------------------------------------*/
  // remove paid_amounts from billing_details
  //
  // get the resulting list of services to have payments removed from
  /*--------------------------------------------------------------------*/
  // remove payments by invoice number if paid to a particular invoice
  if ($invoice_number > 0) {
    $query = "SELECT * FROM billing_details ".
      "WHERE paid_amount > 0 AND invoice_number = $invoice_number";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("invoice1 Query Failed");
    $invoiceresult = $DB->Execute($query) or die ("invoice2 $l_queryfailed");
    
    // update values with missing information
    $myresult = $invoiceresult->fields;
    $billingid = $myresult['billing_id'];

    // else remove payments where paid anything
  } else {
    $query = "SELECT * FROM billing_details ". 
      "WHERE paid_amount > 0 AND billing_id = $billingid";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("billingid $l_queryfailed");	
  }

  /*-------------------------------------------------------------------------*/
  // go through the list and subtract the payment from each until
  // the amount is depleted
  /*-------------------------------------------------------------------------*/
  while (($myresult = $result->FetchRow()) and (round($amount,2) > 0)) {
    $id = $myresult['id'];
    $paid_amount = sprintf("%.2f",$myresult['paid_amount']);

    // fix float precision too    
    if (round($amount,2) >= round($paid_amount,2)) {
      $amount = round($amount - $paid_amount, 2);
      $fillamount = 0;

      $query = "UPDATE billing_details ".
	"SET paid_amount = '$fillamount' ".
	"WHERE id = $id";
      $greaterthanresult = $DB->Execute($query)
	or die ("greaterthan $l_queryfailed");

    } else { 
      // amount is less than paid_amount
      $available = $amount;
      $amount = 0;
      $fillamount = round($paid_amount - $available,2);

      $query = "UPDATE billing_details ".
	"SET paid_amount = '$fillamount' ".
	"WHERE id = $id";
      $lessthenresult = $DB->Execute($query) 
	or die ("lessthan $l_queryfailed");

    }

    //echo "$query<br>\n";
    
  }

  // redirect back to the billing screen
  print "<script language=\"JavaScript\">window.location.href = ".
    "\"index.php?load=billing&type=module\";</script>";

 } else {
  
  print "<br><br>";
  print "<h4>&nbsp;&nbsp;&nbsp; $l_areyousurensf $amount</h4>";
  print "<table cellpadding=15 cellspacing=0 border=0 width=720>".
    "<td align=right width=360>";

  print "<form style=\"margin-bottom:0;\" action=\"index.php\">";
  print "<input type=hidden name=load value=billing>";
  print "<input type=hidden name=type value=module>";
  print "<input type=hidden name=nsf value=on>";
  print "<input type=hidden name=paymentid value=$paymentid>";
  print "<input type=hidden name=amount value=$amount>";
  print "<input type=hidden name=invoicenum value=$invoice_number>";
  print "<input type=hidden name=billingid value=$billingid>";
  print "<input type=hidden name=payment_type value=$payment_type>";    
  print "<input name=save type=submit value=\" $l_yes \" ".
    "class=smallbutton></form></td>";
  print "<td align=left width=360><form style=\"margin-bottom:0;\" ".
    "action=\"index.php\">";
  print "<input name=done type=submit value=\" $l_no  \" class=smallbutton>";
  print "<input type=hidden name=load value=billing>";
  print "<input type=hidden name=type value=module>";
  print "</form></td></table>";
 }
?>
