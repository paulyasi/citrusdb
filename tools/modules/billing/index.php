<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_billing</h3>";
// Copyright (C) 2003  Paul Yasi <paul@citrusdb.org>, read the README file for more information
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
if (!isset($base->input['billingtypes'])) { $base->input['billingtypes'] = ""; }
if (!isset($base->input['einvoice'])) { $base->input['einvoice'] = ""; }
if (!isset($base->input['importnew'])) { $base->input['importnew'] = ""; }
if (!isset($base->input['uploadnew'])) { $base->input['uploadnew'] = ""; }
if (!isset($base->input['declined'])) { $base->input['declined'] = ""; }
if (!isset($base->input['importccupdates'])) { $base->input['importccupdates'] = ""; }
if (!isset($base->input['uploadccupdates'])) { $base->input['uploadccupdates'] = ""; }
if (!isset($base->input['processcards'])) { $base->input['processcards'] = ""; }

$billingtypes = $base->input['billingtypes'];
$importnew = $base->input['importnew'];
$uploadnew = $base->input['uploadnew'];
$declined = $base->input['declined'];
$importccupdates = $base->input['importccupdates'];
$uploadccupdates = $base->input['uploadccupdates'];
$einvoice = $base->input['einvoice'];
$processcards = $base->input['processcards'];


if ($billingtypes)
{
        include('billingtypes.php');
}
elseif ($einvoice)
{
	include('einvoice.php');
}
elseif ($importnew)
{
        include('importnew.php');
}
elseif ($processcards)
{
	include('processcards.php');
}
elseif ($uploadnew)
{
        include('uploadnew.php');
}
elseif ($declined)
{
	include('declined.php');
}
elseif ($importccupdates)
{
	include('importccupdates.php');
}
elseif ($uploadccupdates)
{
	include('uploadccupdates.php');
}
else
{

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
        echo '$l_youmusthaveadmin<br>';
        exit;
	}

echo '[ <a href="index.php?load=billing&tooltype=module&type=tools&billingtypes=on">$l_editbillingtypes</a> ]';

}
?>
</table>
</body>
</html>
