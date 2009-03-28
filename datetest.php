<?php
// Copyright (C) 2002-2004  Paul Yasi (paul at citrusdb.org)
// read the README file for more information

//
// TEST: test the date functions
//

//
// get the current date and time in the SQL query format
//

$yesterday  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));
print "yesterday = $yesterday<p>";

$today = date("Y-m-d");
print "today = $today<p>";

$tomorrow  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));
print "tomorrow = $tomorrow<p>";

$thenextday = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")+2, date("Y")));
print "the next day = $thenextday<p>";

$thedayafterthat = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")+3, date("Y")));
print "the day after that = $thedayafterthat<p>";


// echo the day of week number
$mydate = "2007-09-01";
list($myyear, $mymonth, $myday) = split('-', $mydate); 
$day_of_week = date("w", mktime(0, 0, 0, $mymonth, $myday, $myyear));
echo "day of week $day_of_week";


?>
