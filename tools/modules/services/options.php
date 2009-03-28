<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_optionstables</h3>";
// Copyright (C) 2002  Paul Yasi <paul@citrusdb.org>, read the README file for more information
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
if (!isset($base->input['tname'])) { $base->input['tname'] = ""; }
if (!isset($base->input['table'])) { $base->input['table'] = ""; }

$submit = $base->input['submit'];
$tname = $base->input['tname'];
$table = $base->input['table'];

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
	if ($table == 'create')
	{
		$tablename = $tname;
		// create a table, then go to the editoptions.php file		
		// put an id field into it by default
		$query = "CREATE TABLE $tablename(id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, user_services INT NOT NULL)";
		$result = $DB->Execute($query) or die ("$l_queryfailed");
	}
}

// Get a list of unique options_tables named in the master_services table
$query = "SELECT DISTINCT options_table FROM master_services";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");

echo "<table cellpadding=5 cellspacing=1><tr bgcolor=\"#eeeeee\"><td><b>$l_name</b></td><td></td></tr>";
// For ones the already exist put an Edit link
// For ones that don't exist put a Create link

while ($myresult = $result->FetchRow())
{
	$options_table = $myresult['options_table'];	

	$i = 0;
	$tableresult = $DB->MetaTables('TABLES');	
	
   	foreach ($tableresult as $row) {
		// check if a table named here matched the options_table 
		// specified, if it does set $i = 1
		
		if($row == $options_table) {$i = 1;};
        	
		//print "Table: $row[0]<br>";
	}
	

	if (($i == 1) and ($options_table <> '')) // the table exists
	{
		print "<tr bgcolor=\"#eeeeee\"><td>$options_table</td><td>$l_ready</td></tr>";
	}
	else // the table is new 
	{
	if ($options_table <> '') {
		print "<tr bgcolor=\"#eeeeee\"><td>$options_table</td><td><a href=\"index.php?load=services&tooltype=module&type=tools&options=on&table=create&submit=Link&tname=$options_table\">$l_create</a></td></tr>";
		}
	}
	
}

?>
</table>
<p>
To edit an options table you must use SQL queries to add fields to them or a 
utility like phpMyAdmin.  When new options tablesare created they are all 
created with an ID and user_services field to connect them to the user's 
services.  You may then addany fields you wish after those first two.
</body>
</html>
