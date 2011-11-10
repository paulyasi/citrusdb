<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>

<h3><?php echo lang('mergeaccounts')?></h3>
<p>

<P>
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/admin/confirmmergeaccounts" METHOD="POST">
<B><?php echo lang('from')?></B><BR><INPUT TYPE="TEXT" NAME="from_account">
<P>
<B><?php echo lang('to')?></B><BR><INPUT TYPE="TEXT" NAME="to_account">
<P>
<P>
<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo lang('submit')?>">
</FORM>

</body>
</html>
