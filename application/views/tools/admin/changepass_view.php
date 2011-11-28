<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<script language="JavaScript" src="include/verify.js"></script>
<H3><?php echo lang('changepassword')?></H3>
<P>
<FORM ACTION="<?php echo $this->ssl_url_prefix?>/index.php/tools/admin/savechangepass" METHOD="POST">
<INPUT TYPE="hidden" NAME="id" VALUE="<?php echo $id?>">
<P>
<B><?php echo lang('newpassword')?>:</B><BR>
<INPUT TYPE="password" NAME="new_password1" VALUE="" SIZE="10" MAXLENGTH="32">
<P>
<B><?php echo lang('newpassword')?> (<?php echo lang('again')?>):</B><BR>
<INPUT TYPE="password" NAME="new_password2" VALUE="" SIZE="10" MAXLENGTH="32">
<P>
<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo lang('changepassword')?>" onclick="if (validatePassword(new_password1.value) == 0) { return false; };">
</FORM>


