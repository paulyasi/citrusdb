<?php
/*
 * ----------------------------------------------------------------------------
 *  convert an ISO format date to a date of Month Name, Day, Year, eg: Jul 12 2011
 * ----------------------------------------------------------------------------
 */
function humandate($date)
{

	if ($date) 
	{
		// split the iso date into parts
		list($myyear, $mymonth, $myday) = explode("-", $date);
	} 
	else 
	{
		$myyear = '';
		$mymonth = '';
		$myday = '';
	}

	// assign the month it's written name
	switch($mymonth) 
	{
		case "01":
			$mymonth = lang('january');
			break;
		case "02":
			$mymonth = lang('february');
			break;
		case "03":
			$mymonth = lang('march');
			break;
		case "04":
			$mymonth = lang('april');
			break;
		case "05":
			$mymonth = lang('may');
			break;
		case "06":
			$mymonth = lang('june');
			break;
		case "07":
			$mymonth = lang('july');
			break;
		case "08":
			$mymonth = lang('august');
			break;
		case "09":
			$mymonth = lang('september');
			break;
		case "10":
			$mymonth = lang('october');
			break;
		case "11":
			$mymonth = lang('november');
			break;
		case "12":
			$mymonth = lang('december');
			break;
	}

	// put it all back together
	$date = "$mymonth $myday, $myyear";

	return $date;
}