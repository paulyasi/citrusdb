<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<form style="margin-bottom:0;" 
action="<?php echo $this->url_prefix?>/index.php/services/savereturnfieldasset" 
method=post>
<table width=720 cellpadding=5 cellspacing=1 border=0>
<input type=hidden name=userserviceid value=<?php echo $userserviceid?>>
<input type=hidden name=item_id value="<?php echo $item_id?>">

<table>

<?php $mydate = date("Y-m-d"); ?>
<td><label><?php echo lang('returndate')?>: </td>
<td><input type=text name=return_date value="<?php echo $mydate?>"></label></td><tr>

<td><label><?php echo lang('returnnotes')?>: </td>
<td><input type=text name=return_notes></label></td><tr>  

<td></td><td>
<input name=fieldassets type=submit value="<?php echo lang('returndevice')?>" class=smallbutton>
</td></table></form><p>

