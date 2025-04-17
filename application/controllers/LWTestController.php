<?php

defined('BASEPATH') or exit('No direct script access allowed');

class LWTestController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('email');
    }

    public function index() {
    }

    public function testpdf() {
        error_reporting(E_ALL);
        ini_set("display_errors", 1);
        $this->load->model('Task_Model', 'Tasks');
        $return_array = $this->Tasks->gererateSanctionLetternew($_GET['lead_id']);
        print_r($return_array);
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

                                <meta http-equiv="
                            <head>Content-Type" content="text/html; charset=utf-8" />
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
                                                                    <p style="margin: 2px 0px;">M/S Aman Fincap Limited, operating under the brand name of Loanwalle.com.</p>
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

            $return_array = lw_send_email("info@tejasloan.com", "LEGAL NOTICE", $message); //"","tech.team@loanwalle.com"
        } else if ($api_type == 8) {
            require_once(COMPONENT_PATH . 'CommonComponent.php');

            $CommonComponent = new CommonComponent();

            $return_array = $CommonComponent->call_pan_ocr_api($lead_id);
        } else if ($api_type == 9) {
            $this->load->helper('integration/payday_runo_call_api_helper');
            $return_array = payday_call_management_api_call("PRECOLLX_CAT_SANCTION", $lead_id, array('mobile' => 9560807913));
        } else if ($api_type == 10) {
            require_once(COMPONENT_PATH . 'CommonComponent.php');

            $CommonComponent = new CommonComponent();

            $return_array = $CommonComponent->run_eligibility($lead_id);
        } elseif ($api_type == 10) {
            echo 'API Called';

            require_once(COMPONENT_PATH . 'CommonComponent.php');

            $CommonComponent = new CommonComponent();
            $request_array = array();
            $request_array['event_type_id'] = $_GET['event_type_id'];
            $return_array = $CommonComponent->payday_appsflyer_campaign_api_call("EVENT_PUSH_CALL", $lead_id, $request_array);
            print_r($return_array);
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
        $customer_email = 'info@tejasloan.com';
        $customer_name = 'Ajay';

        $this->load->model('Task_Model');
        //$enc = $this->Task_Model->sent_loan_closed_noc_letter($lead_id);
        // $enc = $this->Task_Model->sendSanctionMail($lead_id);
        //$enc = $this->Task_Model->preApprovedOfferEmailer($customer_email, $customer_name, $lead_id = 16, 2);
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

        $res = lw_send_email('info@tejasloan.com', 'Test', 'Testing');
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
        $sms_type_id = $_GET['sms_type_id'];
        $lead_id = $_GET['lead_id'];
        $req = array();
        $sql = 'select LD.lead_id,LC.mobile,LC.first_name as name from leads LD inner join lead_customer LC on (LC.customer_lead_id=LD.lead_id) where LD.lead_id=' . $lead_id;
        $result = $this->db->query($sql);
        if ($result->num_rows() > 0) {
            $result = $result->result_array();
        }
        foreach ($result as $row) {
            $req['lead_id'] = $row['lead_id'];
            $req['mobile'] = $row['mobile'];
            $req['name'] = $row['name'];
            $req['otp'] = rand(1000, 9999);
        }
        $req['mobile'] = 8750256406;
        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->payday_sms_api($sms_type_id, $req['lead_id'], $req);

        print_r($res);
    }

    public function lead_thankyou_sms() {
        $sms_type_id = 2;
        $lead_id = $_GET['lead_id'];
        $req = array();
        $sql = 'select LD.lead_id,LD.lead_reference_no as reference_no,LC.mobile,LC.first_name as name from leads LD inner join lead_customer LC on (LC.customer_lead_id=LD.lead_id) where LD.lead_id=' . $lead_id;
        $result = $this->db->query($sql);
        if ($result->num_rows() > 0) {
            $result = $result->result_array();
        }
        foreach ($result as $row) {
            $req['lead_id'] = $row['lead_id'];
            $req['refrence_no'] = $row['reference_no'];
            $req['mobile'] = $row['mobile'];
            $req['name'] = $row['name'];
        }
        $req['mobile'] = 8750256406;
        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->payday_sms_api($sms_type_id, $req['lead_id'], $req);

        print_r($res);
    }

    public function connect_executive_sms() {

        $sms_type_id = 3;
        $lead_id = $_GET['lead_id'];

        $req = array();
        $sql = 'select LD.lead_id,LC.first_name as name,LC.mobile, U.name as executive_name,U.mobile as executive_mobile from leads LD inner join lead_customer LC on (LC.customer_lead_id=LD.lead_id)';
        $sql .= ' inner join users U on (U.user_id=LD.lead_screener_assign_user_id) where LD.lead_id=' . $lead_id;

        $result = $this->db->query($sql);
        if ($result->num_rows() > 0) {
            $result = $result->result_array();
        }
        foreach ($result as $row) {
            $req['lead_id'] = $row['lead_id'];
            $req['executive_name'] = $row['executive_name'];
            $req['executive_mobile'] = $row['executive_mobile'];
            $req['mobile'] = $row['mobile'];
            $req['name'] = $row['name'];
        }
        $req['mobile'] = 8750256406;
        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->payday_sms_api($sms_type_id, $req['lead_id'], $req);

        print_r($res);
    }

    public function lead_rejection_sms() {
        $sms_type_id = 4;
        $lead_id = $_GET['lead_id'];

        $req = array();
        $sql = 'select LD.lead_id,LC.mobile from leads LD inner join lead_customer LC on (LC.customer_lead_id=LD.lead_id) where LD.lead_id=' . $lead_id;

        $result = $this->db->query($sql);
        if ($result->num_rows() > 0) {
            $result = $result->result_array();
        }
        foreach ($result as $row) {
            $req['lead_id'] = $row['lead_id'];
            $req['mobile'] = $row['mobile'];
        }
        $req['mobile'] = 8750256406;
        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->payday_sms_api($sms_type_id, $req['lead_id'], $req);

        print_r($res);
    }

    public function loan_disbursed_sms() {
        $sms_type_id = 5;
        $lead_id = $_GET['lead_id'];

        $req = array();
        $sql = 'select LD.lead_id,LC.first_name as name,LC.mobile, L.loan_no, L.recommended_amount, CB.account, CAM.repayment_amount, CAM.repayment_date from leads LD inner join lead_customer LC on (LC.customer_lead_id=LD.lead_id)';
        $sql .= ' inner join loan L on (L.lead_id=LD.lead_id) inner join credit_analysis_memo CAM on (CAM.lead_id=LD.lead_id) inner join customer_banking CB on (CB.lead_id=LD.lead_id) where LD.lead_id=' . $lead_id;

        $result = $this->db->query($sql);
        if ($result->num_rows() > 0) {
            $result = $result->result_array();
        }
        foreach ($result as $row) {
            $req['lead_id'] = $row['lead_id'];
            $req['loan_no'] = $row['loan_no'];
            $req['loan_amount'] = $row['recommended_amount'];
            $req['cust_bank_account_no'] = $row['account'];
            $req['repayment_amount'] = $row['repayment_amount'];
            $req['repayment_date'] = $row['repayment_date'];
            $req['mobile'] = $row['mobile'];
            $req['name'] = $row['name'];
        }
        $req['mobile'] = 8750256406;
        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->payday_sms_api($sms_type_id, $req['lead_id'], $req);

        print_r($res);
    }

    public function loan_repayment_reminder_sms() {
        $sms_type_id = $_GET['sms_type_id'];
        $lead_id = $_GET['lead_id'];

        $req = array();

        $sql = 'select LD.lead_id, LC.first_name as cust_name,LC.mobile, L.loan_no, CAM.repayment_date, CAM.repayment_amount from leads LD inner join lead_customer LC on (LC.customer_lead_id=LD.lead_id)';
        $sql .= ' inner join loan L on (L.lead_id=LD.lead_id) inner join credit_analysis_memo CAM on (CAM.lead_id=LD.lead_id) where LD.lead_id=' . $lead_id;

        $result = $this->db->query($sql);
        if ($result->num_rows() > 0) {
            $result = $result->result_array();
        }

        foreach ($result as $row) {
            $req['pending_days'] = 2;
            $req['lead_id'] = $row['lead_id'];
            $req['loan_no'] = $row['loan_no'];
            $req['name'] = $row['cust_name'];
            $req['repayment_amount'] = $row['repayment_amount'];
            $req['repayment_date'] = $row['repayment_date'];
            $req['mobile'] = $row['mobile'];
        }
        $req['mobile'] = 8750256406;
        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->payday_sms_api($sms_type_id, $req['lead_id'], $req);

        print_r($res);
    }

    public function lead_apply_contact_sms() {
        $sms_type_id = 7;
        $lead_id = $_GET['lead_id'];
        $req = array();
        $sql = 'select LD.lead_id,LC.mobile,LC.first_name as name from leads LD inner join lead_customer LC on (LC.customer_lead_id=LD.lead_id) where LD.lead_id=' . $lead_id;
        $result = $this->db->query($sql);
        if ($result->num_rows() > 0) {
            $result = $result->result_array();
        }
        foreach ($result as $row) {
            $req['lead_id'] = $row['lead_id'];
            $req['mobile'] = $row['mobile'];
            $req['name'] = $row['name'];
        }
        $req['mobile'] = 8750256406;
        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->payday_sms_api($sms_type_id, $req['lead_id'], $req);

        print_r($res);
    }

    public function finbox() {
        echo 'Api Called';
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->finbox_api_call($_GET['lead_id'], "");

        echo '<pre>';
        print_r($res);
        echo 'Api Ended';
    }

    public function fetch_pan_details() {
        echo 'Api Called';
        //        ini_set('display_errors', 1);
        //        ini_set('display_startup_errors', 1);
        //        error_reporting(E_ALL);

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->call_pan_verification_api($_GET['lead_id']);

        echo '<pre>';
        print_r($res);
        echo 'Api Ended';
    }

    public function finbox_bureauconnect_api() {
        echo 'Api Called';
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->call_finbox_bureauconnect_api($_GET['lead_id'], "");

        echo '<pre>';
        print_r($res);
        echo 'Api Ended';
    }

    public function finbox_bank_connect_api() {
        echo 'Api Called';
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $lead_id = $_GET['lead_id'];
        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->call_finbox_bank_connect_upload_api($lead_id, "");

        if ($res['status'] == 1) {
            $request_array['entity_id'] = $res['data']['entity_id'];
            //echo $request_array['entity_id'];
            $res = $CommonComponent->call_finbox_bank_connect_fetch_api($lead_id, $request_array);
        }
        echo "<pre>";
        print_r($res);
        echo 'Api Ended';
    }

    public function get_sql() {
        $conditions['LD.lead_id'] = 2665;
        $this->load->model('Task_Model', 'Tasks');
        $data = $this->Tasks->getLeadDetails($conditions);
        echo "<pre>";
        print_r(json_encode($data->row()));
    }

    public function send_link() {
        $req = array();
        $sms_type_id = $_GET['sms_type_id'];
        $lead_id = $_GET['lead_id'];

        $req = array();
        $sql = 'select LD.lead_id,LC.mobile,LC.first_name as name from leads LD inner join lead_customer LC on (LC.customer_lead_id=LD.lead_id) where LD.lead_id=' . $lead_id;
        $result = $this->db->query($sql);
        if ($result->num_rows() > 0) {
            $result = $result->result_array();
        }
        foreach ($result as $row) {
            $req['lead_id'] = $row['lead_id'];
            $req['mobile'] = $row['mobile'];
            $req['name'] = $row['name'];
        }

        if ($sms_type_id == 12) {
            $req['esign_link'] = "https://esign.nsdl.com";
        } else if ($sms_type_id == 13) {
            $req['ekyc_link'] = "https://esign.digilocker.com";
        }
        $req['mobile'] = 8750256406;
        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->payday_sms_api($sms_type_id, $req['lead_id'], $req);

        print_r($res);
    }

    public function update_cam() {
        $lead_id = $_GET['lead_id'];
        $nobel_response_data = $this->db->query('SELECT docs.lead_id as docs_lead_id, api_banking_cart_log.cart_log_id as cart_log_id, api_banking_cart_log.cart_return_novel_doc_id as log_nobel_return_id, api_banking_cart_log.cart_response as nobel_response_data
                FROM `docs`
                INNER join api_banking_cart_log ON api_banking_cart_log.cart_lead_id = docs.lead_id
                where docs.lead_id =' . $lead_id . ' AND docs.docs_master_id = 6 AND docs.docs_active = 1 AND docs.docs_deleted = 0 AND api_banking_cart_log.cart_method_id = 2 AND api_banking_cart_log.cart_api_status_id IN (1,2) AND api_banking_cart_log.cart_active = 1 AND api_banking_cart_log.cart_deleted = 0
                ORDER BY api_banking_cart_log.cart_log_id DESC LIMIT 1');

        if ($nobel_response_data->num_rows() > 0) {

            $api_data = $nobel_response_data->row();

            $nobel_response_data_json = stripslashes($api_data->nobel_response_data);

            $response_data_array = json_decode($nobel_response_data_json, true);
            // echo "<pre>";
            // print_r($response_data_array);die;

            if ($response_data_array['status'] == 'Submitted') {
                $responseArray['success_msg'] = "Transaction is processed but Final Summary output is not available for View.";
            } else if ($response_data_array['status'] == 'In Progress') {
                $responseArray['success_msg'] = "Transaction is uploaded and is In process. Not available for view at this stage.";
            } else if ($response_data_array['status'] == 'Deleted') {
                $responseArray['success_msg'] = "Transaction was deleted";
            } else if (in_array(strtolower($response_data_array['status']), ['downloaded', 'processed'])) {
                // echo "<pre>";
                // print_r($response_data_array);die;

                $account_details = $response_data_array['data'][0];
                $cam_details = $account_details['camAnalysisData'];
                $cam_details_monthly_wise = $account_details['camAnalysisData']['camAnalysisMonthly'];
                $cheque_Bounces = $account_details['chequeBounces'];
                $emi = $account_details['emi'];
                $salary = $account_details['salary'];

                $salary = array_reverse($salary);

                $update_monthly_salary_count = 1;

                $iii = 0;

                $cam_salary_data = $this->db->query('SELECT salary_credit1_date, salary_credit1_amount, salary_credit2_date, salary_credit2_amount, salary_credit3_date, salary_credit3_amount
                FROM `credit_analysis_memo` where lead_id=' . $lead_id);

                $cam_salary_data = $cam_salary_data->row();

                $salary_credit1_date = (empty($cam_salary_data->salary_credit1_date) || $cam_salary_data->salary_credit1_date == 0) ? 0 : $cam_salary_data->salary_credit1_date;
                $salary_credit2_date = (empty($cam_salary_data->salary_credit2_date) || $cam_salary_data->salary_credit2_date == 0) ? 0 : $cam_salary_data->salary_credit2_date;
                $salary_credit3_date = (empty($cam_salary_data->salary_credit3_date) || $cam_salary_data->salary_credit3_date == 0) ? 0 : $cam_salary_data->salary_credit3_date;
                $salary_credit1_amount = (empty($cam_salary_data->salary_credit1_amount) || $cam_salary_data->salary_credit1_amount == 0) ? 0 : $cam_salary_data->salary_credit1_amount;
                $salary_credit2_amount = (empty($cam_salary_data->salary_credit2_amount) || $cam_salary_data->salary_credit2_amount == 0) ? 0 : $cam_salary_data->salary_credit2_amount;
                $salary_credit3_amount = (empty($cam_salary_data->salary_credit3_amount) || $cam_salary_data->salary_credit3_amount == 0) ? 0 : $cam_salary_data->salary_credit3_amount;

                foreach ($salary as $salary_months) {
                    foreach ($salary_months as $key => $value) {
                        if ($update_monthly_salary_count <= 3) {
                            if ($key == 'transactions') {
                                foreach ($salary[$iii]['transactions'] as $key_salary_transactions) {
                                    foreach ($key_salary_transactions as $key_day => $key_balance) {
                                        if ($update_monthly_salary_count == 1) {
                                            if ($key_day == 'transactionDate') {
                                                $response_data['salary_credit1_date'] = (date('Y-m-d', substr($key_balance, 0, -3)));
                                            }
                                            if ($key_day == 'amount') {
                                                $response_data['salary_credit1_amount'] = (($key_balance) ? $key_balance : '-');
                                            }
                                        } else if ($update_monthly_salary_count == 2) {
                                            if ($key_day == 'transactionDate') {
                                                $response_data['salary_credit2_date'] = (date('Y-m-d', substr($key_balance, 0, -3)));
                                            }
                                            if ($key_day == 'amount') {
                                                $response_data['salary_credit2_amount'] = (($key_balance) ? $key_balance : '-');
                                            }
                                        } else if ($update_monthly_salary_count == 3) {
                                            if ($key_day == 'transactionDate') {
                                                $response_data['salary_credit3_date'] = (date('Y-m-d', substr($key_balance, 0, -3)));
                                            }
                                            if ($key_day == 'amount') {
                                                $response_data['salary_credit3_amount'] = (($key_balance) ? $key_balance : '-');
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            break;
                        }
                    }
                    $update_monthly_salary_count++;
                    $iii++;
                }

                $update_data = array();
                //}
                if ($salary_credit1_date == 0) {
                    $update_data['salary_credit1_date'] = $response_data['salary_credit1_date'];
                } else {
                    $update_data['salary_credit1_date'] = $salary_credit1_date;
                }
                if ($salary_credit2_date == 0) {
                    $update_data['salary_credit2_date'] = $response_data['salary_credit2_date'];
                } else {
                    $update_data['salary_credit2_date'] = $salary_credit2_date;
                }
                if ($salary_credit3_date == 0) {
                    $update_data['salary_credit3_date'] = $response_data['salary_credit3_date'];
                } else {
                    $update_data['salary_credit3_date'] = $salary_credit3_date;
                }
                if ($salary_credit1_amount == 0) {
                    $update_data['salary_credit1_amount'] = $response_data['salary_credit1_amount'];
                } else {
                    $update_data['salary_credit1_amount'] = $salary_credit1_amount;
                }
                if ($salary_credit2_amount == 0) {
                    $update_data['salary_credit2_amount'] = $response_data['salary_credit2_amount'];
                } else {
                    $update_data['salary_credit2_amount'] = $salary_credit2_amount;
                }
                if ($salary_credit3_amount == 0) {
                    $update_data['salary_credit3_amount'] = $response_data['salary_credit3_amount'];
                } else {
                    $update_data['salary_credit3_amount'] = $salary_credit3_amount;
                }
                if (!empty($update_data)) {
                    $this->db->update("credit_analysis_memo", $update_data, array("lead_id" => $lead_id));
                }

                /*                 * ******************Check Salary Details in CAM End************************************** */
            } else if (in_array(strtolower($response_data_array['status']), ['rejected'])) {
                $responseArray['error_msg'] = $response_data_array['message'];
            } else if (strpos(strtolower($response_data_array['message']), 'fraud') !== false) {
                $responseArray['error_msg'] = $response_data_array['message'];
            }

            // echo "response_data_array : <pre>"; print_r($response_data_array); exit;
        } else {
            $responseArray['error_msg'] = "Document not found. Please Try Again.";
        }

        return $responseArray;
    }

    public function url_short() {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $url = $_GET['url'];

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->call_url_shortener_api($url);

        print_r($res["short_url"]);
    }


    public function digilocker_api() {
        // ini_set('display_errors', 1);
        // ini_set('display_startup_errors', 1);
        // error_reporting(E_ALL);

        $lead_id = $_GET['lead_id'];

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->call_aadhaar_verification_request_api(1, $lead_id);

        print_r($res);
    }
    public function email_verification() {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $lead_id = $_GET['lead_id'];

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();
        $request_array = array();
        $request_array['email_type'] = $_GET['type'];

        $res = $CommonComponent->call_email_verification_api($lead_id, $request_array);
        echo '<pre>';
        print_r($res);
    }

    public function whatsapp_api() {

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $req = array();
        $templete_type_id = $_GET['type_id'];
        $lead_id = $_GET['lead_id'];

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->payday_whatsapp_api($templete_type_id, $lead_id);

        echo "<pre>";
        print_r($res);
    }

    public function bureau_api() {

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $req = array();
        $lead_id = $_GET['lead_id'];

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->call_bre_rule_engine($lead_id);

        echo "<pre>";
        print_r($res);
    }

    public function bank_account_verification() {

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $req = array();
        $lead_id = $_GET['lead_id'];

        $request_array = array();
        $request_array['beneficiaryAccount'] = 9717882592;
        $request_array['beneficiaryIFSC'] = "Customer";
        $request_array['beneficiaryMobile'] = '';
        $request_array['beneficiaryName'] = 'BHAVISH';
        $request_array['nameFuzzy'] = true;
        $request_array['nameMatchScore'] = '0..9';
        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();
        $res = $CommonComponent->payday_bank_account_verification_api(1, $lead_id, $request_array);

        echo "<pre>";
        print_r($res);
    }

    public function down_file() {
        $name = "9618_lms_20230515170913621.pdf";
        $ci = &get_instance();
        $ci->load->helper(array('commonfun'));
        move_uploaded_file(downloadDocument($name, 0), base_url("public/uploads/") . $name);
        //file_put_contents(base_url("public/uploads/").$name,$file);
        //print_r($file);
    }

    public function get_file() {
        $name = $_GET['file'];

        $this->load->helper('commonfun');

        $file = file_get_contents(downloadDocument($name, 0));

        echo $file;
    }

    public function check_table_data() {
        echo 'Testing';
        $lead_id = 15905;
        $this->load->model('Task_Model', 'Tasks');

        $data = $this->Tasks->getEmploymentDetails($lead_id);

        echo "<pre>";
        print_r($data->row());
    }

    public function call_payday_bank_analysis() {


        $request_array = array();


        $sql = 'SELECT docs.docs_id, docs.lead_id, leads.status, leads.stage, leads.lead_status_id FROM `docs` INNER join leads ON leads.lead_id = docs.lead_id where docs.lead_id =15905 AND docs.docs_master_id = 6 ORDER BY  docs.docs_id DESC LIMIT 1';
        $docs_details = $this->db->query($sql);
        //  print_r($docs_details);
        if ($docs_details->num_rows() > 0) {
            $docs = $docs_details->row();
            $doc_id = $docs->docs_id;

            $request_array['doc_id'] = $doc_id;

            require_once(COMPONENT_PATH . "CommonComponent.php");

            $CommonComponent = new CommonComponent();
            // echo 'object created';

            $result = $CommonComponent->payday_bank_analysis_api_call(1, 15905, $request_array);

            print_r($result);
        }
    }

    public function upload_test() {

        echo 'LW Tescontroller START';
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        require_once(COMPONENT_PATH . '/s3_bucket/S3_upload.php');

        $new = new S3_upload();
        $res = $new->upload_file('/home/devmunitechuat/public_html/upload/', '1788_1_20230329081119_2635.pdf', 0);
        echo "<pre>";
        print_r($res);
        echo 'LW Tescontroller END';
    }

    public function write_letter() {
        $lead_ID = $_GET['lead_id'];
        // $lead_ID = 0;
        $this->load->model('Task_Model', 'Tasks');
        $this->Tasks->sendSanctionMail($lead_ID);
        echo 'Mail send Succufully :' . $lead_ID;
    }

    public function check_mail() {

        $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                            <html xmlns="http://www.w3.org/1999/xhtml">
                                <head>
                                    <meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
                                    <title>Digital E-KYC</title>
                                </head>
                                <body>
                                    <table width = "800" border = "0" align = "center" cellpadding = "0" cellspacing = "0" style = "border:solid 1px #ddd;font-family:Arial, Helvetica, sans-serif;">
                                        <tr>
                                            <td width = "800" colspan = "2" style = "background:url(' . EKYC_HEADER_BACK . ');" >
                                                <table width = "100%" border = "0" cellpadding = "0" cellspacing = "0">
                                                    <tr>
                                                        <td width = "25%" valign = "top"><a href = "' . WEBSITE_URL . '" target = "_blank"><img src = "https://crm.tejasloan.com/public/images/final_logo.png" alt = "logo" width = "200" height = "50" style = "margin-top:10px;margin-left:12px;"></a></td>
                                                        <td width = "64%" align = "center" valign = "middle"><strong style = "color:#fff; font-size:20px;">DIGITAL E-KYC</strong></td>
                                                        <!-- <td width = "11%" align = "right"><img src = "' . EKYC_LINES . '" width = "26" height = "147" /></td> -->
                                                    </tr>
                                                </table>

                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan = "2" valign = "top"><table width = "100%" border = "0" cellpadding = "0" cellspacing = "0" style = "padding:0px 10px;">
                                                    <tr>
                                                        <td width = "50%" rowspan = "10" valign = "top" style = "border-right:solid 1px #8180e0;"><table width = "100%" border = "0">
                                                                <tr>
                                                                    <td align = "center" valign = "middle">&nbsp;
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td valign = "middle"><span style = "font-weight:bold; font-size:25px; color:#8180e0;">Dear ' . $customer_name . ' </span></td>
                                                                </tr>
                                                                <tr>
                                                                    <td align = "center">&nbsp;
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 25px;">We thank you for showing interest in ' . WEBSITE . ' . Your loan application has been assigned for credit approval.</p></td>
                                                                </tr>
                                                                <tr>
                                                                    <td align = "center">&nbsp;
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 25px;">In order to process your loan application further, please do the e-kyc via DigiLocker using your Aadhaar.</p></td>
                                                                </tr>
                                                                <tr>
                                                                    <td align = "center">&nbsp;
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 25px;">Once you click on the Digital E-KYC button, You will redirect to the DigiLocker portal, where you need to follow the steps given in <b>"How it Works"</b> on your right side.</p></td>
                                                                </tr>
                                                                <tr>
                                                                    <td align = "center">&nbsp;
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 25px;">Kindly click on the below button to proceed.</p></td>
                                                                </tr>
                                                                <tr>
                                                                    <td align = "center">&nbsp;
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td align = "center"><a href = "' . $digital_ekyc_url . '" style = "background: #8180e0;color: #fff;padding: 7px 15px;border-radius: 3px;text-decoration: blink;">Digital E-KYC</a></td>
                                                                </tr>
                                                                <!-- <tr>
                                                                    <td align = "center"><img src = "' . EKYC_LINES . '" alt = "line" width = "26" height = "10" /></td>
                                                                </tr> -->
                                                                <tr>
                                                                    <td><br><p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 20px;">If you are not able to click on the above button, then please copy and paste this URL <a href = "' . $digital_ekyc_url . '">' . $digital_ekyc_url . ' </a> in the browser to proceed.</p></td>
                                                                </tr>
                                                            </table></td>
                                                        <td width = "0" rowspan = "10" align = "center">&nbsp;
                                                        </td>
                                                        <td colspan = "2" align = "left">&nbsp;
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan = "2" align = "center"><span style = "font-weight:bold; font-size:25px; color:#8180e0;">How it Works</span></td>
                                                    </tr>

                                                    <!-- <tr>
                                                        <td colspan = "2" align = "left"><img src = "' . EKYC_LINES . '" alt = "line" width = "26" height = "5" /></td>
                                                    </tr> -->

                                                    <tr>
                                                        <!-- <td width = "23%" align = "left"><a href = "' . EKYC_IMAGES_1_SHOW . '" target = "_blank"><img src = "' . EKYC_IMAGES_1 . '" alt = "1st" width = "172" height = "103" /></a></td> -->
                                                        <td width = "35%" valign = "top">
                                                            <p style = "color: #8180e0;font-size:18px;margin: 0px;padding-left: 10px;"><strong>First Step</strong></p>
                                                            <p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 20px;">Please enter your 12 digits Aadhaar No. and press next.</p>
                                                        </td>
                                                    </tr>
                                                    <!-- <tr>
                                                        <td colspan = "2" align = "left"><img src = "' . EKYC_LINES . '" alt = "line" width = "26" height = "5" /></td>
                                                    </tr> -->
                                                    <tr>
                                                        <!-- <td align = "left"><a href = "' . EKYC_IMAGES_2_SHOW . '" target = "_blank"><img src = "' . EKYC_IMAGES_2 . '" alt = "2nd" width = "171" height = "103" /></a></td> -->
                                                        <td align = "left" valign = "top">
                                                            <p style = "color: #8180e0;font-size:18px;margin: 0px;padding-left: 10px;"><strong>Second Step</strong></p>
                                                            <p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 20px;">Please enter the OTP received in your registered mobile no. with Aadhaar and press continue.</p>
                                                        </td>
                                                    </tr>
                                                    <!-- <tr>
                                                        <td colspan = "2" align = "left"><img src = "' . EKYC_LINES . '" alt = "line" width = "26" height = "5" /></td>
                                                    </tr> -->
                                                    <tr>
                                                        <!-- <td align = "left"><a href = "' . EKYC_IMAGES_3_SHOW . '" target = "_blank"><img src = "' . EKYC_IMAGES_3 . '" alt = "3rd" width = "173" height = "103" /></a></td> -->
                                                        <td align = "left" valign = "top"><p style = "color: #8180e0;font-size:18px;margin: 0px;padding-left: 10px;"><strong>Third Step</strong></p>
                                                            <p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 20px;">Press allow to give access of your DigiLocker account for documents verification.</p></td>
                                                    </tr>
                                                    <!-- <tr>
                                                        <td colspan = "2" align = "left"><img src = "' . EKYC_LINES . '" alt = "line" width = "26" height = "5" /></td>
                                                    </tr> -->
                                                    <tr>
                                                        <!-- <td align = "left"><a href = "' . EKYC_IMAGES_4_SHOW . '" target = "_blank"><img src = "' . EKYC_IMAGES_4 . '" alt = "4th" width = "173" height = "102" /></a></td> -->
                                                        <td align = "left" valign = "top"><p style = "color: #8180e0;font-size:18px;margin: 0px;padding-left: 10px;"><strong>Thank You</strong></p>
                                                            <p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 20px;">Your approval to access DigiLocker account for E-KYC has been successfully submitted.</p></td>
                                                    </tr>
                                                    <tr>
                                                        <td valign = "top" style = "border-right:solid 1px #8180e0;">&nbsp;
                                                        </td>
                                                        <td align = "center">&nbsp;
                                                        </td>
                                                        <td align = "left">&nbsp;
                                                        </td>
                                                        <td align = "left" valign = "top">&nbsp;
                                                        </td>
                                                    </tr>

                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan = "4" align = "center" valign = "middle" style = "border-top:solid 1px #ddd; padding-top:5px;">
                                                <a href = "' . LINKEDIN_LINK . '" target = "_blank"> <img src = "https://crm.tejasloan.com/public/images/linkedin.png" alt = "linkdin" width = "32" height = "32" /></a>
                                                <a href = "' . INSTAGRAM_LINK . '" target = "_blank"> <img src = "https://crm.tejasloan.com/public/images/instagram.png" alt = "instagram" width = "32" height = "32" /></a>
                                                <a href = "' . FACEBOOK_LINK . '" target = "_blank"> <img src = "https://crm.tejasloan.com/public/images/facebook.png" alt = "facebook" width = "32" height = "32" /></a>
                                                <a href = "' . TWITTER_LINK . '" target = "_blank" style = "color:#fff;"> <img src = "https://crm.tejasloan.com/public/images/twitter.png" alt = "twitter" width = "32" height = "32" /> </a>
                                                <a href = "' . YOUTUBE_LINK . '" target = "_blank" style = "color:#fff;"> <img src = "https://crm.tejasloan.com/public/images/youtube.png" alt = "youtube" width = "32" height = "32" /> </a>
                                                <!-- <a href = "' . APPLE_STORE_LINK . '" target = "_blank"> <img src = "https://crm.tejasloan.com/public/images/googleplay.png" alt = "google_play" width = "100" height = "30" style = "border-radius: 50px;"></a> -->
                                            </td>
                                        </tr>

                                        <tr>
                                            <td colspan = "4" align = "center" valign = "middle" bgcolor = "#8180e0" style = "padding:10px; color:#fff; font-weight:normal; font-size:16px;"><a href = "tel:' . REGISTED_MOBILE . '" style = "color:#fff; text-decoration:blink;"><img src = "https://crm.tejasloan.com/public/images/phone.jpg" width = "16" height = "16" alt = "phone-icon" style = "margin-bottom: -2px;"> ' . REGISTED_MOBILE . ' </a> <a href = "' . WEBSITE_URL . '" target = "_blank" style = "color:#fff; text-decoration:blink;"><img src = "https://tejasloan.com/public/images/favicon.png" width = "16" height = "16" alt = "web-icon" style = "margin-bottom: -2px;margin-right:3px;"> ' . WEBSITE . ' </a> <img src = "https://tejasloan.com/public/images/email.jpg" width = "16" height = "16" alt = "email-icon" style = "margin-bottom: -2px;"><a href = "mailto:' . INFO_EMAIL . '" style = "color:#fff; text-decoration:blink;">' . INFO_EMAIL . ' </a></td>
                                        </tr>
                                    </table>
                                </body>
                            </html>';
        $subject = "Test";

        require_once(COMPONENT_PATH . 'includes/functions.inc.php');

        // common_send_email('info@tejasloan.com', $subject, $message);
        common_send_email('alam@salarywalle.com', $subject, $message, "", "", "", "", "https://crm.tejasloan.com/direct-document-file/3718078_lms_20241120105925275.jpeg");


        // $curl = curl_init();

        // curl_setopt_array($curl, array(
        //   CURLOPT_URL => 'https://api.mailgun.net/v3/tejasloan.com/messages',
        //   CURLOPT_RETURNTRANSFER => true,
        //   CURLOPT_ENCODING => '',
        //   CURLOPT_MAXREDIRS => 10,
        //   CURLOPT_TIMEOUT => 0,
        //   CURLOPT_FOLLOWLOCATION => true,
        //   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //   CURLOPT_CUSTOMREQUEST => 'POST',
        //   CURLOPT_POSTFIELDS => array('from' => 'info@tejasloan.com','to' => 'chhabrabhavish@gmail.com','text' => 'Test','subject' => 'Testing'),
        //   CURLOPT_HTTPHEADER => array(
        //     'Authorization: Basic YXBpOmFjMjg1MjIzOTU0ZmU4MmVhYTVjNGYwNDg1NzJjY2RhLTE5ODA2ZDE0LTExMWFlYjBi'
        //   ),
        // ));

        // $response = curl_exec($curl);

        // curl_close($curl);
        // if(!empty($res)){
        //     echo 'Testing right';
        // }else{
        //     echo 'Testing Wrong';
        // }

    }

    function validate_email() {

        $api_key = 'ae394093e501ad6c8eba27f84cf9591b-f6202374-5f97cbf2'; // Replace with your Mailgun API key
        $domain = 'tejasloan.com'; // Replace with your Mailgun domain

        $url = 'https://api.mailgun.net/v3/tejasloan.com/messages';

        $to = 'info@tejasloan.com';
        $from = 'info@tejasloan.com';
        $subject = 'Test email';
        $message = 'This is a test email sent using Mailgun.';

        $data = [
            'from' => $from,
            'to' => $to,
            'subject' => $subject,
            'text' => $message
        ];

        $options = [
            'http' => [
                'header' => "Content-Type:multipart/form-data",
                'method' => 'POST',
                'content' => http_build_query($data),
                'ignore_errors' => true
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        echo $response;
    }

    // Example usage:




    public function send_disbursal_email() {
        $lead_ID = $_GET['lead_id'];
        $this->load->model('Task_Model', 'Tasks');
        $res = $this->Tasks->sendDisbursalMail($lead_ID);
        echo '<pre>';
        print_r($res);
    }

    public function noc_Settled_Payment() {
        $lead_ID = $_GET['lead_id'];
        $this->load->model('Task_Model', 'Tasks');
        $res = $this->Tasks->nocSettledPayment($lead_ID);
        echo '<pre>';
        print_r($res);
    }

    public function noc_Settled_closing() {
        $lead_ID = $_GET['lead_id'];
        $this->load->model('Task_Model', 'Tasks');
        $res = $this->Tasks->sent_loan_closed_noc_letter($lead_ID);
        echo '<pre>';
        print_r($res);
    }

    public function check_crif() {
        // echo 'Testing wright';

        require_once(COMPONENT_PATH . "CommonComponent.php");
        $CommonComponent = new CommonComponent();
        $res = $CommonComponent->call_bureau_api(15873);

        echo '<pre>';
        print_r($res);
    }

    public function adjust() {
        echo '<pre>';
        //        ini_set('display_errors', 1);
        //        ini_set('display_startup_errors', 1);
        //        error_reporting(E_ALL);
        //
        require_once(COMPONENT_PATH . "CommonComponent.php");

        $CommonComponent = new CommonComponent();
        $res = $CommonComponent->call_adjust_api(15927);
        print_r($res);
    }

    public function generate_sanction_letter() {
        $lead_id = $_GET['lead_id'];
        $this->load->model('Task_Model', 'Tasks');
        $data = $this->Tasks->gererateSanctionLetter($lead_id);
        header("Content-Type: {$data['header_content_type']}");
        echo $data['document_body'];
    }

    public function eligibility() {
        echo '<pre>';
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $lead_id = 15905;
        require_once(COMPONENT_PATH . "CommonComponent.php");
        $CommonComponent = new CommonComponent();
        $res = $CommonComponent->run_eligibility($lead_id);
        print_r($res);
    }

    public function smsAnalizer() {
        echo '<pre>';
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $lead_id = 21;
        //$request_array['userIds'] = [1234];
        //$request_array['userId'] = 1234;
        $request_array = ['syncId' => "1696312788209_1234", 'sendAppInfo' => true, '1' => true, 'sendCallLogsInfo' => true, 'sendSms' => true, 'sendExcelPath' => true, 'sendFraudIndicator' => true];
        require_once(COMPONENT_PATH . "CommonComponent.php");
        $CommonComponent = new CommonComponent();
        $res = $CommonComponent->call_payday_sms_analyser('GET_USER_SCOPE', $lead_id, $request_array);
        print_r($res);
    }

    public function getContent() {
        $url = "http://speedoloan.sms.variables.digitap.demo.in.s3.ap-south-1.amazonaws.com/2b7a82215241b5cd/1234/1696312788209_1234/syncData.json?X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Date=20231017T090354Z&X-Amz-SignedHeaders=host&X-Amz-Expires=3600&X-Amz-Credential=AKIA325LPZPYI54D42HV%2F20231017%2Fap-south-1%2Fs3%2Faws4_request&X-Amz-Signature=96e3600b05be6e4891bc43cf071df89f7e5aa4f7f244a076d41c360a417db022";
        $obj = json_decode(file_get_contents($url), true);
        echo '<pre>';
        print_r($obj);
        die;
    }

    public function digilocker_create_url() {
        $lead_id = $_GET['lead_id'];
        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->call_aadhaar_verification_request_api($lead_id);

        echo "<pre>";

        print_r($res);
    }

    public function smartping() {

        $this->load->helper('integration/payday_smartping_call_api_helper');

        echo '<pre>';
        echo 'CALLED';
        //        ini_set('display_errors', 1);
        //        ini_set('display_startup_errors', 1);
        //        error_reporting(E_ALL);
        //        $lead_id = $_GET['lead_id'];

        $request_array = array();
        $request_array['call_type'] = 1;
        $request_array['profile_type'] = 2;
        $request_array['lead_list'] = array(1, 2, 5, 41, 93, 18, 19, 20, 21, 22);
        print_r($request_array);

        $res = payday_call_management_api_call("SMARTPING_BULK_UPLOAD", 0, $request_array);
        print_r($res);
    }

    public function bank_analysis_upload() {

        $lead_id = $_GET['lead_id'];

        $request_array['doc_id'] = $_GET['doc_id'];

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        // echo BANK_STATEMENT_UPLOAD;

        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $res = $CommonComponent->call_payday_bank_analysis("BANK_STATEMENT_UPLOAD", $lead_id, $request_array);

        echo '<pre>';
        print_r($res);
    }

    public function bank_analysis_download() {
        $lead_id = $_GET['lead_id'];

        $request_array['doc_id'] = $_GET['doc_id'];

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $res = $CommonComponent->call_payday_bank_analysis("BANK_STATEMENT_DOWNLOAD", $lead_id, $request_array);

        echo '<pre>';
        print_r($res['raw_response']);
    }

    public function smart_ping_whatsapp() {
        require_once(COMPONENT_PATH . 'CommonComponent.php');
        $CommonComponent = new CommonComponent();
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $request_array = array('smart_ping_whatsapp_flag' => 1);
        $res = $CommonComponent->call_whatsapp_api(14, 16047, $request_array);
        echo '<pre>';
        print_r($res);
        die;
    }

    public function bureau_customer_mobile_number() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $lead_id = $_GET['lead_id'];
        require_once(COMPONENT_PATH . 'CommonComponent.php');
        $CommonComponent = new CommonComponent();
        $res = $CommonComponent->call_bureau_api($lead_id);
        echo '<pre>';
        print_r($res);
    }

    public function ai_sensy_api_test() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $lead_id = $_GET['lead_id'];
        $template_id = $_GET['template_id'];
        $api_provider_id = $_GET['api_provider_id'];
        require_once(COMPONENT_PATH . 'CommonComponent.php');
        $CommonComponent = new CommonComponent();
        $request_array = array(
            "template_id" => $template_id,
            "user_id" => 222,
            "api_provider_id" => $api_provider_id
        );
        $res = $CommonComponent->call_whatsapp_api(1, $lead_id, $request_array);
        echo '<pre>';
        print_r($res);
        die("END");
    }

    public function account_aggregator() {
        $enc_lead_id = $this->encrypt->encode($_GET['lead_id']);
        $lead_id = $_GET['lead_id'];

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $request_array['redirect_url'] = WEBSITE_URL . "account-consent-thank-you?refstr=" . $enc_lead_id;

        $res = $CommonComponent->call_payday_account_aggregator("CONSENT_HANDLE_REQUEST", $lead_id, $request_array);

        echo '<pre>';
        print_r($res);
    }

    public function get_consent_status() {
        $lead_id = $_GET['lead_id'];

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $res = $CommonComponent->call_payday_account_aggregator("CONSENT_STATUS", $lead_id, $request_array);

        echo '<pre>';
        print_r($res);
    }

    public function get_data_fetch_status() {
        $lead_id = $_GET['lead_id'];

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $res = $CommonComponent->call_payday_account_aggregator("DATA_FETCH_STATUS", $lead_id, $request_array);

        echo '<pre>';
        print_r($res);
    }

    public function inactive_user() {
        if (date('H') >= 8 && date('Hi') <= 930) {
            $this->db->query("UPDATE user_lead_allocation_log SET ula_user_status = 2 WHERE ula_user_id !=15 AND DATE(ula_created_on) = CURRENT_DATE()");
            echo "Done";
            exit;
        }
        echo "Not Allowed";
        exit;
    }

    public function bank_statement_fetch() {
        $lead_id = $_GET['lead_id'];

        $request_array = $_GET;

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $res = $CommonComponent->call_payday_account_aggregator("BANK_STATEMENT_FETCH", $lead_id, $request_array);

        echo '<pre>';
        print_r($res);
    }

    public function send_sanction_letter() {

        //$enc_lead_id = $this->encrypt->encode($_GET['lead_id']);
        $lead_id = $_GET['lead_id'];

        $email = "info@tejasloan.com";
        $alternate_email = '';

        $bcc_email = '';

        $fullname = 'Akash Kushwaha';

        //        if (!empty($camDetails->middle_name)) {
        //            $fullname .= ' ' . $camDetails->middle_name;
        //        }
        //
        //        if (!empty($camDetails->sur_name)) {
        //            $fullname .= ' ' . $camDetails->sur_name;
        //        }
        //        $cam_sanction_letter_file_name = "";
        //        if (!empty($camDetails->cam_sanction_letter_file_name)) {
        //            $cam_sanction_letter_file_name = $camDetails->cam_sanction_letter_file_name;
        //        }


        $sanction_date = date("d-m-Y", strtotime(date("Y-m-d")));

        $title = "Mr. ";
        //        if ($camDetails->gender == 'MALE') {
        //            $title = "Mr.";
        //        } else if ($camDetails->gender == 'FEMALE') {
        //            $title = "Ms.";
        //        }

        $residence_address = "B-19, Gali no. 1, Rajveer Colony, Gharoli ext., Delhi-96";

        //        if (!empty($getResidenceDetails->current_house)) {
        //            $residence_address .= " " . $getResidenceDetails->current_house;
        //        }
        //
        //        if (!empty($getResidenceDetails->current_locality)) {
        //            $residence_address .= ", " . $getResidenceDetails->current_locality . "<br/>";
        //        }
        //
        //        if (!empty($getResidenceDetails->current_landmark)) {
        //            $residence_address .= " " . $getResidenceDetails->current_landmark . "<br/>";
        //        }
        //
        //        if (!empty($getResidenceDetails->res_city)) {
        //            $residence_address .= $getResidenceDetails->res_city . ", " . $getResidenceDetails->res_state . " - " . $getResidenceDetails->cr_residence_pincode . ".";
        //        }


        $residence_address = trim($residence_address);

        $subject = BRAND_NAME . ' | Loan Sanction Letter - ' . $fullname;

        $acceptance_button = '';
        $digital_esign_url = base_url('sanction-esign-request') . "?refstr=$enc_lead_id";

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->call_url_shortener_api($digital_esign_url, $lead_id);

        $esign_short_url = $res['short_url'];
        $acceptance_button_link = '<br/><br/><center><a style="text-align:center;outline : none;color: #fff; background: #e52255; border-bottom: none !important; padding: 12px 9px !important;" href="' . $esign_short_url . '">eSign Sanction Letter</a></center><br/><br/>';
        $acceptance_button_link .= "If you are not able to click on the eSign button then please copy and paste this url in browser to proceed or click here .<br/><a href='" . $esign_short_url . "'>" . $esign_short_url . "</a>";

        if (in_array($lead_data_source_id, array(21, 27))) {
            $esign_short_url = base_url('loanAgreementLetterResponse') . "?refstr=$enc_lead_id";
            $acceptance_button_link = '<br/><br/><center><a style="text-align:center;outline : none;color: #fff; background: #e52255; border-bottom: none !important; padding: 12px 9px !important;" href="' . $esign_short_url . '">Accept Sanction Letter</a></center><br/><br/>';
            $acceptance_button_link = "If you are not able to click on the accept button then please copy and paste this url in browser to proceed or click here .<br/><a href='" . $esign_short_url . "'>" . $esign_short_url . "</a>";
        }

        $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns="http://www.w3.org/1999/xhtml">
                        <head>
                            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                            <title>' . $subject . '</title>
                        </head>

                        <body>
                            <table
                            width="667"
                            border="0"
                            align="center"
                            style="
                                font-family: Arial, Helvetica, sans-serif;
                                line-height: 25px;
                                font-size: 14px;
                                border: solid 1px #ddd;
                                padding: 0px 10px;
                            ">
                            <tr>
                                <td colspan="2" valign="middle">
                                <p style="color:#font-size: 18px; color: #0363a3; font-size:18px;">
                                    <img
                                    src=" ' . SANCTION_LETTER_HEADER . ' "
                                    alt="Sanctionletter-header"
                                    width="760"
                                    height="123"
                                    border="0"
                                    usemap="#Map" onContextMenu="return false;"
                                    />
                                </p>
                                </td>
                            </tr>
                            <tr>
                                <td align="right">
                                <span style="color:#font-size: 18px; color: #0363a3; font-size:18px;">Date : ' . $sanction_date . '</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>To,</strong></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td><strong>' . $title . ' </strong>' . $fullname . '.</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td>' . $residence_address . '</td>
                                <td>&nbsp;</td>
                            </tr>

                            <tr>
                                <td><strong>Contact No. :</strong> +91-8750256406</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                Thank you for showing your interest in ' . BRAND_NAME . ' and giving us an
                                opportunity to serve you.
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                We are pleased to inform you that your loan application has been
                                approved as per the below mentioned terms and conditions.
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                <strong>' . BRAND_NAME . ', a brand name under ' . COMPANY_NAME . ' (RBI approved NBFC – Reg No. ' . RBI_LICENCE_NUMBER . ') <br/>' . REGISTED_ADDRESS . '.</strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                This sanction will be subject to the following Terms and Conditions:
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                <table
                                    width="100%"
                                    border="0"
                                    cellpadding="8"
                                    cellspacing="1"
                                    bgcolor="#ddd">
                                    <tr>
                                    <td width="43%" align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Customer Name</strong>
                                    </td>
                                    <td width="4%" align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td width="53%" align="left" valign="middle" bgcolor="#FFFFFF">
                                        ' . $fullname . '
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Sanctioned Loan Amount (Rs.)</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        10000/-
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Rate of Interest (% ) per day</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        1
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Date of Sanction</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        ' . $sanction_date . '
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Total Repayment Amount (Rs.</strong>)
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        12000/-
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Tenure in Days</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        12
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Repayment Date</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        31-01-2024
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Penal Interest(%) per day</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        2
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Processing Fee </strong> (<strong>Rs.)</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        1000/-
                                        (Including 18% GST)
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Repayment Cheque(s)</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">-</td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Cheque drawn on (name of the Bank)</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">-</td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Cheque and NACH Bouncing Charges (Rs.)</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        1,000.00/- per bouncing/dishonour.
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Annualised ROI (%)</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        365
                                    </td>
                                    </tr>
                                </table>
                                </td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                Henceforth visiting (physically) your Workplace and Residence has your
                                concurrence on it.
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                Kindly request you to go through above mentioned terms and conditions
                                and provide your kind acceptance using Aadhaar E-Sign so that we can process
                                your loan for final disbursement.
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                <strong style="color: #0363a3">Best Regards</strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                <strong style="color: #0363a3">Team ' . BRAND_NAME . '</strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                <strong style="color: #0363a3">(Brand Name for ' . COMPANY_NAME . ')</strong>
                                </td>
                            </tr>
                            <tr>
                                <td>' . $acceptance_button . '</td>
                            </tr>
                            <tr>
                                <td>' . $acceptance_button_link . '</td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Kindly Note:</strong></td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                Non-payment of loan on time will adversely affect your Credit score,
                                further reducing your chances of getting loan again
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                Upon approval the processing fee will be deducted from your Sanction
                                amount and balance amount will be disbursed to your account.
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">This Sanction letter is valid for 24 Hours only.</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                You can Prepay/Repay the loan amount using our link
                                <a href="' . LOAN_REPAY_LINK . '"
                                    target="_blank"
                                    style="color: #e52255; text-decoration: blink"
                                    >' . LOAN_REPAY_LINK . '</a>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                <img
                                    src=" ' . SANCTION_LETTER_FOOTER . ' " width="760" height="44"/>
                                </td>
                            </tr>
                            </table>

                            <map name="Map" id="Map">
                            <area shape="rect"
                                coords="574,21,750,110"
                                href="' . WEBSITE_URL . '"
                                target="_blank"/>
                            </map>
                        </body>
                        </html>';

        echo $message;
    }

    public function createDigilockerUrl() {
        $lead_id = $_GET['lead_id'];

        $enc_lead_id = $this->encrypt->encode($lead_id);
        $request_array['redirect_url'] = WEBSITE_URL . 'verify-ekyc?refstr=' . $enc_lead_id;

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $res = $CommonComponent->call_aadhaar_verification_request_api($lead_id, $request_array);

        echo '<pre>';
        print_r($res);
    }

    public function payday_send_sms() {

        require_once(COMPONENT_PATH . 'CommonComponent.php');
        $CommonComponent = new CommonComponent();

        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $lead_id = $_GET['lead_id'];
        $api_id = $_GET['api_id'];

        $sms_input_data = array();
        $sms_input_data['mobile'] = 8287804880;
        $sms_input_data['name'] = "Customer";
        $sms_input_data['otp'] = 1111;
        $res = $CommonComponent->payday_sms_api($api_id, $lead_id, $sms_input_data);

        echo '<pre>';
        print_r($res);
    }

    public function cbil_call() {
        // echo phpinfo();
    }

    public function get_document() {

        require_once(COMPONENT_PATH . 'CommonComponent.php');
        $CommonComponent = new CommonComponent();

        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $lead_id = $_GET['lead_id'];
        $api_id = $_GET['api_id'];

        $sms_input_data = array();
        $sms_input_data['mobile'] = 9717882592;
        $sms_input_data['name'] = "Customer";
        $sms_input_data['otp'] = 1111;
        $res = $CommonComponent->call_aadhaar_verification_response_api($lead_id);

        echo '<pre>';
        print_r($res);
    }

    function getRepayment() {
        $lead = $_GET['lead_id'];

        require_once(COMPONENT_PATH . 'CommonComponent.php');
        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->get_loan_repayment_details($lead);
        echo "<pre>";
        print_r($res);
    }

    function panCard() {
        $lead = $_GET['lead_id'];

        require_once(COMPONENT_PATH . 'CommonComponent.php');
        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->call_pan_verification_api_call("DIGITAP_PANEXTENSION", $lead);
        echo "<pre>";
        print_r($res);
    }

    function esignDownload() {
        $lead = $_GET['lead_id'];

        require_once(COMPONENT_PATH . 'CommonComponent.php');
        $CommonComponent = new CommonComponent();

        $res = $CommonComponent->download_esign_document_api($lead);
        echo "<pre>";
        print_r($res);
    }

    public function appsflyer() {
        require_once(COMPONENT_PATH . 'CommonComponent.php');
        echo "api called";
        $CommonComponent = new CommonComponent();
        $request_array = array();
        $lead_id = $_GET['lead_id'];
        $request_array['event_type_id'] = $_GET['event_type_id'];
        $return_array = $CommonComponent->payday_appsflyer_campaign_api_call("EVENT_PUSH_CALL", $lead_id, $request_array);
        print_r($return_array);
    }

    public function whatsapp() {
        require_once(COMPONENT_PATH . 'CommonComponent.php');
        $CommonComponent = new CommonComponent();
        $lead_id =  $_GET['lead_id'];
        $template_name =  $_GET['template_name'];
        $request_array = array(
            "template_name" => $template_name,
            "user_id" => 222,
            "api_provider_id" => $api_provider_id
        );
        $res = $CommonComponent->call_whatsapp_api(1, $lead_id, $request_array);
        echo '<pre>';
        print_r($res);
        die("END");
    }

    public function QRCode() {

        $lead_id = $_GET['lead_id'];
        if (empty($lead_id)) {
            echo 'Lead ID is required';
            exit;
        }
        require_once(COMPONENT_PATH . 'CommonComponent.php');
        $CommonComponent = new CommonComponent();
        $res = $CommonComponent->call_qrcode_api($lead_id);
        echo '<pre>';
        print_r($res);
        die;
    }

    public function CollectPay() {

        $lead_id = $_GET['lead_id'];
        $customer_vpa = $_GET['customer_vpa'];
        if (empty($lead_id) || empty($customer_vpa)) {
            echo 'Lead ID is required';
            exit;
        }
        require_once(COMPONENT_PATH . 'CommonComponent.php');
        $CommonComponent = new CommonComponent();
        $res = $CommonComponent->call_collectpay_api($lead_id, array('customer_vpa' => $customer_vpa));
        echo '<pre>';
        print_r($res);
        die;
    }

    public function check_upi_api() {

        $lead_id = $_GET['lead_id'];
        $type_id = $_GET['type_id'];
        $payment_mode_id = $_GET['payment_mode_id'];

        if (empty($lead_id) || empty($type_id)) {
            echo 'Lead ID is required';
            exit;
        }

        require_once(COMPONENT_PATH . 'CommonComponent.php');
        $CommonComponent = new CommonComponent();

        if ($type_id == 1) {

            $res = $CommonComponent->call_qrcode_api($lead_id);
        } else if ($type_id == 2) {
            $customer_vpa = $_GET['customer_vpa'];
            if (empty($payment_mode_id)) {
                echo 'VPA ID is required';
                exit;
            }

            $res = $CommonComponent->call_collectpay_api($lead_id, array('customer_vpa' => $customer_vpa));
        } else if ($type_id == 3) {
            $payment_mode_id = $_GET['payment_mode_id'];
            if (empty($payment_mode_id)) {
                echo 'Lead ID is required';
                exit;
            }

            $res = $CommonComponent->call_check_upi_api($lead_id, array("payment_mode_id" => $payment_mode_id));
        }

        echo json_encode($res);
    }

    public function reminder() {

        $lead = $_GET['lead_id'];

        // Fetch lead details securely
        $leaddetails = $this->db->query("SELECT * FROM leads WHERE lead_id = ?", array($lead))->row();

        ob_start();
?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Repayment Reminder</title>
            <style>
                @media (max-width: 600px) {

                    .header,
                    .content,
                    .footer {
                        padding: 15px;
                    }

                    h1 {
                        font-size: 20px;
                    }

                    p,
                    td {
                        font-size: 14px;
                    }

                    .btn {
                        font-size: 14px;
                        padding: 10px 20px;
                    }
                }
            </style>
        </head>

        <body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f3f4f6; color: #333;">
            <div style="max-width: 600px; margin: 20px auto; background: #ffffff; border: 1px solid #ddd; border-radius: 12px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); overflow: hidden;">
                <div style="background: linear-gradient(90deg, #E53935, #FF6F00); color: #fff; padding: 20px; text-align: center;">
                    <h1 style="margin: 0; font-size: 24px;">Payment Reminder</h1>
                </div>
                <div style="padding: 20px;">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <img src="https://sot-website.s3.ap-south-1.amazonaws.com/emailer/payment_remider.png" alt="Payment Reminder Banner" style="width: 100%; max-width: 560px; border-radius: 8px;">
                    </div>
                    <p style="line-height: 1.8; margin: 10px 0; font-size: 16px;">Dear <strong><?= htmlspecialchars($leaddetails->first_name) ?></strong>,</p>

                    <p style="line-height: 1.8; margin: 10px 0;">This is a gentle reminder regarding your upcoming repayment for your loan with <strong><?= BRAND_NAME ?></strong>. Below are the details:</p>

                    <table style="width: 100%; margin: 20px 0; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;">Loan Account Number:</td>
                            <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;"><strong><?= htmlspecialchars($leaddetails->loan_no) ?></strong></td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;">Due Date:</td>
                            <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;"><strong><?= htmlspecialchars($leaddetails->lead_entry_date) ?></strong></td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;">Amount Due:</td>
                            <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px; color: #E53935;"><strong>₹<?= htmlspecialchars($leaddetails->loan_amount) ?></strong></td>
                        </tr>
                    </table>

                    <p style="line-height: 1.8; margin: 10px 0; font-size: 16px; text-align: center;"><strong>Scan the QR code below to make your payment easily:</strong></p>

                    <div style="text-align: center; margin: 20px 0;">
                        <img src="https://quickchart.io/qr?size=300&text=upi%3A%2F%2Fpay%3Fpa%3DKASAR3%40icici%26pn%3DKasar+Credit%26tr%3DEZV2025011512410420291896%26am%3D76390%26cu%3DINR%26mc%3D6012" alt="QR Code for Payment" style="max-width: 200px; border: 2px solid #ddd; border-radius: 8px;">
                    </div>

                    <p style="line-height: 1.8; margin: 10px 0;">Alternatively, you can use the link below to make your payment:</p>

                    <div style="text-align: center; margin: 20px 0;">
                        <a href="[Payment Link]" class="btn" style="display: inline-block; background: #4CAF50; color: #fff; padding: 12px 24px; text-decoration: none; font-size: 16px; border-radius: 6px;">Make Payment</a>
                    </div>

                    <p style="line-height: 1.8; margin: 10px 0;">If you have already made the payment, please disregard this message. For any assistance, contact our support team at <a href="mailto:<?= CARE_EMAIL ?>" style="color: #4CAF50; text-decoration: none;"><?= CARE_EMAIL ?></a> or call us at <strong><?= REGISTED_MOBILE ?></strong>.</p>

                    <p style="line-height: 1.8; margin: 10px 0; font-size: 16px;">Thank you for choosing <?= BRAND_NAME ?>.</p>

                    <p style="line-height: 1.8; margin: 10px 0; font-size: 16px;">Best regards,<br><strong>The <?= BRAND_NAME ?> Team</strong></p>
                </div>
                <div style="background: #f9f9f9; color: #666; text-align: center; font-size: 14px; padding: 20px;">
                    <p style="margin: 0;">&copy; 2025 <?= BRAND_NAME ?>. All rights reserved.</p>
                    <p style="margin: 0;">
                        <a href="[Privacy Policy Link]" style="color: #4CAF50; text-decoration: none; margin-right: 15px;">Privacy Policy</a>
                        <a href="[Terms of Service Link]" style="color: #4CAF50; text-decoration: none; margin-right: 15px;">Terms of Service</a>
                        <a href="[Contact Us Link]" style="color: #4CAF50; text-decoration: none;">Contact Us</a>
                    </p>
                    <div style="margin-top: 15px;">
                        <tr>
                            <td style="background-color: #f4f4f4; padding: 20px; text-align: center;">
                                <p style="font-size: 14px; color: #777; margin: 0 0 10px;">
                                    Follow us on:
                                </p>
                                <a href="<?= FACEBOOK_LINK ?>" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                    <img src="<?= FACEBOOK_ICON ?>" alt="facebook" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                </a>
                                <a href="<?= TWITTER_LINK ?>" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                    <img src="<?= TWITTER_ICON ?>" alt="twitter" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                </a>
                                <a href="<?= LINKEDIN_LINK ?>" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                    <img src="<?= LINKEDIN_ICON ?>" alt="linkedin" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                </a>
                                <a href="<?= INSTAGRAM_LINK ?>" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                    <img src="<?= INSTAGRAM_ICON ?>" alt="instagram" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                </a>
                                <a href="<?= YOUTUBE_LINK ?>" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                    <img src="<?= YOUTUBE_ICON ?>" alt="youtube" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                </a>
                            </td>
                        </tr>
                    </div>
                </div>
            </div>
        </body>

        </html>
<?php
        echo ob_get_clean();
        exit;
    }
}
