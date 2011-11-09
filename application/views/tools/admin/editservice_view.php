<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>

<H3><?php echo lang('editservices')?></H3><P>

<h3><?php echo lang('organizationname')?>: <?php echo $org_name?></h3>

<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/admin/saveeditservice" METHOD="POST">
<B>$l_description</B><BR>
<INPUT TYPE=\"TEXT\" NAME=\"service_description\"
VALUE=\"$service_description\" MAXLENGTH=\"128\"><P>
<B>$l_price</B><BR>
<INPUT TYPE=\"TEXT\" NAME=\"pricerate\" VALUE=\"$pricerate\" SIZE=\"20\" MAXLENGTH=\"32\"><P>
<B>$l_frequency</B><BR>
<INPUT TYPE=\"TEXT\" NAME=\"frequency\" VALUE=\"$frequency\" SIZE=\"20\" MAXLENGTH=\"32\"><P>
<B>$l_optionstable</B><BR>
<INPUT TYPE=\"TEXT\" NAME=\"options_table\" VALUE=\"$options_table\" SIZE=\"20\" MAXLENGTH=\"32\"><P>
<B>$l_category</B><BR>
<INPUT TYPE=\"TEXT\" NAME=\"category\" VALUE=\"$category\" SIZE=\"20\" MAXLENGTH=\"32\"><P>";

echo "<B>$l_sellingactive</B>";
if ($selling_active == 'y')
{
	echo "<input type=\"radio\" name=selling_active value=\"y\" checked>$l_yes<input type=\"radio\" name=selling_active value=\"n\">$l_no<p>";
} else {
	echo "<input type=\"radio\" name=selling_active value=\"y\">$l_yes<input type=\"radio\" name=selling_active value=\"n\" checked>$l_no<p>";
}

echo "<B>$l_hideonline</B>";

if ($hide_online == 'y')
{
	echo "<input type=\"radio\" name=hide_online value=\"y\" checked>$l_yes
		<input type=\"radio\" name=hide_online value=\"n\">$l_no<p>";
} else {
	echo "<input type=\"radio\" name=hide_online value=\"y\">$l_yes
		<input type=\"radio\" name=hide_online value=\"n\" checked>$l_no<p>";
}

echo "<B>$l_activatenotify</B>         
<INPUT TYPE=\"text\" NAME=\"activate_notify\" VALUE=\"$activate_notify\"><P>
<B>$l_shutoffnotify</B>         
<INPUT TYPE=\"text\" NAME=\"shutoff_notify\" VALUE=\"$shutoff_notify\"><P>                                    
<B>$l_modifynotify</B>         
<INPUT TYPE=\"text\" NAME=\"modify_notify\" VALUE=\"$modify_notify\"><P>

<B>$l_supportnotify</B>         
<INPUT TYPE=\"text\" NAME=\"support_notify\" VALUE=\"$support_notify\"><P>

<b>$l_activationstring</b>
<input type=text name=activation_string value=\"$activation_string\">
<p>
<b>$l_usagelabel</b>
<input type=text name=usage_label value=\"$usage_label\">
<p>
<b>$l_carrierdependent</b>";


if ($carrier_dependent == 'y')
{
	echo "<input type=\"radio\" name=carrier_dependent value=\"y\" checked>$l_yes
		<input type=\"radio\" name=carrier_dependent value=\"n\">$l_no<p>";
} else {
	echo "<input type=\"radio\" name=carrier_dependent value=\"y\">$l_yes
		<input type=\"radio\" name=carrier_dependent value=\"n\" checked>$l_no<p>";
}

echo "<p>
<input type=hidden name=sid value=\"$sid\">
<input type=hidden name=load value=services>
<input type=hidden name=tooltype value=module>
<input type=hidden name=type value=tools>
<input type=hidden name=edit value=on>
<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_submit\">
</FORM>";


?>
