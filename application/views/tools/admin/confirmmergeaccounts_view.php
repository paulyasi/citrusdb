<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<?php echo lang('merge')?>: <p>
<table cellpadding=5><td><u><?php echo lang('from')?></u>
<?php echo $from_account?><br>
<?php echo $m['from_name']?><br>
<?php echo $m['from_company']?><br>
<?php echo $m['from_street']?>
<br><?php echo $m['from_city']." ".$m['from_state']." ".$m['from_zip'];?></td>
<td><u><?php echo lang('to')?></u>: 
<?php echo $to_account?><br>
<?php echo $m['to_name']?><br>
<?php echo $m['to_company']?><br>
<?php echo $m['to_street']?>
<br>
<?php echo $m['to_city']." ".$m['to_state']." ".$m['to_zip'];?></td></table><br>

<table cellpadding=15 cellspacing=0 border=0 width=720><td align=right width=360>
<form style="margin-bottom:0;" 
action="<?php echo $this->url_prefix?>/index.php/tools/admin/savemergeaccounts" method=post>
<input type=hidden name=to_account value=<?php echo $to_account?>>
<input type=hidden name=from_account value=<?php echo $from_account?>>
<input name=confirm type=submit value="  <?php echo lang('yes')?>  " class=smallbutton></form></td>
<td align=left width=360>
<form style="margin-bottom:0;" action="<?php echo $this->url_prefix?>/index.php/tools/admin/mergeaccounts" method=post>
<input name=done type=submit value="  <?php echo lang('no')?>  " class=smallbutton>
</form></td></table>

