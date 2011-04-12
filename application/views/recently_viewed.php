<p align=center><b>Recently Viewed:</b>
<table cellpadding=10><td class="smalltext">

<?php foreach($recent->result() as $customer):?>

  <a href="
  <?php echo $this->ssl_url_prefix?>view/account/<?php echo $customer->account_number;?>
  ">
  <?php echo $customer->account_number . ": " . $customer->name?></a><br>

<?php endforeach;?>
</td></table></p>