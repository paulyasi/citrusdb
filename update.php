<html>
<head><title>Update Citrus Database</title>
<body>
<?php
// get the configuration info
include ('./include/config.inc.php');
include ('./include/database.inc.php');

// Get our base functions and class
require './include/citrus_base.php';
$base = new citrus_base();

// turn on debugging
//$DB->debug = true;

if (!isset($base->input['submit'])) { $base->input['submit'] = ""; }
if ($base->input['submit'] == "Update")
{
	$databaseversion = $base->input['databaseversion'];
	if ($databaseversion == "0.9.2 or older")
	{
		echo "Updating to 0.9.3<br>\n";

		$query = "ALTER TABLE `billing` 
		CHANGE `state` `state` char( 3 ) NOT NULL";
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";
	
		$query = "ALTER TABLE `customer` 
		CHANGE `state` `state` char( 3 ) NOT NULL";
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";
	
		$query = "ALTER TABLE `general` 
		ADD `version` VARCHAR( 12 ) DEFAULT '0.9.3' 
		NOT NULL AFTER `id`";	
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";
		
		$databaseversion = "0.9.3";
	}
	if ($databaseversion == "0.9.3")
	{
		echo "Updating to 0.9.4<br>\n";
		$query = "ALTER TABLE `general` CHANGE `version` `version` VARCHAR( 12 ) NOT NULL";
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";		
	
		$query = "UPDATE `general` SET `version` = '0.9.4' WHERE `id` =1 LIMIT 1";		
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";	
	
		$databaseversion = "0.9.4";
	}
	if ($databaseversion == "0.9.4")
	{
		echo "Updating to 0.9.5<br>\n";
	
		$query = "UPDATE `general` SET `version` = '0.9.5' WHERE `id` =1 LIMIT 1";		
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";	
	
		$databaseversion = "0.9.5";
	
	}
	if ($databaseversion == "0.9.5")
	{
		echo "Updating to 1.0 RC1<br>\n";
		$query = "ALTER TABLE `payment_history` 
			ADD `check_number` VARCHAR( 32 ) NULL";
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";	
		
		$query = "ALTER TABLE `payment_history` 
			ADD `avs_response` VARCHAR( 32 ) NULL";
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";

		$query = "UPDATE `general` SET `version` = '1.0 RC1' 
			WHERE `id` =1 LIMIT 1";		
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";	
		
		$databaseversion = "1.0 RC1";	
	}
	if ($databaseversion == "1.0 RC1")
	{
		$query = "UPDATE `general` SET `version` = '1.0 RC2' 
			WHERE `id` =1 LIMIT 1";		
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";	

		$databaseversion = "1.0 RC2";
	}
	if ($databaseversion == "1.0 RC2")
	{
		$query = "ALTER TABLE `master_services` DROP `postbilled`";
		$result = $DB->Execute($query);
		echo "$query<br>\n";	

		$query = "UPDATE `general` SET `version` = '1.0' 
			WHERE `id` =1 LIMIT 1";		
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";	

		$databaseversion = "1.0";
	}
	if ($databaseversion == "1.0")
	{
		// create a tax_exempt table
		$query = "CREATE TABLE `tax_exempt` (
		`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`account_number` INT( 11 ) NOT NULL ,
		`tax_rate_id` INT( 11 ) NOT NULL ,
		`customer_tax_id` VARCHAR( 64 ) NULL ,
		`expdate` DATE NULL
		) TYPE = MyISAM";
		$result = $DB->Execute($query);
		echo "$query<br>\n";

		// remove the tax_exempt_id field from customer
		$query = "ALTER TABLE `customer` DROP `tax_exempt_id`";
		$result = $DB->Execute($query);
		echo "$query<br>\n";

		$query = "UPDATE `general` SET `version` = '1.0.1' 
			WHERE `id` =1 LIMIT 1";		
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";	

		$databaseversion = "1.0.1";
	}
	if ($databaseversion == "1.0.1")
	{
		//change search 8 for support ticket number
		$query = "UPDATE `searches` 
		SET `query` = 'SELECT * FROM customer_history WHERE id = %s1%' 
		WHERE `id` =8 LIMIT 1";
		$result = $DB->Execute($query);
		echo "$query<br>\n";
	
		// remove the sortorder field from the billing_types table
		$query = "ALTER TABLE `billing_types` DROP `sortorder`";
		$result = $DB->Execute($query);
		echo "$query<br>\n";

		$query = "UPDATE `general` SET `version` = '1.0.2' 
			WHERE `id` =1 LIMIT 1";		
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";	
	
		$databaseversion = "1.0.2";
	}
	if ($databaseversion == "1.0.2")
	{
		$query = "UPDATE `general` SET `version` = '1.0.3' 
			WHERE `id` =1 LIMIT 1";		
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";	

		$databaseversion = "1.0.3";
	}
	if ($databaseversion == "1.0.3")
	{
		$query = "UPDATE `general` SET `version` = '1.0.4' 
			WHERE `id` =1 LIMIT 1";		
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";

		$query = "ALTER TABLE `general` 
		ADD `default_invoicenote` VARCHAR( 255 ) NULL ,
		ADD `pastdue_invoicenote` VARCHAR( 255 ) NULL ,
		ADD `turnedoff_invoicenote` VARCHAR( 255 ) NULL ,
		ADD `collections_invoicenote` VARCHAR( 255 ) NULL"; 
		$result = $DB->Execute($query) or die ("FAILED: $query");
		echo "$query<br>\n";

		$databaseversion = "1.0.4";
	}
	if ($databaseversion == "1.0.4")
	{
		$query = "UPDATE `general` SET `version` = '1.1' 
			WHERE `id` =1 LIMIT 1";		
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";

		// update for 1.1 with adodb sessions support
		$query = "CREATE TABLE sessions2(
		  sesskey VARCHAR( 64 ) NOT NULL DEFAULT '',
		  expiry TIMESTAMP NOT NULL ,
		  expireref VARCHAR( 250 ) DEFAULT '',
		  created TIMESTAMP NOT NULL ,
		  modified TIMESTAMP NOT NULL ,
		  sessdata LONGTEXT DEFAULT '',
		PRIMARY KEY ( sesskey ) ,
		INDEX sess2_expiry( expiry ),
		INDEX sess2_expireref( expireref )
		)";
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";

		// update for 1.1 with payment_mode table
		$query = "CREATE TABLE `payment_mode` (
	 	`id` int(11) NOT NULL auto_increment,
	 	`name` varchar(32),
	 	PRIMARY KEY (`id`)
		) TYPE=MyISAM AUTO_INCREMENT=1 ";
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";

		$query = "INSERT INTO `payment_mode` 
		VALUES (1, 'check'), (2, 'eft'), (3, 'cash')";
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";
	
		$databaseversion = "1.1";
	}
	if ($databaseversion == "1.1")
	{
		$query = "UPDATE `general` SET `version` = '1.1.1' 
			WHERE `id` =1 LIMIT 1";		
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";

		$databaseversion = "1.1.1";
	}
	if ($databaseversion == "1.1.1")
	{
		$query = "UPDATE `general` SET `version` = '1.1.2' 
			WHERE `id` =1 LIMIT 1";		
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";

		$databaseversion = "1.1.2";
	}
	if ($databaseversion == "1.1.2")
	{
		// add a po_number field to billing
		$query = "ALTER TABLE `billing` 
			ADD `po_number` VARCHAR( 64 ) NULL";
		$result = $DB->Execute($query) or die ("query failed");
			echo "$query<br>\n";

		// update the version number
		$query = "UPDATE `general` SET `version` = '1.1.3' 
			WHERE `id` =1 LIMIT 1";		
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";

		$databaseversion = "1.1.3";
	}
	if ($databaseversion == "1.1.3")
	{
		$query = "UPDATE `general` SET `version` = '1.1.4' 
			WHERE `id` =1 LIMIT 1";		
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";

		$databaseversion = "1.1.4";
	}
	
	if ($databaseversion == "1.1.4")
	{
		// update the general table with billingweekend fields
		$query = "ALTER TABLE `general` 
		ADD `billingweekend_sunday` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'y',
		ADD `billingweekend_monday` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n',
		ADD `billingweekend_tuesday` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n',
		ADD `billingweekend_wednesday` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n',
		ADD `billingweekend_thursday` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n',
		ADD `billingweekend_friday` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n',
		ADD `billingweekend_saturday` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'y'";
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";

		// update the version number
		$query = "UPDATE `general` SET `version` = '1.1.5' 
			WHERE `id` =1 LIMIT 1";		
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";

		$databaseversion = "1.1.5";
	}

	if ($databaseversion == "1.1.5") {
	
		// add the refund fields to billing details
		$query = "ALTER TABLE `billing_details` 
			ADD `refund_amount` FLOAT NOT NULL DEFAULT '0',
			ADD `refunded` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n';";
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";

		// update the version number
		$query = "UPDATE `general` SET `version` = '1.1.6' 
			WHERE `id` =1 LIMIT 1";		
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";

		$databaseversion = "1.1.6";
	}

	if ($databaseversion == "1.1.6") {
		// add refund_date field
		$query = "ALTER TABLE `billing_details` 
				ADD `refund_date` DATE NULL ;";
		$result = $DB->Execute($query) or die ("query failed");
                echo "$query<br>\n";


		// add a credit status to the payment_history
		$query = "ALTER TABLE `payment_history` CHANGE `status` `status` SET( 'authorized', 'declined', 'pending', 'donotreactivate', 'collections', 'turnedoff', 'credit' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ";
		$result = $DB->Execute($query) or die ("query failed");
                echo "$query<br>\n";

                // update the version number
                $query = "UPDATE `general` SET `version` = '1.2' 
                        WHERE `id` =1 LIMIT 1";
                $result = $DB->Execute($query) or die ("query failed");
                echo "$query<br>\n";

                $databaseversion = "1.2";
	}

	if ($databaseversion == "1.2")
	{
		$query = "UPDATE `general` SET `version` = '1.2.1' 
			WHERE `id` =1 LIMIT 1";		
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";

		$databaseversion = "1.2.1";
	}
	if ($databaseversion == "1.2.1")
	{
		$query = "ALTER TABLE `payment_history` CHANGE `transaction_code` `transaction_code` VARCHAR( 32 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ";
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";

		$query = "ALTER TABLE `general` ADD `declined_subject` VARCHAR( 64 ) NULL , ADD `declined_message` TEXT NULL";
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";
	
		$query = "UPDATE `general` SET `version` = '1.2.2' 
			WHERE `id` =1 LIMIT 1";		
		$result = $DB->Execute($query) or die ("query failed");
		echo "$query<br>\n";

		$databaseversion = "1.2.2";
	}

	if ($databaseversion == "1.2.2") {
	  // add new settings table to hold citrus specific settings
	  $query = "CREATE TABLE `settings` (".
	    "`id` int(11) NOT NULL auto_increment, ".
	    "`version` varchar(12) NOT NULL, ".
	    "`default_group` varchar(30) default NULL, ".
	    "`path_to_ccfile` varchar(255) default NULL, ".
	    "`billingdate_rollover_time` time default NULL, ".  
	    "`billingweekend_sunday` enum('y','n') NOT NULL default 'y', ".
	    "`billingweekend_monday` enum('y','n') NOT NULL default 'n', ".
	    "`billingweekend_tuesday` enum('y','n') NOT NULL default 'n', ".
	    "`billingweekend_wednesday` enum('y','n') NOT NULL default 'n', ".
	    "`billingweekend_thursday` enum('y','n') NOT NULL default 'n', ".
	    "`billingweekend_friday` enum('y','n') NOT NULL default 'n', ".
	    "`billingweekend_saturday` enum('y','n') NOT NULL default 'y', ".
	    "PRIMARY KEY  (`id`) ) TYPE=MyISAM";
	  $result = $DB->Execute($query) or die ("$query query failed");
	  echo "$query<br>\n";

	  //
	  // GRAB THE VALUES FROM GENERAL FIRST BEFORE INSERTING THEM 
	  // INTO THE NEW
	  // SETTINGS TABLE AND REMOVING THE GENERAL FIELDS
	  //
	  $query = "SELECT * FROM general WHERE id = 1";
	  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	  $result = $DB->Execute($query) or die ("query failed");
	  $myresult = $result->fields;
	  $default_group = $myresult['default_group'];
	  $path_to_ccfile = $myresult['path_to_ccfile'];
	  $billingdate_rollover_time = $myresult['billingdate_rollover_time'];
	  $billingweekend_sunday = $myresult['billingweekend_sunday'];
	  $billingweekend_monday = $myresult['billingweekend_monday'];
	  $billingweekend_tuesday = $myresult['billingweekend_tuesday'];
	  $billingweekend_wednesday = $myresult['billingweekend_wednesday'];
	  $billingweekend_thursday = $myresult['billingweekend_thursday'];
	  $billingweekend_friday = $myresult['billingweekend_friday'];	
	  $billingweekend_saturday = $myresult['billingweekend_saturday'];

	  // insert the values from general into the new settings table
	  $query="INSERT INTO `settings` VALUES (1, '1.2.3', 'default_group', 
		'$path_to_ccfile','$billingdate_rollover_time',
		'$billingweekend_sunday',
		'$billingweekend_monday',
		'$billingweekend_tuesday',
		'$billingweekend_wednesday',
		'$billingweekend_thursday',
		'$billingweekend_friday',
		'$billingweekend_saturday')";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";
	
	  // remove the old fields from general
	  $query = "ALTER TABLE `general` DROP `version` ,
	DROP `default_group` ,
	DROP `path_to_ccfile`, 
	DROP `billingdate_rollover_time`,
	DROP `billingweekend_sunday`,
	DROP `billingweekend_monday`,
	DROP `billingweekend_tuesday`,
	DROP `billingweekend_wednesday`,
	DROP `billingweekend_thursday`,
	DROP `billingweekend_friday`,
	DROP `billingweekend_saturday`";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";

	  // add a new footer field to general for invoices
	  $query = "ALTER TABLE `general` ADD `invoice_footer` TEXT NULL";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";	

	  // add the organization id to the billing table
	  $query = "ALTER TABLE `billing` 
		ADD `organization_id` INT NOT NULL DEFAULT '1'";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";

	  // add the organization id to the master services
	  $query = "ALTER TABLE `master_services` 
	ADD `organization_id` INT NOT NULL DEFAULT '1';";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";

	  $query = "ALTER TABLE `billing` CHANGE `pastdue_exempt` `pastdue_exempt` ENUM( 'y', 'n', 'bad_debt' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'n'";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";

	  // Add a cancel reason table to hold cancel reasons to choose from
	  $query = " CREATE TABLE `cancel_reason` ".
	    "(`id` INT NOT NULL AUTO_INCREMENT ,".
	    "`reason` VARCHAR( 128 ) NOT NULL , ".
	    "PRIMARY KEY ( `id` )) TYPE = MYISAM ";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";
  
	  // Add cancel reasons to the new table
	  $query = "INSERT INTO `cancel_reason` (`id`, `reason`) VALUES " .
	    "(1, 'Closing Business'), " .
	    "(2, 'Computer Broken/Unavailable'), " .
	    "(3, 'Connection Problems'), " .
	    "(4, 'Does not use service'), " .
	    "(5, 'Does not want service'), " .
	    "(6, 'Duplicate Account'), " .
	    "(7, 'Fraud'), ". 
	    "(8, 'Moving'), ". 
	    "(9, 'Non-Payment'), " .
	    "(10, 'Outside Coverage Area'), " .
	    "(11, 'Switched to other service provider'), " .
	    "(12, 'Transient Account'), " .
	    "(13, 'Unserviceable Address'), " .
	    "(14, 'Vacation');";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";	  
	  
	  // Add a cancel reason ID number to the customer table to connect it
	  $query = "ALTER TABLE `customer` ADD `cancel_reason` INT NULL";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";

	  $databaseversion = "1.2.3";
	}

	if ($databaseversion == "1.2.3") {

	  // add the modify_notify field to master_services
	  $query = "ALTER TABLE `master_services` ADD `modify_notify` ".
	    "VARCHAR( 32 ) NULL AFTER `activate_notify` ;";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";

	  // add the canceled and cancelwfee and pastdue and notice status to payment_history status
	  $query = " ALTER TABLE `payment_history` CHANGE `status` `status` ".
	    "SET( 'authorized', 'declined', 'pending', 'donotreactivate', ".
	    "'collections', 'turnedoff', 'credit', 'canceled', 'cancelwfee',".
	    "'pastdue', 'noticesent','waiting') ".
	    "CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";

	  // add a login failures table to track failure and eventually
	  // restrict them from trying at all
	   $query = "CREATE TABLE `login_failures` (".
      	     "`ip` VARCHAR( 64 ) NOT NULL ,".
	     "`logintime` DATETIME NOT NULL ) TYPE = MYISAM ";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";

	  // add carrier_dependent field to indicate they get canceled with fee
	  // if they owe money and are on a different past due days track
	  $query = "ALTER TABLE `master_services` ADD `carrier_dependent` ".
	    "ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n';";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";

	  // rename the pastdue_days fields to regular_ fields
	  // as opposed to the new dependent type fields
	  $query = "ALTER TABLE `general` ".
	    "CHANGE `pastdue_days1` `regular_pastdue` INT( 11 ) ".
	    "NOT NULL DEFAULT '0',".
	    "CHANGE `pastdue_days2` `regular_turnoff` INT( 11 ) ".
	    "NOT NULL DEFAULT '0',".
	    "CHANGE `pastdue_days3` `regular_canceled` INT( 11 ) ".
	    "NOT NULL DEFAULT '0' ";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";


	  // add the pastdue counters for carrier_dependent service types
	  $query = "ALTER TABLE `general` ADD ".
	    "`dependent_pastdue` INT NOT NULL DEFAULT '0',".
	    "ADD `dependent_shutoff_notice` INT NOT NULL DEFAULT '0',".
	    "ADD `dependent_turnoff` INT NOT NULL DEFAULT '0',".
	    "ADD `dependent_canceled` INT NOT NULL DEFAULT '0';";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";

	  // add linkurl and linkname to the customer_history for file linking and other links
	  $query = "ALTER TABLE `customer_history` ".
	    "ADD `linkurl` VARCHAR( 255 ) NULL , ".
	    "ADD `linkname` VARCHAR( 64 ) NULL ";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";

	  $query = "ALTER TABLE `settings` ADD `dependent_cancel_url` VARCHAR( 255 ) NULL ;";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";	  

	  $query = "ALTER TABLE `settings` ADD `default_billing_group` VARCHAR( 32 ) NOT NULL DEFAULT 'billing'";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";	  
	  
	  // add nsf type for non-sufficient fund indicator
	  $query = " ALTER TABLE `payment_history` ".
	    "CHANGE `payment_type` `payment_type` ".
	    "SET( 'creditcard', 'check', 'cash', 'eft', 'nsf' ) ".
	    "CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL  ";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";	  

	  // set the version, using the new settings field
	  $query = "UPDATE `settings` SET `version` = '1.3' WHERE `id` =1 LIMIT 1";		
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";
	  
	  $databaseversion = "1.3";
	  
	}

	if ($databaseversion == "1.3") {

	  // add percentage_or_fixed multiply indicator field to tax_rates
	  $query = "ALTER TABLE `tax_rates` ADD `percentage_or_fixed` ".
	    "ENUM( 'percentage', 'fixed' ) NOT NULL DEFAULT 'percentage';";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";

	  // add user_services_id to the customer_history table
	  $query = "ALTER TABLE `customer_history` ADD `user_services_id` ".
	    "INT NULL ;";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";

	  // add rerun flag to billing details
	  $query = "ALTER TABLE `billing_details` ".
	    "ADD `rerun` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n';";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";

	  // add a rerun_date field to the billing_details
	  $query = "ALTER TABLE `billing_details` ADD `rerun_date` DATE NULL;";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";
	  
	  // add payment_applied date to billing details
	  $query = "ALTER TABLE `billing_details` ".
	    "ADD `payment_applied` DATE NULL ;";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";	  

	  // set current rerun dates to null, NULL is now important
	  // if you have upcoming reruns, must reset them after
	  $query = "UPDATE billing SET rerun_date = NULL";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";

	  // add an original_invoice_number field to keep that number around
	  // even when making reruns on new invoices
	  $query =" ALTER TABLE `billing_details` ".
	    "ADD `original_invoice_number` INT NULL";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";	  
	  
	  // set the version, using the new settings field
	  $query = "UPDATE `settings` SET `version` = '1.3.1' ".
	    "WHERE `id` =1 LIMIT 1";		
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";
	}


	if ($databaseversion == "1.3.1") {

	  $query = "ALTER TABLE `user_services` ".
	    "ADD INDEX `master_service_id_index` ( `master_service_id` )";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";
	  
	  $query = "ALTER TABLE `user_services` ".
	    "ADD INDEX `billing_id_index` ( `billing_id` )";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";
	  
	  $query = "ALTER TABLE `billing` ".
	    "ADD INDEX `billing_type_index` ( `billing_type` )";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";
	  
	  $query = "ALTER TABLE `tax_exempt` ".
	    "ADD INDEX `account_number_index` ( `account_number` )";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";
	  
	  $query = "ALTER TABLE `billing_details` ".
	    "ADD INDEX `billing_id_index` ( `billing_id` )";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";
	  
	  $query ="ALTER TABLE `payment_history` ".
	    "ADD INDEX `billing_id_index` ( `billing_id` )";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";
	  
	  // set the version, using the new settings field
	  $query = "UPDATE `settings` SET `version` = '1.3.2' ".
	    "WHERE `id` =1 LIMIT 1";		
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";

	  // add notes field to customer table
	  $query = "ALTER TABLE `customer` ADD `notes` TEXT NULL";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";

	  // add support_notify field to master_services table
	  $query = "ALTER TABLE `master_services` ADD `support_notify` VARCHAR( 32 ) NULL ;";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";

	  // change most of the float fields to decimal type
	  // to fix large number precision
	  $query = "ALTER TABLE `user_services` ".
	    "CHANGE `usage_multiple` `usage_multiple` ".
	    "DECIMAL( 9, 2 ) NOT NULL DEFAULT '1'";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";
	  
	  $query = "ALTER TABLE `payment_history` ".
	    "CHANGE `billing_amount` `billing_amount` ".
	    "DECIMAL( 9, 2 ) NULL DEFAULT NULL";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";
	  
	  $query = "ALTER TABLE `billing_history` ".
	    "CHANGE `new_charges` `new_charges` ".
	    "DECIMAL( 9, 2 ) NOT NULL DEFAULT '0', ".
	    "CHANGE `past_due` `past_due` ".
	    "DECIMAL( 9, 2 ) NULL DEFAULT '0', ".
	    "CHANGE `late_fee` `late_fee` ".
	    "DECIMAL( 9, 2 ) NOT NULL DEFAULT '0', ".
	    "CHANGE `tax_due` `tax_due` ".
	    "DECIMAL( 9, 2 ) NOT NULL DEFAULT '0', ".
	    "CHANGE `total_due` `total_due` ".
	    "DECIMAL( 9, 2 ) NOT NULL DEFAULT '0'";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";
	  
	  $query="ALTER TABLE `billing_details` ".
	    "CHANGE `billed_amount` `billed_amount` ".
	    "DECIMAL( 9, 2 ) NOT NULL DEFAULT '0', ".
	    "CHANGE `paid_amount` `paid_amount` ".
	    "DECIMAL( 9, 2 ) NOT NULL DEFAULT '0', ".
	    "CHANGE `refund_amount` `refund_amount` ".
	    "DECIMAL( 9, 2 ) NOT NULL DEFAULT '0'";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";

	  $query = " ALTER TABLE `general` CHANGE ".
	    "`email_sales` `email_sales` VARCHAR( 128 ) ".
	    "CHARACTER SET latin1 COLLATE latin1_swedish_ci ".
	    "NULL DEFAULT NULL , ".
	    "CHANGE `email_billing` `email_billing` VARCHAR( 128 ) ".
	    "CHARACTER SET latin1 COLLATE latin1_swedish_ci ".
	    "NULL DEFAULT NULL , ".
	    "CHANGE `email_custsvc` `email_custsvc` VARCHAR( 128 ) ".
	    "CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";

	  $query = "INSERT INTO `payment_mode` VALUES (NULL, 'discount')";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";	  

	  $query = " ALTER TABLE `payment_history` CHANGE `payment_type` `payment_type` SET( 'creditcard', 'check', 'cash', 'eft', 'nsf', 'discount' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL  ";
	  $result = $DB->Execute($query) or die ("query failed");
	  echo "$query<br>\n";
	  
	}

	if ($databaseversion == "1.3.2") {

	  // change the normal card number to varchar so it can hold ***
	  // for the truncated card numbers we show to regular users
	  $query = " ALTER TABLE `billing` CHANGE `creditcard_number` ".
	    "`creditcard_number` VARCHAR( 16 ) NULL DEFAULT NULL  ";
	  $result = $DB->Execute($query) or die ("$query failed");
	  echo "$query<br>\n";

	  // add the TEXT field that will hold the ascii armored encrypted card number
	  $query = "ALTER TABLE `billing` ADD `encrypted_creditcard_number` TEXT NULL";
	  $result = $DB->Execute($query) or die ("$query failed");
	  echo "$query<br>\n";
	  
	  // add the export prefix field that holds a prefix for the organization being exported
	  $query = "ALTER TABLE `general` ADD `exportprefix` VARCHAR( 64 ) NULL ;";
	  $result = $DB->Execute($query) or die ("$query failed");
	  echo "$query<br>\n";

	  // add payment_history_id to link individual payments to items
	  $query = "ALTER TABLE `billing_details` ADD `payment_history_id` ".
	    "INT NULL ;";
	  $result = $DB->Execute($query) or die ("$query failed");
	  echo "$query<br>\n";

	  // create the new activity_log table
	  $query = "CREATE TABLE IF NOT EXISTS `activity_log` (".
	    "`datetime` datetime NOT NULL,".
	    "`user` varchar(128) NOT NULL,".
	    "`ip_address` varchar(64) NOT NULL,".
	    "`account_number` int(11) default NULL,".
	    "`activity_type` enum('login','logout','view','edit','create',".
	    "'delete','undelete','export','import','cancel','uncancel') ".
	    "NOT NULL,".
	    "`record_type` enum('dashboard','customer','billing','service',".
	    "'creditcard') NOT NULL,".
	    "`record_id` int(11) default NULL,".
	    "`result` enum('success','failure') NOT NULL".
	    ") ENGINE=MyISAM DEFAULT CHARSET=latin1;";	  

	  $result = $DB->Execute($query) or die ("$query failed");
	  echo "$query<br>\n";	  	  
	  
	  // add automatic_receipt marker to know who wants automatic receipts
	  $query = "ALTER TABLE `billing` ADD `automatic_receipt` ".
	    "ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n';";
	  $result = $DB->Execute($query) or die ("$query failed");
	  echo "$query<br>\n";

	  // add closed_by and closed_date fields to customer_history table
	  $query = "ALTER TABLE `customer_history` ADD `closed_by` ".
	    "VARCHAR( 64 ) NULL ,ADD `closed_date` DATETIME NULL ;";
	  $result = $DB->Execute($query) or die ("$query failed");
	  echo "$query<br>\n";
	  
	  // sub_history table to hold new entries associated with the same customer history entry
	  $query = "CREATE TABLE IF NOT EXISTS `sub_history` (".
	    "`id` int(10) unsigned NOT NULL auto_increment,".
	    "`creation_date` datetime NOT NULL default '0000-00-00 00:00:00',".
	    "`created_by` varchar(20) NOT NULL default 'citrus',".
	    "`customer_history_id` int(11) NOT NULL default '0',".
	    "`description` text NOT NULL,".
	    "PRIMARY KEY  (`id`)".
	    ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
	  $result = $DB->Execute($query) or die ("$query query failed");
	  echo "$query<br>\n";

	  // set the version number in the database to 2.0
	  $query = "UPDATE `settings` SET `version` = '2.0' ".
	    "WHERE `id` =1 LIMIT 1";
	  $result = $DB->Execute($query) or die ("$query failed");
	  echo "$query<br>\n";

	  
	}

	if ($databaseversion == "2.0") {
	  // set the version number in the database to 2.0
	  $query = "UPDATE `settings` SET `version` = '2.0.1' ".
	    "WHERE `id` =1 LIMIT 1";
	  $result = $DB->Execute($query) or die ("$query failed");
	  echo "$query<br>\n";
	}

	echo "<center><h2>Database Updated</h2></center>";
}
else 
{
	$query = "SELECT * FROM general";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("query failed");
	$myresult = $result->fields;
	$databaseversion = $myresult['version'];
	if ($databaseversion == "")
	{
	  // if databaseversion is empty then query the settings table
	  $query = "SELECT version FROM settings";
	  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
	  $result = $DB->Execute($query) or die ("query failed");
	  $myresult = $result->fields;
	  $databaseversion = $myresult['version'];
	  if ($databaseversion == "") {
	    $databaseversion = "0.9.2 or older";
	  }
	}
	echo "<center>	
	This update script only works with versions 0.9.2 or greater
	<p>
	Your database version: <b>$databaseversion</b><p>

	This script will update it to version: <b>2.0.1</b></h3>";

	echo "<p style=\"font-weight: bold;\">Upgrading version 1.3.0 or ".
	  "older will reset the rerun dates ".
	  "to NULL when running this upgrade script.  Please make sure you ".
	  "check for pending reruns before running this script on an active ".
	  "system.</p>";

	if ($databaseversion == "2.0.1") {
		echo "<p><b>Nothing to update</b>";
	} else {
		echo "
		<form action=update.php>
		<input type=hidden name=databaseversion 
			value=\"$databaseversion\">
		<input name=submit type=submit value=\"Update\">
		</form>"; 
	}
}


?>
</center>
</body>
</html>
