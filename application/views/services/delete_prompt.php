<br><br>
<h4>
<?php echo lang('areyousuredelete') . ": $userserviceid $servicedescription"; ?>
</h4>
<table cellpadding=15 cellspacing=0 border=0 width=720>
<td align=right>

<form style="margin-bottom:0;" action="<?php echo $this->url_prefix?>/index.php/services/deletenow/<?php echo $userserviceid?>" method=post>
<input name=deletenow type=submit value="<?php echo lang('deleteservice_removeuser') . " $removal_date";?> "
class=smallbutton></form></td>

<td align=left><form style="margin-bottom:0;" action="<?php echo $this->url_prefix?>/index.php/services/deletetoday/<?php echo $userserviceid?>" method=post>
<input name=deletetoday type=submit value="<?php echo lang('deleteservice_removetoday') ?>" class=smallbutton></form></td>   

<td align=left><form style="margin-bottom:0;" action="<?php echo $this->url_prefix?>/index.php/services/deletenoauto/<?php echo $userserviceid?>" method=post>
<input name=deletenoauto type=submit value="<?php echo lang('deleteservice_activeuser')?> " class=smallbutton></form></td> 

<td align=left><form style="margin-bottom:0;" action="<?php echo $this->url_prefix?>/index.php/customer" method=post>
<input name=done type=submit value=" <?php echo lang('no') ?>" class=smallbutton>
<input type=hidden name=load value=services>        
<input type=hidden name=type value=module>
</form></td></table>
</blockquote>

