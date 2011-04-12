<?php

  // get the cancel reason text
  if ($cancel_reason_id > 0) {
    $query = "SELECT reason FROM cancel_reason WHERE id = $cancel_reason_id";
    $result = $this->db->query($query) or die ("$l_queryfailed");
    $myresult = $result->row();
    $cancel_reason = $myresult->reason;
  } else {
    $cancel_reason = "";
  }

  // print the customer edit links
  echo "<a href=\"index.php/customer/edit\">".
    "[ " . lang('editcustomer') . "]</a>";
  if ($cancel_date) {
    echo "<a href=\"index.php/customer/undelete\">".
      "[ $l_uncancelcustomer ]</a>";
  } else {
    echo "<a href=\"index.php/customer/delete\">".
      "[ $l_cancelcustomer ]</a>";
   }
  // print the HTML table
  echo "<table cellpadding=0 border=0 cellspacing=1 width=719>".
    "<td valign=top width=259 style=\"background-color: #dde;\">".
    "<table cellpadding=4 cellspacing=0 border=0 width=259>".
  
  "<tr>".
  //"<td width=180><b>$l_name</b></td>".
  "<td style=\"font-size: 12pt;\">" . $name . "</td>".

  "<tr>".
  //"<td><b>$l_company</b></td>".
  "<td style=\"font-size: 12pt;\">$company</td>".
  
  "<tr>".  
  //"<td><b>$l_street</b></td>".
  "<td>$street</td>".
  
  "<tr>".
  //"<td><b>$l_city</b></td>".
  "<td>$city $state $zip</td>".

  //"<tr>".
  //"<td><b>$l_state</b></td>".
  //"<td>$state</td>".
  
  //"<tr>".  
  //"<td><b>$l_zip</b></td>".
  //"<td>$zip</td>".
  
  "<tr>".
  //"<td><b>$l_phone</b></td>".
  "<td>$l_phone: $phone</td>".
  
  "<tr>".
  //"<td><b>$l_alt_phone</b></td>".
  "<td>$l_alt_phone: $alt_phone</td>".
  
  "<tr>".
  //"<td><b>$l_fax</b></td>".
    "<td>$l_fax: $fax</td>".

    "<tr>".
    "<td>$l_notes: $notes</td>".

  // end of left table column
  
  "</table></td><td valign=top width=360 style=\"background-color: #dde;\">".
  "<table cellpadding=3 cellspacing=0 border=0 width=360>";


echo 
  "<td width=160><b>$l_signupdate</b></td>".
  "<td width=200>$signup_date</td><tr>".

  "<td valign=top><b>$l_canceldate</b></td>".
  "<td class=redbold>$cancel_date<br> $cancel_reason</td><tr>".
  
  "<td><b>$l_source</b></td>".
  "<td>$source</td><tr>".
  
  "<td><b>$l_contactemail</b></td>".
  "<td>$contactemail</td><tr>".
  
  "<td><b>$l_secret_question</b></td>".
  "<td>$secret_question</td><tr>".
  
  "<td><b>$l_secret_answer</b></td>".
  "<td>$secret_answer</td><tr>".
  
  "<td><b>$l_defaultbillingid</b></td>".
  "<td>$default_billing_id</td><tr>".
  
  "<td><b>$l_country</b></td>".
  "<td>$country</td><tr>".
  
  "<td><b>$l_acctmngrpasswd</b></td>";
  if ($account_manager_password) { 
    echo "<td><a href=\"$url_prefix/index.php?load=customer&type=module&resetamp=on\">$l_reset</a></td><tr>";
  } else {
    echo "<td><a href=\"$url_prefix/index.php?load=customer&type=module&resetamp=on\">$l_notset</a></td><tr>";
  }
    echo "</table></td></table></form>";
//end of second column

 echo "<p>";
 