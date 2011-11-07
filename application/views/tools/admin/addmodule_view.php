<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">

<h3><?php echo lang('addmodule')?></h3>
[ <a href="<?php echo $this->url_prefix?>/index.php/tools/admin/modules"><?php echo lang('back')?></a> ]
<P>
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/admin/saveaddmodule" METHOD="POST">
<B><?php echo lang('commonname')?></B><BR><INPUT TYPE="TEXT" NAME="commonname">
<P>
<B><?php echo lang('modulename')?></B><BR><INPUT TYPE="TEXT" NAME="modulename">
<P>
<B><?php echo lang('sortorder')?></B><BR><INPUT TYPE="TEXT" NAME="sortorder">
<P>
<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo lang('add')?>">
</FORM>

</body>
</html>
