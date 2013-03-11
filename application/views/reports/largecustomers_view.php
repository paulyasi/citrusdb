<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<h3><?php echo lang('largecustomerreport')?></h3>
<?php
// show the form to pick what day to view
$day_1  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
$day_2  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));
$day_3  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-2, date("Y")));
$day_90  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-90, date("Y")));
$day_180  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-180, date("Y")));
$yearago  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")-1));
?>
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/reports/largecustomers" METHOD="POST">
<table>
<?php echo lang('from')?>: <input type=text name="day1" value="<?php echo $day_180?>"> - 
<?php echo lang('to')?>: <input type=text name="day2" value="<?php echo $day_1?>">

</td><tr> 
<td></td><td><br><input type=submit name="<?php echo lang('submit')?>" value="submit"></td>
</table>
</form>
<table><td><?php echo lang('account')?></td>
<td><?php echo lang('name')?></td>
<td><?php echo lang('company')?></td>
<td><?php echo lang('street')?></td>
<td><?php echo lang('total')?></td><td></td>
<tr>
<?php
foreach ($largecustomers AS $myresult) 
{
    $billing_id = $myresult['billing_id'];
    $name = $myresult['name'];
    $company = $myresult['company'];
    $street = $myresult['street'];
    $account_number = $myresult['account_number'];
    $billing_date = $myresult['billing_date'];
    $invoice = $myresult['invoice'];
    $charges = $myresult['TotalCharges'];

    echo "<td>$account_number</td>";
    echo "<td>$name</td>";
    echo "<td>$company</td>";
    echo "<td>$street</td>";
    echo "<td>$charges</td>";
    echo "<tr>";
}
?>

</table>
</body>
</html>
