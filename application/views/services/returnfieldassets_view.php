<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

// ask for return date and return notes and send to returned  
print "<form style=\"margin-bottom:0;\" action=\"index.php\" method=post>".
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

