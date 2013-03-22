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
<legend><b><?php echo lang('customer'); ?></b></legend>

<table>   
<td valign=top>
<form ACTION="<?php echo $this->url_prefix;?>/index.php/search/results/1/20" METHOD="POST" name="namesearch">
<?php echo lang('name') . "/"; echo lang('company'); ?> &nbsp;
<input type=text name=s1>
<input type=hidden name=id value=2> <!-- the id of this search in the searches table -->
<input type=submit name=submit value="<?php echo lang('search');?>" 
class=smallbutton onclick="nameformat();">
</form>

</td><tr><td valign=top>
<form ACTION="<?php echo $this->url_prefix;?>/index.php/search/results/1/20" 
METHOD="POST" name="phonesearch">
<?php echo lang('phonenumber');?> &nbsp;
<input type=text name=s1>
<input type=hidden name=s2>   
<input type=hidden name=id value=3> <!-- the id of this search in the searches table -->
<input type=submit name=submit value="<?php echo lang('search');?>" 
class=smallbutton onclick="phoneformat();">
</form>


</td><tr><td valign=top>
<form ACTION="<?php echo $this->url_prefix;?>/index.php/search/results/1/20" 
METHOD="POST">
<?php echo lang('signupdaterange'); ?> &nbsp;
<input type=text name=s1 size=5> <?php echo lang('to'); ?> 
<input type=text name=s2 size=5><input type=hidden name=id value=4>
<input type=submit name=submit value="<?php echo lang('search');?>" class=smallbutton>
</form>


</td><tr><td valign=top>
<form ACTION="<?php echo $this->url_prefix;?>/index.php/search/results/1/20" 
METHOD="POST">
<?php echo lang('street'); ?> 
<input type=text name=s1 size=20><input type=hidden name=id value=12>
<input type=submit name=submit value="<?php echo lang('search');?>" class=smallbutton>
</form>

</td></table>
</fieldset>




