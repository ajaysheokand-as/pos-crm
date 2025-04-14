<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class CronCollectionController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $this->load->model('CronJobs/CronCollection_Model', 'CollectionModel');
    }

    public function index() {
        
    }

    public function collectionLoanAllocation() {
//        error_reporting(E_ALL);
//        ini_set("display_errors", 1);
        echo "<pre>";

        $start_datetime = date("d-m-Y H:i:s");
        $message = "";
        $counter = array('assign_record' => 0, 'assign_failed' => 0);

        if (intval(date('H')) >= 9 && intval(date('H')) < 22) {
            
        } else {
            die("NO WORKING HOURS");
        }

        $cron_name = "collectionloanallocation";

        $assign_column_name = array('lead_pre_collection_executive_assign_user_id1', 'lead_pre_collection_executive_assign_user_id2', 'lead_collection_executive_assign_user_id1', 'lead_collection_executive_assign_user_id2');
        $assign_column_date_time = array('lead_pre_collection_executive_assign_datetime1', 'lead_pre_collection_executive_assign_datetime2', 'lead_collection_executive_assign_datetime1', 'lead_collection_executive_assign_datetime2');

        $current_datetime = date('Y-m-d H:i:s', strtotime('-10 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->CollectionModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

//        if (!empty($tempDetails['status'])) {
//            echo "Already Cron in prcoess";
//            die;
//        }

        $email_data = array();
        $email_data['email'] = $this->cron_notification_email;
        $email_data['subject'] = "PROD : COLLECTION LOAN ALLOCATION  - start time :" . $start_datetime;

//        $cron_insert_id = $this->CollectionModel->insert_cron_logs($cron_name);

        $master_user_lead = array();
        $dpd_type_id = $_GET['dpd_type'];
        $role_id = $_GET['role_id'];

        $tempDetails = $this->CollectionModel->getCollectionAssignmentCasesLead($dpd_type_id);
        $tempUsers = $this->CollectionModel->get_collection_users_lead_list($role_id, $dpd_type_id);

        print_r($tempDetails);
        print_r($tempUsers);
        if (!empty($tempDetails)) {

            $message = "Total Users = " . count($tempDetails) . '<br/>';

            $i = 0;
            foreach ($tempDetails as $dpd_type_id => $user_data) {

                $lead_id = $user_data['lead_id'];

                $tempUsers = $tempUsers[$dpd_type_id];

                if (!empty($tempUsers)) {

                    foreach ($tempUsers as $user_data) {
                        $user_id = $user_data['user_id'];
                        $user_active_flag = $user_data['user_active_flag'];
                        $user_total_count = intval($user_data['total_leads']);
                        $loan_amount_type = $user_data['loan_amount_type'];
                        $loan_dpd_categories = intval($user_data['loan_dpd_categories']);

                        if (empty($user_active_flag) || empty($loan_amount_type) || empty($loan_dpd_categories)) {
                            continue;
                        }

                        $lead_udpate_data = array();
                        $lead_udpate_data[$assign_column_name[$loan_dpd_categories]] = $user_id;
                        $lead_udpate_data[$assign_column_date_time[$loan_dpd_categories]] = date('Y-m-d H:i:s');

                        $update_flag = $this->CollectionModel->update('leads', ['lead_id' => $lead_id], $lead_udpate_data);

                        if ($update_flag) {

                            $user_total_count = $user_total_count + 1;

                            $lead_assigned_count = $lead_assigned_count + 1;

                            $counter['assign_record'] = $counter['assign_record'] + 1;

                            $master_user_lead[$user_key]['count'] = $user_total_count;
                            $master_user_lead[$user_key]['assigned'] = $lead_assigned_count;
                            break;
                        } else {
                            $counter['assign_failed'] = $counter['assign_failed'] + 1;
                        }
                    }
                }
            }


            if (!empty($tempDetails)) {

                $tempDetails = $tempDetails['data'];

                $message .= "Total Leads = " . count($tempDetails) . '<br/>';

                $message .= "Lead Allocation Details : " . '<br/>';
                $message .= "assign_record=" . $counter['assign_record'] . " | assign_failed=" . $counter['assign_failed'] . '<br/>';
                $message .= json_encode($master_user_lead) . '<br/>';
            } else {
                $message .= "No Data" . '<br/>';
            }
        } else {
            $message = "No User Data";
        }

        $email_data['subject'] .= " | end time : " . date("d-m-Y H:i:s");
        $email_data['message'] = $message;

//        if (!empty($cron_insert_id)) {
//            $this->CollectionModel->update_cron_logs($cron_insert_id, $counter['assign_record'], $counter['assign_failed']);
//        }
    }

    public function loanDefaulterat1Dayto30Day() {

        if (true) {

            $tempDetails = $this->CollectionModel->getAllDefaulterCollectionApps(1, 30);

            $start_datetime = date("d-m-Y H:i:s");

            $email_counter = array('update_record' => 0, 'update_failed' => 0);

            if (!empty($tempDetails)) {

                foreach ($tempDetails as $customer_data) {

                    if (!empty($customer_data['loan_id'])) {

                        $lead_id = $customer_data['lead_id'];
                        $loan_id = $customer_data['loan_id'];

                        $loan_udpate_data = array();
                        $loan_udpate_data['loan_recovery_status_id'] = 1; //collection pending

                        $update_flag = $this->CollectionModel->update('loan', ['loan_id' => $loan_id, 'lead_id' => $lead_id], $loan_udpate_data);

                        if ($update_flag) {
                            $email_counter['update_record'] = $email_counter['update_record'] + 1;
                        } else {
                            $email_counter['update_failed'] = $email_counter['update_failed'] + 1;
                        }
                    }
                }

                $email_data = array();
                $email_data['email'] = "shubham.agrawal@loanwalle.com";
                $email_data['subject'] = "PROD-COLLECTION PENDING 1-30 DAYS DEFAULT - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
                $email_data['message'] = "update_record=" . $email_counter['update_record'] . " | update_failed=" . $email_counter['update_failed'];

                $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

                echo "update_record=" . $email_counter['update_record'] . " | update_failed=" . $email_counter['update_failed'];
            } else {
                echo "No Data";
            }
        } else {
            $email_data = array();
            $email_data['email'] = "shubham.agrawal@loanwalle.com";
            $email_data['subject'] = "PROD-COLLECTION PENDING 1-30 DAYS DEFAULT - " . date("d-m-Y");
            $email_data['message'] = "Unauthorized";

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);
            echo "Unauthorized";
        }
    }

    public function loanDefaulterat31Dayto60Day() {

        if (true) {

            $tempDetails = $this->CollectionModel->getAllDefaulterCollectionApps(31, 30);

            $start_datetime = date("d-m-Y H:i:s");

            $email_counter = array('update_record' => 0, 'update_failed' => 0);

            if (!empty($tempDetails)) {

                foreach ($tempDetails as $customer_data) {

                    if (!empty($customer_data['loan_id'])) {

                        $lead_id = $customer_data['lead_id'];
                        $loan_id = $customer_data['loan_id'];

                        $loan_udpate_data = array();
                        $loan_udpate_data['loan_recovery_status_id'] = 2; //recovery pending

                        $update_flag = $this->CollectionModel->update('loan', ['loan_id' => $loan_id, 'lead_id' => $lead_id], $loan_udpate_data);

                        if ($update_flag) {
                            $email_counter['update_record'] = $email_counter['update_record'] + 1;
                        } else {
                            $email_counter['update_failed'] = $email_counter['update_failed'] + 1;
                        }
                    }
                }

                $email_data = array();
                $email_data['email'] = "shubham.agrawal@loanwalle.com";
                $email_data['subject'] = "PROD-RECOVERY PENDING 31-60 DAYS DEFAULT - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
                $email_data['message'] = "update_record=" . $email_counter['update_record'] . " | update_failed=" . $email_counter['update_failed'];

                $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

                echo "update_record=" . $email_counter['update_record'] . " | update_failed=" . $email_counter['update_failed'];
            } else {
                echo "No Data";
            }
        } else {
            $email_data = array();
            $email_data['email'] = "shubham.agrawal@loanwalle.com";
            $email_data['subject'] = "PROD-RECOVERY PENDING 31-60 DAYS DEFAULT - " . date("d-m-Y");
            $email_data['message'] = "Unauthorized";

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);
            echo "Unauthorized";
        }
    }

    public function loanDefaulterat60PlusDay() {

        if (true) {

            $tempDetails = $this->CollectionModel->getAllDefaulterCollectionApps(61, 30);

            $start_datetime = date("d-m-Y H:i:s");

            $email_counter = array('update_record' => 0, 'update_failed' => 0);

            if (!empty($tempDetails)) {

                foreach ($tempDetails as $customer_data) {

                    if (!empty($customer_data['loan_id'])) {

                        $lead_id = $customer_data['lead_id'];
                        $loan_id = $customer_data['loan_id'];

                        $loan_udpate_data = array();
                        $loan_udpate_data['loan_recovery_status_id'] = 3; //legal pending

                        $update_flag = $this->CollectionModel->update('loan', ['loan_id' => $loan_id, 'lead_id' => $lead_id], $loan_udpate_data);

                        if ($update_flag) {
                            $email_counter['update_record'] = $email_counter['update_record'] + 1;
                        } else {
                            $email_counter['update_failed'] = $email_counter['update_failed'] + 1;
                        }
                    }
                }

                $email_data = array();
                $email_data['email'] = "shubham.agrawal@loanwalle.com";
                $email_data['subject'] = "PROD-LEGAL 60 PLUS DAYS DEFAULT - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
                $email_data['message'] = "update_record=" . $email_counter['update_record'] . " | update_failed=" . $email_counter['update_failed'];

                $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

                echo "update_record=" . $email_counter['update_record'] . " | update_failed=" . $email_counter['update_failed'];
            } else {
                echo "No Data";
            }
        } else {
            $email_data = array();
            $email_data['email'] = "shubham.agrawal@loanwalle.com";
            $email_data['subject'] = "PROD-LEGAL 60 PLUS DAYS DEFAULT - " . date("d-m-Y");
            $email_data['message'] = "Unauthorized";

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);
            echo "Unauthorized";
        }
    }

    public function calculationAllLoans() {

        $time_close = intval(date("Hi"));

        // if ($time_close > 1235) {
        //     echo "Time exit";
        //     die;
        // }

        require_once (COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $tempDetails = $this->CollectionModel->getAllLoansApps();

        $start_datetime = date("d-m-Y H:i:s");
        $loan_no_array = array();

        if (!empty($tempDetails)) {

            foreach ($tempDetails as $customer_data) {

                if (!empty($customer_data['lead_id'])) {

                    $lead_id = $customer_data['lead_id'];
                    $loan_no_array[] = $customer_data['loan_no'];

                    $CommonComponent->get_loan_repayment_details($lead_id);
                }
            }

            $email_data = array();
            $email_data['email'] = "shubham.agrawal@loanwalle.com";
            $email_data['subject'] = "PROD-ALL CASES CALCULATION - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = count($loan_no_array);

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

            echo "Completed";
        } else {
            echo "No Data";
        }
    }

    public function calculationOpenCaseLoans() {
//        error_reporting(E_ALL);
//        ini_set("display_errors", 1);
        $cron_name = "calculationopencaseloans";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->CollectionModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->CollectionModel->insert_cron_logs($cron_name);

        require_once (COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $tempDetails = $this->CollectionModel->getAllOpenCaseLoansApps();

        $start_datetime = date("d-m-Y H:i:s");

        $loan_no_array = array();

        if (!empty($tempDetails)) {

            foreach ($tempDetails as $customer_data) {

                if (!empty($customer_data['lead_id'])) {

                    $lead_id = $customer_data['lead_id'];
                    $loan_no_array[] = $customer_data['loan_no'];

                    $CommonComponent->get_loan_repayment_details($lead_id);
                }
            }

            $email_data = array();
            $email_data['email'] = "shubham.agrawal@loanwalle.com";
            $email_data['subject'] = "PROD-$cron_name - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = count($loan_no_array);

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

            echo "Completed";
        } else {
            echo "No Data";
        }

        if (!empty($cron_insert_id)) {
            $this->CollectionModel->update_cron_logs($cron_insert_id, count($loan_no_array), 0);
        }
    }

    public function middlewareEmail($email, $subject, $message, $bcc_email = "", $email_type_id = 0, $cc_email = "", $reply_to = "") {
        $status = 0;
        $error = "";
        $provider_name = "";
        if (empty($email) || empty($subject) || empty($message)) {
            $error = "Please check email id, subject and message when sent email";
        } else {

            $to_email = $email;
            $from_email = "info@loanwalle.com";

            $provider_name = "MAILGUN";

            // $return_array = lw_send_email($to_email, $subject, $message, $bcc_email, $cc_email, $from_email, $reply_to);
            // $status = $return_array['status'];
            // $error = $return_array['error'];

            $insert_log_array = array();
            $insert_log_array['email_provider'] = $provider_name;
            $insert_log_array['email_type_id'] = $email_type_id;
            $insert_log_array['email_address'] = $email;
            $insert_log_array['email_content'] = addslashes($message);
            $insert_log_array['email_api_status_id'] = $status;
            $insert_log_array['email_errors'] = $error;
            $insert_log_array['email_created_on'] = date("Y-m-d H:i:s");

            $this->CollectionModel->emaillog_insert($insert_log_array);

            $return_array = array("status" => $status, "error" => $error);

            return $return_array;
        }
    }
}
