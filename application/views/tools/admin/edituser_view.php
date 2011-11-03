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
<INPUT TYPE="TEXT" NAME="realname" VALUE="<?php echo $u['real_name']?>" SIZE="20" MAXLENGTH="65">
<P>
<B><?php echo lang('username')?>:</B> <INPUT TYPE="TEXT" NAME="username" VALUE="<?php echo $u['username']?>" SIZE="20" MAXLENGTH="65">
<p>
<B><?php echo lang('contactemail')?>:</B> <INPUT TYPE="TEXT" NAME="email" VALUE="<?php echo $u['email']?>" SIZE="20" MAXLENGTH="65"><p>
<B><?php echo lang('screenname')?>:</B><INPUT TYPE="TEXT" NAME="screenname" VALUE="<?php echo $u['screenname']?>" SIZE="20" MAXLENGTH="65">
<P>
<b><?php echo lang('privileges')?>:</b><br>
<table>
<?php 
if ($u['email_notify'] == 'y') {
	echo "<td>".lang('email_support_notification')."</td><td><input type=\"radio\" name=email_notify value=\"y\" checked>".lang('yes')."<input type=\"radio\" name=email_notify value=\"n\">".lang('no')."<tr>";
} else {
	echo "<td>".lang('email_support_notification')."</td><td><input type=\"radio\" name=email_notify value=\"y\">".lang('yes')."<input type=\"radio\" name=email_notify value=\"n\" checked>".lang('no')."<tr>";
}

if ($u['screenname_notify'] == 'y') {
	echo "<td>".lang('im_support_notification')."</td><td><input type=\"radio\" name=screenname_notify value=\"y\" checked>".lang('yes')."<input type=\"radio\" name=screenname_notify value=\"n\">".lang('no')."<tr>";
} else {
	echo "<td>".lang('im_support_notification')."</td><td><input type=\"radio\" name=screenname_notify value=\"y\">".lang('yes')."<input type=\"radio\" name=screenname_notify value=\"n\" checked>".lang('no')."<tr>";
}

if ($u['admin'] == 'y') {
	echo "<td>".lang('admin')."</td><td><input type=\"radio\" name=admin value=\"y\" checked>".lang('yes')."<input type=\"radio\" name=admin value=\"n\">".lang('no')."<tr>";
} else {
	echo "<td>".lang('admin')."</td><td><input type=\"radio\" name=admin value=\"y\">".lang('yes')."<input type=\"radio\" name=admin value=\"n\" checked>".lang('no')."<tr>";
}

if ($u['manager'] == 'y') {
	echo "<td>".lang('manager')."</td><td><input type=\"radio\" name=manager value=\"y\" checked>".lang('yes')."<input type=\"radio\" name=manager value=\"n\">".lang('no')."<tr>";
} else {
	echo "<td>".lang('manager')."</td><td><input type=\"radio\" name=manager value=\"y\">".lang('yes')."<input type=\"radio\" name=manager value=\"n\" checked>".lang('no')."<tr>";
}
?>

</table>
<p>
<input type=hidden name="userid" value="<?php echo $userid?>">
<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo lang('savechanges')?>">
</FORM>
</body>
</html>

