<div id="header">
  <div class="buttonbaritem">
    <img class="full-image"
	 src="<?php echo $this->url_prefix;?>/images/my-logo.png">
  </div>
  <div class="buttonbaritem">
    <a href="<?php echo $this->url_prefix;?>/index.php/customer/newcustomer"
      style="background: transparent url(<?php echo $this->url_prefix;?>/images/new-icon.png) scroll no-repeat left center; padding: 2px 0px 2px 25px;">
      <?php echo lang('new')?>
    </a>
  </div>
  <div class="buttonbaritem">
    <a href="<?php echo $this->url_prefix;?>/index.php/dashboard"
      style="background: transparent url(<?php echo $this->url_prefix;?>/images/search-icon.png) scroll no-repeat left center; padding: 2px 0px 2px 25px;">
      <?php echo lang('search')?>
    </a>
  </div>
  <div class="buttonbaritem">
    <a href="<?php echo $this->url_prefix;?>/index.php/reports"
      style="background: transparent url(<?php echo $this->url_prefix;?>/images/reports-icon.png) scroll no-repeat left center; padding: 2px 0px 2px 25px;">
      <?php echo lang('reports')?>
    </a>
  </div>
  <div class="buttonbaritem">
    <a href="<?php echo $this->url_prefix;?>/index.php/tools"
       style="background: transparent url(<?php echo $this->url_prefix;?>/images/admin-icon.png) scroll no-repeat left center; padding: 2px 0px 2px 25px;">
      <?php echo lang('tools');?>
    </a>
  </div>
  <div class="buttonbaritem">
    <a href="<?php echo $this->url_prefix;?>/index.php/session/logout"
       style="background: transparent url(<?php echo $this->url_prefix;?>/images/logout-icon.png) scroll no-repeat left center; padding: 2px 0px 2px 25px;"><?php echo lang('logout'); echo " $this->user"; ?>
    </a>
  </div>
</div>
<div id="sidebar">

