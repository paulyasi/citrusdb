<?php

abstract class PrintInvoice
{
    protected $createdby;
    protected $mydate;
    protected $mybilling_id;
    protected $billing_name;
    protected $billing_company;
    protected $billing_street;
    protected $billing_city;
    protected $billing_state;
    protected $billing_zip;
    protected $billing_acctnum;
    protected $billing_fromdate;
    protected $billing_todate;
    protected $billing_payment_due_date;
    protected $billing_notes;       
    protected $billing_new_charges;
    protected $billing_past_due;
    protected $billing_late_fee;
    protected $billing_tax_due;
    protected $billing_total_due;
    protected $billing_credit_applied;
    protected $billing_email;
    protected $billing_po_number;
    protected $org_name;
    protected $org_street;
    protected $org_city;
    protected $org_state;
    protected $org_zip;
    protected $phone_billing;
    protected $email_billing;
    protected $invoice_footer;
    protected $einvoice_footer;

    // initialize line item counters
    protected $myline = 1;
    protected $lineYoffset = 105;
    protected $fillcolor = 200;
    protected $lastserviceid = 0;

    protected $invoicedetails;

    protected $invoiceid;
    protected $pdfobject;
    protected $email;

    abstract function print_invoice();

    function __construct($invoiceid, $pdfobject, $email)
    {
        $this->invoiceid = $invoiceid;
        $this->pdfobject = $pdfobject;
        $this->email = $email;

        $CI =& get_instance();
        $CI->load->model('billing_model');

        // get the invoice data to print on the bill
        $myinvresult = $CI->billing_model->get_invoice_data($invoiceid);
        $this->invoicedetails = $CI->billing_model->invoice_details($invoiceid);

        $this->createdby = $myinvresult['h_created_by'];
        $this->mydate = $myinvresult['h_billing_date'];
        $this->mybilling_id = $myinvresult['b_id'];
        $this->billing_name = $myinvresult['b_name'];
        $this->billing_company = $myinvresult['b_company'];
        $this->billing_street =  $myinvresult['b_street'];
        $this->billing_city = $myinvresult['b_city'];
        $this->billing_state = $myinvresult['b_state'];
        $this->billing_zip = $myinvresult['b_zip'];
        $this->billing_acctnum = $myinvresult['b_acctnum'];
        $this->billing_fromdate = $myinvresult['h_from_date'];
        $this->billing_todate = $myinvresult['h_to_date'];
        $this->billing_payment_due_date = $myinvresult['h_payment_due_date'];
        $this->billing_notes = $myinvresult['h_notes'];       
        $this->billing_new_charges = sprintf("%.2f",$myinvresult['h_new_charges']);
        $this->billing_past_due = sprintf("%.2f",$myinvresult['h_past_due']);
        $this->billing_late_fee = sprintf("%.2f",$myinvresult['h_late_fee']);
        $this->billing_tax_due = sprintf("%.2f",$myinvresult['h_tax_due']);
        $this->billing_total_due = sprintf("%.2f",$myinvresult['h_total_due']);
        $this->billing_credit_applied = sprintf("%.2f",$myinvresult['h_credit_applied']);
        $this->billing_email = $myinvresult['b_contact_email'];
        $this->billing_po_number = $myinvresult['b_po_number'];

        $myorgresult = $CI->billing_model->get_organization_data($this->mybilling_id);
        $this->org_name = $myorgresult['org_name'];
        $this->org_street = $myorgresult['org_street'];
        $this->org_city = $myorgresult['org_city'];
        $this->org_state = $myorgresult['org_state'];
        $this->org_zip = $myorgresult['org_zip'];
        $this->phone_billing = $myorgresult['phone_billing'];
        $this->email_billing = $myorgresult['email_billing'];
        $this->invoice_footer = $myorgresult['invoice_footer'];
        $this->einvoice_footer = $myorgresult['einvoice_footer'];

        // convert dates to human readable form using my date helper
        $CI->load->helper('htmlascii');
        $CI->load->helper('date');
        $this->billing_mydate = humandate($this->mydate);
        $this->billing_fromdate = humandate($this->billing_fromdate);
        $this->billing_todate = humandate($this->billing_todate);
        $this->billing_payment_due_date = humandate($this->billing_payment_due_date);

    }
}


class PdfInvoice extends PrintInvoice
{
    public function print_invoice()
    {
        $CI =& get_instance();
        $CI->load->library('fpdf');    
        $pdf = $this->pdfobject;

        // convert html character codes to ascii for pdf
        $this->billing_name = html_to_ascii($this->billing_name);
        $this->billing_company = html_to_ascii($this->billing_company);
        $this->billing_street = html_to_ascii($this->billing_street);
        $this->billing_city = html_to_ascii($this->billing_city);
        $this->org_name = html_to_ascii($this->org_name);
        $this->org_street = html_to_ascii($this->org_street);
        $this->org_city = html_to_ascii($this->org_city);

        //$pdf=new FPDF();
        $pdf->AddPage();

        // get the page the current invoice in the batch starts on
        // necessary for batches with multiple invoices
        $invoicestartpage = $pdf->PageNo();

        $pdf->SetFont('Arial','B',18);
        $pdf->Cell(60,10,"$this->org_name",0);    
        $pdf->SetXY(10,20);
        $pdf->SetFont('Arial','',9);    
        $pdf->MultiCell(80,4,"$this->org_street\n$this->org_city, $this->org_state $this->org_zip\n$this->phone_billing\n$this->email_billing",0);
        $pdf->Rect(135,10,1,36,"F");

        $pdf->SetXY(140,10);
        $pdf->SetFontSize(10);
        $pdf->MultiCell(70,6,"$this->billing_mydate\n".lang('accountnumber').": $this->billing_acctnum\n".lang('invoicenumber').": $this->invoiceid\n$this->billing_fromdate ".lang('to')." $this->billing_todate\n".lang('paymentdue').": $this->billing_payment_due_date\n".lang('total').": $this->billing_total_due",0);
        $pdf->SetXY(10,60);
        $pdf->SetFontSize(10);

        if ($this->billing_po_number) 
        {
            // only print the po number if they have one
            $pdf->MultiCell(60,5,"$this->billing_name\n$this->billing_company\n$this->billing_street\n$this->billing_city $this->billing_state $this->billing_zip\n".lang('po_number').": $this->billing_po_number",0);
        } 
        else 
        {
            $pdf->MultiCell(60,5,"$this->billing_name\n$this->billing_company\n$this->billing_street\n$this->billing_city $this->billing_state $this->billing_zip\n",0);
        }

        $pdf->SetXY(130,60);

        $pdf->Line(5,102,200,102);
        $pdf->SetXY(10,103);
        $pdf->Cell(100,5,lang('description'));
        $pdf->SetXY(160,103);
        $pdf->Cell(50,5,lang('amount'));

        foreach($this->invoicedetails AS $myresult) 
        {
            // check if it's a tax with a tax id or service with
            // no tax idfirst to set detail items
            $serviceid = $myresult['u_id'];
            $taxid = $myresult['tr_id'];
            if ($taxid == NULL) 
            {
                // it's a service
                // select the options_table to get data for the details
                $options_table = $myresult['m_options_table'];
                $id = $myresult['u_id'];
                if ($options_table <> '') 
                {
                    // get the data from the options table and put into variables
                    $myoptions = $CI->service_model->options_attributes($id, $options_table);
                    //echo "$myoptions->username";
                    if (count($myoptions) >= 3) {
                        $optiondetails = $myoptions[2];
                    } else {
                        $optiondetails = '';
                    }
                } 
                else 
                {
                    $optiondetails = '';        
                }
                $service_description = $myresult['m_service_description'];
                $tax_description = '';
            } else {
                // it's a tax
                $tax_description = "     ".$myresult['tr_description'];
                $service_description = '';
                $optiondetails = '';
            }

            $billed_amount = sprintf("%.2f",$myresult['d_billed_amount']);

            // calculate the month multiple, only print for services, not taxes
            $pricerate = $myresult['pricerate'];
            if (($pricerate > 0) AND ($taxid == NULL)) 
            {
                $monthmultiple = sprintf("%.2f", $billed_amount/$pricerate);
            } 
            else 
            {
                $monthmultiple = 1;
            }

            // printing pdf invoice

            // alternate fill color
            if ($serviceid <> $this->lastserviceid) 
            {
                $lastserviceid = $serviceid;
                if ($this->fillcolor == 200) 
                {
                    $this->fillcolor = 255;
                    $pdf->SetFillColor($this->fillcolor);
                } 
                else 
                {
                    $this->fillcolor = 200;
                    $pdf->SetFillColor($this->fillcolor);
                }
            }

            $service_description = html_to_ascii($service_description);
            $tax_description = html_to_ascii($tax_description);
            $optiondetails = html_to_ascii($optiondetails);
            $lineY = $this->lineYoffset + ($this->myline*5);
            $pdf->SetXY(10,$lineY);

            if ($monthmultiple <> 1) 
            {
                $pdf->Cell(151,5,"$serviceid $service_description $tax_description ($monthmultiple @ $pricerate) $optiondetails", 0, 0, "L", TRUE);
            } 
            else 
            {
                $pdf->Cell(151,5,"$serviceid $service_description $tax_description $optiondetails", 0, 0, "L", TRUE);
            }

            //$pdf->SetXY(110,$lineY);
            //$pdf->Cell(110,5,"$optiondetails");
            $pdf->SetXY(160,$lineY);
            $pdf->Cell(40,5,"$billed_amount", 0, 0, "L", TRUE);

            $this->myline++;            

            // add a new page if there are many line items
            // TODO: check for page number here
            // if page number greater than 1, then myline would be larger
            // set an invoicestartpage at the start of each invoice for multi invoice batches
            $pagenumber = $pdf->PageNo();

            if ($pagenumber - $invoicestartpage > 0) 
            {
                $linetotal = 44;
            } 
            else 
            {
                $linetotal = 27;
            }

            if ($this->myline > $linetotal) 
            {
                $pdf->AddPage();
                $pdf->SetXY(10,20);
                $this->myline = 1;
                $this->lineYoffset = 20;
            }
        }

        $lineY = $this->lineYoffset + ($this->myline*5);
        $pdf->Line(5,$lineY,200,$lineY);

        // print the notes and totals at the bottom of the invoice
        if ($this->email == TRUE)
        {
            // set the invoice footer to use the one for email invoices
            $this->invoice_footer = $this->einvoice_footer;
        }

        // fix html characters
        $this->billing_notes = html_to_ascii($this->billing_notes);
        $this->invoice_footer = html_to_ascii($this->invoice_footer);

        $lineY = $lineY + 10;
        $pdf->SetXY(10,$lineY);
        $pdf->MultiCell(100,5,"$this->billing_notes");
        $pdf->SetXY(135,$lineY);
        $pdf->MultiCell(100,5,lang('credit').": $this->billing_credit_applied\n".lang('newcharges').": $this->billing_new_charges\n".lang('pastdue').": $this->billing_past_due\n".lang('tax').": $this->billing_tax_due\n");
        $pdf->SetXY(135,$lineY+20);
        $pdf->SetFont('Arial','BU',10);
        $pdf->Cell(100,5,lang('total').": $this->billing_total_due");
        $lineY = $lineY + 10;
        $pdf->SetFont('Arial','',9);
        $pdf->SetXY(10,$lineY);
        $pdf->MultiCell(110,4,"$this->invoice_footer");

        return $pdf;
    }
}


class TextInvoice extends PrintInvoice
{
    public function print_invoice()
    {
        $CI =& get_instance();

        $output = "$this->billing_mydate\n".lang('accountnumber').": $this->billing_acctnum\n\n";
        $output .= lang('invoicenumber').": $this->invoiceid\n";
        $output .= "$this->billing_fromdate - $this->billing_todate \n";
        $output .= lang('paymentduedate').": $this->billing_payment_due_date\n";
        $output .= "\n\n";

        $output .= lang('to').": $this->billing_email\n";
        $output .= "$this->billing_name $this->billing_company\n";
        $output .= "$this->billing_street ";
        $output .= "$this->billing_city $this->billing_state ";

        $output .= "$this->billing_zip\n";

        if ($this->billing_po_number) 
        {
            // only print the po number if they have one
            $output .= lang('po_number').": $this->billing_po_number\n";
        } 
        else 
        {
            $output .= "\n";
        }

        $output .= "----------------------------------------";
        $output .= "----------------------------------------\n";

        foreach($this->invoicedetails AS $myresult) 
        {
            // check if it's a tax with a tax id or service with
            // no tax idfirst to set detail items
            $serviceid = $myresult['u_id'];
            $taxid = $myresult['tr_id'];
            if ($taxid == NULL) 
            {
                // it's a service
                // select the options_table to get data for the details
                $options_table = $myresult['m_options_table'];
                $id = $myresult['u_id'];
                if ($options_table <> '') 
                {
                    // get the data from the options table and put into variables
                    $myoptions = $CI->service_model->options_attributes($id, $options_table);
                    //echo "$myoptions->username";
                    if (count($myoptions) >= 3) {
                        $optiondetails = $myoptions[2];
                    } else {
                        $optiondetails = '';
                    }
                } 
                else 
                {
                    $optiondetails = '';        
                }
                $service_description = $myresult['m_service_description'];
                $tax_description = '';
            } else {
                // it's a tax
                $tax_description = "     ".$myresult['tr_description'];
                $service_description = '';
                $optiondetails = '';
            }

            $billed_amount = sprintf("%.2f",$myresult['d_billed_amount']);

            // calculate the month multiple, only print for services, not taxes
            $pricerate = $myresult['pricerate'];
            if (($pricerate > 0) AND ($taxid == NULL)) 
            {
                $monthmultiple = sprintf("%.2f", $billed_amount/$pricerate);
            } 
            else 
            {
                $monthmultiple = 1;
            }

            if ($monthmultiple <> 1) 
            {
                $output .= "$serviceid \t $service_description $tax_description ($monthmultiple @ $pricerate) \t $optiondetails \t $billed_amount\n";
            } 
            else 
            {
                $output .= "$serviceid \t $service_description $tax_description \t $optiondetails \t $billed_amount\n";
            }

            $this->myline++;            

        }

        $output .= "----------------------------------------";
        $output .= "----------------------------------------\n";


        // print the notes and totals at the bottom of the invoice
        if ($this->email == TRUE)
        {
            // set the invoice footer to use the one for email invoices
            $this->invoice_footer = $this->einvoice_footer;
        }

        $output .= "$this->billing_notes\n";
        $output .= lang('credit').": $this->billing_credit_applied\n";
        $output .= lang('newcharges').": $this->billing_new_charges\n";
        $output .= lang('pastdue').": $this->billing_past_due\n";
        $output .= lang('tax').": $this->billing_tax_due\n";
        $output .= lang('total').": $this->billing_total_due\n";

        $output .= "\n$this->invoice_footer\n";

        return $output;
    }
}

class ExtendedPdfInvoice extends PrintInvoice
{
    public function print_invoice()
    {
        $this->load->library('fpdf');    
        $pdf = $pdfobject;
    }
}

class ExtendedTextInvoice extends PrintInvoice
{
    public function print_invoice()
    {
    }

}
?>
