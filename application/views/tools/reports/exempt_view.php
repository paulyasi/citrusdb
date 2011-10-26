<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<html>
<body bgcolor="#ffffff">
<h3><?php echo lang('exemptreport')?>: 
<p><FORM ACTION="<?php echo $this->url_prefix?>/index.php/tools/reports/showexempt" METHOD="POST"><table>
<select name="exempttype">
<option value="pastdueexempt"><?php echo lang('pastdueexempt')?></option>
<option value="baddebt"><?php echo lang('bad_debt')?></option>"
<option value="taxexempt"><?php echo lang('taxexempt')?></option>
</select>
</select><input type=hidden name=type value=tools>
<input type=hidden name=load value=exemptreport>
</td><tr> 
<td></td><td><br>
<input type=submit name="<?php echo lang('submit')?>" value="submit"></td>
</table>
</form> <p>
</body>
</html>
