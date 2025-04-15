<?php

defined('BASEPATH') or exit('No direct script access allowed');

class User_Activity_Model extends CI_Model {

    function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
    }

    public function get_user_allocation_data($user_id) {
        $result = array();
        $current_date = date("Y-m-d");

        $select = "SELECT * FROM user_lead_allocation_log WHERE ula_user_id='$user_id' AND DATE(ula_created_on) = '$current_date' AND ula_active=1 AND ula_deleted=0 ORDER BY ula_id DESC LIMIT 1";

        $userDetails = $this->db->query($select);

        if (!empty($userDetails->num_rows())) {
            $result = $userDetails->row_array();
        }

        return $result;
    }

    public function get_user_collection_allocation_data($user_id) {
        $result = array();

        $select = "SELECT * FROM user_collection_allocation_log WHERE uca_user_id='$user_id' AND uca_active=1 AND uca_deleted=0 ORDER BY uca_id DESC LIMIT 1";

        $userDetails = $this->db->query($select);

        if (!empty($userDetails->num_rows())) {
            $result = $userDetails->row_array();
        }

        return $result;
    }

    public function get_user_achieve_data($user_id, $type_id) {
        $result = array('status' => 0, 'data' => '');

        $select = "SELECT * FROM user_target_allocation_log WHERE uta_user_id='$user_id' AND uta_type_id='$type_id' AND DATE_FORMAT(uta_created_on, '%M-%y') = DATE_FORMAT(NOW(), '%M-%y') AND uta_active=1 AND uta_deleted=0 ORDER BY uta_id DESC LIMIT 1";

        $userDetails = $this->db->query($select);

        if ($userDetails->num_rows() > 0) {
            $result['data'] = $userDetails->row_array();
            $result['status'] = 1;
        }

        return $result;
    }

    public function get_user_collection_history_data($user_id, $type_id) {
        $result = array('status' => 0, 'data' => '');

        $select = "SELECT * FROM user_target_allocation_log WHERE uta_user_id='$user_id' AND uta_user_loan_total_cases >0 AND uta_type_id='$type_id' AND uta_active=1 AND uta_deleted=0 ORDER BY uta_created_on DESC";

        $collectionData = $this->db->query($select)->result_array();

        if (!empty($collectionData)) {

            $data = array();
            foreach ($collectionData as $value) {

                $rapay_month = date('F-Y', strtotime($value['uta_created_on']));

                $data[$rapay_month]['month'] = $rapay_month;
                $data[$rapay_month]['total_cases'] = $value['uta_user_loan_total_cases'];
                $data[$rapay_month]['closed_cases'] = $value['uta_user_loan_closed_cases'];
                $data[$rapay_month]['principle_amount'] = $value['uta_user_loan_total_principle'];
                $data[$rapay_month]['payable_amount'] = $value['uta_user_loan_payable_amount'];
                $data[$rapay_month]['principle_rcvd'] = $value['uta_user_loan_principle_received'];
                $data[$rapay_month]['interest_rcvd'] = $value['uta_user_loan_int_received'];
                $data[$rapay_month]['total_rcvd'] = $value['uta_user_loan_total_received'];
                $data[$rapay_month]['principle_outstanding'] = $value['uta_user_loan_principle_outstanding'];
                $data[$rapay_month]['interest_outstanding'] = $value['uta_user_loan_interest_outstanding'];
            }

            $result['status'] = 1;
            $result['data'] = $data;
        }

        return $result;
    }

    public function get_user_target_history_data($user_id, $type_id) {
        $result = array('status' => 0, 'data' => '', 'target_flag' => 0);

        $select = "SELECT * FROM user_target_allocation_log WHERE uta_user_id='$user_id' AND uta_type_id='$type_id' AND uta_active=1 AND uta_deleted=0 ORDER BY uta_created_on DESC";

        $collectionData = $this->db->query($select)->result_array();

        if (!empty($collectionData)) {

            $data = array();
            foreach ($collectionData as $value) {

                $rapay_month = date('F-Y', strtotime($value['uta_created_on']));

                $data[$rapay_month]['month'] = $rapay_month;
                $data[$rapay_month]['uta_type_id'] = $value['uta_type_id'];
                $data[$rapay_month]['uta_user_target_cases'] = $value['uta_user_target_cases'];
                $data[$rapay_month]['uta_user_achieve_cases'] = $value['uta_user_achieve_cases'];
                $data[$rapay_month]['uta_user_target_followups'] = $value['uta_user_target_followups'];
                $data[$rapay_month]['uta_user_achieve_followups'] = $value['uta_user_achieve_followups'];
                $data[$rapay_month]['uta_user_target_amount'] = $value['uta_user_target_amount'];
                $data[$rapay_month]['uta_user_achieve_amount'] = $value['uta_user_achieve_amount'];

                if ($rapay_month == date('F-Y')) {
                    $result['target_flag'] = 1;
                }
            }

            $result['status'] = 1;
            $result['data'] = $data;
        }

        return $result;
    }
}
