<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<center>
<table height=90% width=100%>
<td align=center>
<b><img alt="citrusdb" src="../../images/citrus-logo.png"><p><?php echo lang('version')?> 
<?php echo $this->softwareversion?></b><p>
<a href="http://www.citrusdb.org" target="_blank">http://www.citrusdb.org</a>
<p>
PHP: <?php print phpversion(); ?>

<br>

<?php
echo lang('database').": ";
echo $this->db->dbdriver." ".$this->db->version();
?>

<br><br><br>
<hr width=450 noshade size=1>
<br><br><br>
<table><td width=400>
<pre><? include ('./README'); ?></pre>
</td>
</table>
</center>



