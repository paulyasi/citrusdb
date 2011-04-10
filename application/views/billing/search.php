<p>
<fieldset>
<legend><b><?php echo "$l_billing"; ?></b></legend>
<table>
<td valign=top>
<form ACTION="index.php?load=dosearch&type=fs" METHOD="POST">
<?php echo "$l_billingid:"; ?> </td><td><input type=text name=s1>
<input type=hidden name=id value=5> <!-- the id of this search in the searches table -->
<?php
echo "<input type=submit name=submit value=\"$l_search\" class=smallbutton>";
?>
</form>
</td><tr><td valign=top>
<form ACTION="index.php?load=dosearch&type=fs" METHOD="POST">
<?php echo "$l_billingcompany:"; ?></td><td><input type=text name=s1>
<input type=hidden name=id value=6> <!-- the id of this search in the searches table -->
<?php
echo "<input type=submit name=submit value=\"$l_search\" class=smallbutton>";
?>
</form>
</td><tr><td valign=top>
<form ACTION="index.php?load=dosearch&type=fs" METHOD="POST">
<?php echo "$l_paymentduedate:"; ?></td><td><input type=text name=s1>
<input type=hidden name=id value=7> <!-- the id of this search in the searches table -->
<?php
echo "<input type=submit name=submit value=\"$l_search\" class=smallbutton>";
?>
</form>

</td><tr><td valign=top>
<form ACTION="index.php?load=dosearch&type=fs" METHOD="POST">
<?php echo "$l_invoicenumber:"; ?></td><td><input type=text name=s1>
<input type=hidden name=id value=13> <!-- the id of this search in the searches table -->
<?php
echo "<input type=submit name=submit value=\"$l_search\" class=smallbutton>";
?>
</form>

</td><tr><td valign=top>
<form ACTION="index.php?load=dosearch&type=fs" METHOD="POST">
<?php echo "$l_contactemail:"; ?></td><td><input type=text name=s1>
<input type=hidden name=id value=14> <!-- the id of this search in the searches table -->
<?php
echo "<input type=submit name=submit value=\"$l_search\" class=smallbutton>";
?>
</form>

</td><tr><td valign=top>
<form ACTION="index.php?load=dosearch&type=fs" METHOD="POST">
<?php echo "$l_nextbillingdate:"; ?></td>
<td><input type=text name=s1 size=9><?php echo " $l_to "; ?><input type=text name=s2 size=9>
   <input type=hidden name=id value=15>
   <!-- the id of this search in the searches table -->
<?php
echo "<input type=submit name=submit value=\"$l_search\" class=smallbutton>";
?>
</form>

</td></table>
</fieldset>
