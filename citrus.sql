-- phpMyAdmin SQL Dump
-- version 2.6.4-pl4
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Mar 06, 2006 at 07:25 AM
-- Server version: 4.0.24
-- PHP Version: 4.3.10-16
-- 
-- Database: `citrus`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `batch`
-- 

CREATE TABLE `batch` (
  `id` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `batch`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `billing`
-- 

CREATE TABLE `billing` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `company` varchar(100) default NULL,
  `street` varchar(100) NOT NULL default '',
  `city` varchar(100) NOT NULL default '',
  `state` char(3) NOT NULL default '',
  `country` varchar(100) NOT NULL default 'USA',
  `zip` varchar(20) NOT NULL default '',
  `phone` varchar(20) NOT NULL default '',
  `fax` varchar(20) default NULL,
  `contact_email` varchar(100) default NULL,
  `account_number` int(11) NOT NULL default '0',
  `billing_type` int(11) NOT NULL default '0',
  `creditcard_number` varchar(16) default NULL,
  `creditcard_expire` smallint(4) unsigned zerofill default NULL,
  `billing_status` int(11) NOT NULL default '0',
  `disable_billing` enum('y','n') default NULL,
  `next_billing_date` date default NULL,
  `prev_billing_date` date default NULL,
  `from_date` date default NULL,
  `to_date` date default NULL,
  `payment_due_date` date default NULL,
  `rerun_date` date default NULL,
  `notes` text,
  `pastdue_exempt` enum('y','n','bad_debt') NOT NULL default 'n',
  `po_number` varchar(64) default NULL,
  `organization_id` int(11) NOT NULL default '1',
  `encrypted_creditcard_number` text NULL default NULL,
  `automatic_receipt` enum('y','n') NOT NULL default 'n',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=34 ;

-- 
-- Dumping data for table `billing`
-- 

INSERT INTO `billing` VALUES (1, 'Example Customer', 'Example Company', 'Example St', 'ExampleCity', 'ABC', 'USA', '12345', '555-555-5555', '555-555-5556', '', 1, 7, '5***********0001', 0909, 0, NULL, '2007-10-07', NULL, '2007-10-07', '2007-11-07', '2007-10-07', NULL, '', 'n', '', 1, '-----BEGIN PGP MESSAGE-----\nVersion: GnuPG v1.4.6 (GNU/Linux)\n\nhQIOA5EpLKAGbJnoEAgAiE78z7h8Cwv3j13JgEnA1mS1NJt4/wC88U7bydZjZX5F\nHPIp89SFlF1OwzTQlJ3Gke3/oVLRQMi5Tg1GMDdTcyon5yqz4Ay7gdO66du21HhA\nVqZYheytSCMbw8mfiHIWj1p0ou4hfhhutk4zbAtlQrHzq6taLL68pSUOcOZskq8Y\nM5MeDZJ+KpIwzuxeEvSWftlxrKT2FL9sOwOB+kGo5j8Ot5wxh/6xckYNdPJiZRuG\n4rZFqwr0X2ybNOoTEhOi65fMpg9ksSTKNvlV82fZmZN6pJhhBwZhEMUUmM/1QYGJ\nNU8N8WhaGNYrnERF4shWR5ctgu9Ke7kOVLarTvoMtQf+LwxeAMV7v7An+0gddF+v\nnUjaZmKB0DdEIXcCyj5vBVTzo6ArwzmIOhOC7eoe8D4nXNrLHsyKHWUt8aEAhCjJ\nurjRePyCqZUUN7lBsoYfMBQugr0myHm8ZqEk6UBEAvmVq4MSoVl3sAeBFJcmss7t\nPweyHbFCZbRwUcExGUaG3xHdCdb29JE3rPD4a3cMLFPecQlmPWHXNNlPHdGVltVF\nOg6pYJ2TUr4eXGIuJr/AKp8snWcx2N3wZVRsEnP9FH2RIvZ+j6b19Ugi/FAC9Qvj\n9G2/cX9a6CQ0vwwciOg7FcjiuTNntwecuatOdTh8lWiiwVlBuTU27h8tu/t5FIFh\nedJBAXY+/vwEoyFKjbXsnU5v6W9mRA6yUSeszxIQ26xfftbd1zyAmUgHwTBmQ3yB\ndhHZ87glQiWZ33rj8fB1SIITABs=\n=Rift\n-----END PGP MESSAGE-----', 'n');

-- --------------------------------------------------------

-- 
-- Table structure for table `billing_details`
-- 

CREATE TABLE `billing_details` (
  `id` int(11) NOT NULL auto_increment,
  `billing_id` int(11) NOT NULL default '0',
  `creation_date` date NOT NULL default '0000-00-00',
  `user_services_id` int(11) default NULL,
  `taxed_services_id` int(11) default NULL,
  `invoice_number` int(11) default NULL,
  `billed_amount` decimal(9,2) NOT NULL default '0',
  `paid_amount` decimal(9,2) NOT NULL default '0',
  `batch` int(11) NOT NULL default '0',
  `refund_amount` decimal(9,2) NOT NULL default '0',
  `refunded` enum('y','n') NOT NULL default 'n',
  `refund_date` date NULL,
  `rerun` enum('y','n') default 'n',
  `rerun_date` date NULL, 
  `payment_applied` date NULL,
  `original_invoice_number` int(11) NULL,
  `payment_history_id` int(11) NULL,
  `recent_invoice_number` int(11) NULL,
  PRIMARY KEY  (`id`),
  KEY `creation_date` (`creation_date`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `billing_details`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `billing_history`
-- 

CREATE TABLE `billing_history` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `billing_date` date NOT NULL default '0000-00-00',
  `created_by` varchar(32) NOT NULL default 'citrus',
  `record_type` set('bill','payment') default NULL,
  `billing_type` set('creditcard','invoice','einvoice','prepay','prepaycc','free') NOT NULL default '',
  `billing_id` int(11) NOT NULL default '0',
  `from_date` date default '0000-00-00',
  `to_date` date default '0000-00-00',
  `payment_due_date` date default NULL,
  `details` varchar(32) default NULL,
  `new_charges` decimal(9,2) NOT NULL default '0',
  `past_due` decimal(9,2) default '0',
  `late_fee` decimal(9,2) NOT NULL default '0',
  `tax_due` decimal(9,2) NOT NULL default '0',
  `total_due` decimal(9,2) NOT NULL default '0',
  `notes` text,
  `credit_applied` decimal(9,2) default '0',	
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `billing_history`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `billing_types`
-- 

CREATE TABLE `billing_types` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(32) NOT NULL default '',
  `frequency` int(11) NOT NULL default '1',
  `method` enum('creditcard','invoice','einvoice','prepay','prepaycc','free') NOT NULL default 'creditcard',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=35 ;

-- 
-- Dumping data for table `billing_types`
-- 

INSERT INTO `billing_types` VALUES (1, 'CreditCard Monthly', 1, 'creditcard');
INSERT INTO `billing_types` VALUES (7, 'Invoice Monthly', 1, 'invoice');
INSERT INTO `billing_types` VALUES (4, 'CreditCard 6 Months', 6, 'creditcard');
INSERT INTO `billing_types` VALUES (6, 'CreditCard 1 Year', 12, 'creditcard');
INSERT INTO `billing_types` VALUES (8, 'Invoice Quarterly', 3, 'invoice');
INSERT INTO `billing_types` VALUES (9, 'Invoice 6 Months', 6, 'invoice');
INSERT INTO `billing_types` VALUES (10, 'Invoice Yearly', 12, 'invoice');
INSERT INTO `billing_types` VALUES (13, 'Prepay 6 Months', 6, 'prepay');
INSERT INTO `billing_types` VALUES (15, 'Prepay 1 Year', 12, 'prepay');
INSERT INTO `billing_types` VALUES (16, 'Free', 0, 'free');
INSERT INTO `billing_types` VALUES (18, 'Prepay 2 Years', 24, 'prepay');
INSERT INTO `billing_types` VALUES (25, 'Prepay CreditCard 6 Months', 6, 'prepaycc');
INSERT INTO `billing_types` VALUES (26, 'Prepay CreditCard 1 Year', 12, 'prepaycc');
INSERT INTO `billing_types` VALUES (32, 'E-Invoice Monthly', 1, 'einvoice');
INSERT INTO `billing_types` VALUES (33, 'E-Invoice 6 Months', 6, 'einvoice');
INSERT INTO `billing_types` VALUES (34, 'E-Invoice Yearly', 12, 'einvoice');

-- --------------------------------------------------------

-- 
-- Table structure for table `credit_options`
-- 

CREATE TABLE `credit_options` (
  `id` int(11) NOT NULL auto_increment,
  `user_services` int(11) NOT NULL default '0',
  `description` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `credit_options`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `customer`
-- 

CREATE TABLE `customer` (
  `source` varchar(100) default NULL,
  `signup_date` date NOT NULL default '0000-00-00',
  `name` varchar(255) NOT NULL default '',
  `company` varchar(255) default NULL,
  `street` varchar(255) NOT NULL default '',
  `city` varchar(255) NOT NULL default '',
  `state` char(3) NOT NULL default '',
  `country` varchar(255) NOT NULL default 'USA',
  `zip` varchar(20) NOT NULL default '',
  `phone` varchar(20) NOT NULL default '',
  `alt_phone` varchar(20) default NULL,
  `fax` varchar(20) default NULL,
  `contact_email` varchar(255) default NULL,
  `account_number` int(11) NOT NULL auto_increment,
  `secret_question` varchar(254) default NULL,
  `secret_answer` varchar(100) default NULL,
  `cancel_date` date default NULL,
  `removal_date` date default NULL,
  `default_billing_id` int(10) unsigned NOT NULL default '0',
  `account_manager_password` varchar(32) default NULL,
  `cancel_reason` int(11) NULL,
  `notes` text, 
  PRIMARY KEY  (`account_number`)
) TYPE=MyISAM AUTO_INCREMENT=10000 ;

-- 
-- Dumping data for table `customer`
-- 

INSERT INTO `customer` VALUES ('example', '2005-09-25', 'Example Customer', 'Example Company', 'Example St', 'ExampleCity', 'MAC', 'USA', '12345', '555-555-5555', '555-666-7777', '555-555-5556', 'example@example.com', 1, 'what question?', 'secret', NULL, NULL, 1, 'test', NULL, NULL);

-- --------------------------------------------------------

-- 
-- Table structure for table `customer_history`
-- 

CREATE TABLE `customer_history` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `creation_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` varchar(20) NOT NULL default 'citrus',
  `notify` varchar(32) NOT NULL default '',
  `account_number` int(11) NOT NULL default '0',
  `status` enum('automatic','not done','pending','completed') NOT NULL default 'automatic',
  `description` text NOT NULL,
  `linkurl` VARCHAR( 255 ) NULL,
  `linkname` VARCHAR( 64 ) NULL,
  `user_services_id` INT NULL,
  `closed_by` varchar(64) NULL,
  `closed_date` datetime NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=3 ;

-- 
-- Dumping data for table `customer_history`
-- 


-- 
-- Table structure for table `example_options`
-- 

CREATE TABLE `example_options` (
  `id` int(11) NOT NULL auto_increment,
  `user_services` int(11) NOT NULL default '0',
  `username` varchar(12) default NULL,
  `password` varchar(12) default NULL,
  `operating_system` enum('Windows 9x','WindowsNT/2K/XP','Mac OS 9','Mac OS X','Linux') default NULL,
  `service_address` varchar(128) default NULL,
  `equipment` varchar(128) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `example_options`
-- 

INSERT INTO `example_options` VALUES (1, 40, 'myname', 'mypass', 'Linux', '123 Main St.', 'equipment');

-- --------------------------------------------------------

-- 
-- Table structure for table `general`
-- 

CREATE TABLE `general` (
  `id` int(11) NOT NULL auto_increment,
  `org_name` varchar(32) NOT NULL default '',
  `org_street` varchar(32) NOT NULL default '',
  `org_city` varchar(32) NOT NULL default '',
  `org_state` varchar(32) NOT NULL default '',
  `org_zip` varchar(32) NOT NULL default '',
  `org_country` varchar(32) NOT NULL default 'USA',
  `phone_sales` varchar(32) default NULL,
  `email_sales` varchar(128) default NULL,
  `phone_billing` varchar(32) default NULL,
  `email_billing` varchar(128) default NULL,
  `phone_custsvc` varchar(32) default NULL,
  `email_custsvc` varchar(128) default NULL,
  `ccexportvarorder` varchar(255) NOT NULL default '$mybilling_id,$invoice_number,$billing_ccnum,$billing_ccexp,$abstotal,$billing_zip,$billing_street',
  `regular_pastdue` int(11) NOT NULL default '0',
  `regular_turnoff` int(11) NOT NULL default '0',
  `regular_canceled` int(11) NOT NULL default '0',
  `default_invoicenote` VARCHAR( 255 ) NULL,
  `pastdue_invoicenote` VARCHAR( 255 ) NULL,
  `turnedoff_invoicenote` VARCHAR( 255 ) NULL,
  `collections_invoicenote` VARCHAR( 255 ) NULL, 
  `declined_subject` VARCHAR( 64 ) NULL, 
  `declined_message` text NULL,
  `invoice_footer` text NULL,
  `dependent_pastdue` INT NOT NULL DEFAULT '0',
  `dependent_shutoff_notice` INT NOT NULL DEFAULT '0',
  `dependent_turnoff` INT NOT NULL DEFAULT '0',
  `dependent_canceled` INT NOT NULL DEFAULT '0',
  `exportprefix` VARCHAR( 64 ) NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `general`
-- 

INSERT INTO `general` VALUES (1,'Citrus DB', '1 Citrus St.', 'Citrus City', 'Orange', '12346', 'USA', '123-456-7890', 'test@citrusdb.org', '617-555-5554', 'billing@citrusdb.org', '555-123456', 'customer@citrusdb.org', '$mybilling_id,$invoice_number,$billing_ccnum,$billing_ccexp,$abstotal,$billing_zip,$billing_street', 1, 14, 30,'','','','','Billing Notice','Please contact COMPANY at 555-555-5555 concerning an issue billing your credit card.','',0,0,0,0,'citrus');

-- --------------------------------------------------------

-- 
-- Table structure for table `groups`
-- 

CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `groupname` varchar(50) NOT NULL default '',
  `groupmember` varchar(32) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=34 ;

-- 
-- Dumping data for table `groups`
-- 

INSERT INTO `groups` VALUES (9, 'users', 'admin');
INSERT INTO `groups` VALUES (27, 'billing', 'admin');

-- --------------------------------------------------------

-- 
-- Table structure for table `holiday`
-- 

CREATE TABLE `holiday` (
  `holiday_date` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`holiday_date`)
) TYPE=MyISAM;

-- 
-- Dumping data for table `holiday`
-- 

INSERT INTO `holiday` VALUES ('2006-01-01');
INSERT INTO `holiday` VALUES ('2006-05-29');
INSERT INTO `holiday` VALUES ('2006-07-04');
INSERT INTO `holiday` VALUES ('2006-09-04');
INSERT INTO `holiday` VALUES ('2006-11-23');
INSERT INTO `holiday` VALUES ('2006-12-25');

-- --------------------------------------------------------

-- 
-- Table structure for table `linked_services`
-- 

CREATE TABLE `linked_services` (
  `id` int(11) NOT NULL auto_increment,
  `linkfrom` int(11) NOT NULL default '0',
  `linkto` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=4 ;

-- 
-- Dumping data for table `linked_services`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `master_services`
-- 

CREATE TABLE `master_services` (
  `id` int(11) NOT NULL auto_increment,
  `service_description` varchar(100) NOT NULL default '',
  `pricerate` float default NULL,
  `frequency` varchar(20) NOT NULL default '1',
  `options_table` varchar(50) default NULL,
  `category` varchar(25) default NULL,
  `selling_active` enum('y','n') default 'y',
  `hide_online` enum('y','n') NOT NULL default 'n',
  `activate_notify` varchar(32) default NULL,
  `modify_notify` varchar(32) default NULL, 
  `shutoff_notify` varchar(32) NOT NULL default '',
  `activation_string` varchar(254) default NULL,
  `usage_label` varchar(32) default NULL,
  `organization_id` int(11) NOT NULL default '1',
  `carrier_dependent` enum('y','n') NOT NULL default 'n',
  `support_notify` varchar(32) default NULL,   
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=18 ;

-- 
-- Dumping data for table `master_services`
-- 

INSERT INTO `master_services` VALUES (3, 'Example Service', 19.95, '1', 'example_options', 'example', 'y', 'n', '', '', '', 'username,password', 'items', 1, 'n', '');
INSERT INTO `master_services` VALUES (2, 'Prorate', 1, '0', 'prorate_options', 'prorate', 'y', 'y', '', '', '', NULL, NULL,1, 'n', '');
INSERT INTO `master_services` VALUES (1, 'Credit', -1, '0', 'credit_options', 'credit', 'y', 'y', 'admin', '', '', '', 'dollars',1, 'n', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `module_permissions`
-- 

CREATE TABLE `module_permissions` (
  `id` int(4) NOT NULL auto_increment,
  `modulename` varchar(30) NOT NULL default '',
  `permission` char(1) NOT NULL default '',
  `user` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=21 ;

-- 
-- Dumping data for table `module_permissions`
-- 

INSERT INTO `module_permissions` VALUES (5, 'customer', 'f', 'users');
INSERT INTO `module_permissions` VALUES (12, 'services', 'f', 'users');
INSERT INTO `module_permissions` VALUES (14, 'billing', 'f', 'users');
INSERT INTO `module_permissions` VALUES (8, 'support', 'f', 'users');

-- --------------------------------------------------------

-- 
-- Table structure for table `modules`
-- 

CREATE TABLE `modules` (
  `id` int(11) NOT NULL auto_increment,
  `commonname` varchar(50) NOT NULL default '',
  `modulename` varchar(50) NOT NULL default '',
  `sortorder` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `sortorder` (`sortorder`)
) TYPE=MyISAM AUTO_INCREMENT=8 ;

-- 
-- Dumping data for table `modules`
-- 

INSERT INTO `modules` VALUES (1, 'Customer', 'customer', 0);
INSERT INTO `modules` VALUES (2, 'Services', 'services', 1);
INSERT INTO `modules` VALUES (3, 'Billing', 'billing', 2);
INSERT INTO `modules` VALUES (4, 'Support', 'support', 3);

-- --------------------------------------------------------

-- 
-- Table structure for table `options_urls`
-- 

CREATE TABLE `options_urls` (
  `id` int(11) NOT NULL auto_increment,
  `urlname` varchar(25) NOT NULL default '',
  `fieldname` varchar(255) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `options_urls`
-- 

INSERT INTO `options_urls` VALUES (1, 'finger', 'username', 'http://www.example.com/finger.cgi?%s1%');

-- --------------------------------------------------------

-- 
-- Table structure for table `payment_history`
-- 

CREATE TABLE `payment_history` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `creation_date` date NOT NULL default '0000-00-00',
  `transaction_code` varchar(32) default NULL,
  `billing_id` int(11) NOT NULL default '0',
  `creditcard_number` varchar(16) default NULL,
  `creditcard_expire` int(4) unsigned zerofill default NULL,
  `billing_amount` decimal(9,2) default NULL,
  `response_code` varchar(100) default NULL,
  `paid_from` date NOT NULL default '0000-00-00',
  `paid_to` date NOT NULL default '0000-00-00',
  `invoice_number` int(128) default NULL,
  `status` set('authorized','declined','pending','donotreactivate','collections','turnedoff','credit','canceled', 'cancelwfee', 'pastdue','noticesent','waiting') default NULL,
  `payment_type` set('creditcard','check','cash','eft','nsf','discount') default NULL,
  `check_number` varchar(32) default NULL,
  `avs_response` varchar(32) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `payment_history`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `prorate_options`
-- 

CREATE TABLE `prorate_options` (
  `id` int(11) NOT NULL auto_increment,
  `user_services` int(11) NOT NULL default '0',
  `service_description` varchar(255) default NULL,
  `service_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `prorate_options`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `searches`
-- 

CREATE TABLE `searches` (
  `id` int(11) NOT NULL auto_increment,
  `query` text NOT NULL,
  `owner` varchar(32) default NULL,
  `outputform` text,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=12 ;

-- 
-- Dumping data for table `searches`
--
INSERT INTO `searches` (`id`, `query`, `owner`, `outputform`) VALUES
(1, 'SELECT * FROM customer WHERE account_number = %s1%', NULL, ''),
(2, '(SELECT * FROM customer WHERE name LIKE ''%%s1%%'') UNION (SELECT * FROM customer WHERE company LIKE ''%%s1%%'')', NULL, NULL),
(3, 'SELECT name,company,street,phone,account_number FROM customer WHERE phone LIKE ''%%s1%%'' OR phone LIKE ''%%s2%%''', NULL, NULL),
(4, 'SELECT * FROM customer c LEFT JOIN billing b on b.id = c.default_billing_id WHERE c.signup_date BETWEEN ''%s1%'' AND ''%s2%'' ORDER BY c.signup_date', NULL, NULL),
(5, 'SELECT * FROM billing WHERE id = %s1%', NULL, NULL),
(6, 'SELECT * FROM billing WHERE company LIKE ''%%s1%%''', NULL, NULL),
(7, 'SELECT * FROM billing WHERE payment_due_date = ''%%s1%%''', NULL, NULL),
(8, 'SELECT * FROM customer_history WHERE id = %s1%', NULL, NULL),
(9, 'SELECT * FROM example_options do LEFT JOIN user_services us ON us.id = do.user_services WHERE username LIKE ''%%s1%%'' ', NULL, NULL),
(10, 'SELECT * FROM example_options wo LEFT JOIN user_services us ON us.id = wo.user_services WHERE password LIKE ''%%s1%%'' ', NULL, NULL),
(11, 'SELECT * FROM example_options wo LEFT JOIN user_services us ON us.id = wo.user_services WHERE equipment LIKE ''%%s1%%'' ', NULL, NULL),
(12, 'SELECT cu.account_number, cu.name, cu.company, cu.street, cu.phone\r\nFROM customer cu WHERE cu.street LIKE ''%%s1%%''', NULL, NULL),
(13, 'SELECT bh.id AS InvoiceNumber, b.account_number, b.name, b.company FROM `billing_history` bh LEFT JOIN billing b ON bh.billing_id = b.id WHERE bh.id = ''%s1%''', NULL, NULL),
(14, 'SELECT * FROM billing WHERE contact_email LIKE ''%%s1%%''', NULL, NULL),
(15, 'SELECT b.id id, b.name, b.company, b.account_number account_number, b.next_billing_date next_billing_date, bt.name billing_type, c.cancel_date cancel_date, c.removal_date removal_date, ms.service_description service_description FROM user_services us LEFT JOIN billing b ON us.billing_id = b.id LEFT JOIN customer c ON b.account_number = c.account_number LEFT JOIN billing_types bt ON b.billing_type = bt.id LEFT JOIN master_services ms ON us.master_service_id = ms.id WHERE b.next_billing_date BETWEEN ''%s1%'' AND ''%s2%'' AND us.removed <> ''y'' AND bt.method <> ''free'' GROUP BY b.id ORDER BY b.next_billing_date', NULL, NULL),
(16, 'SELECT * FROM customer_history WHERE DATE(creation_date) = ''%s1%'' ORDER BY created_by ASC', NULL, NULL),
(17, 'SELECT * FROM customer_history WHERE created_by = ''%s1%'' ORDER BY creation_date DESC', NULL, NULL);


-- --------------------------------------------------------

-- 
-- Table structure for table `tax_rates`
-- 

CREATE TABLE `tax_rates` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(50) NOT NULL default '',
  `rate` float NOT NULL default '0',
  `if_field` varchar(30) default NULL,
  `if_value` varchar(30) default NULL,
  `percentage_or_fixed` enum('percentage','fixed') NOT NULL default 'percentage',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=53 ;

-- 
-- Dumping data for table `tax_rates`
-- 

INSERT INTO `tax_rates` VALUES (1, 'Federal Telephone Excise Tax', 0.03, '', '','percentage');
INSERT INTO `tax_rates` VALUES (3, 'Alabama Sales Tax', 0.04, 'state', 'AL','percentage');
INSERT INTO `tax_rates` VALUES (4, 'Arizona Sales Tax', 0.056, 'state', 'AZ','percentage');
INSERT INTO `tax_rates` VALUES (5, 'Arkansas Sales Tax', 0.05125, 'state', 'AR','percentage');
INSERT INTO `tax_rates` VALUES (6, 'California Sales Tax', 0.0725, 'state', 'CA','percentage');
INSERT INTO `tax_rates` VALUES (7, 'Colorado Sales Tax', 0.029, 'state', 'CO','percentage');
INSERT INTO `tax_rates` VALUES (8, 'Connecticut Sales Tax', 0.06, 'state', 'CT','percentage');
INSERT INTO `tax_rates` VALUES (9, 'Florida Sales Tax', 0.06, 'state', 'FL','percentage');
INSERT INTO `tax_rates` VALUES (10, 'Georgia Sales Tax', 0.04, 'state', 'GA','percentage');
INSERT INTO `tax_rates` VALUES (11, 'Hawaii Sales Tax', 0.04, 'state', 'HI','percentage');
INSERT INTO `tax_rates` VALUES (12, 'Idaho Sales Tax', 0.06, 'state', 'ID','percentage');
INSERT INTO `tax_rates` VALUES (13, 'Illinois Sales Tax', 0.0625, 'state', 'IL','percentage');
INSERT INTO `tax_rates` VALUES (14, 'Indiana Sales Tax', 0.06, 'state', 'IN','percentage');
INSERT INTO `tax_rates` VALUES (15, 'Iowa Sales Tax', 0.05, 'state', 'IA','percentage');
INSERT INTO `tax_rates` VALUES (16, 'Kansas Sales Tax', 0.053, 'state', 'KS','percentage');
INSERT INTO `tax_rates` VALUES (17, 'Kentucky Sales Tax', 0.06, 'state', 'KY','percentage');
INSERT INTO `tax_rates` VALUES (18, 'Louisiana Sales Tax', 0.04, 'state', 'LA','percentage');
INSERT INTO `tax_rates` VALUES (19, 'Maine Sales Tax', 0.05, 'state', 'ME','percentage');
INSERT INTO `tax_rates` VALUES (20, 'Massachusetts Sales Tax', 0.05, 'state', 'MA','percentage');
INSERT INTO `tax_rates` VALUES (21, 'Michigan Sales Tax', 0.06, 'state', 'MI','percentage');
INSERT INTO `tax_rates` VALUES (22, 'Minnesota Sales Tax', 0.065, 'state', 'MN','percentage');
INSERT INTO `tax_rates` VALUES (23, 'Mississippi Sales Tax', 0.07, 'state', 'MS','percentage');
INSERT INTO `tax_rates` VALUES (24, 'Missouri Sales Tax', 0.04225, 'state', 'MO','percentage');
INSERT INTO `tax_rates` VALUES (25, 'Nebraska Sales Tax', 0.055, 'state', 'NE','percentage');
INSERT INTO `tax_rates` VALUES (26, 'Nevada Sales Tax', 0.065, 'state', 'NV','percentage');
INSERT INTO `tax_rates` VALUES (27, 'New Jersey Sales Tax', 0.06, 'state', 'NJ','percentage');
INSERT INTO `tax_rates` VALUES (28, 'New Mexico Sales Tax', 0.05, 'state', 'NM','percentage');
INSERT INTO `tax_rates` VALUES (29, 'New York Sales Tax', 0.0425, 'state', 'NY','percentage');
INSERT INTO `tax_rates` VALUES (30, 'North Carolina Sales Tax', 0.045, 'state', 'NC','percentage');
INSERT INTO `tax_rates` VALUES (31, 'North Dakota Sales Tax', 0.05, 'state', 'ND','percentage');
INSERT INTO `tax_rates` VALUES (32, 'Ohio Sales Tax', 0.06, 'state', 'OH','percentage');
INSERT INTO `tax_rates` VALUES (33, 'Oklahoma Sales Tax', 0.045, 'state', 'OK','percentage');
INSERT INTO `tax_rates` VALUES (34, 'Pennsylvania Sales Tax', 0.06, 'state', 'PA','percentage');
INSERT INTO `tax_rates` VALUES (35, 'Rhode Island Sales Tax', 0.07, 'state', 'RI','percentage');
INSERT INTO `tax_rates` VALUES (36, 'South Carolina Sales Tax', 0.05, 'state', 'SC','percentage');
INSERT INTO `tax_rates` VALUES (37, 'South Dakota Sales Tax', 0.04, 'state', 'SD','percentage');
INSERT INTO `tax_rates` VALUES (38, 'Tennessee Sales Tax', 0.07, 'state', 'TN','percentage');
INSERT INTO `tax_rates` VALUES (39, 'Texas Sales Tax', 0.0625, 'state', 'TX','percentage');
INSERT INTO `tax_rates` VALUES (40, 'Utah Sales Tax', 0.0475, 'state', 'UT','percentage');
INSERT INTO `tax_rates` VALUES (41, 'Vermont Sales Tax', 0.06, 'state', 'VT','percentage');
INSERT INTO `tax_rates` VALUES (42, 'Virginia Sales Tax', 0.045, 'state', 'VA','percentage');
INSERT INTO `tax_rates` VALUES (43, 'Washington Sales Tax', 0.065, 'state', 'WA','percentage');
INSERT INTO `tax_rates` VALUES (44, 'West Virginia Sales Tax', 0.06, 'state', 'WV','percentage');
INSERT INTO `tax_rates` VALUES (45, 'Wisconsin Sales Tax', 0.05, 'state', 'WI','percentage');
INSERT INTO `tax_rates` VALUES (46, 'Wyoming Sales Tax', 0.04, 'state', 'WY','percentage');
INSERT INTO `tax_rates` VALUES (47, 'District of Columbia Sales Tax', 0.0575, 'state', 'DC','percentage');

-- --------------------------------------------------------

-- 
-- Table structure for table `taxed_services`
-- 

CREATE TABLE `taxed_services` (
  `id` int(10) NOT NULL auto_increment,
  `master_services_id` int(10) NOT NULL default '0',
  `tax_rate_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `taxed_services`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `user`
-- 

CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(50) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `real_name` varchar(50) NOT NULL default '',
  `admin` enum('y','n') NOT NULL default 'n',
  `manager` enum('y','n') NOT NULL default 'n',
  `email` varchar(100) default NULL,
  `remote_addr` varchar(15) default NULL,
  `screenname` varchar(254) default NULL,
  `email_notify` enum('y','n') default 'n',
  `screenname_notify` enum('y','n') default 'n',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=24 ;

-- 
-- Dumping data for table `user`
-- 

INSERT INTO `user` VALUES (5, 'admin', '098f6bcd4621d373cade4e832627b4f6', 'Admin User', 'y', 'y', NULL, '', NULL, 'n','n');

-- --------------------------------------------------------

-- 
-- Table structure for table `user_services`
-- 

CREATE TABLE `user_services` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `account_number` int(11) NOT NULL default '0',
  `master_service_id` int(11) NOT NULL default '0',
  `billing_id` int(11) NOT NULL default '0',
  `start_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `end_datetime` datetime default NULL,
  `removal_date` date default NULL,
  `salesperson` varchar(40) default NULL,
  `usage_multiple` decimal(9,2) NOT NULL default '1',
  `removed` set('y','n') NOT NULL default 'n',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=41 ;

-- 
-- Dumping data for table `user_services`
-- 

INSERT INTO `user_services` VALUES (1, 1, 3, 1, '2005-09-28 09:09:11', '2006-02-08 14:02:04', '2006-02-08', 'admin', 0, 'y');
INSERT INTO `user_services` VALUES (40, 1, 3, 1, '2006-02-08 14:02:33', NULL, NULL, 'admin', 1, 'n');


--
-- table structure for tax_exempt
--

CREATE TABLE `tax_exempt` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`account_number` INT( 11 ) NOT NULL ,
`tax_rate_id` INT( 11 ) NOT NULL ,
`customer_tax_id` VARCHAR( 64 ) NULL ,
`expdate` DATE NULL
) TYPE=MyISAM ;

--
-- sessions2 support from adodb
--

CREATE TABLE sessions2(
	  sesskey VARCHAR( 64 ) NOT NULL DEFAULT '',
	  expiry TIMESTAMP NOT NULL ,
	  expireref VARCHAR( 250 ) DEFAULT '',
	  created TIMESTAMP NOT NULL ,
	  modified TIMESTAMP NOT NULL ,
	  sessdata LONGTEXT,
	PRIMARY KEY ( sesskey ) ,
	INDEX sess2_expiry( expiry ),
	INDEX sess2_expireref( expireref )
) TYPE=MyISAM ;


--
-- Table structure for payment_mode
--
CREATE TABLE `payment_mode` (
 `id` int(11) NOT NULL auto_increment,
 `name` varchar(32),
 PRIMARY KEY (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;


--
-- Insert data into payment_mode table
--
INSERT INTO `payment_mode` VALUES (1, 'check'), (2, 'eft'), (3, 'cash'), (4, 'discount');

-- 
-- Table structure for table `settings`
-- 

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL auto_increment,
  `version` varchar(12) NOT NULL,
  `default_group` varchar(30) default NULL,
  `path_to_ccfile` varchar(255) default NULL,
  `billingdate_rollover_time` time default NULL,  
  `billingweekend_sunday` enum('y','n') NOT NULL default 'y',
  `billingweekend_monday` enum('y','n') NOT NULL default 'n',
  `billingweekend_tuesday` enum('y','n') NOT NULL default 'n',
  `billingweekend_wednesday` enum('y','n') NOT NULL default 'n',
  `billingweekend_thursday` enum('y','n') NOT NULL default 'n',
  `billingweekend_friday` enum('y','n') NOT NULL default 'n',
  `billingweekend_saturday` enum('y','n') NOT NULL default 'y',
  `dependent_cancel_url` VARCHAR( 255 ) NULL,
  `default_billing_group` VARCHAR( 32 ) NOT NULL DEFAULT 'billing',
  `default_shipping_group` VARCHAR( 32 ) NOT NULL DEFAULT 'shipping',	
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `settings`
-- 

INSERT INTO `settings` VALUES (1, '2.2.1', 'users', '/home/pyasi/citrus_project/io','16:00:00','y','n','n','n','n','n','y','http://localhost/cancel', 'billing', 'shipping');



--
-- Table structure for table `cancel_reason`
--

CREATE TABLE IF NOT EXISTS `cancel_reason` (
  `id` int(11) NOT NULL auto_increment,
  `reason` varchar(128) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `cancel_reason`
--

INSERT INTO `cancel_reason` (`id`, `reason`) VALUES
(1, 'Closing Business'),
(2, 'Computer Broken/Unavailable'),
(3, 'Connection Problems'),
(4, 'Does not use service'),
(5, 'Does not want service'),
(6, 'Duplicate Account'),
(7, 'Fraud'),
(8, 'Moving'),
(9, 'Non-Payment'),
(10, 'Outside Coverage Area'),
(11, 'Switched to other service provider'),
(12, 'Transient Account'),
(13, 'Unserviceable Address'),
(14, 'Vacation');

 CREATE TABLE `login_failures` (
`ip` VARCHAR( 64 ) NOT NULL ,
`logintime` DATETIME NOT NULL ) ENGINE = MYISAM ;

--
-- add table indexes
--
ALTER TABLE `user_services` ADD INDEX `master_service_id_index` ( `master_service_id` );
ALTER TABLE `user_services` ADD INDEX `billing_id_index` ( `billing_id` );
ALTER TABLE `billing` ADD INDEX `billing_type_index` ( `billing_type` );
ALTER TABLE `tax_exempt` ADD INDEX `account_number_index` ( `account_number` );
ALTER TABLE `billing_details` ADD INDEX `billing_id_index` ( `billing_id` );
ALTER TABLE `payment_history` ADD INDEX `billing_id_index` ( `billing_id` );
ALTER TABLE `customer_history` ADD INDEX ( `notify` );  
ALTER TABLE  `citrus`.`billing_history` ADD INDEX  `billing_id_index` (  `id` ,  `billing_id` );

--
-- Table structure for activity_log table
--

CREATE TABLE IF NOT EXISTS `activity_log` (
  `datetime` datetime NOT NULL,
  `user` varchar(128) NOT NULL,
  `ip_address` varchar(64) NOT NULL,
  `account_number` int(11) default NULL,
  `activity_type` enum('login','logout','view','edit','create','delete','undelete','export','import','cancel','uncancel') NOT NULL,
  `record_type` enum('dashboard','customer','billing','service','creditcard') NOT NULL,
  `record_id` int(11) default NULL,
  `result` enum('success','failure') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for sub_history table
--

CREATE TABLE IF NOT EXISTS `sub_history` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `creation_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` varchar(20) NOT NULL default 'citrus',
  `customer_history_id` int(11) NOT NULL default '0',
  `description` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE `master_field_assets` ( 
    `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY , 
    `description` VARCHAR( 128 ) NOT NULL , 
    `status` ENUM( 'current', 'old' ) NOT NULL DEFAULT 'current', 
    `weight` FLOAT NULL, 
    `category` VARCHAR( 128 ) NOT NULL 
    ) ENGINE = MYISAM;  

CREATE TABLE `field_asset_items` (
    `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY , 
    `master_field_assets_id` INT NOT NULL , 
    `creation_date` DATE NOT NULL , 
    `serial_number` VARCHAR( 254 ) NULL , 
    `status` ENUM( 'infield', 'returned' ) NOT NULL , 
    `sale_type` ENUM( 'included','rent', 'purchase' ) NOT NULL , 
    `user_services_id` INT NULL , 
    `shipping_tracking_number` VARCHAR( 254 ) NULL , 
    `shipping_date` DATE NULL , 
    `return_date` DATE NULL , 
    `return_notes` VARCHAR( 254 ) NULL
    ) ENGINE = MYISAM ;

CREATE TABLE `vendor_names` (
    `name` VARCHAR( 64 ) NOT NULL
    ) ENGINE = MYISAM;


CREATE TABLE `vendor_history` (
   `id` INT NOT NULL AUTO_INCREMENT ,
   `datetime` DATETIME NOT NULL ,
   `entry_type` ENUM( 'order','change','recurring bill','onetime bill','disconnect' ) NOT NULL ,
   `entry_date` DATE NOT NULL ,
   `vendor_name` VARCHAR( 64 ) NOT NULL ,
   `vendor_bill_id` VARCHAR( 128 ) NULL ,
   `vendor_cost` DECIMAL( 9, 2 ) NULL ,
   `vendor_tax` DECIMAL( 9, 2 ) NULL ,
   `vendor_item_id` VARCHAR( 128 ) NULL ,
   `vendor_invoice_number` VARCHAR( 64 ) NULL ,
   `vendor_from_date` VARCHAR( 32 ) NULL ,
   `vendor_to_date` VARCHAR( 32 ) NULL ,
   `user_services_id` INT NOT NULL ,
   `account_status` VARCHAR( 64 ) NULL ,
   `billed_amount` DECIMAL( 9, 2 ) NULL ,
   PRIMARY KEY ( `id` )
   ) ENGINE = MYISAM; 