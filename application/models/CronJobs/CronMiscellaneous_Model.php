<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/models/CronJobs/CronCommon_Model.php';

class CronMiscellaneous_Model extends CronCommon_Model {

//    public function insert($table, $data) {
//        $this->db->insert($table, $data);
//        return $this->db->insert_id();
//    }
//
//    public function update($table, $conditions, $data) {
//        return $this->db->where($conditions)->update($table, $data);
//    }

    public function old_get_customer_loan($request_data) {
        $return_array = ['status' => 0];

        $disbursal_start_date = $request_data['start_date'];
        $disbursal_end_date = $request_data['end_date'];

        $sql = "SELECT DISTINCT LD.lead_id, LD.loan_no, LD.pancard , L.loan_id";
        $sql .= " FROM leads LD";
        $sql .= " INNER JOIN lead_customer LC ON(LD.lead_id = LC.customer_lead_id AND LC.customer_active=1 AND LC.customer_deleted=0)";
        $sql .= " INNER JOIN credit_analysis_memo CAM ON(LD.lead_id = CAM.lead_id AND CAM.cam_active=1 AND CAM.cam_deleted=0)";
        $sql .= " INNER JOIN loan L ON(L.lead_id = LD.lead_id AND L.loan_active=1 AND L.loan_deleted=0)";
        $sql .= " WHERE L.loan_status_id=14 AND LD.source NOT IN ('C4C', 'REFCASE') AND CAM.disbursal_date >= '$disbursal_start_date' AND CAM.disbursal_date <= '$disbursal_end_date'";
//        echo $sql;
        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $return_array['status'] = 1;
            $return_array['loan'] = $tempDetails->result_array();
        }

        return $return_array;
    }

    public function get_customer_loan() {

        $return_array = ['status' => 0];

        $sql = "SELECT DISTINCT LD.lead_id, L.loan_no, LD.pancard , L.loan_id";
        $sql .= " FROM leads LD";
        $sql .= " INNER JOIN lead_customer LC ON(LD.lead_id = LC.customer_lead_id AND LC.customer_active=1 AND LC.customer_deleted=0)";
        $sql .= " INNER JOIN credit_analysis_memo CAM ON(LD.lead_id = CAM.lead_id AND CAM.cam_active=1 AND CAM.cam_deleted=0)";
        $sql .= " INNER JOIN loan L ON(L.lead_id = LD.lead_id AND L.loan_active=1 AND L.loan_deleted=0)";
        $sql .= " WHERE L.loan_status_id=14";
        $sql .= " AND L.loan_no IN(SELECT kyc_loan_no FROM test_kyc_loan WHERE kyc_loan_done=0)";

//        echo $sql;
        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $return_array['status'] = 1;
            $return_array['loan'] = $tempDetails->result_array();
        }

        return $return_array;
    }

    public function old_get_loans_kyc_documents($request_data) {

        $return_array = ['status' => 0];
        $pancard = $request_data['pancard'];
        $lead_id = $request_data['lead_id'];

        $sql = "SELECT D.lead_id, D.docs_id, D.docs_type, D.sub_docs_type, D.docs_master_id, D.file";
        $sql .= " FROM docs D";
        $sql .= " WHERE (TRIM(D.pancard) = '$pancard' OR D.lead_id=$lead_id) AND D.docs_active=1 AND D.docs_deleted=0";
        $sql .= " ORDER BY D.docs_id DESC";

        $tempDetails = $this->db->query($sql);
        if (!empty($tempDetails->num_rows())) {
            $return_array['status'] = 1;
            $return_array['docs'] = $tempDetails->result_array();
        }

        return $return_array;
    }

    public function get_loans_kyc_documents($request_data) {

        $return_array = ['status' => 0];
        $pancard = $request_data['pancard'];
        $lead_id = $request_data['lead_id'];

        $sql = "SELECT D.lead_id, D.docs_id, D.docs_type, D.sub_docs_type, D.file";
        $sql .= " FROM docs D";
        $sql .= " WHERE (TRIM(D.pancard) = '$pancard' OR D.lead_id=$lead_id) AND D.docs_active=1 AND D.docs_deleted=0";
        $sql .= " ORDER BY D.docs_id DESC";

        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $return_array['status'] = 1;
            $return_array['docs'] = $tempDetails->result_array();
        }

        return $return_array;
    }

    public function get_loan_list($disbursal_start_date, $disbursal_end_date) {

        $return_array = ['status' => 0];

        $sql = "SELECT DISTINCT D.lead_id, LD.loan_no, LD.pancard,D.docs_id,D.docs_master_id ";
        $sql .= " FROM leads LD";
        $sql .= " INNER JOIN lead_customer LC ON(LD.lead_id = LC.customer_lead_id AND LC.customer_active=1 AND LC.customer_deleted=0)";
        $sql .= " INNER JOIN credit_analysis_memo CAM ON(LD.lead_id = CAM.lead_id AND CAM.cam_active=1 AND CAM.cam_deleted=0)";
        $sql .= " INNER JOIN loan L ON(L.lead_id = LD.lead_id AND L.loan_active=1 AND L.loan_deleted=0)";
        $sql .= " INNER JOIN docs D ON(D.pancard = LD.pancard OR D.lead_id = LD.lead_id)";
        $sql .= " WHERE L.loan_status_id=14 AND LD.source NOT IN ('C4C', 'REFCASE') AND CAM.disbursal_date >= '" . $disbursal_start_date . "' AND CAM.disbursal_date <= '" . $disbursal_end_date . "'";
        $sql .= " AND D.docs_master_id IN(1,2,3) AND D.docs_aadhaar_masked!=1 AND D.docs_id NOT IN(SELECT poi_ocr_doc_id_1 FROM api_poi_ocr_logs WHERE poi_ocr_method_id=3 AND poi_ocr_doc_id_1 >0)";

        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $return_array['status'] = 1;
            $return_array['loan'] = $tempDetails->result_array();
        }

        return $return_array;
    }

    public function get_aadhaar_docs() {

        $return_array = ['status' => 0, 'docs' => array()];

        $sql = "SELECT D.lead_id, D.docs_id, D.docs_type, D.sub_docs_type, D.docs_master_id, D.file";
        $sql .= " FROM docs D";
        $sql .= " WHERE D.lead_id=9621 AND D.docs_master_id IN(1,2,3) AND D.docs_aadhaar_masked!=1 AND D.docs_active=1 AND D.docs_deleted=0 AND D.created_on > '2022-05-25'";
        $sql .= " AND D.docs_id NOT IN(SELECT poi_ocr_doc_id_1 FROM api_poi_ocr_logs WHERE poi_ocr_method_id=3 AND poi_ocr_doc_id_1 > 0)";
        $sql .= " ORDER BY D.docs_id DESC";


        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $return_array['status'] = 1;
            $return_array['docs'] = $tempDetails->result_array();
        }

        return $return_array;
    }

    public function get_all_documents() {

        $return_array = ['status' => 0];

        $sql = "SELECT docs_id, lead_id, file";
        $sql .= " FROM docs";
        $sql .= " WHERE docs_active=1 AND docs_deleted=0";
        $sql .= " ORDER BY docs_id DESC";

        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $return_array['status'] = 1;
            $return_array['docs'] = $tempDetails->result_array();
        }

        return $return_array;
    }

    public function get_all_collection_documents() {

        $return_array = ['status' => 0];

        $sql = "SELECT id, lead_id, loan_no, docs";
        $sql .= " FROM collection WHERE lead_id IN(430649,411852,427688,412701,413558,408140,430156,407560,430242,408072,408675,419240,408165,408467,406243,383661,418390,430737,430726,409435,429990,394839,411866,406943,412600,419152,407595,399664,429965,430284,426486,420474,430045,430274,408092,430676,430814,409190,409031,429007,12214,410198,376901,420541,430326,427191,424725,408663,409555,412573,423279,411179,408331,409695,430024,413083,413156,419132,406894,407501)";
        $sql .= " ORDER BY id DESC";

        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $return_array['status'] = 1;
            $return_array['colldocs'] = $tempDetails->result_array();
        }

        return $return_array;
    }
    
    public function insert($data = null, $table = null) {
        return $this->db->insert($table, $data);
    }

    public function select($conditions = null, $data = null, $table = null) {
        return $this->db->select($data)->where($conditions)->from($table)->get();
    }

    public function update($table, $conditions, $data) {
        return $this->db->where($conditions)->update($table, $data);
    }
    
}

?>
