<html>
<head>
<LINK href="../../citrus.css" type=text/css rel=STYLESHEET>
<LINK href="../../fullscreen.css" type=text/css rel=STYLESHEET>
<link rel="shortcut icon" type="image/ico" href="../../favicon.ico" />
<title><?php echo lang('title');?></title>
</head>
<body marginheight=0 marginwidth=0 topmargin=0 leftmargin=0>
<div id=horizon>
	<div id=loginbox>
		<center><table><td valign=top><img src="../../images/my-logo.png">
		<P>
		<form action = <?php echo $this->config->item('ssl_base_url') . "/index.php/session/auth" ?> 
			autocomplete=off method=post>
		<B><?php echo lang('username', 'user_name'); ?></B><BR>
		<INPUT TYPE="TEXT" id="user_name" NAME="user_name" VALUE="" SIZE="15" MAXLENGTH="15">
		<P>
		<B><?php echo lang('password', 'password'); ?></B><BR>
		<INPUT TYPE="password" NAME="password" VALUE="" SIZE="15" MAXLENGTH="32">
		<P>
		<INPUT TYPE="SUBMIT" NAME="submit" VALUE="login" class=smallbutton>
		</FORM>
		<P></td></table>
	</div>
</div>
</body>
</html>

