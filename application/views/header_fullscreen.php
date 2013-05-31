<html>
<head>
<title><?php echo lang('title');?></title>
<LINK href="<?php echo $this->url_prefix;?>/citrus.css" type=text/css rel=STYLESHEET>
<LINK href="<?php echo $this->url_prefix;?>/fullscreen.css" type=text/css rel=STYLESHEET>
<link rel="shortcut icon" type="image/ico" href="favicon.ico" />
<SCRIPT LANGUAGE="JavaScript" SRC="<?php echo $this->url_prefix?>/js/CalendarPopup.js"></SCRIPT>
<script language="javascript" src="<?php echo $this->url_prefix?>/js/prototype.js"></script>	 
   <SCRIPT LANGUAGE="JavaScript">
   var cal = new CalendarPopup();

function cardval(s) 
{
  // remove non-numerics
  var v = "0123456789";
  var w = "";
  for (i=0; i < s.length; i++) {
    x = s.charAt(i);
    if (v.indexOf(x,0) != -1) {
      w += x;
    }
  }
  
  // validate number
  j = w.length / 2;
  if (j < 6.5 || j > 8 || j == 7) {
    return false;
  }
  
  k = Math.floor(j);
  m = Math.ceil(j) - k;
  c = 0;
  for (i=0; i<k; i++) {
    a = w.charAt(i*2+m) * 2;
    c += a > 9 ? Math.floor(a/10 + a%10) : a;
  }
  
  for (i=0; i<k+m; i++) c += w.charAt(i*2+1-m) * 1; {
    return (c%10 == 0);
  }
}
</SCRIPT>
<script language="JavaScript">
function h(oR) 
{
	oR.style.backgroundColor='ffdd77';
}	
function deh(oR) 
{
	oR.style.backgroundColor='ddddee';
}
function dehnew(oR) 
{
	oR.style.backgroundColor='ddeeff';
}

function popupPage(page) {
	window.open(page, "Tools", "height=400,width=600,location=0,scrollbars=1,menubar=1,toolbar=0,resizeable=1,left=100,top=100");
}

function toggleOff()
{       
	var myelement = document.getElementById("WaitingMessage").style;
	myelement.display="none";     
}

function toggleOn()
{
	var myelement = document.getElementById("WaitingMessage").style;
	myelement.display="block";
}

</script>
</head>
<body onload="toggleOff();">
