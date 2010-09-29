<?php
// Copyright (C) 2005  Paul Yasi (paul at citrusdb.org) 
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

echo "<div id=\"tabnav\">";

$query = "SELECT * FROM modules ORDER BY sortorder";
$result = $DB->Execute($query) or die ("$l_queryfailed");

while ($myresult = $result->FetchRow())
{
	$commonname = $myresult['commonname'];
	$modulename = $myresult['modulename'];

	// change the commonname for base modules to a language compatible name
	if ($commonname == "Customer") { $commonname = $l_customer; }
	if ($commonname == "Services") { $commonname = $l_services; }
	if ($commonname == "Billing") { $commonname = $l_billing; }
	if ($commonname == "Support") { $commonname = $l_support; }

    if (in_array ($modulename, $viewable))
    {
		if ($load == $modulename) {
			print "<div><a class=\"active\" href=\"index.php?load=$modulename&type=module\">$commonname</a></div>";
		} else {
			print "<div><a href=\"index.php?load=$modulename&type=module\">$commonname</a></div>";
		}
    }
    	
	if ($modulename == "support")
	{
	  echo "<hr size=2 style=\"color:#eee;\">";
	  
	  // initialize the messagearray
	  $messagearray = array();
	  
	  // query the customer_history for the number of 
	  // waiting messages sent to that user
	  $supportquery = "SELECT * FROM customer_history 
				WHERE notify = '$user' 
				AND status = \"not done\" ";
	  $supportresult = $DB->Execute($supportquery) or die ("$l_queryfailed");
	  $num_rows = $supportresult->RowCount();

	  // assign the number of messages for the user to the message associative array
	  $messagearray[$user] = $num_rows;
	  
	  // query the customer_history for messages sent to 
	  // groups the user belongs to
	  $query = "SELECT * FROM groups WHERE groupmember = '$user' ";
	  $supportresult = $DB->Execute($query) 
	    or die ("$l_queryfailed");
	  
	  while ($mygroupresult = $supportresult->FetchRow())
	    {
	      if (!isset($mygroupresult['groupname'])) 
		{ 
		  $mygroupresult['groupname'] = ""; 
		}
	      
	      $groupname = $mygroupresult['groupname'];
	      $query = "SELECT * FROM customer_history 
				WHERE notify = '$groupname' 
				AND status = \"not done\" ";
	      $gpresult = $DB->Execute($query) 
		or die ("$l_queryfailed");
	      $num_rows = $gpresult->RowCount(); // + $num_rows;

	      // assign the number of messages for the group to the message associative array
	      $messagearray[$groupname] = $num_rows;
	      
	    }
	  
	  // put the num messages link inside of their own tabnav
	  echo "<div id=\"tabnav\">";
	  echo "<div>\n";
	  foreach ($messagearray as $recipient => $messagecount) {
	    if ($messagecount == 0) {
	      echo "<a href=\"index.php?load=tickets&type=base#$recipient\"><b style=\"font-weight:normal;\">".
		"$recipient($messagecount)</b></a>\n";
	    } else {
	      echo "<a href=\"index.php?load=tickets&type=base#$recipient\">$recipient($messagecount)</a>\n";    
	    }
	  }
	  echo "</div>";
	  
	  //print "<div><a href=\"index.php?load=tickets&type=base\" class=smalltext>$num_rows $l_newmessage</a></div>";
	}
	
}

echo "</div>";

?>
