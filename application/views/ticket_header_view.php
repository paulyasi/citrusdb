<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// show recently viewed customers using a query to the log
$this->load->model('log_model');
$data['recent'] = $this->log_model->recently_viewed(
$this->session->userdata('user_name')
);
$this->load->view('recently_viewed_view', $data);
?>
<hr>
<div id="messagetabs">
</div>

<script language="javascript">
new Ajax.PeriodicalUpdater('messagetabs', '<?php echo $this->url_prefix?>/index.php/tickets/messagetabs',
{
method: 'get',
frequency: 300,
});
</script></form>

<?php

$this->load->view('buttonbar_view');

