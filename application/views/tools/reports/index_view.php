<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<div class=toolblock style="height: 180px;">
<b><?php echo lang('reports')?></b>
<br>

<div class=icon>
<a href=# onclick="popupPage('index.php/tools/customersummary'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_report.png"><br>
<?php echo lang('customersummary')?></a>
</div>

<div class=icon>
<a href=# onclick="popupPage('<?php echo $this->url_prefix?>/index.php/tools/revenuereport'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_report.png"><br>
<?php echo lang('revenuereport')?></a>
</div>

<div class=icon>
<a href=# onclick="popupPage('<?php echo $this->url_prefix?>/index.php/tools/refundreport'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_report.png"><br>
<?php echo lang('refundreport')?></a>
</div>

<div class=icon>
<a href=# onclick="popupPage('<?php echo $this->url_prefix?>/index.php/tools/pastduereport'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_declines.png"><br>
<?php echo lang('pastduereport')?></a>
</div>

<div class=icon>
<a href=# onclick = "popupPage('<?php echo $this->url_prefix?>/index.php/tools/paymentstatus'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_declines.png"><br>
<?php echo lang('paymentstatus')?></a></div>

<div class=icon>
<a href=# onclick="popupPage('<?php echo $this->url_prefix?>/index.php/tools/servicereport'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_report.png"><br>
<?php echo lang('servicereport')?></a>
</div>

<div class=icon>
<a href=# onclick="popupPage('<?php echo $this->url_prefix?>/index.php/tools/sourcereport'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_report.png"><br>
<?php echo lang('sourcereport')?></a>
</div>

<div class=icon>
<a href=# onclick="popupPage('<?php echo $this->url_prefix?>/index.php/tools/exemptreport'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_report.png"><br>
<?php echo lang('exemptreport')?></a>
</div>

<div class=icon>
<a href=# onclick="popupPage('<?php echo $this->url_prefix?>/index.php/tools/printnotices'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_report.png"><br>
<?php echo lang('printnotices')?></a>
</div>

<div class=icon>
<a href=# onclick="popupPage('<?php echo $this->url_prefix?>/index.php/tools/servicechurn'); return false;">
<img border=0 src="<?php echo $this->url_prefix?>/images/icons/citrus_report.png"><br>
<?php echo lang('servicechurn')?></a>
</div>

</div>

