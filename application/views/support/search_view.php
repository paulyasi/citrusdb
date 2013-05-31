<p>
<fieldset>
<legend><b><?php echo lang('support'); ?></b></legend>
<table>
<td valign=top>
<form ACTION="<?php echo $this->url_prefix;?>index.php/search/results/1/20" 
METHOD="POST">
<?php echo lang('ticketnumber'); ?> &nbsp;<input type=text name=s1>
<input type=hidden name=id value=8> <!-- the id of this search in the searches table -->
<input type=submit name=submit value="<?php echo lang ('search');?>" class=smallbutton>
</form>

</td><tr><td valign=top>
<form ACTION="<?php echo $this->url_prefix;?>index.php/search/results/1/20" 
METHOD="POST">
<?php echo lang('creationdate'); ?> &nbsp;<input type=text name=s1 size=5>
<input type=hidden name=id value=16> <!-- the id of this search in the searches table -->
<input type=submit name=submit value="<?php echo lang('search')?>" class=smallbutton>
</form>

</td><tr><td valign=top>
<form ACTION="<?php echo $this->url_prefix;?>index.php/search/results/1/20" 
METHOD="POST">
<?php echo lang('createdby'); ?> &nbsp;<input type=text name=s1 size=5>
<input type=hidden name=id value=17> <!-- the id of this search in the searches table -->
<input type=submit name=submit value="<?php echo lang('search');?>" class=smallbutton>
</form>

</td></table>
</fieldset>
