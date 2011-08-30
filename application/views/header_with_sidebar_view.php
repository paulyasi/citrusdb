<html>
<head>
<title><?php echo lang('title');?></title>
<LINK href="<?php echo $this->url_prefix;?>/citrus.css" type=text/css rel=STYLESHEET>
<link rel="shortcut icon" type="image/ico" href="favicon.ico" />
<script language="JavaScript">
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
		window.open(page, "Tools", "height=400,width=600,location=0,scrollbars=1,menubar=1,toolbar=0,resizeable=1,left=100,top=100");
	}
</script>
</head>
<body marginheight=0 marginwidth=0 topmargin=0 leftmargin=0>
<div id="sidebar">
<p align=center>
<img src="<?php echo $this->url_prefix;?>/images/my-logo.png"></p>
