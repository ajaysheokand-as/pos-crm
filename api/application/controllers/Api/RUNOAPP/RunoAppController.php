S<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class RunoAppController extends REST_Controller {

    var $master_process = [
        "62563169ca1e240009a35407" => "Sanction Team",
        "62563198ca1e240009a35409" => "Collection Team",
        "625631a6ca1e240009a3540b" => "Pre-Collection Team",
        "625631aeca1e240009a3540d" => "customer care",
        "6274c25895fbdc0009829737" => "Sanction_Collection",
        "63776a1f3ae9412b35911f23" => "Recovery Team"
    ];

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
        $this->load->model('Task_Model', 'Task');
    }

    public function webhookRunoAddInteraction_post() {

        $input_data = file_get_contents("php://input");

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $api_status_id = 0;
        $api_errors = "";
        $api_process_name = "";
        $status_name = "";
        $status_rejection_name = "";
        $status_prospect_name = "";
        $runo_call_log_id = "";
        $lead_id = 0;
        $user_id = 0;
        $response_array = [];
        $api_request_datetime = date('Y-m-d H:i:s');

        try {

            $headers = $this->input->request_headers();
            $token = $this->_token();
            $header_validation = (($headers['Accept'] == "application/json") && ($token['token_runoapp'] == base64_decode($headers['Auth'])));

            if (empty($post)) {
                throw new Exception('API Request is empty.');
            }

            if ($header_validation) {
                throw new Exception('Missing Required Parameters');
            }



            foreach ($post['userFields'] as $arr) {
                if ($arr['name'] == 'Lead_id') {
                    $lead_id = $arr['value'];
                } else if ($arr['name'] == 'User_id') {
                    $user_id = $arr['value'];
                } else if ($arr['name'] == 'Status') {
                    $status_name = $arr['value'];
                } else if ($arr['name'] == 'REJECTION_REASONS') {
                    $status_rejection_name = $arr['value'];
                } else if ($arr['name'] == 'PROSPECT_REASONS') {
                    $status_prospect_name = $arr['value'];
                }
            }

            $mobile = str_replace('+91', '', $post['customer']['phoneNumber']);
            $req_status = $post['statusCode'];
            $req_message = $post['message'];
            $runo_call_log_id = !empty($post['callLogId']) ? $post['callLogId'] : "";
            $runo_call_log_id = (empty($runo_call_log_id) && !empty($post['callId'])) ? $post['callId'] : $runo_call_log_id;
            $call_remarks = !empty($post['notes']) ? $post['notes'] : "";
            $call_datetime = !empty($post['createdAt']) ? date('Y-m-d H:i:s', $post['createdAt']) : "";

            $api_process_name = $this->master_process[$post['processId']];

            if ($req_status == 1) {
                throw new Exception($req_message);
            }

            if (empty($lead_id)) {
                throw new Exception('Missing lead id.');
            }

            if (empty($mobile)) {
                throw new Exception('Missing mobile number.');
            }

            if (empty($api_process_name)) {
                throw new Exception('Invalid Process flow.');
            }

            if (empty($status_name)) {
                throw new Exception('Required interaction status.');
            }


            if (in_array($api_process_name, array("Pre-Collection Team", "Sanction_Collection", "Recovery Team", "Collection Team"))) {
                $tempDetails = $this->Task->get_master_collection_followup_status('', $status_name);

                if ($tempDetails['status'] != 1) {
                    throw new Exception('Invalid interaction status.[COL]');
                }

                $master_collection_followup = $tempDetails['data'];
                $call_status_id = array_key_first($master_collection_followup);

                $insert_flag = $this->Task->insertCollectionCallFollowup($lead_id, $call_status_id, $call_remarks, $user_id, $call_datetime, $runo_call_log_id, $mobile);

                if (!$insert_flag) {
                    throw new Exception('Unable to process the intraction.[COL]');
                }
            } else if (in_array($api_process_name, array("Sanction Team"))) {

                if ($status_name == 'PROSPECT') {
                    $status_name = $status_prospect_name;
                } else if ($status_name == 'REJECTION') {
                    $status_name = $status_rejection_name;
                }

                $tempDetails = $this->Task->get_master_sanction_followup_status('', $status_name);

                if ($tempDetails['status'] != 1) {
                    throw new Exception('Invalid interaction status.[SAN]');
                }

                $master_sanction_followup = $tempDetails['data'];
                $call_status_id = array_key_first($master_sanction_followup);

                $insert_flag = $this->Task->insertSanctionCallFollowup($lead_id, $call_status_id, $call_remarks, $user_id, $call_datetime, $runo_call_log_id, $mobile);

                if (!$insert_flag) {
                    throw new Exception('Unable to process the intraction.[SAN]');
                }
            }


            $api_status_id = 1;
            $return_message = "RUNO Interaction saved successfully.";
        } catch (Exception $ex) {
            $api_status_id = 2;
            $api_errors = $ex->getMessage();
            $return_message = $api_errors;
        }

        $response_array['Status'] = $api_status_id;
        $response_array['Message'] = $return_message;

        $insertData = array();
        $insertData['acrs_method_id'] = 1;
        $insertData['acrs_runo_call_log_id'] = $runo_call_log_id;
        $insertData['acrs_process_name'] = $api_process_name;
        $insertData['acrs_allocated_user_id'] = $user_id;
        $insertData['acrs_lead_id'] = $lead_id;
        $insertData['acrs_mobile'] = $mobile;
        $insertData['acrs_api_status_id'] = $api_status_id;
        $insertData['acrs_request'] = !empty($post) ? json_encode($post) : '';
        $insertData['acrs_response'] = !empty($response_array) ? json_encode($response_array) : '';
        $insertData['acrs_request_datetime'] = $api_request_datetime;
        $insertData['acrs_response_datetime'] = date('Y-m-d H:i:s');
        $insertData['acrs_errors'] = $api_errors;

        $this->db->insert('api_callback_runo_service', $insertData);

        return json_encode($this->response($response_array, REST_Controller::HTTP_OK));
    }

}
