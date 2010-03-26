<?php   
// Copyright (C) 2010  Paul Yasi (paul@citrusdb.org)
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
if (!isset($base->input['return'])) { $base->input['return'] = ""; }
if (!isset($base->input['master_inventory_id'])) { $base->input['master_inventory_id'] = ""; }

// GET Variables
$ship = $base->input['ship'];
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

  echo "<p>$description, $l_newinventory: $newcount, $l_usedinventory: $usedcount</p>";

  // TODO: print a form that asks for info about what they want to ship

  /*
   serial_number
   from new or used inventory
   sale type
   shipping tracking number
   shipping date
  */ 

  
 } else if ($return) {

  // TODO: put this inventory into a return status
  

  // TODO: make an new entry in inventory to put this item in under a used status
  
 }



?>

