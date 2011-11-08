<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<H3><?php echo lang('addservice')?></H3>
<P>
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/admin/saveaddnewservice" METHOD="POST">

<b><?php echo lang('organizationname')?></b>
<select name="organization_id">
<option value=""><?php echo lang('choose')?></option>
<?php
// pick an organization that this service belongs to
foreach ($orglist as $myresult) {
	$myid = $myresult['id'];
	$myorg = $myresult['org_name'];
	echo "<option value=\"$myid\">$myorg</option>";
}
?>
</select><p>

<B><?php echo lang('description')?></B><BR>
<INPUT TYPE="TEXT" NAME="service_description" VALUE=""
SIZE="20" MAXLENGTH="128"><P>
<B>$l_price</B><BR>
<INPUT TYPE=\"TEXT\" NAME=\"pricerate\" VALUE=\"\" SIZE=\"20\" MAXLENGTH=\"32\"><P>
<B>$l_frequency</B><BR>
<INPUT TYPE=\"TEXT\" NAME=\"frequency\" VALUE=\"\" SIZE=\"20\" MAXLENGTH=\"32\"><P>
<B>$l_optionstables</B><BR>
<INPUT TYPE=\"TEXT\" NAME=\"options_table\" VALUE=\"\" SIZE=\"20\" MAXLENGTH=\"32\"><P>
<B>$l_category</B><BR>
<INPUT TYPE=\"TEXT\" NAME=\"category\" VALUE=\"\" SIZE=\"20\" MAXLENGTH=\"32\"><P>
<B>$l_sellingactive</B> 
<input type=\"radio\" name=selling_active value=\"y\" checked>$l_yes<input type=\"radio\" name=selling_active value=\"n\">$l_no<p>
<B>$l_hideonline</B> 
<input type=\"radio\" name=hide_online value=\"y\">$l_yes<input
type=\"radio\" name=hide_online value=\"n\" checked>$l_no<p>
<B>$l_activatenotify</B>         
<INPUT TYPE=\"text\" NAME=\"activate_notify\" VALUE=\"\"><P>
<B>$l_shutoffnotify</B>         
<INPUT TYPE=\"text\" NAME=\"shutoff_notify\" VALUE=\"\"><P>

<B>$l_modifynotify</B>         
<INPUT TYPE=\"text\" NAME=\"modify_notify\" VALUE=\"\"><P>
<B>$l_supportnotify</B>         
<INPUT TYPE=\"text\" NAME=\"support_notify\" VALUE=\"\"><P>

<B>$l_activationstring</B>         
<INPUT TYPE=\"text\" NAME=\"activation_string\" VALUE=\"\"><P>
<B>$l_usagelabel</B>         
<INPUT TYPE=\"text\" NAME=\"usage_label\" VALUE=\"\">";

echo "<p>
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
<input type=hidden name=load value=services>
<input type=hidden name=tooltype value=module>
<input type=hidden name=type value=tools>
<input type=hidden name=new value=on>
<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_add\">
</FORM>";


?>



