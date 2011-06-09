<?php
/*
 * ----------------------------------------------------------------------------
 *  select enum items and generate a drop down menu with them
 * ----------------------------------------------------------------------------
 */
function enum_select($table,$name,$default) 
{ 
	$sql = "SHOW COLUMNS FROM $table LIKE '$name'"; 
	$result = $this->db->query($sql) or die ("Enum Query Failed");         
	echo "<select name='$name'>\n\t"; 
	if($default) 
	{
		echo "<option selected value='$default'>$default</option>\n\t";
	}
	while($myrow = $result->FetchRow())
	{ 
		$enum_field = substr($myrow[1],0,4); 
		if($enum_field == "enum")
		{ 
			global $enum_field; 
			$enums = substr($myrow[1],5,-1); 
			$enums = preg_replace("/'/","",$enums); 
			$enums = explode(",",$enums); 
			foreach($enums as $val) 
			{ 
				echo "<option value='$val'>$val</option>\n\t"; 
			}//----end foreach 
		}//----end if 
	}//----end while 
	echo "\r</select>"; 
}
