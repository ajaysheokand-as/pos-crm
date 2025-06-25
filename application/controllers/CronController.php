<?php

defined('BASEPATH') or exit('No direct script access allowed');
ini_set('max_execution_time', 3600);
ini_set("memory_limit", "1024M");

class CronController extends CI_Controller {

    public $tbl_leads = 'leads LD';
    public $tbl_lead_followup = 'lead_followup LF';
    public $tbl_customer = 'lead_customer C';
    public $tbl_docs = 'docs D';
    public $tbl_users = 'users U';
    public $tbl_customer_employment = "customer_employment CE";
    public $tbl_cam = "credit_analysis_memo CAM";

    public function __construct() {
        parent::__construct();
        $this->load->model('Leadmod', 'Leads');
        $this->load->model('Task_Model', 'Tasks');
        $this->load->model('Admin_Model', 'Admin');
        $this->load->model('CAM_Model', 'CAM');
        $this->load->model('Docs_Model', 'Docs');
        $this->load->model('Users/Email_Model', 'Email');
        $this->load->model('Users/SMS_Model', 'SMS');

        date_default_timezone_set('Asia/Kolkata');
        $timestamp = date("Y-m-d H:i:s");
    }

    public function error_page() {
        $this->load->view('errors/index');
    }

    public function index() {
    }

    public function sentRepaymentReminderOnMail() {
        //die;
        $message_type = 'EMAIL_REMINDER';
        $SmsRow = $this->Tasks->getRepaymentReminderSend($message_type);
        foreach ($SmsRow as $return_sms) {
            if ($return_sms['lead_status_id'] != 14 && $return_sms['lead_status_id'] != 19) {
                $return_sms['status'] = 0;
                $return_sms['msg'] = "Not Disburse Status!";
                echo json_encode($return_sms);
                die;
            }

            $firstname = $return_sms['first_name'];
            $lead_id = $return_sms['lead_id'];
            $email_to = $return_sms['email'];
            //$email_to = "rohitniit66@gmail.com";
            $mobile = $return_sms['mobile'];
            $address = $return_sms['current_house'];
            $loan_recommended = $return_sms['loan_recommended'];
            $loan_no = $return_sms['loan_no'];
            $repayment_amount = $return_sms['repayment_amount'];
            $repayment_date = date('d-m-Y', strtotime($return_sms['repayment_date']));
            $days_until_repayment = $return_sms['days_until_repayment'];
            $insertReminderAttention = array(
                'lead_id' => $lead_id,
                'title' => "Reminder Payment Attention On Mail",
                'message_type' => "EMAIL_REMINDER",
                'created_on' => date('Y-m-d H:i:s'),
                'status' => 1
            );
            $days = isset($return_sms['days_until_repayment']) ? ($return_sms['days_until_repayment']) : 0;
            $subject = "Reminder Payment Attention";
            $message = '<!DOCTYPE html>
                                <html lang="en">
                                <head>
                                    <style>
                                        .email-container {
                                            width: 100%;
                                            padding: 20px 0;
                                            display: flex;
                                            justify-content: center;
                                        }
                                    .email-content {
                                      height: 180vh;
                                      background-image: url(\'' . base_url() . '/public/images/head_background_mail.jpg\');
                                      background-size: 80% 100%;
                                      background-repeat: no-repeat;
                                      padding: 20px;
                                      box-shadow: 0 0 10px rgba(0,0,0,0.1);
                                      background-position: center;
                                            }
                                            .header {
                                                width: 100%;
                                                text-align: center;
                                                padding: 10px 0;
                                                position: relative;
                                            }.header img {
                                                display: inline-block;
                                                vertical-align: middle;
                                            }.header .left-banner {
                                                width: 50%;
                                            }.header .left-banner img{
                                                width: 62%;
                                    		    margin-left:25px;
                                            }.header .left-top {
                                                width: 30%;
                                            }.header .left-top img{
                                                width: 58%;
                                                padding-left:0px;
                                            }.header .right-top {
                                                width: 70%;
                                            }.header .right-banner {
                                                width: 50%;
                                            }.header .right-banner img {
                                                width: 63%;
                                    			text-align: center;
                                            }.header .reminder-text{
                                                padding-top:40px;
                                            }.header .reminder-text span {
                                                color: white;
                                                text-align: center;
                                                font-size: 17px;
                                                line-height: 20px;
                                             }
                                             @media only screen and (max-width: 600px) {
                                             .email-content {
                                                    height: 65vh !important;
                                            }.header .left-banner {
                                                    width: 60% !important;
                                        			text-align: left;
                                        			margin-left: 55px !important;
                                            }.header .left-top img{
                                                width: 58%;
                                                margin-left:-80px !important;
                                            }.header .reminder-text{
                                                padding-top:28px !important;
                                            }.header .right-banner  {
                                                    width: 40% !important;
                                            }.header .reminder-text{
                                                    padding-top:20px !important;
                                            }.header .right-banner img {
                                                    width: 70% !important;
                                        			text-align: left !important;
                                        			margin-right:120px !important;
                                            }.header .reminder-text span {
                                                color: white !important;
                                                text-align: center;
                                                padding-left:2px !important;
                                                padding-right:2px !important;
                                                font-size: 8px !important;
                                                line-height: 10px !important;
                                             }
                                            }
                                        </style>
                                    </head>
                                    <body>
                                        <div class="email-container">
                                            <div class="email-content">
                                                <table class="header" width="100%">
                                                    <tr>
                                                        <!--<td  class="left-top"><img src="https://lms.paisaonsalary.in/public/images/Kasar_background.png" alt="Left Banner"></td>-->
                                                        <td  class="left-top">Aman Fincap Ltd.</td>
                                                         <td class="right-top" ></td>
                                                    </tr>';
            if (isset($return_sms['days_until_repayment']) && ($return_sms['days_until_repayment'] < 1)) {
                $message .= '<tr>
                                                        <td class="left-banner"><img src="https://lms.paisaonsalary.in/public/images/important-reminer.png" alt="Important Reminder"></td>
                                                        <td class="right-banner"><img src="https://lms.paisaonsalary.in/public/images/LAST-DAY.png" alt="LAST DAY"></td>
                                                    </tr>';
            } else {
                $message .= '<tr>
                                                        <td class="left-banner"><img src="https://lms.paisaonsalary.in/public/images/friend_backend.jpg" alt="Frindly Reminder"></td>
                                                        <td class="right-banner"><img src="https://lms.paisaonsalary.in/public/images/' . $days . '-DAYS.png" alt="' . $days . ' DAYS"></td>
                                                    </tr>';
            }

            $message .= '<tr>
                                                     <td class="reminder-text" colspan="2"><span>We would like to remind you that your account has an outstanding<br> payment of <strong>' . $repayment_amount . '</strong>, which is due on <strong>' . $repayment_date . '</strong>.</span></td>
                                                    </tr>
                                                </table>
                                                <table class="content" width="100%">
                                                    <tr>
                                                        <td>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </body>
                                    </html>';
            require_once(COMPONENT_PATH . 'includes/functions.inc.php');
            $return_array = common_send_email($email_to, $subject, $message, "", "", "", "", "", $file_name, 'Reminder Message');
            $this->db->insert('customer_msg_reminder', $insertReminderAttention);
            //echo "<pre>";
            //print_r($insertReminderAttendence); echo "</pre><br/>";
        }
    }

    public function sentRepaymentReminderOnSMS() {

        $message_type = 'SMS_REMINDER';
        $SmsRow = $this->Tasks->getRepaymentReminderSend($message_type);
        foreach ($SmsRow as $return_sms) {

            if ($return_sms['lead_status_id'] != 14 && $return_sms['lead_status_id'] != 19) {
                $return_sms['status'] = 0;
                $return_sms['msg'] = "Not Disburse Status!";
                echo json_encode($return_sms);
                die;
            }

            $firstname = $return_sms['first_name'];
            $lead_id = $return_sms['lead_id'];
            //$email_to = $return_sms['email'];
            $mobile = $return_sms['mobile'];
            //$mobile = '9716763608';
            $address = $return_sms['current_house'];
            $loan_recommended = $return_sms['loan_recommended'];
            $loan_no = $return_sms['loan_no'];
            $repayment_amount = $return_sms['repayment_amount'];
            $repayment_date = $return_sms['repayment_date'];
            $days_until_repayment = $return_sms['days_until_repayment'];



            if (isset($days_until_repayment) && $days_until_repayment >= 1 && $days_until_repayment <= 7) {

                require_once(COMPONENT_PATH . 'CommonComponent.php');
                $CommonComponent = new CommonComponent();

                $sms_input_data = array(
                    'mobile' => $mobile,
                    'first_name' => $firstname,
                    'repayment_amount' => $repayment_amount,
                    'loan_no' => $loan_no,
                    'repayment_date' => $repayment_date
                );

                $sms_veri_return = $CommonComponent->payday_sms_api(2, $lead_id, $sms_input_data);
                // print_r($sms_veri_return);
                $insertReminderAttention = array(
                    'lead_id' => $lead_id,
                    'title' => "Reminder Payment Attention On SMS",
                    'created_on' => date('Y-m-d H:i:s'),
                    'message_type' => "SMS_REMINDER",
                    'status' => 1
                );

                $this->db->insert('customer_msg_reminder', $insertReminderAttention);
            } else {
                echo 'SMS Allready Sent Successfully';
            }
        }
    }

    public function sentRepaymentReminderOnWhatsapp() {
        $message_type = 'WHATSAPP_REMINDER';
        $SmsRow = $this->Tasks->getRepaymentReminderSend($message_type);
        foreach ($SmsRow as $return_sms) {

            if ($return_sms['lead_status_id'] != 14 && $return_sms['lead_status_id'] != 19) {
                $return_sms['status'] = 0;
                $return_sms['msg'] = "Not Disburse Status!";
                echo json_encode($return_sms);
                die;
            }

            $firstname = $return_sms['first_name'];
            $lead_id = $return_sms['lead_id'];
            $mobile = $return_sms['mobile'];
            $address = $return_sms['current_house'];
            $loan_recommended = $return_sms['loan_recommended'];
            $loan_no = $return_sms['loan_no'];
            $repayment_amount = $return_sms['repayment_amount'];
            $repayment_date = $return_sms['repayment_date'];
            $days_until_repayment = $return_sms['days_until_repayment'];

            // print_r($days_until_repayment); die;

            if (isset($days_until_repayment) && $days_until_repayment >= 0  && $days_until_repayment <= 7) {

                require_once(COMPONENT_PATH . 'CommonComponent.php');
                $CommonComponent = new CommonComponent();
                $CommonComponent->call_whatsapp_api(1, $lead_id);
                $insertReminderAttention = array(
                    'lead_id' => $lead_id,
                    'title' => "Reminder Payment Attention On Whatsapp",
                    'created_on' => date('Y-m-d H:i:s'),
                    'message_type' => "WHATSAPP_REMINDER",
                    'status' => 1
                );

                $this->db->insert('customer_msg_reminder', $insertReminderAttention);
            } else {
                echo 'Whatsapp Allready Sent Successfully';
            }
        }
    }

    // function to export the XLX data into the database //

    public function __destruct() {
        $this->db->close();
    }
}
