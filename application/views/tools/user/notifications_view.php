<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<h3><?php echo lang('notifications')?></h3>
<p>
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/user/savenotifications" METHOD="POST">
<B><?php echo lang('contactemail')?>:</B> 
<INPUT TYPE="TEXT" NAME="email" VALUE="<?php echo $email?>" SIZE="20" MAXLENGTH="65"><p>
<B><?php echo lang('screenname')?>:</B>
<INPUT TYPE="TEXT" NAME="screenname" VALUE="<?php echo $screenname?>" SIZE="20" MAXLENGTH="65">
<P>
<table>
<?php
if ($email_notify == 'y') {
	echo "<td>".lang('email_support_notification')."</td><td><input type=\"radio\" name=email_notify value=\"y\" checked>".lang('yes')."<input type=\"radio\" name=email_notify value=\"n\">".lang('no')."<tr>";
} else {
	echo "<td>".lang('email_support_notification')."</td><td><input type=\"radio\" name=email_notify value=\"y\">".lang('yes')."<input type=\"radio\" name=email_notify value=\"n\" checked>".lang('no')."<tr>";
}

if ($screenname_notify == 'y') {
	echo "<td>".lang('im_support_notification')."</td><td><input type=\"radio\" name=screenname_notify value=\"y\" checked>".lang('yes')."<input type=\"radio\" name=screenname_notify value=\"n\">".lang('no')."<tr>";
} else {
	echo "<td>".lang('im_support_notification')."</td><td><input type=\"radio\" name=screenname_notify value=\"y\">".lang('yes')."<input type=\"radio\" name=screenname_notify value=\"n\" checked>".lang('no')."<tr>";
}
?>
</table>
<p>
<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo lang('savechanges')?>">
</FORM>
