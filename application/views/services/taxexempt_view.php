<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<h4><?php echo lang('exempt')?></h4>
<form method=post action="<?php echo $this->url_prefix?>/index.php/services/savetaxexempt">
<table width=720 cellpadding=5 cellspacing=1 border=0>
		
<input type=hidden name=taxrateid value="<?php echo $tax_rate_id?>">
		
<td bgcolor="ccccdd" width=180><b><?php echo lang('taxexemptid')?></b></td>
<td bgcolor="#ddddee"><input type=text name=customer_tax_id></td><tr>
		
<td bgcolor="ccccdd"width=180><b><?php echo lang('expirationdate')?></b></td>
<td bgcolor="#ddddee"><input type=text name=expdate></td><tr>
		
<td></td><td>
<input name=saveexempt type=submit value="<?php echo lang('savechanges')?>" class=smallbutton>
</td></table></form>
