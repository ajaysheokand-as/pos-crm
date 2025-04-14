<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Verification_Model extends CI_Model {

    function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
    }

    public function getVerificationData($lead_id) {
        $data = array();
        if (!empty($lead_id)) {
            $sql = "SELECT leads.lead_is_mobile_verified, lead_customer.email, lead_customer.email_verified_on, lead_customer.alternate_email, lead_customer.alternate_email_verified_on, lead_customer.email_verified_status, lead_customer.alternate_email_verified_status, scm_residence_user.name as scm_fi_res_name, scm_office_user.name as scm_fi_office_user";
            // $sql .= " , rm_residence_user.name as rm_fi_res_name, rm_office_user.name as rm_fi_office_user ,tbl_verification.residence_initiated_on, tbl_verification.office_initiated_on, tbl_verification.init_residence_cpv, tbl_verification.init_office_cpv  ";
            $sql .= "  ,cam_sanction_letter_esgin_type_id,cam_sanction_letter_esgin_file_name,cam_sanction_letter_esgin_on,cam_sanction_letter_ip_address";
            $sql .= " FROM leads";
            // $sql .= " LEFT JOIN tbl_verification ON tbl_verification.lead_id = leads.lead_id";
            $sql .= " LEFT JOIN lead_customer ON lead_customer.customer_lead_id = leads.lead_id";
            $sql .= " LEFT JOIN credit_analysis_memo CAM ON(CAM.lead_id = leads.lead_id)";
            $sql .= " LEFT JOIN users scm_residence_user ON scm_residence_user.user_id = leads.lead_fi_scm_residence_assign_user_id";
            $sql .= " LEFT JOIN users scm_office_user ON scm_office_user.user_id = leads.lead_fi_scm_office_assign_user_id";
            // $sql .= " LEFT JOIN users rm_residence_user ON rm_residence_user.user_id = tbl_verification.residece_cpv_allocated_to";
            // $sql .= " LEFT JOIN users rm_office_user ON rm_office_user.user_id = tbl_verification.office_cpv_allocated_to";
            $sql .= " where leads.lead_id='$lead_id' ";

            $query = $this->db->query($sql);

            if ($query->num_rows() > 0) {
                $data = $query->row_array();
            }
        }

        return $data;
    }

    public function getFinboxDeviceData($lead_id) {

        $finbox_response_data = $this->db->query("SELECT leads.lead_id , leads.lead_active, APIfinbox.finbox_dc_lead_id, APIfinbox.finbox_dc_provider_id, APIfinbox.finbox_dc_api_status_id , APIfinbox.finbox_dc_request,APIfinbox.finbox_dc_response FROM `leads`
        INNER join api_finbox_device_connect_logs APIfinbox ON APIfinbox.finbox_dc_lead_id = leads.lead_id
        where leads.lead_id = $lead_id AND APIfinbox.finbox_dc_api_status_id=1
        ORDER BY APIfinbox.finbox_dc_lead_id ASC");


        $finbox_data = $finbox_response_data->row();
        $finbox_data_result = json_decode($finbox_data->finbox_dc_response, true);
        return $finbox_data_result;
    }
    public function getFinboxBankingDeviceData($lead_id) {

        $data = array();
        if (!empty($lead_id)) {
            $query = $this->db->query("SELECT leads.lead_id , leads.lead_active, APIfinbox.finbox_bc_lead_id, APIfinbox.finbox_bc_provider_id, APIfinbox.finbox_bc_api_status_id , APIfinbox.finbox_bc_method_id, APIfinbox.finbox_bc_request,APIfinbox.finbox_bc_response FROM `leads`
            INNER join api_finbox_bank_connect_logs APIfinbox ON APIfinbox.finbox_bc_lead_id = leads.lead_id
            where APIfinbox.finbox_bc_lead_id = leads.lead_id AND APIfinbox.finbox_bc_api_status_id=1
            ORDER BY APIfinbox.finbox_bc_lead_id ASC");

            //$query = $this->db->query($sql);
            return $query->result();
        }


        // $finbox_banking_response_data = $this->db->query("");
        // echo '<pre>';
        // print_r($finbox_banking_response_data );

        // $finbox_data = $finbox_banking_response_data->row();
        // $finbox_banking_data_result = json_decode($finbox_data->finbox_bc_response, true);

        // return $finbox_banking_data_result;

    }
}
