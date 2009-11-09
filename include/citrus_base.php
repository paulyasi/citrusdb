<?php
/*----------------------------------------------------------------------------*/
// Copyright (C) 2005  Paul Yasi (paul at citrusdb.org)
// Read the README file for more information
/*----------------------------------------------------------------------------*/

class citrus_base {

    var $input  = array();
    var $cookie = array();


    /*-------------------------------------------------------------------------*/
    // Constructor - Initialize class
    /*-------------------------------------------------------------------------*/
    function __construct()
    {

        // Automatically parse our incoming variables, this prevents us from
        //  having to call this method each time we include this class ;)
        $this->input = $this->parse_data();

        // And do the same for cookies
        $this->cookie = $this->parse_cookies();

        return $this;

    }


    /*-------------------------------------------------------------------------*/
    // Parse any and all GET and POST data 'safely'
    /*-------------------------------------------------------------------------*/
    function parse_data()
    {

        // Our variable we will be returning
        $output = array();
    	
        // Do we have any GET data?
		if( is_array($_GET) )
		{
            // Loop through each pair
			while( list($key, $value) = each($_GET) )
			{
                // Make them 'safe'
				$output[$this->safe_key($key)] = $this->safe_value($value);
			}
		}

        // Do we have any POST data?  If so, overwrite GET data.
        if( is_array($_POST) )
        {
            // Loop through each pair
            while( list($key, $value) = each($_POST) )
            {
                // Make them 'safe', overriding any GET key/value pair
                $output[$this->safe_key($key)] = $this->safe_value($value);
            }
        }
		
        return $output;
    }


    /*-------------------------------------------------------------------------*/
    // Parse any present cookies
    /*-------------------------------------------------------------------------*/  
    function parse_cookies()
    {

        // Our variable we will be returning
        $output = array();

        // Do we have any COOKIE data?
		if( is_array($_COOKIE) )
		{
            // Loop through each pair
			while( list($key, $value) = each($_COOKIE) )
			{
                // Make them 'safe'
				$output[$this->safe_key($key)] = $this->safe_value($value);
			}
		}

        return $output;
    }


    /*-------------------------------------------------------------------------*/
    // Ensure safe keys
    /*-------------------------------------------------------------------------*/
    function safe_key($key) {
    
        // Do we have anything to worry about?
    	if ($key == "")
    	{
            // Nope
    		return "";
    	}

        // Yup, so let's kill bad stuff
        $key = preg_replace( "/\.\./"           , ""  , $key );
    	$key = preg_replace( "/\_\_(.+?)\_\_/"  , ""  , $key );
    	$key = preg_replace( "/^([\w\.\-\_]+)$/", "$1", $key );
    	
    	return $key;
    }


    /*-------------------------------------------------------------------------*/
    // Ensure safe values
    /*-------------------------------------------------------------------------*/
    function safe_value($value)
    {

        // Do we have anything to worry about?
    	if ($value == "")
    	{
            // Nope
    		return "";
    	}
    	
        // Take care of encoded spaces
    	$value = str_replace( "&#032;", " ", $value );
        // Even sneaky ones ;)
        $value = str_replace( chr(0xCA), "", $value );

	// Depending on environment strip slashes
        if ( get_magic_quotes_gpc() )
        {
                $value = stripslashes($value);
        }


        // Here we convert unsafe, or convenient characters
        $value = str_replace( "&"            , "&amp;"         , $value );
        $value = str_replace( "<!--"         , "&#60;&#33;--"  , $value );
        $value = str_replace( "-->"          , "--&#62;"       , $value );
        $value = preg_replace( "/<script/i"  , "&#60;script"   , $value );
        $value = str_replace( ">"            , "&gt;"          , $value );
        $value = str_replace( "<"            , "&lt;"          , $value );
        $value = str_replace( "\""           , "&quot;"        , $value );
        $value = preg_replace( "/\\\$/"      , "&#036;"        , $value );
        $value = str_replace( "!"            , "&#33;"         , $value );
	$value = str_replace( "\\'"          , "'"	       , $value );
	$value = str_replace( "\\\""         , "\""            , $value );
	$value = str_replace( "\\"           , "&#92;"         , $value );
        // Helps make SQL safer
        $value = str_replace( "'"            , "&#39;"         , $value );

        // Handy replaces as needed?
        $value = preg_replace( "/\n/"        , " "        , $value );
        $value = preg_replace( "/\r/"        , ""              , $value );
		

        return $value;
    }


} // end base class


/*-------------------------------------------------------------------------*/
// Ensure safe values with newlines for things like ascii armor
/*-------------------------------------------------------------------------*/
function safe_value_with_newlines($value)
{
  
  // Do we have anything to worry about?
  if ($value == "") {
    // Nope
    return "";
  }
  
  // Take care of encoded spaces
  $value = str_replace( "&#032;", " ", $value );
  // Even sneaky ones ;)
  $value = str_replace( chr(0xCA), "", $value );
  
  // Depending on environment strip slashes
  if ( get_magic_quotes_gpc() ) {
    $value = stripslashes($value);
  }
  
  
  // Here we convert unsafe, or convenient characters
  $value = str_replace( "&"            , "&amp;"         , $value );
  $value = str_replace( "<!--"         , "&#60;&#33;--"  , $value );
  $value = str_replace( "-->"          , "--&#62;"       , $value );
  $value = preg_replace( "/<script/i"  , "&#60;script"   , $value );
  $value = str_replace( ">"            , "&gt;"          , $value );
  $value = str_replace( "<"            , "&lt;"          , $value );
  $value = str_replace( "\""           , "&quot;"        , $value );
  $value = preg_replace( "/\\\$/"      , "&#036;"        , $value );
  $value = str_replace( "!"            , "&#33;"         , $value );
  $value = str_replace( "\\'"          , "'"	       , $value );
  $value = str_replace( "\\\""         , "\""            , $value );
  $value = str_replace( "\\"           , "&#92;"         , $value );
  // Helps make SQL safer
  $value = str_replace( "'"            , "&#39;"         , $value );
  
  return $value;
}


function get_nextbillingdate()
{
	global $DB;
	
	// get the current date and time in the SQL query format
	$mydate = date("Y-m-d");
	$mytime = date("H:i:s");
	
	// check if it's after the dayrollover time and get tomorrow's date if it is
	$query = "SELECT billingdate_rollover_time from settings WHERE id = 1";
	$DB->SetFetchMode(ADODB_FETCH_ASSOC);
	$result = $DB->Execute($query) or die ("Billing date rollover Query Failed");
	$myresult = $result->fields;
	$rollover_time = $myresult['billingdate_rollover_time'];
	if ($mytime > $rollover_time) {
		$mydate = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));
	}

        // check if the date is in the holiday table, move up one day until not matched
        $holiday = true;
        while ($holiday == true)
        {
                $query = "SELECT holiday_date from holiday WHERE holiday_date = '$mydate'";
                $DB->SetFetchMode(ADODB_FETCH_ASSOC);
                $result = $DB->Execute($query) or die ("Holiday date Query Failed");
                $myresult = $result->fields;
                $myholiday = $myresult['holiday_date'];

                // check for billing weekend days
				// check the database for what days are marked as billing weekends
				$query = "SELECT billingweekend_sunday, billingweekend_monday, billingweekend_tuesday, 
						billingweekend_wednesday, billingweekend_thursday, billingweekend_friday, 
						billingweekend_saturday FROM settings WHERE id = 1";
				$result = $DB->Execute($query) or die ("Weekend Query Failed");
                $myresult = $result->fields;
				$sunday = $myresult['billingweekend_sunday'];
                $monday = $myresult['billingweekend_monday'];
                $tuesday = $myresult['billingweekend_tuesday'];
                $wednesday = $myresult['billingweekend_wednesday'];
                $thursday = $myresult['billingweekend_thursday'];
                $friday = $myresult['billingweekend_friday'];
                $saturday = $myresult['billingweekend_saturday'];                                
                
                // check the date we have agains those billing weekends
                list($myyear, $mymonth, $myday) = split('-', $mydate); 
				$day_of_week = date("w", mktime(0, 0, 0, $mymonth, $myday, $myyear));
				
				// if the weekday is a billing weekend, then make it a holiday so it gets moved forward
				if ($sunday == 'y' && $day_of_week == 0) { $myholiday = $mydate; }
				if ($monday == 'y' && $day_of_week == 1) { $myholiday = $mydate; }
				if ($tuesday == 'y' && $day_of_week == 2) { $myholiday = $mydate; }
				if ($wednesday == 'y' && $day_of_week == 3) { $myholiday = $mydate; }
				if ($thursday == 'y' && $day_of_week == 4) { $myholiday = $mydate; }
				if ($friday == 'y' && $day_of_week == 5) { $myholiday = $mydate; }
				if ($saturday == 'y' && $day_of_week == 6) { $myholiday = $mydate; }

                if($myholiday == $mydate) {
                        // holiday is still true move up one day and test that one
                        $mydate = date("Y-m-d", mktime(0, 0, 0, $mymonth , $myday+1, $myyear));
                } else {
                $holiday = false;
                }

                //echo "holiday $mydate<br>";
        }
	return $mydate;
}

function credit_card_validator( $credit_card_number ) {      

    # Clean out any non-numeric characters in $credit_card_number 
    $credit_card_number = EREG_REPLACE( '[^0-9]','', $credit_card_number ); 
     
    $ccn_length = STRLEN( $credit_card_number ); 
     
    # Find the type of the card based on the prefix and length of the card number 
    IF( EREG( '^3[4|7]', $credit_card_number ) && $ccn_length == 15 ) 
        $type = 'American Express'; 
    ELSE IF( EREG( '^4', $credit_card_number )  && ( $ccn_length == 13 || $ccn_length == 16 ) ) 
        $type = 'Visa'; 
    ELSE IF( EREG( '^5[1-5]', $credit_card_number ) && $ccn_length == 16 ) 
        $type = 'Mastercard'; 
    ELSE 
        RETURN ARRAY( 'valid' => false, 'type' => 'unknown', 'error' => 'This is not a valid number for an American Express, Mastercard or Visa Card. Please re-enter the number or use a different card.' ); 

    # Reverse the credit card number 
    $x = STRREV( $credit_card_number ); 

    # Loop through the reversed credit card number one digit at a time. Transform odd numbered entries and sum with even entries 
    FOR( $i = 0;  $i < $ccn_length ; $i++ ) 
        IF( $i % 2 ) # Test to see if the current string index ( $i ) is odd. 
            $sum += ( ( $x[ $i ] % 5 ) * 2 ) + FLOOR( ( $x[ $i ] / 5 ) ); 
	# This formula is equivalent to multiplying a number by two and then, if the result has two digits, summing the two digits. 
        ELSE 
            $sum += $x[ $i ]; 

    IF( ! ( $sum % 10 ) ) # If the result, divided by 10 has no fractional remainer, then the card is valid. 
        RETURN ARRAY( 'valid' => true, 'type' => $type ); 
    ELSE 
        RETURN ARRAY( 'valid' => false, 'type' => $type, 'error' => "This is not a valid number for a $type Card. Please re-enter the card number or use a different card." ); 
}  


function getmicrotime(){ 
    list($usec, $sec) = explode(" ",microtime()); 
    return ((float)$usec + (float)$sec); 
    } 

// enum_select (TABLE_NAME, FIELD_NAME, DEFAULT_VALUE)
function enum_select($table,$name,$default) { 
	global $DB;
         $sql = "SHOW COLUMNS FROM $table LIKE '$name'"; 
	 $DB->SetFetchMode(ADODB_FETCH_NUM);
	 $result = $DB->Execute($sql) or die ("Enum Query Failed");         
	 echo "<select name='$name'>\n\t"; 
	 if($default) {
	   echo "<option selected value=$default>$default</option>\n\t";
	 }
	 while($myrow = $result->FetchRow()){ 
		$enum_field = substr($myrow[1],0,4); 
                if($enum_field == "enum"){ 
			global $enum_field; 
                        $enums = substr($myrow[1],5,-1); 
                        $enums = ereg_replace("'","",$enums); 
                        $enums = explode(",",$enums); 
                        foreach($enums as $val) { 
				echo "<option value='$val'>$val</option>\n\t"; 
                        }//----end foreach 
                }//----end if 
        }//----end while 
	echo "\r</select>"; 
}

function getPagerData($numHits, $limit, $page) 
{ 
        $numHits  = (int) $numHits; 
        $limit    = max((int) $limit, 1); 
        $page     = (int) $page; 
        $numPages = ceil($numHits / $limit); 

        $page = max($page, 1); 
        $page = min($page, $numPages); 

        $offset = ($page - 1) * $limit; 

        $ret = new stdClass; 

        $ret->offset   = $offset; 
        $ret->limit    = $limit; 
        $ret->numPages = $numPages; 
	$ret->page     = $page; 

	return $ret; 
}

function log_activity($DB,$citrus_user,$account_number,$activity_type,$activity_result)
{
  $datetime = date("Y-m-d H:i:s");
  $user_ip_address = $_SERVER["REMOTE_ADDR"];
  
  switch($activity_type) {

  case 'login':
    $query = "INSERT INTO activity_log (datetime,citrus_user,user_ip_address,activity_type,activity_result) ".
      "VALUES ('$datetime','$citrus_user','$user_ip_address','$activity_type','$activity_result')";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("login activity_log insert failed");
    break;
    
  case 'logout':
    $query = "INSERT INTO activity_log (datetime,citrus_user,user_ip_address,activity_type,activity_result) ".
      "VALUES ('$datetime','$citrus_user','$user_ip_address','$activity_type','$activity_result')";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("logout activity_log insert failed");    
    break;
    
  case 'view_customer':
    $query = "INSERT INTO activity_log (datetime,citrus_user,user_ip_address,customer_account_number, ".
      "activity_type,activity_result) ".
      "VALUES ('$datetime','$citrus_user','$user_ip_address','$account_number','$activity_type','$activity_result')";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("view_customer activity_log insert failed");     
    break;
    
  case 'edit_customer':
    $query = "INSERT INTO activity_log (datetime,citrus_user,user_ip_address,customer_account_number, ".
      "activity_type,activity_result) ".
      "VALUES ('$datetime','$citrus_user','$user_ip_address','$account_number','$activity_type','$activity_result')";
    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $result = $DB->Execute($query) or die ("edit_customer activity_log insert failed");       
    break;
    
  case 'view_billing':
    
    break;
  case 'edit_billing':
    
    break;
  case 'export_carddata':
    
    break;
  case 'import_carddata':
    
    break;
    
  } // end activity_type switch
} // end log_activity



?>
