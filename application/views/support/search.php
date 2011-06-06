<p>
<fieldset>
<legend><b><?php echo lang('support'); ?></b></legend>
<table>
<td valign=top>
<form ACTION="<?php echo $this->url_prefix;?>index.php/search/listresults/1/20" 
METHOD="POST">
<?php echo lang('ticketnumber'); ?> </td><td><input type=text name=s1 size=32>
<input type=hidden name=id value=8> <!-- the id of this search in the searches table -->
<input type=submit name=submit value="<?php echo lang ('search');?>" class=smallbutton>
</form>

</td><tr><td valign=top>
<form ACTION="<?php echo $this->url_prefix;?>index.php/search/listresults/1/20" 
METHOD="POST">
<?php echo lang('creationdate'); ?> </td><td><input type=text name=s1 size=9>
<input type=hidden name=id value=16> <!-- the id of this search in the searches table -->
<input type=submit name=submit value="<?php echo lang('search')?>" class=smallbutton>
</form>

</td><tr><td valign=top>
<form ACTION="<?php echo $this->url_prefix;?>index.php/search/listresults/1/20" 
METHOD="POST">
<?php echo lang('createdby'); ?> </td><td><input type=text name=s1 size=9>
<input type=hidden name=id value=17> <!-- the id of this search in the searches table -->
<input type=submit name=submit value="<?php lang('search');?>" class=smallbutton>
</form>

</td></table>
</fieldset>
