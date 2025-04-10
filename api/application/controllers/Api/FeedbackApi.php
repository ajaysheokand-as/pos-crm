<?php

// defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class FeedbackApi extends REST_Controller {

    public $api_status = 0;
    public $result_array = array('status' => 0);

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        date_default_timezone_set('Asia/Kolkata');
        ini_set('memory_limit', '-1');
        set_time_limit(0);
    }

    public function request($input_data) {

        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }
        return $post;
    }

    public function get_customer_details_post() {
        $post = $this->request(file_get_contents("php://input"));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim");

            if ($this->form_validation->run() == FALSE) {
                $this->result_array['message'] = strip_tags(validation_errors());
            } else {

                $lead_id = $this->encrypt->decode($post['lead_id']);

                if (!empty($lead_id)) {

                    $lead_details = $this->Tasks->get_lead_details($lead_id);

                    if (!empty($lead_details['status'])) {
                        $this->result_array['status'] = 1;
                        $this->result_array['data'] = $lead_details['data'];
                        $this->api_status = REST_Controller::HTTP_OK;
                    } else {
                        $this->result_array['message'] = "Application no not found.";
                        $this->api_status = REST_Controller::HTTP_OK;
                    }
                } else {
                    $this->result_array['message'] = "Application details not found.";
                    $this->api_status = REST_Controller::HTTP_OK;
                }
            }
        } else {
            $this->result_array['message'] = "Request Method Post Failed.";
            $this->api_status = REST_Controller::HTTP_OK;
        }
        return json_encode($this->response($this->result_array, $this->api_status));
    }

    public function save_customer_feedback_post() {
        $post = $this->request(file_get_contents("php://input"));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->form_validation->set_data($post);
            $this->form_validation->set_rules("lead_id", "Lead ID", "required|trim");
            $this->form_validation->set_rules("form_json", "Form Json", "required|trim");
            $this->form_validation->set_rules("customer_ip", "Customer Ip", "required|trim");
            $this->form_validation->set_rules("window", "Window", "required|trim");
            $this->form_validation->set_rules("agent", "Agent", "required|trim");
            $this->form_validation->set_rules("platform", "Platform", "required|trim");
            $this->form_validation->set_rules("remarks", "Remarks", "required|trim");

            if ($this->form_validation->run() == FALSE) {
                $this->result_array['error'] = strip_tags(validation_errors());
            } else {
                $lead_id = $this->encrypt->decode($post['lead_id']);
                $lead_details = $this->Tasks->get_lead_details($lead_id);

                if (!empty($lead_details['status'])) {
                    $insert_data_feedback_main = array();

                    $form_json = $post['form_json'];
                    $name = stripslashes($post['customer_name']);
                    $mobile = stripslashes($post['customer_mobile']);
                    $email = stripslashes($post['customer_email']);
                    $customer_ip = stripslashes($post['customer_ip']);
                    $window = stripslashes($post['window']);
                    $agent = stripslashes($post['agent']);
                    $platform = stripslashes($post['platform']);
                    $remarks = stripslashes($post['remarks']);

                    $insert_data_feedback_main['cfm_lead_id'] = $lead_id;
                    $insert_data_feedback_main['cfm_customer_name'] = $name;
                    $insert_data_feedback_main['cfm_email'] = $email;
                    $insert_data_feedback_main['cfm_mobile'] = $mobile;
                    $insert_data_feedback_main['cfm_form_json'] = $form_json;
                    $insert_data_feedback_main['cfm_ip'] = $customer_ip;
                    $insert_data_feedback_main['cfm_windows'] = $window;
                    $insert_data_feedback_main['cfm_agent'] = $agent;
                    $insert_data_feedback_main['cfm_plateform'] = $platform;
                    $insert_data_feedback_main['cfm_remarks'] = $remarks;
                    $insert_data_feedback_main['cfm_created_on'] = date('Y-m-d H:i:s');

                    $query_response_id = $this->Tasks->insert("customer_feedback_main", $insert_data_feedback_main);

                    if (!empty($query_response_id)) {
                        $question_answer_array = json_decode($form_json);

                        foreach ($question_answer_array as $key => $value) {
                            $insert_data_feedback_main_response = array();

                            $insert_data_feedback_main_response['cfmr_main_id'] = $query_response_id;
                            $insert_data_feedback_main_response['cfmr_question_id'] = $key;
                            $insert_data_feedback_main_response['cfmr_answer_id'] = $value;
                            $insert_data_feedback_main_response['cfmr_created_on'] = date('Y-m-d H:i:s');

                            $this->Tasks->insert("customer_feedback_main_response", $insert_data_feedback_main_response);
                        }
                        $this->result_array['status'] = 1;
                        $this->result_array['message'] = "Feedback saved successfully.";
                    } else {
                        $this->result_array['error'] = "Failed to save feedback. try again!";
                    }
                    $this->api_status = REST_Controller::HTTP_OK;
                } else {
                    $this->result_array['message'] = "Application no not found.";
                    $this->api_status = REST_Controller::HTTP_OK;
                }
            }
        } else {
            $this->result_array['message'] = "Request Method Post Failed.";
            $this->api_status = REST_Controller::HTTP_OK;
        }
        return json_encode($this->response($this->result_array, $this->api_status));
    }

}

?>
