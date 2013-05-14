-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 03, 2012 at 09:54 AM
-- Server version: 5.5.22
-- PHP Version: 5.3.10-1ubuntu3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `citrus3`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE IF NOT EXISTS `activity_log` (
  `datetime` datetime NOT NULL,
  `user` varchar(128) NOT NULL,
  `ip_address` varchar(64) NOT NULL,
  `account_number` int(11) DEFAULT NULL,
  `activity_type` enum('login','logout','view','edit','create','delete','undelete','export','import','cancel','uncancel') NOT NULL,
  `record_type` enum('dashboard','customer','billing','service','creditcard') NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `result` enum('success','failure') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`datetime`, `user`, `ip_address`, `account_number`, `activity_type`, `record_type`, `record_id`, `result`) VALUES
('2012-05-03 09:28:48', 'admin', '127.0.0.1', 0, 'login', 'dashboard', 0, 'success'),
('2012-05-03 09:32:37', 'admin', '127.0.0.1', 1, 'view', 'customer', 0, 'success'),
('2012-05-03 09:36:07', 'admin', '127.0.0.1', 1, 'edit', 'billing', 1, 'success'),
('2012-05-03 09:36:40', 'admin', '127.0.0.1', 1, 'edit', 'billing', 1, 'success'),
('2012-05-03 09:49:59', 'admin', '127.0.0.1', 0, 'login', 'dashboard', 0, 'success'),
('2012-05-03 09:50:03', 'admin', '127.0.0.1', 1, 'view', 'customer', 0, 'success');

-- --------------------------------------------------------

--
-- Table structure for table `batch`
--

CREATE TABLE IF NOT EXISTS `batch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `batch`
--

INSERT INTO `batch` (`id`) VALUES
(1),
(2);

-- --------------------------------------------------------

--
-- Table structure for table `billing`
--

CREATE TABLE IF NOT EXISTS `billing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `company` varchar(100) DEFAULT NULL,
  `street` varchar(100) NOT NULL DEFAULT '',
  `city` varchar(100) NOT NULL DEFAULT '',
  `state` char(3) NOT NULL DEFAULT '',
  `country` varchar(100) NOT NULL DEFAULT 'USA',
  `zip` varchar(20) NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL DEFAULT '',
  `fax` varchar(20) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `account_number` int(11) NOT NULL DEFAULT '0',
  `billing_type` int(11) NOT NULL DEFAULT '0',
  `creditcard_number` varchar(16) DEFAULT NULL,
  `creditcard_expire` smallint(4) unsigned zerofill DEFAULT NULL,
  `billing_status` int(11) NOT NULL DEFAULT '0',
  `disable_billing` enum('y','n') DEFAULT NULL,
  `next_billing_date` date DEFAULT NULL,
  `prev_billing_date` date DEFAULT NULL,
  `from_date` date DEFAULT NULL,
  `to_date` date DEFAULT NULL,
  `payment_due_date` date DEFAULT NULL,
  `rerun_date` date DEFAULT NULL,
  `notes` text,
  `pastdue_exempt` enum('y','n','bad_debt') NOT NULL DEFAULT 'n',
  `po_number` varchar(64) DEFAULT NULL,
  `organization_id` int(11) NOT NULL DEFAULT '1',
  `encrypted_creditcard_number` text,
  `automatic_receipt` enum('y','n') NOT NULL DEFAULT 'n',
  `einvoice_type` enum('txt','pdf') NOT NULL DEFAULT 'txt',
  PRIMARY KEY (`id`),
  KEY `billing_type_index` (`billing_type`),
  KEY `account_number_index` (`account_number`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=34 ;

--
-- Dumping data for table `billing`
--

INSERT INTO `billing` (`id`, `name`, `company`, `street`, `city`, `state`, `country`, `zip`, `phone`, `fax`, `contact_email`, `account_number`, `billing_type`, `creditcard_number`, `creditcard_expire`, `billing_status`, `disable_billing`, `next_billing_date`, `prev_billing_date`, `from_date`, `to_date`, `payment_due_date`, `rerun_date`, `notes`, `pastdue_exempt`, `po_number`, `organization_id`, `encrypted_creditcard_number`, `automatic_receipt`, `einvoice_type`) VALUES
(1, 'Example Customer', 'Example Company', 'Example St', 'ExampleCity', 'ABC', 'USA', '12345', '555-555-5555', '555-555-5556', 'pyasi@localhost', 1, 7, '5***********0001', 0909, 0, NULL, '2012-07-03', NULL, '2012-07-03', '2012-08-03', '2012-07-03', NULL, '', 'n', '', 1, '-----BEGIN PGP MESSAGE-----\nVersion: GnuPG v1.4.6 (GNU/Linux)\n\nhQIOA5EpLKAGbJnoEAgAiE78z7h8Cwv3j13JgEnA1mS1NJt4/wC88U7bydZjZX5F\nHPIp89SFlF1OwzTQlJ3Gke3/oVLRQMi5Tg1GMDdTcyon5yqz4Ay7gdO66du21HhA\nVqZYheytSCMbw8mfiHIWj1p0ou4hfhhutk4zbAtlQrHzq6taLL68pSUOcOZskq8Y\nM5MeDZJ+KpIwzuxeEvSWftlxrKT2FL9sOwOB+kGo5j8Ot5wxh/6xckYNdPJiZRuG\n4rZFqwr0X2ybNOoTEhOi65fMpg9ksSTKNvlV82fZmZN6pJhhBwZhEMUUmM/1QYGJ\nNU8N8WhaGNYrnERF4shWR5ctgu9Ke7kOVLarTvoMtQf+LwxeAMV7v7An+0gddF+v\nnUjaZmKB0DdEIXcCyj5vBVTzo6ArwzmIOhOC7eoe8D4nXNrLHsyKHWUt8aEAhCjJ\nurjRePyCqZUUN7lBsoYfMBQugr0myHm8ZqEk6UBEAvmVq4MSoVl3sAeBFJcmss7t\nPweyHbFCZbRwUcExGUaG3xHdCdb29JE3rPD4a3cMLFPecQlmPWHXNNlPHdGVltVF\nOg6pYJ2TUr4eXGIuJr/AKp8snWcx2N3wZVRsEnP9FH2RIvZ+j6b19Ugi/FAC9Qvj\n9G2/cX9a6CQ0vwwciOg7FcjiuTNntwecuatOdTh8lWiiwVlBuTU27h8tu/t5FIFh\nedJBAXY+/vwEoyFKjbXsnU5v6W9mRA6yUSeszxIQ26xfftbd1zyAmUgHwTBmQ3yB\ndhHZ87glQiWZ33rj8fB1SIITABs=\n=Rift\n-----END PGP MESSAGE-----', 'y', 'txt');

-- --------------------------------------------------------

--
-- Table structure for table `billing_details`
--

CREATE TABLE IF NOT EXISTS `billing_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billing_id` int(11) NOT NULL DEFAULT '0',
  `creation_date` date NOT NULL DEFAULT '0000-00-00',
  `user_services_id` int(11) DEFAULT NULL,
  `taxed_services_id` int(11) DEFAULT NULL,
  `invoice_number` int(11) DEFAULT NULL,
  `billed_amount` decimal(9,2) NOT NULL DEFAULT '0.00',
  `paid_amount` decimal(9,2) NOT NULL DEFAULT '0.00',
  `batch` int(11) NOT NULL DEFAULT '0',
  `refund_amount` decimal(9,2) NOT NULL DEFAULT '0.00',
  `refunded` enum('y','n') NOT NULL DEFAULT 'n',
  `refund_date` date DEFAULT NULL,
  `rerun` enum('y','n') DEFAULT 'n',
  `rerun_date` date DEFAULT NULL,
  `payment_applied` date DEFAULT NULL,
  `original_invoice_number` int(11) DEFAULT NULL,
  `payment_history_id` int(11) DEFAULT NULL,
  `recent_invoice_number` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `creation_date` (`creation_date`),
  KEY `billing_id_index` (`billing_id`),
  KEY `invoice_number_index` (`invoice_number`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `billing_details`
--

INSERT INTO `billing_details` (`id`, `billing_id`, `creation_date`, `user_services_id`, `taxed_services_id`, `invoice_number`, `billed_amount`, `paid_amount`, `batch`, `refund_amount`, `refunded`, `refund_date`, `rerun`, `rerun_date`, `payment_applied`, `original_invoice_number`, `payment_history_id`, `recent_invoice_number`) VALUES
(2, 1, '2012-05-03', 40, NULL, 2, 19.95, 19.95, 2, 0.00, 'n', NULL, 'n', NULL, '2012-05-03', 2, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `billing_history`
--

CREATE TABLE IF NOT EXISTS `billing_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `billing_date` date NOT NULL DEFAULT '0000-00-00',
  `created_by` varchar(32) NOT NULL DEFAULT 'citrus',
  `record_type` set('bill','payment') DEFAULT NULL,
  `billing_type` set('creditcard','invoice','einvoice','prepay','prepaycc','free') NOT NULL DEFAULT '',
  `billing_id` int(11) NOT NULL DEFAULT '0',
  `from_date` date DEFAULT '0000-00-00',
  `to_date` date DEFAULT '0000-00-00',
  `payment_due_date` date DEFAULT NULL,
  `details` varchar(32) DEFAULT NULL,
  `new_charges` decimal(9,2) NOT NULL DEFAULT '0.00',
  `past_due` decimal(9,2) DEFAULT '0.00',
  `late_fee` decimal(9,2) NOT NULL DEFAULT '0.00',
  `tax_due` decimal(9,2) NOT NULL DEFAULT '0.00',
  `total_due` decimal(9,2) NOT NULL DEFAULT '0.00',
  `notes` text,
  `credit_applied` decimal(9,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `billing_id_index` (`billing_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `billing_history`
--

INSERT INTO `billing_history` (`id`, `billing_date`, `created_by`, `record_type`, `billing_type`, `billing_id`, `from_date`, `to_date`, `payment_due_date`, `details`, `new_charges`, `past_due`, `late_fee`, `tax_due`, `total_due`, `notes`, `credit_applied`) VALUES
(2, '2012-05-03', 'admin', 'bill', 'invoice', 1, '2012-06-03', '2012-07-03', '2012-06-03', NULL, 19.95, 0.00, 0.00, 0.00, 19.95, '', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `billing_types`
--

CREATE TABLE IF NOT EXISTS `billing_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '',
  `frequency` int(11) NOT NULL DEFAULT '1',
  `method` enum('creditcard','invoice','einvoice','prepay','prepaycc','free') NOT NULL DEFAULT 'creditcard',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=35 ;

--
-- Dumping data for table `billing_types`
--

INSERT INTO `billing_types` (`id`, `name`, `frequency`, `method`) VALUES
(1, 'CreditCard Monthly', 1, 'creditcard'),
(7, 'Invoice Monthly', 1, 'invoice'),
(4, 'CreditCard 6 Months', 6, 'creditcard'),
(6, 'CreditCard 1 Year', 12, 'creditcard'),
(8, 'Invoice Quarterly', 3, 'invoice'),
(9, 'Invoice 6 Months', 6, 'invoice'),
(10, 'Invoice Yearly', 12, 'invoice'),
(13, 'Prepay 6 Months', 6, 'prepay'),
(15, 'Prepay 1 Year', 12, 'prepay'),
(16, 'Free', 0, 'free'),
(18, 'Prepay 2 Years', 24, 'prepay'),
(25, 'Prepay CreditCard 6 Months', 6, 'prepaycc'),
(26, 'Prepay CreditCard 1 Year', 12, 'prepaycc'),
(32, 'E-Invoice Monthly', 1, 'einvoice'),
(33, 'E-Invoice 6 Months', 6, 'einvoice'),
(34, 'E-Invoice Yearly', 12, 'einvoice');

-- --------------------------------------------------------

--
-- Table structure for table `cancel_reason`
--

CREATE TABLE IF NOT EXISTS `cancel_reason` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reason` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
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

-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
--

CREATE TABLE IF NOT EXISTS `ci_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_activity_idx` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ci_sessions`
--

INSERT INTO `ci_sessions` (`session_id`, `ip_address`, `user_agent`, `last_activity`, `user_data`) VALUES
('6d986e405abd53424386872a39b06edb', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:12.0) G', 1335970563, ''),
('dce7e9c00e99fe18250141c2d01ca7c3', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:12.0) G', 1335971592, ''),
('e2961807ecf06a1234dd5f8b66288a59', '127.0.0.1', 'gvfs/1.12.1', 1335971870, ''),
('aeb53719acf6be91633b8c0637e75f2b', '0.0.0.0', '0', 1335971960, ''),
('d6c94d78c05b1c25f90863498732ae56', '0.0.0.0', '0', 1335971982, ''),
('774d55f1b29b25937c240db908b47caf', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:12.0) G', 1336051649, 'a:3:{s:9:"user_name";s:5:"admin";s:14:"account_number";s:1:"1";s:9:"logged_in";b:1;}'),
('bc4fc3fb59088420eed4bf489cdd9f38', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:12.0) G', 1336052159, ''),
('dc53b66c06e070c7b1cc3d93622fef51', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:12.0) G', 1336052189, ''),
('f6eecaf5fa6f96c0ead24600dcafcb90', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:12.0) G', 1336053049, ''),
('72521559d5ac7a9db2156038b83fd28f', '0.0.0.0', '0', 1336053056, ''),
('29e37f0bb9bd6f9b41842376280c7c41', '0.0.0.0', '0', 1336053163, '');

-- --------------------------------------------------------

--
-- Table structure for table `credit_options`
--

CREATE TABLE IF NOT EXISTS `credit_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_services` int(11) NOT NULL DEFAULT '0',
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE IF NOT EXISTS `customer` (
  `source` varchar(100) DEFAULT NULL,
  `signup_date` date NOT NULL DEFAULT '0000-00-00',
  `name` varchar(255) NOT NULL DEFAULT '',
  `company` varchar(255) DEFAULT NULL,
  `street` varchar(255) NOT NULL DEFAULT '',
  `city` varchar(255) NOT NULL DEFAULT '',
  `state` char(3) NOT NULL DEFAULT '',
  `country` varchar(255) NOT NULL DEFAULT 'USA',
  `zip` varchar(20) NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL DEFAULT '',
  `alt_phone` varchar(20) DEFAULT NULL,
  `fax` varchar(20) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `account_number` int(11) NOT NULL AUTO_INCREMENT,
  `secret_question` varchar(254) DEFAULT NULL,
  `secret_answer` varchar(100) DEFAULT NULL,
  `cancel_date` date DEFAULT NULL,
  `removal_date` date DEFAULT NULL,
  `default_billing_id` int(10) unsigned NOT NULL DEFAULT '0',
  `account_manager_password` varchar(60) DEFAULT NULL,
  `cancel_reason` int(11) DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`account_number`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10000 ;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`source`, `signup_date`, `name`, `company`, `street`, `city`, `state`, `country`, `zip`, `phone`, `alt_phone`, `fax`, `contact_email`, `account_number`, `secret_question`, `secret_answer`, `cancel_date`, `removal_date`, `default_billing_id`, `account_manager_password`, `cancel_reason`, `notes`) VALUES
('example', '2005-09-25', 'Example Customer', 'Example Company', 'Example St', 'ExampleCity', 'MAC', 'USA', '12345', '555-555-5555', '555-666-7777', '555-555-5556', 'example@example.com', 1, 'what question?', 'secret', NULL, NULL, 1, 'test', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customer_history`
--

CREATE TABLE IF NOT EXISTS `customer_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `creation_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` varchar(20) NOT NULL DEFAULT 'citrus',
  `notify` varchar(32) NOT NULL DEFAULT '',
  `account_number` int(11) NOT NULL DEFAULT '0',
  `status` enum('automatic','not done','pending','completed') NOT NULL DEFAULT 'automatic',
  `description` text NOT NULL,
  `linkurl` varchar(255) DEFAULT NULL,
  `linkname` varchar(64) DEFAULT NULL,
  `user_services_id` int(11) DEFAULT NULL,
  `closed_by` varchar(64) DEFAULT NULL,
  `closed_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notify` (`notify`),
  KEY `account_number_index` (`account_number`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `example_options`
--

CREATE TABLE IF NOT EXISTS `example_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_services` int(11) NOT NULL DEFAULT '0',
  `username` varchar(12) DEFAULT NULL,
  `password` varchar(12) DEFAULT NULL,
  `operating_system` enum('Windows 9x','WindowsNT/2K/XP','Mac OS 9','Mac OS X','Linux') DEFAULT NULL,
  `service_address` varchar(128) DEFAULT NULL,
  `equipment` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `example_options`
--

INSERT INTO `example_options` (`id`, `user_services`, `username`, `password`, `operating_system`, `service_address`, `equipment`) VALUES
(1, 40, 'myname', 'mypass', 'Linux', '123 Main St.', 'equipment');

-- --------------------------------------------------------

--
-- Table structure for table `field_asset_items`
--

CREATE TABLE IF NOT EXISTS `field_asset_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `master_field_assets_id` int(11) NOT NULL,
  `creation_date` date NOT NULL,
  `serial_number` varchar(254) DEFAULT NULL,
  `status` enum('infield','returned') NOT NULL,
  `sale_type` enum('included','rent','purchase') NOT NULL,
  `user_services_id` int(11) DEFAULT NULL,
  `shipping_tracking_number` varchar(254) DEFAULT NULL,
  `shipping_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `return_notes` varchar(254) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `general`
--

CREATE TABLE IF NOT EXISTS `general` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `org_name` varchar(32) NOT NULL DEFAULT '',
  `org_street` varchar(32) NOT NULL DEFAULT '',
  `org_city` varchar(32) NOT NULL DEFAULT '',
  `org_state` varchar(32) NOT NULL DEFAULT '',
  `org_zip` varchar(32) NOT NULL DEFAULT '',
  `org_country` varchar(32) NOT NULL DEFAULT 'USA',
  `phone_sales` varchar(32) DEFAULT NULL,
  `email_sales` varchar(128) DEFAULT NULL,
  `phone_billing` varchar(32) DEFAULT NULL,
  `email_billing` varchar(128) DEFAULT NULL,
  `phone_custsvc` varchar(32) DEFAULT NULL,
  `email_custsvc` varchar(128) DEFAULT NULL,
  `ccexportvarorder` text,
  `regular_pastdue` int(11) NOT NULL DEFAULT '0',
  `regular_turnoff` int(11) NOT NULL DEFAULT '0',
  `regular_canceled` int(11) NOT NULL DEFAULT '0',
  `default_invoicenote` varchar(255) DEFAULT NULL,
  `pastdue_invoicenote` varchar(255) DEFAULT NULL,
  `turnedoff_invoicenote` varchar(255) DEFAULT NULL,
  `collections_invoicenote` varchar(255) DEFAULT NULL,
  `declined_subject` varchar(64) DEFAULT NULL,
  `declined_message` text,
  `invoice_footer` text,
  `einvoice_footer` text,
  `dependent_pastdue` int(11) NOT NULL DEFAULT '0',
  `dependent_shutoff_notice` int(11) NOT NULL DEFAULT '0',
  `dependent_turnoff` int(11) NOT NULL DEFAULT '0',
  `dependent_canceled` int(11) NOT NULL DEFAULT '0',
  `exportprefix` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `general`
--

INSERT INTO `general` (`id`, `org_name`, `org_street`, `org_city`, `org_state`, `org_zip`, `org_country`, `phone_sales`, `email_sales`, `phone_billing`, `email_billing`, `phone_custsvc`, `email_custsvc`, `ccexportvarorder`, `regular_pastdue`, `regular_turnoff`, `regular_canceled`, `default_invoicenote`, `pastdue_invoicenote`, `turnedoff_invoicenote`, `collections_invoicenote`, `declined_subject`, `declined_message`, `invoice_footer`, `einvoice_footer`, `dependent_pastdue`, `dependent_shutoff_notice`, `dependent_turnoff`, `dependent_canceled`, `exportprefix`) VALUES
(1, 'Citrus DB', '1 Citrus St.', 'Citrus City', 'Orange', '12346', 'USA', '123-456-7890', 'test@citrusdb.org', '617-555-5554', 'billing@citrusdb.org', '555-123456', 'customer@citrusdb.org', '$mybilling_id,$invoice_number,$billing_ccnum,$billing_ccexp,$abstotal,$billing_zip,$billing_street', 1, 14, 30, '', '', '', '', 'Billing Notice', 'Please contact COMPANY at 555-555-5555 concerning an issue billing your credit card.', '', NULL, 0, 0, 0, 0, 'citrus');

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groupname` varchar(50) NOT NULL DEFAULT '',
  `groupmember` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=34 ;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `groupname`, `groupmember`) VALUES
(9, 'users', 'admin'),
(27, 'billing', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `holiday`
--

CREATE TABLE IF NOT EXISTS `holiday` (
  `holiday_date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`holiday_date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `holiday`
--

INSERT INTO `holiday` (`holiday_date`) VALUES
('2006-01-01'),
('2006-05-29'),
('2006-07-04'),
('2006-09-04'),
('2006-11-23'),
('2006-12-25');

-- --------------------------------------------------------

--
-- Table structure for table `keys`
--

CREATE TABLE IF NOT EXISTS `keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(40) NOT NULL,
  `level` int(2) NOT NULL,
  `ignore_limits` tinyint(1) NOT NULL DEFAULT '0',
  `date_created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `linked_services`
--

CREATE TABLE IF NOT EXISTS `linked_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `linkfrom` int(11) NOT NULL DEFAULT '0',
  `linkto` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `login_failures`
--

CREATE TABLE IF NOT EXISTS `login_failures` (
  `ip` varchar(64) NOT NULL,
  `logintime` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `master_field_assets`
--

CREATE TABLE IF NOT EXISTS `master_field_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(128) NOT NULL,
  `status` enum('current','old') NOT NULL DEFAULT 'current',
  `weight` float DEFAULT NULL,
  `category` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `master_services`
--

CREATE TABLE IF NOT EXISTS `master_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_description` varchar(100) NOT NULL DEFAULT '',
  `pricerate` float DEFAULT NULL,
  `frequency` varchar(20) NOT NULL DEFAULT '1',
  `options_table` varchar(50) DEFAULT NULL,
  `category` varchar(25) DEFAULT NULL,
  `selling_active` enum('y','n') DEFAULT 'y',
  `hide_online` enum('y','n') NOT NULL DEFAULT 'n',
  `activate_notify` varchar(32) DEFAULT NULL,
  `modify_notify` varchar(32) DEFAULT NULL,
  `shutoff_notify` varchar(32) NOT NULL DEFAULT '',
  `activation_string` varchar(254) DEFAULT NULL,
  `usage_label` varchar(32) DEFAULT NULL,
  `organization_id` int(11) NOT NULL DEFAULT '1',
  `carrier_dependent` enum('y','n') NOT NULL DEFAULT 'n',
  `support_notify` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

--
-- Dumping data for table `master_services`
--

INSERT INTO `master_services` (`id`, `service_description`, `pricerate`, `frequency`, `options_table`, `category`, `selling_active`, `hide_online`, `activate_notify`, `modify_notify`, `shutoff_notify`, `activation_string`, `usage_label`, `organization_id`, `carrier_dependent`, `support_notify`) VALUES
(3, 'Example Service', 19.95, '1', 'example_options', 'example', 'y', 'n', '', '', '', 'username,password', 'items', 1, 'n', ''),
(2, 'Prorate', 1, '0', 'prorate_options', 'prorate', 'y', 'y', '', '', '', NULL, NULL, 1, 'n', ''),
(1, 'Credit', -1, '0', 'credit_options', 'credit', 'y', 'y', 'admin', '', '', '', 'dollars', 1, 'n', '');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE IF NOT EXISTS `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `commonname` varchar(50) NOT NULL DEFAULT '',
  `modulename` varchar(50) NOT NULL DEFAULT '',
  `sortorder` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sortorder` (`sortorder`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `commonname`, `modulename`, `sortorder`) VALUES
(1, 'Customer', 'customer', 0),
(2, 'Services', 'services', 1),
(3, 'Billing', 'billing', 2),
(4, 'Support', 'support', 3);

-- --------------------------------------------------------

--
-- Table structure for table `module_permissions`
--

CREATE TABLE IF NOT EXISTS `module_permissions` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `modulename` varchar(30) NOT NULL DEFAULT '',
  `permission` char(1) NOT NULL DEFAULT '',
  `user` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

--
-- Dumping data for table `module_permissions`
--

INSERT INTO `module_permissions` (`id`, `modulename`, `permission`, `user`) VALUES
(5, 'customer', 'f', 'users'),
(12, 'services', 'f', 'users'),
(14, 'billing', 'f', 'users'),
(8, 'support', 'f', 'users');

-- --------------------------------------------------------

--
-- Table structure for table `options_urls`
--

CREATE TABLE IF NOT EXISTS `options_urls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `urlname` varchar(25) NOT NULL DEFAULT '',
  `fieldname` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `options_urls`
--

INSERT INTO `options_urls` (`id`, `urlname`, `fieldname`, `url`) VALUES
(1, 'finger', 'username', 'http://www.example.com/finger.cgi?%s1%');

-- --------------------------------------------------------

--
-- Table structure for table `payment_history`
--

CREATE TABLE IF NOT EXISTS `payment_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `creation_date` date NOT NULL DEFAULT '0000-00-00',
  `transaction_code` varchar(32) DEFAULT NULL,
  `billing_id` int(11) NOT NULL DEFAULT '0',
  `creditcard_number` varchar(16) DEFAULT NULL,
  `creditcard_expire` int(4) unsigned zerofill DEFAULT NULL,
  `billing_amount` decimal(9,2) DEFAULT NULL,
  `response_code` varchar(100) DEFAULT NULL,
  `paid_from` date NOT NULL DEFAULT '0000-00-00',
  `paid_to` date NOT NULL DEFAULT '0000-00-00',
  `invoice_number` int(128) DEFAULT NULL,
  `status` set('authorized','declined','pending','donotreactivate','collections','turnedoff','credit','canceled','cancelwfee','pastdue','noticesent','waiting') DEFAULT NULL,
  `payment_type` set('creditcard','check','cash','eft','nsf','discount') DEFAULT NULL,
  `check_number` varchar(32) DEFAULT NULL,
  `avs_response` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `billing_id_index` (`billing_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `payment_history`
--

INSERT INTO `payment_history` (`id`, `creation_date`, `transaction_code`, `billing_id`, `creditcard_number`, `creditcard_expire`, `billing_amount`, `response_code`, `paid_from`, `paid_to`, `invoice_number`, `status`, `payment_type`, `check_number`, `avs_response`) VALUES
(1, '2012-05-03', NULL, 1, NULL, NULL, 19.95, NULL, '0000-00-00', '0000-00-00', 2, 'authorized', 'check', '123', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payment_mode`
--

CREATE TABLE IF NOT EXISTS `payment_mode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `payment_mode`
--

INSERT INTO `payment_mode` (`id`, `name`) VALUES
(1, 'check'),
(2, 'eft'),
(3, 'cash'),
(4, 'discount');

-- --------------------------------------------------------

--
-- Table structure for table `prorate_options`
--

CREATE TABLE IF NOT EXISTS `prorate_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_services` int(11) NOT NULL DEFAULT '0',
  `service_description` varchar(255) DEFAULT NULL,
  `service_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `searches`
--

CREATE TABLE IF NOT EXISTS `searches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `query` text NOT NULL,
  `owner` varchar(32) DEFAULT NULL,
  `outputform` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

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
-- Table structure for table `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` varchar(12) NOT NULL,
  `default_group` varchar(30) DEFAULT NULL,
  `path_to_ccfile` varchar(255) DEFAULT NULL,
  `billingdate_rollover_time` time DEFAULT NULL,
  `billingweekend_sunday` enum('y','n') NOT NULL DEFAULT 'y',
  `billingweekend_monday` enum('y','n') NOT NULL DEFAULT 'n',
  `billingweekend_tuesday` enum('y','n') NOT NULL DEFAULT 'n',
  `billingweekend_wednesday` enum('y','n') NOT NULL DEFAULT 'n',
  `billingweekend_thursday` enum('y','n') NOT NULL DEFAULT 'n',
  `billingweekend_friday` enum('y','n') NOT NULL DEFAULT 'n',
  `billingweekend_saturday` enum('y','n') NOT NULL DEFAULT 'y',
  `dependent_cancel_url` varchar(255) DEFAULT NULL,
  `default_billing_group` varchar(32) NOT NULL DEFAULT 'billing',
  `default_shipping_group` varchar(32) NOT NULL DEFAULT 'shipping',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `version`, `default_group`, `path_to_ccfile`, `billingdate_rollover_time`, `billingweekend_sunday`, `billingweekend_monday`, `billingweekend_tuesday`, `billingweekend_wednesday`, `billingweekend_thursday`, `billingweekend_friday`, `billingweekend_saturday`, `dependent_cancel_url`, `default_billing_group`, `default_shipping_group`) VALUES
(1, '3.0', 'users', '/home/pyasi/Code/io', '16:00:00', 'y', 'n', 'n', 'n', 'n', 'n', 'y', 'http://localhost/cancel', 'billing', 'shipping');

-- --------------------------------------------------------

--
-- Table structure for table `sub_history`
--

CREATE TABLE IF NOT EXISTS `sub_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `creation_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` varchar(20) NOT NULL DEFAULT 'citrus',
  `customer_history_id` int(11) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `taxed_services`
--

CREATE TABLE IF NOT EXISTS `taxed_services` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `master_services_id` int(10) NOT NULL DEFAULT '0',
  `tax_rate_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `tax_exempt`
--

CREATE TABLE IF NOT EXISTS `tax_exempt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_number` int(11) NOT NULL,
  `tax_rate_id` int(11) NOT NULL,
  `customer_tax_id` varchar(64) DEFAULT NULL,
  `expdate` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `account_number_index` (`account_number`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tax_rates`
--

CREATE TABLE IF NOT EXISTS `tax_rates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL DEFAULT '',
  `rate` float NOT NULL DEFAULT '0',
  `if_field` varchar(30) DEFAULT NULL,
  `if_value` varchar(30) DEFAULT NULL,
  `percentage_or_fixed` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=53 ;

--
-- Dumping data for table `tax_rates`
--

INSERT INTO `tax_rates` (`id`, `description`, `rate`, `if_field`, `if_value`, `percentage_or_fixed`) VALUES
(1, 'Federal Telephone Excise Tax', 0.03, '', '', 'percentage'),
(3, 'Alabama Sales Tax', 0.04, 'state', 'AL', 'percentage'),
(4, 'Arizona Sales Tax', 0.056, 'state', 'AZ', 'percentage'),
(5, 'Arkansas Sales Tax', 0.05125, 'state', 'AR', 'percentage'),
(6, 'California Sales Tax', 0.0725, 'state', 'CA', 'percentage'),
(7, 'Colorado Sales Tax', 0.029, 'state', 'CO', 'percentage'),
(8, 'Connecticut Sales Tax', 0.06, 'state', 'CT', 'percentage'),
(9, 'Florida Sales Tax', 0.06, 'state', 'FL', 'percentage'),
(10, 'Georgia Sales Tax', 0.04, 'state', 'GA', 'percentage'),
(11, 'Hawaii Sales Tax', 0.04, 'state', 'HI', 'percentage'),
(12, 'Idaho Sales Tax', 0.06, 'state', 'ID', 'percentage'),
(13, 'Illinois Sales Tax', 0.0625, 'state', 'IL', 'percentage'),
(14, 'Indiana Sales Tax', 0.06, 'state', 'IN', 'percentage'),
(15, 'Iowa Sales Tax', 0.05, 'state', 'IA', 'percentage'),
(16, 'Kansas Sales Tax', 0.053, 'state', 'KS', 'percentage'),
(17, 'Kentucky Sales Tax', 0.06, 'state', 'KY', 'percentage'),
(18, 'Louisiana Sales Tax', 0.04, 'state', 'LA', 'percentage'),
(19, 'Maine Sales Tax', 0.05, 'state', 'ME', 'percentage'),
(20, 'Massachusetts Sales Tax', 0.05, 'state', 'MA', 'percentage'),
(21, 'Michigan Sales Tax', 0.06, 'state', 'MI', 'percentage'),
(22, 'Minnesota Sales Tax', 0.065, 'state', 'MN', 'percentage'),
(23, 'Mississippi Sales Tax', 0.07, 'state', 'MS', 'percentage'),
(24, 'Missouri Sales Tax', 0.04225, 'state', 'MO', 'percentage'),
(25, 'Nebraska Sales Tax', 0.055, 'state', 'NE', 'percentage'),
(26, 'Nevada Sales Tax', 0.065, 'state', 'NV', 'percentage'),
(27, 'New Jersey Sales Tax', 0.06, 'state', 'NJ', 'percentage'),
(28, 'New Mexico Sales Tax', 0.05, 'state', 'NM', 'percentage'),
(29, 'New York Sales Tax', 0.0425, 'state', 'NY', 'percentage'),
(30, 'North Carolina Sales Tax', 0.045, 'state', 'NC', 'percentage'),
(31, 'North Dakota Sales Tax', 0.05, 'state', 'ND', 'percentage'),
(32, 'Ohio Sales Tax', 0.06, 'state', 'OH', 'percentage'),
(33, 'Oklahoma Sales Tax', 0.045, 'state', 'OK', 'percentage'),
(34, 'Pennsylvania Sales Tax', 0.06, 'state', 'PA', 'percentage'),
(35, 'Rhode Island Sales Tax', 0.07, 'state', 'RI', 'percentage'),
(36, 'South Carolina Sales Tax', 0.05, 'state', 'SC', 'percentage'),
(37, 'South Dakota Sales Tax', 0.04, 'state', 'SD', 'percentage'),
(38, 'Tennessee Sales Tax', 0.07, 'state', 'TN', 'percentage'),
(39, 'Texas Sales Tax', 0.0625, 'state', 'TX', 'percentage'),
(40, 'Utah Sales Tax', 0.0475, 'state', 'UT', 'percentage'),
(41, 'Vermont Sales Tax', 0.06, 'state', 'VT', 'percentage'),
(42, 'Virginia Sales Tax', 0.045, 'state', 'VA', 'percentage'),
(43, 'Washington Sales Tax', 0.065, 'state', 'WA', 'percentage'),
(44, 'West Virginia Sales Tax', 0.06, 'state', 'WV', 'percentage'),
(45, 'Wisconsin Sales Tax', 0.05, 'state', 'WI', 'percentage'),
(46, 'Wyoming Sales Tax', 0.04, 'state', 'WY', 'percentage'),
(47, 'District of Columbia Sales Tax', 0.0575, 'state', 'DC', 'percentage');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(60) NOT NULL DEFAULT '',
  `real_name` varchar(50) NOT NULL DEFAULT '',
  `admin` enum('y','n') NOT NULL DEFAULT 'n',
  `manager` enum('y','n') NOT NULL DEFAULT 'n',
  `email` varchar(100) DEFAULT NULL,
  `remote_addr` varchar(15) DEFAULT NULL,
  `screenname` varchar(254) DEFAULT NULL,
  `email_notify` enum('y','n') DEFAULT 'n',
  `screenname_notify` enum('y','n') DEFAULT 'n',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `real_name`, `admin`, `manager`, `email`, `remote_addr`, `screenname`, `email_notify`, `screenname_notify`) VALUES
(5, 'admin', '$P$BIuBRamAQ.iszHJLVNfS0UbYcCdd70/', 'Admin User', 'y', 'y', NULL, '', NULL, 'n', 'n');

-- --------------------------------------------------------

--
-- Table structure for table `user_services`
--

CREATE TABLE IF NOT EXISTS `user_services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_number` int(11) NOT NULL DEFAULT '0',
  `master_service_id` int(11) NOT NULL DEFAULT '0',
  `billing_id` int(11) NOT NULL DEFAULT '0',
  `start_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_datetime` datetime DEFAULT NULL,
  `removal_date` date DEFAULT NULL,
  `salesperson` varchar(40) DEFAULT NULL,
  `usage_multiple` decimal(9,2) NOT NULL DEFAULT '1.00',
  `removed` set('y','n') NOT NULL DEFAULT 'n',
  PRIMARY KEY (`id`),
  KEY `master_service_id_index` (`master_service_id`),
  KEY `billing_id_index` (`billing_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=41 ;

--
-- Dumping data for table `user_services`
--

INSERT INTO `user_services` (`id`, `account_number`, `master_service_id`, `billing_id`, `start_datetime`, `end_datetime`, `removal_date`, `salesperson`, `usage_multiple`, `removed`) VALUES
(1, 1, 3, 1, '2005-09-28 09:09:11', '2006-02-08 14:02:04', '2006-02-08', 'admin', 0.00, 'y'),
(40, 1, 3, 1, '2006-02-08 14:02:33', NULL, NULL, 'admin', 1.00, 'n');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_history`
--

CREATE TABLE IF NOT EXISTS `vendor_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datetime` datetime NOT NULL,
  `entry_type` enum('order','change','recurring bill','onetime bill','disconnect') NOT NULL,
  `entry_date` date NOT NULL,
  `vendor_name` varchar(64) NOT NULL,
  `vendor_bill_id` varchar(128) DEFAULT NULL,
  `vendor_cost` decimal(9,2) DEFAULT NULL,
  `vendor_tax` decimal(9,2) DEFAULT NULL,
  `vendor_item_id` varchar(128) DEFAULT NULL,
  `vendor_invoice_number` varchar(64) DEFAULT NULL,
  `vendor_from_date` varchar(32) DEFAULT NULL,
  `vendor_to_date` varchar(32) DEFAULT NULL,
  `user_services_id` int(11) NOT NULL,
  `account_status` varchar(64) DEFAULT NULL,
  `billed_amount` decimal(9,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_names`
--

CREATE TABLE IF NOT EXISTS `vendor_names` (
  `name` varchar(64) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
