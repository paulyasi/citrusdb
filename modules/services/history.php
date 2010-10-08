<?php   
// Copyright (C) 2002-2005  Paul Yasi <paul@citrusdb.org>
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

if (!isset($base->input['editremovaldate'])) { $base->input['editremovaldate'] = ""; }
if (!isset($base->input['saveremovaldate'])) { $base->input['saveremovaldate'] = ""; }
if (!isset($base->input['removaldate'])) { $base->input['removaldate'] = ""; }
if (!isset($base->input['serviceid'])) { $base->input['serviceid'] = ""; }

$editremovaldate = $base->input['editremovaldate'];
$saveremovaldate = $base->input['saveremovaldate'];
$removaldate = $base->input['removaldate'];
$serviceid = $base->input['serviceid'];

if ($editremovaldate) {
  // prompt to change the removal date
  echo "<FORM ACTION=\"index.php\" METHOD=\"GET\">".
    "<input type=hidden name=load value=services>".
    "<input type=hidden name=type value=module>".
    "<input type=hidden name=history value=on>".
    "<input type=hidden name=saveremovaldate value=\"on\">".     
    "<input type=hidden name=serviceid value=\"$serviceid\">".
    "<table>".
    "<td>$l_new $l_removaldate:</td><td><input type=text name=removaldate ".
    "value=\"$removaldate\"></td><tr>".
    "<td></td><td><INPUT TYPE=\"SUBMIT\" NAME=\"submit\" ".
    "value=\"$l_submitrequest\"></td>".
    "</form>";
 } else if ($saveremovaldate) {
  // TODO: check that the new date entered is today or in the future, not the past
  
   $query = "UPDATE user_services SET removal_date = '$removaldate' ".
     "WHERE id = '$serviceid'";
   $result = $DB->Execute($query) or die ("due date update $l_queryfailed");

    // redirect back to the service history for their account
   echo "<script language=\"JavaScript\">window.location.href ".
     "= \"index.php?load=services&type=module&history=on\";</script>";
  
 } else {
  // print the history
echo "
<h3>$l_service $l_history</h3>
<a href=\"index.php?load=services&type=module\">[ $l_back ]</a>
<table cellpadding=0 border=0 cellspacing=0 width=720><td valign=top>		
	<table cellpadding=5 cellspacing=1 border=0 width=720>
	<td bgcolor=\"#ccccdd\"><b>$l_id</b></td>
	<td bgcolor=\"#ccccdd\"><b>$l_service</b></td>
	<td bgcolor=\"#ccccdd\"><b>$l_details</b></td>
	<td bgcolor=\"#ccccdd\"><b>$l_started</b></td>
	<td bgcolor=\"#ccccdd\"><b>$l_ended</b></td>
	<td bgcolor=\"#ccccdd\"><b>$l_removaldate</b></td>
	<td bgcolor=\"#ccccdd\"><b>$l_billingid</b></td>
	<td bgcolor=\"#ccccdd\"><b>$l_price</b></td>
	<td></td>
"; // end

// select all the user information in user_services and connect it with the 
// master_services description and cost

$query = "SELECT user.*, master.service_description, master.options_table, 
	master.pricerate, master.frequency 
	FROM user_services AS user, master_services AS master 
	WHERE user.master_service_id = master.id 
	AND user.account_number = '$account_number' AND user.removed = 'y' 
	ORDER BY user.usage_multiple DESC, master.pricerate DESC";
        $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");

// Print HTML table of user_services
while ($myresult = $result->FetchRow())
{
	// select the options_table to get data for the details column
	
	$options_table = $myresult['options_table'];
	$id = $myresult['id'];
	if ($options_table <> '') {
		// get the data from the options table and put into variables
		$query = "SELECT * FROM $options_table WHERE user_services = '$id'";
		$DB->SetFetchMode(ADODB_FETCH_NUM);
		$optionsresult = $DB->Execute($query) or die ("$l_queryfailed");
		$myoptions = $optionsresult->fields;
		$optiondetails = $myoptions[2];
	} else {
		$optiondetails = '';	
	}
	$master_service_id = $myresult['master_service_id'];
	$start_datetime = $myresult['start_datetime'];
	$end_datetime = $myresult['end_datetime'];
	$removal_date = $myresult['removal_date'];
	$billing_id = $myresult['billing_id'];
	$pricerate = $myresult['pricerate'];
	$usage_multiple = $myresult['usage_multiple'];
	$frequency = $myresult['frequency'];
	$removed = $myresult['removed'];
	$service_description = $myresult['service_description']; // from the LEFT JOINED master_services table

	// get the data from the billing tables to compare service and billing frequency
	$query = "SELECT * FROM billing b LEFT JOIN billing_types t ON b.billing_type = t.id WHERE b.id = '$billing_id'";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$freqoutput = $DB->Execute($query) or die ("$l_queryfailed");
	$freqresult = $freqoutput->fields;
	$billing_freq = $freqresult['frequency'];

	// multiply the pricerate and the usage_multiple to get the price to show
	$totalprice = $pricerate * $usage_multiple;

	//print "\n<tr onMouseOver='h(this);' onmouseout='deh(this);' onmousedown='window.location.href=\"index.php?load=services&type=module&edit=on&userserviceid=$id&servicedescription=$service_description&optionstable=$options_table&editbutton=Edit\";' bgcolor=\"#ddddee\">";
	
	print "<tr bgcolor=\"#ddddee\">\n
	<td onMouseOver='h(this);' onmouseout='deh(this);' onmousedown='window.location.href=\"index.php?load=services&type=module&edit=on&userserviceid=$id&servicedescription=$service_description&optionstable=$options_table&editbutton=Edit\";'>$id</td>
	<td onMouseOver='h(this);' onmouseout='deh(this);' onmousedown='window.location.href=\"index.php?load=services&type=module&edit=on&userserviceid=$id&servicedescription=$service_description&optionstable=$options_table&editbutton=Edit\";'>$service_description</td>
	<td onMouseOver='h(this);' onmouseout='deh(this);' onmousedown='window.location.href=\"index.php?load=services&type=module&edit=on&userserviceid=$id&servicedescription=$service_description&optionstable=$options_table&editbutton=Edit\";'>$optiondetails</td>
	<td onMouseOver='h(this);' onmouseout='deh(this);' onmousedown='window.location.href=\"index.php?load=services&type=module&edit=on&userserviceid=$id&servicedescription=$service_description&optionstable=$options_table&editbutton=Edit\";'>$start_datetime</td>
	<td onMouseOver='h(this);' onmouseout='deh(this);' onmousedown='window.location.href=\"index.php?load=services&type=module&edit=on&userserviceid=$id&servicedescription=$service_description&optionstable=$options_table&editbutton=Edit\";'>$end_datetime</td>
	<td><a href=\"index.php?load=services&type=module&history=on&serviceid=$id&editremovaldate=on&removaldate=$removal_date\">$removal_date</a></td>
	<td onMouseOver='h(this);' onmouseout='deh(this);' onmousedown='window.location.href=\"index.php?load=services&type=module&edit=on&userserviceid=$id&servicedescription=$service_description&optionstable=$options_table&editbutton=Edit\";'>$billing_id</td>
	<td onMouseOver='h(this);' onmouseout='deh(this);' onmousedown='window.location.href=\"index.php?load=services&type=module&edit=on&userserviceid=$id&servicedescription=$service_description&optionstable=$options_table&editbutton=Edit\";'>$totalprice</td>
	</td></tr>";
} 
print <<<END

	</table>

END;

 } // end else all

?>

