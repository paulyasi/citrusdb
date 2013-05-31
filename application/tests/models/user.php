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

    function test_admin_login_success()
    {
        $login = $this->CI->user_model->user_login('admin', 'test', '127.0.0.1'); 
        $this->assertTrue($login);
    }

    function test_admin_login_failure()
    {
        // if you run this test over and over with the same IP
        // you will lockout that IP instead of testing user login failure
        // TODO the login failure or success methods should be called
        // by the session controller instead of the user_login method inside the model
        $login = $this->CI->user_model->user_login('admin', 'blahblahblah', '127.1.2.3'); 
        $this->assertFalse($login);
    }

}
