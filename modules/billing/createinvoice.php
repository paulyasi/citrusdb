<?php
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

// Copyright (C) 2002-2008  Paul Yasi <paul@citrusdb.org>
// read the README file for more information

//GET Variables
if (!isset($base->input['billing_id'])) { $base->input['billing_id'] = ""; }

$billing_id = $base->input['billing_id'];

if ($save) {
  // figure out the billing method so that this can make invoices for any method

  $query = "SELECT t.method FROM billing b LEFT JOIN billing_types t ".
    "ON t.id = b.billing_type WHERE b.id = $billing_id";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("Method Query Failed");
  $myresult = $result->fields;	
  $method = $myresult['method'];
  
  /*--------------------------------------------------------------------*/
  // Create the billing data
  /*--------------------------------------------------------------------*/
  
  // determine the next available batch number
  $batchid = get_nextbatchnumber($DB);
  //echo "BATCH: $batchid<p>\n";
  
  $numtaxes = add_taxdetails($DB, NULL, $billing_id,
			     $method, $batchid, NULL);
  $numservices = add_servicedetails($DB, NULL, $billing_id,
				    $method, $batchid, NULL);
  
  //echo "taxes: $numtaxes, services: $numservices<p>";
  
  // create billinghistory
  create_billinghistory($DB, $batchid, $method, $user);	
  
  echo "$l_createdinvoice $billing_id";
  
  print "<script language=\"JavaScript\">window.location.href = \"index.php?load=billing&type=module\";</script>";
  
 } else {
	print "<br><br>";
	print "<h4>$l_areyousurecreateinvoice</h4>";
    print "<table cellpadding=15 cellspacing=0 border=0 width=720><td align=right width=360>";
    print "<form style=\"margin-bottom:0;\" action=\"index.php\" method=post>";
    print "<input type=hidden name=load value=billing>";
    print "<input type=hidden name=type value=module>";
    print "<input type=hidden name=createinvoice value=on>";
    print "<input type=hidden name=billing_id value=$billing_id>";
    print "<input name=save type=submit value=\" $l_yes \" class=smallbutton></form></td>";
    print "<td align=left width=360><form style=\"margin-bottom:0;\" action=\"index.php\" method=post>";
    print "<input name=done type=submit value=\" $l_no  \" class=smallbutton>";
    print "<input type=hidden name=load value=billing>";
    print "<input type=hidden name=type value=module>";
	print "</form></td></table>";
}
    
?>
