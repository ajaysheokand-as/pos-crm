<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Collection_Model extends CI_Model {

    public $result_array = array('status' => 0);
    public $master_income_type = array(1 => "Salaried", 2 => "Self-Employed");
    public $visit_type = array(1 => 'Residence', 2 => 'Office');

    public function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Kolkata");
    }

    public function globel_insert($table, $data) {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    public function globel_update($table, $data, $conditions) {
        $this->db->where($conditions);
        return $this->db->update($table, $data);
    }

    public function check_validationToken($token) {
        $result_array = array("status" => 0);
        $currentdate = date('Y-m-d H:i:s');

        $select = 'MLT.mlt_id, MLT.mlt_user_id, MLT.mlt_valid_datetime, MLT.mlt_token_valid_time';

        $conditions['MLT.mlt_token'] = $token;
        $conditions['MLT.mlt_active'] = 1;
        $conditions['MLT.mlt_deleted'] = 0;
//            $conditions['mlt_valid_datetime >='] = $currentdate;

        $this->db->select($select);
        $this->db->from('mobileapp_login_trans MLT');
        $this->db->join('users U', "U.user_id = MLT.mlt_user_id AND U.user_last_login_datetime = MLT.mlt_login_time");
        $this->db->where($conditions);
        $query = $this->db->order_by('MLT.mlt_id', 'DESC')->get();

        if (!empty($query->num_rows())) {
            $token_status = $query->row_array();

            $mlt_valid_datetime = $token_status['mlt_valid_datetime'];
            $mlt_token_valid_time = $token_status['mlt_token_valid_time'];
            $minutes_to_add = 30;

            $time = new DateTime($mlt_valid_datetime);
            $time->add(new DateInterval('PT' . $minutes_to_add . 'M'));
            $mlt_token_valid_timenew = $mlt_token_valid_time + $minutes_to_add;
            $newexptime = $time->format('Y-m-d H:i:s');

            $conditions_update['mlt_id'] = $token_status['mlt_id'];

            $update_mobileapp_login_trans['mlt_valid_datetime'] = $newexptime;
            $update_mobileapp_login_trans['mlt_token_valid_time'] = $mlt_token_valid_timenew;
            $update_mobileapp_login_trans['mlt_updated_on'] = date('Y-m-d H:i:s');

            $res = $this->globel_update('mobileapp_login_trans', $update_mobileapp_login_trans, $conditions_update);
            if (!empty($res)) {
                $result_array['status'] = 1;
            }
        }
        return $result_array;
    }

    public function get_total_collection_lists($conditions = null, $limit = null, $start = null) {
        $result_array = array();
        $collection_array = array();
        $conditions["LCV.col_visit_active"] = 1;
        $conditions["LCV.col_visit_deleted"] = 0;
        $conditions["LCV.col_visit_field_status_id"] = 2;

        $select = "LD.lead_id, LD.loan_no, CONCAT(LC.first_name ,' ', LC.middle_name ,' ', LC.sur_name) as full_name, MS.status_name as lead_status, ";
        $select .= " LC.mobile, LC.alternate_mobile, LC.email, LC.alternate_email, ";
        $select .= " MVS.m_visit_heading as col_visit_field_status, ";
//        $select .= " DOCS.file as customer_profile, ";
        $select .= " '' as customer_profile, ";
        $select .= " Lead_applied_state.m_state_name as customer_lead_applied_state, Lead_applied_city.m_city_name as customer_lead_applied_city, ";
        $select .= " LCV.col_visit_id, visit_allocated_by.name as visit_allocated_by, LCV.col_visit_allocate_on, LCV.col_visit_address_type ";

        $this->db->select($select);
        $this->db->from("loan_collection_visit LCV");
        $this->db->join("leads LD", "LCV.col_lead_id = LD.lead_id");
        $this->db->join("lead_customer LC", "LCV.col_lead_id = LC.customer_lead_id");
        $this->db->join("master_state Lead_applied_state", "Lead_applied_state.m_state_id = LD.state_id");
        $this->db->join("master_city Lead_applied_city", "Lead_applied_city.m_city_id = LD.city_id");
        $this->db->join("master_status MS", "MS.status_id = LD.lead_status_id");
        $this->db->join("master_visit_status MVS", "MVS.m_visit_id = LCV.col_visit_field_status_id");
        $this->db->join("users visit_allocated_by", "visit_allocated_by.user_id = LCV.col_visit_scm_id");
//        $this->db->join("docs DOCS", "DOCS.lead_id = LD.lead_id AND DOCS.docs_master_id = 18", "LEFT");

        $this->db->where($conditions);
        $this->db->where_in("LD.lead_status_id", [14, 18, 19]);

        if (!empty($limit)) {
            $this->db->limit($limit, $start);
        }

        $tempDetails = $this->db->get();

//            echo $this->db->last_query(); exit;

        if (!empty($tempDetails->num_rows())) {
            $result_array['status'] = 1;
            foreach ($tempDetails->result_array() as $columns) {
                $data['customer_lead_id'] = $this->encrypt->encode($columns['lead_id']);
                $data['customer_visit_id'] = $this->encrypt->encode($columns['col_visit_id']);
                $data['customer_loan_no'] = ($columns['loan_no']) ? $columns['loan_no'] : "-";
                $data['customer_loan_status'] = ($columns['lead_status']) ? $columns['lead_status'] : "-";
                $data['customer_visit_status'] = ($columns['col_visit_field_status']) ? $columns['col_visit_field_status'] : "-";
                $data['customer_fullname'] = ($columns['full_name']) ? $columns['full_name'] : "-";
                $data['customer_mobile'] = ($columns['mobile']) ? $columns['mobile'] : "-";
                $data['customer_alternate_mobile'] = ($columns['alternate_mobile']) ? $columns['alternate_mobile'] : "-";
                $data['customer_lead_applied_state'] = ($columns['customer_lead_applied_state']) ? $columns['customer_lead_applied_state'] : "-";
                $data['customer_lead_applied_city'] = ($columns['customer_lead_applied_city']) ? $columns['customer_lead_applied_city'] : "-";
                $data['customer_visit_allocated_by'] = ($columns['visit_allocated_by']) ? $columns['visit_allocated_by'] : "-";
                $data['customer_visit_allocated_on'] = ($columns['col_visit_allocate_on']) ? date("d-m-Y H:i:s", strtotime($columns['col_visit_allocate_on'])) : "-";
                $data['customer_profile'] = ($columns['customer_profile']) ? (COLLEX_DOC_URL . $columns['customer_profile']) : "-";
                $data['customer_visit_address_type'] = ($columns['col_visit_address_type']) ? ($columns['col_visit_address_type']) : "-"; // 1=>residence, 2=>office

                $collection_array[] = $data;
            }
            $result_array['data']['collection_list'] = $collection_array;
        }
        return $result_array;
    }

    public function get_lead_details($lead_id = null) {

        $conditions['LD.lead_id'] = $lead_id;

        $this->db->select('LD.*, LC.first_name, LC.middle_name, LC.sur_name');
        $this->db->from('leads LD');
        $this->db->join('lead_customer LC', "LC.customer_lead_id = LD.lead_id");

        if (!empty($lead_id)) {
            $this->db->where($conditions);
        }

        $tempDetails = $this->db->get();

        return $this->result_array = $tempDetails;
    }

    public function get_visit_details($conditions) {

        $this->db->select('*');
        $this->db->from('loan_collection_visit');

        $conditions['col_visit_active'] = 1;
        $conditions['col_visit_deleted'] = 0;

        if (!empty($conditions)) {
            $this->db->where($conditions);
        }

        $tempDetails = $this->db->get();

        return $this->result_array = $tempDetails;
    }

    public function get_cfe_last_visits($user_id) {
        $result_array = array("status" => 0);

        $select = 'LCV.col_visit_id, LCV.col_lead_id as lead_id, LCV.col_visit_address_type, LCV.col_visit_allocate_on, CFE.name as visit_allocated_to, ';
        $select .= ' LCV.col_fe_visit_end_datetime, LCV.col_fe_rtoh_return_type ';

        $this->db->select($select);
        $this->db->from('loan_collection_visit LCV');
        $this->db->join('users CFE', "CFE.user_id = LCV.col_visit_allocated_to");

        $conditions['LCV.col_visit_field_status_id'] = 5;
        $conditions['LCV.col_visit_allocated_to'] = $user_id;
//            $conditions['date(LCV.col_fe_visit_end_datetime)'] = date('Y-m-d');
        $conditions['LCV.col_visit_active'] = 1;
        $conditions['LCV.col_visit_deleted'] = 0;

        $this->db->where($conditions);

        $tempDetails = $this->db->order_by('LCV.col_fe_visit_end_datetime', 'desc')->limit(1)->get();

        if (!empty($tempDetails->num_rows())) {
            $row = $tempDetails->row_array();

            $data['col_visit_id'] = $row['col_visit_id'];
            $data['lead_id'] = $row['lead_id'];
            $data['col_visit_address_type'] = $row['col_visit_address_type'];
            $data['col_visit_allocate_on'] = $row['col_visit_allocate_on'];
            $data['visit_allocated_to'] = $row['visit_allocated_to'];
            $data['col_fe_visit_end_datetime'] = $row['col_fe_visit_end_datetime'];

            $result_array['status'] = 1;
            $result_array['data']['running_visit'] = $data;
        }

        return $result_array;
    }

    public function get_user_running_visit($user_id) {
        $result_array = array("status" => 0);

        $select = 'LCV.col_visit_id, LCV.col_lead_id as lead_id, LCV.col_visit_address_type, CE.name as visit_requested_by, ';
        $select .= ' LCV.col_visit_requested_by_remarks, LCV.col_visit_requested_datetime, ';
        $select .= ' SCM.name as visit_allocated_by, LCV.col_visit_scm_remarks, LCV.col_visit_allocate_on, ';
        $select .= ' CFE.name as visit_allocated_to, LCV.col_visit_field_status_id, LCV.col_visit_field_schedule_datetime, ';
        $select .= ' LCV.col_visit_field_datetime, LCV.col_visit_field_remarks, LCV.col_visit_created_on, LCV.col_visit_updated_on, ';
        $select .= ' LCV.col_fe_visit_trip_status_id, LCV.col_fe_visit_trip_start_datetime, LCV.col_fe_visit_trip_stop_datetime, LCV.col_fe_visit_end_datetime, ';

        $this->db->select($select);
        $this->db->from('loan_collection_visit LCV');
        $this->db->join('users CE', "CE.user_id = LCV.col_visit_requested_by");
        $this->db->join('users SCM', "SCM.user_id = LCV.col_visit_scm_id");
        $this->db->join('users CFE', "CFE.user_id = LCV.col_visit_allocated_to");

        $conditions['LCV.col_visit_allocated_to'] = $user_id;
        $conditions_in['LCV.col_fe_visit_trip_status_id'] = [1, 3];
        $conditions['LCV.col_visit_active'] = 1;
        $conditions['LCV.col_visit_deleted'] = 0;

        $this->db->where($conditions);
        $this->db->where_in($conditions_in);

        $tempDetails = $this->db->get();
        if (!empty($tempDetails->num_rows())) {
            $row = $tempDetails->row_array();
            $data['followup_visit_id'] = ($row['col_visit_id']) ? $this->encrypt->encode($row['col_visit_id']) : "-";
            $data['followup_lead_id'] = ($row['lead_id']) ? $this->encrypt->encode($row['lead_id']) : "-";
            $data['followup_visit_address_type'] = ($row['col_visit_address_type']) ? $row['col_visit_address_type'] : "-";
            $data['followup_visit_requested_by'] = ($row['visit_requested_by']) ? $row['visit_requested_by'] : "-";
            $data['followup_visit_requested_by_datetime'] = ($row['col_visit_requested_datetime']) ? date("d-m-Y H:i:s", strtotime($row['col_visit_requested_datetime'])) : "-";
            $data['followup_visit_requested_by_remarks'] = ($row['col_visit_requested_by_remarks']) ? $row['col_visit_requested_by_remarks'] : "-";
            $data['followup_visit_allocated_by'] = ($row['visit_allocated_by']) ? $row['visit_allocated_by'] : "-";
            $data['followup_visit_allocated_by_datetime'] = ($row['col_visit_allocate_on']) ? date("d-m-Y H:i:s", strtotime($row['col_visit_allocate_on'])) : "-";
            $data['followup_visit_allocated_by_remarks'] = ($row['col_visit_scm_remarks']) ? $row['col_visit_scm_remarks'] : "-";
            $data['followup_visit_allocated_to'] = ($row['visit_allocated_to']) ? $row['visit_allocated_to'] : "-";
            $data['followup_visit_allocated_to_remarks'] = ($row['col_visit_field_remarks']) ? $row['col_visit_field_remarks'] : "-";
            $data['followup_visit_start_datetime'] = ($row['col_fe_visit_trip_start_datetime']) ? date("d-m-Y H:i:s", strtotime($row['col_fe_visit_trip_start_datetime'])) : "-";
            $data['followup_visit_stop_datetime'] = ($row['col_fe_visit_trip_stop_datetime']) ? date("d-m-Y H:i:s", strtotime($row['col_fe_visit_trip_stop_datetime'])) : "-";
            $data['followup_visit_end_datetime'] = ($row['col_fe_visit_end_datetime']) ? date("d-m-Y H:i:s", strtotime($row['col_fe_visit_end_datetime'])) : "-";
            $data['followup_visit_trip_status_id'] = ($row['col_fe_visit_trip_status_id']) ? $row['col_fe_visit_trip_status_id'] : "-";

            $result_array['status'] = 1;
            $result_array['data']['running_visit'] = $data;
        }

        return $result_array;
    }

    public function get_loan_details($lead_id = null) {
        $result_array = array();
        $conditions['LD.lead_id'] = $lead_id;

        $select = 'LD.lead_id, LD.loan_no, MS.status_name as lead_status, LD.created_on as lead_applied_on, LC.pancard, LD.cibil, ';
        $select .= ' LD.pincode, MDS.data_source_name, LC.gender, CAM.borrower_age, ';
        $select .= ' TRIM(CONCAT_WS (" ",LC.first_name, LC.middle_name, LC.sur_name)) as full_name, LC.email, ';
        $select .= ' LC.alternate_email, LC.mobile, LC.alternate_mobile, LC.dob, ';
        $select .= ' CE.salary_mode, CE.monthly_income, CE.income_type, ';
        $select .= ' Lead_applied_state.m_state_name as customer_lead_applied_state, Lead_applied_city.m_city_name as customer_lead_applied_city, LD.pincode, ';
        $select .= ' CAM.loan_recommended as loan_amount, CAM.roi, CAM.tenure, CAM.admin_fee, CAM.disbursal_date, CAM.repayment_date, CAM.adminFeeWithGST, ';
        $select .= ' CAM.total_admin_fee, CAM.net_disbursal_amount, CAM.repayment_amount, CAM.cam_advance_interest_amount ';

        $this->db->select($select);
        $this->db->from("leads LD");
        $this->db->join("lead_customer LC", "LC.customer_lead_id = LD.lead_id AND LC.customer_active = 1 AND LC.customer_deleted = 0");
        $this->db->join("customer_employment CE", "CE.lead_id = LD.lead_id AND CE.emp_active = 1 AND CE.emp_deleted = 0");
        $this->db->join("master_state Lead_applied_state", "Lead_applied_state.m_state_id = LD.state_id");
        $this->db->join("master_city Lead_applied_city", "Lead_applied_city.m_city_id = LD.city_id");
        $this->db->join("master_status MS", "MS.status_id = LD.lead_status_id");
        $this->db->join("master_data_source MDS", "MDS.data_source_id = LD.lead_data_source_id");
        $this->db->join("credit_analysis_memo CAM", "CAM.lead_id = LD.lead_id AND CAM.cam_active = 1 AND CAM.cam_deleted = 0");

        if (!empty($lead_id)) {
            $this->db->where($conditions);
        }

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $columns = $tempDetails->row_array();

            $result_array['status'] = 1;
            $data['customer_lead_id'] = $this->encrypt->encode($columns['lead_id']);
            $data['customer_loan_no'] = ($columns['loan_no']) ? $columns['loan_no'] : "-";
            $data['customer_loan_status'] = ($columns['lead_status']) ? $columns['lead_status'] : "-";
            $data['customer_fullname'] = ($columns['full_name']) ? $columns['full_name'] : "-";
            $data['customer_lead_source_name'] = ($columns['data_source_name']) ? $columns['data_source_name'] : "-";
            $data['customer_gender'] = ($columns['gender']) ? $columns['gender'] : "-";
            $data['customer_age'] = ($columns['borrower_age']) ? $columns['borrower_age'] : "-";
            $data['customer_dob'] = ($columns['dob']) ? date("d-m-Y", strtotime($columns['dob'])) : "-";
            $data['customer_mobile'] = ($columns['mobile']) ? $columns['mobile'] : "-";
            $data['customer_alternate_mobile'] = ($columns['alternate_mobile']) ? $columns['alternate_mobile'] : "-";
            $data['customer_email'] = ($columns['email']) ? $columns['email'] : "-";
            $data['customer_alternate_email'] = ($columns['alternate_email']) ? $columns['alternate_email'] : "-";
            $data['customer_pancard'] = ($columns['pancard']) ? $columns['pancard'] : "-";
            $data['customer_income_type'] = ($columns['income_type']) ? $this->master_income_type[$columns['income_type']] : "-";
            $data['customer_salary_mode'] = ($columns['salary_mode']) ? $columns['salary_mode'] : "-";
            $data['customer_monthly_income'] = ($columns['monthly_income']) ? number_format((float) $columns['monthly_income'], 2, '.', '') : "-";
            $data['customer_lead_applied_state'] = ($columns['customer_lead_applied_state']) ? $columns['customer_lead_applied_state'] : "-";
            $data['customer_lead_applied_city'] = ($columns['customer_lead_applied_city']) ? $columns['customer_lead_applied_city'] : "-";
            $data['customer_lead_applied_pincode'] = ($columns['pincode']) ? $columns['pincode'] : "-";
            $data['customer_lead_applied_on'] = ($columns['lead_applied_on']) ? date("d-m-Y H:i:s", strtotime($columns['lead_applied_on'])) : "-";
            $data['customer_loan_amount'] = ($columns['loan_amount']) ? number_format($columns['loan_amount'], 2, '.', '') : "-";
            $data['customer_loan_tenure'] = ($columns['tenure']) ? $columns['tenure'] : "-";
            $data['customer_loan_admin_fee'] = ($columns['admin_fee']) ? number_format((float) $columns['admin_fee'], 2, '.', '') : "-";
            $data['customer_loan_disbursed_date'] = ($columns['disbursal_date']) ? date("d-m-Y", strtotime($columns['disbursal_date'])) : "-";
            $data['customer_loan_repayment_date'] = ($columns['repayment_date']) ? date("d-m-Y", strtotime($columns['repayment_date'])) : "-";
            $data['customer_loan_adminFeeWithGST'] = ($columns['adminFeeWithGST']) ? number_format((float) $columns['adminFeeWithGST'], 2, '.', '') : "-";
            $data['customer_loan_total_admin_fee'] = ($columns['total_admin_fee']) ? number_format((float) $columns['total_admin_fee'], 2, '.', '') : "-";
            $data['customer_loan_net_disbursal_amount'] = ($columns['net_disbursal_amount']) ? number_format((float) $columns['net_disbursal_amount'], 2, '.', '') : "-";
            $data['customer_loan_repayment_amount'] = ($columns['repayment_amount']) ? number_format((float) $columns['repayment_amount'], 2, '.', '') : "-";
            $data['customer_advance_interest_amount'] = ($columns['cam_advance_interest_amount']) ? number_format((float) $columns['cam_advance_interest_amount'], 2, '.', '') : "-";

            $result_array['data']['loan_details'] = $data;
        }

        $result_array['data']['reference_list'] = $this->get_customer_reference_details($lead_id);
        $result_array['data']['visit_and_manager_details'] = $this->get_visit_and_manager_details($lead_id);
        $result_array['data']['repayment_details'] = $this->get_repayment_details($lead_id);
        return $result_array;
    }

    public function get_customer_reference_details($lead_id = null) {
        $reference_array = array();
        $conditions['LCR.lcr_lead_id'] = $lead_id;
        $conditions['LCR.lcr_active'] = 1;
        $conditions['LCR.lcr_deleted'] = 0;

        $select = 'LCR.lcr_lead_id as lead_id, LCR.lcr_name as customer_reference_username, MRT.mrt_name as customer_reference_relation, ';
        $select .= ' LCR.lcr_mobile as customer_reference_mobile ';

        $this->db->select($select);
        $this->db->from("lead_customer_references LCR");
        $this->db->join("master_relation_type MRT", "MRT.mrt_id = LCR.lcr_relationType", "LEFT");

        if (!empty($lead_id)) {
            $this->db->where($conditions);
        }

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            foreach ($tempDetails->result_array() as $columns) {
                $data['customer_reference_username'] = ($columns['customer_reference_username']) ? $columns['customer_reference_username'] : "-";
                $data['customer_reference_mobile'] = ($columns['customer_reference_mobile']) ? $columns['customer_reference_mobile'] : "-";
                $data['customer_reference_relation'] = ($columns['customer_reference_relation']) ? $columns['customer_reference_relation'] : "-";

                $reference_array[] = $data;
            }
        }
        return $reference_array;
    }

    public function get_customer_residence_details($lead_id = null) {
        $result_array = array();
        $conditions['LD.lead_id'] = $lead_id;

        $select = 'LD.lead_id, LC.current_landmark, Residence_state.m_state_name as residence_state, Residence_city.m_city_name as residence_city, LC.cr_residence_pincode as residence_pincode, ';
        $select .= ' CONCAT_WS(" ", LC.current_house, LC.current_locality) as current_residence_address, ';
        $select .= ' CONCAT_WS(" ", LC.aa_current_house, LC.aa_current_locality) as address2_as_per_aadhar, ';
        $select .= ' LC.aa_current_landmark as landmark_as_per_aadhar, Aadhar_Residence_state.m_state_name as state_as_per_aadhar, Aadhar_Residence_city.m_city_name as city_as_per_aadhar, LC.aa_cr_residence_pincode as pincode_as_per_aadhar, ';
        $select .= ' LC.current_residence_since, LC.current_residence_type, LC.current_residing_withfamily, MR.religion_name as customer_religion ';

        $this->db->select($select);
        $this->db->from("leads LD");
        $this->db->join("lead_customer LC", "LC.customer_lead_id = LD.lead_id AND LC.customer_active = 1 AND LC.customer_deleted = 0");
        $this->db->join("master_state Residence_state", "Residence_state.m_state_id = LC.state_id", 'LEFT');
        $this->db->join("master_city Residence_city", "Residence_city.m_city_id = LC.city_id", 'LEFT');
        $this->db->join("master_state Aadhar_Residence_state", "Aadhar_Residence_state.m_state_id = LC.aa_current_state_id", ' LEFT');
        $this->db->join("master_city Aadhar_Residence_city", "Aadhar_Residence_city.m_city_id = LC.aa_current_city_id", 'LEFT');
        $this->db->join("master_religion MR", "MR.religion_id = LC.customer_religion_id", 'LEFT');

        if (!empty($lead_id)) {
            $this->db->where($conditions);
        }

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $columns = $tempDetails->row_array();

            $result_array['status'] = 1;
            $data['customer_lead_id'] = $this->encrypt->encode($columns['lead_id']);
            $data['customer_residence_state'] = ($columns['residence_state']) ? $columns['residence_state'] : "-";
            $data['customer_residence_city'] = ($columns['residence_city']) ? $columns['residence_city'] : "-";
            $data['customer_residence_pincode'] = ($columns['residence_pincode']) ? $columns['residence_pincode'] : "-";
            $data['customer_current_landmark'] = ($columns['current_landmark']) ? $columns['current_landmark'] : "-";
            $data['customer_current_residence_address1'] = ($columns['current_residence_address']) ? $columns['current_residence_address'] : "-";
            $data['customer_state_as_per_aadhar'] = ($columns['state_as_per_aadhar']) ? $columns['state_as_per_aadhar'] : "-";
            $data['customer_city_as_per_aadhar'] = ($columns['city_as_per_aadhar']) ? $columns['city_as_per_aadhar'] : "-";
            $data['customer_pincode_as_per_aadhar'] = ($columns['pincode_as_per_aadhar']) ? $columns['pincode_as_per_aadhar'] : "-";
            $data['customer_landmark_as_per_aadhar'] = ($columns['landmark_as_per_aadhar']) ? $columns['landmark_as_per_aadhar'] : "-";
            $data['customer_address2_as_per_aadhar'] = ($columns['address2_as_per_aadhar']) ? $columns['address2_as_per_aadhar'] : "-";
            $data['customer_current_residence_since'] = ($columns['current_residence_since']) ? $columns['current_residence_since'] : "-";
            $data['customer_current_residing_withfamily'] = ($columns['current_residing_withfamily']) ? $columns['current_residing_withfamily'] : "-";
            $data['customer_religion'] = ($columns['customer_religion']) ? $columns['customer_religion'] : "-";

            $result_array['data']['residence_details'] = $data;
        }
        $result_array['data']['visit_and_manager_details'] = $this->get_visit_and_manager_details($lead_id);
        return $result_array;
    }

    public function get_customer_office_details($lead_id = null) {
        $result_array = array();
        $conditions['LD.lead_id'] = $lead_id;

        $select = 'LD.lead_id, ';
        $select .= ' CE.employer_name, CE.emp_landmark, CE.emp_residence_since, CE.emp_designation, CE.emp_department, CE.emp_employer_type, ';
        $select .= ' CE.presentServiceTenure, CE.emp_website, ';
        $select .= ' CONCAT_WS(" ", CE.emp_house, CE.emp_street) as customer_office_address, ';
        $select .= ' OFFICE_ST.m_state_name as office_state, OFFICE_CT.m_city_name as office_city, CE.emp_pincode';

        $this->db->select($select);
        $this->db->from("leads LD");
        $this->db->join("customer_employment CE", "CE.lead_id = LD.lead_id AND CE.emp_active = 1 AND CE.emp_deleted = 0");
        $this->db->join("master_state OFFICE_ST", "OFFICE_ST.m_state_id = CE.state_id AND OFFICE_ST.m_state_active = 1 AND OFFICE_ST.m_state_deleted = 0", "LEFT");
        $this->db->join("master_city OFFICE_CT", "OFFICE_CT.m_city_id = CE.city_id AND OFFICE_CT.m_city_active = 1 AND OFFICE_CT.m_city_deleted = 0", "LEFT");

        if (!empty($lead_id)) {
            $this->db->where($conditions);
        }

        $tempDetails = $this->db->get();
//            echo $this->db->last_query(); exit;

        if (!empty($tempDetails->num_rows())) {
            $columns = $tempDetails->row_array();

            $result_array['status'] = 1;
            $data['customer_lead_id'] = $this->encrypt->encode($columns['lead_id']);
            $data['customer_office_state'] = ($columns['office_state']) ? $columns['office_state'] : "-";
            $data['customer_office_city'] = ($columns['office_city']) ? $columns['office_city'] : "-";
            $data['customer_office_pincode'] = ($columns['emp_pincode']) ? $columns['emp_pincode'] : "-";
            $data['customer_office_landmark'] = ($columns['emp_landmark']) ? $columns['emp_landmark'] : "-";
            $data['customer_office_address1'] = ($columns['customer_office_address']) ? $columns['customer_office_address'] : "-";
            $data['customer_office_employer_name'] = ($columns['employer_name']) ? $columns['employer_name'] : "-";
            $data['customer_office_residence_since'] = ($columns['emp_residence_since']) ? date('d-m-Y', strtotime($columns['emp_residence_since'])) : "-";
            $data['customer_office_emp_designation'] = ($columns['emp_designation']) ? $columns['emp_designation'] : "-";
            $data['customer_office_emp_department'] = ($columns['emp_department']) ? $columns['emp_department'] : "-";
            $data['customer_office_website'] = ($columns['emp_website']) ? $columns['emp_website'] : "-";
            $data['customer_office_present_service_tenure'] = ($columns['presentServiceTenure']) ? $columns['presentServiceTenure'] : "-";
            $data['customer_office_present_service_tenure_type'] = "Days"; // Days, Month, Year

            $result_array['data']['office_details'] = $data;
        }
        $result_array['data']['visit_and_manager_details'] = $this->get_visit_and_manager_details($lead_id);
        return $result_array;
    }

    public function get_master_payment_mode($mpm_id = null) {
        $result_array = array('status' => 0);
        $data_array = array();

        $select = 'mpm.mpm_id, mpm.mpm_name, mpm.mpm_heading ';
        $this->db->select($select);
        $this->db->from("master_payment_mode mpm");

        if (!empty($mpm_id)) {
            $conditions['mpm_id'] = $mpm_id;
            $this->db->where($conditions);
        }

        $tempDetails = $this->db->get();

        if (!empty($mpm_id)) {
            $result_array['status'] = 1;
            $columns = $tempDetails->row_array();
            $data['payment_mode_id'] = $columns['mpm_id'];
            $data['payment_mode_name'] = $columns['mpm_name'];
            $data['payment_mode_heading'] = $columns['mpm_heading'];

            $data_array[] = $data;
        } else {
            $result_array['status'] = 1;
            foreach ($tempDetails->result_array() as $columns) {
                $data['payment_mode_id'] = $columns['mpm_id'];
                $data['payment_mode_name'] = $columns['mpm_name'];
                $data['payment_mode_heading'] = $columns['mpm_heading'];

                $data_array[] = $data;
            }
        }

        $result_array['data']['payment_mode_list'] = $data_array;
        return $result_array;
    }

    public function get_master_status($status_id = null) {
        $result_array = array('status' => 0);
        $data_array = array();

        $select = 'MS.status_id, MS.status_name ';
        $this->db->select($select);
        $this->db->from("master_status MS");

        if (!empty($status_id)) {
            $conditions['MS.status_id'] = $status_id;
            $this->db->where($conditions);
        }

        $tempDetails = $this->db->get();

        if (!empty($mpm_id)) {
            $result_array['status'] = 1;
            $columns = $tempDetails->row_array();
            $data['repay_status_id'] = $columns['status_id'];
            $data['repay_status_name'] = $columns['status_name'];

            $data_array[] = $data;
        } else {
            $result_array['status'] = 1;
            foreach ($tempDetails->result_array() as $columns) {
                $data['repay_status_id'] = $columns['status_id'];
                $data['repay_status_name'] = $columns['status_name'];

                $data_array[] = $data;
            }
        }

        $result_array['data']['repay_status_list'] = $data_array;
        return $result_array;
    }

    public function get_LoanDetail_payment_mode($loan_no){

        $data_array = array("status"=>0,"message"=>"");
        $conditions['LD.loan_no'] = $loan_no;
        $conditions['LD.lead_status_id'] = 14;
        $select = 'LD.lead_id, LD.loan_no';
        
        $this->db->select($select);
        $this->db->from("leads LD");
        $this->db->where($conditions);
        
        $LoanDetails = $this->db->get();
        
        if (!empty($LoanDetails->num_rows())) {
            $row_data=$LoanDetails->result_array();
            
            $payment_select = 'SELECT mpm_id, mpm_name FROM master_payment_mode WHERE mpm_active=1';
            $paymet_mode = $this->db->query($payment_select)->result_array();

            $collection_select = 'SELECT id, old_recovery_id, user_id, lead_id, company_id, product_id, customer_id, loan_no, payment_mode, payment_mode_id, received_amount, refrence_no, refund, remarks   FROM `collection` WHERE loan_no= "'.$loan_no.'"';
            $collection_mode = $this->db->query($collection_select)->result_array();

            $data_array['status'] = 1;
            $data_array['message'] = "Record Found";
            $data_array['data']['loan_detais'] = $row_data[0];
            $data_array['data']['payment_detais'] = $paymet_mode;         
            $data_array['data']['collection_detais'] = $collection_mode; 
            if(($data_array['data']['collection_detais']['loan_no']!=$data_array['data']['loan_detais']['loan_no']) && (!empty($data_array['data']['collection_detais']['loan_no']))){  
               $this->db->insert('collection',$data_array['data']['loan_detais']);
            }
        }else{       
            $data_array['message'] = "Record Not Found";
        }
        
        return $data_array;
    }

   

    public function get_collection_followup_details($lead_id) {

        $data_array = array();
        $result_array = array();
        $conditions['LCF.lcf_lead_id'] = $lead_id;
        $conditions["LCF.lcf_active"] = 1;
        $conditions["LCF.lcf_deleted"] = 0;

        $select = "LCF.lcf_lead_id as lead_id, LCF.lcf_remarks, LCF.lcf_next_schedule_datetime, LCF.lcf_fe_upload_selfie, LCF.lcf_fe_upload_location, LCF.lcf_created_on, ";
        $select .= " Followup_user.name as visit_followup_by, ";
        $select .= " MFT.m_followup_type_heading as followup_type, MFS.m_followup_status_heading as followup_status";

        $this->db->select($select);
        $this->db->from("loan_collection_followup LCF");
        $this->db->join("users Followup_user", "Followup_user.user_id = LCF.lcf_user_id");
        $this->db->join("master_followup_type MFT", "MFT.m_followup_type_id = LCF.lcf_type_id", "LEFT"); //  AND MFT.m_followup_type_active = 1 AND MFT.m_followup_type_deleted = 0
        $this->db->join("master_followup_status MFS", "MFS.m_followup_status_id = LCF.lcf_status_id", "LEFT"); //  AND MFS.m_followup_status_active = 1 AND MFS.m_followup_status_deleted = 0

        if (!empty($conditions)) {
            $this->db->where($conditions);
        }

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $result_array['status'] = 1;
            foreach ($tempDetails->result_array() as $columns) {
                $data['customer_lead_id'] = $this->encrypt->encode($columns['lead_id']);
                $data['customer_followup_next_date'] = ($columns['lcf_next_schedule_datetime'] != NULL) ? date("d-m-Y H:i:s", strtotime($columns['lcf_next_schedule_datetime'])) : "-";
                $data['customer_followup_upload_selfie'] = ($columns['lcf_fe_upload_selfie']) ? UPLOAD_PATH_URL . $columns['lcf_fe_upload_selfie'] : "-";
                $data['customer_followup_upload_location'] = ($columns['lcf_fe_upload_location']) ? UPLOAD_PATH_URL . $columns['lcf_fe_upload_location'] : "-";
                $data['customer_followup_by'] = ($columns['visit_followup_by']) ? $columns['visit_followup_by'] : "-";
                $data['customer_followup_date'] = ($columns['lcf_created_on']) ? date("d-m-Y H:i:s", strtotime($columns['lcf_created_on'])) : "-";
                $data['customer_followup_type'] = ($columns['followup_type']) ? $columns['followup_type'] : "-";
                $data['customer_followup_status'] = ($columns['followup_status']) ? $columns['followup_status'] : "-";
                $data['customer_followup_remarks'] = ($columns['lcf_remarks']) ? $columns['lcf_remarks'] : "-";
                $data_array[] = $data;
            }
            $result_array['data']['last_collection_followup'] = $data_array;
        }
        return $result_array;
    }

    public function get_agent_profile($conditions) {
        $data_array = array();
        $result_array = array();

        $select = "U.user_id, U.name, U.email, U.mobile, U.status, U.user_status_id ";

        $this->db->select($select);
        $this->db->from("users U");
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $result_array['status'] = 1;
            $columns = $tempDetails->row_array();

            $data['agent_user_id'] = $this->encrypt->encode($columns['user_id']);
            $data['agent_name'] = ($columns['name']) ? $columns['name'] : "-";
            $data['agent_email'] = ($columns['email']) ? $columns['email'] : "-";
            $data['agent_mobile'] = ($columns['mobile']) ? $columns['mobile'] : "-";
            $data['agent_status'] = ($columns['user_status_id'] == 1) ? "ACTIVE" : "INACTIVE";

            $result_array['data']['agent_profile'] = $data;
        }
        return $result_array;
    }

    public function get_visit_and_manager_details($lead_id) {
        $result_array = array();
        $conditions["LCV.col_lead_id"] = $lead_id;
        $conditions["LCV.col_visit_active"] = 1;
        $conditions["LCV.col_visit_deleted"] = 0;
        $conditions["LCV.col_visit_field_status_id"] = 2;

        $select = "LD.lead_id, LD.loan_no, CONCAT(LC.first_name ,' ', LC.middle_name ,' ', LC.sur_name) as full_name, MS.status_name as lead_status, ";
        $select .= " LC.mobile, LC.alternate_mobile, LC.email, LC.alternate_email, ";
        $select .= " MVS.m_visit_heading as col_visit_field_status, ";
        $select .= " Lead_applied_state.m_state_name as customer_lead_applied_state, Lead_applied_city.m_city_name as customer_lead_applied_city, ";
        $select .= " visit_allocated_by.user_id, visit_allocated_by.name as visit_allocated_by, visit_allocated_by.email, visit_allocated_by.mobile, ";
        $select .= "  LCV.col_visit_id, LCV.col_fe_visit_trip_status_id, visit_allocated_by.name as visit_allocated_by, LCV.col_visit_allocate_on, LCV.col_visit_address_type ";

        $this->db->select($select);
        $this->db->from("loan_collection_visit LCV");
        $this->db->join("leads LD", "LCV.col_lead_id = LD.lead_id");
        $this->db->join("lead_customer LC", "LCV.col_lead_id = LC.customer_lead_id");
        $this->db->join("master_state Lead_applied_state", "Lead_applied_state.m_state_id = LD.state_id");
        $this->db->join("master_city Lead_applied_city", "Lead_applied_city.m_city_id = LD.city_id");
        $this->db->join("master_status MS", "MS.status_id = LD.lead_status_id");
        $this->db->join("master_visit_status MVS", "MVS.m_visit_id = LCV.col_visit_field_status_id");
        $this->db->join("users visit_allocated_by", "visit_allocated_by.user_id = LCV.col_visit_scm_id");

        $this->db->where($conditions);

        $tempDetails = $this->db->get();

        if (!empty($tempDetails->num_rows())) {
            $columns = $tempDetails->row_array();

            $data['customer_lead_id'] = $this->encrypt->encode($columns['lead_id']);
            $data['customer_visit_id'] = $this->encrypt->encode($columns['col_visit_id']);
            $data['manager_user_id'] = $this->encrypt->encode($columns['user_id']);
            $data['fe_visit_trip_status_id'] = ($columns['col_fe_visit_trip_status_id']) ? $columns['col_fe_visit_trip_status_id'] : "-";
            $data['manager_name'] = ($columns['visit_allocated_by']) ? $columns['visit_allocated_by'] : "-";
            $data['manager_email'] = ($columns['email']) ? $columns['email'] : "-";
            $data['manager_mobile'] = ($columns['mobile']) ? $columns['mobile'] : "-";
            $data['customer_visit_status'] = ($columns['col_visit_field_status']) ? $columns['col_visit_field_status'] : "-";
            $data['customer_visit_allocated_on'] = ($columns['col_visit_allocate_on']) ? date("d-m-Y H:i:s", strtotime($columns['col_visit_allocate_on'])) : "-";

            $result_array[] = $data;
        }
        return $result_array;
    }

    public function get_repayment_details($lead_id) {
//            $result_array = array('status' => 0);

        require_once (COMPONENT_PATH . 'CommonComponent.php');

        $CommonComponent = new CommonComponent();
        $repay = $CommonComponent->get_loan_repayment_details($lead_id);

        $result_array = $repay['repayment_data'];

        return $result_array;
    }

    public function get_repayment_details_20220725($lead_id) {
        $result_array = array();

        $conditions['LD.lead_id'] = $lead_id;
        $conditions['L.loan_status_id'] = 14;

        $select = 'LD.lead_id, LD.customer_id, MS.status_name as status, MS.status_stage as stage, LD.lead_status_id, LD.lead_black_list_flag, LD.loan_no, ';
        $select .= ' CAM.cam_id, CAM.loan_recommended, CAM.final_foir_percentage, CAM.foir_enhanced_by, CAM.processing_fee_percent, ';
        $select .= ' CAM.roi, CAM.admin_fee, CAM.disbursal_date, CAM.repayment_date, CAM.adminFeeWithGST, CAM.total_admin_fee, CAM.tenure, ';
        $select .= ' CAM.net_disbursal_amount, CAM.repayment_amount, CAM.panel_roi, CAM.cam_advance_interest_amount ';

        $this->db->select($select);
        $this->db->from('leads LD');
        $this->db->join('loan L', "L.lead_id = LD.lead_id");
        $this->db->join('credit_analysis_memo CAM', "CAM.lead_id = LD.lead_id");
        $this->db->join('master_status MS', "MS.status_id = LD.lead_status_id");
        $this->db->where($conditions);

        $leads = $this->db->get();

        if (!empty($leads->num_rows())) {

            $lead_details = $leads->row();

            $lead_status_id = $lead_details->lead_status_id;
            $status = $lead_details->status;
            $stage = $lead_details->stage;
            $loan_no = $lead_details->loan_no;
            $loan_recommended = ($lead_details->loan_recommended) ? $lead_details->loan_recommended : 0;
            $roi = ($lead_details->roi) ? $lead_details->roi : 0;
            $panel_roi = $roi * 2;
            $disbursal_date = ($lead_details->disbursal_date) ? date('d-m-Y', strtotime($lead_details->disbursal_date)) : '';
            $repayment_date = ($lead_details->repayment_date) ? date('d-m-Y', strtotime($lead_details->repayment_date)) : '';
            $tenure = ($lead_details->tenure) ? $lead_details->tenure : 0;
            $repayment_amount = ($lead_details->repayment_amount) ? $lead_details->repayment_amount : 0;

            $rtenure = 0;
            $ptenure = 0;
            $total_received_amount = 0;

            $date_of_receive = strtotime(date('d-m-Y'));
            $disbursal_date_to_time = strtotime($disbursal_date);
            $repayment_date_to_time = strtotime($repayment_date);

            $collection_conditions['CO.lead_id'] = $lead_id;
            $collection_conditions['CO.payment_verification'] = 1;
            $collection_conditions['CO.collection_active'] = 1;
            $collection_conditions['CO.collection_deleted'] = 0;

            $fetch = 'CO.date_of_recived, CO.recovery_status';
            $last_collection = $this->db->select($fetch)->where($collection_conditions)->from('collection CO')->order_by('CO.id', 'desc')->limit(1)->get();

            if (!empty($last_collection->num_rows())) {
                if (in_array($lead_status_id, [16, 17])) {
                    $collection = $last_collection->row();
                    $date_of_receive = strtotime(date('d-m-Y', strtotime($collection->date_of_recived)));
                }

                $recoveredAmount = $this->db->select('SUM(CO.received_amount) as total_paid')->where($collection_conditions)->from('collection CO')->get()->row();
                $total_received_amount = $recoveredAmount->total_paid;
            }

            if ($date_of_receive <= $repayment_date_to_time) {
                $realdays = $date_of_receive - $disbursal_date_to_time;
                $rtenure = ($realdays / 60 / 60 / 24);
            } else {
                $realdays = $repayment_date_to_time - $disbursal_date_to_time;
                $rtenure = ($realdays / 60 / 60 / 24);
            }

            if ($date_of_receive <= $repayment_date_to_time) {
                $realdays = $date_of_receive - $disbursal_date_to_time;
            } else {
                $endDate = $date_of_receive - $repayment_date_to_time;
                $oneDay = (60 * 60 * 24);
                $dateDays60 = ($oneDay * 60);
//                $date4 = ($repayment_date_to_time + $dateDays60); // stopped LPI days

                if ($endDate <= $dateDays60) {
                    $realdays = $repayment_date_to_time - $disbursal_date_to_time;
                    $rtenure = ($realdays / 60 / 60 / 24);
                    $paneldays = $date_of_receive - $repayment_date_to_time;
                    $ptenure = ($paneldays / 60 / 60 / 24);
                } else {
                    $ptenure = 60;
                }
            }

            $tenure = ($repayment_date_to_time - $disbursal_date_to_time) / (60 * 60 * 24);
            $repayment_amount = $loan_recommended + (($loan_recommended * $roi * $tenure) / 100);

            if (in_array($lead_status_id, [16])) {
                if ($date_of_receive <= $repayment_date_to_time) {
                    $paidBeforeDays = ($repayment_date_to_time - $date_of_receive) / (60 * 60 * 24);
                    $rtenure = $tenure - $paidBeforeDays;
                }
            }

            $realIntrest = ($loan_recommended * $roi * $rtenure) / 100;
            $penaltyIntrest = ($loan_recommended * ($panel_roi) * $ptenure) / 100;
            $penality_repayment_amount = ($loan_recommended + $realIntrest + $penaltyIntrest);
            $todal_due_amount = $penality_repayment_amount - $total_received_amount;

            $data['loan_no'] = $loan_no;
            $data['lead_black_list_flag'] = !empty($lead_details->lead_black_list_flag) ? $lead_details->lead_black_list_flag : '';
            $data['status'] = $status;
            $data['disbursal_date'] = $disbursal_date;
            $data['repayment_date'] = $repayment_date;
            $data['loan_recommended'] = number_format((float) $loan_recommended, 2, '.', '');
            $data['roi'] = $roi;
            $data['panel_roi'] = number_format((float) $panel_roi, 2, '.', '');
            $data['tenure'] = $tenure;
            $data['penalty_days'] = $ptenure;
            $data['real_interest'] = number_format((float) $realIntrest, 2, '.', '');
            $data['penality_interest'] = number_format((float) $penaltyIntrest, 2, '.', '');
            $data['repayment_amount'] = number_format((float) $repayment_amount, 2, '.', '');
            $data['total_penality_repayment_amount'] = number_format((float) $penality_repayment_amount, 2, '.', '');
            $data['total_received_amount'] = number_format((float) $total_received_amount, 2, '.', '');
            $data['total_due_amount'] = number_format((float) $todal_due_amount, 2, '.', '');

            $result_array[] = $data;
        }
        return $result_array;
    }

    public function upload_base64encode($last_inserted_id = null, $file) {
        $result_array = array("status" => 0);
        $data = array();       
        
        //$imgUrl = rand(1000000, 9999999) . date("dmYHis") . "_" . $last_inserted_id . '.jpeg';
        //$image_upload_dir = UPLOAD_PATH . $imgUrl;

        if (!empty($last_inserted_id)) {            
                       
            if (!empty($file['lcf_fe_upload_selfie'])) {
                //$flag = file_put_contents($image_upload_dir, base64_decode($file['lcf_fe_upload_selfie']));                
                $upload_return = uploadDocument(base64_decode($file['lcf_fe_upload_selfie']),$last_inserted_id,1,'jpeg');
                $update_loan_collection_followup['lcf_fe_upload_selfie'] = $upload_return['file_name'];
                $condition['lcf_id'] = $last_inserted_id;
                $upload = $this->globel_update("loan_collection_followup", $update_loan_collection_followup, $condition);

                $data['lcf_fe_upload_selfie'] = $upload_return['file_name'];
            } else if (!empty($file['lcf_fe_upload_location'])) {
                //$flag = file_put_contents($image_upload_dir, base64_decode($file['lcf_fe_upload_location']));
                $upload_return = uploadDocument(base64_decode($file['lcf_fe_upload_location']),$last_inserted_id,1,'jpeg');              
                $update_loan_collection_followup['lcf_fe_upload_location'] = $upload_return['file_name'];

                $condition['lcf_id'] = $last_inserted_id;
                $upload = $this->globel_update("loan_collection_followup", $update_loan_collection_followup, $condition);

                $data['lcf_fe_upload_location'] = $upload_return['file_name'];
            } else if (!empty($file['docs'])) {
                //$flag = file_put_contents($image_upload_dir, base64_decode($file['docs']));
                $upload_return = uploadDocument(base64_decode($file['docs']),$last_inserted_id,1,'jpeg');
                $update_collection['docs'] = $upload_return['file_name'];

                $condition['id'] = $last_inserted_id;
                $upload = $this->globel_update("collection", $update_collection, $condition);

                $data['docs'] = $upload_return['file_name'];
            } else if (!empty($file['lcf_fe_upload_selfie_return_from_visit'])) {
                //$flag = file_put_contents($image_upload_dir, base64_decode($file['lcf_fe_upload_selfie_return_from_visit']));
                $upload_return = uploadDocument(base64_decode($file['lcf_fe_upload_selfie_return_from_visit']),$last_inserted_id,1,'jpeg');                
                $update_loan_collection_followup['col_fe_rtoh_upload_selfie'] = $upload_return['file_name'];

                $condition['col_visit_id'] = $last_inserted_id;
                $upload = $this->globel_update("loan_collection_visit", $update_loan_collection_followup, $condition);
                $data['col_fe_rtoh_upload_selfie'] = $upload_return['file_name'];
            }

            if (!empty($upload)) {
                $result_array['status'] = 1;
                $result_array['mesage'] = "File upload successfully.";
                $result_array['data'] = $data;
            }
        }
        return $result_array;
    }

    public function send_email_for_visit($visit_id) {
        $result_array = array("status" => 0);

        $conditions['LCV.col_visit_id'] = $visit_id;
//            $conditions['LCV.col_fe_visit_trip_status_id'] = 4; // Visit End
//            $conditions['LCV.col_visit_field_status_id'] = 5; // Final Visit Completed
        $conditions['LCV.col_visit_active'] = 1;
        $conditions['LCV.col_visit_deleted'] = 0;

        $select = 'LCV.col_lead_id as lead_id, LCV.col_visit_address_type, LCV.col_visit_requested_by, LCV.col_visit_scm_id, ';
        $select .= ' LCV.col_visit_allocated_to, LCV.col_visit_field_status_id, LCV.col_fe_visit_trip_status_id, LCV.col_fe_visit_total_amount_received, ';
        $select .= ' LD.loan_no ';

        $this->db->select($select);
        $this->db->from("loan_collection_visit LCV");
        $this->db->join("leads LD", "LD.lead_id = LCV.col_lead_id");
        $this->db->where($conditions);

        $this->db->distinct();

        $temp_data = $this->db->get();

        if (!empty($temp_data->num_rows())) {
            $row = $temp_data->row_array();

            $lead_id = $row['lead_id'];

            $reyayment_details = $this->get_repayment_details($lead_id);

            $email_data['visit_type_id'] = $visit_id;
            $email_data['visit_type_id'] = $row['col_visit_address_type'];
            $email_data['lead_id'] = $lead_id;
            $email_data['loan_no'] = $row['loan_no'];
            $email_data['total_due_amount'] = $reyayment_details[0]['total_due_amount'];
            $email_data['scm_user_id'] = $row['col_visit_scm_id'];
            $email_data['cfe_user_id'] = $row['col_visit_allocated_to'];
            $email_data['total_amount_received'] = $row['col_fe_visit_total_amount_received'];

            $email_sent_status = $this->send_email_cfe_request_visit($email_data);

            if (!empty($email_sent_status['status'])) {
                $result_array['status'] = 1;
            } else {
                $result_array['error'] = $email_sent_status['error'];
            }
        }

        return $result_array;
    }

    public function send_email_cfe_request_visit($email_data) {
        $result_array = array('status' => 0);

        $lead_id = $email_data['lead_id'];
        $visit_type_id = $email_data['visit_type_id'];
        $loan_no = $email_data['loan_no'];
        $total_due_amount = $email_data['total_due_amount'];
        $total_amount_received = $email_data['total_amount_received'];
        $today_date = date("d-m-Y H:i:s");

        $scm_users = $this->get_user_email_details($email_data['scm_user_id']);
        $cfe_users = $this->get_user_email_details($email_data['cfe_user_id']);

        if (!empty($scm_users['status']) && !empty($cfe_users['status'])) {

            $from_agent_email = $cfe_users['data']['agent_email'];
            $from_agent_name = $cfe_users['data']['agent_name'];

            $to_agent_email = $scm_users['data']['agent_email'];
            $to_agent_name = $scm_users['data']['agent_name'];

            $leads = $this->get_lead_details($lead_id);
            $lead_details = $leads->row_array();

            $visit_location_details = $this->get_visit_location($lead_id, $visit_type_id);

            $state_name = $visit_location_details['data']['state_name'];
            $city_name = $visit_location_details['data']['city_name'];
            $status = $visit_location_details['data']['status'];
            $customer_name = $lead_details['first_name'] . " " . $lead_details['middle_name'] . " " . $lead_details['sur_name'];
            $visit_type = $this->visit_type[$visit_type_id];

            $subject = 'Collection Visit Completed';
            $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns="http://www.w3.org/1999/xhtml">
                        <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <title>Field Executive</title>
                        </head>
                        <body>
                        <table width="491" border="0" align="center" cellpadding="0" cellspacing="0" style="border:solid 1px #0463a3; background:#fff; font-family:Arial, Helvetica, sans-serif;border-radius: 5px; padding-bottom:15px;">
                        <tr>
                        <td width="489" align="center" style="background:#0463a3; padding:15px;"><a href="' . WEBSITE_URL . '" target="_blank"><img src="' . COLLECTION_BRAND_LOGO . '" alt="LW" width="218" height="52"/></a></td>
                        </tr>
                        <tr>
                        <td><img src="' . COLLECTION_FIELD_ROAD . '" alt="Collection-Executive-banner" width="491" height="128"/></td>
                        </tr>
                        <tr>
                        <td><img src="' . COLLECTION_LINE . '" alt="line" width="34" height="15" /></td>
                        </tr>
                        <tr>
                          <td align="center" style="padding:10px 15px; font-size:18px; color:#0463a3;"><img src="' . COLLECTION_FIELD_BANNER . '" alt="Field-Executive-icon" width="111" height="111" /></td>
                        </tr>
                        <tr>
                          <td align="center" style="padding:10px 15px; font-size:18px; color:#0463a3;"><strong>Dear ' . $to_agent_name . ',</strong></td>
                        </tr>
                        <tr>
                          <td align="center" style="padding:0px 15px; line-height:25px; font-size:17px;">The ' . $visit_type . ' visit on ' . $today_date . ' for the<br /> 
                        following account has been completed by ' . $from_agent_name . '.</td>
                        </tr>
                        <tr>
                          <td style="padding:0px 15px; line-height:25px;"><img src="' . COLLECTION_LINE . '" alt="line" width="34" height="25" /></td>
                        </tr>
                        <tr>
                          <td style="padding:0px 15px;"><table width="100%" border="0" cellpadding="2" cellspacing="1" bgcolor="#dddddd" style="font-size:14px;">

                            <tr>
                              <td align="left" bgcolor="#0463a3" style="padding:10px;"><span style="padding:10px; color:#fff;"><strong>Loan No.</strong></span></td>
                              <td bgcolor="#FFFFFF" style="padding:15px;">' . $loan_no . '</td>
                              </tr>

                            <tr>
                              <td align="left" bgcolor="#0463a3" style="padding:10px;"><span style="padding:10px; color:#fff;"><strong>Loan Status.</strong></span></td>
                              <td bgcolor="#FFFFFF" style="padding:15px;">' . $status . '</td>
                              </tr>
                            <tr>
                              <td width="31%" align="left" bgcolor="#0463a3" style="padding:15px; color:#fff;"><strong>Customer Name</strong></td>
                              <td width="69%" bgcolor="#FFFFFF" style="padding:15px;">' . $customer_name . '</td>
                              </tr>
                            <tr>
                              <td align="left" bgcolor="#0463a3" style="padding:10px;"><span style="padding:10px; color:#fff;"><strong>State</strong></span></td>
                              <td bgcolor="#FFFFFF" style="padding:15px;">' . $state_name . '</td>
                              </tr>
                            <tr>
                              <td align="left" bgcolor="#0463a3" style="padding:10px;"><span style="padding:10px; color:#fff;"><strong>City</strong></span></td>
                              <td bgcolor="#FFFFFF" style="padding:15px;">' . $city_name . '</td>
                              </tr>
                            <tr>
                              <td align="left" bgcolor="#0463a3" style="padding:10px;"><span style="padding:10px; color:#fff;"><strong>Due Amount</strong></span></td>
                              <td bgcolor="#FFFFFF" style="padding:15px;"><img src="' . COLLECTION_INR_ICON . '" alt="inr" width="15" height="15" style="    position: relative;
                            margin-bottom: -1px;">' . $total_due_amount . '</td>
                              </tr>
                              <td align="left" bgcolor="#0463a3" style="padding:10px;"><span style="padding:10px; color:#fff;"><strong>Received Amount</strong></span></td>
                              <td bgcolor="#FFFFFF" style="padding:15px;"><img src="' . COLLECTION_INR_ICON . '" alt="inr" width="15" height="15" style="    position: relative;
                            margin-bottom: -1px;">' . $total_amount_received . '</td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                        <tr>
                        <td><img src="' . COLLECTION_LINE . '" alt="line" width="34" height="25" /></td>
                            </tr>
                            <tr>
                            <td align="center" style="border-top:solid 1px #ddd; padding-top:15px;"><a href="' . APPLE_STORE_LINK . '" target="_blank"><img src="' . APPLE_STORE_ICON . '" alt="aap-sore" width="108" height="30" /></a> <a href="' . LINKEDIN_LINK . '" target="_blank"><img src="' . LINKEDIN_ICON . '" alt="linkdin" width="30" height="30" /></a><a href="' . INSTAGRAM_LINK . '" target="_blank"><img src="' . INSTAGRAM_ICON . '" alt="instagram" width="30" height="30" /></a><a href="' . FACEBOOK_LINK . '" target="_blank"><img src="' . FACEBOOK_ICON . '" alt="facebook" width="30" height="30" /></a><a href="' . TWITTER_LINK . '" target="_blank"><img src="' . TWITTER_ICON . '" alt="twitter" width="30" height="30" /></a><a href="' . YOUTUBE_LINK . '" target="_blank"><img src="' . YOUTUBE_ICON . '" alt="youtube" width="30" height="30" /></a>&nbsp;<a href="' . ANDROID_STORE_LINK . '" target="_blank"><img src="' . ANDROID_STORE_ICON . '" alt="google-play" width="108" height="30" /></a></td>
                            </tr>
                            <tr>
                              <td align="center"><img src="' . COLLECTION_LINE . '" alt="line" width="34" height="5" /></td>
                            </tr>
                            <tr>
                            <td align="center" style="font-size:14px; font-weight:bold;"> <a href="tel:' . REGISTED_MOBILE . '" style="text-decoration:blink; color:#0463a3;"><img src="' . COLLECTION_PHONE_ICON . '" alt="phone" width="20" height="20" style="position: relative;
                                margin-bottom: -5px;"/>' . REGISTED_MOBILE . '</a> <a href="' . WEBSITE_URL . '" target="_blank" style="text-decoration:blink; color:#0463a3;"><img src="' . COLLECTION_WEB_ICON . '" alt="web" width="20" height="20" style="position: relative;
                                margin-bottom: -5px;"/> ' . WEBSITE . '</a> <a href="mailto:' . INFO_EMAIL . '" style="text-decoration:blink; color:#0463a3;"> <img src="' . COLLECTION_EMAIL_ICON . '" alt="email" width="20" height="20" style="position: relative;
                                margin-bottom: -5px;"/> ' . INFO_EMAIL . '</a> </td>
                            </tr>
                            </table>
                            </body>
                            </html>';

            require_once (COMPONENT_PATH . 'CommonComponent.php');
            $CommonComponent = new CommonComponent();

            $email_sent_status = $CommonComponent->call_sent_email($to_agent_email, $subject, $message, "", "", $from_agent_email, "");

            if (!empty($email_sent_status['status'])) {
                $result_array['status'] = 1;
            } else {
                $result_array['error'] = $email_sent_status['error'];
            }
        }

        return $result_array;
    }

    public function get_visit_location($lead_id, $visit_id) {
        $result_array = array("status" => 0);

        $select = "MS.status_name as status ";

        if ($visit_id == 1) {
            $select .= " , LC.state_id, MSR.m_state_name as state_name, MCR.m_city_name as city_name";
        }
        if ($visit_id == 2) {
            $select .= " , CE.state_id, MSO.m_state_name as state_name, MCO.m_city_name as city_name";
        }

        $this->db->select($select);
        $this->db->from('leads LD');

        $this->db->join('master_status MS', "MS.status_id = LD.lead_status_id");

        if ($visit_id == 1) {
            $this->db->join('lead_customer LC', "LC.customer_lead_id = LD.lead_id");
            $this->db->join('master_state MSR', "MSR.m_state_id = LC.state_id");
            $this->db->join('master_city MCR', "MCR.m_city_id = LC.city_id");

            $this->db->where("LC.customer_lead_id", $lead_id);
        }

        if ($visit_id == 2) {
            $this->db->join('customer_employment CE', "CE.lead_id = LD.lead_id");
            $this->db->join('master_state MSO', "MSO.m_state_id = CE.state_id");
            $this->db->join('master_city MCO', "MCO.m_city_id = CE.city_id");

            $this->db->where("CE.lead_id", $lead_id);
        }

        $temp_data = $this->db->get();

        if (!empty($temp_data->num_rows())) {
            $result_array['status'] = 1;
            $result_array['data'] = $temp_data->row_array();
        }

        return $result_array;
    }

    public function get_user_email_details($user_id) {
        $result_array = array("status" => 0, "message" => "User details not found.");

        $conditions_user['U.user_id'] = $user_id;
        $conditions_user['U.user_status_id'] = 1;
        $conditions_user['U.user_active'] = 1;
        $conditions_user['U.user_deleted'] = 0;

        $this->db->select("U.name as agent_name, U.email");
        $this->db->from("users U");
        $this->db->where($conditions_user);
        $temp_data = $this->db->get();

        if (!empty($temp_data->num_rows())) {
            unset($result_array['message']);
            $users = $temp_data->row_array();

            $result_array['status'] = 1;
            $result_array['data']['agent_email'] = $users['email'];
            $result_array['data']['agent_name'] = $users['agent_name'];
        }

        return $result_array;
    }

    public function insertUserActivity($user_id, $role_id, $activity_type = 1) {

        $user_activity_log = array();
        $user_activity_log["ual_url"] = "";
        $user_activity_log["ual_platform"] = $this->agent->platform();
        $user_activity_log["ual_browser"] = $this->agent->browser() . ' ' . $this->agent->version();
        $user_activity_log["ual_agent"] = $this->agent->agent_string();
        $user_activity_log["ual_ip"] = $this->input->ip_address();
        $user_activity_log["ual_datetime"] = date("Y-m-d H:i:s");
        $user_activity_log["ual_user_id"] = $user_id;
        $user_activity_log["ual_role_id"] = $role_id;
        $user_activity_log["ual_type_id"] = $activity_type;
        $user_activity_log["ual_source_type"] = 2;

        $this->db->insert('user_activity_log', $user_activity_log);
    }

}
