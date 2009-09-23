<?php   
// Copyright (C) 2002-2007  Paul Yasi <paul@citrusdb.org>, read the README file for more information

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

//session_start();
$account_number = $_SESSION['account_number'];

// GET Variables
$userserviceid = $base->input['userserviceid'];
$deletenow = $base->input['deletenow'];
$deletenoauto = $base->input['deletenoauto'];
$undeletenow = $base->input['undeletenow'];

if ($deletenow) {
	// figure out the signup anniversary removal date
	$query = "SELECT signup_date FROM customer 
		WHERE account_number = '$account_number'";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	$myresult = $result->fields;
	$signup_date = $myresult['signup_date'];
	list($myyear, $mymonth, $myday) = split('-', $signup_date);
	$removal_date  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("$myday"), date("Y")));
	$today  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
	if ($removal_date <= $today) {
        	$removal_date  = date("Y-m-d", mktime(0, 0, 0, date("m")+1  , date("$myday"), date("Y")));
        }
	
	// delete the service and do other notifications
	delete_service($userserviceid, 'removed', $removal_date);
	
	print "<script language=\"JavaScript\">window.location.href = \"index.php?load=services&type=module\";</script>";
}

if ($deletenoauto) {
  // delete the service without an automatic removal dateand do other notifications
  delete_service($userserviceid, 'removed', '');
  
  print "<script language=\"JavaScript\">window.location.href = \"index.php?load=services&type=module\";</script>";
}


if ($undeletenow) {
	// undelete the service by removing the removed flag and dates
	undelete_service($userserviceid, $l_undelete);
	
	print "<script language=\"JavaScript\">window.location.href = \"index.php?load=services&type=module\";</script>";

}

?>
