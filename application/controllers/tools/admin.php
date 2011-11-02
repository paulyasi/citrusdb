<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends App_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('customer_model');
		$this->load->model('module_model');
		$this->load->model('user_model');
		$this->load->model('admin_model');
	}		


	function organization($id = NULL)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		// if no id is specified, set to 1
		if (!$id) 
		{
			$id = 1;
		}

		$data['org_list'] = $this->admin_model->org_list();
		$data['org'] = $this->admin_model->get_organization($id);
		
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/organization_view', $data);
	}


	function updateorganization()
	{
		$id = $this->input->post('id');

		$org_data = array(
				'org_name' => $this->input->post('org_name'),
				'org_street' => $this->input->post('org_street'),
				'org_city' => $this->input->post('org_city'),
				'org_state' => $this->input->post('org_state'),
				'org_country' => $this->input->post('org_country'),
				'org_zip' => $this->input->post('org_zip'),
				'phone_sales' => $this->input->post('phone_sales'),
				'email_sales' => $this->input->post('email_sales'),
				'phone_billing' => $this->input->post('phone_billing'),
				'email_billing' => $this->input->post('email_billing'),
				'phone_custsvc' => $this->input->post('phone_custsvc'),
				'email_custsvc' => $this->input->post('email_custsvc'),
				'ccexportvarorder' => $this->input->post('ccexportvarorder'),
				'regular_pastdue' => $this->input->post('regular_pastdue'),
				'regular_turnoff' => $this->input->post('regular_turnoff'),
				'regular_canceled' => $this->input->post('regular_canceled'),
				'dependent_pastdue' => $this->input->post('dependent_pastdue'),
				'dependent_shutoff_notice' => $this->input->post('dependent_shutoff_notice'),
				'dependent_turnoff' => $this->input->post('dependent_turnoff'),
				'dependent_canceled' => $this->input->post('dependent_canceled'),
				'default_invoicenote' => $this->input->post('default_invoicenote'),
				'pastdue_invoicenote' => $this->input->post('pastdue_invoicenote'),
				'turnedoff_invoicenote' => $this->input->post('turnedoff_invoicenote'),
				'collections_invoicenote' => $this->input->post('collections_invoicenote'),
				'declined_subject' => $this->input->post('declined_subject'),
				'declined_message' => $this->input->post('declined_message'),
				'invoice_footer' => $this->input->post('invoice_footer'),
				'einvoice_footer' => $this->input->post('einvoice_footer'),
				'exportprefix' => $this->input->post('exportprefix') 
					);

		$this->admin_model->update_organization($id, $org_data);

		redirect("/tools/admin/organization/".$id);
	}


	function addorganization()
	{
		$newid = $this->admin_model->add_organization();
		redirect("/tools/admin/organization/".$newid);
	}


	function settings()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['set'] = $this->admin_model->get_settings();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/settings_view', $data);
	}


	function savesettings()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$settings_array = array(
				'path_to_ccfile' => $this->input->post('path_to_ccfile'),
				'default_group' => $this->input->post('default_group'),
				'default_billing_group' => $this->input->post('default_billing_group'),
				'default_shipping_group' => $this->input->post('default_shipping_group'),
				'billingdate_rollover_time' => $this->input->post('billingdate_rollover_time'),
				'billingweekend_sunday' => $this->input->post('billingweekend_sunday'),
				'billingweekend_monday' => $this->input->post('billingweekend_monday'),
				'billingweekend_tuesday' => $this->input->post('billingweekend_tuesday'),
				'billingweekend_wednesday' => $this->input->post('billingweekend_wednesday'),
				'billingweekend_thursday' => $this->input->post('billingweekend_thursday'),
				'billingweekend_friday' => $this->input->post('billingweekend_friday'),
				'billingweekend_saturday' => $this->input->post('billingweekend_saturday'),
				'dependent_cancel_url' => $this->input->post('dependent_cancel_url')
				);

		$this->admin_model->update_settings($settings_array);

		redirect('/tools/admin/settings');

	}


	function users()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['users'] = $this->admin_model->get_users();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/users_view', $data);
	}

	
	function newuser()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/newuser_view');
	}


	function savenewuser()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$new_user_name = $this->input->post('new_user_name');
		$password1 = $this->input->post('password1');
		$password2 = $this->input->post('password2');
		$real_name = $this->input->post('real_name');
		$admin = $this->input->post('admin');
		$manager = $this->input->post('manager');

		$feedback = $this->user_model->user_register($new_user_name,$password1,$password2,$real_name,$admin,$manager);

		$this->load->model('settings_model');
		$default_group = $this->settings_model->get_default_group();

		// if there is a default group, add them to that group
		if ($default_group != '')
		{
			$this->user_model->add_user_to_group($default_group, $new_user_name);
		}

		if ($feedback) {
			echo '<FONT COLOR="RED"><H2>'.$feedback.'</H2></FONT>';
			echo "<p>$new_user_name<p>$password1<p>$password2<p>$real_name";
		}

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/newuser_view');
	}


	function edituser($userid)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['users'] = $this->admin_model->get_user($userid);

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/edituser_view', $data);
	}


	function deleteuser($userid)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['users'] = $this->admin_model->get_user($userid);

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/deleteuser_view', $data);
	}

	function groups()
	{
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/groups_view');
	}


	function modules()
	{
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/modules_view');
	}


	function billingtypes()
	{
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/billingtypes_view');
	}


	function services()
	{
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/services_view');
	}


	function mergeaccounts()
	{
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/mergeaccounts_view');
	}
}

/* End of file user */
/* Location: ./application/controllers/tools/user.php */
