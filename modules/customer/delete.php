<?php   
// Copyright (C) 2002-2008  Paul Yasi <paul@citrusdb.org>
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

if (!isset($base->input['userserviceid'])) { $base->input['userserviceid'] = ""; }
if (!isset($base->input['deletenow'])) { $base->input['deletenow'] = ""; }
if (!isset($base->input['now'])) { $base->input['now'] = ""; }
if (!isset($base->input['whycancel'])) { $base->input['whycancel'] = ""; }
if (!isset($base->input['cancel_reason'])) { $base->input['cancel_reason'] = ""; }

// GET Variables
$userserviceid = $base->input['userserviceid'];
$deletenow = $base->input['deletenow'];
$whycancel = $base->input['whycancel'];
$cancel_reason = $base->input['cancel_reason'];
$now = $base->input['now'];

if ($whycancel) {
  // ask what reason they are canceling
  print "$l_whycanceling<p>";
  print "<form style=\"margin-bottom:0;\" action=\"index.php\" name=\"cancelform\">";
  print "<input type=hidden name=load value=customer>";        
  print "<input type=hidden name=type value=module>";
  print "<input type=hidden name=delete value=on>";
  print "<input type=hidden name=now value=\"$now\">";  

  // print list of reasons to choose from
  $query = "SELECT * FROM cancel_reason";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  echo "<select name=\"cancel_reason\" onChange=\"document.cancelform.deletenow.disabled=false\">".
    "<option value=\"\">Choose One...</option>";
  while ($myresult = $result->FetchRow()) {
    $myid = $myresult['id'];
    $myreason = $myresult['reason'];
    echo "<option value=\"$myid\">$myreason</option>";
  }
  echo "</select><p>";

  // make sure the select one, use javascript to disable this until they pick 
  // a value for cancel reason
  print "<input disabled name=deletenow id=deletenow type=submit value=\"$l_cancelcustomer\" class=smallbutton></form><p>";
  
 } elseif ($deletenow) {
  
   // set the removal date correctly for now or later
   if ($now == "on") {
     // they should be removed as immediately as possible
     //so use the next billing date as the removal date
     $removal_date = get_nextbillingdate();
   } else {
     // figure out the customer's current next billing anniversary date
     $query = "SELECT b.next_billing_date FROM customer c " .
       "LEFT JOIN billing b ON c.default_billing_id = b.id ".
       "WHERE c.account_number = '$account_number'";
     $DB->SetFetchMode(ADODB_FETCH_ASSOC);
     $result = $DB->Execute($query) or die ("$query $l_queryfailed");
     $myresult = $result->fields;
     $next_billing_date = $myresult['next_billing_date'];

     // split date into pieces
     list($myyear, $mymonth, $myday) = split('-', $next_billing_date);
      
     // removal date is normally the anniversary billing date
     $removal_date  = $next_billing_date;

     // today's date
     $today  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
     // if the next billing date is less than today, remove them next available day
     if ($removal_date < $today) {
       $removal_date = get_nextbillingdate();
     }
   }
   
   // figure out all the services that the customer has and delete each one.
   $query = "SELECT * FROM user_services 
		WHERE account_number = '$account_number' AND removed <> 'y'";
   $DB->SetFetchMode(ADODB_FETCH_ASSOC);
   $result = $DB->Execute($query) or die ("$l_queryfailed");
   while ($myserviceresult = $result->FetchRow()) {
     $userserviceid = $myserviceresult['id'];
     delete_service($userserviceid,'canceled',$removal_date);
   }
   
   // set cancel date and removal date of customer record
   $query = "UPDATE customer ".
     "SET cancel_date = CURRENT_DATE, ". 
     "cancel_reason = '$cancel_reason' ".
     "WHERE account_number = '$account_number'";
   $result = $DB->Execute($query) or die ("$l_queryfailed");
   
   // set next_billing_date to NULL since it normally won't be billed again
   $query = "UPDATE billing ".
     "SET next_billing_date = NULL ". 
     "WHERE account_number = '$account_number'";
   $result = $DB->Execute($query) or die ("$l_queryfailed");   
   
   // get the text of the cancel reason to use in the note
   $query = "SELECT * FROM cancel_reason " . 
     "WHERE id = '$cancel_reason'";
   $DB->SetFetchMode(ADODB_FETCH_ASSOC);
   $result = $DB->Execute($query) or die ("$l_queryfailed");
   $myresult = $result->fields;
   $cancel_reason_text = $myresult['reason'];
   
   // add cancel ticket to customer_history
   // if they are carrier dependent, send a note to
   // the billing_noti
   $desc = "$l_canceled: $cancel_reason_text";
   create_ticket($DB, $user, NULL, $account_number, 'automatic', $desc);
   
   // get the billing_id for the customer's payment_history
   $query = "SELECT default_billing_id FROM customer " . 
     "WHERE account_number = '$account_number'";
   $DB->SetFetchMode(ADODB_FETCH_ASSOC);
   $result = $DB->Execute($query) or die ("$l_queryfailed");
   $myresult = $result->fields;
   $default_billing_id = $myresult['default_billing_id'];
   
   // add a canceled entry to the payment_history
   $query = "INSERT INTO payment_history ".
     "(creation_date, billing_id, status) ".
     "VALUES (CURRENT_DATE,'$default_billing_id','canceled')";
   $paymentresult = $DB->Execute($query) or die ("$l_queryfailed");
   
   // redirect them to the customer page	
   print "<script language=\"JavaScript\">window.location.href = \"index.php?load=customer&type=module\";</script>";

 } else {

  // check if the services on the account are carrier_dependent
  // if it is carrier dependent, then send user to the
  // carrier dependent cancel form instead of the regular cancel system
  $dependent = carrier_dependent($account_number);

  if ($dependent == true) {
    // print a message that this customer is carrier dependent
    echo "<h3>$l_carrierdependentmessage</h3><p align=center>";
    
    // get the dependent_cancel_url from the settings table
    $query = "SELECT dependent_cancel_url FROM settings WHERE id = 1";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("$l_queryfailed");
    $myresult = $result->fields;
    $dependent_cancel_url = $myresult['dependent_cancel_url'];
  
    // print a link to the url to fill out the carrier dependent cancel form
    print "<a href=\"$dependent_cancel_url\">$l_cancelcustomer</a></p>";
    
  }

  // check if the user has manager privileges
  $query = "SELECT * FROM user WHERE username='$user'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);

  $result = $DB->Execute($query) or die ("$l_queryfailed");
  $myresult = $result->fields;
  $manager = $myresult['manager'];


  if ($dependent == false OR $manager == 'y') {
    // show the regular cancel form for non carrier dependent and for managers
    // ask if they are sure they want to cancel this customer
    print "<br><br>";
    print "<h4>$l_areyousurecancel: $account_number</h4>";
    print "<table cellpadding=15 cellspacing=0 border=0 width=720>";
    print "<td align=right width=240>";
    
    // if they hit yes, this will sent them into the delete.php file and remove the service on their next billing anniversary
    
    print "<form style=\"margin-bottom:0;\" action=\"index.php\">";
    print "<input type=hidden name=load value=customer>";
    print "<input type=hidden name=type value=module>";
    print "<input type=hidden name=delete value=on>";
    print "<input name=whycancel type=submit value=\" $l_yes \" class=smallbutton></form></td>";
    
    // if they hit no, send them back to the service edit screen
    
    print "<td align=left width=240>";
    print "<form style=\"margin-bottom:0;\" action=\"index.php\">";
    print "<input type=hidden name=load value=customer>";        
    print "<input type=hidden name=type value=module>";
    print "<input name=done type=submit value=\" $l_no \" class=smallbutton>";
    print "</form></td>";
    
    // if they hit Remove Now, send them to delete.php and remove the service on the next available
    // work date, the next valid billing date
    
    print "<td align=left width=240>";
    print "<form style=\"margin-bottom:0;\" action=\"index.php\">";
    print "<input type=hidden name=load value=customer>";        
    print "<input type=hidden name=type value=module>";
    print "<input type=hidden name=delete value=on>";
    print "<input type=hidden name=now value=on>";
    print "<input name=whycancel type=submit value=\"$l_remove_now\" class=smallbutton>";  
    print "</form></td>";
    
    print "</table>";
    print "</blockquote>";
  }
 }

