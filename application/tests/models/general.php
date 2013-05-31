<?php
/**
 * Check we have the general model working
 */

// prevent direct loading of this file
if ( basename(__FILE__) == basename($_SERVER['PHP_SELF']) ) { die("This file cannot be loaded directly."); }

class Test_General extends CI_TestCase
{
	function setUp()
    {
		parent::setUp();
        $this->CI->load->model('general_model');    
	}

    // test that we only have the 1 test org we expect
    function test_list_organizations()
    {
        $org_list = $this->CI->general_model->list_organizations();
        $this->assertEquals( '1', $org_list[0]['id']);
        $this->assertEquals( 'Citrus DB', $org_list[0]['org_name']);
    }

    
	function test_get_org_name()
    {
        $org_name = $this->CI->general_model->get_org_name('1');
		$this->assertEquals( 'Citrus DB' , $org_name );
	}
}
