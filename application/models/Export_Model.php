<?php

class Export_Model extends CI_Model {

    public function getExportPermissionList($export_id = 0, $permission = true) {

        $user_role = $_SESSION['isUserSession']['user_role_id'];
        $user_id = $_SESSION['isUserSession']['user_id'];

        $status = 0;
        $export_list = array();
        $conditions = array(
            "export_permission_user_role_id" => $user_role,
            "export_permission_user_id" => $user_id,
            "export_permission_active" => 1,
            "export_permission_deleted" => 0
        );

        if ($permission == true) {
            $conditions["export_permission_export_id"] = $export_id;
        }

        $this->db->select('export_permission_export_id');
        $this->db->from('user_export_permission');
        $this->db->join('master_export', 'export_permission_export_id = m_export_id', 'INNER');
        $tempDetails = $this->db->where($conditions)->get();

        if ($tempDetails->num_rows() > 0) {
            $export_list = $tempDetails->result_array();
            $status = 1;
        }
        return array("status" => $status, "data" => $export_list);
    }

    public function ExportMaster() {

        $permissions_array = $this->getExportPermissionList('', false);

        $sql = "SELECT * FROM master_export WHERE m_export_active=1 ";

        $user_id = $_SESSION['isUserSession']['user_id'];

        //            if (agent != 'CA' && !in_array($user_id, [222, 217])) {
        //        if (in_array($user_id, [265])) {
        //            $sql .= "AND m_export_id IN(33, 34) ";
        //        } else {
        //            $sql .= "AND m_export_is_live = 1 ";
        //        }
        $sql .= "AND m_export_is_live = 1 ";
        //            }
        $sql .= "ORDER BY m_export_name ASC;";
        $result = $this->db->query($sql)->result_array();

        $export_array = array();

        foreach ($result as $value) {
            $export_array[$value['m_export_id']]['m_export_id'] = $value['m_export_id'];
            $export_array[$value['m_export_id']]['m_export_name'] = $value['m_export_name'];
            $export_array[$value['m_export_id']]['m_export_heading'] = $value['m_export_heading'];
            $export_array[$value['m_export_id']]['permission'] = 0;
        }

        foreach ($permissions_array['data'] as $value) {
            if (isset($export_array[$value['export_permission_export_id']])) {
                $export_array[$value['export_permission_export_id']]['permission'] = 1;
            }
        }

        return $export_array;
    }

    public function ReportName($fname) {
        if (!empty($fname)) {
            $q = $this->db->select('m_export_name')
                ->from('master_export')
                ->where('m_export_id', $fname)
                ->get();
            $result = $q->result_array();
            //        print_r($this->db->last_query());
            //         exit;
            return $result;
        }
    }

    public function ExportLeadTotal($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));
            $result = $this->db->select('LD.lead_id, LMS.m_state_name as lead_state_name, LD.lead_journey_type_id, CAM.created_at, CAM.disbursal_date, LD.lead_final_disbursed_date, LD.loan_no, LD.source, LD.utm_source, LD.utm_medium, LD.utm_campaign, LD.utm_term, tbl_rejection_master.reason as rejected_reason, L.agrementResponseDate, LD.customer_id, CONCAT_WS(" ", LC.first_name, LC.middle_name, LC.sur_name) as first_name, LD.pancard, LD.mobile, LD.email, LD.lead_is_mobile_verified, LD.user_type, LD.monthly_salary_amount, LD.loan_amount, CAM.loan_recommended,  CAM.admin_fee, LD.tenure, CAM.roi, CAM.repayment_amount, CAM.repayment_date, LD.cibil, LD.obligations, LD.source, LC.dob, LC.customer_docs_available, LC.gender, MS.m_state_name, MC.m_city_name, LD.status, LD.created_on, CAM.disbursal_date, U1.name screenname, LD.lead_screener_assign_datetime, U2.name sanctionby, LD.lead_credit_assign_datetime, LC.updated_at, U3.name sanctionapproveby, LD.lead_credit_approve_datetime, LD.lead_final_disbursed_date, U4.name finaldisbursed, LD.utm_campaign, LD.lead_credithead_assign_datetime, LD.lead_screener_recommend_datetime, LD.lead_credit_recommend_datetime, U5.name disburseverifiedby, LD.lead_disbursal_assign_datetime, LD.lead_disbursal_recommend_datetime, LD.lead_disbursal_approve_datetime, Branch.m_branch_name branch, master_religion.religion_name,U6.name as lead_rejected_assign_user_name,LD.lead_rejected_assign_datetime as lead_rejected_assign_datetime,LD.lead_rejected_assign_counter as lead_rejected_assign_counter,LC.customer_docs_available')
                ->from('leads LD')
                ->join('lead_customer LC', 'LD.lead_id = LC.customer_lead_id', 'INNER')
                ->join('loan L', 'LD.lead_id = L.lead_id', 'LEFT')
                ->join('customer_employment CE', 'LD.lead_id = CE.lead_id', 'LEFT')
                ->join('master_state MS', 'LC.aa_current_state_id = MS.m_state_id', 'LEFT')
                ->join('master_state LMS', 'LD.state_id = MS.m_state_id', 'LEFT')
                ->join('master_city MC', 'LD.city_id = MC.m_city_id', 'LEFT')
                ->join('credit_analysis_memo CAM', 'LD.lead_id = CAM.lead_id', 'LEFT')
                ->join('users U1', 'LD.lead_screener_assign_user_id = U1.user_id', 'LEFT')
                ->join('users U2', 'LD.lead_credit_assign_user_id = U2.user_id', 'LEFT')
                ->join('users U3', 'LD.lead_credit_approve_user_id = U3.user_id', 'LEFT')
                ->join('users U4', 'LD.lead_disbursal_approve_user_id = U4.user_id', 'LEFT')
                ->join('users U5', 'LD.lead_disbursal_assign_user_id = U5.user_id', 'LEFT')
                ->join('users U6', 'LD.lead_rejected_assign_user_id = U6.user_id', 'LEFT')
                ->join('master_branch Branch', 'LD.lead_branch_id = Branch.m_branch_id', 'LEFT')
                ->join('tbl_rejection_master tbl_rejection_master', 'LD.lead_rejected_reason_id = tbl_rejection_master.id', 'LEFT')
                ->join('master_religion', 'LC.customer_religion_id=master_religion.religion_id', 'LEFT')
                ->where("LD.lead_active = 1  AND LD.lead_entry_date >= '$fromDate' AND LD.lead_entry_date<= '$toDate'")
                ->group_by('LD.lead_id')
                ->order_by('LD.lead_id', 'DESC')
                ->get();

            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportLeadDuplicate($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('LD.lead_id, LD.customer_id, LD.first_name, LD.purpose, LD.loan_amount, LD.user_type, LD.tenure, LD.cibil, LD.obligations, LD.source, LD.status, LD.utm_source, LD.lead_entry_date, CE.monthly_income, LC.dob, LC.gender, LC.current_house, LC.current_locality, LC.current_landmark, MS.m_state_name, MC.m_city_name,LC.current_city, LC.cr_residence_pincode, LC.aadhar_no, LC.current_residence_type,  LC.updated_at, CAM.roi, CAM.repayment_amount, U1.name screener, LD.lead_screener_assign_datetime')
                ->from('leads LD')
                ->join('customer_employment CE', 'LD.lead_id = CE.lead_id', 'LEFT')
                ->join('credit_analysis_memo CAM', 'LD.lead_id = CAM.lead_id', 'LEFT')
                ->join('lead_customer LC', 'LD.lead_id = LC.customer_lead_id', 'LEFT')
                ->join('master_state MS', 'LD.state_id = MS.m_state_id', 'LEFT')
                ->join('master_city MC', 'LD.city_id = MC.m_city_id', 'LEFT')
                ->join('users U1', 'LD.lead_screener_assign_user_id = U1.user_id', 'LEFT')
                ->where("LD.lead_active=1 AND LD.lead_status_id = 7 AND LD.lead_entry_date IS NOT NULL AND LD.lead_entry_date IS NOT NULL AND LD.lead_entry_date >= '$fromDate' AND LD.lead_entry_date<= '$toDate'")
                ->get();

            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportPartialLeaddataModel($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('LD.lead_id,  LD.mobile, LD.source, LD.lead_entry_date, LD.lead_is_mobile_verified')
                ->from('leads LD')
                ->where("LD.lead_active=1 AND LD.first_name IS NULL AND LD.lead_entry_date IS NOT NULL  AND LD.lead_entry_date >= '$fromDate' AND LD.lead_entry_date<= '$toDate'")
                ->get();

            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportDisbursed($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $branch = implode(",", $_SESSION['isUserSession']['user_branch']);

            $qry = 'SELECT L.lead_id, master_branch.m_branch_name, L.customer_id, CAM.cam_risk_profile, CONCAT_WS(" ", LC.first_name, LC.middle_name, LC.sur_name) as full_name, CAM.loan_recommended, LD.user_type, LD.pancard, L.loan_no, LC.mobile, LC.alternate_mobile, LC.email, CAM.admin_fee, CAM.total_admin_fee, CAM.roi, CAM.tenure, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, L.mode_of_payment, LC.dob, LD.cibil, CB.account, CB.bank_name, CB.ifsc_code, L.disburse_refrence_no, L.company_account_no, L.status, U2.name sanctionby, LD.lead_credit_assign_datetime, LD.created_on, LD.lead_final_disbursed_date, U1.name screenby, U3.name loan_disburse_by, U4.name sanction_approve_by, MC.m_city_name, LD.source, LD.lead_disbursal_approve_datetime, U5.name loan_initiat_by, LD.lead_disbursal_assign_datetime, LD.lead_disbursal_recommend_datetime, LD.utm_source, LD.utm_campaign ';

            $qry .= 'FROM loan L LEFT JOIN leads LD ON L.lead_id = LD.lead_id LEFT JOIN lead_customer LC ON L.lead_id = LC.customer_lead_id LEFT JOIN customer_banking CB ON(L.lead_id = CB.lead_id AND CB.account_status_id=1 AND CB.customer_banking_active=1) LEFT JOIN customer_employment CE ON LD.lead_id = CE.lead_id LEFT JOIN credit_analysis_memo CAM ON L.lead_id = CAM.lead_id LEFT JOIN master_city MC ON LC.city_id = MC.m_city_id LEFT JOIN master_branch ON LD.lead_branch_id = master_branch.m_branch_id LEFT JOIN users U1 ON LD.lead_screener_assign_user_id = U1.user_id LEFT JOIN users U2 ON LD.lead_credit_assign_user_id = U2.user_id LEFT JOIN users U3 ON LD.lead_disbursal_approve_user_id = U3.user_id LEFT JOIN users U4 ON LD.lead_credithead_assign_user_id = U4.user_id LEFT JOIN users U5 ON LD.lead_disbursal_assign_user_id = U5.user_id ';

            if (!empty($branch)) {

                $qry .= "WHERE L.loan_active = 1 AND L.loan_status_id = 14 AND LD.lead_branch_id IN('$branch') AND CAM.disbursal_date IS NOT NULL AND LD.lead_final_disbursed_date IS NOT NULL AND LD.lead_final_disbursed_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate' AND CB.account_status_id=1 AND CB.customer_banking_active=1";
            } else {

                $qry .= "WHERE L.loan_active = 1 AND L.loan_status_id = 14 AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date IS NOT NULL AND LD.lead_final_disbursed_date >= '$fromDate' AND LD.lead_final_disbursed_date <= '$toDate'";
            }

            $result = $this->db->query($qry);

            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportDisbursed_bk($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $branch = implode(",", $_SESSION['isUserSession']['user_branch']);

            $qry = 'SELECT L.lead_id, master_branch.m_branch_name, L.customer_id, CAM.cam_risk_profile, CONCAT_WS(" ", LC.first_name, LC.middle_name, LC.sur_name) as full_name, CAM.loan_recommended, LD.user_type, LD.pancard, L.loan_no, LC.mobile, LC.email, CAM.admin_fee, CAM.roi, CAM.tenure, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, L.mode_of_payment, LC.dob, LD.cibil, CB.account, CB.bank_name, CB.ifsc_code, L.disburse_refrence_no, L.company_account_no, L.status, U2.name sanctionby, LD.lead_credit_assign_datetime, LD.created_on, LD.lead_final_disbursed_date, U1.name screenby, U3.name loan_disburse_by, U4.name sanction_approve_by, MC.m_city_name, LD.source, LD.lead_disbursal_approve_datetime, U5.name loan_initiat_by, LD.lead_disbursal_assign_datetime, LD.lead_disbursal_recommend_datetime ';

            $qry .= 'FROM loan L LEFT JOIN leads LD ON L.lead_id = LD.lead_id LEFT JOIN lead_customer LC ON L.lead_id = LC.customer_lead_id LEFT JOIN customer_banking CB ON(L.lead_id = CB.lead_id AND CB.account_status_id=1 AND CB.customer_banking_active=1) LEFT JOIN customer_employment CE ON LD.lead_id = CE.lead_id LEFT JOIN credit_analysis_memo CAM ON L.lead_id = CAM.lead_id LEFT JOIN master_city MC ON LC.city_id = MC.m_city_id LEFT JOIN master_branch ON LD.lead_branch_id = master_branch.m_branch_id LEFT JOIN users U1 ON LD.lead_screener_assign_user_id = U1.user_id LEFT JOIN users U2 ON LD.lead_credit_assign_user_id = U2.user_id LEFT JOIN users U3 ON LD.lead_disbursal_approve_user_id = U3.user_id LEFT JOIN users U4 ON LD.lead_credithead_assign_user_id = U4.user_id LEFT JOIN users U5 ON LD.lead_disbursal_assign_user_id = U5.user_id ';

            if (!empty($branch)) {

                $qry .= "WHERE L.loan_active = 1 AND L.loan_status_id = 14 AND LD.lead_branch_id IN('$branch') AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate' AND CB.account_status_id=1 AND CB.customer_banking_active=1";
            } else {

                $qry .= "WHERE L.loan_active = 1 AND L.loan_status_id = 14 AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate'";
            }

            $result = $this->db->query($qry);

            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportDisbursedPending($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('L.lead_id, LD.source, LD.utm_source, L.customer_id, CONCAT_WS(" ",LC.first_name, LC.middle_name, LC.sur_name) as full_name, CAM.loan_recommended, LD.user_type, LD.pancard, L.loan_no, CAM.admin_fee, CAM.roi, CAM.tenure, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, CB.account, CB.bank_name, CB.ifsc_code, L.status, LD.lead_entry_date, U2.name sname, LD.lead_credit_assign_datetime, L.created_on, LD.lead_final_disbursed_date, U1.name screenby, U3.name loan_dname, U4.name sanctio_approve_by')
                ->from('loan L')
                ->join('leads LD', 'L.lead_id = LD.lead_id', 'LEFT')
                ->join('lead_customer LC', 'L.lead_id = LC.customer_lead_id', 'LEFT')
                ->join('customer_banking CB', 'L.lead_id = CB.lead_id', 'LEFT')
                ->join('credit_analysis_memo CAM', 'L.lead_id = CAM.lead_id', 'LEFT')
                ->join('users U1', 'LD.lead_screener_assign_user_id = U1.user_id', 'LEFT')
                ->join('users U2', 'LD.lead_credit_assign_user_id = U2.user_id', 'LEFT')
                ->join('users U3', 'L.updated_by = U3.user_id', 'LEFT')
                ->join('users U4', 'LD.lead_credithead_assign_user_id = U4.user_id', 'LEFT')
                ->where("L.loan_active = 1 AND CB.account_status_id=1 AND CB.customer_banking_active=1 AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate' AND LD.lead_status_id = 13")
                ->get();
            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportLeadRejected($fromDate, $toDate) {

        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $sql = "SELECT DISTINCT LD.lead_id, CAM.loan_recommended, L.loan_no, LD.first_name, LD.pancard, LD.mobile, LD.email, LD.user_type, CE.monthly_income, LD.loan_amount, LD.cibil, LD.obligations, LD.source, LD.utm_source, LD.utm_campaign, LC.dob, LC.gender, MS.m_state_name, MC.m_city_name, LD.status, LD.created_on lead_entry_date, LD.lead_screener_assign_datetime, U2.name cname, LD.lead_credit_assign_datetime, LC.updated_at, U3.name caname, LD.lead_credit_approve_datetime, LD.lead_final_disbursed_date, U4.name dname, L.user_id, LD.lead_screener_assign_datetime, RM.reason remarks, LD.lead_rejected_reason_id, U5.name rejectedby, LD.lead_rejected_datetime rejecteddate ";

            $sql .= " FROM leads LD LEFT JOIN lead_customer LC ON(LD.lead_id = LC.customer_lead_id) LEFT JOIN customer_employment CE ON(LD.lead_id = CE.lead_id) LEFT JOIN master_state MS ON(LD.state_id = MS.m_state_id) LEFT JOIN master_city MC ON(LD.city_id = MC.m_city_id) LEFT JOIN credit_analysis_memo CAM ON(LD.lead_id = CAM.lead_id) LEFT JOIN loan L ON(LD.lead_id = L.lead_id) LEFT JOIN tbl_rejection_master RM ON(RM.id = LD.lead_rejected_reason_id) LEFT JOIN users U5 ON LD.lead_rejected_user_id = U5.user_id LEFT JOIN users U1 ON(LD.lead_screener_assign_user_id = U1.user_id) LEFT JOIN users U2 ON(LD.lead_credit_assign_user_id = U2.user_id) LEFT JOIN users U3 ON(LD.lead_credit_approve_user_id = U3.user_id) LEFT JOIN users U4 ON(LD.lead_disbursal_approve_user_id = U4.user_id) LEFT JOIN lead_eligibility_rules_result LER ON(LD.lead_id=LER.lerr_lead_id) ";

            $sql .= " WHERE LD.lead_status_id IN(9) AND LD.lead_entry_date IS NOT NULL AND LD.lead_entry_date IS NOT NULL AND LD.lead_entry_date >= '$fromDate' AND LD.lead_entry_date<= '$toDate'";

            $result = $this->db->query($sql);

            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportTotalRecovery($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('C.lead_id, CONCAT_WS(" ",LC.first_name, LC.middle_name, LC.sur_name) as full_name, CAM.loan_recommended, C.loan_no, CAM.roi, CAM.tenure, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, C.received_amount, C.date_of_recived, L.status leadstatus, C.payment_mode, C.discount,C.refund, U1.name rname, C.collection_executive_payment_created_on, U2.name closure_name, C.closure_payment_updated_on, C.company_account_no, C.refrence_no, C.remarks, C.collection_type, C.noc,  MS.m_state_name, MC.m_city_name, master_branch.m_branch_name, MSC.status_name status, C.closure_remarks, L.source')
                ->from('collection C')
                ->join('lead_customer LC', 'C.lead_id = LC.customer_lead_id', 'LEFT')
                ->join('leads L', 'C.lead_id = L.lead_id', 'LEFT')
                ->join('credit_analysis_memo CAM', 'C.lead_id = CAM.lead_id', 'LEFT')
                ->join('master_status MSC', 'C.repayment_type = MSC.status_id', 'LEFT')
                ->join('master_state MS', 'L.state_id = MS.m_state_id', 'LEFT')
                ->join('master_city MC', 'L.city_id = MC.m_city_id', 'LEFT')
                ->join('master_branch', 'MC.m_city_branch_id = master_branch.m_branch_id', 'LEFT')
                ->join('users U1', 'C.collection_executive_user_id = U1.user_id', 'LEFT')
                ->join('users U2', 'C.closure_user_id = U2.user_id', 'LEFT')
                ->where("C.payment_verification = 1 AND C.collection_active = 1 AND C.date_of_recived IS NOT NULL AND C.date_of_recived IS NOT NULL AND DATE(C.closure_payment_updated_on) >= '$fromDate' AND DATE(C.closure_payment_updated_on) <= '$toDate'")
                ->get();

            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportBOBTotalRecovery($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('C.lead_id, CONCAT_WS(" ",LC.first_name, LC.middle_name, LC.sur_name) as full_name, CAM.loan_recommended, C.loan_no, CAM.roi, CAM.tenure, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, C.received_amount, C.date_of_recived, L.status leadstatus, C.payment_mode, C.discount, U1.name rname, C.collection_executive_payment_created_on, U2.name closure_name, C.closure_payment_updated_on, C.company_account_no, C.refrence_no, C.remarks, C.collection_type, C.noc,  MS.m_state_name, MC.m_city_name, master_branch.m_branch_name, MSC.status_name status, C.closure_remarks, L.source')
                ->from('collection C')
                ->join('lead_customer LC', 'C.lead_id = LC.customer_lead_id', 'LEFT')
                ->join('leads L', 'C.lead_id = L.lead_id', 'LEFT')
                ->join('loan', 'loan.lead_id = L.lead_id', 'INNER')
                ->join('credit_analysis_memo CAM', 'C.lead_id = CAM.lead_id', 'LEFT')
                ->join('master_status MSC', 'C.repayment_type = MSC.status_id', 'LEFT')
                ->join('master_state MS', 'LC.state_id = MS.m_state_id', 'LEFT')
                ->join('master_city MC', 'LC.city_id = MC.m_city_id', 'LEFT')
                ->join('master_branch', 'MC.m_city_branch_id = master_branch.m_branch_id', 'LEFT')
                ->join('users U1', 'C.collection_executive_user_id = U1.user_id', 'LEFT')
                ->join('users U2', 'C.closure_user_id = U2.user_id', 'LEFT')
                ->where("C.payment_verification = 1 AND C.collection_active = 1 AND C.date_of_recived IS NOT NULL AND C.date_of_recived IS NOT NULL AND C.date_of_recived >= '$fromDate' AND C.date_of_recived <= '$toDate'")
                ->get();

            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportLoanClosed($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('C.lead_id, LO.loan_principle_received_amount, LO.loan_interest_received_amount, LO.loan_penalty_received_amount, LO.loan_total_received_amount, LO.loan_total_discount_amount,L.pancard, L.user_type, CONCAT_WS(" ",LC.first_name, LC.middle_name, LC.sur_name) as full_name, CAM.loan_recommended, C.loan_no, CAM.roi, CAM.tenure, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, C.date_of_recived, C.payment_mode, C.discount, U1.name rname, C.collection_executive_payment_created_on, U2.name closure_name, C.closure_payment_updated_on, C.company_account_no, C.refrence_no, C.remarks, IF(LO.loan_noc_letter_sent_status=1,"YES","NO") noc,  MS.m_state_name, MC.m_city_name, MSC.status_name status, U3.name noc_sent_by, LO.loan_noc_letter_sent_datetime')
                ->from('collection C')
                ->join('lead_customer LC', 'C.lead_id = LC.customer_lead_id', 'LEFT')
                ->join('leads L', 'C.lead_id = L.lead_id', 'LEFT')
                ->join('loan LO', 'LO.lead_id = C.lead_id', 'LEFT')
                ->join('credit_analysis_memo CAM', 'C.lead_id = CAM.lead_id', 'LEFT')
                ->join('master_status MSC', 'C.repayment_type = MSC.status_id', 'LEFT')
                ->join('master_state MS', 'LC.state_id = MS.m_state_id', 'LEFT')
                ->join('master_city MC', 'LC.city_id = MC.m_city_id', 'LEFT')
                ->join('users U1', 'C.collection_executive_user_id = U1.user_id', 'LEFT')
                ->join('users U2', 'C.closure_user_id = U2.user_id', 'LEFT')
                ->join('users U3', 'LO.loan_noc_letter_sent_user_id = U3.user_id', 'LEFT')
                ->where("C.payment_verification = 1 AND C.collection_active = 1 AND (C.repayment_type = 16 OR C.repayment_type = 17 OR C.repayment_type = 18) AND C.date_of_recived IS NOT NULL AND C.date_of_recived IS NOT NULL AND DATE(C.closure_payment_updated_on) >= '$fromDate' AND DATE(C.closure_payment_updated_on) <= '$toDate'")
                ->get();
            //  $sql = $this->db->get_compiled_select();
            // echo $sql;die;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    // public function ExportACReport($fromDate, $toDate) {
    //     if (!empty($fromDate) && !empty($toDate)) {
    //         $fromDate = date('Y-m-d', strtotime($fromDate));
    //         $toDate = date('Y-m-d', strtotime($toDate));

    //         //                $result = $this->db->select('L.lead_id, CAM.total_admin_fee, CAM.adminFeeWithGST, L.recommended_amount, L.customer_id, CONCAT_WS(" ",LC.first_name, LC.middle_name, LC.sur_name) as full_name, CAM.loan_recommended, LD.user_type, LD.pancard, L.loan_no, LC.mobile, LC.email, CAM.admin_fee, CAM.roi, CAM.tenure, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, L.mode_of_payment, CB.account, CB.bank_name, CB.ifsc_code, L.disburse_refrence_no, L.company_account_no, L.status, LD.lead_entry_date, U2.name sname, LD.lead_credit_assign_datetime, L.created_on, LD.lead_final_disbursed_date, U3.name loan_dname, U4.name sanctio_approve_by, MC.m_city_name current_city, CE.emp_house, MS.m_state_name, IF(LD.state_id=10,0,(CAM.admin_fee * 0.152542372881356)) IGST, IF(LD.state_id<>10,0,(CAM.admin_fee * 0.076271186440678)) CGST, IF(LD.state_id<>10,0,(CAM.admin_fee * 0.076271186440678)) SGST, (CAM.admin_fee * 0.847457627) processingfee')
    //         //                        ->from('loan L')
    //         //                        ->join('leads LD', 'L.lead_id = LD.lead_id', 'LEFT')
    //         //                        ->join('lead_customer LC', 'L.lead_id = LC.customer_lead_id', 'LEFT')
    //         //                        ->join('customer_banking CB', 'L.lead_id = CB.lead_id', 'LEFT')
    //         //                        ->join('customer_employment CE', 'LD.lead_id = CE.lead_id', 'LEFT')
    //         //                        ->join('credit_analysis_memo CAM', 'L.lead_id = CAM.lead_id', 'LEFT')
    //         //                        ->join('master_city MC', 'LD.city_id = MC.m_city_id', 'LEFT')
    //         //                        ->join('master_state MS', 'LD.state_id = MS.m_state_id', 'LEFT')
    //         //                        ->join('users U2', 'LD.lead_credit_assign_user_id = U2.user_id', 'LEFT')
    //         //                        ->join('users U3', 'L.updated_by = U3.user_id', 'LEFT')
    //         //                        ->join('users U4', 'LD.lead_credithead_assign_user_id = U4.user_id', 'LEFT')
    //         //                        ->where("L.loan_active = 1 AND (L.status = 'DISBURSED'  OR L.status = 'Pre Disburse') AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate' AND CB.account_status_id=1 AND CB.customer_banking_active=1")
    //         //                        ->distinct()
    //         //                        ->get();

    //         $qry = 'SELECT DISTINCT L.lead_id, CAM.total_admin_fee, CAM.adminFeeWithGST, L.recommended_amount, L.customer_id, CONCAT_WS(" ", LC.first_name, LC.middle_name, LC.sur_name) as full_name, CAM.loan_recommended, LD.user_type, LD.pancard, L.loan_no, LC.mobile, LC.email, CAM.admin_fee, CAM.roi, CAM.tenure, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, L.mode_of_payment, CB.account, ';
    //         $qry .= " CB.bank_name, CB.ifsc_code, L.disburse_refrence_no, L.company_account_no, L.status, LD.lead_entry_date, U2.name sname, LD.lead_credit_assign_datetime, L.created_on, LD.lead_final_disbursed_date, U3.name loan_dname, U4.name sanctio_approve_by, MC.m_city_name current_city, CE.emp_house, MS.m_state_name, IF(LD.state_id=10, 0, (CAM.admin_fee * 0.152542372881356)) IGST, IF(LD.state_id<>10, 0, (CAM.admin_fee * 0.076271186440678)) CGST, IF(LD.state_id<>10, 0, (CAM.admin_fee * 0.076271186440678)) SGST, (CAM.admin_fee * 0.847457627) processingfee ";
    //         $qry .= "FROM loan L LEFT JOIN leads LD ON(L.lead_id=LD.lead_id) LEFT JOIN lead_customer LC ON(L.lead_id=LC.customer_lead_id) LEFT JOIN customer_banking CB ON(L.lead_id=CB.lead_id AND CB.account_status_id=1 AND CB.customer_banking_active=1) LEFT JOIN customer_employment CE ON(LD.lead_id=CE.lead_id) LEFT JOIN credit_analysis_memo CAM ON(L.lead_id=CAM.lead_id) LEFT JOIN master_city MC ON(LD.city_id=MC.m_city_id) LEFT JOIN master_state MS ON(LD.state_id=MS.m_state_id) LEFT JOIN users U2 ON(LD.lead_credit_assign_user_id=U2.user_id) LEFT JOIN users U3 ON(L.updated_by=U3.user_id) LEFT JOIN users U4 ON(LD.lead_credithead_assign_user_id=U4.user_id) ";
    //         $qry .= "WHERE L.loan_active=1 AND LD.lead_status_id IN(14,16,17,18,19) AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate'";

    //         $result = $this->db->query($qry);

    //         //                           print_r($this->db->last_query());
    //         //                 exit;
    //         return $result;
    //     } else {
    //         return redirect(base_url('exportData/'), 'refresh');
    //     }
    // }

    public function ExportACReport($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $qry = 'SELECT DISTINCT
            L.lead_id,
            CAM.total_admin_fee,
            CAM.adminFeeWithGST,
            L.recommended_amount,
            L.customer_id,
            CONCAT_WS(" ", LC.first_name, LC.middle_name, LC.sur_name) AS full_name,
            CAM.loan_recommended,
            LD.user_type,
            LD.pancard,
            L.loan_no,
            LC.mobile,
            LC.email,
            CAM.admin_fee,
            CAM.roi,
            CAM.tenure,
            CAM.repayment_amount,
            CAM.disbursal_date,
            CAM.repayment_date,
            L.mode_of_payment,
            CB.account,
            CB.bank_name,
            CB.ifsc_code,
            L.disburse_refrence_no,
            L.company_account_no,
            L.status,
            LD.lead_entry_date,
            U2.name AS sname,
            LD.lead_credit_assign_datetime,
            L.created_on,
            LD.lead_final_disbursed_date,
            U3.name AS loan_dname,
            U4.name AS sanctio_approve_by,
            MC.m_city_name AS current_city,
            CE.emp_house,
            MS.m_state_name,
            IF(LC.state_id = 10, 0, (CAM.admin_fee * 0.152542372881356)) AS IGST,
            IF(LC.state_id <> 10, 0, (CAM.admin_fee * 0.076271186440678)) AS CGST,
            IF(LC.state_id <> 10, 0, (CAM.admin_fee * 0.076271186440678)) AS SGST,
            (CAM.admin_fee * 0.847457627) AS processingfee
        FROM
            loan L
        LEFT JOIN
            leads LD ON L.lead_id = LD.lead_id
        LEFT JOIN
            lead_customer LC ON L.lead_id = LC.customer_lead_id
        LEFT JOIN
            customer_banking CB ON L.lead_id = CB.lead_id
            AND CB.account_status_id = 1
            AND CB.customer_banking_active = 1
        LEFT JOIN
            customer_employment CE ON LD.lead_id = CE.lead_id
        LEFT JOIN
            credit_analysis_memo CAM ON L.lead_id = CAM.lead_id
        LEFT JOIN
            master_city MC ON LC.city_id = MC.m_city_id
        LEFT JOIN
            master_state MS ON LC.state_id = MS.m_state_id
        LEFT JOIN
            users U2 ON LD.lead_credit_assign_user_id = U2.user_id
        LEFT JOIN
            users U3 ON L.updated_by = U3.user_id
        LEFT JOIN
            users U4 ON LD.lead_credithead_assign_user_id = U4.user_id
        WHERE
            L.loan_active = 1
            AND LD.lead_status_id IN (14, 16, 17, 18, 19)
            AND CAM.disbursal_date >= "$fromDate"
            AND CAM.disbursal_date <= "$toDate";';

            $result = $this->db->query($qry);

            // print_r($this->db->last_query());
            // die;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportSanction($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('L.lead_id, CONCAT_WS(" ", LC.first_name, LC.middle_name, LC.sur_name) as full_name, CAM.loan_recommended, LD.user_type, LD.pancard, L.loan_no, CAM.admin_fee, CAM.roi, CAM.tenure, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, LD.cibil, CB.account, CB.bank_name, CB.ifsc_code, L.status, LD.lead_entry_date, U2.name sname, LD.lead_credit_assign_datetime, L.created_on, LD.lead_final_disbursed_date, U1.name screenby, U3.name loan_dname, MC.m_city_name current_city')
                ->from('loan L')
                ->join('leads LD', 'L.lead_id = LD.lead_id', 'LEFT')
                ->join('lead_customer LC', 'L.lead_id = LC.customer_lead_id', 'LEFT')
                ->join('customer_banking CB', 'L.lead_id = CB.lead_id', 'LEFT')
                ->join('credit_analysis_memo CAM', 'L.lead_id = CAM.lead_id', 'LEFT')
                ->join('master_city MC', 'LC.current_city = MC.m_city_id', 'LEFT')
                ->join('users U1', 'LD.lead_screener_assign_user_id = U1.user_id', 'LEFT')
                ->join('users U2', 'LD.lead_credit_assign_user_id = U2.user_id', 'LEFT')
                ->join('users U3', 'L.updated_by = U3.user_id', 'LEFT')
                ->where("L.loan_active = 1 AND LD.lead_status_id = 12 AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate' AND CB.account_status_id=1 AND CB.customer_banking_active=1")
                ->distinct()
                ->get();

            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportApprovedSanction($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $qry = "SELECT DISTINCT L.lead_id, CONCAT_WS(' ', LC.first_name, LC.middle_name, LC.sur_name) as full_name, CAM.loan_recommended, LD.user_type, LD.pancard, L.loan_no, CAM.admin_fee, CAM.roi, CAM.tenure, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, LD.cibil, CB.account, CB.bank_name, CB.ifsc_code, L.status, LD.lead_entry_date, U2.name sname, LD.lead_credit_assign_datetime, L.created_on, LD.lead_final_disbursed_date, U1.name screenby, U3.name loan_dname, MC.m_city_name current_city, LD.status, LD.utm_source, LD.lead_credit_approve_datetime ";

            $qry .= "FROM loan L LEFT JOIN leads LD ON L.lead_id = LD.lead_id LEFT JOIN lead_customer LC ON L.lead_id = LC.customer_lead_id LEFT JOIN customer_banking CB ON(L.lead_id = CB.lead_id AND CB.account_status_id=1) LEFT JOIN credit_analysis_memo CAM ON L.lead_id = CAM.lead_id LEFT JOIN master_city MC ON LC.current_city = MC.m_city_id LEFT JOIN users U1 ON LD.lead_screener_assign_user_id = U1.user_id LEFT JOIN users U2 ON LD.lead_credit_assign_user_id = U2.user_id LEFT JOIN users U3 ON L.updated_by = U3.user_id ";

            $qry .= "WHERE L.loan_active = 1 AND LD.lead_credit_approve_user_id > 0 AND DATE(LD.lead_credit_approve_datetime) >= '$fromDate' AND DATE(LD.lead_credit_approve_datetime) <= '$toDate' ";

            $result = $this->db->query($qry);

            // print_r($this->db->last_query());
            // exit;

            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportCollectionReport($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('L.lead_id, L.customer_id, MDS.data_source_name as lead_source, CAM.cam_risk_profile, CONCAT_WS(" ", LC.first_name, LC.middle_name, LC.sur_name) as full_name, CAM.loan_recommended, LD.user_type, LD.pancard, L.loan_no, LC.gender, LC.mobile, LC.alternate_mobile, LC.email, LC.alternate_email, CAM.admin_fee, CAM.roi, CAM.tenure, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, L.mode_of_payment, LC.dob, LD.cibil, CB.account, CB.bank_name, CB.ifsc_code, L.disburse_refrence_no, L.company_account_no, L.status, LD.lead_entry_date, U2.name sname, LD.lead_credit_assign_datetime, L.created_on, LD.created_on lead_created_on, LD.lead_final_disbursed_date, U1.name screenby, U3.name loan_dname, U4.name sanctio_approve_by, CONCAT_WS(" ", LC.current_house, " ", LC.current_locality) Address, CONCAT(LC.aa_current_house, LC.aa_current_locality) aadharaddress, MC.m_city_name, CE.emp_house, LC.cr_residence_pincode pincode, LD.utm_source, LD.lead_disbursal_approve_datetime, CE.employer_name, CAM.salary_credit1_date, CAM.salary_credit1_amount, LC.current_residence_type, CE.emp_designation, CE.emp_department, CE.emp_employer_type, CE.emp_website')
                ->from('loan L')
                ->join('leads LD', 'L.lead_id = LD.lead_id', 'LEFT')
                ->join('lead_customer LC', 'L.lead_id = LC.customer_lead_id', 'LEFT')
                ->join('customer_banking CB', 'L.lead_id = CB.lead_id AND CB.account_status_id=1 AND CB.customer_banking_active=1', 'LEFT')
                ->join('customer_employment CE', 'LD.lead_id = CE.lead_id', 'LEFT')
                ->join('credit_analysis_memo CAM', 'L.lead_id = CAM.lead_id', 'LEFT')
                ->join('master_city MC', 'LC.city_id = MC.m_city_id', 'LEFT')
                ->join('master_data_source MDS', 'LD.lead_data_source_id = MDS.data_source_id', 'LEFT')
                ->join('users U1', 'LD.lead_screener_assign_user_id = U1.user_id', 'LEFT')
                ->join('users U2', 'LD.lead_credit_assign_user_id = U2.user_id', 'LEFT')
                ->join('users U3', 'L.updated_by = U3.user_id', 'LEFT')
                ->join('users U4', 'LD.lead_credithead_assign_user_id = U4.user_id', 'LEFT')
                ->where("L.loan_active = 1 AND (L.status = 'DISBURSED'  OR L.status = 'Pre Disburse') AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate'")
                ->distinct()
                ->get();

            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportCibilReport($fromDate, $toDate) {

        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('L.lead_id, CONCAT_WS(" ", LC.first_name, LC.middle_name, LC.sur_name) as full_name, LC.email, LC.alternate_email, L.loan_no, CAM.disbursal_date, CAM.loan_recommended, CAM.repayment_amount,  LC.pancard, LC.dob, LC.gender, LC.mobile, SUBSTRING(CONCAT_WS(" ", LC.current_house, " ", LC.current_locality), 1, 245) Address, LC.current_residence_type, LC.aadhar_no, ST.cibil_state_code state_id, LC.alternate_mobile, CAM.repayment_date, CONCAT_WS(" ",LC.aa_current_house, LC.aa_current_locality) aadharaddress, LC.cr_residence_pincode pincode, CAM.roi')
                ->from('loan L')
                ->join('lead_customer LC', 'L.lead_id = LC.customer_lead_id', 'INNER')
                ->join('credit_analysis_memo CAM', 'L.lead_id = CAM.lead_id', 'INNER')
                ->join('master_state ST', 'LC.state_id = ST.m_state_id', 'INNER')
                ->where("CAM.cam_active = 1 AND L.loan_bureau_report_flag != 2 AND CAM.cam_deleted = 0 AND L.loan_active = 1 AND L.loan_deleted = 0 AND L.loan_status_id = 14 AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date <= '$toDate'")
                ->get();
            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportDisbursedSendback($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('L.lead_id, L.customer_id, CONCAT_WS(" ",LC.first_name, LC.middle_name, LC.sur_name) as full_name, CAM.loan_recommended, LD.user_type, LD.pancard, L.loan_no, CAM.admin_fee, CAM.roi, CAM.tenure, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, CB.account, CB.bank_name, CB.ifsc_code, "DISBURSAL-SEND-BACK" as status, LD.lead_entry_date, U2.name sanctionby, LD.lead_credit_assign_datetime, L.created_on, LD.lead_final_disbursed_date, U1.name screenby, U3.name loan_initial_by, U4.name sanctio_approve_by, LD.lead_disbursal_assign_datetime, LD.lead_disbursal_recommend_datetime')
                ->from('loan L')
                ->join('leads LD', 'L.lead_id = LD.lead_id', 'LEFT')
                ->join('lead_customer LC', 'L.lead_id = LC.customer_lead_id', 'LEFT')
                ->join('customer_banking CB', 'L.lead_id = CB.lead_id', 'LEFT')
                ->join('credit_analysis_memo CAM', 'L.lead_id = CAM.lead_id', 'LEFT')
                ->join('users U1', 'LD.lead_screener_assign_user_id = U1.user_id', 'LEFT')
                ->join('users U2', 'LD.lead_credit_assign_user_id = U2.user_id', 'LEFT')
                ->join('users U4', 'LD.lead_credithead_assign_user_id = U4.user_id', 'LEFT')
                ->join('users U3', 'LD.lead_disbursal_assign_user_id = U3.user_id', 'LEFT')
                ->where("L.loan_active = 1 AND CB.account_status_id=1 AND CB.customer_banking_active=1 AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate' AND LD.lead_status_id = 37")
                ->get();
            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportDisbursedHold($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('L.lead_id, L.customer_id, CONCAT_WS(" ",LC.first_name, LC.middle_name, LC.sur_name) as full_name, CAM.loan_recommended, LD.user_type, LD.pancard, L.loan_no, CAM.admin_fee, CAM.roi, CAM.tenure, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, CB.account, CB.bank_name, CB.ifsc_code, L.status, LD.lead_entry_date, U2.name sanctionby, LD.lead_credit_assign_datetime, L.created_on, LD.lead_final_disbursed_date, U1.name screenby, U3.name loan_initial_by, U4.name sanctio_approve_by, LD.lead_disbursal_assign_datetime, LD.lead_disbursal_recommend_datetime')
                ->from('loan L')
                ->join('leads LD', 'L.lead_id = LD.lead_id', 'LEFT')
                ->join('lead_customer LC', 'L.lead_id = LC.customer_lead_id', 'LEFT')
                ->join('customer_banking CB', 'L.lead_id = CB.lead_id', 'LEFT')
                ->join('credit_analysis_memo CAM', 'L.lead_id = CAM.lead_id', 'LEFT')
                ->join('users U1', 'LD.lead_screener_assign_user_id = U1.user_id', 'LEFT')
                ->join('users U2', 'LD.lead_credit_assign_user_id = U2.user_id', 'LEFT')
                ->join('users U3', 'LD.lead_disbursal_assign_user_id = U3.user_id', 'LEFT')
                ->join('users U4', 'LD.lead_credithead_assign_user_id = U4.user_id', 'LEFT')
                ->where("L.loan_active = 1 AND CB.account_status_id=1 AND CB.customer_banking_active=1 AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate' AND LD.lead_status_id = 35")
                ->get();
            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportBlackListed($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('BL.bl_id,BL.bl_source_entity,BL.bl_lead_id, BL.bl_loan_no, CONCAT_WS(" ",BL.bl_customer_first_name, BL.bl_customer_middle_name, BL.bl_customer_sur_name) as full_name, BL.bl_customer_dob, BL.bl_customer_pancard, BL.bl_customer_mobile, BL.bl_customer_alternate_mobile, BL.bl_customer_email, BL.bl_customer_alternate_email, U.name user_name_added_by,BL.bl_created_on, MR.m_br_name, BL.bl_reason_remark')
                ->from('customer_black_list BL')
                ->join('users U', 'BL.bl_created_user_id = U.user_id', 'LEFT')
                ->join('master_blacklist_reject_reason MR', 'BL.bl_reason_id = MR.m_br_id', 'LEFT')
                ->where("BL.bl_active = 1 AND DATE(BL.bl_created_on) >= '$fromDate' AND DATE(BL.bl_created_on) <= '$toDate'")
                ->order_by('BL.bl_id', 'DESC')
                ->get();
            //            print_r($this->db->last_query());
            //            exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportPreCollection($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $current_date = date('Y-m-d', strtotime($fromDate));
            $reminder_date = date('Y-m-d', strtotime($toDate));

            //            $current_date = date("Y-m-d");
            //            $repayment_reminder_days = 5;
            //            $reminder_date = date("Y-m-d", strtotime("+$repayment_reminder_days day", strtotime(date("Y-m-d"))));
            //                $return_apps_array = array();

            $sql = "SELECT LD.lead_id,LD.source, LD.status, CONCAT_WS(' ',LC.first_name, LC.middle_name, LC.sur_name) as cust_full_name";
            $sql .= " , LC.email,LC.alternate_email, LC.mobile, LC.alternate_mobile, L.loan_no, L.recommended_amount, CAM.loan_recommended, CAM.roi, CAM.tenure, CAM.repayment_date, CAM.disbursal_date, CAM.repayment_amount";
            $sql .= " , credit_manager.name as credit_manager_name, ST.m_state_name, MC.m_city_name";
            $sql .= " FROM leads LD";
            $sql .= " INNER JOIN lead_customer LC ON(LD.lead_id = LC.customer_lead_id)";
            $sql .= " INNER JOIN credit_analysis_memo CAM ON(LD.lead_id = CAM.lead_id)";
            $sql .= " INNER JOIN loan L ON(L.lead_id = LD.lead_id)";
            $sql .= " LEFT JOIN master_state ST ON(LC.state_id = ST.m_state_id)";
            $sql .= " LEFT JOIN master_city MC ON(LC.city_id = MC.m_city_id)";
            $sql .= " LEFT JOIN users credit_manager ON(LD.lead_credit_assign_user_id = credit_manager.user_id )";
            $sql .= " WHERE LD.lead_status_id IN(14,19) AND repayment_date >= '$current_date' AND repayment_date <= '$reminder_date'";

            $tempDetails = $this->db->query($sql);

            //            if ($tempDetails->num_rows() > 0) {
            //                $return_apps_array = $tempDetails;
            //            }

            return $tempDetails;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportPendingCollectionverification($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('C.lead_id, CONCAT_WS(" ",LC.first_name, LC.middle_name, LC.sur_name) as full_name, CAM.loan_recommended, C.loan_no, CAM.roi, CAM.tenure, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, C.received_amount, C.date_of_recived, L.status leadstatus, C.payment_mode, C.discount, U1.name rname, C.collection_executive_payment_created_on, C.company_account_no, C.refrence_no, C.remarks, MS.m_state_name, MC.m_city_name, MSC.status_name status')
                ->from('collection C')
                ->join('lead_customer LC', 'C.lead_id = LC.customer_lead_id', 'LEFT')
                ->join('leads L', 'C.lead_id = L.lead_id', 'LEFT')
                ->join('credit_analysis_memo CAM', 'C.lead_id = CAM.lead_id', 'LEFT')
                ->join('master_status MSC', 'C.repayment_type = MSC.status_id', 'LEFT')
                ->join('master_state MS', 'LC.state_id = MS.m_state_id', 'LEFT')
                ->join('master_city MC', 'LC.city_id = MC.m_city_id', 'LEFT')
                ->join('users U1', 'C.collection_executive_user_id = U1.user_id', 'LEFT')
                ->where("C.payment_verification = 0 AND C.collection_active = 1 AND DATE(C.collection_executive_payment_created_on) >= '$fromDate' AND DATE(C.collection_executive_payment_created_on) <= '$toDate'")
                ->get();

            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportLegalData($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('L.lead_id, L.loan_no, CONCAT_WS(" ",LC.first_name, LC.middle_name, LC.sur_name) as full_name, LC.mobile, CAM.loan_recommended, CAM.admin_fee, CAM.tenure, CAM.roi, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, LD.user_type, master_status.status_name, U1.name, CONCAT_WS(" ", LC.current_house, " ", LC.current_locality) Address, CONCAT(LC.aa_current_house, LC.aa_current_locality) aadharaddress, LC.cr_residence_pincode pincode, LC.aa_cr_residence_pincode, master_branch.m_branch_name')
                ->from('loan L')
                ->join('lead_customer LC', 'L.lead_id = LC.customer_lead_id', 'LEFT')
                ->join('leads LD', 'L.lead_id = LD.lead_id', 'INNER')
                ->join('credit_analysis_memo CAM', 'L.lead_id = CAM.lead_id', 'LEFT')
                ->join('master_status', 'LD.lead_status_id= master_status.status_id', 'LEFT')
                ->join('master_city', 'LD.city_id= master_city.m_city_id', 'LEFT')
                ->join('master_branch', 'master_city.m_city_branch_id = master_branch.m_branch_id', 'LEFT')
                ->join('users U1', 'LD.lead_credit_assign_user_id = U1.user_id', 'LEFT')
                ->where("LD.lead_active = 1 AND (LD.lead_status_id=14 OR lead_status_id=19) AND L.loan_status_id=14 AND DATE(CAM.repayment_date) >= '$fromDate' AND DATE(CAM.repayment_date) <= '$toDate'")
                ->get();

            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportVisitRequet($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('CV.col_lead_id, CAM.repayment_date, CAM.loan_recommended, MC.m_city_name, master_state.m_state_name, LD.pincode, CONCAT_WS(" ", CE.emp_house, CE.emp_street, CE.emp_landmark) office_address, LC.customer_lead_id, CONCAT_WS(" ", LC.current_house, LC.current_locality) residence_address, MB.m_branch_name, CONCAT_WS(" ",LC.first_name, LC.middle_name, LC.sur_name) as full_name, LD.mobile, LD.loan_no, CV.col_visit_address_type, U1.name coll_name, CV.col_visit_requested_by_remarks, CV.col_visit_requested_datetime, U2.name scm_name, MS.m_visit_name, CV.col_visit_created_on')
                ->from('loan_collection_visit CV')
                ->join('master_visit_status MS', 'CV.col_visit_field_status_id = MS.m_visit_id', 'INNER')
                ->join('leads LD', 'LD.lead_id=CV.col_lead_id', 'INNER')
                ->join('lead_customer LC', 'LC.customer_lead_id=CV.col_lead_id', 'INNER')
                ->join('master_city MC', 'LD.city_id=MC.m_city_id', 'LEFT')
                ->join('credit_analysis_memo CAM', 'LD.lead_id=CAM.lead_id', 'LEFT')
                ->join('master_state', 'LD.state_id=master_state.m_state_id', 'LEFT')
                ->join('customer_employment CE', 'LC.customer_lead_id=CE.lead_id', 'LEFT')
                ->join('master_branch MB', 'LD.lead_branch_id=MB.m_branch_id', 'LEFT')
                ->join('users U1', 'CV.col_visit_requested_by=U1.user_id', 'LEFT')
                ->join('users U2', 'CV.col_visit_scm_id=U2.user_id', 'LEFT')
                ->where("CV.col_visit_active=1 AND CV.col_visit_field_status_id=1 AND DATE(CV.col_visit_created_on) >= '$fromDate' AND DATE(CV.col_visit_created_on) <= '$toDate'")
                ->get();

            //            print_r($this->db->last_query());
            //            exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportVisitCompleted($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('CV.col_lead_id, CV.col_fe_visit_approval_datetime, CV.col_visit_allocated_to, CV.col_fe_rtoh_total_distance_covered, CV.col_fe_rtoh_return_type, CV.col_fe_visit_approval_status, U4.name as approved_by, CAM.repayment_date, CAM.loan_recommended, CE.lead_id, MC.m_city_name, master_state.m_state_name, LD.pincode, CONCAT_WS(" ", CE.emp_house, CE.emp_street, CE.emp_landmark) office_address, LC.customer_lead_id, CONCAT_WS(" ", LC.current_house, LC.current_locality) residence_address, CV.col_visit_address_type, MB.m_branch_name, CONCAT_WS(" ",LC.first_name, LC.middle_name, LC.sur_name) as full_name, LD.mobile, LD.loan_no, CV.col_visit_address_type, U1.name coll_name, CV.col_visit_requested_by_remarks, CV.col_visit_requested_datetime, U2.name scm_name, CV.col_visit_scm_remarks, CV.col_visit_allocate_on, MS.m_visit_name, CV.col_visit_created_on, U3.name rm_name, CV.col_fe_visit_trip_start_datetime, CV.col_fe_visit_trip_stop_datetime, CV.col_fe_visit_end_datetime, CV.col_fe_visit_total_distance_covered, CV.col_fe_visit_total_amount_received, CV.col_visit_field_remarks, CV.col_fe_visit_trip_start_latitude, CV.col_fe_visit_trip_start_longitude, CV.col_fe_visit_end_latitude, CV.col_fe_visit_end_longitude, LD.user_type, CE.emp_pincode, CV.col_fe_rtoh_return_datetime, CV.col_fe_rtoh_end_latitude, CV.col_fe_rtoh_end_longitude, off_MC.m_city_name off_citys, off_state.m_state_name off_states')
                ->from('loan_collection_visit CV')
                ->join('master_visit_status MS', 'CV.col_visit_field_status_id = MS.m_visit_id', 'INNER')
                ->join('leads LD', 'LD.lead_id=CV.col_lead_id', 'INNER')
                ->join('lead_customer LC', 'LC.customer_lead_id=CV.col_lead_id', 'INNER')
                ->join('credit_analysis_memo CAM', 'LD.lead_id=CAM.lead_id', 'LEFT')
                ->join('customer_employment CE', 'LC.customer_lead_id=CE.lead_id', 'LEFT')
                ->join('master_city MC', 'LD.city_id=MC.m_city_id', 'LEFT')
                ->join('master_state', 'LD.state_id=master_state.m_state_id', 'LEFT')
                ->join('master_city off_MC', 'CE.city_id=off_MC.m_city_id', 'LEFT') //
                ->join('master_state off_state', 'CE.state_id=off_state.m_state_id', 'LEFT')
                ->join('master_branch MB', 'LD.lead_branch_id=MB.m_branch_id', 'LEFT')
                ->join('users U1', 'CV.col_visit_requested_by=U1.user_id', 'LEFT')
                ->join('users U2', 'CV.col_visit_scm_id=U2.user_id', 'LEFT')
                ->join('users U3', 'CV.col_visit_allocated_to=U3.user_id', 'LEFT')
                ->join('users U4', 'CV.col_fe_visit_approval_user_id=U4.user_id', 'LEFT')
                ->where("CV.col_visit_active=1 AND CV.col_visit_field_status_id=5 AND DATE(CV.col_fe_visit_end_datetime) >= '$fromDate' AND DATE(CV.col_fe_visit_end_datetime) <= '$toDate'")
                ->get();

            //            print_r($this->db->last_query());
            //            exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportVisitPending($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('CV.col_lead_id, CAM.repayment_date, CAM.loan_recommended, MC.m_city_name, master_state.m_state_name, CE.lead_id, LD.pincode, CONCAT_WS(" ", CE.emp_house, CE.emp_street, CE.emp_landmark) office_address, LC.customer_lead_id, CONCAT_WS(" ", LC.current_house, LC.current_locality) residence_address, CV.col_visit_address_type, MB.m_branch_name, CONCAT_WS(" ",LC.first_name, LC.middle_name, LC.sur_name) as full_name, LD.mobile, LD.loan_no, CV.col_visit_address_type, U1.name coll_name, CV.col_visit_requested_by_remarks, CV.col_visit_requested_datetime, U2.name scm_name, CV.col_visit_scm_remarks, CV.col_visit_allocate_on, MS.m_visit_name, CV.col_visit_created_on, U3.name rm_name, CV.col_fe_visit_trip_start_datetime, CV.col_fe_visit_trip_stop_datetime, CV.col_fe_visit_end_datetime, CV.col_fe_visit_total_distance_covered, CV.col_fe_visit_total_amount_received, CV.col_visit_field_remarks')
                ->from('loan_collection_visit CV')
                ->join('master_visit_status MS', 'CV.col_visit_field_status_id = MS.m_visit_id', 'INNER')
                ->join('leads LD', 'LD.lead_id=CV.col_lead_id', 'INNER')
                ->join('lead_customer LC', 'LC.customer_lead_id=CV.col_lead_id', 'INNER')
                ->join('credit_analysis_memo CAM', 'LD.lead_id=CAM.lead_id', 'LEFT')
                ->join('master_city MC', 'LD.city_id=MC.m_city_id', 'LEFT')
                ->join('master_state', 'LD.state_id=master_state.m_state_id', 'LEFT')
                ->join('customer_employment CE', 'LC.customer_lead_id=CE.lead_id', 'LEFT')
                ->join('master_branch MB', 'LD.lead_branch_id=MB.m_branch_id', 'LEFT')
                ->join('users U1', 'CV.col_visit_requested_by=U1.user_id', 'LEFT')
                ->join('users U2', 'CV.col_visit_scm_id=U2.user_id', 'LEFT')
                ->join('users U3', 'CV.col_visit_allocated_to=U3.user_id', 'LEFT')
                ->where("CV.col_visit_active=1 AND CV.col_visit_field_status_id=2 AND DATE(CV.col_visit_allocate_on) >= '$fromDate' AND DATE(CV.col_visit_allocate_on) <= '$toDate'")
                ->get();

            //            print_r($this->db->last_query());
            //            exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportLoanWaived($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('L.lead_id, master_branch.m_branch_name, L.customer_id, CONCAT_WS(" ", LC.first_name, LC.middle_name, LC.sur_name) as full_name, CAM.loan_recommended, LD.user_type, LD.pancard, L.loan_no, LC.mobile, LC.email, CAM.admin_fee, CAM.roi, CAM.tenure, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, L.mode_of_payment, LC.dob, LD.cibil, CB.account, CB.bank_name, CB.ifsc_code, L.disburse_refrence_no, L.company_account_no, L.status, U2.name sanctionby, LD.lead_credit_assign_datetime, L.created_on, LD.lead_final_disbursed_date, U1.name screenby, U3.name loan_disburse_by, U4.name sanction_approve_by, MC.m_city_name, LD.source, LD.lead_disbursal_approve_datetime, U5.name loan_initiat_by, LD.lead_disbursal_assign_datetime, LD.lead_disbursal_recommend_datetime')
                ->from('loan L')
                ->join('leads LD', 'L.lead_id = LD.lead_id', 'LEFT')
                ->join('lead_customer LC', 'L.lead_id = LC.customer_lead_id', 'LEFT')
                ->join('customer_banking CB', 'L.lead_id = CB.lead_id', 'LEFT')
                ->join('customer_employment CE', 'LD.lead_id = CE.lead_id', 'LEFT')
                ->join('credit_analysis_memo CAM', 'L.lead_id = CAM.lead_id', 'LEFT')
                ->join('master_city MC', 'LC.city_id = MC.m_city_id', 'LEFT')
                ->join('master_branch', 'MC.m_city_branch_id = master_branch.m_branch_id', 'LEFT')
                ->join('users U1', 'LD.lead_screener_assign_user_id = U1.user_id', 'LEFT')
                ->join('users U2', 'LD.lead_credit_assign_user_id = U2.user_id', 'LEFT')
                ->join('users U3', 'LD.lead_disbursal_approve_user_id = U3.user_id', 'LEFT')
                ->join('users U4', 'LD.lead_credithead_assign_user_id = U4.user_id', 'LEFT')
                ->join('users U5', 'LD.lead_disbursal_assign_user_id = U5.user_id', 'LEFT')
                ->where("L.loan_active = 1 AND L.loan_status_id = 40 AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate'")
                ->distinct()
                ->get();
            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportPanindiasummary($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('L.lead_id, MS.m_state_name state, BR.m_branch_name branch,  CONCAT_WS(" ", LC.first_name, LC.middle_name, LC.sur_name) as full_name, CAM.loan_recommended, LD.user_type, LD.pancard, L.loan_no, LC.mobile, LC.alternate_mobile, LC.email, LC.alternate_email, CAM.admin_fee, CAM.roi, CAM.tenure, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, LD.cibil, LD.status, LD.lead_entry_date, U1.name sanctionproveby, U2.name sanctionby, CONCAT_WS(" ", LC.current_house, " ", LC.current_locality) Address, CONCAT(LC.aa_current_house, LC.aa_current_locality) aadharaddress, MC.m_city_name, LC.cr_residence_pincode pincode, LD.utm_source, CAM.salary_credit1_date, CAM.salary_credit1_amount, LC.current_residence_type, L.loan_noc_letter_sent_status, L.loan_noc_letter_sent_datetime')
                ->from('loan L')
                ->join('leads LD', 'L.lead_id = LD.lead_id', 'LEFT')
                ->join('lead_customer LC', 'L.lead_id = LC.customer_lead_id', 'LEFT')
                ->join('customer_banking CB', 'L.lead_id = CB.lead_id', 'LEFT')
                ->join('customer_employment CE', 'LD.lead_id = CE.lead_id', 'LEFT')
                ->join('credit_analysis_memo CAM', 'L.lead_id = CAM.lead_id', 'LEFT')
                ->join('master_city MC', 'LD.city_id = MC.m_city_id', 'LEFT')
                ->join('master_state MS', 'LD.state_id  = MS.m_state_id', 'LEFT')
                ->join('master_branch BR', 'LD.lead_branch_id  = BR.m_branch_id', 'LEFT')
                ->join('users U1', 'LD.lead_credit_approve_user_id	 = U1.user_id', 'LEFT')
                ->join('users U2', 'LD.lead_credit_assign_user_id = U2.user_id', 'LEFT')
                ->join('users U3', 'LD.lead_credit_assign_user_id = U3.user_id', 'LEFT')
                ->where("L.loan_active = 1 AND L.loan_status_id = 14 AND LD.lead_status_id != 9 AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date <= '$toDate' AND CB.account_status_id=1 AND CB.customer_banking_active=1")
                ->distinct()
                ->get();

            //            AND CAM.disbursal_date >= '$fromDate'
            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportOutstandingData($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $current_date = date('Y-m-d', strtotime($fromDate));
            $reminder_date = date('Y-m-d', strtotime($toDate));

            $branchIds = "";

            if (isset($_SESSION['isUserSession']['user_branch']) && !empty($_SESSION['isUserSession']['user_branch'])) {
                $branchIds = implode(",", $_SESSION['isUserSession']['user_branch']);
            }


            // $sql = "SELECT LD.lead_id,LD.source, LD.status, CE.monthly_income, CE.employer_name, master_branch.m_branch_name, CONCAT_WS(' ',LC.first_name, LC.middle_name, LC.sur_name) as cust_full_name";
            // $sql .= " , LC.email,LC.alternate_email, LC.mobile, LC.gender, LC.alternate_mobile, L.loan_no, L.recommended_amount, CAM.loan_recommended, CAM.roi, CAM.tenure, CAM.repayment_date, CAM.disbursal_date, CAM.repayment_amount, LD.user_type";
            // $sql .= ' , credit_manager.name as credit_manager_name, CONCAT_WS(" ", LC.current_house, LC.current_locality, LC.current_landmark) current_address, ST.m_state_name, MC.m_city_name, LC.cr_residence_pincode pincode, CONCAT_WS(" ", CE.emp_house, CE.emp_street, CE.emp_landmark) office_address, employer_city.m_city_name emp_city, employer_state.m_state_name emp_state, CE.emp_pincode office_pincode,';
            // $sql .= " L.loan_principle_outstanding_amount, L.loan_total_received_amount, L.loan_total_outstanding_amount ";
            // $sql .= " FROM leads LD";
            // $sql .= " INNER JOIN lead_customer LC ON(LD.lead_id = LC.customer_lead_id)";
            // $sql .= " INNER JOIN credit_analysis_memo CAM ON(LD.lead_id = CAM.lead_id)";
            // $sql .= " INNER JOIN loan L ON(L.lead_id = LD.lead_id)";
            // $sql .= " INNER JOIN customer_employment CE ON(CE.lead_id = LD.lead_id)";
            // $sql .= " LEFT JOIN master_branch ON(LD.lead_branch_id = master_branch.m_branch_id)";
            // $sql .= " LEFT JOIN master_state ST ON(LC.state_id = ST.m_state_id)";
            // $sql .= " LEFT JOIN master_city MC ON(LC.city_id = MC.m_city_id)";
            // $sql .= " LEFT JOIN master_state employer_state ON(CE.state_id = employer_state.m_state_id)";
            // $sql .= " LEFT JOIN master_city employer_city ON(CE.city_id = employer_city.m_city_id)";
            // $sql .= " LEFT JOIN users credit_manager ON(LD.lead_credit_assign_user_id = credit_manager.user_id )";
            // $sql .= " WHERE LD.lead_status_id IN(14,19) AND CAM.repayment_date >= '$current_date' AND CAM.repayment_date <= '$reminder_date'";

            $sql = "SELECT 
    LD.lead_id, 
    LD.source, 
    LD.status, 
    CE.monthly_income, 
    CE.employer_name, 
    
    -- Reference 1
    ref1.reference1_name,
    ref1.reference1_mobile,
    ref1.reference1_relation,
    
    -- Reference 2
    ref2.reference2_name,
    ref2.reference2_mobile,
    ref2.reference2_relation,

    master_branch.m_branch_name, 
    CONCAT_WS(' ', LC.first_name, LC.middle_name, LC.sur_name) as cust_full_name, 
    LC.email, 
    LC.alternate_email, 
    LC.mobile, 
    LC.gender, 
    LC.alternate_mobile, 

    L.loan_no, 
    L.recommended_amount, 
    CAM.loan_recommended, 
    CAM.roi, 
    CAM.tenure, 
    CAM.repayment_date, 
    CAM.disbursal_date, 
    CAM.repayment_amount, 
    LD.user_type, 
    credit_manager.name as credit_manager_name, 

    CONCAT_WS(' ', LC.current_house, LC.current_locality, LC.current_landmark) current_address, 
    ST.m_state_name, 
    MC.m_city_name, 
    LC.cr_residence_pincode pincode, 

    CONCAT_WS(' ', CE.emp_house, CE.emp_street, CE.emp_landmark) office_address, 
    employer_city.m_city_name emp_city, 
    employer_state.m_state_name emp_state, 
    CE.emp_pincode office_pincode, 

    L.loan_principle_outstanding_amount, 
    L.loan_total_received_amount, 
    L.loan_total_outstanding_amount 

FROM leads LD 

INNER JOIN lead_customer LC ON LC.customer_lead_id = LD.lead_id
INNER JOIN credit_analysis_memo CAM ON CAM.lead_id = LD.lead_id
INNER JOIN loan L ON L.lead_id = LD.lead_id
INNER JOIN customer_employment CE ON CE.lead_id = LD.lead_id

-- Flattened Reference 1
LEFT JOIN (
    SELECT 
        lcr1.lcr_lead_id,
        lcr1.lcr_name AS reference1_name,
        lcr1.lcr_mobile AS reference1_mobile,
        mrt.mrt_name AS reference1_relation
    FROM lead_customer_references lcr1
    LEFT JOIN master_relation_type as mrt
    ON lcr1.lcr_relationType = mrt.mrt_id
    WHERE lcr1.lcr_id = (
        SELECT MIN(lcr_sub1.lcr_id)
        FROM lead_customer_references lcr_sub1
        WHERE lcr_sub1.lcr_lead_id = lcr1.lcr_lead_id
    )
) ref1 ON ref1.lcr_lead_id = LD.lead_id

-- Flattened Reference 2
LEFT JOIN (
    SELECT 
        lcr2.lcr_lead_id,
        lcr2.lcr_name AS reference2_name,
        lcr2.lcr_mobile AS reference2_mobile,
        mrt.mrt_name AS reference2_relation
    FROM lead_customer_references lcr2
    LEFT JOIN master_relation_type as mrt
    ON lcr2.lcr_relationType = mrt.mrt_id
    WHERE lcr2.lcr_id = (
        SELECT lcr_sub2.lcr_id
        FROM lead_customer_references lcr_sub2
        WHERE lcr_sub2.lcr_lead_id = lcr2.lcr_lead_id
        ORDER BY lcr_sub2.lcr_id ASC
        LIMIT 1 OFFSET 1
    )
) ref2 ON ref2.lcr_lead_id = LD.lead_id

-- Other optional joins
LEFT JOIN master_branch ON LD.lead_branch_id = master_branch.m_branch_id
LEFT JOIN master_state ST ON LC.state_id = ST.m_state_id
LEFT JOIN master_city MC ON LC.city_id = MC.m_city_id
LEFT JOIN master_state employer_state ON CE.state_id = employer_state.m_state_id
LEFT JOIN master_city employer_city ON CE.city_id = employer_city.m_city_id
LEFT JOIN users credit_manager ON LD.lead_credit_assign_user_id = credit_manager.user_id

-- Filter: Only active loans with repayment today
WHERE LD.lead_status_id IN (14, 19)
  AND CAM.repayment_date >= '$current_date'
  AND CAM.repayment_date <= '$reminder_date'";

            if (!empty($branchIds)) {
                $sql .= " AND LD.lead_branch_id IN($branchIds)";
            }

            $tempDetails = $this->db->query($sql);

            //                print_r($this->db->last_query());
            //                exit;

            return $tempDetails;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportLoanPool($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $from_date = date('Y-m-d', strtotime($fromDate));
            $to_date = date('Y-m-d', strtotime($toDate));

            $sql = "SELECT LD.lead_id, LD.lead_status_id, LD.created_on, LD.lead_entry_date, LD.source, LD.status, LD.loan_no, LD.user_type, concat_ws(' ',LC.first_name,LC.middle_name,LC.sur_name) as customer_name, LD.pancard, ";
            $sql .= "CAM.loan_recommended AS sanction_loan_amount, CAM.processing_fee_percent AS sanction_processing_fee, CAM.roi AS sanction_roi, CAM.panel_roi AS sanction_panel_roi, CAM.admin_fee AS total_admin_fees, CAM.adminFeeWithGST as admin_fee_gst, CAM.total_admin_fee AS net_admin_fees, CAM.disbursal_date, CAM.repayment_date, CAM.repayment_amount, ";
            $sql .= "(SELECT COL.date_of_recived FROM collection COL WHERE COL.lead_id=LD.lead_id AND COL.payment_verification=1 AND COL.collection_active=1 AND COL.date_of_recived <='$to_date' limit 1) as last_payment_date,(SELECT SUM(COL.received_amount) FROM collection COL WHERE COL.lead_id=LD.lead_id AND COL.payment_verification=1 AND COL.collection_active=1 AND COL.date_of_recived <='2022-03-31') AS coll_amt_as_on_31march2022, (SELECT SUM(COL.received_amount) FROM collection COL WHERE COL.lead_id=LD.lead_id AND COL.payment_verification=1 AND COL.collection_active=1 AND COL.date_of_recived >'2022-03-31' AND COL.date_of_recived <='$to_date') AS coll_amt_after_31march22_on_to_date, (SELECT SUM(COL.received_amount) FROM collection COL WHERE COL.lead_id=LD.lead_id AND COL.payment_verification=1 AND COL.collection_active=1 AND COL.date_of_recived <='$to_date') AS collection_amount_as_on_to_date";
            $sql .= " FROM leads LD ";
            $sql .= "INNER JOIN lead_customer LC ON(LD.lead_id=LC.customer_lead_id) ";
            $sql .= "INNER JOIN credit_analysis_memo CAM ON(CAM.lead_id=LD.lead_id) ";
            $sql .= "INNER JOIN loan L ON(L.lead_id=LD.lead_id)";
            $sql .= " WHERE L.loan_status_id=14 AND CAM.disbursal_date >= '$from_date' AND CAM.disbursal_date <= '$to_date' AND LD.lead_status_id IN(14,16,17,18,19) ";
            $sql .= "AND LD.lead_active=1 AND CAM.cam_active=1 AND L.loan_active=1 ORDER BY CAM.disbursal_date ASC";

            $loanpool = $this->db->query($sql);

            return $loanpool;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportFollowUp($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $from_date = date('Y-m-d', strtotime($fromDate));
            $to_date = date('Y-m-d', strtotime($toDate));

            $sql = "SELECT LCF.lcf_lead_id, L.loan_no, CAM.repayment_date, MFT.m_followup_type_name, MFS.m_followup_status_name, LCF.lcf_remarks, U.name, LCF.lcf_next_schedule_datetime, LCF.lcf_created_on ";
            $sql .= "FROM loan_collection_followup LCF INNER JOIN users U ON(LCF.lcf_user_id=U.user_id) LEFT JOIN master_followup_status MFS ON(LCF.lcf_status_id=MFS.m_followup_status_id) INNER JOIN master_followup_type MFT ON(LCF.lcf_type_id=MFT.m_followup_type_id) INNER JOIN loan L ON(LCF.lcf_lead_id=L.lead_id) INNER JOIN credit_analysis_memo CAM ON(LCF.lcf_lead_id=CAM.lead_id) ";
            $sql .= "WHERE LCF.lcf_active=1 AND LCF.lcf_lead_id=CAM.lead_id AND LCF.lcf_type_id=1 AND DATE(LCF.lcf_created_on) >= '$from_date' AND DATE(LCF.lcf_created_on) <= '$to_date'";

            $followup = $this->db->query($sql);

            //                print_r($this->db->last_query());
            //                exit;

            return $followup;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportRejectedPayments($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $from_date = date('Y-m-d', strtotime($fromDate));
            $to_date = date('Y-m-d', strtotime($toDate));

            $sql = "SELECT C.lead_id, C.received_amount, loan_status.status_name loan_status, C.loan_no, CONCAT_WS(' ', LC.first_name, LC.middle_name, LC.sur_name) full_name, LC.mobile, LC.email, uploaded_user.name executive_name, C.collection_executive_payment_created_on uploaded_date, executive_status.status_name executive_status, closer_user.name Closure_name, C.closure_payment_updated_on Closur_updated_date, C.date_of_recived, C.refrence_no, C.payment_mode, C.remarks Executive_remarks, C.closure_remarks Closure_remarks ";
            $sql .= "FROM collection C INNER JOIN lead_customer LC ON(C.lead_id=LC.customer_lead_id) INNER JOIN leads LD ON(C.lead_id=LD.lead_id) LEFT JOIN master_status loan_status ON(LD.lead_status_id=loan_status.status_id) LEFT JOIN master_status executive_status ON(C.repayment_type=executive_status.status_id) LEFT JOIN users uploaded_user ON(C.collection_executive_user_id=uploaded_user.user_id) LEFT JOIN users closer_user ON(C.closure_user_id=closer_user.user_id) ";
            $sql .= "WHERE C.collection_active=1 AND C.payment_verification=2 AND LC.customer_active=1 AND C.lead_id=LC.customer_lead_id AND C.lead_id=LD.lead_id AND DATE(C.closure_payment_updated_on) <= '$to_date' AND DATE(C.closure_payment_updated_on) >='$from_date'";

            $rejectedfollowup = $this->db->query($sql);

            return $rejectedfollowup;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportBOBDisbursed($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $qry = 'SELECT L.lead_id, L.loan_penalty_discount_amount, L.loan_penalty_outstanding_amount, L.loan_penalty_received_amount, L.loan_principle_outstanding_amount, L.loan_interest_received_amount, master_status.status_name as current_status, master_branch.m_branch_name, L.customer_id, CAM.cam_risk_profile, CAM.cam_risk_score, CAM.cam_advance_interest_amount, CONCAT_WS(" ", LC.first_name, LC.middle_name, LC.sur_name) as full_name, CAM.loan_recommended, LD.user_type, LD.pancard, L.loan_no, LC.mobile, LC.email, CAM.admin_fee, CAM.roi, CAM.tenure, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, L.mode_of_payment, LC.dob, LD.cibil, CB.account, CB.bank_name, CB.ifsc_code, L.disburse_refrence_no, L.company_account_no, L.status, U1.name screenby, LD.lead_screener_assign_datetime, U2.name sanctionby, LD.lead_credit_assign_datetime, L.created_on, LD.lead_final_disbursed_date, U3.name loan_disburse_by, U4.name sanction_approve_by, LD.lead_credit_approve_datetime, MC.m_city_name, LD.source, LD.lead_disbursal_approve_datetime, U5.name loan_initiat_by, LD.lead_disbursal_assign_datetime, LD.lead_disbursal_recommend_datetime, L.loan_total_received_amount as collection_amount, ';

            $qry .= "(SELECT COUNT(LF.lcf_lead_id) FROM loan_collection_followup LF WHERE LF.lcf_type_id=1 AND LF.lcf_lead_id=LD.lead_id AND LF.lcf_active=1) as total_calls, (SELECT COUNT(CV.col_lead_id) FROM loan_collection_visit CV WHERE CV.col_lead_id=LD.lead_id AND CV.col_visit_field_status_id=5 AND CV.col_visit_active=1) as total_visits, (SELECT LF.lcf_remarks FROM loan_collection_followup LF WHERE LF.lcf_type_id=1 AND LF.lcf_lead_id=LD.lead_id AND LF.lcf_active=1 ORDER BY LF.lcf_created_on DESC LIMIT 1) as call_remarks, (SELECT MS.m_followup_status_name FROM loan_collection_followup LF INNER JOIN master_followup_status MS ON(LF.lcf_status_id=MS.m_followup_status_id) WHERE LF.lcf_type_id=1 AND LF.lcf_lead_id=LD.lead_id AND LF.lcf_active=1 ORDER BY LF.lcf_created_on DESC LIMIT 1) as call_status ";

            $qry .= 'FROM loan L LEFT JOIN leads LD ON L.lead_id = LD.lead_id LEFT JOIN lead_customer LC ON L.lead_id = LC.customer_lead_id LEFT JOIN customer_banking CB ON L.lead_id = CB.lead_id LEFT JOIN customer_employment CE ON LD.lead_id = CE.lead_id LEFT JOIN credit_analysis_memo CAM ON L.lead_id = CAM.lead_id LEFT JOIN master_city MC ON LC.city_id = MC.m_city_id LEFT JOIN master_branch ON LD.lead_branch_id = master_branch.m_branch_id LEFT JOIN users U1 ON LD.lead_screener_assign_user_id = U1.user_id LEFT JOIN users U2 ON LD.lead_credit_assign_user_id = U2.user_id LEFT JOIN users U3 ON LD.lead_disbursal_approve_user_id = U3.user_id LEFT JOIN users U4 ON LD.lead_credit_approve_user_id = U4.user_id LEFT JOIN users U5 ON LD.lead_disbursal_assign_user_id = U5.user_id INNER JOIN master_status ON(LD.lead_status_id=master_status.status_id) ';

            $qry .= "WHERE L.loan_active = 1 AND L.loan_status_id = 14  AND CB.account_status_id=1 AND CB.customer_banking_active=1 ";

            $qry .= " AND LD.loan_no IS NOT NULL AND LD.loan_no IS NOT NULL AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate' ";

            $result = $this->db->query($qry);

            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportSuspenseRecoveryModel($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $result = $this->db->select('C.lead_id, TIMESTAMPDIFF(day, C.date_of_recived, C.closure_payment_updated_on) as suspense_day, CONCAT_WS(" ",LC.first_name, LC.middle_name, LC.sur_name) as full_name, CAM.loan_recommended, C.loan_no, CAM.roi, CAM.tenure, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, C.received_amount, C.date_of_recived, L.status leadstatus, C.payment_mode, C.discount, U1.name rname, C.collection_executive_payment_created_on, U2.name closure_name, C.closure_payment_updated_on, C.company_account_no, C.refrence_no, C.remarks, C.collection_type, C.noc,  MS.m_state_name, MC.m_city_name, master_branch.m_branch_name, MSC.status_name status, C.closure_remarks, L.source')
                ->from('collection C')
                ->join('lead_customer LC', 'C.lead_id = LC.customer_lead_id', 'LEFT')
                ->join('leads L', 'C.lead_id = L.lead_id', 'LEFT')
                ->join('credit_analysis_memo CAM', 'C.lead_id = CAM.lead_id', 'LEFT')
                ->join('master_status MSC', 'C.repayment_type = MSC.status_id', 'LEFT')
                ->join('master_state MS', 'LC.state_id = MS.m_state_id', 'LEFT')
                ->join('master_city MC', 'LC.city_id = MC.m_city_id', 'LEFT')
                ->join('master_branch', 'MC.m_city_branch_id = master_branch.m_branch_id', 'LEFT')
                ->join('users U1', 'C.collection_executive_user_id = U1.user_id', 'LEFT')
                ->join('users U2', 'C.closure_user_id = U2.user_id', 'LEFT')
                ->where("C.payment_verification = 1 AND C.collection_active = 1 AND (TIMESTAMPDIFF(day, C.date_of_recived, C.closure_payment_updated_on)) > 0 AND C.date_of_recived IS NOT NULL AND C.date_of_recived IS NOT NULL AND DATE(C.closure_payment_updated_on) >= '$fromDate' AND DATE(C.closure_payment_updated_on) <= '$toDate'")
                ->get();

            //                print_r($this->db->last_query());
            //                exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportBOBDisbursalPending($fromDate, $toDate) {

        if (!empty($fromDate) && !empty($toDate)) {

            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $query = "SELECT  L.loan_no, CAM.net_disbursal_amount, CB.beneficiary_name, CB.account, CB.ifsc_code , (SELECT disb_bank_account_no FROM master_disbursement_banks WHERE disb_bank_id=5 AND disb_bank_active=1) AS company_disb_account_no";
            $query .= " FROM loan L INNER JOIN leads LD ON (L.lead_id = LD.lead_id)";
            $query .= " INNER JOIN customer_banking CB ON (L.lead_id = CB.lead_id)";
            $query .= " INNER JOIN credit_analysis_memo CAM ON (CAM.lead_id = LD.lead_id)";
            $query .= " WHERE CAM.cam_active AND LD.lead_active = 1 AND L.loan_active = 1 AND L.loan_status_id = 13 AND LD.lead_status_id = 13";
            $query .= " AND LD.lead_disbursal_recommend_datetime IS NOT NULL AND LD.lead_disbursal_recommend_datetime IS NOT NULL";
            $query .= " AND CB.account_status_id=1 AND CB.customer_banking_active=1";
            $query .= " AND DATE(LD.lead_disbursal_recommend_datetime) >= '$fromDate' AND DATE(LD.lead_disbursal_recommend_datetime) <= '$toDate'";
            $query .= " GROUP BY L.loan_no";
            //            echo $query;
            $result = $this->db->query($query);

            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportNewDisbursed($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $return_array = array();

            $branch = implode(",", $_SESSION['isUserSession']['user_branch']);

            $qry = 'SELECT L.lead_id, master_branch.m_branch_name, LC.cr_residence_pincode, LD.utm_source, LD.utm_campaign, LD.status as current_status, L.customer_id, CAM.cam_risk_profile, CONCAT_WS(" ", LC.first_name, LC.middle_name, LC.sur_name) as full_name, CAM.loan_recommended, LD.user_type, LD.pancard, L.loan_no, LC.mobile, LC.email, CAM.admin_fee, CAM.total_admin_fee, CAM.adminFeeWithGST, CAM.roi, CAM.tenure, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, L.mode_of_payment, LC.dob, LD.cibil, CB.account, CB.bank_name, CB.ifsc_code, L.disburse_refrence_no, L.company_account_no, L.status, U2.name sanctionby, LD.lead_credit_assign_datetime, L.created_on, LD.lead_final_disbursed_date, U1.name screenby, U3.name loan_disburse_by, U4.name sanction_approve_by, MC.m_city_name, LD.source, LD.lead_disbursal_approve_datetime, U5.name loan_initiat_by, LD.lead_disbursal_assign_datetime, LD.lead_disbursal_recommend_datetime, L.loan_principle_received_amount, L.loan_principle_outstanding_amount, L.loan_principle_discount_amount, L.loan_interest_received_amount, L.loan_interest_outstanding_amount, L.loan_interest_discount_amount, L.loan_penalty_received_amount, L.loan_penalty_outstanding_amount, L.loan_penalty_discount_amount, L.loan_total_received_amount, L.loan_total_outstanding_amount, L.loan_total_discount_amount, L.loan_closure_date, L.loan_settled_date, L.loan_penalty_payable_amount, L.loan_principle_payable_amount, L.loan_interest_payable_amount ';

            $qry .= 'FROM loan L LEFT JOIN leads LD ON L.lead_id = LD.lead_id LEFT JOIN lead_customer LC ON L.lead_id = LC.customer_lead_id LEFT JOIN customer_banking CB ON(L.lead_id = CB.lead_id AND CB.account_status_id=1 AND CB.customer_banking_active=1) LEFT JOIN customer_employment CE ON LD.lead_id = CE.lead_id LEFT JOIN credit_analysis_memo CAM ON L.lead_id = CAM.lead_id LEFT JOIN master_city MC ON LC.city_id = MC.m_city_id LEFT JOIN master_branch ON LD.lead_branch_id = master_branch.m_branch_id LEFT JOIN users U1 ON LD.lead_screener_assign_user_id = U1.user_id LEFT JOIN users U2 ON LD.lead_credit_assign_user_id = U2.user_id LEFT JOIN users U3 ON LD.lead_disbursal_approve_user_id = U3.user_id LEFT JOIN users U4 ON LD.lead_credithead_assign_user_id = U4.user_id LEFT JOIN users U5 ON LD.lead_disbursal_assign_user_id = U5.user_id ';

            if (!empty($branch)) {

                $qry .= "WHERE L.loan_active = 1 AND L.loan_status_id = 14 AND LD.lead_branch_id IN('$branch') AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate' AND CB.account_status_id=1 AND CB.customer_banking_active=1";
            } else {

                $qry .= "WHERE L.loan_active = 1 AND L.loan_status_id = 14 AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date IS NOT NULL AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate'";
            }

            $result = $this->db->query($qry);

            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportCollectionModel($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            if (in_array(agent, ["CO1", "CO2", "CO3"])) {
                $fromDate = (strtotime($fromDate) > strtotime('-10 day', strtotime(date('Y-m-d'))) ? date('Y-m-d', strtotime('-10 day', strtotime(date('Y-m-d')))) : $fromDate);
                $toDate = (strtotime($toDate) > strtotime('-10 day', strtotime(date('Y-m-d'))) ? date('Y-m-d', strtotime('-10 day', strtotime(date('Y-m-d')))) : $toDate);
            }

            $branch = implode(",", $_SESSION['isUserSession']['user_branch']);



            $qry = 'SELECT L.lead_id, master_branch.m_branch_name, LD.status as current_status, L.customer_id, CAM.cam_risk_profile, CONCAT_WS(" ", LC.first_name, LC.middle_name, LC.sur_name) as full_name, CAM.loan_recommended, LD.user_type,
            LD.pancard, L.loan_no, LC.mobile, LC.email, CAM.admin_fee, CAM.roi, CAM.tenure, CAM.repayment_amount, CAM.disbursal_date, CAM.repayment_date, L.mode_of_payment, LC.dob, LD.cibil, CB.account, CB.bank_name, CB.ifsc_code, L.disburse_refrence_no,
            L.company_account_no, L.status, U2.name sanctionby, LD.lead_credit_assign_datetime, L.created_on, LD.lead_final_disbursed_date, U1.name screenby, U3.name loan_disburse_by, U4.name sanction_approve_by, MC.m_city_name, LD.source,
            LD.lead_disbursal_approve_datetime, U5.name loan_initiat_by, LD.lead_disbursal_assign_datetime, LD.lead_disbursal_recommend_datetime, L.loan_principle_received_amount, L.loan_principle_outstanding_amount,
            L.loan_principle_discount_amount, L.loan_interest_received_amount, L.loan_interest_outstanding_amount, L.loan_interest_discount_amount, L.loan_penalty_received_amount,
            L.loan_penalty_outstanding_amount, L.loan_penalty_discount_amount, L.loan_total_received_amount, L.loan_total_outstanding_amount, L.loan_total_discount_amount, L.loan_closure_date,
            L.loan_settled_date, L.loan_penalty_payable_amount, L.loan_principle_payable_amount, L.loan_interest_payable_amount FROM loan L LEFT JOIN leads LD ON L.lead_id = LD.lead_id
            LEFT JOIN lead_customer LC ON L.lead_id = LC.customer_lead_id LEFT JOIN customer_banking CB ON(L.lead_id = CB.lead_id AND CB.account_status_id=1 AND CB.customer_banking_active=1)
            LEFT JOIN customer_employment CE ON LD.lead_id = CE.lead_id LEFT JOIN credit_analysis_memo CAM ON L.lead_id = CAM.lead_id LEFT JOIN master_city MC ON LC.city_id = MC.m_city_id
            LEFT JOIN master_branch ON LD.lead_branch_id = master_branch.m_branch_id LEFT JOIN users U1 ON LD.lead_screener_assign_user_id = U1.user_id LEFT JOIN users U2 ON LD.lead_credit_assign_user_id = U2.user_id
            LEFT JOIN users U3 ON LD.lead_disbursal_approve_user_id = U3.user_id LEFT JOIN users U4 ON LD.lead_credithead_assign_user_id = U4.user_id LEFT JOIN users U5 ON LD.lead_disbursal_assign_user_id = U5.user_id';

            if (!empty($branch)) {
                $qry .= " WHERE L.loan_active = 1 AND L.loan_status_id = 14 AND LD.lead_branch_id IN($branch) AND CAM.repayment_date >= '$fromDate' AND CAM.repayment_date <= '$toDate' AND CB.account_status_id=1 AND CB.customer_banking_active=1";
            } else {
                $qry .= " WHERE L.loan_active = 1 AND L.loan_status_id = 14 AND CAM.repayment_date >= '$fromDate' AND CAM.repayment_date <= '$toDate'";
            }

            $result = $this->db->query($qry);

            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportDashboardDataModel($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            // $branch = implode(",", $_SESSION['isUserSession']['user_branch']);

            $qry = "SELECT LD.lead_id, L.loan_no, CAM.loan_recommended, CAM.repayment_amount, L.loan_total_received_amount, L.loan_principle_outstanding_amount, L.loan_total_outstanding_amount, LD.user_type, concat_ws(' ',LC.first_name,LC.middle_name,LC.sur_name) as customer_name, LD.mobile, LD.email, CAM.disbursal_date, CAM.repayment_date, CAM.roi, CAM.tenure, MB.m_branch_name, U.name AS sanction_by , LD.status ";

            $qry .= "FROM leads LD INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) INNER JOIN lead_customer LC ON(LD.lead_id=LC.customer_lead_id) INNER JOIN master_branch MB ON(LD.lead_branch_id=MB.m_branch_id) LEFT JOIN users U ON(LD.lead_credit_assign_user_id=U.user_id) ";

            $qry .= "WHERE LD.lead_id=CAM.lead_id AND LD.lead_id=L.lead_id AND LD.lead_id=LC.customer_lead_id AND LD.lead_status_id IN(14, 16, 17, 18, 19) AND CAM.repayment_date >= '$fromDate' AND CAM.repayment_date <= '$toDate' ORDER BY repayment_date ASC";

            $result = $this->db->query($qry);

            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportLoanDumpModel($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $qry = "SELECT LD.lead_id, LD.customer_id, master_status.status_name as current_status, CE.income_type, LD.loan_no, LD.mobile, LD.purpose, LD.user_type, LD.pancard, LD.loan_amount, LD.tenure, LD.cibil, LD.obligations, LD.promocode, LD.source, LD.lead_final_disbursed_date, master_branch.m_branch_name AS BRANCH, lead_state.m_state_name AS STATE, lead_city.m_city_name AS CITY, LD.pincode, LD.coordinates, LD.status, LD.utm_source, LD.utm_campaign, LD.ip, LD.created_on, LD.lead_entry_date, LD.lead_reference_no, lead_screener_assign_user_id.name AS SCREEN_ASSIGNED_TO, LD.lead_screener_assign_datetime, LD.lead_screener_recommend_datetime, lead_credit_assign_user_id.name AS CREDIT_ASSIGN_TO, LD.lead_credit_assign_datetime, LD.lead_credit_recommend_datetime, lead_credithead_assign_user_id.name AS SANCTION_ASSIGNED_BY, LD.lead_credithead_assign_datetime, lead_credit_approve_user_id.name AS SANCTION_APPROVED_BY, LD.lead_credit_approve_datetime, lead_disbursal_assign_user_id.name AS DISBURSAL_ASSIGNED_BY, LD.lead_disbursal_assign_datetime, LD.lead_disbursal_recommend_datetime, lead_disbursal_approve_user_id.name AS DISBURSAL_APPROVED_BY, LD.lead_disbursal_approve_datetime, LD.lead_final_disbursed_date, LD.lead_rejected_reason_id, lead_rejected_user_id.name AS REJECTED_BY, LD.lead_rejected_datetime, LD.lead_black_list_flag, LD.lead_stp_flag, LC.first_name, LC.middle_name, LC.sur_name, LC.gender, LC.dob, LC.pancard_verified_status, LC.pancard_verified_on, LC.pancard_ocr_verified_status, LC.pancard_ocr_verified_on, LC.email_verified_status, LC.email_verified_on, LC.alternate_email_verified_status, LC.alternate_email_verified_on, LC.mobile_verified_status, LC.current_house, LC.current_locality, LC.current_landmark, LC.cr_residence_pincode, LC.aa_current_house, LC.aa_current_locality, LC.aa_current_landmark, LC.aa_cr_residence_pincode,  aadhar_state.m_state_name AS AADHAAR_STATE,  aadhar_city.m_city_name AS AADHAAR_CITY, LC.current_residence_since, LC.current_residence_type, LC.current_residing_withfamily,  curr_state.m_state_name AS CURRENT_STATE,  curr_city.m_city_name AS CURRENT_CITY, LC.aadhar_no, master_religion.religion_name as RELIGION, LC.father_name, LC.aadhaar_ocr_verified_status, LC.aadhaar_ocr_verified_on, LC.customer_ekyc_request_initiated_on, LC.customer_ekyc_request_ip, LC.customer_digital_ekyc_flag, LC.customer_digital_ekyc_done_on, CAM.ntc, CAM.run_other_pd_loan, CAM.delay_other_loan_30_days, CAM.job_stability, CAM.city_category, CAM.salary_credit1, CAM.salary_credit1_date, CAM.salary_credit1_amount, CAM.salary_credit2, CAM.salary_credit2_date, CAM.salary_credit2_amount, CAM.salary_credit3, CAM.salary_credit3_date, CAM.salary_credit3_amount, CAM.next_pay_date, CAM.median_salary, CAM.borrower_age, CAM.eligible_foir_percentage, CAM.eligible_loan, CAM.loan_recommended, CAM.processing_fee_percent, CAM.roi, CAM.admin_fee, CAM.disbursal_date, CAM.repayment_date, CAM.adminFeeWithGST, CAM.total_admin_fee, CAM.tenure, CAM.net_disbursal_amount, CAM.repayment_amount, CAM.panel_roi, CAM.remark,  cam_created_by.name AS CAM_CREATED_BY, CAM.created_at,  cam_updated_by.name AS UPDATED_BY, CAM.updated_at, CAM.cam_sanction_letter_file_name, CAM.cam_sanction_letter_esgin_type_id, CAM.cam_sanction_letter_esgin_file_name, CAM.cam_sanction_letter_esgin_on, CAM.cam_sanction_letter_ip_address, CAM.cam_sanction_letter_esgin_count, CAM.cam_risk_profile, CAM.cam_risk_score, CAM.cam_advance_interest_amount, CAM.cam_appraised_obligations, CAM.cam_appraised_monthly_income, CAM.cam_blacklist_removed_flag, CAM.cam_sanction_remarks, L.recommended_amount, L.disburse_refrence_no, L.status, L.agrementRequestedDate, L.loanAgreementResponse, L.agrementUserIP, L.agrementResponseDate, L.mode_of_payment, L.channel,  loan_user_id.name AS LOAN_CREATED_BY, L.created_on,  loan_updated_by.name AS LOAN_UPDATED_BY, L.updated_on, L.loan_disbursement_payment_mode_id, L.loan_disbursement_payment_type_id, L.loan_noc_letter_sent_status, L.loan_noc_letter_sent_datetime, L.loan_noc_letter_sent_user_id, L.loan_bureau_report_flag, L.loan_principle_payable_amount, L.loan_interest_payable_amount, L.loan_penalty_payable_amount, L.loan_principle_received_amount, L.loan_interest_received_amount, L.loan_penalty_received_amount, L.loan_principle_discount_amount, L.loan_interest_discount_amount, L.loan_penalty_discount_amount, L.loan_principle_outstanding_amount, L.loan_interest_outstanding_amount, L.loan_penalty_outstanding_amount, L.loan_total_payable_amount, L.loan_total_received_amount, L.loan_total_discount_amount, L.loan_total_outstanding_amount, L.loan_closure_date, L.loan_settled_date, L.loan_writeoff_date, CE.employer_name, CE.emp_pincode, CE.emp_house, CE.emp_street, CE.emp_landmark, CE.emp_residence_since, CE.emp_designation, CE.emp_department, CE.emp_employer_type, CE.presentServiceTenure, CE.emp_website, CE.monthly_income, CE.income_type, CE.salary_mode, CE.emp_locality,  emp_state.m_state_name AS EMP_STATE,  emp_city.m_city_name AS EMP_CITY, CB.bank_name, CB.ifsc_code, CB.branch, CB.beneficiary_name, CB.account, CB.account_type, CB.account_status_id,  banking_created_by.name AS BANKING_CREATED_BY, CB.created_on,  banking_updated_by.name AS BANKING_UPDATED_BY, CB.updated_on ";

            $qry .= "FROM leads LD INNER JOIN lead_customer LC ON(LD.lead_id=LC.customer_lead_id) INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id) INNER JOIN loan L ON(LD.lead_id=L.lead_id) INNER JOIN customer_employment CE ON(LD.lead_id=CE.lead_id) LEFT JOIN customer_banking CB ON(LD.lead_id=CB.lead_id AND CB.account_status_id=1 AND CB.customer_banking_active=1) ";
            $qry .= "LEFT JOIN master_state lead_state ON(LD.state_id=lead_state.m_state_id) LEFT JOIN master_city lead_city ON(LD.city_id=lead_city.m_city_id) ";
            $qry .= "LEFT JOIN master_state curr_state ON(LC.state_id=curr_state.m_state_id) LEFT JOIN master_city curr_city ON(LC.city_id=curr_city.m_city_id) ";
            $qry .= "LEFT JOIN master_state emp_state ON(CE.state_id=emp_state.m_state_id) LEFT JOIN master_city emp_city ON(CE.city_id=emp_city.m_city_id) ";
            $qry .= "LEFT JOIN master_state aadhar_state ON(LC.aa_current_state_id=aadhar_state.m_state_id) LEFT JOIN master_city aadhar_city ON(LC.aa_current_city_id=aadhar_city.m_city_id) ";

            $qry .= "LEFT JOIN users lead_screener_assign_user_id ON(LD.lead_screener_assign_user_id=lead_screener_assign_user_id.user_id) ";
            $qry .= "LEFT JOIN users lead_credit_assign_user_id ON(LD.lead_credit_assign_user_id=lead_credit_assign_user_id.user_id) ";
            $qry .= "LEFT JOIN users lead_credithead_assign_user_id ON(LD.lead_credithead_assign_user_id=lead_credithead_assign_user_id.user_id) ";
            $qry .= "LEFT JOIN users lead_credit_approve_user_id ON(LD.lead_credit_approve_user_id=lead_credit_approve_user_id.user_id) ";
            $qry .= "LEFT JOIN users lead_disbursal_assign_user_id ON(LD.lead_disbursal_assign_user_id=lead_disbursal_assign_user_id.user_id) ";
            $qry .= "LEFT JOIN users lead_disbursal_approve_user_id ON(LD.lead_disbursal_approve_user_id=lead_disbursal_approve_user_id.user_id) ";
            $qry .= "LEFT JOIN users lead_rejected_user_id ON(LD.lead_rejected_user_id=lead_rejected_user_id.user_id) ";
            $qry .= "LEFT JOIN users cam_created_by ON(CAM.created_by=cam_created_by.user_id) ";
            $qry .= "LEFT JOIN users cam_updated_by ON(CAM.updated_by=cam_updated_by.user_id) ";
            $qry .= "LEFT JOIN users loan_user_id ON(L.user_id=loan_user_id.user_id) ";
            $qry .= "LEFT JOIN users loan_updated_by ON(L.updated_by=loan_updated_by.user_id) ";
            $qry .= "LEFT JOIN users banking_created_by ON(CB.created_by=banking_created_by.user_id) ";
            $qry .= "LEFT JOIN users banking_updated_by ON(CB.updated_by=banking_updated_by.user_id) ";

            $qry .= "LEFT JOIN master_status ON(LD.lead_status_id=master_status.status_id) ";

            $qry .= "LEFT JOIN master_religion ON(LC.customer_religion_id=master_religion.religion_id) ";

            $qry .= "LEFT JOIN master_branch ON(LD.lead_branch_id=master_branch.m_branch_id) ";
            $qry .= "WHERE LD.lead_id=LC.customer_lead_id AND LD.lead_active=1 AND LD.lead_id=CAM.lead_id AND LD.lead_id=L.lead_id AND LD.lead_status_id IN(14) AND LD.user_type!='REPEAT' AND L.loan_status_id=14 AND LD.lead_final_disbursed_date >= '$fromDate' AND LD.lead_final_disbursed_date <= '$toDate' AND LD.lead_id=CE.lead_id ";

            $result = $this->db->query($qry);

            //             print_r($this->db->last_query());
            //             exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVRefernceDetailsModel($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $qry = "SELECT L.lead_id, L.loan_no, LCR.lcr_name, LCR.lcr_mobile, MRT.mrt_name FROM lead_customer_references LCR INNER JOIN master_relation_type MRT ON(LCR.lcr_relationType=MRT.mrt_id) INNER JOIN credit_analysis_memo CAM ON(LCR.lcr_lead_id=CAM.lead_id) INNER JOIN loan L ON(LCR.lcr_lead_id=L.lead_id) ";
            $qry .= "WHERE LCR.lcr_active=1 AND CAM.disbursal_date >='$fromDate' AND CAM.disbursal_date <='$toDate' ORDER BY L.lead_id ASC ";

            $result = $this->db->query($qry);

            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportBRERulesResultModel($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $qry = "SELECT MBRE.m_bre_cat_name, LBRR.* FROM lead_bre_rule_result LBRR INNER JOIN master_bre_category MBRE ON(LBRR.lbrr_rule_id=MBRE.m_bre_cat_id) ";
            $qry .= "WHERE LBRR.lbrr_active=1 AND date(LBRR.lbrr_created_on) >= '$fromDate' AND date(LBRR.lbrr_created_on) <= '$toDate'";

            $result = $this->db->query($qry);

            // print_r($this->db->last_query());
            // exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportSalariedBorrowersModel($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $qry = "SELECT LD.lead_id, LC.first_name, LC.sur_name, CE.emp_pincode, LC.mobile, LD.lead_reference_no, LD.email, LD.pancard, LC.aadhar_no, CE.monthly_income, LC.dob, LC.current_residence_type, LC.current_house, LC.current_locality, LC.cr_residence_pincode, LC.current_residence_since, LC.aa_current_house, LC.aa_current_locality, LC.aa_cr_residence_pincode, CE.income_type, CE.salary_mode, LD.obligations, CAM.loan_recommended, CAM.tenure, CAM.roi, LD.purpose, CE.presentServiceTenure  ";
            $qry .= " , LC.father_name,  LC.gender, CE.employer_name, CONCAT_WS(' ', LC.aa_current_house, LC.aa_current_locality, LC.aa_current_landmark, aadhar_city.m_city_name, aadhar_state.m_state_name) as aadhar_address, CONCAT_WS(' ', LC.current_house, LC.current_locality, LC.current_landmark, curr_city.m_city_name, curr_state.m_state_name) as current_address, CONCAT_WS(' ', CE.emp_house, CE.emp_street, CE.emp_landmark, emp_city.m_city_name, emp_state.m_state_name) as office_address, CE.emp_employer_type, CE.emp_designation, CE.emp_residence_since, CB.beneficiary_name, CB.ifsc_code, CB.bank_name, CB.account, CB.confirm_account, CB.account_type ";
            //            $qry .= "FROM leads LD INNER JOIN lead_customer LC ON(LD.lead_id=LC.customer_lead_id) INNER JOIN customer_employment CE ON(LD.lead_id=CE.lead_id) LEFT JOIN lead_customer_references LCR ON(LD.lead_id=LCR.lcr_lead_id) ";
            $qry .= "FROM leads LD INNER JOIN lead_customer LC ON(LD.lead_id=LC.customer_lead_id) INNER JOIN customer_employment CE ON(LD.lead_id=CE.lead_id) INNER JOIN credit_analysis_memo CAM ON(LD.lead_id=CAM.lead_id)  ";
            $qry .= "LEFT JOIN customer_banking CB ON(LD.lead_id=CB.lead_id AND CB.account_status_id=1)  ";
            $qry .= "LEFT JOIN master_city as emp_city ON(CE.city_id=emp_city.m_city_id) LEFT JOIN master_state as emp_state ON(CE.state_id=emp_state.m_state_id) ";
            $qry .= "LEFT JOIN master_city as curr_city ON(LC.current_city=curr_city.m_city_id) LEFT JOIN master_state as curr_state ON(LC.current_state=curr_state.m_state_id) ";
            $qry .= "LEFT JOIN master_city as aadhar_city ON(LC.current_city=aadhar_city.m_city_id) LEFT JOIN master_state as aadhar_state ON(LC.current_state=aadhar_state.m_state_id) ";
            $qry .= "WHERE LD.lead_active=1 AND DATE(LD.lead_credit_approve_datetime) >= '$fromDate' AND DATE(LD.lead_credit_approve_datetime) <= '$toDate' AND LD.lead_status_id IN(14,16,17,18,19)";

            $result = $this->db->query($qry);

            //             print_r($this->db->last_query());
            //             exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportICICIBANKModel($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));
            //$qry = "SELECT LD.mobile,LD.email,L.loan_no,L.disburse_refrence_no,L.mode_of_payment,L.channel,CAM.net_disbursal_amount, CB.beneficiary_name, CB.account, CB.ifsc_code , (SELECT disb_bank_account_no FROM master_disbursement_banks WHERE disb_bank_id=1 AND disb_bank_active=1) AS company_disb_account_no FROM loan L INNER JOIN leads LD ON (L.lead_id = LD.lead_id) INNER JOIN customer_banking CB ON (L.lead_id = CB.lead_id) INNER JOIN credit_analysis_memo CAM ON (CAM.lead_id = LD.lead_id) WHERE CAM.cam_active AND LD.lead_active = 1 AND L.loan_active = 1 AND LD.lead_disbursal_recommend_datetime IS NOT NULL AND LD.lead_disbursal_recommend_datetime IS NOT NULL AND CB.account_status_id=1 AND CB.customer_banking_active=1 AND DATE(LD.lead_disbursal_recommend_datetime) >= '$fromDate' AND DATE(LD.lead_disbursal_recommend_datetime) <= '$toDate' GROUP BY L.loan_no";
            $qry = "SELECT LD.mobile,LD.email,L.loan_no,L.disburse_refrence_no,L.mode_of_payment,L.channel,CAM.net_disbursal_amount, CB.beneficiary_name, CB.account, CB.ifsc_code , (SELECT disb_bank_account_no FROM master_disbursement_banks WHERE disb_bank_id=1 AND disb_bank_active=1) AS company_disb_account_no FROM loan L INNER JOIN leads LD ON (L.lead_id = LD.lead_id) INNER JOIN customer_banking CB ON (L.lead_id = CB.lead_id) INNER JOIN credit_analysis_memo CAM ON (CAM.lead_id = LD.lead_id) WHERE CAM.cam_active AND LD.lead_active = 1 AND L.loan_active = 1 AND L.loan_status_id = 13 AND LD.lead_status_id = 13 AND LD.lead_disbursal_recommend_datetime IS NOT NULL AND LD.lead_disbursal_recommend_datetime IS NOT NULL AND CB.account_status_id=1 AND CB.customer_banking_active=1 AND DATE(LD.lead_disbursal_recommend_datetime) >= '$fromDate' AND DATE(LD.lead_disbursal_recommend_datetime) <= '$toDate' GROUP BY L.loan_no";
            //$qry = "SELECT LD.lead_id, LD.email, LD.mobile,CB.bank_name,CB.ifsc_code,CB.branch,CB.beneficiary_name,CB.account,LN.loan_no,LN.status,LN.mode_of_payment,LN.channel,LN.disburse_refrence_no,LN.company_account_no,LN.loan_disbursement_trans_status_datetime";
            //$qry .= " FROM leads LD INNER JOIN customer_banking CB ON(LD.lead_id=CB.lead_id) INNER JOIN loan LN ON(LD.lead_id=LN.lead_id)";
            //$qry .= "WHERE LD.lead_active=1 AND DATE(LD.lead_credit_approve_datetime) >= '$fromDate' AND DATE(LD.lead_credit_approve_datetime) <= '$toDate'";
            $result = $this->db->query($qry);
            //print_r($this->db->last_query());
            //exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportLegalNoticeSentLogModel($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $qry = "SELECT LD.first_name, LD.pancard, LLNL.*, U.name as sent_by, CONCAT_WS(' ', LC.aa_current_house, LC.aa_current_locality, LC.aa_current_landmark, MS.m_state_name, MC.m_city_name, LC.aa_cr_residence_pincode) as  aadhaar_address, LD.mobile ";
            $qry .= "FROM loan_legal_notice_logs LLNL INNER JOIN leads LD ON(LLNL.legal_notice_lead_id=LD.lead_id) INNER JOIN users U ON(LLNL.legal_notice_user_id=U.user_id) INNER JOIN lead_customer LC ON(LLNL.legal_notice_lead_id=LC.customer_lead_id) ";
            $qry .= "INNER JOIN master_state MS ON(LC.aa_current_state_id=MS.m_state_id) INNER JOIN master_city MC ON(LC.aa_current_city_id=MC.m_city_id) ";
            $qry .= "WHERE LLNL.legal_notice_active=1 AND LLNL.legal_notice_api_status_id=1 AND LLNL.legal_notice_sent_status=1  AND DATE(legal_notice_sent_datetime) >= '$fromDate' AND DATE(legal_notice_sent_datetime) <= '$toDate' ";

            $result = $this->db->query($qry);
            //print_r($this->db->last_query());
            //exit;
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportDibsursalAccountModel($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $sql = "SELECT LD.lead_id, LD.lead_status_id, LD.created_on, LD.loan_no, CONCAT_WS(' ', LC.first_name, LC.middle_name, LC.sur_name) AS customer_name,";
            $sql .= " CAM.loan_recommended AS sanction_loan_amount, CAM.processing_fee_percent AS sanction_processing_fee, CAM.admin_fee AS total_admin_fees,";
            $sql .= " CAM.adminFeeWithGST AS processing_fee, CAM.total_admin_fee AS net_admin_fees, CAM.disbursal_date, CAM.repayment_date, CAM.repayment_amount,";
            $sql .= " MS.m_state_name AS state_name, LC.state_id,";
            $sql .= " IF(LC.state_id = 10, 0, (CAM.admin_fee * 0.152542372881356)) AS IGST, ";
            $sql .= " IF(LC.state_id <> 10, 0, (CAM.admin_fee * 0.076271186440678)) AS CGST, ";
            $sql .= " IF(LC.state_id <> 10, 0, (CAM.admin_fee * 0.076271186440678)) AS SGST ";
            $sql .= " FROM  leads LD ";
            $sql .= "INNER JOIN lead_customer LC ON LD.lead_id = LC.customer_lead_id ";
            $sql .= "INNER JOIN credit_analysis_memo CAM ON CAM.lead_id = LD.lead_id ";
            $sql .= "INNER JOIN loan L ON L.lead_id = LD.lead_id ";
            $sql .= "INNER JOIN master_state MS ON MS.m_state_id = LC.state_id ";
            $sql .= " WHERE L.loan_status_id=14 AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate' AND LD.lead_status_id IN(14,16,17,18,19) ";
            $sql .= "AND LD.lead_active=1 AND CAM.cam_active=1 AND L.loan_active=1 ORDER BY CAM.disbursal_date ASC";

            $result = $this->db->query($sql);

            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportMasterDisbursalModel($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $sql = "SELECT LD.lead_id, LD.lead_status_id, LD.user_type, LD.created_on, LD.loan_no, CONCAT_WS(' ', LC.first_name, LC.middle_name, LC.sur_name) AS customer_name,";
            $sql .= "CAM.loan_recommended AS sanction_loan_amount, CAM.processing_fee_percent AS sanction_processing_fee, CAM.admin_fee AS total_admin_fees, LD.pancard, ";
            $sql .= "CAM.adminFeeWithGST AS processing_fee, CAM.total_admin_fee AS net_admin_fees, CAM.disbursal_date, CAM.repayment_date, CAM.repayment_amount, CAM.tenure, CAM.final_foir_percentage, ";
            $sql .= "MS.m_state_name AS state_name, LD.state_id, US.name AS sanction_by, ";
            $sql .= "IF(LD.state_id = 10, 0, (CAM.admin_fee * 0.152542372881356)) AS IGST, ";
            $sql .= "IF(LD.state_id <> 10, 0, (CAM.admin_fee * 0.076271186440678)) AS CGST, ";
            $sql .= "IF(LD.state_id <> 10, 0, (CAM.admin_fee * 0.076271186440678)) AS SGST ";
            $sql .= " FROM  leads LD ";
            $sql .= "INNER JOIN lead_customer LC ON LD.lead_id = LC.customer_lead_id ";
            $sql .= "INNER JOIN credit_analysis_memo CAM ON CAM.lead_id = LD.lead_id ";
            $sql .= "INNER JOIN loan L ON L.lead_id = LD.lead_id ";
            $sql .= "INNER JOIN master_state MS ON MS.m_state_id = LD.state_id ";
            $sql .= "LEFT JOIN users US ON US.user_id = LD.lead_credit_assign_user_id";
            $sql .= " WHERE L.loan_status_id=14 AND CAM.disbursal_date >= '$fromDate' AND CAM.disbursal_date <= '$toDate' AND LD.lead_status_id IN(14,16,17,18,19) ";
            $sql .= "AND LD.lead_active=1 AND CAM.cam_active=1 AND L.loan_active=1 ORDER BY CAM.disbursal_date ASC";

            $result = $this->db->query($sql);

            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }
    public function ExportAuditTatModel($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $sql = "SELECT
                        LD.lead_id,
                        LD.loan_no,
                        LD.user_type,
                        LD.status,
                        LD.lead_credit_assign_datetime,
                        LD.lead_credit_approve_datetime,
                        LD.lead_audit_assign_date_time,
                        TIME_FORMAT(TIMEDIFF(LD.lead_credit_recommend_datetime, LD.lead_credit_assign_datetime), '%H:%i:%s') AS credit_manager_tat,
                        UR.name AS credit_manager_name,
                        TIME_FORMAT(TIMEDIFF(LD.lead_credithead_assign_datetime, LD.lead_credit_recommend_datetime), '%H:%i:%s') AS credit_head_tat,
                        UR2.name AS credit_head_name,
                        TIME_FORMAT(TIMEDIFF(LD.lead_credit_approve_datetime, LD.lead_audit_assign_date_time), '%H:%i:%s') AS audit_tat,
                        UR3.name AS audit_name,
                        TIME_FORMAT(TIMEDIFF(LD.lead_disbursal_approve_datetime, LD.lead_audit_assign_date_time), '%H:%i:%s') AS disbursal_tat,
                        UR4.name AS disbursal_name
                    FROM
                        leads LD
                    LEFT JOIN
                        users UR ON LD.lead_credit_assign_user_id = UR.user_id
                    LEFT JOIN
                        users UR2 ON LD.lead_credithead_assign_user_id = UR2.user_id
                    LEFT JOIN
                        users UR3 ON LD.lead_audit_assign_user_id = UR3.user_id
                    LEFT JOIN
                        users UR4 ON LD.lead_disbursal_assign_user_id = UR4.user_id";

            $sql .= " WHERE LD.lead_final_disbursed_date BETWEEN '$fromDate' and '$toDate' AND LD.user_type = 'NEW' AND LD.lead_status_id IN(14,16,17,18,19) ";
            $sql .= "ORDER BY audit_tat DESC ";

            $result = $this->db->query($sql);
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function ExportClosedLoanModel($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {

            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $qry = "SELECT LD.lead_id, CONCAT_WS(' ', LC.first_name, LC.middle_name, LC.sur_name) AS name, LD.pancard, LD.mobile,CAM.loan_recommended,LD.status, CAM.disbursal_date,CAM.repayment_date, loan.loan_closure_date,TIMESTAMPDIFF(DAY, CAM.repayment_date,loan.loan_closure_date) as DPD";
            $qry .= "  FROM leads LD INNER JOIN lead_customer LC ON LD.lead_id = LC.customer_lead_id";
            $qry .= "  INNER JOIN credit_analysis_memo CAM ON LD.lead_id = CAM.lead_id";
            $qry .= "  INNER JOIN loan ON LD.lead_id=loan.lead_id";
            $qry .= "  INNER JOIN ( SELECT pancard, MAX(lead_id) AS max_lead_id FROM leads WHERE lead_status_id = 16 GROUP BY pancard
					) maxLeadID ON LD.pancard = maxLeadID.pancard AND LD.lead_id = maxLeadID.max_lead_id";
            $qry .= " WHERE CAM.repayment_date >= '$fromDate' AND LD.lead_status_id = 16 AND LD.pancard NOT IN ( SELECT pancard FROM leads WHERE lead_status_id IN (14, 17, 18, 19))";
            $qry .= "ORDER BY LD.lead_id DESC";

            $result = $this->db->query($qry);

            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVReloanTatModel($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {

            // Convert dates to Y-m-d format
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $sql = "SELECT
                    LD.lead_id,
                    LD.loan_no,
                    LD.first_name,
                    LD.pancard,
                    LD.mobile,
                    LD.status
                FROM
                    leads LD
                    INNER JOIN collection AS C ON C.lead_id = LD.lead_id
                WHERE
                    LD.lead_status_id = 16
                    AND DATE(C.closure_payment_updated_on) >= '$fromDate' AND DATE(C.closure_payment_updated_on) <= '" . $toDate . "'
                    AND NOT EXISTS (
                        SELECT
                            1
                        FROM
                            leads LD2
                        WHERE
                            LD.pancard = LD2.pancard
                            AND LD2.lead_status_id NOT IN (16, 9)
                    )
                    AND NOT EXISTS (
                        SELECT
                            1
                        FROM
                            customer_black_list BL
                        WHERE
                            BL.bl_customer_pancard = LD.pancard
                    )
                GROUP BY
                    LD.lead_id ";
            $result = $this->db->query($sql);

            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVLowConversionTatReportModel($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $sql = "SELECT
                        U.name,
                        COUNT(LD.LEAD_ID) AS TOTAL_LEAD,
                        COUNT(CASE WHEN LD.status = 2 THEN 1 END) AS LEAD_INPROCESS,
                        COUNT(CASE WHEN LD.status = 3 THEN 1 END) AS LEAD_HOLD,
                        COUNT(CASE WHEN LD.status = 5 THEN 1 END) AS APPLICATION_INPROCESS,
                        COUNT(CASE WHEN LD.status = 9 THEN 1 END) AS REJECT,
                        COUNT(CASE WHEN LD.status = 14 THEN 1 END) AS DISBURSED,
                        (COUNT(CASE WHEN LD.status = 14 THEN 1 END) * 100.0) / COUNT(LD.LEAD_ID) AS DISBURSEMENT_PERCENTAGE
                    FROM
                        leads AS LD
                    INNER JOIN
                        users AS U ON LD.lead_screener_assign_user_id = U.user_id
                    WHERE
                        LD.created_on >= '" . $fromDate . "' AND LD.created_on <= '" . $toDate . "'
                        AND LD.user_type = 'new' AND
                        U.user_allocation_type_id=1
                        AND LD.lead_screener_assign_user_id IS NOT NULL
                        AND LD.lead_active = 1
                    GROUP BY
                        U.name";

            $result = $this->db->query($sql);
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportCSVHighConversionTatReportModel($fromDate, $toDate) {
        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = date('Y-m-d', strtotime($fromDate));
            $toDate = date('Y-m-d', strtotime($toDate));

            $sql = "SELECT
                        U.name,
                        COUNT(LD.LEAD_ID) AS TOTAL_LEAD,
                        COUNT(CASE WHEN LD.status = 2 THEN 1 END) AS LEAD_INPROCESS,
                        COUNT(CASE WHEN LD.status = 3 THEN 1 END) AS LEAD_HOLD,
                        COUNT(CASE WHEN LD.status = 5 THEN 1 END) AS APPLICATION_INPROCESS,
                        COUNT(CASE WHEN LD.status = 9 THEN 1 END) AS REJECT,
                        COUNT(CASE WHEN LD.status = 14 THEN 1 END) AS DISBURSED,
                        (COUNT(CASE WHEN LD.status = 14 THEN 1 END) * 100.0) / COUNT(LD.LEAD_ID) AS DISBURSEMENT_PERCENTAGE
                    FROM
                        leads AS LD
                    INNER JOIN
                        users AS U ON LD.lead_screener_assign_user_id = U.user_id
                    WHERE
                        LD.created_on >= '" . $fromDate . "' AND LD.created_on <= '" . $toDate . "'
                        AND LD.user_type = 'new' AND
                        U.user_allocation_type_id=2
                        AND LD.lead_screener_assign_user_id IS NOT NULL
                        AND LD.lead_active = 1
                    GROUP BY
                        U.name";

            $result = $this->db->query($sql);
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }

    public function exportLeadInteractionSummary($from_date, $to_date) {

        if (!empty($from_date) && !empty($to_date)) {
            $fromDate = date('Y-m-d', strtotime($from_date));
            $toDate = date('Y-m-d', strtotime($to_date));

            $sql = "SELECT
                        LD.lead_id,
                        LD.utm_source,
                        LD.source,
                        LD.utm_campaign,
                        LD.monthly_salary_amount,
                        LD.status,
                        U.name AS first_assignment_user_name,
                        MIN(LF.created_on) AS first_assignment_date,
                        U2.name AS screener_user_name,
                        LD.lead_screener_assign_datetime,
                        U3.name AS credit_user_name,
                        LD.lead_credit_assign_datetime,
                        U4.name AS credithead_user_name,
                        LD.lead_credithead_assign_datetime,
                        U6.name AS audit_user_name,
                        LD.lead_audit_assign_date_time,
                        U5.name AS SanctionBy,
                        LD.lead_credit_approve_datetime,
                        U7.name AS disbursal_user_name,
                        LD.lead_disbursal_assign_datetime,
                        U8.name AS disbursal_approve_user_name,
                        LD.lead_disbursal_approve_datetime,
                        CAM.disbursal_date,
                        CAM.repayment_date,
                        CAM.loan_recommended,
                        CAM.repayment_amount, LD.user_type
                    FROM
                        leads AS LD
                        LEFT JOIN credit_analysis_memo CAM ON (LD.lead_id = CAM.lead_id)
                        LEFT JOIN lead_followup AS LF ON (
                            LD.lead_id = LF.lead_id
                            AND LF.lead_followup_status_id = 2
                        )
                        LEFT JOIN users AS U ON (LF.user_id = U.user_id)
                        LEFT JOIN users AS U2 ON LD.lead_screener_assign_user_id = U2.user_id
                        LEFT JOIN users AS U3 ON LD.lead_credit_assign_user_id = U3.user_id
                        LEFT JOIN users AS U4 ON LD.lead_credithead_assign_user_id = U4.user_id
                        LEFT JOIN users AS U5 ON LD.lead_credit_approve_user_id = U5.user_id
                        LEFT JOIN users AS U6 ON LD.lead_audit_assign_user_id = U6.user_id
                        LEFT JOIN users AS U7 ON LD.lead_disbursal_assign_user_id = U7.user_id
                        LEFT JOIN users AS U8 ON LD.lead_disbursal_approve_user_id = U8.user_id
                    WHERE
                        LD.lead_entry_date >= '$fromDate'
                        AND LD.lead_entry_date <= '$toDate'
                        AND LD.lead_status_id != 8
                    GROUP BY
                        LD.lead_id
                    ORDER BY
                        LD.lead_entry_date ASC";

            $result = $this->db->query($sql);
            return $result;
        } else {
            return redirect(base_url('exportData/'), 'refresh');
        }
    }
}
