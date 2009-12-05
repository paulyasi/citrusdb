<?php
// Copyright (C) 2009 Paul Yasi (paul at citrusdb.org)
// Read the README file for more information

/*----------------------------------------------------------------------------*/
// Check for authorized accesss
/*----------------------------------------------------------------------------*/
if(constant("INDEX_CITRUS") <> 1){
  echo "You must be logged in to run this.  Goodbye.";
  exit;	
}

if (!defined("INDEX_CITRUS")) {
  echo "You must be logged in to run this.  Goodbye.";
  exit;
}


// print out a graph that compares each of the last 12 months
// of service start_dates compared to end_dates
$current  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));

// TODO: do this for each master_service id
$query = "SELECT monthname(us.start_datetime), year(start_datetime), count(*) FROM user_services us 
  LEFT JOIN master_services ms ON ms.id = us.master_service_id 
WHERE ms.id = 3 
  GROUP BY YEAR(us.start_datetime),MONTH(us.start_datetime)";




// GET Variables
if (!isset($base->input['day'])) { $base->input['day'] = ""; }
$day = $base->input['day'];

if ($day) {
  // get the path_to_ccfile
  $query = "SELECT path_to_ccfile FROM settings WHERE id = 1";
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  $myresult = $result->fields;
  $mydir = $myresult['path_to_ccfile'];

  // the pdfs are all named with
  // -2009-08-10.pdf at the end
  $pdfname = "-$day.pdf";
  
  // check if it is a pdf file that we allow anyone to open
  if ($handle = opendir($mydir)) {
    while (false !== ($file = readdir($handle))) {
      if (substr($file,-15) == $pdfname) {
	echo "<a href=\"http://db.gis.net/index.php?load=tools/downloadfile&type=dl&filename=$file\">$file</a><br>\n";
      }
    }
    closedir($handle);
  }
  
} else {

  // print a form to ask what date you want to view
  echo "Enter date of pdf notices to view:";
echo "<FORM ACTION=\"index.php\" METHOD=\"GET\">".
  "Date: <input type=text name=\"day\" value=\"$day\">".
  "<input type=hidden name=type value=tools>".
  "<input type=hidden name=load value=listpdf>".
  "&nbsp;<input type=submit name=\"$l_submit\" value=\"submit\">".
  "</form> <p>";
}
?>
