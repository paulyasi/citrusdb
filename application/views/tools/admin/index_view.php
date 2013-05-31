<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<div class=toolblock style="height: 180px;">
<b><?php echo lang('admin')?></b>
<br>
<div class=icon>
<a href=# onclick="popupPage('<?php echo $this->url_prefix?>/index.php/tools/admin/organization'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_config.png">
<?php echo lang('generalconfiguration')?></a></li>
</div>

<div class=icon>
<a href=# onclick="popupPage('<?php echo $this->url_prefix?>/index.php/tools/admin/settings'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_config.png">
<?php echo lang('settings')?></a></li>
</div>

<div class=icon>
<a href=# onclick="popupPage('<?php echo $this->url_prefix?>/index.php/tools/admin/users'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_users.png"><br>
<?php echo lang('users')?></a>
</div>

<div class=icon>
<a href=# onclick="popupPage('<?php echo $this->url_prefix?>/index.php/tools/admin/groups'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_groups.png"><br>
<?php echo lang('groups')?></a>
</div>

<div class=icon>
<a href=# onclick="popupPage('<?php echo $this->url_prefix?>/index.php/tools/admin/modules'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_modules.png"><br>
<?php echo lang('editmodules')?></a>
</div>

<div class=icon>
<a href=# onclick = "popupPage('<?php echo $this->url_prefix?>/index.php/tools/admin/billingtypes'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_edit.png"><br>
<?php echo lang('editbillingtypes')?></a>
</div>

<div class=icon>
<a href=# onclick = "popupPage('<?php echo $this->url_prefix?>/index.php/tools/admin/services'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_edit.png"><br>
<?php echo lang('editservices')?></a>
</div>

<div class=icon>
<a href=# onclick="popupPage('<?php echo $this->url_prefix?>/index.php/tools/admin/mergeaccounts'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_modules.png"><br>
<?php echo lang('mergeaccounts')?></a>
</div>

</div>

