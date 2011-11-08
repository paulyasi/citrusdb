<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">
<br><br>
<h4><?php echo lang('areyousuredelete')?>: <?php echo $typeid?></h4>
<table cellpadding=15 cellspacing=0 border=0 width=720>
<td align=right width=360>

<form style="margin-bottom:0;" 
action="<?php echo $this->url_prefix?>/index.php/tools/admin/saveremovebillingtype" 
method=post>
<input type=hidden name=typeid value="<?php echo $typeid?>">
<input name=deletenow type=submit value="<?php echo lang('yes')?>" class=smallbutton>
</form></td>

<td align=left width=360>
<form style="margin-bottom:0;" 
action="<?php echo $this->url_prefix?>/index.php/tools/admin/billingtypes" 
method=post>
<input name=done type=submit value="<?php echo lang('no')?>" class=smallbutton>
</form></td></table>
