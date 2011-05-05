<?php   
	if ($this->customer_model->is_not_canceled($this->account_number))
   	{
		echo "<a href=\"services/create\">[ ".
		lang('addservice') . " ]</a>";
   	}
?>
&nbsp;&nbsp<a href="services/history">
[ <?php echo lang('history')?> ]</a> &nbsp;&nbsp;&nbsp;&nbsp;

<?php 
$myuri = $this->uri->segment(2);

if ($myuri = 'category') 
{
// print the showall tab as unactive
	echo "<a href=\"services\" style=\"font-weight: normal; border: 1px solid #eee; padding-left: 5px; padding-right: 5px; background-color: #eee;\">" . lang('showall') . "</a> ";
} 
else 
{
	// print the showall tab as active
	echo "<a href=\"services\" style=\"font-weight: normal; border: 1px solid #ccd; padding-left: 5px; padding-right: 5px; background-color: #ccd;\">" . lang('showall') . "</a> ";
}

foreach ($categories->result() as $myresult) 
{
	$categoryname = $myresult->category;
	
	$myuri = $this->uri->segment(2);	
	
	if ($myuri == $categoryname) 
	{
		echo "<a href=\"services/category/".
	 	$categoryname . "\" style=\"font-weight: normal; border: 1px solid #ccd; padding-left: 5px; padding-right: 5px; background-color: #ccd;\">$categoryname</a> \n";
	} 
	else 
	{
		echo "<a href=\"services/category/".
	 	$categoryname . "\" style=\"font-weight: normal; border: 1px solid #eee; padding-left: 5px; padding-right: 5px; background-color: #eee;\">$categoryname</a> \n";       
	}
}
?>