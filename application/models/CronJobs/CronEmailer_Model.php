<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/models/CronJobs/CronCommon_Model.php';

class CronEmailer_Model extends CronCommon_Model {

    public function emaillog_insert($data) {
        return $this->db->insert('api_email_logs', $data);
    }

    public function emaillog_leagal_insert($data) {
        return $this->db->insert('legal_email_logs', $data);
    }

    public function getTodayBirthdayCustomer() {
        $return_apps_array = array();

        $dob_val = date("m-d");
        //email sent to customer whose has birthday today.
        $tempDetails = $this->db->query("SELECT LOWER(LD.email) as user_email_id, LD.first_name as 'name'  FROM leads LD INNER JOIN lead_customer LC ON(LC.customer_lead_id = LD.lead_id) WHERE DATE_FORMAT(DATE(LC.dob),'%m-%d')='$dob_val' AND LC.dob IS NOT NULL AND LD.email!='' AND LD.first_name !='' AND LD.email IS NOT NULL GROUP BY LD.email");

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getAllNewCustomerEmails() {
        $return_apps_array = array();

        //        $tempDetails = $this->db->query("SELECT DISTINCT(TRIM(LOWER(email))) as user_email_id FROM leads WHERE email IS NOT NULL AND email!='' AND DATE(`created_on`) >= '2021-03-01' AND DATE(`created_on`) <= '2021-06-30' AND status IN('Rejected','Credit','New Leads','Docs pending','HOLD','Application','New Lead','Cancelled')");
        //        $tempDetails = $this->db->query("SELECT DISTINCT(TRIM(LOWER(LD.email))) as user_email_id FROM `leads` LD INNER JOIN `tbl_rejected_loan` RL ON(LD.lead_id=RL.lead_id) WHERE LD.lead_id=RL.lead_id AND LD.email IS NOT NULL AND LD.email!='' AND RL.reson in('LOW BUREAU SCORE','NOT CONTACTABLE','NI: PRICING HIGHER','NE: SALARY LOW','NE: ELIGIBILITY NOT MET','NE: DELAY IN SALARY','NI: DOCS NOT SENT','NE: VERIFICATION NEGATIVE','NE: POOR RTR WITH HIGH BUREAU SCORE','NE: POOR BANKING/ BOUNCING','NE: NEGATIVE PROFILE','CANCEL: NOT INTERESTED','NE: NO ADDRESS PROOF') AND LD.created_on >= '2021-01-01' AND LD.created_on <= '2021-12-31' AND LD.utm_source NOT IN('NEWCUSTEMAILJAN22','NEW CUSTOMER','NEWCUSTSMS' ,'LOAN PAID','LoanPaidSms')"); //getAllNewCustomerEmails
        $tempDetails = $this->db->query("SELECT DISTINCT(TRIM(LOWER(LD.email))) as user_email_id FROM `leads` LD WHERE LD.lead_entry_date >= '2022-01-01' AND LD.email IS NOT NULL AND LD.email!='' AND LD.lead_status_id=9 AND LD.lead_rejected_reason_id in(7,31)");
        //        $tempDetails = $this->db->query("SELECT DISTINCT(TRIM(LOWER(LD.email))) as user_email_id FROM `leads` LD WHERE LD.email IS NOT NULL AND LD.email!='' AND LD.user_type='NEW' AND lead_status_id in(1,2,3,4,5,6,7,8,9,15) AND LD.email NOT IN (SELECT email FROM leads LC INNER JOIN loan LL ON(LC.lead_id=LL.lead_id) WHERE LL.loan_status_id>12)"); //getAllNewCustomerEmails

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getAllNotContactCustomerEmails() {
        $return_apps_array = array();

        $start_date = date('Y-m-d', strtotime('-30 days', strtotime(date("Y-m-d"))));

        $sql = "SELECT DISTINCT(TRIM(LOWER(LD.email))) as user_email_id FROM leads LD ";
        $sql .= " WHERE LD.lead_entry_date >= '$start_date' AND LD.email IS NOT NULL AND LD.email!=''";
        $sql .= " AND LD.lead_status_id=9 AND LD.lead_rejected_reason_id in(7,31) AND LD.lead_data_source_id NOT IN(21,27)";
        $sql .= " AND LD.email NOT IN (SELECT email FROM leads WHERE lead_status_id IN(14,17,18,19,1,2) AND email IS NOT NULL AND email!='')";

        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    //Close loan customers
    public function getAllCloseLoanEmails() {

        $return_apps_array = array();

        $tempDetails = $this->db->query("SELECT DISTINCT(TRIM(LOWER(email))) as user_email_id FROM leads WHERE email IS NOT NULL AND email!='' AND lead_status_id = 16");

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getAllCloseLoanForFeedbackEmails() {

        $return_apps_array = array();

        $tempDetails = $this->db->query("SELECT lead_id, first_name, TRIM(LOWER(email)) as user_email_id FROM leads WHERE email IS NOT NULL AND email!='' AND lead_status_id = 16 AND lead_data_source_id NOT IN(21,27)");

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getAllOldCloudEmails() {
        $return_apps_array = array();
        $tempDetails = $this->db->query("SELECT DISTINCT(TRIM(LOWER(email))) as user_email_id FROM settlement_cases_email WHERE email IS NOT NULL AND email!=''");

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getAllReplayLoanEmails() {
        $return_apps_array = array();
        $tempDetails = $this->db->query("SELECT DISTINCT(TRIM(LOWER(LD.email))) as user_email_id FROM leads LD INNER JOIN credit_analysis_memo CAM on (CAM.lead_id=LD.lead_id) WHERE CAM.lead_id=LD.lead_id AND CAM.repayment_date >='2022-01-29' AND CAM.repayment_date <='2022-02-05' AND LD.lead_status_id = 14 AND LD.lead_data_source_id NOT IN(21,27)");

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getAllValentineWeekEmails() {
        $return_apps_array = array();

        $tempDetails = $this->db->query("SELECT DISTINCT(TRIM(LOWER(LD.email))) as user_email_id FROM `leads` LD WHERE LD.email IS NOT NULL AND LD.email!='' AND (lead_entry_date>='2021-01-01' AND lead_entry_date<='2022-01-31') AND LD.email NOT IN (SELECT email FROM leads LC WHERE LC.lead_status_id IN(12,13,14,17,18,19) OR utm_source='ALLCUSTVALENTINE2022')");

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getAllLegaNoticeEmails() {

        $current_date = date("Y-m-d");
        $ntc_cases = "'NFPL12064','NFPL11409','NFPL10331','NFPL10500','NFPL10452','NFPL11791','NFPL11673','NFPL10684','NFPL11067','NFPL11766','NFPL12521','NFPL10102','NFPL11720','NFPL10805','NFPL11842','NFPL11910','NFPL11479','NFPL11821','NFPL10108','NFPL10694','NFPL10887','NFPL11669','NFPL11065','NFPL12055','NFPL12222','NFPL11938','NFPL11817','NFPL12324','NFPL12451','NFPL11614','NFPL11302','NFPL12003','NFPL10968','NFPL11282','NFPL10318','NFPL12153','NFPL11900','NFPL10697','NFPL10516','NFPL10553','NFPL10855','NFPL10656','NFPL10987','NFPL10905','NFPL11613','NFPL11898','NFPL12108','NFPL11772','NFPL11226','NFPL11744','NFPL12149','NFPL12373','NFPL11989','NFPL11931','NFPL12139','NFPL10381','NFPL12144','NFPL11156','NFPL10722','NFPL11841','NFPL12178','NFPL12151','NFPL10637','NFPL12160','NFPL09788','NFPL12027','NFPL10548','NFPL12174','NFPL11597','NFPL10785','NFPL12754','NFPL10328','NFPL09814','NFPL11812','NFPL10105','NFPL10153','NFPL10359','NFPL10384','NFPL11445','NFPL10991','NFPL11169','NFPL09248','NFPL09391','NFPL09473','NFPL09517','NFPL09567','NFPL09596','NFPL09614','NFPL09667','NFPL09190','NFPL08091','NFPL08503','NFPL09353','NFPL09486'";
        //        $ntc_cases = "'NFPL12064'";

        $return_apps_array = array();

        $sql = "SELECT LD.lead_id, LC.email , CONCAT_WS(' ',LC.first_name, LC.middle_name, LC.sur_name) as cust_full_name, LC.first_name, LC.middle_name, LC.sur_name, LC.mobile, L.loan_no, L.recommended_amount, CAM.roi, CAM.tenure, CAM.repayment_date, CAM.disbursal_date, CAM.repayment_amount";
        $sql .= " FROM leads LD";
        $sql .= " INNER JOIN lead_customer LC ON(LD.lead_id = LC.customer_lead_id)";
        $sql .= " INNER JOIN credit_analysis_memo CAM ON(LD.lead_id = CAM.lead_id)";
        $sql .= " INNER JOIN loan L ON(L.lead_id = LD.lead_id)";
        $sql .= " WHERE LD.loan_no IN($ntc_cases) AND LD.lead_status_id IN(14,19) AND repayment_date < '$current_date'";

        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getAllRepaymentReminderEmails($reminder_flag = false, $reminder_days = 0) {

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

        $sql = "SELECT LD.lead_id, CONCAT_WS(' ',LC.first_name, LC.middle_name, LC.sur_name) as cust_full_name, LC.first_name, LC.middle_name, LC.sur_name";
        $sql .= " , LC.email,LC.alternate_email, LC.mobile, LC.alternate_mobile, L.loan_no, L.recommended_amount, CAM.loan_recommended, CAM.roi, CAM.tenure, CAM.repayment_date, CAM.disbursal_date, CAM.repayment_amount";
        $sql .= " , credit_manager.name as credit_manager_name, credit_manager.email as credit_manager_email, credit_manager.mobile as credit_manager_mobile";
        $sql .= " FROM leads LD";
        $sql .= " INNER JOIN lead_customer LC ON(LD.lead_id = LC.customer_lead_id)";
        $sql .= " INNER JOIN credit_analysis_memo CAM ON(LD.lead_id = CAM.lead_id)";
        $sql .= " INNER JOIN loan L ON(L.lead_id = LD.lead_id)";
        $sql .= " LEFT JOIN users credit_manager ON(LD.lead_credit_assign_user_id = credit_manager.user_id )";
        $sql .= " WHERE LD.lead_status_id IN(14,19) AND LD.lead_data_source_id NOT IN(21,27) AND CAM.repayment_date >= '$current_date' AND CAM.repayment_date <= '$reminder_date'";
        $sql .= " AND LD.lead_id NOT IN(SELECT COL.lead_id FROM collection COL WHERE COL.collection_active=1 AND COL.collection_deleted=0 AND COL.payment_verification=0)";

        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getAllCustomerEmail() {
        $return_apps_array = array();

        $tempDetails = $this->db->query("SELECT LOWER(email) as user_email_id FROM leads  WHERE email!='' AND email IS NOT NULL GROUP BY email");

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getAllDefaulterCustomerApps($defaulter_start_day = 0, $defaulter_days = 0) {

        $current_date = date("Y-m-d");

        $defaulter_end_date = date("Y-m-d", strtotime("-$defaulter_start_day day", strtotime($current_date)));

        $defaulter_start_date = date("Y-m-d", strtotime("-$defaulter_days day", strtotime($defaulter_end_date)));

        $return_apps_array = array();

        $sql = "SELECT LD.lead_id, CONCAT_WS(' ',LC.first_name, LC.middle_name, LC.sur_name) as cust_full_name, LC.first_name, LC.middle_name, LC.sur_name";
        $sql .= " , LC.email,LC.alternate_email, LC.mobile, LC.alternate_mobile, L.loan_no, CAM.loan_recommended, CAM.roi, CAM.tenure, CAM.repayment_date, CAM.disbursal_date, CAM.repayment_amount";
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

    public function getAllDefaulterCustomerByDate($start_date = '', $end_date = '') {

        $start_date = date("Y-m-d", strtotime($start_date));

        $end_date = date("Y-m-d", strtotime($end_date));

        $return_apps_array = array();

        $sql = "SELECT LD.lead_id, CONCAT_WS(' ',LC.first_name, LC.middle_name, LC.sur_name) as cust_full_name, LC.first_name, LC.middle_name, LC.sur_name";
        $sql .= " , LC.email,LC.alternate_email, LC.mobile, LC.alternate_mobile, L.loan_no, CAM.loan_recommended, CAM.roi, CAM.tenure, CAM.repayment_date, CAM.disbursal_date, CAM.repayment_amount";
        $sql .= " , L.loan_id";
        $sql .= " FROM leads LD";
        $sql .= " INNER JOIN lead_customer LC ON(LD.lead_id = LC.customer_lead_id AND LC.customer_active=1 AND LC.customer_deleted=0)";
        $sql .= " INNER JOIN credit_analysis_memo CAM ON(LD.lead_id = CAM.lead_id AND CAM.cam_active=1 AND CAM.cam_deleted=0)";
        $sql .= " INNER JOIN loan L ON(L.lead_id = LD.lead_id AND L.loan_active=1 AND L.loan_deleted=0)";
        $sql .= " WHERE LD.lead_status_id IN(14,19) AND LD.lead_data_source_id NOT IN(21,27) AND CAM.disbursal_date >= '$start_date' AND CAM.disbursal_date <= '$end_date'";
        $sql .= " AND LD.lead_id NOT IN(SELECT COL.lead_id FROM collection COL WHERE COL.collection_active=1 AND COL.collection_deleted=0 AND COL.payment_verification=0)";

        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getAllLoanCustomer() {

        $return_apps_array = array();

        $sql = "SELECT DISTINCT LC.email as user_email_id";
        $sql .= " FROM leads LD";
        $sql .= " INNER JOIN lead_customer LC ON(LD.lead_id = LC.customer_lead_id AND LC.customer_active=1 AND LC.customer_deleted=0)";
        $sql .= " INNER JOIN loan L ON(L.lead_id = LD.lead_id AND L.loan_active=1 AND L.loan_deleted=0)";
        $sql .= " WHERE L.loan_status_id = 14 AND LD.lead_data_source_id NOT IN(21) AND LC.email!='' AND LC.email IS NOT NULL";

        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getAllHighTicketSizeCustomer() {

        $return_apps_array = array();

        $sql = "SELECT DISTINCT TRIM(LD.email) as user_email_id FROM leads LD  INNER JOIN credit_analysis_memo CAM ON(LD.lead_id = CAM.lead_id AND CAM.cam_active=1 AND CAM.cam_deleted=0)  INNER JOIN loan L ON(L.lead_id = LD.lead_id AND L.loan_active=1 AND L.loan_deleted=0)  WHERE LD.lead_status_id IN(16) AND LD.lead_data_source_id NOT IN(21,27,30) AND (CAM.loan_recommended >= 50000 OR LD.lead_data_source_id IN(29,31))";

        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getAllDiwaliCustomer() {

        $return_apps_array = array();

        $sql = "SELECT DISTINCT TRIM(email) as user_email_id FROM leads LD INNER JOIN master_city mc ON(LD.city_id=mc.m_city_id) WHERE email is not NULL AND email!='' AND (mc.m_city_category='A' OR lead_status_id IN(14,16,19)) AND lead_status_id NOT IN(17,18) AND lead_data_source_id NOT IN(21,27) AND email NOT IN(SELECT email_address FROM api_email_logs WHERE email_type_id=27)";

        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function get_closed_loan_for_reloan() {
        $return_array = ['status' => 0];
        $today = date("Y-m-d");
        $sql = "SELECT
                        LD.lead_id,
                        LD.email,
                        LD.loan_no,
                        LD.first_name,
                        LD.pancard,
                        LD.mobile,
                        LD.status
                FROM
                        leads LD
                        INNER JOIN collection as C ON (C.lead_id = LD.lead_id)
                WHERE
                        LD.lead_status_id=16
                        AND DATE(C.closure_payment_updated_on) <= '" . $today . "'
                        AND NOT EXISTS (
                                SELECT
                                        1
                                FROM
                                        leads LD2
                                WHERE
                                        LD.pancard = LD2.pancard
                                        AND LD2.lead_status_id NOT IN (8, 9, 16)
                        )
                        AND NOT EXISTS (
                                SELECT
                                        1
                                FROM
                                        customer_black_list BL
                                WHERE
                                        BL.bl_customer_pancard = LD.pancard AND BL.bl_active=1 AND BL.bl_deleted=0
                        )
                GROUP BY
                        LD.pancard, LD.mobile";

        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $return_array['status'] = 1;
            $return_array['loan'] = $tempDetails->result_array();
        }

        return $return_array;
    }

    public function getAllRepaymentReminderData($reminder_flag = false, $reminder_days = 0) {

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

        $sql = "SELECT
                    LD.lead_id,
                    CONCAT_WS (' ', LC.first_name, LC.middle_name, LC.sur_name) as cust_full_name,
                    LC.first_name,
                    LC.middle_name,
                    LC.sur_name,
                    LC.email,
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
                    credit_manager.name as credit_manager_name,
                    credit_manager.email as credit_manager_email,
                    credit_manager.mobile as credit_manager_mobile,
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
                    LEFT JOIN users credit_manager ON (
                        LD.lead_credit_assign_user_id = credit_manager.user_id
                    )
                WHERE
                    LD.lead_status_id IN (14, 19)
                     AND CAM.repayment_date >= '$current_date' AND CAM.repayment_date <= '$reminder_date'
                    AND LD.lead_id NOT IN (
                        SELECT
                            COL.lead_id
                        FROM
                            collection COL
                        WHERE
                            COL.collection_active = 1
                            AND COL.collection_deleted = 0
                            AND COL.payment_verification = 0
                    )
                ORDER BY dpd ASC";

        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }
}
