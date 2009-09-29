#!/usr/bin/php
<?php
/*--------------------------------------------------------------------------*/
//
// This script will email a reminder to multi-month creditcard customers
// that they will be billed automatically in 3 weeks time.
//
// To run this script, copy this script to the root of your citrus folder
// It can be executed from the command line or in a cron job
//
// You must edit the $message_body variable to say what you would like it
// to say about the upcoming transaction  and the $from_email to indicate
// the address the email will come from
//
/*---------------------------------------------------------------------------*/

$message_body = "Thank you for choosing [YOUR COMPANY]. We would like to remind you that your $billingtype ".
     "account will renew automatically on $next_billing_date using the credit card on file. ".
     "Your current cost for service is \$$newtotal\n\n".
     "Please call with any updates or changes to your billing information.\n\n".
     "If you have any questions, please call our offices at [YOUR PHONE NUMBER]";

$from_email = "yourname@example.com";


// Includes
include('./include/config.inc.php');
include('./include/database.inc.php');
include('./include/billing.inc.php');
include('./include/citrus_base.php');
include('./include/support.inc.php');

// Select them from the database
$query = "SELECT b.id, b.contact_email, b.name, b.next_billing_date, bt.name bt_name, b.account_number ".
	"FROM billing b ".
	"LEFT JOIN customer c on c.account_number = b.account_number ".
	"LEFT JOIN billing_types bt ON bt.id = b.billing_type ".
	"WHERE ((b.billing_type = 40) OR (b.billing_type = 4) OR (b.billing_type = 6)) ".
	"AND b.next_billing_date = DATE_ADD(CURRENT_DATE, INTERVAL 21 DAY) ".
	"AND c.cancel_date IS NULL";

$DB->SetFetchMode(ADODB_FETCH_ASSOC);
$result = $DB->Execute($query) or die ("3 week SELECT failed");

while($myresult = $result->FetchRow())
{
	$billing_id = $myresult['id'];
        $to = $myresult['contact_email'];
	$name = $myresult['name'];
	$next_billing_date = $myresult['next_billing_date'];
	$next_billing_date = humandate($next_billing_date, $lang);
	$billingtype = $myresult['bt_name'];
	$account_number = $myresult['account_number'];

	$newtaxes = sprintf("%.2f",total_taxitems($DB, $billing_id));
  	$newcharges = sprintf("%.2f",total_serviceitems($DB, $billing_id)+$newtaxes);
  	$pastcharges = sprintf("%.2f",total_pastdueitems($DB, $billing_id));
  	$newtotal = sprintf("%.2f",$newcharges + $pastcharges);

	$subject = "Time to renew your internet account!";

	$message = "$name,\n $message_body";

	echo "sending a reminder to $to for $newtotal\n";	
	//echo "to: $to\n";
	//echo "subject: $subject\n";
	//echo "$message\n";
	$headers = "From: $from_email \n";
	mail ($to, $subject, $message, $headers);

// put a ticket to say that this message was sent
$user = "system";
$notify = "nobody";
$status = "automatic";
$description = "Sent reminder for $newtotal to $to";
create_ticket($DB, $user, $notify, $account_number, $status, $description);

}

?>
