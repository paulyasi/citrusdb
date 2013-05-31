<?php
/**
 * Check we have the settings model working
 */

// prevent direct loading of this file
if ( basename(__FILE__) == basename($_SERVER['PHP_SELF']) ) { die("This file cannot be loaded directly."); }

class Test_Settings extends CI_TestCase
{
	function setUp()
    {
		parent::setUp();
        $this->CI->load->model('settings_model');    
	}

    function test_dependent_cancel_url()
    {
        $url = $this->CI->settings_model->dependent_cancel_url();
        $this->assertEquals('http://localhost/cancel', $url);        
    }

<<<<<<< HEAD
=======
    function test_get_path_to_ccfile()
    {
        $path = $this->CI->settings_model->get_path_to_ccfile();
        $this->assertEquals('/home/pyasi/Code/io', $path);
    }

>>>>>>> d6db3ec329232a8379c7b27daaf6392fb9ddc040
    function test_get_default_group()
    {
        $group = $this->CI->settings_model->get_default_group();
        $this->assertEquals('users', $group);
    }


    function test_get_default_shipping_group()
    {
        $shippinggroup = $this->CI->settings_model->get_default_shipping_group();
        $this->assertEquals('shipping', $shippinggroup);        
    }

	function test_get_default_billing_group()
    {
        $group = $this->CI->settings_model->get_default_billing_group();
        $this->assertEquals('billing', $group);
	}
    
}
