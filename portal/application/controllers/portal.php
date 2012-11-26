<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Portal extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */

	/*
	 * requires php5-curl package
	 */
	
	public function index()
	{
		//$this->load->view('welcome_message');
		
		// Load the rest client spark
		$this->load->spark('restclient/2.0.0');

		// load the rest library in the spark
		$this->load->library('rest');
									   
		// test url
		// http://localhost/~pyasi/code/citrusdb_3.x/index.php/api/portal/customer/id/1
		
		// initialize with SSL
		/*
		$this->rest->initialize(array('server' => 'https://ubuntu/~pyasi/code/citrusdb_3.x/index.php/api/portal/',
					'http_user' => '1',
					'http_pass' => 'test',
					'http_auth' => 'basic',
					'ssl_verify_peer' => true,
					'ssl_cainfo' => '/ssl-cert-snakeoil.pem')); // use ssl to make sure this is securely transmitted
		*/
		
		// OR initialize without SSL, only do this if you are not sending data
		// over a network, but all on the same localhost
	   $this->rest->initialize(array('server' => 'http://localhost/citrusdb/index.php/api/portal/',
		   'http_user' => '1',
		   'http_pass' => 'test',
		   'http_auth' => 'basic'));

		// set the apikey header
		$this->rest->api_key('$4$Sr0mgU3V$XanWKx0cv+HjOmCxnInTEh42UWY$', 'X-API-KEY');

		
		// Pull in an array of the billing profile
		$customer_output = $this->rest->get('customer');
		
		// Pull in an array of the billing profile
		$billing_output = $this->rest->get('billing');
		
		// update the billing profile info			
		$billing_result = $this->rest->post('billing', array('name' => 'Example Customer', 
													'company' => 'Example Company',
													'street' => 'Example Street',
													'city' => 'ExampleLand',
													'state' => 'MA',
													'zip' => '01234',
													'country' => 'USA',
													'phone' => '555-555-5555',
													'creditcard_expire' => '0113',
													'contact_email' => 'test@example.com'));

        $service_output = $this->rest->get('services');
        $billinghistory_output = $this->rest->get('billinghistory');
        $paymenthistory_output = $this->rest->get('paymenthistory');

        // get a pdf post output for a single invoice id			
        $invoice_data_result = $this->rest->get('invoicedata/2', 
                array('id' => '2'));
       
        // get a pdf post output for a single invoice id			
        $invoice_detail_result = $this->rest->get('invoicedetail',
                array('id' => '2'));

        $support_result = $this->rest->post('support', array(
                    'notify' => 'admin',
                    'status' => 'not done',
                    'description' => 'test to support',
                    'services_id' => '0'));

        $count = 1;		

        echo "customer output: $customer_output<pre>";
        var_dump($customer_output);

        echo "billing output: $billing_output<pre>";
        var_dump($billing_output);

        echo "billing result: $billing_result<pre>";
        var_dump($billing_result);

        echo "service output: $service_output<pre>";
        var_dump($service_output);

        echo "billinghistory output: $billinghistory_output<pre>";
        var_dump($billinghistory_output);

        echo "paymenthistory output: $paymenthistory_output<pre>";
        var_dump($paymenthistory_output);
        
        echo "invoice data output: $customer_output<pre>";
        var_dump($invoice_data_result);
      
        echo "invoice detail output: $customer_output<pre>";
        var_dump($invoice_detail_result);
        
        echo "support result: $support_result<pre>";
        var_dump($support_result);

        //var_dump($output);

        /*
           foreach ($profile as $billing)
           {
           print_r(array_keys($billing));
           foreach ($billing as $billingrecord)
           {
        //print_r(array_keys($billingrecord));
        echo $billingrecord['t_name'];
        }
        }
         */


    }
}

/* End of file portal.php */
/* Lnocation: ./application/controllers/portal.php */
