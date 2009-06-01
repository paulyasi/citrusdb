<?php   
// Includes Code Contributed by Rich Cloutier (RTC)
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

if (!isset($base->input['addnow'])) { $base->input['addnow'] = ""; }
if (!isset($base->input['addbutton'])) { $base->input['addbutton'] = ""; }
if (!isset($base->input['serviceid'])) { $base->input['serviceid'] = ""; }
if (!isset($base->input['options_table_name'])) {
  $base->input['options_table_name'] = ""; }
if (!isset($base->input['fieldlist'])) { $base->input['fieldlist'] = ""; }
if (!isset($fieldvalues)) { $fieldvalues = ""; }
if (!isset($base->input['usagemultiple'])) {
  $base->input['usagemultiple'] = ""; }
if (!isset($base->input['showall'])) { $base->input['showall'] = ""; }
if (!isset($base->input['billing_id'])) { $base->input['billing_id'] = ""; }
if (!isset($base->input['create_billing'])) {
  $base->input['create_billing'] = ""; }


// GET Variables
$addnow = $base->input['addnow'];
$addbutton = $base->input['addbutton'];
$serviceid = $base->input['serviceid'];
$usagemultiple = $base->input['usagemultiple'];
$options_table_name = $base->input['options_table_name'];
$fieldlist = $base->input['fieldlist'];
$showall = $base->input['showall'];
$billing_id = $base->input['billing_id'];
$create_billing = $base->input['create_billing'];

//$DB->debug = true;

if ($addnow) {
  // add the services to the user_services table and the options table
  $fieldlist = substr($fieldlist, 1); 

  // loop through post_vars associative/hash to get field values
  $array_fieldlist = explode(",",$fieldlist);
  
  foreach ($base->input as $mykey => $myvalue) {
    foreach ($array_fieldlist as $myfield) {
      // print "$mykey<br>";
      if ($myfield == $mykey) {
	$fieldvalues .= ',\'' . $myvalue . '\'';
      }
    }
  }

  $fieldvalues = substr($fieldvalues, 1);

  // make the creation date YYYY-MM-DD HOUR:MIN:SEC
  $mydate = date("Y-m-d H:i:s");

  // if there is a create_billing request, create a billing record first
  if ($create_billing) {
    $billing_id = create_billing_record($create_billing, $account_number, $DB);
  }

  $user_service_id = create_service($account_number, $serviceid, $billing_id,
				    $usagemultiple, $options_table_name,
				    $fieldlist, $fieldvalues);

  
  // insert any linked_services into the user_services table
  $query = "SELECT * FROM linked_services WHERE linkfrom = $serviceid";
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  while ($myresult = $result->FetchRow()) {
    $linkto = $myresult['linkto'];

    create_service($account_number, $linkto, $billing_id,
		   $usagemultiple, NULL, NULL, NULL);
  }	
	
  // add an entry to the customer_history to the activate_notify user
  service_message('added', $account_number, $serviceid,
		  $user_service_id, NULL, NULL);

  print "$l_addedservice<p>";
  print "<script language=\"JavaScript\">window.location.href = ".
    "\"index.php?load=services&type=module\";</script>";
} else if ($addbutton) {
  // list the service options after they clicked on the add button.
  echo "<a href=\"index.php?load=services&type=module\">[ $l_undochanges ]</a>";
  $query = "SELECT * FROM master_services ms ". 
    "LEFT JOIN general g ON g.id = ms.organization_id ". 
    "WHERE ms.id = $serviceid";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  $myresult = $result->fields;	
  $servicename = $myresult['service_description'];
  $options_table_name = $myresult['options_table'];
  $usage_label = $myresult['usage_label'];
  $service_org_id = $myresult['organization_id'];
  $service_org_name = $myresult['org_name'];
	
  echo "<script language=javascript>".
    "function popupURL(url,value) { ".
    "newurl = \"url + value\";".
    "window.open(\"newurl\");".
    "}".
    "</script>";	
	
  print "<h4>$l_addingservice: $servicename ($service_org_name)</h4>".
    "<form action=\"index.php\" name=\"AddService\">".
    "<table width=720 cellpadding=5 cellspacing=1 border=0>\n";
  print "<input type=hidden name=load value=services>\n";
  print "<input type=hidden name=type value=module>\n";
  print "<input type=hidden name=create value=on>\n";
  print "<input type=hidden name=options_table_name value=$options_table_name>".
    "<input type=hidden name=serviceid value=$serviceid>";
	
  // check that there is an options_table_name, if so, show the options choices

  if ($options_table_name <> '') {
    // ADODB MetaColumns function gives a field object for table columns
    $fields = $DB->MetaColumns($options_table_name);
    $i = 0;
    foreach($fields as $v) {
      //echo "Name: $v->name ";
      //echo "Type: $v->type ";
        
      $fieldname = $v->name;
      $fieldflags = $v->type;

      // Added the default value from the database schema
      // so we can use it when needed - by RTC
      $default_value = $v->default_value;

      //echo "Default: $default_value<br>";
      
      if ($fieldname <> "id" AND $fieldname <> "user_services") {
	if ($fieldflags == "enum") {
	  echo "<td bgcolor=\"ccccdd\"width=180><b>$fieldname</b></td>".
	    "<td bgcolor=\"#ddddee\">";

	  // print all the items listed in the enum
	  enum_select($options_table_name, $fieldname, $default_value);

	  echo "</select></td><tr>\n";

	} else {
	  echo "<td bgcolor=\"ccccdd\"width=180>".
	    "<b>$fieldname</b></td>".
	    "<td bgcolor=\"#ddddee\">".
	    "<input type=text name=$fieldname id=\"$fieldname\" ".
	    "value=\"$default_value\">\n";
	  echo "</td><tr>\n";
	}
	$fieldlist .= ',' . $fieldname;
      }
      $i++;
    } //endforeach
	
    print "<input type=hidden name=fieldlist value=$fieldlist>";
  } //endwhile

  // print the usage_multiple entry field
  // if there is a usage label, use it instead of the generic name
  if($usage_label) {
    print "<tr><td bgcolor=\"#ccccdd\"><b>$usage_label</b></td>";
  } else {
    print "<tr><td bgcolor=\"#ccccdd\"><b>$l_usagemultiple</b></td>";
  }
	
  print"<td bgcolor=\"#ddddee\"><input type=text name=\"usagemultiple\" ".
    "value=\"1\"></td><tr>";

  // print the billing id choices available to this service type
  // if no billing id choices match, then ask them to create a billing
  // record for this service with a matching billing org

  print "<td bgcolor=\"#ddaaee\"><b>$l_organizationname</b></td>".
    "<td bgcolor=\"#ddaaee\">";
	
  $query = "SELECT b.id,bt.name,g.org_name FROM billing b ".
    "LEFT JOIN general g ON g.id = b.organization_id ".
    "LEFT JOIN billing_types bt ON b.billing_type = bt.id  ".
    "WHERE b.account_number = '$account_number' AND ".
    "g.id = '$service_org_id'";

  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  if (!$result || $result->RowCount() < 1){
    echo "<b>$l_willcreatebillingrecord $service_org_name</b>".
      "<input type=hidden name=create_billing value=$service_org_id>";	
  } else {
    echo "<select name=billing_id>";
    while ($myresult = $result->FetchRow()) {
      $billing_id = $myresult['id'];
      $org_name = $myresult['org_name'];
      $billing_type = $myresult['name'];
      print "<option value=$billing_id>$billing_id ($org_name) $billing_type</option>";
    }
  }
  echo "</select></td><tr>";

  print "<td></td><td><input name=addnow type=submit value=\"$l_add\" ".
    "class=smallbutton></td></table></form>";
} else {
  /*-------------------------------------------------------------------*/
  // print the list of services that may be added to the account
  /*-------------------------------------------------------------------*/
  echo "<a href=\"index.php?load=services&type=module\">[ $l_undochanges ]</a>";

  // if the user is admin or manager then have the option to show all services
  // query user properties
  $query = "SELECT * FROM user WHERE username='$user'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $userresult = $DB->Execute($query) or die ("$l_queryfailed");
  $myuserresult = $userresult->fields;
  $showall_permission = false;
  if (($myuserresult['manager'] == 'y') OR ($myuserresult['admin'] == 'y')) {
    $showall_permission = true;
    echo "<a href=\"index.php?load=services&type=module&create=on&showall=y\">".
      "[ $l_showall ]</a>";
  }
	
  // print drop down menu to choose service categories
  echo "<SCRIPT LANGUAGE=\"JavaScript\">".
	"<!-- Begin".
	"function dropdownHandler(dropdown) {".
    "var URL = dropdown.dropcategory.options[dropdown.dropcategory.".
    "selectedIndex].value;".
    "window.location.href = URL;".
    "}".
    "// End -->".
    "</script>";
	
  echo "<center><form name=dropdown><select name=dropcategory size=1 ".
    "onchange=\"dropdownHandler(this.form)\">";
  echo "<option value=\"#\">$l_selectcategory</option>\n";
	
  $query = "SELECT DISTINCT category FROM master_services ORDER BY category ";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  while ($myresult = $result->FetchRow()) {
    $categoryname = $myresult['category'];
    echo "<option value=\"#$categoryname\">$categoryname</option>\n";
  }
	
  echo "</select></form></center>";

  // TODO: select service listing based upon the customer's
  // default billing organization

  $query = "SELECT organization_id FROM billing ".
    "WHERE account_number = '$account_number' LIMIT 1";
  $DB->SetFetchMode(ADODB_FETCH_NUM);
  $orgresult = $DB->Execute($query) or die ("$l_queryfailed");
  $myorgresult = $orgresult->fields;
  $my_organization_id = $myorgresult[0];
  
  if ($showall == 'y' & $showall_permission == true) {
    $query = "SELECT * FROM master_services m ".
      "LEFT JOIN general g ON g.id = m.organization_id ".
      "WHERE selling_active = 'y' ".
      "ORDER BY category, pricerate, service_description";
  } else {
    $query = "SELECT * FROM master_services m ".
      "LEFT JOIN general g ON g.id = m.organization_id ".
      "WHERE selling_active = 'y' AND hide_online <> 'y' ".
      "AND organization_id = '$my_organization_id' ".
      "ORDER BY category, pricerate, service_description";
  }
  $DB->SetFetchMode(ADODB_FETCH_NUM);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  // Print HTML table of servicesresults

  echo '<table border=0 cellspacing=1 cellpadding=5 width=720>';
  echo "<tr><td bgcolor=\"#ccccdd\"><b>$l_id</b></td>".
    "<td bgcolor=\"#ccccdd\"><b>$l_description</b></td>".
    "<td bgcolor=\"#ccccdd\"><b>$l_rate</b></td>".
    "<td bgcolor=\"#ccccdd\"><b>$l_frequency</b></td>".
    "<td bgcolor=\"#ccccdd\"><b>$l_organizationname</b></td>".
    "<td bgcolor=\"#ccccdd\">&nbsp;</td></tr>";
	
  $previouscategory = "";
  
  while ($myrow = $result->FetchRow()) {
    $category = $myrow[5];
    // print category heading
    if ($category <> $previouscategory) {
      echo "<tr bgcolor=\"#ccccdd\"><td colspan=6><b>$l_category: $category".
	"</b><a name=\"$category\"></td></tr>\n";
    }
    $previouscategory = $category;
		
    // print service listing
    printf("<tr onmouseover='h(this);' onmouseout='deh(this);' onmousedown='window.location.href=\"index.php?load=services&type=module&create=on&serviceid=$myrow[0]&addbutton=Add\";' bgcolor=\"#ddddee\"><td>%s</td><td>%s</td><td>$%s</td><td>%s</td><td>%s</td><td align=center><form  style=\"margin-bottom:0;\" action=\"index.php\"><input type=hidden name=load value=services><input type=hidden name=type value=module><input type=hidden name=create value=on><input type=hidden name=serviceid value=$myrow[0]><input name=addbutton type=submit value=\"$l_add\" class=smallbutton></form></td></tr>\n", $myrow[0], $myrow[1], $myrow[2], $myrow[3], $myrow[13]); 

    // print the list of linked services under the service they are linked to      
    $query = "SELECT mfrom.id mfrom_id, ".
      "mfrom.service_description mfrom_description, ".
      "mto.id mto_id, mto.service_description mto_description, ".
      "mto.pricerate mto_pricerate, l.linkfrom, l.linkto ".
      "FROM linked_services l ".
      "LEFT JOIN master_services mfrom ON mfrom.id = l.linkfrom ".
      "LEFT JOIN master_services mto ON mto.id = l.linkto ".
      "WHERE l.linkfrom = $myrow[0]";

    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $lresult = $DB->Execute($query) or die ("$l_queryfailed");

    while ($lmyresult = $lresult->FetchRow()) {
      $todesc = $lmyresult['mto_description'];
      $toprice = $lmyresult['mto_pricerate'];
      print "<tr bgcolor=\"#ffffff\" cellpadding=0 cellspacing=0><td></td><td style=\"font-size: 10px\"; colspan=5 bgcolor=\"#eeeeff\"> + $todesc $l_currency$toprice</td></tr>\n";
    }
  } 
  echo "</table>\n";
  
}

?>
