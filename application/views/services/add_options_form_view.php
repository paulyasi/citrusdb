<?php
// list the service options after they clicked on the add button.
echo "<a href=\"".$this->url_prefix."/index.php/services\">
[ ". lang('undochanges') ."</a> ]";
$myresult = $this->service_model->service_with_org($serviceid);
$servicename = $myresult['service_description'];
$options_table_name = $myresult['options_table'];
$usage_label = $myresult['usage_label'];
$service_org_id = $myresult['organization_id'];
$service_org_name = $myresult['org_name'];
?>

<script language=javascript>
function popupURL(url,value) { 
newurl = "url + value";
window.open("newurl");
}
</script>	

<h4><?php echo lang('addingservice');?>: <?php echo $servicename?> (<?php echo $service_org_name?>)</h4>
<form action="<?php echo $this->url_prefix?>/index.php/services/add_service" name="AddService" method=post> 
<table width=720 cellpadding=5 cellspacing=1 border=0>
<input type=hidden name=options_table_name value=<?=$options_table_name?>>
<input type=hidden name=serviceid value=<?=$serviceid?>>

<?php
// check that there is an options_table_name, if so, show the options choices
if ($options_table_name <> '') {
	// get a list of all the field names in the options table
	// SHOW COLUMNS FROM $options_table_name and look at the type column
	// or more properly query the information_schema table
	/*
	   SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
	   FROM INFORMATION_SCHEMA.COLUMNS
	   WHERE table_name = 'example_options'
	   AND table_schema = 'citrus'
	 */
	//$fields = $this->db->field_data($options_table_name);	

	// get the columns from schema model
	$fields = $this->schema_model->columns($this->db->database, $options_table_name);

	//initialize variables
	$fieldlist = "";
	$i = 0;

	foreach($fields->result() as $v) {
		//echo "Name: $v->name ";
		//echo "Type: $v->type ";

		$fieldname = $v->COLUMN_NAME;
		$fieldflags = $v->DATA_TYPE;
		$fieldtype = $v->COLUMN_TYPE; // for enum has value: enum('1','2') etc.

		if ($detail1 <> '' AND $i == 2) 
		{
			// if the first attbibute has a prefilled in value, 
			// use that as default value
			$default_value = $detail1;
		} 
		else 
		{
			$default_value = '';
		}

		if ($fieldname <> "id" AND $fieldname <> "user_services") 
		{
			if ($fieldflags == "enum") 
			{
				echo "<td bgcolor=\"ccccdd\"width=180><b>$fieldname</b></td>".
					"<td bgcolor=\"#ddddee\">";

				// print all the items listed in the enum data
				$this->schema_model->enum_select($fieldtype, $fieldname, $default_value);

				echo "</td><tr>\n";
			} 
			elseif ($fieldname == "description")
			{
				echo "<td bgcolor=\"ccccdd\"width=180><b>$fieldname</b></td>".
					"<td bgcolor=\"#ddddee\"><input size=40 maxlength=44 ".
					"type=text name=\"$fieldname\" value=\"".$default_value."\">";
				echo "</td><tr>";
			} 
			else 
			{
				// print fields for each attributes
				echo "<td bgcolor=\"ccccdd\"width=180>".
					"<b>$fieldname</b></td>".
					"<td bgcolor=\"#ddddee\">".
					"<input type=text name=$fieldname id=\"$fieldname\" ".
					"value=\"$default_value\">\n";
				echo "</td><tr>\n";
			}
			$fieldlist .= ',' . $fieldname;
		}
		$i++;
	} //endforeach

	print "<input type=hidden name=fieldlist value=$fieldlist>";
} //endwhile

// print the usage_multiple entry field
// if there is a usage label, use it instead of the generic name
if($usage_label) {
	print "<tr><td bgcolor=\"#ccccdd\"><b>$usage_label</b></td>";
} else {
	print "<tr><td bgcolor=\"#ccccdd\"><b>". lang('usagemultiple') ."</b></td>";
}

print"<td bgcolor=\"#ddddee\"><input type=text name=\"usagemultiple\" ".
"value=\"1\"></td><tr>";

// print the billing id choices available to this service type
// if no billing id choices match, then ask them to create a billing
// record for this service with a matching billing org

print "<td bgcolor=\"#ddaaee\"><b>".lang('organizationname')."</b></td>".
"<td bgcolor=\"#ddaaee\">";


if (!$org_billing_types || $org_billing_types->num_rows() < 1){
	echo "<b>".lang('willcreatebillingrecord')." $service_org_name</b>".
		"<input type=hidden name=create_billing value=$service_org_id>";	
} else {
	echo "<select name=billing_id>";
	foreach ($org_billing_types->result_array() as $myresult) 
	{
		$billing_id = $myresult['id'];
		$org_name = $myresult['org_name'];
		$billing_type = $myresult['name'];
		print "<option value=$billing_id>$billing_id ($org_name) $billing_type</option>";
	}
}
echo "</select></td><tr>";

print "<td></td><td><input name=addnow type=submit value=\"".lang('add')."\" ".
"class=smallbutton></td></table></form>";
?>
