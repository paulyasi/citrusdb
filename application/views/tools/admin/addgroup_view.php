<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<H3><?php echo lang('addnewgroup')?></H3>
<P>
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/admin/saveaddgroup" METHOD="POST">
<B><?php echo lang('addmember')?>:</B><BR>
<SELECT NAME="membername">

<?php	
foreach ($users AS $u)
{
	echo "<option>".$u['username']."</option>";
}	
?>

</SELECT>
<P>
<B><?php echo lang('togroupnamed')?>:</B><BR>
<INPUT TYPE="TEXT" NAME="groupname" VALUE="" SIZE="20" MAXLENGTH="32">
<P>
<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo lang('add')?>">
</FORM>
