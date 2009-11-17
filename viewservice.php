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
$userserviceid = $base->input['userserviceid'];

echo "<html>
<body bgcolor=\"#ffffff\">
<script language=\"JavaScript\">window.location.href = \"index.php?load=services&type=module&edit=on&userserviceid=$userserviceid&editbutton=Edit\";</script>

</body>
</html>";

?>
