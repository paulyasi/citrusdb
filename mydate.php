<?php
$mydate = date("Ymd");
echo "date: $mydate<p>";

$mytime = date("H:i:s");
echo "time: $mytime<p>";

$mydate  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));
echo "tomorrow: $mydate";

?>
