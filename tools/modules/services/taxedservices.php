<?php
// Copyright (C) 2002-2004  Paul Yasi (paul at citrusdb.org)
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

//GET Variables
if (!isset($base->input['linkedservice'])) {
  $base->input['linkedservice'] = ""; }
if (!isset($base->input['torate'])) { $base->input['torate'] = ""; }
if (!isset($base->input['id'])) { $base->input['id'] = ""; }

$submit = $base->input['submit'];
$linkedservice = $base->input['linkedservice'];
$torate = $base->input['torate'];
$id = $base->input['id'];

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
  echo "$l_youmusthaveadmin";
  exit;
}

if ($submit) {
  // then we add a new taxed services link
  $query = "INSERT INTO taxed_services (master_services_id,tax_rate_id) ".
    "VALUES ('$linkedservice','$torate')";
	
  $result = $DB->Execute($query) or die ("$l_queryfailed");

  print "<h3>$l_changessaved</h3>".
    "[<a href=\"index.php?load=services&tooltype=module&type=tools\">".
    "$l_done</a>]";
}

if ($delete) {
  // then we delete a taxed service link
  $query = "DELETE FROM taxed_services WHERE id = '$id'";
  $result = $DB->Execute($query) or die ("$l_queryfailed");	
  
  print "<h3>$l_changessaved</h3> ".
    "[<a href=\"index.php?load=services&tooltype=module&type=tools\">".
    "$l_done</a>]";
}

// query the taxed_services and link it with master_service and tax_rates
// to get descriptions and rates shown

$query = "SELECT ts.id ts_id, ts.master_services_id ts_serviceid, ".
  "ts.tax_rate_id ts_rateid, ms.id ms_id, ".
  "ms.service_description ms_description, ".
  "tr.id tr_id, tr.description tr_description ".
  "FROM taxed_services ts ".
  "LEFT JOIN master_services ms ON ms.id = ts.master_services_id ".
  "LEFT JOIN tax_rates tr ON tr.id = ts.tax_rate_id";

$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");

echo '<table cellpadding=5 cellspacing=1><tr bgcolor="#eeeeee">';
echo "<td><b>$l_id</b></td> <td><b>$l_serviceid</b></td> ".
"<td><b>$l_service $l_description</b></td> <td><b>$l_taxrateid</b></td> ".
"<td><b>$l_tax $l_description</b></td> <td></td> </tr>";

while ($myresult = $result->FetchRow())
  {
    $id = $myresult['ts_id'];
    $serviceid = $myresult['ts_serviceid'];
    $rateid = $myresult['ts_rateid'];
    $service_description = $myresult['ms_description'];
    $rate_description = $myresult['tr_description'];
    print "<tr bgcolor=\"#eeeeee\"><td>$id</td><td>$serviceid</td> ".
      "<td>$service_description</td><td>$rateid</td> ".
      "<td>$rate_description</td>";
    print "<td><a href=\"index.php?load=services&tooltype=module&type=tools&tax=on&taxedservices=on&id=$id&delete=Delete\">$l_delete</a></td></tr>\n";
}

echo "</table><p>
<b>$l_add:</b><br>
<FORM ACTION=\"index.php\" METHOD=\"GET\">
$l_linkservice: <select name=linkedservice>";
// get the list of services from the table
$query = "SELECT * FROM master_services ORDER BY service_description";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");

while ($myresult = $result->FetchRow())
  {
    $id = $myresult['id'];
    $description = $myresult['service_description'];
    print "<option value=\"$id\">$description</option>\n";
  }

echo "</select>
$l_totaxrate: <select name=torate>";
// get the list of services from the table
$query = "SELECT * FROM tax_rates ORDER BY description";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");

while ($myresult = $result->FetchRow())
  {
    $id = $myresult['id'];
    $description = $myresult['description'];
    $rate = $myresult['rate'];
    print "<option value=\"$id\">$description ($rate)</option>\n";
  }

echo "
</select>
<input type=hidden name=load value=services>
<input type=hidden name=tooltype value=module>
<input type=hidden name=type value=tools>
<input type=hidden name=tax value=on>
<input type=hidden name=taxedservices value=on>
<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_add\">
</FORM>";
?>
<p>

