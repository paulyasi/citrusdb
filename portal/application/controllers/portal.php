<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Portal extends CI_Controller {

  function __construct()
  {
    parent::__construct();

    // load url helper
    $this->load->helper('url');

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

  }

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
    // Pull in an array of the billing profile
    $customer = $this->rest->get('customer', array(), 'json');

    // Pull in an array of the billing profile
    $billing_output = $this->rest->get('billing');

    /*
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
    $paymenthistory_output = $this->rest->get('paymenthistory');


    $support_result = $this->rest->post('support', array(
          'notify' => 'admin',
          'status' => 'not done',
          'description' => 'test to support',
          'services_id' => '0'));

    $count = 1;		

       echo "customer output: $customer_output<pre>";

       echo "billing output: $billing_output<pre>";
       var_dump($billing_output);

       echo "billing result: $billing_result<pre>";
       var_dump($billing_result);

       echo "service output: $service_output<pre>";
       var_dump($service_output);

     */

    echo "<h2>Customer Portal</h2>";
    echo "$customer->name<br>";
    echo "$customer->company<br>";
    echo "$customer->street<br>";
    echo "$customer->city"." "."$customer->state"." "."$customer->zip";
    echo "<br>";

    // this returns an array of objects 
    $billinghistory_output = $this->rest->get('billinghistory', array(), 'json');

    echo "<table><td>invoice</td><td>account</td><td>date</td><td>type</td><td>from</td><td>to</td><td>total</td><td>due date</td><tr>";
    foreach ($billinghistory_output as $this_invoice)
    {
      echo "<td><a href=\"".base_url()."index.php/portal/invoice/$this_invoice->h_id\">view invoice ".$this_invoice->h_id."</a></td>";
      echo "<td>".$this_invoice->b_acctnum."</td>";
      echo "<td>".$this_invoice->h_bdate."</td>";
      echo "<td>".$this_invoice->h_btype."</td>";
      echo "<td>".$this_invoice->h_from."</td>";
      echo "<td>".$this_invoice->h_to."</td>";
      echo "<td>".$this_invoice->h_total."</td>";
      echo "<td>".$this_invoice->h_payment_due_date."</td>";
      echo "<tr>";
    }
    echo "</table>";

    echo "<br>\n";
  }

  public function invoice($invoice_id)
  {
    // get a pdf post output for a single invoice id
    $invoice_data = $this->rest->get('invoicedata',
        array('id' => $invoice_id), 'json');

    echo "<b>invoice id: $invoice_data->h_id</b>";
    
    echo "<br>";
    echo "invoice date: $invoice_data->h_billing_date";
    echo "<br>";
    echo "from: $invoice_data->h_from_date";
    echo " to: $invoice_data->h_to_date";
    echo "<br>";
    echo "due: $invoice_data->h_payment_due_date";
    echo "<br>";
    
   
    echo "billed to:<br>";
    echo "<br>";
    echo "$invoice_data->b_name";
    echo "<br>";
    echo "$invoice_data->b_company";
    echo "<br>";
    echo "$invoice_data->b_street";
    echo "<br>";
    echo "$invoice_data->b_city";
    echo "<br>";
    echo "$invoice_data->b_state";
    echo "<br>";
    echo "$invoice_data->b_zip";
    echo "<br>";
    echo "<br>";
    

    // get a pdf post output for a single invoice id
    $invoice_detail = $this->rest->get('invoicedetail',
        array('id' => $invoice_id), 'json');

    echo "<table><td>service id</td><td>description</td><td>price</td><tr>";
    foreach ($invoice_detail as $item)
    {
      echo "<td>$item->d_user_services_id</td>";
      echo "<td>$item->m_service_description $item->tr_description</td>";
      echo "<td>$item->d_billed_amount</td><tr>";
    }
    echo "</table>";
   
    echo "<br>";
    echo "new charges: $invoice_data->h_new_charges";
    echo "<br>";
    echo "past charges: $invoice_data->h_past_due";
    echo "<br>";
    echo "late fee: $invoice_data->h_late_fee";
    echo "<br>";
    echo "tax/fees: $invoice_data->h_tax_due";
    echo "<br>";
    echo "total: $invoice_data->h_total_due";
    echo "<br>";
    
  }
}

/* End of file portal.php */
/* Lnocation: ./application/controllers/portal.php */
