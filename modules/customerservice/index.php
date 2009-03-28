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
} else if ($pallow_view) {
    
  // get the customer information
  $query = "SELECT * FROM customer WHERE account_number = $account_number";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
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
  
  // get their billing status from billing_details, select all accounts for this user that have 
  // a billed amount greater than the paid amount
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


  // print the customer edit links
  echo "<a href=\"index.php?load=customer&type=module&edit=on\">".
    "[ $l_editcustomer ]</a>".
    "<a href=\"index.php?load=customer&type=module&delete=on\">".
    "[ $l_cancelcustomer ]</a>";

 // print the HTML table
echo "<table cellpadding=0 border=0 cellspacing=1 width=719>".
  "<td valign=top width=359 style=\"background-color: #dde;\">".
  "<table cellpadding=3 cellspacing=0 border=0 width=359>".
  
  "<tr>".
  "<td width=180><b>$l_name</b></td>".
  "<td width=180>$name</td>".

  "<tr>".
  "<td><b>$l_company</b></td>".
  "<td>$company</td>".
  
  "<tr>".  
  "<td><b>$l_street</b></td>".
  "<td>$street</td>".
  
  "<tr>".
  "<td><b>$l_city</b></td>".
  "<td>$city</td>".

  "<tr>".
  "<td><b>$l_state</b></td>".
  "<td>$state</td>".
  
  "<tr>".  
  "<td><b>$l_zip</b></td>".
  "<td>$zip</td>".
  
  "<tr>".
  "<td><b>$l_phone</b></td>".
  "<td>$phone</td>".
  
  "<tr>".
  "<td><b>$l_alt_phone</b></td>".
  "<td>$alt_phone</td>".
  
  "<tr>".
  "<td><b>$l_fax</b></td>".
  "<td>$fax</td>".

  // end of left table column
  
  "</table></td><td valign=top width=360 style=\"background-color: #dde;\">".
  "<table cellpadding=3 cellspacing=0 border=0 width=360>";


// show the correct billing status
// TODO: show for all their billing records, not just the default one

// $billingstatus = billingstatus($DB, $default_billing_id, $lang);
// print "<td width=180><b>$l_billingstatus</b></td>
//	<td width=180><b>$billingstatus</b></td><tr>"; 

echo 
  "<td width=180><b>$l_signupdate</b></td>".
  "<td width=180>$signup_date</td><tr>".

  "<td><b>$l_canceldate</b></td>".
  "<td class=redbold>$cancel_date</td><tr>".
  
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
 $query = "SELECT b.id b_id, g.org_name g_org_name, t.name t_name 
FROM billing b 
LEFT JOIN billing_types t ON b.billing_type = t.id 
LEFT JOIN general g ON b.organization_id = g.id 
WHERE b.account_number = $account_number";
 $DB->SetFetchMode(ADODB_FETCH_ASSOC);
 $result = $DB->Execute($query) or die ("$l_queryfailed");
 
 echo "<b>$l_organizationname</b>&nbsp;".
   "<a href=\"index.php?load=billing&type=module&create=on\">".
   "$l_addaltbilling</a> ".
   "<br><table width=720 cellpadding=3>";

 while ($myresult = $result->FetchRow()) {
  $billing_id = $myresult['b_id'];
  $billing_type = $myresult['t_name'];
  $billing_orgname = $myresult['g_org_name'];
  
  $mystatus = billingstatus($DB, $billing_id, $lang);

  if (($mystatus == $l_authorized)
      OR ($mystatus == $l_new)
      OR ($mystatus == $l_free)) {
    echo "<tr style=\"background-color: bdd;\">";
  } else {
        echo "<tr style=\"background-color: fbb;\">";
  }
  
  print "<td><b>$billing_orgname</b>&nbsp;".
    "<a href=\"index.php?load=billing&type=module&edit=on&".
    "billing_id=$billing_id\">$l_edit $billing_id</a>".
    "</td><td>$billing_type</td><td>$mystatus</td>";

  // print rerun link
  echo "<td><a href=\"index.php?load=billing&type=module&rerun=on&".
    "billing_id=$billing_id\">[ $l_rerun ]</a> &nbsp;&nbsp;&nbsp;";
    
  if (($myuserresult['manager'] == 'y') OR ($myuserresult['admin'] == 'y')) {
    echo "<a href=\"index.php?load=invmaint&type=tools&billingid=$billing_id&".
      "submit=Submit\">[$l_invoicemaintenance ]</a> &nbsp;&nbsp;&nbsp;". 
      "<a href=\"index.php?load=refund&type=tools&billingid=$billing_id&".
      "submit=Submit\">[ $l_refundreport ]</a></td>";
    }
 }
 
 echo "</table>";
 
 echo "<p>";
 // show the services module info
 // Check if the file is inside the path_to_citrus
 $filepath = "$path_to_citrus/modules/services/index.php";
 if (file_exists($filepath)) {
   include('./modules/services/index.php');
 }
 
} else permission_error();
?>
