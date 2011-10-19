<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('customersummary')?></h3>
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/reports/summary/view" 
METHOD="POST" name="form1">

<td><b><?php echo lang('organizationname')?></b></td>
<td><select name="organization_id">
<option value=""><?php echo lang('choose')?></option>
<?php
foreach ($orglist as $myresult) {
	$myid = $myresult['id'];
	$myorg = $myresult['org_name'];
	echo "<option value=\"$myid\">$myorg</option>";
}
?>
</select></td><tr>

<input type="SUBMIT" NAME="submit" value="<?php echo lang('submit')?>"><p>

<a href="<?php echo $this->url_prefix?>/index.php/tools/reports/summary/file/<?php echo $organization_id?>">
<u class="bluelink"><?php echo lang('download')?></u></a><p>

<table cellpadding=2><td><b><?php echo lang('services')?></b></td>
<td><b>Frequency</b></td>
<td><b>Category</b></td>
<td><b>Customers</b></td>
<td><b>Service Cost</b></td>
<td><b>Monthly <?php echo lang('total')?></b></td><tr>
<?php echo $service_listing?>
<?php echo $tax_listing?>
<?php echo $listing_footer?>
</table>

<?php print lang('paidsubscriptions').": $paidsubscriptions<p>"; ?>
<?php echo $billing_methods?>
		
<p><b>Service Category Totals: </b><br><i style="font-size: 8pt;">
total monthly services in each category, includes declined and 
turned off pending payment, does not include canceled services</i>
</p><blockquote>
<?php echo $service_categories?>
</blockquote>
<hr>
<?php print lang('totalpayingcustomers').": $totalpayingcustomers<p>"; ?>
<?php print lang('totalcustomers').": $totalcustomers<p>"; ?>

</body>
</html>







