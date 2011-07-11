<?php
// Copyright (C) 2002-2009  Paul Yasi (paul at citrusdb dot org)
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

// include the class used to create and send notices
include('include/notice.class.php');

//GET Variables
if (!isset($base->input['billing_id'])) { $base->input['billing_id'] = ""; }
if (!isset($base->input['cancel_date'])) { $base->input['cancel_date'] = ""; }
if (!isset($base->input['turnoff_date'])) { $base->input['turnoff_date'] = ""; }

$billing_id = $base->input['billing_id'];
$cancel_date = $base->input['cancel_date'];
$turnoff_date = $base->input['turnoff_date'];

if ($save) {

  echo "<pre>";
  $mynotice = new notice('collections',$billing_id, 'both', $turnoff_date, $turnoff_date, $cancel_date);
  echo "</pre>";

  // print link to the pdf to download
  $linkname = $mynotice->pdfname;
  $contactemail = $mynotice->contactemail;
  $linkurl = "index.php?load=tools/downloadfile&type=dl&filename=$linkname";
  
  echo "<p>$l_sent_collections_notice_answer $contactemail</p>";
  echo "<p>$l_download_pdf: <a href=\"$linkurl\">$linkname</a></p>";

  
  
  //  print "<script language=\"JavaScript\">window.location.href = \"index.php?load=billing&type=module\";</script>";
  
 } else {

  // calculate their cancel_date

  $query = "SELECT bi.id, bi.account_number, bh.payment_due_date,
  DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_turnoff DAY) AS turnoff_date, 
  DATE_ADD(bh.payment_due_date, INTERVAL g.dependent_canceled DAY) AS cancel_date 
  FROM billing_details bd 
  LEFT JOIN billing bi ON bd.billing_id = bi.id 
  LEFT JOIN billing_history bh ON bh.id = bd.invoice_number 
  LEFT JOIN general g ON bi.organization_id = g.id 
  WHERE bd.billed_amount > bd.paid_amount AND bd.billing_id = '$billing_id' GROUP BY bi.id";
  
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  $myresult = $result->fields;
  $cancel_date = $myresult['cancel_date'];
  $turnoff_date = $myresult['turnoff_date'];

  $human_cancel = humandate($cancel_date, $lang);
    
  // print the yes/no confirmation form
  print "<br><br>";
  print "<table cellpadding=15 cellspacing=0 border=0 width=520><td><center>";
  print "<form style=\"margin-bottom:0;\" action=\"index.php\" name=\"form1\" method=post>";

  print "$l_send_collections_notice_question <p>";
  print "<input type=hidden name=cancel_date value=$cancel_date>";
  print "<input type=hidden name=turnoff_date value=$turnoff_date>";
  
  print "<input type=hidden name=load value=billing>";
  print "<input type=hidden name=type value=module>";
  print "<input type=hidden name=collectionsnotice value=on>";
  print "<input type=hidden name=billing_id value=$billing_id>";
  
  print "<input name=save type=submit value=\" $l_yes \" class=smallbutton></form></center></td>";
  print "</td><td><form style=\"margin-bottom:0;\" action=\"index.php\" method=post>";
  print "<input name=done type=submit value=\" $l_no  \" class=smallbutton>";
  print "<input type=hidden name=load value=billing>";
  print "<input type=hidden name=type value=module>";
  print "</form></td></table>";
}
    
?>
