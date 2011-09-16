<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('invoicemaintenance')?></h3>

[ <a href="<?php echo $this->url_prefix?>/index.php/billing"><?php echo lang('back')?></a> ]

<FORM ACTION="index.php/billing/saveinvoiceduedate" METHOD="POST">
<input type=hidden name=invoicenum value="<?php echo $invoicenum?>">
<input type=hidden name=billingid value="<?php echo $billingid?>">
<table>
<td><?php echo lang('new') . " " .  lang('duedate');?>:
</td><td><input type=text name=duedate value="<?php echo $duedate?>"></td><tr>
<td></td><td><INPUT TYPE="SUBMIT" NAME="submit" value="<?php echo lang('submitrequest');?>"></td>
</form>
</body>
</html>
