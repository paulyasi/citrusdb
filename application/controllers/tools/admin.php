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

		if ($feedback['status'] == TRUE)
		{
			$this->load->model('settings_model');
			$default_group = $this->settings_model->get_default_group();

			// if there is a default group, add them to that group
			if ($default_group != '')
			{
				$this->user_model->add_user_to_group($default_group, $new_user_name);
			}
		}

		echo '<FONT COLOR="RED"><H2>'.$feedback['message'].'</H2></FONT>';
		echo "<p>$new_user_name<p>$password1<p>$password2<p>$real_name";

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

		$data['userid'] = $userid;
		$data['u'] = $this->user_model->get_user_info($userid);

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/edituser_view', $data);
	}


	function saveedituser()
	{
		$userid = $this->input->post('userid');

		$userinfo = array(
				'username' => $this->input->post('username'),
				'real_name' => $this->input->post('realname'),
				'admin' => $this->input->post('admin'),
				'manager' => $this->input->post('manager'),
				'email' => $this->input->post('email'),
				'screenname' => $this->input->post('screenname'),
				'email_notify' => $this->input->post('email_notify'),
				'screenname_notify' => $this->input->post('screenname_notify')
				);

		$this->user_model->update_user_info($userid, $userinfo);

		redirect("/tools/admin/edituser/".$userid);
	}


	function deleteuser($uid)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['uid'] = $uid;

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/deleteuser_view', $data);
	}


	function savedeleteuser()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$uid = $this->input->post('uid');

		$username = $this->user_model->get_username($uid);

		$this->user_model->delete_user($uid);

		$this->user_model->delete_username_from_groups($username);

		// redirect back to the user list page
		redirect('/tools/admin/users');
	}

	function groups()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}
		
		$data['groups'] = $this->admin_model->get_groups();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/groups_view', $data);
	}


	function addgroup()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}
		
		$data['users'] = $this->user_model->list_users();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/addgroup_view', $data);
	}


	function saveaddgroup()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$membername = $this->input->post('membername');
		$groupname = $this->input->post('groupname');

		$this->user_model->add_to_group($groupname, $membername);

		print "<h3>$l_changessaved</h3>";

		redirect("/tools/admin/groups");
	}


	function deletegroup($gid)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['gid'] = $gid;

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/deletegroup_view', $data);
	}


	function savedeletegroup()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$gid = $this->input->post('gid');

		$this->user_model->delete_group($gid);

		// redirect back to the group list page
		redirect("/tools/admin/groups");
	}


	function modules()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['modules'] = $this->module_model->modulelist();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/modules_view', $data);
	}

	function addmodule()
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

		$this->load->view('tools/admin/addmodule_view');
	}


	function saveaddmodule()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$commonname = $this->input->post('commonname');
		$modulename = $this->input->post('modulename');
		$sortorder = $this->input->post('sortorder');

		$this->module_model->addmodule($commonname, $modulename, $sortorder);
		print "<h3>Modules Updated</h3>";

		redirect('/tools/admin/modules');
	}


	function modulepermissions($modulename)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['module'] = $modulename;
		$data['permissions'] = $this->module_model->get_module_permissions($modulename);

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/modulepermissions_view', $data);
	}


	function addmodulepermissions($modulename)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['module'] = $modulename;
		$data['permissions'] = $this->module_model->get_module_permissions($modulename);
		$data['groupslist'] = $this->user_model->list_groups();
		$data['userslist'] = $this->user_model->list_users();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/addmodulepermissions_view', $data);
	}


	function savemodulepermissions()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}
		$module = $this->input->post('module');
		$permission = $this->input->post('permission');
		$usergroup = $this->input->post('usergroup');

		$this->module_model->add_permissions($module, $permission, $usergroup);

		print lang('changessaved');

		redirect("/tools/admin/modulepermissions/".$module);

	}

	function removemodulepermissions($pid, $module)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['pid'] = $pid;
		$data['module'] = $module;
		
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/removemodulepermissions_view', $data);
	}


	function saveremovemodulepermissions()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		//GET Variables
		$module = $this->input->post('module');
		$deletenow = $this->input->post('deletenow');
		$pid = $this->input->post('pid');

		$this->module_model->remove_permission($pid);

		print lang('changessaved');

		redirect("/tools/admin/modulepermissions/".$module);
	}


	function billingtypes()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['billingtypes'] = $this->admin_model->get_billing_types();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/billingtypes_view', $data);
	}


	function addbillingtype()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$method = $this->input->post('method');
		$frequency = $this->input->post('frequency');
		$name = $this->input->post('name');

		$this->admin_model->add_billing_type($name, $frequency, $method);

		redirect('/tools/admin/billingtypes');
	}


	function removebillingtype($typeid)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['typeid'] = $typeid;

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/removebillingtype_view', $data);
	}


	function saveremovebillingtype()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$typeid = $this->input->post('typeid');

		$this->admin_model->remove_billing_type($typeid);


		redirect('/tools/admin/billingtypes');
	}


	function services()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['masterservices'] = $this->admin_model->get_master_services();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/services_view', $data);
	}

	function addnewservice()
	{
		// need general model to get list of org's
		$this->load->model('general_model');

		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}
		
		$data['org_list'] = $this->general_model->list_organizations();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/addnewservice_view', $data);
	}


	function saveaddnewservice()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$servicearray = array(
				'service_description' => $this->input->post('service_description'),
				'pricerate' => $this->input->post('pricerate'),
				'frequency' => $this->input->post('frequency'),
				'options_table' => $this->input->post('options_table'),
				'category' => $this->input->post('category'),
				'selling_active' => $this->input->post('selling_active'),
				'activate_notify' => $this->input->post('activate_notify'),
				'shutoff_notify' => $this->input->post('shutoff_notify'),
				'hide_online' => $this->input->post('hide_online'),
				'activation_string' => $this->input->post('activation_string'),
				'usage_label' => $this->input->post('usage_label'),
				'organization_id' => $this->input->post('organization_id'), 
				'modify_notify' => $this->input->post('modify_notify'), 
				'support_notify' => $this->input->post('support_notify'), 
				'carrier_dependent' => $this->input->post('carrier_dependent')
				);

		$this->admin_model->add_master_service($servicearray);

		redirect('/tools/admin/services');
	}
	
	function editservice($service_id)
	{
		// need general model to get list of org's
		$this->load->model('general_model');

		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['service_id'] = $service_id;
		$data['s'] = $this->admin_model->get_service_info($service_id);
		$data['org_name'] = $this->general_model->get_org_name($data['s']['organization_id']);

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/editservice_view', $data);
	}


	function saveeditservice()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}
		$service_id = $this->input->post('service_id');

		$servicearray = array(
				'service_description' => $this->input->post('service_description'),
				'pricerate' => $this->input->post('pricerate'),
				'frequency' => $this->input->post('frequency'),
				'options_table' => $this->input->post('options_table'),
				'category' => $this->input->post('category'),
				'selling_active' => $this->input->post('selling_active'),
				'hide_online' => $this->input->post('hide_online'),
				'activate_notify' => $this->input->post('activate_notify'),
				'shutoff_notify' => $this->input->post('shutoff_notify'),
				'modify_notify' => $this->input->post('modify_notify'),
				'support_notify' => $this->input->post('support_notify'),
				'activation_string' => $this->input->post('activation_string'),
				'usage_label' => $this->input->post('usage_label'),
				'carrier_dependent' => $this->input->post('carrier_dependent')
				);

		$this->admin_model->update_service_info($service_id, $servicearray);

		redirect('/tools/admin/services');

	}


	function linkservices()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}
		
		$data['master_services'] = $this->admin_model->get_master_services();
		$data['linkedservices'] = $this->admin_model->linked_services();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/linkservices_view', $data);
	}


	function savelinkservices()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$linkfrom = $this->input->post('linkfrom');
		$linkto = $this->input->post('linkto');

		$this->admin_model->add_service_link($linkfrom, $linkto);
		redirect('/tools/admin/linkservices');
	}

	function removelinkservices($linkfrom, $linkto)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		// remove the link
		$this->admin_model->remove_service_link($linkfrom, $linkto);
		redirect('/tools/admin/linkservices');
	}


	function optionstables()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}
		
		$data['options_tables'] = $this->admin_model->options_tables();
		$data['tableresult'] = $this->db->list_tables();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/optionstables_view', $data);
	}


	function createoptionstable($tablename)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$this->admin_model->create_options_table($tablename);

		redirect('/tools/admin/optionstables');
	}


	function taxes()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}
		
		$data['tax_rates'] = $this->admin_model->get_tax_rates();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/taxes_view', $data);
	}

	function addtaxrate()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$description = $this->input->post('description');
		$rate = $this->input->post('rate');
		$if_field = $this->input->post('if_field');
		$if_value = $this->input->post('if_value');
		$percentage_or_fixed = $this->input->post('percentage_or_fixed');

		$this->admin_model->add_tax_rate($description, $rate, $if_field, $if_value, $percentage_or_fixed);

		redirect('/tools/admin/taxes');
	}

	function deletetaxrate($id)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['id'] = $id;

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/deletetaxrate_view', $data);

	}

	function savedeletetaxrate()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$this->admin_model->delete_tax_rate($this->input->post('id'));
		redirect('/tools/admin/taxes');
	}


	function taxedservices()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['taxed_services'] = $this->admin_model->taxed_services();
		$data['master_services'] = $this->admin_model->get_master_services();
		$data['tax_rates'] = $this->admin_model->get_tax_rates();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/taxedservices_view', $data);
	}

	function addtaxedservice()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$linkedservice = $this->input->post('linkedservice');
		$torate = $this->input->post('torate');

		$this->admin_model->add_taxed_service($linkedservice, $torate);

		redirect('/tools/admin/taxedservices');
	}

	function deletetaxedservice($id)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['id'] = $id;

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/deletetaxedservice_view', $data);
	}

	function savedeletetaxedservice()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$id = $this->input->post('id');

		$this->admin_model->delete_taxed_service($id);

		redirect('/tools/admin/taxedservices');
	}


	function fieldassets()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['service_categories'] = $this->admin_model->get_service_categories();
		$data['master_field_assets'] = $this->admin_model->get_field_assets();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/fieldassets_view', $data);
	}


	function addfieldasset() 
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$description = $this->input->post('description');
		$status = $this->input->post('status');
		$weight = $this->input->post('weight');
		$category = $this->input->post('category');

		$this->admin_model->add_field_asset($description, $status, $weight, $category);
		
		redirect('/tools/admin/fieldassets');
	}

	function changefieldassetstatus ($id, $status) 
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$this->admin_model->change_asset_status($id, $status);

		redirect('/tools/admin/fieldassets');
	}

	function mergeaccounts()
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

		$this->load->view('tools/admin/mergeaccounts_view');
	}

	function confirmmergeaccounts()
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

		$this->load->view('tools/admin/confirmmergeaccounts_view');
	}


	function savemergeaccounts() 
	{
		// get the default billing id in the $to_account
		$query = "SELECT default_billing_id FROM customer WHERE account_number = '$to_account'";
		$result = $DB->Execute($query) or die ("default billing id select $l_queryfailed");
		$myresult = $result->fields;
		$default_billing_id = $myresult['default_billing_id'];

		// move the services to the new record
		$query = "UPDATE user_services SET account_number = '$to_account', ".
			"billing_id = '$default_billing_id' WHERE account_number = '$from_account'";
		$result = $DB->Execute($query) or die ("user services update $l_queryfailed");

		// move the customer history to the new record
		$query = "UPDATE customer_history SET account_number = '$to_account' ".
			"WHERE account_number = '$from_account'";
		$result = $DB->Execute($query) or die ("customer history update $l_queryfailed");

		// make a note on both records that they were merged
		$desc = "$l_merged $from_account $l_to $to_account";  
		create_ticket($DB, $user, NULL, $to_account, 'automatic', $desc);
		create_ticket($DB, $user, NULL, $from_account, 'automatic', $desc);

		print "<h3>$desc</h3>";
	}
}

/* End of file admin */
/* Location: ./application/controllers/tools/admin.php */
