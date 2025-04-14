<?php

defined('BASEPATH') or exit('No direct script access allowed');

class LWTestNewController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function index() {
        
    }

    public function apitesting() {
        $return_array = array();
        $this->load->helper('commonfun');
        $api_type = $_GET['apitype'];
        $lead_id = $_GET['leadid'];
        if ($api_type == 1) {
            $this->load->helper('integration/payday_quick_call_api');
            $return_array = payday_quickcall_api_call("LEAD_PUSH", $lead_id);
        } else if ($api_type == 5) {

            $this->load->helper('integration/payday_disbursement_api');

            $request_array = array();
            $request_array['bank_id'] = 1;
            $request_array['payment_mode_id'] = 1;
            $request_array['payment_type_id'] = 1;
            $request_array['bank_active'] = 1;

            $return_array = payday_loan_disbursement_call($lead_id, $request_array);
        } else if ($api_type == 6) {
            $this->load->helper('integration/payday_disbursement_api');
            $request_array = array();
            $request_array["trans_type"] = $_GET['trans_type'];

            $return_array = payday_loan_disbursement_api_call("DisburseLoanStatus", $lead_id, $request_array);
        } else if ($api_type == 7) {
            $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns="http://www.w3.org/1999/xhtml">

                            <head>
                                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                <title>Legal Notice :</title>
                            </head>

                            <body>

                                <table width="763" border="0" align="center" cellpadding="0" cellspacing="0" style="font-family:Arial, Helvetica, sans-serif;">
                                    <tr>
                                        <td>
                                            <table width="763" border="0" align="center" style="border:solid 1px #ddd; padding:10px; font-family:Arial, Helvetica, sans-serif; line-height:22px;">
                                                <tr>
                                                    <td>
                                                        <table width="100%" border="0">
                                                            <tr>
                                                                <td colspan="2" align="center"><img src="https://loanwalle.com/public/emailimages/preach-law/image/preach-law-logo.png" alt="preach-law-logo" width="237" height="128" /></td>
                                                            </tr>
                                                            <tr>
                                                                <td width="52%" align="left"><strong>Ref: Notice/Naman/2022/01</strong></td>
                                                                <td width="48%" align="right"><strong>Date: </strong></td>
                                                            </tr>
                                                            <tr>
                                                                <td align="left">&nbsp;</td>
                                                                <td align="right">
                                                                    <!-- <p align="right"><strong>Delhi, India</strong></p> -->
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2" align="right">&nbsp;</td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">To,</td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <p style="margin: 2px 0px;">Mr/Mrs : <span style="text-decoration:underline;"></span></p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <p style="margin: 2px 0px;">Loan I’D : <span style="text-decoration:underline;"></span></p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>&nbsp;</td>
                                                                <td>&nbsp;</td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <p style="margin: 2px 0px;">Subject: Reminder notice for loan amount recovery.</p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <p style="margin: 2px 0px;"><strong>My Client:</strong> <strong></strong></p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <p style="margin: 2px 0px;">M/S Naman Finlease Pvt. Ltd., operating under the brand name of Loanwalle.com.</p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">&nbsp;</td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <p style="margin: 2px 0px;">To whomsoever it may concern,</p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <p style="margin: 2px 0px;">On instructions and on behalf of my above-named client i.e., M/S Naman Finlease Pvt Ltd., operating under the brand name of &ldquo;Loanwalle.com&rdquo;, having it&rsquo;s registered head office at S-370, LGF, Panchsheel Park, New Delhi- 110017, I hereby serve upon you the following notice:-</p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <p style="margin: 2px 0px;">You had approached my client for a short-term loan on .</p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <p style="margin: 2px 0px;">Your repayment amount including the interest and other dues as on  is <img src="https://loanwalle.com/public/emailimages/preach-law/image/inr.png" alt="inr" width="13" height="13" /> , the particulars of which arementioned below:</p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">&nbsp;</td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <table width="100%" border="0" cellpadding="5" cellspacing="1" bgcolor="#ccc">
                                                                        <tr>
                                                                            <td width="197" valign="top" bgcolor="#FFFFFF">
                                                                                <p style="margin: 2px 0px;"><strong>PARTICULARS</strong></p>
                                                                            </td>
                                                                            <td width="188" valign="top" bgcolor="#FFFFFF">
                                                                                <p style="margin: 2px 0px;"><strong>AMOUNT/DAYS</strong></p>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td width="197" valign="top" bgcolor="#FFFFFF">
                                                                                <p style="margin: 2px 0px;">Principal Loan</p>
                                                                            </td>
                                                                            <td width="188" valign="top" bgcolor="#FFFFFF">
                                                                                <p style="margin: 2px 0px;"><img src="https://loanwalle.com/public/emailimages/preach-law/image/inr.png" alt="inr" width="13" height="13" /></p>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td width="197" valign="top" bgcolor="#FFFFFF">
                                                                                <p style="margin: 2px 0px;">Interest</p>
                                                                            </td>
                                                                            <td width="188" valign="top" bgcolor="#FFFFFF">
                                                                                <p style="margin: 2px 0px;"><img src="https://loanwalle.com/public/emailimages/preach-law/image/inr.png" alt="inr" width="13" height="13" /></p>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td width="197" valign="top" bgcolor="#FFFFFF">
                                                                                <p style="margin: 2px 0px;">Delay in Repayment</p>
                                                                            </td>
                                                                            <td width="188" valign="top" bgcolor="#FFFFFF">
                                                                                <p style="margin: 2px 0px;"></p>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td width="197" valign="top" bgcolor="#FFFFFF">
                                                                                <p style="margin: 2px 0px;">Late Penalty Interest</p>
                                                                            </td>
                                                                            <td width="188" valign="top" bgcolor="#FFFFFF">
                                                                                <p style="margin: 2px 0px;"><img src="https://loanwalle.com/public/emailimages/preach-law/image/inr.png" alt="inr" width="13" height="13" /></p>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td width="197" valign="top" bgcolor="#FFFFFF">
                                                                                <p style="margin: 2px 0px;">Total Due</p>
                                                                            </td>
                                                                            <td width="188" valign="top" bgcolor="#FFFFFF">
                                                                                <p style="margin: 2px 0px;"><img src="https://loanwalle.com/public/emailimages/preach-law/image/inr.png" alt="inr" width="13" height="13" /></p>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td width="197" valign="top" bgcolor="#FFFFFF">
                                                                                <p style="margin: 2px 0px;">Payment Received</p>
                                                                            </td>
                                                                            <td width="188" valign="top" bgcolor="#FFFFFF">
                                                                                <p style="margin: 2px 0px;"><img src="https://loanwalle.com/public/emailimages/preach-law/image/inr.png" alt="inr" width="13" height="13" /></p>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td width="197" valign="top" bgcolor="#FFFFFF">
                                                                                <p style="margin: 2px 0px;">Final Total</p>
                                                                            </td>
                                                                            <td width="188" valign="top" bgcolor="#FFFFFF">
                                                                                <p style="margin: 2px 0px;"><img src="https://loanwalle.com/public/emailimages/preach-law/image/inr.png" alt="inr" width="13" height="13" /></p>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">&nbsp;</td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <p style="margin: 2px 0px;">As on , the total amount due and payable by you to my client is <img src="https://loanwalle.com/public/emailimages/preach-law/image/inr.png" alt="inr" width="13" height="13" /></p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <p style="margin: 2px 0px;">You are hereby called upon to pay all the updated dues immediately in the absence of which my client will be compelled to initiate legal proceedings against you as per the law.</p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <p style="margin: 2px 0px;">You are also advised to take note of the fact that any further delay in repayment will be duly updated by my client with all the credit bureaus which will severe disparagement to your further borrowing capacity from any bank or financial institution.</p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">Copy of this notice has been retained in my office for further course of actions and recourse.
                                                                    </p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">&nbsp;</td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <p align="right"><strong>Yours truly,</strong></p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2" align="right">
                                                                    <p align="right"><strong>Krishna Kumar Mishra</strong><br />
                                                                        (Advocate&amp; Attorney)<br />
                                                                        PREACH LAW LLP</p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2" align="center">
                                                                    <p align="center"><em>Note: This is system generated demand notice, hence rubber stamp and signature are not required</em></p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2">&nbsp;</td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2" align="center" style="border-top:solid 2px #000;">
                                                                    <p align="center">PREACH LAW LLP<br />
                                                                        Office: E-111-B, Nawada Housing Complex, Uttam Nagar, Delhi – 110059<br />
                                                                        <a href="mailto:admin@preachlaw.com" style="color:#000; text-decoration:none !important;">admin@preachlaw.com</a> | T: 01146574455 | M:+91 9311664455 | <a href="http://www.preachlaw.com/" target="_blank" style="color:#000; text-decoration:none !important;">www.preachlaw.com</a>
                                                                    </p>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p style=" line-height:22px; margin-bottom:5px;"><strong>Regards</strong><br />
                                                <strong style="color:#339;">Krishna Kumar Mishra, Founder Partner</strong><br />
                                                <span style="color:#339;">(Advocate, Consultant &amp; IPR Attorney)</span>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><img src="https://loanwalle.com/public/emailimages/law-firm/image/line.jpg" alt="line" style="margin-bottom:10px;" /></td>
                                    </tr>
                                    <tr>
                                        <td style="font-style:italic; line-height:25px;"><strong>PREACH LAW LLP</strong><br />
                                            Reg. Office: B-34, S/F, Arjun Park, New Delhi – 110043<br />
                                            E: <a href="mailto:admin@preachlaw.com" style="color:#000; text-decoration:underline;">admin@preachlaw.com</a> | W: <a href="www.preachlaw.com" target="_blank" style="color:#000; text-decoration:underline;">www.preachlaw.com</a><br />
                                            T: 011-46574455 | M: +91 9311664455 | 9311465113 </td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                    </tr>
                                </table>
                            </body>

                        </html>';

            $return_array = lw_send_email("shubham.agrawal@loanwalle.com", "LEGAL NOTICE", $message); //"","tech.team@loanwalle.com"
        } else if ($api_type == 8) {
            require_once(COMPONENT_PATH . 'CommonComponent.php');

            $CommonComponent = new CommonComponent();

            $return_array = $CommonComponent->call_pan_ocr_api($lead_id);
        } else if ($api_type == 9) {
            $this->load->helper('integration/payday_runo_call_api_helper');
            $return_array = payday_call_management_api_call("PRECOLLX_CAT_SANCTION", $lead_id, array('mobile' => 9560807913));
        } else {
            die("Invalid Request");
        }


        echo "<pre>";
        print_r($return_array);
    }

    public function report() {
        $this->load->model('Report_Model');
        $result = $this->Report_Model->PaymentAnalysis(1, '01-04-2022');
        echo "<pre>";
        print_r($result);
        exit;
    }

    public function send_letter() {
        $lead_id = $_GET['lead_id'];
        $template = $_GET['templete'];
        $this->load->model('Task_Model');
        $result = $this->Task_Model->$template($lead_id);
        print_r("Sent.......'.$result.'");
    }

    public function test() {
        $lead_id = $_GET['lead_id'];
        $customer_email = 'alam@bharatloan.com';
        $customer_name = 'Alam';

        $this->load->model('Task_Model');
        $enc = $this->Task_Model->sent_loan_closed_noc_letter($lead_id);
//            $enc = $this->Task_Model->sendSanctionMail($lead_id);
//            $enc = $this->Task_Model->preApprovedOfferEmailer($customer_email, $customer_name, $lead_id = 0, 2);
        print_r($enc);
    }

    public function encry_test() {
        $encode = $this->encrypt->encode('73458');
        $decode = $this->encrypt->decode('XBcFKAEpAzEEMFNuBmUHc1Y7UnNUJw9BAztTYAE6By4=');
        print_r('LMS enc : ' . $encode);
        echo '<br>';
        print_r($decode);
    }

    public function send_pre_approved_mail() {

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $this->load->model('Task_Model', 'Task');

//            $this->Task->preApprovedOfferEmailer('alam.ansari@bharatloan.com', 'Alam', 4005, 2);
//            $this->Task->send_Customer_Feedback_Emailer(4005, 'alam.ansari@bharatloan.com', 'Alam');
//           $res = $this->Task->sent_ekyc_request_email(4005);

        $res = lw_send_email('alam.ansari@bharatloan.com', 'Test', 'Testing');
        print_r($res);
        exit;
    }

    

    public function fb() {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

//            $form_id = $_GET['form_id'];
//            $min = $_GET['min'];

        $this->load->helper('/integration/payday_fb_call_api');
//            $res = payday_fb_campaign_api_call('GET_FORM_DATA', $form_id, $min);
        $res = get_fb_page_forms_api('GET_PAGE_FORM');
        echo '<pre>';
        print_r($res);
    }
public function send_sms() {

//        ini_set('display_errors', 1);
//        ini_set('display_startup_errors', 1);
//        error_reporting(E_ALL);

        $req = array();
        $sms_type_id = 1;
        $lead_id=$_GET['lead_id'];
        $req = array();
        $sql='select LD.lead_id,LC.mobile,LC.first_name as name from leads LD inner join lead_customer LC on (LC.customer_lead_id=LD.lead_id) where LD.lead_id='.$lead_id;
        $result=$this->db->query($sql);
        if($result->num_rows() > 0){
            $result=$result->result_array();
        }
        foreach($result as $row){
            $req['lead_id']=$row['lead_id'];
            $req['mobile']=$row['mobile'];
            $req['name']=$row['name'];
            $req['otp']=rand(1000,9999);
        }
        $req['mobile'] = 8750256406;
        require_once (COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->payday_sms_api($sms_type_id, $req['lead_id'], $req);

        print_r($res);
        
    }
    public function lead_thankyou_sms() {
        $sms_type_id = 2;
        $lead_id=$_GET['lead_id'];
        $req = array();
        $sql='select LD.lead_id,LD.lead_reference_no as reference_no,LC.mobile,LC.first_name as name from leads LD inner join lead_customer LC on (LC.customer_lead_id=LD.lead_id) where LD.lead_id='.$lead_id;
        $result=$this->db->query($sql);
        if($result->num_rows() > 0){
            $result=$result->result_array();
        }
        foreach($result as $row){
            $req['lead_id']=$row['lead_id'];
            $req['refrence_no']=$row['reference_no'];
            $req['mobile']=$row['mobile'];
            $req['name']=$row['name'];
        }
        $req['mobile'] = 8750256406;
        require_once (COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->payday_sms_api($sms_type_id, $req['lead_id'], $req);

        print_r($res);
    }

    public function connect_executive_sms() {

        $sms_type_id = 3;
        $lead_id = $_GET['lead_id'];

        $req = array();
        $sql='select LD.lead_id,LC.first_name as name,LC.mobile, U.name as executive_name,U.mobile as executive_mobile from leads LD inner join lead_customer LC on (LC.customer_lead_id=LD.lead_id)';
        $sql.=' inner join users U on (U.user_id=LD.lead_screener_assign_user_id) where LD.lead_id='.$lead_id;

$result=$this->db->query($sql);
        if($result->num_rows() > 0){
            $result=$result->result_array();
        }
        foreach($result as $row){
            $req['lead_id']=$row['lead_id'];
            $req['executive_name']=$row['executive_name'];
            $req['executive_mobile']=$row['executive_mobile'];
            $req['mobile']=$row['mobile'];
            $req['name']=$row['name'];
        }
        $req['mobile'] = 8750256406;
        require_once (COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->payday_sms_api($sms_type_id, $req['lead_id'], $req);

        print_r($res);
    }

public function lead_rejection_sms(){
    $sms_type_id=4;
    $lead_id = $_GET['lead_id'];

        $req = array();
        $sql='select LD.lead_id,LC.mobile from leads LD inner join lead_customer LC on (LC.customer_lead_id=LD.lead_id) where LD.lead_id='.$lead_id;

$result=$this->db->query($sql);
        if($result->num_rows() > 0){
            $result=$result->result_array();
        }
        foreach($result as $row){
            $req['lead_id']=$row['lead_id'];
            $req['mobile']=$row['mobile'];
        }
        $req['mobile'] = 8750256406;
        require_once (COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->payday_sms_api($sms_type_id, $req['lead_id'], $req);

        print_r($res);
}
public function loan_disbursed_sms(){
    $sms_type_id=5;
    $lead_id = $_GET['lead_id'];

        $req = array();
        $sql='select LD.lead_id,LC.first_name as name,LC.mobile, L.loan_no, L.recommended_amount, CB.account, CAM.repayment_amount, CAM.repayment_date from leads LD inner join lead_customer LC on (LC.customer_lead_id=LD.lead_id)';
        $sql.=' inner join loan L on (L.lead_id=LD.lead_id) inner join credit_analysis_memo CAM on (CAM.lead_id=LD.lead_id) inner join customer_banking CB on (CB.lead_id=LD.lead_id) where LD.lead_id='.$lead_id;

$result=$this->db->query($sql);
        if($result->num_rows() > 0){
            $result=$result->result_array();
        }
        foreach($result as $row){
            $req['lead_id']=$row['lead_id'];
            $req['loan_no']=$row['loan_no'];
            $req['loan_amount']=$row['recommended_amount'];
            $req['cust_bank_account_no']=$row['account'];
            $req['repayment_amount']=$row['repayment_amount'];
            $req['repayment_date']=$row['repayment_date'];
            $req['mobile']=$row['mobile'];
            $req['name']=$row['name'];
        }
        $req['mobile'] = 8750256406;
        require_once (COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->payday_sms_api($sms_type_id, $req['lead_id'], $req);

        print_r($res);
}
public function loan_repayment_reminder_sms(){
 $sms_type_id=6;
    $lead_id = $_GET['lead_id'];

        $req = array();
        $sql='select LD.lead_id,LC.mobile, L.loan_no, CAM.repayment_date from leads LD inner join lead_customer LC on (LC.customer_lead_id=LD.lead_id)';
        $sql.=' inner join loan L on (L.lead_id=LD.lead_id) inner join credit_analysis_memo CAM on (CAM.lead_id=LD.lead_id) where LD.lead_id='.$lead_id;

$result=$this->db->query($sql);
        if($result->num_rows() > 0){
            $result=$result->result_array();
        }
        foreach($result as $row){
            $req['lead_id']=$row['lead_id'];
            $req['loan_no']=$row['loan_no'];
            $req['repayment_date']=$row['repayment_date'];
            $req['mobile']=$row['mobile'];
        }
        $req['mobile'] = 8750256406;
        require_once (COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->payday_sms_api($sms_type_id, $req['lead_id'], $req);

        print_r($res);   
}
public function lead_apply_contact_sms(){
    $sms_type_id = 7;
        $lead_id=$_GET['lead_id'];
        $req = array();
        $sql='select LD.lead_id,LC.mobile,LC.first_name as name from leads LD inner join lead_customer LC on (LC.customer_lead_id=LD.lead_id) where LD.lead_id='.$lead_id;
        $result=$this->db->query($sql);
        if($result->num_rows() > 0){
            $result=$result->result_array();
        }
        foreach($result as $row){
            $req['lead_id']=$row['lead_id'];
            $req['mobile']=$row['mobile'];
            $req['name']=$row['name'];
        }
        $req['mobile'] = 8750256406;
        require_once (COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->payday_sms_api($sms_type_id, $req['lead_id'], $req);

        print_r($res);
}
    public function db_test() {
        $this->load->model('DB_Connection_Model');

        $res = $this->DB_Connection_Model->get_db_data();

        print_r($res);
        exit;
    }

}
