<?php
/*----------------------------------------------------------------------------*/
// Copyright (C) 2005  Paul Yasi <paul@citrusdb.org>
// Read the README file for more information
/*----------------------------------------------------------------------------*/

class citrus_base {

    var $input  = array();
    var $cookie = array();
    var $magic_quotes = "";


    /*-------------------------------------------------------------------------*/
    // Constructor - Initialize class
    /*-------------------------------------------------------------------------*/
    function citrus_base()
    {

        // Get environment settings
        $this->magic_quotes = get_magic_quotes_gpc();

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

        // Helps make SQL safer
        $value = str_replace( "'"            , "&#39;"         , $value );

        // Handy replaces as needed?
        $value = preg_replace( "/\n/"        , "<br />"        , $value );
        $value = preg_replace( "/\r/"        , ""              , $value );
		

        // Depending on environment strip slashes
    	if ( $this->magic_quotes )
    	{
    		$value = stripslashes($value);
    	}
    	
        return $value;
    }


} // end base class

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
         $sql = "SHOW COLUMNS FROM $table LIKE '$name'"; 
         $result = mysql_query($sql); 
         echo "<select name='$name'>\n\t"; 
	 echo "<option selected value=$default>$default</option>\n\t";
	while($myrow = mysql_fetch_row($result)){ 
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


?>