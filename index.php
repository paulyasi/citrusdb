<?php
/*----------------------------------------------------------------------------*/
// CitrusDB - The Open Source Customer Database
// Copyright (C) 2002-2008 Paul Yasi
//
// This program is free software; you can redistribute it and/or modify it under
// the terms of the GNU General Public License as published by the
// Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful, but WITHOUT 
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
// details.
//
// You should have received a copy of the GNU General Public License along with 
// this program; if not, write to the Free Software Foundation, Inc., 59 Temple 
// Place, Suite 330, Boston, MA 02111-1307 USA
//
// http://www.citrusdb.org
// Read the README file for more details
/*----------------------------------------------------------------------------*/

// Includes
include('./include/config.inc.php');
include('./include/local/us-english.inc.php');
include("$lang");
include('./include/database.inc.php');
include('./include/billing.inc.php');
include('./include/support.inc.php');
include('./include/services.inc.php');

//$DB->debug = true;

// Get our user class
include('./include/user.class.php');
$u = new user();

// Get our base functions and class
require './include/citrus_base.php';
$base = new citrus_base();

// kick you out if you have 5 failed logins from the same ip
$failures = checkfailures();
if ($failures) {
  echo "Login Failure.  Please See Administrator";
  die;
}


// set input indexes to values so they are not undefined
if (!isset($base->input['submit'])) { $base->input['submit'] = ""; }
if (!isset($base->input['load'])) { $base->input['load'] = ""; }
if (!isset($base->input['type'])) { $base->input['type'] = ""; }
if (!isset($base->input['edit'])) { $base->input['edit'] = ""; }
if (!isset($base->input['create'])) { $base->input['create'] = ""; }
if (!isset($base->input['delete'])) { $base->input['delete'] = ""; }
if (!isset($base->input['account_number'])) { $base->input['account_number'] = ""; }
if (!isset($base->input['ticketuser'])) { $base->input['ticketuser'] = ""; }
if (!isset($base->input['ticketgroup'])) { $base->input['ticketgroup'] = ""; }

if ($base->input['submit'] == $l_login) {
	// check the user login information
	$u->user_login($base->input['user_name'],$base->input['password']);

	// redirect to myself to get the cookie loaded
	print "<script language=\"JavaScript\">window.location.href = \"$url_prefix/index.php?load=search&type=base\";</script>";
}

//echo '<pre>';
//var_dump($_SERVER);
//die;

if ($u->user_isloggedin()) {
	session_start();
	
	// define this constant
	define('INDEX_CITRUS',1);

	$user =  $u->user_getname();

	//GET Variables (sorta like the old way for now)
	$load = $base->input['load'];
	$type = $base->input['type'];
	$edit = $base->input['edit'];
	$create = $base->input['create'];
	$delete = $base->input['delete'];
	$ticketuser = $base->input['ticketuser'];
	$ticketgroup = $base->input['ticketgroup'];
	
	if (!$type) { 
		$load = "search"; 
		$type="base";	
	}
	
	switch ($type)
	{
	case 'dl':  // for file downloads like pdf's, so there is no html printed before the file is sent
		// Check if the file is inside the path_to_citrus
		$filepath = "$path_to_citrus/$load.php";
		if (file_exists($filepath)) {
			include('./'.$load.'.php');
		}
	break; // end download
	
	case 'fs':  // full screen
		echo "<html>
		<head>
		<title>$l_title</title>
		<LINK href=\"citrus.css\" type=text/css rel=STYLESHEET>
		<LINK href=\"fullscreen.css\" type=text/css rel=STYLESHEET>
                <link rel=\"shortcut icon\" type=\"image/ico\" href=\"favicon.ico\" />
		<script language=\"JavaScript\">
		function h(oR) {
			oR.style.backgroundColor='ffdd77';
		}	
		function deh(oR) {
			oR.style.backgroundColor='ddddee';
		}
		function dehnew(oR) {
			oR.style.backgroundColor='ddeeff';
		}
		</script>
		<script language=\"JavaScript\" src=\"include/md5.js\"></script>
                <SCRIPT LANGUAGE=\"JavaScript\" SRC=\"include/prototype.js\"></SCRIPT>
		</head>
		<body marginheight=0 marginwidth=0 topmargin=0 leftmargin=0>";

		// Check if the file is inside the path_to_citrus
		$filepath = "$path_to_citrus/$load.php";
		if (file_exists($filepath)) {
			include('./'.$load.'.php');
		}
	break; // end full screen
	
	case 'module':	// show a module
		// print the html for the top of pages
		echo "<html>
		<head>
		<title>$l_title</title>
		<LINK href=\"citrus.css\" type=text/css rel=STYLESHEET>
        <link rel=\"shortcut icon\" type=\"image/ico\" href=\"favicon.ico\" />
		<script language=\"JavaScript\">
		function h(oR) {
			oR.style.backgroundColor='ffdd77';
		}	
		function deh(oR) {
			oR.style.backgroundColor='ddddee';
		}
		function dehnew(oR) {
			oR.style.backgroundColor='ddeeff';
		}
		function popupPage(page) {
			windowprops = \"height=400,width=600,location=no,\"+ \"scrollbars=yes,menubars=no,toolbars=no,resizable=no\";
			window.open(page, \"Tools\", windowprops);
		}
		</script>
		<script language=\"JavaScript\" src=\"include/md5.js\"></script>
                <SCRIPT LANGUAGE=\"JavaScript\" SRC=\"include/prototype.js\"></SCRIPT>
		</head>
		<body marginheight=0 marginwidth=0 topmargin=0 leftmargin=0>";

		
		//SESSION Variables
		if (!$_SESSION['account_number']) {
			$_SESSION['account_number'] = 1;	
		}

		$account_number = $_SESSION['account_number'];
		
		$time_start = getmicrotime();
		
		// select name and company from the customer table
		$query = "SELECT name,company FROM customer ".
		  "WHERE account_number = $account_number";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("$l_queryfailed");
		$myresult = $result->fields;
		$acct_name = $myresult['name'];
		$acct_company = $myresult['company'];
	
		// print the left side bar 

	
		echo '<div id="sidebar">
		<p align=center class="smalltext">
		<img src="images/my-logo.png">
		<p align=center class="smalltext">';
		echo "<b>$l_accountnum:&nbsp; $account_number &nbsp; </b><br>";
		echo "$acct_name<br>";
		echo "$acct_company<br></p><p align=right>";
		
		include('./modules.php');
		
		print '</div>';
	

		print '<div id="header">';
		
		include('./header.php');  
		
		print '</div><div id="content">';
		
		// show the module
		// Check if the file is inside the path_to_citrus
		$filepath = "$path_to_citrus/modules/$load/index.php";
		if (file_exists($filepath)) {
			include('./modules/'.$load.'/index.php');
		}					

		print "<br>";

        	include('./footer.php'); 
		
		print "<br>";
		$time_end = getmicrotime();
		$time = round(($time_end - $time_start),4);
		echo "<center><b><a target=\"_blank\" href=\"$url_prefix/documentation.html#$load\" style=\"color: red; font-size: 10pt;\">?</a></b></center><br>&nbsp; &nbsp; $l_completedin $time $l_seconds";
	
		print "</div></body></html>";
	break; // end module
	
	case 'base': // base functions
	  if ($load == 'tickets') {

	    if ($ticketuser) {
	      $ticketdatetime = date('YmdHis');
	      $cookiename = $ticketuser . 'datetime'; //usernamedatetime
	      setcookie($cookiename,$ticketdatetime,(time()+3600000),'/','',0);
	      echo "<html><head>";
	      echo "<meta http-equiv=\"refresh\" content=\"300;url=$url_prefix/index.php?load=tickets&type=base&ticketuser=$ticketuser&lastview=$ticketdatetime\">";
	    }

	    if ($ticketgroup) {
	      $ticketdatetime = date('YmdHis');
	      $cookiename = $ticketgroup . 'datetime'; //groupnamedatetime
	      setcookie($cookiename,$ticketdatetime,(time()+3600000),'/','',0);
	      echo "<html><head>";
	      echo "<meta http-equiv=\"refresh\" content=\"300;url=$url_prefix/index.php?load=tickets&type=base&ticketgroup=$ticketgroup&lastview=$ticketdatetime\">";
	    }
	    
	  }
// print the html for the top of pages

		echo "<title>$l_title</title>
		<LINK href=\"citrus.css\" type=text/css rel=STYLESHEET>
        <link rel=\"shortcut icon\" type=\"image/ico\" href=\"favicon.ico\" />
		<script language=\"JavaScript\">
		function h(oR) {
			oR.style.backgroundColor='ffdd77';
		}	
		function deh(oR) {
			oR.style.backgroundColor='ddddee';
		}
		function dehnew(oR) {
			oR.style.backgroundColor='ddeeff';
		}

		function popupPage(page) {
			window.open(page, \"Tools\", \"height=400,width=600,location=0,scrollbars=1,menubar=1,toolbar=0,resizeable=1,left=100,top=100\");
		}
		</script>
		<script language=\"JavaScript\" src=\"include/md5.js\"></script>
                <SCRIPT LANGUAGE=\"JavaScript\" SRC=\"include/prototype.js\"></SCRIPT>
		</head>
		<body marginheight=0 marginwidth=0 topmargin=0 leftmargin=0>";
	
		//SESSION Variables
		if (!isset($_SESSION['account_number'])) {
			$_SESSION['account_number'] = 1;	
		}
		
		$account_number = $_SESSION['account_number'];
		
		$time_start = getmicrotime();
		
		// select name and company from the customer table
		$query = "SELECT name,company FROM customer WHERE account_number = $account_number";
		$DB->SetFetchMode(ADODB_FETCH_ASSOC);
		$result = $DB->Execute($query) or die ("$l_queryfailed");
		$myresult = $result->fields;	
		$acct_name = $myresult['name'];
		$acct_company = $myresult['company'];
	
		// print the left side bar 
		
		echo '<div id="sidebar">
		<p align=center>
		<img src="images/my-logo.png"></p>';

		/* TEST HIDING THE MODULES ON THE SIDE, PUT SOME DASHBOARD THERE?
		<p align=center class="smalltext">';
		echo "<b>$l_accountnum:&nbsp; $account_number &nbsp; </b><br>";
		echo "$acct_name<br>";
		echo "$acct_company<br></p><p align=right>";
		
		include('./modules.php');
		*/
		include ('./dashboard.php');
		print '</div><div id="header">';
		
		include('./header.php');  
		
		print '</div><div id="content">';
	
		
		// show base function
		// Load the files wrapped by buttons
		// Check if the file is inside the path_to_citrus
		$filepath = "$path_to_citrus/$load.php";
		if (file_exists($filepath)) {
			include('./'.$load.'.php');
		} else {
			echo "<br><br><br><b> $l_error: $l_yourpathtocitrusisincorrect, ";
			$mypath = $_SERVER["SCRIPT_FILENAME"];
			$mypath = substr($mypath,0,-10);
			echo "$l_itshouldbesetto '$mypath'</b>";
		}


		print "<br>";
		$time_end = getmicrotime();
		$time = round(($time_end - $time_start),4);
                
		echo "<center><b><a target=\"_blank\" href=\"$url_prefix/documentation.html#$load\" style=\"color: red; font-size: 10pt;\">?</a></b></center><br>&nbsp; &nbsp; $l_completedin $time $l_seconds";
	
		print "</div></body></html>";
	break; // end base
	
	case 'tools': // show in tools function
		// print the html for the top of pages
		echo "<html>
		<head>
		<title>$l_title</title>
		<LINK href=\"citrus.css\" type=text/css rel=STYLESHEET>
		<LINK href=\"fullscreen.css\" type=text/css rel=STYLESHEET>
        <link rel=\"shortcut icon\" type=\"image/ico\" href=\"favicon.ico\" />
		<script language=\"JavaScript\">
		function h(oR) {
			oR.style.backgroundColor='ffdd77';
		}	
		function deh(oR) {
			oR.style.backgroundColor='ddddee';
		}
		function dehnew(oR) {
			oR.style.backgroundColor='ddeeff';
		}
		
		function popupPage(page) {
			windowprops = \"height=400,width=600,location=no,\"+ \"scrollbars=no,menubars=no,toolbars=no,resizable=no\";
			window.open(page, \"Tools\", windowprops);
		}


                function toggleOff()
                {       
                        var myelement = document.getElementById(\"WaitingMessage\").style;
                        myelement.display=\"none\";     
                }

                function toggleOn()
                {
                        var myelement = document.getElementById(\"WaitingMessage\").style;
                        myelement.display=\"block\";
                }
                
                </script>
                <script language=\"JavaScript\" src=\"include/md5.js\"></script>
                <SCRIPT LANGUAGE=\"JavaScript\" SRC=\"include/prototype.js\"></SCRIPT>
                </head>
                <body marginheight=0 marginwidth=0 topmargin=0 leftmargin=0 onload=\"toggleOff();\">";

		// Check if the file is inside the path_to_citrus
		$filepath = "$path_to_citrus/tools/index.php";
		if (file_exists($filepath)) {
			include('./tools/index.php');
		} else {
			echo "<br><br><br><b> $l_error: $l_yourpathtocitrusisincorrect, ";
			$mypath = $_SERVER["SCRIPT_FILENAME"]; 
 			$mypath = substr($mypath,0,-10);
			echo "$l_itshouldbesetto '$mypath'</b>";			}		
	break; // end tools
	
	}
}
else // show the login screen
{
echo "<html>
<head>
<title>$l_title</title>
<LINK href=\"citrus.css\" type=text/css rel=STYLESHEET>
<LINK href=\"fullscreen.css\" type=text/css rel=STYLESHEET>
<link rel=\"shortcut icon\" type=\"image/ico\" href=\"favicon.ico\" />
<script language=\"JavaScript\">
function h(oR) {
	oR.style.backgroundColor='ffdd77';
}	
function deh(oR) {
	oR.style.backgroundColor='ddddee';
}
function dehnew(oR) {
	oR.style.backgroundColor='ddeeff';
}
</script>
<script language=\"JavaScript\" src=\"include/md5.js\"></script>
<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"include/prototype.js\"></SCRIPT>
</head>
<body marginheight=0 marginwidth=0 topmargin=0 leftmargin=0>
	<div id=horizon>
		<div id=loginbox>
	<center><table><td valign=top><img src=\"images/my-logo.png\">
	<P>
	$l_logintext
	<P>
	<FORM ACTION=\"$ssl_url_prefix/index.php\" METHOD=\"POST\" AUTOCOMPLETE=\"off\">
	<B>$l_username</B><BR>
	<INPUT TYPE=\"TEXT\" NAME=\"user_name\" VALUE=\"\" SIZE=\"15\" MAXLENGTH=\"15\">
	<P>
	<B>$l_password</B><BR>
	<INPUT TYPE=\"password\" NAME=\"password\" VALUE=\"\" SIZE=\"15\" MAXLENGTH=\"32\">
	<P>
	<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_login\"  ".($ldap_enable?"":"onclick=\"password.value = calcMD5(password.value)\" ")."class=smallbutton>
	</FORM>
	<P></td></table></div></div></body></html>";
}

?>
