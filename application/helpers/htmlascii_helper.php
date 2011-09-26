<?php
/*----------------------------------------------------------------------------*/
// Convert html character codes to ascii for pdf printing
/*----------------------------------------------------------------------------*/
function html_to_ascii($value) {
	$value = str_replace( "&amp;" , "&" , $value );
	$value = str_replace( "&gt;" , ">" , $value );
	$value = str_replace( "&lt;" , "<" , $value );
	$value = str_replace( "&quot;" , "\"" , $value );
	$value = str_replace( "&#036;", "$" , $value );
	$value = str_replace( "&#33;" , "!" , $value );
	$value = str_replace( "&#39;" , "'" , $value );	
	return $value;
} // end html_to_ascii

