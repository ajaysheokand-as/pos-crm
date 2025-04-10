<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Bre_Model extends CI_Model {

    function __construct() {
        parent::__construct();
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

    public function getBreAllRuleResult($lead_id, $trans_rule_id = 0) {

        $return_array = array("status" => 0, "bre_rule_result" => "");

        $sql = "SELECT LBRR.lbrr_id,MBC.m_bre_cat_id,MBC.m_bre_cat_name, LBRR.lbrr_rule_name, LBRR.lbrr_rule_cutoff_value, LBRR.lbrr_rule_actual_value,LBRR.lbrr_rule_relevant_inputs,LBRR.lbrr_rule_system_decision_id,LBRR.lbrr_rule_manual_decision_id,LBRR.lbrr_rule_manual_decision_remarks";
        $sql .= " FROM lead_bre_rule_result LBRR";
        $sql .= " INNER JOIN master_bre_rule MBR ON(LBRR.lbrr_rule_id=MBR.m_bre_rule_id)";
        $sql .= " INNER JOIN master_bre_category MBC ON(MBR.m_bre_rule_catgory_id=MBC.m_bre_cat_id)";
        $sql .= " WHERE LBRR.lbrr_lead_id=$lead_id AND LBRR.lbrr_active=1";

        if (!empty($trans_rule_id)) {
            $sql .= " AND LBRR.lbrr_id=$trans_rule_id";
        }

        $sql .= " ORDER BY MBC.m_bre_cat_id ASC,LBRR.lbrr_rule_name ASC";

        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_array['status'] = 1;
            $return_array['bre_rule_result'] = $tempDetails->result_array();
        }

        return $return_array;
    }

    public function getMasterBreCategory() {
        $return_array = [];
        $sql = "SELECT MBC.m_bre_cat_id,MBC.m_bre_cat_name";
        $sql .= " FROM master_bre_category MBC";
        $sql .= " ORDER BY MBC.m_bre_cat_id ASC";

        $tempDetails = $this->db->query($sql);

        if ($tempDetails->num_rows() > 0) {
            $return_array = $tempDetails->result_array();
        }

        return $return_array;
    }

}

?>
