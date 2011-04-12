<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
echo "<hr><div id=\"tabnav\">\n";

// get the ticketdatetime for the user, the last time tickets.php was loaded for $user
$ticketdatetime = $this->user . 'datetime';
if (!isset($_COOKIE[$ticketdatetime])) { 
  $_COOKIE[$ticketdatetime] = ""; 
}
$usernamedatetime = $_COOKIE[$ticketdatetime];

// lookup the ticket count for this user
$data = $this->ticket_model->user_count($this->user);
$created = $data['created'];
$num_rows = $data['num_rows'];

if (!empty($usernamedatetime) AND $created > $usernamedatetime) {
  $bgstyle = "style = \"background-color: #AFA;\"";
  // TODO: figure out if viewing the current user tickets
//} elseif ($ticketuser == $this->user) {
//  $bgstyle = "class = \"active\"";
} else {
  $bgstyle = "";
}

if ($num_rows == 0) {
  echo "<a href=\"$this->url_prefix/index.php?load=tickets&type=base&ticketuser=$this->user&lastview=$usernamedatetime\" $bgstyle>".
    "<b style=\"font-weight:normal;\">$this->user($num_rows)</b></a>\n";
} else {
  echo "<a href=\"$this->url_prefix/index.php?load=tickets&type=base&ticketuser=$this->user&lastview=$usernamedatetime\" $bgstyle>$this->user($num_rows)</a>\n";    
}

// query the customer_history for messages sent to 
// groups the user belongs to
//$query = "SELECT * FROM groups WHERE groupmember = '$this->user' ";
//$supportresult = $this->db->query($query) 
//  or die ("$l_queryfailed");
$query = $this->db->get_where('groups', array('groupmember' => $this->user));
  
foreach ($query->result() as $row) {
  
  $groupname = $row->groupname;

  // get the ticketdatetime for this group, the last time tickets.php was loaded for $groupname
  $ticketdatetime = $groupname . 'datetime';
  if (!isset($_COOKIE[$ticketdatetime])) { 
    $_COOKIE[$ticketdatetime] = ""; 
  }
  $groupnamedatetime = $_COOKIE[$ticketdatetime];
  
	$data = $this->ticket_model->group_count($groupname);
	$created = $data['created'];
	$num_rows = $data['num_rows'];	

  if (!empty($groupnamedatetime) AND $created > $groupnamedatetime) {
    $bgstyle = "style = \"background-color: #AFA;\"";
    // TODO: figure out if viewing the current ticket group
//  } elseif ($ticketgroup == $groupname) {
//    $bgstyle = "class = \"active\"";
  } else {
    $bgstyle = "";
  }

  if ($num_rows == 0) {
    echo "<a href=\"$this->url_prefix/index.php?load=tickets&type=base&ticketgroup=$groupname&lastview=$groupnamedatetime\" $bgstyle><b style=\"font-weight:normal;\">$groupname($num_rows)</b></a>\n";
  } else {
    echo "<a href=\"$this->url_prefix/index.php?load=tickets&type=base&ticketgroup=$groupname&lastview=$groupnamedatetime\" $bgstyle>$groupname($num_rows)</a>\n";    
  }

}


echo "</div>\n";
