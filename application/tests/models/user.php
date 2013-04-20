<?php
/**
 * Check we have the user model working
 */

// prevent direct loading of this file
if ( basename(__FILE__) == basename($_SERVER['PHP_SELF']) ) { die("This file cannot be loaded directly."); }

class Test_User extends CI_TestCase
{
	function setUp()
    {
		parent::setUp();
        $this->CI->load->model('user_model');    
    }

    function test_admin_user_privileges()
    {
        $privileges = $this->CI->user_model->user_privileges('admin');
        $this->assertEquals('y', $privileges['admin']);
    }

    function test_login_failures_is_false()
    {
        $loginfailure = $this->CI->user_model->checkfailures('127.0.0.1');
        $this->assertFalse($loginfailure);
    }

    function test_admin_login()
    {
        $login = $this->CI->user_model->user_login('admin', 'test', '127.0.0.1'); 
        $this->assertTrue($login);
    }

}
