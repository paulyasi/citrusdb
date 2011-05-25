<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// prompt for some standard information to put in the new customer record
?>
<a href="<?php echo $this->url_prefix?>/index.php/dashboard">
[ <?php echo lang('undochanges'); ?>]</a>
<table cellpadding=0 border=0 cellspacing=0 width=720>
<td valign=top width=720>
<form action="index.php/customer/create" method=POST>
<table cellpadding=5 cellspacing=1 border=0 width=720>

<?php
// print list of organizations to choose from
$query = "SELECT id,org_name FROM general";
$result = $this->db->query($query) or die ("query failed");

echo "<td bgcolor=\"#ccccdd\"><b>". lang('organizationname') ."</b></td>".
"<td bgcolor=\"#ddddee\"><select name=\"organization_id\">";

foreach($result->result() as $myresult)
{
	$myid = $myresult->id;
	$myorg = $myresult->org_name;
	if ($myid == $billedby) 
	{
		echo "<option value=\"$myid\" selected>$myorg</option>";
	} 
	else 
	{
		echo "<option value=\"$myid\">$myorg</option>";
	}
}
?>

</select></td><tr>

<td bgcolor="#ccccdd"><b><?php echo lang('name');?></b></td><td bgcolor="#ddddee">
<input name="name" type=text></td><tr>
<td bgcolor="#ccccdd"><b><?php echo lang('company');?></b></td><td bgcolor="#ddddee">
<input name="company" type=text></td><tr>
<td bgcolor="#ccccdd"><b><?php echo lang('street');?></b></td><td bgcolor="#ddddee">
<input name="street" type=text></td><tr>
<td bgcolor="#ccccdd"><b><?php echo lang('city');?></b></td><td bgcolor="#ddddee">
<input name="city" type=text></td><tr>
<td bgcolor="#ccccdd"><b><?php echo lang('state');?></b></td><td bgcolor="#ddddee">
<input name="state" type=text size=2></td><tr>
<td bgcolor="#ccccdd"><b><?php echo lang('zip');?></b></td><td bgcolor="#ddddee">
<input name="zip" size=5 type=text></td><tr>
<td bgcolor="#ccccdd"><b><?php echo lang('country');?></b></td><td bgcolor="#ddddee">
<input name="country" type=text></td><tr>
<td bgcolor="#ccccdd"><b><?php echo lang('phone');?></b></td><td bgcolor="#ddddee">
<input name="phone" type=text></td><tr>
<td bgcolor="#ccccdd"><b><?php echo lang('fax');?></b></td><td bgcolor="#ddddee">
<input name="fax" type=text></td><tr>
<td bgcolor="#ccccdd"><b><?php echo lang('contactemail');?></b></td><td bgcolor="#ddddee">
<input name="contact_email" type=text></td><tr>
<td bgcolor="#ccccdd"><b><?php echo lang('secret_question');?></b></td><td bgcolor="#ddddee">
<input name="secret_question" type=text></td><tr>
<td bgcolor="#ccccdd"><b><?php echo lang('secret_answer');?></b></td><td bgcolor="#ddddee">
<input name="secret_answer" type=text></td><tr>
<td bgcolor="#ccccdd"><b><?php echo lang('source');?></b></td><td bgcolor="#ddddee">
<input name="source" type=text></td><tr>
</table>
<br />
<center>
<input name=save type=submit class=smallbutton value="<?php echo lang('add');?>">
</center>
</td>
</table>
</form>
