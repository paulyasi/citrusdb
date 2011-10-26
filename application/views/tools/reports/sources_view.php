<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('sourcereport')?>: 

<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/reports/showsources" METHOD="POST">
<table>
<select name="category">

<?php
$emptyday1  = date("Y-m-d", mktime(0, 0, 0, date("m")-1  , date("d"), date("Y")));
$emptyday2  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));

foreach ($servicecategories AS $myresult) 
{
  $category = $myresult['category'];
  
  echo "<option value=\"$category\">$category</option>";
 
}
?>

</select>
<?php echo lang('from')?>: <input type=text name="day1" value="<?php echo $emptyday1?>"> - 
<?php echo lang('to')?>: <input type=text name="day2" value="<?php echo $emptyday2?>">
<input type=hidden name=type value=tools>
<input type=hidden name=load value=sourcereport>
</td><tr> 
<td></td><td><br><input type=submit name="<?php echo lang('submit')?>" value="submit"></td>
</table>
</form> <p>

</body>
</html>







