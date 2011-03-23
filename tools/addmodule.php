<html>
<body bgcolor="#ffffff">

<?php
echo "<h3>$l_addmodule</h3>
[ <a href=\"index.php?load=modules&type=tools\">$l_back</a> ]
<p>";

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

// GET & POST Variables
if (!isset($_POST['feedback'])) { $_POST['feedback'] = ""; }
if (!isset($base->input['commonname'])) { $base->input['commonname'] = ""; }
if (!isset($base->input['modulename'])) { $base->input['modulename'] = ""; }
if (!isset($base->input['sortorder'])) { $base->input['sortorder'] = ""; }

$feedback = $_POST['feedback'];
$submit = $base->input['submit'];
$commonname = $base->input['commonname'];
$modulename = $base->input['modulename'];
$sortorder = $base->input['sortorder'];

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
        echo '$l_youmusthaveadmin<br>';
        exit;
}

if ($submit) {
	// insert groupname and username into the groups table
	$query = "INSERT INTO modules (commonname,modulename,sortorder) VALUES ('$commonname','$modulename','$sortorder')";
	$result = $DB->Execute($query) or die ("$query $l_queryfailed");
	print "<h3>Modules Updated</h3>";
}

echo "$l_beforeyoucanaddanewmodule $path_to_citrus/modules";
	echo "
	<P>
	<FORM ACTION=\"index.php\" METHOD=\"POST\">
	<B>$l_commonname</B><BR><INPUT TYPE=\"TEXT\" NAME=\"commonname\">
	<P>
	<B>$l_modulename</B><BR><INPUT TYPE=\"TEXT\" NAME=\"modulename\">
	<P>
	<B>$l_sortorder</B><BR><INPUT TYPE=\"TEXT\" NAME=\"sortorder\">
	<P>
	<input type=hidden name=load value=addmodule>
	<input type=hidden name=type value=tools>
	<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_add\">
	</FORM>";


?>

</body>
</html>
