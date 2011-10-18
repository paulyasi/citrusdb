<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_exemptreport: ";
// Copyright (C) 2008  Paul Yasi (paul at citrusdb dot org)
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


if (!isset($base->input['exempttype'])) { $base->input['exempttype'] = ""; }
$exempttype = $base->input['exempttype'];

if ($exempttype == "pastdueexempt") {
  echo "$l_pastdueexempt<p>";
   echo "<table><tr style=\"font-weight: bold;\">".
     "<td>$l_accountnumber</td><td>$l_name</td><td>$l_company</td><td>$l_street</td><tr>";

   $query = "SELECT * FROM billing WHERE pastdue_exempt = 'y'";
   $DB->SetFetchMode(ADODB_FETCH_ASSOC);
   $result = $DB->Execute($query) or die ("$l_queryfailed");
   
   while ($myresult = $result->FetchRow()) {
     $acctnum = $myresult['account_number'];
     $name = $myresult['name'];
     $company = $myresult['company'];
     $street = $myresult['street'];
     echo "<td>$acctnum</td>".
       "<td>$name</td>".
       "<td>$company</td>".
       "<td>$street</td><tr>";
   }     
   echo "</table>";
  
 } elseif ($exempttype == "baddebt") {
   echo "$l_bad_debt<p>";
   echo "<table><tr style=\"font-weight: bold;\">".
     "<td>$l_accountnumber</td><td>$l_name</td><td>$l_company</td><td>$l_street</td><tr>";

   $query = "SELECT * FROM billing WHERE pastdue_exempt = 'bad_debt'";
   $DB->SetFetchMode(ADODB_FETCH_ASSOC);
   $result = $DB->Execute($query) or die ("$l_queryfailed");
   
   while ($myresult = $result->FetchRow()) {
     $acctnum = $myresult['account_number'];     
     $name = $myresult['name'];
     $company = $myresult['company'];
     $street = $myresult['street'];
     echo "<td>$acctnum</td>".
       "<td>$name</td>".
       "<td>$company</td>".
       "<td>$street</td><tr>";
   }     
   echo "</table>";
   
   } elseif ($exempttype == "taxexempt") {
     echo "$l_taxexempt<p>";
     echo "<table><tr style=\"font-weight: bold;\">".
       "<td>$l_accountnumber</td>".
       "<td>$l_description</td><td>$l_name</td><td>$l_company</td>".
       "<td>$l_taxexemptid</td><td>$l_expirationdate</td><tr>";
     
     $query = "SELECT tr.description, c.account_number, c.name, c.company, ".
       "te.customer_tax_id, ".
       "te.expdate FROM tax_exempt te ".
       "LEFT JOIN customer c ON c.account_number = te.account_number ".
       "LEFT JOIN tax_rates tr ON tr.id = tax_rate_id";
     $DB->SetFetchMode(ADODB_FETCH_ASSOC);
     $result = $DB->Execute($query) or die ("$l_queryfailed");
     
     while ($myresult = $result->FetchRow()) {
       $description = $myresult['description'];
       $acctnum = $myresult['account_number'];       
       $name = $myresult['name'];
       $company = $myresult['company'];
       $customertaxid = $myresult['customer_tax_id'];
       $customertaxexpdate = $myresult['expdate'];
       echo "<td>$acctnum</td>".
	 "<td>$description</td>".
	 "<td>$name</td>".
	 "<td>$company</td>".
	 "<td>$customertaxid</td>".
	 "<td>$customertaxexpdate</td><tr>";
     }
     
     echo "</table>";

     } else {
  echo "<p><FORM ACTION=\"index.php\" METHOD=\"GET\"><table>".
    "<select name=\"exempttype\">";
  echo "<option value=\"pastdueexempt\">$l_pastdueexempt</option>";
  echo "<option value=\"baddebt\">$l_bad_debt</option>";  
  echo "<option value=\"taxexempt\">$l_taxexempt</option>";
  echo "</select>";
  echo "</select><input type=hidden name=type value=tools>".
    "<input type=hidden name=load value=exemptreport>".
    "</td><tr> ".
    "<td></td><td><br>".
    "<input type=submit name=\"$l_submit\" value=\"submit\"></td>".
    "</table>".
    "</form> <p>";
 }

?>
</body>
</html>







