<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('settings')?></h3>


<?php echo lang('databaseversion')?>: <?php echo $set['version']?><br>
<?php echo lang('softwareversion')?>: <?php echo $this->softwareversion?><br>
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/admin/savesettings" METHOD="POST">
<table><td>
<B><?php echo lang('pathtocreditcardfile')?></B></td><td>
<INPUT TYPE="TEXT" NAME="path_to_ccfile" VALUE="<?php echo $set['path_to_ccfile']?>" SIZE="50" MAXLENGTH=128">
</td><tr><td>
<B><?php echo lang('defaultgroup')?></B></td><td>
<INPUT TYPE="TEXT" NAME="default_group" VALUE="<?php echo $set['default_group']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B><?php echo lang('defaultbillinggroup')?></B></td><td>
<INPUT TYPE="TEXT" NAME="default_billing_group" VALUE="<?php echo $set['default_billing_group']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B><?php echo lang('defaultshippinggroup')?></B></td><td>
<INPUT TYPE="TEXT" NAME="default_shipping_group" VALUE="<?php echo $set['default_shipping_group']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B><?php echo lang('carrierdependentcancelurl')?></B></td><td>
<INPUT TYPE="TEXT" NAME="dependent_cancel_url" VALUE="<?php echo $set['dependent_cancel_url']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td>
<B><?php echo lang('billingdaterollovertime')?>:</B></td><td>
<INPUT TYPE="TEXT" NAME="billingdate_rollover_time" VALUE="<?php echo $set['billingdate_rollover_time']?>" SIZE="20" MAXLENGTH="32">
</td><tr><td valign=top>	
<B><?php echo lang('billingweekend_sunday')?></B></td><td>
<INPUT TYPE="radio" NAME="billingweekend_sunday" VALUE="y" 
<?php if ($set['billingweekend_sunday'] == 'y') { echo "checked"; } ?>> <?php echo lang('yes')?> 
<INPUT TYPE="radio" NAME="billingweekend_sunday" VALUE="n" 
<?php if ($set['billingweekend_sunday'] == 'n') { echo "checked"; } ?>> <?php echo lang('no')?> 
</td><tr><td>

<B><?php echo lang('billingweekend_monday')?></B></td><td>
<INPUT TYPE="radio" NAME="billingweekend_monday" VALUE="y" 
<?php if ($set['billingweekend_monday'] == 'y') { echo "checked"; } ?>> <?php echo lang('yes')?> 
<INPUT TYPE="radio" NAME="billingweekend_monday" VALUE="n" 
<?php if ($set['billingweekend_monday'] == 'n') { echo "checked"; } ?>> <?php echo lang('no')?> 
</td><tr><td>

<B><?php echo lang('billingweekend_tuesday')?></B></td><td>
<INPUT TYPE="radio" NAME="billingweekend_tuesday" VALUE="y" 
<?php if ($set['billingweekend_tuesday'] == 'y') { echo "checked"; } ?>> <?php echo lang('yes')?> 
<INPUT TYPE="radio" NAME="billingweekend_tuesday" VALUE="n" 
<?php if ($set['billingweekend_tuesday'] == 'n') { echo "checked"; } ?>> <?php echo lang('no')?> 
</td><tr><td>

<B><?php echo lang('billingweekend_wednesday')?></B></td><td>
<INPUT TYPE="radio" NAME="billingweekend_wednesday" VALUE="y" 
<?php if ($set['billingweekend_wednesday'] == 'y') { echo "checked"; } ?>> <?php echo lang('yes')?> 
<INPUT TYPE="radio" NAME="billingweekend_wednesday" VALUE="n" 
<?php if ($set['billingweekend_wednesday'] == 'n') { echo "checked"; } ?>> <?php echo lang('no')?>  
</td><tr><td>

<B><?php echo lang('billingweekend_thursday')?></B></td><td>
<INPUT TYPE="radio" NAME="billingweekend_thursday" VALUE="y" 
<?php if ($set['billingweekend_thursday'] == 'y') { echo "checked"; } ?>> <?php echo lang('yes')?> 
<INPUT TYPE="radio" NAME="billingweekend_thursday" VALUE="n" 
<?php if ($set['billingweekend_thursday'] == 'n') { echo "checked"; } ?>> <?php echo lang('no')?> 
</td><tr><td>

<B><?php echo lang('billingweekend_friday')?></B></td><td>
<INPUT TYPE="radio" NAME="billingweekend_friday" VALUE="y" 
<?php if ($set['billingweekend_friday'] == 'y') { echo "checked"; } ?>> <?php echo lang('yes')?> 
<INPUT TYPE="radio" NAME="billingweekend_friday" VALUE="n" 
<?php if ($set['billingweekend_friday'] == 'n') { echo "checked"; } ?>> <?php echo lang('no')?> 
</td><tr><td>

<B><?php echo lang('billingweekend_saturday')?></B></td><td>
<INPUT TYPE="radio" NAME="billingweekend_saturday" VALUE="y" 
<?php if ($set['billingweekend_saturday'] == 'y') { echo "checked"; } ?>> <?php echo lang('yes')?> 
<INPUT TYPE="radio" NAME="billingweekend_saturday" VALUE="n" 
<?php if ($set['billingweekend_saturday'] == 'n') { echo "checked"; } ?>> <?php echo lang('no')?> 
</td><tr><td>



</td><td>
<INPUT TYPE="SUBMIT" NAME="submit" value="<?php echo lang('savechanges')?>">
</FORM>

</body>
</html>
