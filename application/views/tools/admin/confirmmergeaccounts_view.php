<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
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
   print "<form style=\"margin-bottom:0;\" action=\"index.php/tools/admin/savemergeaccounts" method=post>";
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
