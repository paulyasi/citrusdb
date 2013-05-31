<FORM ACTION="<?php echo $this->url_prefix;?>index.php/services/saveremovaldate" METHOD="POST">
<input type=hidden name=serviceid value="<?php echo $serviceid?>">
<table>
<td><?php echo lang('new')." ".lang('removaldate');?>:</td>
<td><input type=text name=removaldate value="<?php echo $removaldate?>"></td><tr>
<td></td><td><INPUT TYPE="SUBMIT" NAME="submit" value="<?php echo lang('submitrequest')?>"></td>
</form>


