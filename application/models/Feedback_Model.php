<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Feedback_Model extends CI_Model {

    private $table = 'leads';
    private $table_lead_customer = 'lead_customer';
    private $table_state = 'master_state';
    private $table_city = 'master_city';
    private $table_data_source = 'master_data_source';

    function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
        define("date", date('Y-m-d'));
        define("timestamp", date('Y-m-d H:i:s'));
        define("ip", $this->input->ip_address());
        define("product_id", $_SESSION['isUserSession']['product_id']);
        define("company_id", $_SESSION['isUserSession']['company_id']);
        define("user_id", $_SESSION['isUserSession']['user_id']);
        define('agent', $_SESSION['isUserSession']['labels']);
    }

    public function index($conditions = null, $limit = null, $start = null, $search_input_array = array(), $where_in = array()) {
        if (!empty($search_input_array['slid'])) {
            $conditions['LD.lead_id'] = intval($search_input_array['slid']);
        }

        if (!empty($search_input_array['sdsid'])) {
            $conditions['LD.lead_data_source_id'] = intval($search_input_array['sdsid']);
        }

        if (!empty($search_input_array['ssid'])) {
            $conditions['LD.state_id'] = intval($search_input_array['ssid']);
        }

        if (!empty($search_input_array['scid'])) {
            $conditions['LD.city_id'] = intval($search_input_array['scid']);
        }

        if (!empty($search_input_array['sbid'])) {
            $conditions['LD.lead_branch_id'] = intval($search_input_array['sbid']);
        }

        if (!empty($search_input_array['sfd'])) {
            $conditions['LD.lead_entry_date >='] = date("Y-m-d", strtotime($search_input_array['sfd']));
        }

        if (!empty($search_input_array['sed'])) {
            $conditions['LD.lead_entry_date <='] = date("Y-m-d", strtotime($search_input_array['sed']));
        }

        if (!empty($search_input_array['sfn'])) {
            $conditions['C.first_name'] = $search_input_array['sfn'];
        }

        if (!empty($search_input_array['sln'])) {
            $conditions['LD.loan_no'] = $search_input_array['sln'];
        }

        if (!empty($search_input_array['smno'])) {
            $conditions['LD.mobile'] = $search_input_array['smno'];
        }

        if (!empty($search_input_array['semail'])) {
            $conditions['LD.email'] = $search_input_array['semail'];
        }

        if (!empty($search_input_array['span'])) {
            $conditions['LD.pancard'] = $search_input_array['span'];
        }

        if (!empty($search_input_array['sut'])) {
            $conditions['LD.user_type'] = $search_input_array['sut'];
        }

        $conditions['LD.lead_active'] = 1;
        $conditions['LD.lead_deleted'] = 0;
        $conditions['CFM.cfm_active'] = 1;
        $conditions['CFM.cfm_deleted'] = 0;

        $select = 'CFM.cfm_id, LD.lead_id, CFM.cfm_created_on as created_on, LD.loan_no, LD.customer_id, LD.application_no, LD.lead_reference_no, ';
        $select .= ' LD.lead_data_source_id, LD.first_name, C.middle_name, C.sur_name, CONCAT_WS(" ",LD.first_name, C.middle_name, C.sur_name) as cust_full_name, ';
        $select .= ' LD.source, LD.email, C.alternate_email, C.gender, LD.mobile, C.alternate_mobile, LD.obligations, LD.promocode, LD.purpose, ';
        $select .= ' ST.m_state_name, CT.m_city_name, LD.pincode, MS.status_name as status, MS.status_stage as stage, DS.data_source_code, ';
        $select .= ' LD.user_type, LD.pancard, CFM.cfm_remarks as remarks ';

        $this->db->select($select);

        $this->db->from('customer_feedback_main CFM');
        $this->db->join($this->table . ' LD', 'LD.lead_id = CFM.cfm_lead_id');
        $this->db->join($this->table_lead_customer . ' C', 'C.customer_lead_id = LD.lead_id ');
        $this->db->join($this->table_state . ' ST', 'ST.m_state_id = LD.state_id', 'left');
        $this->db->join($this->table_city . ' CT', 'CT.m_city_id = LD.city_id', 'left');
        $this->db->join($this->table_data_source . ' DS', 'DS.data_source_id = LD.lead_data_source_id', 'left');
        $this->db->join('master_status MS', 'MS.status_id = LD.lead_status_id');

        $this->db->distinct();

        if (!empty($limit)) {
            $this->db->limit($limit, $start);
        }

        if (!empty($conditions)) {
            $this->db->where($conditions);
        }

        $order_by_name = "CFM.cfm_id";
        $order_by_type = "DESC";

        $return = $this->db->order_by($order_by_name, $order_by_type)->get();
//        echo $this->db->last_query();
//        exit;
        return $return;
    }

    public function countLeads($conditions, $search_input_array = array(), $where_in = array()) {
        $total_rows = 0;

        if (!empty($search_input_array['slid'])) {
            $conditions['LD.lead_id'] = intval($search_input_array['slid']);
        }

        if (!empty($search_input_array['sdsid'])) {
            $conditions['LD.lead_data_source_id'] = intval($search_input_array['sdsid']);
        }

        if (!empty($search_input_array['ssid'])) {
            $conditions['LD.state_id'] = intval($search_input_array['ssid']);
        }

        if (!empty($search_input_array['scid'])) {
            $conditions['LD.city_id'] = intval($search_input_array['scid']);
        }

        if (!empty($search_input_array['sbid'])) {
            $conditions['LD.lead_branch_id'] = intval($search_input_array['sbid']);
        }

        if (!empty($search_input_array['sfd'])) {
            $conditions['LD.lead_entry_date >='] = date("Y-m-d", strtotime($search_input_array['sfd']));
        }

        if (!empty($search_input_array['sed'])) {
            $conditions['LD.lead_entry_date <='] = date("Y-m-d", strtotime($search_input_array['sed']));
        }

        if (!empty($search_input_array['sfn'])) {
            $conditions['C.first_name'] = $search_input_array['sfn'];
        }

        if (!empty($search_input_array['sln'])) {
            $conditions['LD.loan_no'] = $search_input_array['sln'];
        }

        if (!empty($search_input_array['smno'])) {
            $conditions['LD.mobile'] = $search_input_array['smno'];
        }

        if (!empty($search_input_array['semail'])) {
            $conditions['LD.email'] = $search_input_array['semail'];
        }

        if (!empty($search_input_array['span'])) {
            $conditions['LD.pancard'] = $search_input_array['span'];
        }

        if (!empty($search_input_array['sut'])) {
            $conditions['LD.user_type'] = $search_input_array['sut'];
        }

        $conditions['LD.lead_active'] = 1;
        $conditions['LD.lead_deleted'] = 0;
        $conditions['CFM.cfm_active'] = 1;
        $conditions['CFM.cfm_deleted'] = 0;

        $select = 'CFM.cfm_id, LD.lead_id, CFM.cfm_created_on as created_on, LD.loan_no, LD.customer_id, LD.application_no, LD.lead_reference_no, ';
        $select .= ' LD.lead_data_source_id, LD.first_name, C.middle_name, C.sur_name, CONCAT_WS(" ",LD.first_name, C.middle_name, C.sur_name) as cust_full_name, ';
        $select .= ' LD.source, LD.email, C.alternate_email, C.gender, LD.mobile, C.alternate_mobile, LD.obligations, LD.promocode, LD.purpose, ';
        $select .= ' ST.m_state_name, CT.m_city_name, LD.pincode, MS.status_name as status, MS.status_stage as stage, DS.data_source_code, DS.data_source_name, ';
        $select .= ' LD.user_type, LD.pancard, CFM.cfm_remarks as remarks ';

        $this->db->select($select);

        $this->db->from('customer_feedback_main CFM');
        $this->db->join($this->table . ' LD', 'LD.lead_id = CFM.cfm_lead_id');
        $this->db->join($this->table_lead_customer . ' C', 'C.customer_lead_id = LD.lead_id ');
        $this->db->join($this->table_state . ' ST', 'ST.m_state_id = LD.state_id', 'left');
        $this->db->join($this->table_city . ' CT', 'CT.m_city_id = LD.city_id', 'left');
        $this->db->join($this->table_data_source . ' DS', 'DS.data_source_id = LD.lead_data_source_id', 'left');
        $this->db->join('master_status MS', 'MS.status_id = LD.lead_status_id');

        if (!empty($conditions)) {
            $this->db->where($conditions);
        }

        $this->db->distinct();

        $leadsDetails = $this->db->get();

        if ($leadsDetails->num_rows() > 0) {
            $total_rows = $leadsDetails->num_rows();
        }

        return $total_rows;
    }

    public function getDataSourceList() {
        $source_array = array();
        $tempDetails = $this->db->select('data_source_id,data_source_name')->from('master_data_source')->get();
        foreach ($tempDetails->result_array() as $source_data) {
            $source_array[$source_data['data_source_id']] = $source_data['data_source_name'];
        }
        return $source_array;
    }

    public function get_customer_feedback($lead_id) {
        $result_array = array('status' => 0);

        $conditions['CFM.cfm_lead_id'] = $lead_id;
        $conditions['CFM.cfm_active'] = 1;
        $conditions['CFM.cfm_deleted'] = 0;

        $select = 'CFM.cfm_customer_name, CFM.cfm_email, CFM.cfm_mobile, ';
        $select .= ' CFM.cfm_lead_id as lead_id, CFM.cfm_remarks, CFMR.cfmr_id, MFQ.mfq_question, MFA.mfa_answer';

        $this->db->select($select);
        $this->db->from('customer_feedback_main CFM');
        $this->db->join('customer_feedback_main_response CFMR', 'CFMR.cfmr_main_id = CFM.cfm_id AND CFMR.cfmr_active = 1 AND CFMR.cfmr_deleted = 0');
        $this->db->join('master_feedback_questions MFQ', 'MFQ.mfq_id = CFMR.cfmr_question_id AND MFQ.mfq_active = 1 AND MFQ.mfq_deleted = 0');
        $this->db->join('master_feedback_answers MFA', 'MFA.mfa_id = CFMR.cfmr_answer_id AND MFA.mfa_active = 1 AND MFA.mfa_deleted = 0');

        $this->db->where($conditions);

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $result_array['status'] = 1;

            $temp = $tempDetails->row_array();
            $result_array['lead_id'] = $temp['lead_id'];
            $result_array['name'] = $temp['cfm_customer_name'];
            $result_array['email'] = $temp['cfm_email'];
            $result_array['mobile'] = $temp['cfm_mobile'];
            $result_array['remarks'] = $temp['cfm_remarks'];

            $data_array = array();
            foreach ($tempDetails->result_array() as $feedback) {
                $data['question'] = $feedback['mfq_question'];
                $data['answer'] = $feedback['mfa_answer'];

                $data_array[] = $data;
            }
            $result_array['data'] = $data_array;
        }

        return $result_array;
    }

}

?>
