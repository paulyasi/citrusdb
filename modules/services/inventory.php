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


if (!isset($base->input['ship'])) { $base->input['ship'] = ""; }
if (!isset($base->input['assign'])) { $base->input['assign'] = ""; }  
if (!isset($base->input['return'])) { $base->input['return'] = ""; }
if (!isset($base->input['master_inventory_id'])) { $base->input['master_inventory_id'] = ""; }

// GET Variables
$ship = $base->input['ship'];
$assign = $base->input['assign'];
$return = $base->input['return'];
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

  echo "<p>$description, $l_newinventory: $newcount, $l_usedinventory: $usedcount</p>";

  // TODO: print a form that asks for info about what they want to ship

  print "<form style=\"margin-bottom:0;\" action=\"index.php\">".
    "<table width=720 cellpadding=5 cellspacing=1 border=0>".
    "<input type=hidden name=optionstable value=$optionstable>";
  print "<input type=hidden name=userserviceid value=$userserviceid>";
  print "<input type=hidden name=load value=services>";
  print "<input type=hidden name=type value=module>";
  print "<input type=hidden name=assign value=on>";
  print "<input type=hidden name=inventory value=on>";

  //from new or used inventory
  echo "<table>";
  echo "<td>Choose From:</td><td><label><input type=radio name=neworused value=new>New</label>";
  echo "<label><input type=radio name=neworused value=used>Used</label></td><tr>";

  //serial_number
  echo "<td><label>Serial Number: </td><td><input type=text name=serial_number></label></td><tr>";  

  //sale type
  echo "<td>Sale Type: </td><td><select name=sale_type>".
    "<option value=\"\">$l_choose</option>".
    "<option value=included>included</option>".
    "<option value=purchase>purchase</option>".
    "<option value=rent>rent</option>".
    "</select></td><tr>";    

  //shipping tracking number
  echo "<td><label>Tracking Number: </td><td><input type=text name=tracking_number></label></td><tr>";  

  //shipping date  
  echo "<td><label>Shipping Date: </td><td><input type=text name=shipping_date></label></td><tr>";  

  // print submit button
  print "<td></td><td><input name=inventory type=submit value=\"$l_assign\" ".
    "class=smallbutton></td></table></form><p>";
  
 } else if ($return) {

  // TODO: put this inventory into a return status
  

  // TODO: make an new entry in inventory to put this item in under a used status
  
 } else if ($assign) {

  // TODO: assign the item from inventory to this service 

 }




?>

