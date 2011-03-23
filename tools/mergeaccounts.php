<html>
<body bgcolor="#ffffff">

<?php
echo "<h3>$l_mergeaccounts</h3>
<p>";

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

// GET & POST Variables
if (!isset($base->input['from_account'])) { $base->input['from_account'] = ""; }
if (!isset($base->input['to_account'])) { $base->input['to_account'] = ""; }
if (!isset($base->input['confirm'])) { $base->input['confirm'] = ""; }

$submit = $base->input['submit'];
$confirm = $base->input['confirm'];
$from_account = $base->input['from_account'];
$to_account = $base->input['to_account'];

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
        echo '$l_youmusthaveadmin<br>';
        exit;
}

if ($submit) {
  // select the customer info for each account and show it to the user
  // to ask them to confirm they want to merge those accounts

  $query = "SELECT * FROM customer WHERE account_number = '$to_account'";
  $result = $DB->Execute($query) or die ("to_account select $l_queryfailed");
  $myresult = $result->fields;
  $to_name = $myresult['name'];
  $to_company = $myresult['company'];  
  $to_street = $myresult['street'];
  $to_city = $myresult['city'];
  $to_state = $myresult['state'];  
  $to_zip = $myresult['zip'];
  
  $query = "SELECT * FROM customer WHERE account_number = '$from_account'";
  $result = $DB->Execute($query) or die ("from_account select $l_queryfailed");
  $myresult = $result->fields;
  $from_name = $myresult['name'];
  $from_company = $myresult['company'];  
  $from_street = $myresult['street'];
  $from_city = $myresult['city'];
  $from_state = $myresult['state'];  
  $from_zip = $myresult['zip'];

  echo "$l_merge: <p>\n";
  echo "<table cellpadding=5><td><u>$l_from</u>: $from_account<br>$from_name<br>$from_company<br>$from_street".
    "<br>$from_city $from_state $from_zip</td>";
  echo "<td><u>$l_to</u>: $to_account<br>$to_name<br>$to_company<br>$to_street".
    "<br>$to_city $to_state $to_zip</td></table><br>";

     print "<table cellpadding=15 cellspacing=0 border=0 width=720><td align=right width=360>";
   print "<form style=\"margin-bottom:0;\" action=\"index.php\" method=post>";
   print "<input type=hidden name=load value=mergeaccounts>";
   print "<input type=hidden name=type value=tools>";
   print "<input type=hidden name=to_account value=$to_account>";
   print "<input type=hidden name=from_account value=$from_account>";
   print "<input name=confirm type=submit value=\"  $l_yes  \" class=smallbutton></form></td>";
   print "<td align=left width=360><form style=\"margin-bottom:0;\" action=\"index.php\" method=post>";
   print "<input type=hidden name=type value=tools>";
   print "<input name=done type=submit value=\"  $l_no  \" class=smallbutton>";
   print "<input type=hidden name=load value=mergeaccounts>";
   print "</form></td></table>";
  
 }

if ($confirm) {
  // get the default billing id in the $to_account
  $query = "SELECT default_billing_id FROM customer WHERE account_number = '$to_account'";
  $result = $DB->Execute($query) or die ("default billing id select $l_queryfailed");
  $myresult = $result->fields;
  $default_billing_id = $myresult['default_billing_id'];

  // move the services to the new record
  $query = "UPDATE user_services SET account_number = '$to_account', ".
    "billing_id = '$default_billing_id' WHERE account_number = '$from_account'";
  $result = $DB->Execute($query) or die ("user services update $l_queryfailed");

  // move the customer history to the new record
  $query = "UPDATE customer_history SET account_number = '$to_account' ".
    "WHERE account_number = '$from_account'";
  $result = $DB->Execute($query) or die ("customer history update $l_queryfailed");

  // make a note on both records that they were merged
  $desc = "$l_merged $from_account $l_to $to_account";  
  create_ticket($DB, $user, NULL, $to_account, 'automatic', $desc);
  create_ticket($DB, $user, NULL, $from_account, 'automatic', $desc);
  
  print "<h3>$desc</h3>";
 }

	echo "
	<P>
	<FORM ACTION=\"index.php\" METHOD=\"POST\">
	<B>$l_from</B><BR><INPUT TYPE=\"TEXT\" NAME=\"from_account\">
	<P>
	<B>$l_to</B><BR><INPUT TYPE=\"TEXT\" NAME=\"to_account\">
	<P>
	<P>
	<input type=hidden name=load value=mergeaccounts>
	<input type=hidden name=type value=tools>
	<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_submit\">
	</FORM>";


?>

</body>
</html>
