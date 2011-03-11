<?php
/*----------------------------------------------------------------------------*/
// Copyright (C) 2005  Paul Yasi <paul@citrusdb.org>
// Read the README file for more information
/*----------------------------------------------------------------------------*/

class user {
	
  var $LOGGED_IN=false;

  //base-2 logarithm of the iteration count for password stretching
  var $hash_cost_log2 = 8;

  // do we require the hashes to be portable to older systems (less secure)?
  // bcrypt hashes have '$2a$' header
  // des ext hashes have '_'
  // portable md5 hashes have '$P$' header
  var $hash_portable = FALSE;

  
  /*--------------------------------------------------------------------*/
  // Constructor - initialize class
  /*--------------------------------------------------------------------*/
  function user() {
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

      $hasher = new PasswordHash($this->hash_cost_log2, $this->hash_portable);
      
      $sql="SELECT account_manager_password FROM customer where account_number='$user_name' LIMIT 1";      
      $result = $DB->Execute($sql);
      $myresult = $result->fields;
      $checkhash = $myresult['account_manager_password'];
      
      // check the password with the new phpass checkpassword function
      $passwordmatch = $hasher->CheckPassword($password, $checkhash);
      
      // bcrypt hashes have '$2a$' header
      // des ext hashes have '_' header
      // portable md5 hashes have '$P$' header
      // the old md5 passwords that should be upgraded have no header
      $bcrypt_h = substr($checkhash, 0, 4);
      $desext_h = substr($checkhash, 0, 1);
      $portmd5_h = substr($checkhash, 0, 3);

      if (($bcrypt_h != '$2a$') AND ($desext_h != '_') AND ($portmd5_h != '$P$')) {
	// the password must be in the old format and must be upgraded to the new type
	if ($password == $checkhash) {
	  // upgrade it to the newer phpass password format
	  $passwordmatch = 1;
	  
	  $newhash = $hasher->HashPassword($password);
	  if (strlen($newhash) < 20) {
	    $feedback .= "Failed to hash new password";
	    return false;
	  }
          
	  $sql="UPDATE customer SET account_manager_password='$newhash' ".
	    "WHERE account_number='$user_name' LIMIT 1";
	  $passresult=$DB->Execute($sql) or die ("Query Failed");
	  
	} else {
	  $passwordmatch = 0;
	}
         
      }      
      
      if (!$result || $result->RowCount() < 1 || !$passwordmatch){
	$feedback .=  " ERROR - User not found or password incorrect";

	// keep track of login failures to stop them trying forever
        $this->loginfailure($user_name);

	return false;
      } else {
	$this->user_set_tokens($user_name);

	//$sql="UPDATE user SET remote_addr='$GLOBALS[REMOTE_ADDR]' WHERE username='$user_name'";
	//$result = $DB->Execute($sql);
	
	if (!$result) {
	  $feedback .= ' ERROR - ';
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
  function loginfailure($user_name) {

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
    
    setcookie('user_name',$user_name,(time()+3600),'/','',0);
    setcookie('id_hash',$id_hash,(time()+3600),'/','',0);
  }
  
  /*--------------------------------------------------------------------*/
  // Change the password for the user
  /*--------------------------------------------------------------------*/
	function user_change_password ($new_password1,$new_password2,$change_user_name,$old_password) {
		global $feedback;
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
					$result=db_query($sql);
					if (!$result || db_numrows($result) < 1) {
						$feedback .= " User not found or bad password $sql".db_error();
						return false;
					} else {
						$sql="UPDATE user SET password='$new_password1' ".
							"WHERE username='$change_user_name' AND password='$old_password'";
							$result=db_query($sql);
							if (!$result || db_affected_rows($result) < 1) {
								$feedback .= ' NOTHING Changed '.db_error();
								return false;
							} else {
								$feedback .= ' Password Changed ';
							return true;
							}
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
	function user_register($user_name,$password1,$password2,$real_name,$admin,$billing,$manager,$restricted) {
		global $feedback,$hidden_hash_var;
		//all vars present and passwords match?
		if ($user_name && $password1 && $password1==$password2) {
			//name is valid?
			if ($this->account_namevalid($user_name)) {
				$user_name=strtolower($user_name);
				//$password1=strtolower($password1);
			
				//does the name exist in the database?
				$sql="SELECT * FROM user WHERE username='$user_name'";
				$result=db_query($sql);
				if ($result && db_numrows($result) > 0) {
					$feedback .=  ' ERROR - USER NAME EXISTS ';
					return false;
				} else {
					$sql="INSERT INTO user (username,real_name,password,remote_addr,admin,billing,manager,restricted) ".
						"VALUES ('$user_name','$real_name','$password1','$GLOBALS[REMOTE_ADDR]','$admin','$billing','$manager','$restricted')";
					$result=db_query($sql);
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
		global $G_USER_RESULT;
		//see if we have already fetched this user from the db, if not, fetch it
		if (!$G_USER_RESULT) {
			$G_USER_RESULT=db_query("SELECT * FROM user WHERE username='" . $this->user_getname() . "'");
		}
		if ($G_USER_RESULT && db_numrows($G_USER_RESULT) > 0) {
			return db_result($G_USER_RESULT,0,'real_name');
		} else {
			return false;
		}
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
		if (strlen($name) < 4) {
			$feedback .= " Name is too short. It must be at least 4 characters. ";
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
