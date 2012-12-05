<div id="header" style="background-color: #000; width: 100%;">
	<div class="buttonbaritem" style="padding-top: 3px; color: white; font-family: DejaVu Sans, Droid Sans, sans-serif; font-size: large;">
		<img height=20 src="<?php echo $this->url_prefix;?>/images/citrus_wedge.png">citrusdb
	</div>
	<div class="buttonbaritem">
		<a href="<?php echo $this->url_prefix;?>/index.php/customer/newcustomer">
		<div style="float: left;">
			<img src="<?php echo $this->url_prefix;?>/images/new-icon.png" alt="<?php echo lang('new')?>" border=0>
		</div>
		<div style="float: left;">
			<?php echo lang('new')?>
		</div>
		</a>
	</div>
	<div class="buttonbaritem">
		<a href="<?php echo $this->url_prefix;?>/index.php/dashboard">
		<div style="float: left;">
		<img src="<?php echo $this->url_prefix;?>/images/search-icon.png" alt="<?php echo lang('search')?>" border=0>
		</div>
		<div style="float: left;">
		<?php echo lang('search')?>
		</div>
		</a>
	</div>
	<div class="buttonbaritem">
		<a href="<?php echo $this->url_prefix;?>/index.php/reports">
		<div style="float: left;">
			<img src="<?php echo $this->url_prefix;?>/images/reports-icon.png" alt="<?php echo lang('reports')?>" border=0>
		</div>
		<div style="float: left;">
			<?php echo lang('reports')?>
		</div>
		</a>
	</div>
	<div class="buttonbaritem">
		<a href="<?php echo $this->url_prefix;?>/index.php/tools">
		<div style="float: left;">
		<img src="<?php echo $this->url_prefix;?>/images/admin-icon.png" alt="<?php echo lang('tools');?>" border=0>
		</div>
		<div style="float: left;">
		<?php echo lang('tools');?>
		</div>
		</a>
	</div>
	<div class="buttonbaritem">
		<a href="<?php echo $this->url_prefix;?>/index.php/session/logout">
		<div style="float: left;">
		<img src="<?php echo $this->url_prefix;?>/images/logout-icon.png" alt="<?php echo lang('logout'); echo " $this->user"; ?>" border=0>
		</div>
		<div style="float: left;">
		<?php echo lang('logout'); echo " $this->user"; ?>
		</div>
	</a>
	</div>
</div>
<div id="sidebar">

