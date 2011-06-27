<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * ----------------------------------------------------------------------------
 *  perform tasks that require lookups in the database schema like
 *  data types and field names
 * ----------------------------------------------------------------------------
 */

class Schema_model extends CI_Model
{
	function __construct()
	{
	    parent::__construct();
	}

	/*
	 * ----------------------------------------------------------------------------
	 *  Get table information from the information schema
	 * ----------------------------------------------------------------------------
	 */
	public function columns($database, $table)
	{
		$query = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, 
			COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
			WHERE table_name = '$table'
			AND table_schema = '$database'";	

			$result = $this->db->query($query) or die ("Schema Query Failed"); 

		return $result;
	}


	/*
	 * ----------------------------------------------------------------------------
	 *  select enum items from an enum column_type data
	 *  and generate a drop down menu with them
	 * ----------------------------------------------------------------------------
	 */
	public function enum_select($column_type, $name, $default) 
	{
		echo "<select name='$name'>\n\t"; 
		if($default) 
		{
			echo "<option selected value='$default'>$default</option>\n\t";
		}

		$enums = substr($column_type,5,-1); 
		echo "enums: $enums";
		$enums = preg_replace("/'/","",$enums); 
		$enums = explode(",",$enums); 
		foreach($enums as $val) 
		{ 
			echo "<option value='$val'>$val</option>\n\t"; 
		}//----end foreach 
		echo "\r</select>"; 
	}

}
