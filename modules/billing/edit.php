<SCRIPT LANGUAGE="JavaScript" SRC="include/CalendarPopup.js"></SCRIPT>
   <SCRIPT LANGUAGE="JavaScript">
   var cal = new CalendarPopup();

function cardval(s) 
{
  // remove non-numerics
  var v = "0123456789";
  var w = "";
  for (i=0; i < s.length; i++) {
    x = s.charAt(i);
    if (v.indexOf(x,0) != -1) {
      w += x;
    }
  }
  
  // validate number
  j = w.length / 2;
  if (j < 6.5 || j > 8 || j == 7) {
    return false;
  }
  
  k = Math.floor(j);
  m = Math.ceil(j) - k;
  c = 0;
  for (i=0; i<k; i++) {
    a = w.charAt(i*2+m) * 2;
    c += a > 9 ? Math.floor(a/10 + a%10) : a;
  }
  
  for (i=0; i<k+m; i++) c += w.charAt(i*2+1-m) * 1; {
    return (c%10 == 0);
  }
}
</SCRIPT>
<?php   
  // Copyright (C) 2002-2009  Paul Yasi (paul at citrusdb.org)
  // Read the README file for more information
  /*--------------------------------------------------------------------------*/
  // Check for authorized accesss
  /*--------------------------------------------------------------------------*/
if(constant("INDEX_CITRUS") <> 1){
  echo "You must be logged in to run this.  Goodbye.";
  exit;	
}

if (!defined("INDEX_CITRUS")) {
  echo "You must be logged in to run this.  Goodbye.";
  exit;
}

// GET Variables
if (!isset($base->input['billing_id'])) { $base->input['billing_id'] = ""; }
if (!isset($base->input['name'])) { $base->input['name'] = ""; }
if (!isset($base->input['company'])) { $base->input['company'] = ""; }
if (!isset($base->input['street'])) { $base->input['street'] = ""; }
if (!isset($base->input['city'])) { $base->input['city'] = ""; }
if (!isset($base->input['state'])) { $base->input['state'] = ""; }
if (!isset($base->input['zip'])) { $base->input['zip'] = ""; }
if (!isset($base->input['country'])) { $base->input['country'] = ""; }
if (!isset($base->input['phone'])) { $base->input['phone'] = ""; }
if (!isset($base->input['fax'])) { $base->input['fax'] = ""; }
if (!isset($base->input['billing_type'])) { $base->input['billing_type'] = ""; }
if (!isset($base->input['creditcard_number'])) { $base->input['creditcard_number'] = ""; }

if (!isset($base->input['creditcard_expire'])
    OR $base->input['creditcard_expire'] == "") { 
  $base->input['creditcard_expire'] = "0"; 
}
if (!isset($base->input['next_billing_date'])
    OR $base->input['next_billing_date'] == "") { 
  $base->input['next_billing_date'] = "0000-00-00"; 
 }
if (!isset($base->input['from_date'])
    OR $base->input['from_date'] == "") { 
  $base->input['from_date'] = "0000-00-00"; 
 }
if (!isset($base->input['payment_due_date'])
    OR $base->input['payment_due_date'] == "") { 
  $base->input['payment_due_date'] = "0000-00-00"; 
 }
if (!isset($base->input['rerun_date']) OR
    $base->input['rerun_date'] == "") { 
  $base->input['rerun_date'] = "0000-00-00"; 
 }

if (!isset($base->input['contact_email'])) { $base->input['contact_email'] = ""; }
if (!isset($base->input['notes'])) { $base->input['notes'] = ""; }
if (!isset($base->input['pastdue_exempt'])) { $base->input['pastdue_exempt'] = ""; }
if (!isset($base->input['po_number'])) { $base->input['po_number'] = ""; }

$billing_id = $base->input['billing_id'];
$name = $base->input['name'];
$company = $base->input['company'];
$street = $base->input['street'];
$city = $base->input['city'];
$state = $base->input['state'];
$zip = $base->input['zip'];
$country = $base->input['country'];
$phone = $base->input['phone'];
$fax = $base->input['fax'];
$billing_type = $base->input['billing_type'];
$creditcard_number = $base->input['creditcard_number'];
$creditcard_expire = $base->input['creditcard_expire'];
$next_billing_date = $base->input['next_billing_date'];
$from_date = $base->input['from_date'];
$payment_due_date = $base->input['payment_due_date'];
$rerun_date = $base->input['rerun_date'];
$contact_email = $base->input['contact_email'];
$notes = $base->input['notes'];
$pastdue_exempt = $base->input['pastdue_exempt'];
$po_number = $base->input['po_number'];

if ($save) {
  //$DB->debug = true;
  // save billing information
  
  // check if there is a non-masked credit card number in the input
  // if the second cararcter is a * then it's already masked
  
  $newcc = FALSE; // set to false so we don't replace it unnecessarily

  // check if the credit card entered already masked and not blank
  // eg: a replacement was not entered
  if ($creditcard_number[1] <> '*' AND $creditcard_number <> '') {

    $gpgcommandline = "echo $creditcard_number | $gpg_command";
    
    $oldhome = getEnv("HOME");

    // destroy the output array before we use it again
    unset($encrypted);
    
    putenv("HOME=$path_to_home");
    $gpgresult = exec($gpgcommandline, $encrypted, $errorcode);
    putenv("HOME=$oldhome");

    // if there is a gpg error, stop here
    if ($errorcode > 0) {
      die ("Credit Card Encryption Error, See Webserver Log");
    }
    
    // change the ouput array into ascii ciphertext block
    $encrypted_creditcard_number = implode("\n",$encrypted);
    
    // wipe out the middle of the creditcard_number before it gets inserted
    $length = strlen($creditcard_number);
    $firstdigit = substr($creditcard_number, 0,1);
    $lastfour = substr($creditcard_number, -4);
    $creditcard_number = "$firstdigit" . "***********" . "$lastfour";    

    //echo "$gpgcommandline<br><pre>$encrypted_creditcard_number</pre>\n";
    
    $newcc = TRUE;
  }

  if ($newcc == TRUE) {
    // insert with a new credit card and encrypted ciphertext
    $query = "UPDATE billing ".
      "SET name = '$name',".
      "company = '$company',".
      "street = '$street',".
      "city = '$city',".
      "state = '$state',".
      "zip = '$zip',".
      "country = '$country',".
      "phone = '$phone',".
      "fax = '$fax',".
      "billing_type = '$billing_type',".
      "creditcard_number = '$creditcard_number',".
      "creditcard_expire = '$creditcard_expire',".
      "next_billing_date = '$next_billing_date',".
      "from_date = '$from_date',".
      "payment_due_date = '$payment_due_date',".
      "notes = '$notes',".
      "pastdue_exempt = '$pastdue_exempt',".
      "po_number = '$po_number',".
      "contact_email = '$contact_email', ".
      "encrypted_creditcard_number = '$encrypted_creditcard_number'".
      "WHERE id = $billing_id";
  } elseif ($creditcard_number == '') {
    // no card number, insert an empty NULL credit card and NULL ciphertext
    $query = "UPDATE billing ".
      "SET name = '$name',".
      "company = '$company',".
      "street = '$street',".
      "city = '$city',".
      "state = '$state',".
      "zip = '$zip',".
      "country = '$country',".
      "phone = '$phone',".
      "fax = '$fax',".
      "billing_type = '$billing_type',".
      "creditcard_number = NULL, ".
      "creditcard_expire = NULL, ".
      "next_billing_date = '$next_billing_date',".
      "from_date = '$from_date',".
      "payment_due_date = '$payment_due_date',".
      "notes = '$notes',".
      "pastdue_exempt = '$pastdue_exempt',".
      "po_number = '$po_number',".
      "contact_email = '$contact_email', ".
      "encrypted_creditcard_number = NULL ".
      "WHERE id = $billing_id";    
  } else {
    // insert without changing the credit card or ciphertext
        $query = "UPDATE billing ".
      "SET name = '$name',".
      "company = '$company',".
      "street = '$street',".
      "city = '$city',".
      "state = '$state',".
      "zip = '$zip',".
      "country = '$country',".
      "phone = '$phone',".
      "fax = '$fax',".
      "billing_type = '$billing_type',".
      "creditcard_expire = '$creditcard_expire',".
      "next_billing_date = '$next_billing_date',".
      "from_date = '$from_date',".
      "payment_due_date = '$payment_due_date',".
      "notes = '$notes',".
      "pastdue_exempt = '$pastdue_exempt',".
      "po_number = '$po_number',".
      "contact_email = '$contact_email' ".
      "WHERE id = $billing_id";
  }

  $result = $DB->Execute($query) or die ("$l_queryfailed");
  
  // set the to_date automatically
  automatic_to_date($DB, $from_date, $billing_type, $billing_id);
  
  print "<h3>$l_changessaved<h3>";
  print "<script language=\"JavaScript\">window.location.href = \"index.php?load=billing&type=module\";</script>";

 } else {  

  /*-----------------------------------------------------------------------*/
  // show the data to edit
  /*-----------------------------------------------------------------------*/
  $query = "SELECT * FROM billing WHERE id = $billing_id";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("Billing Query Failed");
  $myresult = $result->fields;	
  
  // Put values into variablies and Print HTML results
  
  $id = $myresult['id'];
  $name = $myresult['name'];
  $company = $myresult['company'];        
  $street = $myresult['street'];
  $city = $myresult['city'];
  $state = $myresult['state'];
  $zip = $myresult['zip'];
  $country = $myresult['country'];
  $phone = $myresult['phone'];
  $fax = $myresult['fax'];
  $billing_type = $myresult['billing_type'];
  $creditcard_number = $myresult['creditcard_number'];
  $creditcard_expire = $myresult['creditcard_expire'];
  $next_billing_date = $myresult['next_billing_date'];
  $from_date = $myresult['from_date'];
  $to_date = $myresult['to_date'];
  $payment_due_date = $myresult['payment_due_date'];
  $contact_email = $myresult['contact_email'];
  $notes = $myresult['notes'];
  $pastdue_exempt = $myresult['pastdue_exempt'];
  $po_number = $myresult['po_number'];
  $organization_id = $myresult['organization_id'];
  
  echo "<a href=\"index.php?load=billing&type=module\">[ $l_undochanges ]</a>";
  
  // get the organization info
  $query = "SELECT org_name FROM general WHERE id = $organization_id LIMIT 1";
  $orgresult = $DB->Execute($query) or die ("$l_queryfailed");
  $myorgresult = $orgresult->fields;
  $organization_name = $myorgresult['org_name']; 
  echo "<h3>$l_organizationname: $organization_name</h3>";
  
  echo "<table cellpadding=0 border=0 cellspacing=0 width=720>
<td valign=top width=360>
<form action=\"index.php?load=billing&type=module&edit=on&save=on\" name=\"form1\" AUTOCOMPLETE=\"off\" method=post>
	<table cellpadding=5 cellspacing=1 border=0 width=360>
	<td bgcolor=\"#ccccdd\" width=180><b>$l_id</b></td><td width=180 bgcolor=\"#ddddee\">$id</td><tr>
	<td bgcolor=\"#ccccdd\"><b>Name</b></td><td bgcolor=\"#ddddee\">
		<input name=\"name\" type=text value=\"$name\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_company</b></td><td bgcolor=\"#ddddee\">
		<input name=\"company\" type=text value=\"$company\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_street</b></td><td bgcolor=\"#ddddee\">
		<input name=\"street\" type=text value=\"$street\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_city</b></td><td bgcolor=\"#ddddee\">
		<input name=\"city\" type=text value=\"$city\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_state</b></td><td bgcolor=\"#ddddee\">
		<input name=\"state\" type=text value=\"$state\" size=3></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_zip</b></td><td bgcolor=\"#ddddee\">
		<input name=\"zip\" size=5 type=text value=\"$zip\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_country</b></td><td bgcolor=\"#ddddee\">
		<input name=\"country\" type=text value=\"$country\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_phone</b></td><td bgcolor=\"#ddddee\">
		<input name=\"phone\" type=text value=\"$phone\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_fax</b></td><td bgcolor=\"#ddddee\">
		<input name=\"fax\" type=text value=\"$fax\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_contactemail</b></td><td bgcolor=\"#ddddee\">
		<input name=\"contact_email\" type=text value=\"$contact_email\"></td><tr>
	</table>
</td>
<td valign=top width=360>
	<table cellpadding=5 cellspacing=1 width=360>
	<td width=180 bgcolor=\"#ccccdd\"><b>$l_billingtype</b></td>
	<td width=180 bgcolor=\"#ffbbbb\">

";

	print "<select name=\"billing_type\">\n";
        $query = "SELECT * FROM billing_types ORDER BY name";
        $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	while ($myresult = $result->FetchRow())
	{
		$bt_id = $myresult['id'];
		$bt_name = $myresult['name'];
		if ($billing_type == $bt_id)
		{
			print "<option selected value=$bt_id>$bt_name</option>\n";
		}
		else 
		{ 
			print "<option value=$bt_id>$bt_name</option>\n"; 
		}
	}

	print "</select>\n";

echo "</td><tr>".
  "<td bgcolor=\"#ccccdd\"><b>$l_ccnumber</b></td>".
  "<td bgcolor=\"#ddddee\">".
  "<input size=17 name=\"creditcard_number\" ".
  "type=text value=\"$creditcard_number\" ".
  "onChange=\"if(!cardval(document.forms['form1'].creditcard_number.value)) ".
  "{ alert ('$l_notvalid'); ".
  "document.form1.creditcard_number.style.color='#EE0000';} ".
  "else { document.form1.creditcard_number.style.color='#000000'; }\">".
  " <a href=\"index.php?load=billing&type=module&asciiarmor=on&billing_id=$id\">$l_ciphertext</a>".
  "</td><tr>".
  "<td bgcolor=\"#ccccdd\"><b>$l_ccexpire</b></td>".
  "<td bgcolor=\"#ddddee\">".
  "<input size=5 name=\"creditcard_expire\" ".
  "type=text value=\"$creditcard_expire\">".
  "</td><tr>".
	
  "<td bgcolor=\"#ccccdd\"><b>$l_pastdueexempt</b></td>".
  "<td bgcolor=\"#ddddee\">".
  "<input type=radio name=pastdue_exempt value=n"; if ($pastdue_exempt == "n") { echo " checked "; }
	echo ">$l_no
	<input type=radio name=pastdue_exempt value=y"; if ($pastdue_exempt == "y") { echo " checked "; }
	echo ">$l_yes
	<input type=radio name=pastdue_exempt value=bad_debt"; if ($pastdue_exempt == "bad_debt") { echo " checked "; }
        echo ">$l_bad_debt
	
	</td><tr>
	
	<td bgcolor=\"#ccccdd\"><b>$l_nextbillingdate</b></td>
	<td bgcolor=\"#ddddee\">
	<input name=\"next_billing_date\" type=text value=\"$next_billing_date\" size=12>
	<A HREF=\"#\"
	onClick=\"cal.select(document.forms['form1'].next_billing_date,'anchor1','yyyy-MM-dd'); 
	return false;\"
	NAME=\"anchor1\" ID=\"anchor1\" style=\"color:blue\">[$l_select]</A>
	</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_from $l_date</b></td><td bgcolor=\"#ddddee\">
	<input name=\"from_date\" type=text value=\"$from_date\" size=12>
	<A HREF=\"#\"
	onClick=\"cal.select(document.forms['form1'].from_date,'anchor1','yyyy-MM-dd'); 
	return false;\"
	NAME=\"anchor1\" ID=\"anchor1\" style=\"color:blue\">[$l_select]</A>
	</td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_to $l_date</b></td>
	<td bgcolor=\"#ddddee\">$to_date</td><tr>

<td bgcolor=\"#ccccdd\"><b>$l_paymentduedate</b></td>
	<td bgcolor=\"#ddddee\">
	<input name=\"payment_due_date\" type=text value=\"$payment_due_date\" size=12>
	<A HREF=\"#\"
	onClick=\"cal.select(document.forms['form1'].payment_due_date,'anchor1','yyyy-MM-dd'); 
	return false;\"
	NAME=\"anchor1\" ID=\"anchor1\" style=\"color:blue\">[$l_select]</A>
	</td><tr>

<td bgcolor=\"#ccccdd\"><b>$l_rerun $l_date</b></td>
	<td bgcolor=\"#ddddee\">
	<input name=\"rerun_date\" type=text value=\"$rerun_date\" size=12>
	<A HREF=\"#\"
	onClick=\"cal.select(document.forms['form1'].rerun_date,'anchor1','yyyy-MM-dd'); 
	return false;\"
	NAME=\"anchor1\" ID=\"anchor1\" style=\"color:blue\">[$l_select]</A>
	</td><tr>

	<td bgcolor=\"#ccccdd\"><b>$l_po_number</b></td>
	<td bgcolor=\"#ddddee\">
	<input name=\"po_number\" type=text value=\"$po_number\"></td><tr>
	<td bgcolor=\"#ccccdd\"><b>$l_notes</b></td>
	<td bgcolor=\"#ddddee\">
	<input name=\"notes\" type=text value=\"$notes\"></td><tr>
	</table>
</td>
<tr>
<td colspan=2>
<center>
<input name=save type=submit class=smallbutton value=\"$l_savechanges\">
<input type=hidden name=load value=billing>
<input type=hidden name=type value=module>
<input type=hidden name=edit value=on>
<input type=hidden name=billing_id value=$billing_id>
</center>
</td>
</table>
</form>

"; // end

}
?>
