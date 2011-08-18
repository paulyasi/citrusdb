<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<h4><?php echo lang('areyousurecancel') . ": " . $this->account_number?></h4>
<table cellpadding=15 cellspacing=0 border=0 width=720>
<td align=right width=240>
<form style="margin-bottom:0;" action="<?php echo $this->url_prefix ?>index.php/customer/whycancel" method=post>
<input name=whycancel type=submit value="<?php echo lang('yes') ?>" class=smallbutton></form></td>

<td align=left width=240>
<form style="margin-bottom:0;" action="<?php echo $this->url_prefix ?>index.php/customer" method=post>
<input name=done type=submit value="<?php echo lang('no') ?>" class=smallbutton>
</form></td>

<td align=left width=240>
<form style="margin-bottom:0;" action="<?php echo $this->url_prefix ?>index.php/customer/whycancel/now" method=post>
<input name=whycancel type=submit value="<?php echo lang('remove_now') ?>" class=smallbutton>
</form></td>

</table>