<?php

defined('BASEPATH') or exit('No direct script access allowed');

class CronMMPToolController extends CI_Controller {

    var $cron_notification_email = 'info@paisaonsalary.in';

    public function __construct() {
        parent::__construct();
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $this->load->model('CronJobs/CronMMPTool_Model', 'MMPToolModel');
    }

    public function updateAppflyerOrganicTagLeads() {

        $cron_name = "updateappflyerorganictagleads";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->MMPToolModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->MMPToolModel->insert_cron_logs($cron_name);

        $tempDetails = $this->MMPToolModel->getAllOrganicTagLeads();

        $start_datetime = date("d-m-Y H:i:s");
        $email_counter = array('email_sent' => 0, 'email_failed' => 0);

        if (!empty($tempDetails)) {
            $update_lead_ids = array();
            foreach ($tempDetails as $appFlyerData) {

                $lead_id = $appFlyerData['lead_id'];
                $status_name = $appFlyerData['status'];
                $status_stage = $appFlyerData['stage'];
                $status_id = $appFlyerData['lead_status_id'];
                $acaf_id = $appFlyerData['acaf_id'];

                $post = json_decode($appFlyerData['acaf_response'], true);

                if (!empty($post) && empty($update_lead_ids[$lead_id])) {

                    $utm_campaign = !empty($post['campaign']) ? trim(strtoupper($post['campaign'])) : "ORGANIC";
                    $utm_source = !empty($post['media_source']) ? trim(strtoupper($post['media_source'])) : "ORGANIC";
                    $utm_medium = !empty($post['af_channel']) ? trim(strtoupper($post['af_channel'])) : "ORGANIC";
                    $af_prt = !empty($post['af_prt']) ? trim(strtoupper($post['af_prt'])) : "ORGANIC";
                    $af_siteid = !empty($post['af_siteid']) ? $post['af_siteid'] : $utm_medium;
                    $appsflyer_id = !empty($post['appsflyer_id']) ? $post['appsflyer_id'] : null;
                    $event_name = !empty($post['event_name']) ? $post['event_name'] : null;
                    $platform = !empty($post['platform']) ? $post['platform'] : null;

                    $affiliateDetails = $this->MMPToolModel->getAffIliateDetails();

                    $affiliateDetails = $affiliateDetails['affiliate_data'];

                    $internal_run_flag = true;

                    foreach ($affiliateDetails as $affiliate_data) {
                        if (strpos($utm_source, trim(strtoupper($affiliate_data['mmc_affiliate_mmp_pid_name']))) !== false || strpos($af_prt, trim(strtoupper($affiliate_data['mmc_affiliate_mmp_partner_name']))) !== false) {
                            $utm_medium = $utm_source;
                            $utm_source = strtoupper($affiliate_data['mmc_name']);
                            if ($utm_source == 'VALUELEAF') {
                                $utm_medium = $af_siteid;
                            }
                            $internal_run_flag = false;
                            break;
                        }
                    }

                    if ($internal_run_flag == true) {
                        if (strpos($utm_source, 'FACEBOOK') !== false || strpos($af_prt, 'FACEBOOK') !== false || strpos($utm_source, 'RESTRICTED') !== false) {
                            $utm_source = "FACEBOOK";
                        } else if (strpos($utm_source, 'GOOGLE') !== false || strpos($af_prt, 'GOOGLE') !== false) {
                            $utm_source = "GOOGLE";
                        } else if (strpos($utm_source, 'ORGANIC') !== false) {
                            $utm_source = "ORGANIC";
                        } else {
                            $utm_source = "ORGANIC";
                        }
                    }

                    if (!empty($lead_id) && !empty($appsflyer_id) && !empty($utm_source) && $utm_source != "ORGANIC") {

                        $data_array = array(
                            'utm_source' => strtoupper($utm_source),
                            'utm_medium' => $utm_medium,
                            'utm_campaign' => strtoupper($utm_campaign),
                            'utm_term' => $af_siteid
                        );

                        $conditions = array('lead_id' => $lead_id);

                        $update_flag = $this->MMPToolModel->update('leads', $conditions, $data_array);

                        if ($update_flag) {
                            $update_lead_ids[$lead_id] = $utm_source . "|" . $acaf_id;
                            $email_counter['email_sent']++;

                            $lead_remark = "UTM Update by Internal Job:";

                            foreach ($data_array as $key => $value) {
                                $lead_remark .= "<br/>" . $key . " : " . $value;
                            }

                            $insert_lead_followup = array(
                                'lead_id' => $lead_id,
                                'user_id' => NULL,
                                'status' => $status_name,
                                'stage' => $status_stage,
                                'created_on' => date("Y-m-d H:i:s"),
                                'lead_followup_status_id' => $status_id,
                                'remarks' => $lead_remark
                            );

                            $this->MMPToolModel->insert('lead_followup', $insert_lead_followup);
                        } else {
                            $email_counter['email_failed']++;
                        }
                    }
                }
            }

            $email_data = array();
            $email_data['email'] = $this->cron_notification_email;
            $email_data['subject'] = "PROD $cron_name - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
            $email_data['message'] .= "<br/>" . json_encode($update_lead_ids);

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

            echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
        } else {
            echo "No Data";
        }


        if (!empty($cron_insert_id)) {
            $this->MMPToolModel->update_cron_logs($cron_insert_id, $email_counter['email_sent'], $email_counter['email_failed']);
        }
    }

    public function pushAppflyerDisbursalEventForLoan() {

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $cron_name = "pushappflyerdisbursaleventforloan";

        $current_datetime = date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime(date("Y-m-d H:i:s"))));
        $check_datetime = date('Y-m-d H:i:s', strtotime('+30 minutes', strtotime(date("Y-m-d H:i:s"))));

        $tempDetails = $this->MMPToolModel->get_cron_logs($cron_name, $current_datetime, $check_datetime);

        if (!empty($tempDetails['status'])) {
            echo "Already Cron in prcoess";
            die;
        }

        $cron_insert_id = $this->MMPToolModel->insert_cron_logs($cron_name);

        $tempDetails = $this->MMPToolModel->getDisbursalLeadsToPushEvent();

        $start_datetime = date("d-m-Y H:i:s");
        $email_counter = array('email_sent' => 0, 'email_failed' => 0);

        if (!empty($tempDetails)) {
            $update_lead_ids = array();
            foreach ($tempDetails as $appFlyerData) {

                $lead_id = $appFlyerData['lead_id'];

                if (!empty($lead_id) && empty($update_lead_ids[$lead_id])) {

                    $eventReturnArray = $CommonComponent->payday_appsflyer_campaign_api_call("EVENT_PUSH_CALL", $lead_id, array('event_type_id' => 4));

                    $loan_mmp_event_push_flag = 2;

                    if ($eventReturnArray['status'] == 1) {
                        $loan_mmp_event_push_flag = 1;
                    }

                    $data_array = array(
                        'loan_mmp_event_push_flag' => $loan_mmp_event_push_flag
                    );

                    $conditions = array('lead_id' => $lead_id);

                    $update_flag = $this->MMPToolModel->update('loan', $conditions, $data_array);

                    if ($update_flag) {
                        $update_lead_ids[$lead_id] = $lead_id;
                        $email_counter['email_sent']++;
                    } else {
                        $email_counter['email_failed']++;
                    }
                }
            }

            $email_data = array();
            $email_data['email'] = $this->cron_notification_email;
            $email_data['subject'] = "PROD $cron_name - start time :" . $start_datetime . " | end time : " . date("d-m-Y H:i:s");
            $email_data['message'] = "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
            $email_data['message'] .= "<br/>" . json_encode($update_lead_ids);

            $this->middlewareEmail($email_data['email'], $email_data['subject'], $email_data['message'], '', 99);

            echo "email_sent=" . $email_counter['email_sent'] . " | email_failed=" . $email_counter['email_failed'];
        } else {
            echo "No Data";
        }

        if (!empty($cron_insert_id)) {
            $this->MMPToolModel->update_cron_logs($cron_insert_id, $email_counter['email_sent'], $email_counter['email_failed']);
        }
    }

    // public function middlewareEmail($email, $subject, $message, $bcc_email = "", $email_type_id = 0, $cc_email = "", $reply_to = "") {
    //     $status = 0;
    //     $error = "";
    //     $provider_name = "";
    //     if (empty($email) || empty($subject) || empty($message)) {
    //         $error = "Please check email id, subject and message when sent email";
    //     } else {

    //         $to_email = $email;
    //         $from_email = INFO_EMAIL;

    //         $return_array = lw_send_email($to_email, $subject, $message, $bcc_email, $cc_email, $from_email, $reply_to);
    //         $status = $return_array['status'];
    //         $error = $return_array['error'];

    //         $insert_log_array = array();
    //         $insert_log_array['email_provider'] = $provider_name;
    //         $insert_log_array['email_type_id'] = $email_type_id;
    //         $insert_log_array['email_address'] = $email;
    //         $insert_log_array['email_content'] = addslashes($message);
    //         $insert_log_array['email_api_status_id'] = $status;
    //         $insert_log_array['email_errors'] = $error;
    //         $insert_log_array['email_created_on'] = date("Y-m-d H:i:s");

    //         $this->MMPToolModel->emaillog_insert($insert_log_array);

    //         $return_array = array("status" => $status, "error" => $error);

    //         return $return_array;
    //     }
    // }

    public function middlewareEmail($email, $subject, $message, $bcc_email = "", $email_type_id = 99, $cc_email = "", $reply_to = "") {
        $status = 0;
        $error = "";
        $provider_name = "";
        require_once(COMPONENT_PATH . 'includes/functions.inc.php');

        if (empty($email) || empty($subject) || empty($message)) {
            $error = "Please check email id, subject and message when sent email";
        } else {

            $to_email = $email;
            $from_email = "info@paisaonsalary.in";

            $return_array = common_send_email($to_email, $subject, $message, $bcc_email, $cc_email, $from_email, $reply_to, "");

            if (!empty($return_array) && $return_array['status'] == 1) {
                $status = $return_array['status'];
            } else {
                $return_array = json_decode($response, true);
                $error = isset($return_array['errors'][0]['message']) ? $return_array['errors'][0]['message'] : "Some error occourred.";
            }

            if ($status == 1) {
                $status = $status;
                $error = $return_array['error'];

                $insert_log_array = array();
                $insert_log_array['email_provider'] = $provider_name;
                $insert_log_array['email_type_id'] = $email_type_id;
                $insert_log_array['email_address'] = $email;
                $insert_log_array['email_content'] = addslashes($message);
                $insert_log_array['email_api_status_id'] = $status;
                $insert_log_array['email_errors'] = $error;
                $insert_log_array['email_created_on'] = date("Y-m-d H:i:s");

                $this->MMPToolModel->emaillog_insert($insert_log_array);
            }

            $return_array = array("status" => $status, "error" => $error);

            return $return_array;
        }
    }
}
