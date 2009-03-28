<?php   
// Copyright (C) 2002-2008  Paul Yasi (paul at citrusdb.org)
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
if (!isset($base->input['undeletenow'])) { $base->input['undeletenow'] = ""; }
$undeletenow = $base->input['undeletenow'];

if ($undeletenow) {

  // undelete the customer record
  $query = "UPDATE customer ".
    "SET cancel_date = NULL, ".
    "cancel_reason = NULL ".
    "WHERE account_number = '$account_number'";
  $result = $DB->Execute($query) or die ("update customer $l_queryfailed");

  // update the default billing records with new billing dates
  $mydate = get_nextbillingdate();
  $query = "UPDATE billing ".
    "SET next_billing_date = '$mydate', ".
    "from_date = '$mydate', ".
    "payment_due_date = '$mydate' ".
    "WHERE account_number = '$account_number'";
  $result = $DB->Execute($query) or die ("update billing $l_queryfailed");  

  // get the default billing id and billing type for automati_to_date
  $query = "SELECT c.default_billing_id,b.billing_type,b.from_date ".
    "FROM customer c ".
    "LEFT JOIN billing b ON b.id = c.default_billing_id ".
    "WHERE c.account_number = '$account_number'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("Billing Query Failed");
  $myresult = $result->fields;	
  $billing_id = $myresult['default_billing_id'];
  $billing_type = $myresult['billing_type'];
  $from_date = $myresult['from_date'];

  // set the to_date automatically
  automatic_to_date($DB, $from_date, $billing_type, $billing_id);
  
  // redirect them to the customer page	
  print "<script language=\"JavaScript\">window.location.href = \"index.php?load=customer&type=module\";</script>";
  
 } else {

  // show the regular cancel form for non carrier dependent and for managers
  // ask if they are sure they want to cancel this customer
  print "<br><br>";
  print "<h4>$l_areyousureuncancel: $account_number</h4>";
  print "<table cellpadding=15 cellspacing=0 border=0 width=720>";
  print "<td align=right width=240>";
    
  // if they hit yes, this will sent them into the undelete.php file
  // and remove the service on their next billing anniversary
  
  print "<form style=\"margin-bottom:0;\" action=\"index.php\">";
  print "<input type=hidden name=load value=customer>";
  print "<input type=hidden name=type value=module>";
  print "<input type=hidden name=undelete value=on>";
  print "<input name=undeletenow type=submit value=\" $l_yes \" class=smallbutton></form></td>";
  
  // if they hit no, send them back to the service edit screen
  
  print "<td align=left width=240>";
  print "<form style=\"margin-bottom:0;\" action=\"index.php\">";
  print "<input type=hidden name=load value=customer>";        
  print "<input type=hidden name=type value=module>";
  print "<input name=done type=submit value=\" $l_no \" class=smallbutton>";
  print "</form></td>";
  
  print "</table>";
  print "</blockquote>";
 }

