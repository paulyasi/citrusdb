<?php
echo "<H3>&nbsp; $l_search</H3>
<blockquote>";
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


// **** CUSTOM SEARCHING ********************
//
// You may add custom search queries below that will be show
// no matter what modules you have installed.
//


echo "<fieldset><legend><b>$l_accountnumber</b></legend>";

echo "
<form ACTION=\"index.php?load=dosearch&type=fs\" METHOD=\"POST\">
	<input type=text name=s1>
	<input type=hidden name=id value=1>
	<input type=submit name=submit value=\"$l_search\" class=smallbutton>
</form>
</fieldset>
";

//**** MODULE SEARCH FUNCTIONS
//
// Load the search.php part of each module that is installed
//


// Check for permissions to view module
    $groupname = array();
    $modulelist = array();
        $query = "SELECT * FROM groups WHERE groupmember = '$user'";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	while ($myresult = $result->FetchRow())
        {
                array_push($groupname,$myresult['groupname']);
    }
    $groups = array_unique($groupname);
    array_push($groups,$user);

    while (list($key,$value) = each($groups))
    {
        $query = "SELECT * FROM module_permissions WHERE user = '$value' ";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	while ($myresult = $result->FetchRow())
        {
                array_push($modulelist,$myresult['modulename']);
        }
    }
    $viewable = array_unique($modulelist);


// Print Modules Menu

$query = "SELECT * FROM modules ORDER BY sortorder";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");

while ($myresult = $result->FetchRow())
{
        $commonname = $myresult['commonname'];
        $modulename = $myresult['modulename'];

    if (in_array ($modulename, $viewable))
    {
	include('./modules/'.$modulename.'/search.php');
    }
}

?>

<P>
</blockquote>
