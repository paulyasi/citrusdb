<div id="header">
	<div class="buttonbaritem">
		<img class="full-image" src="<?php echo $this->url_prefix;?>/images/my-logo.png">
	</div>
	<div class="buttonbaritem">
		<a href="<?php echo $this->url_prefix;?>/index.php/customer/newcustomer">
			<img src="<?php echo $this->url_prefix;?>/images/new-icon.png" alt="<?php echo lang('new')?>" border=0>
			<br>
			<?php echo lang('new')?>
		</a>
	</div>
	<div class="buttonbaritem">
		<a href="<?php echo $this->url_prefix;?>/index.php/dashboard">
		<img src="<?php echo $this->url_prefix;?>/images/search-icon.png" alt="<?php echo lang('search')?>" border=0>
			<br>
		<?php echo lang('search')?>
		</a>
	</div>
	<div class="buttonbaritem">
		<a href="<?php echo $this->url_prefix;?>/index.php/reports">
			<img src="<?php echo $this->url_prefix;?>/images/reports-icon.png" alt="<?php echo lang('reports')?>" border=0>
			<br>
			<?php echo lang('reports')?>
		</a>
	</div>
	<div class="buttonbaritem">
		<a href="<?php echo $this->url_prefix;?>/index.php/tools">
		<img src="<?php echo $this->url_prefix;?>/images/admin-icon.png" alt="<?php echo lang('tools');?>" border=0>
			<br>
		<?php echo lang('tools');?>
		</a>
	</div>
	<div class="buttonbaritem">
		<a href="<?php echo $this->url_prefix;?>/index.php/session/logout">
		<img src="<?php echo $this->url_prefix;?>/images/logout-icon.png" alt="<?php echo lang('logout'); echo " $this->user"; ?>" border=0>
			<br>
		<?php echo lang('logout'); echo " $this->user"; ?>
	</a>
	</div>
</div>
<div id="sidebar">

