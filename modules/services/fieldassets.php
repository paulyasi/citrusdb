<?php   
// Copyright (C) 2010  Paul Yasi (paul at citrusdb.org)
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



if (!isset($base->input['userserviceid'])) { $base->input['userserviceid'] = ""; }
if (!isset($base->input['neworused'])) { $base->input['neworused'] = ""; }
if (!isset($base->input['serial_number'])) { $base->input['serial_number'] = ""; }
if (!isset($base->input['sale_type'])) { $base->input['sale_type'] = ""; }
if (!isset($base->input['tracking_number'])) { $base->input['tracking_number'] = ""; }
if (!isset($base->input['shipping_date'])) { $base->input['shipping_date'] = ""; }
if (!isset($base->input['return_date'])) { $base->input['return_date'] = ""; }
if (!isset($base->input['return_notes'])) { $base->input['return_notes'] = ""; }
if (!isset($base->input['assign'])) { $base->input['assign'] = ""; }  
if (!isset($base->input['ship'])) { $base->input['ship'] = ""; }
if (!isset($base->input['return'])) { $base->input['return'] = ""; }
if (!isset($base->input['item_id'])) { $base->input['item_id'] = ""; }
if (!isset($base->input['master_field_assets_id'])) { $base->input['master_field_assets_id'] = ""; }

// GET Variables
$userserviceid = $base->input['userserviceid'];
$neworused = $base->input['neworused'];
$serial_number = $base->input['serial_number'];
$sale_type = $base->input['sale_type'];
$tracking_number = $base->input['tracking_number'];
$shipping_date = $base->input['shipping_date'];
$assign = $base->input['assign'];
$ship = $base->input['ship'];
$return = $base->input['return'];
$returned = $base->input['returned'];
$return_date = $base->input['return_date'];
$return_notes = $base->input['return_notes'];
$item_id = $base->input['item_id'];
$master_field_assets_id = $base->input['master_field_assets_id'];

if ($ship) {
  echo "<h3>$l_shipfieldassets</h3>";

  // get the name of the item being assigned from master_field_assets
  $query = "SELECT description FROM master_field_assets ".
    "WHERE id = '$master_field_assets_id'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$query $l_queryfailed");
  $myresult = $result->fields;
  $description = $myresult['description'];
  
  echo "<p><b>$description:</b></p>";

  // print a form that asks for info about what the asset they are assigning

  print "<form style=\"margin-bottom:0;\" action=\"index.php\">".
    "<table width=720 cellpadding=5 cellspacing=1 border=0>";
  print "<input type=hidden name=userserviceid value=$userserviceid>";
  print "<input type=hidden name=load value=services>";
  print "<input type=hidden name=type value=module>";
  print "<input type=hidden name=assign value=on>";
  print "<input type=hidden name=inventory value=on>";
  print "<input type=hidden name=master_field_assets_id value=\"$master_field_assets_id\">";

  //from new or used inventory
  echo "<table>";

  //serial_number
  echo "<td><label>$l_serialnumber: </td><td><input type=text name=serial_number></label></td><tr>";  

  //sale type
  echo "<td>$l_saletype: </td><td><select name=sale_type>".
    "<option value=included>included</option>".
    "<option value=purchase>purchase</option>".
    "<option value=rent>rent</option>".
    "</select></td><tr>";    

  //shipping tracking number
  echo "<td><label>$l_trackingnumber: </td><td><input type=text name=tracking_number></label></td><tr>";  

  //shipping date
  $mydate = date("Y-m-d");
  echo "<td><label>$l_shippingdate: </td><td><input type=text name=shipping_date value=\"$mydate\"></label></td><tr>";  

  // print submit button
  print "<td></td><td><input name=fieldassets type=submit value=\"$l_assign\" ".
    "class=smallbutton></td></table></form><p>";
  
 } else if ($return) {

  // ask for return date and return notes and send to returned  
  print "<form style=\"margin-bottom:0;\" action=\"index.php\">".
    "<table width=720 cellpadding=5 cellspacing=1 border=0>";
  print "<input type=hidden name=userserviceid value=$userserviceid>";
  print "<input type=hidden name=load value=services>";
  print "<input type=hidden name=type value=module>";
  print "<input type=hidden name=returned value=on>";
  print "<input type=hidden name=fieldassets value=on>";
  print "<input type=hidden name=item_id value=\"$item_id\">";

  echo "<table>";

  //return_date
  $mydate = date("Y-m-d");
  echo "<td><label>$l_returndate: </td><td><input type=text name=return_date value=\"$mydate\"></label></td><tr>";  

  //return_notes
  echo "<td><label>$l_returnnotes: </td><td><input type=text name=return_notes></label></td><tr>";  

  // print submit button
  print "<td></td><td><input name=fieldassets type=submit value=\"$l_returndevice\" ".
    "class=smallbutton></td></table></form><p>";

 } else if ($returned) {
  // put this inventory item into a return status  
  $query = "UPDATE field_asset_items SET ".
    "status = 'returned', ".
    "return_date = '$return_date', ".
    "return_notes = '$return_notes' ".    
    "WHERE id = '$item_id' LIMIT 1";
  $result = $DB->Execute($query) or die ("$query $l_queryfailed");

  // get the name of the item being updated from master_field_assets
  $query = "SELECT ma.description FROM field_asset_items fa ".
    "LEFT JOIN master_field_assets ma ON ma.id = fa.master_field_assets_id ".
    "WHERE fa.id = '$item_id'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$query $l_queryfailed");
  $myresult = $result->fields;
  $description = $myresult['description'];

  // get the default billing group
  $query = "SELECT default_billing_group FROM settings WHERE id = '1'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$query $l_queryfailed");
  $myresult = $result->fields;
  $default_billing_group = $myresult['default_billing_group'];  
  
  // leave a note to the billing group that the item was returned
  $status = "not done";
  $description = "$l_returned $description $return_date $return_notes";
  create_ticket($DB, $user, $default_billing_group, $account_number, $status, $description, NULL, NULL, NULL, $userserviceid);
  

  // redirect back to the service edit screen, now showing the returned inventory listed there
  echo "returned";
  print "<script language=\"JavaScript\">window.location.href = ".
    "\"index.php?load=services&type=module\";</script>";  
  
 } else if ($assign) {

  // assign the field_asset to this service
  /*
   $serial_number = $base->input['serial_number'];
   $sale_type = $base->input['sale_type'];
   $tracking_number = $base->input['tracking_number'];
   $shipping_date = $base->input['shipping_date'];
  */

  $query = "INSERT into field_asset_items ".
    "(master_field_assets_id, creation_date, serial_number, status, sale_type, shipping_tracking_number, shipping_date, user_services_id ) VALUES ".
    "('$master_field_assets_id', CURRENT_DATE, '$serial_number', 'infield', '$sale_type', '$tracking_number', '$shipping_date', '$userserviceid')";
  $result = $DB->Execute($query) or die ("$query $l_queryfailed");

  // get the name of the item being assigned from master_field_assets
  $query = "SELECT description FROM master_field_assets ".
    "WHERE id = '$master_field_assets_id'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$query $l_queryfailed");
  $myresult = $result->fields;
  $description = $myresult['description'];

  // get the default shipping group
  $query = "SELECT default_shipping_group FROM settings WHERE id = '1'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$query $l_queryfailed");
  $myresult = $result->fields;
  $default_shipping_group = $myresult['default_shipping_group'];  
  
  // leave a note that the item was assigned
  $status = "not done";
  $description = "$l_shipped $description, $l_trackingnumber: $tracking_number";
  create_ticket($DB, $user, $default_shipping_group, $account_number, $status, $description, NULL, NULL, NULL, $userserviceid);
  
  // redirect back to the service edit screen, now showing the assigned inventory listed there
  echo "assigned";
  print "<script language=\"JavaScript\">window.location.href = ".
    "\"index.php?load=services&type=module\";</script>";

 }




?>

