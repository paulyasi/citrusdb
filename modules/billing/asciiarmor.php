<?php   
// Copyright (C) 2009  Paul Yasi (paul at citrusdb.org)
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

// GET Variables
if (!isset($base->input['billing_id'])) { $base->input['billing_id'] = ""; }
$billing_id = $base->input['billing_id'];
$creditcard_number = $base->input['creditcard_number'];

$encrypted = $_POST['encrypted'];
$encrypted = safe_value_with_newlines($encrypted);

if ($save) {
  // make sure the first lines says -----BEGIN PGP MESSAGE-----
  // make sure the last line says -----END PGP MESSAGE-----
  
  $encrypted_line = explode("\n", $encrypted);
  $firstline = rtrim($encrypted_line[0]); // rtrim to remove the newline character at the end
  $lastline = array_pop($encrypted_line); // do not rtrim since no newline should be here
  if ($firstline <> "-----BEGIN PGP MESSAGE-----") {
    echo "\"$firstline\" ";
    die ("Error in ciphertext format");
  }
  if ($lastline <> "-----END PGP MESSAGE-----") {
    echo "\"$lastline\" ";
    die ("Error in ciphertext format");
  }
  
  
  // update the billing record with the new info
  $query = "UPDATE billing SET ".
    "encrypted_creditcard_number = '$encrypted', ".
    "creditcard_number = '$creditcard_number' ".
    "WHERE id = '$billing_id' LIMIT 1";
  $billingupdate = $DB->Execute($query) or die ("$l_queryfailed");

  print "<script language=\"JavaScript\">window.location.href = ".
    "\"index.php?load=billing&type=module\";</script>";

} else {

  $query = "SELECT encrypted_creditcard_number, creditcard_number FROM billing ".
    "WHERE id = '$billing_id'";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("Armor Query Failed");
  $myresult = $result->fields;	
  $encrypted_card = $myresult['encrypted_creditcard_number'];
  $creditcard_number = $myresult['creditcard_number'];
  
  print "<br><br>";
  print "<h4>Replace the ciphertext<br>(in ascii armor format)</h4>";
  print "<table cellpadding=15 cellspacing=0 border=0 width=620>".
    "<td align=center width=360>";
  print "<form style=\"margin-bottom:0;\" action=\"index.php\" method=post>";
  print "<textarea name=encrypted cols=70 rows=20>$encrypted_card</textarea><br>";
  print "$l_masked_ccnumber: <input type=text name=creditcard_number value=\"$creditcard_number\"><br>";  
  print "<input type=hidden name=load value=billing>";
  print "<input type=hidden name=type value=module>";
  print "<input type=hidden name=asciiarmor value=on>";
  print "<input type=hidden name=billing_id value=$billing_id>";
  print "<input name=save type=submit value=\" $l_replace \" ".
    "class=smallbutton></form>";
  print "<br><form style=\"margin-bottom:0;\" ".
    "action=\"index.php\">";
  print "<input name=done type=submit value=\" $l_cancel  \" class=smallbutton>";
  print "<input type=hidden name=load value=billing>";
  print "<input type=hidden name=type value=module>";
  print "</form></td></table>";
}
?>
