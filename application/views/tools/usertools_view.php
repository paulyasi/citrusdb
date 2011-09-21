<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<div class=toolblock><b><?php echo lang('user') . ": $this->user"?></b><br>

<?php
$ldap_enable = $this->config->item('ldap_enable');
// if ldap is not enabled then show the built in password change icon
if ($this->config->item('ldap_enable') == FALSE)
{
	echo "<div class=icon>
	<a href=# onclick=\"popupPage('$this->url_prefix/index.php/tools/changepass'); return false;\">
	<img src=\"$this->url_prefix/images/icons/citrus_changepass.png\" border=0><br>
	".lang('changeyourpassword')."</a>
	</div>";
}
?>
<div class=icon>
<a href=# onclick="popupPage('<?php echo $this->url_prefix?>/index.php/tools/version'); return false;">
<img src="<?php echo $this->url_prefix?>/images/icons/citrus_version.png" border=0><br>
<?php echo lang('version')?></a>
</div>

<div class=icon>
<a href=# onclick="popupPage('<?php echo $this->url_prefix?>/index.php/tools/notifications'); return false;">
<img src="<?php echo $this->url_prefix?>/images/icons/citrus_email.png" border=0><br>
<?php echo lang('notifications')?></a>
</div>

</div>

