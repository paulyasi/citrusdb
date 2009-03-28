<?php
/*----------------------------------------------------------------------------*/
// Includes Code Contributed by Matthew Veno
// Copyright (C) 2005-2008  Paul Yasi (paul at citrusdb.org)
// Read the README file for more information
/*----------------------------------------------------------------------------*/

class user {
	
  var $LOGGED_IN=false;
	
  /*--------------------------------------------------------------------*/
  // Constructor - initialize class
  /*--------------------------------------------------------------------*/
  function __construct() {
    //clear it out in case someone sets it in the URL or something
    unset($LOGGED_IN);
  }

  /*--------------------------------------------------------------------*/
  // Check if they are logged in
  /*--------------------------------------------------------------------*/
  function user_isloggedin() {
    
    global $hidden_hash_var,$LOGGED_IN;
    
    //if we already ran the hash checks, return the pre-set var
    if (isset($LOGGED_IN)) {
      return $LOGGED_IN;
    }	
    if (!isset($_COOKIE['user_name'])) { 
      $_COOKIE['user_name'] = ""; 
    }
    if ($_COOKIE['user_name'] && $_COOKIE['id_hash']) {
      $hash=md5($_COOKIE['user_name'].$hidden_hash_var);
      if ($hash == $_COOKIE['id_hash']) {
	$LOGGED_IN=true;
	return true;
      } else {
	$LOGGED_IN=false;
	return false;
      }
    } else {
      $LOGGED_IN=false;
      return false;
    }
  }
  
  /*--------------------------------------------------------------------*/
  // Log in the user
  /*--------------------------------------------------------------------*/
  function user_login($user_name,$password) {
    
    global $feedback, $DB;

    if (!$user_name || !$password) {
      $feedback .=  ' ERROR - Missing user name or password ';
      return false;

    } else {

      $user_name=strtolower($user_name);

      $sql="SELECT * FROM user WHERE username='$user_name' ".
	"AND password='$password'";
      $result = $DB->Execute($sql);

      if (!$result ||  $result->RowCount() < 1){

	$feedback .=  " ERROR - User not found or password ".
	  "incorrect $user_name $password ";
	
	// keep track of login failures to stop them trying forever
	$this->loginfailure();
	
	return false;

      } else {

	$this->user_set_tokens($user_name);

	if (!isset($GLOBALS['REMOTE_ADDR'])) {
	  $GLOBALS['REMOTE_ADDR'] = "";
	}

	$sql="UPDATE user SET remote_addr='$GLOBALS[REMOTE_ADDR]' ".
	  "WHERE username='$user_name'";
	$result = $DB->Execute($sql);

	if (!$result) {
	  $feedback .= ' ERROR - '.db_error();
	  return false;
	} else {
	  $feedback .=  ' SUCCESS - You Are Now Logged In ';
	  return true;
	}
	
      }
      
    }
    
  }

  /*--------------------------------------------------------------------*/
  // Logout the user
  /*--------------------------------------------------------------------*/
  function user_logout() {
    setcookie('user_name','',(time()+2592000),'/','',0);
    setcookie('id_hash','',(time()+2592000),'/','',0);
  }

  /*--------------------------------------------------------------------*/
  // keep track of failed login attempts from IP addresses
  /*--------------------------------------------------------------------*/
  function loginfailure() {

    global $DB;
    
    $ipaddress = $_SERVER["REMOTE_ADDR"];
    
    $query="INSERT INTO login_failures(ip,logintime) ".
	    "VALUES ('$ipaddress',CURRENT_TIMESTAMP)";
    $result=$DB->Execute($query) or die ("Log Insert Failed");
  }

  /*--------------------------------------------------------------------*/
  // Set the cookie tokens
  /*--------------------------------------------------------------------*/
  function user_set_tokens($user_name_in) {
    global $hidden_hash_var,$user_name,$id_hash;
    if (!$user_name_in) {
      $feedback .=  ' ERROR - User Name Missing When Setting Tokens ';
      return false;
    }
    $user_name=strtolower($user_name_in);
    $id_hash= md5($user_name.$hidden_hash_var);
    
    setcookie('user_name',$user_name,(time()+36000),'/','',0);
    setcookie('id_hash',$id_hash,(time()+36000),'/','',0);
  }

  /*--------------------------------------------------------------------*/
  // Change the password for the user
  /*--------------------------------------------------------------------*/
  function user_change_password ($new_password1,$new_password2,$change_user_name,$old_password) {
    global $feedback, $DB;
    //new passwords present and match?
    if ($new_password1 && ($new_password1==$new_password2)) {
      //is this password long enough?
      //if (account_pwvalid($new_password1)) {
      //all vars are present?
      if ($change_user_name && $old_password) {
	//lower case everything
	$change_user_name=strtolower($change_user_name);
	//$old_password=strtolower($old_password);
	//$new_password1=strtolower($new_password1);
	$sql="SELECT * FROM user WHERE username='$change_user_name' AND password='$old_password'";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result=$DB->Execute($sql) or die ("Query Failed");
	if (!$result || $result->RowCount() < 1) {
	  $feedback .= " User not found or bad password";
	  return false;
	} else {
	  $sql="UPDATE user SET password='$new_password1' ".
	    "WHERE username='$change_user_name' AND password='$old_password'";
	  $result=$DB->Execute($sql) or die ("Query Failed");
	  $feedback .= ' Password Changed ';
	  return true;
	}
      } else {
	$feedback .= ' Must Provide User Name And Old Password ';
	return false;
      }
    } else {
      return false;
      $feedback .= ' New Passwords Must Match ';
    }
  }
	
  /*--------------------------------------------------------------------*/
  // Register a new user
  /*--------------------------------------------------------------------*/
  function user_register($user_name,$password1,$password2,$real_name,$admin,$manager) {
    global $feedback,$hidden_hash_var,$DB;
    //all vars present and passwords match?
    if ($user_name && $password1 && $password1==$password2) {
      //name is valid?
      if ($this->account_namevalid($user_name)) {
	$user_name=strtolower($user_name);
	//$password1=strtolower($password1);
	
	//does the name exist in the database?
	$sql="SELECT * FROM user WHERE username='$user_name'";
	$result=$DB->Execute($sql);
	if ($result && $result->RowCount() > 0) {
	  $feedback .=  ' ERROR - USER NAME EXISTS ';
	  return false;
	} else {
	  $sql="INSERT INTO user (username,real_name,password,remote_addr,admin,manager) ".
	    "VALUES ('$user_name','$real_name','$password1','$GLOBALS[REMOTE_ADDR]','$admin','$manager')";
	  $result=$DB->Execute($sql) or die ("Insert Query Failed");
	  if (!$result) {
	    $feedback .= ' ERROR - '.db_error();
	    return false;
	  } else {
	    $feedback .= ' Successfully Registered. ';
	    return true;
	  }
	}
      } else {
	$feedback .=  ' Account Name or Password Invalid ';
	return false;
      }
    } else {
      $feedback .=  ' ERROR - Must Fill In User Name, and Matching Passwords ';
      return false;
    }
  }

  
  /*--------------------------------------------------------------------*/
  // Get their user id
  /*--------------------------------------------------------------------*/
  function user_getid() {
    global $G_USER_RESULT;
    //see if we have already fetched this user from the db, if not, fetch it
    if (!$G_USER_RESULT) {
      $G_USER_RESULT=db_query("SELECT * FROM user WHERE username='" . $this->user_getname() . "'");
    }
    if ($G_USER_RESULT && db_numrows($G_USER_RESULT) > 0) {
      return db_result($G_USER_RESULT,0,'user_id');
    } else {	
      return false;
    }
  }

  /*--------------------------------------------------------------------*/
  // Get their real name
  /*--------------------------------------------------------------------*/
  function user_getrealname() {
    global $DB;
    $myusername = $this->user_getname();
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $query = "SELECT * FROM user WHERE username = '$myusername'";
    $result = $DB->Execute($query) or die ("Query Failed");
    $myresult = $result->fields;
    $real_name = $myresult['real_name'];
    return $real_name;
  }

  /*--------------------------------------------------------------------*/
  // Get their email address
  /*--------------------------------------------------------------------*/
  function user_getemail() {
    global $G_USER_RESULT;
    //see if we have already fetched this user from the db, if not, fetch it
    if (!$G_USER_RESULT) {
      $G_USER_RESULT=db_query("SELECT * FROM user WHERE username='" . $this->user_getname() . "'");
    }
    if ($G_USER_RESULT && db_numrows($G_USER_RESULT) > 0) {
      return db_result($G_USER_RESULT,0,'email');
    } else {
      return false;
    }
  }

  
  /*--------------------------------------------------------------------*/
  // Get their username
  /*--------------------------------------------------------------------*/
  function user_getname() {
    if ($this->user_isloggedin()) {
      return $_COOKIE['user_name'];
    } else {
      //look up the user some day when we need it
      return ' ERROR - Not Logged In ';
    }
  }
  
  /*--------------------------------------------------------------------*/
  // Check if the account username they entered is valid
  /*--------------------------------------------------------------------*/
  function account_namevalid($name) {	
    global $feedback;
    // no spaces
    if (strrpos($name,' ') > 0) {
      $feedback .= " There cannot be any spaces in the login name. ";
      return false;
    }
    
    // must have at least one character
    if (strspn($name,"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ") == 0) {
      $feedback .= "There must be at least one character.";
      return false;
    }
    
    // must contain all legal characters
    if (strspn($name,"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_")
	!= strlen($name)) {
      $feedback .= " Illegal character in name. ";
      return false;
    }
    
    // min and max length
    if (strlen($name) < 2) {
      $feedback .= " Name is too short. It must be at least 2 characters. ";
      return false;
    }
    if (strlen($name) > 60) {
      $feedback .= "Name is too long. It must be less than 60 characters.";
      return false;
    }
    
    // illegal names
    if (eregi("^((root)|(bin)|(daemon)|(adm)|(admin)|(lp)|(sync)|(shutdown)|(halt)|(mail)|(news)"
	      . "|(uucp)|(operator)|(games)|(mysql)|(httpd)|(nobody)|(dummy)"
	      . "|(www)|(cvs)|(shell)|(ftp)|(irc)|(debian)|(ns)|(download))$",$name)) {
      $feedback .= "Name is reserved.";
      return 0;
    }
    if (eregi("^(anoncvs_)",$name)) {
      $feedback .= "Name is reserved for CVS.";
      return false;
    }
    
    return true;
  }
  
  /*--------------------------------------------------------------------*/
  // Check if their email address is valid
  /*--------------------------------------------------------------------*/
  function validate_email ($address) {
    return (ereg('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+'. '@'. '[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.' . '[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$', $address));
  }
  
  } // end class

?>
