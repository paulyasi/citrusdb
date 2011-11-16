<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<h3><?php echo lang('shipfieldassets')?></h3>

<p><b><?php echo $description?>:</b></p>


<form style="margin-bottom:0;" 
action="<?php echo $this->url_prefix?>/index.php/services/assignfieldasset" method=post>
<table width=720 cellpadding=5 cellspacing=1 border=0>
<input type=hidden name=userserviceid value="<?php echo $userserviceid?>">
<input type=hidden name=master_field_assets_id value="<?php echo $master_field_assets_id?>">

<table>

<td><label><?php echo lang('serialnumber')?>: </td>
<td><input type=text name=serial_number></label></td><tr>

<td><?php echo lang('saletype')?>: </td><td><select name=sale_type>
<option value=included>included</option>
<option value=purchase>purchase</option>
<option value=rent>rent</option>
</select></td><tr>

<td><label><?php echo lang('trackingnumber')?>: </td>
<td><input type=text name=tracking_number></label></td><tr>

<?php $mydate = date("Y-m-d"); ?>
<td><label><?php echo lang('shippingdate')?>: </td>
<td><input type=text name=shipping_date value="<?php echo $mydate?>"></label></td><tr>

<td></td><td><input name=fieldassets type=submit value="<?php echo lang('assign')?>"
class=smallbutton></td></table></form><p>

