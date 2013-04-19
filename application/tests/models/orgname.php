<?php
/**
 * Check we have the general information like org_name
 */

// prevent direct loading of this file
if ( basename(__FILE__) == basename($_SERVER['PHP_SELF']) ) { die("This file cannot be loaded directly."); }

class Test_Org_Name extends CI_TestCase
{
	function setUp()
    {
		parent::setUp();
        $this->CI->load->model('general_model');    
	}

	function testVersion()
    {
        $org_name = $this->CI->general_model->get_org_name('1');
		$this->assertEquals( 'Citrus DB' , $org_name );
	}
}
