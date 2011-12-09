<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<?php echo lang('whycanceling')?>  <p>
<form style="margin-bottom:0;" 
action="<?php echo $this->url_prefix?>/index.php/customer/delete/<?php echo $now?>" 
name="cancelform" method=post>

<select name="cancel_reason" onChange="document.cancelform.deletenow.disabled=false">
<option value="">Choose One...</option>
<?php
foreach ($cancelreasons as $myresult) 
{
	$myid = $myresult['id'];
	$myreason = $myresult['reason'];
	echo "<option value=\"$myid\">$myreason</option>";
}
?>
</select><p>

<input disabled name=deletenow id=deletenow type=submit 
value="<?php echo lang('cancelcustomer')?>" class=smallbutton></form><p>
