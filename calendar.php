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

// GET Variables
$calmonth = $base->input['calmonth'];
$calyear = $base->input['calyear'];

$prevmonth = $calmonth - 1;
$nextmonth = $calmonth + 1;

/*----------------------------------------------------------------------------*/
// The generate_calendar function is from
// PHP Calendar (version 2.3), written by Keith Devens
// http://keithdevens.com/software/php_calendar
//  see example at http://keithdevens.com/weblog
// License: http://keithdevens.com/software/license
/*-----------------------------------------------------------------------------*/

function generate_calendar($year, $month, $days = array(), $day_name_length = 3, $month_href = NULL, $first_day = 0, $pn = array()){
	$first_of_month = gmmktime(0,0,0,$month,1,$year);
	#remember that mktime will automatically correct if invalid dates are entered
	# for instance, mktime(0,0,0,12,32,1997) will be the date for Jan 1, 1998
	# this provides a built in "rounding" feature to generate_calendar()

	$day_names = array(); #generate all the day names according to the current locale
	for($n=0,$t=(3+$first_day)*86400; $n<7; $n++,$t+=86400) #January 4, 1970 was a Sunday
		$day_names[$n] = ucfirst(gmstrftime('%A',$t)); #%A means full textual day name

	list($month, $year, $month_name, $weekday) = explode(',',gmstrftime('%m,%Y,%B,%w',$first_of_month));
	$weekday = ($weekday + 7 - $first_day) % 7; #adjust for $first_day
	$title   = htmlentities(ucfirst($month_name)).'&nbsp;'.$year;  #note that some locales don't capitalize month and day names

	#Begin calendar. Uses a real <caption>. See http://diveintomark.org/archives/2002/07/03
	@list($p, $pl) = each($pn); @list($n, $nl) = each($pn); #previous and next links, if applicable
	if($p) $p = '<span class="calendar-prev">'.($pl ? '<a href="'.htmlspecialchars($pl).'">'.$p.'</a>' : $p).'</span>&nbsp;';
	if($n) $n = '&nbsp;<span class="calendar-next">'.($nl ? '<a href="'.htmlspecialchars($nl).'">'.$n.'</a>' : $n).'</span>';
	$calendar = '<table class="calendar">'."\n".
		'<caption class="calendar-month">'.$p.($month_href ? '<a href="'.htmlspecialchars($month_href).'">'.$title.'</a>' : $title).$n."</caption>\n<tr>";

	if($day_name_length){ #if the day names should be shown ($day_name_length > 0)
		#if day_name_length is >3, the full name of the day will be printed
		foreach($day_names as $d)
			$calendar .= '<th abbr="'.htmlentities($d).'">'.htmlentities($day_name_length < 4 ? substr($d,0,$day_name_length) : $d).'</th>';
		$calendar .= "</tr>\n<tr>";
	}

	if($weekday > 0) $calendar .= '<td colspan="'.$weekday.'">&nbsp;</td>'; #initial 'empty' days
	
	for($day=1,$days_in_month=gmdate('t',$first_of_month); $day<=$days_in_month; $day++,$weekday++){
		if($weekday == 7){
			$weekday   = 0; #start a new week
			$calendar .= "</tr>\n<tr>";
		}
		// format the date in ISO format for SQL
		
		// edited by pyasi for use in citrusdb
		if(isset($days[$day]) and is_array($days[$day])){
			@list($link, $classes, $content) = $days[$day];
			if(is_null($content))  $content  = $day;
			$calendar .= '<td'.($classes ? ' class="'.htmlspecialchars($classes).'">' : '>').
				($link ? '<a href="'.htmlspecialchars($link).'">'.$content.'</a>' : $content).'</td>';
		}
		else $calendar .= "<td><a href=\"#\" onclick=\"filldate($year,$month,$day)\">$day</a></td>";
	}
	if($weekday != 7) $calendar .= '<td colspan="'.(7-$weekday).'">&nbsp;</td>'; #remaining "empty" days

	return $calendar."</tr>\n</table>\n";
}

echo 'Maybe put this in an iframe or css layer popup.<p>';

    //$days = array(
    //    2=>array('#','linked-day'),
    //    3=>array('/weblog/archive/2004/Jan/03','linked-day'),
    //    8=>array('/weblog/archive/2004/Jan/08','linked-day'),
    //    22=>array('/weblog/archive/2004/Jan/22','linked-day'),
    //);
    $pn = array('&laquo;'=>"index.php?load=calendar&type=fs&calmonth=$prevmonth&calyear=$calyear",
    '&raquo;'=>"index.php?load=calendar&type=fs&calmonth=$nextmonth&calyear=$calyear");
    
    echo generate_calendar($calyear, $calmonth, NULL, 3, NULL, 0, $pn);
?>
<script language=javascript>
function Pad(str,intPlaces) {
	var intLength = str.length;
	var intDifference = intPlaces-intLength;
	while (intDifference>0) {
		str = "0"+str;
		intDifference--;
	}
	return str;
}

function filldate(year,month,day) {
	var mymonth = String(month);
	var myday = String(day);
	var mydate = new Date(year,month,day);
	document.dateform.date.value = String(year) + "-" + Pad(mymonth,2) + "-" + Pad(myday,2);
}
</script>

<form action="#" name="dateform">
<input type=text name="date">
</form>
