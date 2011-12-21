<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// page header
$this->load->view('header_with_sidebar_view');
			
// show the customer title info, name and company
$data = $this->customer_model->title($this->account_number);
$this->load->view('customer_in_sidebar_view', $data);

// show the module tab listing (customer, services, billing, etc.)
$this->load->view('moduletabs_view');
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
// show the buttons across the top (new, search, tools, etc)
$this->load->view('buttonbar_view');

