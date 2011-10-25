<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<html>
<body bgcolor="#ffffff">
<?php
echo "<h3>".lang('servicereport').": ".$description."</h3>";

echo "<h2>".lang('added').": $service_count</h2>\n";

echo "<h3>Active</h3><blockquote>$active <p><b>Declined: $declinedvalue</b></blockquote><p><h3>Inactive</h3><blockquote>$inactive </blockquote><p><h3>Other</h3><blockquote>$other</blockquote>\n";

