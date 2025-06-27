<?php

defined('BASEPATH') or exit('No direct script access allowed');

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
        // 'default' is automatically loaded
        $this->db2 = $this->load->database('second', TRUE); // TRUE returns the DB object
    }

    public function index($conditions = null, $limit = null, $start = null, $search_input_array = array(), $where_in = array()) {

        $orderByS['sOrderBy'] = $conditions['sOrderBy'];
        unset($conditions['sOrderBy']);

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

        if (isset($conditions['LD.stage']) && in_array($conditions['LD.stage'], ['S1', 'S2', 'S3'])) {
            if (!empty($search_input_array['sfd'])) {
                $conditions['LD.lead_entry_date >='] = date("Y-m-d", strtotime($search_input_array['sfd']));
            }

            if (!empty($search_input_array['sed'])) {
                $conditions['LD.lead_entry_date <='] = date("Y-m-d", strtotime($search_input_array['sed']));
            }
        }


        if (isset($conditions['LD.stage']) && $conditions['LD.stage'] == 'S14') {
            if (!empty($search_input_array['sfd'])) {
                $conditions['LD.lead_final_disbursed_date >='] = date("Y-m-d", strtotime($search_input_array['sfd']));
            }

            if (!empty($search_input_array['sed'])) {
                $conditions['LD.lead_final_disbursed_date <='] = date("Y-m-d", strtotime($search_input_array['sed']));
            }
        }

        if (isset($conditions['LD.lead_status_id']) && $conditions['LD.lead_status_id'] == '16') {
            if (!empty($search_input_array['sfd'])) {
                $conditions['CO.collection_executive_payment_created_on >='] = date("Y-m-d", strtotime($search_input_array['sfd']));
            }

            if (!empty($search_input_array['sed'])) {
                $conditions['CO.collection_executive_payment_created_on <='] = date("Y-m-d", strtotime($search_input_array['sed']));
            }
        }

        if (!empty($search_input_array['lfid'])) {
            $conditions['LD.lead_loan_last_followup_type_id'] = $search_input_array['lfid'];
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

        if (!empty($search_input_array['scb'])) {
            $conditions['user_screener.name'] = $search_input_array['scb'];
        }

        if (isset($_SESSION['isUserSession']['labels']) && $_SESSION['isUserSession']['labels'] == 'CR1') {
            $twentySecondsAgo = date('Y-m-d H:i:s', strtotime('-15 minutes'));
        } else {
            $twentySecondsAgo = date('Y-m-d H:i:s', strtotime('-5 seconds'));
        }

        $select = 'LD.lead_id, DATE_FORMAT(LD.lead_disbursal_recommend_datetime, "%d-%m-%Y %H:%i:%s") as disbursal_recommend,  LD.monthly_salary_amount, LD.loan_no, LD.customer_id, LD.application_no, LD.lead_reference_no, LD.lead_data_source_id, LD.first_name, C.middle_name, C.sur_name, CONCAT_WS(" ",LD.first_name, C.middle_name, C.sur_name) as cust_full_name,';
        $select .= ' LD.email, C.alternate_email, C.gender, LD.mobile, C.alternate_mobile, LD.obligations, LD.promocode, LD.purpose, DATE_FORMAT(LD.lead_final_disbursed_date,"%d-%m-%Y") AS lead_final_disbursed_date,';
        $select .= ' LD.user_type, LD.pancard, LD.loan_amount, LD.tenure, LD.cibil, CE.income_type, CE.salary_mode, CE.monthly_income, LD.lead_is_mobile_verified, ';
        $select .= ' LOT.lot_otp_verify_flag, DATE_FORMAT(LD.created_on, "%i") as lead_generate_tm, ';
        $select .= ' LD.source,LD.utm_source, DATE_FORMAT(C.dob,"%d-%m-%Y") AS dob, LD.state_id, LD.city_id, LD.lead_branch_id, ST.m_state_name, CT.m_city_name, CT.m_city_trial_sourcing, LD.pincode, LD.status, LD.stage, LD.lead_status_id, LD.schedule_time, LD.created_on, ';
        $select .= ' LD.coordinates, LD.ip, LD.imei_no, LD.term_and_condition, LD.application_status, LD.lead_fi_residence_status_id,';
        $select .= ' LD.lead_fi_office_status_id,LD.scheduled_date,CAM.loan_recommended as sanctionedAmount,  CAM.repayment_amount, DATE_FORMAT(CAM.disbursal_date,"%d-%m-%Y") AS disbursal_date, LD.lead_credit_assign_user_id, LD.lead_screener_assign_user_id, LD.lead_disbursal_assign_user_id,';
        $select .= ' user_screener.name as screenedBy,  user_audit.name as auditBy, DATE_FORMAT(LD.lead_screener_assign_datetime, "%d-%m-%Y %H:%i:%s") as screenedOn,';
        $select .= ' user_sanction.name as sanctionAssignTo,  DATE_FORMAT(LD.lead_credit_approve_datetime, "%d-%m-%Y %H:%i:%s") as sanctionedOn, ';
        $select .= ' L.loan_status_id, L.loan_recovery_status_id, DATE_FORMAT(LD.lead_disbursal_approve_datetime, "%d-%m-%Y %H:%i:%s"), L.loan_disbursement_trans_status_id, ';
        $select .= ' C.customer_religion_id, religion.religion_name, C.father_name, branch.m_branch_name, C.aadhar_no, C.customer_digital_ekyc_flag, C.customer_marital_status_id, C.customer_spouse_name, C.customer_spouse_occupation_id, C.customer_qualification_id,';
        $select .= ' C.customer_docs_available,';
        $select .= ' CAM.repayment_date, CAM.cam_sanction_letter_esgin_on,';
        $select .= ' MRT.m_marital_status_name as marital_status, MOC.m_occupation_name as occupation, MQ.m_qualification_name as qualification';

        if (in_array($conditions["LD.stage"], array("S13", "S21", "S22", "S25"))) {
            $select .= ',user_disbursal.name as disbursalAssignTo, DATE_FORMAT(LD.lead_disbursal_recommend_datetime, "%d-%m-%Y %H:%i:%s") as disbursal_recommend';
        }

        if ($this->uri->segment(1) == "preclosure" || $this->uri->segment(1) == "closure") {
            $select .= ',CO.received_amount,CO.date_of_recived, collection_executive.name as collection_executive,CO.payment_mode_id, CO.collection_executive_payment_created_on as payment_uploaded_on ';
        }

        $this->db->select($select);

        $this->db->from($this->table . ' LD');
        $this->db->join('lead_customer C', 'C.customer_lead_id = LD.lead_id ', 'LEFT');
        $this->db->join($this->table_customer_employment . ' CE', 'CE.lead_id = LD.lead_id AND CE.emp_active=1 AND CE.emp_deleted=0', 'left');
        $this->db->join($this->table_credit_analysis_memo . ' CAM', 'CAM.lead_id = LD.lead_id AND CAM.cam_active=1 AND CAM.cam_deleted=0', 'left');
        $this->db->join($this->table_loan . ' L', 'L.lead_id = LD.lead_id AND L.loan_active=1 AND L.loan_deleted=0', 'left');
        $this->db->join($this->table_state . ' ST', 'ST.m_state_id = LD.state_id', 'left');
        $this->db->join($this->table_city . ' CT', 'CT.m_city_id = LD.city_id', 'left');
        $this->db->join('leads_otp_trans LOT', 'LOT.lot_lead_id = LD.lead_id ', 'LEFT');
        $this->db->join('master_religion religion', 'religion.religion_id = C.customer_religion_id', 'left');
        $this->db->join('master_qualification MQ', 'MQ.m_qualification_id = C.customer_qualification_id', 'left');
        $this->db->join('master_occupation MOC', 'MOC.m_occupation_id = C.customer_spouse_occupation_id', 'left');
        $this->db->join('master_marital_status MRT', 'MRT.m_marital_status_id = C.customer_marital_status_id', 'left');
        $this->db->join('master_branch branch', 'branch.m_branch_id = LD.lead_branch_id', 'LEFT');
        $this->db->join($this->table_data_source . ' DS', 'DS.data_source_id = LD.lead_data_source_id', 'left');
        $this->db->join($this->table_users . ' user_screener', 'user_screener.user_id = LD.lead_screener_assign_user_id', 'left');
        $this->db->join($this->table_users . ' user_sanction', 'user_sanction.user_id = LD.lead_credit_assign_user_id', 'left');
        $this->db->join($this->table_users . ' user_audit', 'user_audit.user_id = LD.lead_audit_assign_user_id', 'left');

        if (in_array($conditions["LD.stage"], array("S13", "S21", "S22", "S25"))) {
            $this->db->join($this->table_users . ' user_disbursal', 'user_disbursal.user_id = LD.lead_disbursal_assign_user_id', 'left');
        }

        if ($this->uri->segment(1) == "preclosure" || $this->uri->segment(1) == "closure") {
            $this->db->join($this->table_collection . ' CO', 'CO.lead_id = LD.lead_id AND CO.collection_active=1', 'left');
            $this->db->join($this->table_users . ' collection_executive', 'collection_executive.user_id = CO.collection_executive_user_id', 'left');
        }

        if (in_array($this->uri->segment(1), ["visitrequest", "visitpending", "visitcompleted"])) {
            unset($conditions["LD.stage"]);
            $this->db->join('loan_collection_visit LCV', 'LCV.col_lead_id = LD.lead_id AND LCV.col_visit_active=1', 'left');
        }

        $this->db->distinct();

        // $page = $this->input->get('page', TRUE) ? $this->input->get('page', TRUE) : 1;
        // $limit = 10;
        // $offset = ($page - 1) * $limit;
        
        if (!empty($limit)) {
            $this->db->limit($limit, $start);
            // $this->db->limit($limit, $offset);
        }
        

        if (!empty($conditions)) {
            $this->db->where($conditions);
        }

        if (in_array($conditions["LD.stage"], ['S1']) && $this->uri->segment(1) == 'enquires') {
            unset($conditions["LD.stage"]);
            $conditions["LD.lead_status_id"] = 1;
            $this->db->where('LD.first_name IS NULL');
        }

        if (in_array($conditions["LD.stage"], array("S1", "S2"))) {
            // $this->db->where('LD.lead_is_mobile_verified', 1);
            $this->db->where('LD.first_name IS NOT NULL', null, false);
        }

        /*         if (in_array($this->uri->segment(1), ["collection"])) {
             $this->db->where('repayment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)', NULL, FALSE);
        } */

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

        $this->db->where('LD.created_on <=', $twentySecondsAgo);

        $order_by_name = "LD.lead_id";
        $order_by_type = "DESC";

        if ($conditions['LD.stage'] == "S2") {
            $order_by_name = "LD.lead_screener_assign_datetime";
            $order_by_type = "DESC";
        } else if ($conditions['LD.stage'] == "S5") {
            $order_by_name = "LD.lead_credit_assign_datetime";
            $order_by_type = "DESC";
        } else if ($conditions['LD.stage'] == "S3") {
            $order_by_name = "LD.scheduled_date";
            $order_by_type = "DESC";
        } else if ($conditions['LD.stage'] == "S6") {
            $order_by_name = "LD.scheduled_date";
            $order_by_type = "DESC";
        } else if (in_array($conditions['LD.stage'], array("S12"))) {
            $order_by_name = "sanctionedOn";
            $order_by_type = "ASC";
        } else if (in_array($conditions['LD.stage'], array("S10"))) {
            $order_by_name = "LD.lead_credit_recommend_datetime";
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

        if (isset($orderByS['sOrderBy']) && !empty($orderByS['sOrderBy'])) {
            $order_by_name = "LD.monthly_salary_amount";
            $order_by_type = $orderByS['sOrderBy'];
        }

        if ($this->uri->segment(1) == "preclosure") {
            $order_by_name = "CO.collection_executive_payment_created_on";
            $order_by_type = "ASC";
        }
        $this->db->group_by('`LD`.`lead_id`');
        $return = $this->db->order_by($order_by_name, $order_by_type)->get();
        // if ($_SESSION['isUserSession']['user_id'] == 91) {
        //     echo $this->db->last_query();
        //     exit;
        // }
        // $query = $this->db->get();
        // print_r($query);
        // exit;
        return $return;
    }

    public function getLeadByPanDetails($pancard) {
        $select .= ' LD.lead_id,LD.pancard,LD.scheduled_date, CAM.repayment_amount, DATE_FORMAT(CAM.disbursal_date,"%d-%m-%Y") AS disbursal_date,';
        $select .= ' L.loan_status_id, L.loan_disbursement_trans_status_id,CAM.repayment_date,L.loan_closure_date, ';

        $this->db->select($select);
        $this->db->from($this->table . ' LD');
        $this->db->join($this->table_credit_analysis_memo . ' CAM', 'CAM.lead_id = LD.lead_id AND CAM.cam_active=1 AND CAM.cam_deleted=0', 'left');
        $this->db->join($this->table_loan . ' L', 'L.lead_id = LD.lead_id AND L.loan_active=1 AND L.loan_deleted=0', 'left');

        if (!empty($pancard)) {
            $this->db->where('LD.pancard', $pancard);
        }
        $this->db->where('L.loan_closure_date!=', NULL);
        $this->db->where('LD.lead_active', 1);
        $this->db->where('LD.lead_deleted', 0);

        $order_by_name = "LD.lead_id";
        $order_by_type = "DESC";

        $return = $this->db->order_by($order_by_name, $order_by_type)->get();
        return $return;
    }

    public function getLeadDetails($conditions) {

        $select = 'LD.lead_id, LD.loan_no, LD.customer_id, LD.application_no, LD.lead_reference_no, LD.monthly_salary_amount, LD.lead_data_source_id, LD.first_name, C.middle_name, C.sur_name, CONCAT_WS(" ",LD.first_name, C.middle_name, C.sur_name) as cust_full_name, LD.check_cibil_status,';
        $select .= ' LD.email, C.alternate_email, C.gender, LD.mobile, C.alternate_mobile, LD.obligations, LD.promocode, LD.purpose, LD.lead_stp_flag, DATE_FORMAT(LD.lead_final_disbursed_date,"%d-%m-%Y") AS lead_final_disbursed_date, L.legal_notice_letter, L.loan_noc_settlement_letter,';
        $select .= ' LD.user_type, LD.pancard, LD.loan_amount, LD.tenure, LD.cibil, CE.income_type, CE.salary_mode, CE.monthly_income, ';
        $select .= ' LD.source,LD.utm_source, LD.utm_campaign, DATE_FORMAT(C.dob,"%d-%m-%Y") AS dob, LD.state_id, LD.city_id, LD.lead_branch_id, ST.m_state_name, CT.m_city_name, LD.pincode, LD.status, LD.stage, LD.lead_status_id, LD.schedule_time, LD.created_on, ';
        $select .= ' LD.coordinates, LD.ip, LD.imei_no, LD.term_and_condition, LD.application_status, LD.lead_fi_residence_status_id,';
        $select .= ' LD.lead_fi_office_status_id,LD.scheduled_date,CAM.loan_recommended as sanctionedAmount, CAM.repayment_amount, DATE_FORMAT(CAM.disbursal_date,"%d-%m-%Y") AS disbursal_date, LD.lead_credit_assign_user_id, LD.lead_screener_assign_user_id, LD.lead_disbursal_assign_user_id,';
        $select .= ' user_screener.name as screenedBy, user_audit.name as auditBy,  DATE_FORMAT(LD.lead_screener_assign_datetime, "%d-%m-%Y %H:%i:%s") as screenedOn, user_screener.user_is_loanwalle,';
        $select .= ' user_sanction.name as sanctionAssignTo,  DATE_FORMAT(LD.lead_credit_approve_datetime, "%d-%m-%Y %H:%i:%s") as sanctionedOn, ';
        $select .= ' L.loan_status_id, LD.lead_disbursal_approve_datetime, L.loan_disbursement_trans_status_id, ';
        $select .= ' C.customer_religion_id, religion.religion_name,branch.m_branch_name, C.customer_spouse_name, C.customer_spouse_occupation_id, C.customer_qualification_id, C.current_residence_type, ';
        $select .= ' C.father_name,C.pancard_verified_status,C.customer_digital_ekyc_flag, C.alternate_email_verified_status, C.customer_appointment_schedule, C.customer_appointment_remark, ';
        $select .= ' LD.lead_rejected_assign_user_id, LD.lead_rejected_reason_id,LD.lead_rejected_assign_counter,C.customer_bre_run_flag, CAM.city_category, C.pancard_ocr_verified_status, C.aadhaar_ocr_verified_status, ';
        $select .= ' MRT.m_marital_status_name as marital_status, MOC.m_occupation_name as occupation, MQ.m_qualification_name as qualification, C.aadhar_no';

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
        $this->db->join('master_religion religion', 'religion.religion_id = C.customer_religion_id', 'left');
        $this->db->join('master_qualification MQ', 'MQ.m_qualification_id = C.customer_qualification_id', 'left');
        $this->db->join('master_occupation MOC', 'MOC.m_occupation_id = C.customer_spouse_occupation_id', 'left');
        $this->db->join('master_marital_status MRT', 'MRT.m_marital_status_id = C.customer_marital_status_id', 'left');
        $this->db->join('master_branch branch', 'branch.m_branch_id = LD.lead_branch_id', 'LEFT');
        $this->db->join($this->table_users . ' user_screener', 'user_screener.user_id = LD.lead_screener_assign_user_id', 'left');
        $this->db->join($this->table_users . ' user_sanction', 'user_sanction.user_id = LD.lead_credit_assign_user_id', 'left');
        $this->db->join($this->table_users . ' user_disbursal', 'user_disbursal.user_id = LD.lead_disbursal_assign_user_id', 'left');
        $this->db->join($this->table_users . ' user_audit', 'user_audit.user_id = LD.lead_audit_assign_user_id', 'left');
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
        $select = 'LD.lead_id, LD.lead_status_id, LD.loan_amount, LD.lead_data_source_id, C.*, CE.*,C.city_id as res_city_id,C.state_id as res_state_id,CE.city_id as office_city_id,CE.state_id as office_state_id';
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

    // public function enquires($conditions = null, $limit = null, $start = null) {
    //     $this->db->select('enquiry.*');
    //     $this->db->from('customer_enquiry enquiry');
    //     $this->db->where($conditions);
    //     $this->db->limit($limit, $start);
    //     $return = $this->db->order_by('enquiry.cust_enquiry_id', 'desc')->get();
    //     return $return;
    // }

    public function enquires() {
        $sql = 'SELECT EC.cust_enquiry_id, EC.cust_enquiry_lead_id, EC.cust_enquiry_mobile, EC.cust_enquiry_remarks, EC.cust_enquiry_created_datetime, LD.first_name, LD.lead_is_mobile_verified, LD.created_on';
        $sql .= ' FROM leads LD';
        $sql .= ' INNER JOIN customer_enquiry EC ON (EC.cust_enquiry_lead_id = LD.lead_id)';
        $sql .= ' WHERE LD.lead_active=1 AND LD.lead_deleted=0 AND LD.lead_is_mobile_verified=0 && LD.first_name IS NULL || LD.first_name=""';
        $sql .= ' ORDER BY EC.cust_enquiry_id DESC';
        // $sql = "SELECT
		// 	LD.lead_id,
		// 	LD.mobile,
		// 	LD.created_on,
		// 	LD.first_name,
		// 	LD.source,
		// 	LD.status,
		// 	LD.lead_is_mobile_verified
		// FROM
		// 	leads LD
		// WHERE
		// 	LD.lead_active = 1
		// 	AND LD.lead_deleted = 0
		// 	AND LD.lead_status_id = 1
		// 	AND LD.status = 'LEAD-NEW'
		// 	AND LD.stage = 'S1'
		// 	AND (LD.first_name IS NULL)
		// group by LD.mobile
		// ORDER BY
		// 	LD.lead_id DESC";
        //echo $sql;
        // Execute the query
        $query = $this->db->query($sql)->result_array();


        return $query;
    }

    public function internalDedupe($lead_id = null) {
        $result = 0;

        if (!empty($lead_id)) { // && !empty($application_no)
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

                $final_where = "LD.lead_id != $lead_id AND LD.lead_status_id NOT IN(7,8) AND (";

                $where = "";

                $query_run_flag = false;

                if (!empty($first_name) && !empty($dob)) {
                    $where .= "OR (C.first_name='$first_name' AND C.dob='$dob')";
                    $query_run_flag = true;
                }


                if (!empty($pancard)) {
                    $where .= "OR LD.pancard='$pancard'";
                    $query_run_flag = true;
                }

                if (!empty($mobile)) {
                    $where .= "OR LD.mobile='$mobile'";
                    $where .= "OR C.alternate_mobile='$mobile'";
                    $query_run_flag = true;
                }

                if (!empty($alternate_mobile)) {
                    $where .= "OR LD.mobile='$alternate_mobile'";
                    $where .= "OR C.alternate_mobile='$alternate_mobile'";
                    $query_run_flag = true;
                }


                if (!empty($email)) {
                    $where .= "OR LD.email='$email'";
                    $where .= "OR C.alternate_email='$email'";
                    $query_run_flag = true;
                }

                if (!empty($alternate_email)) {
                    $where .= "OR LD.email='$alternate_email'";
                    $where .= "OR C.alternate_email='$alternate_email'";
                    $query_run_flag = true;
                }

                //                if (!empty($aadhar_no) && !empty($dob)) {
                //                    $where .= "OR (C.aadhar_no='$aadhar_no' AND C.dob='$dob')";
                //                }
                if (!empty($first_name) && !empty($father_name)) {
                    $where .= "OR (C.first_name='$first_name' AND C.father_name='$father_name')";
                    $query_run_flag = true;
                }

                $where = ltrim($where, 'OR ');

                $final_where .= " " . $where . " )";

                //                $select = 'LD.lead_id, LD.product_id, LD.customer_id, LD.loan_no, LD.application_no, LD.lead_data_source_id, LD.first_name,';
                //                $select .= ' C.father_name, C.middle_name, C.sur_name, LD.email, C.alternate_email, C.gender, LD.mobile, C.alternate_mobile, LD.obligations, LD.promocode,';
                //                $select .= ' LD.purpose, LD.user_type, LD.pancard, C.aadhar_no, IF(CAM.loan_recommended>0,CAM.loan_recommended,LD.loan_amount) as loan_amount, LD.tenure, LD.cibil, CE.income_type, CE.salary_mode,';
                //                $select .= ' CE.monthly_income, LD.source, C.dob, LD.state_id, LD.city_id, ST.m_state_name as state, CT.m_city_name as city, LD.pincode, LD.status, LD.stage, LD.schedule_time,';
                //                $select .= ' LD.created_on as lead_initiated_date, LD.coordinates, LD.ip, LD.imei_no, LD.term_and_condition,';
                //                $select .= ' REJ.reason as reject_reason, REJU.name as rejected_by_name,CAM.disbursal_date';
                //                $this->db->where($final_where);
                //                $this->db->select($select);
                //                $this->db->distinct();
                //                $this->db->from($this->table . ' LD');
                //                $this->db->join($this->table_state . ' ST', 'ST.m_state_id = LD.state_id', 'left');
                //                $this->db->join($this->table_city . ' CT', 'CT.m_city_id = LD.city_id', 'left');
                //                $this->db->join($this->table_lead_customer . ' C', 'C.customer_lead_id = LD.lead_id', 'left');
                //                $this->db->join($this->table_customer_employment . ' CE', 'CE.lead_id = LD.lead_id', 'left');
                //                $this->db->join($this->table_credit_analysis_memo . ' CAM', 'CAM.lead_id = LD.lead_id', 'left');
                //                $this->db->join($this->table_loan . ' L', 'L.lead_id = LD.lead_id', 'left');
                //                $this->db->join('tbl_rejection_master REJ', 'REJ.id = LD.lead_rejected_reason_id', 'left');
                //                $this->db->join('users REJU', 'REJU.user_id = LD.lead_rejected_user_id', 'left');
                //                $result = $this->db->order_by('LD.lead_id', 'DESC')->limit(100)->get();

                $dedupeSql = 'SELECT LD.lead_id, LD.product_id, LD.customer_id, LD.loan_no, LD.application_no, LD.lead_data_source_id, LD.first_name,';
                $dedupeSql .= ' C.father_name, C.middle_name, C.sur_name, LD.email, C.alternate_email, C.gender, LD.mobile, C.alternate_mobile, LD.obligations, LD.promocode,';
                $dedupeSql .= ' LD.purpose, LD.user_type, LD.pancard, C.aadhar_no, IF(CAM.loan_recommended>0,CAM.loan_recommended,LD.loan_amount) as loan_amount, LD.tenure, LD.cibil, CE.income_type, CE.salary_mode,';
                $dedupeSql .= ' CE.monthly_income, LD.source, C.dob, LD.state_id, LD.city_id, ST.m_state_name as state, CT.m_city_name as city, LD.pincode, LD.status, LD.stage, LD.schedule_time,';
                $dedupeSql .= ' LD.created_on as lead_initiated_date, LD.coordinates, LD.ip, LD.imei_no, LD.term_and_condition,';
                $dedupeSql .= ' REJ.reason as reject_reason, REJU.name as rejected_by_name,CAM.disbursal_date';
                $dedupeSql .= ' FROM leads LD';
                $dedupeSql .= ' INNER JOIN lead_customer C ON(C.customer_lead_id = LD.lead_id)';
                $dedupeSql .= ' LEFT JOIN master_state ST ON(ST.m_state_id = LD.state_id)';
                $dedupeSql .= ' LEFT JOIN master_city CT ON(CT.m_city_id = LD.city_id)';
                $dedupeSql .= ' LEFT JOIN customer_employment CE ON(CE.lead_id = LD.lead_id)';
                $dedupeSql .= ' LEFT JOIN credit_analysis_memo CAM ON(CAM.lead_id = LD.lead_id)';
                $dedupeSql .= ' LEFT JOIN loan L ON(L.lead_id = LD.lead_id)';
                $dedupeSql .= ' LEFT JOIN tbl_rejection_master REJ ON(REJ.id = LD.lead_rejected_reason_id)';
                $dedupeSql .= ' LEFT JOIN users REJU ON(REJU.user_id = LD.lead_rejected_user_id)';
                $dedupeSql .= ' WHERE ' . $final_where;
                $dedupeSql .= ' GROUP BY LD.lead_id ORDER BY LD.lead_id DESC LIMIT 100';
                //                echo $dedupeSql;
                if ($query_run_flag) {
                    $result = $this->db->query($dedupeSql)->result();
                }
            }
        }
        $response['lead_data'] = $lead_data;
        $response['result'] = $result;
        return $response;
    }

    public function internalDedupeDuniya($lead_id = null)
    {
        $result = 0;

        if (!empty($lead_id)) { // && !empty($application_no)
            $sql = 'SELECT LD.lead_id, LD.pancard, C.first_name, C.middle_name, C.sur_name, C.dob, LD.mobile, C.alternate_mobile, C.email, C.alternate_email, C.aadhar_no, C.father_name';
            $sql .= " FROM leads LD";
            $sql .= " INNER JOIN lead_customer C  ON (C.customer_lead_id = LD.lead_id AND C.customer_active=1 AND C.customer_deleted=0)";
            $sql .= " WHERE LD.lead_id = $lead_id AND LD.lead_active=1 AND LD.lead_deleted=0";

            $leadsDetails = $this->db->query($sql); //->get()->row_array()

            if ($leadsDetails->num_rows() > 0) {

                $lead_data = $leadsDetails->row_array();

                $first_name = !empty($lead_data['first_name']) ? strtoupper($lead_data['first_name']) : "";

                $dob = !empty($lead_data['dob']) ? $lead_data['dob'] : "";

                $pancard = !empty($lead_data['pancard']) ? strtoupper($lead_data['pancard']) : "";

                $mobile = !empty($lead_data['mobile']) ? $lead_data['mobile'] : "";

                $alternate_mobile = !empty($lead_data['alternate_mobile']) ? $lead_data['alternate_mobile'] : "";

                $email = !empty($lead_data['email']) ? strtoupper($lead_data['email']) : "";

                $alternate_email = !empty($lead_data['alternate_email']) ? strtoupper($lead_data['alternate_email']) : "";

                $aadhar_no = !empty($lead_data['aadhar_no']) ? strtoupper($lead_data['aadhar_no']) : "";

                $father_name = !empty($lead_data['father_name']) ? strtoupper($lead_data['father_name']) : "";

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
                $this->db2->where($final_where);
                $this->db2->select($select);
                $this->db2->distinct();
                $this->db2->from($this->table . ' LD');
                $this->db2->join($this->table_state . ' ST', 'ST.m_state_id = LD.state_id', 'left');
                $this->db2->join($this->table_city . ' CT', 'CT.m_city_id = LD.city_id', 'left');
                $this->db2->join($this->table_lead_customer . ' C', 'C.customer_lead_id = LD.lead_id AND C.customer_active=1 AND C.customer_deleted=0', 'left');
                $this->db2->join($this->table_customer_employment . ' CE', 'CE.lead_id = LD.lead_id AND CE.emp_active=1 AND CE.emp_deleted=0', 'left');
                $this->db2->join($this->table_credit_analysis_memo . ' CAM', 'CAM.lead_id = LD.lead_id AND CAM.cam_active=1 AND CAM.cam_deleted=0', 'left');
                $this->db2->join($this->table_loan . ' L', 'L.lead_id = LD.lead_id AND L.loan_active=1 AND L.loan_deleted=0', 'left');
                $this->db2->join('tbl_rejection_master REJ', 'REJ.id = LD.lead_rejected_reason_id', 'left');
                $this->db2->join('users REJU', 'REJU.user_id = LD.lead_rejected_user_id', 'left');
                $result = $this->db2->order_by('LD.created_on', 'DESC')->get();
                            //    echo $this->db2->last_query();
            }
        }
        // print_r($result);
        return $result;
    }


    public function disbursalHistory($lead_id = null) {
        $result = 0;
        $lead_data = [];
        if (!empty($lead_id)) { // && !empty($application_no)
            $sql = "SELECT c.loan_disbursement_trans_status_id,c.loan_principle_payable_amount,c.disburse_refrence_no,c.recommended_amount,c.loan_no,a.*,b.disburse_api_status_id,b.disburse_bank_reference_no,b.disburse_beneficiary_account_no,b.disburse_beneficiary_ifsc_code,b.disburse_beneficiary_name,b.disburse_errors,b.disburse_response,cam.disbursal_date,cam.processing_fee_percent
FROM `lead_disbursement_trans_log` as a
left join api_disburse_logs as b on a.disb_trans_reference_no = b.disburse_trans_refno
left join loan as c on c.lead_id = a.disb_trans_lead_id
left join credit_analysis_memo as cam on cam.lead_id = a.disb_trans_lead_id
where a.disb_trans_lead_id = " . $lead_id;

            $leadsDetails = $this->db->query($sql); //->get()->row_array()

            if ($leadsDetails->num_rows() > 0) {

                $lead_data = $leadsDetails->result_array();
                //
            }
        }
        $response['lead_data'] = $lead_data;
        $response['result'] = $result;
        return $response;
    }


    public function gererateSanctionLetternew($lead_id) {

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
            $father_name = $camDetails->father_name;
            $pancard = $camDetails->pancard;

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

            $residence_address = trim(strtoupper($residence_address));

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
                                            <td bgcolor = "#FFFFFF">One</td>
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
                                                <p>' . CONTACT_PERSON . '</p>
                                                <p>Mobile: ' . REGISTED_MOBILE . '</p>
                                                <p>Address: ' . REGISTED_ADDRESS . '</p>
                                            </td>
                                        </tr>


                                    </table>
                                    <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
                                    <br/><br/><br/><br/><br/><br/>
                                    <table width="667" border="0" cellpadding="1" cellspacing="1" align="center" style="font-family:Arial, Helvetica, sans-serif; line-height:17px; font-size:13px; border:solid 1px #ddd; padding:0px 7px;">
                                        <tr>
                                            <td colspan="2" valign="middle"><p style="font-size: 18px; color: #00455E; font-size:18px;"><img src="' . SANCTION_LETTER_HEADER . '" width="760" height="123" border="0" usemap="#Map" /></td>
                                        </tr>
                                        <tr>
                                            <td align="right"><span style="color: #00455E; font-size:16px;">Date : ' . $sanction_date . '</span></td>
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
                                            <td colspan = "2"><strong style = "color:#00455E;">Best Regards</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan = "2"><strong style = "color:#00455E;">Team ' . BRAND_NAME . '</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan = "2"><strong style = "color:#00455E;">(Brand Name for ' . COMPANY_NAME . ')</strong></td>
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
                                            <td colspan = "2">You can Prepay/Repay the loan amount using our link <a href = "' . LOAN_REPAY_LINK . '" target = "_blank" style = "color:#00455E; text-decoration:blink;">' . LOAN_REPAY_LINK . '</a></td>
                                        </tr>
                                        <tr>
                                            <td colspan = "2"><img src = "' . SANCTION_LETTER_FOOTER . '" width="760" height="44"/></td>
                                        </tr>

                                    </table><br/><br/>

                                    <div>

                                        <div style="padding-top:150px;border-top: 1px solid black;">
            <p align="center" style="text-align: center; margin-bottom: 0px;">

                <b>

                    <h2 style="line-height: 107%; text-align: center;font-family: Arial, Helvetica, sans-serif;">Borrower Agreement</h2>
                    <p style="line-height: 107%; font-size:18pt;text-align: center;font-family: Arial, Helvetica, sans-serif;">FOR</p>
<h1 style="line-height: 107%; text-align: center;font-family: Arial, Helvetica, sans-serif;color:#c9211e">FDSPL</h1>
                    <h2 style="line-height: 107%; text-align: center;font-family: Arial, Helvetica, sans-serif;">(Fintelligence Data Science Private Limited)</h2>
                    <p style="text-align: center"><img style="width:25%" src="'.(getenv("WEBSITE_URL") ?? 'https://cms.paisaonsalary.in/').'public/new_images/images/rupee-circle-qdw99gwiqzbhbl8qonh304.webp" /></p>
                </b>

            </p>
        </div>

        <div>

            <p align="left" style="text-align: center; margin-bottom: 0px;">

                <b>

                    <u><span style="font-size: 12pt; line-height: 107%; font-family: Arial, Helvetica, sans-serif;">Loan Agreement</span></u>

                </b>

            </p>

            <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

                This Loan Agreement is made on this ' . date("d") . ' day of ' . date("M") . ' , ' . date("Y") . ' at Delhi, India. (the “Effective Date”)

            </p>

            <p align="left" style="text-align: left; margin-bottom: 0px;">

                <b>

                    <u><span style="font-size: 12pt; line-height: 107%; font-family: Arial, Helvetica, sans-serif;">BY AND BETWEEN:</span></u>

                </b>

            </p>



            <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

                <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">The Lenders as per the Annexure II</span> & </b>

            </p>

            <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

                Mr/Ms/Mrs. [' . $fullname . '],bearing PAN [' . $pancard . '] son of/ daughter of [' . $father_name . '], an adult Indian Citizen and Indian Resident, residing at [' . $residence_address . '] (hereinafter referred to as "Borrower", which expression shall, unless it be repugnant to or inconsistent with the subject or context, mean and include the legal heirs, legal representatives, executors, administrators and permitted assigns)of the SECOND PART],

            </p>

             <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

                <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;"> Lender(s) and Borrower are hereinafter collectively referred to as “Parties” and individually as a “Party”.</span></b>

             </p>



            <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

                <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">Whereas</span></b> :

                A. FDSPL is engaged in the business of running an online peer-to-peer lending platform that connects potential borrowers and

                lenders and through its Website facilitates the borrowers to raise and the lenders to finance unsecured / secured personal

                and business loans.

            </p>



            <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

                B. The Borrower has on the Website applied for a loan and the Lenders have agreed to finance an amount as mentioned herein relying on the covenants of the Borrower and the representations and warranties contained herein.

             </p>



             <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

                C. This document lays out the terms and conditions which shall be applicable to all Loans availed of by the Borrowers from the Lenders through the Website.

             </p>



             <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

                <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">NOW THEREFORE, IN CONSIDERATION OF

                    THE MUTUAL PROMISES, COVENANTS AND CONDITIONS HEREINAFTER SET FORTH, THE RECEIPT AND SUFFICIENCY OF WHICH IS HEREBY ACKNOWLEDGED,

                     THE PARTIES HERETO AGREE AS FOLLOWS:</span>

               </b>

            </p>



            <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

                This Loan Agreement consists of: (I) The Loan Agreement- Principal Document; and (II) The Loan Agreement- Standard Terms and Conditions (the “Loan Agreement”).

            </p>



            <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

                All capitalized terms used in this Loan Agreement shall have the meaning ascribed to them in the Loan Agreement- Standard Terms and Conditions.

           </p>





           <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">I. LOAN AGREEMENT- PRINCIPAL DOCUMENT</span>

            </b>

           </p>





           <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">1. Purpose of the Loan</span></b>

            <br>

            Borrower hereby undertakes that the amount borrowed from the Lenders under this Loan Agreement shall be used for the purpose stated herein, being the following:

            <b>Personal Loan</b> (the “Purpose”).

           </p>

           <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            Each Lender hereby confirms that the funds committed/used for the purpose of lending

            and advancing the Loan are from a lawful and genuine source and have not been obtained in an unlawful, unethical or immoral manner.

           </p>







           <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">2. Amount of the Loan</span></b> :

            Each Lender has agreed to lend and advance to the Borrower the amount as stated below, for the purpose stated herein.

        </p>

        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b>Lender1:________________________________________________________.</b>

        </p>

        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b>Lender2:________________________________________________________.</b>

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b>Lender3:________________________________________________________.</b>

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b>3. Loan Period:</b> ' . $tenure . ' DAY.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b>4. Designated Borrower Account:


            lw_send_email
                ________________________________________________________

                </b>

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b>5. Guarantee</b>

            The Loan together with all Interest, further interest, additional interest, liquidated damages, costs, expenses, fees including expenses payable to the Lenders, Escrow Bank, Trustee and/or FDSPL and any other monies stipulated in the Transaction Documents shall, be secured by a guarantee to be provided by ________________________________ (the “Guarantor”)

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b>6. Interest</b>

            The interest rate shall be______________________________________________.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b>7. Late Fee</b>

            The late fee shall be charged as per the Terms and Conditions stated on the Website from time to time.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b>8. Jurisdiction</b>

            The Parties agree the courts in Delhi shall have exclusive jurisdiction to settle any dispute arising out of or in connection with the Transaction Documents or the performance thereof.

        </p>







        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">II. LOAN AGREEMENT- STANDARD TERMS AND CONDITIONS</span>

            </b>

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            This Loan Agreement consists of: (I) The Loan Agreement- Principal Document; and (II) The Loan Agreement- Standard Terms and Conditions (the “Loan Agreement”).

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            1. Definitions and Interpretations

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            1.1. In these terms and conditions (the “Loan Agreement-Standard Terms”), unless there is anything repugnant to the subject or context thereof, the expressions listed below, if applicable, shall have the following meanings:



            “Act” shall mean the (Indian) Companies Act, 2013 or the (Indian) Companies Act, 1956 as the case may be.



            “Applicable Law” shall mean, any statute, law, regulation, ordinance, rule, judgment, order, decree, by-law, directive, policy,

            requirement and/or any governmental restriction or any similar form of decision of, or determination by, or any

             interpretation or administration having the force of law of any of the foregoing, by any governmental authority having

             jurisdiction over the matter in question, whether in effect as of the date or thereafter.



        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Application Form” shall mean the form submitted by the Borrower on the Website.

        </p>

        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Availability Period” shall have the meaning given to it in Clause 3 (b) of the Loan Agreement- Standard Terms.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Borrower” shall mean and refers jointly and severally to the Persons who have been sanctioned/granted the Loan by the Lenders, as specified in the Loan Agreement the Borrower is an individual, his legal heirs, representatives, executors, administrators and permitted assigns

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Business Day” shall mean the day on which the banks are open for business in India.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Borrowers Escrow Account” shall mean the Fintelligence Data Science Pvt. Ltd.- Borrowers Escrow Account established with the Escrow Agent as per the Escrow Agreement.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Cheques” shall have the meaning given to it in Clause 3 c (i) of the Loan Agreement- Standard Terms.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Credit Bureau” shall mean any credit bureau as authorized and licensed by the Reserve Bank of India (including the Credit Information Bureau (India) Limited (CIBIL), Experian, Equifax, Crif-Highmark).

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Credit score (Bureau Score)” shall mean the score or report as obtained from a recognized Credit Bureau (CIBIL, Experian, Equifax, Crif-Highmark).

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Designated Borrower Account” shall mean the account of the Borrower, details of which are provided in the Loan Agreement- Principal Document, or any other bank account of the Borrower, if such a change is requested by the Borrower in writing and accepted by FDSPL.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Designated Lender Account” shall mean the account of each Lender, details of which are provided by each such Lender to FDSPL at the time of execution of this Loan Agreement, or any other bank account of any Lender, if such a change is requested by the Lender in writing and accepted by FDSPL.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

          “Electronic Signature” shall have the meaning ascribed to it under the Information and Technology Act, 2000 and any reference in this Loan Agreement to affixation of an “Electronic Signature” shall mean a reference to affixing the same on a document, as recognized and provided for under the relevant provisions of the Information and Technology Act, 2000 read together with the relevant provisions of the Evidence Act, 1872.

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Effective Date” shall mean the date mentioned in the preamble to the Loan Agreement.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Escrow Agreement” shall mean the agreement dated 30 June 2017 entered into between FDSPL, the Trustee and the Escrow Agent for the purpose of establishing the Escrow Account.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Escrow Agent” or “Escrow Bank” shall have the meaning given to it in the Escrow Agreement.

        </p>

        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Escrow Account” shall have the meaning given to it in the Escrow Agreement.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “EMI” shall mean Equated Monthly Installments to be paid by the Borrower as per the repayment schedule set out in Annexure I to thisLoan Agreement.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Guarantor” shall have the meaning given to it in Clause 5. of the Loan Agreement- Principal Document.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Indian Citizen” shall have the meaning given to it in The Citizenship Act, 1955, as amended from time to time.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Indian Resident” is a person resident in India as defined under clause 2(v) of the Foreign Exchange Management Act, 1999, as amended from time to time. Indian Resident is currently defined as under:

            Person resident in India for more than 182 days during the course of preceding financial year but does not include:

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            A. A person who has gone out of India or who stays outside India, in either case:

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            (i) for or on taking up employment outside India; or

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            (ii) for carrying on outside India a business or vocation outside India; or

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            (iii) for any other purpose, in such circumstances as would indicate his intention to stay outside India for an uncertain period.

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            B. A person who has come to or stay in India, in either case, otherwise than:

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            (i) for or on taking up employment in India, or

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            (ii) for carrying on in India a business or vocation in India, or

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            (iii) for any other purpose, in such circumstances as would indicate his intention to stay in India for an uncertain period;

        </p>







        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Interest” shall mean interest payable on the Loan at the interest rate agreed to between the Parties, such interest rate being specified in the Loan Agreement.

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “KYC Documents” shall mean all documents and information as may be provided by the Borrower to the Lenders and/or FDSPL as may be required by the Lenders, including for the purpose of customer identification, whether required under law or otherwise, including information submitted on the Website.

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Lenders” shall mean collectively the Persons specified as such in the Loan Agreement and shall individually be referred to as the “Lender” and depending on the nature of the Lender: (a) if the Lender is a company within the meaning of the Companies Act 1956 or the Companies Act 2013, its successors and assigns,; (b) if the Lender is an individual, his legal heirs, legal representatives, executors, administrators and assigns; (c) if the Lender is a HUF, the Karta acting on behalf of the HUF and all members of the HUF and their respective heirs, executors, administrators and assigns; d) if the Lender is a partnership, being a firm registered under the Indian Partnership Act 1932, each of its partners and their respective heirs, executors, administrators and assigns or the heirs, executors, administrators and assigns of the last surviving partner, and e) if the Lender is a limited liability partnership incorporated and registered under the provisions of Limited Liability Partnership Act, 2008, its successors and assigns.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Lenders Escrow Account” shall mean the Fintelligence Data Science Pvt. Ltd.- Investors Escrow Account established with the Escrow Agent as per the Escrow Agreement.

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Loan” shall mean such amount of the loan/financial assistance which is specified in the Loan Agreement and where there is more than one Lender, Loan shall mean in respect of each Lender, the amount of the loan/financial assistance as specified against the name of such Lender in the Loan Agreement.

        </p>







        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Loan Agreement” shall mean this Loan Agreement consisting of: (a) the Loan Agreement- Principal Document; and (ii) the Loan Agreement- Standard Terms and Conditions.

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Loan Period” shall mean the term/period of the Loan as specified in the Loan Agreement.

        </p>

        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “NACH” shall mean National Automated Clearing House as implemented by the National Payments Corporation of India for the purpose of making electronic payments.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “ FDSPL” shall mean Fintelligence Data Science Pvt. Ltd. including its successors and assigns.

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “P2P RBI Master Directions” shall mean the Master Directions on Non-Banking Financial Company- Peer to Peer Lending Platform (Reserve Bank) Directions, 2017 issued by the Reserve Bank of India on 4 October 2017, as amended from time to time.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Parties” shall mean the Borrower and the Lenders collectively, and Party shall mean any of them, individually.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Person” shall mean any of the following: an individual, a Karta of a HUF acting on behalf of such HUF, sole proprietorship firm, partnership firm, limited liability partnership (LLP), company, as the case may be.

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Pre-payment” shall mean premature repayment of the Loan in partial or full.

        </p>

        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Privacy Policy” shall mean the privacy policy for the access/use of the Website and/or Services, as available on the Website.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Purpose” shall have the meaning given to it in Clause 1 of the Loan Agreement- Principal Document.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “RBI” shall mean the Reserve Bank of India.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Repayment” shall mean the repayment of the principal amount and of the Loan, Interest thereon, any additional interest, commitment and/or any other charges, fees, penalties or other dues payable in terms of the Transaction Documents.

        </p>







        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Services” shall mean the providing of a platform by FDSPL, by means of the Website, for connecting the Borrower and the Lenders

            to facilitate borrowing and lending on or through the Website and such services that are incidental, ancillary or connected therewith.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">“Transaction Documents” shall mean:</span></b> :

            Each Lender has agreed to lend and advance to the Borrower the amount as stated below, for the purpose stated herein.

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">●</span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

              the Application Form;

            </span>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">●</span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

             the KYC Documents;

            </span>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">●</span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

            the Loan Agreement;

            </span>

        </p>

        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">●</span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

            Terms and Conditions and the Privacy Policy;

            </span>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">●</span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

           Cheques, NACH instructions and any demand promissory note as may be provided by or on behalf of the Borrower; and

            </span>

        </p>

        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">●</span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

            Any and all writings and other documents executed or entered into between the Borrower and the Lender(s), in relation, or pertaining, to the Loan and each such Transaction Document as amended from time to time.

            </span>

        </p>









        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Terms and Conditions” shall mean the terms and conditions for the access/use of the Website and/or Services available on the Website as may be updated from time to time.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            “Trustee” shall mean ICICI TRUSTEESHIP SERVICES LTD, who has been appointed to act as trustee to operate the Escrow Account pursuant to the Trustee Agreement dated 19 March 2019 entered into between FDSPL and ICICI TRUSTEESHIP SERVICES LTD.

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">“Website” shall mean www.rupeecircle.com, which is owned by Fintelligence Data Science Pvt. Ltd..</span></b>

        </p>







        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

                1.2. In this Loan Agreement, unless the contrary intention appears:

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

                1.2.1 Any reference to a particular article, clause, recital, appendix or schedule shall be a reference to that article,

                clause, recital, appendix or schedule to this Loan Agreement.

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

                1.2.2. Any references in this Loan Agreement to any law, statute or statutory provision include a reference to such law,

                 statute or statutory provision as from time to time amended, modified, re-enacted, extended, consolidated or replaced

                 (whether before or after the date of this Loan Agreement) and to any subordinate legislation made from time to time

                 under the law, statute or statutory provision.

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            1.2.3 Any references to any gender shall include all genders and references to the singular number shall include the plural number and vice versa.

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            1.2.4 The Recitals and Schedules to this Loan Agreement shall constitute and form an integral part of this Loan Agreement.

        </p>

        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            1.2.5.Headings used in this Loan Agreement are for convenience of reference only and shall not affect the interpretation of this Loan Agreement.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            1.2.6 References in this Loan Agreement to any Party shall include, or be deemed to be references to (as may be appropriate) its

            respective successors, personal representatives and permitted assignees or transferees. If the Party is a Lender, then the Lender

            shall be entitled to, after giving notice to other Lenders but without the consent of the Borrower, assign all or any of its rights,

            benefits and obligations hereunder.

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">2. Purpose of the Loan</span>

            </b> :

            The Purpose of the Loan shall be as specified in Clause 1 of the Loan Agreement- Principal Document.

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">



            <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

                <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">3. Agreement and terms of the Loan</span></b>

            </p>



            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">  a. Amount of Loan : </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp; The Lender/s hereby agree to grant to the Borrower a sum not exceeding the amount specified in the Loan Agreement for the Loan

                Period and the Borrower here by accepts the Loan and agrees to repay the amount along with all Interest, charges, dues, in

                accordance with the terms and conditions set out in the Transaction Documents.

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">  b. Disbursement of Loan </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;The Loan or such portion thereof, as agreed to be contributed by each Lender shall be disbursed by the respective Lenders to the Borrower within 2 (two) Business Days of the later of the execution of this Loan Agreement and the successful completion of all the conditions precedent mentioned in the Transaction Documents including receipt of all required KYC Documents from the Borrower and creation and/or perfection of security if any (“Availability Period”) subject to the Borrower complying with the provisions of the Transaction Documents. The obligations of each of the Lenders is several. Failure of a Lender to carry out its obligations hereunder does not relieve the Borrower of its obligations under the Transaction Documents to which it is a party. No Lender is responsible for the obligations of the other Lenders under this Loan Agreement. The rights of each Lender under the Transaction Documents and any security document as applicable, are also separate and independent. A Lender may separately enforce any of its rights arising out of the Transaction Documents and the security documents, if any.

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;The execution of this Loan Agreement shall commit the Borrower to borrow the amount requested herein unless the Lenders have cancelled the Loan as per the terms of Clause 3 (b) of this Loan Agreement or given a notice of suspension, termination or cancellation pursuant to this Loan Agreement.

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;Upon successful completion of all the conditions precedent mentioned in the Transaction Documents including receipt of all required KYC Documents from the Borrower and creation and/or perfection of security, if any, each Lender shall, within the Availability Period, transfer by RTGS or any other electronic mode or by way of a cheque, the amount as mentioned in Clause 2 of the Loan Agreement- Principal Document, in immediately available funds in Rupees, into the Lender Escrow Account. The Trustee will release the amounts from the escrow accounts to the Designated Borrower Account within [2] Business Days from the completion of the Availability Period. The withdrawal by the Borrower of any amount disbursed into the Designated Borrower Bank Accounts also an acceptance of the Loan by the Borrower as per the terms and conditions mentioned in the Transaction Documents. The Borrower acknowledges and understands that the Lender(s) retain the right to cancel the Loan or their contribution thereto, in full or in part, in case of more than one Lender, at their sole discretion at any stage during the Availability Period without any obligation to notify the Borrower. Any undisbursed portion of the Loan shall stand automatically cancelled at the close of normal business hours at the end of the Availability Period. The Borrower further acknowledges and agrees such cancellation shall not result in any responsibility or obligation on any of the Lenders and/or FDSPL, including without limitation, the obligation to arrange any replacement lender. The Borrower further acknowledges and agrees that such cancellation shall not in any manner affect the obligations of the Borrower hereunder and under the Transaction Documents including their obligations to make Repayments as per the terms of the Transaction Documents.

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;In case of multiple Lenders, the failure of any Lender to make available to the Borrower its contribution to the Loan, in full or in part, shall not relieve any other Lender of its obligation hereunder and under the Transaction Documents to make available to the Borrower its contribution to the Loan in full and no Lender shall be responsible for the failure of any other Lender to make available its contribution to the Loan or any portion thereof.

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;Notwithstanding anything else provided in the Transaction Documents, in case of any activity being undertaken by the Borrower and/or the Lender(s), which is viewed by  FDSPL, at its sole discretion, to be non-compliant with or in breach of any Applicable Law or involving unethical business practices, or against public policy, or to be in violation of its internal policies,  FDSPL shall have the right, such right to be exercised at its sole discretion, but not the obligation, to cancel the provision of any Loan through the Website, whether any complaint is received by  FDSPL regarding such Borrower and/or Lender(s) or otherwise.

            </span>





            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">  c. Security Interest </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;The Borrowers obligations to repay the Loan and payment of Interest, charges, fees and all other obligations and liabilities of the Borrower to the Lenders under this Loan Agreement shall be secured in the following manner:

            </span>



            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;(i) The Borrower shall on the Effective Date provide three (3) signed cheques (“Cheques”) each, in the name of the “Fintelligence Data Science Pvt. Ltd. Borrower Escrow A/c” of a bank regulated by the RBI to  FDSPL. The Cheques shall be provided towards Repayment and shall be for an amount equal to the total EMI amount payable by the Borrower to the Lenders pursuant to this Loan Agreement and shall be drawn in the name of “Fintelligence Data Science Pvt. Ltd. Borrower Escrow A/c”. The Borrower shall not close this bank account, or make a stop payment request for such Cheques or payment instructions without prior written approval of  FDSPL and in case such account is closed with the prior approval as stated above, the Borrower shall prior to such closure, replace the Cheques with fresh cheques in the name of the “Fintelligence Data Science Pvt. Ltd. Borrower Escrow A/c” to the satisfaction of  FDSPL. The Trustee and/or  FDSPL shall be entitled to present these Cheques with bank for realization of such Cheques in case the Borrower is in default of payment of any EMI.

             </span>

             <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;(i) If at any time after the Effective Date, any of the Cheques delivered by the Borrower:

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;(1) is used, lost, destroyed or misplaced, then on the occurrence of such an event, the Borrower on receipt of such an

                intimation of such use, loss, destruction or misplacement (as the case may be) from the Lenders, the Trustee or from  FDSPL,

                (on behalf of the Lenders, deliver to the Lenders, the Trustee or to  FDSPL as required in terms of such intimation) such number of cheques as are adequate to replace those that have been used, lost, destroyed, misplaced, within a period of three (03) Business Days from the date of receipt of such information.

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;(2) becomes non-encashable due to the insolvency of the Borrower then in such an event, the Lenders and/or Trustee and/or  FDSPL shall be entitled to institute insolvency proceedings against the Borrower.

             </span>

             <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;(3) becomes non-encashable due to the death of a Borrower then in such an event the Lenders claims under this Loan Agreement can be settled from the estate of the deceased Borrower, if the estate is solvent.

             </span>

             <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;(i) On the Effective Date, the Borrower shall also execute a demand promissory, in the form set forth at (Schedule I) to this Loan Agreement, in favour of the Lenders. The promissory note can be presented for enforcement by the Lenders and/or Trustee and/or  FDSPL only upon the occurrence of an Event of Default.

             </span>







             <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">  d. Interest </span>

             <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (i) The Borrower shall be liable to pay Interest in respect of the entire outstanding, unpaid principal balance of the Loan and outstanding interest payments to each Lender at the interest rate specified in the Loan Agreement and as per the repayment schedule set out in Annexure I to this Loan Agreement. The interest rates payable by the Borrower shall be subject to the changes in interest rate directed under any Applicable Law including directions by the RBI from time to time to the extent such Applicable Law and/or directives are applicable to the Loan. The Interest on the Loan shall accrue as from the date the amount of the Loan is credited to the Designated Borrower Account. Such repayments of any of the Loan shall continue until the date of complete Repayment of the Loan and all Interest and all other amounts, accrued and payable in terms of this Loan Agreement are being paid in full. The Borrower further acknowledges that the interest rate may be determined on the basis of the credit score of the Borrower or any other proprietary method to assess the credit worthiness of the Borrower, as deemed appropriate.

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (ii) Notwithstanding anything stated in this Loan Agreement, in the first instance the Borrower shall repay, the principal amounts and all other amounts payable with respect to the Loan to each of the Lenders as per the repayment schedule, commencing from the first repayment date as set out in Annexure I to this Loan Agreement and subject to Clause 3 (d)

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (iii) below no reduction in the EMI shall be permitted by any of the Lenders, unless mutually agreed by all the Lenders in writing.

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (iii) If, for any reason, the amount of the Loan disbursed by the Lenders is less than the Loan, the amounts of EMI shall stand reduced proportionately and shall be payable on the dates as specified in Annexure I to this Loan Agreement.

             </span>

             <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (iv) Without prejudice to or limiting the rights and remedies available to the Lenders under the Transaction Documents or otherwise under Applicable Law, if the Borrower fails to pay any amount payable by it to the Lenders under the Transaction Documents on their respective due dates, the Borrower shall pay on the defaulted amounts, liquidated damages at the default interest rate, as may be applicable as per the terms and conditions on the Website, from the respective due date until the actual date of payment. For the avoidance of doubt, it is clarified that the liquidated damages payable by the Borrower shall be in addition to the Interest payable by the Borrower.

             </span>

             <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (v) The Borrower acknowledges that the sums, including but not limited to any late fee, additional interest, liquidated damages stated herein are reasonable and normal and they represent genuine pre-estimates of the loss likely to be incurred by the Lenders in the event of non-payment or deviation by the Borrower.

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (vi) The Borrower acknowledges that the Loan provided under this Loan Agreement are for commercial transaction and waives any defences available under usury or other Applicable Law relating to charging of Interest.

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">  e. Mode of payment of Instalment </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (i) The Borrower shall transfer by RTGS or NACH or any other electronic mode or by way of deposit of a cheque, as may be permitted for making such transfer under Indian laws, a sum equal to the EMI into the Borrowers Escrow Account as per the repayment schedule set out in Annexure I to this Loan Agreement or if such day is a not a Business Day, then the same will be transferred or deposited on the next Business Day. If the amount transferred or deposited into the Borrowers Escrow Amount is less than the EMI, the amount transferred or deposited by the Borrower into the Borrowers Escrow Account shall be utilized in making payment to each Lender in proportion to their contribution to the Loan. Not with standing anything else provided in the Transaction Documents, FDSPL shall have the absolute and unconditional authority and right to change or authorize a change of the repayment schedule of EMI set out at Annexure I to this Loan Agreement, to any other day of the month, with notice to the Borrower and the Lender.

                The amounts deposited into the Borrowers Escrow Account shall be distributed by the Trustee between the Lenders for repayment of the Loan by transferring the repayment amounts to the Designated Lender Accounts.



                On the failure to pay any portion of the EMI or any interest fee, charges, costs or any other amounts due by the Borrower to the Lenders or any part thereof, on the due date of such payment, the Lenders shall be entitled to exercise all rights available under Applicable Law and/or equity and to also send reminder notices to the Borrower, either directly or through FDSPL and/or any Person acting on its/ FDSPLs behalf.



            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (ii) Unless otherwise required by the Lenders, any payments due and payable to the Lenders and made by the Borrower shall be appropriated towards such dues in the following order:

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (A) firstly, towards costs, charges, expenses and other amounts incurred by the Lenders;

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (B) secondly, towards liquidated damages if any, due in terms of the Transaction Documents;

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (C) thirdly, towards any additional interest if any, due in terms of the Transaction Documents;

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (D) fourthly, towards Interest payment;

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (E ) fifthly, towards premium on Pre-payment of the Loan; and

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (F) lastly, towards repayment of principal Loan due and payable.

            </span>





            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (iii) The late fee will be charged at the rate of mentioned in this Loan Agreement .In case of delayed payments by the Borrower, the monies received from the Borrower shall be appropriated in the following order:

              </span>



              <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (A) Costs, charges, expenses and other amounts incurred in getting the due payments from the Borrower;

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (B) Late fee and penal charges, if any due to the Lenders;

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (C) Liquidated damages if any, due in terms of the Transaction Documents;

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (D) Additional interest if any, due in terms of the Transaction Documents;

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (E) Interest if any, due in terms of the Transaction Documents;

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (F) Premium on Pre-payment of the Loan; and

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (G) principal Loan due and payable.

            </span>







            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (iv) The Borrower shall be responsible for payment of all taxes (whether payable during the tenor of this Loan or thereafter), charges duties, costs and expenses including the stamp duty in respect of this Loan Agreement or the transaction contemplated therein.

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (v) In the event that the Borrower does not deposit or pay any EMI or Interest or charges or any part thereof, by the stipulated due date, or upon the occurrence of any Event of Default, in addition to any other right available under the Transaction Documents, the Trustee and/or FDSPL is entitled to deposit any of the Cheques provided by the Borrower, without any requirement to give notice or intimation to the Borrower.

                The Borrower understands and unconditionally waives any notice or intimation for deposit of such Cheques.

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (vi) If any of the Cheques delivered by the Borrower is used by the Trustee pursuant to Clause 3 (e)

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (vii)  then upon the occurrence of such an event. the Borrower on receipt of such notification, shall deliver to the Trustee or to  FDSPL freshly issued Cheques to replace those that have been, used within a period of fifteen (15) Business Days from the date of receipt of such notification.(vi) If any of the Cheques delivered by the Borrower is used by the Trustee pursuant to Clause 3 (e)

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (viii) In addition to any other rights available under Applicable Law and the Transaction Documents, the Borrower shall be liable to pay penalty charges and any other charges levied by the Lenders and/or FDSPL on account of cheque bouncing or on return of any cheques/dishonour of NACH issued by the Borrower pursuant to the Transaction Documents.

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (IX) It is agreed and understood by the Borrower that failure to deposit the Cheques due to any reasons whatsoever will not affect the liability of the Borrower to repay the Loan.

            </span>









            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                f. Pre-payment of Loan

            </span>



            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                The Borrower, after a period of three months from the date of disbursement of the Loan to the Designated Borrower Account, can at time prepay the Loan and there is no penalty for the same. Pre-payment can be done in partial or full without any charges accruing to the Borrower after expiry of three months from the date of disbursement of the Loan to the Designated Borrower Account. The Borrower can prepay the Loan at any time before expiry of the three months from the date of disbursement of the Loan to the Designated Borrower Account, provided the Borrower pays Interest for the three month period.

            </span>



        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">4. Security</span></b>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;The Loans together with all Interest, further interest, additional interest, liquidated damages, costs, expenses, fees including expenses payable to the Lenders, Escrow Bank, Trustee and/or FDSPL and any other monies stipulated in the Transaction Documents shall, in the form and manner satisfactory to the Lenders, be secured by a guarantee, if so specified in this Loan Agreement.

                The Borrower shall, in form and substance as stated in this Loan Agreement, do all such acts and deeds, including without limitation, filing and registering any document, as may be required to create the security as set out in sub-Clause 4.(a) above read together with Clause 4 of the Loan Agreement- Principal Document, and the security shall be duly perfected and duly registered in accordance with Applicable Law.

                The security specified in sub-Clause 4.(a) above read together with Clause 5 of the Loan Agreement- Principal Document, shall be created and perfected, on or before (Not applicable) Business Days from the execution of this Loan Agreement.





                Provided however that, the Borrower shall procure that the Guarantor shall execute the deed of guarantee, simultaneously with the execution of this Loan Agreement.



            </span>

        </p>

        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">5. Representations and warranties of the Parties</span></b>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                a. Each Party makes the following representations and warranties with respect to itself, and confirms that they are, true, correct and valid:

            </span>

        </p>

        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">●</span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;  Each Party and the Guarantor is an Indian Resident and an Indian Citizen, and they have full power and authority to enter into, deliver and perform the terms and provisions of each of the Transaction Documents and, in particular, to exercise its rights, perform the obligations expressed to be assumed by and make the representations and warranties made by them thereunder. </span>

        </p>

        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">●</span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp; If the Borrower and/or Guarantor is an individual, then each such party represents that he is 21 years of age or above and if any Lender is an individual, then such Party represents that he is 18 years of age or above.

        </p>

        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">●</span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp; If any of the Parties is a company, then such Party also represents that it is duly organized and validly existing company incorporated in India under the Act.

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">●</span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp; The obligations of each Party under the Transaction Documents are legal and valid binding on it and enforceable against it in accordance with the terms hereof.

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">●</span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp; The Parties and the Guarantor have the legal competence and capacity to execute and perform the Transaction Documents.

        </p>





        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">●</span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp; Each Party shall ensure compliance with all Applicable Law and regulations in compliance with or performance of the terms and provisions of the Transaction Documents. Each Party is entering into the Transaction Documents and the transaction(s) contemplated therein in compliance with the P2P RBI Master Directions.

        </p>





        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                b. The Borrower hereby represents that the Borrower and the Guarantor/security provider, if any, under this Loan Agreement, are solvent and have not become the subject of voluntary or involuntary proceedings under any bankruptcy, insolvency law, or winding up and no such proceedings are threatened to be initiated against the Borrower or the Guarantor or any security provider.

             </span>

        </p>





        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                c. The Borrower hereby represents and warrants that the aggregate loans taken by the Borrower, across all peer to peer lending

                platforms, is and shall continue to be within the cap of Rs. 10,00,000 (Rupees ten lakhs only) provided in the P2P RBI Master

                Directions.

            </span>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                d. Each Lender hereby represents that the funds used for the purpose of lending and advancing the Loan have been obtained from a lawful and genuine source and have not been obtained in an unlawful, unethical or immoral manner.



                Each Lender also represents that he is not a money-lender under Applicable Law relating to money-lending in India.



            </span>

        </p>

        lw_send_email

        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                e. Each Lender hereby represents that the exposure of such Lender to the Borrower, across all peer to peer platforms, is within the cap of Rs. 50,000 (Rupees fifty thousand only) provided in the P2P RBI Master Directions.

            </span>

        </p>





        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                f. Each Lender hereby represents and warrants that the aggregate exposure of such Lender to all borrowers, across all peer to peer platforms, is and shall continue to be within the cap of Rs.50,00,000 (Rupees Fifty lakhs only) provided in the P2P RBI Master Directions.

            </span>

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">6. Covenants by the Parties</span></b>

        </p>







        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                a. Each individual Lender and the Borrower covenant to each other and to FDSPL and agree:

            </span>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">●</span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp; that they have read this Loan Agreement, the Terms and Conditions, Privacy Policy, the Transaction Documents and other material available on the Website and this Loan Agreement, the Terms and Conditions, Privacy Policy, the Transaction Documents and other material available on the Website has been explained to them in the language understood by them and they have understood the entire meaning of all the clauses, and hereby confirm that they are legally bound by the all of the aforesaid documents and material.

        </p>





        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">●</span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp; that the information and financial details submitted by them on the Website are factually true, correct and complete.

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">●</span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp; that they understand and agree that  FDSPL through its Website only facilitates the meeting of the lenders and the borrowers and is not responsible or liable in any way for the accuracy of any information provided on the Website by lenders and borrowers, for the lending or repayment of any loans availed by the borrowers.

        </p>







        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">●</span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp; that FDSPL is not responsible in any manner in relation to the acts or omissions of the Borrower and/or the Lender(s).

        </p>

        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">●</span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp; that  FDSPL has absolute and unconditional authority and right to change or authorise a change of the repayment schedule of EMI set out at Annexure I to this Loan Agreement, to any other day of the month, with notice to the Borrower and the Lender(s).

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">●</span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp; that  FDSPL at its sole discretion, has absolute and unconditional authority and right to require a change of the

                Designated Borrower Account and/or the Designated Lender Accounts by sending a written notice to the Borrower and/or

                Lender(s) as the case may be.

        </p>









        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                b. The Borrower further covenants, agrees and undertakes:a. Each individual Lender and the Borrower covenant to each other and to FDSPL and agree:

            </span>

        </p>







        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

            &nbsp;

            (i) to utilize the entire Loan for the Purpose and not to utilize the loan for any unlawful purpose;

        </span>



        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

            &nbsp;

            (ii) to  FDSPL notify any event or circumstances, which might operate as a cause of delay in the completion of the transactions contemplated under the Transaction Documents;

        </span>



        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

            &nbsp;

            (iii) to provide accurate and true information;

        </span>



        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

            &nbsp;

            (iv) to repay the required funded amount without any failure;

        </span>



        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

            &nbsp;

            (v) to maintain sufficient balance in the account of the drawee bank for payment of EMIs and the Cheques issued by them on the day when any instalment becomes due and thereafter to honour all such post-dated cheques;

        </span>



        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

            &nbsp;

            (vi) to due performance of all the terms and conditions provided under the Transaction Documents;

        </span>





        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                that it agrees and accepts that a copy of this Loan Agreement shall be uploaded on the Website once it has been executed by the Parties and physical copies of the executed Loan Agreement shall not be provided to the Borrower;

            </span>

        </p>





        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                and explicitly authorizes  FDSPL to access their credit score either directly or through any other lending or financing institute;

            </span>

        </p>





        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                to bear the collection charges, if any, incurred by the Lenders, the Trustee,  FDSPL and/or any other Person on their behalf;

            </span>

        </p>





        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                to do all acts, deeds and things essential to act in compliance with the P2P RBI Master Directions, including without limitation executing power/s of attorney and/or other documents/writings in favour of  FDSPL;          to bear the collection charges, if any, incurred by the Lenders, the Trustee,  FDSPL and/or any other Person on their behalf;

            </span>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                to pay and discharge all taxes imposed on it including all taxes the Borrower has agreed to pay pursuant to any Transaction Documents and shall file all returns relating thereto. The Borrower shall also pay or arrange for payment of all duties, fees or other charges payable on or in connection with the execution, issue, delivery, registration, or notarization, or for the legality, or enforceability, of this Loan Agreement, the other Transaction Documents and any other document related to this Loan Agreement; and to indemnify and hold Lender and  FDSPL harmless from and against any and all claims, action, liability, cost, loss, damage, accrued to FDSPL arising out of any Events of Default under Clause 7 of this Loan Agreement and/or breach/ violation of the Transaction Documents by the Borrower and/or breach/violation of the Terms and Conditions by the Borrower and/or non-compliance by the Borrower with Applicable Law, rules and regulations or agreements prevailing from time to time.

            </span>

        </p>





        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                c. The Lenders further covenant, agree and undertake:

            </span>

        </p>





        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

            &nbsp;

            (i) to provide accurate and true information;

        </span>



        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

            &nbsp;

            (ii) to fund the amount specified against their name in the Loan Agreement- principal Document to the Borrower by making necessary transfer to the Lender Escrow Account;

        </span>



        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

            &nbsp;

            (iii) due performance of all the terms and conditions provided under Transaction Documents;

        </span>



        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

            &nbsp;

            (iv) to do all acts, deeds and things essential to act in compliance with the P2P RBI Master Directions, including without limitation executing power/s of attorney and/or other documents/writings in favour of  FDSPL; and

        </span>





        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

            &nbsp;

            (v) to indemnify and hold FDSPL harmless from and against any and all claims, action, liability, cost, loss, damage, accrued to  FDSPL arising out of any Event of Default under sub-Clause 7 e) of this Loan Agreement and/or breach/violation of the Transaction Documents by the Lender(s) and/or breach/violation of the Terms and Conditions by the Lender(s) and/or non-compliance with Applicable Law, rules and regulations or agreements prevailing from time to time.

        </span>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">7. Events of default</span></b>

        </p>



        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

           <b>The following events shall constitute Events of Default under this Loan Agreement:</b>

        </span>





        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                a. The Borrower failing to repay the Loan or any Interest, fee, charges, or costs in the manner herein contained or any other amount due hereunder remains unpaid after the date on which it is due; or

            </span>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                b. In case of death of the Borrower or the Borrower becomes insolvent or bankrupt; or

            </span>

        </p>





        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                c. Any of the Cheques delivered or to be delivered by the Borrower in terms and conditions hereof is not realized for any reason whatsoever on presentation; or any instruction being given by the Borrower for stop payment of any Cheques for any reason whatsoever; or

            </span>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                d. On the Borrower committing breach of any of the terms, covenants and conditions contained in the Transaction Documents or any information given or representations made by the Borrower under the Transaction Documents being found to be inaccurate or misleading;

             </span>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                e. Termination of registration of any Borrower or Lender on the Website as envisaged in the Terms and Conditions, thereby constituting termination of relationship between FDSPL and such Borrower and/or Lender. It is hereby clarified that, upon any assignment or transfer of the Website by FDSPL to its successors or assigns or any third parties generally, all aspects of operation of the Website/platform and all matters pertaining thereto, including without limitation all Transaction Documents executed by and between all Lenders and Borrowers and all transactions contemplated thereunder, shall continue to be legally valid and subsisting and effective; or

            </span>

        </p>

        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                f. There exists any other circumstance, at the Lenders discretion, which may jeopardize the Lenders interest.

            </span>

        </p>



        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">8. Consequence of default</span></b>

        </p>





        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                a. On the happening of any of the Events of Default, the Lenders either individually or collectively, and/or  FDSPL (acting on behalf of the Lenders, at its sole discretion and subject to Applicable Law) and/or any Person acting on their behalf may, at their discretion, by a notice in writing to the Borrower and without prejudice to any other rights and remedies available to Lenders under this Loan Agreement and/or any other Transaction Document or otherwise call upon the Borrower to pay all the Borrower’s dues in respect of the Loan.

             </span>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                b. On the happening of any of the Events of Default, the Trustee and/or FDSPL shall be entitled to present the Cheques with a bank for realization of such Cheques against all dues and amounts payable by the Borrower under the Transaction Documents.

             </span>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                c. On the happening of any of the Events of Default, the Lenders, either individually or collectively, and/or  FDSPL (acting on behalf of the Lenders, at its sole discretion and subject to Applicable Law) shall have the right to take such necessary steps as permitted by Applicable Law against the Borrower to realize the amounts due along with the Interest and other fees/costs as agreed in the Transaction Documents including appointment of collection agents, attorneys and/or consultants, as it thinks fit.

            </span>

        </p>

        Profiling [ Edit inline ] [ Edit ] [ Explain SQL ] [ Create PHP code ] [ Refresh ]


        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                d. In the event of death of the Borrower then in such an event the Lenders claims under this Loan Agreement can be settled from the estate of the deceased Borrower, if the estate is solvent.

            </span>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                e. In the event of the Borrower committing default in the repayment of EMI or any portion thereof or any amounts due and

                payable in respect of the Loan or upon the occurrence of any Events of Default, the Lenders shall have an unqualified right

                 to disclose the name of the Borrower and its directors, if any, to any governmental, legislative, executive, administrative,

                 judicial or regulatory authority, body or agency including the RBI and Credit Bureau.

                 The Borrower declares that the information and data furnished by the Borrower to the Lenders and/or to the Trustee and/or to FDSPL is updated, true, correct and complete and further agrees and undertakes that:

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (i)  FDSPL is authorized to access the Borrowers credit information, undertake credit assessment and risk profiling of the Borrower and disclose the same to the Lenders;

             </span>

             <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (ii) Credit Bureau and any other agency so authorized may access, use, process the said information and data disclosed by the Lenders in the manner as deemed fit by them; and

            </span>

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                &nbsp;

                (iii) Credit Bureau, and any other agency so authorized may furnish for consideration, the processed information and date or products thereof prepared by them, to banks/financial institutions and other credit grantors or registered users, as may be specified by the RBI in this behalf.

            </span>

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">9. Lien and Set-Off</span></b>

        </p>





        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                The Lenders shall have the right of lien and set-off, which the Lenders may at any time without prejudice to any of its specific rights under any other agreements, at its sole discretion and without notice to the Borrower, utilize to appropriate any moneys belonging to the Borrower and lying/deposited with the Lenders or due by the Lenders to the Borrower, towards any of the Lenders dues and outstanding amounts under or in respect of a loan facility, including any charges/fees/dues payable under this Loan Agreement.

             </span>

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">10. Notices</span></b>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                Except as otherwise expressly provided herein, all notices and other communications provided at various places in this Loan Agreement shall be in writing</span>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                Any such notice or other written communication shall be deemed to have been served:

            </span>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                a. if delivered personally, at the time of delivery;

             </span>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                b. if sent by registered letter when the registered letter would, in the ordinary course of post, be delivered whether actually delivered or not;

             </span>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                c. if sent by courier service, (i) two (2) Business Day after deposit with an overnight courier if for inland delivery and (ii) five (5) Business Days after deposit with an international courier if for overseas delivery;

             </span>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                d. if sent by facsimile transmission or electronic mail, at the time of transmission (if sent during business hours) or (if not sent during business hours) at the beginning of business hours next following the time of transmission in the place to which the facsimile was sent.

              </span>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                In proving such service it shall be sufficient to prove that personal delivery was made or in the case of prepaid recorded delivery, registered post or by courier, that such notice or other written communication was properly addressed and delivered or in the case of a facsimile message or electronic mail, that an activity or other report from the senders facsimile machine or transmitting device can be produced in respect of the notice or other written communication.

            </span>

        </p>

        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                All correspondence shall be addressed to the address as mentioned in the description of Parties appearing in this Loan Agreement unless a different address is notified by such Party in writing to the other Parties.

            </span>

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">11. Severability</span></b>

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            If any provision of this Loan Agreement is found to be invalid or unenforceable, then the invalid or unenforceable provision will be deemed superseded by a valid enforceable provision that most closely matches the intent of the original provision and the remainder of this Loan Agreement shall continue in effect.

        </p>









        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">12. Governing law, dispute resolution and jurisdiction</span></b>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                a. The Parties agree the courts as specified in this Loan Agreement shall have exclusive jurisdiction to settle any dispute arising out of or in connection with the Transaction Documents or the performance thereof and accordingly, any suit, action or proceeding arising out of the Transaction Documents or the performance thereof may be brought in such courts or tribunals and the Parties irrevocably submits to and accepts the jurisdiction of those courts or tribunals.</span>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                b. The Transaction Documents and the arrangements contemplated hereby shall in all respects be governed by and construed in accordance with the laws of India without giving effect to the principles of conflict of laws thereunder.

        </p>





        <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

            <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">13. Force majeure</span></b>

        </p>



        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                No Party shall be liable to the other if, and to the extent, that the performance or delay in performance of any of their obligations under this Loan Agreement is prevented, restricted, delayed or interfered with, due to circumstances beyond the reasonable control of such party, including but not limited to, Government legislations, fires, floods, explosions, epidemics, accidents, acts of God, wars, riots, strikes, lockouts, or other concerted acts of workmen, acts of Government and/or shortages of materials. The Party claiming an event of force majeure shall pr FDSPL notify the other Parties in writing, and provide full particulars of the cause or event and the date of first occurrence thereof, as soon as possible after the event and also keep the other Parties informed of any further developments. The Party so affected shall use its best efforts to remove the cause of non-performance, and the Parties shall resume performance hereunder with the utmost dispatch when such cause is removed.

            </span>

       </p>





       <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

        <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">14. Binding effect</span></b>

       </p>





       <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

            All warranties, undertakings and agreements given herein by the Parties shall be binding upon the Parties and upon its legal representatives and estates. This Loan Agreement (together with any amendments or modifications thereof) supersedes all prior discussions and agreements (whether oral or written) between the parties with respect to the transactions contemplated hereunder.

        </span>

      </p>



      <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">

        <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">15. Entire agreement</span></b>

       </p>



       <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

            This Loan Agreement has been generated by the Website electronically in such form and content as approved by the Parties, by the Parties following prescribed procedures on the Website and such aforesaid approval of Parties on the Website constitutes an approval of all terms and conditions herein contained.

                  </span>

       </p>



       <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

            b. This Loan Agreement together with the Transaction Documents constitutes the entire agreement between the Parties.

            </span>

         </p>

         <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">

            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">

                c. In the event of any inconsistency between the provisions of the Terms and Conditions, the Privacy Policy, and this Loan Agreement, the provisions of the Terms and Conditions shall prevail.
                </span>
             </p>
             <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
                <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
                    d. The provisions of this Loan Agreement shall have an overriding effect over the provisions of the Escrow Agreement and in the event of any conflict between the provisions contained in the Escrow Agreement and the provisions contained in this Loan Agreement, the provisions contained in this Loan Agreement shall prevail.
                 </span>
                 </p>
                 <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">
                    <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">16. Miscellaneous</span></b>
                   </p>
                   <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
                    <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
                        a. Amendments and Waivers :
                        The Lenders reserve the right to modify/revise/add any of the terms and conditions of this Loan Agreement, pertaining to the Loan, as they deem fit. No waiver of any provisions, condition or covenant of the Transaction Documents shall be effective as against the waiving party unless such waiver is in writing signed by the waiving party.
                    </span>
                     </p>
                     <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
                        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
                            b. Language:
                            English shall be used in all correspondence and communications between the Parties.
                         </span>
                         </p>
                         <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
                            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
                                c. Cumulative Rights :
                                All remedies of the Lenders under this Loan Agreement whether provided herein or conferred by statute, civil law, common law, custom, trade, or usage are cumulative and not alternative and may be enforced successively or concurrently.
                             </span>
                             </p>
<p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
                                <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
                                    d. Benefit of this Loan Agreement :
                                    This Loan Agreement shall be binding upon inure to the benefit of each Party thereto and its successors or heirs, administrators, as the case may be.
                                 </span>
                                 </p>
                                 <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
                                    <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
                                        e. Costs :
                                        All costs and expenses of with respect to execution of the Loan Agreement and security documents, if any, shall be borne by the Borrower.
                                        Stamp duty and similar duty or payments, if any, payable with respect to the Loan Agreement and the security documents, if any, shall be borne entirely by the Borrower.
                                    </span>
                                     </p>
                                     <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
                                        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
                                            f. Delays/Omissions :
                                            Any delay in exercising or omission to exercise any right, power or remedy accruing to the Lenders and/or the Escrow Agent and/or the Trustee and/or  FDSPL under these Transaction Documents or other document shall not impair any such right, power or remedy and shall not be construed to be a waiver thereof or any acquiescence in any default; nor shall the action or inaction of the Lenders and/or the Escrow Agent and/or the Trustee and/or  FDSPL in respect of any default or any acquiescence in any default, affect or impair any right, power or remedy of the Lenders and/or the Escrow Agent and/or the Trustee and/or  FDSPL in respect of any other default.
                                        </span>
                                         </p>
                                         <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
                                            <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
                                                g. Counterparts and Execution
                                                This Loan Agreement may be executed in one or more counterparts, each of which shall be deemed an original but all of which together shall constitute one and the same instrument and any Party may execute this Agreement by signing any one or more of such originals or counterparts.
                                                <b>The Parties agree that:</b>
                                                each Part shall execute this Loan Agreement, within 24 hours of the same being generated/provided by the Website, as envisaged at Clause 15(a) hereof;
                                                each Party may execute this Loan Agreement, either by affixing an Electronic Signature on an electronic copy of this Loan Agreement or by affixing a physical signature on a hard copy of this Loan Agreement as per its convenience and further agree that regardless of which of the aforesaid methods of affixation of signatures is employed by any Party, such Party shall be deemed to have duly affixed its signature and to have validly executed this Loan Agreement.
                                                Where physical signature is affixed, facsimile transmission of an executed signature page of this Agreement or email attaching a scanned copy of executed signature page of this Agreement, shall constitute due execution of this Agreement by such Party and shall be sufficient evidence of the execution hereof.
                                            </span>
                                             </p>
     <p style="margin-bottom: 0in; line-height: 150%; background: white; font-size: 10pt;">
         <b><span style="font-size: 12pt; font-family: Arial, Helvetica, sans-serif; color: #2e2e2e;">17. Acceptance</span></b>
     </p>
     <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            The Parties hereby declare that each Party is responsible/liable for its own actions/decisions, and it has made its own independent decisions to enter into the transactions contemplated herein and/or the Transaction Documents. It is not relying on any communication (written or oral) of the other Party or  FDSPL as advice or as a recommendation to enter into the transactions contemplated under this Loan Agreement and/or the Transaction Documents, it being agreed that any provisions/contents of the Terms and Conditions and/or Privacy Policy and/or any other Transaction Document shall not be considered advice or a recommendation to avail of the Loan and/or enter into any transaction and hereby agree that it is solely responsible/liable for all risks associated with the transactions contemplated under this Agreement and/or any other Transaction Document.
       </span>
    </p>
    <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            <b>Schedule</b>
        </span>
    </p>
    <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            ON DEMAND, I, Borrower, unconditionally promise to pay [Investor as per Annexure II], the lender at [ _______ ] or at such other place as the lender may designate, the principal sum of Rs. [Principal amount] {______} with interest thereon, from the date hereof through and including the dates of payment, at interest calculated at [ROI]% (_______ percent) per annum. This amount cannot exceed the amount due to the Lender from the Borrower as per the repayment schedule set forth in the loan agreement between the Borrower and the Lender "Loan Agreement". The Borrower does hereby acknowledge that time is of the essence hereof, and unconditionally promise, that for any principal and interest sum due under this Promissory Note if not received by the Lender within ________ (______) days after the date the Lender make demand for payment of such sum, the Borrower shall pay in addition to the amount of such sum a late payment charge of _______% (______________ percent) per annum over and above the applicable rate of interest of such sum until realization.
        </span>
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            This is the Promissory Note issued pursuant to the Loan Agreement. For the avoidance of doubt, this Promissory Note can be presented by the Lender only upon the occurrence of an Event of Default and the amount payable under this Promissory Note represents the liabilities of the Borrower under the Loan Agreement and shall not in any event exceed the liabilities of the Borrower thereunder. Further any amounts paid under this Promissory Note shall reduce the corresponding liabilities of the Borrower under the Loan Agreement
Capitalized terms used herein but not defined shall have the same meanings given to them in the Loan Agreement
        </span>
    </p>
    <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            <b>[Borrower Signature]
                Date____________
                </b>
        </span>
    </p>
    <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            <b>Annexure I</b>
        </span>
    </p>
    <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            [to insert repayment schedule including details of EMIs]
        </span>
    </p>
    <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            <b>Annexure II </b>
        </span>
    </p>
    <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            1.	Lender 1 :- INDV ID [INDV ID] invested [ amount of loan]
        </span>
    </p>
    <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            2.	Lender 2:- INDV ID [INDV ID] invested [ amount of loan]
        </span>
    </p>
    <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            3.	Lender 3:- INDV ID [INDV ID] invested [ amount of loan]
        </span>
    </p>
    <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            [Lender [●], Lender [●] and Lender [●] shall be collectively referred to as the “Lenders” and individually be referred to as the “Lender”.]
        </span>
    </p>
    <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            IN WHEREOF the Parties have executed this Loan Agreement as of the day and year first above written:
        </span>
    </p>
    <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            <b>For Borrower</b>
        </span>
    </p>
    <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%;
     background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            <b>Signature:</b>
        </span>
        </p>
        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%;
        background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            <b>Name:</b>
        </span>
        </p>
        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%;
        background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            <b>Title:</b>
        </span>
        </p>
        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%;
        background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            <b>(Authorised signatory)
                Date:
                </b>
        </span>
    </p>
    <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            <b>For Lender[please insert ID]</b>
        </span>
        </p>
        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%;
        background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            <b>Signature:</b>
        </span>
        </p>
        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%;
        background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            <b>Date:</b>
        </span>
    </p>
    <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%; background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            <b>For Lender[please insert INDV ID]</b>
        </span>
        </p>
        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%;
        background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            <b>Signature:</b>
        </span>
        </p>
        <p style="margin-top: 14pt; margin-right: 0in; margin-bottom: 0in; margin-left: 1in; text-indent: -0.25in; line-height: 150%;
        background: white; border: none;">
        <span style="font-size: 10pt; line-height: 150%; font-family: Arial, Helvetica, sans-serif; color: black;">
            <b>Date:</b>
        </span>
    </p>
            </div>
    </body>
</html>';

            $file_name = "sanction_letter_" . $lead_id . "_" . rand(1000, 9999) . ".pdf";

            //$file_path_with_name = UPLOAD_PATH . $file_name;
            $file_path_with_name = '/var/tmp/' . $file_name;
            require_once __DIR__ . '/../../vendor/autoload.php';

            //  print_r($html_string);

            $mpdf = new \Mpdf\Mpdf();

            $mpdf->WriteHTML($html_string);

            //$mpdf->Output(UPLOAD_TEMP_PATH . $file_name, 'F');
            $mpdf->Output($file_path_with_name, 'I');

            //            if (file_exists($file_path_with_name)) {
            //
            //                $upload_return = uploadDocument($file_path_with_name, $lead_id, 2, 'pdf');
            //                $return_array['status'] = 1;
            //                $return_array['file_name'] = $upload_return['file_name'];
            //                unlink($file_path_with_name);
            //                //$this->updateLeads(['lead_id' => $lead_id], ['cam_sanction_letter_file_name' => $file_name], 'credit_analysis_memo');
            //                $this->updateLeads(['lead_id' => $lead_id], ['cam_sanction_letter_file_name' => $upload_return['file_name']], 'credit_analysis_memo');
            //            } else {
            //
            //                $return_array['errors'] = "File does not exist. Please check offline";
            //            }
        } catch (Exception $e) {

            $return_array['errors'] = "PDF Error : " . $e->getMessage();
        }

        return $return_array;
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

        if (isset($conditions['LD.stage']) && in_array($conditions['LD.stage'], ['S1', 'S2', 'S3'])) {
            if (!empty($search_input_array['sfd'])) {
                $conditions['LD.lead_entry_date >='] = date("Y-m-d", strtotime($search_input_array['sfd']));
            }

            if (!empty($search_input_array['sed'])) {
                $conditions['LD.lead_entry_date <='] = date("Y-m-d", strtotime($search_input_array['sed']));
            }
        }


        if (isset($conditions['LD.stage']) && $conditions['LD.stage'] == 'S14') {
            if (!empty($search_input_array['sfd'])) {
                $conditions['LD.lead_final_disbursed_date >='] = date("Y-m-d", strtotime($search_input_array['sfd']));
            }

            if (!empty($search_input_array['sed'])) {
                $conditions['LD.lead_final_disbursed_date <='] = date("Y-m-d", strtotime($search_input_array['sed']));
            }
        }

        if (isset($conditions['LD.lead_status_id']) && $conditions['LD.lead_status_id'] == '16') {
            if (!empty($search_input_array['sfd'])) {
                $conditions['CO.collection_executive_payment_created_on >='] = date("Y-m-d", strtotime($search_input_array['sfd']));
            }

            if (!empty($search_input_array['sed'])) {
                $conditions['CO.collection_executive_payment_created_on <='] = date("Y-m-d", strtotime($search_input_array['sed']));
            }
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

        if (!empty($search_input_array['scb'])) {
            $conditions['user_screener.name'] = $search_input_array['scb'];
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
        // print_r($conditions);
        // exit;

        $select = 'LD.lead_id ';
        if ($this->uri->segment(1) == "preclosure" || $this->uri->segment(1) ==  "closure") {
            $select .= ' , CO.id';
        }

        $this->db->select($select);

        $this->db->from('leads LD');
        $this->db->join('lead_customer C', 'LD.lead_id = C.customer_lead_id', 'LEFT');
        $this->db->join('loan L', 'L.lead_id = C.customer_lead_id', 'LEFT');
        $this->db->join('credit_analysis_memo CAM', 'LD.lead_id = CAM.lead_id', 'LEFT');
        $this->db->join('users user_screener', 'user_screener.user_id = LD.lead_screener_assign_user_id', 'LEFT');

        if ($this->uri->segment(1) == "preclosure" || $this->uri->segment(1) == "closure") {
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

        if (in_array($conditions["LD.stage"], ['S1']) && $this->uri->segment(1) == 'enquires') {
            unset($conditions["LD.stage"]);
            $conditions["LD.lead_status_id"] = 1;
            $this->db->where('LD.first_name IS NULL');
        }

        if (in_array($conditions["LD.stage"], array("S1", "S2"))) {
            // $this->db->where('LD.lead_is_mobile_verified', 1);
            $this->db->where('LD.first_name IS NOT NULL', null, false);
        }

        if (in_array($this->uri->segment(1), ["collection"])) {
            $this->db->where('repayment_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)', NULL, FALSE);
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

        //$this->db->distinct();
        // $this->db->group_by('`LD`.`lead_id`');
        $leadsDetails = $this->db->get();

        if ($leadsDetails->num_rows() > 0) {
            $total_rows = $leadsDetails->num_rows();
        }
        // print_r($this->db->last_query());
        // exit;

        // print_r($this->db->queries[1]);
        // exit;

        return $total_rows;
    }


    public function countAmountLeads($conditions, $search_input_array = array(), $where_in = array()) {

        $loan_recommended_total = 0;

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

        if (isset($conditions['LD.stage']) && in_array($conditions['LD.stage'], ['S1', 'S2', 'S3'])) {
            if (!empty($search_input_array['sfd'])) {
                $conditions['LD.lead_entry_date >='] = date("Y-m-d", strtotime($search_input_array['sfd']));
            }

            if (!empty($search_input_array['sed'])) {
                $conditions['LD.lead_entry_date <='] = date("Y-m-d", strtotime($search_input_array['sed']));
            }
        }

        if (isset($conditions['LD.stage']) && $conditions['LD.stage'] == 'S14') {
            if (!empty($search_input_array['sfd'])) {
                $conditions['LD.lead_final_disbursed_date >='] = date("Y-m-d", strtotime($search_input_array['sfd']));
            }

            if (!empty($search_input_array['sed'])) {
                $conditions['LD.lead_final_disbursed_date <='] = date("Y-m-d", strtotime($search_input_array['sed']));
            }
        }

        if (isset($conditions['LD.lead_status_id']) && $conditions['LD.lead_status_id'] == '16') {
            if (!empty($search_input_array['sfd'])) {
                $conditions['CO.collection_executive_payment_created_on >='] = date("Y-m-d", strtotime($search_input_array['sfd']));
            }

            if (!empty($search_input_array['sed'])) {
                $conditions['CO.collection_executive_payment_created_on <='] = date("Y-m-d", strtotime($search_input_array['sed']));
            }
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

        if (!empty($search_input_array['scb'])) {
            $conditions['user_screener.name'] = $search_input_array['scb'];
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

        $select = 'LD.lead_id, SUM(CAM.loan_recommended) as loan_recommended_total';
        if ($this->uri->segment(1) == "preclosure" || $this->uri->segment(1) ==  "closure") {
            $select .= ' , CO.id';
        }

        if ($this->uri->segment(1) == "collection") {
            $select = 'LD.lead_id, SUM(CAM.repayment_amount) as loan_recommended_total';
        }

        if ($this->uri->segment(1) == "closure") {
            $select = 'LD.lead_id, SUM(CO.received_amount) as loan_recommended_total';
        }

        if ($this->uri->segment(1) == "audit-inprocess" || $this->uri->segment(1) == "audit-hold" || $this->uri->segment(1) == "audit-recommended") {
            $select = 'LD.lead_id, SUM(CAM.loan_recommended) as loan_recommended_total';
        }

        $this->db->select($select);

        $this->db->from('leads LD');
        $this->db->join('lead_customer C', 'LD.lead_id = C.customer_lead_id', 'LEFT');
        $this->db->join('loan L', 'L.lead_id = C.customer_lead_id', 'LEFT');
        $this->db->join('credit_analysis_memo CAM', 'LD.lead_id = CAM.lead_id', 'LEFT');
        $this->db->join('users user_screener', 'user_screener.user_id = LD.lead_screener_assign_user_id', 'LEFT');
        $this->db->join('users user_audit', 'user_audit.user_id = LD.lead_audit_assign_user_id', 'LEFT');

        if ($this->uri->segment(1) == "preclosure" || $this->uri->segment(1) ==  "closure") {
            $this->db->join($this->table_collection . ' CO', 'CO.lead_id = LD.lead_id AND CO.collection_active = 1 AND CO.collection_deleted = 0', 'left');
        }

        if (in_array($this->uri->segment(1), ["visitrequest", "visitpending", "visitcompleted"])) {
            unset($conditions["LD.stage"]);
            $this->db->join('loan_collection_visit LCV', 'LCV.col_lead_id = LD.lead_id AND LCV.col_visit_active=1', 'left');
        }

        if (in_array($conditions["LD.stage"], array("S1", "S2"))) {

            // $this->db->where('LD.lead_is_mobile_verified', 1);
            $this->db->where('LD.first_name IS NOT NULL', null, false);
        }

        if (in_array($this->uri->segment(1), ["collection"])) {
            $this->db->where('repayment_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)', NULL, FALSE);
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

        //$this->db->distinct();
        $this->db->group_by('`LD`.`lead_id`');

        $leadsDetails = $this->db->get();


        if ($leadsDetails->num_rows() > 0) {
            $total_rows = $leadsDetails->num_rows();
            foreach ($leadsDetails->result() as $row) {
                $loan_recommended_total += $row->loan_recommended_total;
            }
        }


        return $loan_recommended_total;
    }

    public function totalOutstandingAmount($conditions, $search_input_array = array(), $where_in = array()) {

        $total_outstanding = 0;

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

        if (isset($conditions['LD.stage']) && in_array($conditions['LD.stage'], ['S1', 'S2', 'S3'])) {
            if (!empty($search_input_array['sfd'])) {
                $conditions['LD.lead_entry_date >='] = date("Y-m-d", strtotime($search_input_array['sfd']));
            }

            if (!empty($search_input_array['sed'])) {
                $conditions['LD.lead_entry_date <='] = date("Y-m-d", strtotime($search_input_array['sed']));
            }
        }

        if (isset($conditions['LD.stage']) && $conditions['LD.stage'] == 'S14') {
            if (!empty($search_input_array['sfd'])) {
                $conditions['LD.lead_final_disbursed_date >='] = date("Y-m-d", strtotime($search_input_array['sfd']));
            }

            if (!empty($search_input_array['sed'])) {
                $conditions['LD.lead_final_disbursed_date <='] = date("Y-m-d", strtotime($search_input_array['sed']));
            }
        }

        if (isset($conditions['LD.lead_status_id']) && $conditions['LD.lead_status_id'] == '16') {
            if (!empty($search_input_array['sfd'])) {
                $conditions['CO.collection_executive_payment_created_on >='] = date("Y-m-d", strtotime($search_input_array['sfd']));
            }

            if (!empty($search_input_array['sed'])) {
                $conditions['CO.collection_executive_payment_created_on <='] = date("Y-m-d", strtotime($search_input_array['sed']));
            }
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

        if (!empty($search_input_array['scb'])) {
            $conditions['user_screener.name'] = $search_input_array['scb'];
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

        $select = 'LD.lead_id, SUM(CAM.loan_recommended) as loan_recommended_total';

        if ($this->uri->segment(1) == "outstanding-cases") {
            $select = 'LD.lead_id, SUM(L.loan_principle_outstanding_amount) as total_outstanding_amount';
        }

        $this->db->select($select);

        $this->db->from('leads LD');
        $this->db->join('lead_customer C', 'LD.lead_id = C.customer_lead_id', 'LEFT');
        $this->db->join('loan L', 'L.lead_id = C.customer_lead_id', 'LEFT');
        $this->db->join('credit_analysis_memo CAM', 'LD.lead_id = CAM.lead_id', 'LEFT');
        $this->db->join('users user_screener', 'user_screener.user_id = LD.lead_screener_assign_user_id', 'LEFT');
        $this->db->join('users user_audit', 'user_audit.user_id = LD.lead_audit_assign_user_id', 'LEFT');

        if ($this->uri->segment(1) == "preclosure" || $this->uri->segment(1) ==  "closure") {
            $this->db->join($this->table_collection . ' CO', 'CO.lead_id = LD.lead_id AND CO.collection_active = 1 AND CO.collection_deleted = 0', 'left');
        }

        if (in_array($this->uri->segment(1), ["visitrequest", "visitpending", "visitcompleted"])) {
            unset($conditions["LD.stage"]);
            $this->db->join('loan_collection_visit LCV', 'LCV.col_lead_id = LD.lead_id AND LCV.col_visit_active=1', 'left');
        }

        if (in_array($conditions["LD.stage"], array("S1", "S2"))) {

            // $this->db->where('LD.lead_is_mobile_verified', 1);
            $this->db->where('LD.first_name IS NOT NULL', null, false);
        }

        if (in_array($this->uri->segment(1), ["collection"])) {
            $this->db->where('repayment_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)', NULL, FALSE);
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

        //$this->db->distinct();
        $this->db->group_by('`LD`.`lead_id`');

        $leadsDetails = $this->db->get();


        if ($leadsDetails->num_rows() > 0) {
            $total_rows = $leadsDetails->num_rows();
            foreach ($leadsDetails->result() as $row) {
                $total_outstanding += $row->total_outstanding_amount;
            }
        }

        return $total_outstanding;
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

    public function getFollowUpList() {
        $followup_array = array();
        $tempDetails = $this->db->select('m_followup_status_id, m_followup_status_heading')->from('master_followup_status')->where(['m_followup_status_active' => 1])->get();
        foreach ($tempDetails->result_array() as $followup_data) {
            $followup_array[$followup_data['m_followup_status_id']] = $followup_data['m_followup_status_heading'];
        }
        return $followup_array;
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
                $leadsCount = $this->db->select('COUNT(*) as total_count')->where('application_no !=', '')->from('leads')->get()->row_array();
                $num1 = preg_replace('/[^0-9]/', '', $leadsCount['total_count']) + $this->count;
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

        $prefix_loan_no = "POS";

        $envSet = ENVIRONMENT;

        if ($envSet == "production") {
            $prefix_loan_no = "POS";
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

                $referenceID = "#POS";

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
            $select = 'CE.city_id,CE.state_id,CE.customer_id, CE.employer_name, CE.emp_state, CE.emp_city, CE.emp_district, CE.emp_pincode, CE.emp_house, CE.emp_street, CE.emp_landmark, CE.emp_residence_since, CE.presentServiceTenure, CE.emp_designation, CE.emp_department,CE.emp_work_mode,CE.emp_occupation_id, CE.emp_employer_type, CE.emp_website, CE.monthly_income, CE.salary_mode, CE.income_type, CE.industry, CE.sector, CE.salary_mode, CE.emp_status, CE.created_on, ST.m_state_name, CT.m_city_name, department.department_name, MO.m_occupation_name,MO.m_occupation_id';

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
        // print_r($result); die;
        return $result;
    }

    // public function getDepartmentMaster() {
    //     return $this->db->select($select)->where(['department_active' => 1, 'department_deleted' => 0])->from('master_department')->get()->result();
    // }

    public function getDepartmentMaster() {
        $select = '*'; // or specify fields like 'department_id, department_name' if needed
        return $this->db
                ->select($select)
                ->from('master_department')
                ->where('department_active', 1)
                ->where('department_deleted', 0)
                ->get()
                ->result();
    }


    public function getEmpOccupation() {
        return $this->db->select('m_occupation_id, m_occupation_name')
            ->where(['m_occupation_active' => 1, 'm_occupation_deleted' => 0])
            ->from('master_occupation')
            ->get()->result();
    }

    public function getCompanyMaster() {
        return $this->db->select('m_company_type_id, m_company_type_name')
            ->where(['m_company_type_active' => 1, 'm_company_type_deleted' => 0])
            ->from('master_company_type')
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
                    'docs' => $row->docs,
                ];
                $input = json_encode($input);

                if ($row->payment_verification == 0 && agent == "AC1") {
                    $editBtn = "&nbsp;<a class='btn btn-control btn-primary' data-toggle='collapse' data-target='#addRecoveryPayment' onclick='editsCoustomerPayment(" . $input . ")'><i class='fa fa-pencil'></i></a>";
                }

                if (!in_array($row->payment_mode_id, array(2)) && $row->payment_verification == 0 && ((agent == "CR2" && $row->collection_executive_user_id == user_id) || (agent == "CO1" && $row->collection_executive_user_id == user_id) || (agent == "CO4" && $row->collection_executive_user_id == user_id) || (agent == "AC1" && $row->collection_executive_user_id == user_id))) {
                    $deleteBtn = '&nbsp;<a type="button" class="btn btn-control btn-danger" onclick="deleteCoustomerPayment(' . $row->id . ', ' . user_id . ')"><i class="fa fa-trash"></i></a>';
                }

                if (!in_array($row->payment_mode_id, array(2)) || !empty($row->docs)) {
                    $documentViewBtn = '<a class="btn btn-control btn-danger" target="_blank" href="' . base_url('direct-document-file/' . $row->docs) . '" title="' . $row->id . '"><i class="fa fa-eye"></i></a>';
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



        // if (in_array($lead_data_source_id, array(21, 27, 33)) || in_array($mobile, array(8282824633, 9717882592))) { //for C4C, REFCASE case which are in pending list for disbursement in same day.
        //     unset($in_process_status_array['DISBURSE-PENDING']);
        //     unset($in_process_status_array['DISBURSED']);
        //     unset($in_process_status_array['SANCTION']);
        //     unset($in_process_status_array['SETTLED']); // activated again on 2022-11-10
        //     unset($in_process_status_array['WRITEOFF']); // activated again on 2022-11-10
        // }

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

        $select = 'LD.lead_id, LD.user_type, LD.lead_data_source_id, LD.customer_id, LD.application_no, C.first_name, C.middle_name, C.sur_name, C.email, C.alternate_email, LD.pancard, LD.cibil, LD.lead_final_disbursed_date, LD.lead_disbursal_assign_user_id,';
        $select .= ' C.father_name, C.gender, C.dob, C.mobile, C.alternate_mobile, LD.obligations, LD.purpose, LD.loan_amount, LD.status, LD.stage, LD.lead_status_id, DATE_FORMAT(LD.lead_disbursal_approve_datetime, "%d-%m-%Y %H:%i:%s"), LD.lead_disbursal_approve_datetime, LD.application_no, CAM.loan_recommended,  L.loan_disbursal_letter, ';
        $select .= ' CAM.roi,CAM.panel_roi, CAM.admin_fee, CAM.total_admin_fee, DATE_FORMAT(CAM.disbursal_date,"%d-%m-%Y") AS disbursal_date, DATE_FORMAT(LD.lead_final_disbursed_date,"%d-%m-%Y") AS lead_final_disbursed_date, DATE_FORMAT(CAM.repayment_date,"%d-%m-%Y") AS repayment_date, CAM.tenure, CAM.net_disbursal_amount, ';
        $select .= ' CAM.repayment_amount, CAM.net_disbursal_amount,L.loan_no, L.loanAgreementRequest, DATE_FORMAT(L.agrementRequestedDate, "%d-%m-%Y %H:%i:%s") AS agrementRequestedDate, L.loanAgreementResponse, ';
        $select .= ' CAM.eligible_foir_percentage, CAM.admin_fee as processing_fee_percent, CAM.user_id, CAM.cam_sanction_letter_esgin_file_name, CAM.cam_esgin_audit_trail_file_name, CAM.cam_sanction_letter_esgin_on, CAM.cam_sanction_letter_file_name, ';
        $select .= ' L.agrementUserIP, L.agrementResponseDate, L.status as loan_status, L.company_account_no, L.channel, LD.lead_final_disbursed_date, L.mode_of_payment, CE.presentServiceTenure, CE.monthly_income, CE.monthly_income as monthly_salary, ';
        $select .= ' L.disburse_refrence_no, CT.m_city_category as city_category, CT.m_city_code as city_code, ';
        $select .= ' user_screener.name as screened_by, DATE_FORMAT(LD.lead_screener_assign_datetime, "%d-%m-%Y %H:%i:%s") as lead_screener_assign_datetime, ';
        $select .= ' user_credit_manager.name as credit_by, DATE_FORMAT(LD.lead_credit_assign_datetime, "%d-%m-%Y %H:%i:%s") as lead_credit_assign_datetime, ';
        $select .= ' user_disbursed.name as disbursal_manager, DATE_FORMAT(LD.lead_disbursal_recommend_datetime, "%d-%m-%Y %H:%i:%s") as disbursal_recommend,';
        $select .= ' user_disbursed_head.name as disbursal_head, DATE_FORMAT(LD.lead_disbursal_approve_datetime, "%d-%m-%Y %H:%i:%s") as disbursal_approve, LD.lead_disbursal_approve_datetime,';
        $select .= ' user_credit_head.name as sanctioned_by, DATE_FORMAT(LD.lead_credit_approve_datetime, "%d-%m-%Y %H:%i:%s") as lead_credit_approve_datetime,';
        $select .= ' cam_sanction_letter_file_name, CAM.cam_risk_profile, CAM.cam_risk_score, CAM.cam_advance_interest_amount as cam_interest_amount, CAM.cam_appraised_monthly_income, ';
        $select .= ' C.current_residence_type, C.email_verified_status, C.alternate_email_verified_status';

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
            $logo_image = base_url('/public/images/final_logo.png');
        }

        // $annual_percent_rate = number_format(($camDetails->roi * 365) + ($camDetails->processing_fee_percent * 12), 2);
        // $annual_percent_rate = 453.75;
        $admin_fee = $camDetails->admin_fee; //Total Admin Fee with GST
        $total_interest = round(($camDetails->repayment_amount), 2) - round(($camDetails->loan_recommended), 2);
        $loan_recommended = $camDetails->loan_recommended; //Loan Amount Recommended
        $tenure = $camDetails->tenure; //Tenure in Days
        $annual_percent_rate = round(((($admin_fee + $total_interest) / $loan_recommended) * (365 / $tenure) * 100), 2); //Annual Percentage Rate


        $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns="http://www.w3.org/1999/xhtml">
                            <head>
                            <link href="https://allfont.net/allfont.css?fonts=courier" rel="stylesheet" type="text/css" />
                                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                <title>' . $subject . '</title>
                            </head>
                            <body style="font-family: Courier, arial;">

                                <table width="778" border="0" align="center" cellpadding="0" cellspacing="0" style="font-family: Courier, arial;padding:10px; border:solid 1px #ccc; font-family:Arial, Helvetica, sans-serif; font-size:15px;">
                                    <tr>
                                        <td width="404" align="left"><img src="' . EMAIL_BRAND_LOGO . '" alt="logo" style=" border-radius: 5px;width: 180px;"/></td>
                                        <td width="4" align="left">&nbsp;</td>
                                        <td width="368" align="right"><table width="100%" border="0">
                                                <tr>
                                                    <td align="right" style="font-family: Courier, arial;"><strong>Dear ' . $fullname . '</strong></td>
                                                </tr>
                                                <tr>
                                                    <td align="right" style="font-family: Courier, arial;"><table cellspacing="0" cellpadding="0">
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
                                                        <p style="line-height:22px;font-family: Courier, arial; margin:0px 0px 10px 0px;">Thank You for choosing ' . BRAND_NAME . ' and giving us the opportunity to serve you.</p>
                                                        <p style="line-height:22px; font-family: Courier, arial; margin:0px 0px 10px 0px;">We are hoping that you are satisfied with our prompt responses as a part of process.</p>
                                                        <p style="line-height:22px;font-family: Courier, arial; margin:0px 0px 10px 0px;">We have received all your details and your submitted loan application has been disbursed.</p>
                                                        <p style="line-height:22px;font-family: Courier, arial; margin:0px 0px 10px 0px;">We request you to go through loan terms and repayment schedule.</p>
                                                        <p style="line-height:22px; font-family: Courier, arial;margin:0px 0px 0px 0px;">Henceforth visiting (physically) your workplace and residence has your concurrence on it.</p>
                                                    </td>
                                                    <td valign="top"><img src="' . DISBURSAL_LETTER_BANNER . '" width="322" height="272" style="padding:10px; border:solid 1px #ddd;"></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            <p style="line-height:25px; font-family: Courier, arial;">
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
                                                        <td width="42%" bgcolor="#FFFFFF" font-family: Courier, arial;style="padding:10px;">Name</td>
                                                        <td width="58%" bgcolor="#FFFFFF" font-family: Courier, arial;style="padding:10px;">' . $fullname . '</td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;font-family: Courier, arial;">Loan Amount (Rs.)</td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;font-family: Courier, arial;">' . number_format($camDetails->loan_recommended, 2) . ' /- </td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;font-family: Courier, arial;">Rate of Interest (%)</td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;font-family: Courier, arial;">' . number_format($camDetails->roi, 2) . ' per day</td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;font-family: Courier, arial;">Disbursal Date</td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;font-family: Courier, arial;">' . $camDetails->disbursal_date . ' </td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;font-family: Courier, arial;">Commitment Payback Date</td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;font-family: Courier, arial;">' . $camDetails->repayment_date . '</td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;font-family: Courier, arial;">Repayment Amount (Rs.)</td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;font-family: Courier, arial;">' . number_format($camDetails->repayment_amount, 2) . ' </td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;font-family: Courier, arial;">Period (Days)</td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;font-family: Courier, arial;">' . $camDetails->tenure . ' </td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;font-family: Courier, arial;">Penalty (%)</td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;font-family: Courier, arial;">' . number_format(round(($camDetails->roi * 2), 2), 2) . '</td>
                                                    </tr>
                                                    <tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">Processing Fee (Rs.)</td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">' . number_format($camDetails->admin_fee, 2) . '/- (Including 18% GST)</td>
                                                    </tr>
                                                    <!--<tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;font-family: Courier, arial;">Repayment Cheque(s)</td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;font-family: Courier, arial;">&nbsp;-</td>
                                                    </tr>-->
                                                    <!--<tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;font-family: Courier, arial;">Cheque drawn on (Bank Name) </td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">&nbsp;-</td>
                                                    </tr>-->
                                                    <!--<tr>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;font-family: Courier, arial;">Cheque &amp; NACH Bouncing Charges </td>
                                                        <td bgcolor="#FFFFFF" style="padding:10px;">Rs. 1000.00/- every time</td>
                                                    </tr>-->
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="font-size:17px; font-family: Courier, arial;line-height: 25px; padding-bottom: 6px;">
                                            <!-- Note* - Annual ROI : ' . (number_format($camDetails->roi * 365, 2)) . '% -->
                                            Note* - Annual ROI : ' . $annual_percent_rate . '%
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="font-size:17px;line-height: 25px;padding-bottom: 6px;">
                                            <p style="line-height:22px;font-family: Courier, arial; margin:0px 0px 0px 0px;">Non-payment of loan on time will affect your credit score and your chance of getting further loans.</p>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-bottom:10px; font-family: Courier, arial;padding-top:10px;"><strong>Best Regards,  </strong></td>
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


        $file_name = "disbursal_letter_" . $lead_id . "_" . date('Ymd') . ".pdf";
        if (LMS_DOC_S3_FLAG == true) {
            $file_path_with_name = TEMP_UPLOAD_PATH . $file_name;
        } else {
            $file_path_with_name = UPLOAD_PATH . $file_name;
        }
        // $file_path_with_name = UPLOAD_DISBURSAL_PATH . $file_name;
        // $file_url_path = LMS_URL . $file_name;

        require_once __DIR__ . '/../../vendor/autoload.php';
        $mpdf = new \Mpdf\Mpdf();

        $mpdf->WriteHTML($message);
        $mpdf->Output($file_path_with_name, 'F');

//         require_once APPPATH . 'libraries/Pdf.php';

// $pdf = new Pdf();
// $pdf->AddPage();

// $pdf->SetFont('helvetica', '', 12);

// $pdf->writeHTML($message, true, false, true, false, '');

// ob_clean(); // Clear any previous output
// $pdf->Output($file_path_with_name, 'F');

        if (file_exists($file_path_with_name)) {
            require_once(COMPONENT_PATH . 'includes/functions.inc.php');
            $return_array = common_send_email($email, $subject, $message, "", "", "", "", $file_path_with_name, $file_disburse_name, 'disbursal_letter.pdf',FALSE);

            // require_once(COMPONENT_PATH . "CommonComponent.php");
            // $CommonComponent = new CommonComponent();
            // $request_array = array();
            // $request_array['flag'] = 1;
            // $request_array['file'] = base64_encode(file_get_contents($file_path_with_name));
            // $request_array['ext'] = pathinfo($file_path_with_name, PATHINFO_EXTENSION);

            // $upload_return = $CommonComponent->upload_document($lead_id, $request_array);

            if (LMS_DOC_S3_FLAG == true) {
                $upload_return = uploadDocument($file_path_with_name, $lead_id, 2, 'pdf');
                $file_name = $upload_return['file_name'];
                unlink($file_path_with_name);
            }

            $return_array['status'] = 1;
            $return_array['msg'] = "Disbursal Letter Send Successfully";

            $update_disbursal_letter = ['loan_disbursal_letter' => $file_name];
            $this->db->where('lead_id', $lead_id)->update('loan', $update_disbursal_letter);
            // print_r($update_disbursal_letter); die;

            $update_disbursal_time = ['lead_disbursal_approve_datetime' => date('Y-m-d H:i:s')];
            $this->db->where('lead_id', $lead_id)->update('leads', $update_disbursal_time);

            $insertApiLog = array(
                'created_on ' => date('Y-m-d H:i:s'),
                'status' => $leadstatus,
                'stage' => $leadStage,
                'user_id' => $_SESSION['isUserSession']['user_id'],
                'lead_id' => $lead_id,
                'lead_followup_status_id' => $lead_status_id,
                'reason' => "Disbursal Letter Send successfully"
            );
            $this->db->insert('lead_followup', $insertApiLog);
            $this->db->insert_id();
            echo json_encode($return_array);
            die;
        }



        // require_once(COMPONENT_PATH . 'includes/functions.inc.php');
        // $return_array = common_send_email($email,  $subject, $message, "","","","","", $file_name,'disbursal_letter.pdf');
        // return $return_array;
    }

    public function sendSanctionMail($lead_id) {

        //  ini_set('display_errors', 1);
        // ini_set('display_startup_errors', 1);
        // error_reporting(E_ALL);


        $sql = $this->getCAMDetails($lead_id);
        $camDetails = $sql->row();

        $sql1 = $this->getResidenceDetails($lead_id);
        $getResidenceDetails = $sql1->row();

        $enc_lead_id = $this->encrypt->encode($lead_id);

        $lead_data_source_id = $camDetails->lead_data_source_id;


        $email = $camDetails->email;
        // print_r($email); die;
        $alternate_email = $camDetails->alternate_email;


        //$bcc_email = BCC_SANCTION_EMAIL;
        $bcc_email = '';

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
        // print_r($camDetails); die;

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
        // $link_value = base_url('sanction-esign-request') . "?lead_id=$lead_id";
        $link_value = base_url('sanction-esign-consent') . "?refstr=$enc_lead_id";
        $acceptance_button_link = '<br/><br/><center><a style="text-align:center;outline : none;color: #fff; background: #00455E; border-bottom: none !important; padding: 12px 9px !important;" href="' . $link_value . '">eSign Sanction Letter</a></center><br/><br/>';
        $acceptance_button_link .= "If you are not able to click on the eSign button then please copy and paste this url in browser to proceed or click here .<br/><a href='" . $link_value . "'>" . $link_value . "</a>";

        if (in_array($lead_data_source_id, array(21, 27))) {
            // $link_value = base_url('loanAgreementLetterResponse') . "?lead_id=$lead_id";
            $link_value = base_url('loanAgreementLetterResponse') . "?refstr=$enc_lead_id";
            $acceptance_button_link = '<br/><br/><center><a style="text-align:center;outline : none;color: #fff; background: #e52255; border-bottom: none !important; padding: 12px 9px !important;" href="' . $link_value . '">Accept Sanction Letter</a></center><br/><br/>';
            $acceptance_button_link = "If you are not able to click on the accept button then please copy and paste this url in browser to proceed or click here .<br/><a href='" . $link_value . "'>" . $link_value . "</a>";
        }

        $total_interest = round(($camDetails->repayment_amount), 2) - round(($camDetails->loan_recommended), 2);

        $net_disbursal1 =   round(($camDetails->loan_recommended * 10 / 100), 2);
        $gst_disbursal =    (round($camDetails->admin_fee, 2));
        // $net_disbursal2 =   ($net_disbursal1 + $gst_disbursal);
        $final_disbursal =  (round($camDetails->loan_recommended, 2) - $gst_disbursal);

        // $annual_percent_rate = number_format(($camDetails->roi * 365) + ($camDetails->processing_fee_percent * 12), 2);
        // $annual_percent_rate = 453.75;
        $admin_fee = $camDetails->admin_fee; //Total Admin Fee with GST
        $loan_recommended = round($camDetails->loan_recommended, 2);
        $tenure = $camDetails->tenure;
        $total_interest = round(($camDetails->repayment_amount), 2) - round(($camDetails->loan_recommended), 2);
        
        $annual_percent_rate = round(((($admin_fee + $total_interest) / $loan_recommended) * (365 / $tenure) * 100), 2); //Annual Percentage Rate


        // $message = 'Sanction Letter Send Successfully';

        $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                           <html lang="en">
                        <head>
                            <meta charset="UTF-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <title>' . $subject . '</title>
                            <style>
                                table {
                                    width: 100%;
                                    border-collapse: collapse;
                                }
                                th, td {
                                    padding: 8px;
                                    text-align: left;
                                    border-bottom: 1px solid #ddd;
                                }
                                th, td, .first-table td {
                                    border: 1px solid #ddd;
                                }
                                th {
                                    background-color: #f2f2f2;
                                }
                                body {
                                    font-family: Arial, sans-serif;
                                    line-height: 1.6;
                                }
                                .container {
                                    max-width: 800px;
                                    margin: 0 auto;
                                    padding: 20px;
                                }
                                .letterhead {
                                    text-align: center;
                                    margin-bottom: 20px;
                                }
                                .letterhead h1 {
                                    margin: 0;
                                }
                                .address {
                                    margin-bottom: 20px;
                                }
                                .details {
                                    margin-bottom: 20px;
                                }
                                .footer {
                                    margin-top: 20px;
                                    text-align: center;
                                }
                                .first-table {
                                    border: 1px solid #ddd;
                                    margin-bottom: 20px;
                                }
                            </style>
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
                                <p style="color:#font-size: 18px; color: #00455E; font-size:18px;">
                                    <img
                                    src=" ' . SANCTION_LETTER_NEW_HEADER . ' "
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
                                <span style="color:#font-size: 18px; color: #00455E; font-size:18px;">Date : ' . $sanction_date . '</span>
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
                                <strong>' . BRAND_NAME . ', a brand name under ' . COMPANY_NAME . ' (RBI approved NBFC – Reg No. ' . RBI_LICENCE_NUMBER . ') </strong>
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
                                        <strong>Total interest charge during the entire Tenure of the loan </strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        ' . $total_interest  . '
                                    </td>
                                    </tr>

                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Net Disbursed Amount</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        ' . $final_disbursal  . '
                                    </td>
                                    </tr>

                                    <tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Annual Percentage Rate - Effective annualized interest rate (in %) (Considering the ROI of ' . $camDetails->roi . '% per day) </strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <!--<td align="left" valign="middle" bgcolor="#FFFFFF">
                                       ' . round(($camDetails->roi * 365), 2) . '
                                    </td>-->
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                       ' . $annual_percent_rate . '
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

                                    <!--<tr>
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
                                    </tr>-->
                                    <!--<tr>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        <strong>Annualised ROI (%)</strong>
                                    </td>
                                    <td align="center" valign="middle" bgcolor="#FFFFFF">
                                        <strong>:</strong>
                                    </td>
                                    <td align="left" valign="middle" bgcolor="#FFFFFF">
                                        ' . round(($camDetails->roi * 365), 2) . '
                                    </td>
                                    </tr>-->
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
                                and provide your kind acceptance using Aadhaar E-Sign so that we can process
                                your loan for final disbursement.
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                <strong style="color: #00455E">Best Regards</strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                <strong style="color: #00455E">Team ' . BRAND_NAME . '</strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                <strong style="color: #00455E">(Brand Name for ' . COMPANY_NAME . ')</strong>
                                </td>
                            </tr>';
                            $message .= '<tr>
                                <td>' . $acceptance_button . '</td>
                            </tr>
                            <tr>
                                <td>' . $acceptance_button_link . '</td>
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
                    <td colspan = "2">You can Prepay/Repay the loan amount using our link <a href = "' . PORTAL_URL . PORTAL_REDIRECTION_URL['REPAY_LOAN'] . '" target = "_blank" style = "color:#0363a3; text-decoration:blink;"> REPAY LOAN</a></td>
                    </tr>
                    <tr>
                    <td colspan = "2"><img src = ' . SANCTION_LETTER_NEW_FOOTER . ' alt = "Sanctionletter-footer.jpg" width = "760" height = "104"></td>
                    </tr>';

                            $message .= '</table>

                            <map name="Map" id="Map">
                            <area shape="rect"
                                coords="574,21,750,110"
                                href="' . WEBSITE_URL . '"
                                target="_blank"/>
                            </map>
                        </body>
                        </html>';


        require_once(COMPONENT_PATH . 'includes/functions.inc.php');

        // $directSanctionLetter = "https://tejasloan.com/direct-document-file/" . $cam_sanction_letter_file_name;
        $directSanctionLetter = (getenv("WEBSITE_URL") ?? 'https://cms.paisaonsalary.in/')."direct-document-file/" . $cam_sanction_letter_file_name;
        $return_array = common_send_email($email,  $subject, $message, "", "", "", "", $directSanctionLetter, 'sanction_letter.pdf', "");
        


        if (!empty($alternate_email)) {
            $return_array = common_send_email($alternate_email,  $subject, $message, "", "", "", "", $directSanctionLetter, 'sanction_letter.pdf', "");
        }
        // print_r($return_array);
        // exit;

        // $this->sent_sacntion_esign_sms($lead_id, $esign_short_url);

        return $return_array;
    }

    public function sent_sacntion_esign_sms($lead_id, $sort_url) {

        //$req['esign_link'] = base_url('sanction-esign-request') . "?refstr=$enc_lead_id";
        $req['esign_link'] = "https://esign.nsdl.com";
        //$req['mobile']=8282824633;
        require_once(COMPONENT_PATH . 'CommonComponent.php');

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

    public function generateSanctionLetter($lead_id) {

        $return_array = array("status" => 0, "errors" => "");

        try {

            $CONTACT_PERSON = CONTACT_PERSON;
            $REGISTED_MOBILE = REGISTED_MOBILE;
            //$REGISTED_ADDRESS = REGISTED_ADDRESS;

            $sql = $this->getCAMDetails($lead_id);
            $camDetails = $sql->row();
            // print_r($camDetails);
            // exit;
            
            $sql1 = $this->getResidenceDetails($lead_id);
            $getResidenceDetails = $sql1->row();

            $conditions = "LD.lead_id = ".$lead_id;
            $sql2 = $this->getLeadDetails($conditions);
            $leadDetails = $sql2->row();
            // print_r($leadDetails);
            // exit;
            
            $sql3 = $this->getEmploymentDetails($lead_id);
            $employmentDetails = $sql3->row();

            $sql4 = "SELECT * FROM loan WHERE lead_id = $lead_id";
            $sqlquery4 = $this->db->query($sql4);
            $loanDetails = $sqlquery4->row();
            // print_r($loanDetails);
            // exit;

            $sql5 = "SELECT * FROM customer_banking WHERE lead_id = $lead_id";
            $sqlquery5 = $this->db->query($sql5);
            $bankDetails = $sqlquery5->row();

            $loan_no = $this->generateLoanNo($lead_id);

            // $annual_percent_rate = number_format(($camDetails->roi * 365) + ($camDetails->processing_fee_percent * 12), 2);
            // $annual_percent_rate = 453.75;

            $subject = 'Loan Sanction Letter - ' . BRAND_NAME;

            $mobile = $camDetails->mobile;
            $loan_recommended = $camDetails->loan_recommended;
            $application_no = $camDetails->application_no;
            $repayment_amount = $camDetails->repayment_amount;
            $tenure = $camDetails->tenure;
            $repayment_date = $camDetails->repayment_date;
            $net_disbursal_amount = $camDetails->net_disbursal_amount;

            $total_interest = round(($camDetails->repayment_amount), 2) - round(($camDetails->loan_recommended), 2);

            $net_disbursal1 =   round(($camDetails->loan_recommended * 10 / 100), 2);
            $gst_disbursal =    (round($camDetails->admin_fee, 2));
            // $net_disbursal2 =   ($net_disbursal1 + $gst_disbursal);
            $final_disbursal =  (round($camDetails->loan_recommended, 2) - $gst_disbursal);

            $roi = $camDetails->roi;
            
            //            $processing_fee_percent = $camDetails->processing_fee_percent;
            $admin_fee = $camDetails->admin_fee; //Total Admin Fee with GST

            $annual_percent_rate = round(((($admin_fee + $total_interest) / $loan_recommended) * (365 / $tenure) * 100), 2); //Annual Percentage Rate

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

            // $html_string = '<!DOCTYPE html>
            //         <html lang="en">
            //         <head>
            //             <meta charset="UTF-8">
            //             <meta name="viewport" content="width=device-width, initial-scale=1.0">
            //             <title>' . $subject . '</title>
            //             <style>
            //                 table {
            //                     width: 100%;
            //                     border-collapse: collapse;
            //                 }
            //                 th, td {
            //                     padding: 8px;
            //                     text-align: left;
            //                     border-bottom: 1px solid #ddd;
            //                 }
            //                 th, td, .first-table td {
            //                     border: 1px solid #ddd;
            //                 }
            //                 th {
            //                     background-color: #f2f2f2;
            //                 }
            //                 body {
            //                     font-family: Arial, sans-serif;
            //                     line-height: 1.6;
            //                 }
            //                 .container {
            //                     max-width: 800px;
            //                     margin: 0 auto;
            //                     padding: 20px;
            //                     text-align: justify;
            //                     font-size: 15px;
            //                 }
            //                 .letterhead {
            //                     text-align: center;
            //                     margin-bottom: 20px;
            //                 }
            //                 .letterhead h1 {
            //                     margin: 0;
            //                 }
            //                 .address {
            //                     margin-bottom: 20px;
            //                 }
            //                 .details {
            //                     margin-bottom: 20px;
            //                 }
            //                 .footer {
            //                     margin-top: 20px;
            //                     text-align: center;
            //                 }
            //                 .first-table {
            //                     border: 1px solid #ddd;
            //                     margin-bottom: 20px;
            //                 }
            //                 .loan-agreement{
            //                     font-size: 18px;
            //                 }
            //             </style>
            //         </head>
            //         <body>
            //             <div class="container">
            //                 <h2 style="color: skyblue; text-align: center;">Key Fact Statement</h2>
            //                 <table>
            //                     <tr>
            //                         <th>S.No.</th>
            //                         <th>Parameters</th>
            //                         <th>Details</th>
            //                     </tr>
            //                     <tr>
            //                         <td>I</td>
            //                         <td>Name</td>
            //                         <td>' . $fullname . '</td>
            //                     </tr>
            //                     <tr>
            //                         <td>II</td>
            //                         <td>Loan Amount</td>
            //                         <td>RS. ' . number_format(round($camDetails->loan_recommended, 0), 2) . '/-</td>
            //                     </tr>
            //                     <tr>
            //                         <td>III</td>
            //                         <td>ROI (in % per day)</td>
            //                         <td>' . number_format($camDetails->roi, 2) . '</td>
            //                     </tr>
            //                     <tr>
            //                         <td>IV</td>
            //                         <td>Total interest charge during the entire Tenure of the loan</td>
            //                         <td>RS. ' . $total_interest . '/-</td>
            //                     </tr>
            //                     <tr>
            //                         <td>V</td>
            //                         <td>Processing Fee (Including 18% GST)</td>
            //                         <td>RS. ' . number_format(round($camDetails->admin_fee, 0), 2) . '/-</td>
            //                     </tr>
            //                     <tr>
            //                         <td>VI</td>
            //                         <td>Insurance charges, if any (in RS.)</td>
            //                         <td>Nil</td>
            //                     </tr>
            //                     <tr>
            //                         <td>VII</td>
            //                         <td>Others (if any) (in RS.)</td>
            //                         <td>Nil</td>
            //                     </tr>
            //                     <tr>
            //                         <td>VIII</td>
            //                         <td>Net disbursed amount</td>
            //                         <td>RS. ' . $final_disbursal . '</td>
            //                     </tr>
            //                     <tr>
            //                         <td>IX</td>
            //                         <td>Total Repayment Amount</td>
            //                         <td>RS. ' . number_format(round($camDetails->repayment_amount, 0), 2) . '/-</td>
            //                     </tr>
            //                     <tr>
            //                         <td>X</td>
            //                         <td>Annual Percentage Rate - Effective annualized interest rate (in %) (Considering the ROI of ' . $roi . '% per day)</td>
            //                         <td>  ' . round(($camDetails->roi * 365), 2) . ' </td>
            //                     </tr>
            //                     <tr>
            //                         <td>XI</td>
            //                         <td>Tenure of the Loan (days)</td>
            //                         <td>' . $camDetails->tenure . '</td>
            //                     </tr>
            //                     <tr>
            //                         <td>XII</td>
            //                         <td>Repayment frequency by the borrowert</td>
            //                         <td>One Time Only</td>
            //                     </tr>
            //                     <tr>
            //                         <td>XIII</td>
            //                         <td>Number of installments of repayment</td>
            //                         <td>One</td>
            //                     </tr>
            //                     <tr>
            //                         <td>XIV</td>
            //                         <td>Amount of each installment of repayment (in RS.)</td>
            //                         <td>RS. ' . number_format(round($camDetails->repayment_amount, 0), 2) . '/-</td>
            //                     </tr>
            //                     <tr>
            //                         <td colspan="3"><strong>Details about Contingent Charges</strong></td>
            //                     </tr>

            //                         <tr>
            //                             <td>XV</td>
            //                             <td>Rate of annualized penal charges in case of delayed payments (if any)</td>
            //                             <td>Double the (III)</td>
            //                         </tr>
            //                         <tr>
            //                             <td colspan="3"><strong>Other Disclosures</strong></td>
            //                         </tr>

            //                         <tr>
            //                             <td>XVI</td>
            //                             <td>Cooling off/look-up period during which borrower shall not be charged any penalty on prepayment of loan</td>
            //                             <td>3 Days</td>
            //                         </tr>
            //                         <tr>
            //                             <td>XVII</td>
            //                             <td>Name, designation, Address and phone number of nodal grievance redressal officer designated specifically to deal with FinTech/ digital lending related complaints/ issues</td>
            //                             <td>' . CONTACT_PERSON .' <br>Mobile: ' . REGISTED_MOBILE . '<br>Address: ' . REGISTED_ADDRESS . '</td>
            //                         </tr>
            //                     </table>
            //                     <div class="address">
            //                         <p><strong>' . COMPANY_NAME . '</strong><br>
            //                         ' . REGISTED_ADDRESS . '<br>
            //                         ' . REGISTED_MOBILE . ',<br>
            //                         info@paisaonsalary.in,<br>
            //                         <a href="https://tejasloan.com">https://www.tejasloan.com</a><br>
            //                         Date : ' . $sanction_date . ' </p>
            //                     </div>
            //                 <div class="customer">
            //                     <p><strong>Customer Details</strong><br>
            //                     Name of Customer  : ' . $fullname . '<br>
            //                     Address of Customer  : ' . $residence_address . '<br></p>
            //                 </div>
            //                 <div class="details">
            //                     <h2>Subject: Sanction Letter for Loan Approval</h2>
            //                     <p>Dear ' . $fullname . ',</p>
            //                     <p>We are pleased to inform you that your application for a loan with Aman Fincap Limited has been successfully approved. We understand the importance of your financial needs and are committed to providing you with the necessary assistance to meet them.</p>
            //                     <h3>Loan Details:</h3>
            //                     <ul>
            //                         <li>Loan Amount:' . number_format(round($camDetails->loan_recommended, 0), 2) . '</li>
            //                         <li>Loan Term: ' . $camDetails->tenure . '</li>
            //                         <li>Interest Rate: ' . number_format($camDetails->roi, 2) . '</li>
            //                         <li>Repayment Amount: ' . number_format(round($camDetails->repayment_amount, 0), 2) . '/-</li>
            //                     </ul>
            //                     <p>Your loan has been sanctioned with the above-mentioned terms and conditions. The loan amount will be disbursed directly to your designated bank account within ' . $sanction_date . ', subject to the completion of any remaining formalities.</p>
            //                     <p>Please carefully review the loan agreement, including the terms, conditions, and repayment schedule. Should you have any questions or require clarification regarding the loan terms, feel free to contact our customer service team at +91-88000 02890 or email us at info@paisaonsalary.in .</p>
            //                     <p>Kindly ensure that you adhere to the repayment schedule to avoid any unnecessary penalties or charges. Timely repayment will also help you maintain a positive credit history with our institution.</p>
            //                     <p>We appreciate your trust in Aman Fincap Limited, and we assure you of our dedicated support in meeting your financial requirements.</p>
            //                     <p>Thank you for choosing Aman Fincap Limited. We look forward to a mutually beneficial relationship.</p>
            //                     <p>Best regards,</p>
            //                     <p>Aman Fincap Limited</p>
            //                 </div>
            //                 <div class="loan-agreement">
            //                     <h2 >Loan Agreement</h2>
            //                     <p>This Loan Agreement is entered into on ' . $sanction_date . ', between:</p>
            //                     <p>Aman Fincap Limited, a company duly registered under the laws of India, having its registered office at '. REGISTED_ADDRESS .' (hereinafter referred to as the "Lender"), on one part and</p>
            //                     <p> ' . $fullname . ', an individual/legal entity, residing at address ' . $residence_address . '(hereinafter referred to as the "Borrower"), on the other part.</p>
            //                     <h3>Background:</h3>
            //                     <p>The Borrower has approached the Lender for a loan to meet its financial requirements, and the Lender has agreed to provide the loan on the terms and conditions set forth in this Agreement.</p>
            //                 </div>
            //                 <div class="loan-details">
            //                     <table>
            //                         <tr>
            //                             <td>Customer Name:</td>
            //                             <td>' . $fullname . '</td>
            //                         </tr>
            //                         <tr>
            //                             <td>Sanctioned Loan Amount (Rs.):</td>
            //                             <td>' . number_format(round($camDetails->loan_recommended, 0), 2) . '/-</td>
            //                         </tr>
            //                         <tr>
            //                             <td>Rate of Interest (%) per day:</td>
            //                             <td>' . number_format($camDetails->roi, 2) . '</td>
            //                         </tr>
            //                         <tr>
            //                             <td>Date of Sanction:</td>
            //                             <td>' . $sanction_date . '</td>
            //                         </tr>
            //                         <tr>
            //                             <td>Total Repayment Amount (Rs.):</td>
            //                             <td>' . number_format(round($camDetails->repayment_amount, 0), 2) . '/-</td>
            //                         </tr>
            //                         <tr>
            //                             <td>Tenure in Days:</td>
            //                             <td>' . $camDetails->tenure . '</td>
            //                         </tr>
            //                         <tr>
            //                             <td>Repayment Date:</td>
            //                             <td>' . $camDetails->repayment_date . '</td>
            //                         </tr>
            //                         <tr>
            //                             <td>Penal Interest (%) per day:</td>
            //                             <td>' . round(($camDetails->roi * 2), 2) . '</td>
            //                         </tr>
            //                         <tr>
            //                             <td>Processing Fee (Rs.):</td>
            //                             <td>' . number_format(round($camDetails->admin_fee, 0), 2) . '/- (Including 18% GST)</td>
            //                         </tr>
            //                         <tr>
            //                             <td>Repayment Cheque(s):</td>
            //                             <td>-</td>
            //                         </tr>
            //                         <tr>
            //                             <td>Cheque drawn on (name of the Bank):</td>
            //                             <td>-</td>
            //                         </tr>
            //                         <tr>
            //                             <td>Cheque and NACH Bouncing Charges (Rs.):</td>
            //                             <td>1,000.00/- per bouncing/dishonour.</td>
            //                         </tr>
            //                         <tr>
            //                             <td>Annualised ROI (%):</td>
            //                             <td>' . round(($camDetails->roi * 365), 2) . '</td>
            //                         </tr>
            //                     </table>
            //                 </div>
            //                 <div class="loan-terms">
            //                     <p>Kindly Note:</p>
            //                     <p>Non-payment of loan on time will adversely affect your Credit score, further reducing your chances of getting Re loan again. Upon approval, the processing fee will be deducted from your Sanction amount and the balance amount will be disbursed to your account.</p>
            //                     <p>This Sanction letter is valid for 24 Hours only. You can Prepay/Repay the loan amount using our link <br> <a href="https://tejasloan.com/repay-now" target="_blank"style="color: #4447fd; text-decoration: blink">Payment Link</a>
            //                     </td>.</p>

            //                     <h2>Agreed Terms and Conditions:</h2>
            //                     <ol>
            //                         <li><strong>Loan Details:</strong>
            //                             <ul>
            //                                 <li>Loan Amount:' . number_format(round($camDetails->loan_recommended, 0), 2) . '</li>
            //                                 <li>Loan Term: ' . $camDetails->tenure . '</li>
            //                                 <li>Interest Rate: ' . number_format($camDetails->roi, 2) . '</li>
            //                                 <li>Repayment Amount: ' . number_format(round($camDetails->repayment_amount, 0), 2) . '/-</li>
            //                             </ul>
            //                         </li>
            //                         <li><strong>Disbursement:</strong>
            //                             <ul>
            //                                 <li>The Lender shall disburse the loan amount to the Borrower of designated bank account within ' . $sanction_date . ' from the execution of this Agreement, subject to the completion of all necessary documentation and formalities.</li>
            //                             </ul>
            //                         </li>
            //                         <li><strong>Repayment:</strong>
            //                             <ul>
            //                                 <li>The Borrower agrees to repay the loan amount along with accrued interest as per the agreed repayment schedule outlined in Schedule A attached hereto.</li>
            //                                 <li>The Borrower shall make repayments on or before the due dates specified in the repayment schedule.</li>
            //                             </ul>
            //                         </li>
            //                         <li><strong>Prepayment:</strong>
            //                             <ul>
            //                                 <li>The Borrower reserves the right to prepay the loan, in part or in full, at any time without incurring any prepayment penalties or charges.</li>
            //                             </ul>
            //                         </li>
            //                         <li><strong>Default:</strong>
            //                             <ul>
            //                                 <li>In the event of default in repayment, the Borrower shall be liable to pay default interest at the rate specified in Schedule A.</li>
            //                                 <li>The Lender reserves the right to take legal action or pursue any other remedies available under law in case of default by the Borrower.</li>
            //                             </ul>
            //                         </li>
            //                         <li><strong>Representations and Warranties:</strong>
            //                             <ul>
            //                                 <li>The Borrower represents and warrants that all information provided to the Lender in connection with this Agreement is true, accurate, and complete.</li>
            //                                 <li>The Borrower undertakes to notify the Lender immediately of any material changes in the information provided.</li>
            //                             </ul>
            //                         </li>
            //                         <li><strong>Governing Law and Jurisdiction:</strong>
            //                             <ul>
            //                                 <li>This Agreement shall be governed by and construed in accordance with the laws of [Delhi].</li>
            //                                 <li>Any disputes arising out of or in connection with this Agreement shall be subject to the exclusive jurisdiction of the courts of [Jurisdiction].</li>
            //                             </ul>
            //                         </li>
            //                     </ol>

            //                     <p><strong>IN WITNESS WHEREOF</strong>, the parties hereto have executed this Agreement on the date first above written.</p>
            //                     <p><strong>For Aman Fincap Limited:</strong></p><br>
            //                     <p><strong>For the Borrower:</strong></p>
            //                     <p>' . $fullname . '<br>' . $sanction_date . '</p>

            //                     <p><strong>Schedule A: Repayment Schedule</strong></p>
            //                     <p>' . $sanction_date . '</p>

            //                     <p>Please ensure that all terms and conditions are thoroughly reviewed and understood by both parties before signing the agreement. It is also advisable to consult with legal professionals to ensure compliance with applicable laws and regulations.</p>
            //                 </div>

            //             </div>
            //         </body>
            //         </html>';

            // $letterhead_url = SANCTION_LETTER_HEADER;
            // $letterfooter_url = SANCTION_LETTER_FOOTER;
            $letterhead_url = SANCTION_LETTER_NEW_HEADER;
            $letterfooter_url = SANCTION_LETTER_NEW_FOOTER;

            $html_string_new = "<html>
   <head>
      <meta http-equiv=Content-Type content='text/html; charset=utf-8'>
      <meta name=Generator content='Microsoft Word 15 (filtered)'>
      <style>
            table {
                width: 100%;
                border-collapse: collapse;html_string_new
            }
            th, td {
                padding: 8px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }
            th, td, .first-table td {
                border: 1px solid #ddd;
            }
            th {
                background-color: #f2f2f2;
            }
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                text-align: justify;
                font-size: 15px;
            }
            .letterhead {
                text-align: center;
                margin-bottom: 20px;
            }
            .letterhead h1 {
                margin: 0;
            }
            .address {
                margin-bottom: 20px;
            }
            .details {
                margin-bottom: 20px;
            }
            .footer {
                margin-top: 20px;
                text-align: center;
            }
            .first-table {
                border: 1px solid #ddd;
                margin-bottom: 20px;
            }
            .loan-agreement{
                font-size: 18px;
            }
        </style>
   </head>
   <body lang=EN-US link=blue vlink='#954F72' style='word-wrap:break-word'>
      <!-- SANCTION LETTER START -->
      <div class=WordSection1>
         <img src='".$letterhead_url."' width='100%'>
         <p align=center style='text-align:center'>
            <b>
            <u>
            <span style='font-size:9.0pt;
               color:black'>SANCTION LETTER</span>
            </u>
            </b>
         </p>
         <p style='text-align:justify'>
            <span style='font-size:9.0pt;color:black'>&nbsp;</span>
         </p>
         <p style='text-align:justify'>
            <span style='font-size:9.0pt;color:black'>Dear ".$fullname.",</span>
         </p>
         <p style='text-align:justify'>
            <span style='font-size:9.0pt;color:black'>Sub:&nbsp; <b>
            <u>Short Term Loan from Aman Fincap Limited.</u>
            </b>
            </span>
         </p>
         <p style='text-align:justify'>
            <span style='font-size:9.0pt;color:black'>With reference to your application dated&nbsp;".date('d-m-Y', strtotime($leadDetails->created_on))."&nbsp;for a Short Term Loan, we have the pleasure of sanctioning you an amount of&nbsp; <strong>Rs. " . number_format(round($camDetails->loan_recommended, 0), 2) . "</strong> subject to the following terms and conditions: </span>
         </p>
         <p style='margin-left:28.35pt;text-align:justify;text-indent:-10.35pt'>
            <span style='font-size:9.0pt;color:black'>1. <span style='font:7.0pt '>&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;color:black'>Loan Amount: " . number_format(round($camDetails->loan_recommended, 0), 2) . "</span>
            </b>
         </p>
         <p style='margin-left:28.35pt;text-align:justify;text-indent:-10.35pt'>
            <span style='font-size:9.0pt;color:black'>2. <span style='font:7.0pt '>&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;color:black'>Interest Rate: ". number_format($camDetails->roi, 2) ."%</span>
            </b>
         </p>
         <p style='text-align:justify'>
            <span style='font-size:9.0pt;color:black'>As of effective date, the Schedule of interest and the charges are as follows:</span>
         </p>
         <table class=MsoNormalTable border=1 cellspacing=0 cellpadding=0 width=652 style='width:100%;border-collapse:collapse;'>
            <tr>
               <td width=192 style='width:144.1pt;border:solid black 1.0pt;padding:7.5pt 7.5pt 7.5pt 7.5pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <b>
                     <span style='font-size:9.0pt;
                        color:black'>Interest on the Loan</span>
                     </b>
                  </p>
               </td>
               <td width=459 style='width:344.55pt;border:solid black 1.0pt;border-left:
                  none;padding:7.5pt 7.5pt 7.5pt 7.5pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <b>
                     <span style='font-size:9.0pt;color:black'>". number_format($camDetails->roi, 2) ."</span>
                     </b>
                     <b>
                     <span style='font-size:9.0pt;color:black'>% per day from the date of disbursal until the date of repayment. In the event of failure to make payment on due date, a penal interest of ". round(($camDetails->roi * 2), 2) ."% per day shall be applicable from the due date until the date of payment.</span>
                     </b>
                  </p>
               </td>
            </tr>
            <tr>
               <td style='border:solid black 1.0pt;border-top:none;padding:7.5pt 7.5pt 7.5pt 7.5pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <b>
                     <span style='font-size:9.0pt;
                        color:black'>Processing Fee</span>
                     </b>
                  </p>
               </td>
               <td width=459 style='width:344.55pt;border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:7.5pt 7.5pt 7.5pt 7.5pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <b>
                     <span style='font-size:9.0pt;
                        color:black'>Lender will charge a processing fee of INR ". number_format(round($camDetails->admin_fee, 0), 2) ." including applicable taxes.</span>
                     </b>
                  </p>
               </td>
            </tr>
            <tr>
               <td style='border:solid black 1.0pt;border-top:none;padding:7.5pt 7.5pt 7.5pt 7.5pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <b>
                     <span style='font-size:9.0pt;
                        color:black'>Late Fee</span>
                     </b>
                  </p>
               </td>
               <td width=459 style='width:344.55pt;border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:7.5pt 7.5pt 7.5pt 7.5pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <b>
                     <span style='font-size:9.0pt;
                        color:black'>In case of delay in payment of amount due, late fee charges shall be INR ". round(($camDetails->roi * 2), 2) ."% of the loan amount, whichever is higher.</span>
                     </b>
                  </p>
               </td>
            </tr>
            <tr>
               <td style='border:solid black 1.0pt;border-top:none;padding:7.5pt 7.5pt 7.5pt 7.5pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <b>
                     <span style='font-size:9.0pt;
                        color:black'>Maximum interest rate</span>
                     </b>
                  </p>
               </td>
               <td width=459 style='width:344.55pt;border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:7.5pt 7.5pt 7.5pt 7.5pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <b>
                     <span style='font-size:9.0pt;
                        color:black'>The Interest rate on the loan shall not exceed 50% per month.</span>
                     </b>
                  </p>
               </td>
            </tr>
         </table>
         <p style='text-align:justify'>
            <span style='font-size:9.0pt;color:black'>The Lender, may at its sole discretion, waive or reduce the charges as mentioned above, on a case to case basis.</span>
         </p>
         <p>
            <span style='font-size:9.0pt;color:black'>For any queries, you may write to us at&nbsp;</span>
            <a href='mailto:info@amanfincap.com' target='_blank'>
            <span style='font-size:
               9.0pt'>info@amanfincap.com</span>
            </a>
            <span style='font-size:9.0pt;color:black'> ,&nbsp;</span>
            <a href='mailto:grievanceredressal@amanfincap.com' target='_blank'>
            <span style='font-size:9.0pt'>grievanceredressal@amanfincap.com</span>
            </a>
            <span style='font-size:9.0pt;color:black'>
            <br> Look forward to serving you. <br> Kind Regards </span>
         </p>
         <p class=MsoNormal style='text-align:justify'>&nbsp;</p>
         <img style='margin-top: 150px;' src='".$letterfooter_url."' width='100%'>
      </div>
      <!-- SANCTION LETTER END -->
      <!-- KEY FACT STATEMENT START -->
      <div class='WordSection1 second-page'>
         <img src='".$letterhead_url."' width='100%'>
         <p class=MsoNormal align=center style='margin-top:0.2in;margin-bottom:0.2in;text-align:center;
            line-height:normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>KEY FACT STATEMENT</span>
            </b>
         </p>
         <!-- <p class=MsoNormal align=center style='margin-bottom:0in;text-align:center;
            line-height:normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>
            <br> (EMI) </span>
            </b>
         </p> -->
         <p class=MsoListParagraph style='margin-top:0in;margin-right:0in;margin-bottom:
            0.2in;margin-left:14.2pt;text-indent:-14.2pt;line-height:normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>A. <span style='font:7.0pt '>&nbsp; </span>
            </span>
            </b>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>DETAILS OF LOAN</span>
            </b>
         </p>
         <table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:100%;border-collapse:collapse'>
            <tr>
               <td style='border:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Name of the Lender</span>
                  </p>
               </td>
               <td style='border:solid black 1.0pt;border-left:
                  none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Aman Fincap Limited.</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td style='border:solid black 1.0pt;border-top:none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Name of the Borrower</span>
                  </p>
               </td>
               <td style='border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>".$fullname."</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td style='border:solid black 1.0pt;border-top:none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Name of the Lending Service Provider (LSP)</span>
                  </p>
               </td>
               <td style='border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Naman Finlease Private Limited</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td style='border:solid black 1.0pt;border-top:none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Name of the Digital Lending Application (DLA)</span>
                  </p>
               </td>
               <td style='border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Paisa On Salary</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td style='border:solid black 1.0pt;border-top:none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Name of the of Loan Service Provider acting as recovery agent and authorized to approach the Borrower</span>
                  </p>
               </td>
               <td style='border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Naman Finlease Private Limited</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td style='border:solid black 1.0pt;border-top:none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Purpose of the Loan</span>
                  </p>
               </td>
               <td style='border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>".$leadDetails->purpose."</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td style='border:solid black 1.0pt;border-top:none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Type of loan</span>
                  </p>
               </td>
               <td style='border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>BULLET LOAN / ONE TIME REPAYMENT</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td style='border:solid black 1.0pt;border-top:none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Loan Amount (INR)</span>
                  </p>
               </td>
               <td style='border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>".number_format(round($camDetails->loan_recommended, 0), 2)."</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td style='border:solid black 1.0pt;border-top:none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>APR (Annualised Percentage Rate)</span>
                  </p>
               </td>
               <td style='border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <!--<span style='font-size:9.0pt;font-family:sans-serif;color:black'>".round(($camDetails->roi * 365), 2)." % p.a. which shall comprise of;</span>-->
                     <span style='font-size:9.0pt;font-family:sans-serif;color:black'>".$annual_percent_rate." % p.a. which shall comprise of;</span>
                  </p>
                  <p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
                     margin-bottom:0in;margin-left:11.45pt;text-indent:-14.15pt;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>1) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
                     </span>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Fixed Rate of Interest : ". number_format($camDetails->roi, 2) ." % on a daily basis;</span>
                  </p>
                  <p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
                     margin-bottom:0in;margin-left:11.45pt;text-indent:-14.15pt;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>2) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
                     </span>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Processing fee : ".number_format(round($camDetails->admin_fee, 0), 2)." (one time, non-refundable fee levied for processing and grant of loan, Taxes and Levies as applicable);</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td style='border:solid black 1.0pt;border-top:none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Net disbursed amount</span>
                  </p>
               </td>
               <td style='border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>INR ".$final_disbursal." after deduction of Processing Fee;</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td style='border:solid black 1.0pt;border-top:none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Total Repayment Amount</span>
                  </p>
               </td>
               <td style='border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>INR ".number_format(round($camDetails->repayment_amount, 0), 2)."</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td style='border:solid black 1.0pt;border-top:none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Tenure /Term of Loan in days</span>
                  </p>
               </td>
               <td style='border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>".$camDetails->tenure."</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td style='border:solid black 1.0pt;border-top:none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Due Date</span>
                  </p>
               </td>
               <td style='border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>".$camDetails->repayment_date."</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td style='border:solid black 1.0pt;border-top:none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Cooling-Off period*</span>
                  </p>
               </td>
               <td style='border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>3 days</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td colspan='2' style='border:solid black 1.0pt;border-top:none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <b>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Fees and Charges</span>
                     </b>
                  </p>
               </td>
            </tr>
            <tr>
               <td style='border:solid black 1.0pt;border-top:none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Pre-Payment Charges</span>
                  </p>
               </td>
               <td style='border:solid black 1.0pt;border-left:
                  none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>NA</span>
                  </p>
               </td>
            </tr>
         </table>
         <br>
         <img style='margin-top: 150px;' src='".$letterfooter_url."' width='100%'>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:100%;border-collapse:collapse'>
            <tr>
               <td width='200' style='border:solid black 1.0pt;border-top:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Default /Penal Rate of Interest</span>
                  </p>
               </td>
               <td style='border-top:solid black 1.0pt;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>According to Late Payment/Bounce Charge Grid</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td width='200' style='border:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Taxes &amp; Levies</span>
                  </p>
               </td>
               <td style='border-top:solid black 1.0pt;;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>The Loan and any other services rendered by the Lender and/ or payments made by the Borrower shall be subject to applicable taxes notified by the government from time to time. The Borrower shall pay all taxes present and future on any transactions undertaken with the Lender.</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td width='200' style='border:solid black 1.0pt;border-top:none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Costs and charges</span>
                  </p>
               </td>
               <td style='border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>1. Cheque/electronic Instrument bounce /non-registration of NACH – NA <br> 2. Payment gateway charges for repayment –NA <br> 3. Stamp Duty -NA </span>
                  </p>
               </td>
            </tr>
         </table>
         <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>
            <br>
            </span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>*A cooling off period allows you / Borrower / end-user to cancel or withdraw from the loan until the Cooling-off Period. Upon expiry of the Cooling-off Period, the loan terms shall be deemed to be accepted by you. The Cooling-off Period on the loan commences / starts from the date of signing / accepting the loan terms / agreement and ends on the third (3) day from such acceptance. The Lender shall not levy any penalty or pre-payment charges for cancellation during the Cooling-off Period.</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>To apply for cancellation of loan during the Cooling-off Period, a request must be submitted by the Borrower at&nbsp;</span>
            <u>
            <a href='mailto:info@amanfincap.com' target='_blank'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:blue'>info@amanfincap.com</span>
               </a>
            </u>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>,&nbsp;</span>
            <a href='mailto:grievanceredressal@amanfincap.com' target='_blank'>
            <span style='font-size:9.0pt;
               font-family:sans-serif'>grievanceredressal@amanfincap.com</span>
            </a>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>. After submission of such request, the Borrower must submit a copy of the bank account statement in which the said loan amount was disbursed along with an undertaking that such loan amount was not utilized by the Borrower during the Cooling-off Period.</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The cancellation request will only be accepted, if (a) the loan has not been utilized; and (b) the Borrower repays the principal amount along with the Rate of Interest.</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         <p class=MsoListParagraph style='margin-top:0in;margin-right:0in;margin-bottom:
            0in;margin-left:14.2pt;text-indent:-14.2pt;line-height:normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>B. <span style='font:7.0pt '>&nbsp; </span>
            </span>
            </b>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>GRIEVANCE REDRESSAL MECHANISM</span>
            </b>
         </p>
         <table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width=659 style='width:100%;border-collapse:collapse'>
            <tr>
               <td style='border:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <b>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Level_1</span>
                     </b>
                  </p>
               </td>
               <td width=590 colspan=2 style='width:442.3pt;border:solid black 1.0pt;
                  border-left:none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <b>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Grievance Redressal Officer (GRO)</span>
                     </b>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>
                     <br> Borrowers can refer to Grievance Redressal Policy at <a href='https://www.paisaonsalary.com/privacy-policy' target='_blank'>https://www.paisaonsalary.com/privacy-policy</a></span>
                  </p>
               </td>
            </tr>
            <tr>
               <td style='border:solid black 1.0pt;border-top:none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <b>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>LSP</span>
                     </b>
                  </p>
               </td>
               <td width=305 style='width:228.8pt;border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <b>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Grievance Redressal Officer (GRO)</span>
                     </b>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>
                     <br>
                     <br> Ms. Chhaya Sharma </span>
                  </p>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Address: 88-E 2nd Floor Near Punjab and Sind Bank, Jindal Chowk Hisar, Haryana 125001 <br> Contact No:&nbsp;9588534824 </span>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>
                     <br> Email:&nbsp;<a href='mailto:grievance@paisaonsalary.in' target='_blank'>grievance@paisaonsalary.in</a></span>,
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>
                     <br>
                     <br></span>
                  </p>
               </td>
               <td width=285 style='width:213.5pt;border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <b>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Nodal Officer</span>
                     </b>
                     <span style='font-size:9.0pt;
                        font-family:sans-serif;color:black'>
                     <br>
                     <br> Ms. Janvi</span>
                  </p>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Address: 88-E 2nd Floor Near Punjab and Sind Bank, Jindal Chowk Hisar, Haryana 125001</span>
                  </p>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Contact No:&nbsp;9588534824</span>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>
                     <br> Email:&nbsp;<a href='info@paisaonsalary.in' target='_blank'>info@paisaonsalary.in</a> <br>
                     <br></span>
                  </p>
               </td>
            </tr>
         </table>
         <br><br>
         <img style='margin-top: 150px;' src='".$letterfooter_url."' width='100%'>
         <br><br>
         <img src='".$letterhead_url."' width='100%'>
         <table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width=659 style='width:100%;border-collapse:collapse'>
            <tr>
               <td style='border:solid black 1.0pt;border-top:none;padding:6.0pt 6.0pt 6.0pt 6.0pt'></td>
               <td width=305 style='width:228.8pt;border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>The GRO may be reached on the number provided above anytime between 10:00 and 19:00 from Monday to Saturdays except public holidays or through the e-mail address above.The GRO shall endeavor to resolve the grievance within a period of (14) fourteen days from the date of receipt of a grievance. </span>
                  </p>
               </td>
               <td width=285 style='width:213.5pt;border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>If the Borrower does not receive a response from the GRO within 14 (fourteen) days of making a representation, or if the Borrower is not satisfied with the response received from the GRO, the Borrower may reach the Nodal Officer anytime between 10:00 to 19:00 from Monday to Saturdays except public holidays or write to the Nodal Officer. </span>
                  </p>
               </td>
            </tr>
            <tr>
               <td style='border:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <b>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>RE</span>
                     </b>
                  </p>
               </td>
               <td width=305 style='width:228.8pt;border-top:solid black 1.0pt;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <b>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Grievance Redressal Officer (GRO)</span>
                     </b>
                  </p>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Mr. ANIKET PRAJAPATI</span>
                  </p>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Address: Office No. 105, First Floor, Plot No. 1/50, Ganga Apartment, Lalita Park, Laxmi Nagar, East Delhi, Shahdara, Shahdara, Delhi, India, 110092</span>
                  </p>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>
                     <br> Contact No:&nbsp;9310604350 </span>
                     <a href='tel:9588534824'></a>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>
                     <br> Email:&nbsp; </span>
                     <a href='mailto:grievanceredressal@amanfincap.com'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>grievanceredressal@amanfincap.com</span>
                     </a>
                  </p>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>The GRO may be reached on the number provided above anytime between 10:00 and 19:00 from Monday to Saturdays except public holidays or through the e-mail address above.The GRO shall endeavor to resolve the grievance within a period of (14) fourteen days from the date of receipt of a grievance</span>
                  </p>
               </td>
               <td width=285 style='width:213.5pt;border-top:solid black 1.0pt;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <b>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>&nbsp;</span>
                     </b>
                  </p>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <b>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Nodal Officer</span>
                     </b>
                  </p>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Mr. NITESH KUMAR MISHRA</span>
                  </p>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Address: Office No. 105, First Floor, Plot No. 1/50, Ganga Apartment, Lalita Park, Laxmi Nagar, East Delhi, Shahdara, Shahdara, Delhi, India, 110092</span>
                  </p>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Contact No:&nbsp;8178698371</span>
                  </p>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <a href='tel:9588534824'></a>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>
                     <br> Email:&nbsp; </span>
                     <a href='mailto:grievance.nodal@amanfincap.com'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>grievance.nodal@amanfincap.com</span>
                     </a>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>
                     <br> The GRO may be reached on the number provided above anytime between 10:00 and 19:00 from Monday to Saturdays except public holidays or through the e-mail address above.The GRO shall endeavor to resolve the grievance within a period of (14) fourteen days from the date of receipt of a grievance </span>
                  </p>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <b>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>&nbsp;</span>
                     </b>
                  </p>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <b>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>&nbsp;</span>
                     </b>
                  </p>
                  <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
                     <b>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>&nbsp;</span>
                     </b>
                  </p>
               </td>
            </tr>
         </table>
         <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>
            <br>
            <br>
            <br>
            </span>
         </p>
         <p class=MsoListParagraph style='margin-top:0in;margin-right:0in;margin-bottom:
            0in;margin-left:14.2pt;text-indent:-14.2pt;line-height:normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>C. <span style='font:7.0pt '>&nbsp; </span>
            </span>
            </b>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>TERMS &amp; CONDITIONS OF RECOVERY MECHANISM</span>
            </b>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The Lender undertakes the recovery practices considering the following terms.</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-indent:0in;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>1. <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>In-house/Outsource Recovery</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-indent:0in;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>2. <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Telephone Recovery {Human / IVR / Robo Calls}</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-indent:0in;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>3. <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Digital Recovery</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-indent:0in;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>4. <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Reminder Communication</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-indent:0in;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>5. <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Legal Notice</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-indent:0in;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>6. <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Arbitration &amp; Conciliation</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>
            <br> For the purpose of undertaking collection and recovery the Lender may, either on its own or through the service provider (including its agents etc.), undertake collection or recovery from the Borrower. The details of service provider and its agents etc. </span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>
            <br>
            <br>
            </span>
         </p>
         <br>
         <img style='margin-top: 100px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoListParagraph style='margin-top:0in;margin-right:0in;margin-bottom:
            0in;margin-left:14.2pt;text-indent:-14.2pt;line-height:normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>D. <span style='font:7.0pt '>&nbsp; </span>
            </span>
            </b>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>REPAYMENT SCHEDULE</span>
            </b>
         </p>
         <table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:100%;border-collapse:collapse'>
            <tr>
               <td width=66 style='width:49.25pt;border:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal align=center style='margin-bottom:0in;text-align:center;
                     line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>S.No.</span>
                  </p>
               </td>
               <td width=113 style='width:85.05pt;border:solid black 1.0pt;border-left:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal align=center style='margin-bottom:0in;text-align:center;
                     line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Principal (INR)</span>
                  </p>
               </td>
               <td width=123 style='width:92.1pt;border:solid black 1.0pt;border-left:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal align=center style='margin-bottom:0in;text-align:center;
                     line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Interest (INR)</span>
                  </p>
               </td>
               <td width=104 style='width:78.0pt;border:solid black 1.0pt;border-left:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal align=center style='margin-bottom:0in;text-align:center;
                     line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Fees (INR)</span>
                  </p>
               </td>
               <td width=123 style='width:92.1pt;border:solid black 1.0pt;border-left:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal align=center style='margin-bottom:0in;text-align:center;
                     line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Due Date</span>
                  </p>
               </td>
               <td width=113 style='width:85.05pt;border:solid black 1.0pt;border-left:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal align=center style='margin-bottom:0in;text-align:center;
                     line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>Repayable (INR)</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td width=66 style='width:49.25pt;border:solid black 1.0pt;border-top:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal align=center style='margin-bottom:0in;text-align:center;
                     line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>1</span>
                  </p>
               </td>
               <td width=113 style='width:85.05pt;border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal align=center style='margin-bottom:0in;text-align:center;
                     line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>".number_format(round($camDetails->loan_recommended, 0), 2)."</span>
                  </p>
               </td>
               <td width=123 style='width:92.1pt;border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal align=center style='margin-bottom:0in;text-align:center;
                     line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>". number_format($camDetails->roi, 2) ."</span>
                  </p>
               </td>
               <td width=104 style='width:78.0pt;border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal align=center style='margin-bottom:0in;text-align:center;
                     line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>".number_format(round($camDetails->admin_fee, 0), 2)."</span>
                  </p>
               </td>
               <td width=123 style='width:92.1pt;border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal align=center style='margin-bottom:0in;text-align:center;
                     line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>".$camDetails->repayment_date."</span>
                  </p>
               </td>
               <td width=113 style='width:85.05pt;border-top:none;border-left:none;
                  border-bottom:solid black 1.0pt;border-right:solid black 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal align=center style='margin-bottom:0in;text-align:center;
                     line-height:normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif;
                        color:black'>".number_format(round($camDetails->repayment_amount, 0), 2)."</span>
                  </p>
               </td>
            </tr>
         </table>
         <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>
            <br>
            <br>
            </span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>For any queries, you may write to us at&nbsp; </span>
            <a href='mailto:info@amanfincap.com' target='_blank'>
            <span style='font-size:9.0pt;font-family:sans-serif'>info@amanfincap.com</span>
            </a>,
            <a href='mailto:grievanceredressal@amanfincap.com' target='_blank'>
            <span style='font-size:9.0pt;font-family:sans-serif'>grievanceredressal@amanfincap.com</span>
            </a>
            <u>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:blue'></span>
            </u>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>
            <br> Look forward to serving you. <br> Kind Regards </span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>&nbsp;</span>
         </p>
      </div>
      <!-- KEY FACT STATEMENT END -->
      <!-- Loan Terms START -->
      <div class=WordSection1>
         <p class=MsoNormal align=center style='margin-bottom:0in;text-align:center;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>LOAN CUM COMMERCIAL TERMS</span>
            </b>
         </p>
         <p class=MsoNormal align=center style='margin-bottom:0in;text-align:center;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-family:sans-serif;color:black'>By clicking the &quot;I AGREE&quot; button, you acknowledge that you have read, understood, and accepted the terms and conditions set forth herein (“Terms”). You further agree to be bound by these Terms and will be fully responsible for complying with them. If you do not agree with any provision of these Terms, do not click the “I AGREE” button.</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-family:sans-serif;color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-family:sans-serif;color:black'>Upon your acceptance of these Terms and the Key Fact Statement (“KFS”), you, the borrower (“Borrower”)—which term shall include individual, legal heir(s), successor(s), and permitted assignee(s), unless repugnant to the context—confirm having requested a credit facility from <b>Aman Fincap Ltd.,</b> having its registered office at 105, Plot No. 1/50, First Floor, Ganga Apartment, Lalita Park, Laxmi Nagar, East Delhi, Delhi-110092 (&quot;Lender&quot;), under the conditions specified herein. </span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-family:sans-serif;color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-family:sans-serif;color:black'>The Borrower hereby understands and acknowledges that these Terms are for two loan products, namely (a) Bullet Repayment; (b) EMI / Installment and that based on the nature / type of Loan availed by me / us through the Application Form and thereafter accepted in the KFS, only the respective Loan product Terms shall apply.</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-family:sans-serif;color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>1. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            </b>
            <b>
            <span style='font-family:sans-serif;color:black'>Definition:</span>
            </b>
            <span style='font-family:sans-serif;color:black'>&nbsp;In these Terms, the capitalized words shall have the following meanings. All terms capitalized but not defined below shall have such meaning as ascribed to them in the Commercial Terms:</span>
         </p>
         <br>
         <img style='margin-top: 50px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>1.1 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-family:sans-serif;
               color:black'>ANNUALISED PERCENTAGE RATE / APR</span>
            </b>
            <span style='font-family:
               sans-serif;color:black'>&nbsp;means the effective annualised rate payable by the Borrower based on an all-inclusive cost and margin including cost of funds, credit cost and operating cost, processing fee, verification charges, maintenance charges, etc., and / or such specific costs indicated in the KFS.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>1.2 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-family:sans-serif;
               color:black'>APPLICATION FORM</span>
            </b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;means, direct application made by the Borrower for the Loan through web portal / mobile application of the Lender.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>1.3 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-family:sans-serif;
               color:black'>BORROWER</span>
            </b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;means the individual availing the Loan from the Lender in under these Terms hereof and who has agreed to the terms and conditions contained in these Terms for the purpose of availing Loan.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>1.4 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-family:sans-serif;
               color:black'>BULLET REPAYMENT</span>
            </b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;shall mean such Loan product where the Borrower repays the full Outstanding Amount on one fixed Due Date;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>1.5 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-family:sans-serif;
               color:black'>COMMERCIAL TERMS</span>
            </b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;shall mean the terms applicable to the Loan as detailed in SCHEDULE I of these Terms.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>1.6 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-family:sans-serif;
               color:black'>DUE DATE</span>
            </b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;shall mean such date identified in the Commercial Terms and KFS on which the EMI shall be due and payable by the Borrower.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>1.7 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-family:sans-serif;
               color:black'>ECS OR NACH</span>
            </b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;means electronic clearing services / electronic payment services, consented to in writing by Borrower, for facilitating the payment of EMI, fees and other applicable charges to the Lender in connection with the Loan.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>1.8 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-family:sans-serif;
               color:black'>ESSENTIAL DATA</span>
            </b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;means such data which is statutorily required by the Lender to be retained for compliance with applicable laws.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>1.9 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-family:sans-serif;
               color:black'>EQUATED MONTHLY INSTALLMENT (EMI) / INSTALLMENT</span>
            </b>
            <span style='font-family:sans-serif;color:black'>&nbsp;shall mean such Loan product whereby the Borrower repays the Outstanding Amount at such frequency (monthly / quarterly) as indicated in the Commercial Terms on the respective Due Date during the Tenure of the Loan.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>1.10 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-family:sans-serif;
               color:black'>GRIEVANCE REDRESSAL OFFICER</span>
            </b>
            <span style='font-family:
               sans-serif;color:black'>&nbsp;shall mean the officer named in the Commercial Terms and KFS who shall address and resolve the complaints / queries of Borrowers.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>1.11 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-family:sans-serif;
               color:black'>LOAN</span>
            </b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;means the credit facility granted / agreed to be granted by the Lender to the Borrower.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>1.12 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-family:sans-serif;
               color:black'>KFS</span>
            </b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;shall mean the Key Fact Statement provided by the Lender before the execution of these Terms which lays down details such as Annual Percentage Rate, terms and conditions of recovery mechanism, details of Grievance Redressal Officer, and Cooling-off/ Look-up period.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>1.13 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-family:sans-serif;
               color:black'>OUTSTANDING AMOUNT</span>
            </b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;shall mean all amounts pertaining to the Loan whether by way of the principal amount together with any EMI, accrued interest, default interest, Prepayment charges, bounce charges and/or any other cost and charges indicated herein or the Commercial Terms or KFS.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>1.14 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-family:sans-serif;
               color:black'>PREPAYMENT</span>
            </b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;means premature repayment of the Loan in full, including principal amount, interest thereon, and all Outstanding Amount which is not yet due for payment by the Borrower under the Terms.</span>
         </p>
         <br>
         <img style='margin-top: 50px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>1.15 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-family:sans-serif;
               color:black'>REPAYMENT SCHEDULE</span>
            </b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;shall mean the schedule shared by the Lender which indicates the Outstanding Amount and the Due Date on which the said Outstanding Amount is to be repaid by the Borrower.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>1.16 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-family:sans-serif;
               color:black'>REPAYMENT INSTRUMENT</span>
            </b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;shall mean an ECS / NACH / standing instructions / cheque / post-dated cheque (PDC) and / or such other negotiable instrument or mode of payment that may be notified by the RBI from time to time.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>1.17 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-family:sans-serif;
               color:black'>RBI</span>
            </b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;shall mean the Reserve Bank of India.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>1.18 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-family:sans-serif;
               color:black'>TENURE</span>
            </b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;shall mean the months / years as indicated in the Commercial Terms and KFS.</span>
         </p>
         <p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;line-height:normal'>
            <span style='font-family:sans-serif;color:black'>&nbsp;</span>
         </p>
         
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>2. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            </b>
            <b>
            <span style='font-family:sans-serif;color:black'>Borrower's Representations, Warranties and Undertakings:</span>
            </b>
            <span style='font-family:
               sans-serif;color:black'>&nbsp;The Borrower hereby represents and warrants as follows: </span>
         </p>
         <p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:18.75pt;text-align:justify;text-indent:-18.75pt;
            line-height:normal;display:none'>
            <b>
            <span style='font-family:sans-serif;
               color:black;display:none'>1 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            </b>
            <span style='font-family:sans-serif;
               color:black;display:none'>&nbsp;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:18.75pt;text-align:justify;text-indent:-18.75pt;
            line-height:normal;display:none'>
            <b>
            <span style='font-family:sans-serif;
               color:black;display:none'>2 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            </b>
            <span style='font-family:sans-serif;
               color:black;display:none'>&nbsp;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>2.1 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-family:sans-serif;
               color:black'>Is of sound mind, competent to contract, has attained the age of majority, and capable of fulfilling the obligations under these terms;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>2.2 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-family:sans-serif;
               color:black'>Is financially stable, not declared bankrupt or insolvent, and capable of repaying the Loan.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>2.3 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-family:sans-serif;
               color:black'>Will maintain sufficient funds in the designated account for repayment.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>2.4 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-family:sans-serif;
               color:black'>Shall maintain confidentiality and security over communications with the Lender.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>2.5 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-family:sans-serif;
               color:black'>Accepts the Lender’s final discretion in case of any disputes regarding interpretation or materiality of any matter.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>2.6 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-family:sans-serif;
               color:black'>All information and documents provided are true, correct, complete, and not misleading.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>2.7 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-family:sans-serif;
               color:black'>Authorizes the Lender to access necessary data (location, CIBIL score, KYC, etc.) for loan processing and servicing.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>2.8 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-family:sans-serif;
               color:black'>Agrees to the storage of Essential Data for a duration of 5 to 8 years as per law.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>2.9 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-family:sans-serif;
               color:black'>Confirms that the Loan shall be used strictly for the declared purpose and agrees to provide end-use certification upon request.</span>
         </p>
         <p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:21.3pt;text-align:justify;line-height:normal'>
            <span style='font-family:sans-serif;color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>3. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            </b>
            <b>
            <span style='font-family:sans-serif;color:black'>Loan disbursement:</span>
            </b>
            <span style='font-family:sans-serif;color:black'>&nbsp;The Lender shall disburse the Loan into an account specified in Commercial Terms, below and/ or such other account as indicated in the Loan disbursal request form (“DRF”).</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-family:sans-serif;color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>4. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            </b>
            <b>
            <span style='font-family:sans-serif;color:black'>Repayment:</span>
            </b>
            <span style='font-family:sans-serif;color:black'>&nbsp;The Borrower hereby agrees and undertakes:</span>
         </p>
         <p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:18.75pt;text-align:justify;text-indent:-18.75pt;
            line-height:normal;display:none'>
            <b>
            <span style='font-family:sans-serif;
               color:black;display:none'>3 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            </b>
            <span style='font-family:sans-serif;
               color:black;display:none'>&nbsp;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:18.75pt;text-align:justify;text-indent:-18.75pt;
            line-height:normal;display:none'>
            <b>
            <span style='font-family:sans-serif;
               color:black;display:none'>4 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            </b>
            <span style='font-family:sans-serif;
               color:black;display:none'>&nbsp;</span>
         </p>
         <br><br><br><br><br><br>
         <img style='margin-top: 70px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>4.1 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-family:sans-serif;
               color:black'>to issue such Repayment Instruments for: (a) Bullet Repayment Loan; (b) EMI Loan, respectively based on the type of Loan product agreed to be availed by the Borrower. The Borrower shall repay the Loan for such amount and at such intervals as is specified in the Commercial Terms, KFS and / or Repayment Schedule shared by the Lender from time to time. The Borrower understands and acknowledges that Lender may collect repayment directly by itself or through third party agents, as appointed by the Lender. The Borrower further undertakes to extend all co-operation with such third-party agents to ensure that due fulﬁllment of Borrower's obligations under these Terms and to obtain a valid discharge against the Outstanding Amounts.</span>
         </p>
         
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>4.2 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-family:sans-serif;
               color:black'>that should at any time during the Tenure of the Loan, if instructions for stop payment, cancellation of a Repayment Instrument and /or fail to confirm / register the ECS / NACH, the same shall be treated as dishonour / bounce of the Repayment Instrument, and the Loan shall be recalled at the discretion of the Lender.</span>
         </p>
         
         <p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif;
               color:black'>4.3 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-family:sans-serif;
               color:black'>that upon delay and / or non-payment of Outstanding Amounts on the respective Due Date, the Lender shall have the right to classify the Borrower and its accounts as Special Mention Account (&quot;SMA”) and / or Non-Performing Asset (“NPA”), as detailed below:</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-family:sans-serif;color:black'>If the Due Date is second (2nd) day of every month and the Borrower pays the Instalment amount on thirtieth (30th) of the month, the Lender shall levy Default Interest starting from third (3rd) until thirtieth (30th). In the second scenario, if the Borrower does not pay on the Due Date and the Outstanding Amount remain unpaid for a continuous period of thirty (30) days, the Lender shall have the right to: (a) levy Default Interest; and (b) report the Borrower as an SMA-0. If the Borrower further continues to default in repayment from a period of thirty (30) days up to sixty (60) days, the Lender shall report the Borrower as SMA-1 and levy Default Interest. If the Borrower further continues to default in repayment from a period of sixty (60) days up to ninety (90) day, the Lender shall report the Borrower as SMA-2 and levy Default Interest. Further, if the Borrower defaults in repayment beyond ninety (90) days, the Lender shall report the Borrower as NPA and levy Default Interest. The Lender shall additionally have the right to call and Event of Default in accordance with Clause 8, below, at any time upon an event of non-payment.</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-family:sans-serif;color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>5. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            </b>
            <b>
            <span style='font-family:sans-serif;color:black'>Interest &amp; costs:</span>
            </b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;The Borrower shall pay interest on the Loan at the Rate of Interest specified in the Commercial Terms from the date when the Loan is disbursed. The Lender shall inform the Borrower regarding any change in Rate of Interest by way of publishing a notice that shall be displayed on the notice board in the branch of the Lender or on the Lender's website. The said notice of change of Rate of Interest shall be deemed to be sufficient notice of change to the Borrower.</span>
         </p>
         <br><br>      
         <img style='margin-top: 70px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-family:sans-serif;color:black'>The Borrower shall pay on the Loan, processing fee, stamp duty, and such other fees and charges indicated in the KFS and/ or notified by the Lender. Further, the Borrower undertakes to pay such fees / charges/ costs that comprise the Annual Percentage Rate, as mentioned in the Commercial Terms and KFS. The Borrower understands that Loan is subject to applicable taxes and such other statutory levies as may be notified by the government from time to time. All such taxes and levies shall be payable by the Borrower.</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>6. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            </b>
            <b>
            <span style='font-family:sans-serif;color:black'>Prepayment.</span>
            </b>
            <span style='font-family:sans-serif;color:black'>&nbsp;In the event the Borrower wishes to prepay the Loan, the same shall be subject to Prepayment Fees as indicated in the Commercial Terms. Such Prepayment Fees shall be levied or become applicable after expiry of the Cooling-off and / or Look-up period indicated in the Commercial Terms and KFS.</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>7. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            </b>
            <b>
            <span style='font-family:sans-serif;color:black'>Default/ Penal Interest:</span>
            </b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;In the event the Borrower fails to comply with the Terms including failure to repay the Loan on a Due Date and/ or such other Events of Default listed in Clause 8 below, the Lender shall be entitled to levy Default/ Penal Interest set out in Commercial Terms and KFS on the Outstanding Amount from the date of default until full and final settlement.</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>8. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            </b>
            <b>
            <span style='font-family:sans-serif;color:black'>Events of Default and Remedies:</span>
            </b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;The following are the events of default under these Terms (“Event of Default”): (a) failure by the Borrower to repay the Loan (whole or part) on the Due Date; (b) breach / non-performance of any representation, warranty, covenant, undertaking or obligations under these Terms (including non-submission of documents or execution thereof), or any change in the information furnished by the Borrower to the Lender, if deemed material by the Lender; (c) the Borrower is subjected to any insolvency or bankruptcy proceeding or a receiver is appointed for its assets; (d) if the Borrower commits default in relation to any other loan or credit facility and /or statutory taxes and dues owed, or enters into any compromise with its creditors, or admits any inability in payment of its other debts; <br>
          (e) the performance of obligations under these Terms becomes void or illegal; (f) non-payment of any of dues and / or outstanding amounts to any other financial institutions; and / or (g) any event which, with the passage of time, is likely to become an Event of Default. 
               <br> On the happening of any Event of Default, the Lender may at its sole discretion , without prejudice to other rights and claims under these Terms, exercise any or all of the following rights, i.e.,: (a) recall the Loan and / or declare the Loan to become immediately due and payable; (b) levy Default Interest on the Outstanding Amount from the date when the Event of Default occurs until the date when the Event of Default has ceased to exist; and / or (c) exercise any other rights or remedies available to the Lender under applicable law including initiating civil and criminal proceedings. </span>
         </p>
         <br><br><br><br><br>
         <img style='margin-top: 80px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>9. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            </b>
            <b>
            <span style='font-family:sans-serif;color:black'>Unconditional Cancellability:</span>
            </b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;The Lender reserves the unconditional right to cancel the Loan and / or any tranches advanced / to be advanced (either fully or partially) without giving any prior notice to the Borrower, on the occurrence of any one or more of the following (a) in case the Loan / part of the Loan are not utilised by the Borrower; (b) in case of ‘Deterioration in the Creditworthiness’ of the Borrower in any manner whatsoever; (c) in case of non-compliance of these Terms. For the purpose of this clause, Deterioration in the Creditworthiness shall mean and include without limitation, the following events: (a) downgrade of the rating of the Borrower by a credit rating agency; and / or (b) any other reason / event in the opinion of the Lender constituting or which may constitute Deterioration in the Creditworthiness.</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>10. <span style='font:7.0pt '></span>
            </span>
            </b>
            <b>
            <span style='font-family:sans-serif;color:black'>Disclaimer:</span>
            </b>
            <span style='font-family:sans-serif;color:black'>&nbsp;The Borrower agrees and acknowledges that the Lender shall not be liable or responsible for any defect in the consumer durable products / goods purchased by the Borrower, in the event of any complaints / queries with respect to a product purchased from a third party from the proceeds of the Loan. The Borrower shall directly address such complaints / queries with the seller / manufacturer/ retailer or any other third party associated with selling such product.</span>
         </p>
         
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>11. <span style='font:7.0pt '></span>
            </span>
            </b>
            <b>
            <span style='font-family:sans-serif;color:black'>Disclosure:</span>
            </b>
            <span style='font-family:sans-serif;color:black'>&nbsp;The Borrower authorizes the Lender to disclose any information in relation to the Loan with: (a) RBI; (b) credit information companies / bureaus and information utilities; (c) any governmental / regulatory / statutory authority; (d) other financial institutions and industry bodies; (e) affiliates and group companies of the Lender; and / or (f) third party engaged by the Lender for purpose of the Loan including but not limited to KYC collection, recovery of dues and repayment and / or such other services as deemed necessary by the Lender. The Borrower hereby acknowledges and agrees that the RBI and / or any credit bureau, information utilities, any governmental / regulatory / statutory authority may publicly publish such data, subject to applicable laws.</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>12. <span style='font:7.0pt '></span>
            </span>
            </b>
            <b>
            <span style='font-family:sans-serif;color:black'>Indemnity:</span>
            </b>
            <span style='font-family:sans-serif;color:black'>&nbsp;The Borrower shall indemnify and hold harmless the Lender and its directors, officers and agents, from and against any and all costs, expenses, direct or indirect claims, liabilities, demands and / or claims whatsoever, including any third-party claims for damages incurred as a consequence of occurrence of an Event of Default, breach of the terms and conditions of these Terms or acts of omission and commission on the part of the Borrower, or otherwise on account of the Loan.</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>13. <span style='font:7.0pt '></span>
            </span>
            </b>
            <b>
            <span style='font-family:sans-serif;color:black'>Evidence to Debt/ Electronic Evidence:</span>
            </b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;The Lender may generate physical copies of these Terms from its system or produce these Terms in any other form at its discretion and the same shall be fully binding on the Borrower and such computer generated certificate / statement from the Lender’s system shall be conclusive evidence of the existence of the Outstanding Amounts of the Borrower. The Borrower hereby waives any right it may have under contract or applicable law to contest or raise an invalidity against such electronic records.</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>14. <span style='font:7.0pt '></span>
            </span>
            </b>
            <b>
            <span style='font-family:sans-serif;color:black'>Assignment:</span>
            </b>
            <span style='font-family:sans-serif;color:black'>&nbsp;The Lender may, without Borrower's consent or notice can assign its rights &amp; obligations under these Terms to its a</span>
            <span style='font-family:'Arial',sans-serif;
               color:black'>ﬃ</span>
            <span style='font-family:sans-serif;
               color:black'>liates or assignee but the Borrower is not entitled to directly or indirectly assign the benefit or obligation of these Terms to any third party.</span>
         </p>
         <br>
         <img style='margin-top: 80px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>15. <span style='font:7.0pt '></span>
            </span>
            </b>
            <b>
            <span style='font-family:sans-serif;color:black'>Dispute Resolution:</span>
            </b>
            <span style='font-family:sans-serif;color:black'>&nbsp;Any or all disputes, claims, differences arising out of or in connection with these Terms between the Lender and the Borrower shall be settled by arbitration to be referred to a sole arbitrator to be appointed by the Lender and the place of the arbitration shall be New Delhi.</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>16. <span style='font:7.0pt '></span>
            </span>
            </b>
            <span style='font-family:sans-serif;color:black'>&nbsp; <b>Governing Law:</b>&nbsp;In the event of any dispute or controversy arising out of the arbitration clause, shall be governed by laws of India and the Courts at New Delhi shall have sole and exclusive jurisdiction. </span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>17. <span style='font:7.0pt '></span>
            </span>
            </b>
            <span style='font-family:sans-serif;color:black'>&nbsp; <b>Notices:</b>&nbsp;Any notice to be given to Borrower in respect of these Terms shall be deemed to have been validly given and received if provided by using telephonic call (recorded) or text message to the registered mobile number of the Borrower, email to registered email id and written notice by courier or registered post to registered address of Borrower. </span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>18. <span style='font:7.0pt '></span>
            </span>
            </b>
            <span style='font-family:sans-serif;color:black'>&nbsp; <b>Joint and Several Liability:</b>&nbsp;The obligations of the Borrower(s) and Co-Borrowers (if any) under these Terms shall be joint and several. </span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-family:sans-serif;color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>19. <span style='font:7.0pt '></span>
            </span>
            </b>
            <span style='font-family:sans-serif;color:black'>&nbsp; <b>NACH Mandate:</b>&nbsp;The amount mentioned on NACH Mandate for approval from borrower is 250% of the existing sanctioned credit limit/ loan amount as it covers possible increase of credit limit in near future, processing fee, interest and other charges levied in the event of default. Lenders, may at their absolute discretion, waive or reduce the charges as mentioned above, on a case to case basis. <br>
            <br> In Witness Where of the Borrower hereby accepts the terms and conditions this Agreement. </span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-family:sans-serif;color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>20. <span style='font:7.0pt '></span>
            </span>
            </b>
            <span style='font-family:sans-serif;color:black'>&nbsp; <b>Appropriation:</b>&nbsp;Any payments made by or recovered from the Borrower or otherwise shall be appropriated in a manner deemed fit by the Lender, as per its policies. </span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-family:sans-serif;color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>21. <span style='font:7.0pt '></span>
            </span>
            </b>
            <span style='font-family:sans-serif;color:black'>&nbsp; <b>Set-off:</b>&nbsp;The Lender shall be entitled to, without further notice, set -off any monies with the Lender or any monies payable by the Lender to the Borrower(s) against all Outstanding Amounts of the Borrower with the Lender. </span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-family:sans-serif;color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-family:sans-serif;color:black'>For any queries, you may write to us at </span>
            <a href='mailto:finance@amanfincap.com'>
            <span style='font-family:sans-serif'>finance@amanfincap.com</span>
            </a>
            <span style='font-family:sans-serif;color:black'>
            <br>
            <br>
            </span>
         </p>
         <!--<p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-family:sans-serif;color:black'>Look forward to serving you. <br> Kind Regards </span>
         </p>-->
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-family:sans-serif;color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>&nbsp;</span>
            </b>
         </p>
         <!-- <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <b>
            <span style='font-family:sans-serif;
               color:black'>By clicking on &quot;I Accept&quot;, the Borrower hereby executes and electronically accept these Terms. Upon acceptance of these Terms, two original counterparts shall be electronically generated, one of which shall be automatically sent to the e-mail disclosed by the Borrower in the Commercial Terms, the other shall be retained by the Lender for its records.</span>
            </b>
         </p> -->
         <!-- <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-family:sans-serif;color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-family:sans-serif;color:black'>&nbsp;</span>
         </p> -->
         <!-- <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-family:sans-serif;color:black'>“ I Accept”</span>
         </p> -->
         <br>
         <img style='margin-top: 80px;' src='".$letterfooter_url."' width='100%'>
         <br>
      </div>
      <!-- Loan Terms END -->
      <!-- Loan Agreement START -->
      <div class=WordSection1>
        <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoNormal align=center style='margin-bottom:0in;text-align:center;
            line-height:normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>LOAN AGREEMENT</span>
            </b>
         </p>
         <p class=MsoNormal align=center style='margin-bottom:0in;text-align:center;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>THIS LOAN AGREEMENT</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;is made at the date and place set out in Schedule A hereto</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
            </b>
         </p>
         <p class=MsoNormal align=center style='margin-bottom:0in;text-align:center;
            line-height:normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>BY AND BETWEEN:</span>
            </b>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The entity listed in Schedule A (hereinafter referred to as the “Lender”, which expression shall, unless it be repugnant to the subject or context thereof, be deemed to mean and include its successors and assigns).</span>
         </p>
         <p class=MsoNormal align=center style='margin-bottom:0in;text-align:center;
            line-height:normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>AND</span>
            </b>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The Person(s) listed in&nbsp; <b>Schedule A</b>&nbsp;hereto, having his / her / its / their address as set out in&nbsp; <b>Schedule A</b>&nbsp;(hereinafter referred to as the&nbsp; <b>“Borrower ”</b>, which expression shall, unless it be repugnant to the subject or context thereof, be deemed to mean and include his / her / its / their respective heirs, executors, administrators, legal representatives, successors, permitted assigns, partner(s) for the time being, including the legal representatives of the deceased partner(s), if any); </span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
            </b>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>WHEREAS:</span>
            </b>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:14.2pt;text-align:justify;text-indent:-14.2pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>1. <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Lender is a non-banking finance company within the meaning of the Reserve Bank of India Act, 1934 and is registered with the Reserve Bank of India as a non-banking finance company. Lender is engaged in the business of providing financial services and financial products as required by customers from time to time</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:14.2pt;text-align:justify;text-indent:-14.2pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>2. <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The Borrower(s) is an employed/salaried individual(s) as per details set out in Schedule A hereto and are in requirement of funds for the purpose more particularly described in the Sanction Letter cum Key Fact Statement and has approached the Service Provider of the Lender to avail the Loan,</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:14.2pt;text-align:justify;text-indent:-14.2pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>3. <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Whereas the Borrower is in need of money and at the request of the Borrower and after scrutinizing all the requisite documents and credibility of the Borrower, the Lender has agreed to grant Loan, to the Borrower in accordance with the terms and conditions hereinafter contained:</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:14.2pt;text-align:justify;text-indent:-14.2pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>4. <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>NOW, THEREFORE,</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;in consideration of the representations, warranties, mutual agreements, and covenants set forth in this Agreement, the Lender and Borrower agree as follows:</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:14.2pt;text-align:justify;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>1. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            </b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp; <b>DEFINITIONS AND REFERENCE TERMS</b>
            </span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The following capitalized words/expressions shall carry the meaning ascribed to them below, throughout this Agreement, unless otherwise stated or unless repugnant to the subject or context thereof:</span>
         </p>
         <br>
         <img style='margin-top: 80px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>
            <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>i. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Agreement”</span>
            </b>
            <span style='font-size:9.0pt;font-family:
               sans-serif;color:black'>&nbsp;means this Loan Agreement, including the Schedules hereto, each as amended, modiﬁed, supplemented and / or restated from time to time, and shall also include Sanction Letter cum Key Fact Statement in accordance with the terms hereof.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>
            <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>ii. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Application Form”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;means the application form as prescribed by the Lender (whether through its Service Provider) and filled in by the Borrower, prior to the date hereof, to avail the Loan.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>
            <span style='font:7.0pt '>&nbsp; </span>iii. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Borrower&quot;</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;shall have the meaning ascribed to such term in the Recitals of this Agreement. For the avoidance of doubt, any reference in this Agreement to the term ‘Borrower’.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>
            <span style='font:7.0pt '>&nbsp; </span>iv. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Collection Account”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;means the bank account of the Lender, designated as such, to which all amounts due and payable by the Borrower pursuant to the Loan Documents, are to be remitted.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>
            <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>v. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Contract Act”</span>
            </b>
            <span style='font-size:9.0pt;font-family:
               sans-serif;color:black'>&nbsp;means Indian Contract Act, 1872;</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>
            <span style='font:7.0pt '>&nbsp; </span>vi. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Approved Bank&quot;</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;means a designated bank approved by the Lender (whether directly or through the Service Provider) for the purposes related to the Loan.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>vii. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Delayed Payment Charges”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;shall mean the additional amount payable by the Borrower to the Lender on account of any delay in payment of any Instalment and / or bullet payment(s), as more particularly set out in the Sanction Letter. Cum KFS</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>viii. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Demand Promissory Note”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;means the Demand Promissory Note in the format prescribed by the Lender, to be executed by the Borrower, to secure payment of the Loan given to the Borrower pursuant to the terms hereof and the Sanction Letter cum KFS.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>
            <span style='font:7.0pt '>&nbsp; </span>ix. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Dishonor Charges”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;means the dishonor charges payable by the Borrower to the Lender in the event of dishonor / bouncing of any Repayment Instrument, pursuant to the provisions of the Loan Documents.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>
            <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>x. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Eligibility Criteria”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;means the minimum eligibility criteria set forth by the Lender to judge the credit worthiness of the Borrower, which is subject to change from time to time, at the sole and absolute discretion of the Lender.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>
            <span style='font:7.0pt '>&nbsp; </span>xi. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Event of Default”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;has the meaning ascribed to such term in Article 8.1 of this Agreement.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>
            <span style='font:7.0pt '></span>xii. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Fee”</span>
            </b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;means the aggregate amount payable by the Borrower to the Lender / Service Provider, as per the details provided in the Sanction Letter cum Key Fact Statement</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xiii. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Repayment”</span>
            </b>
            <span style='font-size:9.0pt;font-family:
               sans-serif;color:black'>&nbsp;This can be further defined and segregated in/as bellow:</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xiv. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Installments”</span>
            </b>
            <span style='font-size:9.0pt;font-family:
               sans-serif;color:black'>&nbsp;means the equated monthly installment payable by the Borrower at monthly intervals, as detailed in the Sanction Letter cum KFS, during the tenor of the Loan, towards repayment of the principal amount of the Outstanding Balance.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>
            <span style='font:7.0pt '></span>xv. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Bullet Payment”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;means the one-time amount payable by the Borrower, as detailed in the Sanction <b>Letter</b> cum KFS, during the tenor of the Loan, towards repayment of the principal amount of the outstanding balance. “Lender” shall have the meaning ascribed to such term in the Recitals of this Agreement. </span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xvi. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Loan”</span>
            </b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;means any and all term loans sanctioned / disbursed by the Lender to the Borrower pursuant to the provisions of this Agreement and the other Loan Documents; and includes re-borrowing of the paid Loan; and disbursal of new Loan.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xvii. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Loan Documents”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;means this Agreement, the Application Form (including all documents furnished by the Borrower with the Application Form), Demand Promissory Note, Sanction Letter cum KFS, Welcome Letter and all other documents, instruments, certiﬁcates, guarantees and agreements executed and/or delivered by the Borrower or any third party in connection with the Loan in favor of the Lender pursuant to the terms of this Agreement or the other Loan Documents.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xviii. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Notice of Demand”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;means a notice of demand issued by or on behalf of the Lender.</span>
         </p>
         <br>
         <img style='margin-top: 80px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xix. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Outstanding Balance”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;means the balance of the principal amount of the Loan outstanding along with all other amounts payable by the Borrower to the Lender, pursuant to the terms of the Loan Documents, including, without limitation, the Interest, Delayed Payment Charges, Fees, Installments and / or bullet payment(s), Prepayment Charges, Dishonor Charges, Taxes, Lender Swap Charges and other costs, charges, expenses.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>
            <span style='font:7.0pt '></span>xx. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Payment”</span>
            </b>
            <span style='font-size:9.0pt;font-family:
               sans-serif;color:black'>&nbsp;means the payment towards Bullet payments, Installments, Interest, Delayed Payment Charges, Fees, Prepayment Charges, Dishonor Charges, Taxes, Lender Swap Charges and other costs, charges, expenses, payable pursuant to the terms of the Loan Documents, in respect of the Loan, through any mode as approved by the Lender.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xxi. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Person&quot;</span>
            </b>
            <span style='font-size:9.0pt;font-family:
               sans-serif;color:black'>&nbsp;shall mean any individual, corporation, partnership, (including, without limitation, association), company incorporated under the provisions of the Companies Act, 1956 / Companies Act, 2013, trust, unincorporated organization, Hindu undivided family or any governmental authority or political subdivision thereof. The expression shall, unless repugnant to the context or meaning thereof, be deemed to mean and include: (i) in case of a company, its successors and permitted assigns; (ii) in case of a partnership ﬁrm, the partners for the time being and from time to time of the partnership ﬁrm, their survivor or survivors of them, their respective heirs, administrators, executors, legal representatives and successors of the partner(s); (iii) in case of a trust, the trustee or trustees of the trust for the time being and from time to time; (iv) in case of a Hindu undivided family, the Karta and the members for the time being and from time to time of the said Hindu undivided family and their respective heirs, executors, administrators and legal representatives; (v) in case of an individual proprietor, the proprietor's heirs, administrators, executors and legal representatives; (vi) in case of an individual, the individual's heirs, administrators, executors and legal representatives;</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xxii. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Pre-Payment&quot;</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;means the payment of the Outstanding Balance (or any part thereof) prior to the Scheduled Due Date for the same, according to the procedure prescribed in Article 6 of this Agreement, through any mode as approved by the Lender. The terms ‘Pre-Pay’ ‘Pre-Paid’ and any other cognate expressions thereof shall be construed accordingly.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xxiii. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Prepayment Amount”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;has the meaning ascribed to such term in Article 6.1 of this Agreement.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xxiv. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Prepayment Charges”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;means the charges payable by the Borrower to the Lender in the event of Pre-Payment of the Outstanding Balance (or any part thereof), pursuant to the terms of the Loan Documents.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xxv. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Rate of Interest”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;or “Interest” means the rate at which interest on the Loan is payable by the Borrower to the Lender, as stipulated in the Sanction Letter cum KFS, as amended from time to time with prior intimation to the Borrower.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xxvi. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Repayment Instruments”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;means Post Dated Cheques (“PDC”), Electronic Clearance Service (“ECS”), National Automated Clearing House Mandate (&quot;NACH&quot;) and / or Standing Instructions (“SI”) (i.e., the debit clearing service notiﬁed by the Reserve Bank of India or the National Payments Corporation of India), for which the Borrower has agreed (in writing) to participate in, for facilitating <b>payment of Instalments and/or Bullet payments.</b>
            </span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xxvii. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Repayment Schedule”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;shall mean the repayment schedule of the Instalments and / or bullet payment(s) set out in the Sanction Letter cum Key Fact Statement, being the schedule for repayment of the Instalments and / or bullet payment(s) in relation to the Loan to the Lender.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xxviii. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Receipt”</span>
            </b>
            <span style='font-size:9.0pt;font-family:
               sans-serif;color:black'>&nbsp;means the receipt issued by the Borrower in favor of the Lender, in the form prescribed by the Lender, for acknowledgment of disbursal of the Loan or a part thereof.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xxix. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Sanction Letter cum Key Fact Statement&quot;</span>
            </b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;means the document that include the key terms of the Loan sanctioned by the Lender to the Borrower, in the way clearly understood by the Borrower, bearing reference number as set out in Schedule A and executed by the Borrower and the Lender on the date set out in Schedule A.</span>
         </p>
         <br>
         <img style='margin-top: 80px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xxx. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Security”</span>
            </b>
            <span style='font-size:9.0pt;font-family:
               sans-serif;color:black'>&nbsp;shall have the meaning set out in the Sanction Letter cum KFS and / or the other Loan Documents.</span>
         </p>

         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xxxi. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Security Documents”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;shall mean and include without limitation any documents (including this Agreement) entered into or executed by the Borrower, or any other Person/s for creating and perfecting the Security (if any) to the satisfaction of the Lender.</span>
         </p>
         
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xxxii. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Security Interest”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;shall mean any mortgage, pledge, hypothecation, assignment, deposit arrangement, encumbrance, lien (statutory or other), preference, priority or other security agreement of any kind or nature whatsoever including, without limitation: (i) any conditional sale or other title retention agreement, any ﬁnancing or similar statement or notice ﬁled under any recording or notice statute; and (ii) any designation of loss payees or beneﬁciaries or any similar arrangement under any insurance contract</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xxxiii. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Service Provider”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;shall mean Naman Finlease Private Limited trading as Paisa On Salary, a company incorporated under the provisions of Companies Act, 2013 and having its registered address at  S-370, BASEMENT PANCHSHEEL PARK , NEW DELHI, Delhi, India - 110017 which expression shall, unless it be repugnant to the subject or context thereof, include its successors and permitted assigns) and / or any agents / sub- contractors appointed by the Lender. The Service Provider acts as the authorized agent and representative of the Lender and shall undertake all requisite activities/services as mentioned in the Service Agreement executed between the Service Provider and Lender.</span>
         </p>
         
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xxxiv. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Scheduled Due Date&quot;</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;means each of the dates on which the Bullet payments, Instalments together with Interest, Delayed Payment Charges, Fees, Prepayment Charges, Dishonor Charges, Taxes, Lender Swap Charges and any other costs, charges, expenses if any, are due and payable by the Borrower to the Lender, pursuant to the terms of the Loan Documents.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xxxv. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Standing Instructions”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;and/or “SI” means the written instructions issued by the Borrower to the Approved Bank where the Borrower maintaining account, of an amount equal to the Instalments or bullet payment(s) in favor of the Lender for repayment of the Loan. It may include other/all applicable (re)payment modes, means Post Dated Cheques (“PDC”), Electronic Clearance Service (“ECS”) National Automated Clearing House Mandate (&quot;NACH&quot;) and / or Standing Instructions (“SI”) (i.e. the debit clearing service notiﬁed by the Reserve Bank of India or the National Payments Corporation of India), for which the Borrower has agreed (in writing) to participate in, for facilitating payment of Instalment(s) and / or bullet payment(s)</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:21.3pt;text-align:justify;text-indent:-21.3pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>xxxvi. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>“Terms and Conditions”</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;or&nbsp; <b>“Terms&quot;</b>&nbsp;mean the terms and conditions as contained in this Agreement, including all covenants, representations, and Schedules herein / hereto. </span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>2. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            </b>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>LOAN, RATE OF INTEREST, TAXES ETC.</span>
            </b>
         </p>
         <p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>2.1 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Loan:</span>
            </b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>a) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-family:sans-serif'>The Lender agrees to make available to the Borrower a Loan in the amount specified in the <strong>
            <span style='font-family:sans-serif'>Sanction Letter cum Key Fact Statement (KFS)</span>
            </strong>, subject to and strictly in accordance with the terms and conditions set forth in this Agreement and the other Loan Documents executed by the Borrower. The Borrower hereby accepts and agrees to avail the Loan on the said terms. </span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>b) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The disbursement of the Loan shall be made by the Lender either in a single lump sum or in such instalments as the Lender may determine at its sole discretion.</span>
         </p>
         <br>
         <img style='margin-top: 160px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>c) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The Borrower shall, upon request by the Lender, acknowledge the receipt of each disbursed amount by executing a receipt or any other document in a form acceptable to the Lender.</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         <p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>2.2 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Terms of Disbursement and drawdowns:</span>
            </b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>a) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-family:sans-serif'>Subject to the terms and conditions set forth in this Agreement and the other Loan Documents, the Lender may extend to the Borrower a Loan up to an aggregate principal amount as specified in the <strong>
            <span style='font-family:sans-serif'>Sanction Letter cum Key Fact Statement (KFS)</span>
            </strong>. </span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>b) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-family:sans-serif'>The Borrower agrees and undertakes to utilize the Loan solely for the purpose stated in the <strong>
            <span style='font-family:sans-serif'>Sanction Letter cum KFS</span>
            </strong>, and for no other purpose whatsoever. The Borrower acknowledges that the Lender shall not be under any obligation to monitor or verify the utilization of the Loan amount and shall not be liable in any manner for the Borrower’s use or misuse thereof. </span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>2.3 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Rate of Interest:</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;The Borrower agrees to pay interest and such other penal/additional interest payable on the Loan at such rates as mentioned in the Sanction Letter cum Key Fact Statement.</span>
         </p>
         
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>2.4 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Direct and Indirect Taxes and Duties:</span>
            </b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;The Borrower hereby agrees that it shall be its sole liability to pay any and all taxes, duties or cesses including but not limited to service tax, GST, stamp duty, VAT, duties, and/or all other levies by whatever name called, payable in relation to the Loan provided by the Lender and/or the services provided pursuant hereto and/or any or all the Loan Documents (collectively referred to as “Taxes”). The Borrower shall reimburse to the Lender any such Taxes which may become payable and/ or has been paid by the Lender, as and when called upon to do so by the Lender and if the Borrower fails to pay/reimburse the same, such amounts paid by the Lender shall form part of the principal amount of the Loan Disbursed.</span>
         </p>
         <p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>2.5 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Cooling off Period:</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;The Borrower is allowed a Cooling Off period of 3 days during which the Borrower shall have the option to decide to either continue with Loan or repay the same by paying the entire outstanding Loan amount including interest, charges or any other charges as mentioned in the Sanction Letter cum KFS post which no request for the cancellation of loan shall be entertained by the Lender.</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>3. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            </b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp; <b>PAYMENTS</b>
            </span>
         </p>
         <p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>3.1 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif'>Payment of Instalments and Bullet Payments</span>
            </b>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>a) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The Borrower shall pay to the Lender all Instalments and/or bullet payments (inclusive of Interest), strictly on a monthly basis, on the Scheduled Due Dates, beginning from the first due date and continuing through to the last due date as stipulated in the Sanction Letter cum Key Fact Statement (“Sanction Letter cum KFS”). Time shall be of the essence for such payments. All payments shall be made through Repayment Instruments issued by the Borrower in favor of the Lender.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>b) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The Borrower shall not instruct or request the Lender to withhold or delay the presentation of any Repayment Instrument. Any delay or failure by the Lender or its Service Provider in presenting a Repayment Instrument shall not affect or defer the Borrower’s liability to make timely payments.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>3.2 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif'>Dishonor of Repayment Instruments</span>
            </b>
            <span style='font-size:9.0pt;font-family:
               sans-serif'>
            <br> In the event of any Repayment Instrument being dishonored, the Borrower shall be liable to pay Dishonor Charges as specified in the Sanction Letter cum KFS. This shall be without prejudice to any other rights and remedies available to the Lender under law or the Loan Documents. </span>
         </p>
         <br>
         <img style='margin-top: 80px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>3.3 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif'>Return or Disposal of Repayment Instruments upon Foreclosure</span>
            </b>
            <span style='font-size:9.0pt;font-family:sans-serif'>
            <br> In the event of foreclosure or closure of the Loan, the Lender shall return any unutilized Repayment Instruments held by it within 30 (thirty) days from the date on which the Lender receives a written request from the Borrower. If the Borrower fails to collect such Repayment Instruments within this period, the Lender shall be entitled, at its sole discretion, to destroy them. The Borrower shall not have any claim or right to demand the return of the Repayment Instruments after this period. </span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>3.4 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif'>Right to Recall in Case of Misrepresentation</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif'>
            <br> The Lender shall have the right to immediately recall the entire Outstanding Balance if it discovers that any information furnished by the Borrower—whether oral or written and whether provided in the Loan Documents, Application Form, or otherwise—relating to income, employment, personal details, or any other material fact, is false, misleading, or incorrect. Such right shall be without prejudice to any other rights or remedies available to the Lender under applicable law. </span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>3.5 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif'>Repayment to Collection Account</span>
            </b>
            <span style='font-size:9.0pt;font-family:sans-serif'>
            <br> The Borrower agrees that all Repayment Instruments shall be drawn in favor of the Collection Account, the details of which are set out in <b>Schedule A</b> to this Agreement. The Lender reserves the right to modify the Collection Account by providing the Borrower with not less than 5 (five) days’ written notice. Upon such change, the Borrower shall ensure that all subsequent payments are made to the new Collection Account and shall issue fresh Repayment Instruments accordingly. </span>
         </p>

         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>3.6 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif'>Corporate Borrower – Change in Authorized Signatories</span>
            </b>
            <span style='font-size:
               9.0pt;font-family:sans-serif'>
            <br> Where the Borrower is a corporate entity, it shall not make any change to or revoke any authorization granted to its authorized signatories who have executed the Repayment Instruments, without prior written notice to the Lender. Upon appointing new authorized signatories, the Borrower shall ensure that such signatories deliver replacement Repayment Instruments to the Lender within 3 (three) calendar days of their appointment. </span>
         </p>
         
         <p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>3.7 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif'>Appointment of Agent</span>
            </b>
            <span style='font-size:9.0pt;font-family:sans-serif'>
            <br> The Borrower acknowledges and agrees that <b>Naman Finlease Private Limited</b> has been appointed by the Lender as its authorized agent (“Agent”) for purposes including, but not limited to, disbursement, collection, recovery, and all other incidental matters related to the Loan. The Agent shall act exclusively under the instructions of the Lender. </span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>4. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            </b>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>SECURITY &amp; OTHER TERMS:</span>
            </b>
         </p>
         <p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>4.1 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The Borrower shall execute, in favor of the Lender, a <b>Demand Promissory Note</b> and such other documents as may be required by the Lender, in a form approved by the Lender, covering the full amount of the Loan along with applicable interest. </span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>4.2 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>Any security provided or created by the Borrower under this Agreement and/or any other Loan Documents shall constitute <b>continuing security</b>, remaining in full force and effect until the entire Outstanding Balance and all other dues payable under the Loan Documents are fully repaid and discharged to the satisfaction of the Lender. </span>
         </p>
        
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>4.3 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>Each security interest created under this Agreement or any other Loan Document shall be in addition to, and not in substitution of, any other security, guarantee, lien, indemnity, undertaking, or right that the Lender presently holds or may hold in the future, whether under law or otherwise, in relation to the Outstanding Balance or any part thereof.</span>
         </p>
         <br>
         <img style='margin-top: 130px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>4.4 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The security created under this Agreement or any other Loan Document shall not be merged with, and shall not exclude or be prejudiced by, any other security interest, right of recourse, or remedy (including the invalidity thereof) that the Lender presently holds or may acquire in the future in respect of the Borrower or any other person liable for repayment of the Outstanding Balance.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>4.5 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The security interest created or to be created in favor of the Lender shall be released only upon full repayment of the Outstanding Balance and settlement of all dues and obligations owed by the Borrower, whether under this Agreement or otherwise, including any obligations owed by the Borrower to affiliates or group companies of the Lender.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>4.6 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>Upon occurrence of an Event of Default, and without prejudice to any other rights or remedies available to the Lender under applicable law or the Loan Documents, the Lender shall have the right to:</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>e) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>Declare the entire Outstanding Balance, whether due or payable at such time or not, as <b>immediately due and payable</b>; </span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>f) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>Exercise any and all powers and rights available under the Loan Documents or under applicable law;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>g) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>Undertake any action permitted, expressly or impliedly, by the Loan Documents or applicable legal provisions.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>4.7 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The Borrower acknowledges that the powers granted to the Lender under this clause are for <b>valuable consideration</b>, are <b>coupled with interest</b>, and shall accordingly be <b>irrevocable</b> for as long as any portion of the Outstanding Balance remains unpaid. </span>
         </p>
         <p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>4.8 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The enforcement of any security interest by the Lender shall not affect the Borrower's continuing liability to the Lender for any <b>shortfall or deficiency</b> arising or continuing under this Agreement or any of the Loan Documents in respect of the Outstanding Balance. </span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>5. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            </b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp; <b>OTHER CONDITIONS</b>
            <br>
            <br>
            </span>
         </p>
         <p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:.25in;text-align:justify;text-indent:-.25in;
            line-height:normal;display:none'>
            <b>
            <span style='font-family:sans-serif;
               display:none'>5 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            </b>
            <span style='font-family:sans-serif;
               display:none'>&nbsp;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>5.1 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-family:sans-serif'>The Borrower agrees and undertakes to comply with the following obligations throughout the tenure of the Loan:</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>a) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif'>Change in Contact and Banking Details</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif'>: Promptly notify the Lender of any change in the Borrower’s residential or office address, and/or any modifications to the bank account from which the Repayment Instruments have been issued and associated repayment instructions provided.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>b) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif'>Salary Account Continuity</span>
            </b>
            <span style='font-size:9.0pt;font-family:sans-serif'>: Ensure continued credit of salary into the same bank account from which Repayment Instruments have been issued towards repayment of the Loan. The Borrower shall not modify, close, or redirect salary credits to any other account without obtaining the Lender’s <b>prior written consent</b>. </span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>c) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif'>Change in Employment / Occupation</span>
            </b>
            <span style='font-size:9.0pt;font-family:
               sans-serif'>: Immediately inform the Lender of any change in employment status, including resignation, termination, job switch, or initiation of a new business or profession. Upon such change, the Borrower shall:</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>a) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>Provide the name and address of the new employer, business, and/or professional establishment;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>b) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>Ensure that salary or business income continues to be credited to the designated repayment account, unless otherwise permitted in writing by the Lender. The Lender reserves the right to terminate this Agreement and the other Loan Documents at its sole discretion in the event of such change, if deemed necessary.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>d) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif'>Prohibited Use of Loan</span>
            </b>
            <span style='font-size:9.0pt;font-family:sans-serif'>: Not utilize any part of the Loan amount for illegal activities, immoral purposes, gambling, betting, lottery, races, speculative trading, or any activity of a similar prohibited or unethical nature.</span>
         </p>
         <br>
         <img style='margin-top: 140px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>e) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif'>Regulatory Compliance</span>
            </b>
            <span style='font-size:9.0pt;font-family:sans-serif'>: Obtain and submit all requisite permissions, consents, and approvals to the Lender and/or its service providers, as may be required under applicable law or internal policy, prior to the disbursement of the Loan.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>f) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif'>Acknowledgement of Terms</span>
            </b>
            <span style='font-size:9.0pt;font-family:sans-serif'>: Acknowledge having read and understood all terms and conditions, privacy policy, and relevant documents made available on the websites of both Paisa On Salary (hereinafter referred to as “Paisa On Salary”) and the Lender.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>g) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif'>Acceptance of Online Terms</span>
            </b>
            <span style='font-size:9.0pt;font-family:sans-serif'>: Unconditionally agree to comply with all the terms and conditions, privacy policy, and other legally binding content published on the websites of Paisa On Salary and the Lender, as may be updated from time to time.</span>
         </p>
         <p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>h) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif'>Accuracy of Information</span>
            </b>
            <span style='font-size:9.0pt;font-family:sans-serif'>: Confirm that all personal, financial, and employment-related information provided to Paisa On Salary and the Lender is true, accurate, and complete in all respects. The Borrower undertakes to immediately inform the Lender of any change or inaccuracy discovered.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:14.2pt;line-height:normal'>
            <span style='font-size:9.0pt;
               font-family:sans-serif'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:14.2pt;line-height:normal'>
            <span style='font-size:9.0pt;
               font-family:sans-serif'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>6. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            </b>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>PRE-PAYMENT</span>
            </b>
            <span style='font-size:9.0pt;font-family:
               sans-serif;color:black'>
            <br>
            <br>
            </span>
         </p>
         <p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:.25in;text-align:justify;text-indent:-.25in;
            line-height:normal;display:none'>
            <span style='font-family:sans-serif;
               display:none'>5 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black;display:none'>&nbsp;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:.25in;text-align:justify;text-indent:-.25in;
            line-height:normal;display:none'>
            <span style='font-family:sans-serif;
               display:none'>6 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black;display:none'>&nbsp;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>6.1 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The Borrower may, subject to the prior written approval of the Lender, prepay the entire Outstanding Balance or any part thereof (“Prepayment Amount”) by providing at least one (1) calendar day prior written notice to the Lender, expressing the intention to make such prepayment.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>6.2 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Upon receiving the Lender’s written approval in response to the Borrower’s notice under Clause 6.1, the Borrower shall remit the Prepayment Amount to the Lender within five (5) calendar days from the date of such approval. Failure to make payment within the specified time frame shall render the prepayment request null and void, unless otherwise agreed by the Lender in writing.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>6.3 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Prepayment of the Loan shall be subject to Prepayment Charges as specified in the Sanction Letter cum Key Fact Statement (KFS), as may be amended from time to time. The applicable Prepayment Charges shall be computed on the Prepayment Amount and shall be payable simultaneously with the Prepayment Amount.</span>
         </p>
         
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>6.4 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The Lender shall have the sole and absolute discretion to apply the Prepayment Amount received from the Borrower in the following order of priority:</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>a) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>First, towards any Prepayment Charges, Interest, Delayed Payment Charges, Fees, Dishonor Charges, Taxes, Lender Swap Charges, and all other costs, expenses, or charges payable under the Loan Documents.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>b) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Thereafter, towards the Bullet Payment or Installments, as applicable, or in such other manner as the Lender may deem appropriate.</span>
         </p>
         <p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>6.5 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Upon full and final prepayment of the entire Outstanding Balance, and subject to clearance of all dues, the Lender shall return the Repayment Instruments to the Borrower in accordance with the provisions of relevant Clause of this Agreement.</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <span style='font-family:sans-serif'>7 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp; <b>BORROWER’S REPRESENTATIONS AND WARRANTIES</b>
            <br>
            <br>
            </span>
         </p>
         <p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>7.1 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The Borrower hereby makes the following representations and warranties to the Lender, which shall be deemed to be continuing and shall remain true and binding throughout the tenure of the Loan and until the discharge of all obligations under the Loan Documents:</span>
         </p>
         <br>
         <img style='margin-top: 120px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>a) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The</span>
            <span style='font-size:9.0pt;font-family:sans-serif'> Loan Documents executed and obligations undertaken by the Borrower are valid, binding, and enforceable in accordance with their terms and do not contravene any applicable laws, regulations, or existing contractual obligations of the Borrower.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>b) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The execution, delivery, and performance of the Loan Documents by the Borrower do not and will not:</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:56.7pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>i. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>violate any applicable law, regulation, or judicial or governmental order;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:56.7pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>ii. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>result in a breach or constitute a default under any agreement or instrument to which the Borrower is a party or by which the Borrower or its assets are bound;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:56.7pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>iii. <span style='font:7.0pt '>&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>cause the creation or imposition of any encumbrance on the Borrower’s assets, except as permitted under the Loan Documents;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:56.7pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>iv. <span style='font:7.0pt '>&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>Contravene any provision of the Borrower’s constitutional or governing documents (where applicable).</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>c) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>There are no actions, suits, proceedings, or investigations pending or, to the Borrower’s knowledge, threatened against the Borrower before any court, tribunal, or governmental authority that could materially affect the Borrower’s financial position, the enforceability of the Loan Documents, or the Borrower’s ability to perform its obligations thereunder.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>d) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>Notwithstanding the Repayment Schedule or Loan Tenure, the Lender shall be entitled to demand immediate repayment of the Loan and all Outstanding Balances at any time. Upon such demand, the Borrower shall make immediate payment, and the Lender may adjust any monies lying in any account of the Borrower with the Lender or its branches towards such outstanding dues.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>e) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>Repayment Instruments and Mandates:</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:56.7pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>i. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The Borrower has issued Repayment Instruments with full knowledge that dishonour of the same constitutes an offence under the Negotiable Instruments Act, 1881, and/or the Payment and Settlement Systems Act, 2007, as applicable.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:56.7pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>ii. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>No notice shall be required for the presentation of such instruments.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:56.7pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>iii. <span style='font:7.0pt '>&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The Borrower may authorize payment via direct debit, NACH/ECS, or similar mandates from an Approved Bank account.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>f) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>Where the Borrower’s employer is registered for DAS, repayment shall be made through salary deduction. Upon cessation of employment, the Borrower shall promptly provide an alternate Repayment Instrument from an Approved Bank where the Borrower holds a new salary account.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>g) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The Borrower shall at all times comply with all applicable laws in India, including but not limited to the Prevention of Money Laundering Act, 2002.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>h) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The Borrower is competent to contract and has taken all necessary steps and obtained requisite authorizations to enter into and perform the Loan Documents.</span>
         </p>
         
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>i) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>All information, financial or otherwise, provided by the Borrower is true, complete, and not misleading in any material respect as of the date provided and remains valid unless updated or withdrawn in writing.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>j) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The Borrower is not insolvent, bankrupt, in receivership, or under liquidation and has not taken any steps toward such proceedings.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>k) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The Borrower confirms that they fully understand the English language and agrees that all communication from the Lender in English shall be binding.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>l) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The Borrower has obtained all applicable statutory approvals and consents necessary to execute the Loan Documents and perform obligations thereunder.</span>
         </p>
         <br>
         <img style='margin-top: 150px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>m) <span style='font:7.0pt '></span>
            </span>
            <span style='font-size:
               9.0pt;font-family:sans-serif;color:black'>The</span>
            <span style='font-size:9.0pt;font-family:sans-serif'> Borrower confirms that he/she is gainfully employed at the time of execution of the Loan Documents, is not under notice or intending to resign. In case of resignation, termination, or change in employment, the Borrower shall promptly notify the Lender.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>n) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The Borrower has duly filed all required tax returns and paid all applicable taxes, including stamp duty on Loan Documents, except where disputes are pending in good faith and proper reserves have been made.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>7.2 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>Creditworthiness and Non-Default <br> The Borrower affirms that they: </span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:56.7pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>i. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>are not identified as a willful defaulter;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:56.7pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>ii. <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>are not listed on the Reserve Bank of India’s caution lists or defaulters' databases;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:56.7pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>iii. <span style='font:7.0pt '>&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>are not involved in any activities or investigations concerning terrorism, anti-national behavior, or enforcement proceedings by any statutory authority.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>7.3 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>Undertaking on Lender Policies <br> The Borrower undertakes to keep themselves updated with the Lender's policies and terms from time to time and agrees to abide by the same. </span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>7.4 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>Regular Payments <br> The Borrower warrants timely and regular payments to the Lender as per the terms of the Loan Documents. </span>
         </p>
         <p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>7.5 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>Authority of Signatories (If Applicable) <br> Where the Borrower is an entity (proprietorship, partnership, LLP, or company), all signatories to the Loan Documents are duly authorized to execute the same on behalf of the Borrower. </span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <span style='font-family:sans-serif'>8 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp; <b>EVENTS OF DEFAULT</b>
            <br>
            <br>
            </span>
         </p>
         <p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>8.1</span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>An event of default (“Event of Default”) shall be deemed to have occurred under the Loan Documents, if:</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>a) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The Borrower commits a breach or fails or neglects to perform, keep or observe any of the conditions set forth in any of the Loan Documents;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>b) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Any covenant, representation and/or warranty of the Borrower is found/ proved to be incomplete, false, or incorrect.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>c) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>If the Borrower fails to deliver Repayment Instrument for the Instalments&nbsp;and / or bullet payment(s).</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>d) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Any Repayment Instrument issued by the Borrower in favor of the Lender is dishonored by the Borrower’s bank.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>e) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The Borrower fail/s to pay the Instalments&nbsp;and / or bullet payment(s)&nbsp;or any other payment comprised in the Outstanding Balance on the Scheduled Due Dates.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>f) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Any information supplied by the Borrower in the Application Form, or any other Loan Document is found to be materially untrue, false, misleading, or incorrect.</span>
         </p>
         
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>g) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The Borrower has admitted to any Person in writing that the Borrower is unable to pay his/her debts and / or that the Borrower is willing to be adjudged bankrupt.</span>
         </p>
         <br>
         <img style='margin-top: 210px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>h) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>If the Borrower commit any act of bankruptcy or makes assignment for the beneﬁt of creditors or consents to the appointment of a trustee or receiver in respect of its properties / assets or insolvency proceedings, whether voluntary or otherwise, are instituted against the Borrower:</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>i) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The receiver is appointed in respect of the assets or properties of the Borrower.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>j) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The Borrower, being an individual, has an insolvency notice served on him or is declared insane or is convicted of any offence.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>k) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Any of the Repayment Instrument delivered or to be delivered by the Borrower to the Lender in accordance hereof, are not encashed / acted upon or dishonored for any reason whatsoever, on presentation being made by the Lender.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>l) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Any instruction is given by the Borrower to stop payment on any of the Repayment Instrument.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>m) <span style='font:7.0pt '></span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>If the Borrower fail/s to create Security Interest in accordance with&nbsp;Article 4.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>n) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>If the Borrower commits a default under any other agreement executed with the Lender or any of its affiliates or group companies.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>o) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>If any other event has occurred which in the opinion of the Lender jeopardizes its interest or would make it difficult for the Lender to recover the Loan or the Outstanding Balance if timely action is not taken.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>p) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The occurrence of any event which, in the opinion of the Lender, may jeopardize the Borrower’s ability to pay the Lender’s dues.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>8.2 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Upon occurrence of any of the Events of Default and at any time thereafter, the Lender shall have the right, but not the obligation to declare all sums outstanding under the Loan (i.e., the entire Outstanding Balance), whether due or not, immediately repayable and upon the Borrower failing to make the said payments within 7 (seven) days thereof, the Lender may at its sole discretion</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>a) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Require the Borrower to pay liquidated damages equal to all unpaid Instalments&nbsp; <b>and / or bullet payment(s)</b>&nbsp;which in the absence of a default would have been payable by the Borrower for the full term hereof together with Interest stipulated in the Sanction Letter cum KFS from the date of such declaration till the date of payment and/or realization; and </span>
         </p>
         
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>b) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Exercise any other right or remedy which may be available to the Lender under any applicable law, including the rights available pursuant to the provisions under the loan documents.</span>
         </p>
         <p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-14.15pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>c) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The Borrower shall also be liable for payment of all legal and other costs and expenses resulting from the defaults or the exercise of the Lender’s remedies.</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <span style='font-family:sans-serif'>9 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp; <b>ARTICLE 9: ASSIGNMENT &amp; DELEGATION</b>
            </span>
         </p>
         <p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>9.1 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The Borrower shall not assign, transfer, or otherwise dispose of, whether in whole or in part, the Loan or any of its rights or obligations under the Loan Documents to any third party without the prior written consent of the Lender.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>9.2 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The Borrower expressly agrees and acknowledges that the Lender shall have the absolute and unfettered right, at its sole discretion, to sell, assign, transfer or otherwise dispose of, whether in whole or in part, its rights, title, and interest in the Loan, the Outstanding Balance, or any part thereof, to any third party, on such terms and in such manner as the Lender may deem fit, without the requirement of any notice to or consent of the Borrower.</span>
         </p>
         <br><br><br>
         <img style='margin-top: 120px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>9.3 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The Borrower further acknowledges and agrees that, without prejudice to the Lender’s right to perform any obligations or functions under the Loan Documents directly, the Lender shall be entitled to appoint one or more third parties, including but not limited to service providers, agents, or collection agencies (collectively referred to as “Service Providers”), and to delegate to such parties any of the Lender’s functions, rights, or obligations under the Loan Documents, including without limitation:</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>a) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Collection of all or any portion of the Instalments and/or Outstanding Balance;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>b) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Communication with the Borrower including issuance of demand notices or payment reminders;</span>
         </p>
         
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>c) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Visiting the Borrower’s residence, office, or place of business, or otherwise contacting the Borrower for collection purposes;</span>
         </p>
         
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>d) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Issuing valid receipts, acknowledgments, or discharges on behalf of the Lender in respect of any payments received from the Borrower;</span>
         </p>
         
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>e) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Performing any other lawful acts or functions as may be delegated by the Lender in connection with the administration, monitoring, or enforcement of the Loan.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>9.4 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The Borrower agrees that any such delegation by the Lender shall be binding upon the Borrower and that all acts done or performed by such Service Providers shall be deemed to have been done or performed by the Lender itself.</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <span style='font-family:sans-serif'>10 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp; <b>COMPLIANCE WITH THE REGULATIONS</b>
            </span>
         </p>
         <p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>10.1 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The Borrower and the Lender agree to comply jointly and severally with all applicable laws and regulations from time to time in force including any amendments, modiﬁcation or change thereof which may be attracted and the Borrower shall indemnify the Lender in respect of any loss, claim or expense to the Lender as a result of non-compliance with any such laws and regulations by the Borrower</span>
         </p>
         <p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>10.2 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The amount of Instalments&nbsp; <b>and / or bullet payment(s)</b>&nbsp;shall be increased by incremental taxes, whether sales, excise, security tax or interest tax and other related taxes now levied on this transaction or hereafter to be levied. </span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <span style='font-family:sans-serif'>11 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp; <b>FURTHER ASSURANCES</b>
            </span>
         </p>
         <p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>11.1 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The Borrower shall pay all costs and other expenses incurred by the Lender in enforcing the Loan Documents and/or for recovery of the Loan / Outstanding Balance.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>11.2 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The Borrower undertake/s to indemnify and keep the Lender indemniﬁed in respect of any cost, loss or liability incurred by the Lender as a result of: (a) the occurrence of any Event of Default; (b) Any information supplied by the Borrower in the Application Form or any other Loan Document is found to be materially untrue, false, misleading or incorrect; (c) failure by the Borrower to pay any amount due under the Loan Documents on its Scheduled Due Date; (d) the Borrower failing to comply with the provisions of any applicable laws; and / or (e) breach of any representation, warranty, covenant or undertaking of the Borrower under the terms of the this Agreement or any of the other Loan Documents.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>11.3 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>In addition to the obligations and documents which the Lender expressly require the Borrower to execute, deliver and perform, the Borrower hereby agree to execute, deliver, and perform, such further acts or documents which the Lender may reasonably require, from time to time, to effectuate the purposes of these Terms and Conditions or any of the other Loan Documents.</span>
         </p>
         <br>
         <img style='margin-top: 140px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <span style='font-family:sans-serif'>12 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp; <b>NOTICE / AUTHORISATION</b>
            </span>
         </p>
         <p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>12.1 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Any notice / letter / other communication sent by the Lender to the Borrower shall be sent at the latest address of the Borrower available in the records of the Lender and in English language (which the Borrower acknowledges and accepts) and the same shall be deemed to have been delivered on expiry of 48 hours after it has been sent by registered post / courier / other modes of delivery. Any notice to be sent to the Lender by the Borrower shall be sent by pre-paid Registered A.D. at its registered office or at such other address as may be intimated by the Lender to the Borrower. The Borrower shall immediately intimate the Lender of any change in its office / residential address Borrower.</span>
         </p>
         
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>12.2 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The Borrower hereby authorize the Lender to contact the Borrower in relation to provision of information about new products of the Lender, offers, promotions and also for the purpose of receiving feedback or conducting surveys and hereby expressly agree to exempt such actions for the purpose of ‘Do Not Call / Disturb’ guidelines issued by the Telecom Regulatory and Development Authority or any other authority.</span>
         </p>
         <p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <span style='font-family:sans-serif'>13 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp; <b>CONSENT TO DISCLOSURE</b>
            </span>
         </p>
         <p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>13.1 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The Borrower understands and acknowledges that, as a pre-condition for the grant of the Loan by the Lender, the Lender requires the Borrower’s express consent for the disclosure and use of certain information and data pertaining to the Borrower. Accordingly, the Borrower hereby unconditionally consents and agrees to the disclosure and furnishing by the Lender, to such parties and in such manner as the Lender may deem fit or as may be authorized by the Reserve Bank of India (RBI), of the following:</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>a) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'> any personal, financial, or credit-related information and data relating to the Borrower;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>b) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>information or data relating to the Loan and/or any credit facility availed or to be availed by the Borrower from the Lender;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>c) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>information regarding any obligations undertaken or to be undertaken by the Borrower in relation to such credit facilities;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>d) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>Information regarding any default or non-compliance by the Borrower in the discharge of any of the aforesaid obligations.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>13.2 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The Borrower declares and affirms that all information and data provided by the Borrower to the Lender, whether submitted in writing or communicated orally, is true, complete, accurate, and not misleading in any respect.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>13.3 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The Borrower further agrees and acknowledges that:</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>a) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>the Credit Information Bureau (India) Limited (CIBIL) and/or any other credit information company or agency authorized by the RBI may use and process the said information and data disclosed by the Lender in such manner as may be deemed appropriate by them;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>b) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>CIBIL and/or such other authorized agency may furnish, for consideration, the processed information or any reports, scores, or products derived therefrom to banks, financial institutions, credit grantors, or other registered users, as permitted under applicable law and regulations;</span>
         </p>
         <br><br><br>
         <img style='margin-top: 130px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>c) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>Notwithstanding anything to the contrary contained in this Agreement, the Loan Documents, or any applicable law, the Borrower agrees that in the event of default in repayment of the Loan, any instalment thereof, or any part of the Outstanding Balance on the Scheduled Due Date(s), the Lender and/or the Reserve Bank of India shall have an unqualified right to disclose, report, or publish the name of the Borrower as a “defaulter” in such manner and through such media as they may, in their sole discretion, deem appropriate.</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin:0in;text-align:justify;text-indent:-.25in;
            line-height:normal'>
            <span style='font-family:sans-serif'>14 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp; <b>MISCELLANEOUS</b>
            <br>
            <br>
            </span>
         </p>
         <p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>14.1 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Interpretation:</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'> Unless the subject or context otherwise requires or unless otherwise stated, in this Agreement: </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>Unless the context otherwise requires or unless expressly stated to the contrary, in this Agreement:</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>a) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>References to “Articles”, “Sections”, or “Schedules” are to the Articles, Sections, and Schedules of this Agreement and shall be deemed to include all amendments and modifications thereto</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>b) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>References to any law, statute, regulation, or provision thereof shall include such law, statute, regulation, or provision as amended, re-enacted, substituted, or supplemented from time to time.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>c) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The headings and titles used in this Agreement are for reference and convenience only and shall not affect the construction or interpretation of any provision of this Agreement.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>d) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>Words denoting the singular shall include the plural and vice versa, as the context may require.</span>
         </p>
         
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>e) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>References to any gender shall include all genders—male, female, and neuter—as the context may require.</span>
         </p>
         
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>f) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The Sanction Letter cum Key Fact Statement (KFS), all Schedules, and the Recitals form an integral and binding part of this Agreement and shall be read in conjunction with the provisions hereof.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>g) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>In the event any payment under this Agreement becomes due on a day which is not a Business Day, such payment shall be made on the next succeeding Business Day. Notwithstanding the actual date of payment, the calculation of interest and other charges shall be made with reference to the originally scheduled due date, unless otherwise agreed by the Lender and notified in writing to the Borrower.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>14.2 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Effective Date of Terms:</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'> These Terms and Conditions shall become binding on the Borrower and the Lender on and from the date of execution hereof. These Terms and Conditions shall be in force till all the monies due and payable to the Lender under the Loan Documents as well as all other agreement(s), document(s) that may be subsisting / executed between the Borrower and the Lender are fully paid to the satisfaction of the Lender.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>14.3 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Representatives and Assigns:</span>
            </b>
            <span style='font-size:
               9.0pt;font-family:sans-serif;color:black'> The Borrower, his/her/its/their heirs, legal representatives, executors, administrators, successors, permitted assigns, partner(s) for the time being, including the legal representatives of the deceased partner(s), if any) shall be bound by all the covenants of these Terms and Conditions.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>14.4 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Non-Waiver:</span>
            </b>
            <span style='font-size:9.0pt;font-family:
               sans-serif;color:black'>&nbsp;Any omission or delay on the part of the Lender, in exercising any of rights, powers or remedy accruing to the Lender, upon failure by the Borrower in the due and punctual fulﬁlment of the obligations of the Borrower hereunder, shall not be deemed to constitute a waiver by the Lender of any of its rights to require such due and punctual performance by the Borrower.</span>
         </p>
         <br><br>
         <img style='margin-top: 130px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>14.5 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Governing Law and Jurisdiction:</span>
            </b>
            <span style='font-size:
               9.0pt;font-family:sans-serif;color:black'>&nbsp;These Terms and Conditions shall be construed and governed in all respects, including validity, interpretation, and effect in accordance with the laws of India. All disputes arising out of these Terms and Conditions shall be subject to the exclusive jurisdiction of the courts or tribunals at - New Delhi, India.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>14.6 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Dispute Resolution:</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;Any question, dispute or claim arising out of or in connection with these Terms and Conditions or the other Loan Documents including any question on its existence, validity or termination shall be referred to and ﬁnally adjudicated through arbitration by a sole arbitrator and in accordance with the Arbitration and Conciliation Act, 1996. The sole arbitrator will be appointed by the Lender. The venue for such arbitration shall be New Delhi, India. The arbitration award given by the sole arbitrator appointed as aforesaid shall be ﬁnal and binding on all the parties in connection with any question of facts or law arising in the course of arbitration or with respect to any award made. Further, the present clause shall survive the termination of these Terms and Conditions and the other Loan Documents. The Courts at New Delhi, India shall have exclusive jurisdiction (subject to the arbitration proceedings which are to be also conducted in New Delhi, India) over any or all disputes arising out of this Agreement and the other Loan Documents. The present clause along with the clause for payment of Interest and other Outstanding Balance by the Borrower shall survive the termination of this Agreement and the other Loan Documents.</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>14.7 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Conﬁdentiality:</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;</span>
            <span style='font-size:9.0pt;font-family:sans-serif'>The Lender shall exercise reasonable care and diligence while handling any confidential information pertaining to the Borrower. However, the Borrower hereby acknowledges and agrees that the Lender shall be entitled to disclose, without any further notice or reference to the Borrower, any information or documentation relating to the Borrower, the Loan, or any of the transactions contemplated under the Loan Documents, in the following circumstances:</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:35.45pt;text-align:justify;text-indent:-7.1pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>a) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>to the Lender’s affiliates, group companies, or subsidiaries;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>b) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>to any actual or prospective transferees, assignees, or purchasers of any rights, obligations or interests in the Loan and/or the Outstanding Balance;</span>
         </p>
         
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>c) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>to any regulatory, governmental, judicial, statutory, or quasi-judicial authority, including but not limited to disclosures made in compliance with any law, rule, regulation, directive, order, notification or instruction issued by the Reserve Bank of India or any other governmental authority;</span>
         </p>
         
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>d) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>in connection with the exercise of the Lender’s rights, remedies, powers, or obligations under the Loan Documents;</span>
         </p>
         
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>e) <span style='font:7.0pt '>&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>to the Lender’s employees, directors, officers, advisors, agents, consultants, service providers and/or subcontractors, on a need-to-know basis, for the purpose of fulfilling the Lender’s obligations under the Loan Documents, or for evaluating or pursuing any business relationship with the Borrower;</span>
         </p>
         <p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:42.55pt;text-align:justify;text-indent:-14.2pt;
            line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>f) <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'> to any credit information bureau, credit rating agency, data repository, or other service providers or agencies engaged by the Lender.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:42.55pt;text-align:justify;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:42.55pt;text-align:justify;text-indent:.5pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>In addition to the above, the Lender shall have the right to disclose, without prior notice to the Borrower, all or any information or documentation, including but not limited to:</span>
         </p>
         <p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:1.0in;text-indent:-29.45pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:Symbol'>· <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>personal and financial information and data relating to the Borrower;</span>
         </p>
         <p class=MsoListParagraphCxSpMiddle style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:1.0in;text-indent:-29.45pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:Symbol'>· <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>details of any credit facility availed or proposed to be availed by the Borrower;</span>
         </p>
         <p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:56.7pt;text-indent:-14.15pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:Symbol'>· <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <span style='font-size:9.0pt;font-family:sans-serif'>any default or delay in repayment, or any other non-compliance with the terms of the Loan Documents;</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:42.55pt;text-align:justify;text-indent:.5pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>to any governmental, taxation, regulatory or judicial authority or agency, including but not limited to the Income Tax Department, Credit Rating Agencies, Credit Information Companies (CICs), or any other authority, body, or department, for the purpose of compliance, regulatory reporting, or protection of the Lender’s interests.</span>
         </p>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:42.55pt;text-align:justify;text-indent:.5pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>&nbsp;</span>
         </p>
         <br>
         <img style='margin-top: 120px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
         <p class=MsoNormal style='margin-top:0in;margin-right:0in;margin-bottom:0in;
            margin-left:42.55pt;text-align:justify;text-indent:.5pt;line-height:normal'>
            <span style='font-size:9.0pt;font-family:sans-serif'>The Borrower expressly waives any right to privacy, confidentiality, or non-disclosure with respect to the disclosures set out herein and further waives any right to initiate legal proceedings for defamation, breach of confidentiality, or any related claim in respect of such disclosures made in accordance with this Clause.</span>
         </p>
         <p class=MsoListParagraphCxSpFirst style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>14.8 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Costs and Expenses:</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>&nbsp;The Borrower shall forthwith on demand being made, pay to the Lender, on a full indemnity basis, all costs and expenses (including legal costs) incurred and/or to be incurred by the Lender and / or Service Provider for the enforcement of these Terms and Conditions, the other Loan Documents and realization/recovery of the amount of the Outstanding Balance from the Borrower, if any.</span>
         </p>
         <p class=MsoListParagraphCxSpLast style='margin-top:0in;margin-right:0in;
            margin-bottom:0in;margin-left:28.35pt;text-align:justify;text-indent:-28.35pt;
            line-height:normal'>
            <span style='font-family:sans-serif'>14.9 <span style='font:7.0pt '>&nbsp;&nbsp;&nbsp;&nbsp; </span>
            </span>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>Amendments:</span>
            </b>
            <span style='font-size:9.0pt;font-family:
               sans-serif;color:black'>&nbsp;These Terms and Conditions may be amended by the Lender at any time. Any such amendment shall take effect only prospectively i.e., after the amendment of the Terms and Conditions. However, the Lender shall upon such variation or amendment being made, inform the Borrower in respect of any variation or amendment in the Terms and Conditions and/or other charges as are applicable to the Loan.</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <b>
            <u>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>SCHEDULE A <br> AGREEMENT PARTICULARS </span>
            </u>
            </b>
         </p>
         <table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:100%;margin-left:-.4pt;border-collapse:collapse'>
            <tr>
               <td width=57 style='width:42.55pt;border:solid #DDDDDD 1.0pt;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <b>
                     <u>
                     <span style='font-size:9.0pt;font-family:sans-serif'>S.NO.</span>
                     </u>
                     </b>
                  </p>
               </td>
               <td width=165 style='width:123.85pt;border:solid #DDDDDD 1.0pt;border-left:
                  none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <b>
                     <u>
                     <span style='font-size:9.0pt;font-family:sans-serif'>PARTICULAR</span>
                     </u>
                     </b>
                  </p>
               </td>
               <td width=459 style='width:343.9pt;border:solid #DDDDDD 1.0pt;border-left:
                  none;padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <b>
                     <u>
                     <span style='font-size:9.0pt;font-family:sans-serif'>DETAILS</span>
                     </u>
                     </b>
                  </p>
               </td>
            </tr>
            <tr>
               <td width=57 style='width:42.55pt;border:solid #DDDDDD 1.0pt;border-top:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>1.</span>
                  </p>
               </td>
               <td width=165 style='width:123.85pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Date of Execution</span>
                  </p>
               </td>
               <td width=459 style='width:343.9pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>".$sanction_date."</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td width=57 style='width:42.55pt;border:solid #DDDDDD 1.0pt;border-top:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>2.</span>
                  </p>
               </td>
               <td width=165 style='width:123.85pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Place of Execution</span>
                  </p>
               </td>
               <td width=459 style='width:343.9pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>".REGISTED_ADDRESS."</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td width=57 style='width:42.55pt;border:solid #DDDDDD 1.0pt;border-top:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>3.</span>
                  </p>
               </td>
               <td width=165 style='width:123.85pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Name of the Lender</span>
                  </p>
               </td>
               <td width=459 style='width:343.9pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Aman Fincap Limited</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td width=57 style='width:42.55pt;border:solid #DDDDDD 1.0pt;border-top:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'></td>
               <td width=165 style='width:123.85pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Name of the Service Provider</span>
                  </p>
               </td>
               <td width=459 style='width:343.9pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Naman Finlease Pvt Ltd.</span>
                  </p>
               </td>
            </tr>
        </table>
        <table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:100%;margin-left:-.4pt;border-collapse:collapse'>
        <tr>
               <td width=57 style='width:42.55pt;border:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'></td>
               <td width=165 style='width:123.85pt;border-top:solid #DDDDDD 1.0pt;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Name of the DLA</span>
                  </p>
               </td>
               <td width=459 style='width:343.9pt;border-top:solid #DDDDDD 1.0pt;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Paisa On Salary</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td width=57 style='width:42.55pt;border:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>4.</span>
                  </p>
               </td>
               <td width=165 style='width:123.85pt;border-top:solid #DDDDDD 1.0pt;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Name of the Borrower</span>
                  </p>
               </td>
               <td width=459 style='width:343.9pt;border-top:solid #DDDDDD 1.0pt;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>".$fullname."</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td width=57 style='width:42.55pt;border:solid #DDDDDD 1.0pt;border-top:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>5.</span>
                  </p>
               </td>
               <td width=165 style='width:123.85pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Address of the Borrower</span>
                  </p>
               </td>
               <td width=459 style='width:343.9pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>".$residence_address."</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td width=57 style='width:42.55pt;border:solid #DDDDDD 1.0pt;border-top:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>6.</span>
                  </p>
               </td>
               <td width=165 style='width:123.85pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Name of the Employer</span>
                  </p>
               </td>
               <td width=459 style='width:343.9pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>".$employmentDetails->employer_name."</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td width=57 style='width:42.55pt;border:solid #DDDDDD 1.0pt;border-top:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>7.</span>
                  </p>
               </td>
               <td width=165 style='width:123.85pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Address of the Employer</span>
                  </p>
               </td>
               <td width=459 style='width:343.9pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>".($employmentDetails->emp_house) . ", ". ($employmentDetails->emp_street) . ", " . ($employmentDetails->emp_landmark) . ", " . ($employmentDetails->emp_city) . " - " . ($employmentDetails->emp_pincode)."</span>
                  </p>
               </td>
            </tr>
        </table>
        <br>
        <img style='margin-top: 200px;' src='".$letterfooter_url."' width='100%'>
         <br>
         <img src='".$letterhead_url."' width='100%'>
         <br>
        <table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='width:100%;margin-left:-.4pt;border-collapse:collapse'>
            <tr>
               <td width=57 style='width:42.55pt;border:solid #DDDDDD 1.0pt;border-top:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>8.</span>
                  </p>
               </td>
               <td width=165 style='width:123.85pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Reference number of Sanction Letter cum KFS</span>
                  </p>
               </td>
               <td width=459 style='width:343.9pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>".$loan_no."</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td width=57 style='width:42.55pt;border:solid #DDDDDD 1.0pt;border-top:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>9.</span>
                  </p>
               </td>
               <td width=165 style='width:123.85pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Date of Execution of Sanction Letter cum KFS</span>
                  </p>
               </td>
               <td width=459 style='width:343.9pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>".$sanction_date."</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td width=57 style='width:42.55pt;border:solid #DDDDDD 1.0pt;border-top:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>10.</span>
                  </p>
               </td>
               <td width=165 style='width:123.85pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Bank Account Details</span>
                  </p>
               </td>
               <td width=459 style='width:343.9pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>".$bankDetails->account."</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td width=57 style='width:42.55pt;border:solid #DDDDDD 1.0pt;border-top:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>11.</span>
                  </p>
               </td>
               <td width=165 style='width:123.85pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Sanctioned Loan Amount</span>
                  </p>
               </td>
               <td width=459 style='width:343.9pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>INR ". number_format(round($camDetails->loan_recommended, 0), 2)."</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td width=57 style='width:42.55pt;border:solid #DDDDDD 1.0pt;border-top:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>12.</span>
                  </p>
               </td>
               <td width=165 style='width:123.85pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Interest Rate</span>
                  </p>
               </td>
               <td width=459 style='width:343.9pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Interest on the Loan: Interest on the Loan: ".number_format($camDetails->roi, 2)."% from the date of disbursal until the date of repayment. In the event of failure to make payment on due date, a penal interest of ".round(($roi * 2), 2)."% shall be applicable from the due date until the date of payment.</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td width=57 style='width:42.55pt;border:solid #DDDDDD 1.0pt;border-top:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>13.</span>
                  </p>
               </td>
               <td width=165 style='width:123.85pt;border-top:solid #DDDDDD 1.0pt;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Processing Fee</span>
                  </p>
               </td>
               <td width=459 style='width:343.9pt;border-top:solid #DDDDDD 1.0pt;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>INR ".number_format(round($camDetails->admin_fee, 0), 2)."</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td width=57 style='width:42.55pt;border:solid #DDDDDD 1.0pt;border-top:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>14.</span>
                  </p>
               </td>
               <td width=165 style='width:123.85pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Late Fee</span>
                  </p>
               </td>
               <td width=459 style='width:343.9pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>In case of delay in payment of Amount Due, Lender shall charge late payment charges of double of Interest rates. </span>
                  </p>
               </td>
            </tr>
            <tr>
               <td width=57 style='width:42.55pt;border:solid #DDDDDD 1.0pt;border-top:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>15.</span>
                  </p>
               </td>
               <td width=165 style='width:123.85pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Maximum Interest Rate</span>
                  </p>
               </td>
               <td width=459 style='width:343.9pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>The Interest rate on the Loan shall not exceed 50% per Month.</span>
                  </p>
               </td>
            </tr>
            <tr>
               <td width=57 style='width:42.55pt;border:solid #DDDDDD 1.0pt;border-top:none;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>16.</span>
                  </p>
               </td>
               <td width=165 style='width:123.85pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>Cooling Period</span>
                  </p>
               </td>
               <td width=459 style='width:343.9pt;border-top:none;border-left:none;
                  border-bottom:solid #DDDDDD 1.0pt;border-right:solid #DDDDDD 1.0pt;
                  padding:6.0pt 6.0pt 6.0pt 6.0pt'>
                  <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
                     normal'>
                     <span style='font-size:9.0pt;font-family:sans-serif'>3 days</span>
                  </p>
               </td>
            </tr>
         </table>
         <br><br>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>The Lender, may at its sole discretion, waive or reduce the charges as mentioned above, on a case-to-case basis.</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>&nbsp;</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>IN WITNESS WHEREOF&nbsp;</span>
            </b>
            <span style='font-size:9.0pt;
               font-family:sans-serif;color:black'>the Borrower hereby accepts the terms and conditions this Agreement.</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>I/we, the Borrower acknowledge that I/we are well versed in and fully understand English language, a copy of the Loan Documents has been furnished to me/us in English language, as specially requested by me/us and that I/we have read and have understood the contents of the Loan Documents and hereby agree, accept, and undertake to abide by the same</span>
            </b>
         </p>
         <!--<p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>____________________________________________</span>
            <span style='font-size:37.5pt;font-family:'Segoe UI Symbol',sans-serif;color:black'>✍</span>
         </p>
         <p class=MsoNormal style='margin-bottom:0in;text-align:justify;line-height:
            normal'>
            <b>
            <span style='font-size:9.0pt;font-family:sans-serif;
               color:black'>SIGNED AND DELIVERED by the within named Borrower</span>
            </b>
         </p> -->
         <br><br><br><br><br><br><br>
         <img style='margin-top: 120px;' src='".$letterfooter_url."' width='100%'>
      </div>
      <!-- Loan Agreement END -->
   </body>
</html>";

    //    print_r($html_string);
    //    exit;             
                    
            $file_name = "sanction_letter_" . $lead_id . "_" . rand(1000, 9999) . ".pdf";
            

            if (LMS_DOC_S3_FLAG == true) {
                $file_path_with_name = TEMP_UPLOAD_PATH . $file_name;
            } else {
                $file_path_with_name = UPLOAD_PATH . $file_name;
            }

            require_once __DIR__ . '/../../vendor/autoload.php';

            $mpdf = new \Mpdf\Mpdf([
                'tempDir' => sys_get_temp_dir(), // or '/tmp'
                'debug' => false,
                'showImageErrors' => false,
            ]);

            $mpdf->WriteHTML($html_string_new);

            $mpdf->Output($file_path_with_name, 'F');

            if (file_exists($file_path_with_name)) {

                if (LMS_DOC_S3_FLAG == true) {
                    $upload_return = uploadDocument($file_path_with_name, $lead_id, 2, 'pdf');
                    $file_name = $upload_return['file_name'];
                    unlink($file_path_with_name);
                }

                $return_array['status'] = 1;
                $return_array['file_name'] = $file_name;
                $this->updateLeads(['lead_id' => $lead_id], ['cam_sanction_letter_file_name' => $file_name], 'credit_analysis_memo');
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
        $adminFeeWithoutGst = round(($admin_fee / 1.18));
        $gst = $admin_fee - $adminFeeWithoutGst;
        // $gst = round(($admin_fee * 18) / 100);
        // $total_admin_fee = round($admin_fee + $gst);
        $total_admin_fee = round($admin_fee - $gst);
        $repayment_amount = ($loan_recommended + ($loan_recommended * $roi * $tenure) / 100);

        $data['roi'] = $roi;
        $data['panel_roi'] = ($roi * 2);
        $data['tenure'] = $tenure;
        $data['repayment_amount'] = round($repayment_amount);
        // $data['admin_fee'] = $total_admin_fee;
        $data['admin_fee'] = $admin_fee;
        $data['adminFeeWithGST'] = $gst;
        $data['adminFeeGST'] = $gst;
        // $data['total_admin_fee'] = $admin_fee;
        $data['total_admin_fee'] = $total_admin_fee;
        $data['net_disbursal_amount'] = $loan_recommended - $total_admin_fee;
        $data['final_foir_percentage'] = number_format((($loan_recommended + $obligations) / $monthly_salary) * 100, 2);
        $data['foir_enhanced_by'] = number_format($data['final_foir_percentage'] - $eligible_foir_percentage, 2);

        return $data;
    }

    public function getDisbursementTransLogs($lead_id) {
        return $this->db->select("*")->from("lead_disbursement_trans_log")->where(['disb_trans_lead_id' => $lead_id, 'disb_trans_active' => 1, 'disb_trans_deleted' => 0])->order_by('disb_trans_id', 'desc')->get()->row_array();
    }

    public function nocSettledPayment($lead_id) {


        $conditions = ['LD.lead_id' => $lead_id, 'LD.lead_status_id' => 17, 'CO.collection_active' => 1, 'CO.collection_deleted' => 0];

        $res = $this->db->select('L.loan_no, CAM.loan_recommended, L.loan_noc_settlement_letter, L.loan_settled_date, CAM.disbursal_date, LC.first_name, LC.middle_name, LC.sur_name, LD.email, LD.lead_status_id, CO.date_of_recived,  CO.closure_payment_updated_on, CO.discount')
            ->from('leads LD')
            ->join('credit_analysis_memo CAM', 'CAM.lead_id = LD.lead_id', 'INNER')
            ->join('loan L', 'L.lead_id = LD.lead_id', 'INNER')
            ->join('lead_customer LC', 'LC.customer_lead_id = LD.lead_id', 'INNER')
            ->join('collection CO', 'CO.lead_id = LD.lead_id', 'INNER')
            ->where('LD.lead_id', $lead_id)
            ->where('LD.lead_status_id', 17)
            ->get();

        // $result = $sql->row();
        //     $result = $this->db->select('L.loan_no, CAM.loan_recommended, L.loan_noc_settlement_letter, L.loan_settled_date, CAM.disbursal_date, LC.first_name, LC.middle_name, LC.sur_name, LD.email, LD.lead_status_id, CO.date_of_recived,  CO.closure_payment_updated_on, CO.discount')
        //             // ->where('CO.repayment_type', 17) // SETTLED
        //             ->where($conditions)
        //             ->from('leads LD')
        //             ->join('credit_analysis_memo CAM', 'CAM.lead_id = LD.lead_id', 'INNER')
        //             ->join('loan L', 'L.lead_id = LD.lead_id', 'INNER')
        //             ->join('lead_customer LC', 'LC.customer_lead_id = LD.lead_id', 'INNER')
        //             ->join('collection CO', 'CO.lead_id = LD.lead_id', 'INNER');
        //     print_r($result); die;
        //     $res = $result->get();


        if ($res->num_rows() > 0) {
            $sql = $res->row();

            $lead_status_id = $sql->lead_status_id;
            $loan_no = $sql->loan_no;
            $customer_name = $sql->first_name;
            $middle_name = $sql->middle_name;
            $sur_name = $sql->sur_name;
            $full_name = $customer_name . ' ' . $middle_name . ' ' . $sur_name;
            $customer_email = $sql->email;
            $loan_recommended = number_format($sql->loan_recommended, 2);
            $disbursal_date = date('d-m-Y', strtotime($sql->disbursal_date));
            $date_of_recived = date('d-m-Y', strtotime($sql->date_of_recived));
            // $loanCloserDate = date('d-m-Y', strtotime($sql->closure_payment_updated_on));
            $loanCloserDate =  date('d-m-Y', strtotime($sql->loan_settled_date));
            $discount = number_format($sql->discount, 2);

            $conditions3 = ['payment_verification' => 1, 'lead_id' => $lead_id, 'collection_active' => 1, 'collection_deleted' => 0];
            $sql13 = $this->db->select('SUM(received_amount) as total_paid')->where($conditions3)->from('collection')->get();
            $recoveredAmount = $sql13->row();

            $ReceivedAmount = 0;
            if ($recoveredAmount->total_paid > 0) {
                $ReceivedAmount = number_format($recoveredAmount->total_paid, 2);
            }

            if ($date_of_recived == '01-01-1970') {
                $date_of_recived = date("d-m-Y");
            }

            $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns="http://www.w3.org/1999/xhtml">
                        <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>Settlement Letter</title>
                        </head>
                        <body>
                        <table width="778" border="0" align="center" cellpadding="0" cellspacing="0" style="padding:10px; border:solid 1px #ccc; font-family:Arial, Helvetica, sans-serif;">
                        <tr>
                            <td width="404" align="left"><img src="'. LMS_COMPANY_LOGO .'" alt="logo" style=" border-radius: 5px;width: 180px;"/></td>
                            <td width="4" align="left">&nbsp;</td>
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
                            <td align="left" valign="top"><strong style="line-height:25px;">Mr/Ms ' . $full_name . '</strong></td>
                            <td>&nbsp;</td>
                        </tr>
                        <br/><br/>
                        <tr>
                            <td align="left" valign="top"><span style="font-size:17px;
                            line-height: 25px;
                            padding-bottom: 6px; text-align:justify;">This is to certify that Mr/Ms <strong>' . $full_name . '</strong> who had taken a short-term loan from<strong> ' . COMPANY_NAME . '</strong> for Rs ' . $loan_recommended . ' on ' . $disbursal_date . '.<br/><br/>We have received Rs. ' . $ReceivedAmount . ' from your total loan outstanding and the same has been settled on ' . $loanCloserDate . '.<br/><br/>For Closure of your loan, kindly pay the closure amount of Rs ' . $discount . '.</span> </td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td align="left" valign="top"><p>This is the amount which we are giving you after discount . If you want to close your loan or remove your settlement status from CIBIL, Kindly pay the closure amount.<br />
                            </p></td>
                            <td>&nbsp;</td>
                        </tr>

                         <tr>
                            <td><strong>' . COMPANY_NAME . ' </strong></td>
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
                        <!--<tr>
                            <td><span style="font-size:17px;line-height: 25px;padding-bottom: 6px; text-align:justify; margin:25px 0px;">
                            <a href="' . APPLE_STORE_LINK . '" target="_blank"><img style="width: 10%;margin-left: 0%;" src="' . APPLE_STORE_ICON . '"></a></span>
                            <span style="font-size:17px;line-height: 25px;padding-bottom: 6px; text-align:justify; margin:10px 0px;"><a href="' . ANDROID_STORE_LINK . '" target="_blank"><img style="width: 10%;margin-left: 0%;" src="' . ANDROID_STORE_ICON . '"></a></span>
                            </td>
                        </tr>-->

                        <tr>
                            <td align="left">&nbsp;</td>
                        </tr>
                        </table>
                        </body>
                    </html>
                ';



            $subject = 'NOC Letter Case Settled';

            $file_name = "Noc_settlement_letter_" . $lead_id . "_" . date('Ymd') . ".pdf";

            $file_path_with_name = UPLOAD_SETTLEMENT_PATH . $file_name;

            require_once __DIR__ . '/../../vendor/autoload.php';

            $mpdf = new \Mpdf\Mpdf();

            $mpdf->WriteHTML($message);


            $mpdf->Output($file_path_with_name, 'F');

            if (file_exists($file_path_with_name)) {

                // $upload_return = uploadDocument($file_path_with_name, $lead_id, 2, 'pdf');
                require_once(COMPONENT_PATH . 'includes/functions.inc.php');

                $return_array = common_send_email($customer_email, $subject, $message, "", $cc_mail = "info@paisaonsalary.in", $from_email, "", $file_path_with_name, $file_name, 'Settlement_letter.pdf');

                if (LMS_DOC_S3_FLAG == true) {
                    $upload_return = uploadDocument($file_path_with_name, $lead_id, 2, 'pdf');
                    $file_name = $upload_return['file_name'];
                    unlink($file_path_with_name);
                }


                $return_array['status'] = 1;
                $return_array['msg'] = "Settlement Letter Send Successfully";

                $update_settlement_letter = ['loan_noc_settlement_letter' => $file_name];
                $this->db->where('lead_id', $lead_id)->update('loan', $update_settlement_letter);

                $insertApiLog = array(
                    'created_on ' => date('Y-m-d H:i:s'),
                    'status' => $leadstatus,
                    'stage' => $leadStage,
                    'user_id' => $_SESSION['isUserSession']['user_id'],
                    'lead_id' => $lead_id,
                    'lead_followup_status_id' => $lead_status_id,
                    'reason' => "Settlement Letter Send successfully"
                );
                $this->db->insert('lead_followup', $insertApiLog);
                $this->db->insert_id();
            } else {
                $return_array['errors'] = "File does not exist. Please check offline";
            }

            echo json_encode($return_array);
            die;
        }
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

        require_once(COMPONENT_PATH . 'includes/functions.inc.php');

        $return_array = common_send_email($to_email, $subject, $message, $bcc_email, $cc_email);
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

        $return_array = array();

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
            //print_r($sql); die;
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


    public function nocRecoveryPaymentLoan($lead_id) {
        $leadId = $this->encrypt->decode($lead_id);
        $sql = "SELECT DISTINCT LD.loan_no, CB.account, LD.lead_status_id, CAM.loan_recommended AS loan_amount, MCI.m_city_name, MSI.m_state_name, LD.pincode,
        CONCAT_WS(' ', LC.first_name, LC.middle_name, LC.sur_name) AS customer_name, LD.email, CAM.disbursal_date AS loaninitiatedDate, LD.mobile,
        LC.customer_digital_ekyc_flag, LC.customer_digital_ekyc_done_on, CAM.repayment_date, LC.father_name, LC.current_house, LC.current_locality,
        CAM.repayment_amount, DATEDIFF(CURDATE(), CAM.repayment_date) AS days_from_repayment, CAM.disbursal_date, L.loan_noc_closed_letter_datetime,
        L.loan_noc_closing_letter FROM leads LD
        INNER JOIN lead_customer LC ON LC.customer_lead_id = LD.lead_id
        INNER JOIN loan L ON L.lead_id = LD.lead_id
        INNER JOIN master_city MCI ON MCI.m_city_id = LD.city_id
        INNER JOIN master_state MSI ON MSI.m_state_id = LD.state_id
        INNER JOIN master_pincode MP ON MP.m_pincode_value = LD.pincode
        INNER JOIN customer_banking CB ON CB.lead_id = LD.lead_id
        INNER JOIN credit_analysis_memo CAM ON CAM.lead_id = LD.lead_id
        WHERE LD.lead_id = $leadId AND DATEDIFF(CURDATE(), CAM.repayment_date) > 60 AND CB.account !=''";

        $sql = $this->db->query($sql)->row();

        // /print_r($sql); die;
        $fathname = ($sql->father_name ? $sql->father_name : 'Not Provided');

        $to = $sql->email;
        //$to = 'info@paisaonsalary.in';

        $current_date = date('d, M Y');
        $loan_disbursal_date = date("F Y", strtotime($sql->disbursal_date));

        if (!empty($to)) {
            $query = $this->db->select_sum('received_amount')->where(['payment_verification' => 1, 'collection_active' => 1, 'collection_deleted' => 0])->where('lead_id', $lead_id)->from('collection')->get()->row();

            $lead_status_id = $sql->lead_status_id;
            $loanCloserDate = date("d-m-Y", strtotime($sql->closure_payment_updated_on));
            $customer_name = $sql->customer_name;
            $repayment_date = $sql->repayment_date;
            $loan_recommended = $sql->loan_recommended;

            $loanInitiatedDate = date("d-M-Y", strtotime($sql->loaninitiatedDate));

            $date_of_recived = date("d-m-Y", ($sql->loan_noc_closed_letter_datetime));

            $loan_amount = number_format($sql->loan_amount, 2);
            $received_amount = number_format($query->received_amount, 2);

            $sent_pre_approved_type = 0;

            $expire_date = date("Y-m-d", strtotime('+10 days', strtotime($repayment_date)));

            if (strtotime($expire_date) > strtotime($sql->loan_noc_closed_letter_datetime) && $sql->customer_digital_ekyc_flag == 1) {

                $sent_pre_approved_type = 1;
                $camp_kyc_date = strtotime(date("Y-m-d", strtotime('+85 day', strtotime($sql->customer_digital_ekyc_done_on))));

                $camp_current_datetime = strtotime(date("Y-m-d"));

                if ($camp_kyc_date > $camp_current_datetime) {
                    $sent_pre_approved_type = 2;
                }
            }
            $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns="http://www.w3.org/1999/xhtml">
               <head>
                  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

                  <meta name="author" content="Salarywalle"/>
                  <style type="text/css"> * {margin:0; padding:0; text-indent:0; }
                     h1 { color: black; font-family:"Times New Roman", serif; font-style: normal; font-weight: bold; text-decoration: none; font-size: 20pt; }
                     .s1 { color: black; font-family:"Times New Roman", serif; font-style: normal; font-weight: bold; text-decoration: none; font-size: 18pt; }
                     .s2 { color: black; font-family:"Times New Roman", serif; font-style: normal; font-weight: bold; text-decoration: none; font-size: 18pt; }
                      p { color: black; font-family:"Times New Roman", serif; font-style: normal; font-weight: normal; text-decoration: none; font-size: 18pt; margin:0pt; }
                     .s3 { color: #454545; font-family:"Times New Roman", serif; font-style: normal; font-weight: normal; text-decoration: none; font-size: 18pt; }
                     .s4 { color: #454545; font-family:"Times New Roman", serif; font-style: normal; font-weight: normal; text-decoration: none; font-size: 18pt; }
                     a { color: #0462C1; font-family:"Times New Roman", serif; font-style: normal; font-weight: normal; text-decoration: underline; font-size: 18pt; }
                     h2 { color: black; font-family:"Times New Roman", serif; font-style: normal; font-weight: bold; text-decoration: underline; font-size: 18pt; }
                     li {display: block; }
                     img.CToWUd{position:absolute !important;}
                     #l1 {padding-left: 0pt;counter-reset: c1 1; }
                     #l1> li>*:first-child:before {counter-increment: c1; content: counter(c1, decimal)". "; color: black; font-family:"Times New Roman", serif; font-style: normal; font-weight: normal; text-decoration: none; font-size: 14pt; }
                     #l1> li:first-child>*:first-child:before {counter-increment: c1 0;  }

                  </style>
               </head>
               <body style="width: 100%;max-width: 720px;margin: 0 auto;">

                  <table cellspacing="0" cellpadding="0"><tr><td><img src="' . New_HEADER_BG . '"/></td></tr></table>
                  <p class="s2" style="padding-left: 153pt;text-indent: 0pt;text-align: left;">Speed Post        <span style="float:right">DATED: ' . $current_date . '</span></p>
                  <p style="padding-top: 9pt;text-indent: 0pt;text-align: left;"><br/></p>
                  <p style="padding-top: 4pt;padding-left: 5pt;text-indent: 0pt;text-align: left;">TO,</p>
                  <p style="text-indent: 0pt;text-align: left;"><br/></p>
                  <p style="padding-left: 5pt;text-indent: 0pt;line-height: 114%;text-align: left;text-decoration: none !important">MR./ MRs. / Ms.  ' . $sql->customer_name . ' S/O- SH. ' . $fathname . '</p>
                  <p style="padding-left: 5pt;text-indent: 0pt;line-height: 14pt;text-align: left;"><span class="p" style="text-decoration: none !important;color:#000 !important;">R/O: - ' . $sql->current_house . ', </span><span class="s3" style=" background-color: #ECEBEB;">' . $sql->current_locality . '</span></p>
                  <p style="padding-left: 5pt;text-indent: 0pt;text-align: left;"><span class="s3" style=" background-color: #ECEBEB;">' . $sql->m_state_name . ', ' . $sql->m_city_name . ' ' . $sql->m_pincode_value . '</span><span class="s4"> </span><span class="s3" style=" background-color: #ECEBEB;">Mob. No. ' . $sql->mobile . '</span></p>
                  <p style="padding-left: 5pt;text-indent: 0pt;text-align: left;"><a href="mailto:' . $sql->email . '" style=" color: black; font-family:&quot;Times New Roman&quot;, serif; font-style: normal; font-weight: normal; text-decoration: none; font-size: 14pt;" target="_blank">Email: - </a><a href="mailto:' . $sql->email . '" target="_blank">"' . $sql->email . '"</a></p>
                  <p style="text-indent: 0pt;text-align: left;"><br/></p>
                  <p class="s2" style="padding-left: 22pt;text-indent: 0pt;line-height: 149%;text-align: center;"><u>LEGAL DEMAND NOTICE OF RECOVERY FOR A SUM OF Rs.</u> <u> "' . $sql->repayment_amount . '"/- (Without Prejudice).</u></p>
                  <p style="text-indent: 0pt;text-align: left;"><br/></p>
                  <p style="padding-left: 5pt;text-indent: 0pt;text-align: left;">Dear Sir/Madam,</p>
                  <p style="text-indent: 0pt;text-align: left;"><br/></p>
                  <p style="padding-left: 5pt;text-indent: 98pt;line-height: 150%;text-align: justify;">Under the instructions received from and on behalf of my client (<u><b>"' . COMPANY_NAME . '" WITH THE</b></u><b> </b><u><b>BRAND NAME "' . BRAND_NAME . '" (Paisa On Salary), </b></u><u>(HEREINAFTER CALLED AS</u></p>
                  <p style="padding-left: 5pt;text-indent: 0pt;line-height: 149%;text-align: left;"><u>MY CLIENT),</u> through its AR/ authorized signatory, I hereby serve/call upon you through this legal notice as following: -</p>
                  <ol id="l1">
                     <li data-list-text="1.">
                        <p style="padding-top: 3pt;padding-left: 41pt;text-indent: -18pt;line-height: 150%;text-align: justify;">That my client is a registered company under the company act and having its registered office at "' . REGISTED_ADDRESS . '". That my client is running the business of financial services to provide loan to people who is in need of money for the short terms under the mutually agreed rate of interest.</p>
                     </li>
                     <li data-list-text="2.">
                        <p style="padding-top: 12pt;padding-left: 41pt;text-indent: -18pt;line-height: 150%;text-align: justify;">That you the addressee in the month of ' . $loan_disbursal_date . ' approached to my client to give you a loan of Rs. "' . $sql->loan_amount . '"/- ) for your basic and personal needs.</p>
                     </li>
                     <li data-list-text="3.">
                        <p style="padding-top: 12pt;padding-left: 41pt;text-indent: -18pt;line-height: 150%;text-align: justify;">That my client on your request/application after furnishing the documents gave you a personal loan for your basic and personal needs of Rs. "' . $sql->loan_amount . '"/-) on dated ' . $sql->loaninitiatedDate . '.</p>
                     </li>
                     <li data-list-text="4.">
                        <p style="padding-top: 11pt;padding-left: 41pt;text-indent: -18pt;line-height: 151%;text-align: justify;">That you the addressee have to return/repay this loan amount alongwith interest on or before "' . $sql->repayment_date . '".</p>
                     </li>
                     <li data-list-text="5.">
                        <p style="padding-left: 41pt;text-indent: -18pt;line-height: 150%;text-align: justify;"><span class="p">That you the addressee at the time of taking loan assured to my client that you shall repay the entire loan amount alongwith interest within specified time i.e. "' . $sql->repayment_date . '". That my client believing upon your words gave you a loan of Rs. ' . $sql->loan_amount . '/- on dated ' . $sql->loaninitiatedDate . ' and transfer the above said amount through NEFT from his account  to  your  account  no.  </span><span class="s3" style=" background-color: #ECEBEB;"></span><span class="s4">' . $sql->account . '</span> <span class="p">with  loan  no. ' . $sql->loan_no . '.</span></p>
                        <p style="padding-top: 8pt;text-indent: 0pt;text-align: left;"><br/></p>
                     </li>
                     <li data-list-text="6.">
                        <p style="padding-left: 41pt;text-indent: -18pt;line-height: 149%;text-align: justify;">That when my client after expire of schedule time of loan has demanded his amount back then you the addressee has started to show your color of cheating and always avoid the genuine request of my client with one pretext to another and you the addressee did not make repayment to my client till date.</p>
                     </li>
                     <li data-list-text="7.">
                        <p style="padding-top: 3pt;padding-left: 41pt;text-indent: -18pt;line-height: 150%;text-align: justify;">That when my client continues with his efforts then finally you the addressee requested to my client to give you more time of one month to make the repayment. That on believing your words in good faith my client accepted your request and gave you more time of one month for making the said repayment.</p>
                     </li>
                     <li data-list-text="8.">
                        <p style="padding-top: 6pt;padding-left: 41pt;text-indent: -18pt;line-height: 150%;text-align: justify;">That after this when my client again contacts you for making the repayment but you the addressee did not give the satisfactory reply and this time also you the addressee did not fulfill your promise and till date you the addressee did not pay even a single penny to my client and your promise remains unfulfilled this time also.</p>
                     </li>
                     <li data-list-text="9.">
                        <p style="padding-top: 6pt;padding-left: 41pt;text-indent: -18pt;line-height: 150%;text-align: justify;">That you the addressee till date did not pay any single penny to my client and my client sent you a number of reminders telephonically and my client’s executives also made personal visits for receiving the repayment the loan amount along with interest but you the addressee did not pay any heed towards my client’s request and now you the addressee stopped to receive the phone calls of my clients.</p>
                     </li>
                     <li data-list-text="10.">
                        <p style="padding-top: 6pt;padding-left: 41pt;text-indent: -18pt;line-height: 149%;text-align: justify;">That even though my client helped you in the good faith but you the addressee destroy the faith of my client for this amount.</p>
                     </li>
                     <li data-list-text="11.">
                        <p style="padding-top: 6pt;padding-left: 41pt;text-indent: -18pt;line-height: 150%;text-align: justify;">That your assurance and promise remained fail and now my client have no alternate except to send this legal notice through his counsel. It seems that you the addressee have turned dishonest and you don’t want to make the payments of my client.</p>
                     </li>
                     <li data-list-text="12.">
                        <p style="padding-top: 6pt;padding-left: 41pt;text-indent: -18pt;line-height: 150%;text-align: justify;">That it is further relevant to mentioned here that at the time of taking the loan you the addressee assured to my client that you the addressee will definitely repay the entire loan amount alongwith its interest on time but you remained fails to keep your promise of repayment of loaned amount.</p>
                     </li>
                     <li data-list-text="13.">
                        <p style="padding-top: 3pt;padding-left: 41pt;text-indent: -18pt;line-height: 150%;text-align: justify;">That now it has become crystal clear from your act and conduct that you the addressee turned dishonest and with fraudulent intention did not want to make the repayment of my client. That it seems you want to digest hard earnest money of my client and breach the trust of my client for this amount. Hence, you have committed an offence punishable under section 316 and 318 of the Bhartiya Nyaya Sanhita 2023 as amended up to date.</p>
                     </li>
                  </ol>
                  <p style="padding-top: 8pt;text-indent: 0pt;text-align: left;"><br/></p>
                  <p style="padding-left: 41pt;text-indent: 137pt;line-height: 150%;text-align: justify;">By virtue of this notice, I hereby call upon you through this legal notice to pay the amount of Rs. "' . $sql->repayment_amount . '"/-  to my client within 7 days of the receipt of this legal notice, failing which I have definite instruction from my client to take the appropriate legal action against you before the court of  law and to file criminal complaint under section <b>316, 318, 337 read with section 338 of the Bhartiya Nyaya Sanhita 2023 </b>as amended up to date under the above provisions, failing which you are liable to punished with punishment from a term of which may extent to five years or with fine or with both and also to seek other legal remedies including the recovery of total outstanding loan amount along with interest.</p>
                  <p style="padding-top: 10pt;padding-left: 41pt;text-indent: 150pt;line-height: 149%;text-align: justify;">You are also advised to pay Rs. 5500/- to my client towards the charges of this legal notice as I have received it from my client.</p>
                  <p style="padding-top: 10pt;padding-left: 41pt;text-indent: 154pt;line-height: 149%;text-align: justify;">A copy of this legal notice has been retained in my office for further use and reference.</p>
                  <img  src="' . ADVOCATE_SIGN . '" style="width:285px;float:right"/>
                  <p class="s2" style="padding-top: 10pt;padding-left: 300pt;text-indent: 0pt;text-align: right;">Harmod Lamba</p>
                  <p class="s2" style="padding-top: 6pt;text-indent: 0pt;text-align: right;">Advocate</p>

               </body>
            </html>';



            $subject = 'NOC Letter Case Recovery';

            $file_name = "Noc_recovery_letter_" . $lead_id . "_" . date('Ymd') . ".pdf";

            $file_path_with_name = UPLOAD_RECOVERY_PATH . $file_name;



            require_once(COMPONENT_PATH . 'includes/functions.inc.php');

            //$return_array = common_send_email("info@paisaonsalary.in", $subject, $message, BCC_NOC_EMAIL, "", "", "", "", $file_name, "noc_recovery_letter.pdf");
            $return_array = common_send_email($to, $subject, $message, "", "info@paisaonsalary.in", "", "", "", "", "noc_recovery_letter.pdf");

            if ($return_array) {

                $return_array['status'] = 1;
                $return_array['msg'] = "Recovery Letter Send Successfully";

                $insertApiLog = array(
                    'created_on ' => date('Y-m-d H:i:s'),
                    'status' => $leadstatus,
                    'stage' => $leadStage,
                    'user_id' => $_SESSION['isUserSession']['user_id'],
                    'lead_id' => $lead_id,
                    'lead_followup_status_id' => $lead_status_id,
                    'reason' => "Recovery Letter Send successfully"
                );
                $this->db->insert('lead_followup', $insertApiLog);
                $this->db->insert_id();
            }
        } else {
            $return_array['errors'] = "File does not exist. Please check offline";
        }

        echo json_encode($return_array);
        die;
        // }
    }

    public function sent_loan_closed_noc_letter($lead_id) {

        $sql = "SELECT DISTINCT LD.loan_no, LD.lead_status_id, CAM.loan_recommended as loan_amount, concat_ws(' ', LC.first_name, LC.middle_name, LC.sur_name) as customer_name, ";
        $sql .= " LD.email, CAM.disbursal_date as loaninitiatedDate,";
        $sql .= " LC.customer_digital_ekyc_flag, LC.customer_digital_ekyc_done_on, CL.closure_payment_updated_on,";
        $sql .= " CAM.repayment_date, CAM.repayment_amount, L.loan_noc_closed_letter_datetime, L.loan_noc_closing_letter";
        $sql .= " FROM leads LD INNER JOIN lead_customer LC ON LC.customer_lead_id=LD.lead_id";
        $sql .= " INNER JOIN loan L ON L.lead_id=LD.lead_id";
        $sql .= " INNER JOIN collection CL ON CL.lead_id=LD.lead_id";
        $sql .= " INNER JOIN credit_analysis_memo CAM ON(CAM.lead_id=LD.lead_id)";
        $sql .= " WHERE LD.lead_id=" . $lead_id . " AND LD.lead_status_id=16";

        $sql = $this->db->query($sql)->row();

        $to = $sql->email;
        //$to = 'info@paisaonsalary.in';

        if (!empty($to)) {
            $query = $this->db->select_sum('received_amount')->where(['payment_verification' => 1, 'collection_active' => 1, 'collection_deleted' => 0])->where('lead_id', $lead_id)->from('collection')->get()->row();

            $lead_status_id = $sql->lead_status_id;
            $loanCloserDate = date("d-m-Y", strtotime($sql->closure_payment_updated_on));
            $customer_name = $sql->customer_name;
            $repayment_date = $sql->repayment_date;

            $loanInitiatedDate = date("d-M-Y", strtotime($sql->loaninitiatedDate));

            $date_of_recived = date("d-m-Y", ($sql->loan_noc_closed_letter_datetime));

            $loan_amount = number_format($sql->loan_amount, 2);
            $received_amount = number_format($query->received_amount, 2);

            $sent_pre_approved_type = 0;

            $expire_date = date("Y-m-d", strtotime('+10 days', strtotime($repayment_date)));

            if (strtotime($expire_date) > strtotime($sql->loan_noc_closed_letter_datetime) && $sql->customer_digital_ekyc_flag == 1) {

                $sent_pre_approved_type = 1;
                $camp_kyc_date = strtotime(date("Y-m-d", strtotime('+85 day', strtotime($sql->customer_digital_ekyc_done_on))));

                $camp_current_datetime = strtotime(date("Y-m-d"));

                if ($camp_kyc_date > $camp_current_datetime) {
                    $sent_pre_approved_type = 2;
                }
            }

            if ($loanCloserDate == '01-01-1970') {
                $loanCloserDate = date("d-m-Y");
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
                                            <td align="left"><img src="' . EMAIL_BRAND_LOGO . '" width="180" height="100" /></td>
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
                                            <td align="left" valign="top"><span style="font-size:17px;line-height: 25px;padding-bottom: 6px; text-align:justify;">This is to certify that Mr/Ms ' . $sql->customer_name . ' who had taken a short-term loan from ' . COMPANY_NAME . ' for Rs. ' . $loan_amount . ' on ' . $loanInitiatedDate . ' has repaid Rs. ' . $received_amount . ' on ' . $loanCloserDate . '.</span></td>
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
                                            <td><!--<img src="' . AUTHORISED_SIGNATORY . '" width="150" height="150" />--><br/><br/><br/></td>
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
                                            <td><strong>' . COMPANY_NAME . ' </strong></td>
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
                                          <td><span style="font-size:17px;line-height: 25px;padding-bottom: 6px; text-align:justify; margin:25px 0px;">
                                            <a href="' . APPLE_STORE_LINK . '" target="_blank"><img style="width: 10%;margin-left: 0%;" src="' . APPLE_STORE_ICON . '"></a></span>
                	                        <span style="font-size:17px;line-height: 25px;padding-bottom: 6px; text-align:justify; margin:10px 0px;"><a href="' . ANDROID_STORE_LINK . '" target="_blank"><img style="width: 10%;margin-left: 0%;" src="' . ANDROID_STORE_ICON . '"></a></span>
                	                        </td>
                                        </tr>

                                        <tr>
                                            <td align="left">&nbsp;</td>
                                        </tr>
                                    </table>
                                </body>
                            </html>';
            $subject = 'NOC Closing Letter';

            $file_name = "Noc_letter_" . $lead_id . "_" . rand(1000, 9999) . ".pdf";

            $file_path_with_name = UPLOAD_LEGAL_PATH . $file_name;
            require_once __DIR__ . '/../../vendor/autoload.php';
            try {
                $mpdf = new \Mpdf\Mpdf();
                $mpdf->WriteHTML($message);
                $mpdf->Output($file_path_with_name, 'F');
            } catch (\Mpdf\MpdfException $e) {
                error_log($e->getMessage());
                echo 'PDF generation error: ' . $e->getMessage();
            }

            if (file_exists($file_path_with_name)) {
                require_once(COMPONENT_PATH . "CommonComponent.php");
                $CommonComponent = new CommonComponent();
                $request_array = array();
                $request_array['flag'] = 1;
                $request_array['file'] = base64_encode(file_get_contents($file_path_with_name));
                $request_array['ext'] = pathinfo($file_path_with_name, PATHINFO_EXTENSION);

                $upload_return = $CommonComponent->upload_document($lead_id, $request_array);

                if ($upload_return['status'] == 1) {
                    $return_array['status'] = 1;
                    $file_name = $upload_return['file_name'];
                    unlink($file_path_with_name);
                }

                $return_array['status'] = 1;
                $this->updateLeads(['lead_id' => $lead_id], ['loan_noc_closing_letter' => $file_name], 'loan');
            } else {
                $return_array['errors'] = "File does not exist. Please check offline";
            }

            if ($print_flag == true) {
                return $message;
            }

            // $loan_disbursal_letter = file_get_contents(downloadDocument($filepdf, 2));

            require_once(COMPONENT_PATH . 'includes/functions.inc.php');

            $return_array = common_send_email($to, $subject, $message, BCC_NOC_EMAIL, $cc_mail = "info@paisaonsalary.in", "", "", "", $file_name, "noc_letter.pdf");
            // print_r($return_array); die;

            $user_id = 0;

            if (!empty($_SESSION['isUserSession']['user_id'])) {
                $user_id = $_SESSION['isUserSession']['user_id'];
            }

            if ($return_array['status'] == 1) {

                $loan_data = array(
                    'loan_noc_letter_sent_status' => 1,
                    'loan_noc_closed_letter_datetime' => date("Y-m-d H:i:s"),
                    'loan_noc_closed_letter_user_id' => $user_id,
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

            /*             $return_array_pa_email = $this->preApprovedOfferEmailer($to, $customer_name, $lead_id, $sent_pre_approved_type);

            if ($return_array_pa_email['status'] == 1) {
                $lead_remark .= "<br/> Pre-Approved Offer email sent successfully.[$sent_pre_approved_type]";
            } else {
                $lead_remark .= "<br/> Pre-Approved Offer email not sent.";
            }

            $return_array_Customer_Feedback_Emailer = $this->send_Customer_Feedback_Emailer($lead_id, $to, $customer_name);

            if ($return_array_Customer_Feedback_Emailer['status'] == 1) {
                $lead_remark .= "<br/> Customer Feedback email sent successfully.";
            } */

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
                                    <td><a href="' . WEBSITE_URL . '" target="_blank"><img src="' . EMAIL_BRAND_LOGO . '" alt="pos-logo" style="border-radius: 11px;padding: 6px 8px;"></a></td>
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

        require_once(COMPONENT_PATH . 'includes/functions.inc.php');

        $return_array = common_send_email($customer_email, 'PRE-APPROVED OFFER | ' . WEBSITE, $message);

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
                                    <table width = "550" border = "0" align = "center" cellpadding = "0" cellspacing = "0" style = "padding:10px 10px 2px 10px; border:solid 2px #00455E; font-family:Arial, Helvetica, sans-serif;border-radius:3px;">
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

        require_once(COMPONENT_PATH . 'includes/functions.inc.php');

        $return_array = common_send_email($customer_email, 'FEEDBACK FORM | ' . WEBSITE, $message, "", "", "", CARE_EMAIL);

        return $return_array;
    }

    public function sent_ekyc_request_email($lead_id) {

        $sql = "SELECT LD.lead_id, LD.lead_status_id, concat_ws(' ', LC.first_name, LC.middle_name, LC.sur_name) as customer_name, LD.email";
        $sql .= " FROM leads LD INNER JOIN lead_customer LC ON LC.customer_lead_id=LD.lead_id ";
        $sql .= " WHERE LD.lead_id=$lead_id";

        $sql = $this->db->query($sql)->row();

        $to = $sql->email;

        if (!empty($to)) {

            $lead_status_id = $sql->lead_status_id;

            $customer_name = $sql->customer_name;

            $enc_lead_id = $this->encrypt->encode($lead_id);

            $active_service = (date('d') % 2) > 0 ? 1 : 2;

            // $digital_ekyc_url = base_url("aadhaar-veri-request") . "?lead_id=" . $lead_id;

            // $digital_ekyc_url = base_url("aadhaar-veri-request") . "?refstr=" . $enc_lead_id;
            // $digital_ekyc_url = base_url("aadhaar-veri-request") . "?lead_id=" . $lead_id;
            if ($active_service == 1) { // DigiTap
                $digital_ekyc_url = base_url("digitap-aadhaar-veri-request") . "?refstr=" . $enc_lead_id;
            } elseif ($active_service == 2) { // Singzy
                $digital_ekyc_url = base_url("aadhaar-veri-request") . "?lead_id=" . $lead_id;
            }

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
                                                        <td width = "25%" valign = "top"><a href = "' . WEBSITE_URL . '" target = "_blank"><img src = "'.LMS_COMPANY_LOGO.'" alt = "logo" width = "200" height = "50" style = "margin-top:10px;margin-left:12px;"></a></td>
                                                        <td width = "64%" align = "center" valign = "middle"><strong style = "color:#fff; font-size:20px;">DIGITAL E-KYC</strong></td>
                                                        <!-- <td width = "11%" align = "right"><img src = "' . EKYC_LINES . '" width = "26" height = "147" /></td> -->
                                                    </tr>
                                                </table>

                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan = "2" valign = "top"><table width = "100%" border = "0" cellpadding = "0" cellspacing = "0" style = "padding:0px 10px;">
                                                    <tr>
                                                        <td width = "50%" rowspan = "10" valign = "top" style = "border-right:solid 1px #00455E;"><table width = "100%" border = "0">
                                                                <tr>
                                                                    <td align = "center" valign = "middle">&nbsp;
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td valign = "middle"><span style = "font-weight:bold; font-size:25px; color:#00455E;">Dear ' . $customer_name . ' </span></td>
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
                                                                    <td align = "center"><a href = "' . $digital_ekyc_url . '" style = "background: #00455E;color: #fff;padding: 7px 15px;border-radius: 3px;text-decoration: blink;">Digital E-KYC</a></td>
                                                                </tr>
                                                                <!-- <tr>
                                                                    <td align = "center"><img src = "' . EKYC_LINES . '" alt = "line" width = "26" height = "10" /></td>
                                                                </tr> -->
                                                                <tr>
                                                                    <td><br><p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 20px;">If you are not able to click on the above button, then please copy and paste this URL <a href = "' . $digital_ekyc_url . '">' . $digital_ekyc_url . ' </a> in the browser to proceed.</p></td>
                                                                </tr>
                                                            </table></td>
                                                        <td width = "0" rowspan = "10" align = "center">&nbsp;
                                                        </td>
                                                        <td colspan = "2" align = "left">&nbsp;
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan = "2" align = "center"><span style = "font-weight:bold; font-size:25px; color:#00455E;">How it Works</span></td>
                                                    </tr>

                                                    <!-- <tr>
                                                        <td colspan = "2" align = "left"><img src = "' . EKYC_LINES . '" alt = "line" width = "26" height = "5" /></td>
                                                    </tr> -->

                                                    <tr>
                                                        <!-- <td width = "23%" align = "left"><a href = "' . EKYC_IMAGES_1_SHOW . '" target = "_blank"><img src = "' . EKYC_IMAGES_1 . '" alt = "1st" width = "172" height = "103" /></a></td> -->
                                                        <td width = "35%" valign = "top">
                                                            <p style = "color: #00455E;font-size:18px;margin: 0px;padding-left: 10px;"><strong>First Step</strong></p>
                                                            <p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 20px;">Please enter your 12 digits Aadhaar No. and press next.</p>
                                                        </td>
                                                    </tr>
                                                    <!-- <tr>
                                                        <td colspan = "2" align = "left"><img src = "' . EKYC_LINES . '" alt = "line" width = "26" height = "5" /></td>
                                                    </tr> -->
                                                    <tr>
                                                        <!-- <td align = "left"><a href = "' . EKYC_IMAGES_2_SHOW . '" target = "_blank"><img src = "' . EKYC_IMAGES_2 . '" alt = "2nd" width = "171" height = "103" /></a></td> -->
                                                        <td align = "left" valign = "top">
                                                            <p style = "color: #00455E;font-size:18px;margin: 0px;padding-left: 10px;"><strong>Second Step</strong></p>
                                                            <p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 20px;">Please enter the OTP received in your registered mobile no. with Aadhaar and press continue.</p>
                                                        </td>
                                                    </tr>
                                                    <!-- <tr>
                                                        <td colspan = "2" align = "left"><img src = "' . EKYC_LINES . '" alt = "line" width = "26" height = "5" /></td>
                                                    </tr> -->
                                                    <tr>
                                                        <!-- <td align = "left"><a href = "' . EKYC_IMAGES_3_SHOW . '" target = "_blank"><img src = "' . EKYC_IMAGES_3 . '" alt = "3rd" width = "173" height = "103" /></a></td> -->
                                                        <td align = "left" valign = "top"><p style = "color: #00455E;font-size:18px;margin: 0px;padding-left: 10px;"><strong>Third Step</strong></p>
                                                            <p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 20px;">Press allow to give access of your DigiLocker account for documents verification.</p></td>
                                                    </tr>
                                                    <!-- <tr>
                                                        <td colspan = "2" align = "left"><img src = "' . EKYC_LINES . '" alt = "line" width = "26" height = "5" /></td>
                                                    </tr> -->
                                                    <tr>
                                                        <!-- <td align = "left"><a href = "' . EKYC_IMAGES_4_SHOW . '" target = "_blank"><img src = "' . EKYC_IMAGES_4 . '" alt = "4th" width = "173" height = "102" /></a></td> -->
                                                        <td align = "left" valign = "top"><p style = "color: #00455E;font-size:18px;margin: 0px;padding-left: 10px;"><strong>Thank You</strong></p>
                                                            <p style = "font-size: 14px;margin: 0px;padding-left: 10px;line-height: 20px;">Your approval to access DigiLocker account for E-KYC has been successfully submitted.</p></td>
                                                    </tr>
                                                    <tr>
                                                        <td valign = "top" style = "border-right:solid 1px #00455E;">&nbsp;
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
                                                <a href = "' . LINKEDIN_LINK . '" target = "_blank"> <img src = "'.(getenv("WEBSITE_URL") ?? 'https://cms.paisaonsalary.in/').'public/images/linkedin.png" alt = "linkdin" width = "32" height = "32" /></a>
                                                <a href = "' . INSTAGRAM_LINK . '" target = "_blank"> <img src = "'.(getenv("WEBSITE_URL") ?? 'https://cms.paisaonsalary.in/').'public/images/instagram.png" alt = "instagram" width = "32" height = "32" /></a>
                                                <a href = "' . FACEBOOK_LINK . '" target = "_blank"> <img src = "'.(getenv("WEBSITE_URL") ?? 'https://cms.paisaonsalary.in/').'public/images/facebook.png" alt = "facebook" width = "32" height = "32" /></a>
                                                <a href = "' . TWITTER_LINK . '" target = "_blank" style = "color:#fff;"> <img src = "'.(getenv("WEBSITE_URL") ?? 'https://cms.paisaonsalary.in/').'public/images/twitter.png" alt = "twitter" width = "32" height = "32" /> </a>
                                                <a href = "' . YOUTUBE_LINK . '" target = "_blank" style = "color:#fff;"> <img src = "'.(getenv("WEBSITE_URL") ?? 'https://cms.paisaonsalary.in/').'public/images/youtube.png" alt = "youtube" width = "32" height = "32" /> </a>
                                                <!-- <a href = "' . APPLE_STORE_LINK . '" target = "_blank"> <img src = "'.(getenv("WEBSITE_URL") ?? 'https://cms.paisaonsalary.in/').'public/images/googleplay.png" alt = "google_play" width = "100" height = "30" style = "border-radius: 50px;"></a> -->
                                            </td>
                                        </tr>

                                        <tr>
                                            <td colspan = "4" align = "center" valign = "middle" bgcolor = "#00455E" style = "padding:10px; color:#fff; font-weight:normal; font-size:16px;"><a href = "tel:' . REGISTED_MOBILE . '" style = "color:#fff; text-decoration:blink;"><img src = "'.(getenv("WEBSITE_URL") ?? 'https://cms.paisaonsalary.in/').'public/images/phone.jpg" width = "16" height = "16" alt = "phone-icon" style = "margin-bottom: -2px;margin-right:3px"> ' . REGISTED_MOBILE . ' </a> <a href = "' . WEBSITE_URL . '" target = "_blank" style = "color:#fff; text-decoration:blink;"><img src = "'.(getenv("WEBSITE_URL") ?? 'https://cms.paisaonsalary.in/').'public/images/favicon.png" width = "16" height = "16" alt = "web-icon" style = "margin-bottom: -2px;"> ' . WEBSITE . ' </a> <img src = "'.(getenv("WEBSITE_URL") ?? 'https://cms.paisaonsalary.in/').'public/images/email.jpg" width = "16" height = "16" alt = "email-icon" style = "margin-bottom: -2px;"><a href = "mailto:' . INFO_EMAIL . '" style = "color:#fff; text-decoration:blink;">' . INFO_EMAIL . ' </a></td>
                                        </tr>
                                    </table>
                                </body>
                            </html>';

            require_once(COMPONENT_PATH . 'includes/functions.inc.php');

            $return_array = common_send_email($to, BRAND_NAME . '  | DIGITAL EKYC : ' . $customer_name, $message);



            // $return_array = common_send_email($to, BRAND_NAME . '  | DIGITAL EKYC : ' . $customer_name, $message);



            if ($return_array['status'] == 1) {
                $lead_remark = "Digital E-KYC email sent successfully.";
                $data = "true";
            } else {
                $lead_remark = "Digital E-KYC email sending failed.";
                $data = "false";
                // echo "init";
            }

            $this->insertLeadFollowupLog($lead_id, $lead_status_id, $lead_remark);

            $this->sent_ekyc_request_sms($lead_id, $digital_ekyc_url);
        } else {
            $data = "false";
        }

        return $data;
    }

    public function sent_ekyc_request_sms($lead_id, $sort_url) {

        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        $sql = 'SELECT LD.lead_id, LC.mobile, LC.first_name as name FROM leads LD INNER JOIN lead_customer LC ON (LC.customer_lead_id=LD.lead_id) WHERE LD.lead_id=' . $lead_id;

        $result = $this->db->query($sql);

        if ($result->num_rows() > 0) {

            $app_data = $result->row_array();

            $sms_request = array();
            $sms_request['lead_id'] = $app_data['lead_id'];
            $sms_request['mobile'] = $app_data['mobile'];
            $sms_request['name'] = $app_data['name'];
            $sms_request['ekyc_link'] = $sort_url;

            $CommonComponent->payday_sms_api(13, $lead_id, $sms_request);
        }
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

        $sql = 'LD.lead_id, LD.city_id, LD.state_id, LD.pincode';

        $this->db->select($sql);
        $this->db->from($this->table . ' LD');
        $this->db->join($this->table_lead_customer . ' C', 'C.customer_lead_id = LD.lead_id AND C.customer_active = 1 AND C.customer_deleted = 0', 'INNER');
        $this->db->where(['LD.lead_id' => $lead_id]);
        $leadDetails = $this->db->get();

        if ($leadDetails->num_rows() > 0) {

            $lead_data = $leadDetails->row_array();

            $city_id = $lead_data['city_id'];

            $state_id = $lead_data['state_id'];

            $pincode = $lead_data['pincode'];



            if (empty($city_id)) {
                $error = "Missing Current Address City";
            } else if (empty($state_id)) {
                $error = "Missing Current Address State";
            } else if (empty($pincode)) {
                $error = "Missing Current City Pincode";
            } else {

                $sql = "SELECT MC.m_city_name, MS.m_state_name, MC.m_city_is_sourcing, MC.m_city_trial_sourcing, MS.m_state_is_sourcing";
                $sql .= " FROM master_city MC";
                $sql .= " INNER JOIN master_state MS ON(MC.m_city_state_id=MS.m_state_id)";
                $sql .= " INNER JOIN master_pincode MP ON(MC.m_city_id=MP.m_pincode_city_id)";
                $sql .= " WHERE MC.m_city_active=1 AND MC.m_city_deleted=0 AND MS.m_state_active=1 AND MS.m_state_deleted=0 AND MP.m_pincode_active=1 AND MP.m_pincode_deleted=0";
                $sql .= " AND MC.m_city_id=$city_id AND MS.m_state_id=$state_id AND MP.m_pincode_value=$pincode";

                $tempDetails = $this->db->query($sql);



                if ($tempDetails->num_rows() > 0) {

                    $city_state_data = $tempDetails->row_array();

                    if (empty($city_state_data['m_city_is_sourcing']) && empty($city_state_data['m_city_trial_sourcing'])) {
                        $error = "Customer current address city is OGL.";
                    } else if (empty($city_state_data['m_state_is_sourcing'])) {
                        $error = "Customer current address state is OGL.";
                    } else if (empty($city_state_data['m_pincode_is_sourcing']) && empty($city_state_data['m_pincode_trial_sourcing'])) {
                        $error = "Customer current address pincode is OGL.";
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
        require_once(COMPONENT_PATH . 'CommonComponent.php');
        $CommonComponent = new CommonComponent();

        $json['sms'] = $CommonComponent->payday_sms_api($sms_type_id, $lead_id, $req);
    }

    public function get_mapped_credit_users($credit_head_id, $role_type_id, $user_case_type) {

        $return_array['status'] = 0;
        $return_array['data'] = '';

        $today_date = date("Y-m-d");

        $sql = "SELECT U.name, UR.user_role_user_id, UR.user_role_id";
        $sql .= " FROM user_roles UR INNER JOIN users U ON (U.user_id = UR.user_role_user_id)";
        $sql .= " INNER JOIN user_lead_allocation_log ULA on (ULA.ula_user_id = UR.user_role_user_id AND DATE(ULA.ula_created_on)='$today_date' AND ULA.ula_user_status=1)";
        $sql .= " WHERE UR.user_role_type_id = $role_type_id AND ULA.ula_user_case_type=$user_case_type AND UR.user_role_supervisor_role_id IN ";
        $sql .= " (SELECT user_role_id FROM user_roles WHERE user_role_type_id = 4 AND user_role_user_id = $credit_head_id)";

        $data = $this->db->query($sql);

        if ($data->num_rows() > 0) {
            $return_array['status'] = 1;
            $return_array['data'] = $data->result_array();
        } else {
            $return_array['status'] = 0;
        }

        return $return_array;
    }

    public function get_mapped_credit_managers($credit_head_id) {

        $return_array = array('status' => 0, 'data' => '');

        $sql = "SELECT U.name, UR.user_role_user_id";
        $sql .= " FROM user_roles UR INNER JOIN users U ON (U.user_id = UR.user_role_user_id)";
        $sql .= " INNER JOIN user_lead_allocation_log ULA on (ULA.ula_user_id = UR.user_role_user_id)";
        $sql .= " WHERE UR.user_role_type_id = 3 AND ULA.ula_user_case_type = $user_type_id AND UR.user_role_supervisor_role_id = ";
        $sql .= " (SELECT user_role_user_id FROM user_roles WHERE user_role_type_id = 4 AND user_role_user_id = $credit_head_id)";

        $data = $this->db->query($sql);

        if ($data->num_rows() > 0) {
            $retrun_array['status'] = 1;
            $return_array['data'] = $data->result_array();
        } else {
            $return_array['status'] = 0;
        }

        return $retrun_array;
    }

    //    ////////  SEND MESSAGE API \\\\\\\\\\\\\
    //
    //    public function generate_Repay_Link_SMS($lead_id){
    //
    //        if (!empty($_SESSION['isUserSession']['user_id'])) {
    //            $user_id = $_SESSION['isUserSession']['user_id'];
    //        }
    //
    //
    //        $repay_loan_url =  REPAYMENT_REPAY_LINK . "/" . $this->encrypt->encode($lead_id).'/'.$this->encrypt->encode($user_id);
    //
    //        require_once (COMPONENT_PATH . 'CommonComponent.php');
    //        $CommonComponent = new CommonComponent();$sql = $this->getCAMDetails($lead_id);
    //        $camDetails = $sql->row();
    //
    //        $sms_type_id = 18;
    //        $leadDetails = $this->select(['lead_id' => $lead_id], 'first_name, email, mobile', 'leads');
    //        $lead_cust = $leadDetails->row();
    //
    //        $req = array();
    //        $req['lead_id'] = $lead_id;
    //
    //        $req['loan_no'] = $camDetails->loan_no;
    //        $req['name'] = $lead_cust->first_name;
    //        $req['mobile'] =  $lead_cust->mobile;
    //
    //        $res = $CommonComponent->call_url_shortener_api($repay_loan_url);
    //        $req['repayment_link_generate'] = $res['short_url'];
    //
    //        echo '<pre>';
    //        print_r($req);
    //        die;
    //
    //        $CommonComponent->payday_sms_api($sms_type_id, $lead_id, $req);
    //
    //        require_once (COMPONENT_PATH . 'CommonComponent.php');
    //        $CommonComponent = new CommonComponent();
    //
    //        $json['sms'] = $CommonComponent->payday_sms_api($sms_type_id, $lead_id, $req);
    //    }

    public function generate_repay_link_mail($lead_id, $user_id, $amount) {

        if (empty($lead_id)) {
            return false;
        }

        if (empty($_SESSION['isUserSession']['user_id'])) {
            return false;
        }

        $sql = "SELECT
                    LD.lead_id,
                    CONCAT_WS (' ', LC.first_name, LC.middle_name, LC.sur_name) as cust_full_name,
                    LC.first_name,
                    LC.middle_name,
                    LC.sur_name,
                    LD.email,
                    LC.alternate_email,
                    LC.mobile,
                    LC.alternate_mobile,
                    L.loan_no,
                    L.recommended_amount,
                    CAM.loan_recommended,
                    CAM.roi,
                    CAM.tenure,
                    CAM.repayment_date,
                    CAM.disbursal_date,
                    CAM.repayment_amount,
                    DATEDIFF (CURRENT_DATE(), CAM.repayment_date) AS dpd,
                    IF (
                        CAM.repayment_date >= CURRENT_DATE(),
                        (
                            (
                                CAM.loan_recommended * CAM.roi * DATEDIFF (CURRENT_DATE(), CAM.disbursal_date) / 100
                            )
                        ) + CAM.loan_recommended,
                        CAM.repayment_amount
                    ) AS total_due
                FROM
                    leads LD
                    INNER JOIN lead_customer LC ON (LD.lead_id = LC.customer_lead_id)
                    INNER JOIN credit_analysis_memo CAM ON (LD.lead_id = CAM.lead_id)
                    INNER JOIN loan L ON (L.lead_id = LD.lead_id)
                WHERE
                    LD.lead_status_id IN (14, 19)
                    AND LD.lead_id = $lead_id";

        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $leadDetails = $tempDetails->row();
        } else {
            return false;
        }

        if ($amount < 1) {
            $amount = $leadDetails->total_due;
        }

        require_once(COMPONENT_PATH . 'CommonComponent.php');
        $CommonComponent = new CommonComponent();

        // $mobile = $lead_cust->mobile;
        $email = $leadDetails->email;

        $return_array = $CommonComponent->call_qrcode_api($lead_id, array('amount' => $amount));
        $email_subject = "";
        $repayment_link = '';

        $encData = base64_encode(json_encode(array('lead_id' => $lead_id, 'amount' => $amount, 'user_id' => $user_id)));
        $repaymentLink = "https://payments.paisaonsalary.com/?encId=" . $encData;

        $qrCodeUrl = $return_array['qrCodeUrl'];

        $email_subject = "Payment Link and QR Code for Your Loan Repayment - " . $leadDetails->loan_no;
        $repayment_link = 'customer_requested_amount.png';

        $email_message = '<!DOCTYPE html>
                            <html lang="en">
                            <head>
                                <meta charset="UTF-8">
                                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                <title>Repayment Reminder</title>
                                <style>
                                    @media (max-width: 600px) {
                                        .header,
                                        .content,
                                        .footer {
                                            padding: 15px;
                                        }
                                        h1 {
                                            font-size: 20px;
                                        }
                                        p,
                                        td {
                                            font-size: 14px;
                                        }
                                        .btn {
                                            font-size: 14px;
                                            padding: 10px 20px;
                                        }
                                    }
                                </style>
                            </head>
                            <body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f3f4f6; color: #333;">
                                <div style="max-width: 600px; margin: 20px auto; background: #ffffff; border: 1px solid #ddd; border-radius: 12px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); overflow: hidden;">
                                    <div style="background: linear-gradient(90deg, #266624, #19BF02); color: #fff; padding: 20px; text-align: center;">
                                        <img src="'.LMS_COMPANY_LOGO.'" style="width: 30%;">
                                    </div>
                                    <div style="padding: 20px;">
                                        <div style="text-align: center; margin-bottom: 20px;">
                                            <img src="https://paisaonsalarybucket.s3.ap-south-1.amazonaws.com/emailer/' . $repayment_link . '" alt="Payment Reminder Banner" style="width: 100%; max-width: 560px; border-radius: 8px;">
                                        </div>
                                        <p style="line-height: 1.8; margin: 10px 0; font-size: 16px;">Dear <strong>' . ucwords($leadDetails->cust_full_name) . '</strong>,</p>

                                        <p style="line-height: 1.8; margin: 10px 0;">As per your request, please find the payment link and QR code below to proceed with the payment of the requested amount as of today:</p>

                                        <table style="width: 100%; margin: 20px 0; border-collapse: collapse;">
                                        <tr>
                                        <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;">Loan Account Number:</td>
                                        <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;"><strong>' . htmlspecialchars($leadDetails->loan_no) . '</strong></td>
                                        </tr>
                                        <tr>
                                        <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;">Due Date:</td>
                                        <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;"><strong>' . htmlspecialchars(date("d M Y", strtotime($leadDetails->repayment_date))) . '</strong></td>
                                        </tr>
                                        <tr>
                                        <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;">Amount Due:</td>
                                        <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px; color: #E53935;"><strong>RS.' . htmlspecialchars(number_format($leadDetails->total_due)) . '</strong></td>
                                        </tr>
                                        <tr>
                                        <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px;">Requested Amount:</td>
                                        <td style="padding: 10px; border: 1px solid #ddd; font-size: 16px; color: green;"><strong>RS.' . htmlspecialchars(number_format($amount)) . '</strong></td>
                                        </tr>
                                        </table>

                                        <p style="line-height: 1.8; margin: 10px 0;color: green !important;font-size: 12px;">We kindly request you to ensure the payment is made on or before the due date to avoid any penalty interest or late fees. Timely repayment also helps maintain your credit score.</p>

                                        <p style="line-height: 1.8; margin: 10px 0; font-size: 16px; text-align: center;"><strong>Scan the QR code below to make your payment easily:</strong></p>

                                        <div style="text-align: center; margin: 20px 0;">
                                            <img src="' . $qrCodeUrl . '" alt="QR Code for Payment" style="max-width: 200px; border: 2px solid #ddd; border-radius: 8px;">
                                        </div>

                                        <p style="text-align: center; line-height: 1.8; margin: 10px 0;">Alternatively, you can use the link below to make your payment:</p>

                                        <div style="text-align: center; margin: 20px 0;">
                                            <a href="' . $repaymentLink . '" class="btn" style="display: inline-block; background: #4CAF50; color: #fff; padding: 12px 24px; text-decoration: none; font-size: 16px; border-radius: 6px;">Make Payment</a>
                                        </div>

                                        <p style="line-height: 1.8; margin: 10px 0;">If you have already made the payment, please disregard this message. For any assistance, contact our support team at <a href="mailto:' . CARE_EMAIL . '" style="color: #4CAF50; text-decoration: none;"><strong>' . CARE_EMAIL . '</strong></a> or call us at <strong><a href="tel:' . REGISTED_MOBILE . '" style="color: #4CAF50; text-decoration: none;">' . REGISTED_MOBILE . '</a></strong></p>

                                        <p style="line-height: 1.8; margin: 10px 0; font-size: 16px;">Thank you for choosing us.</p>

                                        <p style="line-height: 1.8; margin: 10px 0; font-size: 16px;">Best regards,<br><strong>' . BRAND_NAME . '</strong></p>
                                    </div>
                                    <div style="background: #f9f9f9; color: #666; text-align: center; font-size: 14px; padding: 20px;">
                                        <p style="margin: 0;">&copy; 2025 ' . BRAND_NAME . '. All rights reserved.</p>
                                        <p style="margin: 0;">
                                            <a href="' . WEBSITE_URL . "privacypolicy" . '" target="_blank"  style="color: #4CAF50; text-decoration: none; margin-right: 15px;">Privacy Policy</a>
                                            <a href="' . WEBSITE_URL . "termsandconditions" . '" target="_blank"  style="color: #4CAF50; text-decoration: none; margin-right: 15px;">Terms of Service</a>
                                            <a href="' . WEBSITE_URL . "contact" . '" target="_blank"  style="color: #4CAF50; text-decoration: none;">Contact Us</a>
                                        </p>
                                        <!--<div style="text-align: center; margin: 20px 0;">
                                            <p style="font-size: 14px; color: #777; margin: 10px;">Follow us on:</p>
                                            <a href="' . FACEBOOK_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                <img src="' . FACEBOOK_ICON . '" alt="facebook" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                            </a>
                                            <a href="' . TWITTER_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                <img src="' . TWITTER_ICON . '" alt="twitter" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                            </a>
                                            <a href="' . LINKEDIN_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                <img src="' . LINKEDIN_ICON . '" alt="linkedin" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                            </a>
                                            <a href="' . INSTAGRAM_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                <img src="' . INSTAGRAM_ICON . '" alt="instagram" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                            </a>
                                            <a href="' . YOUTUBE_LINK . '" target="_blank" style="text-decoration: none; margin: 0 5px;">
                                                <img src="' . YOUTUBE_ICON . '" alt="youtube" style="width: 30px; height: 30px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);">
                                            </a>
                                        </div>-->
                                    </div>
                                </div>
                            </body>
                            </html>';

        $return_array = common_send_email($email, $email_subject, $email_message, "", "", "info@paisaonsalary.in", "", "", "", "");

        if ($return_array['status'] == 1) {
            $data = 1;
        } else {
            $data = 0;
        }

        return $data;
    }

    public function sentLegalNoticeToCustomer($lead_id) {
        $user_id = !empty($_SESSION['isUserSession']['user_id']) ? $_SESSION['isUserSession']['user_id'] : 0;

        $select = "SELECT LD.mobile, LD.lead_status_id, LD.stage, LD.status, concat_ws(' ', LC.first_name, LC.middle_name, LC.sur_name) as customer_name,CAM.disbursal_date,LC.gender,LD.email,CAM.tenure,CAM.loan_recommended,LD.loan_no,LC.aa_current_house,LC.aa_current_locality,LC.aa_current_landmark,LC.aa_cr_residence_pincode,LC.aa_current_district,MS.m_state_name State,MC.m_city_name City,LN.loan_total_received_amount,LN.loan_principle_payable_amount, LN.loan_legal_notice_name";
        $select .= " FROM leads LD INNER JOIN lead_customer LC ON LC.customer_lead_id=LD.lead_id INNER JOIN credit_analysis_memo CAM ON CAM.lead_id=LD.lead_id INNER JOIN loan LN ON LN.lead_id=LD.lead_id LEFT JOIN master_state MS ON MS.m_state_id=LC.aa_current_state_id LEFT JOIN master_city MC ON MC.m_city_id=LC.aa_current_city_id";
        $select .= " WHERE LD.lead_id=$lead_id";
        $sql = $this->db->query($select)->row();

        $customer_email = $sql->email;
        $customer_mobile = $sql->mobile;
        $disbursal_date = $sql->disbursal_date;
        $gender = $sql->gender;
        $tenure = $sql->tenure;

        $loan_legal_sent_type = !empty($sql->loan_legal_notice_name) ? 3 : 2;

        $d1 = strtotime($disbursal_date);

        $d2 = strtotime(Date("Y-m-d"));

        if (!empty($d2)) {
            $datediff = $d2 - $d1;
            $tenure = round($datediff / (60 * 60 * 24));
        }

        $loan_total_received_amount = $sql->loan_total_received_amount;
        $loan_principle_payable_amount = $sql->loan_recommended;
        $total_due_amount = $loan_principle_payable_amount - $loan_total_received_amount;
        $interest_amount = (($total_due_amount * ((18 / 365) / 100) * $tenure));
        $total_final_amount = $total_due_amount + $interest_amount;

        $aadhaar_address = $sql->aa_current_house;
        $aadhaar_address .= !empty($sql->aa_current_locality) ? ', ' . $sql->aa_current_locality : "";
        $aadhaar_address .= !empty($sql->aa_current_landmark) ? '<br>' . $sql->aa_current_landmark : "";
        $aadhaar_address .= !empty($sql->State) ? '<br> ' . $sql->State : "";
        $aadhaar_address .= !empty($sql->City) ? ',' . $sql->City : "";
        $aadhaar_address .= !empty($sql->aa_cr_residence_pincode) ? '-' . $sql->aa_cr_residence_pincode : "";

        if ($gender == 'FEMALE') {
            $customer_name = 'SHRI/SMT ' . $sql->customer_name;
        } else {
            $customer_name = 'SHRI/SMT ' . $sql->customer_name;
        }

        $subject = 'LEGAL NOTICE - ' . $customer_name . ' | ' . BRAND_NAME;

        if ($loan_legal_sent_type == 2) {

            $pdf_html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
                <title>Legal Notice</title>
            </head>
            <body style="font-family: Courier, arial;">
            <table style="width:892px;height:1262px; margin: 0 auto;font-family: Courier, arial;">
                    <tr>
                        <td style="background:#000;"><center>
                            <img style="width:auto;" src="' . WEBSITE_URL . 'public/image/adv_header.jpg" alt="background image"/>
                            </center>
                        </td>
                    </tr>
                    <tr>
                       <td>
                        <br>DELHI,<br>' . date('d,M Y') . '<br><br><br>
                       </td>
                    </tr>
                    <tr>
                       <td>
                        To,
                       </td>
                    </tr>
                    <tr>
                       <td>' . $customer_name . '<br><br></td>
                    </tr>
                    <tr>
                       <td>' . $aadhaar_address . '</td>
                    </tr>
                    <tr>
                        <td style="text-align:center;"><b style="font-size: 20px;">LEGAL NOTICE</b> <b style="font-size: 15px;">(Without prejudice)</b></td>
                        <br><br>
                    </tr>
                    <tr>
                       <td>
                          <span> <b> ' . $customer_name . ',</b></span><br/>
                          <br>Under instructions from and on behalf of my client ' . COMPANY_NAME . ' With the brand name “' . BRAND_NAME . '” having its ' . REGISTED_ADDRESS . ', I address you as under. <br><br>
                       </td>
                    </tr>
                    <tr>
                        <td>1. That you had approached my client for a short term loan as you were in dire need of money on ' . date('d,M Y', strtotime($sql->disbursal_date)) . '. <br><br></td>
                    </tr>
                    <tr>
                        <td>2. That Pursuant to the terms and condition of the customer agreement form as agreed by you, you were provided the short-term loan of Rs. ' . number_format($loan_principle_payable_amount) . ' with Loan Account Number - ' . $sql->loan_no . '.<br><br><br></td>
                    </tr>
                    <tr>
                        <td>3.That it has been more than ' . $tenure . ' days since you contacted my client regarding the outstanding loan amount and interest that you are bound to payback to my client ' . COMPANY_NAME . ' With the brand name “' . BRAND_NAME . '”.<br><br><br></td>
                    </tr>
                    <tr>
                        <td>4. That thus by your act and conduct,it is evident that since the time of availing such loan you had malafide intention.<br><br><br></td>
                    </tr>
                    <tr>
                        <td>5. That below mentioned are the details of the loan.<br><br></td>
                    </tr>
                    <tr>
                        <td>
                        <table border="4" style="width: 100%; padding: 9px; border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5;text-align:center">
                          <tr>
                            <th style="border-radius: 10px;background-color: #fefefe;font-size: 16px;font-weight:bold;border: 2px solid #d0e4f5">Particulars</th>
                            <th style="border-radius: 10px;background-color: #fefefe;font-size: 16px;font-weight:bold;border: 2px solid #d0e4f5">Amount</th>
                          </tr>
                          <tr>
                            <td style="border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5">Principal Amount</td>
                            <td style="border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5">&nbsp;&nbsp;Rs. ' . number_format($sql->loan_recommended) . '</td>
                          </tr>
                          <tr>
                            <td style="border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5">Payment Received</td>
                            <td style="border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5">&nbsp;&nbsp;Rs. ' . number_format($loan_total_received_amount) . '</td>
                          </tr>
                          <tr>
                            <td style="border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5">Interest % (Annual)</td>
                            <td style="border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5">&nbsp;&nbsp;18%</td>
                          </tr>
                          <tr>
                            <td style="border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5">Total Outstanding Balance</td>
                            <td style="border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5">&nbsp;&nbsp;Rs. ' . number_format($total_due_amount) . '</td>
                          </tr>

                          <tr>
                            <td style="border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5">Final Total </td>
                            <td style="border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5">&nbsp;&nbsp;Rs. ' . number_format($total_final_amount) . '</td>
                          </tr>
                        </table>
                        <br> <br>
                        </td>
                    </tr>
                    <br/>
                    <tr>
                    <br>
                        <td>6. That as on date an amount of Rs. ' . number_format($total_final_amount) . ' (Settlement Amount) is due and payable by you in the aforesaid connection to our client.<br><br><br></td>

</tr>
                    <tr>
                        <td>I, therefore, by means of the present legal notice, call upon you, the notice, to make the payment of the aforesaid amount Rs. ' . number_format($total_final_amount) . '/- to my client within 7 days of the receipt of this notice by you, failing which my client shall be constrained to initiate legal proceedings against you under the provisions of section <b>420,468 & 471 of THE INDIAN PENAL CODE,1860 entirely at your Cost, Risk and responsibility</b>.<br><br><br></td>
                    </tr>
                    <tr>
                         <td>A copy of this notice has been retained in my office for further reference, record and action.</td>
                    </tr>
                    <tr>
                         <td style="text-align:right;margin:1px 0;"><b>Yours Faithfully</b></td>
                    </tr>
                    <tr>
                         <td style="text-align:right;margin:0 3px;"><img width="100" height="100" src="' . WEBSITE_URL . 'public/image/adv_sign.png" alt="background image"/></td>
                    </tr>
                    <tr>
                        <td style="text-align: right; margin: 0 3px;"><b>(HARSH TRIKHA) </b><br><b style="text-align: center;">Advocate</b></td>
                    </tr>
                </table>
                </body>
            </html>';
        } else {
            $pdf_html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                            <html xmlns="http://www.w3.org/1999/xhtml">
                               <head>
                                  <meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />

                                  <title>Legal Notice Reminder</title>
                               </head>
                               <body style="font-family: Courier, arial;">
                                  <table style="width:892px;height:1262px; margin: 0 auto;font-family: Courier, arial;">
                                     <tr>
                                        <td style="background:#000;">
                                           <center>
                                              <img style="width:auto;" src="' . WEBSITE_URL . 'public/image/adv_header.jpg" alt="background image"/>
                                           </center>
                                        </td>
                                     </tr>
                                     <tr>
                                        <td>
                                           <br>DELHI,<br>' . date('d,M Y') . '<br><br><br>
                                        </td>
                                     </tr>
                                     <tr>
                                        <td>
                                           To,
                                        </td>
                                     </tr>
                                     <tr>
                                        <td>' . $customer_name . '<br><br></td>
                                     </tr>
                                     <tr>
                                        <td>' . $aadhaar_address . '</td>
                                     </tr>
                                     <tr>
                                        <td style="text-align:center;"><b style="font-size: 20px;">LEGAL NOTICE</b> <b style="font-size: 15px;">(Without prejudice)</b></td>
                                        <br><br>
                                     </tr>
                                     <tr>
                                        <td>
                                           <span> <b> ' . $customer_name . ',</b></span><br/>
                                           <br>Under Instructions from and on behalf of my client ' . COMPANY_NAME . ' With the brand name “' . BRAND_NAME . '” having its ' . REGISTED_ADDRESS . ', I address you as under. <br><br>
                                        </td>
                                     </tr>
                                     <tr>
                                        <td>1. That you had approached my client for a short term loan of Rs. ' . number_format($loan_principle_payable_amount) . ' as you were in dire need of money on. ' . date('d,M Y', strtotime($sql->disbursal_date)) . ' <br><br></td>
                                     </tr>
                                     <tr>
                                        <td>2.That pursuant to the terms and conditions of the Lender and borrower
                                           agreement as agreed by you, you were provided the short term loan of Rs. ' . number_format($loan_principle_payable_amount) . ' with Loan Account Number - ' . $sql->loan_no . '.<br><br><br>
                                        </td>
                                     </tr>
                                     <tr>
                                        <td>3. That you the notice was unable to pay-back the aforesaid loan amount
                                           with mutually agreed interest rate within the period of 7 days of the receipt of the said legal notice
                                           by you the notice, failing which my client shall be constrained to initiate
                                           legal proceedings against you under the provisions of section 420,468 and
                                           471 of the Indian Penal Code, 1860 entirely at your Cost, Risk and
                                           Responsibility.<br><br><br>
                                        </td>
                                     </tr>
                                     <tr>
                                        <td>4. Thereafter a Legal Notice was served upon you to make the aforesaid
                                           payment within the period of <br><br><br>
                                        </td>
                                     </tr>
                                     <tr>
                                        <td>5. That you the Noticee after receiving the said Legal Notice requested my
                                           client to grant you some extra time as your financial condition is not as it
                                           was at the time of availing the said loan.<br><br>
                                        </td>
                                     </tr>
                                     <tr>
                                        <td>6. That on such request my client agreed to grant you some extra time in
                                           good faith and even offered you an EMI option to help you to settle/close
                                           the said loan and to save you from legal action against you the notice to
                                           recover the said amount.<br><br>
                                        </td>
                                     </tr>
                                     <tr>
                                        <td>7. That you the noticee kept deferring the payment date on one pretext or
                                           the other but did not pay-back the loan taken by you the notice till now
                                           after passage of stipulated period of time.<br><br>
                                        </td>
                                     </tr>
                                     <tr>
                                        <td>8. That it is clear by your conduct that you the notice have no intention to
                                           pay-back the aforesaid amount.<br><br>
                                        </td>
                                     </tr>
                                     <tr>
                                        <td>9. That below mentioned details are details of the loan:<br><br></td>
                                     </tr>
                                     <tr>
                                        <td>
                                           <table border="4" style="width: 100%; padding: 9px; border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5;text-align:center">
                                              <tr>
                                                 <th style="border-radius: 10px;background-color: #fefefe;font-size: 16px;font-weight:bold;border: 2px solid #d0e4f5">Particulars</th>
                                                 <th style="border-radius: 10px;background-color: #fefefe;font-size: 16px;font-weight:bold;border: 2px solid #d0e4f5">Amount</th>
                                              </tr>
                                              <tr>
                                                 <td style="border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5">Principal Amount</td>
                                                 <td style="border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5">&nbsp;&nbsp;Rs. ' . number_format($sql->loan_recommended) . '</td>
                                              </tr>
                                              <tr>
                                                 <td style="border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5">Payment Received</td>
                                                 <td style="border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5">&nbsp;&nbsp;Rs. ' . number_format($loan_total_received_amount) . '</td>
                                              </tr>
                                              <tr>
                                                 <td style="border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5">Interest % (Annual)</td>
                                                 <td style="border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5">&nbsp;&nbsp;18%</td>
                                              </tr>
                                              <tr>
                                                 <td style="border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5">Total Outstanding Balance</td>
                                                 <td style="border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5">&nbsp;&nbsp;Rs. ' . number_format($total_due_amount) . '</td>
                                              </tr>
                                              <tr>
                                                 <td style="border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5">Final Total </td>
                                                 <td style="border-radius: 10px;background-color: #fefefe;font-size: 15px;font-weight:bold;border: 2px solid #d0e4f5">&nbsp;&nbsp;Rs. ' . number_format($total_final_amount) . '</td>
                                              </tr>
                                           </table>
                                           <br> <br>
                                        </td>
                                     </tr>
                                     <br/>
                                     <tr>
                                        <td>I am writing to remind you of your outstanding debt to ' . COMPANY_NAME . ' With the brand name "' . BRAND_NAME . '" having its ' . REGISTED_ADDRESS . ' ,
                                           &amp; as per the loan agreement. Despite our previous attempts to reach a resolution, your account
                                           remains in default.</b><br><br><br>
                                        </td>
                                     </tr>
                                     <tr>
                                        <td><strong>As of ' . date('d,M Y') . ', the total outstanding balance on your loan account is
                                           ' . number_format($total_final_amount) . '  INR. It is imperative that you settle this outstanding amount
                                           within the next 7 days from the date of this notice. Failure to do so will
                                           leave us with no alternative but to initiate strict legal action against you U/s
                                           420,268 and 471 of the Indian Penal Code, 1860, as we believe you have not
                                           only failed to fulfil your repayment obligations but have also provided false
                                           statements to my client regarding your financial situation, which attracts
                                           action under section 406 of IPC,1860.</strong>
                                        </td>
                                     </tr>
                                     <tr>
                                        <td>
                                           Should you fail to comply with this notice within the stipulated 7-day period,
                                           my client shall be left with no choice but to pursue all available legal remedies,
                                           which may include filing a lawsuit to recover the outstanding debt and my legal
                                           fees of Rs. 11,000(Rupees Eleven Thousand Only).
                                        </td>
                                     </tr>
                                     <tr>
                                        <td>This notice serves as your final opportunity to resolve this matter amicably. We
                                           strongly advise you to consider the legal and financial implications of continued
                                           non-compliance.
                                        </td>
                                     </tr>
                                     <tr>
                                        <td>Copy of this notice has been retained in my office for further reference, record,
                                           and action.
                                        </td>
                                     </tr>
                                     <tr>
                                        <td style="text-align:right;margin:1px 0;"><b>Yours Faithfully</b></td>
                                     </tr>
                                     <tr>
                                        <td style="text-align:right;margin:0 3px;"><img width="100" height="100" src="' . WEBSITE_URL . 'public/image/adv_sign.png" alt="background image"/></td>
                                     </tr>
                                     <tr>
                                        <td style="text-align: right; margin: 0 3px;"><b>(HARSH TRIKHA) </b><br><b style="text-align: center;">Advocate</b></td>
                                     </tr>
                                  </table>
                               </body>
                            </html>';
        }
        $file_name = "legal_notice_" . $lead_id . "_" . rand(1000, 9999) . ".pdf";

        $file_path_with_name = TEMP_UPLOAD_PATH . $file_name;

        require_once __DIR__ . '/../../vendor/autoload.php';

        $mpdf = new \Mpdf\Mpdf();

        $mpdf->WriteHTML($pdf_html);

        $mpdf->Output($file_path_with_name, 'F');

        // print_r($pdf_html);

        if (file_exists($file_path_with_name)) {
            require_once(COMPONENT_PATH . "CommonComponent.php");
            $CommonComponent = new CommonComponent();
            $request_array = array();
            $request_array['flag'] = 1;
            $request_array['file'] = base64_encode(file_get_contents($file_path_with_name));
            $request_array['ext'] = pathinfo($file_path_with_name, PATHINFO_EXTENSION);

            $upload_return = $CommonComponent->upload_document($lead_id, $request_array);

            if ($upload_return['status'] == 1) {
                $return_array['status'] = 1;
                $file_name = $upload_return['file_name'];
                unlink($file_path_with_name);
                $this->updateLeads(['lead_id' => $lead_id], ['loan_legal_notice_date' => date('Y-m-d H:i:s'), 'loan_legal_notice_user_id' => $_SESSION['isUserSession']['user_id'], 'loan_legal_notice_name' => $file_name], 'loan');
            }
        } else {
            $return_array['errors'] = "File does not exist. Please check offline";
        }

        $dataArr = [
            'legal_notice_lead_id' => $lead_id,
            'legal_notice_loan_no' => $sql->loan_no,
            'legal_notice_type_id' => $loan_legal_sent_type,
            'legal_notice_sent_status' => 1,
            'legal_notice_sent_datetime' => date('Y-m-d H:i:s'),
            'legal_notice_sent_txn_no' => '',
            'legal_notice_return_remarks' => '',
            'legal_notice_api_status_id' => 1,
            'legal_notice_errors' => 0,
            'legal_notice_total_dpd_days' => $tenure,
            'legal_notice_total_due_amount' => $total_final_amount,
            'legal_notice_total_received_amount' => $loan_total_received_amount,
            'legal_notice_user_id' => $_SESSION['isUserSession']['user_id'],
            'legal_notice_created_on' => date('Y-m-d H:i:s'),
            'legal_notice_file_name' => $file_name
        ];

        $this->db->insert('loan_legal_notice_logs', $dataArr);

        require_once(COMPONENT_PATH . 'includes/functions.inc.php');

        $return_array = common_send_email($customer_email, $subject, $pdf_html, INFO_EMAIL, LEGAL_EXECUTIVE_EMAIL, LEGAL_EMAIL, LEGAL_EMAIL, "", $file_name, "LEGAL_NOTICE_" . date("Ymd") . ".pdf");

        $sms_request = array();
        $sms_request['lead_id'] = $lead_id;
        $sms_request['mobile'] = $customer_mobile; //
        $sms_request['name'] = $sql->customer_name;

        $CommonComponent->payday_sms_api(18, $lead_id, $sms_request);

        $lead_followup_insert_array = [
            'lead_id' => $lead_id,
            'user_id' => $user_id,
            'status' => $sql->status,
            'stage' => $sql->stage,
            'lead_followup_status_id' => $sql->lead_status_id,
            'remarks' => (($loan_legal_sent_type == 3) ? "Reminder Legal Notice Sent to Customer" : "Legal Notice Sent to Customer"),
            'created_on' => date("Y-m-d H:i:s")
        ];

        $this->insert($lead_followup_insert_array, 'lead_followup');

        return $return_array;
    }

    public function send_docs_upload_link($lead_id, $lead_details) {

        $return_array = array('status' => 1, 'message' => '');

        if (empty($lead_id)) {
            return null;
        }

        $customer_name = $lead_details['first_name'];
        $mobile = $lead_details['mobile'];
        $email = $lead_details['email'];
        $lead_reference_no = $lead_details['lead_reference_no'];

        $link_expire_datetime = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $send_url = WEBSITE_URL . 'document-upload/' . $this->encrypt->encode($lead_id) . '?sessionId=' . base64_encode($link_expire_datetime);

        require_once(COMPONENT_PATH . 'CommonComponent.php');
        $CommonComponent = new CommonComponent();
        $res = $CommonComponent->call_url_shortener_api($send_url, $lead_id);
        $short_url = $res['short_url'];

        $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
                <head>
                    <meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
                    <title>Customer Upload Documents</title>
                </head>
                <body>
                    <table width="778" border="0" align="center" cellpadding="0" cellspacing="0" style="border:solid 1px #ccc; font-family:Arial, Helvetica, sans-serif;">
                        <tr>
                            <td align="center"><img style="margin-top:10px" src="' . EMAIL_BRAND_LOGO . '" width="180" height="85" /></td>
                        </tr>
                        <tr>
                            <td><img style="margin-top:10px" src="' . WEBSITE_URL . '/public/images/document_upload_banner.jpg" width="auto" /></td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="padding:0 10px"><strong style = "color:#0463A3;">Dear ' . $customer_name . ', </strong></td>
                        </tr>
                        <tr>
                            <td style="padding:0 10px"><p style = "margin:0px;color: #000;line-height: 25px;">We hope this mail finds you well. In our efforts to provide you with the best loan offers, we kindly request that you upload the necessary documents to complete your Loan Application. These documents help us verify and protect your account, ensuring that it remains secure and compliant with regulatory requirements.</p></td>
                        </tr>
                        <tr>
                            <td style="padding:0 10px"><p style = "margin:0px;color: #000;line-height: 25px;">To upload your documents, please click on the following link:</p></td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td align = "center" style = "text-align:center;padding:0 10px"><a href = "' . $short_url . '" target = "_blank" style = "background:#0463a3;border-radius: 3px;padding: 8px 30px;color: #fff;text-decoration: blink;font-weight: bold;">Click Here</a></td>
                        </tr>
                        <tr>
                            <td style="padding:0 10px"><p style = "margin:0px;margin-top:15px;color: #000;line-height: 25px;border-radius: 3px;">In case the button is not working please copy & paste below link in your browser.</p></td>
                        </tr>
                        <tr>
                            <td style="padding:0 10px"><p style = "margin:0px;color: #000;line-height: 25px;color:#0463a3">' . $short_url . '</p></td>
                        </tr>
                        <tr>
                            <td style="padding:0 10px"><p style = "margin:0px;margin-top:10px;color: #000;line-height: 25px;color:#0463a3">Best Wishes<br>Bharatloan</p>
                        </tr>
                        <tr>
                                            <td colspan = "4" align = "center" valign = "middle" style = "border-top:solid 1px #ddd; padding-top:5px;">
                                                <a href = "' . APPLE_STORE_LINK . '" target = "_blank"><img src = "' . APPLE_STORE_ICON . '" alt = "app_store" width = "100" height = "30" style = "border-radius: 50px;"></a>
                                                <a href = "' . LINKEDIN_LINK . '" target = "_blank"> <img src = "' . LINKEDIN_ICON . '" alt = "linkdin" width = "32" height = "32" /></a>
                                                <a href = "' . INSTAGRAM_LINK . '" target = "_blank"> <img src = "' . INSTAGRAM_ICON . '" alt = "instagram" width = "32" height = "32" /></a>
                                                <a href = "' . FACEBOOK_LINK . '" target = "_blank"> <img src = "' . FACEBOOK_ICON . '" alt = "facebook" width = "32" height = "32" /></a>
                                                <a href = "' . TWITTER_LINK . '" target = "_blank" style = "color:#fff;"> <img src = "' . TWITTER_ICON . '" alt = "twitter" width = "32" height = "32" /> </a>
                                                <a href = "' . YOUTUBE_LINK . '" target = "_blank" style = "color:#fff;"> <img src = "' . YOUTUBE_ICON . '" alt = "youtube" width = "32" height = "32" /> </a>
                                                <a href = "' . ANDROID_STORE_LINK . '" target = "_blank"> <img src = "' . ANDROID_STORE_ICON . '" alt = "google_play" width = "100" height = "30" style = "border-radius: 50px;"></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan = "4" align = "center" valign = "middle" bgcolor = "#e52255" style = "padding:10px; color:#fff; font-weight:normal; font-size:16px;"><a href = "tel:' . REGISTED_MOBILE . '" style = "color:#fff; text-decoration:blink;"><img src = "' . PHONE_ICON . '" width = "16" height = "16" alt = "phone-icon" style = "margin-bottom: -2px;"> ' . REGISTED_MOBILE . ' </a> <a href = "' . WEBSITE_URL . '" target = "_blank" style = "color:#fff; text-decoration:blink;"><img src = "' . WEB_ICON . '" width = "16" height = "16" alt = "web-icon" style = "margin-bottom: -2px;"> ' . WEBSITE . ' </a> <img src = "' . EMAIL_ICON . '" width = "16" height = "16" alt = "email-icon" style = "margin-bottom: -2px;"><a href = "mailto:' . INFO_EMAIL . '" style = "color:#fff; text-decoration:blink;">' . INFO_EMAIL . ' </a></td>
                                        </tr>
                    </table>
                </body>
            </html>';

        require_once(COMPONENT_PATH . 'includes/functions.inc.php');

        $send_email = common_send_email($email, 'Request for Document Upload | ' . BRAND_NAME, $message);

        if (empty($send_email['status']) || $send_email['status'] != 1) {
            $return_array['status'] = 0;
            $return_array['message'] = 'Mail not sent';
        }

        $sms_request = array();
        $sms_request['lead_id'] = $lead_id;
        $sms_request['mobile'] = $mobile;
        $sms_request['name'] = $customer_name;
        $sms_request['doc_link'] = $short_url;
        $sms_request['refrence_no'] = $lead_reference_no;
        $send_sms = $CommonComponent->payday_sms_api(20, $lead_id, $sms_request);

        //        if ($send_sms['status'] != 1) {
        //            $return_array['status'] = 0;
        //            $return_array['message'] = 'SMS not sent';
        //        }

        return $return_array;
    }

    public function sent_link_for_customer_sms($lead_id) {
        $send_url = WEBSITE_URL . 'document-upload/' . $this->encrypt->encode($lead_id);
        require_once(COMPONENT_PATH . 'CommonComponent.php');
        $CommonComponent = new CommonComponent();
        $sql = 'SELECT LD.lead_id, LC.mobile, LC.first_name as name FROM leads LD INNER JOIN lead_customer LC ON (LC.customer_lead_id=LD.lead_id) WHERE LD.lead_id=' . $lead_id;
        $result = $this->db->query($sql);
        if ($result->num_rows() > 0) {
            $app_data = $result->row_array();
            $sms_request = array();
            $sms_request['lead_id'] = $app_data['lead_id'];
            $sms_request['mobile'] = $app_data['mobile'];
            //$sms_request['mobile'] = '8809625493';
            $sms_request['name'] = $app_data['name'];
            $sms_request['upload_doc_link'] = $send_url;
            $response = $CommonComponent->payday_sms_api(19, $lead_id, $sms_request);
        }
    }

    public function get_list_audit_followup($lead_id) {
        $result = array('error' => '', 'success' => '', 'data' => array());
        $select = 'AD.id,AD.audit_assign_date_time,AD.audit_assign_user_id,AD.audit_user_id,AD.audit_status,AD.audit_remarks,AD.audit_created_on,AD.audit_lead_status_id,AD.audit_case_type_id,AD.audit_lead_id,U.name as assign_name,U1.name as assigned_name';
        $this->db->select($select);
        $this->db->from('lead_audit AD');
        $this->db->join('users U', 'U.user_id = AD.audit_assign_user_id', 'left');
        $this->db->join('users U1', 'U1.user_id = AD.audit_user_id', 'left');
        $this->db->where(['AD.audit_lead_id' => $lead_id, 'AD.audit_active' => 1, 'AD.audit_deleted' => 0]);
        $followup_array = $this->db->order_by('AD.id', 'ASC')->get();

        $data = '<div class="table-responsive">
            <table class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr class="table-primary">
                            <th class="whitespace">ID</th>
                            <th class="whitespace">Lead ID</th>
                            <th class="whitespace">Audit Generated By</th>
                            <th class="whitespace">Audit Created On</th>
                            <th class="whitespace">Audit Case Type</th>
                            <th class="whitespace">Audit By</th>
                            <th class="whitespace">Audit Remarks</th>
                            <th class="whitespace">Audit Lead Status Id</th>
                            <th class="whitespace">Current Stage</th>
                        </tr>
                    </thead>';
        if (!empty($followup_array->num_rows())) {
            foreach ($followup_array->result() as $colum) {
                if (isset($colum->audit_case_type_id) && $colum->audit_case_type_id == '1') {
                    $case_type = 'Pre Audit';
                } else {
                    $case_type = 'Post Audit';
                }
                $data .= '<tbody>
                        <tr>
                            <td class="whitespace">' . (($colum->id) ? $colum->id : '-') . '</td>
                            <td class="whitespace">' . (($colum->audit_lead_id) ? $colum->audit_lead_id : '-') . '</td>
                            <td class="whitespace">' . (($colum->assign_name) ? $colum->assign_name : '-') . '</td>
                            <td class="whitespace">' . (($colum->audit_created_on) ? date("d-m-Y H:i:s", strtotime($colum->audit_created_on)) : '-') . '</td>
                            <td class="whitespace">' . (($case_type) ? $case_type : '-') . '</td>
                            <td class="whitespace">' . (($colum->assigned_name) ? $colum->assigned_name : '-') . '</td>
                            <td class="whitespace">' . (($colum->audit_remarks) ? $colum->audit_remarks : '-') . '</td>
                            <td class="whitespace">' . (($colum->audit_lead_status_id) ? $colum->audit_lead_status_id : '-') . '</td>
                            <td class="whitespace">' . (($colum->audit_status) ? $colum->audit_status : '-') . '</td>
                        </tr>';
            }
        } else {
            $data .= '<tbody><tr><td colspan="9" style="text-align:center;color:red;">Record Not Found...</td></tr></tbody></table></div>';
        }

        $result['data'] = $data;

        return $result;
    }

    public function getAccountAggregatorLogs($lead_id, $method_id) {
        $return_array = array("status" => 0, "account_aggregator_logs" => array());

        if (!empty($lead_id) && !empty($method_id)) {

            $sql = "SELECT *";
            $sql .= " FROM api_account_aggregator_logs";
            $sql .= " WHERE  aa_lead_id=$lead_id AND aa_active=1 AND aa_deleted=0";
            $sql .= " AND aa_method_id=$method_id AND aa_api_status_id=1 AND aa_active=1 AND aa_deleted=0";
            $sql .= " ORDER BY aa_log_id DESC LIMIT 1";

            $accountAggregatorLogs = $this->db->query($sql);

            if ($accountAggregatorLogs->num_rows() > 0) {
                $return_array['status'] = 1;
                $return_array['account_aggregator_logs'] = $accountAggregatorLogs->row_array();
            }
        }

        return $return_array;
    }

    public function sent_account_aggregator_request_email($lead_id) {

        if (!empty($lead_id)) {

            $sql = "SELECT LD.lead_status_id, concat_ws(' ', LC.first_name, LC.middle_name, LC.sur_name) as customer_name, LD.email";
            $sql .= " FROM leads LD INNER JOIN lead_customer LC ON LC.customer_lead_id=LD.lead_id ";
            $sql .= " WHERE LD.lead_id=$lead_id";

            $sql = $this->db->query($sql)->row();

            $to = $sql->email;

            if (!empty($to)) {

                $lead_status_id = $sql->lead_status_id;

                $customer_name = $sql->customer_name;

                $enc_lead_id = $this->encrypt->encode($lead_id);

                require_once(COMPONENT_PATH . 'CommonComponent.php');

                $CommonComponent = new CommonComponent();

                $request_array['redirect_flag'] = 1;

                $request_array['redirect_url'] = WEBSITE_URL . "account-consent-thank-you?refstr=" . $enc_lead_id;

                $create_account_aggregator_link = $CommonComponent->call_payday_account_aggregator("CONSENT_HANDLE_REQUEST", $lead_id, $request_array);

                if ($create_account_aggregator_link['status'] == 1) {
                    if (!empty($create_account_aggregator_link['url'])) {

                        $url = $create_account_aggregator_link['url'];

                        $res = $CommonComponent->call_url_shortener_api($url, $lead_id);

                        $account_aggregator_register_url = $res['short_url'];

                        $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                                    <html xmlns="http://www.w3.org/1999/xhtml">
                                        <head>
                                            <meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
                                            <title>Account Aggregator</title>
                                        </head>
                                        <body style="padding: 0; margin: 0;">
                                            <table width="600" align="center" cellspacing="0" cellpadding="0" style="border: 1px solid #bebaba; background: url(images/bg_image.jpg);background-size: cover;">
                                                <tbody>
                                                    <tr>
                                                        <td align="center">
                                                            <table style="text-align: center;"  bgcolor="" cellspacing="0"  style="border: 1px solid #bebaba;" border="0" width="600" cellpadding="0">
                                                                <tbody>
                                                                    <tr>
                                                                        <td style="line-height: 0; padding-top: 0;">
                                                                            <a href="#/" target="_blank">
                                                                                <img src="' . WEBSITE_URL . 'public/images/banner_account_aggregator.jpg" alt="" width="600">
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                            <table style="text-align: center;"  bgcolor="" cellspacing="0"  style="background: url(images/bg_image.jpg);" border="0" width="600" cellpadding="0">
                                                                <tbody>
                                                                    <tr>
                                                                        <td colspan="3" style="border-radius: 11px; color: #000; font-size: 13px;line-height: 18px; text-align: left;padding: 26px;">

                                                                            <span width="300" cellpadding="0">
                                                                                Dear ' . ucwords($customer_name) . ',
                                                                                <br/><br/>
                                                                                We thank you for showing interest in Rupee112 Instant personal loan.
                                                                                <br/><br/>Your application process is pending a crucial step, which involves obtaining your consent to access your salary bank account for retrieving the most recent bank statement.
                                                                                <br />
                                                                                In order to process your loan application further, please give your consent on our Account Aggregator portal to share your bank statement securely.
                                                                                <br /><br/>
                                                                                To facilitate the continued processing of your loan application, we kindly request your consent to securely share your bank statement through our Account Aggregator portal.
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="3" style="border-radius: 11px; color: #000; font-size: 13px;line-height: 18px; text-align: center;padding: 10px;">
                                                                            <span width="300" cellpadding="0"><a href="' . $account_aggregator_register_url . '" style="border-radius: 20px;background-color: #df2b4d;border: none;color: #fff;font-size: 13px;font-weight: 600;padding: 5px 19px;margin: 2%;letter-spacing: 1px;text-decoration:none">Fetch Salary Account Bank Statement</a></span>
                                                                            <br />
                                                                            <br />
                                                                            <span width="300" cellpadding="0">If you are not able to click on the above button, then please copy and paste this URL ' . $account_aggregator_register_url . ' in the browser to proceed.</span>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                            <tr>
                                                                <td colspan="3" style="border-radius: 11px; color: #000; font-size: 14px;
                                                                    line-height: 35px; text-align: center;">
                                                                    <b style="background-color: #000062;padding: 10px 10px 7px 10px;font-weight: 100;border-radius: 20px;">
                                                                        <a style="font-size: 14px;color: #fff;font-weight: 100;text-decoration: none;letter-spacing: 1px;font-family:Times New Roman;">  <img src="' . PHONE_ICON . '"> ' . REGISTED_MOBILE . '</a> &nbsp;
                                                                        <a style="font-size: 14px;color: #fff;font-weight: 100;text-decoration: none;letter-spacing: 1px;font-family:Times New Roman">  <img src="' . WEB_ICON . '"> ' . WEBSITE_URL . ' </a> &nbsp;
                                                                        <a style="font-size: 14px;color: #fff;font-weight: 100;text-decoration: none;letter-spacing: 1px;font-family:Times New Roman">  <img src="' . EMAIL_ICON . '"> ' . INFO_EMAIL . '</a>
                                                                    </b>
                                                                </td>
                                                            </tr>
                                                            <table style="text-align: center;margin:0 auto">
                                                                <tbody>
                                                                    <tr>
                                                                        <td colspan="3">
                                                                            <div>
                                                                                <a href="' . ANDROID_STORE_LINK . '" target="_blank" style="text-decoration: none; vertical-align: middle;">
                                                                                    <img src="' . ANDROID_STORE_ICON . '" alt="google play"/>
                                                                                </a>
                                                                            </div>
                                                                        </td>
                                                                        <td colspan="3">
                                                                            <div style="background-color: #000062;     padding: 5px 12px 3px 12px;border-radius: 20px;">
                                                                                <a href="' . INSTAGRAM_LINK . '" target="_blank" style="    font-size: 14px;color: #fff;font-weight: 100;text-decoration: none;letter-spacing: 1px;align-items: center;display: inline-flex;">
                                                                                    <img src="' . INSTAGRAM_ICON . '" alt="Instagram"/>
                                                                                </a>
                                                                                <a href="' . FACEBOOK_LINK . '" target="_blank" style="    font-size: 14px;color: #fff;font-weight: 100;text-decoration: none;letter-spacing: 1px;align-items: center;display: inline-flex;">
                                                                                    <img src=" ' . FACEBOOK_ICON . ' " alt="Facebook"/>
                                                                                </a>
                                                                                <a href="' . TWITTER_LINK . '" target="_blank" style="    font-size: 14px;color: #fff;font-weight: 100;text-decoration: none;letter-spacing: 1px;align-items: center;display: inline-flex;">
                                                                                    <img src="' . TWITTER_ICON . '"alt="Twitter"/>
                                                                                </a>
                                                                                <a href="' . YOUTUBE_LINK . '" target="_blank" style="    font-size: 14px;color: #fff;font-weight: 100;text-decoration: none;letter-spacing: 1px;align-items: center;display: inline-flex;">
                                                                                    <img src="' . YOUTUBE_ICON . '" alt="Youtube">
                                                                                </a>
                                                                                <a href="' . LINKEDIN_LINK . '" target="_blank" style="    font-size: 14px;color: #fff;font-weight: 100;text-decoration: none;letter-spacing: 1px;align-items: center;display: inline-flex;">
                                                                                    <img src="' . LINKEDIN_ICON . '" alt="LinkedIn"/>
                                                                                </a>
                                                                            </div>
                                                                        </td>
                                                                        <td colspan="3">
                                                                            <div>
                                                                                <a href="' . APPLE_STORE_LINK . '" target="_blank" style="text-decoration: none; vertical-align: middle;">
                                                                                    <img src="' . APPLE_STORE_ICON . '" alt="Apple Store"/>
                                                                                </a>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </body>
                                    </html>';

                        require_once(COMPONENT_PATH . 'includes/functions.inc.php');

                        $return_array = common_send_email($to, BRAND_NAME . '  | CONSENT FOR BANK STATEMENT : ' . $customer_name, $message);

                        if ($return_array['status'] == 1) {
                            $lead_remark = "Account Aggregator email sent successfully.";
                            $data = "true";
                        } else {
                            $lead_remark = "Account Aggregator email sending failed.";
                            $data = "false";
                        }

                        $this->insertLeadFollowupLog($lead_id, $lead_status_id, $lead_remark);

                        $this->sent_account_aggregator_request_sms($lead_id, $account_aggregator_register_url);
                    } else {
                        $data = "false";
                    }
                } else {
                    $data = "false";
                }
            } else {
                $data = "false";
            }
        } else {
            $data = "false";
        }
        return $data;
    }

    public function sent_account_aggregator_request_sms($lead_id, $account_aggregator_register_url) {
        require_once(COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();

        if (!empty($lead_id) && !empty($account_aggregator_register_url)) {


            $sql = 'SELECT LD.lead_id, LC.mobile, LC.first_name as name FROM leads LD INNER JOIN lead_customer LC ON (LC.customer_lead_id=LD.lead_id) WHERE LD.lead_id=' . $lead_id;

            $result = $this->db->query($sql);

            if ($result->num_rows() > 0) {

                $app_data = $result->row_array();

                $sms_request = array();
                $sms_request['lead_id'] = $app_data['lead_id'];
                $sms_request['mobile'] = $app_data['mobile'];
                $sms_request['name'] = $app_data['name'];
                $sms_request['account_aggregator_link'] = $account_aggregator_register_url;

                $CommonComponent->payday_sms_api(23, $lead_id, $sms_request);
            }
        }
    }

    public function getRepaymentReminderSend($message_type = NULL) {
        // Get today's reminders to exclude these leads
        $getAllLeads = $this->db->select('lead_id')
            ->from('customer_msg_reminder')
            ->where('DATE(created_on) =', date("Y-m-d"))
            ->where('message_type', $message_type)
            ->get()
            ->result_array();

        $lead_ids = array_column($getAllLeads, 'lead_id');

        // Define the repayment date range
        $from_repayment_date = date('Y-m-d', strtotime('0 day'));
        $to_repayment_date = date('Y-m-d', strtotime('+5 days'));

        // Build the main query
        $this->db->select(
            '
			LD.lead_id, LD.first_name, LD.email, LD.mobile,
			LD.lead_status_id, LD.lead_final_disbursed_date,
			C.current_house, L.loan_no,
			L.loan_interest_payable_amount, L.loan_penalty_payable_amount,
			CAM.loan_recommended, CAM.repayment_amount, CO.received_amount,
			(CAM.repayment_amount - CO.received_amount) AS Due_Amount,
			CAM.repayment_date,
			DATEDIFF(DATE(CAM.repayment_date), CURDATE()) AS days_until_repayment'
        )
            ->from('leads LD')
            ->join('lead_customer C', 'LD.lead_id = C.customer_lead_id', 'LEFT')
            ->join('loan L', 'L.lead_id = LD.lead_id', 'LEFT')
            ->join('collection CO', 'CO.lead_id = LD.lead_id', 'LEFT')
            ->join('credit_analysis_memo CAM', 'LD.lead_id = CAM.lead_id', 'LEFT')
            ->where_in('LD.lead_status_id', [14, 19])
            ->where('CAM.repayment_date >=', $from_repayment_date)
            ->where('CAM.repayment_date <=', $to_repayment_date)
            ->group_by('LD.lead_id');

        if (!empty($lead_ids)) {
            $this->db->where_not_in('LD.lead_id', $lead_ids);
        }
        $result_repayment_reminder = $this->db->get()->result_array();

        return $result_repayment_reminder;
    }

    public function getUserList($stage = "") {

        if (in_array($stage, ["S2", "S3"])) {
            $condition = "AND UR.user_role_type_id = 2";
        } elseif (in_array($stage, ["S5", "S6", "S10", "S11", "S12", "S14", "S16"])) {
            $condition = "AND UR.user_role_type_id = 3";
        } else {
            return [];
        }

        $query = "SELECT
                        U.user_id,
                        U.name
                    FROM
                        users U
                        INNER JOIN user_roles UR ON (U.user_id = UR.user_role_user_id)
                    WHERE
                        UR.user_role_active = 1
                        AND U.user_status_id = 1
                        AND U.user_is_loanwalle != 0
                        " . $condition . "
                    GROUP BY
                        U.name
                    ORDER BY
                        U.name ASC";

        $userList_array = array();
        $tempDetails = $this->db->query($query);

        foreach ($tempDetails->result_array() as $user_data) {
            $userList_array[$user_data['user_id']] = $user_data['name'];
        }
        return $userList_array;
    }

    public function getAAconsentLog($lead_id, $methodId, $fields = []) {
        if (empty($fields)) {
            $select = ' b.* ';
        } else {
            $select = implode(',', $fields);
        }

        $where = [
            'b.aa_api_status_id' => 1,
            'a.lead_id' => $lead_id,
            'b.aa_active' => 1
        ];
        if (!empty($methodId)) {
            $where['b.aa_method_id'] = $methodId;
        }

        $this->db->select($select);
        $this->db->from('leads a');
        $this->db->join('api_account_aggregator_logs b', 'a.lead_id = b.aa_lead_id', 'left');
        $this->db->where($where);
        $this->db->order_by('b.aa_id', 'DESC');
        $this->db->limit(1);
        return $data = $this->db->get()->row();
    }

    public function masterPincodeCount($conditions) {
        $this->db->select('COUNT(DISTINCT MP.m_pincode_city_id) as total')
            ->from('master_pincode MP')
            ->where($conditions);
        $query = $this->db->get();
        $result = $query->row();
        return !empty($result) ? $result->total : 0;
    }

    public function getAAconsentAllLog($lead_id, $methodId = null, $fields = [], $listAll = false) {
        if (empty($fields)) {
            $select = ' * ';
        } else {
            $select = implode(',', $fields);
        }
        $where = [
            'aa_api_status_id' => 1,
            'aa_lead_id' => $lead_id,
            'aa_active' => 1
        ];
        if (!empty($methodId)) {
            $where['aa_method_id'] = $methodId;
        }
        $this->db->select($select);
        $this->db->from('api_account_aggregator_logs');
        $this->db->where($where);
        $this->db->order_by('aa_id', 'DESC');
        if ($listAll == false) {
            $this->db->limit(1);
            return $data = $this->db->get()->result_array();
        } else {
            return $data = $this->db->get()->result_array();
        }
    } 
}
