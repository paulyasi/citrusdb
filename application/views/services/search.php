<p>
<fieldset>
<legend><b><?php echo "$l_services"; ?></b></legend>
<table>
<td valign=top>
<form ACTION="index.php?load=dosearch&type=fs" METHOD="POST">
<?php echo "$l_exampleusername:"; ?></td><td><input type=text name=s1 size=32>
<input type=hidden name=id value=9> <!-- the id of this search in the searches table -->
<?php
echo "<input type=submit name=submit value=\"$l_search\" class=smallbutton>";
?>
</form>
</td><tr>
<td valign=top>
<form ACTION="index.php?load=dosearch&type=fs" METHOD="POST">
<?php echo "$l_examplepassword:"; ?> </td><td><input type=text name=s1 size=32>
<input type=hidden name=id value=10> <!-- the id of this search in the searches table -->
<?php
echo "<input type=submit name=submit value=\"$l_search\" class=smallbutton>";
?>
</form>
</td><tr>
<td valign=top>
<form ACTION="index.php?load=dosearch&type=fs" METHOD="POST">
<?php echo "$l_exampleequipment:"; ?> </td><td><input type=text name=s1 size=32>
<input type=hidden name=id value=11> <!-- the id of this search in the searches table -->
<?php
echo "<input type=submit name=submit value=\"$l_search\" class=smallbutton>";
?>
</form>
</td></table>
</fieldset>


