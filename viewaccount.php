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

?>

<html>
<body bgcolor="#ffffff">
<script language="JavaScript">window.location.href = "index.php?load=customer&type=module";</script>
</body>
</html>
