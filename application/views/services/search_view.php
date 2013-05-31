<p>
<fieldset>
<legend><b><?php echo lang('services') ?></b></legend>
<table>
<td valign=top>
<form ACTION="<?php echo $this->url_prefix;?>/index.php/search/results/1/20" 
METHOD="POST">
<?php echo lang('exampleusername') ?>&nbsp;<input type=text name=s1>
<input type=hidden name=id value=9> <!-- the id of this search in the searches table -->
<input type=submit name=submit value=<?php echo lang('search');?> class=smallbutton>
</form>
</td><tr>
<td valign=top>
<form ACTION="<?php echo $this->url_prefix;?>/index.php/search/results/1/20" 
METHOD="POST">
<?php echo lang('examplepassword'); ?>&nbsp;<input type=text name=s1>
<input type=hidden name=id value=10> <!-- the id of this search in the searches table -->
<input type=submit name=submit value=<?php echo lang('search');?> class=smallbutton>
</form>
</td><tr>
<td valign=top>
<form ACTION="<?php echo $this->url_prefix;?>/index.php/search/results/1/20" 
METHOD="POST">
<?php echo lang('exampleequipment'); ?>&nbsp;<input type=text name=s1>
<input type=hidden name=id value=11> <!-- the id of this search in the searches table -->
<input type=submit name=submit value=<?php echo lang('search');?> class=smallbutton>
</form>
</td></table>
</fieldset>


