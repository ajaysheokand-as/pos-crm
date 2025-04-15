<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/models/CronJobs/CronCommon_Model.php';

class CronSanction_Model extends CronCommon_Model {

    var $non_contactable_days = 7;

    //    public function __construct() {
    //        parent::__construct();
    //        date_default_timezone_set('Asia/Kolkata');
    //    }

    public function emaillog_insert($data) {
        return $this->db->insert('api_email_logs', $data);
    }

    public function get_lead_new_list($user_type = null, $orderBy = null) {

        $return_array = ['status' => 0];
        if (!empty($user_type)) {
            $userType = " and user_type = '" . $user_type . "'";
        } else {
            $userType = '';
        }

        if (!empty($orderBy)) {
            if ($orderBy == 'salary') {
                $orderBy = " ORDER BY LD.monthly_salary_amount DESC, LD.lead_id ASC ";
            } else if ($orderBy == 'date') {
                $orderBy = " ORDER BY LD.lead_entry_date DESC, LD.lead_id ASC ";
            } else {
                $orderBy = " ORDER BY LD.lead_id ASC ";
            }
        } else {
            $orderBy = " ORDER BY LD.lead_id ASC ";
        }

        if ($user_type == 'REPEAT') {
            $twentySecondsAgo = date('Y-m-d H:i:s', strtotime('-10 minutes'));
            $sql = "SELECT LD.lead_id, LD.user_type, LD.utm_source, LD.pancard, LD.email, LD.mobile, LD.first_name, LD.monthly_salary_amount,LD.lead_entry_date";
            $sql .= " FROM leads LD";
            $sql .= " WHERE LD.status='APPLICATION-NEW' AND LD.pancard IS NOT NULL AND (LD.lead_screener_assign_user_id IS NULL OR LD.lead_screener_assign_user_id=0)  AND `LD`.`lead_is_mobile_verified` = 1 AND LD.first_name IS NOT NULL AND `LD`.`lead_active` = 1 AND `LD`.`lead_deleted` = 0 and LD.created_on <= '" . $twentySecondsAgo .  "' " . $userType . "  " . $orderBy;
        } else {
            $twentySecondsAgo = date('Y-m-d H:i:s', strtotime('-20 minutes'));
            $sql = "SELECT LD.lead_id, LD.user_type, LD.utm_source, LD.pancard, LD.email, LD.mobile, LD.first_name, LD.monthly_salary_amount,LD.lead_entry_date";
            $sql .= " FROM leads LD";

            $sql .= " WHERE LD.status='LEAD-NEW' AND LD.pancard IS NOT NULL AND (LD.lead_screener_assign_user_id IS NULL OR LD.lead_screener_assign_user_id=0)  AND `LD`.`lead_is_mobile_verified` = 1 AND LD.first_name IS NOT NULL AND `LD`.`lead_active` = 1 AND `LD`.`lead_deleted` = 0 and ( LD.created_on <= '" . $twentySecondsAgo .  "' and LD.created_on > '2024-08-20 23:53:00')  " . $userType . "  " . $orderBy;

            //$sql .= " WHERE LD.status='LEAD-NEW' AND LD.pancard IS NOT NULL AND (LD.lead_screener_assign_user_id IS NULL OR LD.lead_screener_assign_user_id=0)  AND `LD`.`lead_is_mobile_verified` = 1 AND LD.first_name IS NOT NULL AND `LD`.`lead_active` = 1 AND `LD`.`lead_deleted` = 0 and LD.created_on > '2024-08-20 23:53:00' and `LD`.`source` = 'ARDSALARYONTIME' ORDER BY LD.monthly_salary_amount DESC, LD.lead_entry_date DESC;";
        }

        //print_r($sql); die;
        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $return_array['status'] = 1;
            $return_array['data'] = $tempDetails->result_array();
        }
        //print_r($return_array); die;
        return $return_array;
    }

    public function get_users_lead_list_nouse() {
        $current_date = date("Y-m-d");
        $return_array = ['status' => 0];

        $sql  = "SELECT U.user_id, U.name, U.email, U.mobile, SUM(IF(LD.lead_id > 0,1,0)) as total_leads, U.user_status_id, ";
        $sql .= " (SELECT IF(LAA.ula_user_status=1,1,0) FROM user_lead_allocation_log LAA WHERE LAA.ula_user_id=U.user_id AND DATE(LAA.ula_created_on)='$current_date' AND LAA.ula_active=1 ORDER BY LAA.ula_id DESC LIMIT 1) as user_active_flag,";
        $sql .= " (SELECT IF(LFR.ula_user_case_type>0,LFR.ula_user_case_type,0) FROM user_lead_allocation_log LFR WHERE LFR.ula_user_id=U.user_id AND DATE(LFR.ula_created_on)='$current_date' AND LFR.ula_user_status=1 AND LFR.ula_active=1 ORDER BY LFR.ula_id DESC LIMIT 1) as user_active_case_type";
        $sql .= " FROM users U INNER JOIN user_roles UR ON(U.user_id=UR.user_role_user_id AND UR.user_role_type_id=2)";

        //$sql .= " LEFT JOIN leads LD ON(LD.lead_screener_assign_user_id>0 AND LD.lead_screener_assign_user_id=U.user_id AND LD.lead_status_id IN(2,3)) AND `LD`.`lead_is_mobile_verified` = 1 AND LD.first_name IS NOT NULL ";
        $sql .= " LEFT JOIN leads LD ON(LD.lead_screener_assign_user_id>0 AND LD.lead_screener_assign_user_id=U.user_id AND LD.lead_status_id IN(2)) AND `LD`.`lead_is_mobile_verified` = 1 AND LD.first_name IS NOT NULL AND LD.monthly_salary_amount >= 25000 AND LD.monthly_salary_amount < 30000";

        $sql .= " WHERE U.user_id=UR.user_role_user_id AND UR.user_role_type_id=2 ";
        $sql .= " AND U.user_active=1 AND U.user_status_id=1 AND UR.user_role_active=1 AND U.role_id in (2) ";
        $sql .= " GROUP BY U.user_id ORDER BY total_leads ASC";
        //echo "<br/>" . $sql;
        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $return_array['status'] = 1;
            $return_array['data'] = $tempDetails->result_array();
        }

        return $return_array;
    }

    public function get_lead_list_nouse($conditions = []) {
        $return_array = ['status' => 0, 'data' => []];

        $this->db->select('LD.*')
            ->from('leads LD')
            ->where($conditions)
            ->where_in('LD.lead_status_id', [1, 41, 42])
            ->limit(250)
            ->order_by('LD.monthly_salary_amount', 'DESC');

        $tempDetails = $this->db->get();

        if ($tempDetails->num_rows() > 0) {
            $return_array['status'] = 1;
            $return_array['data'] = $tempDetails->result_array();
        }

        return $return_array;
    }

    public function get_lead_list($conditions = []) {
        $return_array = ['status' => 0, 'data' => []];

        $this->db->select('LD.lead_id, LD.mobile, LD.email, LD.first_name, LD.monthly_salary_amount, LD.lead_screener_assign_user_id, LD.lead_screener_assign_datetime, LD.lead_status_id, LD.stage, LD.user_type, LD.lead_data_source_id')
            ->from('leads LD')
            ->where($conditions)
            ->limit(1500)
            ->order_by('LD.monthly_salary_amount', 'DESC');

        $tempDetails = $this->db->get();

        if ($tempDetails->num_rows() > 0) {
            $return_array['status'] = 1;
            $return_array['data'] = $tempDetails->result_array();
        }

        return $return_array;
    }

    public function get_users_lead_list($user_type = 0) {
        $current_date = date("Y-m-d");
        $return_array = ['status' => 0, 'data' => []];

        $sql  = "SELECT
                    U.user_id,
                    U.name,
                    U.email,
                    U.mobile,
                    SUM(IF(LD.lead_id > 0, 1, 0)) AS total_leads,

                    (SELECT
                        SUM(IF(LU.lead_id > 0, 1, 0))
                    FROM
                        leads LU
                    WHERE
                        LU.lead_screener_assign_user_id = U.user_id
                        AND LU.lead_screener_assign_user_id > 0
                        AND DATE(LU.lead_screener_assign_datetime) = '$current_date'
                    ) AS total_today_process_leads,

                    (SELECT
                        SUM(IF(LU.lead_id > 0, 1, 0))
                    FROM
                        leads LU
                    WHERE
                        LU.lead_screener_assign_user_id = U.user_id
                        AND LU.lead_screener_assign_user_id > 0
                     	AND LU.lead_status_id = 2
                    ) AS total_current_inprocess_leads,

                    (SELECT
                    IF(LAA.ula_user_status=1,1,0)
                    FROM user_lead_allocation_log LAA
                    WHERE LAA.ula_user_id=U.user_id
                    AND DATE(LAA.ula_created_on)= '$current_date'
                    AND LAA.ula_active=1
                    ORDER BY LAA.ula_id DESC LIMIT 1
                    ) AS user_active_flag

                FROM
                    users U
                INNER JOIN
                    user_roles UR
                    ON U.user_id = UR.user_role_user_id
                    AND UR.user_role_type_id = 2
                LEFT JOIN
                    leads LD
                    ON LD.lead_screener_assign_user_id = U.user_id
                    AND LD.lead_status_id IN (2)
                WHERE
                    U.user_id = UR.user_role_user_id
                    AND UR.user_role_type_id = 2
                    AND U.user_is_loanwalle = $user_type
                    AND U.user_active = 1
                    AND U.user_status_id = 1
                    AND UR.user_role_active = 1
                    AND UR.user_role_product_id = 1

                GROUP BY
                    U.user_id

                ORDER BY
                    total_leads ASC";
        // echo "<br/>" . $sql; exit;
        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $return_array['status'] = 1;
            $return_array['data'] = $tempDetails->result_array();
        }

        return $return_array;
    }

    public function get_screener_users_lead_list($allocation_type = 0) {
        $current_date = date("Y-m-d");
        $return_array = ['status' => 0, 'data' => []];

        $sql  = "SELECT
                    U.user_id,
                    U.name,
                    U.email,
                    U.mobile,
                    SUM(IF(LD.lead_id > 0, 1, 0)) AS total_leads,

                    (SELECT
                        SUM(IF(LU.lead_id > 0, 1, 0))
                    FROM
                        leads LU
                    WHERE
                        LU.lead_screener_assign_user_id = U.user_id
                        AND LU.lead_screener_assign_user_id > 0
                        AND DATE(LU.lead_screener_assign_datetime) = '$current_date'
                    ) AS total_today_process_leads,

                    (SELECT
                        SUM(IF(LU.lead_id > 0, 1, 0))
                    FROM
                        leads LU
                    WHERE
                        LU.lead_screener_assign_user_id = U.user_id
                        AND LU.lead_screener_assign_user_id > 0
                     	AND LU.lead_status_id = 2
                    ) AS total_current_inprocess_leads,

                    (SELECT
                    IF(LAA.ula_user_status=1,1,0)
                    FROM user_lead_allocation_log LAA
                    WHERE LAA.ula_user_id=U.user_id
                    AND DATE(LAA.ula_created_on)= '$current_date'
                    AND LAA.ula_active=1
                    ORDER BY LAA.ula_id DESC LIMIT 1
                    ) AS user_active_flag

                FROM
                    users U
                INNER JOIN
                    user_roles UR
                    ON U.user_id = UR.user_role_user_id
                    AND UR.user_role_type_id = 2
                LEFT JOIN
                    leads LD
                    ON LD.lead_screener_assign_user_id = U.user_id
                    AND LD.lead_status_id IN (2)
                WHERE
                    U.user_id = UR.user_role_user_id
                    AND UR.user_role_type_id = 2
                    AND U.user_allocation_type_id = $allocation_type
                    AND U.user_active = 1
                    AND U.user_status_id = 1
                    AND UR.user_role_active = 1
                    AND UR.user_role_product_id = 1

                GROUP BY
                    U.user_id

                ORDER BY
                    total_leads ASC";
        // echo "<br/>" . $sql;
        // exit;
        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $return_array['status'] = 1;
            $return_array['data'] = $tempDetails->result_array();
        }

        return $return_array;
    }

    public function get_credit_users_lead_list($allocation_type = 0) {
        $current_date = date("Y-m-d");
        $return_array = ['status' => 0, 'data' => []];

        if ($allocation_type == 2) {
            $AllowUsersList = "AND U.user_id IN (61,99,12,25,15,32)";
        } elseif ($allocation_type == 1) {
            $AllowUsersList = "AND U.user_id IN (39, 54, 76, 128, 98, 60, 155, 77, 157, 158)";
        } else {
            $AllowUsersList = "";
        }

        $sql  = "SELECT
                    U.user_id,
                    U.name,
                    U.email,
                    U.mobile,
                    SUM(IF(LD.lead_id > 0, 1, 0)) AS total_leads,

                    (SELECT
                        SUM(IF(LU.lead_id > 0, 1, 0))
                    FROM
                        leads LU
                    WHERE
                        LU.lead_credit_assign_user_id = U.user_id
                        AND LU.lead_credit_assign_user_id > 0
                        AND DATE(LU.lead_credit_assign_datetime) = '$current_date'
                    ) AS total_today_process_leads,

                    (SELECT
                        SUM(IF(LU.lead_id > 0, 1, 0))
                    FROM
                        leads LU
                    WHERE
                        LU.lead_credit_assign_user_id = U.user_id
                        AND LU.lead_credit_assign_user_id > 0
                     	AND LU.lead_status_id = 5
                    ) AS total_current_inprocess_leads,

                    (SELECT
                    IF(LAA.ula_user_status=1,1,0)
                    FROM user_lead_allocation_log LAA
                    WHERE LAA.ula_user_id=U.user_id
                    AND DATE(LAA.ula_created_on)= '$current_date'
                    AND LAA.ula_active=1
                    ORDER BY LAA.ula_id DESC LIMIT 1
                    ) AS user_active_flag

                FROM
                    users U
                INNER JOIN
                    user_roles UR
                    ON U.user_id = UR.user_role_user_id
                    AND UR.user_role_type_id = 3
                LEFT JOIN
                    leads LD
                    ON LD.lead_credit_assign_user_id = U.user_id
                    AND LD.lead_status_id IN (5)
                WHERE
                    U.user_id = UR.user_role_user_id
                    AND UR.user_role_type_id = 3
                    AND U.user_allocation_type_id = $allocation_type
                    AND U.user_active = 1
                    AND U.user_status_id = 1
                    AND UR.user_role_active = 1
                    AND UR.user_role_product_id = 1
                    " . $AllowUsersList . "
                GROUP BY
                    U.user_id

                ORDER BY
                    total_leads ASC";
        // echo "<br/>" . $sql;
        // exit;
        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $return_array['status'] = 1;
            $return_array['data'] = $tempDetails->result_array();
        }

        return $return_array;
    }

    public function update_sanction_target() {

        $return_array = array('status' => 0, 'message' => '');

        $updated_on = date('Y-m-d H:i:s');

        $update = "UPDATE user_target_allocation_log UTA INNER JOIN users U ON(UTA.uta_user_id=U.user_id) INNER JOIN user_roles UR ON(UTA.uta_user_id=UR.user_role_user_id) ";
        $update .= "SET UTA.uta_user_achieve_amount=(SELECT SUM(CAM.loan_recommended) FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) WHERE LD.lead_credit_assign_user_id=UTA.uta_user_id AND DATE_FORMAT(CAM.disbursal_date, '%M-%y') = DATE_FORMAT(NOW(), '%M-%y') AND LD.lead_id=CAM.lead_id AND LD.lead_data_source_id NOT IN(21,27,33) AND LD.lead_status_id IN(14, 16, 17, 19)), ";
        $update .= "UTA.uta_user_achieve_cases=(SELECT COUNT(LD.lead_id) FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) WHERE LD.lead_credit_assign_user_id=UTA.uta_user_id AND DATE_FORMAT(CAM.disbursal_date, '%M-%y') = DATE_FORMAT(NOW(), '%M-%y') AND LD.lead_id=CAM.lead_id AND LD.lead_data_source_id NOT IN(21,27,33) AND LD.lead_status_id IN(14, 16, 17, 19)), ";
        $update .= "UTA.uta_updated_on = '$updated_on' ";
        $update .= "WHERE U.user_status_id=1  AND UR.user_role_type_id=3 AND UTA.uta_type_id=1 AND DATE_FORMAT(UTA.uta_created_on, '%M-%y') = DATE_FORMAT(NOW(), '%M-%y') AND UTA.uta_active=1 AND UTA.uta_deleted=0";

        $tempDetails = $this->db->query($update);

        if ($tempDetails == 1) {
            $return_array['status'] = 1;
            $return_array['message'] = 'Achieve Target updated.';
        }

        return $return_array;
    }

    public function update_sanction_collection_history() {

        $return_array = array('status' => 0, 'message' => '', 'updated_record' => 0);
        $insert_collection = array();
        $user_id = 0;
        $uta_id = 0;
        $current_date = date("Y-m-d", strtotime("-1 year", strtotime(date('Y-m-d'))));
        $to_date = date('Y-m-d');

        $select_users = "SELECT uta_id, uta_type_id, uta_user_id, uta_created_on FROM user_target_allocation_log WHERE DATE(uta_created_on) >= '$current_date' AND DATE(uta_created_on) <= '$to_date' AND uta_active=1 AND uta_type_id=1";

        $user_data = $this->db->query($select_users)->result_array();

        if (!empty($user_data)) {

            foreach ($user_data as $value) {

                $user_id = $value['uta_user_id'];
                $uta_id = $value['uta_id'];

                $select = "SELECT COUNT(LD.lead_id) as total_cases, SUM(IF(LD.lead_status_id=16 OR LD.lead_status_id=17 OR LD.lead_status_id=18, 1,0)) as closed_cases, SUM(CAM.loan_recommended) as loan_amount, SUM(L.loan_total_payable_amount) as payable_amount, SUM(L.loan_principle_received_amount) as principle_rcvd, SUM(L.loan_interest_received_amount) as interest_rcvd, SUM(L.loan_total_received_amount) as total_rcvd, SUM(L.loan_principle_outstanding_amount) as principle_outstanding, SUM(L.loan_interest_outstanding_amount) as interest_outstanding ";
                $select .= "FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) ";
                $select .= "WHERE LD.lead_active=1 AND LD.lead_data_source_id NOT IN(21,27,33) AND LD.lead_status_id IN(14, 16, 17, 18, 19) AND LD.lead_credit_assign_user_id='$user_id' AND CAM.repayment_date >= '$current_date' AND CAM.repayment_date <= '$to_date'";

                $tempDetails = $this->db->query($select)->row();

                if (!empty($tempDetails)) {

                    $insert_collection = array(
                        'uta_user_loan_total_cases' => $tempDetails->total_cases,
                        'uta_user_loan_closed_cases' => $tempDetails->closed_cases,
                        'uta_user_loan_total_principle' => $tempDetails->loan_amount,
                        'uta_user_loan_payable_amount' => $tempDetails->payable_amount,
                        'uta_user_loan_principle_received' => $tempDetails->principle_rcvd,
                        'uta_user_loan_int_received' => $tempDetails->interest_rcvd,
                        'uta_user_loan_total_received' => $tempDetails->total_rcvd,
                        'uta_user_loan_principle_outstanding' => $tempDetails->principle_outstanding,
                        'uta_user_loan_interest_outstanding' => $tempDetails->interest_outstanding,
                    );

                    $condition = "uta_id=$uta_id AND uta_active=1 AND uta_type_id=1 AND uta_user_id = $user_id";
                    $this->update('user_target_allocation_log', $condition, $insert_collection);

                    $return_array['status'] = 1;
                    $return_array['message'] = 'Updated Successfully.';
                    $return_array['updated_record'] += 1;
                }
            }
        }
        return $return_array;
    }

    public function get_not_contactable_lead_list() {
        $return_array = ['status' => 0];
        $days_ago = date('Y-m-d', strtotime('-' . $this->non_contactable_days . ' days', strtotime(date('Y-m-d'))));

        $sql = "SELECT LD.lead_id,LD.email,LD.mobile,LD.user_type,LD.utm_source,LD.first_name,LD.lead_rejected_user_id,LD.lead_rejected_assign_counter,LD.lead_status_id FROM leads LD";
        $sql .= " WHERE LD.lead_status_id=9 AND LD.lead_rejected_reason_id in (7,31) AND ";
        $sql .= "(LD.lead_rejected_assign_user_id IS NULL OR LD.lead_rejected_assign_user_id=0) AND ";
        $sql .= "(LD.lead_rejected_assign_counter is NULL OR LD.lead_rejected_assign_counter<=5) AND LD.lead_rejected_datetime > '" . $days_ago . "'";
        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $return_array['status'] = 1;
            $return_array['data'] = $tempDetails->result_array();
        }

        return $return_array;
    }

    public function get_not_contactable_user_rejected_leads($user_id) {
        $return_array = ['status' => 0];
        $days_ago = date('Y-m-d', strtotime('-' . $this->non_contactable_days . ' days', strtotime(date('Y-m-d'))));

        $sql = "SELECT lrr_lead_id FROM lead_rejection_reasons LLR WHERE lrr_active=1 AND lrr_user_id=" . $user_id . " AND LLR.lrr_lead_id IN (select LD.lead_id from leads LD";
        $sql .= " WHERE LD.lead_status_id=9 and LD.lead_rejected_reason_id in (7,31) AND ";
        $sql .= "(LD.lead_rejected_assign_user_id IS NULL OR LD.lead_rejected_assign_user_id=0) AND ";
        $sql .= "(LD.lead_rejected_assign_counter is NULL OR LD.lead_rejected_assign_counter<=5) AND LD.lead_rejected_datetime > '" . $days_ago . "')";
        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $return_array['status'] = 1;
            $return_array['data'] = $tempDetails->result_array();
        }

        return $return_array;
    }

    public function get_not_contactable_users_lead_list() {
        $current_date = date("Y-m-d");
        $return_array = ['status' => 0];

        $sql = "SELECT U.user_id, U.name, U.email, U.mobile, SUM(IF(LD.lead_id > 0,1,0)) as total_leads, ";
        $sql .= " (SELECT IF(LAA.ula_user_status=1,1,0) FROM user_lead_allocation_log LAA WHERE LAA.ula_user_id=U.user_id AND DATE(LAA.ula_created_on)='$current_date' AND LAA.ula_active=1 ORDER BY LAA.ula_id DESC LIMIT 1) as user_active_flag,";
        $sql .= " (SELECT IF(LFR.ula_user_case_type>0,LFR.ula_user_case_type,0) FROM user_lead_allocation_log LFR WHERE LFR.ula_user_id=U.user_id AND DATE(LFR.ula_created_on)='$current_date' AND LFR.ula_user_status=1 AND LFR.ula_active=1 ORDER BY LFR.ula_id DESC LIMIT 1) as user_active_case_type";
        $sql .= " FROM users U INNER JOIN user_roles UR ON(U.user_id=UR.user_role_user_id AND UR.user_role_type_id=2)";
        $sql .= " LEFT JOIN leads LD ON(LD.lead_rejected_assign_user_id>0 AND LD.lead_rejected_assign_user_id=U.user_id AND LD.lead_status_id=9 AND LD.lead_rejected_reason_id in (7,31))";
        $sql .= " WHERE U.user_id=UR.user_role_user_id AND UR.user_role_type_id=2 AND U.user_is_loanwalle=0";
        $sql .= " AND U.user_active=1 AND U.user_status_id=1 AND UR.user_role_active=1";
        $sql .= " GROUP BY U.user_id ORDER BY total_leads ASC";
        //        echo "<br/>" . $sql;
        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $return_array['status'] = 1;
            $return_array['data'] = $tempDetails->result_array();
        }

        return $return_array;
    }

    public function get_lead_hold($set_hours = 0) {
        $return_array = ['status' => 0];

        if (!empty($set_hours)) {
            $hours = $set_hours;
        } else {
            $hours = 36;
        }

        $sql = "SELECT
                    lead_id,
                    lead_status_id,
                    stage,
                    updated_on,
                    monthly_salary_amount
                FROM
                    leads
                WHERE
                    lead_status_id IN(2, 3)
                    AND TIMESTAMPDIFF(HOUR, lead_screener_assign_datetime, NOW()) > $hours
                    AND (monthly_salary_amount = 0 OR monthly_salary_amount > 25000)
                    AND user_type = 'NEW'
                ORDER BY
                    updated_on ASC";

        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $return_array['status'] = 1;
            $return_array['data'] = $tempDetails->result_array();
        }

        return $return_array;
    }
}
