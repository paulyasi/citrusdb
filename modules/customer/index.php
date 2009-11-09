<?php
// Copyright (C) 2003-2008  Paul Yasi (paul at citrusdb.org)
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

//Includes
require_once('./include/permissions.inc.php');


// GET Variables
if (!isset($base->input['undelete'])) { $base->input['undelete'] = ""; }
$undelete = $base->input['undelete'];


if ($edit) {
  if ($pallow_modify) {
      include('./modules/customer/edit.php');
    }  else permission_error();
} else if ($create) {
  if ($pallow_create) {
      include('./modules/customer/create.php');
    } else permission_error();
 } else if ($delete) {
    if ($pallow_remove) {
       include('./modules/customer/delete.php');
    } else permission_error();
 } else if ($undelete) {
  if ($pallow_remove) {
    include('./modules/customer/undelete.php');
  } else permission_error();
} else if ($pallow_view) {
    
  // get the customer information
  $query = "SELECT * FROM customer WHERE account_number = $account_number";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("customer info $l_queryfailed");
  $myresult = $result->fields;	
  // Put values into variablies and Print HTML results

  $signup_date = $myresult['signup_date'];
  $name = $myresult['name'];
  $company = $myresult['company'];        
  $street = $myresult['street'];
  $city = $myresult['city'];
  $state = $myresult['state'];
  $zip = $myresult['zip'];
  $country = $myresult['country'];
  $phone = $myresult['phone'];
  $alt_phone = $myresult['alt_phone']; 
  $fax = $myresult['fax'];
  $source = $myresult['source'];
  $contactemail = $myresult['contact_email'];
  $secret_question = $myresult['secret_question'];
  $secret_answer = $myresult['secret_answer'];
  $default_billing_id = $myresult['default_billing_id'];
  $cancel_date = $myresult['cancel_date'];
  //$removal_date = $myresult['removal_date'];
  $account_manager_password = $myresult['account_manager_password'];
  $cancel_reason_id = $myresult['cancel_reason'];
  $notes = $myresult['notes'];

  // get the cancel reason text
  if ($cancel_reason_id > 0) {
    $query = "SELECT reason FROM cancel_reason WHERE id = $cancel_reason_id";
    $result = $DB->Execute($query) or die ("$l_queryfailed");
    $myresult = $result->fields;
    $cancel_reason = $myresult['reason'];
  }
  
  /*************************************
  // get their billing status from billing_details, select all accounts for
  // this user that have a billed amount greater than the paid amount
  $query="SELECT c.account_number c_acctnum, u.account_number u_acctnum, 
	u.billing_id u_bid, d.billing_id d_bid, d.creation_date d_cdate, 
	d.billed_amount d_billed, d.paid_amount d_paid 
	FROM customer c    
        LEFT JOIN user_services u ON c.account_number = u.account_number   
        LEFT JOIN billing_details d ON u.billing_id = d.billing_id   
        WHERE c.account_number = $account_number AND d.billed_amount > d.paid_amount 
	AND CURRENT_DATE > DATE_ADD(creation_date,INTERVAL 30 DAY)";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  
  // loop through results and add to total pastdue
  $totalpastdue = 0;
  while ($myresult = $result->FetchRow()) {	
      $totalpastdue = $totalpastdue + $myresult['d_billed'] - $myresult['d_paid'];
  }
  ******************************/

  // print the customer edit links
  echo "<a href=\"index.php?load=customer&type=module&edit=on\">".
    "[ $l_editcustomer ]</a>";
  if ($cancel_date) {
    echo "<a href=\"index.php?load=customer&type=module&undelete=on\">".
      "[ $l_uncancelcustomer ]</a>";
  } else {
    echo "<a href=\"index.php?load=customer&type=module&delete=on\">".
      "[ $l_cancelcustomer ]</a>";
   }
  // print the HTML table
  echo "<table cellpadding=0 border=0 cellspacing=1 width=719>".
  "<td valign=top width=259 style=\"background-color: #dde;\">".
  "<table cellpadding=4 cellspacing=0 border=0 width=259>".
  
  "<tr>".
  //"<td width=180><b>$l_name</b></td>".
  "<td style=\"font-size: 12pt;\">$name</td>".

  "<tr>".
  //"<td><b>$l_company</b></td>".
  "<td style=\"font-size: 12pt;\">$company</td>".
  
  "<tr>".  
  //"<td><b>$l_street</b></td>".
  "<td>$street</td>".
  
  "<tr>".
  //"<td><b>$l_city</b></td>".
  "<td>$city $state $zip</td>".

  //"<tr>".
  //"<td><b>$l_state</b></td>".
  //"<td>$state</td>".
  
  //"<tr>".  
  //"<td><b>$l_zip</b></td>".
  //"<td>$zip</td>".
  
  "<tr>".
  //"<td><b>$l_phone</b></td>".
  "<td>$l_phone: $phone</td>".
  
  "<tr>".
  //"<td><b>$l_alt_phone</b></td>".
  "<td>$l_alt_phone: $alt_phone</td>".
  
  "<tr>".
  //"<td><b>$l_fax</b></td>".
    "<td>$l_fax: $fax</td>".

    "<tr>".
    "<td>$l_notes: $notes</td>".

  // end of left table column
  
  "</table></td><td valign=top width=360 style=\"background-color: #dde;\">".
  "<table cellpadding=3 cellspacing=0 border=0 width=360>";


echo 
  "<td width=160><b>$l_signupdate</b></td>".
  "<td width=200>$signup_date</td><tr>".

  "<td valign=top><b>$l_canceldate</b></td>".
  "<td class=redbold>$cancel_date<br> $cancel_reason</td><tr>".
  
  "<td><b>$l_source</b></td>".
  "<td>$source</td><tr>".
  
  "<td><b>$l_contactemail</b></td>".
  "<td>$contactemail</td><tr>".
  
  "<td><b>$l_secret_question</b></td>".
  "<td>$secret_question</td><tr>".
  
  "<td><b>$l_secret_answer</b></td>".
  "<td>$secret_answer</td><tr>".
  
  "<td><b>$l_defaultbillingid</b></td>".
  "<td>$default_billing_id</td><tr>".
  
  "<td><b>$l_country</b></td>".
  "<td>$country</td><tr>".
  
  "<td><b>$l_acctmngrpasswd</b></td>".
  "<td>$account_manager_password</td><tr>".
  
  "</table></td></table></form>";
//end of second column

 echo "<p>";
 // show the billing record info
 // print a list of alternate billing id's if any
 $query = "SELECT b.id b_id, g.org_name g_org_name, t.name t_name ".
   "FROM billing b ".
   "LEFT JOIN billing_types t ON b.billing_type = t.id ".
   "LEFT JOIN general g ON b.organization_id = g.id ".
   "WHERE b.account_number = $account_number";
 $DB->SetFetchMode(ADODB_FETCH_ASSOC);
 $result = $DB->Execute($query) or die ("$l_queryfailed");
 
 echo "<br><table width=720 cellpadding=3><tr bgcolor=\"#dddddd\">".
   "<td><b>$l_organizationname</b></td><td><b>$l_type</b></td>".
   "<td><b>$l_status</b></td><td><b>$l_newcharges</b></td>".
   "<td><b>$l_tax</b></td><td><b>$l_pastcharges</b></td>".
   "<td><b>$l_total</b></td><tr>";

 while ($myresult = $result->FetchRow()) {
  $billing_id = $myresult['b_id'];
  $billing_type = $myresult['t_name'];
  $billing_orgname = $myresult['g_org_name'];

  // check if billing type has active services
  $query = "SELECT billing_id FROM user_services ".
    "WHERE removed = 'n' AND billing_id = $billing_id LIMIT 1";
  $usresult = $DB->Execute($query) or die ("user service $l_queryfailed");
  $myusresult = $usresult->fields;
  $not_removed_id = $myusresult['billing_id'];

  $mystatus = billingstatus($billing_id);


  // show active billing services that are authorized new or free in green
  // show active billing services not in good standing in red
  if ($billing_id == $not_removed_id) {    
    if (($mystatus == $l_authorized)
	OR ($mystatus == $l_new)
	OR ($mystatus == $l_free)
	OR ($mystatus == $l_pastdueexempt)) {
      echo "<tr style=\"background-color: bdd;\">";
    } else {
      echo "<tr style=\"background-color: fbb;\">";
    }
  } else {
    // show inactive billing services that are in not in good standing in red
    // show inactive billing services in other status in grey
    if (($mystatus == $l_pastdue)
	OR ($mystatus == $l_waiting)
	OR ($mystatus == $l_noticesent)
	OR ($mystatus == $l_turnedoff)
	OR ($mystatus == $l_declined)
	OR ($mystatus == $l_initialdecline)
	OR ($mystatus == $l_declined2x)) {
      echo "<tr style=\"background-color: fbb;\">";
    } else { 
      // print in grey if all services are removed from that billing id
      echo "<tr style=\"background-color: eee; color: aaa;\">";
    }
  }
    
  print "<td style=\"font-weight: bold;\">$billing_orgname&nbsp;".
    "<a href=\"index.php?load=billing&type=module&edit=on&".
    "billing_id=$billing_id\">$l_edit $billing_id</a>";

  print "</td><td>$billing_type</td><td>$mystatus</td>";

  $newtaxes = sprintf("%.2f",total_taxitems($DB, $billing_id));
  $newcharges = sprintf("%.2f",total_serviceitems($DB, $billing_id)+$newtaxes);
  $pastcharges = sprintf("%.2f",total_pastdueitems($DB, $billing_id));
  
  print "<td>$newcharges</td><td>$newtaxes</td><td>$pastcharges</td>";

  $newtotal = sprintf("%.2f",$newcharges + $pastcharges);
  print "<td>$newtotal</td>";
  
 }
 
 echo "</table>";
 
 echo "<p>";
 // show the services module info
 // Check if the file is inside the path_to_citrus
 $filepath = "$path_to_citrus/modules/services/index.php";
 if (file_exists($filepath)) {
   include('./modules/services/index.php');
 }

 // add a log entry that this customer record was viewed
 log_activity($DB,$user,$account_number,'view_customer','success');
 
} else permission_error();
?>
