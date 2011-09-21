<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>


/*----------------------------------------------------------------------------*/
// Show Admin Functions
/*----------------------------------------------------------------------------*/
if ($myresult['admin'] == 'y')
{
echo "<div class=toolblock style=\"height: 180px;\">
<b>$l_admin</b>
<br>
<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=general&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_config.png\">
$l_generalconfiguration</a></li>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=settings&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_config.png\">
$l_settings</a></li>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=users&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_users.png\"><br>
$l_users</a>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=groups&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_groups.png\"><br>
$l_groups</a>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=modules&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_modules.png\"><br>
$l_editmodules</a>
</div>

<div class=icon>
<a href=# onclick = \"popupPage('index.php?load=billing&tooltype=module&type=tools&billingtypes=on'); return false;\">
<img border=0 src=\"images/icons/citrus_edit.png\"><br>
$l_editbillingtypes</a>
</div>

<div class=icon>
<a href=# onclick = \"popupPage('index.php?load=services&tooltype=module&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_edit.png\"><br>
$l_editservices</a>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=mergeaccounts&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_modules.png\"><br>
$l_mergeaccounts</a>
</div>

</div>
";
}

