<?php

defined('BASEPATH') or exit('No direct script access allowed');

class CronSMSController extends CI_Controller {

    var $cron_notification_email = 'alam@salarywalle.com';
    var $cron_notification_cc_email = '';

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $this->load->model('CronJobs/CronSMS_Model', 'SMSModel');
    }

    public function index() {
    }

    public function freshNotContactableCustomerSMS() { //Normal Email Template
        //        error_reporting(E_ALL);
        //        ini_set('display_errors', 1);
        $cron_name = "freshnotcontactablecustomersms";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->SMSModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->SMSModel->insert_cron_logs($cron_name);

        $tempDetails = $this->SMSModel->getAllNotContactCustomerSMS();

        $start_datetime = date("d-m-Y H:i:s");
        $sms_counter = array('sms_sent' => 0, 'sms_failed' => 0);

        if (!empty($tempDetails)) {

            foreach ($tempDetails as $customer_data) {
                if (!empty($customer_data['user_mobile'])) {

                    //                    $mobile_no = "9289767308";
                    $mobile_no = $customer_data['user_mobile'];
                    $return_array = $this->SMSModel->sendSMS($mobile_no, 1);

                    if ($return_array['status'] == 1) {
                        $sms_counter['sms_sent'] = $sms_counter['sms_sent'] + 1;
                    } else {
                        $sms_counter['sms_failed'] = $sms_counter['sms_failed'] + 1;
                    }
                }
            }
            $email_data = array();
            $email_data['email'] = $this->cron_notification_email;
            $email_data['subject'] = "PROD-Not Contactable Customer SMS - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "sms_sent=" . $sms_counter['sms_sent'] . " | sms_failed=" . $sms_counter['sms_failed'] . " <br/> Campaign Name : CRONSMSNC" . date("Ymd");

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99, $this->cron_notification_cc_email);

            echo "sms_sent=" . $sms_counter['sms_sent'] . " | sms_failed=" . $sms_counter['sms_failed'];
        } else {
            echo "No Data";
        }


        if (!empty($cron_insert_id)) {
            $this->SMSModel->update_cron_logs($cron_insert_id, $sms_counter['sms_sent'], $sms_counter['sms_failed']);
        }
    }

    public function repaymentReminder0DaySMS() { //Normal Email Template
        $cron_name = "repaymentreminder0daysms";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->SMSModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->SMSModel->insert_cron_logs($cron_name);

        $day_counter = 0;

        $tempDetails = $this->SMSModel->getAllRepaymentReminderSMS(true, $day_counter);

        $start_datetime = date("d-m-Y H:i:s");
        $sms_counter = array('sms_sent' => 0, 'sms_failed' => 0);

        if (!empty($tempDetails)) {

            foreach ($tempDetails as $customer_data) {
                if (!empty($customer_data['loan_no'])) {

                    $lead_id = $customer_data['lead_id'];
                    $mobile_no = $customer_data['mobile'];
                    $alternate_mobile = $customer_data['alternate_mobile'];
                    //                    $mobile_no = "9289767308";
                    //                    $alternate_mobile = $customer_data['alternate_mobile'];

                    $request_data = array();
                    $request_data['customer_name'] = $customer_data['cust_full_name'];
                    $request_data['loan_no'] = $customer_data['loan_no'];
                    $request_data['due_amount'] = !empty($customer_data['loan_total_outstanding_amount']) ? $customer_data['loan_total_outstanding_amount'] : 0;
                    $request_data['gender'] = strtoupper($customer_data['gender']);

                    $return_array = $this->SMSModel->sendSMS($mobile_no, 3, $lead_id, $request_data);

                    if ($return_array['status'] == 1) {
                        $sms_counter['sms_sent'] = $sms_counter['sms_sent'] + 1;
                    } else {
                        $sms_counter['sms_failed'] = $sms_counter['sms_failed'] + 1;
                    }

                    if (!empty($alternate_mobile)) {

                        $return_array = $this->SMSModel->sendSMS($alternate_mobile, 3, $lead_id, $request_data);

                        if ($return_array['status'] == 1) {
                            $sms_counter['sms_sent'] = $sms_counter['sms_sent'] + 1;
                        } else {
                            $sms_counter['sms_failed'] = $sms_counter['sms_failed'] + 1;
                        }
                    }
                }
            }
            $email_data = array();
            $email_data['email'] = $this->cron_notification_email;
            $email_data['subject'] = "PROD-REPAYMENT $day_counter DAY REMINDER SMS - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "sms_sent=" . $sms_counter['sms_sent'] . " | sms_failed=" . $sms_counter['sms_failed'] . " <br/> Campaign Name : CRONSMSNC" . date("Ymd");

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99, $this->cron_notification_cc_email);

            echo "sms_sent=" . $sms_counter['sms_sent'] . " | sms_failed=" . $sms_counter['sms_failed'];
        } else {
            echo "No Data";
        }


        //        if (!empty($cron_insert_id)) {
        //            $this->SMSModel->update_cron_logs($cron_insert_id, $sms_counter['sms_sent'], $sms_counter['sms_failed']);
        //        }
    }

    public function repeatCustomerCloseLoanSMS() { //Normal Email Template
        $cron_name = "repeatcustomercloseloansms";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->SMSModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->SMSModel->insert_cron_logs($cron_name);

        $tempDetails = $this->SMSModel->getAllCloseLoanCustomerSMS();

        $start_datetime = date("d-m-Y H:i:s");
        $sms_counter = array('sms_sent' => 0, 'sms_failed' => 0);

        if (!empty($tempDetails)) {

            foreach ($tempDetails as $customer_data) {

                if (!empty($customer_data['user_mobile'])) {

                    $mobile_no = $customer_data['user_mobile'];
                    $return_array = $this->SMSModel->sendSMS($mobile_no, 2);

                    if ($return_array['status'] == 1) {
                        $sms_counter['sms_sent'] = $sms_counter['sms_sent'] + 1;
                    } else {
                        $sms_counter['sms_failed'] = $sms_counter['sms_failed'] + 1;
                    }
                }
            }

            $email_data = array();
            $email_data['email'] = $this->cron_notification_email;
            $email_data['subject'] = "PROD-Close Loan Repeat Customer SMS - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "sms_sent=" . $sms_counter['sms_sent'] . " | sms_failed=" . $sms_counter['sms_failed'] . " <br/> Campaign Name : CRONREPEATSMS";

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99, $this->cron_notification_cc_email);

            echo "sms_sent=" . $sms_counter['sms_sent'] . " | sms_failed=" . $sms_counter['sms_failed'];
        } else {
            echo "No Data";
        }


        if (!empty($cron_insert_id)) {
            $this->SMSModel->update_cron_logs($cron_insert_id, $sms_counter['sms_sent'], $sms_counter['sms_failed']);
        }
    }

    public function sendNotificationEvery15Min() { // Notification Every 15 Min
        // error_reporting(E_ALL);
        // ini_set('display_errors', 1);
        $cron_name = "repeatcustomercloseloansms";

        require(COMPONENT_PATH . 'CommonComponent.php');
        $CommonComponent = new CommonComponent();

        $current_datetime = date('Y-m-d H:i:s', strtotime('-5 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->SMSModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        // $cron_insert_id = $this->SMSModel->insert_cron_logs($cron_name);

        $tempDetails = $this->SMSModel->getIncopleteJourneyData();

        $start_datetime = date("d-m-Y H:i:s");
        $sms_counter = array('sms_sent' => 0, 'sms_failed' => 0);
        $mobile_array = array();

        if (!empty($tempDetails)) {


            foreach ($tempDetails as $customer_data) {

                if (!empty($customer_data['mobile'])) {

                    $mobile_no = $customer_data['mobile'];
                    $lead_id = $customer_data['lead_id'];
                    //$mobile_no = "9319062592";

                    $sms_input_data['mobile'] = $mobile_no;

                    // $sms_input_data['name'] = "Aashu";
                    // $sms_input_data['otp'] = 1234;
                    // $sms_input_data['repayment_amount'] = 1234;
                    // $sms_input_data['repayment_date'] = "1234";
                    // $sms_input_data['loan_no'] = "121211ssdwsd";

                    $return_array = $CommonComponent->payday_sms_api(3, $lead_id, $sms_input_data);
                    $return_array = json_decode($return_array, true);

                    // $return_whatsApp = $CommonComponent->call_whatsapp_api(2, $lead_id, $sms_input_data);
                    // $return_whatsApp = json_decode($return_whatsApp, true);

                    if ($return_array['status'] == "OK") {
                        $sms_counter['sms_sent'] = $sms_counter['sms_sent'] + 1;
                        $mobile_array[] = $mobile_no;
                    } else {
                        $sms_counter['sms_failed'] = $sms_counter['sms_failed'] + 1;
                    }

                    // if ($return_whatsApp['status'] == 1) {
                    //     $sms_counter['whatsApp_sent'] = $sms_counter['whatsApp_sent'] + 1;
                    //     $mobile_array[] = $mobile_no;
                    // } else {
                    //     $sms_counter['whatsApp_failed'] = $sms_counter['whatsApp_failed'] + 1;
                    // }
                }
            }

            $email_data = array();
            $email_data['email'] = $this->cron_notification_email;
            $email_data['subject'] = "PROD Send Notification To the Customer - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "sms_sent=" . $sms_counter['sms_sent'] . " | sms_failed=" . $sms_counter['sms_failed'] . " \n";
            $email_data['message'] .= "Mobile No : " . implode(",", $mobile_array);

            $this->middlewareEmail($this->cron_notification_email, $email_data['subject'], $email_data['message'], '', 99, $this->cron_notification_cc_email);

            echo "sms_sent=" . $sms_counter['sms_sent'] . " | sms_failed=" . $sms_counter['sms_failed'];
        } else {
            echo "No Data";
        }


        if (!empty($cron_insert_id)) {
            $this->SMSModel->update_cron_logs($cron_insert_id, $sms_counter['sms_sent'], $sms_counter['sms_failed']);
        }
    }

    public function middlewareEmail($email, $subject, $message, $bcc_email = "", $email_type_id = 99, $cc_email = "", $reply_to = "") {
        $status = 0;
        $error = "";
        $provider_name = "";
        require_once(COMPONENT_PATH . 'includes/functions.inc.php');

        if (empty($email) || empty($subject) || empty($message)) {
            $error = "Please check email id, subject and message when sent email";
        } else {

            $to_email = $email;
            $from_email = "info@salarywalle.com";

            $return_array = common_send_email($to_email, $subject, $message, $bcc_email, $cc_email, $from_email, $reply_to, "", "", "");

            // $return_array = lw_send_email($to_email, $subject, $message, $bcc_email, $cc_email, $from_email, $reply_to);
            $status = $return_array['status'];
            $error = $return_array['error'];

            $insert_log_array = array();
            $insert_log_array['email_provider'] = $provider_name;
            $insert_log_array['email_type_id'] = $email_type_id;
            $insert_log_array['email_address'] = $email;
            $insert_log_array['email_content'] = addslashes($message);
            $insert_log_array['email_api_status_id'] = $status;
            $insert_log_array['email_errors'] = $error;
            $insert_log_array['email_created_on'] = date("Y-m-d H:i:s");

            $this->SMSModel->emaillog_insert($insert_log_array);

            $return_array = array("status" => $status, "error" => $error);

            return $return_array;
        }
    }
}
