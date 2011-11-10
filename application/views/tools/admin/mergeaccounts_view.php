<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>

<h3><?php echo lang('mergeaccounts')?></h3>
<p>

$confirm = $base->input['confirm'];
$from_account = $base->input['from_account'];
$to_account = $base->input['to_account'];

// check that the user has admin privileges
$query = "SELECT * FROM user WHERE username='$user'";
$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("$l_queryfailed");
$myresult = $result->fields;
if ($myresult['admin'] == 'n') {
        echo '$l_youmusthaveadmin<br>';
        exit;
}


function savemergeaccounts() 
{
  // get the default billing id in the $to_account
  $query = "SELECT default_billing_id FROM customer WHERE account_number = '$to_account'";
  $result = $DB->Execute($query) or die ("default billing id select $l_queryfailed");
  $myresult = $result->fields;
  $default_billing_id = $myresult['default_billing_id'];

  // move the services to the new record
  $query = "UPDATE user_services SET account_number = '$to_account', ".
    "billing_id = '$default_billing_id' WHERE account_number = '$from_account'";
  $result = $DB->Execute($query) or die ("user services update $l_queryfailed");

  // move the customer history to the new record
  $query = "UPDATE customer_history SET account_number = '$to_account' ".
    "WHERE account_number = '$from_account'";
  $result = $DB->Execute($query) or die ("customer history update $l_queryfailed");

  // make a note on both records that they were merged
  $desc = "$l_merged $from_account $l_to $to_account";  
  create_ticket($DB, $user, NULL, $to_account, 'automatic', $desc);
  create_ticket($DB, $user, NULL, $from_account, 'automatic', $desc);
  
  print "<h3>$desc</h3>";
 }

<P>
<FORM ACTION="index.php/tools/admin/confirmmergeaccounts" METHOD="POST">
<B><?php echo lang('from')?></B><BR><INPUT TYPE="TEXT" NAME="from_account">
<P>
<B><?php echo lang('to')?></B><BR><INPUT TYPE="TEXT" NAME="to_account">
<P>
<P>
<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo lang('submit')?>">
</FORM>

</body>
</html>
