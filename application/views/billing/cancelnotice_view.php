<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed')?>
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

$billing_id = $base->input['billing_id'];
$cancel_date = $base->input['cancel_date'];

if ($save) {

  echo "<pre>";
  $mynotice = new notice('cancel', $billing_id, 'both', $cancel_date, $cancel_date, $cancel_date);
  echo "</pre>";

  // print link to the pdf to download
  $linkname = $mynotice->pdfname;
  $contactemail = $mynotice->contactemail;
  $linkurl = "index.php?load=tools/downloadfile&type=dl&filename=$linkname";
  
  echo "<p>$l_sent_cancel_notice $contactemail</p>";
  echo "<p>$l_download_pdf: <a href=\"$linkurl\">$linkname</a></p>";

  
  
  //  print "<script language=\"JavaScript\">window.location.href = \"index.php?load=billing&type=module\";</script>";
  
 } else {
    
  // print the yes/no confirmation form
  print "<br><br>";
  print "<table cellpadding=15 cellspacing=0 border=0 width=520><td><center>";
  print "<form style=\"margin-bottom:0;\" action=\"index.php\" method=post name=\"form1\">";

  print "$l_send_cancel_notice_question $human_cancel? <p>";
  print "<input type=hidden name=cancel_date value=$cancel_date>";
  
  print "<input type=hidden name=load value=billing>";
  print "<input type=hidden name=type value=module>";
  print "<input type=hidden name=cancelnotice value=on>";
  print "<input type=hidden name=billing_id value=$billing_id>";
  
  print "<input name=save type=submit value=\" $l_yes \" class=smallbutton></form></center></td>";
  print "</td><td><form style=\"margin-bottom:0;\" action=\"index.php\" method=post>";
  print "<input name=done type=submit value=\" $l_no  \" class=smallbutton>";
  print "<input type=hidden name=load value=billing>";
  print "<input type=hidden name=type value=module>";
  print "</form></td></table>";
}
    
?>
