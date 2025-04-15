<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Performance_Model extends CI_Model {

    function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
    }

    public function sanction_popup_model($user_id, $type_id = 0) {

        $return_data = array('status' => 0, 'data' => array(), 'message' => '');

        $current_date = date('Y-m-01');
        $to_date = date('Y-m-d');

        $target_allocation_query = "SELECT UTA.uta_user_id, UTA.uta_user_target_amount, UTA.uta_user_target_cases, (SELECT SUM(CAM.loan_recommended) FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) WHERE LD.lead_credit_assign_user_id=UTA.uta_user_id AND DATE_FORMAT(CAM.disbursal_date, '%M-%y') = DATE_FORMAT(NOW(), '%M-%y') AND LD.lead_id=CAM.lead_id AND LD.lead_status_id IN(14, 16, 17, 18, 19)) as uta_user_achieve_amount, ";
        $target_allocation_query .= " (SELECT COUNT(LD.lead_id) FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) WHERE LD.lead_credit_assign_user_id=UTA.uta_user_id AND DATE_FORMAT(CAM.disbursal_date, '%M-%y') = DATE_FORMAT(NOW(), '%M-%y') AND LD.lead_id=CAM.lead_id AND LD.lead_status_id IN(14, 16, 17, 18, 19)) as uta_user_achieve_cases ";
        $target_allocation_query .= " FROM user_target_allocation_log UTA INNER JOIN users U ON(UTA.uta_user_id=U.user_id) INNER JOIN user_roles UR ON(UTA.uta_user_id=UR.user_role_user_id) ";
        $target_allocation_query .= " WHERE U.user_status_id=1  AND UR.user_role_type_id=3 AND UTA.uta_type_id=1 AND DATE_FORMAT(UTA.uta_created_on, '%M-%y') = DATE_FORMAT(NOW(), '%M-%y') AND UTA.uta_active=1 AND UTA.uta_deleted=0 AND UTA.uta_user_id=$user_id";

        $monthly_data = $this->db->query($target_allocation_query)->row_array();

        $today_disburse_query = "SELECT COUNT(LD.lead_id) as total_cases, SUM(CAM.loan_recommended) as loan_amount, LD.lead_credit_assign_user_id ";
        $today_disburse_query .= "FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) ";
        $today_disburse_query .= "WHERE LD.lead_active=1 AND LD.lead_status_id IN(14) AND LD.lead_credit_assign_user_id='$user_id' AND CAM.disbursal_date = '$to_date'";

        $daily_data = $this->db->query($today_disburse_query)->row_array();

        $today_sanction_query = "SELECT COUNT(LD.lead_id) as total_cases, SUM(CAM.loan_recommended) as loan_amount, LD.lead_credit_assign_user_id ";
        $today_sanction_query .= "FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) ";
        $today_sanction_query .= "WHERE LD.lead_active=1  AND LD.lead_credit_assign_user_id='$user_id' AND DATE(LD.lead_credit_approve_datetime) = '$to_date'";

        $daily_sanction_data = $this->db->query($today_sanction_query)->row_array();
        
        $monthly_sanction_query = "SELECT COUNT(LD.lead_id) as total_cases, SUM(CAM.loan_recommended) as loan_amount, LD.lead_credit_assign_user_id ";
        $monthly_sanction_query .= "FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) ";
        $monthly_sanction_query .= "WHERE LD.lead_active=1  AND LD.lead_credit_assign_user_id='$user_id' AND DATE_FORMAT(LD.lead_credit_approve_datetime,'%M-%y') = DATE_FORMAT(NOW(),'%M-%y')";
        
        $monthly_sanction_data = $this->db->query($monthly_sanction_query)->row_array();
        
        if (!empty($monthly_data) || !empty($daily_data) || !empty($monthly_sanction_data)) {
            $return_data['data']['today_disburse_cases'] = ($daily_data['total_cases'] > 0 ? $daily_data['total_cases'] : 0);
            $return_data['data']['today_disburse_amount'] = ($daily_data['loan_amount'] > 0 ? $daily_data['loan_amount'] : 0);
            $return_data['data']['today_sanction_cases'] = ($daily_sanction_data['total_cases'] > 0 ? $daily_sanction_data['total_cases'] : 0);
            $return_data['data']['today_sanction_amount'] = ($daily_sanction_data['loan_amount'] > 0 ? $daily_sanction_data['loan_amount'] : 0);
            $return_data['data']['monthly_achieve_cases'] = $monthly_data['uta_user_achieve_cases'];
            $return_data['data']['monthly_sanction_achieve_amount'] = $monthly_data['uta_user_achieve_amount'];
            $return_data['data']['monthly_target_cases'] = $monthly_data['uta_user_target_cases'];
            $return_data['data']['monthly_target_amount'] = $monthly_data['uta_user_target_amount'];
            $return_data['data']['monthly_sanction_cases']=($monthly_sanction_data['total_cases'] > 0 ? $monthly_sanction_data['total_cases'] : 0);
            $return_data['data']['monthly_sanction_amount']=($monthly_sanction_data['loan_amount'] > 0 ? $monthly_sanction_data['loan_amount'] : 0);
            $return_data['status'] = 1;
        }

        return $return_data;
    }

}

?>
