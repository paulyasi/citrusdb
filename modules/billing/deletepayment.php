<?php   
// Copyright (C) 2008  Paul Yasi (paul at citrusdb.org)
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

if ($save) {
  // set the account payment_history to nsf
  $query = "DELETE FROM payment_history ".
    "WHERE id = $paymentid LIMIT 1";
  $paymentresult = $DB->Execute($query) or die ("$l_queryfailed");
  
  // redirect back to the billing screen
  print "<script language=\"JavaScript\">window.location.href = ".
    "\"index.php?load=billing&type=module\";</script>";

 } else {
  
  print "<br><br>";
  print "<h4>&nbsp;&nbsp;&nbsp; Are you sure you want to delete this payment entry?</h4>";
  print "<table cellpadding=15 cellspacing=0 border=0 width=720>".
    "<td align=right width=360>";

  print "<form style=\"margin-bottom:0;\" action=\"index.php\">";
  print "<input type=hidden name=load value=billing>";
  print "<input type=hidden name=type value=module>";
  print "<input type=hidden name=deletepayment value=on>";
  print "<input type=hidden name=paymentid value=$paymentid>";
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
