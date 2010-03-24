<SCRIPT LANGUAGE="JavaScript" SRC="include/CalendarPopup.js"></SCRIPT>
   <SCRIPT LANGUAGE="JavaScript">
   var cal = new CalendarPopup();

function cardval(s) 
{
  // remove non-numerics
  var v = "0123456789";
  var w = "";
  for (i=0; i < s.length; i++) 
    {
      x = s.charAt(i);
      if (v.indexOf(x,0) != -1)
	{
	  w += x;
	}
    }
  
  // validate number
  j = w.length / 2;
  if (j < 6.5 || j > 8 || j == 7) 
    {
      return false;
    }
  
  k = Math.floor(j);
  m = Math.ceil(j) - k;
  c = 0;
  for (i=0; i<k; i++) 
    {
      a = w.charAt(i*2+m) * 2;
      c += a > 9 ? Math.floor(a/10 + a%10) : a;
    }
  
  for (i=0; i<k+m; i++) c += w.charAt(i*2+1-m) * 1;
  {
    return (c%10 == 0);
  }
}
</SCRIPT>
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

//$DB->debug = true;

//Includes
require_once('./include/permissions.inc.php');

// GET Variables
if (!isset($base->input['inventory_items_id'])) { $base->input['inventory_items_id'] = ""; }

$inventory_items_id = $base->input['inventory_items_id'];

if ($edit)
{
    if ($pallow_modify)
    {
      // TODO: edit inventory items
    }  else permission_error();
}
else if ($create) // add the message to customer history
{
	if ($pallow_create)
    	{
	  // TODO: assign an inventory item to this customer's services

	} else permission_error();
}

else if ($delete)
{
    if ($pallow_remove)
    {
      // TODO: mark an inventory item as being in returned status with a return date and notes
    } else permission_error();
}

else if ($pallow_view)
{
  // print the column headings
  echo "<table><thead><tr><th>$l_description</th><th>$l_creation_date</th><th>$l_serial_number</th><th>$l_status</th><th>$l_type</th><th>$l_tracking_number</th><th>$l_shipping_date</th><th>$l_return_date</th><th>$l_return_notes</th></thead></tr>";

  // show inventory assigned to this customer's services  
  $query = "SELECT ii.id, mi.description, ii.creation_date, ii.serial_number, ".
    "ii.status, ii.sale_type, ii.shipping_tracking_number, ii.shipping_date, ".
    "ii.return_date, ii.return_notes ".
    "FROM inventory_items ii ".
    "LEFT JOIN master_inventory mi ON mi.id = ii.master_inventory_id ".
    "LEFT JOIN user_services us ON us.id = ii.user_services_id ".
    "WHERE us.account_number = $account_number";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("this $l_queryfailed");
  while($myresult = $result->FetchRow()) {
    $id = $myresult['id'];
    $description = $myresult['description'];
    $creation_date = $myresult['creation_date'];
    $serial_number = $myresult['serial_number'];
    $status = $myresult['status'];
    $sale_type = $myresult['sale_type'];
    $tracking_number = $myresult['shipping_tracking_number'];
    $shipping_date = $myresult['shipping_date'];
    $return_date = $myresult['return_date'];
    $return_notes = $myresult['return_notes'];
    
    // print the listing of devices assigned to this customer
    echo "<td>$description</td><td>$creation_date</td><td>$serial_number</td><td>$status</td><td>$sale_type</td><td>$tracking_number</td><td>$shipping_date</td><td>$return_date</td><td>$return_notes</td><tr>";
  }

  echo "</table>";


} else permission_error();

?>
