<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<html>
<head>
<title>CitrusDB</title>
<LINK href="<?php echo $this->ssl_url_prefix?>/citrus.css" type=text/css rel=STYLESHEET>
     <LINK href="<?php echo $this->ssl_url_prefix?>/fullscreen.css" type=text/css rel=STYLESHEET>
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
    windowprops = "height=400,width=600,location=no,"+ "scrollbars=no,menubars=no,toolbars=no,resizable=no";
    window.open(page, "Tools", windowprops);
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
    <body marginheight=0 marginwidth=0 topmargin=0 leftmargin=0 onload="toggleOff();"><div id="toolcontent">

    <h3><?php echo lang('exportcreditcards')?></h3>
    <SCRIPT LANGUAGE="JavaScript" SRC="<?php echo $this->ssl_url_prefix?>/js/CalendarPopup.js"></SCRIPT>
    <SCRIPT LANGUAGE="JavaScript">
    var cal = new CalendarPopup();
</SCRIPT>
<table>
<FORM ACTION="<?php echo $this->ssl_url_prefix?>/index.php/tools/billing/saveexportcc" METHOD="POST" name="form1" onsubmit="toggleOn();" AUTOCOMPLETE="off">
    <td><b><?php echo lang('organizationname')?></b></td>
    <td><select name="organization_id">
    <option value=""><?php echo lang('choose')?></option>
    <?php
    foreach ($orglist as $myresult) {
    $myid = $myresult['id'];
    $myorg = $myresult['org_name'];
    echo "<option value=\"$myid\">$myorg</option>";
}
?>
</select></td><tr>

<td><?php echo lang('whatdatewouldyouliketobill')?>:</td>
<td><input type=text name=billingdate value="YYYY-MM-DD" size=12>
    <A HREF="#" 
    onClick="cal.select(document.forms['form1'].billingdate
,'anchor1','yyyy-MM-dd'); return false;"
    NAME="anchor1" ID="anchor1" style="color:blue">[<?php echo lang('select')?>]</A>
    </td><tr>
    <td align=right><?php echo lang('passphrase')?>:</td><td><input type=password name=passphrase></td><tr>
    <td></td><td><INPUT TYPE="SUBMIT" NAME="submit" value="<?php echo lang('submitrequest')?>">
    </td>
    </form>
    </table><br><br><br>

    <FORM ACTION="<?php echo $this->ssl_url_prefix?>/index.php/tools/billing/saveexportcc" METHOD="POST" name="form2" onsubmit="toggleOn();" AUTOCOMPLETE="off">
    <table>

    <td><b><?php echo lang('organizationname')?></b></td>
    <td><select name="organization_id">
    <option value=""><?php echo lang('choose')?></option>
    <?php
    foreach ($orglist as $myresult) {
    $myid = $myresult['id'];
    $myorg = $myresult['org_name'];
    echo "<option value=\"$myid\">$myorg</option>";
}
?>
</select></td><tr>

<td><?php echo lang('whatdatewouldyouliketobill')?>:</td>
<td><input type=text name=billingdate1 value="YYYY-MM-DD" size=12>
    <A HREF="#" onClick="cal.select(document.forms['form2'].billingdate1
,'anchorb1','yyyy-MM-dd'); return false;"
    NAME="anchorb1" ID="anchorb1" style="color:blue">[<?php echo lang('select')?>]</A>
    </td> 
    <td> to <input type=text name=billingdate2 value="YYYY-MM-DD" size=12>
    <A HREF="#" 
    onClick="cal.select(document.forms['form2'].billingdate2
,'anchorb2','yyyy-MM-dd'); return false;"
    NAME="anchorb2" ID="anchorb2" style="color:blue">[<?php echo lang('select')?>]</A>
    </td><tr>
    <td align=right><?php echo lang('passphrase')?>:</td><td>
    <input type=password name=passphrase></td><tr>
    <td></td><td><INPUT TYPE="SUBMIT" NAME="submit" value="<?php echo lang('submitrequest')?>">
    </td>
    </form>
    </table><p>

    <a href="<?php echo $this->ssl_url_prefix?>/index.php/tools/billing/fixexportcc">
    <?php echo lang('exportpreviousbatchid')?></a><p>

    <div id="WaitingMessage" style="border: 0px double black; 
background-color: #fff; position: absolute; text-align: center; top: 0px; width: 550px; height: 400px;">
    <BR><BR><BR><h3><?php echo lang('processing')?>...</h3>
    <p><img src="<?php echo $this->ssl_url_prefix?>/images/spinner.gif"></p>
    </div>	

