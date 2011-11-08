<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">
<p>
<h3><?php echo lang('removepermissions')?></h3>
  
<?php echo lang('areyousureyouwanttoremovethis')?>
<p>

<table><td>
<form style="margin-bottom:0;" 
action="<?php echo $this->url_prefix?>/index.php/tools/admin/saveremovemodulepermissions" 
method=post>
<input type=hidden name=module value="<?php echo $module?>">
<input type=hidden name=pid value="<?php echo $pid?>">
<input name=deletenow type=submit value="<?php echo lang('yes')?>" class=smallbutton></form></td><td>

<form style="margin-bottom:0;" 
action="<?php echo $this->url_prefix?>/index.php/tools/admin/modulepermissions/<?php echo $module?>" 
method=post>
<input name=done type=submit value="<?php echo lang('no')?>" class=smallbutton>
</form></td></table>

</table>
</body>
</html>

