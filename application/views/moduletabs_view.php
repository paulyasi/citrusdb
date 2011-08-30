<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
// check which modules we are allowed to view
$viewable = $this->module_model->module_permission_list($this->user);

// Print Modules Menu

echo "<div id=\"tabnav\">";

// get list of the modules that are installed
$result = $this->module_model->modulelist();

foreach($result->result() as $myresult)
{
	$commonname = $myresult->commonname;
	$modulename = $myresult->modulename;

	// change the commonname for base modules to a language compatible name
	if ($commonname == "Customer") { $commonname = lang('customer'); }
	if ($commonname == "Services") { $commonname = lang('services'); }
	if ($commonname == "Billing") { $commonname = lang('billing'); }
	if ($commonname == "Support") { $commonname = lang('support'); }
	
	$myuri = $this->uri->segment(1);

    if (in_array ($modulename, $viewable))
    {
		if ($myuri == $modulename) {
			echo "<div><a class=\"active\" href=\"" . $this->url_prefix . "/index.php/$modulename\">$commonname</a></div>";
		} else {
			echo "<div><a href=\"" . $this->url_prefix . "/index.php/$modulename\">$commonname</a></div>";
		}
    }
	
}

echo "</div>";
