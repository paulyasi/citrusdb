<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<p>
<h3><?php echo lang('importnewaccounts')?></h3>
<?php echo $error;?>
<FORM ACTION="<?php echo $this->ssl_url_prefix?>/index.php/tools/billing/uploadnew" METHOD="POST" enctype="multipart/form-data">
<table>
<td><?php echo lang('importfile')?>:</td><td><input type=file name="userfile"></td><tr> 
<td></td><td><br><input type=submit name="import" value="<?php echo lang('import')?>"></td>
</table>
</form> 

