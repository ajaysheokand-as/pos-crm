<?php

// defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class IOSMasterController extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Task_Model', 'Tasks');
        date_default_timezone_set('Asia/Kolkata');
        define('created_on', date('Y-m-d H:i:s'));
    }

    //*********** Api for Get States *************//
    public function qdeAppMasterAPI_post() {
        $input_data = file_get_contents("php://input");
        $post = $this->security->xss_clean(json_decode($input_data, true));
        if ($input_data) {
            $post = $this->security->xss_clean(json_decode($input_data, true));
        } else {
            $post = $this->security->xss_clean($_POST);
        }

        $headers = $this->input->request_headers();
        $token = $this->_token();
        $header_validation = (($headers['Accept'] == "application/json") && ($token['token_Leads'] == base64_decode($headers['Auth'])));

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $header_validation) {
            if ($post['apiname'] == 'getstate') {

                $num_rows = $this->MasterModel->getStateData();
                return json_encode($this->response(['Status' => 1, 'Message' => 'Data found', 'data' => $num_rows], REST_Controller::HTTP_OK));
            } else if ($post['apiname'] == 'getcity') {

                $this->form_validation->set_data($post);
                $this->form_validation->set_rules("apiname", "Enter Api Name", "required|trim");
                $this->form_validation->set_rules("id", "Enter City Id", "required|trim");
                if ($this->form_validation->run() == FALSE) {
                    json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
                } else {

                    $state_id = $post['id'];
                    $num_rows = $this->MasterModel->getCityData($state_id);
                    return json_encode($this->response(['Status' => 1, 'Message' => 'Data found', 'data' => $num_rows], REST_Controller::HTTP_OK));
                }
            } else if ($post['apiname'] == 'getpincode') {
                $this->form_validation->set_data($post);
                $this->form_validation->set_rules("apiname", "Enter Api Name", "required|trim");
                $this->form_validation->set_rules("id", "Enter City Id", "required|trim");
                if ($this->form_validation->run() == FALSE) {
                    return json_encode($this->response(['Status' => 0, 'Message' => validation_errors()], REST_Controller::HTTP_OK));
                } else {
                    $city_id = $post['id'];
                    $num_rows = $this->MasterModel->getPincode($city_id);
                    return json_encode($this->response(['Status' => 1, 'Message' => 'Data found', 'data' => $num_rows], REST_Controller::HTTP_OK));
                }
            } else if ($post['apiname'] == 'getpupposeofloan') {

                $num_rows = $this->MasterModel->getPurposeOfLoan();

                return json_encode($this->response(['Status' => 1, 'Message' => 'Data found', 'data' => $num_rows], REST_Controller::HTTP_OK));
            }
        } else {
            return json_encode($this->response(['Status' => 0, 'Message' => 'Request Method Post Failed.'], REST_Controller::HTTP_OK));
        }
    }

}

?>
