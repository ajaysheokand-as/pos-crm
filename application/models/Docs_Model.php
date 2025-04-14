<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Docs_Model extends CI_Model {

    private $table = 'docs';
    private $table_collection = 'collection';
    private $table_docs_master = 'docs_master';
    private $table_docs_download_logs = 'docs_download_logs';
    private $table_loan_collection_followup = 'loan_collection_followup';
    private $table_loan_collection_visit = ' loan_collection_visit';

    public function docs_type_master() {

        return $this->db->select('distinct(docs_type), docs_required')->from($this->table_docs_master)->get();
    }

    public function getDocumentSubType($docs_type) {

        return $this->db->select('docs_type, docs_sub_type,id')->where(['docs_type' => $docs_type, 'document_active' => 1, 'document_deleted' => 0])->from($this->table_docs_master)->get();
    }

    public function index($limit = null, $order_by = null) {

        return $this->db->select('*')->from($this->table)->limit($limit)->order_by($order_by)->get();
    }

    public function select($conditions, $data = null) {

        return $this->db->select($data)->where($conditions)->from($this->table)->get();
    }

    public function insert($data) {

        return $this->db->insert($this->table, $data);
    }

    public function update($conditions, $data) {

        return $this->db->where($conditions)->update($this->table, $data);
    }

    public function delete($conditions) {

        return $this->db->where($conditions)->delete($this->table);
    }

    public function join_table($conditions = null, $data = null, $table2 = null, $table3 = null) {

        return $this->db->select($data)
            ->where($conditions)
            ->from($this->table . ' LD')
            ->join($table2, 'DS.lead_id = LD.lead_id')
            ->join($table3, 'ST.state_id = LD.state_id')
            ->get();
    }

    public function getDocumentDetails($doc_id) {

        $return_array = array("status" => 0, "doc_data" => array());

        if (!empty($doc_id)) {

            $temDetails = $this->db->select('*')->where(['docs_id' => $doc_id])->from($this->table)->get();

            if ($temDetails->num_rows() > 0) {
                $return_array['status'] = 1;
                $return_array['doc_data'] = $temDetails->row_array();
            }
        }

        return $return_array;
    }

    public function getCollectionDocumentDetails($collection_id) {

        $return_array = array("status" => 0, "doc_data" => array());

        if (!empty($collection_id)) {

            $temDetails = $this->db->select('*')->where(['id' => $collection_id])->from($this->table_collection)->get();

            if ($temDetails->num_rows() > 0) {
                $return_array['status'] = 1;
                $return_array['doc_data'] = $temDetails->row_array();
            }
        }

        return $return_array;
    }

    public function getDocumentMasterById($doc_id) {

        return $this->db->select('docs_type, docs_sub_type,id')->where('id', $doc_id)->from($this->table_docs_master)->get();
    }

    public function getLeadDocumentWithTypeDetails($lead_id, $document_type_id = "") {

        $return_array = array("status" => 0, "doc_data" => array());

        if (!empty($lead_id) && !empty($document_type_id)) {

            $sql = "SELECT D.docs_id, D.docs_master_id, D.file";
            $sql .= " FROM leads LD";
            $sql .= " INNER JOIN lead_customer C  ON (C.customer_lead_id = LD.lead_id AND C.customer_active=1 AND C.customer_deleted=0)";
            $sql .= " INNER JOIN docs D  ON (D.lead_id = LD.lead_id)";
            $sql .= " WHERE LD.lead_id = $lead_id AND LD.lead_active=1 AND LD.lead_deleted=0";
            $sql .= " AND D.docs_master_id IN($document_type_id) AND D.docs_active=1 AND D.docs_deleted=0";
            $sql .= " ORDER BY D.docs_id DESC";

            $documentResult = $this->db->query($sql);

            if ($documentResult->num_rows() > 0) {
                $return_array['status'] = 1;
                $return_array['doc_data'] = $documentResult->row_array();
            }
        }

        return $return_array;
    }

    public function getSanctionLetterPdf($lead_id, $document_type_id = "") {

        $return_array = array("status" => 0, "doc_data" => array());

        if (!empty($lead_id)) {

            $sql = "SELECT cam_sanction_letter_file_name,cam_sanction_letter_esgin_type_id,cam_sanction_letter_esgin_file_name,cam_sanction_letter_esgin_on,cam_sanction_letter_ip_address";
            $sql .= " FROM leads LD";
            $sql .= " INNER JOIN lead_customer C ON C.customer_lead_id = LD.lead_id";
            $sql .= " INNER JOIN credit_analysis_memo CAM ON(CAM.lead_id = LD.lead_id)";
            $sql .= " WHERE LD.lead_id=$lead_id";

            $documentResult = $this->db->query($sql);

            if ($documentResult->num_rows() > 0) {
                $return_array['status'] = 1;
                $return_array['doc_data'] = $documentResult->row_array();
            }
        }

        return $return_array;
    }



    public function getCollectionFollowupDocumentDetails($lcf_id) {

        $return_array = array("status" => 0, "doc_data" => array());

        if (!empty($lcf_id)) {

            $temDetails = $this->db->select('*')->where(['lcf_id' => $lcf_id])->from($this->table_loan_collection_followup)->get();

            if ($temDetails->num_rows() > 0) {
                $return_array['status'] = 1;
                $return_array['doc_data'] = $temDetails->row_array();
            }
        }

        return $return_array;
    }

    public function getCollectionVisitDocumentDetails($col_visit_id) {

        $return_array = array("status" => 0, "doc_data" => array());

        if (!empty($col_visit_id)) {

            $temDetails = $this->db->select('*')->where(['col_visit_id' => $col_visit_id])->from($this->table_loan_collection_visit)->get();

            if ($temDetails->num_rows() > 0) {
                $return_array['status'] = 1;
                $return_array['doc_data'] = $temDetails->row_array();
            }
        }

        return $return_array;
    }

    public function insertDocumentDownloadLogs($data) {

        return $this->db->insert($this->table_docs_download_logs, $data);
    }
}
