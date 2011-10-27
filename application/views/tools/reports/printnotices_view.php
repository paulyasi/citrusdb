<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
	<?php
if ($day) 
{
	// the notification pdfs are all named with
	// -2009-08-10.pdf at the end
	$pdfname = "-$day.pdf";

	// check if it is a pdf file that we allow anyone to open
	if ($handle = opendir($path_to_ccfile)) 
	{
		while (false !== ($file = readdir($handle))) 
		{
			if (substr($file,-15) == $pdfname) 
			{
				echo "<a href=\"$this->url_prefix/index.php/tools/dashboard/downloadfile/$file\">$file</a><br>\n";
			}
		}
		closedir($handle);
	}
}
?>  

Enter date of pdf notices to view:
<FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/reports/printnotices" METHOD="POST">
Date: <input type=text name="day" value="<?php echo $day?>">
<input type=hidden name=type value=tools>
<input type=hidden name=load value=listpdf>
&nbsp;<input type=submit name="<?php echo lang('submit')?>" value="submit">
</form> <p>
