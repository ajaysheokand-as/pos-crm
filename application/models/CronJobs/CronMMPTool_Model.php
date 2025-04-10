<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/models/CronJobs/CronCommon_Model.php';

class CronMMPTool_Model extends CronCommon_Model {

    public function emaillog_insert($data) {
        return $this->db->insert('api_email_logs', $data);
    }

    public function getAllOrganicTagLeads() {
        $return_apps_array = array();

        $sql = "SELECT AF.acaf_response, AF.acaf_id, LD.lead_id, LD.status, LD.stage, LD.lead_status_id FROM api_callback_appsflyer AF";
        $sql .= " INNER JOIN lead_customer LC ON(LC.customer_adjust_adid=AF.acaf_appsflyer_id)";
        $sql .= " INNER JOIN leads LD ON(LD.lead_id=LC.customer_lead_id)";
        $sql .= " WHERE LC.customer_adjust_adid IS NOT NULL";
        $sql .= " AND LD.utm_source='ORGANIC' AND AF.acaf_utm_source!='ORGANIC'";
        $sql .= " AND AF.acaf_event_name='login_otp_verified' ORDER BY acaf_id DESC";

        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getDisbursalLeadsToPushEvent() {
        $return_apps_array = array();

        $sql = 'SELECT LD.lead_id
                FROM leads LD
                INNER JOIN lead_customer LC ON LD.lead_id = LC.customer_lead_id
                INNER JOIN loan L ON LD.lead_id = L.lead_id
                INNER JOIN master_marketing_channel MMC ON(LD.utm_source=MMC.mmc_name)
                WHERE
                    LD.lead_status_id = 14
                    AND LC.customer_adjust_adid IS NOT NULL
                    AND L.loan_mmp_event_push_flag IS NULL
                    AND LD.user_type="NEW" AND DATE(LD.lead_final_disbursed_date) >= "2024-07-13"
                    AND MMC.mmc_affiliate_flag=1 AND MMC.mmc_affiliate_mmp_pid_name IS NOT NULL
                ORDER BY LD.lead_id ASC';

        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getAffIliateDetails() {

        $result_array = array("status" => 0);

        $query = "SELECT * FROM master_marketing_channel MMC";
        $query .= " WHERE MMC.mmc_affiliate_flag=1 AND MMC.mmc_affiliate_mmp_pid_name IS NOT NULL";
        $query .= " AND MMC.mmc_active=1";

        $tempDetails = $this->db->query($query);

        if ($tempDetails->num_rows() > 0) {
            $result_array['status'] = 1;
            $result_array['affiliate_data'] = $tempDetails->result_array();
        }

        return $result_array;
    }
}
