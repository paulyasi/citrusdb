<p>
<fieldset>
<legend><b><?php echo "$l_support"; ?></b></legend>
<table>
<td valign=top>
<form ACTION="index.php?load=dosearch&type=fs" METHOD="POST">
<?php echo "$l_ticketnumber:"; ?> </td><td><input type=text name=s1 size=32>
<input type=hidden name=id value=8> <!-- the id of this search in the searches table -->
<?php
echo "<input type=submit name=submit value=\"$l_search\" class=smallbutton>";
?>
</form>

</td><tr><td valign=top>
<form ACTION="index.php?load=dosearch&type=fs" METHOD="POST">
<?php echo "$l_creationdate:"; ?> </td><td><input type=text name=s1 size=9>
<input type=hidden name=id value=16> <!-- the id of this search in the searches table -->
<?php
echo "<input type=submit name=submit value=\"$l_search\" class=smallbutton>";
?>
</form>

</td><tr><td valign=top>
<form ACTION="index.php?load=dosearch&type=fs" METHOD="POST">
<?php echo "$l_createdby:"; ?> </td><td><input type=text name=s1 size=9>
<input type=hidden name=id value=17> <!-- the id of this search in the searches table -->
<?php
echo "<input type=submit name=submit value=\"$l_search\" class=smallbutton>";
?>
</form>

</td></table>
</fieldset>
