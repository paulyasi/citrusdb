
   <script language=javascript>
      function phoneformat() {
      if (document.phonesearch.s1.value.match(/^\d+$/)) {
	// all numbers, add dashes for s2	      
	document.phonesearch.s2.value = document.phonesearch.s1.value.slice(0,3)+"-"+document.phonesearch.s1.value.slice(3,6)+"-"+document.phonesearch.s1.value.slice(6,10);
      }
      else {
	// must have dashes in it, remove dashes for s2
	document.phonesearch.s2.value = document.phonesearch.s1.value.replace(/\-/g, "")
      }
    }

      function nameformat() {
	document.namesearch.s1.value = document.namesearch.s1.value.replace(/\s/g, "%")
      }
   </script>
      
<fieldset>
<legend><b><?php echo "$l_customer"; ?></b></legend>

<table width=500>   
<td valign=top>
<form ACTION="index.php?load=dosearch&type=fs" METHOD="POST" name="namesearch">
<?php echo "$l_name/$l_company"; ?></td><td><input type=text name=s1>
<input type=hidden name=id value=2> <!-- the id of this search in the searches table -->
<?php
echo "<input type=submit name=submit value=\"$l_search\" class=smallbutton onclick=\"nameformat();\">";
?>
</form>

</td><tr><td valign=top>
<form ACTION="index.php?load=dosearch&type=fs" METHOD="POST" name="phonesearch">
<?php echo "$l_phonenumber"; ?> </td><td>
<input type=text name=s1>
<input type=hidden name=s2>   
<input type=hidden name=id value=3> <!-- the id of this search in the searches table -->
<?php
echo "<input type=submit name=submit value=\"$l_search\" class=smallbutton onclick=\"phoneformat();\">";
?>
</form>


</td><tr><td valign=top>
<form ACTION="index.php?load=dosearch&type=fs" METHOD="POST">
<?php echo "$l_signupdaterange"; ?> </td>
<td><input type=text name=s1 size=9> <?php echo "$l_to"; ?> <input type=text name=s2 size=9><input type=hidden name=id value=4>
<?php
echo "<input type=submit name=submit value=\"$l_search\" class=smallbutton>";
?>
</form>


</td><tr><td valign=top>
<form ACTION="index.php?load=dosearch&type=fs" METHOD="POST">
<?php echo "$l_street"; ?> </td>
<td><input type=text name=s1 size=20><input type=hidden name=id value=12>
<?php
echo "<input type=submit name=submit value=\"$l_search\" class=smallbutton>";
?>
</form>

</td></table>
</fieldset>




