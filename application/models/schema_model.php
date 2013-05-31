<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * ----------------------------------------------------------------------------
 *  perform tasks that require lookups in the database schema like
 *  data types and field names and database upgrades
 * ----------------------------------------------------------------------------
 */

class Schema_model extends CI_Model
{
	function __construct()
	{
	    parent::__construct();
	}

	/*
	 * ----------------------------------------------------------------------------
	 *  Get table information from the information schema
	 * ----------------------------------------------------------------------------
	 */
	public function columns($database, $table)
	{
		$query = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, 
			COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
			WHERE table_name = ? AND table_schema = ?";	

		$result = $this->db->query($query, array($table, $database))
				or die ("columns Query Failed"); 

		return $result;
	}


	/*
	 * ----------------------------------------------------------------------------
	 *  select enum items from an enum column_type data
	 *  and generate a drop down menu with them
	 * ----------------------------------------------------------------------------
	 */
	public function enum_select($column_type, $name, $default) 
	{
		echo "<select name='$name'>\n\t"; 
		if($default) 
		{
			echo "<option selected value='$default'>$default</option>\n\t";
		}

		$enums = substr($column_type,5,-1); 
		echo "enums: $enums";
		$enums = preg_replace("/'/","",$enums); 
		$enums = explode(",",$enums); 
		foreach($enums as $val) 
		{ 
			echo "<option value='$val'>$val</option>\n\t"; 
		}//----end foreach 
		echo "\r</select>"; 
    }

    
    public function databaseversion()
    {
        $query = "SELECT version FROM settings";
        $result = $this->db->query($query) or die ("databaseversion query failed");
        $myresult = $result->row_array();
        
        return $myresult['version']; 
    }

    public function update_database_version($databaseversion) 
    {
        if ($databaseversion == "1.2.3") {

            // add the modify_notify field to master_services
            $query = "ALTER TABLE `master_services` ADD `modify_notify` ".
                "VARCHAR( 32 ) NULL AFTER `activate_notify` ;";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            // add the canceled and cancelwfee and pastdue and notice status to payment_history status
            $query = " ALTER TABLE `payment_history` CHANGE `status` `status` ".
                "SET( 'authorized', 'declined', 'pending', 'donotreactivate', ".
                "'collections', 'turnedoff', 'credit', 'canceled', 'cancelwfee',".
                "'pastdue', 'noticesent','waiting') ".
                "CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            // add a login failures table to track failure and eventually
            // restrict them from trying at all
            $query = "CREATE TABLE `login_failures` (".
                "`ip` VARCHAR( 64 ) NOT NULL ,".
                "`logintime` DATETIME NOT NULL ) TYPE = MYISAM ";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            // add carrier_dependent field to indicate they get canceled with fee
            // if they owe money and are on a different past due days track
            $query = "ALTER TABLE `master_services` ADD `carrier_dependent` ".
                "ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n';";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            // rename the pastdue_days fields to regular_ fields
            // as opposed to the new dependent type fields
            $query = "ALTER TABLE `general` ".
                "CHANGE `pastdue_days1` `regular_pastdue` INT( 11 ) ".
                "NOT NULL DEFAULT '0',".
                "CHANGE `pastdue_days2` `regular_turnoff` INT( 11 ) ".
                "NOT NULL DEFAULT '0',".
                "CHANGE `pastdue_days3` `regular_canceled` INT( 11 ) ".
                "NOT NULL DEFAULT '0' ";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";


            // add the pastdue counters for carrier_dependent service types
            $query = "ALTER TABLE `general` ADD ".
                "`dependent_pastdue` INT NOT NULL DEFAULT '0',".
                "ADD `dependent_shutoff_notice` INT NOT NULL DEFAULT '0',".
                "ADD `dependent_turnoff` INT NOT NULL DEFAULT '0',".
                "ADD `dependent_canceled` INT NOT NULL DEFAULT '0';";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            // add linkurl and linkname to the customer_history for file linking and other links
            $query = "ALTER TABLE `customer_history` ".
                "ADD `linkurl` VARCHAR( 255 ) NULL , ".
                "ADD `linkname` VARCHAR( 64 ) NULL ";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            $query = "ALTER TABLE `settings` ADD `dependent_cancel_url` VARCHAR( 255 ) NULL ;";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";	  

            $query = "ALTER TABLE `settings` ADD `default_billing_group` VARCHAR( 32 ) NOT NULL DEFAULT 'billing'";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";	  

            // add nsf type for non-sufficient fund indicator
            $query = " ALTER TABLE `payment_history` ".
                "CHANGE `payment_type` `payment_type` ".
                "SET( 'creditcard', 'check', 'cash', 'eft', 'nsf' ) ".
                "CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL  ";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";	  

            // set the version, using the new settings field
            $query = "UPDATE `settings` SET `version` = '1.3' WHERE `id` =1 LIMIT 1";		
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            $databaseversion = "1.3";

        }

        if ($databaseversion == "1.3") {

            // add percentage_or_fixed multiply indicator field to tax_rates
            $query = "ALTER TABLE `tax_rates` ADD `percentage_or_fixed` ".
                "ENUM( 'percentage', 'fixed' ) NOT NULL DEFAULT 'percentage';";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            // add user_services_id to the customer_history table
            $query = "ALTER TABLE `customer_history` ADD `user_services_id` ".
                "INT NULL ;";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            // add rerun flag to billing details
            $query = "ALTER TABLE `billing_details` ".
                "ADD `rerun` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n';";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            // add a rerun_date field to the billing_details
            $query = "ALTER TABLE `billing_details` ADD `rerun_date` DATE NULL;";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            // add payment_applied date to billing details
            $query = "ALTER TABLE `billing_details` ".
                "ADD `payment_applied` DATE NULL ;";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";	  

            // set current rerun dates to null, NULL is now important
            // if you have upcoming reruns, must reset them after
            $query = "UPDATE billing SET rerun_date = NULL";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            // add an original_invoice_number field to keep that number around
            // even when making reruns on new invoices
            $query =" ALTER TABLE `billing_details` ".
                "ADD `original_invoice_number` INT NULL";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";	  

            // set the version, using the new settings field
            $query = "UPDATE `settings` SET `version` = '1.3.1' ".
                "WHERE `id` =1 LIMIT 1";		
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            $databaseversion = "1.3.1";
        }


        if ($databaseversion == "1.3.1") {

            $query = "ALTER TABLE `user_services` ".
                "ADD INDEX `master_service_id_index` ( `master_service_id` )";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            $query = "ALTER TABLE `user_services` ".
                "ADD INDEX `billing_id_index` ( `billing_id` )";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            $query = "ALTER TABLE `billing` ".
                "ADD INDEX `billing_type_index` ( `billing_type` )";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            $query = "ALTER TABLE `tax_exempt` ".
                "ADD INDEX `account_number_index` ( `account_number` )";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            $query = "ALTER TABLE `billing_details` ".
                "ADD INDEX `billing_id_index` ( `billing_id` )";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            $query ="ALTER TABLE `payment_history` ".
                "ADD INDEX `billing_id_index` ( `billing_id` )";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            // set the version, using the new settings field
            $query = "UPDATE `settings` SET `version` = '1.3.2' ".
                "WHERE `id` =1 LIMIT 1";		
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            // add notes field to customer table
            $query = "ALTER TABLE `customer` ADD `notes` TEXT NULL";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            // add support_notify field to master_services table
            $query = "ALTER TABLE `master_services` ADD `support_notify` VARCHAR( 32 ) NULL ;";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            // change most of the float fields to decimal type
            // to fix large number precision
            $query = "ALTER TABLE `user_services` ".
                "CHANGE `usage_multiple` `usage_multiple` ".
                "DECIMAL( 9, 2 ) NOT NULL DEFAULT '1'";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            $query = "ALTER TABLE `payment_history` ".
                "CHANGE `billing_amount` `billing_amount` ".
                "DECIMAL( 9, 2 ) NULL DEFAULT NULL";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

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
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            $query="ALTER TABLE `billing_details` ".
                "CHANGE `billed_amount` `billed_amount` ".
                "DECIMAL( 9, 2 ) NOT NULL DEFAULT '0', ".
                "CHANGE `paid_amount` `paid_amount` ".
                "DECIMAL( 9, 2 ) NOT NULL DEFAULT '0', ".
                "CHANGE `refund_amount` `refund_amount` ".
                "DECIMAL( 9, 2 ) NOT NULL DEFAULT '0'";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            $query = " ALTER TABLE `general` CHANGE ".
                "`email_sales` `email_sales` VARCHAR( 128 ) ".
                "CHARACTER SET latin1 COLLATE latin1_swedish_ci ".
                "NULL DEFAULT NULL , ".
                "CHANGE `email_billing` `email_billing` VARCHAR( 128 ) ".
                "CHARACTER SET latin1 COLLATE latin1_swedish_ci ".
                "NULL DEFAULT NULL , ".
                "CHANGE `email_custsvc` `email_custsvc` VARCHAR( 128 ) ".
                "CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            $query = "INSERT INTO `payment_mode` VALUES (NULL, 'discount')";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";	  

            $query = " ALTER TABLE `payment_history` CHANGE `payment_type` `payment_type` SET( 'creditcard', 'check', 'cash', 'eft', 'nsf', 'discount' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL  ";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            $databaseversion = "1.3.2";

        }

        if ($databaseversion == "1.3.2") {

            // change the normal card number to varchar so it can hold ***
            // for the truncated card numbers we show to regular users
            $query = " ALTER TABLE `billing` CHANGE `creditcard_number` ".
                "`creditcard_number` VARCHAR( 16 ) NULL DEFAULT NULL  ";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            // add the TEXT field that will hold the ascii armored encrypted card number
            $query = "ALTER TABLE `billing` ADD `encrypted_creditcard_number` TEXT NULL";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            // add the export prefix field that holds a prefix for the organization being exported
            $query = "ALTER TABLE `general` ADD `exportprefix` VARCHAR( 64 ) NULL ;";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            // add payment_history_id to link individual payments to items
            $query = "ALTER TABLE `billing_details` ADD `payment_history_id` ".
                "INT NULL ;";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

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

            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";	  	  

            // add automatic_receipt marker to know who wants automatic receipts
            $query = "ALTER TABLE `billing` ADD `automatic_receipt` ".
                "ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n';";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            // add closed_by and closed_date fields to customer_history table
            $query = "ALTER TABLE `customer_history` ADD `closed_by` ".
                "VARCHAR( 64 ) NULL ,ADD `closed_date` DATETIME NULL ;";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            // sub_history table to hold new entries associated with the same customer history entry
            $query = "CREATE TABLE IF NOT EXISTS `sub_history` (".
                "`id` int(10) unsigned NOT NULL auto_increment,".
                "`creation_date` datetime NOT NULL default '0000-00-00 00:00:00',".
                "`created_by` varchar(20) NOT NULL default 'citrus',".
                "`customer_history_id` int(11) NOT NULL default '0',".
                "`description` text NOT NULL,".
                "PRIMARY KEY  (`id`)".
                ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
            $result = $this->db->query($query) or die ("$query query failed");
            echo "$query\n";

            // set the version number in the database to 2.0
            $query = "UPDATE `settings` SET `version` = '2.0' ".
                "WHERE `id` =1 LIMIT 1";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            $databaseversion = "2.0";


        }

        if ($databaseversion == "2.0") {
            // set the version number in the database to 2.0
            $query = "UPDATE `settings` SET `version` = '2.0.1' ".
                "WHERE `id` =1 LIMIT 1";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            $databaseversion = "2.0.1";
        }

        if ($databaseversion == "2.0.1") {
            // add screenname field for xmpp to user table
            $query = "ALTER TABLE `user` ADD `screenname` VARCHAR( 254 ) NULL";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            // add notify variables too
            $query = "ALTER TABLE `user` ADD `email_notify` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n', ADD `screenname_notify` ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n';";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            // add credit_applied field to billing_history	  
            $query = "ALTER TABLE `billing_history` ADD `credit_applied` DECIMAL( 9, 2 ) NOT NULL DEFAULT '0.00'";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            // set the version number in the database to 2.0.2
            $query = "UPDATE `settings` SET `version` = '2.0.2' ".
                "WHERE `id` =1 LIMIT 1";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";
        }

        if ($databaseversion == "2.0.2") {
            // add master_field_assets table
            $query = "CREATE TABLE `master_field_assets` ( ".
                "`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY , ".
                "`description` VARCHAR( 128 ) NOT NULL , ".
                "`status` ENUM( 'current', 'old' ) NOT NULL DEFAULT 'current', ".
                "`weight` FLOAT NULL, ".
                "`category` VARCHAR( 128) NOT NULL ".
                ") ENGINE = MYISAM  ";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            // add field_asset_items table
            $query = "CREATE TABLE `field_asset_items` ( ".
                "`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY , ".
                "`master_field_assets_id` INT NOT NULL , ".
                "`creation_date` DATE NOT NULL , ".
                "`serial_number` VARCHAR( 254 ) NULL , ".
                "`status` ENUM( 'infield', 'returned' ) NOT NULL , ".
                "`sale_type` ENUM( 'included','rent', 'purchase' ) NOT NULL , ".
                "`user_services_id` INT NULL , ".
                "`shipping_tracking_number` VARCHAR( 254 ) NULL , ".
                "`shipping_date` DATE NULL , ".
                "`return_date` DATE NULL , ".
                "`return_notes` VARCHAR( 254 ) NULL ".
                ") ENGINE = MYISAM ";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            // add default shipping group field
            $query = "ALTER TABLE `settings` ADD `default_shipping_group` VARCHAR( 32 ) NOT NULL DEFAULT 'shipping';";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            // set the version number in the database to 2.1
            $query = "UPDATE `settings` SET `version` = '2.1' ".
                "WHERE `id` =1 LIMIT 1";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";	  

            $databaseversion = "2.1";

        }

        if ($databaseversion == "2.1") {
            $query = "CREATE TABLE `vendor_names` (".
                "`name` VARCHAR( 64 ) NOT NULL ".
                ") ENGINE = MYISAM ";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            $query = "CREATE TABLE `vendor_history` ( ".
                "`id` INT NOT NULL AUTO_INCREMENT ,".
                "`datetime` DATETIME NOT NULL ,".
                "`entry_type` ENUM( 'order','change','recurring bill','onetime bill','disconnect' ) NOT NULL ,".
                "`entry_date` DATE NOT NULL ,".
                "`vendor_name` VARCHAR( 64 ) NOT NULL ,".
                "`vendor_bill_id` VARCHAR( 128 ) NULL ,".
                "`vendor_cost` DECIMAL( 9, 2 ) NULL ,".
                "`vendor_tax` DECIMAL( 9, 2 ) NULL ,".
                "`vendor_item_id` VARCHAR( 128 ) NULL ,".
                "`vendor_invoice_number` VARCHAR( 64 ) NULL ,".
                "`vendor_from_date` VARCHAR( 32 ) NULL ,".
                "`vendor_to_date` VARCHAR( 32 ) NULL ,".
                "`user_services_id` INT NOT NULL ,".
                "`account_status` VARCHAR( 64 ) NULL ,".
                "`billed_amount` DECIMAL( 9, 2 ) NULL ,".
                "PRIMARY KEY ( `id` )".
                ") ENGINE = MYISAM ";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            // index the notify column of customer_history for speed up
            $query = " ALTER TABLE `customer_history` ADD INDEX ( `notify` )  ";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            // allow the next_billing_date to be set NULL
            $query = " ALTER TABLE `billing` CHANGE `next_billing_date` `next_billing_date` DATE NULL  ";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            // set the version number in the database to 2.1.1
            $query = "UPDATE `settings` SET `version` = '2.1.1' ".
                "WHERE `id` =1 LIMIT 1";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            $databaseversion = "2.1.1";

        }

        if ($databaseversion == "2.1.1") {
            // set the version number in the database to 2.2
            $query = "UPDATE `settings` SET `version` = '2.2' ".
                "WHERE `id` =1 LIMIT 1";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";	  

            $databaseversion = "2.2";
        }

        if ($databaseversion == "2.2") {
            // add the recent_invoice_number to billing details for new credit card rerun method
            // that keeps the regular invoice number the same and just makes a new invoice
            // with a pastdue amount, keeping items on old invoice itself
            $query = "ALTER TABLE  `billing_details` ADD  `recent_invoice_number` INT NULL DEFAULT NULL";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            // add indexes
            $query = "ALTER TABLE `billing_history` ADD INDEX  `billing_id_index` ( `billing_id` )";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            $query = "ALTER TABLE `billing_details` ADD INDEX  `invoice_number_index` ( `invoice_number` )";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            $query = "ALTER TABLE `customer_history` ADD INDEX  `account_number_index` (  `account_number` )";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            $query = "ALTER TABLE `billing` ADD INDEX  `account_number_index` (  `account_number` )";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            // set the version number in the database to 2.3
            $query = "UPDATE `settings` SET `version` = '2.3' ".
                "WHERE `id` =1 LIMIT 1";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";	  

            $databaseversion = "2.3";
        }

        if ($databaseversion == "2.3") {
            // make sure the user table unique
            $query = "ALTER TABLE  `user` ADD UNIQUE (`username`)";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";	  	  

            // increase size of password field to hold new bcrypt length passwords
            $query = "ALTER TABLE  `user` CHANGE  `password`  `password` VARCHAR( 60 ) NOT NULL DEFAULT  ''";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            // increase size of account_manager_password field to hold bcrypt length passwords
            $query = "ALTER TABLE  `customer` CHANGE  `account_manager_password`  ".
                "`account_manager_password` VARCHAR( 60 ) NULL DEFAULT NULL";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";

            // set the version number in the database to 2.4
            $query = "UPDATE `settings` SET `version` = '2.4' ".
                "WHERE `id` =1 LIMIT 1";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";	  

            $databaseversion = "2.4";
        }

        if ($databaseversion == "2.4" OR $databaseversion == "2.4.1" OR $databaseversion == "2.4.2") {

            // make new session table for codeigniter
            $query = "CREATE TABLE IF NOT EXISTS  `ci_sessions` (
                session_id varchar(40) DEFAULT '0' NOT NULL,
                ip_address varchar(45) DEFAULT '0' NOT NULL,
                user_agent varchar(120) NOT NULL,
                last_activity int(10) unsigned DEFAULT 0 NOT NULL,
                user_data text DEFAULT '' NOT NULL,
                PRIMARY KEY (session_id)
            );";

            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            // drop old sessions2 table used by adodb
            $query = "DROP TABLE sessions2";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            // add new api_keys table
            $query = "CREATE TABLE `api_keys` (".
                "`id` int(11) NOT NULL AUTO_INCREMENT,".
                "`key` varchar(40) NOT NULL,".
                "`level` int(2) NOT NULL,".
                "`ignore_limits` tinyint(1) NOT NULL DEFAULT '0',".
                "`date_created` int(11) NOT NULL,".
                "PRIMARY KEY (`id`)".
                ") ENGINE=MyISAM DEFAULT CHARSET=utf8;";

            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            // change ccexportvarorder to TEXT field
            $query = "ALTER TABLE `general` 
                CHANGE `ccexportvarorder` `ccexportvarorder` text NOT NULL";
            $result = $this->db->query($query) or die ("query failed");
            echo "$query\n";

            // set the version number in the database to 3.0
            $query = "UPDATE `settings` SET `version` = '3.0' ".
                "WHERE `id` =1 LIMIT 1";
            $result = $this->db->query($query) or die ("$query failed");
            echo "$query\n";	  

            $databaseversion = "3.0";
        }

        return $databaseversion;
    }

}
