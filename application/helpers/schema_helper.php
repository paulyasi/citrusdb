<?php
/*
 * ----------------------------------------------------------------------------
 *  Get table information from the information schema
 * ----------------------------------------------------------------------------
 */
function information_schema($database, $table)
{
	$query = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
		FROM INFORMATION_SCHEMA.COLUMNS 
		WHERE table_name = '$table'
		AND table_schema = '$database'";	

	$result = $this->db->query($query) or die ("Schema Query Failed"); 
	
	return $result;
}
