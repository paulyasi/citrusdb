<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('invoicemaintenance')?></h3>

[ <a href="<?php echo $this->url_prefix?>/index.php/billing"><?php echo lang('back')?></a> ]

<p><b><?php echo lang('areyousureyouwanttoremoveinvoice') . "$invoicenum";?></b>
<table cellpadding=15 cellspacing=0 border=0 width=720><td align=right width=360>
<form style="margin-bottom:0;" action="<?php echo $this->url_prefix?>/index.php/billing/deleteinvoice" method=post>
<input type=hidden name=invoicenum value=$invoicenum>
<input name=deletenow type=submit value="<?php echo lang('yes')?>" class=smallbutton></form></td>
<td align=left width=360><form style="margin-bottom:0;" action="<?php echo $this->url_prefix?>/index.php/billing" method=post>
<input type=hidden name=type value=tools>
<input name=done type=submit value="<?php echo lang('no')?>" class=smallbutton>
<input type=hidden name=load value=invmaint>
</form></td></table>
</body>
</html>
