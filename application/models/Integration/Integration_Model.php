<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Integration_Model extends CI_Model {

    public $result_array = array('status' => 0);
    public $master_income_type = array(1 => "Salaried", 2 => "Self-Employed");
    public $visit_type = array(1 => 'Residence', 2 => 'Office');

    function __construct() {
        parent::__construct();
    }

    public function select($table, $conditions, $data = null) {
        return $this->db->select($data)->where($conditions)->from($table)->get();
    }

    public function insert($table, $data) {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    public function update($table, $conditions, $data) {
        return $this->db->where($conditions)->update($table, $data);
    }

    public function getLeadDetails($lead_id) {

        $return_array = array("status" => 0, "app_data" => array());

        if (!empty($lead_id)) {
            //            $tempDetails = $this->db->select("*")->where(["lead_id" => $lead_id, 'lead_active' => 1, 'lead_deleted' => 0])->from("leads")->get();
            $this->db->select("LD.*,LC.customer_digital_ekyc_flag");
            $this->db->from('leads LD');
            $this->db->where(["LD.lead_id" => $lead_id, 'LD.lead_active' => 1, 'LD.lead_deleted' => 0]);
            $this->db->join('lead_customer LC', 'LC.customer_lead_id = LD.lead_id AND LC.customer_active=1 AND LC.customer_deleted=0');
            $tempDetails = $this->db->get();
            if ($tempDetails->num_rows()) {
                $return_array["status"] = 1;
                $return_array["app_data"] = $tempDetails->row_array();
            }
        }

        return $return_array;
    }

    public function getQuickCallDetails($lead_id, $campaign_name) {

        $return_array = array("status" => 0, "output_data" => array());

        if (!empty($lead_id) && !empty($campaign_name)) {

            $conditions = [
                "quickcall_lead_id" => $lead_id,
                "quickcall_api_status_id" => 1,
                "quickcall_campaign_name" => $campaign_name,
                "quickcall_active" => 1,
                "quickcall_deleted" => 0
            ];

            $tempDetails = $this->db->select("*")->where($conditions)->from("api_quickcall_logs")->order_by("quickcall_log_id", "DESC")->get()->row_array();

            if (!empty($tempDetails)) {
                $return_array["status"] = 1;
                $return_array["output_data"] = $tempDetails;
            }
        }


        return $return_array;
    }

    public function getUMSUserDetails($user_id) {

        $return_array = array("status" => 0, "app_data" => array());

        if (!empty($user_id)) {
            $tempDetails = $this->db->select("*")->where(["user_id" => $user_id])->from("users")->get();
            if ($tempDetails->num_rows()) {
                $return_array["status"] = 1;
                $return_array["app_data"] = $tempDetails->row_array();
            }
        }


        return $return_array;
    }

    public function getLeadLoanDetails($lead_id) {

        $return_array = array("status" => 0, "loan_data" => array());

        if (!empty($lead_id)) {
            $tempDetails = $this->db->select("*")->from("loan")->where(["lead_id" => $lead_id, 'loan_active' => 1, 'loan_deleted' => 0])->order_by('loan_id', 'DESC')->get();
            if ($tempDetails->num_rows()) {
                $return_array["status"] = 1;
                $return_array["loan_data"] = $tempDetails->row_array();
            }
        }

        return $return_array;
    }

    public function getLeadCAMDetails($lead_id) {

        $return_array = array("status" => 0, "cam_data" => array());

        if (!empty($lead_id)) {
            $tempDetails = $this->db->select("*")->from("credit_analysis_memo")->where(["lead_id" => $lead_id, 'cam_active' => 1, 'cam_deleted' => 0])->order_by('cam_id', 'DESC')->get();
            if ($tempDetails->num_rows()) {
                $return_array["status"] = 1;
                $return_array["cam_data"] = $tempDetails->row_array();
            }
        }

        return $return_array;
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

    public function getDisbursementTransDetails($method_id, $lead_id, $log_id, $bank_id) {

        $return_array = array("status" => 0, "log_data" => array());

        if (!empty($lead_id)) {
            //"disburse_log_id" => $log_id,
            $tempDetails = $this->db->select("*")->from("api_disburse_logs")->where(["disburse_method_id" => $method_id, "disburse_lead_id" => $lead_id, "disburse_bank_id" => $bank_id, "disburse_active" => 1, "disburse_deleted" => 0])->order_by('disburse_log_id', 'DESC')->get();
            if ($tempDetails->num_rows()) {
                $return_array["status"] = 1;
                $return_array["log_data"] = $tempDetails->row_array();
            }
        }

        return $return_array;
    }

    public function getLeadDocumentDetails($lead_id, $doc_id) {

        $return_array = array("status" => 0, "docs_data" => array());

        if (!empty($lead_id) && !empty($doc_id)) {

            $tempDetails = $query = $this->db->select("*")->where(["docs_id" => $doc_id, 'lead_id' => $lead_id])->from("docs")->get();

            if ($tempDetails->num_rows()) {
                $return_array["status"] = 1;
                $return_array["docs_data"] = $tempDetails->row_array();
            }
        }
        return $return_array;
    }

    public function getLeadFullDetails($lead_id) {

        $return_array = array("status" => 0, "app_data" => array());

        if (!empty($lead_id)) {
            $this->db->select('LD.lead_id, LC.customer_seq_id, LD.status, LD.stage, LD.lead_status_id, LC.email, LC.alternate_email, LC.email_verified_status, LC.alternate_email_verified_status, LD.mobile, LC.alternate_mobile, CONCAT_WS(" ", LC.first_name, LC.middle_name, LC.sur_name) name, CONCAT_WS(" ", LC.current_house, LC.current_locality, LC.current_landmark) address, MS.m_state_name state, MC.m_city_name city, LD.pincode, LC.pancard, LD.source, LD.lead_screener_assign_user_id');
            $this->db->from('leads LD');
            $this->db->join('lead_customer LC', 'LC.customer_lead_id = LD.lead_id AND LC.customer_active=1 AND LC.customer_deleted=0');
            $this->db->join('master_state MS', 'LD.state_id = MS.m_state_id', 'LEFT');
            $this->db->join('master_city MC', 'LD.city_id = MC.m_city_id', 'LEFT');
            $this->db->where(['LD.lead_id' => $lead_id]);
            $tempDetails = $this->db->get();
            if ($tempDetails->num_rows()) {
                $return_array["status"] = 1;
                $return_array["app_data"] = $tempDetails->row_array();
            }
        }

        return $return_array;
    }

    public function getPaymentDetails($lead_id) {

        $return_array = array("status" => 0, "app_data" => array());

        if (!empty($lead_id)) {
            $this->db->select('LD.lead_id, LC.customer_seq_id, LD.status, LC.email, LD.mobile, LC.alternate_mobile, CONCAT_WS(" ", LC.first_name, LC.middle_name, LC.sur_name) name, CONCAT_WS(" ", LC.current_house, LC.current_locality, LC.current_landmark) address, MS.m_state_name state, MC.m_city_name city, LD.pincode, LC.pancard, LD.source, CAM.loan_recommended, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, CAM.tenure, CAM.roi, LD.loan_no, LD.status, MB.m_branch_name branch');
            $this->db->from('leads LD');
            $this->db->join('lead_customer LC', 'LC.customer_lead_id = LD.lead_id', 'INNER');
            $this->db->join('credit_analysis_memo CAM', 'LD.lead_id = CAM.lead_id', 'INNER');
            $this->db->join('master_state MS', 'LD.state_id = MS.m_state_id', 'LEFT');
            $this->db->join('master_city MC', 'LD.city_id = MC.m_city_id', 'LEFT');
            $this->db->join('master_branch MB', 'LD.lead_branch_id = MB.m_branch_id', 'LEFT');
            $this->db->where(['LD.lead_id' => $lead_id]);
            $tempDetails = $this->db->get();
            if ($tempDetails->num_rows()) {
                $return_array["status"] = 1;
                $return_array["app_data"] = $tempDetails->row_array();
            }
        }
        if (!empty($lead_id)) {

            $sql = "SELECT SUM(received_amount) received_amount, max(date_of_recived) date_of_recived FROM collection WHERE payment_verification=1 AND collection_active=1 AND lead_id='$lead_id'";
            $tempquery = $this->db->query($sql);
            $return_array["app_data1"] = $tempquery->row_array();
        }

        return $return_array;
    }

    public function getLeadDocumentDetailsByNovelId($doc_id) {

        $return_array = array("status" => 0, "docs_data" => array());

        if (!empty($doc_id)) {

            $tempDetails = $query = $this->db->select("*")->where(["docs_novel_return_id" => $doc_id])->from("docs")->get();

            if ($tempDetails->num_rows()) {
                $return_array["status"] = 1;
                $return_array["docs_data"] = $tempDetails->row_array();
            }
        }
        return $return_array;
    }

    public function getDisbursementTransLogs($lead_id) {
        return $this->db->select("*")->from("lead_disbursement_trans_log")->where(['disb_trans_lead_id' => $lead_id, 'disb_trans_active' => 1, 'disb_trans_deleted' => 0])->order_by('disb_trans_id', 'desc')->get()->row_array();
    }

    public function getCustomerBankAccountDetails($lead_id, $cust_bank_id) {

        $return_array = array("status" => 0, "banking_data" => array());

        if (!empty($lead_id)) {
            $tempDetails = $this->db->select("*")->from("customer_banking")->where(["lead_id" => $lead_id, "id" => $cust_bank_id, "customer_banking_active" => 1, "customer_banking_deleted" => 0])->order_by('id', 'DESC')->get();
            if ($tempDetails->num_rows()) {
                $return_array["status"] = 1;
                $return_array["banking_data"] = $tempDetails->row_array();
            }
        }

        return $return_array;
    }

    public function getBankAccountVerifiationLastToken() {

        $return_array = array("status" => 0, "token_data" => array());

        $tempDetails = $this->db->select("bav_response_datetime, bav_auth_token")->from("api_bank_account_verification_logs")->where(["bav_method_id" => 1, "bav_active" => 1, "bav_deleted" => 0])->order_by('bav_id', 'DESC')->get();
        if ($tempDetails->num_rows()) {
            $return_array["status"] = 1;
            $return_array["token_data"] = $tempDetails->row_array();
        }


        return $return_array;
    }

    public function getLoanApplicationDetails($lead_id) {

        $return_array = array("status" => 0, "app_data" => array());

        if (!empty($lead_id)) {
            $sql = "SELECT LD.lead_id, LD.source , LD.status,LD.loan_no, LC.email, LD.mobile, LC.alternate_mobile, CONCAT_WS(' ', LC.first_name, LC.middle_name, LC.sur_name) as name,";
            $sql .= " CONCAT_WS(' ', LC.current_house, LC.current_locality, LC.current_landmark) address, MS.m_state_name state, MC.m_city_name city, LD.pincode, LC.pancard,";
            $sql .= " CAM.loan_recommended, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, CAM.tenure, CAM.roi,  MB.m_branch_name branch,";
            $sql .= " L.loan_total_received_amount, L.loan_principle_outstanding_amount, L.loan_interest_outstanding_amount, L.loan_penalty_outstanding_amount";

            $sql .= " FROM leads LD ";
            $sql .= "INNER JOIN lead_customer LC ON LC.customer_lead_id=LD.lead_id ";
            $sql .= "INNER JOIN credit_analysis_memo CAM ON LD.lead_id=CAM.lead_id ";
            $sql .= "INNER JOIN loan L ON LD.lead_id=L.lead_id ";
            $sql .= "LEFT JOIN master_state MS ON LD.state_id=MS.m_state_id ";
            $sql .= "LEFT JOIN master_city MC ON LD.city_id=MC.m_city_id ";
            $sql .= "LEFT JOIN master_branch MB ON LD.lead_branch_id=MB.m_branch_id ";
            $sql .= "WHERE LD.lead_id IN($lead_id)";

            $tempDetails = $this->db->query($sql)->result_array();

            if (!empty($tempDetails)) {
                $return_array["status"] = 1;
                $return_array["app_data"] = $tempDetails;
            }
        }

        return $return_array;
    }

    public function geteSignCamDetails($lead_id) {

        $return_array = array("status" => 0, "cam_data" => array());

        if (!empty($lead_id)) {
            $sql = "SELECT DISTINCT LD.lead_id, LD.lead_data_source_id, LD.lead_status_id, LD.lead_screener_assign_user_id, LD.lead_branch_id, LD.user_type, C.pancard";
            $sql .= " ,C.first_name,C.middle_name,sur_name,C.gender";
            $sql .= " ,C.email_verified_status, C.customer_digital_ekyc_flag, CAM.cam_appraised_monthly_income";
            $sql .= " ,CAM.cam_status, CAM.eligible_loan, CAM.loan_recommended, CAM.processing_fee_percent, CAM.roi, CAM.admin_fee";
            $sql .= " ,CAM.disbursal_date, CAM.repayment_date, CAM.tenure, CAM.repayment_amount, CAM.net_disbursal_amount, CAM.cam_advance_interest_amount";
            $sql .= " FROM leads LD";
            $sql .= " INNER JOIN lead_customer C ON(LD.lead_id=C.customer_lead_id)";
            $sql .= " INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id)";
            $sql .= " INNER JOIN customer_employment CE ON(LD.lead_id=CE.lead_id)";
            $sql .= " WHERE LD.lead_id=" . $lead_id;
            $tempDetails = $this->db->query($sql);

            if ($tempDetails->num_rows()) {
                $return_array["status"] = 1;
                $return_array["cam_data"] = $tempDetails->row_array();
            }
        }

        return $return_array;
    }

    public function getLeadDetailsList($lead_array) {

        $return_array = array("status" => 0, "app_data" => array());

        if (!empty($lead_array)) {

            $lead_array = implode(",", $lead_array);

            $sql = "SELECT LD.lead_id, LD.source , LD.status,LD.loan_no, LD.loan_amount, LD.user_type, LC.email, LD.mobile, LC.alternate_mobile, CONCAT_WS(' ', LC.first_name, LC.middle_name, LC.sur_name) name,";
            $sql .= " CONCAT_WS(' ', LC.current_house, LC.current_locality, LC.current_landmark) address, MS.m_state_name state, MC.m_city_name city, LD.pincode, LC.pancard,";
            $sql .= " CAM.loan_recommended, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, CAM.tenure, CAM.roi,  MB.m_branch_name branch,";
            $sql .= " L.loan_total_received_amount, L.loan_principle_outstanding_amount, L.loan_interest_outstanding_amount, L.loan_penalty_outstanding_amount";

            $sql .= " FROM leads LD ";
            $sql .= " INNER JOIN lead_customer LC ON LC.customer_lead_id = LD.lead_id ";
            $sql .= " INNER JOIN credit_analysis_memo CAM ON LD.lead_id = CAM.lead_id ";
            $sql .= " INNER JOIN loan L ON LD.lead_id = L.lead_id ";
            $sql .= " LEFT JOIN master_state MS ON LD.state_id = MS.m_state_id ";
            $sql .= " LEFT JOIN master_city MC ON LD.city_id = MC.m_city_id ";
            $sql .= " LEFT JOIN master_branch MB ON LD.lead_branch_id = MB.m_branch_id ";
            $sql .= " WHERE LD.lead_id IN($lead_array) AND LD.lead_active=1 ";

            $tempDetails = $this->db->query($sql)->result_array();

            if (!empty($tempDetails)) {
                $return_array["status"] = 1;
                $return_array["app_data"] = $tempDetails;
            }
        }

        return $return_array;
    }

    public function geteSignDetailsByDocId($docId) {

        $return_array = array("status" => 0, "app_data" => array());

        if (!empty($docId)) {

            $sql = "SELECT esign_id, esign_lead_id FROM `api_esign_logs`  ";
            $sql .= " WHERE esign_return_url='$docId'  ";
            $sql .= " AND esign_provider=2 AND esign_method_id=1 AND esign_api_status_id=1 AND esign_active=1 AND esign_deleted=0 ";
            $sql .= " ORDER BY esign_id DESC LIMIT 1";

            $tempDetails = $this->db->query($sql)->row_array();

            if (!empty($tempDetails)) {
                $return_array["status"] = 1;
                $return_array["esign_data"] = $tempDetails;
            }
        }

        return $return_array;
    }
}
