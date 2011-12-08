<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
echo "<hr><div id=\"tabnav\">\n";

// get the ticketdatetime for the user, the last time tickets.php was loaded for $user
$ticketdatetime = $this->user . 'datetime';
if (!isset($_COOKIE[$ticketdatetime])) { 
  $_COOKIE[$ticketdatetime] = ""; 
}
$usernamedatetime = $_COOKIE[$ticketdatetime];

// lookup the ticket count for this user
$data = $this->support_model->user_count($this->user);
$created = $data['created'];
$num_rows = $data['num_rows'];

$myuri = $this->uri->uri_string();

if (!empty($usernamedatetime) AND $created > $usernamedatetime) {
  $bgstyle = "style = \"background-color: #AFA;\"";
  // figures out if viewing the current user tickets
} elseif ($myuri == "tickets/user/$this->user") {
  $bgstyle = "class = \"active\"";
} else {
  $bgstyle = "";
}

if ($num_rows == 0) {
  echo "<a href=\"$this->url_prefix/index.php/tickets/user/$this->user/$usernamedatetime\" $bgstyle>".
    "<b style=\"font-weight:normal;\">$this->user($num_rows)</b></a>\n";
} else {
  echo "<a href=\"$this->url_prefix/index.php/tickets/user/$this->user/$usernamedatetime\" $bgstyle>$this->user($num_rows)</a>\n";    
}

  
foreach ($usergroups as $row) 
{
  $groupname = $row['groupname'];

  // get the ticketdatetime for this group, the last time tickets.php was loaded for $groupname
  $ticketdatetime = $groupname . 'datetime';
  if (!isset($_COOKIE[$ticketdatetime])) { 
    $_COOKIE[$ticketdatetime] = ""; 
  }
  $groupnamedatetime = $_COOKIE[$ticketdatetime];
  
	$data = $this->support_model->group_count($groupname);
	$created = $data['created'];
	$num_rows = $data['num_rows'];	

	$myuri = $this->uri->uri_string();
	
  if (!empty($groupnamedatetime) AND $created > $groupnamedatetime) {
    $bgstyle = "style = \"background-color: #AFA;\"";
    // figure out if viewing the current ticket group
  } elseif ($myuri == "tickets/group/$groupname") {
	    $bgstyle = "class = \"active\"";
  	} else {
    	$bgstyle = "";
  }

  if ($num_rows == 0) {
    echo "<a href=\"$this->url_prefix/index.php/tickets/group/$groupname/$groupnamedatetime\" $bgstyle><b style=\"font-weight:normal;\">$groupname($num_rows)</b></a>\n";
  } else {
    echo "<a href=\"$this->url_prefix/index.php/tickets/group/$groupname/$groupnamedatetime\" $bgstyle>$groupname($num_rows)</a>\n";    
  }

}


echo "</div>\n";
