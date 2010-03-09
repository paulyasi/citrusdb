<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>$l_notifications</h3>";
// Copyright (C) 2010  Paul Yasi (paul at citrusdb.org)
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

//GET Variables
if (!isset($base->input['email'])) { $base->input['email'] = ""; }
if (!isset($base->input['screenname'])) { $base->input['screenname'] = ""; }
if (!isset($base->input['email_notify'])) { $base->input['email_notify'] = ""; }
if (!isset($base->input['screenname_notify'])) { $base->input['screenname_notify'] = ""; }

$submit = $base->input['submit'];
$email = $base->input['email'];
$screenname = $base->input['screenname'];
$email_notify = $base->input['email_notify'];
$screenname_notify = $base->input['screenname_notify'];

if ($submit) {
  // save user information
  $query = "UPDATE user ".
    "SET email = '$email', ".
    "screenname = '$screenname', ".
    "email_notify = '$email_notify', ".
    "screenname_notify = '$screenname_notify' ".
    "WHERE username = '$user'";
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  print "<h3>$l_changessaved</h3>";

}

        // get the variables out of the general configuration table
        $query = "SELECT * FROM user WHERE username = '$user'";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
        $result = $DB->Execute($query) or die ("$l_queryfailed");
        
	$myresult = $result->fields;
        $email = $myresult['email'];
        $screenname = $myresult['screenname'];
        $email_notify = $myresult['email_notify'];
        $screenname_notify = $myresult['screenname_notify'];

echo "<script language=\"JavaScript\" src=\"include/md5.js\"></script>
        <script language=\"JavaScript\" src=\"include/verify.js\"></script>
	<p>
	<FORM ACTION=\"index.php\" METHOD=\"GET\">
	<input type=hidden name=load value=notifications>
	<input type=hidden name=type value=tools>
        <B>$l_contactemail:</B> <INPUT TYPE=\"TEXT\" NAME=\"email\" VALUE=\"$email\" SIZE=\"20\" MAXLENGTH=\"65\"><p>
        <B>$l_screenname:</B><INPUT TYPE=\"TEXT\" NAME=\"screenname\" VALUE=\"$screenname\" SIZE=\"20\" MAXLENGTH=\"65\">
        <P>
        <table>";

if ($email_notify == 'y') {
echo "<td>$l_email_support_notification</td><td><input type=\"radio\" name=email_notify value=\"y\" checked>$l_yes<input type=\"radio\" name=email_notify value=\"n\">$l_no<tr>";
} else {
echo "<td>$l_email_support_notification</td><td><input type=\"radio\" name=email_notify value=\"y\">$l_yes<input type=\"radio\" name=email_notify value=\"n\" checked>$l_no<tr>";
}

if ($screenname_notify == 'y') {
echo "<td>$l_im_support_notification</td><td><input type=\"radio\" name=screenname_notify value=\"y\" checked>$l_yes<input type=\"radio\" name=screenname_notify value=\"n\">$l_no<tr>";
} else {
echo "<td>$l_im_support_notification</td><td><input type=\"radio\" name=screenname_notify value=\"y\">$l_yes<input type=\"radio\" name=screenname_notify value=\"n\" checked>$l_no<tr>";
}

echo "</table>
        <p>
        <INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_savechanges\">
	</FORM>";
?>
</body>
</html>







