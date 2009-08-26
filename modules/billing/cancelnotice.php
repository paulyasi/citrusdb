<?php
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

// Copyright (C) 2002-2008  Paul Yasi (paul at citrusdb dot org)
// read the README file for more information

// print the calendar popup javascript
echo "<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"include/CalendarPopup.js\"></SCRIPT>
	<SCRIPT LANGUAGE=\"JavaScript\">
	var cal = new CalendarPopup();

	function cardval(s) 
	{
		// remove non-numerics
		var v = \"0123456789\";
		var w = \"\";
		for (i=0; i < s.length; i++) 
		{
			x = s.charAt(i);
			if (v.indexOf(x,0) != -1)
			{
				w += x;
			}
		}
		
		// validate number
		j = w.length / 2;
		if (j < 6.5 || j > 8 || j == 7) 
		{
			return false;
		}
		
		k = Math.floor(j);
		m = Math.ceil(j) - k;
		c = 0;
		for (i=0; i<k; i++) 
		{
			a = w.charAt(i*2+m) * 2;
			c += a > 9 ? Math.floor(a/10 + a%10) : a;
		}
		
		for (i=0; i<k+m; i++) c += w.charAt(i*2+1-m) * 1;
		{
			return (c%10 == 0);
		}
	}
	</SCRIPT>
";


// include the class used to create and send notices
include('include/notice.class.php');

//GET Variables
if (!isset($base->input['billing_id'])) { $base->input['billing_id'] = ""; }
if (!isset($base->input['cancel_date'])) { $base->input['cancel_date'] = ""; }

$billing_id = $base->input['billing_id'];
$cancel_date = $base->input['cancel_date'];

if ($save) {

  echo "<pre>";
  $mynotice = new notice('cancel', $billing_id, 'both', $cancel_date, $cancel_date, $cancel_date);
  echo "</pre>";

  // print link to the pdf to download
  $linkname = $mynotice->pdfname;
  $contactemail = $mynotice->contactemail;
  $linkurl = "index.php?load=tools/downloadfile&type=dl&filename=$linkname";
  
  echo "<p>Sent cancel notice to $contactemail</p>";
  echo "<p>Download pdf: <a href=\"$linkurl\">$linkname</a></p>";

  
  
  //  print "<script language=\"JavaScript\">window.location.href = \"index.php?load=billing&type=module\";</script>";
  
 } else {
  print "<br><br>";
  print "<table cellpadding=15 cellspacing=0 border=0 width=720><td>";
  print "<form style=\"margin-bottom:0;\" action=\"index.php\" name=\"form1\">";

  print "<b>Enter cancel date for notice:</b>&nbsp;";
  echo "<A HREF=\"#\"
	   onClick=\"cal.select(document.forms['form1'].cancel_date,'anchor1','yyyy-MM-dd'); 
	return false;\"
	NAME=\"anchor1\" ID=\"anchor1\" style=\"color:blue\">[$l_select]</A>";
  

  print "&nbsp;<input type=text name=cancel_date>";
		       
  print "<input type=hidden name=load value=billing>";
  print "<input type=hidden name=type value=module>";
  print "<input type=hidden name=cancelnotice value=on>";
  print "<input type=hidden name=billing_id value=$billing_id>";
  print "<input name=save type=submit value=\" Send \" class=smallbutton></form></td>";
  print "</td><td><form style=\"margin-bottom:0;\" action=\"index.php\">";
  print "<input name=done type=submit value=\" Cancel  \" class=smallbutton>";
  print "<input type=hidden name=load value=billing>";
  print "<input type=hidden name=type value=module>";
  print "</form></td></table>";
}
    
?>
