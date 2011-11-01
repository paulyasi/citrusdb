<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<script language="JavaScript" src="include/verify.js"></script>
<H3><?php echo lang('addnewuser')?></H3>
<P>
<FORM ACTION="<?php echo $this->ssl_url_prefix?>/index.php/tools/admin/savenewuser" METHOD="POST">
<B><?php echo lang('name')?>:</B><BR>
<INPUT TYPE="TEXT" NAME="real_name" VALUE="" SIZE="20" MAXLENGTH="65">
<P>
<B><?php echo lang('username')?>:</B><BR>
<INPUT TYPE="TEXT" NAME="new_user_name" VALUE="" SIZE="10" MAXLENGTH="32">
<?php 
if (!$this->config->item('ldap_enable'))
{ 
echo ":<P><B>".lang('password').":</B><BR>".
 "<INPUT TYPE=\"password\" NAME=\"password1\" VALUE=\"\" SIZE=\"10\" MAXLENGTH=\"32\">".
 "<P>".
 "<B>".lang('password')." (".lang('again')."):</B><BR>".
 "<INPUT TYPE=\"password\" NAME=\"password2\" VALUE=\"\" SIZE=\"10\" MAXLENGTH=\"32\">";
}?>
<P>
<b><?php echo lang('privileges')?>:</b><br>
<table>
<td><?php echo lang('admin')?></td><td><input type="radio" name=admin value="y"><?php echo lang('yes')?><input type="radio" name=admin value="n" checked><?php echo lang('no')?><tr>
<td><?php echo lang('manager')?></td><td><input type="radio" name=manager value="y"><?php echo lang('yes')?><input type="radio" name=manager value="n" checked><?php echo lang('no')?><tr>
</table>
<p>
<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo lang('submit')?>">
</FORM>
