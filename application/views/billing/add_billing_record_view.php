<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<a href="<?php echo $this->url_prefix;?>/index.php/billing">[ <?php echo lang('undochanges');?> ]</a>
<p>
<?php echo lang('areyousureadd') . " " . $account_number; ?>
<p>
<form style="margin-bottom:0;" action="<?php echo $this->url_prefix?>/index.php/billing/create" name="form1" method=post>

<?php								  
// print list of organizations to choose from
$query = "SELECT id,org_name FROM general";
$result = $this->db->query($query) or die ("queryfailed");
echo "<b>" . lang('organizationname') . "</b> <select name=\"organization_id\">";
foreach ($result->result_array() as $myresult)
{
	$myid = $myresult['id'];
	$myorg = $myresult['org_name'];
	echo "<option value=\"$myid\">$myorg</option>";
}
?>
</select>&nbsp;&nbsp;
<input name=addnow type=submit value="<?php echo lang('addbilling');?>" class=smallbutton>
</form>

