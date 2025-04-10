<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class CronCall_Model extends CI_Model {

    public function calllog_insert($data) {
        return $this->db->insert('api_call_campaign_logs', $data);
    }

    public function getAllRepaymentReminderEmails($start_day = 0, $end_day = 0) {

        $current_date = date("Y-m-d", strtotime("+$start_day day", strtotime(date("Y-m-d"))));
        $reminder_date = date("Y-m-d", strtotime("+$end_day day", strtotime(date("Y-m-d"))));

        $return_apps_array = array();

        $sql = "SELECT LD.lead_id, CONCAT_WS(' ',LC.first_name, LC.middle_name, LC.sur_name) as cust_full_name, LC.first_name, LC.middle_name, LC.sur_name";
        $sql .= " , LC.email,LC.alternate_email, LC.mobile, LC.alternate_mobile, L.loan_no, L.recommended_amount, CAM.loan_recommended, CAM.roi, CAM.tenure, CAM.repayment_date, CAM.disbursal_date, CAM.repayment_amount";
        $sql .= " FROM leads LD";
        $sql .= " INNER JOIN lead_customer LC ON(LD.lead_id = LC.customer_lead_id)";
        $sql .= " INNER JOIN credit_analysis_memo CAM ON(LD.lead_id = CAM.lead_id)";
        $sql .= " INNER JOIN loan L ON(L.lead_id = LD.lead_id)";
        $sql .= " WHERE LD.lead_status_id IN(14,19) AND LD.lead_data_source_id NOT IN(21,27) AND repayment_date >= '$current_date' AND repayment_date <= '$reminder_date'";
        $sql .= " AND LD.lead_id NOT IN(SELECT COL.lead_id FROM collection COL WHERE COL.collection_active=1 AND COL.collection_deleted=0 AND COL.payment_verification=0)";
        
        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getAllDefaulterCollectionApps($defaulter_start_day = 0, $defaulter_days = 0) {

        $current_date = date("Y-m-d");

        $defaulter_end_date = date("Y-m-d", strtotime("-$defaulter_start_day day", strtotime($current_date)));

        $defaulter_start_date = date("Y-m-d", strtotime("-$defaulter_days day", strtotime($defaulter_end_date)));

        $return_apps_array = array();

        $sql = "SELECT LD.lead_id, CONCAT_WS(' ',LC.first_name, LC.middle_name, LC.sur_name) as cust_full_name, LC.first_name, LC.middle_name, LC.sur_name";
        $sql .= " , LC.email,LC.alternate_email, LC.mobile, LC.alternate_mobile, L.loan_no, L.recommended_amount, CAM.loan_recommended, CAM.roi, CAM.tenure, CAM.repayment_date, CAM.disbursal_date, CAM.repayment_amount";
        $sql .= " , L.loan_id";
        $sql .= " FROM leads LD";
        $sql .= " INNER JOIN lead_customer LC ON(LD.lead_id = LC.customer_lead_id AND LC.customer_active=1 AND LC.customer_deleted=0)";
        $sql .= " INNER JOIN credit_analysis_memo CAM ON(LD.lead_id = CAM.lead_id AND CAM.cam_active=1 AND CAM.cam_deleted=0)";
        $sql .= " INNER JOIN loan L ON(L.lead_id = LD.lead_id AND L.loan_active=1 AND L.loan_deleted=0)";
        $sql .= " WHERE LD.lead_status_id IN(14,19) AND LD.lead_data_source_id NOT IN(21,27) AND CAM.repayment_date > '$defaulter_start_date' AND CAM.repayment_date <= '$defaulter_end_date'";
        $sql .= " AND LD.lead_id NOT IN(SELECT COL.lead_id FROM collection COL WHERE COL.collection_active=1 AND COL.collection_deleted=0 AND COL.payment_verification=0)";
        
        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

}

?>
