<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<html>
<body bgcolor="#ffffff">
<?php
<h3><?php echo lang('taxes')?></h3>

[ <a href="<?php echo $this->url_prefix?>/index.php/tools/admin/taxes"><?php echo lang('taxrates')?></a> ] 
[ <a href="<?php echo $this->url_prefix?>/index.php/tools/admin/taxedservices"><?php echo lang('taxedservices')?></a> ]
	
<p><b>$l_taxrates</b><p>


<table cellpadding=5 cellspacing=1><tr bgcolor="#eeeeee">
<td><b>$l_id</b></td> <td><b>$l_description</b></td> <td><b>$l_rate</b></td> <td><b>$l_iffield</b></td><td><b>$l_ifvalue</b></td><td><b>$l_percentage_or_fixed</b></td> <td></td>
</tr>

<?php
foreach ($tax_rates AS $myresult)
{
	$id = $myresult['id'];
        $desc = $myresult['description'];
        $rate = $myresult['rate'];
	$if_field = $myresult['if_field'];
	$if_value = $myresult['if_value'];
	$percentage_or_fixed = $myresult['percentage_or_fixed'];
	print "<tr bgcolor=\"#eeeeee\"><td>$id</td><td>$desc</td><td>$rate</td><td>$if_field</td><td>$if_value</td><td>$percentage_or_fixed</td>";
	print "<td><a href=\"index.php/tools/admin/deletetaxrate/$id\">$l_delete</a></td></tr>\n";
}
?>

echo "</table><p>
<b>$l_add:</b><br>
<FORM ACTION=\"index.php/tools/admin/addtaxrate\" METHOD=\"GET\">
$l_description: <input type=text name=\"description\"><br>
$l_rate: <input type=text name=\"rate\"><br>
$l_iffield <input type=text name=\"if_field\"><br>
$l_ifvalue <input type=text name=\"if_value\"><br>
$l_percentage_or_fixed
<select name=\"percentage_or_fixed\">
<option value=\"percentage\">percentage</option>
<option value=\"fixed\">fixed</option>
</select>
<br>
<input type=hidden name=load value=services>
<input type=hidden name=tooltype value=module>
<input type=hidden name=type value=tools>
<input type=hidden name=tax value=on>
<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_add\">
</FORM>
<p>";

}
?>

</body>
</html>
