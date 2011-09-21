<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>


/*----------------------------------------------------------------------------*/
// Show Reports
// print reports for manager
/*----------------------------------------------------------------------------*/
if (($myresult['manager'] == 'y') OR ($myresult['admin'] == 'y'))
{
echo "<div class=toolblock style=\"height: 180px;\">
<b>$l_reports</b>
<br>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=summary&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_report.png\"><br>
$l_customersummary</a>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=revenue&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_report.png\"><br>
$l_revenuereport</a>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=refunds&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_report.png\"><br>
$l_refundreport</a>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=pastdue&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_declines.png\"><br>
$l_pastduereport</a>
</div>

<div class=icon>
<a href=# onclick = \"popupPage('index.php?load=billing&tooltype=module&type=tools&declined=on'); return false;\">
<img border=0 src=\"images/icons/citrus_declines.png\"><br>
$l_paymentstatus</a></div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=servicereport&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_report.png\"><br>
$l_servicereport</a>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=sourcereport&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_report.png\"><br>
$l_sourcereport</a>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=exemptreport&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_report.png\"><br>
$l_exemptreport</a>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=listpdf&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_report.png\"><br>
$l_printnotices</a>
</div>

<div class=icon>
<a href=# onclick=\"popupPage('index.php?load=servicegrowth&type=tools'); return false;\">
<img border=0 src=\"images/icons/citrus_report.png\"><br>
$l_servicegrowth</a>
</div>

</div>
";
}

