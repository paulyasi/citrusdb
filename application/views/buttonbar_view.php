<div id="header" style="background-color: #000; width: 100%;">
	<div class="buttonbaritem" style="padding: 3px;"><img height=20 src="images/my-logo.png"></div>
	<div class="buttonbaritem">
	<a href="<?php echo $this->url_prefix;?>/index.php/customer/newcustomer">
		<table><td>
		<img src="<?php echo $this->url_prefix;?>/images/new-icon.png" alt="<?php echo lang('new')?>" border=0></td>
		<td style="color: #eee;"><?php echo lang('new')?>
		</td></table>
	</a>
	</div>
	<div class="buttonbaritem">
	<a href="<?php echo $this->url_prefix;?>/index.php/dashboard">
		<table><td>
		<img src="<?php echo $this->url_prefix;?>/images/search-icon.png" alt="<?php echo lang('search')?>" border=0></td>
		<td style="color: #eee;"><?php echo lang('search')?>
		</td></table>
	</a>
	</div>
	<div class="buttonbaritem">
	<a href="<?php echo $this->url_prefix;?>/index.php/reports">
		<table><td>
		<img src="<?php echo $this->url_prefix;?>/images/reports-icon.png" alt="<?php echo lang('reports')?>" border=0></td>
		<td style="color: #eee;"><?php echo lang('reports')?>
		</td></table>
	</a>
	</div>
	<div class="buttonbaritem">
	<a href="<?php echo $this->url_prefix;?>/index.php/tools">
		<table><td>
		<img src="<?php echo $this->url_prefix;?>/images/admin-icon.png" alt="<?php echo lang('tools');?>" border=0></td>
		<td style="color: #eee;"><?php echo lang('tools');?>
		</td></table>
	</a>
	</div>
	<div class="buttonbaritem">
	<a href="<?php echo $this->url_prefix;?>/index.php/session/logout">
		<table><td>
		<img src="<?php echo $this->url_prefix;?>/images/logout-icon.png" alt="<?php echo lang('logout'); echo " $this->user"; ?>" border=0></td>
		<td style="color: #eee;"><?php echo lang('logout'); echo " $this->user"; ?>
		</td></table>
	</a>
	</div>
</div>
<div id="sidebar">

