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

// GET & POST Variables
if (!isset($_POST['feedback'])) { $_POST['feedback'] = ""; }
if (!isset($base->input['new_password1'])) { $base->input['new_password1'] = ""; }
if (!isset($base->input['new_password2'])) { $base->input['new_password2'] = ""; }
if (!isset($base->input['change_user_name'])) { $base->input['change_user_name'] = ""; }
if (!isset($base->input['old_password'])) { $base->input['old_password'] = ""; }

$feedback = $_POST['feedback'];
$submit = $base->input['submit'];
$new_password1 = $base->input['new_password1'];
$new_password2 = $base->input['new_password2'];
$change_user_name = $base->input['change_user_name'];
$old_password = $base->input['old_password'];

$real_name = $u->user_getrealname($user);
echo "$real_name, $l_youareloggedinas $user<br>";


if ($submit) {
	$u->user_change_password($new_password1,$new_password2,$change_user_name,$old_password);
}

if ($feedback) {
	echo '<FONT COLOR="RED"><H2>'.$feedback.'</H2></FONT>';
}

// MUST ENCRYPT THE PASSWORD USING JAVASCRIPT MD5 BEFORE SENDING IT TO THE SCRIPT

echo "<script language=\"JavaScript\" src=\"include/md5.js\"></script>
	<script language=\"JavaScript\" src=\"include/verify.js\"></script>
	<H3>$l_changepassword</H3>
	<P>
	<FORM ACTION=\"index.php\" METHOD=\"GET\">
	<input type=hidden name=load value=\"changepass\">
	<input type=hidden name=type value=\"tools\">
	<INPUT TYPE=\"hidden\" NAME=\"change_user_name\" VALUE=\"$user\" SIZE=\"10\" MAXLENGTH=\"32\">
	<P>
	<B>$l_oldpassword:</B><BR>
	<INPUT TYPE=\"password\" NAME=\"old_password\" VALUE=\"\" SIZE=\"10\" MAXLENGTH=\"32\">
	<P>
	<B>$l_newpassword:</B><BR>
	<INPUT TYPE=\"password\" NAME=\"new_password1\" VALUE=\"\" SIZE=\"10\" MAXLENGTH=\"32\">
	<P>
	<B>$l_newpassword ($l_again):</B><BR>
	<INPUT TYPE=\"password\" NAME=\"new_password2\" VALUE=\"\" SIZE=\"10\" MAXLENGTH=\"32\">
	<P>
	<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_changepassword\" onclick=\"if (validatePassword(new_password1.value) == 0) { return false; }; old_password.value = calcMD5(old_password.value); new_password1.value = calcMD5(new_password1.value); new_password2.value = calcMD5(new_password2.value);\">
	</FORM>";


?>









