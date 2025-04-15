<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Task_Model extends CI_Model {

    private $table = 'leads';
    private $table_lead_customer = 'lead_customer';
    private $table_customer_employment = 'customer_employment';
    private $table_credit_analysis_memo = 'credit_analysis_memo';
    private $table_state = 'master_state';
    private $table_city = 'master_city';
    private $table_data_source = 'master_data_source';
    private $table_loan = 'loan';
    private $table_users = 'users';
    private $table_disburse = 'tbl_disburse';
    private $table_collection = 'collection';

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

        if (!empty($search_input_array['sdu'])) {
            if (in_array($search_input_array['sdu'], [1])) { // 1 => docs avaialable 
                $conditions['C.customer_docs_available'] = 1;
            } else if (in_array($search_input_array['sdu'], [2])) { // 2 => Docs not avaialble
                $conditions['C.customer_docs_available!='] = 1;
            } else if (in_array($search_input_array['sdu'], [3])) { // 3 => All Docs
                unset($conditions['C.customer_docs_available']);
            }
        }

        $select = 'LD.lead_id, LD.lead_disbursal_recommend_datetime, LD.loan_no, LD.customer_id, LD.application_no, LD.lead_reference_no, LD.lead_data_source_id, LD.first_name, C.middle_name, C.sur_name, CONCAT_WS(" ",LD.first_name, C.middle_name, C.sur_name) as cust_full_name,';
        $select .= ' LD.email, C.alternate_email, C.gender, LD.mobile, C.alternate_mobile, LD.obligations, LD.promocode, LD.purpose, ';
        $select .= ' LD.user_type, LD.pancard, LD.loan_amount, LD.tenure, LD.cibil, CE.income_type, CE.salary_mode, CE.monthly_income, ';
        $select .= ' LD.source,LD.utm_source, DATE_FORMAT(C.dob,"%d-%m-%Y") AS dob, LD.state_id, LD.city_id, LD.lead_branch_id, ST.m_state_name, CT.m_city_name, LD.pincode, LD.status, LD.stage, LD.lead_status_id, LD.schedule_time, LD.created_on, ';
        $select .= ' LD.coordinates, LD.ip, LD.imei_no, LD.term_and_condition, LD.application_status, LD.lead_fi_residence_status_id,';
        $select .= ' LD.lead_fi_office_status_id,LD.scheduled_date,CAM.loan_recommended as sanctionedAmount, LD.lead_credit_assign_user_id, LD.lead_screener_assign_user_id, LD.lead_disbursal_assign_user_id,';
        $select .= ' user_screener.name as screenedBy,  DATE_FORMAT(LD.lead_screener_assign_datetime, "%d-%m-%Y %H:%i:%s") as screenedOn,';
        $select .= ' user_sanction.name as sanctionAssignTo,  DATE_FORMAT(LD.lead_credit_approve_datetime, "%d-%m-%Y %H:%i:%s") as sanctionedOn, ';
        $select .= ' L.loan_status_id, L.loan_recovery_status_id, LD.lead_disbursal_approve_datetime, L.loan_disbursement_trans_status_id, ';
        $select .= ' C.customer_religion_id, religion.religion_name, C.father_name, branch.m_branch_name, C.aadhar_no, C.customer_digital_ekyc_flag, C.customer_marital_status_id, C.customer_spouse_name, C.customer_spouse_occupation_id, C.customer_qualification_id,';
        $select .= ' C.customer_docs_available,';
        $select .= ' CAM.repayment_date, CAM.cam_sanction_letter_esgin_on';

        if (in_array($conditions["LD.stage"], array("S13", "S21", "S22", "S25"))) {
            $select .= ',user_disbursal.name as disbursalAssignTo, LD.lead_disbursal_recommend_datetime';
        }

        if ($this->uri->segment(1) == "preclosure") {
            $select .= ',CO.received_amount, collection_executive.name as collection_executive, CO.collection_executive_payment_created_on as payment_uploaded_on ';
        }

        $this->db->select($select);

        $this->db->from($this->table . ' LD');
        $this->db->join($this->table_lead_customer . ' C', 'C.customer_lead_id = LD.lead_id ', 'LEFT');
        $this->db->join($this->table_customer_employment . ' CE', 'CE.lead_id = LD.lead_id AND CE.emp_active=1 AND CE.emp_deleted=0', 'left');
        $this->db->join($this->table_credit_analysis_memo . ' CAM', 'CAM.lead_id = LD.lead_id AND CAM.cam_active=1 AND CAM.cam_deleted=0', 'left');
        $this->db->join($this->table_loan . ' L', 'L.lead_id = LD.lead_id AND L.loan_active=1 AND L.loan_deleted=0', 'left');
        $this->db->join($this->table_state . ' ST', 'ST.m_state_id = LD.state_id', 'left');
        $this->db->join($this->table_city . ' CT', 'CT.m_city_id = LD.city_id', 'left');
        $this->db->join('master_religion religion', 'religion.religion_id = C.customer_religion_id', 'left');
        $this->db->join('master_branch branch', 'branch.m_branch_id = LD.lead_branch_id', 'LEFT');
        $this->db->join($this->table_data_source . ' DS', 'DS.data_source_id = LD.lead_data_source_id', 'left');
        $this->db->join($this->table_users . ' user_screener', 'user_screener.user_id = LD.lead_screener_assign_user_id', 'left');
        $this->db->join($this->table_users . ' user_sanction', 'user_sanction.user_id = LD.lead_credit_assign_user_id', 'left');

        if (in_array($conditions["LD.stage"], array("S13", "S21", "S22", "S25"))) {
            $this->db->join($this->table_users . ' user_disbursal', 'user_disbursal.user_id = LD.lead_disbursal_assign_user_id', 'left');
        }

        if ($this->uri->segment(1) == "preclosure") {
            $this->db->join($this->table_collection . ' CO', 'CO.lead_id = LD.lead_id AND CO.collection_active=1', 'left');
            $this->db->join($this->table_users . ' collection_executive', 'collection_executive.user_id = CO.collection_executive_user_id', 'left');
        }

        if (in_array($this->uri->segment(1), ["visitrequest", "visitpending", "visitcompleted"])) {
            unset($conditions["LD.stage"]);
            $this->db->join('loan_collection_visit LCV', 'LCV.col_lead_id = LD.lead_id AND LCV.col_visit_active=1', 'left');
        }

        $this->db->distinct();

        if (!empty($limit)) {
            $this->db->limit($limit, $start);
        }

        if (!empty($conditions)) {
            $this->db->where($conditions);
        }

        if (!empty($where_in['LD.lead_status_id'])) {
            $this->db->where_in('LD.lead_status_id', $where_in['LD.lead_status_id']);
        }

        if (!empty($where_in['LD.lead_branch_id'])) {
            $this->db->where_in('LD.lead_branch_id', $where_in['LD.lead_branch_id']);
        }

        if (!empty($where_in['LD.state_id'])) {
            $this->db->where_in('LD.state_id', $where_in['LD.state_id']);
        }

        if (!empty($conditions['LD.stage']) && $conditions['LD.stage'] == 'S1' && !empty($where_in['LD.lead_branch_id_is_null'])) {
            $this->db->where("(LD.stage='" . $conditions['LD.stage'] . "' OR LD.lead_branch_id=" . $where_in['LD.lead_branch_id_is_null'] . ")");
        }

        $this->db->where('LD.lead_active', 1);
        $this->db->where('LD.lead_deleted', 0);

        $order_by_name = "LD.lead_id";
        $order_by_type = "DESC";

        if ($conditions['LD.stage'] == "S3") {
            $order_by_name = "LD.scheduled_date";
            $order_by_type = "DESC";
        } else if ($conditions['LD.stage'] == "S6") {
            $order_by_name = "LD.scheduled_date";
            $order_by_type = "DESC";
        } else if (in_array($conditions['LD.stage'], array("S12"))) {
            $order_by_name = "sanctionedOn";
            $order_by_type = "ASC";
        } else if (in_array($conditions['LD.stage'], array("S20"))) {
            $order_by_name = "CAM.cam_sanction_letter_esgin_on";
            $order_by_type = "ASC";
        } else if (in_array($conditions['LD.stage'], array("S13"))) {
            $order_by_name = "lead_disbursal_recommend_datetime";
            $order_by_type = "ASC";
        } else if (in_array($conditions['LD.stage'], array("S14"))) {
            $order_by_name = "lead_disbursal_approve_datetime";
            $order_by_type = "DESC";
        }

        if ($this->uri->segment(1) == "preclosure") {
            $order_by_name = "CO.collection_executive_payment_created_on";
            $order_by_type = "ASC";
        }

        $return = $this->db->order_by($order_by_name, $order_by_type)->get();
//        echo $this->db->last_query();
//        exit;
        return $return;
    }

    public function getLeadDetails($conditions) {
        $select = 'LD.lead_id, LD.loan_no, LD.customer_id, LD.application_no, LD.lead_reference_no, LD.lead_data_source_id, LD.first_name, C.middle_name, C.sur_name, CONCAT_WS(" ",LD.first_name, C.middle_name, C.sur_name) as cust_full_name, LD.check_cibil_status,';
        $select .= ' LD.email, C.alternate_email, C.gender, LD.mobile, C.alternate_mobile, LD.obligations, LD.promocode, LD.purpose, LD.lead_stp_flag, ';
        $select .= ' LD.user_type, LD.pancard, LD.loan_amount, LD.tenure, LD.cibil, CE.income_type, CE.salary_mode, CE.monthly_income, ';
        $select .= ' LD.source,LD.utm_source, LD.utm_campaign, DATE_FORMAT(C.dob,"%d-%m-%Y") AS dob, LD.state_id, LD.city_id, LD.lead_branch_id, ST.m_state_name, CT.m_city_name, LD.pincode, LD.status, LD.stage, LD.lead_status_id, LD.schedule_time, LD.created_on, ';
        $select .= ' LD.coordinates, LD.ip, LD.imei_no, LD.term_and_condition, LD.application_status, LD.lead_fi_residence_status_id,';
        $select .= ' LD.lead_fi_office_status_id,LD.scheduled_date,CAM.loan_recommended as sanctionedAmount, LD.lead_credit_assign_user_id, LD.lead_screener_assign_user_id, LD.lead_disbursal_assign_user_id,';
        $select .= ' user_screener.name as screenedBy,  DATE_FORMAT(LD.lead_screener_assign_datetime, "%d-%m-%Y %H:%i:%s") as screenedOn,';
        $select .= ' user_sanction.name as sanctionAssignTo,  DATE_FORMAT(LD.lead_credit_approve_datetime, "%d-%m-%Y %H:%i:%s") as sanctionedOn, ';
        $select .= ' L.loan_status_id, LD.lead_disbursal_approve_datetime, L.loan_disbursement_trans_status_id, ';
        $select .= ' C.customer_religion_id, religion.religion_name,branch.m_branch_name, ';
        $select .= ' MS.m_marital_status_name,';
        $select .= ' MO.m_occupation_name,';
        $select .= ' MQ.m_qualification_name,';
        $select .= ' C.father_name,C.pancard_verified_status,C.customer_digital_ekyc_flag, C.alternate_email_verified_status, C.customer_appointment_schedule, C.customer_appointment_remark,C.customer_marital_status_id, C.customer_spouse_name, C.customer_spouse_occupation_id, C.customer_qualification_id,';
        $select .= ' LD.lead_rejected_assign_user_id, LD.lead_rejected_reason_id,LD.lead_rejected_assign_counter,C.customer_bre_run_flag';

        $this->db->select($select);
        $this->db->distinct();
        $this->db->from($this->table . ' LD');
        $this->db->join($this->table_lead_customer . ' C', 'C.customer_lead_id = LD.lead_id ', 'LEFT');
        $this->db->join($this->table_customer_employment . ' CE', 'CE.lead_id = LD.lead_id AND CE.emp_active=1 AND CE.emp_deleted=0', 'left');
        $this->db->join($this->table_credit_analysis_memo . ' CAM', 'CAM.lead_id = LD.lead_id AND CAM.cam_active=1 AND CAM.cam_deleted=0', 'left');
        $this->db->join($this->table_loan . ' L', 'L.lead_id = LD.lead_id AND L.loan_active=1 AND L.loan_deleted=0', 'left');
        $this->db->join($this->table_state . ' ST', 'ST.m_state_id = LD.state_id', 'left');
        $this->db->join($this->table_city . ' CT', 'CT.m_city_id = LD.city_id', 'left');
        $this->db->join($this->table_data_source . ' DS', 'DS.data_source_id = LD.lead_data_source_id', 'left');
        $this->db->join('master_branch branch', 'branch.m_branch_id = LD.lead_branch_id', 'LEFT');
        $this->db->join('master_marital_status MS', 'MS.m_marital_status_id = C.customer_marital_status_id', 'LEFT');
        $this->db->join('master_occupation MO', 'MO.m_occupation_id = C.customer_spouse_occupation_id', 'LEFT');
        $this->db->join('master_qualification MQ', 'MQ.m_qualification_id = C.customer_qualification_id', 'LEFT');
        $this->db->join('master_religion religion', 'religion.religion_id = C.customer_religion_id', 'left');
        $this->db->join($this->table_users . ' user_screener', 'user_screener.user_id = LD.lead_screener_assign_user_id', 'left');
        $this->db->join($this->table_users . ' user_sanction', 'user_sanction.user_id = LD.lead_credit_assign_user_id', 'left');
        $this->db->join($this->table_users . ' user_disbursal', 'user_disbursal.user_id = LD.lead_disbursal_assign_user_id', 'left');
        $this->db->join($this->table_collection . ' CO', 'CO.lead_id = LD.lead_id ', 'left');

        if (!empty($conditions)) {
            $this->db->where($conditions);
        }

        $this->db->where('LD.lead_active', 1);
        $this->db->where('LD.lead_deleted', 0);

        $order_by_name = "LD.lead_id";
        $order_by_type = "DESC";

        $return = $this->db->order_by($order_by_name, $order_by_type)->get();
        return $return;
    }

    public function collection($conditions = null, $limit = null, $start = null) {
        $select = 'LD.lead_id, LD.loan_no, LD.customer_id, LD.application_no, LD.lead_reference_no, LD.lead_data_source_id, LD.first_name, C.middle_name, C.sur_name, CONCAT_WS(" ",LD.first_name, C.middle_name, C.sur_name) as cust_full_name,';
        $select .= ' LD.email, C.alternate_email, C.gender, LD.mobile, C.alternate_mobile, LD.obligations, LD.promocode, LD.purpose, ';

        $select .= ' LD.user_type, LD.pancard, LD.loan_amount, LD.tenure, LD.cibil, ';
        $select .= ' LD.source, C.dob, LD.state_id, LD.city_id, ST.m_state_name, CT.m_city_name, LD.pincode, LD.status, LD.stage, LD.lead_status_id, LD.schedule_time, LD.created_on, ';
        $select .= ' LD.coordinates, LD.ip, LD.imei_no, LD.term_and_condition, LD.application_status, LD.lead_fi_residence_status_id,';

        $select .= ' LD.lead_fi_office_status_id, DATE_FORMAT(LD.lead_screener_assign_datetime, "%d-%m-%Y %H:%i:%s") as screenedOn';
        $this->db->select($select);
        $this->db->from($this->table . ' LD');
        $this->db->join($this->table_state . ' ST', 'ST.m_state_id = LD.state_id', 'left');
        $this->db->join($this->table_city . ' CT', 'CT.m_city_id = LD.city_id', 'left');
        $this->db->join($this->table_data_source . ' DS', 'DS.data_source_id = LD.lead_data_source_id', 'left');
        $this->db->join($this->table_lead_customer . ' C', 'C.customer_lead_id = LD.lead_id ', 'left');

        $this->db->join($this->table_credit_analysis_memo . ' CAM', 'CAM.lead_id = LD.lead_id AND CAM.cam_active=1 AND CAM.cam_deleted=0', 'left');

        $this->db->join($this->table_collection . ' CO', 'CO.lead_id = LD.lead_id ', 'left');
        $this->db->distinct();

        if (!empty($limit)) {
            $this->db->limit($limit, $start);
        }
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }

        $return = $this->db->order_by('LD.lead_id', 'desc')->get();
        return $return;
    }

    public function pagination($totalcount, $per_page, $segment_upto) {
        if ($uri_segment == $uri_segment + 1) {
            $url = (base_url() . $this->uri->segment(1) . '/' . $this->uri->segment(2));
        } else {
            $url = (base_url() . $this->uri->segment(1));
        }
        $config = array();
        $config["base_url"] = $url;
        $config["total_rows"] = $totalcount;
        $config["per_page"] = $per_page;
        $config["uri_segment"] = $uri_segment;
        $config['full_tag_open'] = '<div class="pagging text-right"><nav><ul class="pagination">';
        $config['full_tag_close'] = '</ul></nav></div>';
        $config['num_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['num_tag_close'] = '</span></li>';
        $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close'] = '<span class="sr-only">(current)</span></span></li>';
        $config['next_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['next_tag_close'] = '<span aria-hidden="true"></span></span></li>';
        $config['prev_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['prev_tag_close'] = '</span></li>';
        $config['first_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['first_tag_close'] = '</span></li>';
        $config['last_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['last_tag_close'] = '</span></li>';
        $this->pagination->initialize($config);
        $config['links'] = $this->pagination->create_links();
        $config['page'] = ($this->uri->segment($segment_upto)) ? $this->uri->segment($segment_upto) : 0;
        return $config;
    }

    public function searchDataTable($post = null) {
        if (!empty($post['search_input']) && !empty($post['search_type'])) {
            $search_input = trim($post["search_input"]);
            $search_type = trim($post["search_type"]);

            if ($search_type == "lead_id") {
                $conditions .= " AND LD.lead_id =" . $search_input;
            } else if ($search_type == "application_no") {
                $conditions .= " AND LD.application_no ='" . $search_input . "'";
            } else if ($search_type == "loan_no") {
                $conditions .= " AND LD.loan_no ='" . $search_input . "'";
            } else if ($search_type == "first_name") {
                $conditions .= " AND LD.first_name ='" . $search_input . "'";
            } else if ($search_type == "mobile") {
                $conditions .= " AND LD.mobile ='" . $search_input . "'";
            } else if ($search_type == "pan") {
                $conditions .= " AND LD.pancard ='" . $search_input . "'";
            }
            return $conditions;
        }
    }

    public function customerPersonalDetails($conditions = null) {
        $select = 'LD.lead_id, LD.loan_amount, LD.lead_data_source_id, C.*, CE.*,C.city_id as res_city_id,C.state_id as res_state_id,CE.city_id as office_city_id,CE.state_id as office_state_id';
        $this->db->select($select);
        $this->db->from($this->table . ' LD');
        $this->db->join($this->table_state . ' ST', 'ST.m_state_id = LD.state_id', 'left');
        $this->db->join($this->table_city . ' CT', 'CT.m_city_id = LD.city_id', 'left');
        $this->db->join($this->table_data_source . ' DS', 'DS.data_source_id = LD.lead_data_source_id', 'left');
        $this->db->join($this->table_lead_customer . ' C', 'C.customer_lead_id = LD.lead_id ', 'left');
        $this->db->join($this->table_customer_employment . ' CE', 'CE.lead_id = LD.lead_id AND CE.emp_active=1 AND CE.emp_deleted=0', 'left');
        $this->db->distinct();

        if (!empty($conditions)) {
            $this->db->where($conditions);
        }

        $return = $this->db->get();
        return $return;
    }

    public function enquires($conditions = null, $limit = null, $start = null) {
        $this->db->select('enquiry.*');
        $this->db->from('customer_enquiry enquiry');
        $this->db->where($conditions);
        $this->db->limit($limit, $start);
        $return = $this->db->order_by('enquiry.cust_enquiry_id', 'desc')->get();
        return $return;
    }

    public function internalDedupe($lead_id = null) {
        $result = 0;

        if (!empty($lead_id)) {// && !empty($application_no)
            $sql = 'SELECT LD.lead_id, LD.pancard, C.first_name, C.middle_name, C.sur_name, C.dob, LD.mobile, C.alternate_mobile, C.email, C.alternate_email, C.aadhar_no, C.father_name';
            $sql .= " FROM leads LD";
            $sql .= " INNER JOIN lead_customer C  ON (C.customer_lead_id = LD.lead_id AND C.customer_active=1 AND C.customer_deleted=0)";
            $sql .= " WHERE LD.lead_id = $lead_id AND LD.lead_active=1 AND LD.lead_deleted=0";

            $leadsDetails = $this->db->query($sql); //->get()->row_array()

            if ($leadsDetails->num_rows() > 0) {

                $lead_data = $leadsDetails->row_array();
//                traceObject($lead_data);

                $first_name = !empty($lead_data['first_name']) ? addslashes(strtoupper($lead_data['first_name'])) : "";

                $dob = !empty($lead_data['dob']) ? $lead_data['dob'] : "";

                $pancard = !empty($lead_data['pancard']) ? strtoupper($lead_data['pancard']) : "";

                $mobile = !empty($lead_data['mobile']) ? $lead_data['mobile'] : "";

                $alternate_mobile = !empty($lead_data['alternate_mobile']) ? $lead_data['alternate_mobile'] : "";

                $email = !empty($lead_data['email']) ? strtoupper($lead_data['email']) : "";

                $alternate_email = !empty($lead_data['alternate_email']) ? strtoupper($lead_data['alternate_email']) : "";

                $aadhar_no = !empty($lead_data['aadhar_no']) ? strtoupper($lead_data['aadhar_no']) : "";

                $father_name = !empty($lead_data['father_name']) ? addslashes(strtoupper($lead_data['father_name'])) : "";

                $final_where = "LD.lead_id != $lead_id AND LD.lead_status_id!=7 AND LD.lead_active=1 AND LD.lead_deleted=0 AND (";

                $where = "";

                if (!empty($first_name) && !empty($dob)) {
                    $where .= "OR (C.first_name='$first_name' AND C.dob='$dob')";
                }

                if (!empty($pancard)) {
                    $where .= "OR C.pancard='$pancard'";
                }

                if (!empty($mobile)) {
                    $where .= "OR C.mobile='$mobile'";
                    $where .= "OR C.alternate_mobile='$mobile'";
                }

                if (!empty($alternate_mobile)) {
                    $where .= "OR C.mobile='$alternate_mobile'";
                    $where .= "OR C.alternate_mobile='$alternate_mobile'";
                }


                if (!empty($email)) {
                    $where .= "OR C.email='$email'";
                    $where .= "OR C.alternate_email='$email'";
                }

                if (!empty($alternate_email)) {
                    $where .= "OR C.email='$alternate_email'";
                    $where .= "OR C.alternate_email='$alternate_email'";
                }

                if (!empty($aadhar_no) && !empty($dob)) {
                    $where .= "OR (C.aadhar_no='$aadhar_no' AND C.dob='$dob')";
                }

                if (!empty($first_name) && !empty($father_name)) {
                    $where .= "OR (C.first_name='$first_name' AND C.father_name='$father_name')";
                }

                $where = ltrim($where, 'OR ');

                $final_where .= " " . $where . " )";

                $select = 'LD.lead_id, LD.product_id, LD.customer_id, LD.loan_no, LD.application_no, LD.lead_data_source_id, LD.first_name,';
                $select .= ' C.father_name, C.middle_name, C.sur_name, LD.email, C.alternate_email, C.gender, LD.mobile, C.alternate_mobile, LD.obligations, LD.promocode,';
                $select .= ' LD.purpose, LD.user_type, LD.pancard, C.aadhar_no, IF(CAM.loan_recommended>0,CAM.loan_recommended,LD.loan_amount) as loan_amount, LD.tenure, LD.cibil, CE.income_type, CE.salary_mode,';
                $select .= ' CE.monthly_income, LD.source, C.dob, LD.state_id, LD.city_id, ST.m_state_name as state, CT.m_city_name as city, LD.pincode, LD.status, LD.stage, LD.schedule_time,';
                $select .= ' LD.created_on as lead_initiated_date, LD.coordinates, LD.ip, LD.imei_no, LD.term_and_condition,';
                $select .= ' REJ.reason as reject_reason, REJU.name as rejected_by_name,CAM.disbursal_date';
                $this->db->where($final_where);
                $this->db->select($select);
                $this->db->distinct();
                $this->db->from($this->table . ' LD');
                $this->db->join($this->table_state . ' ST', 'ST.m_state_id = LD.state_id', 'left');
                $this->db->join($this->table_city . ' CT', 'CT.m_city_id = LD.city_id', 'left');
                $this->db->join($this->table_lead_customer . ' C', 'C.customer_lead_id = LD.lead_id AND C.customer_active=1 AND C.customer_deleted=0', 'left');
                $this->db->join($this->table_customer_employment . ' CE', 'CE.lead_id = LD.lead_id AND CE.emp_active=1 AND CE.emp_deleted=0', 'left');
                $this->db->join($this->table_credit_analysis_memo . ' CAM', 'CAM.lead_id = LD.lead_id AND CAM.cam_active=1 AND CAM.cam_deleted=0', 'left');
                $this->db->join($this->table_loan . ' L', 'L.lead_id = LD.lead_id AND L.loan_active=1 AND L.loan_deleted=0', 'left');
                $this->db->join('tbl_rejection_master REJ', 'REJ.id = LD.lead_rejected_reason_id', 'left');
                $this->db->join('users REJU', 'REJU.user_id = LD.lead_rejected_user_id', 'left');
                $result = $this->db->order_by('LD.created_on', 'DESC')->get();
//                echo $this->db->last_query();
            }
        }
        return $result;
    }

    public function holdleads($conditions = null, $limit = null, $start = null) {
        $sql = 'LD.lead_id, LD.customer_id, LD.application_no, LD.lead_data_source_id, L.loan_no, CAM.tenure, LD.first_name, C.middle_name, C.sur_name, CONCAT_WS(" ",LD.first_name, C.middle_name, C.sur_name) as cust_full_name,';
        $sql .= ' LD.email, C.dob, LD.mobile, LD.pancard, LD.user_type, LD.state_id, LD.city_id, ST.m_state_name, CT.m_city_name, LD.created_on, LD.source, LD.status, LD.lead_status_id, LD.ip, LD.coordinates, LD.imei_no, CE.salary_mode, CE.monthly_income,lead_fi_executive_residence_assign_user_id,lead_fi_executive_office_assign_user_id,';
        $sql .= ' LD.scheduled_date';
        $this->db->select($sql);
        $this->db->from($this->table . ' LD');
        $this->db->join($this->table_state . ' ST', 'ST.m_state_id = LD.state_id', 'left');
        $this->db->join($this->table_city . ' CT', 'CT.m_city_id = LD.city_id', 'left');
        $this->db->join($this->table_lead_customer . ' C', 'C.customer_lead_id = LD.lead_id AND C.customer_active=1 AND C.customer_deleted=0', 'left');
        $this->db->join($this->table_customer_employment . ' CE', 'CE.lead_id = LD.lead_id AND CE.emp_active=1 AND CE.emp_deleted=0', 'left');
        $this->db->join($this->table_credit_analysis_memo . ' CAM', 'CAM.lead_id = LD.lead_id AND CAM.cam_active=1 AND CAM.cam_deleted=0', 'left');
        $this->db->join($this->table_loan . ' L', 'L.lead_id = LD.lead_id AND L.loan_active=1 AND L.loan_deleted=0', 'left');
        $this->db->distinct();
        if ((!empty($limit) && $limit != null) && (!empty($start) && $start != null)) {
            $this->db->limit($limit, $start);
        }

        $this->db->where($conditions);
        return $this->db->order_by('LD.scheduled_date', 'desc')->get();
    }

    public function getState() {
        return $this->db->select('ST.m_state_id, ST.m_state_name, ST.m_state_code')
                        ->where(['ST.m_state_active' => 1, 'ST.m_state_deleted' => 0])
                        ->from('master_state as ST')
                        ->get();
    }

    public function getReligion() {
        return $this->db->select('religion_id, religion_name')
                        ->where(['religion_active' => 1, 'religion_deleted' => 0])
                        ->from('master_religion')
                        ->get();
    }

    public function getMaritalStatus() {
        return $this->db->select('m_marital_status_id, m_marital_status_name')
                        ->where(['m_marital_status_active' => 1, 'm_marital_status_deleted' => 0])
                        ->from('master_marital_status')
                        ->get();
    }
    public function getSpouseOccupation() {
        return $this->db->select('m_occupation_id, m_occupation_name')
                        ->where(['m_occupation_active' => 1, 'm_occupation_deleted' => 0])
                        ->from('master_occupation')
                        ->get();
    }
    public function getQualification() {
        return $this->db->select('m_qualification_id, m_qualification_name')
                        ->where(['m_qualification_active' => 1, 'm_qualification_deleted' => 0])
                        ->from('master_qualification')
                        ->get();
    }

    public function getPincode($city_id) {
        $data = 0;
        if (!empty($city_id)) {
            $data = $this->db->select('PIN.m_pincode_id, PIN.m_pincode_value, PIN.m_pincode_city_id')
                            ->where(['PIN.m_pincode_city_id' => $city_id, 'PIN.m_pincode_active' => 1, 'PIN.m_pincode_deleted' => 0])
                            ->from('master_pincode PIN')->get();
        }
        return $data;
    }

    public function getCity($state_id) {
        $data = 0;
        if (!empty($state_id)) {
            $data = $this->db->select('CT.m_city_id, CT.m_city_state_id, CT.m_city_name, CT.m_city_code')
                            ->where(['CT.m_city_state_id' => $state_id, 'CT.m_city_active' => 1, 'CT.m_city_deleted' => 0])
                            ->from('master_city CT')->get();
        }
        return $data;
    }

    public function getBranchDetails($city_id) {
        $result = array('status' => 0, 'branch_data' => array());

        if (!empty($city_id)) {
            $conditions = ['city.m_city_id' => $city_id];

            $select = 'city.m_city_id, city.m_city_state_id, city.m_city_name, branch.m_branch_id, branch.m_branch_name ';

            $this->db->select($select);
            $this->db->where($conditions);
            $this->db->from('master_city city');
            $this->db->join('master_branch branch', 'branch.m_branch_id = city.m_city_branch_id', 'LEFT');

            $temp_data = $this->db->get();
            if (!empty($temp_data->num_rows())) {
                $result['branch_data'] = $temp_data->row_array();
                $result['status'] = 1;
            }
        }
        return $result;
    }

    public function getLeadsCount($stage) {
        $result = $this->db->select("LD.lead_id")
                        ->where('LD.stage', $stage)
                        ->where('LD.lead_active', 1)
                        ->where('LD.lead_deleted', 0)
                        ->from($this->table . ' LD')->get();
        return $result->num_rows();
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

        if (!empty($search_input_array['sdu'])) {
            if (in_array($search_input_array['sdu'], [1])) { // 1 => docs avaialable 
                $conditions['C.customer_docs_available'] = 1;
            } else if (in_array($search_input_array['sdu'], [2])) { // 2 => Docs not avaialble
                $conditions['C.customer_docs_available!='] = 1;
            } else if (in_array($search_input_array['sdu'], [3])) { // 3 => All Docs
                unset($conditions['C.customer_docs_available']);
            }
        }

        $select = 'LD.lead_id ';
        if ($this->uri->segment(1) == "preclosure") {
            $select .= ' , CO.id';
        }

        $this->db->select($select);

        $this->db->from('leads LD');
        $this->db->join('lead_customer C', 'LD.lead_id = C.customer_lead_id', 'LEFT');
        $this->db->join('loan L', 'L.lead_id = C.customer_lead_id', 'LEFT');
        $this->db->join('credit_analysis_memo CAM', 'LD.lead_id = CAM.lead_id', 'LEFT');

        if ($this->uri->segment(1) == "preclosure") {
            $this->db->join($this->table_collection . ' CO', 'CO.lead_id = LD.lead_id AND CO.collection_active = 1 AND CO.collection_deleted = 0', 'left');
        }

        if (in_array($this->uri->segment(1), ["visitrequest", "visitpending", "visitcompleted"])) {
            unset($conditions["LD.stage"]);
            $this->db->join('loan_collection_visit LCV', 'LCV.col_lead_id = LD.lead_id AND LCV.col_visit_active=1', 'left');
        }


        if (!empty($conditions)) {
            $conditions['LD.lead_active'] = 1;
            $conditions['LD.lead_deleted'] = 0;
            $this->db->where($conditions);
        }

        if (!empty($where_in['LD.lead_status_id'])) {
            $this->db->where_in('LD.lead_status_id', $where_in['LD.lead_status_id']);
        }

        if (!empty($where_in['LD.lead_branch_id'])) {
            $this->db->where_in('LD.lead_branch_id', $where_in['LD.lead_branch_id']);
        }

        if (!empty($where_in['LD.state_id'])) {
            $this->db->where_in('LD.state_id', $where_in['LD.state_id']);
        }

        $this->db->distinct();

        $leadsDetails = $this->db->get();

        if ($leadsDetails->num_rows() > 0) {
            $total_rows = $leadsDetails->num_rows();
        }


        return $total_rows;
    }

    public function enquiriesCount($conditions) {
        return $this->db->select("enquiry.*")->where($conditions)->from('customer_enquiry enquiry')->get()->num_rows();
    }

    public function insert($data = null, $table = null) {
        return $this->db->insert($table, $data);
    }

    public function select($conditions = null, $data = null, $table = null) {
        return $this->db->select($data)->where($conditions)->from($table)->get();
    }

    public function update($conditions, $data) {
        return $this->db->where($conditions)->update($this->table, $data);
    }

    public function join_table($conditions = null, $data = null, $table1 = null, $table2 = null, $join2 = null, $table3 = null, $join3 = null, $table4 = null, $join4 = null) {
        return $this->db->select($data)
                        ->where($conditions)
                        ->from($table1)
                        ->join($table2, $join2, 'left')
                        ->join($table3, $join3, 'left')
                        ->join($table4, $join4, 'left')
                        ->distinct()
                        ->get();
    }

    public function join_two_table($data = null, $table1 = null, $table2 = null, $join2 = null) {
        return $this->db->select($data)
                        ->from($table1)
                        ->join($table2, $join2, 'left')
                        ->get();
    }

    public function join_two_table_with_where($conditions = null, $data = null, $table1 = null, $table2 = null, $join2 = null) {
        return $this->db->select($data)
                        ->where($conditions)
                        ->from($table1)
                        ->join($table2, $join2, 'left')
                        ->get();
    }

    public function join_two_table_with_where_order_by($conditions = null, $data = null, $table1 = null, $table2 = null, $join2 = null, $order_by_key, $order_by_val) {
        return $this->db->select($data)
                        ->where($conditions)
                        ->from($table1)
                        ->join($table2, $join2, 'left')
                        ->order_by($order_by_key, $order_by_val)
                        ->get();
    }

    public function three_join_table($conditions = null, $data = null, $table2 = null, $table3 = null) {
        return $this->db->select($data)
                        ->where($conditions)
                        ->from($this->table . ' LD')
                        ->join($this->table_disburse . " DS", 'DS.lead_id = LD.lead_id')
                        ->join($this->table_state . " ST", 'ST.state_id = LD.state_id')
                        ->get();
    }

    public function globalUpdate($conditions = null, $data = null, $table = null) {
        return $this->db->where($conditions)->update($table, $data);
    }

    public function updateLeads($conditions = null, $data = null, $table = null) {
        return $this->db->where($conditions)->update($table, $data);
    }

    public function delete($conditions = null, $table = null) {
        return $this->db->where($conditions)->delete($table);
    }

    public function import_lead_data($data) {
        return $this->db->insert($this->table, $data);
    }

    public function getProducts($product_id) {
        return $this->db->select('PD.product_code, PD.product_name, PD.source')->where('PD.product_id', $product_id)->from('tbl_product as PD')->get();
    }

    public function getProductsList() {
        $product_array = array();
        $tempDetails = $this->db->select('product_id,product_name')->from('tbl_product')->get();
        foreach ($tempDetails->result_array() as $product_data) {
            $product_array[$product_data['product_id']] = $product_data['product_name'];
        }
        return $product_array;
    }

    public function getDataSourceList() {
        $source_array = array();
        $tempDetails = $this->db->select('data_source_id,data_source_name')->from('master_data_source')->where(['data_source_active' => 1])->get();
        foreach ($tempDetails->result_array() as $source_data) {
            $source_array[$source_data['data_source_id']] = $source_data['data_source_name'];
        }
        return $source_array;
    }

    public function getCityList($state_id = '') {
        $city_array = array();

        $this->db->select('m_city_id,m_city_name');
        $this->db->from('master_city');
        $this->db->where(['m_city_active' => 1, 'm_city_deleted' => 0]);

        if (!empty($state_id)) {
            $this->db->where(['m_city_state_id' => $state_id]);
        }


        $tempDetails = $this->db->get();

        if (!empty($tempDetails->result_array())) {
            foreach ($tempDetails->result_array() as $city_data) {
                $city_array[$city_data['m_city_id']] = $city_data['m_city_name'];
            }
        }
        return $city_array;
    }

    public function getStateList() {
        $state_array = array();
        $tempDetails = $this->db->select('m_state_id,m_state_name')->from('master_state')->get();
        foreach ($tempDetails->result_array() as $state_data) {
            $state_array[$state_data['m_state_id']] = $state_data['m_state_name'];
        }
        return $state_array;
    }

    public function getBranchList() {
        $branch_array = array();
        $tempDetails = $this->db->select('m_branch_id, m_branch_name')->from('master_branch')->get();
        foreach ($tempDetails->result_array() as $branch_data) {
            $branch_array[$branch_data['m_branch_id']] = $branch_data['m_branch_name'];
        }
        return $branch_array;
    }

    public function getBankAccountStatusList() {
        $acc_status_array = array();
        $tempDetails = $this->db->select('bas_id,bas_name')->from('master_bank_account_status')->where(['bas_active' => 1, 'bas_deleted' => 0])->get();
        foreach ($tempDetails->result_array() as $temp_data) {
            $acc_status_array[$temp_data['bas_id']] = $temp_data['bas_name'];
        }
        return $acc_status_array;
    }

    public function getDisbursementBankList() {
        $disbursement_bank_array = array();
        $tempDetails = $this->db->select('*')->from('master_disbursement_banks')->where(['disb_bank_active' => 1, 'disb_bank_deleted' => 0])->get();

        foreach ($tempDetails->result_array() as $temp_data) {
            $disbursement_bank_array[$temp_data['disb_bank_id']] = $temp_data;
        }
        return $disbursement_bank_array;
    }

    public function getLeadStatus($type) {
        if ($type == 'PART PAYMENT') {
            $status = $type;
            $stage = 'S16';
        } else if ($type == 'CLOSED') {
            $status = $type;
            $stage = 'S17';
        } else if ($type == 'SETTLED') {
            $status = $type;
            $stage = 'S18';
        } else if ($type == 'WRITEOFF') {
            $status = $type;
            $stage = 'S19';
        } else {
            $status = '-';
            $stage = '-';
        }
        $data['status'] = $status;
        $data['stage'] = $stage;
        return $data;
    }

    public $count = 1;

    public function generateApplicationNo($lead_id) {
        $application_no = 0;
        if (!empty($lead_id)) {
            $conditions2 = ['lead_id' => $lead_id, 'application_no !=' => ''];
            $leadsDetails = $this->select($conditions2, 'lead_id, application_no', 'leads');
            if ($leadsDetails->num_rows() > 0) {
                $leadApp_no = $leadsDetails->row_array();
                $application_no = $leadApp_no['application_no'];
            } else {
                $conditions2 = ['P.company_id' => company_id, 'P.product_id' => product_id];
                $fetch2 = 'P.product_id, P.product_name, P.product_code';
                $sql2 = $this->select($conditions2, $fetch2, 'tbl_product P');
                $product = $sql2->row();

                $conditions3 = ['LD.lead_id' => $lead_id];
                $sql3 = $this->db->select("LD.city_id, CT.m_city_code")->where($conditions3)->from($this->table . ' LD')->join('master_city CT', 'LD.city_id = CT.m_city_id', 'left')->get();
                $query = $sql3->row();
                $leadsCount = $this->db->select('lead_id')->where('application_no !=', '')->from('leads')->get()->num_rows();
                $num1 = preg_replace('/[^0-9]/', '', $leadsCount) + $this->count;
                $zerocounts = '';
                for ($i = strlen('000000000'); $i > strlen($num1); $i--) {
                    $zerocounts = $zerocounts . '0';
                }
                $number = $zerocounts . '' . $num1;
                $application_no = 'AP' . $product->product_code . '' . $query->m_city_code . '' . $number; // NFPDNRL000000063  
            }
        }
        return $application_no;
    }

    public function generateLoanNo($lead_id) {



        $q = $this->db->select('L.loan_no')->where('L.loan_no !=', '')->from('loan L')->order_by('loan_id', 'desc')->limit(1)->get();
        $pre_loan = $q->row();

        $num1 = (int) filter_var($pre_loan->loan_no, FILTER_SANITIZE_NUMBER_INT);
        $num1 = $num1 + 1;

        $prefix_loan_no = "BLUAT";

        $envSet = ENVIRONMENT;

        if ($envSet == "production") {
            $prefix_loan_no = "BLPLZ";
        }

        $loan_no = $prefix_loan_no . str_pad(($num1), 11, "0", STR_PAD_LEFT); //16 chars

        return $loan_no;
    }

    public function generateReferenceCode($lead_id) {

        $sql = 'LD.lead_id, LD.first_name, C.middle_name, C.sur_name, LD.mobile, LD.pancard, LD.lead_reference_no';

        $this->db->select($sql);
        $this->db->from($this->table . ' LD');
        $this->db->join($this->table_lead_customer . ' C', 'C.customer_lead_id = LD.lead_id AND C.customer_active=1 AND C.customer_deleted=0', 'inner');
        $this->db->where(['LD.lead_id' => $lead_id]);
        $leadDetails = $this->db->get();

        $referenceID = "";

        if ($leadDetails->num_rows() > 0) {
            $leadDetails = $leadDetails->row_array();

            $first_name = $leadDetails['first_name'];
            $last_name = $leadDetails['sur_name'];
            $mobile = $leadDetails['mobile'];

            if (!empty($leadDetails['lead_reference_no'])) {

                $referenceID = $leadDetails['lead_reference_no'];
            } else {
                $code_mix = array($lead_id[rand(0, strlen($lead_id) - 1)], $first_name[rand(0, strlen($first_name) - 1)], $first_name[rand(0, strlen($first_name) - 1)], $last_name[rand(0, strlen($last_name) - 1)], $last_name[rand(0, strlen($last_name) - 1)], $mobile[rand(0, strlen($mobile) - 1)], $mobile[rand(0, strlen($mobile) - 1)]);

                shuffle($code_mix);

                $referenceID = "#BL";

                foreach ($code_mix as $each) {

                    $referenceID .= $each;
                }

                $referenceID = str_replace(" ", "X", $referenceID);

                $referenceID = strtoupper($referenceID);
            }
        }

        return $referenceID;
    }

    public function getResidenceDetails($lead_id) {
        $result = 0;
        if (!empty($lead_id)) {
            $conditions = ['LD.lead_id' => $lead_id];
            $select = 'C.aa_current_city_id,C.aa_current_state_id,C.state_id,C.city_id,C.current_house, C.current_locality, C.aadhar_no, C.current_landmark, C.aa_current_landmark, C.current_residence_since, C.current_residence_type, C.current_residing_withfamily, C.current_state, C.current_city, C.current_district, C.cr_residence_pincode, C.current_res_status, C.aa_same_as_current_address, C.aa_current_house, C.aa_current_locality, C.aa_current_state, C.aa_current_city, C.aa_current_district, C.aa_cr_residence_pincode, res_state.m_state_name as res_state, res_city.m_city_name as res_city, aadhar_state.m_state_name as aadhar_state, aadhar_city.m_city_name as aadhar_city';

            $this->db->select($select);
            $this->db->where($conditions);
            $this->db->from($this->table . ' LD');
            $this->db->join($this->table_lead_customer . ' C', 'C.customer_lead_id = LD.lead_id ', 'left');
            $this->db->join($this->table_state . ' res_state', 'res_state.m_state_id = C.state_id', 'left');
            $this->db->join($this->table_city . ' res_city', 'res_city.m_city_id = C.city_id', 'left');
            $this->db->join($this->table_state . ' aadhar_state', 'aadhar_state.m_state_id = C.aa_current_state_id', 'left');
            $this->db->join($this->table_city . ' aadhar_city', 'aadhar_city.m_city_id = C.aa_current_city_id', 'left');
            $this->db->join($this->table_customer_employment . ' CE', 'CE.lead_id = LD.lead_id AND CE.emp_active=1 AND CE.emp_deleted=0', 'left');
            $result = $this->db->get();
        }
        return $result;
    }

    public function getEmploymentDetails($lead_id) {
        $result = 0;
        if (!empty($lead_id)) {
            $conditions = ['LD.lead_id' => $lead_id];
            $select = 'CE.city_id,CE.state_id,CE.customer_id, CE.employer_name, CE.emp_state, CE.emp_city, CE.emp_district, CE.emp_pincode, CE.emp_house, CE.emp_street, CE.emp_landmark, CE.emp_residence_since, CE.presentServiceTenure, CE.emp_designation, CE.emp_department,CE.emp_work_mode,CE.emp_occupation_id, CE.emp_employer_type, CE.emp_website, CE.monthly_income, CE.salary_mode, CE.income_type, CE.industry, CE.sector, CE.salary_mode, CE.emp_status, CE.created_on, ST.m_state_name, CT.m_city_name, department.department_name,
            MO.m_occupation_name,MO.m_occupation_id';

            $this->db->select($select);
            $this->db->where($conditions);
            $this->db->from($this->table . ' LD');
            $this->db->join($this->table_lead_customer . ' C', 'C.customer_lead_id = LD.lead_id ', 'left');
            $this->db->join($this->table_customer_employment . ' CE', 'CE.lead_id = LD.lead_id AND CE.emp_active=1 AND CE.emp_deleted=0', 'left');
            $this->db->join($this->table_state . ' ST', 'ST.m_state_id = CE.state_id', 'left');
            $this->db->join($this->table_city . ' CT', 'CT.m_city_id = CE.city_id', 'left');
            $this->db->join('master_occupation MO', 'MO.m_occupation_id = CE.emp_occupation_id ', 'left');
            $this->db->join('master_department department', 'department.department_id = CE.emp_department ', 'left');
            $result = $this->db->get();
        }
        return $result;
    }

    public function getDepartmentMaster() {
        return $this->db->select($select)->where(['department_active' => 1, 'department_deleted' => 0])->from('master_department')->get()->result();
    }

    public function getEmpOccupation() {
        return $this->db->select('m_occupation_id, m_occupation_name')
                        ->where(['m_occupation_active' => 1, 'm_occupation_deleted' => 0])
                        ->from('master_occupation')
                        ->get()->result();
    }

    public function selectQuery($fetch, $where, $table) {
        return $this->db->select($fetch)->where($where)->get($table);
    }

    public function updateQuery($where, $table, $update) {
        $this->db->where($where)->update($table, $update);
    }

    public function joinTwoTable($fetch, $where, $table, $joinTableTwo, $joinEqualTo) {
        return $this->db->select($fetch)->where($where)->from($table)->join($joinTableTwo, $joinEqualTo)->get();
    }

    public function getMISData() {
        if (!empty($_SESSION['isUserSession']['user_id'])) {
            $data = $this->db->distinct()->select('recovery.*, recovery.payment_amount as total_paid, leads.name, leads.email, tb_states.state, leads.source, leads.status, loan.loan_no, users.name as recovery_by')
                    ->where('DATE(recovery.created_on)', currentDate)
                    ->where('recovery.PaymentVerify', 0)
                    ->from('recovery')
                    ->join('leads', 'leads.lead_id = recovery.lead_id')
                    ->join('credit', 'credit.lead_id = recovery.lead_id')
                    ->join('loan', 'loan.lead_id = recovery.lead_id')
                    ->join('tb_states', 'leads.state_id = tb_states.id')
                    ->join('users', 'users.user_id = recovery.recovery_by')
                    ->get();
            return $data;
        }
    }

    public function getRecoveryData($sql) {
        $data = '
                <div class="table-responsive">
                    <table data-order="[[0, "desc" ]]" class="table table-striped table-bordered table-hover" style="border: 1px solid #dde2eb">
                        <thead>
                            <tr>
                                <th class="whitespace"><b>#</b></th>
                                <th class="whitespace"><b>Loan&nbsp;No</b></th>
                                <th class="whitespace"><b>Remarks</b></th>
                                <th class="whitespace"><b>Payment&nbsp;Mode</b></th>
                                <th class="whitespace"><b>Payment&nbsp;Amount</b></th>
                                <th class="whitespace"><b>Discount</b></th>
                                <th class="whitespace"><b>Refund</b></th>
                                <th class="whitespace"><b>Refrence&nbsp;No</b></th>
                                <th class="whitespace"><b>Recovery&nbsp;Date</b></th>
                                <th class="whitespace"><b>Loan&nbsp;Status</b></th>
                                <th class="whitespace"><b>Payment&nbsp;Verification</b></th>
                                <th class="whitespace"><b>Payment Uploaded By</b></th>
                                <th class="whitespace"><b>Payment Uploaded On</b></th>
                                <th class="whitespace"><b>Payment Verified By</b></th>
                                <th class="whitespace"><b>Payment Verified On</b></th>
                                <th class="whitespace"><b>Action</b></th>
                            </tr>
                        </thead>
                	<tbody>';
        if ($sql->num_rows() > 0) {
            foreach ($sql->result() as $row) {
                if ($row->payment_verification == 1) {
                    $payment_verification = 'APPROVED';
                } else if ($row->payment_verification == 2) {
                    $payment_verification = 'REJECTED';
                } else {
                    $payment_verification = 'PENDING';
                }
                $repaymentstatus = $this->db->select('status_id, status_name')->where('status_id', $row->repayment_type)->from('master_status')->get()->row();

                $editBtn = "";
                $deleteBtn = "";
                $documentViewBtn = "";

                $date_of_recived = (!empty($row->date_of_recived) && $row->date_of_recived != '0000-00-00') ? date('d-m-Y', strtotime($row->date_of_recived)) : "-";

                $input = [
                    'id' => $row->id,
                    'received_amount' => $row->received_amount,
                    'refrence_no' => $row->refrence_no,
                    'discount' => $row->discount,
                    'refund' => $row->refund,
                    'date_of_recived' => $date_of_recived,
                    'payment_mode' => $row->payment_mode,
                    'payment_mode_id' => $row->payment_mode_id,
                    'repayment_type' => $row->repayment_type,
                ];
                $input = json_encode($input);

                if ($row->payment_verification == 0 && agent == "AC1") {
                    $editBtn = "&nbsp;<a class='btn btn-control btn-primary' data-toggle='collapse' data-target='#addRecoveryPayment' onclick='editsCoustomerPayment(" . $input . ")'><i class='fa fa-pencil'></i></a>";
                }

                if (!in_array($row->payment_mode_id, array(2)) && $row->payment_verification == 0 && ((agent == "CR2" && $row->collection_executive_user_id == user_id) || (agent == "CO1" && $row->collection_executive_user_id == user_id) || (agent == "AC1" && $row->collection_executive_user_id == user_id))) {
                    $deleteBtn = '&nbsp;<a type="button" class="btn btn-control btn-danger" onclick="deleteCoustomerPayment(' . $row->id . ', ' . user_id . ')"><i class="fa fa-trash"></i></a>';
                }

                if (!in_array($row->payment_mode_id, array(2)) || !empty($row->docs)) {
                    $documentViewBtn = '<a class="btn btn-control btn-danger" target="_blank" href="' . base_url('view-document-file/' . $row->id . '/2') . '" title="' . $row->id . '"><i class="fa fa-eye"></i></a>';
                }


                $data .= '
                        <tr>
                            <td class="whitespace">' . intval($row->id) . '</td>
                            <td class="whitespace">' . strval($row->loan_no) . '</td>
                            <td class="whitespace"><div class="tooltip"><i class="fa fa-comment"></i><span class="tooltiptext">
                                    <b>Remarks Collection : </b><i>' . strval($row->remarks) . '</i><br/>
                                    <b>Remarks Closure : </b><i>' . strval($row->closure_remarks) . '</i>
                                    </span></div></td>
                            <td class="whitespace">' . strval($row->payment_mode) . '</td>
                            <td class="whitespace">' . intval($row->received_amount) . '</td>
                            <td class="whitespace">' . intval($row->discount) . '</td>
                            <td class="whitespace">' . strval($row->refund) . '</td>
                            <td class="whitespace">' . strval($row->refrence_no) . '</td>
                            <td class="whitespace">' . $date_of_recived . '</td>
                            <td class="whitespace">' . strval($repaymentstatus->status_name) . '</td>
                            <td class="whitespace">' . strval($payment_verification) . '</td>
                            <td class="whitespace">' . strval($row->collection_executive_name) . '</td>
                            <td class="whitespace">' . (($row->collection_executive_payment_created_on == null) ? '-' : date('d-m-Y H:i:s', strtotime($row->collection_executive_payment_created_on))) . '</td>
                            <td class="whitespace">' . (($row->closure_user_name == null) ? '-' : strval($row->closure_user_name)) . '</td>
                            <td class="whitespace">' . (($row->closure_payment_updated_on == null) ? '-' : date('d-m-Y H:i:s', strtotime($row->closure_payment_updated_on))) . '</td>
                            <td class="whitespace">' . $documentViewBtn . $editBtn . $deleteBtn . '</td>
                        </tr>';
            }
            $data .= '</tbody></table></div>';
        } else {
            $data .= '<tbody><tr><td colspan = "40" style = "text-align: -webkit-center;color:red;">Record Not Found...</td></tr></tbody></table></div>';
        }
        return $data;
    }

    public function duplicateTask() {
        $where = ['company_id' => company_id, 'product_id' => product_id];
        $this->db->select('leads.lead_id, leads.name, leads.email, tb_states.state, leads.created_on, leads.source, leads.status')
                ->where($where)
                ->where('leads.leads_duplicate', 1)
                ->from(tableLeads)
                ->join('tb_states', 'leads.state_id = tb_states.id');
        return $query = $this->db->order_by('leads.lead_id', 'desc')->get();
    }

    public function duplicateTaskList($lead_id) {
        $where = ['company_id' => company_id, 'product_id' => product_id];
        $this->db->select('tbl_duplicate_leads.duplicate_lead_id, users.name, tbl_duplicate_leads.reson, tbl_duplicate_leads.created_on')
                ->where('tbl_duplicate_leads.lead_id', $lead_id)
                ->where($where)
                ->from('tbl_duplicate_leads')
                ->join('users', 'users.user_id = tbl_duplicate_leads.user_id');
        $duplicateTaskDetails = $this->db->order_by('tbl_duplicate_leads.duplicate_lead_id', 'desc')->get()->result();

        $data = '<div class = "table-responsive">
                <table class = "table table-hover table-striped">
                <thead>
                <tr class = "table-primary">
                <th scope = "col">#</th>
                <th scope = "col">Duplicate Lead ID</th>
                <th scope = "col">Duplicate Marked By</th>
                <th scope = "col">Reson</th>
                <th scope = "col">Initiated On</th>
                </tr>
                </thead>';
        $i = 1;
        if ($duplicateTaskDetails > 0) {
            foreach ($duplicateTaskDetails as $column) {
                $data .= '<tbody>
                <tr>
                <td>' . $i++ . '</th>
                <td>' . $column->duplicate_lead_id . '</td>
                <td>' . $column->name . '</td>
                <td>' . $column->reson . '</td>
                <td>' . $column->created_on . '</td>
                </tr>';
            }

            $data .= '</tbody></table></div>';
        } else {
            $data .= '<tbody><tr><td colspan = "7" style = "text-align: -webkit-center;color:red;">Record Not Found...</td></tr></tbody></table></div>';
        }
        return $data;
    }

    public function rejectedTask() {
        $where = ['company_id' => company_id, 'product_id' => product_id];
        $this->db->select('leads.lead_id,leads.application_no, leads.name, leads.middle_name, leads.sur_name, leads.email, leads.mobile, leads.pancard, tb_states.state, leads.city, leads.created_on, leads.source, leads.status, leads.credit_manager_id, leads.partPayment')
                ->where($where)
                ->where('leads.status', "REJECT")
                ->from(tableLeads)
                ->join('tb_states', 'leads.state_id = tb_states.id');
        return $query = $this->db->order_by('leads.lead_id', 'desc')->get();
    }

    public function rejectedLeadDetails($lead_id) {
        $where = ['company_id' => company_id, 'product_id' => product_id];
        $this->db->select('tbl_rejected_loan.rejected_lead_id, users.name, tbl_rejected_loan.reson, tbl_rejected_loan.created_on')
                ->where('tbl_rejected_loan.lead_id', $lead_id)
                ->where($where)
                ->from('tbl_rejected_loan')
                ->join('users', 'users.user_id = tbl_rejected_loan.user_id', 'left');
        $rejectedTaskDetails = $this->db->order_by('tbl_rejected_loan.rejected_lead_id', 'desc')->get()->result();

        $data = '<div class = "table-responsive">
                <table class = "table table-hover table-striped">
                <thead>
                <tr class = "table-primary">
                <th scope = "col">#</th>
                <th scope = "col">Rejected Lead ID</th>
                <th scope = "col">Rejected By</th>
                <th scope = "col">Reson</th>
                <th scope = "col">Initiated On</th>
                </tr>
                </thead>';
        $i = 1;
        if ($rejectedTaskDetails > 0) {
            foreach ($rejectedTaskDetails as $column) {
                $data .= '<tbody>
                		<tr>
							<td>' . $i++ . '</th>
							<td>' . $column->rejected_lead_id . '</td>
							<td>' . $column->name . '</td>
							<td>' . $column->reson . '</td>
							<td>' . $column->created_on . '</td>
						</tr>';
            }

            $data .= '</tbody></table></div>';
        } else {
            $data .= '<tbody><tr><td colspan = "7" style = "text-align: -webkit-center;color:red;">Record Not Found...</td></tr></tbody></table></div>';
        }
        return $data;
    }

    public function get_filtered_credit() {
        $this->get_credits($lead_id);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function get_all_credit() {
        $this->db->select('*');
        $this->db->from("credit");
        $lead_id = $this->uri->segment(3);
        $this->db->where('lead_id', $lead_id);
        return $this->db->count_all_results();
    }

    public function bank_analiysis($lead_id) {
        $result = $this->db->select('tbl_cart.*')->where('lead_id', $lead_id)->where('product_id', product_id)->order_by('tbl_cart.cart_id', 'desc')->get('tbl_cart');
        $data = '<div class = "table-responsive">
                <table class = "table table-hover table-striped">
                <thead>
                <tr class = "table-primary">
                <th scope = "col">Sr.</th>
                <th scope = "col">Doc ID</th>
                <th scope = "col">Initiated On</th>
                <th scope = "col">Action</th>
                </tr>
                </thead>';
        if ($result->num_rows() > 0) {
            $i = 1;
            foreach ($result->result() as $column) {
                $id = $column->lead_id;
                $status = "In Progress";
                if ($column->status == "Processed") {
                    $status = '<a href = "#" data-toggle = "modal" data-target = "#viewCibilModel" id = "viewCibilPDF" onclick = "ViewBankingAnalysis(this.title)" title = "' . $column->docId . '"><i class = "fa fa-file-pdf-o"></i></a>';
                }
                $data .= '<tbody>
                <tr>
                <td>' . $i . '</td>
                <td>' . $column->docId . '</td>
                <td>' . $column->created_at . '</td>
                <td><a href = "#" data-toggle = "modal" data-target = "#viewCibilModel" id = "viewCibilPDF" onclick = "ViewBankingAnalysis(this.title)" title = "' . $column->docId . '">' . $column->status . '</a>
                </td>
                </tr>';

                $i++;
            }
            $data .= '</tbody></table></div>';
        } else {
            $data .= '<tbody><tr><td colspan="9" style="text-align: -webkit-center;color:red;">Record Not Found...</td></tr></tbody></table></div>';
        }
        return $data;
    }

    public function DownloadBankingAnalysis($doc_id) {

        $Auth_Token = "LIVE";

        if ($Auth_Token == "UAT") {
            define('api_token', 'API://IlJKyP5wUwzCvKQbb796ZSjOITkMSRN8rifQTMrNM1/NUUv8/tuaN6Lun6d1NG4S');
        } else {
            define('api_token', 'API://9jwoyrhfdtDuDt0epG4VsisYdBHMsZMGC7IlUhwN8t1Qb2bgwxFqrn7K0LgWIly1');
        }

        $urlDownload = 'https://cartbi.com/api/downloadFile';
        $header2 = [
            'Content-Type: text/plain',
            'auth-token: ' . api_token
        ];

        $ch2 = curl_init($urlDownload);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, $header2);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_POSTFIELDS, $doc_id);

        $downloadCartData = curl_exec($ch2);
        curl_close($ch2);
        return $this->db->where('docId', $doc_id)->update('tbl_cart', ['downloadCartData' => $downloadCartData]);
    }

    public function ViewBankingAnalysis($doc_id) {
        $row = $this->db->select('tbl_cart.downloadCartData')->where('docId', $doc_id)->get('tbl_cart');
        $collection = $row->row_array();
        $CartData = json_decode($collection['downloadCartData']);

        $data .= '
                <div id="collection">
                    <div class="footer-support">
                        <h2 class="footer-support">Salary Details &nbsp;<i class="fa fa-angle-double-down"></i></h2>
                    </div>
                </div>
                <div class="table-responsive">
    		        <table class="table table-hover table-bordered table-striped">
                      <thead>
                        <tr class="table-primary">
                            <th scope="col"><strong>Transaction Date</strong></th>	
                            <th scope="col"><strong>Narration</strong></th>
                            <th scope="col"><strong>Cheque</strong></th>
                            <th scope="col"><strong>Amount</strong></th>
                            <th scope="col"><strong>Type</strong></th>
                        </tr>
                    </thead>';
        foreach ($CartData->data[0]->salary as $salary) {
            $data .= '<tbody>
	                <tr>
						<td><strong>' . $salary->month . '</strong></td>
						<td colspan="12"><strong>Rs. ' . number_format($salary->totalSalary, 2) . '</strong></td>
	                </tr>';
            foreach ($salary->transactions as $salaryList) {
                $data .= '
					    <tr>
    					    <td>' . date('d/M/Y', substr($salaryList->transactionDate, 0, 10)) . '</td>
                            <td>' . $salaryList->narration . '</td>	
                            <td>' . $salaryList->cheque . '</td>
                            <td>' . number_format($salaryList->amount, 2) . '</td>
                            <td>' . $salaryList->type . '</td>
					    </tr>';
            }
        }
        $data .= '</tbody></table></div>';

        $data .= '
                <div id="collection">
                    <div class="footer-support">
                        <h2 class="footer-support">EMI Details &nbsp;<i class="fa fa-angle-double-down"></i></h2>
                    </div>
                </div>
                <div class="table-responsive">
    		        <table class="table table-hover table-bordered table-striped">
                      <thead>
                        <tr class="table-primary">
                            <th scope="col"><strong>Transaction Date</strong</th>
                            <th scope="col"><strong>Narration</strong</th>		
                            <th scope="col"><strong>Payment Category</strong</th>
                            <th scope="col"><strong>Cheque</strong</th>
                            <th scope="col"><strong>Amount</strong</th>
                            <th scope="col"><strong>Type</strong</th>	
                        </tr>
                    </thead>';
        foreach ($CartData->data[0]->emi as $emi) {
            $data .= '<tbody>
	                <tr>
						<td><strong>' . $emi->commonEntity . '</strong></td>
						<td colspan="5"><strong>Rs. ' . number_format($emi->amount, 2) . '</strong></td>
						
	                </tr>';
            foreach ($emi->transactions as $emiList) {
                $data .= '
					    <tr>
    					    <td>' . date('d/M/Y', substr($emiList->transactionDate, 0, 10)) . '</td>
                            <td>' . $emiList->narration . '</td>		
                            <td>' . $emiList->paymentCategory . '</td>
                            <td>' . $emiList->cheque . '</td>
                            <td>Rs. ' . number_format($emiList->amount, 2) . '</td>
                            <td>' . $emiList->type . '</td>
					    </tr>';
            }
        }
        $data .= '</tbody>
                </table>
            </div>';
        return $data;
    }

    public function ViewCivilStatement($lead_id) {
        $data = '<div class="table-responsive">
		        <table class="table table-hover table-striped table-bordered" data-order="[[ 0, "desc" ]]" style="margin-top: 10px;">
                  <thead>
                    <tr class="table-primary">
                      <th class="whitespace" scope="col">Sr.&nbsp;No</th>
                      <th class="whitespace" scope="col">Member&nbsp;ID</th>
                      <th class="whitespace" scope="col">High&nbsp;Credit/&nbsp;Sanc&nbsp;Amt</th>
                      <th class="whitespace" scope="col">Total&nbsp;Accounts</th>
                      <th class="whitespace" scope="col">Overdue&nbsp;Accounts</th>
                      <th class="whitespace" scope="col">Overdue&nbsp;Amount</th>
                      <th class="whitespace" scope="col">Zero&nbsp;Balance&nbsp;Accounts</th>
                      <th class="whitespace" scope="col">Current&nbsp;Balance</th>
                      <th class="whitespace" scope="col">Score</th>
                      <th class="whitespace" scope="col">Report By</th>
                      <th class="whitespace" scope="col">Report&nbsp;Date</th>
                      <th class="whitespace" scope="col">Report</th>
                    </tr>
                </thead>';
        if (!empty($lead_id)) {

            $sql_main = 'LD.lead_id, LD.pancard';

            $this->db->select($sql_main);
            $this->db->from($this->table . ' LD');
            $this->db->join($this->table_lead_customer . ' C', 'C.customer_lead_id = LD.lead_id AND C.customer_active = 1 AND C.customer_deleted = 0', 'INNER');
            $this->db->where(['LD.lead_id' => $lead_id]);
            $leadDetails = $this->db->get();

            if ($leadDetails->num_rows() > 0) {

                $lead_data = $leadDetails->row_array();
                $lead_pancard = $lead_data['pancard'];

                $sql = 'SELECT CB.lead_id, CB.customer_id, CB.cibil_id, CB.cibilScore, CB.cibil_file, CB.memberCode, CB.created_at, ';
                $sql .= ' CB.highCrSanAmt, CB.totalAccount, CB.totalBalance, CB.overDueAccount, CB.overDueAmount, CB.zeroBalance,';
                $sql .= ' U.name as bureau_fetch_username';
                $sql .= ' FROM tbl_cibil CB';
                $sql .= ' LEFT JOIN users U on(U.user_id=CB.cibil_created_by)';
                $sql .= ' WHERE (CB.lead_id=' . $lead_id . ' OR CB.cibil_pancard="' . $lead_pancard . '") AND cibil_active=1 AND cibil_deleted=0 ORDER BY CB.cibil_id DESC';

//            $cibilData = $this->select($conditions, $select, $table);

                $cibilData = $this->db->query($sql);

                if ($cibilData->num_rows() > 0) {
                    $i = 1;
                    foreach ($cibilData->result() as $column) {
                        $id = $column->lead_id;
                        $data .= '<tbody>
                    		<tr ' . (($lead_id != $id) ? "class='danger'" : "") . '>
                                    <td class="whitespace">' . $i . '</td>
                                    <td class="whitespace">' . $column->memberCode . '</td>		
                                    <td class="whitespace">' . $column->highCrSanAmt . '</td>
                                    <td class="whitespace">' . strval($column->totalAccount) . '</td>
                                    <td class="whitespace">' . strval($column->overDueAccount) . '</td>
                                    <td class="whitespace">' . strval($column->overDueAmount) . '</td>
                                    <td class="whitespace">' . strval($column->zeroBalance) . '</td>
                                    <td class="whitespace">' . strval($column->totalBalance) . '</td>
                                    <td class="whitespace">' . strval($column->cibilScore) . '</td>
                                    <td class="whitespace">' . strval($column->bureau_fetch_username) . '</td>
                                    <td class="whitespace">' . date('d-m-Y h:i:s', strtotime($column->created_at)) . '</td>
                                    <td class="whitespace">
                                        <a href="' . base_url('viewCustomerCibilPDF/' . $column->cibil_id) . '" target="_blank"><i class="fa fa-file-pdf-o" title="View CREDIT BUREAU"></i></a> |  
                                        <a href="' . base_url('viewCustomerCibilPDF/' . $column->cibil_id) . '" target="_blank" download><i class="fa fa-download" title="Download CREDIT BUREAU"></i></a>
                                    </td>
                            </tr>';
                        $i++;
                    }
                    return $data .= '</tbody></table></div>';
                } else {
                    return $data .= '<tbody><tr><td colspan="12" style="text-align: -webkit-center;color:red;">Record Not Found...</td></tr></tbody></table></div>';
                }
            } else {
                return $data .= '<tbody><tr><td colspan="12" style="text-align: -webkit-center;color:red;">Record Not Found...</td></tr></tbody></table></div>';
            }
        } else {
            return $data .= '<tbody><tr><td colspan="12" style="text-align: -webkit-center;color:red;">Record Not Found...</td></tr></tbody></table></div>';
        }
    }

    public function isAnotherLeadInprocess($lead_id) {


        $sql = $this->db->select('pancard, mobile, lead_data_source_id')->where('lead_id', $lead_id)->from('leads')->get();
        $result = $sql->row();
        $pancard = $result->pancard;
        $mobile = $result->mobile;
        $lead_data_source_id = $result->lead_data_source_id;

        $in_process_status_array = array(
            "SANCTION" => 12,
            "DISBURSE-PENDING" => 13,
            "DISBURSED" => 14,
            "SETTLED" => 17, //asked by Meena mam over email on 2022-01-19// activated again on 2022-11-10
            "WRITEOFF" => 18,
            "PART-PAYMENT" => 19
        );

        if (in_array($lead_data_source_id, array(21, 27, 33)) || in_array($mobile, array(9560807913, 7505476947))) {//for C4C, REFCASE case which are in pending list for disbursement in same day.
            unset($in_process_status_array['DISBURSE-PENDING']);
            unset($in_process_status_array['DISBURSED']);
            unset($in_process_status_array['SANCTION']);
            unset($in_process_status_array['SETTLED']); // activated again on 2022-11-10
            unset($in_process_status_array['WRITEOFF']); // activated again on 2022-11-10
        }

        $in_process_status_array = array_values($in_process_status_array);
//        $wherecond = '(status = "SANCTION" OR status= "DISBURSE-PENDING" OR status= "DISBURSED" OR status= "SETTLED" OR status= "WRITEOFF" OR status= "PART-PAYMENT")';

        $query = $this->db->select('lead_id, status, first_name')
                ->where('lead_id !=', $lead_id)
                ->where('pancard', $pancard)
                ->where_in('lead_status_id', $in_process_status_array)
                ->from('leads')
                ->get();
        return $query;
    }

    public function isThisCustomerIsOldCustomer($customer_id) {
        $sql = $this->db->select('cif_number, mobile')->where('cif_number', $customer_id)->from('cif_customer')->get();
        $result = $sql->row();
        $pancard = $result->pancard;

        if (empty($pancard)) {
            $result = $sql->result();
            foreach ($result as $row) {
                if (!empty($row->pancard)) {
                    $pancard = $row->pancard;
                    break;
                }
            }
        }
        $wherecond = '(status = "SANCTION" OR status= "DISBURSE-PENDING" OR status= "DISBURSED" OR status= "SETTLED" OR status= "WRITEOFF" OR status= "PART-PAYMENT")';

        $query = $this->db->select('leads.lead_id, leads.status, leads.first_name')
                ->where('leads.lead_id !=', $lead_id)
                ->where('leads.pancard', $pancard)
                ->where($wherecond)
                ->from('leads')
                ->get();
        return $query;
    }

    public function getCAMDetails($lead_id) {
        $conditions = ['LD.lead_id' => $lead_id];

        $select = 'LD.lead_id, LD.user_type, LD.lead_data_source_id, LD.customer_id, LD.application_no, C.first_name, C.middle_name, C.sur_name, C.email, C.alternate_email, LD.pancard, LD.cibil, LD.lead_disbursal_assign_user_id, ';
        $select .= ' C.gender, C.dob, C.mobile, C.alternate_mobile, LD.obligations, LD.purpose, LD.loan_amount, LD.status, LD.stage, LD.lead_status_id, CAM.loan_recommended, ';
        $select .= ' CAM.roi,CAM.panel_roi, CAM.admin_fee, CAM.total_admin_fee, DATE_FORMAT(CAM.disbursal_date,"%d-%m-%Y") AS disbursal_date, DATE_FORMAT(CAM.repayment_date,"%d-%m-%Y") AS repayment_date, CAM.tenure, CAM.net_disbursal_amount, ';
        $select .= ' CAM.repayment_amount, CAM.net_disbursal_amount,L.loan_no, L.loanAgreementRequest, L.agrementRequestedDate, L.loanAgreementResponse, ';
        $select .= ' CAM.eligible_foir_percentage, CAM.admin_fee as processing_fee_percent, CAM.user_id, ';
        $select .= ' L.agrementUserIP, L.agrementResponseDate, L.status as loan_status, L.company_account_no, L.channel, L.mode_of_payment, CE.presentServiceTenure, CE.monthly_income, CE.monthly_income as monthly_salary, ';
        $select .= ' L.disburse_refrence_no, CT.m_city_category as city_category, CT.m_city_code as city_code, ';
        $select .= ' user_screener.name as screened_by, DATE_FORMAT(LD.lead_screener_assign_datetime, "%d-%m-%Y %H:%i:%s") as lead_screener_assign_datetime, ';
        $select .= ' user_credit_manager.name as credit_by, DATE_FORMAT(LD.lead_credit_assign_datetime, "%d-%m-%Y %H:%i:%s") as lead_credit_assign_datetime, ';
        $select .= ' user_disbursed.name as disbursal_manager, LD.lead_disbursal_recommend_datetime as disbursal_recommend,';
        $select .= ' user_disbursed_head.name as disbursal_head, LD.lead_disbursal_approve_datetime as disbursal_approve, ';
        $select .= ' user_credit_head.name as sanctioned_by, DATE_FORMAT(LD.lead_credit_approve_datetime, "%d-%m-%Y %H:%i:%s") as lead_credit_approve_datetime,';
        $select .= ' cam_sanction_letter_file_name, CAM.cam_risk_profile, CAM.cam_risk_score, CAM.cam_advance_interest_amount as cam_interest_amount, CAM.cam_appraised_monthly_income, ';
        $select .= ' C.current_residence_type, C.email_verified_status, C.alternate_email_verified_status ';

        $this->db->select($select);
        $this->db->distinct();
        $this->db->from($this->table . ' LD');
        $this->db->where($conditions);
        $this->db->join($this->table_state . ' ST', 'ST.m_state_id = LD.state_id', 'left');
        $this->db->join($this->table_city . ' CT', 'CT.m_city_id = LD.city_id', 'left');
        $this->db->join($this->table_lead_customer . ' C', 'C.customer_lead_id = LD.lead_id AND C.customer_active=1 AND C.customer_deleted=0', 'left');
        $this->db->join($this->table_customer_employment . ' CE', 'CE.lead_id = LD.lead_id AND CE.emp_active=1 AND CE.emp_deleted=0', 'left');
        $this->db->join($this->table_credit_analysis_memo . ' CAM', 'CAM.lead_id = LD.lead_id AND CAM.cam_active=1 AND CAM.cam_deleted=0', 'left');
        $this->db->join($this->table_loan . ' L', 'L.lead_id = LD.lead_id AND L.loan_active=1 AND L.loan_deleted=0', 'left');
        $this->db->join('users user_screener', 'user_screener.user_id = LD.lead_screener_assign_user_id', 'left');
        $this->db->join('users user_disbursed', 'user_disbursed.user_id = LD.lead_disbursal_assign_user_id', 'left');
        $this->db->join('users user_disbursed_head', 'user_disbursed_head.user_id = LD.lead_disbursal_approve_user_id', 'left');
        $this->db->join('users user_credit_manager', 'user_credit_manager.user_id = LD.lead_credit_assign_user_id', 'left');
        $this->db->join('users user_credit_head', 'user_credit_head.user_id = LD.lead_credit_approve_user_id', 'left');
        $data = $this->db->get();
        return $data;
    }

    public function sendDisbursalMail($lead_id, $print_flag = false) {

        $sql = $this->getCAMDetails($lead_id);

        $camDetails = $sql->row();

        $email = $camDetails->email;

        $alternate_email = $camDetails->alternate_email;

        $subject = 'Loan Disbursal Letter - ' . BRAND_NAME;

        $bcc_email = BCC_DISBURSAL_EMAIL;

        $fullname = $camDetails->first_name . ' ' . $camDetails->middle_name . ' ' . $camDetails->sur_name;

        $loan_no = $camDetails->loan_no;
        $acceptance_button = '';
        $acceptance_button_link = '';
        if ($print_flag == true) {
            $logo_image = base_url('/public/logo.jpg');
        }


        $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns="http://www.w3.org/1999/xhtml">
                            <head>
                                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                <title>' . $subject . '</title>
                            </head>
                            <body>
                                <table width="778" border="0" align="center" cellpadding="0" cellspacing="0" style="padding:10px; border:solid 1px #ccc; font-family:Arial, Helvetica, sans-serif; font-size:15px;">
                                    <tr>
                                        <td width="404" align="left"><img src="' . EMAIL_BRAND_LOGO . '" alt="logo" style=" border-radius: 5px;width: 180px;"/></td>
                                        <td width="4" align="left">&nbsp;</td>
                                        <td width="368" align="right"><table width="100%" border="0">
                                                <tr>
                                                    <td align="right"><strong>Dear ' . $fullname . '</strong></td>
                                                </tr>
                                                <tr>
                                                    <td align="right"><table cellspacing="0" cellpadding="0">
                                                            <tr>
                                                                <td align="right">Loan No. : ' . $loan_no . '</td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            <hr style="background:#ddd !important;"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" valign="top"><table width="100%" border="0">
                                                <tr>
                                                    <td valign="top">
                                                        <p style="line-height:22px; margin:0px 0px 10px 0px;">Thank You for choosing ' . BRAND_NAME . ' and giving us the opportunity to serve you.</p>
                                                        <p style="line-height:22px;  margin:0px 0px 10px 0px;">We are hoping that you are satisfied with our prompt responses as a part of process.</p>
                                                        <p style="line-height:22px; margin:0px 0px 10px 0px;">We have received all your details and your submitted loan application has been approved.</p>
                                                        <p style="line-height:22px; margin:0px 0px 10px 0px;">We request you to go through loan terms and repayment schedule.</p>
                                                        <p style="line-height:22px; margin:0px 0px 0px 0px;">Henceforth visiting (physically) your workplace and residence has your concurrence on it.</p>
                                                    </td>
                                                    <td valign="top"><img src="' . DISBURSAL_LETTER_BANNER . '" width="322" height="272" style="padding:10px; border:solid 1px #ddd;"></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            <p style="line-height:25px;">
                                                <strong>' . BRAND_NAME . ' Powered by ' . COMPANY_NAME . ' (RBI approved NBFC - Reg No. ' . RBI_LICENCE_NUMBER . ')<br/>
                                                ' . REGISTED_ADDRESS . '<br/>
                                                    Loan Terms to be agreed by the customer : </strong>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            <table width="100%" border="0" cellpadding="5" cellspacing="1" bgcolor="#CCCCCC">
                                                <tbody>
                                                    <tr>
                                                        <td width="42%" bgcolor="#FFFFFF" style="padding:10px;">Name</td>
                                                        <td width="58%" bgcolor="#FFFFFF" style="padding:10px;">' . $fullname . '</td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">Loan Amount (Rs.)</td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">' . number_format($camDetails->loan_recommended, 2) . ' /- </td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">Rate of Interest (%)</td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">' . number_format($camDetails->roi, 2) . ' per day</td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">Disbursal Date</td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">' . $camDetails->disbursal_date . ' </td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">Commitment Payback Date</td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">' . $camDetails->repayment_date . '</td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">Repayment Amount (Rs.)</td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">' . number_format($camDetails->repayment_amount, 2) . ' </td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">Period (Days)</td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">' . $camDetails->tenure . ' </td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">Penalty (%)</td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">' . number_format(round(($camDetails->roi * 2), 2), 2) . '</td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">Processing Fee (Rs.)</td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">' . number_format($camDetails->admin_fee, 2) . '/- (Including 18% GST)</td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">Repayment Cheque(s)</td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">&nbsp;-</td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">Cheque drawn on (Bank Name) </td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">&nbsp;-</td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">Cheque &amp; NACH Bouncing Charges </td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">Rs. 1000.00/- every time</td>
                                                    </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="font-size:17px; line-height: 25px; padding-bottom: 6px;">
                                            Note* - Annual ROI : ' . (number_format($camDetails->roi * 365, 2)) . '%
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="font-size:17px;line-height: 25px;padding-bottom: 6px;">
                                            <p style="line-height:22px; margin:0px 0px 0px 0px;">Non-payment of loan on time will affect your credit score and your chance of getting further loans.</p>
                                            <p style="line-height:22px; margin:0px 0px 10px 0px;">Please share your kind acceptance over email to execute further.</p>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-bottom:10px; padding-top:10px;"><strong>Best Regards,  </strong></td>
                                        <td style="padding-bottom:10px; padding-top:10px;">&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Team ' . BRAND_NAME . '</strong></td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>' . $acceptance_button . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . $acceptance_button_link . '</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                </table>
                            </body>
                        </html>';

                    $file_name = "sanction_letter_" . $lead_id . "_" . rand(1000, 9999) . ".pdf";
        
                    //$file_path_with_name = UPLOAD_PATH . $file_name;
                    $file_path_with_name = '/var/tmp/' . $file_name;
                    require_once __DIR__ . '/../../vendor/autoload.php';
        
                    $mpdf = new \Mpdf\Mpdf();
        
                    $mpdf->WriteHTML($message);
                    
                    //$mpdf->Output(UPLOAD_TEMP_PATH . $file_name, 'F');
                    $mpdf->Output($file_path_with_name, 'F');
        
                    if(file_exists($file_path_with_name)) {
        
                        $upload_return = uploadDocument($file_path_with_name,$lead_id,2,'pdf');
                        $return_array['status'] = 1;
                        $return_array['file_name'] = $upload_return['file_name'];
                        unlink($file_path_with_name);
                        //$this->updateLeads(['lead_id' => $lead_id], ['cam_sanction_letter_file_name' => $file_name], 'credit_analysis_memo');
                        $this->updateLeads(['lead_id' => $lead_id], ['cam_sanction_letter_file_name' => $upload_return['file_name']], 'credit_analysis_memo');
                    } else {
        
                        $return_array['errors'] = "File does not exist. Please check offline";
                    }

        if ($print_flag == true) {
            return $message;
        }

        $return_array = lw_send_email($email, $subject, $message, $bcc_email);

        if (!empty($alternate_email)) {
            $return_array = lw_send_email($alternate_email, $subject, $message, $bcc_email);
        }

        return true;
    }

    public function sendSanctionMail($lead_id) {
        $sql = $this->getCAMDetails($lead_id);
        $camDetails = $sql->row();
        $sql1 = $this->getResidenceDetails($lead_id);
        $getResidenceDetails = $sql1->row();

        $enc_lead_id = $this->encrypt->encode($lead_id);

        $lead_data_source_id = $camDetails->lead_data_source_id;
        $email = $camDetails->email;
        $alternate_email = $camDetails->alternate_email;

        $bcc_email = BCC_SANCTION_EMAIL;

        $fullname = $camDetails->first_name;

        if (!empty($camDetails->middle_name)) {
            $fullname .= ' ' . $camDetails->middle_name;
        }

        if (!empty($camDetails->sur_name)) {
            $fullname .= ' ' . $camDetails->sur_name;
        }

        $cam_sanction_letter_file_name = "";

        if (!empty($camDetails->cam_sanction_letter_file_name)) {
            $cam_sanction_letter_file_name = $camDetails->cam_sanction_letter_file_name;
        }


        $sanction_date = DATE("d-m-Y", strtotime($camDetails->lead_credit_approve_datetime));

        $title = "";
        if ($camDetails->gender == 'MALE') {
            $title = "Mr.";
        } else if ($camDetails->gender == 'FEMALE') {
            $title = "Ms.";
        }

        $residence_address = "";

        if (!empty($getResidenceDetails->current_house)) {
            $residence_address .= " " . $getResidenceDetails->current_house;
        }

        if (!empty($getResidenceDetails->current_locality)) {
            $residence_address .= ", " . $getResidenceDetails->current_locality . "<br/>";
        }

        if (!empty($getResidenceDetails->current_landmark)) {
            $residence_address .= " " . $getResidenceDetails->current_landmark . "<br/>";
        }

        if (!empty($getResidenceDetails->res_city)) {
            $residence_address .= $getResidenceDetails->res_city . ", " . $getResidenceDetails->res_state . " - " . $getResidenceDetails->cr_residence_pincode . ".";
        }


        $residence_address = trim($residence_address);

        $subject = BRAND_NAME . ' | Loan Sanction Letter - ' . $fullname;

        $acceptance_button = '';
        $link_value = base_url('sanction-esign-request') . "?refstr=$enc_lead_id";
        $acceptance_button_link = '<br/><br/><center><a style="text-align:center;outline : none;color: #fff; background: #e52255; border-bottom: none !important; padding: 12px 9px !important;" href="' . $link_value . '">eSign Sanction Letter</a></center><br/><br/>';
        $acceptance_button_link .= "If you are not able to click on the eSign button then please copy and paste this url in browser to proceed or click here .<br/><a href='" . $link_value . "'>" . $link_value . "</a>";

        if (in_array($lead_data_source_id, array(21, 27))) {
            $link_value = base_url('loanAgreementLetterResponse') . "?refstr=$enc_lead_id";
            $acceptance_button_link = '<br/><br/><center><a style="text-align:center;outline : none;color: #fff; background: #e52255; border-bottom: none !important; padding: 12px 9px !important;" href="' . $link_value . '">Accept Sanction Letter</a></center><br/><br/>';
            $acceptance_button_link = "If you are not able to click on the accept button then please copy and paste this url in browser to proceed or click here .<br/><a href='" . $link_value . "'>" . $link_value . "</a>";
        }

        $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns="http://www.w3.org/1999/xhtml">
                        <head>
                            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                            <title>' . $subject . '</title>
                        </head>
                        
                        <body>
                            <table
                            width="667"
                            border="0"
                            align="center"
                            style="
                                font-family: Arial, Helvetica, sans-serif;
                                line-height: 25px;
                                font-size: 14px;
                                border: solid 1px #ddd;
                                padding: 0px 10px;
                            ">
                            <tr>
                                <td colspan="2" valign="middle">
                                <p style="color:#font-size: 18px; color: #0363a3; font-size:18px;">
                                    <img
                                    src=" ' . SANCTION_LETTER_HEADER . ' "
                                    alt="Sanctionletter-header"
                                    width="760"
                                    height="123"
                                    border="0"
                                    usemap="#Map" onContextMenu="return false;" 
                                    />
                                </p>
                                </td>
                            </tr>
                            <tr>
                                <td align="right">
                                <span style="color:#font-size: 18px; color: #0363a3; font-size:18px;">Date : ' . $sanction_date . '</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>To,</strong></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td><strong>' . $title . ' </strong>' . $fullname . '.</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td>' . $residence_address . '</td>
                                <td>&nbsp;</td>
                            </tr>
                        
                            <tr>
                                <td><strong>Contact No. :</strong> +91-' . $camDetails->mobile . '</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                Thank you for showing your interest in ' . BRAND_NAME . ' and giving us an
                                opportunity to serve you.
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                We are pleased to inform you that your loan application has been
                                approved as per the below mentioned terms and conditions.
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                <strong>' . BRAND_NAME . ', a brand name under ' . COMPANY_NAME . ' (RBI approved NBFC – Reg No. ' . RBI_LICENCE_NUMBER . ') <br/>' . REGISTED_ADDRESS . '.</strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                This sanction will be subject to the following Terms and Conditions:
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                <table
                                    width="100%"
                                    border="0"
                                    cellpadding="8"
                                    cellspacing="1"
                                    bgcolor="#ddd">
                                    <tr>
                                    <td width="43%" align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Customer Name</strong>
                                    </td>
                                    <td width="4%" align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td width="53%" align="left" valign="middle" bgcolor="#FFFFFF">
                                        ' . $fullname . '
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Sanctioned Loan Amount (Rs.)</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        ' . number_format(round($camDetails->loan_recommended, 0), 2) .
                '/-
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Rate of Interest (% ) per day</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        ' . number_format($camDetails->roi, 2) . '
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Date of Sanction</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        ' . $sanction_date . '
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Total Repayment Amount (Rs.</strong>)
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        ' . number_format(round($camDetails->repayment_amount, 0), 2) .
                '/-
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Tenure in Days</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        ' . $camDetails->tenure . '
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Repayment Date</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        ' . $camDetails->repayment_date . '
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Penal Interest(%) per day</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        ' . round(($camDetails->roi * 2), 2) . '
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Processing Fee </strong> (<strong>Rs.)</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        ' . number_format(round($camDetails->admin_fee, 0), 2) . '/-
                                        (Including 18% GST)
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Repayment Cheque(s)</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">-</td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Cheque drawn on (name of the Bank)</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">-</td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Cheque and NACH Bouncing Charges (Rs.)</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        1,000.00/- per bouncing/dishonour.
                                    </td>
                                    </tr>
                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Annualised ROI (%)</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        ' . round(($camDetails->roi * 365), 2) . '
                                    </td>
                                    </tr>
                                </table>
                                </td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                Henceforth visiting (physically) your Workplace and Residence has your
                                concurrence on it.
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                Kindly request you to go through above mentioned terms and conditions
                                and provide your kind acceptance over E-mail so that we can process
                                your loan for final disbursement.
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                <strong style="color: #0363a3">Best Regards</strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                <strong style="color: #0363a3">Team ' . BRAND_NAME . '</strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                <strong style="color: #0363a3"
                                    >(Brand Name for ' . COMPANY_NAME . ')</strong
                                >
                                </td>
                            </tr>' . BRAND_NAME . '
                            <tr>
                                <td>' . $acceptance_button . '</td>
                            </tr>
                            <tr>
                                <td>' . $acceptance_button_link . '</td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Kindly Note:</strong></td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                Non-payment of loan on time will adversely affect your Credit score,
                                further reducing your chances of getting loan again
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                Upon approval the processing fee will be deducted from your Sanction
                                amount and balance amount will be disbursed to your account.
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">This Sanction letter is valid for 24 Hours only.</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                You can Prepay/Repay the loan amount using our link
                                <a href="' . LOAN_REPAY_LINK . '"
                                    target="_blank"
                                    style="color: #e52255; text-decoration: blink"
                                    >' . LOAN_REPAY_LINK . '</a>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                <img
                                    src=" ' . SANCTION_LETTER_FOOTER . ' " width="760" height="44"/>
                                </td>
                            </tr>
                            </table>
                        
                            <map name="Map" id="Map">
                            <area shape="rect"
                                coords="574,21,750,110"
                                href="' . WEBSITE_URL . '"
                                target="_blank"/>
                            </map>
                        </body>
                        </html>';

        $return_array = lw_send_email($email, $subject, $message, $bcc_email, "", "", "", "", $cam_sanction_letter_file_name);

        if (!empty($alternate_email)) {
            $return_array = lw_send_email($alternate_email, $subject, $message, $bcc_email, "", "", "", "", $cam_sanction_letter_file_name);
        }

        $this->sent_sacntion_esign_sms($lead_id, $esign_short_url);

        return $return_array;
    }

    public function sent_sacntion_esign_sms($lead_id, $sort_url) {

        //$req['esign_link'] = base_url('sanction-esign-request') . "?refstr=$enc_lead_id";
        $req['esign_link']="https://esign.nsdl.com";
        //$req['mobile']=8750256406;
        require_once (COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $sql = 'SELECT LD.lead_id, LC.mobile, LC.first_name as name FROM leads LD INNER JOIN lead_customer LC ON (LC.customer_lead_id=LD.lead_id) WHERE LD.lead_id=' . $lead_id;

        $result = $this->db->query($sql);

        if ($result->num_rows() > 0) {

            $app_data = $result->row_array();

            $sms_request = array();
            $sms_request['lead_id'] = $app_data['lead_id'];
            $sms_request['mobile'] = $app_data['mobile'];
            $sms_request['name'] = $app_data['name'];
            $sms_request['esign_link'] = $sort_url;

            $CommonComponent->payday_sms_api(12, $lead_id, $sms_request);
        }
    }

    public function gererateSanctionLetter($lead_id) {

        $return_array = array("status" => 0, "errors" => "");

        try {

            $CONTACT_PERSON = CONTACT_PERSON;
            $REGISTED_MOBILE = REGISTED_MOBILE;
            $REGISTED_ADDRESS = REGISTED_ADDRESS;

            $sql = $this->getCAMDetails($lead_id);
            $camDetails = $sql->row();
            $sql1 = $this->getResidenceDetails($lead_id);
            $getResidenceDetails = $sql1->row();

            $subject = 'Loan Sanction Letter - ' . BRAND_NAME;

            $mobile = $camDetails->mobile;
            $loan_recommended = $camDetails->loan_recommended;
            $repayment_amount = $camDetails->repayment_amount;
            $tenure = $camDetails->tenure;
            $repayment_date = $camDetails->repayment_date;
            $net_disbursal_amount = $camDetails->net_disbursal_amount;

            $roi = $camDetails->roi;
//            $processing_fee_percent = $camDetails->processing_fee_percent;
            $admin_fee = $camDetails->admin_fee; //Total Admin Fee with GST

            $fullname = $camDetails->first_name;

            if (!empty($camDetails->middle_name)) {
                $fullname .= ' ' . $camDetails->middle_name;
            }

            if (!empty($camDetails->sur_name)) {
                $fullname .= ' ' . $camDetails->sur_name;
            }


            $sanction_date = date("d-m-Y");

            $title = "";
            if ($camDetails->gender == 'MALE') {
                $title = "Mr.";
            } else if ($camDetails->gender == 'FEMALE') {
                $title = "Ms.";
            }

            $residence_address = "";

            if (!empty($getResidenceDetails->current_house)) {
                $residence_address .= " " . $getResidenceDetails->current_house;
            }

            if (!empty($getResidenceDetails->current_locality)) {
                $residence_address .= ", " . $getResidenceDetails->current_locality . "<br/>";
            }

            if (!empty($getResidenceDetails->current_landmark)) {
                $residence_address .= " " . $getResidenceDetails->current_landmark . "<br/>";
            }

            if (!empty($getResidenceDetails->res_city)) {
                $residence_address .= $getResidenceDetails->res_city . ", " . $getResidenceDetails->res_state . " - " . $getResidenceDetails->cr_residence_pincode . ".";
            }

            $residence_address = trim($residence_address);

            $html_string = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                            <html xmlns="http://www.w3.org/1999/xhtml">
                                <head>
                                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                    <title>' . $subject . '</title>
                                </head>


                                <body><br/><br/>

                                    <table width="667" border="0" cellpadding="4" cellspacing="1" bgcolor="#ddd" style="font-size:11px;margin-top:10px;">

                                        <tr>
                                            <td colspan="3" bgcolor = "#FFFFFF" align="center">
                                                <p style="font-size:18pt;font-weight:bold;color:blue;text-align:center">Key Fact Statement</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="width:10%;text-align:left;font-weight:bold" bgcolor = "#FFFFFF">S.No.</td>
                                            <td style="width:45%;text-align:left;font-weight:bold" bgcolor = "#FFFFFF">Parameters</td>
                                            <td style="width:45%;text-align:left;font-weight:bold" bgcolor = "#FFFFFF">Details</td>
                                        </tr>



                                        <tr>
                                            <td bgcolor = "#FFFFFF"><b>(I)</b></td>
                                            <td bgcolor = "#FFFFFF">Name</td>
                                            <td bgcolor = "#FFFFFF">
                                                ' . $fullname . '
                                            </td>
                                        </tr>
                                        <tr>
                                            <td bgcolor = "#FFFFFF"><b>(II)</b></td>
                                            <td bgcolor = "#FFFFFF">Loan Amount</td>
                                            <td bgcolor = "#FFFFFF">&#8377;&nbsp;
                                                ' . number_format(round($loan_recommended, 0), 2) . '
                                            </td>
                                        </tr>
                                        <tr>
                                            <td bgcolor = "#FFFFFF"><b>(III)</b></td>
                                            <td bgcolor = "#FFFFFF">ROI (in % per day)</td>
                                            <td bgcolor = "#FFFFFF">
                                                ' . $roi . '
                                            </td>
                                        </tr>

                                        <tr>
                                            <td bgcolor = "#FFFFFF"><b>(IV)</b></td>
                                            <td bgcolor = "#FFFFFF">Total interest charge during the entire Tenure of the loan</td>
                                            <td bgcolor = "#FFFFFF">&#8377;&nbsp;
                                                ' . number_format(round((($loan_recommended * $roi * $tenure) / 100), 0), 2) . '
                                            </td>
                                        </tr>
                                        <tr>
                                            <td bgcolor = "#FFFFFF"><b>(V)</b></td>
                                            <td bgcolor = "#FFFFFF">Processing Fee (Including 18% GST)</td>
                                            <td bgcolor = "#FFFFFF">&#8377;&nbsp;
                                                ' . number_format(round($admin_fee, 0), 2) . '
                                            </td>
                                        </tr>
                                        <tr>
                                            <td bgcolor = "#FFFFFF"><b>(VI)</b></td>
                                            <td bgcolor = "#FFFFFF">Insurance charges, if any (in &#8377;)</td>
                                            <td bgcolor = "#FFFFFF">Nil</td>
                                        </tr>
                                        <tr>
                                            <td bgcolor = "#FFFFFF"><b>(VII)</b></td>
                                            <td bgcolor = "#FFFFFF">Others (if any) (in &#8377;)</td>
                                            <td bgcolor = "#FFFFFF">Nil</td>
                                        </tr>
                                        <tr>
                                            <td bgcolor = "#FFFFFF"><b>(VIII)</b></td>
                                            <td bgcolor = "#FFFFFF">Net disbursed amount</td>
                                            <td bgcolor = "#FFFFFF">&#8377;&nbsp;
                                                ' . number_format(round($net_disbursal_amount, 0), 2) . '
                                            </td>
                                        </tr>
                                        <tr>
                                            <td bgcolor = "#FFFFFF"><b>(IX)</b></td>
                                            <td bgcolor = "#FFFFFF">Total Repayment Amount</td>
                                            <td bgcolor = "#FFFFFF">&#8377;&nbsp;
                                                ' . number_format(round($repayment_amount, 0), 2) . '

                                            </td>
                                        </tr>
                                        <tr>
                                            <td bgcolor = "#FFFFFF"><b>(X)</b></td>
                                            <td bgcolor = "#FFFFFF" style="width:min-content">Annual Percentage Rate - Effective annualized interest rate (in %)
                                                (Considering the ROI of ' . $roi . '% per day)</td>
                                            <td bgcolor = "#FFFFFF">
                                                ' . ($roi * 365) . '
                                            </td>
                                        </tr>
                                        <tr>
                                            <td bgcolor = "#FFFFFF"><b>(XI)</b></td>
                                            <td bgcolor = "#FFFFFF">Tenure of the Loan (days)</td>
                                            <td bgcolor = "#FFFFFF">
                                                ' . $tenure . '&nbsp;Days
                                            </td>
                                        </tr>
                                        <tr>
                                            <td bgcolor = "#FFFFFF"><b>(XII)</b></td>
                                            <td bgcolor = "#FFFFFF">Repayment frequency by the borrower</td>
                                            <td bgcolor = "#FFFFFF">One Time Only</td>
                                        </tr>
                                        <tr>
                                            <td bgcolor = "#FFFFFF"><b>(XIII)</b></td>
                                            <td bgcolor = "#FFFFFF">Number of installments of repayment</td>
                                            <td bgcolor = "#FFFFFF">1</td>
                                        </tr>
                                        <tr>
                                            <td bgcolor = "#FFFFFF"><b>(XIV)</b></td>
                                            <td bgcolor = "#FFFFFF">Amount of each installment of repayment (in &#8377;)</td>
                                            <td bgcolor = "#FFFFFF">(IX)</td>
                                        </tr>
                                        <tr>
                                            <td bgcolor = "#FFFFFF" colspan="3"><p><strong>Details about Contingent Charges</strong></p></td>
                                        </tr>
                                        <tr>
                                            <td bgcolor = "#FFFFFF"><b>(XV)</b></td>
                                            <td bgcolor = "#FFFFFF">
                                                Rate of annualized penal charges in case of delayed payments (if any)
                                            </td>
                                            <td bgcolor = "#FFFFFF">
                                                Double the <strong>(III)</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td bgcolor = "#FFFFFF" colspan="3"><p><strong>Other Disclosures</strong></p></td>
                                        </tr>
                                        <tr>
                                            <td bgcolor = "#FFFFFF"><b>(XVI)</b></td>
                                            <td bgcolor = "#FFFFFF">
                                                Cooling off/look-up period during which borrower shall not be charged any penalty on prepayment of loan  
                                            </td>
                                            <td bgcolor = "#FFFFFF">
                                                3 Days
                                            </td>
                                        </tr>
                                        <tr>
                                            <td bgcolor = "#FFFFFF"><b>(XVII)</b></td>
                                            <td bgcolor = "#FFFFFF">
                                                Name, designation, Address and phone number of nodal grievance
                                                redressal officer designated specifically to deal with FinTech/ digital lending related complaints/ issues
                                            </td>
                                            <td bgcolor = "#FFFFFF">
                                                <p>' . $CONTACT_PERSON . '</p>
                                                <p>Mobile:' . $REGISTED_MOBILE . '</p>
                                                <p>Address: ' . $REGISTED_ADDRESS . '</p>
                                            </td>
                                        </tr>


                                    </table>
                                    <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
                                    <br/><br/><br/><br/><br/><br/>
                                    <table width="667" border="0" cellpadding="1" cellspacing="1" align="center" style="font-family:Arial, Helvetica, sans-serif; line-height:17px; font-size:13px; border:solid 1px #ddd; padding:0px 7px;">
                                        <tr>
                                            <td colspan="2" valign="middle"><p style="font-size: 18px; color: #0363a3; font-size:18px;"><img src="' . SANCTION_LETTER_HEADER . '" width="760" height="123" border="0" usemap="#Map" /></td>
                                        </tr>
                                        <tr>
                                            <td align="right"><span style="color: #0363a3; font-size:16px;">Date : ' . $sanction_date . '</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>To,</strong></td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td><strong>' . $title . '  </strong>' . $fullname . '.</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td>' . $residence_address . '</td>
                                            <td>&nbsp;</td>
                                        </tr>

                                        <tr>
                                            <td><strong>Contact  No. :</strong> +91-' . $mobile . '</td>
                                            <td>&nbsp;</td>
                                        </tr>

                                        <tr>
                                            <td colspan="2">Thank you for  showing your interest in ' . BRAND_NAME . ' and giving us an opportunity to serve  you.</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">We are pleased  to inform you that your loan application has been approved as per the below  mentioned terms and conditions. </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><strong>' . BRAND_NAME . ',  a brand name under ' . COMPANY_NAME . ' (RBI approved NBFC – Reg No. ' . RBI_LICENCE_NUMBER . ')  <br>' . REGISTED_ADDRESS . '</strong></td>                                        
                                        </tr>
                                        <tr>
                                            <td colspan="2">This sanction will be subject to the following Terms and Conditions:</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <table width="100%" border="0" cellpadding="4" cellspacing="1" bgcolor="#ddd" style="font-size:11px;margin-top:10px;">
                                                    <tr>
                                                        <td width = "43%" align = "left" valign = "middle" bgcolor = "#FFFFFF"><strong>Customer Name</strong></td>
                                                        <td width = "4%" align = "center" valign = "middle" bgcolor = "#FFFFFF"><strong>:</strong></td>
                                                        <td width = "53%" align = "left" valign = "middle" bgcolor = "#FFFFFF">' . $fullname . ' </td>
                                                    </tr>
                                                    <tr>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF"><strong>Sanctioned Loan Amount (Rs.)</strong></td>
                                                        <td align = "center" valign = "middle" bgcolor = "#FFFFFF"><strong>:</strong></td>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF">' . number_format(round($loan_recommended, 0), 2) . '/- </td>
                                                    </tr>
                                                    <tr>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF"><strong>Rate of Interest (%) per day</strong> </td>
                                                        <td align = "center" valign = "middle" bgcolor = "#FFFFFF"><strong>:</strong></td>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF">' . number_format($roi, 2) . ' </td>
                                                    </tr>
                                                    <tr>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF"><strong>Date of Sanction</strong></td>
                                                        <td align = "center" valign = "middle" bgcolor = "#FFFFFF"><strong>:</strong></td>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF">' . $sanction_date . ' </td>
                                                    </tr>
                                                    <tr>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF"><strong>Total Repayment Amount (Rs.</strong>) </td>
                                                        <td align = "center" valign = "middle" bgcolor = "#FFFFFF"><strong>:</strong></td>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF">' . number_format(round($repayment_amount, 0), 2) . '/- </td>
                                                    </tr>
                                                    <tr>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF"><strong>Tenure in Days</strong></td>
                                                        <td align = "center" valign = "middle" bgcolor = "#FFFFFF"><strong>:</strong></td>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF">' . $tenure . ' </td>
                                                    </tr>
                                                    <tr>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF"><strong>Repayment Date</strong></td>
                                                        <td align = "center" valign = "middle" bgcolor = "#FFFFFF"><strong>:</strong></td>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF">' . $repayment_date . ' </td>
                                                    </tr>
                                                    <tr>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF"><strong>Penal Interest(%) per day</strong></td>
                                                        <td align = "center" valign = "middle" bgcolor = "#FFFFFF"><strong>:</strong></td>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF">' . round(($roi * 2), 2) . ' </td>
                                                    </tr>
                                                    <tr>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF"><strong>Processing Fee </strong> (<strong>Rs.)</strong></td>
                                                        <td align = "center" valign = "middle" bgcolor = "#FFFFFF"><strong>:</strong></td>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF">' . number_format(round($admin_fee, 0), 2) . '/- (Including 18% GST) </td>
                                                    </tr>
                                                    <tr>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF"><strong>Repayment Cheque(s)</strong></td>
                                                        <td align = "center" valign = "middle" bgcolor = "#FFFFFF"><strong>:</strong></td>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF">-</td>
                                                    </tr>
                                                    <tr>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF"><strong>Cheque drawn on (name of the Bank)</strong></td>
                                                        <td align = "center" valign = "middle" bgcolor = "#FFFFFF"><strong>:</strong></td>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF">-</td>
                                                    </tr>
                                                    <tr>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF"><strong>Cheque and NACH Bouncing Charges (Rs.)</strong></td>
                                                        <td align = "center" valign = "middle" bgcolor = "#FFFFFF"><strong>:</strong></td>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF">1,000.00/- per bouncing/dishonour.</td>
                                                    </tr>
                                                    <tr>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF"><strong>Annualised ROI (%)</strong></td>
                                                        <td align = "center" valign = "middle" bgcolor = "#FFFFFF"><strong>:</strong></td>
                                                        <td align = "left" valign = "middle" bgcolor = "#FFFFFF">' . round(($roi * 365), 2) . '</td>
                                                    </tr>
                                                </table>

                                            </td>
                                        </tr>

                                        <tr>
                                            <td colspan = "2">Henceforth visiting (physically) your Workplace and Residence has your concurrence on it.</td>
                                        </tr>
                                        <tr>
                                            <td colspan = "2">Kindly request you to go through above mentioned terms and conditions and provide your kind acceptance over E-mail so that we can process your loan for final disbursement. </td>
                                        </tr>
                                        <tr>
                                            <td colspan = "2"><strong style = "color:#0363a3;">Best Regards</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan = "2"><strong style = "color:#0363a3;">Team ' . BRAND_NAME . '</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan = "2"><strong style = "color:#0363a3;">(Brand Name for ' . COMPANY_NAME . ')</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan = "2"><strong>Kindly Note:</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan = "2">Non-payment of loan on time will adversely affect your Credit score, further reducing your chances of getting loan again</td>
                                        </tr>
                                        <tr>
                                            <td colspan = "2">Upon approval the processing fee will be deducted from your Sanction amount and balance amount will be disbursed to your account.</td>
                                        </tr>
                                        <tr>
                                            <td colspan = "2">This Sanction letter is valid for 24 Hours only.</td>
                                        </tr>
                                        <tr>
                                            <td colspan = "2">You can Prepay/Repay the loan amount using our link <a href = "' . LOAN_REPAY_LINK . '" target = "_blank" style = "color:#0363a3; text-decoration:blink;">' . LOAN_REPAY_LINK . '</a></td>
                                        </tr>
                                        <tr>
                                            <td colspan = "2"><img src = "' . SANCTION_LETTER_FOOTER . '" width="760" height="44"/></td>
                                        </tr>

                                    </table><br/><br/>

                                    <div>

                                        <p  align = "center" style="text-align:center; margin-bottom:0px;"><b><u><span style="font-size:12.0pt;line-height:107%;font-family:Arial, Helvetica, sans-serif;">TERMS AND CONDITIONS</span></u></b></p>


                                        <p><span  style="font-size:10.0pt;line-height:107%;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E;margin-bottom:0px;background:white">The Borrower confirms to have read and understood these Terms of Agreement before accepting a personal loan (“Loan”) offer with us. By clicking on the "eSign" button, the Borrower shall be deemed to have electronically accepted these Terms of Agreement. To the extent of any inconsistency, these Terms of Agreement shall prevail.</span></p>

                                        <ol style="margin-top:0in" start=1 type=1> <li  style="color:#2E2E2E;margin-bottom:0in;line-height:150%;background:white"><span  style="font-size:10.0pt;line-height:150%;font-family:Arial, Helvetica, sans-serif;">The Loan shall carry a fixed rate of interest specified at the time of applying for the loan.</span></li>
                                            <li  style="color:#2E2E2E;margin-bottom:0in;line-height:150%;background:white"><span  style="font-size:10.0pt;line-height:150%;font-family:Arial, Helvetica, sans-serif;">The Loan amount shall be disbursed, after debiting processing fees, in Borrower’s account only with the Bank on accepting the Personal Loan Terms of Agreement.</span></li>
                                            <li  style="color:#2E2E2E;margin-bottom:0in;line-height:150%;background:white"><span  style="font-size:10.0pt;line-height:150%;font-family:Arial, Helvetica, sans-serif;">The repayment amount shall consist of principal and interest components. The Borrower confirms to repay the repayment amount on the specified repayment date.</span></li>
                                            <li  style="color:#2E2E2E;margin-bottom:0in;line-height:150%; background:white"><span  style="font-size:10.0pt;line-height:150%;font-family:Arial, Helvetica, sans-serif;">If repayment is not done by the specified date, the Borrower will be liable for penal interest.</span></li>
                                            <li  style="color:#2E2E2E;margin-bottom:0in;line-height:150%; background:white"><span  style="font-size:10.0pt;line-height: 150%;font-family:Arial, Helvetica, sans-serif;">If any repayment cheque is not honored, the Borrower will be liable for dishonor charges and penal interest.</span></li>
                                            <li  style="color:#2E2E2E;margin-bottom:0in;line-height:150%; background:white"><span  style="font-size:10.0pt;line-height: 150%;font-family:Arial, Helvetica, sans-serif;">The Borrower agrees to pay the processing fee, payment dishonor charges, etc.</span></li>
                                            <li  style="color:#2E2E2E;margin-bottom:0in;line-height:150%; background:white"><span  style="font-size:10.0pt;line-height: 150%;font-family:Arial, Helvetica, sans-serif;">Any overdue payment incurs interest at the penal interest rate (which is higher than the usual interest rate). We may change the interest rate if required by the statutory/regulatory authority.</span></li>
                                            <li  style="color:#2E2E2E;margin-bottom:0in;line-height:150%;background:white"><span  style="font-size:10.0pt;line-height:150%;font-family:Arial, Helvetica, sans-serif;">The Borrower agrees that fees and charges specified may be revised from time to time and binding on the Borrower.</span></li>
                                            <li  style="color:#2E2E2E;margin-bottom:0in;line-height:150%;background:white"><span  style="font-size:10.0pt;line-height:150%;font-family:Arial, Helvetica, sans-serif;">The Borrower agrees to pay applicable Goods and Service Tax.</span></li>
                                        </ol>

                                        <p  style="margin-top:14.0pt;margin-right:0in;margin-bottom:0in;margin-left:.5in;line-height:normal;background:white"><span style="font-size:10.0pt;font-family:Arial, Helvetica, sans-serif;color:black">Borrower Representations</span><span  style="font-size:10.0pt;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">&nbsp;– The Borrower represents and covenants that the Borrower:</span></p>

                                        <p  style="margin-top:14.0pt;margin-right:0in;margin-bottom:0in;margin-left:1.0in;text-indent:-.25in;line-height:150%;background:white;border:none"><span  style="font-size:10.0pt;line-height:150%;font-family:Arial, Helvetica, sans-serif;color:black">●</span><span  style="font-size:10.0pt;line-height:150%;font-family:Arial, Helvetica, sans-serif;color:black">&nbsp; will use the Loan amount for legitimate purposes.</span></p>

                                        <p  style="margin-top:0in;margin-right:0in;margin-bottom:0in;margin-left:1.0in;text-indent:-.25in;line-height:150%;background:white;border:none"><span  style="font-size:10.0pt;line-height:150%;font-family:Arial, Helvetica, sans-serif;color:black">●</span><span  style="font-size:10.0pt;line-height:150%;font-family:Arial, Helvetica, sans-serif;color:black">&nbsp; will not use the Loan for any speculative, antisocial, or prohibited purposes. If the Loan funds have been used for purposes as stated above, we shall be entitled to do all acts and things that we deem necessary to comply with its policies. The Borrower agrees to bear all costs and expenses incurs as a result thereof.</span></p>

                                        <p  style="margin-top:0in;margin-right:0in;margin-bottom:0in;margin-left:1.0in;text-indent:-.25in;line-height:150%;background:white;border:none"><span  style="font-size:10.0pt;line-height:150%;font-family:Arial, Helvetica, sans-serif;color:black">●</span><span  style="font-size:10.0pt;line-height:150%;font-family:Arial, Helvetica, sans-serif;color:black">&nbsp; shall notify, within 7 calendar days, if any information given by the Borrower changes. In the specific event of a change in address due to relocation or any other reason, the Borrower shall intimate the new address as soon as possible but no later than 15 days of such a change.</span></p>

                                        <p  style="margin-top:0in;margin-right:0in;margin-bottom:0in;margin-left:1.0in;text-indent:-.25in;line-height:150%;background:white;border:none"><span  style="font-size:10.0pt;line-height:150%;font-family:Arial, Helvetica, sans-serif;color:black">●</span><span  style="font-size:10.0pt;line-height:150%;font-family:Arial, Helvetica, sans-serif;color:black">&nbsp; information of the Borrower with us is correct, complete, and updated.</span></p>

                                        <p  style="margin-top:0in;margin-right:0in;margin-bottom:0in;margin-left:1.0in;text-indent:-.25in;line-height:150%;background:white;border:none"><span  style="font-size:10.0pt;line-height:150%;font-family:Arial, Helvetica, sans-serif;color:black">●</span><span  style="font-size:10.0pt;line-height:150%;font-family:Arial, Helvetica, sans-serif;color:black">&nbsp; has read and understood the Privacy Policy available on our website.</span></p>

                                        <p  style="margin-top:14.0pt;margin-right:0in;margin-bottom:0in;margin-left:.5in;line-height:normal;background:white"><b><span style="font-size:10.0pt;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">Notice</span></b><span style="font-size:10.0pt;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">&nbsp;– We may send Loan-related notices, statements, or any other communication to the Borrower by in-app messages, short message system (SMS), Whatsapp messaging service, electronic mail, ordinary prepaid post, or personal delivery to Borrower’s registered communication address. Communication and notices sent by in-app messages/facsimile/SMS/email will be considered to have been sent and received by the Borrower on the same day irrespective of carrier delays. Communication and notices sent by pre-paid mail will be considered to have been delivered on the day immediately after the date of posting.</span></p>



                                        <p  style="margin-top:100.0pt;margin-right:0in;margin-bottom:0in;margin-left:.5in;line-height:normal;background:white"><b><span  style="font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">Consent to Disclose</span></b></p>

                                        <p  style="margin-top:14.0pt;margin-right:0in;margin-bottom: 0in;margin-left:1.0in;text-indent:-.25in;line-height:115%;background:white"><span style="font-size:10.0pt;line-height:115%;font-family:Arial, Helvetica, sans-serif; color:#2E2E2E">●<span style="font:7.0pt "></span></span><span  style="font-size:10.0pt;line-height:115%;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">&nbsp;&nbsp; The Borrower has no objection in and gives consent for sharing Loan details including Borrower’s personal details to branches, affiliates, services providers, agents, contractors, surveyors, agencies, credit bureaus, etc. in or outside India, to enable us to provide services under the arrangement with the third parties including customized solutions and marketing services. The Borrower confirms that the authorization given above shall be valid till written communication of withdrawal of Borrower’s consent is acknowledged by us. </span></p>

                                        <p  style="margin-top:0in;margin-right:0in;margin-bottom:0in;margin-left:1.0in;text-indent:-.25in;line-height:115%;background:white"><span style="font-size:10.0pt;line-height:115%;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">●<span style="font:7.0pt "></span></span><span  style="font-size:10.0pt;line-height:115%;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">&nbsp;&nbsp; The Borrower understands and accepts the risks involved in sharing personal information including sensitive personal information like account details with a third party.</span></p>

                                        <p  style="margin-top:0in;margin-right:0in;margin-bottom:0in;margin-left:1.0in;text-indent:-.25in;line-height:115%;background:white"><span style="font-size:10.0pt;line-height:115%;font-family:Arial, Helvetica, sans-serif; color:#2E2E2E">●<span style="font:7.0pt "> </span></span><span  style="font-size:10.0pt;line-height:115%;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">&nbsp;&nbsp; The Borrower consents to share Borrower’s personal information with third parties for processing, statistical or risks analysis, conducting credit or anti-money laundering checks, designing financial services or related products, marketing financial services or related products, customer recognition on our website/app, offering relevant product and service offers to customers, etc.</span></p>

                                        <p  style="margin-top:0in;margin-right:0in;margin-bottom:0in;margin-left:1.0in;text-indent:-.25in;line-height:115%;background:white"><span style="font-size:10.0pt;line-height:115%;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">●<span style="font:7.0pt "></span></span><span  style="font-size:10.0pt;line-height:115%;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">&nbsp;&nbsp; The Borrower agrees that we may disclose Borrower’s information to the Reserve Bank of India, other statutory/regulatory authorities, arbitrator, credit bureaus, local authorities, credit rating agency, information utility, marketing agencies, and service providers if required.</span></p>

                                        <p  style="margin-top:0in;margin-right:0in;margin-bottom:0in;margin-left:1.0in;text-indent:-.25in;line-height:115%;background:white"><span style="font-size:10.0pt;line-height:115%;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">●<span style="font:7.0pt "></span></span><span  style="font-size:10.0pt;line-height:115%;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">&nbsp;&nbsp; The Borrower authorizes to provide monthly details of the Loan Account and the credit facilities extended to the Borrower to credit information companies. We may obtain information on credit facilities availed by the Borrower from other financial institutions to determine whether we can extend additional credit facilities. On the regularization of the Borrowers account, we will update the credit information companies accordingly.</span></p>

                                        <p  style="margin-top:0in;margin-right:0in;margin-bottom:0in;margin-left:1.0in;text-indent:-.25in;line-height:115%;background:white"><span style="font-size:10.0pt;line-height:115%;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">●<span style="font:7.0pt "></span></span><span  style="font-size:10.0pt;line-height:115%;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">&nbsp;&nbsp; The Borrower authorizes to verify any of the information of the Borrower including Borrower’s credit standing from anyone we may consider appropriate including credit bureaus, local authority, credit rating agencies etc.</span></p>

                                        <p  style="margin-top:0in;margin-right:0in;margin-bottom:0in;margin-left:1.0in;text-indent:-.25in;line-height:115%;background:white"><span style="font-size:10.0pt;line-height:115%;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">●<span style="font:7.0pt "></span></span><span  style="font-size:10.0pt;line-height:115%;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">&nbsp;&nbsp; The Borrower authorizes us to inform Borrower’s employer of any default in repayment and agrees to do things necessary to fulfill Borrower’s obligations. </span></p>

                                        <p  style="margin-top:0in;margin-right:0in;margin-bottom:0in;margin-left:1.0in;text-indent:-.25in;line-height:115%;background:white"><span style="font-size:10.0pt;line-height:115%;font-family:Arial, Helvetica, sans-serif; color:#2E2E2E">●<span style="font:7.0pt "></span></span><span  style="font-size:10.0pt;line-height:115%;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">&nbsp;&nbsp; Our records about the Loan shall be conclusive and binding on the Borrower.</span></p>

                                        <p  style="margin-top:0in;margin-right:0in;margin-bottom:0in;margin-left:1.0in;text-indent:-.25in;line-height:115%;background:white"><span style="font-size:10.0pt;line-height:115%;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">●<span style="font:7.0pt "></span></span><span  style="font-size:10.0pt;line-height:115%;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">&nbsp;&nbsp; In case of default in repayment of the Loan amount, Borrower authorizes us and our collection assistance specialist engaged, to contact Borrower over phone, office or visit Borrower’s residence or such other place where Borrower is located. </span></p>

                                        <p  style="margin-top:14.0pt;margin-right:0in;margin-bottom:0in;margin-left:.5in;line-height:normal;background:white"><b><span style="font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">Effective Date</span></b><span style="font-size:10.0pt;font-family:Arial, Helvetica, sans-serif; color:#2E2E2E">&nbsp;– These Terms of Agreement shall be effective from the date of disbursal of the loan amount.</span></p>

                                        <p  style="margin-right:0in;margin-bottom: 0in;margin-left:.5in;line-height:normal;background:white"><b><span style="font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">Assignment</span></b><span style="font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">&nbsp;</span><span style="font-size:10.0pt;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">– The Borrower agrees that, with or without intimation to the Borrower, be authorized to sell and /or assign to any third party, the Loan and all outstanding dues under this Agreement, in any manner, in whole or in part, and on such terms as we may decide. Any such sale or assignment shall bind the Borrower, and the Borrower shall accept the third party as its sole creditor or creditor jointly with us and in such event, the Borrower shall pay us or such creditor or as we may direct, the outstanding amounts due from the Borrower under this Agreement.</span></p>

                                        <p  style="margin-top:14.0pt;margin-right:0in;margin-bottom:0in;margin-left:.5in;line-height:normal;background:white"><b><span style="font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">Governing Law &amp; Jurisdiction</span></b><span  style="font-size:10.0pt;font-family:Arial, Helvetica, sans-serif;color:#2E2E2E">&nbsp;– The Loan shall be governed by the laws of India and all claims and disputes arising out of or in connection with the Loan shall be settled by arbitration. Any arbitration award/ direction passed shall be final and binding on the parties. The language of the arbitration shall be English/Hindi and the venue of such arbitration shall be in New Delhi.</span></p>

                                    </div>

                                </body>
                            </html>';

            $file_name = "sanction_letter_" . $lead_id . "_" . rand(1000, 9999) . ".pdf";
            
            //$file_path_with_name = UPLOAD_PATH . $file_name;
            $file_path_with_name = '/var/tmp/' . $file_name;
            require_once __DIR__ . '/../../vendor/autoload.php';

            $mpdf = new \Mpdf\Mpdf();

            $mpdf->WriteHTML($html_string);
            
            //$mpdf->Output(UPLOAD_TEMP_PATH . $file_name, 'F');
            $mpdf->Output($file_path_with_name, 'F');

            if(file_exists($file_path_with_name)) {

                $upload_return = uploadDocument($file_path_with_name,$lead_id,2,'pdf');
                $return_array['status'] = 1;
                $return_array['file_name'] = $upload_return['file_name'];
                unlink($file_path_with_name);
                //$this->updateLeads(['lead_id' => $lead_id], ['cam_sanction_letter_file_name' => $file_name], 'credit_analysis_memo');
                $this->updateLeads(['lead_id' => $lead_id], ['cam_sanction_letter_file_name' => $upload_return['file_name']], 'credit_analysis_memo');
            } else {

                $return_array['errors'] = "File does not exist. Please check offline";
            }
        } catch (Exception $e) {

            $return_array['errors'] = "PDF Error : " . $e->getMessage();
        }

        return $return_array;
    }

    public function calcAmount($input) {
        $loan_recommended = $input['loan_recommended'];
        $obligations = $input['obligations'];
        $monthly_salary = $input['monthly_salary'];
        $eligible_foir_percentage = $input['eligible_foir_percentage'];
        $roi = ($input['roi'] ? $input['roi'] : 1);
        $user_type = $input['user_type'];
        $processing_fee_percent = ($input['processing_fee_percent']) ? ($input['processing_fee_percent']) : 0;
        $disbursal_date = $input['disbursal_date'];
        $repayment_date = $input['repayment_date'];

        $d1 = strtotime($disbursal_date);
        $d2 = strtotime($repayment_date);
        $tenure = 0;
        if (!empty($d2)) {
            $datediff = $d2 - $d1;
            $tenure = round($datediff / (60 * 60 * 24));
        }

        $admin_fee = round(($loan_recommended * $processing_fee_percent) / 100);
        $gst = round(($admin_fee * 18) / 100);
        $total_admin_fee = round($admin_fee + $gst);
        $repayment_amount = ($loan_recommended + ($loan_recommended * $roi * $tenure) / 100);

        $data['roi'] = $roi;
        $data['panel_roi'] = ($roi * 2);
        $data['tenure'] = $tenure;
        $data['repayment_amount'] = round($repayment_amount);
        $data['admin_fee'] = $total_admin_fee;
        $data['adminFeeWithGST'] = $gst;
        $data['adminFeeGST'] = $gst;
        $data['total_admin_fee'] = $admin_fee;
        $data['net_disbursal_amount'] = $loan_recommended - $total_admin_fee;
        $data['final_foir_percentage'] = number_format((($loan_recommended + $obligations) / $monthly_salary) * 100, 2);
        $data['foir_enhanced_by'] = number_format($data['final_foir_percentage'] - $eligible_foir_percentage, 2);

        return $data;
    }

    public function getDisbursementTransLogs($lead_id) {
        return $this->db->select("*")->from("lead_disbursement_trans_log")->where(['disb_trans_lead_id' => $lead_id, 'disb_trans_active' => 1, 'disb_trans_deleted' => 0])->order_by('disb_trans_id', 'desc')->get()->row_array();
    }

    public function nocSettledPayment($lead_id) {
        // $lead_id = 338470; // settled case
        $conditions = ['LD.lead_id' => $lead_id, 'LD.lead_status_id' => 17, 'CO.collection_active' => 1, 'CO.collection_deleted' => 0];
        $result = $this->db->select('L.loan_no, CAM.loan_recommended, CAM.disbursal_date, LD.first_name, LD.email, LD.lead_status_id, CO.date_of_recived,  CO.closure_payment_updated_on, CO.discount')

                // ->where('CO.repayment_type', 17) // SETTLED
                ->where($conditions)
                ->from('leads LD')
                ->join('credit_analysis_memo CAM', 'CAM.lead_id = LD.lead_id', 'INNER')
                ->join('loan L', 'L.lead_id = LD.lead_id', 'INNER')
                ->join('collection CO', 'CO.lead_id = LD.lead_id', 'INNER')
                ->order_by('CO.id', 'desc');
        $res = $result->get();

        if ($res->num_rows() > 0) {
            $sql = $res->row();
            $lead_status_id = $sql->lead_status_id;
            $loan_no = $sql->loan_no;
            $customer_name = $sql->first_name;
            $customer_email = $sql->email;
            $loan_recommended = number_format($sql->loan_recommended, 2);
            $disbursal_date = date('d-m-Y', strtotime($sql->disbursal_date));
            $date_of_recived = date('d-m-Y', strtotime($sql->date_of_recived));
            // $loanCloserDate = date('d-m-Y', strtotime($sql->closure_payment_updated_on));
            $loanCloserDate = date('d-m-Y', strtotime(timestamp));
            $discount = number_format($sql->discount, 2);

            $conditions3 = ['payment_verification' => 1, 'lead_id' => $lead_id, 'collection_active' => 1, 'collection_deleted' => 0];
            $sql13 = $this->db->select('SUM(received_amount) as total_paid')->where($conditions3)->from('collection')->get();
            $recoveredAmount = $sql13->row();

            $ReceivedAmount = 0;
            if ($recoveredAmount->total_paid > 0) {
                $ReceivedAmount = number_format($recoveredAmount->total_paid, 2);
            }

            $message = '
                    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns="http://www.w3.org/1999/xhtml">
                        <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>Settlement Letter</title>
                        </head>
                        <body>
                        <table width="778" border="0" align="center" cellpadding="0" cellspacing="0" style="padding:10px; border:solid 1px #ccc; font-family:Arial, Helvetica, sans-serif;">
                        <tr>
                            <td width="381" align="left"><img src="' . EMAIL_BRAND_LOGO . '" width="234" height="60" /></td>
                            <td width="11" align="left">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="margin-bottom:10px; padding-bottom:10px;"><hr style="background:#ddd !important;"></td>
                        </tr>
                        <tr>
                            <td colspan="2" align="center"><strong style="line-height:25px; font-size:20px; text-decoration:underline;">Settlement Letter</strong></td>
                        </tr>
                        <tr>
                            <td align="left" valign="top"><strong style="line-height:25px;">Date : ' . $loanCloserDate . '</strong></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td align="left" valign="top"><strong style="line-height:25px;">Loan No. : ' . $loan_no . '</strong></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td align="left" valign="top"><strong style="line-height:25px;">Mr/Ms ' . $customer_name . '</strong></td>
                            <td>&nbsp;</td>
                        </tr>
                        <br/><br/>
                        <tr>
                            <td align="left" valign="top"><span style="font-size:17px;
                            line-height: 25px;
                            padding-bottom: 6px; text-align:justify;">This is to certify that Mr/Ms <strong>' . $customer_name . '</strong> who had taken a short-term loan from<strong> ' . COMPANY_NAME . '</strong> for Rs ' . $loan_recommended . ' on ' . $disbursal_date . '.<br/><br/>We have received Rs. ' . $ReceivedAmount . ' from your total loan outstanding and the same has been settled on ' . $date_of_recived . '.<br/><br/>For Closure of your loan, kindly pay the settlement amount of Rs ' . $discount . '.</span> </td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td align="left" valign="top"><p>This is the amount which we are giving you after discount . If you want to close your loan or remove your settlement status from CIBIL, Kindly pay the same amount.<br /> 
                            </p></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td align="left" valign="top"><strong style="line-height:25px;">For ' . COMPANY_NAME . '</strong></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td align="left" valign="top"><strong style="line-height:25px;">Authorised Signatory</strong></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td><img src="' . AUTHORISED_SIGNATORY . '" width="184" height="97" /></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="margin-top:20px;">&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="margin-top:20px;"><strong>* This is Computer generated document, hence does not require any signature</strong></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="margin-top:20px;">&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td><strong style="color:#2e5f8b;">' . COMPANY_NAME . ' </strong></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="line-height:25px;">' . REGISTED_ADDRESS . '</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="line-height:25px;">Phone: ' . REGISTED_MOBILE . '</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td><span style="font-size:17px;
                            line-height: 25px;
                            padding-bottom: 6px; text-align:justify; margin:10px 0px;"><a href="mailto:' . INFO_EMAIL . '" style="text-decoration:blink;">' . INFO_EMAIL . ' </a></span></td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="2" align="left">&nbsp;</td>
                        </tr>
                        </table>
                        </body>
                    </html>
                ';

            $subject = 'NOC Letter Case Settled';

            $return_array = lw_send_email($customer_email, $subject, $message, BCC_NOC_EMAIL, CARE_EMAIL);

            $user_id = 0;

            if (!empty($_SESSION['isUserSession']['user_id'])) {
                $user_id = $_SESSION['isUserSession']['user_id'];
            }

            if ($return_array['status'] == 1) {

                $loan_data = array(
                    'loan_noc_letter_sent_status' => 1,
                    'loan_noc_letter_sent_datetime' => date("Y-m-d H:i:s"),
                    'loan_noc_letter_sent_user_id' => $user_id
                );

                $this->globalUpdate(['lead_id' => $lead_id], $loan_data, 'loan');
                $lead_remark = "Settlement NOC Letter sent successfully";
                $data = "true";
            } else {
                $lead_remark = "Settlement NOC Letter sending failed";
                $data = "false";
            }

            $this->insertLeadFollowupLog($lead_id, $lead_status_id, $lead_remark);
        } else {
            $data = "false";
        }
        return $data;
    }

    public function nocDisbursalWaivedOFF($lead_id, $remarks = "") {
        $sql = $this->getCAMDetails($lead_id);
        $camDetails = $sql->row();
        $remarks = (!empty($remarks) ? $remarks : "-");

        $subject = 'Loan Disbursal Waive OFF';
        $fullname = $camDetails->first_name . ' ' . $camDetails->middle_name . ' ' . $camDetails->sur_name;
        $loan_no = $camDetails->loan_no;

        $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns="http://www.w3.org/1999/xhtml">
                            <head>
                                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                <title>' . $subject . '</title>
                            </head>
                            <body>
                                <table width="778" border="0" align="center" cellpadding="0" cellspacing="0" style="padding:10px; border:solid 1px #ccc; font-family:Arial, Helvetica, sans-serif; font-size:15px;">
                                    <tr>
                                        <td width="404" align="left"><img src="' . EMAIL_BRAND_LOGO . '" alt="logo" style=" border-radius: 5px;width: 180px;"/></td>
                                        <td width="4" align="left">&nbsp;</td>
                                        <td width="368" align="right"><table width="100%" border="0">
                                                <tr>
                                                    <td align="right"><strong>Customer Name- ' . $fullname . '</strong></td>
                                                </tr>
                                                <tr>
                                                    <td align="right"><table cellspacing="0" cellpadding="0">
                                                            <tr>
                                                                <td align="right">Loan No.: ' . $loan_no . '</td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            <hr style="background:#ddd !important;"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            <table width="100%" border="0" cellpadding="5" cellspacing="1" bgcolor="#CCCCCC">
                                                <tbody>
                                                    <tr>
                                                        <td colspan="3" width="42%" bgcolor="#FFFFFF" style="padding:10px;"><i>Note - This case has been waive off due to some technical reason. Please do not consider this case as dusbursed case. </i></td>
                                                    </tr>
                                                    <tr>
                                                        <th align="left" valign="middle" bgcolor="#FFFFFF">Name</th>
                                                        <td align="left" valign="middle" bgcolor="#FFFFFF">' . $fullname . '</td>
                                                    </tr>
                                                    <tr>
                                                        <th align="left" valign="middle" bgcolor="#FFFFFF">Loan Amount (Rs.)</th>
                                                        <td align="left" valign="middle" bgcolor="#FFFFFF">' . (number_format($camDetails->loan_recommended, 2)) . '/- </td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">Rate of Interest (%)</td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">' . $camDetails->roi . ' per day</td>
                                                    </tr>
                                                    <tr>
                                                        <th align="left" valign="middle" bgcolor="#FFFFFF">Disbursal Date</th>
                                                        <td align="left" valign="middle" bgcolor="#FFFFFF">' . $camDetails->disbursal_date . ' </td>
                                                    </tr>
                                                    <tr>
                                                        <th align="left" valign="middle" bgcolor="#FFFFFF">Commitment Payback Date</th>
                                                        <td align="left" valign="middle" bgcolor="#FFFFFF">' . $camDetails->repayment_date . '</td>
                                                    </tr>
                                                    <tr>
                                                        <th align="left" valign="middle" bgcolor="#FFFFFF">Repayment Amount (Rs.)</th>
                                                        <td align="left" valign="middle" bgcolor="#FFFFFF">' . (number_format($camDetails->repayment_amount, 2)) . '/-</td>
                                                    </tr>
                                                    <tr>
                                                        <th align="left" valign="middle" bgcolor="#FFFFFF">Administrative Fee (Rs.)</th>
                                                        <td align="left" valign="middle" bgcolor="#FFFFFF">' . (number_format($camDetails->admin_fee, 2)) . '/-</td>
                                                    </tr>
                                                    <tr>
                                                        <th align="left" valign="middle" bgcolor="#FFFFFF">Disbursed Waived By</th>
                                                        <td align="left" valign="middle" bgcolor="#FFFFFF">' . $_SESSION['isUserSession']['name'] . '</td>
                                                    </tr>
                                                    <tr>
                                                        <th align="left" valign="middle" bgcolor="#FFFFFF">Disbursed Waived On</th>
                                                        <td align="left" valign="middle" bgcolor="#FFFFFF">' . date('d-m-Y H:i:s') . '</td>
                                                    </tr>
                                                    <tr>
                                                        <th align="left" valign="middle" bgcolor="#FFFFFF">Remarks</th>
                                                        <td align="left" valign="middle" bgcolor="#FFFFFF">' . $remarks . '</td>
                                                    </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="padding-bottom:10px; padding-top:10px;"><strong>Best Regards,  </strong></td>
                                        <td style="padding-bottom:10px; padding-top:10px;">&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Team ' . BRAND_NAME . '</strong></td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                </table>
                            </body>
                        </html>';

        $to_email = INFO_EMAIL;
        $cc_email = CC_DISBURSAL_WAIVE_EMAIL;
        $bcc_email = BCC_DISBURSAL_WAIVE_EMAIL;

        $return_array = lw_send_email($to_email, $subject, $message, $bcc_email, $cc_email);
        $data = "true";

        return $data;
    }

    public function getLeadLogs($lead_id) {

        $leadsDetails = array();

        if (!empty($lead_id)) {

            $leadsDetails = $this->db->select('LF.id as log_id, S.status_id, U.name, LF.created_on, S.status_name, LF.reason, LF.remarks')
                    ->from('lead_followup LF')
                    ->join('master_status S', 'S.status_id = LF.lead_followup_status_id', 'LEFT')
                    ->join('users U', 'LF.user_id = U.user_id', 'LEFT')
                    ->where(['lead_id' => $lead_id, 'lead_followup_active' => 1, 'lead_followup_deleted' => 0])
                    ->order_by('log_id', 'desc')
                    ->get();

            //echo $this->db->_error_message();
//            traceObject($this->db->error()); 
        }

        return $leadsDetails;
    }

    public function getSanctionFollowupLogs($lead_id) {

        $leadsDetails = array();

        if (!empty($lead_id)) {

            $leadsDetails = $this->db->select('LSF.lsf_id, LSF.lsf_remarks, LSF.lsf_created_on, S.m_sf_status_id, S.m_sf_status_name, U.name')
                    ->from('lead_sanction_followups LSF')
                    ->join(' master_sanction_followup_status S', 'S.m_sf_status_id  = LSF.lsf_status_id', 'LEFT')
                    ->join('users U', 'LSF.lsf_user_id = U.user_id', 'LEFT')
                    ->where(['LSF.lsf_lead_id' => $lead_id, 'LSF.lsf_active' => 1, 'LSF.lsf_deleted' => 0])
                    ->order_by('LSF.lsf_id', 'DESC')
                    ->get();
        }

        return $leadsDetails;
    }

    public function getCustomerAccountDetails($lead_id) {

        $return_array = array("status" => 0, "banking_data" => array());

        if (!empty($lead_id)) {
            $tempDetails = $this->db->select("*")->from("customer_banking")->where(["lead_id" => $lead_id, "account_status_id" => 1, "customer_banking_active" => 1, "customer_banking_deleted" => 0])->order_by('id', 'DESC')->get();
            if ($tempDetails->num_rows()) {
                $return_array["status"] = 1;
                $return_array["banking_data"] = $tempDetails->row_array();
            }
        }

        return $return_array;
    }

    public function getCustomerDetails($lead_id) {

        $sql = 'LD.lead_id, LD.status, LD.stage, LD.lead_status_id, LD.lead_black_list_flag, LD.loan_no, LD.pancard, LD.city_id, LD.state_id, C.first_name, C.middle_name, C.sur_name, C.dob, LD.mobile, C.alternate_mobile, C.email, C.alternate_email';

        $this->db->select($sql);
        $this->db->from($this->table . ' LD');
        $this->db->join($this->table_lead_customer . ' C', 'C.customer_lead_id = LD.lead_id AND C.customer_active = 1 AND C.customer_deleted = 0', 'INNER');
        $this->db->where(['LD.lead_id' => $lead_id]);
        $leadDetails = $this->db->get();

        $lead_data = array();

        if ($leadDetails->num_rows() > 0) {
            $lead_data = $leadDetails->row_array();
        }

        return $lead_data;
    }

    public function checkBlackListedCustomer($lead_id, $inactive_bl_flag = 0) {

        $return_array = array("status" => 0, "error_msg" => '');

        $sql = 'LD.lead_id, LD.pancard, C.first_name, C.middle_name, C.sur_name, C.dob, LD.mobile, C.alternate_mobile,C.email, C.alternate_email';

        $this->db->select($sql);
        $this->db->from($this->table . ' LD');
        $this->db->join($this->table_lead_customer . ' C', 'C.customer_lead_id = LD.lead_id AND C.customer_active = 1 AND C.customer_deleted = 0', 'INNER');
        $this->db->where(['LD.lead_id' => $lead_id]);
        $leadDetails = $this->db->get();

        if ($leadDetails->num_rows() > 0) {

            $lead_data = $leadDetails->row_array();

            $first_name = !empty($lead_data['first_name']) ? strtoupper($lead_data['first_name']) : "";

            $dob = !empty($lead_data['dob']) ? $lead_data['dob'] : "";

            $pancard = !empty($lead_data['pancard']) ? strtoupper($lead_data['pancard']) : "";

            $mobile = !empty($lead_data['mobile']) ? $lead_data['mobile'] : "";

            $alternate_mobile = !empty($lead_data['alternate_mobile']) ? $lead_data['alternate_mobile'] : "";

            $email = !empty($lead_data['email']) ? strtoupper($lead_data['email']) : "";

            $alternate_email = !empty($lead_data['alternate_email']) ? strtoupper($lead_data['alternate_email']) : "";

            $sql = "SELECT * FROM customer_black_list";

            $where = "";

            if (!empty($first_name) && !empty($dob)) {
                $where .= "OR (bl_customer_first_name='$first_name' AND bl_customer_dob='$dob')";
            }

            if (!empty($pancard)) {
                $where .= "OR bl_customer_pancard='$pancard'";
            }

            if (!empty($mobile)) {
                $where .= "OR bl_customer_mobile='$mobile'";
                $where .= "OR bl_customer_alternate_mobile='$mobile'";
            }

            if (!empty($alternate_mobile)) {
                $where .= "OR bl_customer_mobile='$alternate_mobile'";
                $where .= "OR bl_customer_alternate_mobile='$alternate_mobile'";
            }


            if (!empty($email)) {
                $where .= "OR bl_customer_email='$email'";
                $where .= "OR bl_customer_alternate_email='$email'";
            }

            if (!empty($alternate_email)) {
                $where .= "OR bl_customer_email='$alternate_email'";
                $where .= "OR bl_customer_alternate_email='$alternate_email'";
            }

            $where = ltrim($where, 'OR ');

            $bl_active_str = "bl_active = 1 AND bl_deleted = 0";

            if ($inactive_bl_flag == 1) {
                $bl_active_str = "bl_active = 0 AND bl_deleted = 1";
            }

            $blacklistResult = $this->db->query($sql . " WHERE $bl_active_str AND (" . $where . ") ORDER BY bl_id DESC");

            if ($blacklistResult->num_rows() > 0) {

                $black_list_data = $blacklistResult->row_array();

                $return_array['status'] = 1;

                $error = "";

                if (!empty($black_list_data['bl_customer_first_name']) && !empty($black_list_data['bl_customer_dob']) && !empty($first_name) && !empty($dob) && $black_list_data['bl_customer_first_name'] == $first_name && $black_list_data['bl_customer_dob'] == $dob) {
                    $error .= ", First Name and DOB";
                }

                if (!empty($black_list_data['bl_customer_pancard']) && !empty($pancard) && $black_list_data['bl_customer_pancard'] == $pancard) {
                    $error .= ", PAN";
                }

                if (!empty($black_list_data['bl_customer_mobile']) && ((!empty($mobile) && $black_list_data['bl_customer_mobile'] == $mobile) || (!empty($alternate_mobile) && $black_list_data['bl_customer_mobile'] == $alternate_mobile))) {
                    $error .= ", Mobile";
                }

                if (!empty($black_list_data['bl_customer_alternate_mobile']) && ((!empty($mobile) && $black_list_data['bl_customer_alternate_mobile'] == $mobile) || (!empty($alternate_mobile) && $black_list_data['bl_customer_alternate_mobile'] == $alternate_mobile))) {
                    $error .= ", Alternate Mobile";
                }

                if (!empty($black_list_data['bl_customer_email']) && ((!empty($email) && $black_list_data['bl_customer_email'] == $email) || (!empty($alternate_email) && $black_list_data['bl_customer_email'] == $alternate_email))) {
                    $error .= ", Email";
                }

                if (!empty($black_list_data['bl_customer_alternate_email']) && ((!empty($email) && $black_list_data['bl_customer_alternate_email'] == $email) || (!empty($alternate_email) && $black_list_data['bl_customer_alternate_email'] == $alternate_email))) {
                    $error .= ", Alternate Email";
                }

                $error = ltrim($error, ', ');

                $return_array['error_msg'] = "Blacklisted Customer : " . $black_list_data['bl_loan_no'] . " | Due to " . $error;
            }
        }

        return $return_array;
    }

    public function sent_loan_closed_noc_letter($lead_id) {

        $sql = "SELECT DISTINCT LD.loan_no, LD.lead_status_id, CAM.loan_recommended as loan_amount, concat_ws(' ', LC.first_name, LC.middle_name, LC.sur_name) as customer_name, ";
        $sql .= " LD.email, CAM.disbursal_date as loaninitiatedDate,";
        $sql .= " LC.customer_digital_ekyc_flag, LC.customer_digital_ekyc_done_on,";
        $sql .= " CAM.repayment_date, CAM.repayment_amount, L.loan_closure_date";
        $sql .= " FROM leads LD INNER JOIN lead_customer LC ON LC.customer_lead_id=LD.lead_id";
        $sql .= " INNER JOIN loan L ON L.lead_id=LD.lead_id";
        $sql .= " INNER JOIN credit_analysis_memo CAM ON(CAM.lead_id=LD.lead_id)";
        $sql .= " WHERE LD.lead_id=" . $lead_id . " AND LD.lead_status_id=16";

        $sql = $this->db->query($sql)->row();

        $to = $sql->email;

        if (!empty($to)) {
            $query = $this->db->select_sum('received_amount')->where(['payment_verification' => 1, 'collection_active' => 1, 'collection_deleted' => 0])->where('lead_id', $lead_id)->from('collection')->get()->row();
            $lead_status_id = $sql->lead_status_id;
            $loanCloserDate = date('d-M-Y');
            $customer_name = $sql->customer_name;
            $repayment_date = $sql->repayment_date;

            $loanInitiatedDate = date('d-m-Y', strtotime($sql->loaninitiatedDate));

            $date_of_recived = date('d-m-Y', strtotime($sql->loan_closure_date));

            $loan_amount = number_format($sql->loan_amount, 2);
            $received_amount = number_format($query->received_amount, 2);

            $sent_pre_approved_type = 0;

            $expire_date = date('Y-m-d', strtotime('+10 days', strtotime($repayment_date)));

            if (strtotime($expire_date) > strtotime($sql->loan_closure_date) && $sql->customer_digital_ekyc_flag == 1) {

                $sent_pre_approved_type = 1;
                $camp_kyc_date = strtotime(date("Y-m-d", strtotime("+85 day", strtotime($sql->customer_digital_ekyc_done_on))));

                $camp_current_datetime = strtotime(date("Y-m-d"));

                if ($camp_kyc_date > $camp_current_datetime) {
                    $sent_pre_approved_type = 2;
                }
            }

            $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                            <html xmlns="http://www.w3.org/1999/xhtml">
                                <head>
                                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                    <title>FULL PAYMENT NOC LETTER</title>
                                </head>
                            
                                <body>
                                    <table width="778" border="0" align="center" cellpadding="0" cellspacing="0" style="padding:10px; border:solid 1px #ccc; font-family:Arial, Helvetica, sans-serif;">
                                        <tr>
                                            <td align="left"><img src="' . EMAIL_BRAND_LOGO . '" width="180" height="85" /></td>
                                        </tr>
                                        <tr>
                                            <td><hr style="background:#ddd !important;"></td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td align="center" valign="top"><strong>No Objection Certificate</strong></td>
                                        </tr>
                                        <tr>
                                            <td align="center" valign="top">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td align="left" valign="top"><strong style="line-height:25px;">Date : ' . $loanCloserDate . '</strong></td>
                                        </tr>
                                        <tr>
                                            <td align="left" valign="top"><strong style="line-height:25px;">Loan No. : ' . $sql->loan_no . '</strong></td>
                                        </tr>
                                        <tr>
                                            <td align="left" valign="top"><strong style="line-height:25px;">Mr/Ms ' . $sql->customer_name . '</strong></td>
                                        </tr>
                                        <tr>
                                            <td align="left" valign="top">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td align="left" valign="top"><span style="font-size:17px;line-height: 25px;padding-bottom: 6px; text-align:justify;">This is to certify that Mr/Ms ' . $sql->customer_name . ' who had taken a short-term loan from ' . COMPANY_NAME . ' for Rs. ' . $loan_amount . ' on ' . $loanInitiatedDate . ' has repaid Rs. ' . $received_amount . ' on ' . $date_of_recived . '.</span></td>
                                        </tr>
                                        <tr>
                                            <td align="left" valign="top"><span style="font-size:17px;line-height: 25px;padding-bottom: 6px; text-align:justify;">This is the full amount which was due from him/her, including interest.</span> </td>
                                        </tr>
                                        <tr>
                                            <td align="left" valign="top"><span style="font-size:17px;line-height: 25px;padding-bottom: 6px; text-align:justify;">Hence, there are no more dues from Mr/Ms ' . $sql->customer_name . ' against loan taken by him/her from ' . COMPANY_NAME . '</span><br /><br /> </td>
                                        </tr>
                                        <tr>
                                            <td align="left" valign="top"><span style="font-size:17px;line-height: 25px;padding-bottom: 6px; text-align:justify; margin:10px 0px;"><strong>For ' . COMPANY_NAME . ' </strong></span></td>
                                        </tr>
                                        <tr>
                                            <td align="left" valign="top"><span style="font-size:17px;line-height: 25px;padding-bottom: 6px; text-align:justify; margin:10px 0px;"><strong>Authorised Signatory</strong></span></td>
                                        </tr>
                                        <tr>
                                            <td><img src="' . AUTHORISED_SIGNATORY . '" width="150" height="150" /></td>
                                        </tr>
                                        <tr>
                                            <td style="margin-top:20px;">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td style="margin-top:20px;"><span style="font-size:17px;line-height:20px;padding-bottom: 6px; text-align:justify; margin:20px 0px;"><strong>* This is Computer generated document, hence does not require any signature.</strong></span></td>
                                        </tr>
                                        <tr>
                                            <td style="margin-top:20px;">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td><strong style="color:#2e5f8b;">' . COMPANY_NAME . ' </strong></td>
                                        </tr>
                                        <tr>
                                            <td><span style="font-size:17px;line-height: 25px;padding-bottom: 6px; text-align:justify;">' . REGISTED_ADDRESS . '</span> </td>
                                        </tr>
                                        <tr>
                                            <td><span style="font-size:17px;line-height: 25px;padding-bottom: 6px; text-align:justify; margin:10px 0px;">Email - <a href="mailto:' . CARE_EMAIL . '" style="text-decoration:blink;">' . CARE_EMAIL . '</a></span></td>
                                        </tr>
                                        <tr>
                                            <td><span style="font-size:17px;line-height: 25px;padding-bottom: 6px; text-align:justify; margin:10px 0px;">Website - <a href="' . WEBSITE_URL . '" target="_blank" style="text-decoration:blink;">' . WEBSITE . '</a></span></td>
                                        </tr>
                                        <tr>
                                            <td align="left">&nbsp;</td>
                                        </tr>
                                    </table>
                                </body>
                            </html>';

            $return_array = lw_send_email($to, 'NOC Letter Full Payment', $message, BCC_NOC_EMAIL, CARE_EMAIL);

            $user_id = 0;

            if (!empty($_SESSION['isUserSession']['user_id'])) {
                $user_id = $_SESSION['isUserSession']['user_id'];
            }

            if ($return_array['status'] == 1) {

                $loan_data = array(
                    'loan_noc_letter_sent_status' => 1,
                    'loan_noc_letter_sent_datetime' => date("Y-m-d H:i:s"),
                    'loan_noc_letter_sent_user_id' => $user_id
                );

                $this->globalUpdate(['lead_id' => $lead_id], $loan_data, 'loan');
                $lead_remark = "Full Payment NOC Letter sent successfully.";
                $data = "true";
            } else {
                $lead_remark = "Full Payment NOC Letter sending failed.";
                $data = "false";
            }

            $return_array_pa_email = $this->preApprovedOfferEmailer($to, $customer_name, $lead_id, $sent_pre_approved_type);

            if ($return_array_pa_email['status'] == 1) {
                $lead_remark .= "<br/> Pre-Approved Offer email sent successfully.[$sent_pre_approved_type]";
            } else {
                $lead_remark .= "<br/> Pre-Approved Offer email not sent.";
            }

            $return_array_Customer_Feedback_Emailer = $this->send_Customer_Feedback_Emailer($lead_id, $to, $customer_name);

            if ($return_array_Customer_Feedback_Emailer['status'] == 1) {
                $lead_remark .= "<br/> Customer Feedback email sent successfully.";
            }


            $this->insertLeadFollowupLog($lead_id, $lead_status_id, $lead_remark);
        } else {
            $data = "false";
        }

        return $data;
    }

    public function insertLeadFollowupLog($lead_id, $lead_status_id, $remark) {

        if (empty($lead_id) || empty($lead_status_id) || empty($remark)) {
            return null;
        }

        $user_id = 0;

        if (isset($_SESSION['isUserSession']['user_id']) && !empty($_SESSION['isUserSession']['user_id'])) {
            $user_id = $_SESSION['isUserSession']['user_id'];
        }

        $insert_log_array = array();
        $insert_log_array['lead_id'] = $lead_id;
        $insert_log_array['user_id'] = $user_id;
        $insert_log_array['lead_followup_status_id'] = $lead_status_id;
        $insert_log_array['remarks'] = addslashes($remark);
        $insert_log_array['created_on'] = date("Y-m-d H:i:s");

        return $this->db->insert('lead_followup', $insert_log_array);
    }

    public function checkUserHaveManyRoles($conditions) {
        $result = array('status' => 0, 'user_roles' => array());

        $this->db->select('user_roles.user_role_type_id');
        $this->db->where($conditions);
        $this->db->from('user_roles');
        $user_roles = $this->db->get();

        $user_role_array = array();

        if (!empty($user_roles->num_rows())) {
            $role_array = $user_roles->result_array();
            foreach ($role_array as $row) {
                $user_role_array[] = $row['user_role_type_id'];
            }
            $result['user_roles'] = $user_role_array;
            $result['status'] = 1;
        }

        return $result;
    }

    public function preApprovedOfferEmailer($customer_email, $customer_name, $lead_id = 0, $sent_pre_approved_type) {

        $return_array = array();

        $app_token = $this->encrypt->encode($lead_id);
        $apply_url = "";

        if ($sent_pre_approved_type == 2) {

            $apply_url = WEBSITE_URL . "pre-approved-customer?utm_source=pre-approved-offeremail&app_token=" . $app_token;

            if (ENVIRONMENT == "production") {
                $apply_url = WEBSITE_URL . "pre-approved-customer?utm_source=pre-approved-offeremail&app_token=" . $app_token;
            }
        } else if ($sent_pre_approved_type == 1) {

            $apply_url = WEBSITE_URL . "apply-now?utm_source=pre-approved-offeremail";

            if (ENVIRONMENT == "production") {
                $apply_url = WEBSITE_URL . "apply-now?utm_source=pre-approved-offeremail";
            }
        } else {
            $return_array['status'] = 0;
            return $return_array;
        }

        $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                    <html xmlns="http://www.w3.org/1999/xhtml">
                        <head>
                            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                            <title>PRE-APPROVED OFFER</title>
                        </head>

                        <body>
                            <table width="550" border="0" align="center" cellpadding="0" cellspacing="0" style="border:solid 1px #0463a3;">
                                <tr>
                                    <td><a href="' . WEBSITE_URL . '" target="_blank"><img src="' . EMAIL_BRAND_LOGO . '" alt="bharatloan-logo" style="border-radius: 11px;padding: 6px 8px;"></a></td>
                                </tr>
                                <tr>
                                    <td align="center" valign="top" bgcolor="#FFFFFF" style="background:url(' . PRE_APPROVED_LINES . '); color:#0664a4; font-size:25px; font-weight:bold; font-family:Arial, Helvetica, sans-serif;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td align="center" valign="top" bgcolor="#FFFFFF" style="background:url(' . PRE_APPROVED_LINES . '); color:#0664a4; font-size:25px; font-weight:bold; font-family:Arial, Helvetica, sans-serif;">Dear ' . $customer_name . '</td>
                                </tr>
                                <tr>
                                    <td valign="top" bgcolor="#FFFFFF" style="background:url(' . PRE_APPROVED_LINES . ');"><a href="' . $apply_url . '" target="_blank"><img src="' . PRE_APPROVED_BANNER . '" width="550" height="459" /></a></td>
                                </tr>
                                <tr>
                                    <td align="center" style="font-size: 17px;font-family: sans-serif;padding: 10px;font-weight: bold;color: #404040;">Pre-Approved  |  Zero Paper Work  |  Instant Disbursal</td>
                                </tr>
                                <tr>
                                    <td align="center"><img src="' . PRE_APPROVED_LINE_COLOR . '" alt="line" width="100%" height="3" /></td>
                                </tr>
                                <tr>
                                    <td align="center" bgcolor="#efefef" style=" font-family: sans-serif; font-weight:bold; color:#0664a4;padding: 10px; font-size:16px;"><a href="tel:' . REGISTED_MOBILE . '" style="color:#0664a4; text-decoration:blink;"><img src="' . PRE_APPROVED_PHONE_ICON . '" alt="phone" width="20" height="20" style="position: relative;top: 4px;"> ' . REGISTED_MOBILE . '</a>   <a href="' . WEBSITE_URL . '" target="_blank" style="color:#0664a4; text-decoration:blink;"><img src="' . PRE_APPROVED_WEB_ICON . '" width="20" height="20" style="position: relative;top: 4px;"> ' . WEBSITE . '</a>&nbsp;   <img src="' . PRE_APPROVED_EMAIL_ICON . '" width="20" height="20" style="position: relative;top: 4px;"> <a href="mailto:' . INFO_EMAIL . '" style="color:#0664a4; text-decoration:blink;">' . INFO_EMAIL . '</a></td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding:10px 10px 7px 10px;">
                                        <img src="' . PRE_APPROVED_ARROW_LEFT . '" alt="arrow-left" width="37" height="11" style="position: relative;top: -9px;" />&nbsp;<a href="' . APPLE_STORE_LINK . '" target="_blank" style="text-decoration:blink;"><img src="' . APPLE_STORE_ICON . '" alt="aap-store" width="106" height="30" /></a> <a href="' . LINKEDIN_LINK . '" target="_blank" style="text-decoration:blink;"><img src="' . LINKEDIN_ICON . '" alt="linkdin" width="29" height="30" /></a> <a href="' . INSTAGRAM_LINK . '" target="_blank" style="text-decoration:blink;"><img src="' . INSTAGRAM_ICON . '" alt="instagram" width="29" height="30" /> </a><a href="' . FACEBOOK_LINK . '" target="_blank" style="text-decoration:blink;"><img src="' . FACEBOOK_ICON . '" width="29" height="30" />  </a>

                                        <a href="' . TWITTER_LINK . '" target="_blank" style="text-decoration:blink;"><img src="' . TWITTER_ICON . '" width="29" height="30" />  </a>
                                        <a href="' . YOUTUBE_LINK . '" target="_blank" style="text-decoration:blink;"><img src="' . YOUTUBE_ICON . '" alt="youtube" width="29" height="30" /> </a><a href="' . ANDROID_STORE_LINK . '" target="_blank" style="text-decoration:blink;"><img src="' . ANDROID_STORE_ICON . '" alt="goolge-play" width="106" height="30" /></a>&nbsp;<img src="' . PRE_APPROVED_ARROW_RIGHT . '" width="37" height="11"  style="position: relative;top: -9px;"></td>
                                </tr>
                                <tr>
                                    <td align="center"><img src="' . PRE_APPROVED_LINE_COLOR . '" alt="line" width="100%" height="5" /></td>
                                </tr>
                            </table>
                        </body>
                    </html>';

        $return_array = lw_send_email($customer_email, 'PRE-APPROVED OFFER | ' . WEBSITE, $message);

        return $return_array;
    }

    public function send_Customer_Feedback_Emailer($lead_id, $customer_email, $customer_name) {

        $feedback_url = FEEDBACK_WEB_PATH . $this->encrypt->encode($lead_id);

        $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                            <html xmlns="http://www.w3.org/1999/xhtml">
                                <head>
                                    <meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
                                    <title>Customer Feedback</title>
                                </head>
                                <body>
                                    <table width = "550" border = "0" align = "center" cellpadding = "0" cellspacing = "0" style = "padding:10px 10px 2px 10px; border:solid 2px #0363a3; font-family:Arial, Helvetica, sans-serif;border-radius:3px;">
                                        <tr>
                                            <td align = "left"><table width = "100%" border = "0" style = "height:270px; padding:30px 0px; background:url(' . FEEDBACK_HEADER . ');">
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><img src = "' . FEEDBACK_LINE . '" alt = "line" width = "34" height = "8" /></td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong style = "color:#0463A3;">Dear ' . $customer_name . ', </strong></td>
                                        </tr>
                                        <tr>
                                            <td><img src = "' . FEEDBACK_LINE . '" alt = "line" width = "34" height = "15" /></td>
                                        </tr>
                                        <tr>
                                            <td>Greetings from <span style = "color:#0463A3; font-size:16px;"><strong>' . BRAND_NAME . '</strong></span></td>
                                        </tr>
                                        <tr>
                                            <td><img src = "' . FEEDBACK_LINE . '" alt = "line" width = "34" height = "15" /></td>
                                        </tr>
                                        <tr>
                                            <td><p style = "margin:0px;color: #000;line-height: 25px;border-radius: 3px;">Please take few minutes to give us feedback about our service by filling in this short Customer Feedback Form.</p></td>
                                        </tr>
                                        <tr>
                                            <td><img src = "' . FEEDBACK_LINE . '" alt = "line" width = "34" height = "8" /></td>
                                        </tr>
                                        <tr>
                                            <td><p style = "margin:0px;color: #000;line-height: 25px;border-radius: 3px;">We are interested in your honest opinion. Your survey responses will remain confidential and will only by viewed in aggregate with answers from other respondents.<br/>
                                                </p></td>
                                        </tr>
                                        <tr>
                                            <td><img src = "' . FEEDBACK_LINE . '" alt = "line" width = "34" height = "8" /></td>
                                        </tr>
                                        <tr>
                                            <td style = "line-height:25px;"></td>
                                        </tr>
                                        <tr>
                                            <td align = "left"><strong style = "color:#0463A3; font-size:18px;">Thank you.<br />
                                                </strong></td>
                                        </tr>
                                        <tr>
                                            <td align = "left"><img src = "' . FEEDBACK_LINE . '" alt = "line" width = "34" height = "15" /></td>
                                        </tr>
                                        <tr>
                                            <td align = "left"><strong style = "color:#000; font-size:15px;">Customer Experience Team</strong> </td>
                                        </tr>
                                        <tr>
                                            <td align = "left"><img src = "' . FEEDBACK_LINE . '" alt = "line" width = "34" height = "2" /></td>
                                        </tr>
                                        <tr>
                                            <td align = "left"><strong style = "color:#0463A3; font-size:18px;"><em style = "font-size:16px; font-style:normal;">' . WEBSITE . ' </em></strong></td>
                                        </tr>
                                        <tr>
                                            <td><img src = "' . FEEDBACK_LINE . '" alt = "line" width = "34" height = "8" /></td>
                                        </tr>
                                        <tr>
                                            <td align = "center" style = "text-align:center;"><a href = "' . $feedback_url . '" target = "_blank" style = "background:#0463a3;border-radius: 3px;padding: 8px 30px;color: #fff;text-decoration: blink;font-weight: bold;">Click Here</a></td>
                                        </tr>
                                        <tr>
                                            <td align = "center" style = "text-align:center;"><img src = "' . FEEDBACK_LINE . '" alt = "line" width = "34" height = "25" /></td>
                                        </tr>
                                        <tr>
                                            <td align = "left" style = "text-align:left; color: #000;line-height: 25px;">If you are unable to click on the above button. Please <a href = "' . $feedback_url . '" target = "_blank" style = "color:#0463a3; text-decoration:underline;">click here</a></td>
                                        </tr>
                                        <tr>
                                            <td>&nbsp;
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan = "3" align = "center" bgcolor = "#0463A3" style = "padding: 7px 0px 7px 0px;color: #fff;font-size: 16px;border-radius: 3px;"><a href = "tel:' . REGISTED_MOBILE . '" style = "color:#fff; text-decoration:blink;"><img src = "' . FEEDBACK_PHONE_ICON . '" width = "20" height = "20" alt = "phone" style = "position: relative;top: 4px;"/> ' . REGISTED_MOBILE . '</a> | <a href = "' . WEBSITE_URL . '" target = "_blank" style = "color:#fff; text-decoration:blink;"><img src = "' . FEEDBACK_WEB_ICON . '" width = "20" height = "20" alt = "phone" style = "position: relative;top: 4px;"/> ' . WEBSITE . '</a> | <a href = "mailto:' . INFO_EMAIL . '" style = "color:#fff; text-decoration:blink;"><img src = "' . FEEDBACK_EMAIL_ICON . '" width = "20" height = "20" alt = "phone" style = "position: relative;top: 4px;"/> ' . INFO_EMAIL . '</a></td>
                                        </tr>
                                        <tr>
                                            <td colspan = "3" align = "center" bgcolor = "#FFFFFF" style = "padding:10px; color:#fff; font-size:14px; font-weight:bold; padding-bottom:0px;"><a href = "' . LINKEDIN_LINK . '" target = "_blank"><img src = "' . LINKEDIN_ICON . '" alt = "linkdin" width = "30" height = "30" /></a> <a href = "' . INSTAGRAM_LINK . '" target = "_blank"><img src = "' . INSTAGRAM_ICON . '" alt = "instagram" width = "30" height = "30" /></a> <a href = "' . FACEBOOK_LINK . '" target = "_blank"><img src = "' . FACEBOOK_ICON . '" alt = "facebook" width = "30" height = "30" /></a> <a href = "' . TWITTER_LINK . '" target = "_blank"><img src = "' . TWITTER_ICON . '" alt = "twitter" width = "30" height = "30" /></a> <a href = "' . YOUTUBE_LINK . '" target = "_blank"> <img src = "' . YOUTUBE_ICON . '" alt = "youtube" width = "30" height = "30" /><span style = "padding:2px; color:#fff; font-size:14px; font-weight:bold; padding-bottom:0px;"></span></a><span style = "padding:2px; color:#fff; font-size:14px; font-weight:bold; padding-bottom:0px;"><a href = "' . ANDROID_STORE_LINK . '" target = "_blank"><img src = "' . ANDROID_STORE_ICON . '" alt = "google-play" width = "100" height = "30" /></a> <a href = "' . APPLE_STORE_LINK . '" target = "_blank"><img src = "' . APPLE_STORE_ICON . '" alt = "app-store" width = "100" height = "30" /></a></span></td>
                                        </tr>
                                    </table>
                                </body>
                            </html>';

        $return_array = lw_send_email($customer_email, 'FEEDBACK FORM | ' . WEBSITE, $message, "", "", "", CARE_EMAIL);

        return $return_array;
    }

    public function sent_ekyc_request_email($lead_id) {

        $sql = "SELECT LD.lead_status_id, concat_ws(' ', LC.first_name, LC.middle_name, LC.sur_name) as customer_name, LD.email";
        $sql .= " FROM leads LD INNER JOIN lead_customer LC ON LC.customer_lead_id=LD.lead_id ";
        $sql .= " WHERE LD.lead_id=$lead_id";

        $sql = $this->db->query($sql)->row();

        $to = $sql->email;

        if (!empty($to)) {

            $lead_status_id = $sql->lead_status_id;

            $customer_name = $sql->customer_name;

            $enc_lead_id = $this->encrypt->encode($lead_id);

            $digital_ekyc_url = base_url("aadhaar-veri-request") . "?refstr=" . $enc_lead_id;

            $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                            <html xmlns="http://www.w3.org/1999/xhtml">
                                <head>
                                    <meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
                                    <title>Digital E-KYC</title>
                                </head>
                                <body>
                                    <table width = "800" border = "0" align = "center" cellpadding = "0" cellspacing = "0" style = "border:solid 1px #ddd;font-family:Arial, Helvetica, sans-serif;">
                                        <tr>
                                            <td width = "800" colspan = "2" style = "background:url(' . EKYC_HEADER_BACK . ');" >
                                                <table width = "100%" border = "0" cellpadding = "0" cellspacing = "0">
                                                    <tr>
                                                        <td width = "25%" valign = "top"><a href = "' . WEBSITE_URL . '" target = "_blank"><img src = "' . EKYC_BRAND_LOGO . '" alt = "logo" width = "200" height = "50" style = "margin-top:10px;"></a></td>
                                                        <td width = "64%" align = "center" valign = "middle"><strong style = "color:#fff; font-size:20px;">DIGITAL E-KYC</strong></td>
                                                        <td width = "11%" align = "right"><img src = "' . EKYC_LINES . '" width = "26" height = "147" /></td>
                                                    </tr>
                                                </table>

                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan = "2" valign = "top"><table width = "100%" border = "0" cellpadding = "0" cellspacing = "0" style = "padding:0px 10px;">
                                                    <tr>
                                                        <td width = "50%" rowspan = "10" valign = "top" style = "border-right:solid 1px #e52255;"><table width = "100%" border = "0">
                                                                <tr>
                                                                    <td align = "center" valign = "middle">&nbsp;
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td valign = "middle"><span style = "font-weight:bold; font-size:25px; color:#e52255;">Dear ' . $customer_name . ' </span></td>
                                                                </tr>
                                                                <tr>
                                                                    <td align = "center">&nbsp;
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 25px;">We thank you for showing interest in ' . WEBSITE . ' . Your loan application has been assigned for credit approval.</p></td>
                                                                </tr>
                                                                <tr>
                                                                    <td align = "center">&nbsp;
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 25px;">In order to process your loan application further, please do the e-kyc via DigiLocker using your Aadhaar.</p></td>
                                                                </tr>
                                                                <tr>
                                                                    <td align = "center">&nbsp;
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 25px;">Once you click on the Digital E-KYC button, You will redirect to the DigiLocker portal, where you need to follow the steps given in <b>"How it Works"</b> on your right side.</p></td>
                                                                </tr>
                                                                <tr>
                                                                    <td align = "center">&nbsp;
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 25px;">Kindly click on the below button to proceed.</p></td>
                                                                </tr>
                                                                <tr>
                                                                    <td align = "center">&nbsp;
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td align = "center"><a href = "' . $digital_ekyc_url . '" style = "background: #e52255;color: #fff;padding: 7px 15px;border-radius: 3px;text-decoration: blink;">Digital E-KYC</a></td>
                                                                </tr>
                                                                <tr>
                                                                    <td align = "center"><img src = "' . EKYC_LINES . '" alt = "line" width = "26" height = "10" /></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 20px;">If you are not able to click on the above button, then please copy and paste this URL <a href = "' . $digital_ekyc_url . '">' . $digital_ekyc_url . ' </a> in the browser to proceed.</p></td>
                                                                </tr>
                                                            </table></td>
                                                        <td width = "0" rowspan = "10" align = "center">&nbsp;
                                                        </td>
                                                        <td colspan = "2" align = "left">&nbsp;
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan = "2" align = "center"><span style = "font-weight:bold; font-size:25px; color:#e52255;">How it Works</span></td>
                                                    </tr>

                                                    <tr>
                                                        <td colspan = "2" align = "left"><img src = "' . EKYC_LINES . '" alt = "line" width = "26" height = "5" /></td>
                                                    </tr>

                                                    <tr>
                                                        <td width = "23%" align = "left"><a href = "' . EKYC_IMAGES_1_SHOW . '" target = "_blank"><img src = "' . EKYC_IMAGES_1 . '" alt = "1st" width = "172" height = "103" /></a></td>
                                                        <td width = "35%" valign = "top">
                                                            <p style = "color: #e52255;font-size:18px;margin: 0px;padding-left: 10px;"><strong>First Step</strong></p>
                                                            <p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 20px;">Please enter your 12 digits Aadhaar No. and press next.</p>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan = "2" align = "left"><img src = "' . EKYC_LINES . '" alt = "line" width = "26" height = "5" /></td>
                                                    </tr>
                                                    <tr>
                                                        <td align = "left"><a href = "' . EKYC_IMAGES_2_SHOW . '" target = "_blank"><img src = "' . EKYC_IMAGES_2 . '" alt = "2nd" width = "171" height = "103" /></a></td>
                                                        <td align = "left" valign = "top">
                                                            <p style = "color: #e52255;font-size:18px;margin: 0px;padding-left: 10px;"><strong>Second Step</strong></p>
                                                            <p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 20px;">Please enter the OTP received in your registered mobile no. with Aadhaar and press continue.</p>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan = "2" align = "left"><img src = "' . EKYC_LINES . '" alt = "line" width = "26" height = "5" /></td>
                                                    </tr>
                                                    <tr>
                                                        <td align = "left"><a href = "' . EKYC_IMAGES_3_SHOW . '" target = "_blank"><img src = "' . EKYC_IMAGES_3 . '" alt = "3rd" width = "173" height = "103" /></a></td>
                                                        <td align = "left" valign = "top"><p style = "color: #e52255;font-size:18px;margin: 0px;padding-left: 10px;"><strong>Third Step</strong></p>
                                                            <p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 20px;">Press allow to give access of your DigiLocker account for documents verification.</p></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan = "2" align = "left"><img src = "' . EKYC_LINES . '" alt = "line" width = "26" height = "5" /></td>
                                                    </tr>
                                                    <tr>
                                                        <td align = "left"><a href = "' . EKYC_IMAGES_4_SHOW . '" target = "_blank"><img src = "' . EKYC_IMAGES_4 . '" alt = "4th" width = "173" height = "102" /></a></td>
                                                        <td align = "left" valign = "top"><p style = "color: #e52255;font-size:18px;margin: 0px;padding-left: 10px;"><strong>Thank You</strong></p>
                                                            <p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 20px;">Your approval to access DigiLocker account for E-KYC has been successfully submitted.</p></td>
                                                    </tr>
                                                    <tr>
                                                        <td valign = "top" style = "border-right:solid 1px #e52255;">&nbsp;
                                                        </td>
                                                        <td align = "center">&nbsp;
                                                        </td>
                                                        <td align = "left">&nbsp;
                                                        </td>
                                                        <td align = "left" valign = "top">&nbsp;
                                                        </td>
                                                    </tr>

                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan = "4" align = "center" valign = "middle" style = "border-top:solid 1px #ddd; padding-top:5px;">
                                                <a href = "' . APPLE_STORE_LINK . '" target = "_blank"><img src = "' . APPLE_STORE_ICON . '" alt = "app_store" width = "100" height = "30" style = "border-radius: 50px;"></a>
                                                <a href = "' . LINKEDIN_LINK . '" target = "_blank"> <img src = "' . LINKEDIN_ICON . '" alt = "linkdin" width = "32" height = "32" /></a>
                                                <a href = "' . INSTAGRAM_LINK . '" target = "_blank"> <img src = "' . INSTAGRAM_ICON . '" alt = "instagram" width = "32" height = "32" /></a>
                                                <a href = "' . FACEBOOK_LINK . '" target = "_blank"> <img src = "' . FACEBOOK_ICON . '" alt = "facebook" width = "32" height = "32" /></a>
                                                <a href = "' . TWITTER_LINK . '" target = "_blank" style = "color:#fff;"> <img src = "' . TWITTER_ICON . '" alt = "twitter" width = "32" height = "32" /> </a>
                                                <a href = "' . YOUTUBE_LINK . '" target = "_blank" style = "color:#fff;"> <img src = "' . YOUTUBE_ICON . '" alt = "youtube" width = "32" height = "32" /> </a>
                                                <a href = "' . APPLE_STORE_LINK . '" target = "_blank"> <img src = "' . ANDROID_STORE_ICON . '" alt = "google_play" width = "100" height = "30" style = "border-radius: 50px;"></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan = "4" align = "center" valign = "middle" bgcolor = "#e52255" style = "padding:10px; color:#fff; font-weight:normal; font-size:16px;"><a href = "tel:' . REGISTED_MOBILE . '" style = "color:#fff; text-decoration:blink;"><img src = "' . PHONE_ICON . '" width = "16" height = "16" alt = "phone-icon" style = "margin-bottom: -2px;"> ' . REGISTED_MOBILE . ' </a> <a href = "' . WEBSITE_URL . '" target = "_blank" style = "color:#fff; text-decoration:blink;"><img src = "' . WEB_ICON . '" width = "16" height = "16" alt = "web-icon" style = "margin-bottom: -2px;"> ' . WEBSITE . ' </a> <img src = "' . EMAIL_ICON . '" width = "16" height = "16" alt = "email-icon" style = "margin-bottom: -2px;"><a href = "mailto:' . INFO_EMAIL . '" style = "color:#fff; text-decoration:blink;">' . INFO_EMAIL . ' </a></td>
                                        </tr>
                                    </table>
                                </body>
                            </html>';
            //$to='akash.kushwaha@bharatloan.com';
            $return_array = lw_send_email($to, BRAND_NAME . '  | DIGITAL EKYC : ' . $customer_name, $message);

            if ($return_array['status'] == 1) {
                $lead_remark = "Digital E-KYC email sent successfully.";
                $data = "true";
            } else {
                $lead_remark = "Digital E-KYC email sending failed.";
                $data = "false";
            }

            $this->insertLeadFollowupLog($lead_id, $lead_status_id, $lead_remark);
        } else {
            $data = "false";
        }

        return $data;
    }
    
    public function sent_ekyc_request_sms($lead_id){
        $sms_type_id=13;
        $req = array();
        $sql = 'select LD.lead_id,LC.mobile,LC.first_name as name from leads LD inner join lead_customer LC on (LC.customer_lead_id=LD.lead_id) where LD.lead_id=' . $lead_id;
        $result = $this->db->query($sql);
        if ($result->num_rows() > 0) {
            $result = $result->result_array();
        }
        foreach ($result as $row) {
            $req['lead_id'] = $row['lead_id'];
            $req['mobile'] = $row['mobile'];
            $req['name'] = $row['name'];
           
        }
        $req['mobile'] = 8750256406;
        
        $enc_lead_id = $this->encrypt->encode($lead_id);

        $digital_ekyc_url = base_url("aadhaar-veri-request") . "?refstr=" . $enc_lead_id;

        require_once (COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();
        
        $res = $CommonComponent->call_url_shortener_api($digital_ekyc_url);
        
        $req['ekyc_link'] = $res['short_url'];

        $CommonComponent->payday_sms_api($sms_type_id, $req['lead_id'], $req);
    }

    public function getCustomerReferenceDetails($lead_id, $cust_reference_id = 0) {

        $result = array('status' => 0, 'customer_reference' => array());

        $conditions['lcr_lead_id'] = $lead_id;

        if (!empty($cust_reference_id)) {
            $conditions['lcr_id'] = $cust_reference_id;
        }

        $this->db->select('*');
        $this->db->where($conditions);
        $this->db->from('lead_customer_references');

        $customer_reference = $this->db->get();

        if (!empty($customer_reference->num_rows())) {
            $result['customer_reference'] = $customer_reference->result_array();
            $result['status'] = 1;
        }

        return $result;
    }

    public function checkCompanyHolidayDate($repayment_date) {

        $result = array('status' => 0, 'message' => "");

        if (empty($repayment_date)) {
            return $result;
        }

        $repayment_date = date("Y-m-d", strtotime($repayment_date));

        $repayment_day_name = trim(strtolower(date("D", strtotime($repayment_date))));

        $conditions['ch_holiday_date'] = $repayment_date;
        $conditions['ch_active'] = 1;
        $conditions['ch_deleted'] = 0;

        $this->db->select('*');
        $this->db->where($conditions);
        $this->db->from('company_holiday');
        $this->db->order_by('ch_id', 'desc');

        $companyholiday = $this->db->get();

        if (!empty($companyholiday->num_rows())) {
            $companyHolidayDetails = $companyholiday->row_array();
            $result['message'] = "Repayment date cannot be holiday date.[ " . $companyHolidayDetails['ch_holiday_date'] . " - " . $companyHolidayDetails['ch_holiday_name'] . " ]";
            $result['status'] = 1;
        } else if ($repayment_day_name == "sun") {
            $result['message'] = "Repayment date cannot be Sunday";
            $result['status'] = 1;
        }

        return $result;
    }

    public function checkCityStateSourcing($lead_id) {

        $return_array = array();
        $status = 0;
        $error = "";

        $sql = 'LD.lead_id, LD.city_id, LD.state_id';

        $this->db->select($sql);
        $this->db->from($this->table . ' LD');
        $this->db->join($this->table_lead_customer . ' C', 'C.customer_lead_id = LD.lead_id AND C.customer_active = 1 AND C.customer_deleted = 0', 'INNER');
        $this->db->where(['LD.lead_id' => $lead_id]);
        $leadDetails = $this->db->get();

        if ($leadDetails->num_rows() > 0) {

            $lead_data = $leadDetails->row_array();

            $city_id = $lead_data['city_id'];

            $state_id = $lead_data['state_id'];

            if (empty($city_id)) {
                $error = "Missing Current Address City";
            } else if (empty($state_id)) {
                $error = "Missing Current Address State";
            } else {

                $sql = "SELECT MC.m_city_name, MS.m_state_name, MC.m_city_is_sourcing, MS.m_state_is_sourcing FROM master_city MC";
                $sql .= " INNER JOIN master_state MS ON(MC.m_city_state_id=MS.m_state_id)";
                $sql .= " WHERE MC.m_city_state_id=MS.m_state_id AND MC.m_city_active=1 AND MC.m_city_deleted=0 AND MS.m_state_active=1 AND MS.m_state_deleted=0";
                $sql .= " AND MC.m_city_id=$city_id AND MS.m_state_id=$state_id";

                $tempDetails = $this->db->query($sql);

                if ($tempDetails->num_rows() > 0) {

                    $city_state_data = $tempDetails->row_array();

                    if ($city_state_data['m_city_is_sourcing'] != 1) {
                        $error = "Customer current address city is OGL.";
                    } else if ($city_state_data['m_state_is_sourcing'] != 1) {
                        $error = "Customer current address state is OGL.";
                    } else {
                        $error = "";
                        $status = 1;
                    }
                } else {
                    $error = "Customer Current Address City/State invalid.";
                }
            }
        } else {
            $error = "Customer details does not exist.";
        }


        $return_array['status'] = $status;
        $return_array['error_msg'] = $error;

        return $return_array;
    }

    public function sendDisbursalSms($lead_id) {
        //$lead_id = $_GET['lead_id'];

        $sql = $this->getCAMDetails($lead_id);

        $camDetails = $sql->row();
        $sms_type_id = 5;
        $leadDetails = $this->select(['lead_id' => $lead_id], 'first_name, email, mobile', 'leads');
        $lead_cust = $leadDetails->row();
//        print_r($lead_cust);
//        die();
        $bankDetails = $this->getCustomerAccountDetails($lead_id);
        $bank_account_details = $bankDetails['banking_data'];
//        echo "<pre>";
//        print_r($bank_account_details);
//        die();
        $req = array();
        $req['lead_id'] = $lead_id;
        $req['loan_no'] = $camDetails->loan_no;
        $req['loan_amount'] = $camDetails->loan_recommended;
        $req['cust_bank_account_no'] = $bank_account_details['account'];
        $req['repayment_date'] = $camDetails->repayment_date;
        $req['repayment_amount'] = $camDetails->repayment_amount;
        $req['mobile'] = $lead_cust->mobile;
        $req['name'] = $lead_cust->first_name;
        //$req['mobile'] = 8750256406;
        require_once (COMPONENT_PATH . 'CommonComponent.php');
        $CommonComponent = new CommonComponent();

        $json['sms'] = $CommonComponent->payday_sms_api($sms_type_id, $lead_id, $req);
    }

}

?>
