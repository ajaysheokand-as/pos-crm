<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/models/CronJobs/CronCommon_Model.php';

class CronSMS_Model extends CronCommon_Model {

    public function smslog_insert($data) {
        return $this->db->insert('api_sms_logs', $data);
    }

    public function emaillog_insert($data) {
        return $this->db->insert('api_email_logs', $data);
    }

    public function sendSMS($input_mobile, $sms_type_id, $lead_id = 0, $input_data = array()) {
        $return_array = array("status" => 0);

        $provider_name = "ROUTE Mobile";
        $sms_username = urlencode("namanfinl");
        $sms_password = urlencode("ASX1@#SD");
        $sms_entityid = 1201159134511282286;

        $status = 0;
        $error = "";
        $utm_url = "";
        $tempid = "";
        $source = "";
        $input_message = "";
        $customer_salutation = "";
        $customer_name = !empty($input_data['customer_name']) ? $input_data['customer_name'] : "";
        $loan_number = !empty($input_data['loan_no']) ? $input_data['loan_no'] : "";
        $due_amount = !empty($input_data['due_amount']) ? $input_data['due_amount'] : "";
        $repayment_url = "";

        if (!empty($input_data['gender'])) {
            if (strtoupper($input_data['gender']) == "MALE") {
                $customer_salutation = "Mr";
            } else if (strtoupper($input_data['gender']) == "FEMALE") {
                $customer_salutation = "Ms";
            }
        }

        if ($sms_type_id == 1) { //LEAD_GENERAL_SMS
            $source = "LWALLE";
            $tempid = '1207164649106695770';
            $utm_url = 'https://bit.ly/3yZ8cIb'; //CRONSMSNC2022
            $input_message = "Need Instant Personal Loan?\nVisit us at " . $utm_url . " and get your loan disbursed in 30 minutes.\nLoanwalle.com (Naman Finlease)";
        } else if ($sms_type_id == 2) { //LEAD_GENERAL_SMS
            $source = "LWALLE";
            $tempid = '1207164649106695770';
            $utm_url = 'http://bit.ly/3ga9Yjk'; //CRONREPEATSMS
            $input_message = "Need Instant Personal Loan?\nVisit us at " . $utm_url . " and get your loan disbursed in 30 minutes.\nLoanwalle.com (Naman Finlease)";
        } else if ($sms_type_id == 3) { //Today repayment date and due amount
            $source = "LWCOLL";
            $tempid = '1207162555877088237';
            $repayment_url = 'https://www.loanwalle.com/repay-loan'; //CRONREPEATSMS
            $input_message = "Dear $customer_salutation. $customer_name, your Loan No. $loan_number is due for repayment TODAY.  Please ensure to make timely payment of Rs. $due_amount today by clicking on the link \n$repayment_url\n - Loanwalle (Naman Finlease)";
        }

        if (empty($input_mobile) || (strlen($input_mobile) != 10) || empty($source) || empty($tempid) || empty($input_message)) {
            $return_array = array("status" => 0, 'message' => "Missing mandatory fields");
        }

        $type = 0;
        $dlr = 1;
        $destination = $input_mobile;
        $message = urlencode($input_message);

        $data = "username=$sms_username&password=$sms_password&type=$type&dlr=$dlr&destination=$destination&source=$source&message=$message&entityid=$sms_entityid&tempid=$tempid";
        $url = "http://sms6.rmlconnect.net/bulksms/bulksms?";

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data
        ));
        $sms_output = curl_exec($ch);
        curl_close($ch);

        $response_array = explode("|", $sms_output);

        if (!empty($response_array) && $response_array[0] == 1701) { //https://routemobile.com/developers/api/ response code
            $status = 1;
        } else {
            $error = $sms_output;
        }

        $insert_log_array = array();
        $insert_log_array['sms_provider'] = $provider_name;
        $insert_log_array['sms_type_id'] = $sms_type_id;
        $insert_log_array['sms_mobile'] = $input_mobile;
        $insert_log_array['sms_content'] = addslashes($input_message);
        $insert_log_array['sms_template_id'] = $tempid;
        $insert_log_array['sms_template_source'] = $source;
        $insert_log_array['sms_api_status_id'] = $status;
        $insert_log_array['sms_lead_id'] = $lead_id;
        $insert_log_array['sms_errors'] = $error;
        $insert_log_array['sms_created_on'] = date("Y-m-d H:i:s");

        $this->smslog_insert($insert_log_array);

        $return_array = array("status" => $status, 'message' => $error);

        return $return_array;
    }

    public function getAllNotContactCustomerSMS() {
        $return_apps_array = array();

        $start_date = date('Y-m-d', strtotime('-5 days', strtotime(date("Y-m-d"))));

        $sql = "SELECT DISTINCT(TRIM(LD.mobile)) as user_mobile FROM leads LD";
        $sql .= " WHERE LD.lead_entry_date >= '$start_date' AND LD.mobile IS NOT NULL AND LD.mobile!=''";
        $sql .= " AND LD.lead_status_id=9 AND LD.lead_rejected_reason_id in(7,31) AND LD.lead_data_source_id NOT IN(21,27)";
        $sql .= " AND LD.mobile NOT IN (SELECT mobile FROM leads WHERE lead_status_id IN(14,17,18,19,1,2))";

        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getAllCloseLoanCustomerSMS() {
        $return_apps_array = array();

        $sql = "SELECT DISTINCT(TRIM(LD.mobile)) as user_mobile FROM leads LD INNER JOIN loan L ON(LD.lead_id=L.lead_id)";
        $sql .= " WHERE L.loan_closure_date >= '2021-12-01' AND L.loan_closure_date <= '2022-09-30' AND LD.mobile IS NOT NULL AND LD.mobile!=''";
        $sql .= " AND LD.lead_status_id=16 AND LD.lead_data_source_id NOT IN(21,27)";
        $sql .= " AND LD.mobile NOT IN (SELECT mobile FROM leads WHERE lead_status_id IN(14,17,18,19,1,2))";

        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getAllRepaymentReminderSMS($reminder_flag = false, $reminder_days = 0) {

        $current_date = date("Y-m-d");

        if ($reminder_flag == true) {
            $repayment_reminder_days = $reminder_days;
            $reminder_date = date("Y-m-d", strtotime("+$repayment_reminder_days day", strtotime(date("Y-m-d"))));
            $current_date = $reminder_date;
        } else {
            $repayment_reminder_days = 5;
            $reminder_date = date("Y-m-d", strtotime("+$repayment_reminder_days day", strtotime(date("Y-m-d"))));
        }


        $return_apps_array = array();

        $sql = "SELECT LD.lead_id, CONCAT_WS(' ',LC.first_name, LC.middle_name, LC.sur_name) as cust_full_name, LC.first_name, LC.middle_name, LC.sur_name, LC.gender,";
        $sql .= " LC.mobile, LC.alternate_mobile, CAM.loan_recommended, CAM.roi, CAM.tenure, CAM.repayment_date, CAM.disbursal_date, CAM.repayment_amount,";
        $sql .= " L.loan_no, L.loan_total_payable_amount, L.loan_total_received_amount, L.loan_total_outstanding_amount";
        $sql .= " FROM leads LD";
        $sql .= " INNER JOIN lead_customer LC ON(LD.lead_id = LC.customer_lead_id)";
        $sql .= " INNER JOIN credit_analysis_memo CAM ON(LD.lead_id = CAM.lead_id)";
        $sql .= " INNER JOIN loan L ON(L.lead_id = LD.lead_id)";
        $sql .= " WHERE LD.lead_status_id IN(14,19) AND LD.lead_data_source_id NOT IN(21,27) AND CAM.repayment_date >= '$current_date' AND CAM.repayment_date <= '$reminder_date'";
        $sql .= " AND LD.lead_id NOT IN(SELECT COL.lead_id FROM collection COL WHERE COL.collection_active=1 AND COL.collection_deleted=0 AND COL.payment_verification=0)";

        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getIncopleteJourneyData($reminder_flag = false, $reminder_days = 0) {

        $current_datetime = date("Y-m-d H:i:s");

        $return_apps_array = array();

        $sql = "SELECT LD.lead_id, LD.mobile ";
        $sql .= " FROM leads LD";
        $sql .= " WHERE LD.lead_status_id =1 AND LD.first_name IS NULL AND LD.created_on >= DATE_SUB(NOW(), INTERVAL 45 MINUTE)";

        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }
}
