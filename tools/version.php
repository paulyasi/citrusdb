<?php 
/*----------------------------------------------------------------------------*/
// Check for authorized accesss
/*----------------------------------------------------------------------------*/

if(constant("INDEX_CITRUS") <> 1){
	echo "You must be logged in to run this.  Goodbye.";
	exit;	
}

if (!defined("INDEX_CITRUS")) {
	echo "You must be logged in to run this.  Goodbye.";
        exit;
}
echo "
<center>
<table height=90% width=100%>
<td align=center>
<b><img alt=\"citrusdb\" src=\"images/citrus-logo.png\"><p>$l_version $softwareversion</b><p>
<a href=\"http://www.citrusdb.org\" target=\"_blank\">http://www.citrusdb.org</a>
<p>
PHP:
";


	print phpversion(); 
?>

<br>

<?php
echo "$l_database: ";
$serverinfo = $DB->ServerInfo();
	$serverdescription = $serverinfo['description'];
	$serverversion = $serverinfo['version'];
	echo "$sys_dbtype $serverversion ($serverdescription)";
?>

<br><br><br>
<hr width=450 noshade size=1>
<br><br><br>
<table><td width=400>
<pre><? include ('./README'); ?></pre>
</td>
</table>
</center>

