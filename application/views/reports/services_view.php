<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<h3><?php echo lang('servicereport')?>:
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/reports/showservices" METHOD="POST">
<table>
<select name="service_id">

<?php
foreach ($listservices AS $myresult) 
{
  $id = $myresult['id'];
  $description = $myresult['service_description'];
  
  echo "<option value=\"$id\">$description</option>";
 
}
?>
</td><tr> 
<td></td><td><br><input type=submit name="<?php echo lang('submit')?>" value="submit">
</td>
</table>
</form> <p>
</body>
</html>







