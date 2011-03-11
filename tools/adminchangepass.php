<?php
// Copyright (C) 2011  Paul Yasi <paul@citrusdb.org>, read the README file for more information
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
    // base-2 logarithm of the iteration count for password stretching
    $hash_cost_log2 = 8;

    // do we require the hashes to be portable to older systems (less secure)?
    // bcrypt hashes have '$2a$' header
    // des ext hashes have '_'
    // portable md5 hashes have '$P$' header
    $hash_portable = FALSE;

    // hash the new password
    $hasher = new PasswordHash($hash_cost_log2, $hash_portable);
    $newhash = $hasher->HashPassword($new_password1);
    // hash always greater than 20 chars, if not something went wrong
    if (strlen($newhash) < 20) {
      print "<h3>$l_error: Failed to hash new password</h3>";
    } else {
      // set the new password value
      $query = "UPDATE user SET password = '$newhash' WHERE id = '$userid'";
      $result = $DB->Execute($query) or die ("new password update $l_queryfailed");
      print "<h3>$l_passwordchanged</h3>";
    }
  }
  else {
    print "<h3>$l_error: $l_passwordsdonotmatch</h3>";
  }
  
}

if (!isset($feedback)) { $feedback = ""; }
if ($feedback) {
  echo '<FONT COLOR="RED"><H2>'.$feedback.'</H2></FONT>';
}

echo "<script language=\"JavaScript\" src=\"include/verify.js\"></script>
	<H3>$l_changepassword</H3>
	<P>
	<FORM ACTION=\"$ssl_url_prefix/index.php\" METHOD=\"POST\">
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
	<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_changepassword\">
	</FORM>";
?>









