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

//GET Variables
if (!isset($base->input['new_password1'])) { $base->input['new_password1'] = ""; }
if (!isset($base->input['new_password2'])) { $base->input['new_password2'] = ""; }
if (!isset($base->input['userid'])) { $base->input['userid'] = ""; }

$submit = $base->input['submit'];
$new_password1 = $base->input['new_password1'];
$new_password2 = $base->input['new_password2'];
$userid = $base->input['userid'];

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
        echo "$l_youmusthaveadmin<br>";
        exit;
}

if ($submit) {
	if ($new_password1 == $new_password2) {
	$query = "UPDATE user SET password = '$new_password1' WHERE id = '$userid'";
        $result = $DB->Execute($query) or die ("$l_queryfailed");
	print "<h3>$l_passwordchanged</h3>";
	}
	else {
	print "<h3>$l_error: $l_passwordsdonotmatch</h3>";
	}

}

if (!isset($feedback)) { $feedback = ""; }
if ($feedback) {
	echo '<FONT COLOR="RED"><H2>'.$feedback.'</H2></FONT>';
}

echo "<script language=\"JavaScript\" src=\"include/md5.js\"></script>
	<script language=\"JavaScript\" src=\"include/verify.js\"></script>
	<H3>$l_changepassword</H3>
	<P>
	<FORM ACTION=\"index.php\" METHOD=\"GET\">
        <input type=hidden name=load value=\"adminchangepass\">
	<input type=hidden name=type value=tools>
	$l_userid $userid
	<INPUT TYPE=hidden NAME=\"userid\" VALUE=\"$userid\" SIZE=\"10\" MAXLENGTH=\"32\">
	<P>
	<B>$l_newpassword:</B><BR>
	<INPUT TYPE=\"password\" NAME=\"new_password1\" VALUE=\"\" SIZE=\"10\" MAXLENGTH=\"32\">
	<P>
	<B>$l_newpassword ($l_again):</B><BR>
	<INPUT TYPE=\"password\" NAME=\"new_password2\" VALUE=\"\" SIZE=\"10\" MAXLENGTH=\"32\">
	<P>
	<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_changepassword\" onclick=\"new_password1.value = calcMD5(new_password1.value); new_password2.value = calcMD5(new_password2.value);\">
	</FORM>";
?>









