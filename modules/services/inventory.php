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
if (!isset($base->input['master_inventory_id'])) { $base->input['master_inventory_id'] = ""; }

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
$return = $base->input['return_date'];
$return = $base->input['return_notes'];
$item_id = $base->input['item_id'];
$master_inventory_id = $base->input['master_inventory_id'];

if ($ship) {
  echo "<h3>$l_shipinventory</h3>";
  
  // get the count of items in new inventory
  $query = "SELECT count(*) AS newcount, mi.description FROM inventory_items ii ".
    "LEFT JOIN master_inventory mi ON mi.id = ii.master_inventory_id ".
    "WHERE ii.status = 'new' AND ii.master_inventory_id = '$master_inventory_id' GROUP BY mi.id";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  $myresult = $result->fields;
  $newcount = $myresult['newcount'];
  $description = $myresult['description'];
  
  // get the count of items in used inventory
  $query = "SELECT count(*) AS usedcount, mi.description FROM inventory_items ii ".
    "LEFT JOIN master_inventory mi ON mi.id = ii.master_inventory_id ".
    "WHERE ii.status = 'used' AND ii.master_inventory_id = '$master_inventory_id' GROUP BY mi.id";  
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  $myresult = $result->fields;
  $usedcount = $myresult['usedcount'];
  
  // put zero into count if nothing returned
  if ($newcount < 1) { $newcount = 0; }
  if ($usedcount < 1) { $usedcount = 0; } 

  echo "<p><b>$description: $newcount $l_new, $usedcount $l_used</b></p>";

  // TODO: print a form that asks for info about what they want to ship

  print "<form style=\"margin-bottom:0;\" action=\"index.php\">".
    "<table width=720 cellpadding=5 cellspacing=1 border=0>";
  print "<input type=hidden name=userserviceid value=$userserviceid>";
  print "<input type=hidden name=load value=services>";
  print "<input type=hidden name=type value=module>";
  print "<input type=hidden name=assign value=on>";
  print "<input type=hidden name=inventory value=on>";
  print "<input type=hidden name=master_inventory_id value=\"$master_inventory_id\">";

  //from new or used inventory
  echo "<table>";
  echo "<td>$l_choosefrom:</td><td><label><input type=radio name=neworused value=new>New</label>";
  echo "<label><input type=radio name=neworused value=used>Used</label></td><tr>";

  //serial_number
  echo "<td><label>$l_serialnumber: </td><td><input type=text name=serial_number></label></td><tr>";  

  //sale type
  echo "<td>$l_saletype: </td><td><select name=sale_type>".
    "<option value=\"\">$l_choose</option>".
    "<option value=included>included</option>".
    "<option value=purchase>purchase</option>".
    "<option value=rent>rent</option>".
    "</select></td><tr>";    

  //shipping tracking number
  echo "<td><label>$l_trackingnumber: </td><td><input type=text name=tracking_number></label></td><tr>";  

  //shipping date  (TODO: pre-enter today's date in here )
  $mydate = date("Y-m-d");
  echo "<td><label>$l_shippingdate: </td><td><input type=text name=shipping_date value=\"$mydate\"></label></td><tr>";  

  // print submit button
  print "<td></td><td><input name=inventory type=submit value=\"$l_assign\" ".
    "class=smallbutton></td></table></form><p>";
  
 } else if ($return) {

  // TODO: ask for return date and return notes
  

  // TODO: put this inventory item into a return status

  // TODO: make an new entry in inventory to put this item in under a used status
  
 } else if ($assign) {

  // assign the item from inventory to this service
  /*
   $neworused = $base->input['neworused'];
   $serial_number = $base->input['serial_number'];
   $sale_type = $base->input['sale_type'];
   $tracking_number = $base->input['tracking_number'];
   $shipping_date = $base->input['shipping_date'];
  */

  $query = "UPDATE inventory_items SET ".
    "serial_number = '$serial_number', ".
    "status = 'infield', ".
    "sale_type = '$sale_type', ".
    "shipping_tracking_number = '$tracking_number', ".
    "shipping_date = '$shipping_date', ".
    "user_services_id = '$userserviceid' ".
    "WHERE master_inventory_id = '$master_inventory_id' AND status = '$neworused' LIMIT 1";
  $result = $DB->Execute($query) or die ("$query $l_queryfailed");
  
  // redirect back to the service edit screen, now showing the assigned inventory listed there
  echo "assigned";
  print "<script language=\"JavaScript\">window.location.href = ".
    "\"index.php?load=services&type=module\";</script>";

 }




?>

