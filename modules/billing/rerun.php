<?php   
// Copyright (C) 2002-2009  Paul Yasi (paul at citrusdb.org)
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
$billing_id = $base->input['billing_id'];
$fieldlist = $base->input['fieldlist'];

if ($save) {
  // set the rerun date to the next available billing date
  $mydate = get_nextbillingdate();
  
  // make sure the rerun date is not set to the same as the next_billing_date
  $query = "SELECT next_billing_date FROM billing WHERE id = '$billing_id'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  $myresult = $result->fields;	
  $next_billing_date = $myresult['next_billing_date'];
  
  if ($next_billing_date == $mydate) {
    echo "<h3>$l_rerundateerror</h3>".
      "<center><form style=\"margin-bottom:0;\" action=\"index.php\">".
      "<input name=done type=submit value=\" $l_ok  \" class=smallbutton>".
      "<p></center>";
  } else {
    $query = "UPDATE billing SET rerun_date = '$mydate' ".
      "WHERE id = '$billing_id'";
    $result = $DB->Execute($query) or die ("$l_queryfailed");

    // TODO: set the rerun flag for the items chosen
    // parse the fieldlist    
  
    // add the services to the user_services table and the options table
    $fieldlist = substr($fieldlist, 1); 
    
    // loop through post_vars associative/hash to get field values
    $array_fieldlist = explode(",",$fieldlist);
    
    foreach ($base->input as $mykey => $myvalue) {
      foreach ($array_fieldlist as $myfield) {
	//print "$mykey<br>";
	if ($myfield == $mykey) {
	  $fieldvalues .= ',\'' . $myvalue . '\'';
	  // set the rerun flag for this billing_detail id value to 'y'
	  $query = "UPDATE billing_details SET rerun = 'y' ".
	    "WHERE id = '$myvalue'";
	  $result = $DB->Execute($query) or die ("$l_queryfailed");

	  // TODO: unset all the other things
	  
	}
      }
    }
    
    print "<h3>$l_changessaved<h3>";
    print "<script language=\"JavaScript\">window.location.href = ".
      "\"index.php?load=billing&type=module\";</script>";
    
  }

     
 } else {
  

  print "<br><br>";
  print "<h4>$l_areyousurereruncreditcard</h4>";
  
  // TODO:
  // show all items from billing details that are unpaid
  // that would be run when a rerun is done
  // and allow one to check or uncheck which ones they want
  // to be rerun

  // select the billing detail items that are unpaid
  $query = "SELECT bd.id bd_id, bd.user_services_id, bd.billed_amount, ".
    "bd.paid_amount, us.id us_id, ms.service_description ".
    "FROM billing_details bd ".
    "LEFT JOIN user_services us ON us.id = bd.user_services_id ".
    "LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
    "WHERE bd.billing_id = '$billing_id' ".
    "AND bd.billed_amount > bd.paid_amount";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);        
  $result = $DB->Execute($query) or die ("Detail Query Failed"); 
  $i = 0;

  print "<form style=\"margin-bottom:0;\" action=\"index.php\">\n";

  echo "<blockquote><table>\n";
  
  while ($myresult = $result->FetchRow()) {
    $detail_id = $myresult['bd_id'];
    $service_id = $myresult['us_id'];
    $user_services_id = $myresult['user_services_id'];
    $detail_total = sprintf("%.2f",$myresult['billed_amount'] - $myresult['paid_amount']);
    $description = $myresult['service_description'];

    // clear the rerun flag on all the items shown before you allow setting
    // of new rerun flags, also clear the rerun date in the billing record here
    $query = "UPDATE billing_details SET ".
      "rerun = 'n' WHERE id = '$detail_id'";
    $updatedetail = $DB->Execute($query) or die ("Detail Update Failed");

    $query = "UPDATE billing SET ".
      "rerun_date = NULL WHERE id = '$billing_id'";
    $updatedetail = $DB->Execute($query) or die ("Detail Update Failed");
    
    // print the detail items that are unpaid and can be rerun

    echo "<td><input checked type=checkbox name=rerun_service_$detail_id value=\"$detail_id\"></td>\n";    
    echo "<td>$service_id</td>\n";
    echo "<td>$description</td>\n";
    echo "<td>$detail_total</td>\n";
    
    echo "<tr>\n";

    $fieldname = "rerun_service_$detail_id";

    $fieldlist .= ',' . $fieldname;
  }

  print "<input type=hidden name=fieldlist value=$fieldlist>";

  echo "</table></blockquote>\n";
  

  // print the yes/no buttons
  
  print "<table cellpadding=15 cellspacing=0 border=0 width=720>".
    "<td align=right width=360>";

  print "<input type=hidden name=load value=billing>";
  print "<input type=hidden name=type value=module>";
  print "<input type=hidden name=rerun value=on>";
  print "<input type=hidden name=billing_id value=$billing_id>";
  print "<input name=save type=submit value=\" $l_yes \" class=smallbutton>".
    "</form></td>";
  print "<td align=left width=360><form style=\"margin-bottom:0;\" ".
    "action=\"index.php\">";

  print "<input type=hidden name=load value=billing>";
  print "<input type=hidden name=type value=module>";
  print "<input name=done type=submit value=\" $l_no  \" class=smallbutton>";
  print "</form></td></table>";
 }
?>
