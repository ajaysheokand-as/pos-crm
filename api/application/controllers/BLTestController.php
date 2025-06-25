<?php

class BLTestController extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function encry_test() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $encode = $this->encrypt->encode('My name is Alam.');
        $decode = $this->encrypt->decode('BzYCZVtnUDlSPw--');
        print_r($encode);
        echo '<br>';
        print_r('API dec : ' . $decode);
    }

    public function test() {
        print_r('Hello World.');
    }

    public function send_appointment() {
        $this->load->model('Task_Model');
        $this->Task_Model->email_appointment_schedule_with_link(6655, 'info@paisaonsalary.in', 'Ajay');
    }

    public function db_test() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $this->load->helper('commonfun');

        $request_array = array();

        $lead_id = 80;
        $request_array['pancard'] = 'BKGPG8542J';
        $request_array['lead_status_id'] = 1;
        $request_array['monthly_salary'] = 50000;
        $request_array['income_type'] = 1;

        $res = insertBharatDetails($lead_id, $request_array);

        echo '<pre>';
        print_r($res);
        exit;
    }

}
