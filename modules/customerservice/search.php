<p>
<fieldset>
<legend><b><?php echo "$l_customer"; ?></b></legend>
<table>
<td valign=top>
<form ACTION="index.php?load=dosearch&type=fs" METHOD="POST">
<?php echo "$l_companyname"; ?></td><td><input type=text name=s1>
<input type=hidden name=id value=2> <!-- the id of this search in the searches table -->
<?php
echo "<input type=submit name=submit value=\"$l_search\" class=smallbutton>";
?>
</form>
</td><tr><td valign=top>
<form ACTION="index.php?load=dosearch&type=fs" METHOD="POST">
<?php echo "$l_customername";  ?></td><td><input type=text name=s1>
<input type=hidden name=id value=3> <!-- the id of this search in the searches table -->
<?php
echo "<input type=submit name=submit value=\"$l_search\" class=smallbutton>";
?>
</form>
</td><tr><td valign=top>
<form ACTION="index.php?load=dosearch&type=fs" METHOD="POST">
<?php echo "$l_phonenumber"; ?> </td><td><input type=text name=s1>
<input type=hidden name=id value=4> <!-- the id of this search in the searches table -->
<?php
echo "<input type=submit name=submit value=\"$l_search\" class=smallbutton>";
?>
</form>
</td></table>
</fieldset>




