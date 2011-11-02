<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('edituser')?></h3>

<script language="JavaScript" src="include/md5.js"></script>
<script language="JavaScript" src="include/verify.js"></script>

<?php 
if (!$this->config->item('ldap_enable'))
{ 
	echo" [<a href=\"$this->url_prefix/index.php/tools/admin/changepass/$userid\">
		".lang('changepassword')."</a>]";
}
?>

<p>
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/admin/saveedituser" METHOD="POST">
<B><?php echo lang('name')?>:</B><BR>
<INPUT TYPE="TEXT" NAME="realname" VALUE="<?php echo $u['realname']?>" SIZE="20" MAXLENGTH="65">
<P>
<B>$l_username:</B> <INPUT TYPE=\"TEXT\" NAME=\"username\" VALUE=\"$username\" SIZE=\"20\" MAXLENGTH=\"65\">
<p>
<B>$l_contactemail:</B> <INPUT TYPE=\"TEXT\" NAME=\"email\" VALUE=\"$email\" SIZE=\"20\" MAXLENGTH=\"65\"><p>
<B>$l_screenname:</B><INPUT TYPE=\"TEXT\" NAME=\"screenname\" VALUE=\"$screenname\" SIZE=\"20\" MAXLENGTH=\"65\">
<P>
<b>$l_privileges:</b><br>
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

if ($admin == 'y') {
	echo "<td>$l_admin</td><td><input type=\"radio\" name=admin value=\"y\" checked>$l_yes<input type=\"radio\" name=admin value=\"n\">$l_no<tr>";
} else {
	echo "<td>$l_admin</td><td><input type=\"radio\" name=admin value=\"y\">$l_yes<input type=\"radio\" name=admin value=\"n\" checked>$l_no<tr>";
}

if ($manager == 'y') {
	echo "<td>$l_manager</td><td><input type=\"radio\" name=manager value=\"y\" checked>$l_yes<input type=\"radio\" name=manager value=\"n\">$l_no<tr>";
} else {
	echo "<td>$l_manager</td><td><input type=\"radio\" name=manager value=\"y\">$l_yes<input type=\"radio\" name=manager value=\"n\" checked>$l_no<tr>";
}


echo "</table>
<p>
<input type=hidden name=\"userid\" value=\"$userid\">
<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_savechanges\">
</FORM>";
?>
</body>
</html>

