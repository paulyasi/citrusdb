<?php
// Code Contributed by David Olivier

// Check for permissions to view module
    $groupname = array();
    $modulelist = array();
    $query = "SELECT * FROM groups WHERE groupmember = '$user'";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("First Permission Query Failed");	
    while ($myresult = $result->FetchRow())
	{
		array_push($groupname,$myresult['groupname']);
    }
    $groups = array_unique($groupname);
    array_push($groups,$user);

    $query = "SELECT user,permission FROM module_permissions WHERE modulename = '$load'";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("Second Permission Query Failed");	
    while ($myresult = $result->FetchRow())
	{
		if (in_array ($myresult['user'], $groups))
        {
            if ($myresult['permission'] == 'r')
            {
            	$pallow_view='y';
            }
            if ($myresult['permission'] == 'c')
            {
            	$pallow_create='y';
            }
            if ($myresult['permission'] == 'm')
            {
            	$pallow_modify='y';
            }
            if ($myresult['permission'] == 'd')
            {
            	$pallow_remove='y';
            }
            if ($myresult['permission'] == 'f')
            {
            	$pallow_view='y';
                $pallow_create='y';
                $pallow_modify='y';
                $pallow_remove='y';
            }
        }
    }

	function permission_error()
	{
		die ("user: $user You don't have permission to this this function");
	}

?>
