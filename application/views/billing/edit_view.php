<SCRIPT LANGUAGE="JavaScript" SRC="include/CalendarPopup.js"></SCRIPT>
   <SCRIPT LANGUAGE="JavaScript">
   var cal = new CalendarPopup();

function cardval(s) 
{
  // remove non-numerics
  var v = "0123456789";
  var w = "";
  for (i=0; i < s.length; i++) {
    x = s.charAt(i);
    if (v.indexOf(x,0) != -1) {
      w += x;
    }
  }
  
  // validate number
  j = w.length / 2;
  if (j < 6.5 || j > 8 || j == 7) {
    return false;
  }
  
  k = Math.floor(j);
  m = Math.ceil(j) - k;
  c = 0;
  for (i=0; i<k; i++) {
    a = w.charAt(i*2+m) * 2;
    c += a > 9 ? Math.floor(a/10 + a%10) : a;
  }
  
  for (i=0; i<k+m; i++) c += w.charAt(i*2+1-m) * 1; {
    return (c%10 == 0);
  }
}
</SCRIPT>
<?php 

if ($creditcard_expire == "")
{
	$creditcard_expire = "0";
}

if ($next_billing_date == "")
{
	$next_billing_date = "0000-00-00";
}

if ($from_date == "")
{
	$from_date = "0000-00-00";
}

if ($payment_due_date == "")
{
	$payment_due_date = "0000-00-00";
}

if ($rerun_date == "")
{
	$rerun_date = "0000-00-00";
}


echo "<a href=\"" . $this->url_prefix . "/index.php/billing\">[ " . lang('undochanges') . " ]</a>";

echo "<h3>".lang('organizationname').": $organization_name</h3>";

$billing_form_url = "$this->ssl_url_prefix" . "/index.php/billing/save";

?>
<table cellpadding=0 border=0 cellspacing=0 width=720>
	<td valign=top width=360>
	<form action=<?php echo $billing_form_url?> name="form1" AUTOCOMPLETE="off" method=post>
	<table cellpadding=5 cellspacing=1 border=0 width=360>
	<td bgcolor="#ccccdd" width=180><b><?php echo lang('id')?></b></td>
	<td width=180 bgcolor="#ddddee"><?php echo $billing_id;?></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('name')?></b></td><td bgcolor="#ddddee">
	<input name="name" type=text value="<?php echo $name?>"></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('company')?></b></td><td bgcolor="#ddddee">
	<input name="company" type=text value="<?php echo $company?>"></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('street')?></b></td><td bgcolor="#ddddee">
	<input name="street" type=text value="<?php echo $street;?>"></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('city')?></b></td><td bgcolor="#ddddee">
	<input name="city" type=text value="<?php echo $city?>"></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('state')?></b></td><td bgcolor="#ddddee">
	<input name="state" type=text value="<?php echo $state?>" size=3></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('zip')?></b></td><td bgcolor="#ddddee">
	<input name="zip" size=5 type=text value="<?php echo $zip?>"></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('country')?></b></td><td bgcolor="#ddddee">
	<input name="country" type=text value="<?php echo $country?>"></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('phone')?></b></td><td bgcolor="#ddddee">
	<input name="phone" type=text value="<?php echo $phone?>"></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('fax')?></b></td><td bgcolor="#ddddee">
	<input name="fax" type=text value="<?php echo $fax?>"></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('contactemail')?></b></td><td bgcolor="#ddddee">
	<input name="contact_email" type=text value="<?php echo $contact_email?>"></td><tr>
	</table></td>
	<td valign=top width=360>
	<table cellpadding=5 cellspacing=1 width=360>
	<td width=180 bgcolor="#ccccdd"><b><?php echo lang('billingtype')?></b></td>
	<td width=180 bgcolor="#ffbbbb">
	
<?php
print "<select name=\"billing_type\">\n";
$query = "SELECT * FROM billing_types ORDER BY name";
$result = $this->db->query($query) or die ("query failed");
foreach ($result->result_array() as $myresult){

	$bt_id = $myresult['id'];
	$bt_name = $myresult['name'];
	
	if ($billing_type == $bt_id)
	{
		print "<option selected value=$bt_id>$bt_name</option>\n";
	}
	else
	{ 
		print "<option value=$bt_id>$bt_name</option>\n"; 
	}
}
?>

</select>

</td><tr>
<td bgcolor="#ccccdd"><b><?php echo lang('ccnumber')?></b></td>
	<td bgcolor="#ddddee">
	<input size=16 name="creditcard_number" type=text value="<?php echo $creditcard_number?>"
	onChange="if(!cardval(document.forms['form1'].creditcard_number.value)) {
    alert ('<?php echo lang('notvalid');?>');
    document.form1.creditcard_number.style.color='#EE0000';
  } else {
    document.form1.creditcard_number.style.color='#000000';
  }">
	<A HREF="" onClick="if(!cardval(document.forms['form1'].creditcard_number.value)) {
alert ('<?php echo lang('notvalid');?>');
} else { alert('<?php echo lang('valid');?>'); } return false;" NAME="anchor1" ID="anchor1" style="color:blue">
  <?php echo lang('validate')?></a><br>
  <a href="<?php echo $this->ssl_url_prefix?>/index.php/billing/asciiarmor/<?php echo $billing_id?>"><?php echo lang('ciphertext')?></a>
  </td><tr>
  <td bgcolor="#ccccdd"><b><?php echo lang('ccexpire')?></b></td>
  <td bgcolor="#ddddee">
  <input size=5 name="creditcard_expire" type=text value="<?php echo $creditcard_expire?>">
  </td><tr>
  
  <td bgcolor="#ccccdd"><b><?php echo lang('pastdueexempt')?></b></td>
  <td bgcolor="#ddddee">
  <input type=radio name=pastdue_exempt value=n <?php if ($pastdue_exempt == "n") { echo " checked "; }?>
><?php echo lang('no');?>
<input type=radio name=pastdue_exempt value=y <?php if ($pastdue_exempt == "y") { echo " checked "; } ?>
><?php echo lang('yes');?>
<input type=radio name=pastdue_exempt value=bad_debt <?php if ($pastdue_exempt == "bad_debt") { echo " checked "; } ?>
><?php echo lang('bad_debt');?>
	
</td><tr>

<td bgcolor="#ccccdd"><b><?php echo lang('automaticreceipt');?></b></td>
<td bgcolor="#ddddee">
  <input type=radio name=automatic_receipt value=n <?php if ($automatic_receipt == "n") { echo " checked "; } ?>
><?php echo lang('no');?>
<input type=radio name=automatic_receipt value=y <?php if ($automatic_receipt == "y") { echo " checked "; } ?>
><?php echo lang('yes');?>
</td><tr>

<td bgcolor="#ccccdd"><b><?php echo lang('nextbillingdate');?></b></td>
<td bgcolor="#ddddee">
  <input name="next_billing_date" type=text value="<?php echo $next_billing_date;?>" size=12>
  <A HREF="#"
  onClick="cal.select(document.forms['form1'].next_billing_date,'anchor1','yyyy-MM-dd'); 
return false;" NAME="anchor1" ID="anchor1" style="color:blue">[<?php echo lang('select');?>]</A>
  </td><tr>
  <td bgcolor="#ccccdd"><b><?php echo lang('from'); echo lang('date');?></b>
  </td>
  <td bgcolor="#ddddee">
						      <input name="from_date" type=text value="<?php echo $from_date?>" size=12>
						      <A HREF="#"
						      onClick="cal.select(document.forms['form1'].from_date,'anchor1','yyyy-MM-dd'); 
	return false;" NAME="anchor1" ID="anchor1" style="color:blue">[<?php echo lang('select');?>]</A>
						      </td><tr>
						      <td bgcolor="#ccccdd"><b><?php echo lang('to'); echo lang('date')?></b></td>
							<td bgcolor="#ddddee"><?php echo $to_date?></td><tr>
							
							<td bgcolor="#ccccdd">
							<b><?php echo lang('paymentduedate');?></b></td>
							<td bgcolor="#ddddee">
							<input name="payment_due_date" type=text value="<?php echo $payment_due_date?>" size=12>
							<A HREF="#"
							onClick="cal.select(document.forms['form1'].payment_due_date,'anchor1','yyyy-MM-dd'); 
	return false;"
	NAME="anchor1" ID="anchor1" style="color:blue">[<?php echo lang('select');?>]</A>
	</td><tr>

	<td bgcolor="#ccccdd"><b><?php echo lang('rerun') . " " .  lang('date');?></b></td>
	<td bgcolor="#ddddee">
	<input name="rerun_date" type=text value="<?php echo $rerun_date?>" size=12>
	<A HREF="#"
	onClick="cal.select(document.forms['form1'].rerun_date,'anchor1','yyyy-MM-dd'); 
	return false;"
	NAME="anchor1" ID="anchor1" style="color:blue">[<?php echo lang('select');?>]</A>
	</td><tr>

	<td bgcolor="#ccccdd"><b><?php echo lang('po_number');?></b></td>
	<td bgcolor="#ddddee">
	<input name="po_number" type=text value="<?php echo $po_number;?>"></td><tr>
	<td bgcolor="#ccccdd"><b><?php echo lang('notes');?></b></td>
	<td bgcolor="#ddddee">
	<input name="notes" type=text value="<?php echo $notes;?>"></td><tr>
	</table>
</td>
<tr>
<td colspan=2>
<center>
<input name=save type=submit class=smallbutton value="<?php echo lang('savechanges');?>">
<input type=hidden name=load value=billing>
<input type=hidden name=type value=module>
<input type=hidden name=edit value=on>
<input type=hidden name=billing_id value=<?php echo $billing_id?>>
</center>
</td>
</table>
</form>
