<?php
/*----------------------------------------------------------------------------*/
// CitrusDB - The Open Source Customer Database
// Copyright (C) 2005 Paul Yasi
//
// This program is free software; you can redistribute it and/or modify it under
// the terms of the GNU General Public License as published by the Free Software 
// Foundation; either version 2 of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful, but WITHOUT 
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License along with 
// this program; if not, write to the Free Software Foundation, Inc., 59 Temple 
// Place, Suite 330, Boston, MA 02111-1307 USA
//
// http://www.citrusdb.org
// Read the README file for more details
/*----------------------------------------------------------------------------*/

// indicate the release version of citrusdb

$softwareversion = "2.1";

// check that we are running php version 5 or greater
if (version_compare(phpversion(), "5.0.0", "<")) {
  echo "<b>You Need PHP5 or greater to run this application</b>";
}

// Connect to the database using ADOdb
include('./include/adodb/adodb.inc.php');
$DB = ADONewConnection($sys_dbtype);
$DB->Connect($sys_dbhost, $sys_dbuser, $sys_dbpasswd, $sys_dbname);
$dberror = $DB->ErrorMsg();
if ($dberror) { echo "<b>$dberror</b>"; }

// set the ini variables we want to use
ini_set("session.gc_maxlifetime","7200");
ini_set("register_globals", "Off");

// include the session2 system from adodb
include_once("./include/adodb/session/adodb-session2.php");
ADOdb_Session::config($sys_dbtype, $sys_dbhost, $sys_dbuser, $sys_dbpasswd, $sys_dbname,$options=false);

// show debug info
//$DB->debug = true;

/*--------------------------------------------------------------------*/
// check for too many login falures
/*--------------------------------------------------------------------*/
function checkfailures() {
  global $DB;
  $ipaddress = $_SERVER["REMOTE_ADDR"];
  $query = "SELECT * FROM login_failures WHERE ip = '$ipaddress' ".
    "AND DATE(logintime) = CURRENT_DATE";
  $result = $DB->Execute($query);
  $attempts = $result->RowCount();
  if ($attempts > 5) {
    return TRUE;
  } else {
    return FALSE;
  }
}

?>
