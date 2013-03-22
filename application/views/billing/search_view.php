<p>
<fieldset>
<legend><b><?php echo lang('billing'); ?></b></legend>
<table>
<td valign=top>
<form ACTION="<?php echo $this->url_prefix;?>/index.php/search/results/1/20" 
METHOD="POST">
<?php echo lang('billingid'); ?> &nbsp;<input type=text name=s1>
<input type=hidden name=id value=5> <!-- the id of this search in the searches table -->
<input type=submit name=submit value="<?php echo lang('search');?>" class=smallbutton>
</form>

</td><tr><td valign=top>
<form ACTION="<?php echo $this->url_prefix;?>/index.php/search/results/1/20" 
METHOD="POST">
<?php echo lang('billingcompany'); ?>&nbsp;<input type=text name=s1>
<input type=hidden name=id value=6> <!-- the id of this search in the searches table -->
<input type=submit name=submit value=<?php echo lang('search');?> class=smallbutton>
</form>
</td><tr><td valign=top>
<form ACTION="<?php echo $this->url_prefix;?>/index.php/search/results/1/20" 
METHOD="POST">
<?php echo lang('paymentduedate');?>&nbsp;<input type=text name=s1>
<input type=hidden name=id value=7> <!-- the id of this search in the searches table -->
<input type=submit name=submit value=<?php echo lang('search');?> class=smallbutton>
</form>

</td><tr><td valign=top>
<form ACTION="<?php echo $this->url_prefix;?>/index.php/search/results/1/20" 
METHOD="POST">
<?php echo lang('invoicenumber'); ?>&nbsp;<input type=text name=s1>
<input type=hidden name=id value=13> <!-- the id of this search in the searches table -->
<input type=submit name=submit value=<?php echo lang('search');?> class=smallbutton>
</form>

</td><tr><td valign=top>
<form ACTION="<?php echo $this->url_prefix;?>/index.php/search/results/1/20" 
METHOD="POST">
<?php echo lang('contactemail'); ?>&nbsp;<input type=text name=s1>
<input type=hidden name=id value=14> <!-- the id of this search in the searches table -->
<input type=submit name=submit value=<?php echo lang('search');?> class=smallbutton>
</form>

</td><tr><td valign=top>
<form ACTION="<?php echo $this->url_prefix;?>/index.php/search/results/1/20" 
METHOD="POST">
<?php echo lang('nextbillingdate'); ?>&nbsp;<input type=text name=s1 size=5><?php echo lang('to'); ?><input type=text name=s2 size=5>
   <input type=hidden name=id value=15>
   <!-- the id of this search in the searches table -->
<input type=submit name=submit value=<?php echo lang('search');?> class=smallbutton>
</form>

</td></table>
</fieldset>
