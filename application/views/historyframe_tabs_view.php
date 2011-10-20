<p>
<table cellpadding=0 cellspacing=0 border=0 width=720 height=180>
<td>
<table cellpadding=5 cellspacing=0 border=0>
<td bgcolor="#eeeedd" width=100 align=center><b class="smalltabs">
<a href="<?=$this->url_prefix?>/index.php/customer/history/<?=$account_number?>" 
target="historyframe"><? echo lang('notes');?></a></b></a></td>
<td>&nbsp;</td>
<td bgcolor="#ddeeee" width=100 align=center><b class="smalltabs">
<a href="<?=$this->url_prefix?>/index.php/billing/billinghistory/<?=$account_number?>" 
target="historyframe"><? echo lang('billing')?></a></b></td>
<td>&nbsp;</td>
<td bgcolor="#eedddd" width=100 align=center><b class="smalltabs">
<a href="<?=$this->url_prefix?>/index.php/billing/paymenthistory/<?=$account_number?>" 
target="historyframe"><? echo lang('payments');?></a></b></td>
<td>&nbsp;</td>
<td bgcolor="#dddddd" width=100 align=center><b class="smalltabs">
<a href="<?=$this->url_prefix?>/index.php/billing/detailhistory/<?=$account_number?>" 
target="historyframe"><? echo lang('billing') . " " . lang('details');?></a></b></td>
</table>
</td><tr>
<td width="720" height="160" bgcolor="#eeeedd" valign=top>
	<table border=0 cellpadding=0 cellspacing=0><td>
<iframe name="historyframe" src="
<?php echo $this->url_prefix; ?>/index.php/customer/history/<?php echo $account_number;?>" 
width=720 height=200 frameborder=0 marginwidth=0 marginheight=1 scrolling=yes></iframe>
</td></table>
</td>
</table>
