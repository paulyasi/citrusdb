<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<center>
<b><img alt="citrusdb" src="../../../images/citrus-logo.png"><p><?php echo lang('version')?> 
<?php echo $this->softwareversion?></b>
<p>
<a href="http://www.citrusdb.org" target="_blank">http://www.citrusdb.org</a>
<p>
PHP: <?php print phpversion(); ?>
<br>

<?php
echo lang('database').": ";
echo $this->db->dbdriver." ".$this->db->version();
?>

<hr width=450 noshade size=1>
<br><br>
<table width=400><td>
<pre><? include ('./README'); ?></pre>
</td></table>
</center>



