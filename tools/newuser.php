<?php
// Copyright (C) 2002-2004  Paul Yasi <paul@citrusdb.org>, read the README file for more information

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
if (!isset($base->input['password1'])) { $base->input['password1'] = ""; }
if (!isset($base->input['password2'])) { $base->input['password2'] = ""; }
if (!isset($base->input['new_user_name'])) { $base->input['new_user_name'] = ""; }
if (!isset($base->input['real_name'])) { $base->input['real_name'] = ""; }
if (!isset($base->input['admin'])) { $base->input['admin'] = ""; }
if (!isset($base->input['manager'])) { $base->input['manager'] = ""; }

$feedback = $_POST['feedback'];
$submit = $base->input['submit'];
$password1 = $base->input['password1'];
$password2 = $base->input['password2'];
$new_user_name = $base->input['new_user_name'];
$real_name = $base->input['real_name'];
$admin = $base->input['admin'];
$manager = $base->input['manager'];

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
	$u->user_register($new_user_name,$password1,$password2,$real_name,$admin,$manager);
	
	// check if there is a default_group setup
	$query = "SELECT default_group FROM settings WHERE id = 1";
        $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("$l_queryfailed");
	$myresult = $result->fields;
        $default_group = $myresult['default_group'];
	
	// if there is a default group, add them to that group
	if ($default_group != '')
	{	
		$query = "INSERT INTO groups (groupname,groupmember) VALUES ('$default_group','$new_user_name')";
        	$result = $DB->Execute($query) or die ("$l_queryfailed");
	}
}

if ($feedback) {
	echo '<FONT COLOR="RED"><H2>'.$feedback.'</H2></FONT>';
	echo "<p>$new_user_name<p>$password1<p>$password2<p>$real_name";
}

$new_user_name='';
$password1='';
$password2='';
$real_name='';

echo "<script language=\"JavaScript\" src=\"include/verify.js\"></script>
	<H3>$l_addnewuser</H3>
	<P>
	<FORM ACTION=\"$ssl_url_prefix/index.php\" METHOD=\"POST\">
	<B>$l_name:</B><BR>
	<INPUT TYPE=\"TEXT\" NAME=\"real_name\" VALUE=\"\" SIZE=\"20\" MAXLENGTH=\"65\">
	<P>
	<B>$l_username:</B><BR>
	<INPUT TYPE=\"TEXT\" NAME=\"new_user_name\" VALUE=\"\" SIZE=\"10\" MAXLENGTH=\"32\">".
        ($ldap_enable?"":
	"<P>
	<B>$l_password:</B><BR>
	<INPUT TYPE=\"password\" NAME=\"password1\" VALUE=\"\" SIZE=\"10\" MAXLENGTH=\"32\">
	<P>
	<B>$l_password ($l_again):</B><BR>
	<INPUT TYPE=\"password\" NAME=\"password2\" VALUE=\"\" SIZE=\"10\" MAXLENGTH=\"32\">"
        ).
	"<P>
	<b>$l_privileges:</b><br>
	<table>
	<td>$l_admin</td><td><input type=\"radio\" name=admin value=\"y\">$l_yes<input type=\"radio\" name=admin value=\"n\" checked>$l_no<tr>
	<td>$l_manager</td><td><input type=\"radio\" name=manager value=\"y\">$l_yes<input type=\"radio\" name=manager value=\"n\" checked>$l_no<tr>
	</table>
	<p>
	<input type=hidden name=load value=newuser>
	<input type=hidden name=type value=tools>
	<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_submit\">
	</FORM>";


?>
