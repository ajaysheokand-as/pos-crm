<?php

defined('BASEPATH') or exit('No direct script access allowed');

class CronCommon_Model extends CI_Model {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
    }

    public function insert($table, $data) {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    public function update($table, $conditions, $data) {
        return $this->db->where($conditions)->update($table, $data);
    }

    public function insert_cron_logs($cron_name) {
        $cron_insert_array = array();
        $cron_insert_array["cs_name"] = $cron_name;
        $cron_insert_array["cs_start_datetime"] = date("Y-m-d H:i:s");
        $cron_insert_array["cs_ip"] = $this->input->ip_address();

        $this->db->insert('cron_scheduler_logs', $cron_insert_array);

        return $this->db->insert_id();
    }

    public function update_cron_logs($cron_id, $success_count = 0, $error_count = 0) {

        $cron_update_array = array();
        $cron_update_array["cs_end_datetime"] = date("Y-m-d H:i:s");
        $cron_update_array["cs_success_count"] = $success_count;
        $cron_update_array["cs_failed_count"] = $error_count;

        $this->db->where(['cs_id' => $cron_id])->update('cron_scheduler_logs', $cron_update_array);
    }

    public function get_cron_logs($cron_name, $current_datetime = "", $check_datetime = "") {

        $return_array = ['status' => 0, 'cron_data' => array()];

        $sql = "SELECT * FROM cron_scheduler_logs ";

        $sql .= " WHERE cs_name='$cron_name' AND cs_active=1 AND cs_deleted=0 ";

        if (!empty($current_datetime) && !empty($check_datetime)) {
            $sql .= " AND cs_start_datetime BETWEEN  '$current_datetime' AND '$check_datetime'";
        }

        $sql .= " ORDER BY cs_id DESC";

        $tempDetails = $this->db->query($sql);

        if (!empty($tempDetails->num_rows())) {
            $return_array['status'] = 1;
            $return_array['cron_data'] = $tempDetails->row_array();
        }

        return $return_array;
    }
}
