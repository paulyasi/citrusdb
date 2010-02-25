<?php
// Copyright (C) 2008  Paul Yasi (paul at citrusdb.org)
// read the README file for more information

// generic ticket creation function
function create_ticket($DB, $user, $notify, $account_number, $status,
		       $description, $linkname = NULL, $linkurl = NULL,
		       $reminderdate = NULL, $user_services_id = NULL)
{
  if ($reminderdate) {
    if ($user_services_id) {
    // add ticket to customer_history table
    $query = "INSERT into customer_history ".
      "(creation_date, created_by, notify, account_number,".
      "status, description, linkurl, linkname, user_services_id) ".
      "VALUES ('$reminderdate', '$user', '$notify', '$account_number',".
      "'$status', '$description', '$linkurl', '$linkname', '$user_services_id')";
    } else {
      $query = "INSERT into customer_history ".
      "(creation_date, created_by, notify, account_number,".
      "status, description, linkurl, linkname) ".
      "VALUES ('$reminderdate', '$user', '$notify', '$account_number',".
      "'$status', '$description', '$linkurl', '$linkname')";
    }
  } else {
    if ($user_services_id) {
    // add ticket to customer_history table
      $query = "INSERT into customer_history ".
      "(creation_date, created_by, notify, account_number,".
      "status, description, linkurl, linkname, user_services_id) ".
      "VALUES (CURRENT_TIMESTAMP, '$user', '$notify', '$account_number',".
      "'$status', '$description', '$linkurl', '$linkname', '$user_services_id')";
    } else {
      $query = "INSERT into customer_history ".
      "(creation_date, created_by, notify, account_number,".
      "status, description, linkurl, linkname) ".
      "VALUES (CURRENT_TIMESTAMP, '$user', '$notify', '$account_number',".
      "'$status', '$description', '$linkurl', '$linkname')";      
    }
  }

  $result = $DB->Execute($query) or die ("create_ticket query failed");
  $ticketnumber = $DB->Insert_ID();

  /*--------------------------------------------------------------------------*/
  // send notifications about new tickets to a the jabber ID or email address
  /*--------------------------------------------------------------------------*/

  $query = "SELECT email,screenname FROM user WHERE username = '$user'";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("select screename $l_queryfailed");
  $myresult = $result->fields;	
  $screenname = $myresult['screenname'];

  // if they have specified a screenname then send them a jabber notification
  if ($screenname) {
    include 'XMPPHP/XMPP.php';

    // TODO: edit this to use database jabber user defined in config file
    $conn = new XMPPHP_XMPP('localhost', 5222, 'citrusdb', 'citrusdb', 'xmpphp', 'localhost', $printlog=false, $loglevel=XMPPHP_Log::LEVEL_INFO);
    
    try {
      $conn->connect();
      $conn->processUntil('session_start');
      $conn->presence();
      $conn->message("$screenname", "$ticketnumber: $description");
      $conn->disconnect();
    } catch(XMPPHP_Exception $e) {
      die($e->getMessage());
    }
  }
  
  // if they have specified an email then send them an email notification
  if ($email) {

    // HTML Email Headers
    $headers = "From: $billing_email \n";
    $to = $email;
    // send the mail
    $subject = "$l_ticket: $ticketnumber";
    $message = "ticketnumber: $description";
    mail ($to, $subject, $message, $headers);
    
  }
}


// add a service message ticket for new, modified, or shutoff services
function service_message($service_notify_type, $account_number,
			 $master_service_id, $user_service_id,
			 $new_master_service_id, $new_user_service_id)
			
{
  global $DB, $user, $lang;
  include("$lang");
  
  /*- Service Notify Types -*/
  // added
  // change - uses both user_service_id and new_user_service_id
  //   the change function will need to create a new_user_service_id
  //   like it should have been doing
  //
  // onetime - for one time billing removals
  // undelete
  //
  // removed
  // canceled
  // turnoff
  /*-------------------------*/

  // get the name of the service
  $query = "SELECT * FROM master_services WHERE id = $master_service_id";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("service_message $l_queryfailed");
  $myresult = $result->fields;	
  $servicename = $myresult['service_description'];
  $activate_notify = $myresult['activate_notify']; // added
  $modify_notify = $myresult['modify_notify'];     // change,undelete
  $shutoff_notify = $myresult['shutoff_notify'];   // turnoff, removed, canceled
  $billing_notify = $myresult['billing_notify'];   // turnoff, removed, canceled
  
  // set a different notify and description depending on service_notify_type

  // ADDED
  if ($service_notify_type == "added") {
    $description = "$l_added $servicename $user_service_id";
    if ($activate_notify <> '') {
      $status = "not done";
      $notify = $activate_notify;
    } else {
      $status = "automatic";
      $notify = "";
    }
  }

  // CHANGE
  if ($service_notify_type == "change") { 
    // get the name of the new service
    $query = "SELECT * FROM master_services WHERE id = $new_master_service_id";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query)
      or die ("service_message_modify $l_queryfailed");
    $myresult = $result->fields;	
    $new_servicename = $myresult['service_description'];
    // use the new services modify notify, maybe different from old one
    $modify_notify = $myresult['modify_notify'];

    $description = "$l_change $servicename $user_service_id -> $new_servicename $new_user_service_id";
    if ($modify_notify <> '') {
      $status = "not done";
      $notify = $modify_notify;
    } else {
      $status = "automatic";
      $notify = "";
    } 
  }

  // UNDELETE
  if ($service_notify_type == "undelete") {
    $description = "$l_undelete $servicename $user_service_id";    
    if ($modify_notify <> '') {
      $status = "not done";
      $notify = $modify_notify;
    } else {
      $status = "automatic";
      $notify = "";
    } 
  }

    // ONETIME
  if ($service_notify_type == "onetime") {
    $description = "$l_onetimebilled $servicename $user_service_id";    
    if ($shutoff_notify <> '') {
      $status = "not done";
      $notify = $shutoff_notify;
    } else {
      $status = "automatic";
      $notify = "";
    } 
  }

  // REMOVED
  if ($service_notify_type == "removed") {
    $description = "$l_removed $servicename $user_service_id";    

    if ($shutoff_notify <> '') {
      $status = "not done";
      $notify = $shutoff_notify;
    } else {
      $status = "automatic";
      $notify = "";
    }
    
  }  
  
  // CANCELED
  if ($service_notify_type == "canceled") {
    $description = "$l_canceled $servicename $user_service_id";    

    if ($shutoff_notify <> '') {
      $status = "not done";
      $notify = $shutoff_notify;
    } else {
      $status = "automatic";
      $notify = "";
    }

  }


  // TURNOFF
  if ($service_notify_type == "turnoff") {
    $description = "$l_turnoff $servicename $user_service_id";    

    if ($shutoff_notify <> '') {
      $status = "not done";
      $notify = $shutoff_notify;
    } else {
      $status = "automatic";
      $notify = "";
    }

  }
  
  // create the ticket with the service message
  create_ticket($DB, $user, $notify, $account_number, $status, $description, NULL, NULL, NULL, $user_service_id);
}