<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
// Check for permissions to view module
    $groupname = array();
    $modulelist = array();
	$query = "SELECT * FROM groups WHERE groupmember = '$this->user'";
	$result = $this->db->query($query) or die ("$l_queryfailed");
	foreach($result->result() as $myresult)
	{
		array_push($groupname,$myresult->groupname);
	}
    $groups = array_unique($groupname);
    array_push($groups,$this->user);

    while (list($key,$value) = each($groups))
    {
        $query = "SELECT * FROM module_permissions WHERE user = '$value' ";
		$result = $this->db->query($query) or die ("$l_queryfailed");
		foreach($result->result() as $myresult)
		{
        	array_push($modulelist,$myresult->modulename);
    	}
    }
    $viewable = array_unique($modulelist);

// Print Modules Menu

echo "<div id=\"tabnav\">";

$query = "SELECT * FROM modules ORDER BY sortorder";
$result = $this->db->query($query) or die ("$l_queryfailed");

foreach($result->result() as $myresult)
{
	$commonname = $myresult->commonname;
	$modulename = $myresult->modulename;

	// change the commonname for base modules to a language compatible name
	if ($commonname == "Customer") { $commonname = $l_customer; }
	if ($commonname == "Services") { $commonname = $l_services; }
	if ($commonname == "Billing") { $commonname = $l_billing; }
	if ($commonname == "Support") { $commonname = $l_support; }

    if (in_array ($modulename, $viewable))
    {
		if ($load == $modulename) {
			print "<div><a class=\"active\" href=\"$url_prefix/index.php?load=$modulename&type=module\">$commonname</a></div>";
		} else {
			print "<div><a href=\"$url_prefix/index.php?load=$modulename&type=module\">$commonname</a></div>";
		}
    }
    	
	if ($modulename == "support")
	{
	  echo "<hr size=2 style=\"color:#eee;\">";

	  
	  echo "<form id=\"messagetabform\">";
	  //echo "<input type=hidden name=\"blah\" value=\"1\">";
	  echo "<div id=\"messagetabs\">";
	  echo "</div>";

	  if ($ticketgroup) {
	    $messagetabsurl = 'index.php?load=messagetabs&type=dl&ticketgroup=' . $ticketgroup;
	  } elseif ($ticketuser) {
	    $messagetabsurl = 'index.php?load=messagetabs&type=dl&ticketuser=' . $ticketuser;
	  } else {
	    $messagetabsurl = 'index.php?load=messagetabs&type=dl';
	  }	  
	  
	  // print the new message count tabs using ajax so they refresh periodically
	  echo "<script language=\"javascript\">".
	    "new Ajax.PeriodicalUpdater('messagetabs', '$messagetabsurl',".
	    "{".
	    "method: 'get',".
	    "frequency: 300,".
	    "});".
	    "</script></form>";
	  
	} // end if modulename == support

	
}

echo "</div>";