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
<font size="-2">
CitrusDB - The Open Source Customer Database<br>
Copyright (C) 2002-2009 Paul Yasi<br>
<p>
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
<p>
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
<p>
You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
<p>
http://www.citrusdb.org<br>
Read the README file for more details
</font>
</td>
</table>
</center>

