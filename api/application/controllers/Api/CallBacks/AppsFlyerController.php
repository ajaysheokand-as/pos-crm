<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class AppsFlyerController extends REST_Controller {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
        $this->load->model('Task_Model', 'Task');
    }

    public function webhookAppsFlyer_post() {
        // error_reporting(E_ALL);
        // ini_set("display_errors", 1);
        $input_data = file_get_contents("php://input");

        $post = $this->security->xss_clean(json_decode($input_data, true));

        $api_status_id = 0;
        $api_errors = "";
        $response_array = array();
        $api_response_datetime = date('Y-m-d H:i:s');

        try {

            $profile_id = !empty($post['customer_user_id']) ? $post['customer_user_id'] : 0;
            $utm_campaign = !empty($post['campaign']) ? trim(strtoupper($post['campaign'])) : "ORGANIC";
            $utm_source = !empty($post['media_source']) ? trim(strtoupper($post['media_source'])) : "ORGANIC";
            $utm_medium = !empty($post['af_channel']) ? trim(strtoupper($post['af_channel'])) : "ORGANIC";
            $af_prt = !empty($post['af_prt']) ? trim(strtoupper($post['af_prt'])) : "ORGANIC";
            $af_siteid = !empty($post['af_siteid']) ? $post['af_siteid'] : $utm_medium;
            $appsflyer_id = !empty($post['appsflyer_id']) ? $post['appsflyer_id'] : null;
            $event_name = !empty($post['event_name']) ? $post['event_name'] : null;
            $platform = !empty($post['platform']) ? $post['platform'] : null;

            if (empty($profile_id)) {
                throw new Exception("Customer profile id not found.");
            }

            if (!in_array(strtolower($event_name), array("login", "otp_verify", "pancard_verification", "resent_otp", "send_otp", "loan_quotation_decision", "generate_loan_quote", "eligibility_success", "eligibility_failed", "application_submit", "loan_disbursed", "install"))) {
                throw new Exception("Event not allowed.");
            }

            $api_status_id = 1;
            $return_message = "AppsFlyer saved successfully.";
        } catch (Exception $ex) {
            $api_status_id = 2;
            $api_errors = $ex->getMessage();
            $return_message = $api_errors;
        }

        $response_array['Status'] = $api_status_id;
        $response_array['Message'] = $return_message;

        $response_array['Status'] = 1;
        $response_array['Message'] = "Response saved successfully";

        if ($api_status_id == 1) {
            $insertData = array();
            $insertData['acaf_method_id'] = 1;
            $insertData['acaf_response'] = !empty($post) ? json_encode($post) : null;
            $insertData['acaf_api_status_id'] = $api_status_id;
            $insertData['acaf_errors'] = $api_errors;
            $insertData['acaf_response_datetime'] = $api_response_datetime;
            $insertData['acaf_profile_id'] = $profile_id;
            $insertData['acaf_appsflyer_id'] = $appsflyer_id;
            $insertData['acaf_platform_name'] = $platform;
            $insertData['acaf_event_name'] = $event_name;
            $insertData['acaf_utm_source'] = $utm_source;
            $insertData['acaf_utm_medium'] = $utm_medium;
            $insertData['acaf_utm_campaign'] = $utm_campaign;
            $insertData['acaf_af_prt'] = $af_prt;
            $insertData['acaf_af_siteid'] = $af_siteid;

            $log_insert_id = $this->Task->insert('api_callback_appsflyer', $insertData);

            $affiliateDetails = $this->Task->getAffIliateDetails();
            $affiliateData = $affiliateDetails['affiliate_data'];

            $internal_run_flag = true;

            foreach ($affiliateData as $affiliate_data) {
                if (strpos($utm_source, trim(strtoupper($affiliate_data['mmc_affiliate_mmp_pid_name']))) !== false || strpos($af_prt, trim(strtoupper($affiliate_data['mmc_affiliate_mmp_partner_name']))) !== false) {
                    $utm_medium = $utm_source;
                    $utm_source = $affiliate_data['mmc_name'];
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

            $update_flag = 0;

            if (!empty($profile_id) && !empty($appsflyer_id) && !empty($utm_source)) {

                $data_array = array(
                    'cp_utm_source' => $utm_source,
                    'cp_utm_medium' => $utm_medium,
                    'cp_utm_campaign' => $utm_campaign,
                    'cp_utm_term' => $af_siteid,
                    'cp_adjust_adid' => $appsflyer_id
                );

                $conditions = array('cp_id' => $profile_id);

                $update_flag = $this->Task->update($conditions, 'customer_profile', $data_array);
            }

            $updateArray = array();
            $updateArray['acaf_profile_utm_source'] = $data_array['cp_utm_source'];
            $updateArray['acaf_profile_utm_medium'] = $data_array['cp_utm_medium'];
            $updateArray['acaf_profile_utm_campaign'] = $data_array['cp_utm_campaign'];
            $updateArray['acaf_profile_update_flag'] = $update_flag;

            if (!empty($log_insert_id)) {
                $conditions = array('acaf_id' => $log_insert_id);
                $this->Task->update($conditions, 'api_callback_appsflyer', $updateArray);
            }
        }

        return json_encode($this->response($response_array, REST_Controller::HTTP_OK));
    }
}
