<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/models/CronJobs/CronCommon_Model.php';

class CronCollection_Model extends CronCommon_Model {

//    public function insert($table, $data) {
//        $this->db->insert($table, $data);
//        return $this->db->insert_id();
//    }
//
//    public function update($table, $conditions, $data) {
//        return $this->db->where($conditions)->update($table, $data);
//    }

    public function emaillog_insert($data) {
        return $this->db->insert('api_email_logs', $data);
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
        $sql .= " WHERE LD.lead_status_id IN(14,19) AND LD.lead_data_source_id!=21 AND CAM.repayment_date > '$defaulter_start_date' AND CAM.repayment_date <= '$defaulter_end_date'";
//        echo $sql;
        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getAllLoansApps() {

        $return_apps_array = array();

        $sql = "SELECT LD.lead_id, L.loan_no";
        $sql .= " FROM leads LD";
        $sql .= " INNER JOIN lead_customer LC ON(LD.lead_id = LC.customer_lead_id AND LC.customer_active=1 AND LC.customer_deleted=0)";
        $sql .= " INNER JOIN credit_analysis_memo CAM ON(LD.lead_id = CAM.lead_id AND CAM.cam_active=1 AND CAM.cam_deleted=0)";
        $sql .= " INNER JOIN loan L ON(L.lead_id = LD.lead_id AND L.loan_active=1 AND L.loan_deleted=0)";
        $sql .= " WHERE LD.lead_status_id IN(14, 16, 17, 18, 19) ORDER BY LD.lead_id ASC";

        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getAllOpenCaseLoansApps() {

        $return_apps_array = array();

        $sql = "SELECT LD.lead_id, L.loan_no,CAM.disbursal_date,CAM.repayment_date, CURDATE() as currentdate, DATE_ADD(CAM.repayment_date, INTERVAL 60 DAY) as plust_repay_60day";
        $sql .= " FROM leads LD";
        $sql .= " INNER JOIN lead_customer LC ON(LD.lead_id = LC.customer_lead_id AND LC.customer_active=1 AND LC.customer_deleted=0)";
        $sql .= " INNER JOIN credit_analysis_memo CAM ON(LD.lead_id = CAM.lead_id AND CAM.cam_active=1 AND CAM.cam_deleted=0)";
        $sql .= " INNER JOIN loan L ON(L.lead_id = LD.lead_id AND L.loan_active=1 AND L.loan_deleted=0)";
        $sql .= " WHERE LD.lead_status_id IN(14, 19) AND DATE_ADD(CAM.repayment_date, INTERVAL 60 DAY) >= CURDATE()";
        $sql .= " ORDER BY CAM.repayment_date ASC";

        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_apps_array = $tempDetails->result_array();
        }

        return $return_apps_array;
    }

    public function getCollectionAssignmentCasesLead($dpd_type_id) {

        $return_apps_array = array();

        $sql = "SELECT LD.lead_id, LD.lead_status_id, CAM.loan_recommended, CAM.repayment_date, IF(CAM.loan_recommended >0 AND CAM.loan_recommended <=25000, 1,IF(CAM.loan_recommended >25000 AND CAM.loan_recommended <=50000,2,IF(CAM.loan_recommended >50000,3,0))) as loan_type_id";
        $sql .= " FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) ";
        $sql .= " WHERE lead_status_id IN(14, 19) AND CAM.loan_recommended>0 "; //AND CAM.repayment_date >= '$from_date' AND CAM.repayment_date <= '$to_date' 

        if ($dpd_type_id == 1) {
            $from_date = date("Y-m-d", strtotime("-10 day"));
            $to_date = date("Y-m-d", strtotime("+5 day"));
            $sql .= " AND LD.lead_pre_collection_executive_assign_user_id1 IS NOT NULL AND CAM.repayment_date >= '$from_date' AND CAM.repayment_date <= '$to_date'  ";
        } elseif ($dpd_type_id == 2) {
            $from_date = date("Y-m-d", strtotime("-40 day"));
            $to_date = date("Y-m-d", strtotime("-11 day"));
            $sql .= " AND LD.lead_pre_collection_executive_assign_user_id2 IS NOT NULL AND CAM.repayment_date >= '$from_date' AND CAM.repayment_date <= '$to_date'  ";
        } elseif ($dpd_type_id == 3) {
            $from_date = date("Y-m-d", strtotime("-60 day"));
            $to_date = date("Y-m-d", strtotime("-41 day"));
            $sql .= " AND LD.lead_collection_executive_assign_user_id1 IS NOT NULL AND CAM.repayment_date >= '$from_date' AND CAM.repayment_date <= '$to_date'  ";
        } elseif ($dpd_type_id == 4) {
            $to_date = date("Y-m-d", strtotime("-60 day"));
            $sql .= " AND LD.lead_collection_executive_assign_user_id2 IS NOT NULL  AND CAM.repayment_date <= '$to_date'  ";
        }

        $sql .= " ORDER BY CAM.loan_recommended ASC ";

//        echo $sql;

        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {

            $result_array = $tempDetails->result_array();

            foreach ($result_array as $key => $value) {
                $return_apps_array[$value['loan_type_id']][$key]['lead_id'] = $value['lead_id'];
                $return_apps_array[$value['loan_type_id']][$key]['lead_status_id'] = $value['lead_status_id'];
                $return_apps_array[$value['loan_type_id']][$key]['loan_recommended'] = $value['loan_recommended'];
                $return_apps_array[$value['loan_type_id']][$key]['repayment_date'] = $value['repayment_date'];
                $return_apps_array[$value['loan_type_id']][$key]['loan_type_id'] = $value['loan_type_id'];
            }
        }

        return $return_apps_array;
    }

    public function get_collection_users_lead_list($role_id, $dpd_type_id) {
        $current_date = date("Y-m-d");
        $return_array = ['status' => 0];

        if (empty($role_id) || !in_array($role_id, [7, 20]) || empty($dpd_type_id)) {
            $return_array['message'] = "Role ID Required.";
            return $return_array;
        }

        if ($role_id == 20) {
            if ($dpd_type_id == 1) {
                $collection_executive_id = " LD.lead_pre_collection_executive_assign_user_id1>0 AND LD.lead_pre_collection_executive_assign_user_id1=U.user_id AND";
            } elseif ($dpd_type_id == 2) {
                $collection_executive_id = " LD.lead_pre_collection_executive_assign_user_id2>0 AND LD.lead_pre_collection_executive_assign_user_id2=U.user_id AND";
            } else {
                return $return_array['message'] = "Role Id does not match.";
            }
        } elseif ($role_id == 7) {
            if ($dpd_type_id == 3) {
                $collection_executive_id = " LD.lead_collection_executive_assign_user_id1>0 AND LD.lead_collection_executive_assign_user_id1=U.user_id AND";
            } elseif ($dpd_type_id == 4) {
                $collection_executive_id = " LD.lead_collection_executive_assign_user_id2>0 AND LD.lead_collection_executive_assign_user_id2=U.user_id AND";
            } else {
                return $return_array['message'] = "Role Id does not match.";
            }
        } else {
            return $return_array['message'] = "Role Id does not match.";
        }

        $sql = "SELECT U.user_id, U.name, U.email, U.mobile, SUM(IF(LD.lead_id > 0,1,0)) as total_leads, ";
//        $sql .= "  IF(CAM.loan_recommended >0 AND CAM.loan_recommended <=25000, 1,IF(CAM.loan_recommended >25000 AND CAM.loan_recommended <=50000,2,IF(CAM.loan_recommended >50000,3,0))) as loan_type_id, ";
        $sql .= " (SELECT IF(LAA.uca_user_status=1,1,0) FROM user_collection_allocation_log LAA WHERE LAA.uca_user_id=U.user_id AND DATE(LAA.uca_created_on)='$current_date' AND LAA.uca_active=1 ORDER BY LAA.uca_id DESC LIMIT 1) as user_active_flag,";
        $sql .= " (SELECT IF(LFR.uca_loan_amount_type_id>0,LFR.uca_loan_amount_type_id,0) FROM user_collection_allocation_log LFR WHERE LFR.uca_user_id=U.user_id AND DATE(LFR.uca_created_on)='$current_date' AND LFR.uca_user_status=1 AND LFR.uca_active=1 ORDER BY LFR.uca_id DESC LIMIT 1) as loan_amount_type,";
        $sql .= " (SELECT IF(LFR.uca_loan_dpd_categories_id>0,LFR.uca_loan_dpd_categories_id,0) FROM user_collection_allocation_log LFR WHERE LFR.uca_user_id=U.user_id AND DATE(LFR.uca_created_on)='$current_date' AND LFR.uca_user_status=1 AND LFR.uca_active=1 ORDER BY LFR.uca_id DESC LIMIT 1) as loan_dpd_categories ";

        $sql .= " FROM users U INNER JOIN user_roles UR ON(U.user_id=UR.user_role_user_id AND UR.user_role_type_id=$role_id) ";
        $sql .= " LEFT JOIN leads LD ON($collection_executive_id LD.lead_status_id IN(14,19)) ";
//        $sql .= " INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) ";
        $sql .= " WHERE U.user_id=UR.user_role_user_id AND UR.user_role_type_id=$role_id ";

        $sql .= " AND U.user_active=1 AND U.user_status_id=1 AND UR.user_role_active=1 ";
        $sql .= " GROUP BY U.user_id ORDER BY total_leads ASC";
        echo $sql;
        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {

            $result_array = $tempDetails->result_array();

            $i = 0;
            foreach ($result_array as $key => $value) {
                $return_array[$value['loan_amount_type']][$key]['user_id'] = $value['user_id'];
                $return_array[$value['loan_amount_type']][$key]['name'] = $value['name'];
                $return_array[$value['loan_amount_type']][$key]['email'] = $value['email'];
                $return_array[$value['loan_amount_type']][$key]['mobile'] = $value['mobile'];
                $return_array[$value['loan_amount_type']][$key]['total_leads'] = $value['total_leads'];
                $return_array[$value['loan_amount_type']][$key]['user_active_flag'] = $value['user_active_flag'];
                $return_array[$value['loan_amount_type']][$key]['loan_amount_type'] = $value['loan_amount_type'];
                $return_array[$value['loan_amount_type']][$key]['loan_dpd_categories'] = $value['loan_dpd_categories'];
            }
        }


        return $return_array;
    }
}

?>
