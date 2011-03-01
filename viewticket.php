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

// set the new account number to view
$account_number = $base->input['acnum'];
$_SESSION['account_number'] = $account_number;

// log this account view
log_activity($DB, $user, $account_number, 'view', 'customer', 0, 'success');

// get the ticket number to redirect to
$ticket = $base->input['ticket'];

echo "<html>
<body bgcolor=\"#ffffff\">
<script language=\"JavaScript\">window.location.href = \"$url_prefix/index.php?load=support&type=module&editticket=on&id=$ticket\";</script>
</body>
</html>";

?>
