<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// show the billing record info
 // print a list of alternate billing id's if any
 $query = "SELECT b.id b_id, g.org_name g_org_name, t.name t_name ".
   "FROM billing b ".
   "LEFT JOIN billing_types t ON b.billing_type = t.id ".
   "LEFT JOIN general g ON b.organization_id = g.id ".
   "WHERE b.account_number = $account_number";
 $DB->SetFetchMode(ADODB_FETCH_ASSOC);
 $result = $DB->Execute($query) or die ("$l_queryfailed");
 
 echo "<br><table width=720 cellpadding=3><tr bgcolor=\"#dddddd\">".
   "<td><b>$l_organizationname</b></td><td><b>$l_type</b></td>".
   "<td><b>$l_status</b></td><td><b>$l_newcharges</b></td>".
   "<td><b>$l_tax</b></td><td><b>$l_pastcharges</b></td>".
   "<td><b>$l_total</b></td><tr>";

 while ($myresult = $result->FetchRow()) {
  $billing_id = $myresult['b_id'];
  $billing_type = $myresult['t_name'];
  $billing_orgname = $myresult['g_org_name'];

  // check if billing type has active services
  $query = "SELECT billing_id FROM user_services ".
    "WHERE removed = 'n' AND billing_id = $billing_id LIMIT 1";
  $usresult = $DB->Execute($query) or die ("user service $l_queryfailed");
  $myusresult = $usresult->fields;
  $not_removed_id = $myusresult['billing_id'];

  $mystatus = billingstatus($billing_id);


  // show active billing services that are authorized new or free in green
  // show active billing services not in good standing in red
  if ($billing_id == $not_removed_id) {    
    if (($mystatus == $l_authorized)
	OR ($mystatus == $l_new)
	OR ($mystatus == $l_free)
	OR ($mystatus == $l_pastdueexempt)) {
      echo "<tr style=\"background-color: bdd;\">";
    } else {
      echo "<tr style=\"background-color: fbb;\">";
    }
  } else {
    // show inactive billing services that are in not in good standing in red
    // show inactive billing services in other status in grey
    if (($mystatus == $l_pastdue)
	OR ($mystatus == $l_waiting)
	OR ($mystatus == $l_noticesent)
	OR ($mystatus == $l_turnedoff)
	OR ($mystatus == $l_declined)
	OR ($mystatus == $l_initialdecline)
	OR ($mystatus == $l_declined2x)) {
      echo "<tr style=\"background-color: fbb;\">";
    } else { 
      // print in grey if all services are removed from that billing id
      echo "<tr style=\"background-color: eee; color: aaa;\">";
    }
  }

  $edit_billing_url = "$ssl_url_prefix" . "index.php?load=billing&type=module&edit=on&";
  
  print "<td style=\"font-weight: bold;\">$billing_orgname&nbsp;".
    "<a href=\"$edit_billing_url".
    "billing_id=$billing_id\">$l_edit $billing_id</a>";

  print "</td><td>$billing_type</td><td>$mystatus</td>";

  $newtaxes = sprintf("%.2f",total_taxitems($DB, $billing_id));
  $newcharges = sprintf("%.2f",total_serviceitems($DB, $billing_id)+$newtaxes);
  $pastcharges = sprintf("%.2f",total_pastdueitems($DB, $billing_id));
  
  print "<td>$newcharges</td><td>$newtaxes</td><td>$pastcharges</td>";

  $newtotal = sprintf("%.2f",$newcharges + $pastcharges);
  print "<td>$newtotal</td>";
  
 }
 
 echo "</table>";
 
 echo "<p>";
 // show the services module info
 // Check if the file is inside the path_to_citrus
 $filepath = "$path_to_citrus/modules/services/index.php";
 if (file_exists($filepath)) {
   include('./modules/services/index.php');
 }
 
} else permission_error();
?>
