<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<br><br>
<h4><?php echo lang('areyousuredelete')?>: <?php echo $id?></h4>
<table cellpadding=15 cellspacing=0 border=0 width=720>
<td align=right width=360>

<form style="margin-bottom:0;" 
action="<?php echo $this->url_prefix?>/index.php/tools/admin/savedeletetaxedservice" method=post>
<input type=hidden name=id value="<?php echo $id?>">
<input name=deletenow type=submit value="<?php echo lang('yes')?>" class=smallbutton>
</form></td>

<td align=left width=360>
<form style="margin-bottom:0;" 
action="<?php echo $this->url_prefix?>/index.php/tools/admin/taxedservices" method=post>
<input name=done type=submit value="<?php echo lang('no')?>" class=smallbutton>
</form></td></table>
</blockquote>"
