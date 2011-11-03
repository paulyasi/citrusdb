<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('deleteuser')?></h3>
<br><br>
<h4><?php echo lang('areyousureyouwanttoremovetheuserid')?>: <?php echo $uid?></h4>
<table cellpadding=15 cellspacing=0 border=0 width=720><td align=right width=360>

<form style="margin-bottom:0;" action="<?php echo $this->url_prefix?>/index.php/tools/admin/savedeleteuser" method=post>
<input type=hidden name=uid value="<?php echo $uid?>">
<input name=deletenow type=submit value="<?php echo lang('yes')?>" class=smallbutton></form></td>

<td align=left width=360><form style="margin-bottom:0;" action="<?php echo $this->url_prefix?>/index.php/tools/admin/users" method=post>
<input name=done type=submit value="<?php echo lang('no')?>" class=smallbutton>
</form></td></table>
</blockquote>
