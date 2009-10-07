<?php   
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

if (!isset($base->input['save'])) { $base->input['save'] = ""; }

if ($base->input['billingaddress']) {
	
  // update their billing address after we prompt them if they want to
  // get the customer information
  $query = "SELECT * FROM customer WHERE account_number = $account_number";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  $myresult = $result->fields;

  $street = $myresult['street'];
  $city = $myresult['city'];
  $state = $myresult['state'];
  $zip = $myresult['zip'];
  $country = $myresult['country'];
  $phone = $myresult['phone'];
  $fax = $myresult['fax'];
  $contact_email = $myresult['contact_email'];
  $default_billing_id = $myresult['default_billing_id'];
  
  
  // save billing address
  $query = "UPDATE billing ".
    "SET street = '$street', ".
    "city = '$city', ".
    "state = '$state', ".
    "zip = '$zip', ".
    "country = '$country', ".
    "phone = '$phone', ".
    "fax = '$fax', ".
    "contact_email = '$contact_email' WHERE id = $default_billing_id";
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  print "<h3>$l_changessaved<h3>";
		
  // redirect them back to the customer record
  print "<script language=\"JavaScript\">window.location.href = \"index.php?load=customer&type=module\";</script>";
	
}
if ($base->input['save']) {
  // save customer information
  //$DB->debug = true;

  // GET Variables
  $name = $base->input['name'];
  $company = $base->input['company'];
  $street = $base->input['street'];
  $city = $base->input['city'];
  $state = $base->input['state'];
  $country = $base->input['country'];
  $zip = $base->input['zip'];
  $phone = $base->input['phone'];
  $alt_phone = $base->input['alt_phone'];
  $fax = $base->input['fax'];
  $source = $base->input['source'];
  $contact_email = $base->input['contact_email'];
  $secret_question = $base->input['secret_question'];
  $secret_answer = $base->input['secret_answer'];
  $account_manager_password = $base->input['account_manager_password'];
  $cancel_date = $base->input['cancel_date'];
  //$removal_date = $base->input['removal_date'];
  $account_number = $_SESSION['account_number'];
  $cancel_reason = $base->input['cancel_reason'];
  $notes = $base->input['notes'];
  
  // get the old values to compare with the new ones to check if we need to update the billing record also
  $old_street = $base->input['old_street'];
  $old_city = $base->input['old_city'];
  $old_state = $base->input['old_state'];
  $old_zip = $base->input['old_zip'];	
  $old_country = $base->input['old_country'];
  $old_phone = $base->input['old_phone'];
  $old_fax = $base->input['old_fax'];
  $old_contact_email = $base->input['old_contact_email'];
  
  // build update query
  // if the cancel date is empty, then put NULL in the cancel and removal date
  if ($cancel_date == "") {                
    $query = "UPDATE customer ".
      "SET name = '$name', ".
      "company = '$company', ".
      "street = '$street', ".
      "city = '$city', ".
      "state = '$state', ".
      "country = '$country', ".
      "zip = '$zip', ".
      "phone = '$phone', ".
      "alt_phone = '$alt_phone', ".
      "fax = '$fax', ".
      "source = '$source', ".
      "contact_email = '$contact_email', ".
      "secret_question = '$secret_question', ".
      "secret_answer = '$secret_answer', ".
      "cancel_date = NULL, ".
      "cancel_reason = NULL, ".
      "notes = '$notes', ".
      "account_manager_password = '$account_manager_password' ".
      "WHERE account_number = '$account_number'";
  } else {
    // there is a cancel date, so put there in there
    $query = "UPDATE customer ".
      "SET name = '$name', ".
      "company = '$company', ".
      "street = '$street', ".
      "city = '$city', ".
      "state = '$state', ".
      "country = '$country', ".
      "zip = '$zip', ".
      "phone = '$phone', ".
      "alt_phone = '$alt_phone', ".
      "fax = '$fax', ".
      "source = '$source', ".
      "contact_email = '$contact_email', ".
      "secret_question = '$secret_question', ".
      "secret_answer = '$secret_answer', ".
      "cancel_date = '$cancel_date', ".
      "cancel_reason = '$cancel_reason', ".
      "notes = '$notes', ".
      "account_manager_password = '$account_manager_password' ".
      "WHERE account_number = '$account_number'";
  }
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  
  print "<h3>$l_changessaved<h3>";
  
  // if the name, company, street, city, state, zip, phone, fax, or contact_email changed, ask if they want to update 
  // the default billing record address also.
  if ( ($street != $old_street) OR ($city != $old_city)
       OR ($state != $old_state) OR ($zip != $old_zip) 
       OR ($country != $old_country) OR ($phone != $old_phone)
       OR ($fax != $old_fax) OR ($contact_email != $old_contact_email) ) {
    echo "$l_addresschange<p>";
    
    print "<table cellpadding=15 cellspacing=0 border=0 width=720><td align=right width=360>";
    
    // if they hit yes, this will sent them into the billingaddress update
    
    print "<form style=\"margin-bottom:0;\" action=\"index.php\">";
    print "<input type=hidden name=load value=customer>";
    print "<input type=hidden name=type value=module>";
    print "<input type=hidden name=edit value=on>";
    print "<input name=billingaddress type=submit value=\" $l_yes \" class=smallbutton></form></td>";
    
    // if they hit no, send them back to the service edit screen
    
    print "<td align=left width=360><form style=\"margin-bottom:0;\" action=\"index.php\">";
    print "<input name=done type=submit value=\" $l_no \" class=smallbutton>";
    print "<input type=hidden name=load value=customer>";        
    print "<input type=hidden name=type value=module>";
    print "</form></td></table>";
    print "</blockquote>";
  } 
  else 
    {
      print "<script language=\"JavaScript\">window.location.href = \"index.php?load=customer&type=module\";</script>";
    }
 }
 else
   {
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
     $contact_email = $myresult['contact_email'];
     $secret_question = $myresult['secret_question'];
     $secret_answer = $myresult['secret_answer'];
     $default_billing_id = $myresult['default_billing_id'];
     $account_manager_password = $myresult['account_manager_password'];
     $cancel_date = $myresult['cancel_date'];
     $cancel_reason = $myresult['cancel_reason'];
     $notes = $myresult['notes'];
	
echo "<a href=\"index.php?load=customer&type=module\">[ $l_undochanges ]</a>";

echo "<table cellpadding=0 border=0 cellspacing=0 width=720>
<td valign=top width=360>
<form action=\"index.php\" method=post>
	<table cellpadding=5 cellspacing=1 border=0 width=360>
	<td bgcolor=\"#ccccdd\" width=180><b>$l_signupdate</b></td><td width=180 bgcolor=\"#ddddee\">$signup_date</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_name</b></td><td bgcolor=\"#ddddee\"><input name=\"name\" type=text value=\"$name\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_company</b></td><td bgcolor=\"#ddddee\"><input name=\"company\" type=text value=\"$company\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_street</b></td><td bgcolor=\"#ddddee\"><input name=\"street\" type=text value=\"$street\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_city</b></td><td bgcolor=\"#ddddee\"><input name=\"city\" type=text value=\"$city\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_state</b></td><td bgcolor=\"#ddddee\"><input name=\"state\" type=text value=\"$state\" size=3></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_zip</b></td><td bgcolor=\"#ddddee\"><input name=\"zip\" size=5 type=text value=\"$zip\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_phone</b></td><td bgcolor=\"#ddddee\"><input name=\"phone\" type=text value=\"$phone\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_alt_phone</b></td><td bgcolor=\"#ddddee\"><input name=\"alt_phone\" type=text value=\"$alt_phone\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_fax</b></td><td bgcolor=\"#ddddee\"><input name=\"fax\" type=text value=\"$fax\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_notes</b></td><td bgcolor=\"#ddddee\"><input name=\"notes\" type=text value=\"$notes\" colspan=2></td><tr>
	</table>
</td>
<td valign=top width=360>
	<table cellpadding=5 cellspacing=1 width=360>
	<td width=180 bgcolor=\"#ccccdd\"><b>$l_billingstatus</b></td><td width=180 bgcolor=\"#ffbbbb\"></td><tr>
	<td width=180 bgcolor=\"#ccccdd\"><b>$l_canceldate</b></td><td width=180 bgcolor=\"#ddddee\"><input name=\"cancel_date\" value=\"$cancel_date\"></td><tr>";
  
//<td width=180 bgcolor=\"#ccccdd\"><b>$l_removaldate</b></td><td width=180 bgcolor=\"#ddddee\"><input name=\"removal_date\" value=\"$removal_date\"></td><tr>
	
echo "<td bgcolor=\"#ccccdd\"><b>$l_source</b></td><td bgcolor=\"#ddddee\"><input name=\"source\" type=text value=\"$source\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_contactemail</b></td><td bgcolor=\"#ddddee\"><input name=\"contact_email\" type=text value=\"$contact_email\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_secret_question</b></td><td bgcolor=\"#ddddee\"><input name=\"secret_question\" type=text value=\"$secret_question\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_secret_answer</b></td><td bgcolor=\"#ddddee\"><input name=\"secret_answer\" type=text value=\"$secret_answer\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_defaultbillingid</b></td><td bgcolor=\"#ddddee\"><input type=hidden name=default_billing_id value=\"$default_billing_id\">$default_billing_id</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_country</b></td><td bgcolor=\"#ddddee\"><input name=\"country\" type=text value=\"$country\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_acctmngrpasswd</b></td><td bgcolor=\"#ddddee\"><input name=\"account_manager_password\" type=text value=\"$account_manager_password\"></td><tr>

<td width=180 bgcolor=\"#ccccdd\"><b>$l_cancelreason</b></td><td width=180 bgcolor=\"#ddddee\">";

// print the current reason and
// print list of reasons to choose from
 $query = "SELECT * FROM cancel_reason";
 $DB->SetFetchMode(ADODB_FETCH_ASSOC);
 $result = $DB->Execute($query) or die ("$l_queryfailed");
 echo "<select name=\"cancel_reason\" style=\"font-size: 7pt;\">".
   "<option value=\"\"></option>";
 while ($myresult = $result->FetchRow()) {
   $myid = $myresult['id'];
   $myreason = $myresult['reason'];
   if ($cancel_reason == $myid) {
     echo "<option value=\"$myid\" selected>$myreason</option>";
   } else {
     echo "<option value=\"$myid\">$myreason</option>";
   }
 }
 echo "</select>";
										echo "</td><tr>
	</table>
</td>
<tr>

<td colspan=2>
<center>

<!-- include hidden fields that hold the old street, city, state, zip, phone, and fax for checking against new ones
if an update is needed for the billing record -->
<input name=old_street type=hidden value=\"$street\">
<input name=old_city type=hidden value=\"$city\">
<input name=old_state type=hidden value=\"$state\">
<input name=old_zip type=hidden value=\"$zip\">
<input name=old_country type=hidden value=\"$country\">
<input name=old_phone type=hidden value=\"$phone\">
<input name=old_fax type=hidden value=\"$fax\">
<input name=old_contact_email type=hidden value=\"$contact_email\">

<input name=save type=submit class=smallbutton value=\"$l_savechanges\">
<input type=hidden name=load value=customer>
<input type=hidden name=type value=module>
<input type=hidden name=edit value=on>

</center>
</td>
</table>
</form>

"; //end

}
?>
