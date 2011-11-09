<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>

<H3><?php echo lang('editservices')?></H3><P>

<h3><?php echo lang('organizationname')?>: <?php echo $org_name?></h3>

<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/admin/saveeditservice" METHOD="POST">
<B><?php echo lang('description')?></B><BR>
<INPUT TYPE="TEXT" NAME="service_description"
VALUE="<?php echo $s['service_description']?>" MAXLENGTH="128"><P>
<B><?php echo lang('price')?></B><BR>
<INPUT TYPE="TEXT" NAME="pricerate" VALUE="<?php echo $s['pricerate']?>" SIZE="20" MAXLENGTH="32"><P>
<B><?php echo lang('frequency')?></B><BR>
<INPUT TYPE="TEXT" NAME="frequency" VALUE="<?php echo $s['frequency']?>" SIZE="20" MAXLENGTH="32"><P>
<B><?php echo lang('optionstable')?></B><BR>
<INPUT TYPE="TEXT" NAME="options_table" VALUE="<?php echo $s['options_table']?>" SIZE="20" MAXLENGTH="32"><P>
<B><?php echo lang('category')?></B><BR>
<INPUT TYPE="TEXT" NAME="category" VALUE="<?php echo $s['category']?>" SIZE="20" MAXLENGTH="32"><P>

<B><?php echo lang('sellingactive')?></B>
<?php
if ($s['selling_active'] == 'y')
{
	echo "<input type=\"radio\" name=selling_active value=\"y\" checked>".lang('yes')."<input type=\"radio\" name=selling_active value=\"n\">".lang('no')."<p>";
} else {
	echo "<input type=\"radio\" name=selling_active value=\"y\">".lang('yes')."<input type=\"radio\" name=selling_active value=\"n\" checked>".lang('no')."<p>";
}
?>
<B><?php echo lang('hideonline')?></B>
<?php
if ($s['hide_online'] == 'y')
{
	echo "<input type=\"radio\" name=hide_online value=\"y\" checked>".lang('yes')."
		<input type=\"radio\" name=hide_online value=\"n\">".lang('no')."<p>";
} else {
	echo "<input type=\"radio\" name=hide_online value=\"y\">".lang('yes')."
		<input type=\"radio\" name=hide_online value=\"n\" checked>".lang('no')."<p>";
}
?>

<B><?php echo lang('activatenotify')?></B>         
<INPUT TYPE="text" NAME="activate_notify" VALUE="<?php echo $s['activate_notify']?>"><P>
<B><?php echo lang('shutoffnotify')?></B>         
<INPUT TYPE="text" NAME="shutoff_notify" VALUE="<?php echo $s['shutoff_notify']?>"><P>                                    
<B><?php echo lang('modifynotify')?></B>         
<INPUT TYPE="text" NAME="modify_notify" VALUE="<?php echo $s['modify_notify']?>"><P>

<B><?php echo lang('supportnotify')?></B>         
<INPUT TYPE="text" NAME="support_notify" VALUE="<?php echo $s['support_notify']?>"><P>

<b><?php echo lang('activationstring')?></b>
<input type=text name=activation_string value="<?php echo $s['activation_string']?>">
<p>
<b><?php echo lang('usagelabel')?></b>
<input type=text name=usage_label value="<?php echo $s['usage_label']?>">
<p>
<b><?php echo lang('carrierdependent')?></b>

<?php
if ($s['carrier_dependent'] == 'y')
{
	echo "<input type=\"radio\" name=carrier_dependent value=\"y\" checked>".lang('yes')."
		<input type=\"radio\" name=carrier_dependent value=\"n\">".lang('no')."<p>";
} else {
	echo "<input type=\"radio\" name=carrier_dependent value=\"y\">".lang('yes')."
		<input type=\"radio\" name=carrier_dependent value=\"n\" checked>".lang('no')."<p>";
}
?>
<p>
<input type=hidden name=service_id value="<?php echo $service_id?>">
<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo lang('submit')?>">
</FORM>
