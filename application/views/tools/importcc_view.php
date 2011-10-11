<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('importcreditcards')?></h3>
<FORM ACTION="<?php echo $this->ssl_url_prefix?>/index.php/tools/saveimportcc" METHOD="POST" enctype="multipart/form-data">
<table>
<td><?php echo lang('importfile')?>:</td>
<td><input type=file name="userfile" size=50></td><tr> 
<td></td><td><br>
<input type=submit name="<?php echo lang('import')?>" value="<?php echo lang('import')?>">
</td>
</table>
</form> 
</body>
</html>
