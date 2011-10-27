<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<?php
if ($year) 
{
  // print out a graph that compares each of the last 12 months
  // of service start_dates compared to end_dates
  $current  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
  echo "$year service churn, month: $month<p>\n";

  // churn, number of customers lost in that period divided by total number of customers we had at the end of the month

  echo "<table>";
  echo "<td>Service</td><td>Category</td><td>Canceled<td><td>Total</td><td>Churn</td><tr>";

  foreach ($servicechurn AS $item)
  {
	  echo "<td>".$item['service_description']."</td><td>".$item['category']."</td>".
		  "<td>".$item['lostcount']."<td><td>".$item['totalformonth']."</td>".
		  "<td>".$item['percentchurn']."&#37;</td><tr>";
  } 
  echo "</table>";

}
?>

Enter year and month of to see service churn:
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/reports/servicechurn" METHOD="POST">
Year: <input type=text name="year" value="<?php echo $year?>" size=4>
Month <select name="month">
<option>01</option>
<option>02</option>
<option>03</option>
<option>04</option>
<option>05</option>
<option>06</option>
<option>07</option>
<option>08</option>
<option>09</option>
<option>10</option>
<option>11</option>
<option>12<option></select>
</select>
&nbsp;<input type=submit name="<?php echo lang('submit')?>" value="submit">
</form> 
<p>
