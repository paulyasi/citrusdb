<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>

<div class=toolblock style="height: 100px;">
<b><?php echo lang('billing');?></b>
<br>

<div class=icon>
<a href=# onclick = "popupPage('<?php echo $this->ssl_url_prefix?>/index.php/tools/importnew'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_importnew.png"><br>
<?php echo lang('importnewaccounts');?></a></div>

<div class=icon>
<a href=# onclick = "popupPage('<?php echo $this->ssl_url_prefix?>/index.php/tools/exportcc'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_creditcards.png"><br>
<?php echo lang('exportcreditcards')?></a></div>

<div class=icon>
<a href=# onclick = "popupPage('<?php echo $this->ssl_url_prefix?>/index.php/tools/importcc'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_creditcards.png"><br>
<?php echo lang('importcreditcards')?></a></div>

<div class=icon>
<a href=# onclick = "popupPage('<?php echo $this->url_prefix?>/index.php/tools/invoice'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_print.png"><br>
<?php echo lang('printinvoices')?></a></div>

<div class=icon>
<a href=# onclick = "popupPage('<?php echo $this->url_prefix?>/index.php/tools/einvoice'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_email.png"><br>
<?php echo lang('emailinvoices')?></a></div>

<div class=icon>
<a href=# onclick = "popupPage('<?php echo $this->url_prefix?>/index.php/tools/payment'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_money.png"><br>
<?php echo lang('enterpayments')?></a></div>

<div class=icon>
<a href=# onclick = "popupPage('<?php echo $this->ssl_url_prefix?>/index.php/tools/refundcc'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_creditcards.png"><br>
<?php echo lang('refund')?></a></div>

</div>

