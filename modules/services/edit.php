<?php
// Copyright (C) 2002-2008  Paul Yasi (paul at citrusdb.org)
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
if (!isset($base->input['userserviceid'])) {
  $base->input['userserviceid'] = ""; }
if (!isset($base->input['servicedescription'])) {
  $base->input['servicedescription'] = ""; }
if (!isset($base->input['optionstable'])) { $base->input['optionstable'] = ""; }
if (!isset($base->input['editbutton'])) { $base->input['editbutton'] = ""; }
if (!isset($base->input['exempt'])) { $base->input['exempt'] = ""; }
if (!isset($base->input['notexempt'])) { $base->input['notexempt'] = ""; }
if (!isset($base->input['save'])) { $base->input['save'] = ""; }
if (!isset($base->input['taxrate'])) { $base->input['taxrate'] = ""; }
if (!isset($base->input['fieldlist'])) { $base->input['fieldlist'] = ""; }
if (!isset($base->input['usage'])) { $base->input['usage'] = ""; }
if (!isset($base->input['usage_multiple'])) {
  $base->input['usage_multiple'] = ""; }
if (!isset($base->input['billing'])) { $base->input['billing'] = ""; }
if (!isset($base->input['billing_id'])) { $base->input['billing_id'] = ""; }
if (!isset($base->input['servicetype'])) { $base->input['servicetype'] = ""; }
if (!isset($base->input['master_service_id'])) {
  $base->input['master_service_id'] = ""; }
if (!isset($base->input['saveexempt'])) { $base->input['saveexempt'] = ""; }
if (!isset($base->input['customer_tax_id'])) {
  $base->input['customer_tax_id'] = ""; }
if (!isset($base->input['expdate'])) { $base->input['expdate'] = ""; }
if (!isset($fieldvalues)) { $fieldvalues = ""; }

$userserviceid = $base->input['userserviceid'];
$servicedescription = $base->input['servicedescription'];
$optionstable = $base->input['optionstable'];
$editbutton = $base->input['editbutton'];
$save = $base->input['save'];
$fieldlist = $base->input['fieldlist'];
$usage = $base->input['usage'];
$usage_multiple = $base->input['usage_multiple'];
$billing = $base->input['billing'];
$billing_id = $base->input['billing_id'];
$servicetype = $base->input['servicetype'];
$master_service_id = $base->input['master_service_id'];
$exempt = $base->input['exempt'];
$notexempt = $base->input['notexempt'];
$tax_rate_id = $base->input['taxrate'];
$saveexempt = $base->input['saveexempt'];
$customer_tax_id = $base->input['customer_tax_id'];
$expdate = $base->input['expdate'];


if ($save) {
  $fieldlist = substr($fieldlist, 1); 
  // loop through post_vars associative/hash to get field values
  $array_fieldlist = explode(",",$fieldlist);
  foreach ($base->input as $mykey => $myvalue) {
    foreach ($array_fieldlist as $myfield) {
      if ($myfield == $mykey) {
	$fieldvalues .= ', ' . $myfield . ' = \'' . $myvalue . '\'';
      }
    }
  }
  $fieldvalues = substr($fieldvalues, 1);
  $query = "UPDATE $optionstable SET $fieldvalues ".
    "WHERE user_services = $userserviceid";
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  
  print "<script language=\"JavaScript\">window.location.href = ".
    "\"index.php?load=services&type=module\";</script>";

} else if ($usage) {
  // update the database if they changed the usage_multiple
  $query = "UPDATE user_services SET usage_multiple = $usage_multiple ".
    "WHERE id = $userserviceid";
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  print "<script language=\"JavaScript\">window.location.href = ".
    "\"index.php?load=services&type=module\";</script>";

} else if ($billing) {
  // update the database if they changed the billing ID
  $query = "UPDATE user_services SET billing_id = $billing_id ".
    "WHERE id = $userserviceid";
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  print "<script language=\"JavaScript\">window.location.href = ".
    "\"index.php?load=services&type=module\";</script>";

} else if ($servicetype) {
  // change service, update the database if they changed the master_service_id
  // TODO: MAKE A COPY OF THE USER SERVICE UNDER THE NEW MASTER SERVICE ID
  // AND REMOVE THE OLD USER SERVICE WITH NO REMOVAL DATE SO THAT THERE IS
  // A RECORD OF THE OLD SERVICE INFORMATION INDEPENDENT OF THE
  // NEW SERVICE THEY CHANGED TO

  /************* CUT OUT OLD CODE ****************************

  
  $query = "UPDATE user_services SET master_service_id = ".
    "$master_service_id WHERE id = $userserviceid";
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  ************* CUT OUT OLD CODE ****************************/
  
  // TODO: call $new_master_service_id = create_service() to make new one with
  // new type but attributes from old service

  // get the old master service id
  $query = "SELECT master_service_id FROM user_services ".
    "WHERE id = $userserviceid";
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  $oldmasterresult = $result->fields;
  $old_master_service_id = $oldmasterresult['master_service_id'];

  // get the name of the options table, always the same
  $query = "SELECT options_table FROM master_services ".
    "WHERE id = $master_service_id";
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  $master_service_results = $result->fields;
  $options_table_name = $master_service_results['options_table'];

  // get the field names and values from the options_table
  $fields = $DB->MetaColumns($options_table_name);
  foreach($fields as $f) {
    $fieldname = $f->name;
    if ($fieldname <> "id" AND $fieldname <> "user_services") {
      $fieldlist .= ',' . $fieldname;
    }
  }
  $fieldlist = substr($fieldlist, 1);


  // get the values out of those fields from the options table
  $query = "SELECT $fieldlist from $options_table_name ".
    "WHERE user_services = $userserviceid";
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  $options_table_result = $result->fields;

  $array_fieldlist = explode(",",$fieldlist);
  foreach($array_fieldlist as $myfield) {
    $myvalue = $options_table_result["$myfield"];
    $fieldvalues .= ',\'' . $myvalue . '\'';
  }  

  $fieldvalues = substr($fieldvalues, 1);

  
  // TODO: make a new service with the new information from above
  $new_user_service_id = create_service($account_number, $master_service_id,
					$billing_id, $usage_multiple, 
					$options_table_name,
					$fieldlist, $fieldvalues);

  // delete the old service but with no removal date and no delete message
  delete_service($userserviceid, 'change', '');
  
  // add an entry to the customer_history to modify_notify for the new service
  service_message('change', $account_number,
		  $old_master_service_id, $userserviceid, 
		  $master_service_id, $new_user_service_id);

  print "<script language=\"JavaScript\">window.location.href = ".
    "\"index.php?load=services&type=module\";</script>";

} else if ($delete) {
  // prompt them to ask if they are sure they want to delete the service
  print "<br><br>";
  print "<h4>$l_areyousuredelete: $servicedescription</h4>";
  print "<table cellpadding=15 cellspacing=0 border=0 width=720>".
    "<td align=right width=360>";

  // if they hit yes, this will sent them into the delete.php file
  // and remove the service

  print "<form style=\"margin-bottom:0;\" action=\"index.php\">".
    "<input type=hidden name=optionstable value=$optionstable>";
  print "<input type=hidden name=userserviceid value=$userserviceid>";
  print "<input type=hidden name=load value=services>";
  print "<input type=hidden name=type value=module>";
  print "<input type=hidden name=delete value=on>";
  print "<input name=deletenow type=submit value=\" $l_yes \" ".
    "class=smallbutton></form></td>";
  
  // if they hit no, send them back to the service edit screen

  print "<td align=left width=360><form style=\"margin-bottom:0;\" ".
    "action=\"index.php\">";
  print "<input name=done type=submit value=\" $l_no \" class=smallbutton>";
  print "<input type=hidden name=load value=services>";        
  print "<input type=hidden name=type value=module>";
  print "</form></td></table>";
  print "</blockquote>";

} else if ($editbutton) {
  // list the service options after they clicked on the add button.
  print "<a href=\"index.php?load=services&type=module\">".
    "[ $l_undochanges ]</a>";
  
  // get the organization_id and name for this service
  $query = "SELECT ms.organization_id, g.org_name ".
    "FROM user_services us ".
    "LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
    "LEFT JOIN general g ON g.id = ms.organization_id ".
    "WHERE us.id = '$userserviceid'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $orgresult = $DB->Execute($query) or die ("$l_queryfailed");
  $myorgresult = $orgresult->fields;
  $service_org_id = $myorgresult['organization_id'];
  $service_org_name = $myorgresult['org_name']; 
  
  // check for optionstable, skip this step if there isn't one
  if ($optionstable <> '') {
    $query = "SELECT * FROM $optionstable ".
      "WHERE user_services = '$userserviceid'";
    $DB->SetFetchMode(ADODB_FETCH_NUM);
    $result = $DB->Execute($query) or die ("$l_queryfailed");
    $myresult = $result->fields;
  }
	
  // edit the things in the options table
  print "<h4>$l_edit: $servicedescription ($service_org_name)</h4>".
    "<form action=\"index.php\"><table width=720 cellpadding=5 cellspacing=1 ".
    "border=0>";
  print "<input type=hidden name=load value=services>";
  print "<input type=hidden name=type value=module>";
  print "<input type=hidden name=edit value=on>";
  print "<input type=hidden name=servicedescription ".
    "value=\"$servicedescription\"><input type=hidden name=optionstable ".
    "value=$optionstable><input type=hidden name=userserviceid ".
    "value=$userserviceid>";

  // check for optionstable, skip this step if there isn't one	
  if ($optionstable <> '') {
    // list out the fields in the options table for that service
    $fields = $DB->MetaColumns($optionstable);
    $i = 0;
    foreach($fields as $v) {
      //echo "Name: $v->name ";
      //echo "Type: $v->type <br>";
      
      $fieldname = $v->name;
      $fieldflags = $v->type;
      if ($fieldname <> "id" AND $fieldname <> "user_services") {
	if ($fieldflags == "enum") {
	  echo "<td bgcolor=\"ccccdd\"width=180><b>$fieldname</b></td>".
	    "<td bgcolor=\"#ddddee\">";
	  // print all the items listed in the enum
	  enum_select($optionstable, $fieldname, $myresult[$i]);
	  
	  echo "</td><tr>\n";
	} elseif ($fieldflags == "text"
		  OR $fieldflags == "blob"
		  OR $fieldflags == "tinytext"
		  OR $fieldflags == "tinyblob"
		  OR $fieldflags == "mediumblob"
		  OR $fieldflags == "mediumtext") {
	  echo "<td bgcolor=\"ccccdd\"width=180><b>$fieldname</b></td>".
	    "<td bgcolor=\"#ddddee\"><textarea cols=40 rows=6 ".
	    "name=\"$fieldname\">$myresult[$i]</textarea>";
	  echo "</td><tr>";
	} else {
	  echo "<td bgcolor=\"ccccdd\"width=180><b>$fieldname</b></td>".
	    "<td bgcolor=\"#ddddee\"><input type=text name=\"$fieldname\" ".
	    "value=\"$myresult[$i]\">";
			
	  // list any applicable options url links
	  $query = "SELECT * FROM options_urls WHERE fieldname = '$fieldname'";
	  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	  $urlresult = $DB->Execute($query) or die ("$l_queryfailed");
	  $urlmyresult = $urlresult->fields;	
	
	  // assign the query from the search to the query string
	  // replace the s1 thru s5 etc place holders with the actual variables
	  $s1 = $myresult[$i];
	  $myurl = $urlmyresult['url'];
	  $urlname = $urlmyresult['urlname'];
	  $url = str_replace("%s1%", $s1, $myurl);
	  if ($url) {
	    echo "&nbsp;&nbsp; <a href=# ".
	      "onclick=\"popupPage('$url')\">$urlname</a>";
	  }	
	  echo "</td><tr>\n";
	}
	$fieldlist .= ',' . $fieldname;
      }
      $i++;
    }
    print "<input type=hidden name=fieldlist value=$fieldlist>";
    print "<td></td><td><input name=save type=submit ".
      "value=\"$l_savechanges\" class=smallbutton>&nbsp;&nbsp;&nbsp;";
  }

  //
  // check if the service is removed or and not canceled
  // show the undelete button only if an account it not canceled
  $query = "SELECT us.removed, c.cancel_date FROM user_services us ".
    "LEFT JOIN customer c ON c.account_number = us.account_number ".
    "WHERE us.id = $userserviceid";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $removedresult = $DB->Execute($query) or die ("$l_queryfailed");
  $myremovedresult = $removedresult->fields;
  $removed = $myremovedresult['removed'];

  if (!isset($myremovedresult['cancel_date'])) {
    $myremovedresult['cancel_date'] = "";
  }
  $cancel_date = $myremovedresult['cancel_date'];

  if ($removed == 'y' AND $cancel_date == '') {
    // print the undelete button
    print "</form><p><form style=\"margin-bottom:0;\" action=\"index.php\">".
      "<input type=hidden name=optionstable value=$optionstable>";
    print "<input type=hidden name=userserviceid value=$userserviceid>";
    print "<input type=hidden name=load value=services>";
    print "<input type=hidden name=type value=module>";
    print "<input type=hidden name=delete value=on>";
    print "<input name=undeletenow type=submit value=\" $l_undelete \" ".
      "class=smallbutton></form>";
  } else {
    // print the delete button
    print "<p><input name=delete type=submit value=\"$l_deleteservice\" ".
      "class=smallbutton></form>";
  }

  // print the open ticket button
  print "<form style=\"margin-bottom:0;\" action=\"index.php\">";
  print "<input type=hidden name=load value=support>";
  print "<input type=hidden name=type value=module>";
  print "<input type=hidden name=serviceid value=\"$userserviceid\">";
  print "<input name=openticket type=submit value=\"$l_openticket\" ".
    "class=smallbutton></form></td></table><p></blockquote>";     
  
  print "<form action=\"index.php\"><table width=720 cellpadding=5 cellspacing=1 border=0>";
  print "<input type=hidden name=load value=services>";
  print "<input type=hidden name=type value=module>";
  print "<input type=hidden name=edit value=on>";
  print "<input type=hidden name=servicedescription ".
    "value=\"$servicedescription\">";
  print "<input type=hidden name=optionstable value=$optionstable>";
  print "<input type=hidden name=userserviceid value=$userserviceid>";
        

  $query = "SELECT * FROM user_services u ".
    "LEFT JOIN master_services m ON u.master_service_id = m.id ".
    "WHERE u.id = '$userserviceid'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  $myresult = $result->fields;
  $usage_multiple = $myresult['usage_multiple'];
  $usage_label = $myresult['usage_label'];
  $account_number = $myresult['account_number'];

  // print the usage_multiple entry field
  // if there is a usage label, use that instead of the generic name
  if($usage_label) {
    print "<tr><td bgcolor=\"#ccccdd\" width=180><b>$usage_label</b></td>";
  } else {
    print "<tr><td bgcolor=\"#ccccdd\" width=180><b>$l_usagemultiple</b></td>";
  }
  echo "<td bgcolor=\"#ddddee\"><input type=text name=usage_multiple ".
    "value=$usage_multiple></td><tr>\n";
  print "<td></td><td><input name=usage type=submit value=\"$l_change\" ".
    "class=smallbutton></td></table></form>";

  // change the billing ID
	
  print "<form action=\"index.php\"><table width=720 cellpadding=5 ".
    "cellspacing=1 border=0>";
  print "<input type=hidden name=load value=services>";
  print "<input type=hidden name=type value=module>";
  print "<input type=hidden name=edit value=on>";
  print "<input type=hidden name=servicedescription ".
    "value=\"$servicedescription\">";
  print "<input type=hidden name=optionstable value=$optionstable>";
  print "<input type=hidden name=userserviceid value=$userserviceid>";
  echo "<td bgcolor=\"ccccdd\"width=180><b>$l_billingid</b></td>".
    "<td bgcolor=\"#ddddee\">";

  $query = "SELECT * FROM user_services WHERE id = '$userserviceid'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  $myresult = $result->fields;
  $billing_id = $myresult['billing_id'];
  $account_number = $myresult['account_number'];
  
  // print a list of billing id's that have the account number associated with
  // them and match the organization_id for this service
        
  print "<select name=billing_id><option selected value=$billing_id>".
    "$billing_id</option>";
	
  $query = "SELECT b.id,bt.name,g.org_name FROM billing b ".
    "LEFT JOIN general g ON g.id = b.organization_id ".
    "LEFT JOIN billing_types bt ON bt.id = b.billing_type ".
    "WHERE b.account_number = '$account_number' AND ".
    "g.id = '$service_org_id'";

  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  while ($myresult = $result->FetchRow()) {
    $billing_id = $myresult['id'];
    $org_name = $myresult['org_name'];
    $billing_type = $myresult['name'];
    print "<option value=$billing_id>$billing_id ($org_name) $billing_type".
      "</option>";
  }

  echo "</select></td><tr>\n";
	
  print "<td></td><td><input name=billing type=submit value=\"$l_change\" ".
    "class=smallbutton></td></table></form>";

  // print the change service type change function only if there is an
  // options table to keep the same attributes with a service
  
  if ($optionstable) {

    print "<form action=\"index.php\"><table width=720 cellpadding=5 ".
      "cellspacing=1 border=0>\n";
    print "<input type=hidden name=load value=services>\n";
    print "<input type=hidden name=type value=module>\n";
    print "<input type=hidden name=edit value=on>\n";
    print "<input type=hidden name=servicedescription ".
      "value=\"$servicedescription\">\n";
    print "<input type=hidden name=optionstable value=$optionstable>\n";
    print "<input type=hidden name=userserviceid value=$userserviceid>\n";
    print "<input type=hidden name=billing_id value=$billing_id>\n";
    print "<input type=hidden name=usage_multiple value=$usage_multiple>\n";
    echo "<td bgcolor=\"ccccdd\"width=180><b>$l_changeservice</b></td>".
      "<td bgcolor=\"#ddddee\">\n";

    $query = "SELECT * FROM user_services us ".
      "LEFT JOIN master_services ms ON ms.id = us.master_service_id ".
      "WHERE us.id = $userserviceid";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("$l_queryfailed");
    $myresult = $result->fields;
    $master_service_id = $myresult['master_service_id'];
    $service_description = $myresult['service_description'];

    // print a list of services that share the same attributes
        
    print "<select name=master_service_id><option selected ".
      "value=$master_service_id>$service_description</option>\n";
	
    $query = "SELECT * FROM master_services ".
      "WHERE options_table = '$optionstable'";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("$l_queryfailed");
    while ($myresult = $result->FetchRow()) {
      $master_service_id = $myresult['id'];
      $service_description = $myresult['service_description'];
      print "<option value=$master_service_id>$service_description</option>\n";
    }

    echo "</select></td><tr>\n";
    
    print "<td></td><td><input name=servicetype type=submit ".
      "value=\"$l_change\" class=smallbutton></td></table></form>";
  } // end if options_table

} else if ($exempt) {
  // ask the user for customer tax id, and exempt id expiration date
  print "<a href=\"index.php?load=services&type=module\">[ $l_undochanges ]</a>";
  print "<h4>$l_exempt</h4><form action=\"index.php\">".
    "<table width=720 cellpadding=5 cellspacing=1 border=0>";
  print "<input type=hidden name=load value=services>";
  print "<input type=hidden name=type value=module>";
  print "<input type=hidden name=edit value=on>";
  print "<input type=hidden name=taxrate value=\"$tax_rate_id\">";
  echo "<td bgcolor=\"ccccdd\"width=180><b>$l_taxexemptid</b></td>".
    "<td bgcolor=\"#ddddee\"><input type=text name=customer_tax_id></td><tr>\n";
  echo "<td bgcolor=\"ccccdd\"width=180><b>$l_expirationdate</b></td>".
    "<td bgcolor=\"#ddddee\"><input type=text name=expdate></td><tr>\n";
  print "<td></td><td><input name=saveexempt type=submit ".
    "value=\"$l_savechanges\" class=smallbutton></td></table></form>";
	
} else if ($saveexempt) { 
  // save the tax exempt status information, make the customer tax exempt
  $query = "INSERT INTO tax_exempt ".
    "(account_number, tax_rate_id, customer_tax_id, expdate) ". 
    "VALUES ('$account_number', '$tax_rate_id','$customer_tax_id','$expdate')";
  $result = $DB->Execute($query) or die ("$l_queryfailed");

  // redirect back to the service index
  print "<script language=\"JavaScript\">window.location.href = ".
    "\"index.php?load=services&type=module\";</script>";

} else if ($notexempt) {
  // make the customer tax not-exempt
  $query = "DELETE FROM tax_exempt WHERE tax_rate_id = '$tax_rate_id' ".
    "AND account_number = '$account_number'";
  $result = $DB->Execute($query) or die ("$l_queryfailed");
	
  // redirect back to the service index
  print "<script language=\"JavaScript\">window.location.href = ".
    "\"index.php?load=services&type=module\";</script>";
}
?>
