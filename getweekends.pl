#!/usr/bin/perl

# print all dates that are weekends in a form that can be put into the holidays database table [YYYY-MM-DD]

$i = 1;
while ($i < 366)
{
	$dayseconds = 86400 * $i;
	@time = localtime(time + $dayseconds);
	$mm = $time[4] + 1;
	$mm = "0$mm" if (length($mm) == 1);
	$dd = $time[3];
	$dd = "0$dd" if (length($dd) == 1);
	$yy = $time[5] + 1900;
	$wday = $time[6];
	$wday = "0$wday" if (length($wday) == 1);

	$day_of_week = ('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')[$wday];

	if (($day_of_week eq 'Saturday') or ($day_of_week eq 'Sunday'))
	{
		print "$yy-$mm-$dd\n";
	}

	$i++;
}

exit;

