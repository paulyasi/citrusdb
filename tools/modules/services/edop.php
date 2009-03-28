<?php
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

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
        echo "$l_youmusthaveadmin";
        exit;
}

if ($save) {
	$fieldlist = substr($fieldlist, 1); 
	// loop through post_vars associative/hash to get field values
	$array_fieldlist = explode(",",$fieldlist);
	foreach ($HTTP_GET_VARS as $mykey => $myvalue) {
		foreach ($array_fieldlist as $myfield) {
			if ($myfield == $mykey) {
				$fieldvalues .= ', ' . $myfield . ' = \'' . $myvalue . '\'';
			}
		}
	}
	$fieldvalues = substr($fieldvalues, 1);
      	$query = "UPDATE $optionstable SET $fieldvalues WHERE user_services = $userserviceid";
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	print "<script language=\"JavaScript\">window.location.href = \"index.php?load=services&type=module\";</script>";
}
else
{
if ($delete) // save the changes to the user_services table and the options table
{
	print "<br><br>";
	print "<h4>$l_areyousuredelete: $servicedescription</h4>";
	print "<table cellpadding=15 cellspacing=0 border=0 width=720><td align=right width=360><form style=\"margin-bottom:0;\" action=\"index.php\"><input type=hidden name=optionstable value=$optionstable><input type=hidden name=userserviceid value=$userserviceid>";
	print "<input type=hidden name=load value=services>";
        print "<input type=hidden name=type value=module>";
        print "<input type=hidden name=delete value=on>";
	print "<input name=deletenow type=submit value=\"  $l_yes  \" class=smallbutton></form></td>";
	print "<td align=left width=360><form style=\"margin-bottom:0;\" action=\"index.php\"><input name=done type=submit value=\"  $l_no  \" class=smallbutton>";
        print "<input type=hidden name=load value=services>";        
        print "<input type=hidden name=type value=module>";
	print "</form></td></table>";
	print "</blockquote>";

}
else if ($editbutton) // list the service options after they clicked on the add button.
{
print "<a href=\"index.php?load=services&type=module\">[ $l_undochanges ]</a>";

	// check for optionstable, skip this step if there isn't one
	if ($optionstable <> '')
        {
	$query = "SELECT * FROM $optionstable WHERE user_services = '$userserviceid'";
        $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	$myresult = $result->fields;
	}
	
	print "<h4>$l_edit: $servicedescription </h4><form action=\"index.php\"><table width=720 cellpadding=5 cellspacing=1 border=0>";
	print "<input type=hidden name=load value=services>";
        print "<input type=hidden name=type value=module>";
        print "<input type=hidden name=edit value=on>";
	print "<input type=hidden name=servicedescription value=\"$servicedescription\"><input type=hidden name=optionstable value=$optionstable><input type=hidden name=userserviceid value=$userserviceid>";

	// check for optionstable, skip this step if there isn't one	
        if ($optionstable <> '') {
	// list out the fields in the options table for that service
	$fields = $DB->MetaColumns($options_table_name);
	foreach($fields as $v) 
	{
		$fieldname = $v->name;
		$fieldflags = $v->type;
		
		if ($fieldflags == "enum")
		{
			echo "<td bgcolor=\"ccccdd\"width=180><b>" . $fieldname . "</b></td><td bgcolor=\"#ddddee\"><select name=$fieldname value=$myresult[$i]></select></td><tr>\n";
		} else {		
			echo "<td bgcolor=\"ccccdd\"width=180><b>" . $fieldname . "</b></td><td bgcolor=\"#ddddee\"><input type=text name=$fieldname value=$myresult[$i]></td><tr>\n";
		}
		$fieldlist .= ',' . $fieldname;
	}
	print "<input type=hidden name=fieldlist value=$fieldlist>";
	print "<td></td><td><input name=save type=submit value=\"$l_savechanges\" class=smallbutton>&nbsp;&nbsp;&nbsp;<input name=delete type=submit value=\"$l_deleteservice\" class=smallbutton></td></table></form></blockquote>";
	}
	else {
	print "<td></td><td><input name=delete type=submit value=\"$l_deleteservice\" class=smallbutton></td></table></form></blockquote>";
	}
}

}
?>
