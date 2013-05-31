<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<H3><?php echo lang('addservice')?></H3>
<P>
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/admin/saveaddnewservice" METHOD="POST">

<b><?php echo lang('organizationname')?></b>
<select name="organization_id">
<option value=""><?php echo lang('choose')?></option>
<?php
// pick an organization that this service belongs to
foreach ($org_list as $myresult) {
	$myid = $myresult['id'];
	$myorg = $myresult['org_name'];
	echo "<option value=\"$myid\">$myorg</option>";
}
?>
</select><p>

<B><?php echo lang('description')?></B><BR>
<INPUT TYPE="TEXT" NAME="service_description" VALUE=""
SIZE="20" MAXLENGTH="128"><P>
<B><?php echo lang('price')?></B><BR>
<INPUT TYPE="TEXT" NAME="pricerate" VALUE="" SIZE="20" MAXLENGTH="32"><P>
<B><?php echo lang('frequency')?></B><BR>
<INPUT TYPE="TEXT" NAME="frequency" VALUE="" SIZE="20" MAXLENGTH="32"><P>
<B><?php echo lang('optionstables')?></B><BR>
<INPUT TYPE="TEXT" NAME="options_table" VALUE="" SIZE="20" MAXLENGTH="32"><P>
<B><?php echo lang('category')?></B><BR>
<INPUT TYPE="TEXT" NAME="category" VALUE="" SIZE="20" MAXLENGTH="32"><P>
<B><?php echo lang('sellingactive')?></B> 
<input type="radio" name=selling_active value="y" checked><?php echo lang('yes')?>
<input type="radio" name=selling_active value="n"><?php echo lang('no')?><p>
<B><?php echo lang('hideonline')?></B> 
<input type="radio" name=hide_online value="y"><?php echo lang('yes')?>
<input type="radio" name=hide_online value="n" checked><?php echo lang('no')?>
<p>
<B><?php echo lang('activatenotify')?></B>         
<INPUT TYPE="text" NAME="activate_notify" VALUE="">
<P>
<B><?php echo lang('shutoffnotify')?></B>         
<INPUT TYPE="text" NAME="shutoff_notify" VALUE="">
<P>

<B><?php echo lang('modifynotify')?></B>         
<INPUT TYPE="text" NAME="modify_notify" VALUE=""><P>
<B><?php echo lang('supportnotify')?></B>         
<INPUT TYPE="text" NAME="support_notify" VALUE=""><P>

<B><?php echo lang('activationstring')?></B>         
<INPUT TYPE="text" NAME="activation_string" VALUE=""><P>
<B><?php echo lang('usagelabel')?></B>         
<INPUT TYPE="text" NAME="usage_label" VALUE="">

<p>
<b><?php echo lang('carrierdependent')?></b>

<input type="radio" name=carrier_dependent value="y"><?php echo lang('yes')?>
<input type="radio" name=carrier_dependent value="n" checked><?php echo lang('no')?><p>

<p>
<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo lang('add')?>">
</FORM>


